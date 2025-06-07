<?php
session_start();
require 'db_connection.php';

if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("UPDATE users SET remember_token = NULL, remember_token_expires_at = NULL WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
}

session_destroy();

// Clear cookie
setcookie("remember_me", "", time() - 3600, "/");

header("Location: login.php");
exit;
?>
