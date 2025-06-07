<?php
    define('BASE_PATH', dirname(__DIR__));

    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "Baobab";

    try {
        $conn = new PDO("mysql:host=$servername; dbname=$dbname", $username, $password);
        $conn -> setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }
    catch(PDOException $e) {
        error_log("Database Connection failed: ". $e->getMessage());
        die("Sorry, we are experiencing technical difficulties. Please try again later.");
    }
?>