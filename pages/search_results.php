<?php
session_start();
require_once '../includes/db_connection.php';

$searchQuery = isset($_GET['query']) ? trim($_GET['query']) : '';

if (empty($searchQuery)) {
    header('Location: ../index.php');
    exit();
}

try {
    // Search for users
    $userStmt = $conn->prepare("
        SELECT userId, firstname, lastname, city, profile_picture 
        FROM users 
        WHERE CONCAT(firstname, ' ', lastname) LIKE :query 
        OR firstname LIKE :query 
        OR lastname LIKE :query
        LIMIT 10
    ");
    $userStmt->bindValue(':query', '%' . $searchQuery . '%', PDO::PARAM_STR);
    $userStmt->execute();
    $users = $userStmt->fetchAll(PDO::FETCH_ASSOC);

    // Search for products (limited for preview)
    $productStmt = $conn->prepare("
        SELECT p.id, p.productName AS name, p.price, p.productCategory AS category, 
            p.productPicture AS image_path, u.city AS location
        FROM products p
        LEFT JOIN users u ON p.userId = u.userId
        WHERE p.productName LIKE :query 
        OR p.description LIKE :query 
        OR p.productCategory LIKE :query
        LIMIT 8
    ");
    $productStmt->bindValue(':query', '%' . $searchQuery . '%', PDO::PARAM_STR);
    $productStmt->execute();
    $products = $productStmt->fetchAll(PDO::FETCH_ASSOC);

    // Count total products for "View All" link
    $countStmt = $conn->prepare("
        SELECT COUNT(*) FROM products 
        WHERE productName LIKE :query 
        OR description LIKE :query 
        OR productCategory LIKE :query
    ");
    $countStmt->bindValue(':query', '%' . $searchQuery . '%', PDO::PARAM_STR);
    $countStmt->execute();
    $totalProducts = $countStmt->fetchColumn();

} catch (PDOException $e) {
    $_SESSION['errors'] = ["Search error: " . $e->getMessage()];
    $users = [];
    $products = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<?php
$pageTitle = "Search Results";
include('../includes/head.php');
?>
<head>
    <link rel="stylesheet" href="../assets/css/shop.css">
    <style>
        .search-section { margin: 20px 0; }
        .user-result {
            display: flex;
            align-items: center;
            padding: 10px;
            border: 1px solid #ddd;
            margin: 10px 0;
            border-radius: 5px;
        }
        .user-result img {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            margin-right: 15px;
        }
        .view-all-btn {
            background: #007bff;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 5px;
            display: inline-block;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <?php include('../includes/header.php'); ?>

    <h2>Search Results for "<?php echo htmlspecialchars($searchQuery); ?>"</h2>

    <?php if (!empty($users)): ?>
        <div class="search-section">
            <h3>Users (<?php echo count($users); ?> found)</h3>
            <?php foreach ($users as $user): ?>
                <div class="user-result">
                    <img src="<?php echo !empty($user['profile_picture']) ? '../' . $user['profile_picture'] : '../assets/images/Welcome/default_profile.jpg'; ?>" 
                         alt="Profile Picture">
                    <div>
                        <h4><?php echo htmlspecialchars($user['firstname'] . ' ' . $user['lastname']); ?></h4>
                        <p><?php echo htmlspecialchars($user['city'] ?? 'Location not specified'); ?></p>
                        <a href="../pages/profile.php?userId=<?php echo $user['userId']; ?>">View Profile</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($products)): ?>
        <div class="search-section">
            <h3>Products (<?php echo $totalProducts; ?> found)</h3>
            <div class="product-grid">
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <img src="<?php echo '../' . $product['image_path']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <h4><?php echo htmlspecialchars($product['name']); ?></h4>
                        <p>R<?php echo number_format($product['price'], 2); ?></p>
                        <p><?php echo htmlspecialchars($product['location'] ?? 'Location not specified'); ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php if ($totalProducts > 8): ?>
                <a href="../pages/search.php?query=<?php echo urlencode($searchQuery); ?>" class="view-all-btn">
                    View All <?php echo $totalProducts; ?> Products
                </a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if (empty($users) && empty($products)): ?>
        <div class="search-section">
            <p>No results found for "<?php echo htmlspecialchars($searchQuery); ?>"</p>
            <a href="../index.php">Browse all products</a>
        </div>
    <?php endif; ?>

    <?php include('../includes/footer.php'); ?>

</body>
</html>