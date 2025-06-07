<?php
session_start();
require_once '../../includes/db_connection.php'; // Adjust path if needed

// Check if user is logged in
if (!isset($_SESSION['userId'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'You must be logged in to delete a product.']);
    exit();
}

$userId = $_SESSION['userId'];

// Check if product ID is provided via POST
if (!isset($_POST['productId']) || !is_numeric($_POST['productId'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid product ID.']);
    exit();
}

$productId = (int)$_POST['productId'];

try {
    // Verify the product exists and belongs to the user
    $stmt = $conn->prepare("SELECT userId, productPicture, productVideo FROM products WHERE id = ?");
    $stmt->execute([$productId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Product not found.']);
        exit();
    }

    if ($row['userId'] != $userId) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'You are not authorized to delete this product.']);
        exit();
    }

    // Begin transaction to ensure data consistency
    $conn->beginTransaction();

    // Delete related order_items first to avoid foreign key constraint violation
    $deleteOrderItems = $conn->prepare("DELETE FROM order_items WHERE product_id = ?");
    $deleteOrderItems->execute([$productId]);

    // Delete associated files
    if (!empty($row['productPicture'])) {
        $images = json_decode($row['productPicture'], true);
        if (is_array($images)) {
            foreach ($images as $image) {
                if (file_exists($image)) {
                    unlink($image);
                }
            }
        }
    }
    if (!empty($row['productVideo']) && file_exists($row['productVideo'])) {
        unlink($row['productVideo']);
    }

    // Delete the product
    $deleteStmt = $conn->prepare("DELETE FROM products WHERE id = ?");
    $deleteStmt->execute([$productId]);

    // Commit the transaction
    $conn->commit();

    header('Content-Type: application/json');
    echo json_encode(['success' => true, 'message' => 'Product deleted successfully.']);
} catch (PDOException $e) {
    // Rollback transaction on error
    $conn->rollBack();
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Error deleting product: ' . $e->getMessage()]);
}

exit();
?>