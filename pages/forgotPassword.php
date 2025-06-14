<?php 
    session_start();

    $error = '';

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
        
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['email'] = $email;
            header("Location: ../api/authentication/sendPasswordEmail.php");
            exit();
        } else {
            $error = "Please enter a valid email address.";
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<?php 
    $pageTitle = "Reset";
    include('../includes/head.php'); 
?>
<head>
    <link rel="stylesheet" href="../assets/css/registration.css">
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
                <p>Reset Your Password</p>
            </div>
            <div id="welcome-img">
                <img src="../assets/images/Welcome/Welcome.png" alt="Welcome image">
            </div>
        </div>
        <div id="signUp-form">
            <div id="account-heading">
                <h1>Forgot Password?</h1>
            </div>
            <?php if ($error): ?>
                <div class="error" id="email-error" aria-live="polite"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <form id="forgotPasswordform" action="../pages/forgotPassword.php" method="post">
                <div class="form">
                    <i class="fa-solid fa-envelope"></i>
                    <input type="email" name="email" id="email" placeholder="Email" required>
                </div>
                <div class="error" id="email-error" aria-live="polite"></div>
                <div id="submit">
                    <button type="submit" class="normal">Send</button>
                </div>
            </form>
        </div>
    </section>

    <script src="../assets/js/userInputValidation.js"></script>
</body>
</html>