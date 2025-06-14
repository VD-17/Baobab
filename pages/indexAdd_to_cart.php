<?php
session_start();
require_once '../includes/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['errors'] = ['Invalid request method.'];
    header('Location: ../pages/shop.php');
    exit();
}

if (!isset($_SESSION['userId'])) {
    $_SESSION['errors'] = ['Please log in to add items to your cart.'];
    $redirect = $_POST['redirect_url'] ?? '../pages/shop.php';
    // Sanitize redirect URL to prevent open redirects
    $redirect = filter_var($redirect, FILTER_VALIDATE_URL) ? $redirect : '../pages/shop.php';
    header('Location: ../pages/signIn.php?redirect=' . urlencode($redirect));
    exit();
}

$userId = (int)$_SESSION['userId'];
// $productId = (int)$_POST['product_id'];  
$productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$quantity = max(1, (int)$_POST['quantity']); // Ensure quantity is at least 1
// $redirectUrl = $_POST['redirect_url'] ?? '../pages/shop.php'; 

// Sanitize redirect URL
// $redirectUrl = filter_var($redirectUrl, FILTER_VALIDATE_URL) ? $redirectUrl : '../pages/shop.php';

try {
    // Verify product exists
    $stmt = $conn->prepare("SELECT id FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($productId <= 0 || !$product) {
        $_SESSION['errors'] = ['Invalid product ID or product not found.'];
        // header('Location: ' . $redirectUrl); 
        exit();
    }

    // Add to cart (or update quantity if already exists)
    $stmt = $conn->prepare("
        INSERT INTO cart (userId, productId, quantity) 
        VALUES (?, ?, ?) 
        ON DUPLICATE KEY UPDATE quantity = quantity + VALUES(quantity)
    ");
    $stmt->execute([$userId, $productId, $quantity]);

    // $_SESSION['success'] = ['Product added to cart successfully!'];
    $_SESSION['cart_success'] = ['Product added to cart successfully!'];

} catch (PDOException $e) {
    // Log error internally (e.g., to a file or error tracking system)
    error_log("Error adding product to cart: " . $e->getMessage());
    $_SESSION['errors'] = ['Error adding product to cart. Please try again.'];
}

header('Location: ../index.php');
exit();
?>