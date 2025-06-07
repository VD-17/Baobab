<!DOCTYPE html>
<html lang="en">
<?php
$pageTitle = "About - Baobab";
include('../includes/head.php');
?>
<head>
    <link rel="stylesheet" href="../assets/css/about.css">
    <link rel="stylesheet" href="../assets/css/index.css">
</head>
<body>
    <?php include('../includes/header.php'); ?>

    <section id="page-header">
        <h2>#About Us</h2>
        <p>Know More About Baobab</p>
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

    <section class="about1">
        <div class="description">
            <h3><span>Connect</span> with local buyers and sellers on Baobab</h3>
            <h5>Founded in 2025, Baobab aims to empower South Africans by creating a safe, accessible platform where anyone can buy and sell goods easily. </h5>
        </div>
        <div class="image">
            <img src="../assets/images/about/aboutPic1.jpg" alt="">
        </div>
    </section>

    <section class="about2">
        <div class="image">
            <img src="../assets/images/about/aboutPic2.jpg" alt="">
        </div>
        <div class="description">
            <h3>Our <span>Belief</span></h3>
            <h5>We believe in fostering economic opportunities and building stronger local communities through commerce. </h5>
        </div>
    </section>

    <section class="about1">
        <div class="description">
            <h3>Our <span>Story</span></h3>
            <h5>Baobab was created to address the unique needs of South African consumers. We understand the local challenges in e-commerce and have built a platform that reflects our country's diversity, resilience, and entrepreneurial spirit. </h5>
        </div>
        <div class="image">
            <img src="../assets/images/about/aboutPic3.jpg" alt="">
        </div>
    </section>

    <section class="about2">
        <div class="image">
            <img src="../assets/images/about/aboutPic4.jpg" alt="">
        </div>
        <div class="description">
            <h3>Our <span>Values</span></h3>
            <h5>Trust, security, and inclusivity are at the core of everything we do. We strive to make online trading accessible to all South Africans through a user-friendly platform that supports multiple languages and payment methods. </h5>
        </div>
    </section>

    <section class="about1">
        <div class="description">
            <h3><span>Suporting</span> Local Communities</h3>
            <h5>Baobab was created to address the unique needs of South African consumers. We understand the local challenges in e-commerce and have built a platform that reflects our country's diversity, resilience, and entrepreneurial spirit. </h5>
        </div>
        <div class="image">
            <img src="../assets/images/about/aboutPic5.jpg" alt="">
        </div>
    </section>

    <section class="about2">
        <div class="image">
            <img src="../assets/images/about/aboutPic6.jpg" alt="">
        </div>
        <div class="description">
            <h3><span>Safety</span> & <span>Security</span></h3>
            <h5>Your security is our priority. We continually invest in advanced security measures, verification systems, and educational resources to ensure every transaction on Baobab is as safe and secure as possible. </h5>
        </div>
    </section>

    <section id="contact">
        <h5>Contact Us</h5>
        <p><a href="../pages/conversation.php?userId=7; ?>"><i class="fa-solid fa-message"></i> Leave a message <i class="fa-solid fa-arrow-right"></i></a></p>
        <p><a href="../pages/contact.php; ?>"><i class="fa-solid fa-headset"></i> Get Support <i class="fa-solid fa-arrow-right"></i></a></p>
    </section>

    <section id="newsletter" class="section-p1 section-m1">
        <div class="newstext">
            <h4>Sign Up For Newsletters</h4>
            <p>Get E-main updates about out latest shop and <span>special offers</span></p>
        </div>
        <div class="form">
            <input type="text" placeholder="Your email address">
            <button class="normal">Sign Up</button>
        </div>
    </section>

    <?php include('../includes/footer.php'); ?>

</body>
</html>