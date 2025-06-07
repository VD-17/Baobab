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
        <div class="topic">
            <div class="icon"></div>
            <h4><a href="../pages/selling-faq.php">Selling/Listing</a></h4>
        </div>
        <div class="topic active">
            <div class="icon"></div>
            <h4><a href="../pages/buying-faq.php">Buying</a></h4>
        </div>
    </section>

    <section id="questions">
        <div id="faq">
            <button class="accordion">How do I purchase an item?</button>
            <div class="panel">
                <p>Browse or search for items, click on a product you're interested in, and then contact the seller through the built-in messaging system.</p>
            </div>
            <button class="accordion">Can I negotiate the price with a seller?</button>
            <div class="panel">
                <p>Yes, many sellers are open to offers. You can use the messaging feature to propose a different price or ask questions about the product. .</p>
            </div>
            <button class="accordion">What payment methods are supported?</button>
            <div class="panel">
                <p>Baobab uses PayFast as their payment Gateway. PayFast supports various payment method such as credit/debit cards and in some cases Cash on delivery or in-person payments.</p>
            </div>
            <button class="accordion">How do I contact a seller? </button>
            <div class="panel">
                <p>Once you find an item you're interested in, there's a "Contact” Button when you view their product and "Message" button on the profile page. Clicking this will allow you to send a direct message to the seller to ask questions about the item, negotiate the price, or arrange for shipping/pickup.</p>
            </div>
            <button class="accordion">What if I don't receive my item or it's not as described?  </button>
            <div class="panel">
                <p>You should first try to communicate with the seller to resolve the issue. If that doesn't work, you can usually file a dispute through the platform, providing evidence of the problem. The platform will then investigate and try to mediate a solution. Contact the seller first to resolve the issue..</p>
            </div>
            <button class="accordion">Can I cancel my purchase?  </button>
            <div class="panel">
                <p>If your order hasn’t been shipped yet, you can request cancellation through your order details. If already shipped, you may need to wait and return the item upon arrival (if eligible). Contact customer support for assistance. Alternatively, you can contact the seller can cancel meetups</p>
            </div>
            <button class="accordion">Can I return or get a refund? </button>
            <div class="panel">
                <p>Returns depend on the seller’s policy. Always confirm return conditions before purchasing.</p>
            </div>
            <button class="accordion">How do I know if a seller is trustworthy? </button>
            <div class="panel">
                <p>Check the seller's rating, read reviews from previous buyers, and look for verified seller badges. Our platform features robust review systems to help build trust.</p>
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