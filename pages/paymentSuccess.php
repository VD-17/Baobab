<?php
session_start();
require_once '../includes/db_connection.php';

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
    
    // Clear the cart after successful payment (only for cart orders)
    if (strpos($order['order_number'], 'CART-') === 0) {
        try {
            $stmt = $conn->prepare("DELETE FROM cart WHERE userId = ?");
            $stmt->execute([$userId]);
        } catch (PDOException $e) {
            error_log("Error clearing cart: " . $e->getMessage());
        }
    }
} else {
    header('Location: ../pages/signIn.php');
    exit();
}
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
    </style>
</head>
<body>
    <div class="success-container">
        <div class="success-icon">✓</div>
        <h1>Payment Successful!</h1>
        <p>Thank you for your purchase. Your order has been processed successfully.</p>
        
        <div class="order-details">
            <h3>Order Details</h3>
            <p><strong>Order Number:</strong> <?php echo htmlspecialchars($order['order_number']); ?></p>
            <p><strong>Total Amount:</strong> R<?php echo number_format($order['total_amount'], 2); ?></p>
            <p><strong>Status:</strong> <?php echo ucfirst($order['payment_status']); ?></p>
        </div>
        
        <a href="../index.php" class="btn">Return to Home</a>
        <a href="../pages/viewOrders.php" class="btn">View My Orders</a>
    </div>
</body>
</html>