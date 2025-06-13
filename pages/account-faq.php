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
        <div class="topic active">
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
            <button class="accordion">How do I create an account?</button>
            <div class="panel">
                <p>Click on the "Sign Up" button at the top-right corner of the homepage. You can register using your email address or social media account and follow the prompts to complete your profile. fill in your details.</p>
            </div>
            <button class="accordion">Is my personal information secure?</button>
            <div class="panel">
                <p>Yes. We use industry-standard encryption and security protocols to protect your data. .</p>
            </div>
            <button class="accordion">Can I change my account details later?</button>
            <div class="panel">
                <p>Yes! Log into your account, go to Dashboard, and edit your personal information as needed. .</p>
            </div>
            <button class="accordion">How can I delete my account? </button>
            <div class="panel">
                <p>Go to Account Settings and select "Delete Account," or contact customer support for assistance.</p>
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