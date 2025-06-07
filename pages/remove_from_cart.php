<?php
session_start();
require_once '../includes/db_connection.php';

if (isset($_GET['cart_id']) && isset($_SESSION['userId'])) {
    $cartId = (int)$_GET['cart_id'];
    $userId = $_SESSION['userId'];

    try {
        $stmt = $conn->prepare("DELETE FROM cart WHERE id = ? AND userId = ?");
        $stmt->execute([$cartId, $userId]);
        $_SESSION['message'] = 'Item removed from cart';
    } catch (PDOException $e) {
        $_SESSION['errors'] = ['Error removing item from cart'];
    }
}

header('Location: cart.php');
exit();
?>