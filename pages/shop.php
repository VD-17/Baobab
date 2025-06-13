<?php
session_start();
require_once '../includes/db_connection.php';

// Pagination settings
$productsPerPage = 16;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $productsPerPage;

try {
    $countStmt = $conn->prepare("SELECT COUNT(*) FROM products");
    $countStmt->execute();
    $totalProducts = $countStmt->fetchColumn();
    $totalPages = ceil($totalProducts / $productsPerPage);

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
$pageTitle = "Shop Products";
include('../includes/head.php');
?>
<head>
    <link rel="stylesheet" href="../assets/css/shop.css">
</head>
<body>
    <?php include('../includes/header.php'); ?>

    <?php if (isset($_SESSION['cart_success'])): ?>
        <div id="cart-notification" class="notification success-notification show">
            <div class="notification-content">
                <i class="fa-solid fa-check-circle"></i>
                <span>Product added to cart successfully!</span>
            </div>
            <button class="notification-close" onclick="closeNotification()">
                <i class="fa-solid fa-times"></i>
            </button>
        </div>
        <?php unset($_SESSION['cart_success']); ?>
    <?php endif; ?>

    <section id="page-header">
        <h2>#Shop</h2>
        <p>Shop Our Exclusive Products!</p>
    </section>

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

    <?php include('../pages/product.php'); ?> 

    <section id="pagination" class="section-p1">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?php echo $i; ?>" <?php echo $i === $page ? 'class="active"' : ''; ?>><?php echo $i; ?></a>
        <?php endfor; ?>
        <?php if ($page < $totalPages): ?>
            <a href="?page=<?php echo $page + 1; ?>"><i class="fa-solid fa-arrow-right"></i></a>
        <?php endif; ?>
    </section>

    <?php include('../includes/footer.php'); ?>

    <script src="../assets/js/shop.js"></script>
</body>
</html>