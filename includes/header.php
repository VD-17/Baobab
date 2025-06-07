<?php
    if (!isset($_SESSION)) {
        session_start();
    }
    require_once(__DIR__ . "/db_connection.php");

    // Check if user is logged in and include appropriate header
    if (isset($_SESSION['userId']) && $_SESSION['userId'] > 0) {
        include('userHeader.php');
    } else {
        include('guestHeader.php');
    }
?>