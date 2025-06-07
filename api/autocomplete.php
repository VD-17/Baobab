<?php
header('Content-Type: application/json');
require_once '../includes/db_connection.php';

if (!isset($_GET['query']) || strlen($_GET['query']) < 2) {
    echo json_encode([]);
    exit();
}

$query = trim($_GET['query']);

try {
    // Search products, users, and categories
    $stmt = $conn->prepare("
        (SELECT DISTINCT productName as suggestion, 'product' as type, id 
         FROM products 
         WHERE productName LIKE :query 
         LIMIT 5)
        UNION
        (SELECT DISTINCT productCategory as suggestion, 'category' as type, NULL as id
         FROM products 
         WHERE productCategory LIKE :query 
         LIMIT 3)
        UNION
        (SELECT CONCAT(firstname, ' ', lastname) as suggestion, 'user' as type, userId as id
         FROM users 
         WHERE CONCAT(firstname, ' ', lastname) LIKE :query 
         LIMIT 3)
        ORDER BY suggestion
        LIMIT 10
    ");
    
    $stmt->bindValue(':query', '%' . $query . '%', PDO::PARAM_STR);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($results);
    
} catch (PDOException $e) {
    echo json_encode([]);
}
?>