<?php
session_start();
require_once '../includes/db_connection.php';

// Check for token and password in session
if (!isset($_SESSION['reset_token']) || !isset($_SESSION['password'])) {
    die("Invalid request.");
}

$token = $_SESSION['reset_token'];
$password = $_SESSION['password'];
$token_hash = hash("sha256", $token);

try {
    // Verify token
    $stmt = $conn->prepare("SELECT * FROM users WHERE reset_token_hash = :token_hash");
    $stmt->execute(['token_hash' => $token_hash]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user === false) {
        die("Token not found.");
    }

    if (strtotime($user["reset_token_expires_at"]) <= time()) {
        die("Token has expired.");
    }

    // Validate password
    $errors = [];
    if (empty($password) || strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }

    if (!empty($errors)) {
        die(implode("<br>", $errors));
    }

    // Update password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users 
                           SET password = :password, 
                               reset_token_hash = NULL, 
                               reset_token_expires_at = NULL 
                           WHERE userId = :userId");
    $stmt->execute([
        'password' => $password_hash,
        'userId' => $user['userId']
    ]);

    if ($stmt->rowCount() > 0) {
        echo "Password updated. You can now login.";
        // header("Location: ../pages/signIn.php"); 
        header("Location: ../root/index.php"); 
        unset($_SESSION['reset_token']);
        unset($_SESSION['password']);
    } else {
        echo "Failed to update password.";
    }
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
}
?>