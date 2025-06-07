<!DOCTYPE html>
<html lang="en">
<?php
$pageTitle = "Contact - Baobab";
include('../includes/head.php');
?>
<head>
    <link rel="stylesheet" href="../assets/css/faq.css">
</head>
<body>

    <?php include('../includes/header.php'); ?>

    <section id="page-header">
        <h2>#FAQs</h2>
        <p>Clear Your Doubts</p>
    </section>

    <section id="boxes">
        <div class="topic active">
            <div class="icon"></div>
            <h4><a href="../pages/faq.php">General</a></h4>
        </div>
        <div class="topic">
            <div class="icon"></div>
            <h4><a href="../pages/account-faq.php">Account</a></h4>
        </div>
        <div class="topic">
            <div class="icon"></div>
            <h4><a href="../pages/selling-faq.php">Selling/Listing</a></h4>
        </div>
        <div class="topic">
            <div class="icon"></div>
            <h4><a href="../pages/buying-faq.php">Buying</a></h4>
        </div>
    </section>

    <section id="questions">
        <div id="faq">
            <button class="accordion">What is Baobab?</button>
            <div class="panel">
                <p>Baobab is a consumer-to-consumer (C2C) e-commerce platform that allows users to buy and sell products directly with each other. Whether you're looking to sell pre-owned items or shop for great deals, our platform connects you with people in your area and beyond.</p>
            </div>
            <button class="accordion">What is a C2C e-commerce website?</button>
            <div class="panel">
                <p>C2C e-commerce refers to a platform where consumers can sell products or services directly to other consumers. This type of marketplace allows individuals to exchange goods without the involvement of a traditional retailer.</p>
            </div>
            <button class="accordion">What types of items can I buy/sell here?</button>
            <div class="panel">
                <p>Our platform supports both new and used items across various categories. From everyday household items to specialized goods, you can find and sell almost anything.</p>
            </div>
            <button class="accordion">How does your C2C platform work? </button>
            <div class="panel">
                <p>Sellers list products with descriptions, images, and prices. Buyers browse listings, contact sellers, and make purchases. Transactions can be facilitated via secure payment methods or in-person exchanges</p>
            </div>
            <button class="accordion">Is there a fee for selling items on Baobab?</button>
            <div class="panel">
                <p>Listing products on Baobab is free.</p>
            </div>
            <button class="accordion">What measures are in place to ensure safety and security on the platform? </button>
            <div class="panel">
                <p>Platforms often implement measures like user verification, secure payment processing, dispute resolution systems, and community guidelines to promote a safe and secure environment. However, users should still exercise caution and be aware of potential risks.</p>
            </div>
            <button class="accordion">How do I contact customer support? </button>
            <div class="panel">
                <p>You can reach out to our support team via the “Contact Us” page or by clicking on the help icon in your account dashboard. We offer live chat, email support, and a knowledge base with helpful articles.</p>
            </div>
            <button class="accordion">How do I find items I want to buy?  </button>
            <div class="panel">
                <p>Our platforms offer a search bar where you can type in keywords related to the item you're looking for. You can also go to shop, browse through categories or use filters to narrow down your search based on price, condition, location, and other criteria.</p>
            </div>
        </div>
    </section>

    <section id="problem">
        <p>Can't find an answer to your question?</p>
        <button class="normal"><a href="../pages/contact.php">Submit A Requests</a></button>
    </section>

    <?php include('../includes/footer.php'); ?>

    <script src="../assets/js/script.js"></script>

</body>
</html>