<!DOCTYPE html>
<html lang="en">
<?php  
    $pageTitle = "My Listings"; 
    include('../includes/head.php');  
?> 
<head>
    <!-- <link rel="stylesheet" href="../assets/css/myListing.css">  -->
</head>
<body>
    <div id="success">
        <h2>Payment Cencelled -> <a href="../pages/payment.php">Try Again</a></h2>
    </div>
</body>
</html>

<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<?php  
    $pageTitle = "Payment Cancelled"; 
    include('../includes/head.php');  
?> 
<head>
    <!-- <link rel="stylesheet" href="../assets/css/myListing.css">  -->
</head>
<body>
    <div style="text-align: center; padding: 50px;">
        <h1>Payment Cancelled</h1>
        <p>Your payment was cancelled. Your cart items are still saved.</p>
        <a href="../pages/cart.php">Return to Cart</a>
    </div>
</body>
</html>