<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['userId'])) {
    header("Location: ../../pages/signIn.php");
    exit();
}

// Check if form was submitted via POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../../pages/settings.php?userId=" . $_SESSION['userId']);
    exit();
}

// Include database connection
include('../../includes/db_connection.php'); 

// Get form data and sanitize
$currentPassword = trim($_POST['current'] ?? '');
$newPassword = trim($_POST['new'] ?? '');
$userId = $_SESSION['userId'];

// Initialize error and success messages
$error = '';
$success = '';

// Validate input
if (empty($currentPassword) || empty($newPassword)) {
    $error = "Both current and new password are required.";
} elseif (strlen($newPassword) < 8) {
    $error = "New password must be at least 8 characters long.";
} elseif ($currentPassword === $newPassword) {
    $error = "New password must be different from your current password.";
} else {
    try {
        // Fetch current password hash from database
        $stmt = $conn->prepare("SELECT password FROM users WHERE userId = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            $error = "User not found.";
        } elseif (!password_verify($currentPassword, $user['password'])) {
            $error = "Current password is incorrect.";
        } else {
            // Hash the new password
            $hashedNewPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            // Update password in database (Fixed: using userId instead of id)
            $updateStmt = $conn->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE userId = ?");
            $result = $updateStmt->execute([$hashedNewPassword, $userId]);
            
            if ($result) {
                $success = "Password updated successfully!";
                
                // Optional: Log password change for security audit
                error_log("Password changed for user ID: " . $userId . " at " . date('Y-m-d H:i:s'));
            } else {
                $error = "Failed to update password. Please try again.";
            }
        }
    } catch (PDOException $e) {
        $error = "Database error occurred. Please try again later.";
        // Log the actual error for debugging (don't show to user)
        error_log("Password update error for user " . $userId . ": " . $e->getMessage());
    }
}

// Store messages in session for display
if (!empty($error)) {
    $_SESSION['error_message'] = $error;
}
if (!empty($success)) {
    $_SESSION['success_message'] = $success;
}

// Redirect back to settings page
header("Location: ../../pages/settings.php?userId=" . $_SESSION['userId']);
exit();
?>