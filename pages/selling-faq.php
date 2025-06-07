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
        <div class="topic">
            <div class="icon"></div>
            <h4><a href="../pages/faq.php">General</a></h4>
        </div>
        <div class="topic">
            <div class="icon"></div>
            <h4><a href="../pages/account-faq.php">Account</a></h4>
        </div>
        <div class="topic active">
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
            <button class="accordion">How do I start selling?</button>
            <div class="panel">
                <p>Log into your account, click “Sell,” fill in the product details, upload photos, set a price, and publish your listing. Find more information <a href="">here</a>.</p>
            </div>
            <button class="accordion">How do I get paid after a sale?</button>
            <div class="panel">
                <p>After an order has been placed. You will get paid within 3-5 working days. Ensure your payment details are updated in your account settings.</p>
            </div>
            <button class="accordion">How do I ship my item?</button>
            <div class="panel">
                <p>You can contact the buyer and decide. You are responsible for packaging and shipping the item promptly.</p>
            </div>
            <button class="accordion">How do I set the price for my item?  </button>
            <div class="panel">
                <p>You can set the price based on the item's condition, market value, and any comparable items listed on the platform. You can also research similar items sold recently to get an idea of a fair price. Consider the condition of your item, its original price, its current market value, and what similar items are selling for on the platform. Be realistic and competitive.</p>
            </div>
            <button class="accordion">How should I describe my items?   </button>
            <div class="panel">
                <p>Provide clear, accurate, and detailed descriptions of your items, including their condition, size, color, brand, and any relevant features or flaws.</p>
            </div>
            <button class="accordion">What happens if my item doesn't sell? </button>
            <div class="panel">
                <p>You can delete your product anytime you want. You may also consider adjusting the price or improving the item description and photos to make it more appealing.</p>
            </div>
            <button class="accordion">How can I increase my sales? </button>
            <div class="panel">
                <p> Use high-quality images and detailed descriptions. Offer competitive pricing. Respond quickly to buyer inquiries. Share listings on social media.</p>
            </div>
            <button class="accordion">Can I edit my listings after posting? </button>
            <div class="panel">
                <p>Yes, you can edit your listing by going to dashboard, My Listing and then u can edit the product.</p>
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