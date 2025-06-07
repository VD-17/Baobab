<?php 
    session_start();
    
?>

<!DOCTYPE html>
<html lang="en">
<?php 
    $pageTitle = "Verify";
    include('../includes/head.php'); 
?>
<head>
    <link rel="stylesheet" href="../assets/css/registration.css">
</head>
<body>
    <section id="signUp">
        <div id="image-content">
            <div id="toBaobab">
                <a href=""><img src="../assets/images/Logo/Baobab_favicon.png" alt="Baobab_logo"></a>
                <a href="">Back to website <i class="fa-solid fa-arrow-right"></i></a>
            </div>
            <div id="welcome">
                <h1>WELCOME TO <span>BAOBAB</span></h1>
                <p>Enter Your Details And Start Your Journey With Us</p>
            </div>
            <div id="welcome-img">
                <img src="../assets/images/Welcome/Welcome.png" alt="Welcome image">
            </div>
        </div>
        <div id="signUp-form">
            <div id="account-heading">
                <h1>Verification</h1>
                <h3>Verify your Phone number</h3>
            </div>
            <form action="verification.php" method="post" enctype="multipart/form-data">
                <div class="form">
                    <button class="white">Send code</button> 
                </div>
                <div class="form form-phone-verify">
                    <i class="fas fa-sms"></i>
                    <input type="text" name="verificationCode" placeholder="Enter Verification Code">
                    <button id="verifyCodeBtn" class="normal">Verify Code</button>
                </div>
            </form>
            <p id="error" class="error"></p>
        </div>
    </section>
    <script src="../assets/js/userInputValidation.js"></script>
</body>
</html>