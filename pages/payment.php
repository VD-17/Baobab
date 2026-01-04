<?php
session_start();
require_once '../includes/db_connection.php';

if (!isset($_SESSION['userId'])) {
    header('Location: ../pages/signIn.php');
    exit();
}

$userId = (int)$_SESSION['userId'];

// Debug: Log the POST data
error_log("POST data: " . print_r($_POST, true));

// Check if this is a direct product purchase or cart checkout
$isDirectPurchase = isset($_POST['product_id']);

error_log("Is direct purchase: " . ($isDirectPurchase ? 'YES' : 'NO'));

if ($isDirectPurchase) {
    // Direct product purchase
    $product_id = (int)$_POST['product_id'];
    $quantity = (int)($_POST['quantity'] ?? 1);
    
    error_log("Product ID: $product_id, Quantity: $quantity");
    
    // Validate inputs
    if ($product_id <= 0 || $quantity <= 0) {
        error_log("Invalid input validation failed");
        $_SESSION['errors'] = ["Invalid product or quantity - Product ID: $product_id, Quantity: $quantity"];
        header('Location: ../pages/shop.php');
        exit();
    }
    
    // Get product and verify seller is actually a seller
    $product_query = $conn->prepare("
        SELECT p.*, u.userId as seller_id, u.firstName, u.lastName, u.is_seller
        FROM products p
        INNER JOIN users u ON p.userId = u.userId
        WHERE p.id = ? AND u.is_seller = 1
    ");
    $product_query->execute([$product_id]);
    $product = $product_query->fetch(PDO::FETCH_ASSOC);
    
    error_log("Product query result: " . print_r($product, true));
    
    if (!$product) {
        // Debug check
        $debug_query = $conn->prepare("SELECT p.*, u.is_seller FROM products p INNER JOIN users u ON p.userId = u.userId WHERE p.id = ?");
        $debug_query->execute([$product_id]);
        $debug_result = $debug_query->fetch(PDO::FETCH_ASSOC);
        
        error_log("Debug query result: " . print_r($debug_result, true));
        
        if (!$debug_result) {
            $_SESSION['errors'] = ["Product not found with ID: $product_id"];
        } else {
            $_SESSION['errors'] = ["Product seller is not active - is_seller: " . $debug_result['is_seller']];
        }
        header('Location: ../pages/shop.php');
        exit();
    }
    
    $item_price = $product['price'];
    $total_amount = $item_price * $quantity;
    $order_number = 'ORD-' . date('Ymd') . '-' . rand(1000, 9999);
    
    error_log("Order details - Price: $item_price, Total: $total_amount, Order: $order_number");
    
    try {
        $conn->beginTransaction();
        
        // Create the main order (without product_id to avoid foreign key issues)
        $create_order = $conn->prepare("
            INSERT INTO orders 
            (order_number, buyer_id, seller_id, total_amount, payment_status, created_at)
            VALUES (?, ?, ?, ?, 'pending', NOW())
        ");
        
        $order_result = $create_order->execute([
            $order_number,
            $userId,
            $product['seller_id'],
            $total_amount,
        ]);
        
        error_log("Order insert result: " . ($order_result ? 'SUCCESS' : 'FAILED'));
        
        $order_id = $conn->lastInsertId();
        error_log("New order ID: $order_id");
        
        // Create the order item
        $create_order_item = $conn->prepare("
            INSERT INTO order_items 
            (order_id, product_id, quantity, item_price, subtotal)
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $item_result = $create_order_item->execute([
            $order_id,
            $product_id,
            $quantity,
            $item_price,
            $total_amount
        ]);
        
        error_log("Order item insert result: " . ($item_result ? 'SUCCESS' : 'FAILED'));
        
        $conn->commit();
        error_log("Transaction committed successfully");
        
    } catch (PDOException $e) {
        $conn->rollback();
        error_log("Database error in direct purchase: " . $e->getMessage());
        $_SESSION['errors'] = ["Unable to create order: " . $e->getMessage()];
        header('Location: ../pages/shop.php');
        exit();
    }
    
    $total = $total_amount;
    $cartItems = [];
    $ordersBySeller = [[
        'order_id' => $order_id,
        'order_number' => $order_number,
        'seller_id' => $product['seller_id'],
        'seller_name' => trim($product['firstName'] . ' ' . $product['lastName']),
        'items' => [[
            'id' => $product['id'],
            'name' => $product['productName'],
            'price' => $product['price'],
            'quantity' => $quantity,
            'image_path' => $product['productPicture'] ?? null
        ]],
        'total' => $total_amount
    ]];
    
    error_log("Direct purchase ordersBySeller created: " . print_r($ordersBySeller, true));
    
} else {
    // Cart checkout
    error_log("Processing cart checkout");
    
    try {
        // Get cart items with seller information - only from active sellers
        $stmt = $conn->prepare("
            SELECT c.quantity, 
                   p.id, p.productName AS name, p.price, 
                   p.productPicture AS image_path,
                   p.userId as seller_id,
                   u.firstName as seller_first_name,
                   u.lastName as seller_last_name,
                   u.is_seller
            FROM cart c
            INNER JOIN products p ON c.productId = p.id
            INNER JOIN users u ON p.userId = u.userId
            WHERE c.userId = ? AND u.is_seller = 1
            ORDER BY u.userId, p.productName
        ");
        $stmt->execute([$userId]);
        $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        error_log("Cart items found: " . count($cartItems));
        error_log("Cart items: " . print_r($cartItems, true));

        if (empty($cartItems)) {
            // Check if cart has items from inactive sellers
            $inactive_check = $conn->prepare("
                SELECT COUNT(*) as inactive_count
                FROM cart c
                INNER JOIN products p ON c.productId = p.id
                INNER JOIN users u ON p.userId = u.userId
                WHERE c.userId = ? AND u.is_seller = 0
            ");
            $inactive_check->execute([$userId]);
            $inactive_result = $inactive_check->fetch();
            
            error_log("Inactive sellers count: " . $inactive_result['inactive_count']);
            
            if ($inactive_result['inactive_count'] > 0) {
                $_SESSION['errors'] = ["Some items in your cart are from inactive sellers. Please remove them and try again."];
            } else {
                $_SESSION['errors'] = ["Your cart is empty"];
            }
            header('Location: ../pages/shop.php');
            exit();
        }

        // Group cart items by seller
        $itemsBySeller = [];
        foreach ($cartItems as $item) {
            $sellerId = $item['seller_id'];
            if (!isset($itemsBySeller[$sellerId])) {
                $itemsBySeller[$sellerId] = [
                    'seller_id' => $sellerId,
                    'seller_name' => trim($item['seller_first_name'] . ' ' . $item['seller_last_name']),
                    'items' => [],
                    'total' => 0
                ];
            }
            $itemsBySeller[$sellerId]['items'][] = $item;
            $itemsBySeller[$sellerId]['total'] += $item['price'] * $item['quantity'];
        }

        error_log("Items by seller: " . print_r($itemsBySeller, true));

        // Create separate orders for each seller
        $ordersBySeller = [];
        $overallTotal = 0;
        
        try {
            $conn->beginTransaction();
            
            foreach ($itemsBySeller as $sellerId => $sellerData) {
                $order_number = 'CART-' . date('Ymd') . '-' . rand(1000, 9999);
                
                error_log("Creating order for seller $sellerId: $order_number");
                
                // Create order for this seller
                $create_order = $conn->prepare("
                    INSERT INTO orders 
                    (order_number, buyer_id, seller_id, total_amount, payment_status, created_at)
                    VALUES (?, ?, ?, ?, 'pending', NOW())
                ");
                
                $order_result = $create_order->execute([
                    $order_number,
                    $userId,
                    $sellerId,
                    $sellerData['total']
                ]);
                
                error_log("Order creation result: " . ($order_result ? 'SUCCESS' : 'FAILED'));
                
                $order_id = $conn->lastInsertId();
                error_log("Order ID: $order_id");
                
                // Create order items for this order
                $create_order_item = $conn->prepare("
                    INSERT INTO order_items 
                    (order_id, product_id, quantity, item_price, subtotal)
                    VALUES (?, ?, ?, ?, ?)
                ");
                
                foreach ($sellerData['items'] as $item) {
                    $subtotal = $item['price'] * $item['quantity'];
                    $item_result = $create_order_item->execute([
                        $order_id,
                        $item['id'],
                        $item['quantity'],
                        $item['price'],
                        $subtotal
                    ]);
                    error_log("Order item creation result: " . ($item_result ? 'SUCCESS' : 'FAILED'));
                }
                
                // Store order info for display
                $ordersBySeller[] = [
                    'order_id' => $order_id,
                    'order_number' => $order_number,
                    'seller_id' => $sellerId,
                    'seller_name' => $sellerData['seller_name'],
                    'items' => $sellerData['items'],
                    'total' => $sellerData['total']
                ];
                
                $overallTotal += $sellerData['total'];
            }
            
            $conn->commit();
            error_log("Cart transaction committed successfully");
            
        } catch (PDOException $e) {
            $conn->rollback();
            error_log("Cart transaction error: " . $e->getMessage());
            throw $e;
        }
        
        $total = $overallTotal;
        
        // For payment, we'll use the first order ID
        $order_id = $ordersBySeller[0]['order_id'];

        error_log("Cart checkout completed - Total orders: " . count($ordersBySeller) . ", Total amount: $total");

    } catch (PDOException $e) {
        error_log("Error processing cart: " . $e->getMessage());
        $_SESSION['errors'] = ["Error processing cart: " . $e->getMessage()];
        $cartItems = [];
        $ordersBySeller = [];
        $total = 0;
        $order_id = null;
    }
}

// Debug the final check
error_log("Final check - ordersBySeller count: " . count($ordersBySeller ?? []));
error_log("Final check - total: " . ($total ?? 0));

// If no valid orders were created, redirect
if (empty($ordersBySeller) || $total <= 0) {
    error_log("FAILING: No valid orders created");
    $_SESSION['errors'] = ["No valid orders could be created - Debug info logged"];
    header('Location: ../pages/shop.php');
    exit();
}

error_log("SUCCESS: Orders created, proceeding to payment");

// Fetch user data
$stmt = $conn->prepare("SELECT firstName, lastName, email FROM users WHERE userId = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    $_SESSION['errors'] = ["User not found"];
    header('Location: ../pages/login.php');
    exit();
}

function generateSignature($data, $passPhrase = null) {
    $pfOutput = '';
    foreach( $data as $key => $val ) {
        if($val !== '' && $val !== null) {
            $pfOutput .= $key .'='. urlencode( trim( $val ) ) .'&';
        }
    }
    $getString = substr( $pfOutput, 0, -1 );
    if( $passPhrase !== null && $passPhrase !== '' ) {
        $getString .= '&passphrase='. urlencode( trim( $passPhrase ) );
    }
    return md5( $getString );
}

// Payment configuration
$data = array(
    'merchant_id' => '10039349',
    'merchant_key' => '1st2fo54c4vnk',
    // 'return_url' => 'https://baobab.great-site.net/pages/paymentSuccess.php?order_id=' . $order_id,
    // 'cancel_url' => 'https://baobab.great-site.net/pages/cancelPayment.php',
    // 'notify_url' => 'https://baobab.great-site.net/pages/notifyPayment.php',
    'return_url' => 'https://a8dd-41-56-193-145.ngrok-free.app/Baobab/pages/paymentSuccess.php?order_id=' . $order_id,
    'cancel_url' => 'https://a8dd-41-56-193-145.ngrok-free.app/Baobab/pages/cancelPayment.php',
    'notify_url' => 'https://a8dd-41-56-193-145.ngrok-free.app/Baobab/pages/notifyPayment.php',
    'name_first' => $user['firstName'] ?? 'Test',
    'name_last'  => $user['lastName'] ?? 'User',
    'email_address'=> $user['email'] ?? 'testbuyer@example.com',
    'm_payment_id' => $order_id,
    'amount' => number_format($total, 2, '.', ''),
    'item_name' => $isDirectPurchase ? $ordersBySeller[0]['items'][0]['name'] : 'Multi-seller Cart Purchase',
    'custom_str1' => (string)$order_id
);

$signature = generateSignature($data);
$data['signature'] = $signature;

$testingMode = true;
$pfHost = $testingMode ? 'sandbox.payfast.co.za' : 'www.payfast.co.za';
?> 

<!DOCTYPE html>
<html lang="en">
<?php  
$pageTitle = $isDirectPurchase ? "Complete Purchase" : "My Cart"; 
include('../includes/head.php');  
?>
<head>
    <link rel="stylesheet" href="../assets/css/myListing.css">
    <link rel="stylesheet" href="../assets/css/payment.css">
    <style>
        .seller-order {
            border: 1px solid #ddd;
            margin: 15px 0;
            padding: 15px;
            border-radius: 8px;
            background-color: #f9f9f9;
        }
        .seller-order h3 {
            color: #2c3e50;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 2px solid #3498db;
        }
        .seller-total {
            font-weight: bold;
            text-align: right;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #bdc3c7;
        }
        .cart-item {
            margin: 8px 0;
            padding: 8px;
            background-color: white;
            border-radius: 4px;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            border-radius: 4px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="cart-container">
        <h2><?php echo $isDirectPurchase ? "Complete Your Purchase" : "My Cart - Orders by Seller"; ?></h2>

        <?php if (isset($_SESSION['errors'])): ?>
            <div class="error">
                <?php foreach ($_SESSION['errors'] as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
                <?php unset($_SESSION['errors']); ?>
            </div>
        <?php endif; ?>

        <?php if ($isDirectPurchase && !empty($ordersBySeller)): ?>
            <div class="seller-order">
                <h3>📦 Order from <?php echo htmlspecialchars($ordersBySeller[0]['seller_name']); ?></h3>
                <p><strong>Order Number:</strong> <?php echo htmlspecialchars($ordersBySeller[0]['order_number']); ?></p>
                
                <div class="cart-item">
                    <div>
                        <h4><?php echo htmlspecialchars($ordersBySeller[0]['items'][0]['name']); ?></h4>
                        <p>Price: R<?php echo number_format($ordersBySeller[0]['items'][0]['price'], 2); ?></p>
                        <p>Quantity: <?php echo (int)$ordersBySeller[0]['items'][0]['quantity']; ?></p>
                        <p>Total: R<?php echo number_format($ordersBySeller[0]['total'], 2); ?></p>
                    </div>
                </div>
            </div>
        <?php elseif (!$isDirectPurchase && !empty($ordersBySeller)): ?>
            <?php foreach ($ordersBySeller as $order): ?>
                <div class="seller-order">
                    <h3>📦 Order from <?php echo htmlspecialchars($order['seller_name']); ?></h3>
                    <p><strong>Order Number:</strong> <?php echo htmlspecialchars($order['order_number']); ?></p>
                    
                    <?php foreach ($order['items'] as $item): ?>
                        <div class="cart-item">
                            <div>
                                <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                                <p>Price: R<?php echo number_format($item['price'], 2); ?></p>
                                <p>Quantity: <?php echo (int)$item['quantity']; ?></p>
                                <p>Subtotal: R<?php echo number_format($item['price'] * $item['quantity'], 2); ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <div class="seller-total">
                        <p>Order Total: R<?php echo number_format($order['total'], 2); ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <?php if (count($ordersBySeller) > 1): ?>
                <div class="order-summary">
                    <p><strong>📋 Summary:</strong> <?php echo count($ordersBySeller); ?> separate orders from different sellers</p>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <?php if (!empty($ordersBySeller) && $total > 0): ?>
            <div class="total">
                <p><strong>💰 Grand Total: R<?php echo number_format($total, 2); ?></strong></p>
            </div>

            <form action="https://<?php echo $pfHost; ?>/eng/process" method="post">
                <?php foreach($data as $name => $value): ?>
                    <input name="<?php echo htmlspecialchars($name); ?>" type="hidden" value="<?php echo htmlspecialchars($value); ?>">
                <?php endforeach; ?>
                <input type="submit" value="Pay Now - All Orders" class="payfast-btn">
            </form>
        <?php endif; ?>

        <a href="../pages/shop.php" class="continue-shopping">Continue Shopping</a>
    </div>
</body>
</html>