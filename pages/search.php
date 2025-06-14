<?php
session_start();
require_once '../includes/db_connection.php';

// Get search query
$searchQuery = isset($_GET['query']) ? trim($_GET['query']) : '';

// Pagination settings
$productsPerPage = 16;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $productsPerPage;

try { 
    if (!empty($searchQuery)) {
        // Count products matching search
        $countStmt = $conn->prepare("
            SELECT COUNT(*) FROM products 
            WHERE productName LIKE :query 
            OR description LIKE :query 
            OR productCategory LIKE :query
        ");
        $countStmt->bindValue(':query', '%' . $searchQuery . '%', PDO::PARAM_STR);
        $countStmt->execute();
        $totalProducts = $countStmt->fetchColumn();

        // Get products matching search
        $stmt = $conn->prepare("
            SELECT p.id, p.productName AS name, p.price, p.productCategory AS category, 
                p.productPicture AS image_path, p.productVideo AS video_path, 
                u.city AS location,
                (SELECT AVG(r.rating) FROM reviews r WHERE r.productId = p.id) AS average_rating
            FROM products p
            LEFT JOIN users u ON p.userId = u.userId
            WHERE p.productName LIKE :query 
            OR p.description LIKE :query 
            OR p.productCategory LIKE :query
            ORDER BY p.created_at DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindValue(':query', '%' . $searchQuery . '%', PDO::PARAM_STR);
    } else {
        // Show all products if no search query
        $countStmt = $conn->prepare("SELECT COUNT(*) FROM products");
        $countStmt->execute();
        $totalProducts = $countStmt->fetchColumn();

        $stmt = $conn->prepare("
            SELECT p.id, p.productName AS name, p.price, p.productCategory AS category, 
                p.productPicture AS image_path, p.productVideo AS video_path, 
                u.city AS location,
                (SELECT AVG(r.rating) FROM reviews r WHERE r.productId = p.id) AS average_rating
            FROM products p
            LEFT JOIN users u ON p.userId = u.userId
            ORDER BY p.created_at DESC
            LIMIT :limit OFFSET :offset
        ");
    }
    
    $totalPages = ceil($totalProducts / $productsPerPage);
    $stmt->bindValue(':limit', $productsPerPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['errors'] = ["Error fetching products: " . $e->getMessage()];
    $result = [];
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
</head>
<body>
    <?php include('../includes/header.php'); ?>

    <h3 style="padding: 20px; color: #080357">
        Search Results
        <?php if (!empty($searchQuery)): ?>
            for "<?php echo htmlspecialchars($searchQuery); ?>"
        <?php endif; ?>
        (<?php echo $totalProducts; ?> products found)
    </h3>

    <?php if (isset($_SESSION['errors']) && !empty($_SESSION['errors'])): ?>
        <section class="section-p1">
            <div class="error-messages">
                <?php foreach ($_SESSION['errors'] as $error): ?>
                    <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
                <?php unset($_SESSION['errors']); ?>
            </div>
        </section>
    <?php endif; ?>

    <?php if (empty($result)): ?>
        <section class="section-p1">
            <p>No products found matching your search.</p>
            <a href="../index.php">Browse all products</a>
        </section>
    <?php else: ?>
        <?php include('../pages/product.php'); ?> 
    <?php endif; ?>

    <?php if ($totalPages > 1): ?>
        <section id="pagination" class="section-p1">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                <a href="?query=<?php echo urlencode($searchQuery); ?>&page=<?php echo $i; ?>" 
                   <?php echo $i === $page ? 'class="active"' : ''; ?>>
                   <?php echo $i; ?>
                </a>
            <?php endfor; ?>
            <?php if ($page < $totalPages): ?>
                <a href="?query=<?php echo urlencode($searchQuery); ?>&page=<?php echo $page + 1; ?>">
                    <i class="fa-solid fa-arrow-right"></i>
                </a>
            <?php endif; ?>
        </section>
    <?php endif; ?>

    <script src="../assets/js/shop.js"></script>
</body>
</html>