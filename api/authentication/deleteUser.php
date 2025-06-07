<?php
session_start();
require_once '../includes/db_connection.php';

// Check if user is logged in and has admin privileges
if (!isset($_SESSION['isAdmin']) || $_SESSION['isAdmin'] != 1) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Access denied. Admin privileges required.']);
    exit;
}

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

// Check if userId is provided
if (!isset($_POST['userId']) || empty($_POST['userId'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'User ID is required.']);
    exit;
}

$userId = filter_var($_POST['userId'], FILTER_VALIDATE_INT);
if ($userId === false || $userId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid User ID.']);
    exit;
}

// Prevent admin from deleting their own account
if ($userId == $_SESSION['userId']) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Cannot delete your own account.']);
    exit;
}

try {
    // Check if user exists
    $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE userId = :userId");
    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
    $stmt->execute();
    $userExists = $stmt->fetchColumn();

    if (!$userExists) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'User not found.']);
        exit;
    }

    // Delete the user
    $stmt = $conn->prepare("DELETE FROM users WHERE userId = :userId");
    $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'User deleted successfully.']);
    } else {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to delete user.']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error deleting user: ' . $e->getMessage()]);
}
?>