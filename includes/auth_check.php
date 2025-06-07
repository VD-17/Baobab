<?php
    session_start();
    require_once 'includes/db_connection.php';

    if (!isset($_SESSION['userId'])) {
        $token = $_COOKIE['remember_me_token'] ?? '';
        if ($token) {
            $stmt = $conn->prepare("SELECT id FROM users WHERE remember_me_token = :token AND remember_me = 1");
            $stmt->execute(['token' => $token]);
            $user = $stmt->fetch();
            if ($user) {
                $_SESSION['user_id'] = $user['id'];
            } 
            else {
                setcookie('remember_me_token', '', time() - 3600, '/'); // Invalidate cookie
            }
        }
    }

    // if (isset($_SESSION['userId'])) {
    //     header('Location: header.php');
    //     exit;
    // }
    // else {
    //     header('Location: signIn.php');
    //     exit;
    // }

?>