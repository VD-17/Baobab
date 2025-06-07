<?php
session_start();
require_once '../includes/db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $cartId = (int)$data['cart_id'];
    $quantity = (int)$data['quantity'];
    $userId = $_SESSION['userId'];

    try {
        $stmt = $conn->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND userId = ?");
        $stmt->execute([$quantity, $cartId, $userId]);
        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
}
?>