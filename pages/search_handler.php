<?php
session_start();
require_once '../includes/db_connection.php';

if (!isset($_GET['query']) || empty(trim($_GET['query']))) {
    header('Location: ../root/index.php');
    exit();
}

$query = trim($_GET['query']);

try {
    // First, check if the search query matches any users
    $userStmt = $conn->prepare("
        SELECT userId, firstname, lastname, city, profile_picture 
        FROM users 
        WHERE CONCAT(firstname, ' ', lastname) LIKE :query 
        OR firstname LIKE :query 
        OR lastname LIKE :query
        LIMIT 5
    ");
    $userStmt->bindValue(':query', '%' . $query . '%', PDO::PARAM_STR);
    $userStmt->execute();
    $users = $userStmt->fetchAll(PDO::FETCH_ASSOC);

    // Then check for products
    $productStmt = $conn->prepare("
        SELECT COUNT(*) as product_count 
        FROM products 
        WHERE productName LIKE :query 
        OR description LIKE :query 
        OR productCategory LIKE :query
    ");
    $productStmt->bindValue(':query', '%' . $query . '%', PDO::PARAM_STR);
    $productStmt->execute();
    $productCount = $productStmt->fetchColumn();

    // Decide where to redirect based on results
    if (count($users) > 0 && $productCount == 0) {
        // Only users found, redirect to first user's profile
        header('Location: ../pages/profile.php?userId=' . $users[0]['userId'] . '&search=' . urlencode($query));
        exit();
    } elseif (count($users) == 1 && $productCount > 0) {
        // One user and products found, show search results page
        header('Location: ../pages/search_results.php?query=' . urlencode($query));
        exit();
    } elseif (count($users) > 1) {
        // Multiple users found, show search results page
        header('Location: ../pages/search_results.php?query=' . urlencode($query));
        exit();
    } else {
        // Only products or no results, redirect to product search
        header('Location: ../pages/search.php?query=' . urlencode($query));
        exit();
    }

} catch (PDOException $e) {
    $_SESSION['errors'] = ["Search error: " . $e->getMessage()];
    header('Location: ../root/index.php');
    exit();
}
?>