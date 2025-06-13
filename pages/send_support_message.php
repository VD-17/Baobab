<?php
session_start();
require_once '../includes/db_connection.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['userId'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['message']) || empty(trim($input['message']))) {
    echo json_encode(['success' => false, 'message' => 'Message cannot be empty']);
    exit;
}

$message = trim($input['message']);
$senderId = $_SESSION['userId'];
$receiverId = 7; // Send only to user ID 7

try {
    // Insert the message
    $stmt = $conn->prepare("
        INSERT INTO messages (sender_id, receiver_id, message, sent_at, is_read) 
        VALUES (?, ?, ?, NOW(), 0)
    ");
    
    $stmt->execute([$senderId, $receiverId, $message]);
    
    echo json_encode([
        'success' => true, 
        'message' => 'Message sent successfully to support'
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false, 
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>