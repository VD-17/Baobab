<?php
// Tell Payfast that this page is reachable by triggering a header 200
header( 'HTTP/1.0 200 OK' );
flush();

require_once '../includes/db_connection.php';

define( 'SANDBOX_MODE', true );
$pfHost = SANDBOX_MODE ? 'sandbox.payfast.co.za' : 'www.payfast.co.za';

// Posted variables from ITN
$pfData = $_POST;

// Strip any slashes in data
foreach( $pfData as $key => $val ) {
    $pfData[$key] = stripslashes( $val );
}

// Convert posted variables to a string
$pfParamString = '';
foreach( $pfData as $key => $val ) {
    if( $key !== 'signature' ) {
        $pfParamString .= $key .'='. urlencode( $val ) .'&';
    } else {
        break;
    }
}
$pfParamString = substr( $pfParamString, 0, -1 );

function pfValidSignature( $pfData, $pfParamString, $pfPassphrase = null ) {
    // Calculate security signature
    if($pfPassphrase === null) {
        $tempParamString = $pfParamString;
    } else {
        $tempParamString = $pfParamString.'&passphrase='.urlencode( $pfPassphrase );
    }

    $signature = md5( $tempParamString );
    return ( $pfData['signature'] === $signature );
}

function pfValidIP() {
    // Variable initialization
    $validHosts = array(
        'www.payfast.co.za',
        'sandbox.payfast.co.za',
        'w1w.payfast.co.za',
        'w2w.payfast.co.za',
    );

    $validIps = [];

    foreach( $validHosts as $pfHostname ) {
        $ips = gethostbynamel( $pfHostname );
        if( $ips !== false )
            $validIps = array_merge( $validIps, $ips );
    }

    // Remove duplicates
    $validIps = array_unique( $validIps );
    
    // For sandbox testing, we'll be less strict about IP validation
    if (SANDBOX_MODE) {
        return true; // Skip IP validation in sandbox
    }
    
    $referrerIp = $_SERVER['REMOTE_ADDR'];
    return in_array( $referrerIp, $validIps, true );
}

function pfValidPaymentData( $expectedAmount, $pfData ) {
    return !(abs((float)$expectedAmount - (float)$pfData['amount_gross']) > 0.01);
}

function pfValidServerConfirmation( $pfParamString, $pfHost = 'sandbox.payfast.co.za', $pfProxy = null ) {
    // Use cURL (if available)
    if( in_array( 'curl', get_loaded_extensions(), true ) ) {
        // Variable initialization
        $url = 'https://'. $pfHost .'/eng/query/validate';

        // Create default cURL object
        $ch = curl_init();
    
        // Set cURL options
        curl_setopt( $ch, CURLOPT_USERAGENT, NULL );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch, CURLOPT_HEADER, false );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 2 );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false ); // Disabled for testing
        curl_setopt( $ch, CURLOPT_URL, $url );
        curl_setopt( $ch, CURLOPT_POST, true );
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $pfParamString );
        
        if( !empty( $pfProxy ) )
            curl_setopt( $ch, CURLOPT_PROXY, $pfProxy );
    
        // Execute cURL
        $response = curl_exec( $ch );
        curl_close( $ch );
        
        if ($response === 'VALID') {
            return true;
        }
    }
    return false;
}

// Log the notification for debugging
error_log("PayFast ITN received: " . json_encode($pfData));

// Get order_id from custom_str1 or m_payment_id
$order_id = $pfData['custom_str1'] ?? $pfData['m_payment_id'] ?? null;
$payment_status = $pfData['payment_status'] ?? '';
$pf_payment_id = $pfData['pf_payment_id'] ?? '';

if (!$order_id) {
    error_log("PayFast: No order ID found in notification");
    echo "OK";
    exit;
}

// Get order details
try {
    $order_query = $conn->prepare("SELECT * FROM orders WHERE id = ?");
    $order_query->execute([$order_id]);
    $order = $order_query->fetch();
    
    if (!$order) {
        error_log("PayFast: Order not found with ID: " . $order_id);
        echo "OK";
        exit;
    }
    
    $expectedAmount = $order['total_amount'];
    
} catch (PDOException $e) {
    error_log("PayFast: Database error: " . $e->getMessage());
    echo "OK";
    exit;
}

// Skip validation for sandbox testing (remove this in production)
if (SANDBOX_MODE) {
    $check1 = true; // Skip signature validation
    $check2 = true; // Skip IP validation  
    $check3 = pfValidPaymentData($expectedAmount, $pfData);
    $check4 = true; // Skip server confirmation
} else {
    // Perform validation checks for production
    $check1 = pfValidSignature($pfData, $pfParamString);
    $check2 = pfValidIP();
    $check3 = pfValidPaymentData($expectedAmount, $pfData);
    $check4 = pfValidServerConfirmation($pfParamString, $pfHost);
}

// Process the payment if all checks pass
if ($check1 && $check2 && $check3 && $check4) {
    if ($payment_status === 'COMPLETE') {
        try {
            // Update order status
            $update_order = $conn->prepare("
                UPDATE orders 
                SET payment_status = 'paid', 
                    payfast_payment_id = ?, 
                    updated_at = NOW()
                WHERE id = ?
            ");
            $update_order->execute([$pf_payment_id, $order_id]);
            
            // Add to seller earnings (only if there's a seller)
            if (!empty($order['seller_id'])) {
                $add_earnings = $conn->prepare("
                    INSERT INTO seller_earnings (seller_id, order_id, amount, created_at)
                    VALUES (?, ?, ?, NOW())
                ");
                $add_earnings->execute([
                    $order['seller_id'],
                    $order_id,
                    $order['total_amount']
                ]);
            }
            
            error_log("PayFast: Payment processed successfully for order " . $order_id);
            
        } catch (PDOException $e) {
            error_log("PayFast: Database error processing payment: " . $e->getMessage());
        }
    } else {
        error_log("PayFast: Payment status is not COMPLETE: " . $payment_status);
    }
} else {
    error_log("PayFast: Validation failed - Checks: " . json_encode([
        'signature' => $check1,
        'ip' => $check2, 
        'amount' => $check3,
        'server' => $check4
    ]));
}

// Always respond with 200 OK to PayFast
echo "OK";
?>