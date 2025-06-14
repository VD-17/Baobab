<?php
    session_start();
    require_once('../includes/db_connection.php');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: ../pages/shop.php');
        exit();
    }

    if (!isset($_SESSION['userId'])) {
        $_SESSION['errors'] = ['Please log in to manage your favorites.'];
        $redirect = $_POST['redirect_url'] ?? '../pages/shop.php';
        header('Location: ../pages/signIn.php?redirect=' . urlencode($redirect));
        exit();
    } 

    $userId = $_SESSION['userId'];
    $productId = (int)$_POST['product_id'];
    $redirectUrl = $_POST['redirect_url'] ?? '../pages/shop.php';

    try {
        // Check if product exists
        $checkStmt = $conn->prepare("SELECT id FROM products WHERE id = ?");
        $checkStmt->execute([$productId]);
        
        if (!$checkStmt->fetch()) {
            $_SESSION['errors'] = ['Product not found.'];
            header('Location: ' . $redirectUrl);
            exit();
        }
        
        // Check if already in favorites
        $favStmt = $conn->prepare("SELECT id FROM favorites WHERE userid = ? AND productid = ?");
        $favStmt->execute([$userId, $productId]);
        
        if ($favStmt->fetch()) {
            // Remove from favorites
            $deleteStmt = $conn->prepare("DELETE FROM favorites WHERE userid = ? AND productid = ?");
            $deleteStmt->execute([$userId, $productId]);
            $_SESSION['success'] = ['Product removed from favorites.'];
        } else {
            // Add to favorites
            // Replace your current INSERT statement with:
            $insertStmt = $conn->prepare("INSERT INTO favorites (userid, productid, created_at) VALUES (?, ?, NOW())");
            $insertStmt->execute([$userId, $productId]);
            $_SESSION['success'] = ['Product added to favorites!'];
        }
        
    } catch (PDOException $e) {
        $_SESSION['errors'] = ['Error updating favorites. Please try again.'];
    }

    header('Location: ' . $redirectUrl);
    exit();
?>