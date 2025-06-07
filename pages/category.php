<?php
    session_start();
    require_once '../includes/db_connection.php';
?>

<!DOCTYPE html>
<html lang="en">
<?php
$pageTitle = "Category";
include('../includes/head.php');
?>
<head>
    <link rel="stylesheet" href="../assets/css/category.css">
</head>
<body id="cat">
    <?php include('../includes/header.php'); ?>

    <section id="page-header">
        <h2>#Categories</h2>
        <p>Explore Our Wide Range of Product Category!</p>
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

    <section id="categories">
        <div class="category">
            <a href="../pages/electronics.php">
                <div id="gif1"></div>
                <h3>Electronics</h3>
            </a>
        </div>
        <div class="category">
            <a href="../pages/vehicle.php">
                <div id="gif2"></div>
                <h3>Vehicles</h3>
            </a>
        </div>
        <div class="category">
            <a href="../pages/home.php">
                <div id="gif3"></div>
                <h3>Home</h3>
            </a>
        </div>
        <div class="category">
            <a href="../pages/fashion.php">
                <div id="gif4"></div>
                <h3>Fashion</h3>
            </a>
        </div>
        <div class="category">
            <a href="../pages/furniture.php">
                <div id="gif5"></div>
                <h3>Furniture</h3>
            </a>
        </div>
        <div class="category">
            <a href="../pages/toys-games.php">
                <div id="gif6"></div>
                <h3>Toys & Games</h3>
            </a>
        </div>
        <div class="category">
            <a href="../pages/outdoor-sports.php">
                <div id="gif7"></div>
                <h3>Outdoor & Sports</h3>
            </a>
        </div>
        <div class="category">
            <a href="../pages/antiques-collectibles.php">
                <div id="gif8"></div>
                <h3>Antiques & Collectibles</h3>
            </a>
        </div>
        <div class="category">
            <a href="../pages/books.php">
                <div id="gif9"></div>
                <h3>Books</h3>
            </a>
        </div>
    </section>
</body>
</html>