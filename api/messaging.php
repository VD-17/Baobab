<?php
session_start();

// Include database connection
require_once '../includes/db_connection.php';

// Set JSON header
header('Content-Type: application/json');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Log all requests for debugging
error_log("Messaging request: " . print_r($_REQUEST, true));
error_log("Session data: " . print_r($_SESSION, true));

try {
    // Get user ID from session
    $user_id = isset($_SESSION['userId']) ? $_SESSION['userId'] : 
               (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 
               (isset($_SESSION['id']) ? $_SESSION['id'] : null));

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] == 'send_message') {
        if (!$user_id) {
            echo json_encode([
                'success' => false, 
                'error' => 'User not logged in'
            ]);
            exit;
        }
        
        $receiver_id = isset($_POST['receiver_id']) ? (int)$_POST['receiver_id'] : 0;
        $message = isset($_POST['message']) ? trim($_POST['message']) : '';
        
        // Validate input
        if (empty($message)) {
            echo json_encode(['success' => false, 'error' => 'Message cannot be empty']);
            exit;
        }
        
        if ($receiver_id <= 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid receiver ID']);
            exit;
        }
        
        // Prevent self-messaging
        if ($user_id == $receiver_id) {
            echo json_encode(['success' => false, 'error' => 'Cannot send message to yourself']);
            exit;
        }
        
        // Check if receiver exists
        $checkStmt = $conn->prepare("SELECT userId FROM users WHERE userId = ?");
        $checkStmt->execute([$receiver_id]);
        if (!$checkStmt->fetch()) {
            echo json_encode(['success' => false, 'error' => 'Receiver not found']);
            exit;
        }
        
        // Insert message
        $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, message, sent_at) VALUES (?, ?, ?, NOW())");
        $result = $stmt->execute([$user_id, $receiver_id, $message]);
        
        if ($result) {
            // Update or create conversation
            $user1 = min($user_id, $receiver_id);
            $user2 = max($user_id, $receiver_id);
            
            // Check if conversation exists
            $checkConvStmt = $conn->prepare("
                SELECT id FROM conversations 
                WHERE user1_id = ? AND user2_id = ?
            ");
            $checkConvStmt->execute([$user1, $user2]);
            
            if ($checkConvStmt->fetch()) {
                // Update existing conversation
                $updateConvStmt = $conn->prepare("
                    UPDATE conversations 
                    SET last_message_at = NOW() 
                    WHERE user1_id = ? AND user2_id = ?
                ");
                $updateConvStmt->execute([$user1, $user2]);
            } else {
                // Create new conversation
                $insertConvStmt = $conn->prepare("
                    INSERT INTO conversations (user1_id, user2_id, last_message_at) 
                    VALUES (?, ?, NOW())
                ");
                $insertConvStmt->execute([$user1, $user2]);
            }
            
            echo json_encode([
                'success' => true, 
                'message' => 'Message sent successfully',
                'debug' => [
                    'sender_id' => $user_id,
                    'receiver_id' => $receiver_id,
                    'message_length' => strlen($message)
                ]
            ]);
        } else {
            $errorInfo = $stmt->errorInfo();
            echo json_encode([
                'success' => false, 
                'error' => 'Failed to send message',
                'sql_error' => $errorInfo
            ]);
        }
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] == 'get_messages') {
        if (!$user_id) {
            echo json_encode(['success' => false, 'error' => 'User not logged in']);
            exit;
        }
        
        $other_user_id = isset($_GET['other_user_id']) ? (int)$_GET['other_user_id'] : 0;
        
        if ($other_user_id <= 0) {
            echo json_encode(['success' => false, 'error' => 'Invalid other user ID']);
            exit;
        }
        
        $stmt = $conn->prepare("
            SELECT m.*, u.firstname as sender_name, m.sender_id
            FROM messages m 
            LEFT JOIN users u ON m.sender_id = u.userId 
            WHERE (m.sender_id = ? AND m.receiver_id = ?) 
               OR (m.sender_id = ? AND m.receiver_id = ?)
            ORDER BY m.sent_at ASC
            LIMIT 100
        ");
        $stmt->execute([$user_id, $other_user_id, $other_user_id, $user_id]);
        $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Mark messages as read
        $markReadStmt = $conn->prepare("
            UPDATE messages 
            SET is_read = TRUE 
            WHERE sender_id = ? AND receiver_id = ? AND is_read = FALSE
        ");
        $markReadStmt->execute([$other_user_id, $user_id]);
        
        echo json_encode(['success' => true, 'messages' => $messages]);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] == 'check_notifications') {
        if (!$user_id) {
            echo json_encode(['success' => false, 'error' => 'User not logged in']);
            exit;
        }

        $stmt = $conn->prepare("
            SELECT COUNT(*) as unread_count
            FROM messages
            WHERE receiver_id = ? AND is_read = FALSE
        ");
        $stmt->execute([$user_id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'success' => true,
            'unread_count' => (int)$result['unread_count']
        ]);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] == 'get_conversations') {
        if (!$user_id) {
            echo json_encode(['success' => false, 'error' => 'User not logged in']);
            exit;
        }

        $stmt = $conn->prepare("
            SELECT 
                c.id,
                c.user1_id,
                c.user2_id,
                c.last_message_at,
                u.userId,
                u.firstname,
                u.lastname,
                u.profile_picture,
                (SELECT COUNT(*) 
                 FROM messages m 
                 WHERE m.receiver_id = ? 
                 AND (m.sender_id = u.userId)
                 AND m.is_read = FALSE) as unread_count,
                (SELECT message 
                 FROM messages m 
                 WHERE (m.sender_id = u.userId AND m.receiver_id = ?) 
                    OR (m.sender_id = ? AND m.receiver_id = u.userId)
                 ORDER BY m.sent_at DESC 
                 LIMIT 1) as last_message
            FROM conversations c
            JOIN users u ON (u.userId = c.user1_id OR u.userId = c.user2_id) 
            AND u.userId != ?
            WHERE c.user1_id = ? OR c.user2_id = ?
            ORDER BY c.last_message_at DESC
        ");
        $stmt->execute([$user_id, $user_id, $user_id, $user_id, $user_id, $user_id]);
        $conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'conversations' => $conversations]);
        exit;
    }
    
    // If no valid action
    echo json_encode(['success' => false, 'error' => 'Invalid action or method']);

} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    error_log("General error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Error: ' . $e->getMessage()]);
}

if ($_GET['action'] === 'mark_read' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $message_id = $data['message_id'] ?? null;
    
    if ($message_id && $user_id) {
        try {
            $stmt = $conn->prepare("UPDATE messages SET is_read = TRUE WHERE id = ? AND receiver_id = ?");
            $stmt->execute([$message_id, $user_id]);
            echo json_encode(['success' => true]);
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid message ID or user not logged in']);
    }
    exit;
}
?>