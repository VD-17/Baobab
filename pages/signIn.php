<?php 
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<?php 
    $pageTitle = "Login";
    include('../includes/head.php'); 
?>
<head>
    <link rel="stylesheet" href="../assets/css/registration.css">
    <style>
        .error-messages {
            background-color: #f8d7da;
            color: #721c24;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #f5c6cb;
            border-radius: 4px;
        }
        .error-messages p {
            margin: 5px 0;
        }
    </style>
</head>
<body class="sign">
    <section id="signUp">
        <div id="image-content">
            <div id="toBaobab">
                <a href="../index.php"><img src="../assets/images/Logo/Baobab_favicon.png" alt="Baobab_logo"></a>
                <a href="../index.php">Back to website <i class="fa-solid fa-arrow-right"></i></a>
            </div>
            <div id="welcome">
                <h1>WELCOME TO <span>BAOBAB</span></h1>
                <p>To Keep Connected With Us Login With Your Info</p>
            </div>
            <div id="welcome-img">
                <img src="../assets/images/Welcome/Welcome.png" alt="Welcome image">
            </div>
        </div>
        <div id="signUp-form">
            <div id="account-heading">
                <h1>Sign-In</h1>
                <h6>Don't have an account? <a href="../pages/signUp.php">Create Account</a></h6>
            </div>
            
            <!-- Display errors if any -->
            <?php if (isset($_SESSION['errors']) && !empty($_SESSION['errors'])): ?>
                <div class="error-messages">
                    <?php foreach ($_SESSION['errors'] as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
                <?php unset($_SESSION['errors']); ?>
            <?php endif; ?>

            <form id="loginform" action="../api/authentication/login.php" method="post">
                <div class="form">
                    <i class="fa-solid fa-envelope"></i>
                    <input type="email" name="email" id="email" placeholder="Email" required>
                </div>
                <div class="error" id="email-error" aria-live="polite"></div>
                <div class="form">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" name="password" id="password" placeholder="Password" required>
                </div>
                <div class="error" id="password-error" aria-live="polite"></div>
                <div id="error-container" class="error" style="display: none;"></div>
                <div class="form-row">
                    <a href="../pages/forgotPassword.php">Forgot Password? <i class="fa-solid fa-arrow-right"></i></a>
                </div>
                <div id="submit">
                    <button type="submit" class="normal">Login</button>
                </div>
            </form>
        </div>
    </section>

    <script src="../assets/js/loginValidation.js"></script> 
</body>
</html>