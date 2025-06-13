<?php
    define('BASE_PATH', dirname(__DIR__));

    $servername = "sql111.infinityfree.com";
    $username = "if0_39226256";
    $password = "mUq9NhRFwDMn";
    $dbname = "if0_39226256_baobab";

    try {
        $conn = new PDO("mysql:host=$servername; dbname=$dbname", $username, $password);
        $conn -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    catch(PDOException $e) {
        error_log("Database Connection failed: ". $e->getMessage());
        die("Sorry, we are experiencing technical difficulties. Please try again later.");
    }
?>