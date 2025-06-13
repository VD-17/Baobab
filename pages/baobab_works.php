<!DOCTYPE html>
<html lang="en">
<?php
$pageTitle = "How Baobab Works - Baobab";
include('../includes/head.php');
?>
<head>
    <link rel="stylesheet" href="../assets/css/sidenav.css">
    <link rel="stylesheet" href="../assets/css/baobab_works.css">
</head>
<body>
    <?php include('../includes/header.php'); ?>

    <section id="page-header">
        <h2>How Baobab Works</h2>
    </section>

    <section id="main">
        <div>
            <div>Step 1</div>
            <h3><i class="fa-solid fa-circle-user"></i>Create an Account</h3>
            <p>Sign up for free in just a few minutes. Complete your profile with your details and preferences to start buying and selling items across South Africa.</p>
            <button><a href="../pages/signUp.php">Register Now <i class="fa-solid fa-arrow-up"></i></a></button>
        </div>

        <div>
            <div>Step 2</div>
            <h3><i class="fa-solid fa-shop"></i>Browse Products</h3>
            <p>Explore our wide range of products and product categories.</p>
            <button><a href="../pages/shop.php">Explore Now <i class="fa-solid fa-arrow-up"></i></a></button>
        </div>

        <div>
            <div>Step 3</div>
            <h3><i class="fa-solid fa-list-check"></i>List Your Items</h3>
            <p>Take clear photos of your items, write detailed descriptions, set your price, and choose the right category. The better your listing, the faster it will sell.</p>
            <button><a href="../pages/contact.php">See Popular Categories <i class="fa-solid fa-arrow-up"></i></a></button>
        </div>

        <div>
            <div>Step 4</div>
            <h3><i class="fa-solid fa-comments"></i>>Connect with Buyers/Sellers</h3>
            <p>Chat securely with interested buyers or sellers through our messaging system. Arrange details for viewing, payment, and pickup or delivery.</p>
            <button><a href="../pages/safety.php">View Safety Guidelines <i class="fa-solid fa-arrow-up"></i></a></button>
        </div>

        <div>
            <div>Step 5</div>
            <h3><i class="fa-solid fa-credit-card"></i>Complete the transaction</h3>
            <p>Meet in a safe location to exchange the item or use our secure payment system for a contactless transaction. Rate your experience to build trust in our community.</p>
            <button><a href="../pages/community.php">Read Community Guidelines <i class="fa-solid fa-arrow-up"></i></a></button>
        </div>
    </section>

    <section id="more">
        <div id="info">
            <h3>Ready to Start <span>BUYING</span> and <span>SELLING</span> on Baobab</h3>
            <h5>Join thousands of South Africans who are already using our platform to buy and sell items safely and easily.</h5>
            <div id="btn">
                <button class="normal" onclick="window.location.href='../pages/signUp.php'">Create your Account</button>
                <button class="white" onclick="window.location.href='../pages/category.php'">Browse Categories</button>
            </div>
        </div>
    </section>

    <?php include('../includes/footer.php'); ?>
</body>
</html>