<?php
session_start();
require_once '../includes/db_connection.php';

// Add debugging to see what PayFast is sending
error_log("PayFast Return GET Parameters: " . print_r($_GET, true));
error_log("PayFast Return POST Parameters: " . print_r($_POST, true));

$order_id = $_GET['order_id'] ?? null;

if (!$order_id) {
    header('Location: ../pages/shop.php');
    exit();
}

// Get order details to verify it belongs to current user
if (isset($_SESSION['userId'])) {
    $userId = (int)$_SESSION['userId'];
    
    $stmt = $conn->prepare("SELECT * FROM orders WHERE id = ? AND buyer_id = ?");
    $stmt->execute([$order_id, $userId]);
    $order = $stmt->fetch();
    
    if (!$order) {
        header('Location: ../pages/shop.php');
        exit();
    }
    
    // **ENHANCED: Process PayFast payment data and handle seller earnings**
    $payment_processed = false;
    
    // Check if PayFast sent payment confirmation data
    if (isset($_GET['pf_payment_id']) || isset($_POST['pf_payment_id'])) {
        $pf_payment_id = $_GET['pf_payment_id'] ?? $_POST['pf_payment_id'] ?? null;
        $payment_status = $_GET['payment_status'] ?? $_POST['payment_status'] ?? 'COMPLETE';
        
        error_log("PayFast payment data found - ID: $pf_payment_id, Status: $payment_status");
        
        // Only process if payment is complete and we don't already have a PayFast ID
        if ($payment_status === 'COMPLETE' && $pf_payment_id && empty($order['payfast_payment_id'])) {
            try {
                $conn->beginTransaction();
                
                // **STEP 1: Update the primary order with PayFast payment ID**
                $update_order = $conn->prepare("
                    UPDATE orders 
                    SET payment_status = 'paid', 
                        payfast_payment_id = ?, 
                        updated_at = NOW()
                    WHERE id = ?
                ");
                $update_result = $update_order->execute([$pf_payment_id, $order_id]);
                
                // **STEP 2: Handle seller earnings for ALL related orders**
                if (strpos($order['order_number'], 'CART-') === 0) {
                    // For cart orders, find all orders created around the same time for this buyer
                    $related_orders_stmt = $conn->prepare("
                        SELECT id, seller_id, total_amount, order_number 
                        FROM orders 
                        WHERE buyer_id = ? 
                        AND DATE(created_at) = DATE(?) 
                        AND order_number LIKE 'CART-%'
                        AND payment_status = 'pending'
                    ");
                    $related_orders_stmt->execute([$userId, $order['created_at']]);
                    $related_orders = $related_orders_stmt->fetchAll();
                    
                    error_log("Found " . count($related_orders) . " related cart orders");
                    
                    foreach ($related_orders as $related_order) {
                        // Update each related order
                        $update_related = $conn->prepare("
                            UPDATE orders 
                            SET payment_status = 'paid', 
                                payfast_payment_id = ?, 
                                updated_at = NOW()
                            WHERE id = ?
                        ");
                        $update_related->execute([$pf_payment_id, $related_order['id']]);
                        
                        // Add seller earnings for each order
                        if (!empty($related_order['seller_id'])) {
                            $add_earnings = $conn->prepare("
                                INSERT INTO seller_earnings (seller_id, order_id, amount, created_at)
                                VALUES (?, ?, ?, NOW())
                                ON DUPLICATE KEY UPDATE 
                                amount = VALUES(amount),
                                created_at = VALUES(created_at)
                            ");
                            $add_earnings->execute([
                                $related_order['seller_id'],
                                $related_order['id'],
                                $related_order['total_amount']
                            ]);
                            
                            error_log("Added seller earnings for order " . $related_order['id'] . " to seller " . $related_order['seller_id']);
                        }
                    }
                } else {
                    // For direct purchases, just handle the single order
                    if (!empty($order['seller_id'])) {
                        $add_earnings = $conn->prepare("
                            INSERT INTO seller_earnings (seller_id, order_id, amount, created_at)
                            VALUES (?, ?, ?, NOW())
                            ON DUPLICATE KEY UPDATE 
                            amount = VALUES(amount),
                            created_at = VALUES(created_at)
                        ");
                        $add_earnings->execute([
                            $order['seller_id'],
                            $order_id,
                            $order['total_amount']
                        ]);
                        
                        error_log("Added seller earnings for direct purchase order $order_id to seller " . $order['seller_id']);
                    }
                }
                
                $conn->commit();
                $payment_processed = true;
                
                // Update the order array with new data
                $order['payment_status'] = 'paid';
                $order['payfast_payment_id'] = $pf_payment_id;
                
                error_log("Payment and seller earnings processed successfully in paymentSuccess.php - Order: $order_id, PayFast ID: $pf_payment_id");
                
            } catch (PDOException $e) {
                $conn->rollback();
                error_log("Error processing payment and seller earnings in paymentSuccess.php: " . $e->getMessage());
            }
        }
    }
    
    // **FALLBACK: Handle cases where PayFast ID might not be available but payment was successful**
    if (!$payment_processed && !empty($order) && $order['payment_status'] === 'pending') {
        // Check if this looks like a successful PayFast return
        $has_payfast_params = (
            isset($_GET['payment_status']) || 
            isset($_GET['item_name']) || 
            isset($_GET['amount_gross']) ||
            isset($_GET['m_payment_id'])
        );
        
        if ($has_payfast_params) {
            $payment_status = $_GET['payment_status'] ?? 'COMPLETE';
            $amount_gross = $_GET['amount_gross'] ?? $order['total_amount'];
            
            // Verify the amount matches (within 1 cent)
            $amount_matches = abs(floatval($amount_gross) - floatval($order['total_amount'])) <= 0.01;
            
            if ($payment_status === 'COMPLETE' && $amount_matches) {
                try {
                    $conn->beginTransaction();
                    
                    // Generate a temporary payment reference since PayFast ID might not be available
                    $temp_payment_ref = 'PF-' . date('YmdHis') . '-' . $order_id;
                    
                    // **STEP 1: Update order status**
                    $update_order = $conn->prepare("
                        UPDATE orders 
                        SET payment_status = 'paid', 
                            payfast_payment_id = COALESCE(payfast_payment_id, ?),
                            updated_at = NOW()
                        WHERE id = ? AND payment_status = 'pending'
                    ");
                    $update_order->execute([$temp_payment_ref, $order_id]);
                    
                    if ($update_order->rowCount() > 0) {
                        // **STEP 2: Add seller earnings**
                        if (!empty($order['seller_id'])) {
                            $add_earnings = $conn->prepare("
                                INSERT INTO seller_earnings (seller_id, order_id, amount, created_at)
                                VALUES (?, ?, ?, NOW())
                                ON DUPLICATE KEY UPDATE 
                                amount = VALUES(amount),
                                created_at = VALUES(created_at)
                            ");
                            $add_earnings->execute([
                                $order['seller_id'],
                                $order_id,
                                $order['total_amount']
                            ]);
                            
                            error_log("Added seller earnings using fallback method - Order: $order_id, Seller: " . $order['seller_id']);
                        }
                        
                        $order['payment_status'] = 'paid';
                        $payment_processed = true;
                        error_log("Payment marked as paid using PayFast return parameters - Order: $order_id");
                    }
                    
                    $conn->commit();
                    
                } catch (PDOException $e) {
                    $conn->rollback();
                    error_log("Error updating payment status and seller earnings (fallback): " . $e->getMessage());
                }
            }
        }
    }
    
    // Clear the cart after successful payment (only for cart orders)
    if (($order['payment_status'] === 'paid' || $payment_processed) && strpos($order['order_number'], 'CART-') === 0) {
        try {
            $stmt = $conn->prepare("DELETE FROM cart WHERE userId = ?");
            $stmt->execute([$userId]);
            error_log("Cart cleared for user $userId after successful payment");
        } catch (PDOException $e) {
            error_log("Error clearing cart: " . $e->getMessage());
        }
    }
    
} else {
    header('Location: ../pages/signIn.php');
    exit();
}

// Refresh order data to get the latest status
$stmt = $conn->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch();
?>

<!DOCTYPE html>
<html lang="en">
<?php  
    $pageTitle = "Payment Successful"; 
    include('../includes/head.php');  
?> 
<head>
    <style>
        .success-container {
            text-align: center;
            padding: 50px;
            max-width: 600px;
            margin: 0 auto;
        }
        .success-icon {
            font-size: 64px;
            color: #28a745;
            margin-bottom: 20px;
        }
        .warning-icon {
            font-size: 64px;
            color: #ffc107;
            margin-bottom: 20px;
        }
        .order-details {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: left;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin: 10px;
        }
        .processing-note {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            border-radius: 4px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="success-container">
        <?php if ($order['payment_status'] === 'paid'): ?>
            <div class="success-icon">✓</div>
            <h1>Payment Successful!</h1>
            <p>Thank you for your purchase. Your order has been processed successfully and seller earnings have been recorded.</p>
        <?php else: ?>
            <div class="warning-icon">⏳</div>
            <h1>Payment Processing</h1>
            <p>Your payment is being processed. This page will be updated once confirmation is received.</p>
            <!-- <div class="processing-note">
                <strong>Note:</strong> Payment confirmation may take a few minutes and the status will be updated.
            </div> -->
        <?php endif; ?>
        
        <div class="order-details">
            <h3>Order Details</h3>
            <p><strong>Order Number:</strong> <?php echo htmlspecialchars($order['order_number']); ?></p>
            <p><strong>Total Amount:</strong> R<?php echo number_format($order['total_amount'], 2); ?></p>
            <p><strong>Status:</strong> <?php echo ucfirst($order['payment_status']); ?></p>
            <?php if (!empty($order['payfast_payment_id'])): ?>
                <p><strong>Payment Reference:</strong> <?php echo htmlspecialchars($order['payfast_payment_id']); ?></p>
            <?php endif; ?>
        </div>
        
        <a href="../index.php" class="btn">Return to Home</a>
        <a href="../pages/viewOrders.php" class="btn">View My Orders</a>
        
        <?php if ($order['payment_status'] !== 'paid'): ?>
            <br><br>
            <a href="javascript:location.reload()" class="btn" style="background: #28a745;">Refresh Status</a>
        <?php endif; ?>
    </div>

    <?php if ($order['payment_status'] !== 'paid'): ?>
    <script>
        // Auto-refresh every 30 seconds to check for payment confirmation
        setTimeout(function() {
            location.reload();
        }, 30000);
    </script>
    <?php endif; ?>
</body>
</html>