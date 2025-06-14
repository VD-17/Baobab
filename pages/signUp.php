<?php 
    session_start();
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $_SESSION['firstname'] = $_POST['firstname'];
        $_SESSION['lastname'] = $_POST['lastname'];
        $_SESSION['email'] = $_POST['email'];
        $_SESSION['password'] = $_POST['password'];
        $_SESSION['phoneNumber'] = $_POST['phoneNumber'];
        header("Location: ../pages/completeSignUp.php");
        exit();
    }
?>

<!DOCTYPE html>
<html lang="en">
<?php 
    $pageTitle = "Sign Up";
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
                <p>Enter Your Details And Start Your Journey With Us</p>
            </div>
            <div id="welcome-img">
                <img src="../assets/images/Welcome/Welcome.png" alt="Welcome image">
            </div>
        </div>
        <div id="signUp-form">
            <div id="account-heading">
                <h1>Create an account</h1>
                <h6>Already have an account? <a href="../pages/signIn.php">Log In</a></h6>
            </div>
            <form id="signUpform" action="signUp.php" method="post" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form">
                        <i class="fa-solid fa-user"></i>
                        <input type="text" name="firstname" id="firstname" placeholder="First Name" required>
                    </div>
                    <div class="form">
                        <i class="fa-solid fa-user"></i>
                        <input type="text" name="lastname" id="lastname" placeholder="Last Name" required>
                    </div>
                </div>
                <div class="error" id="firstname-error" aria-live="polite"></div>
                <div class="error" id="lastname-error" aria-live="polite"></div>
                <div class="form">
                    <i class="fa-solid fa-envelope"></i>
                    <input type="email" name="email" id="email" placeholder="Email" required>
                </div>
                <div class="error" id="email-error" aria-live="polite"></div>
                <div class="form">
                    <i class="fas fa-phone"></i>
                    <input type="text" name="phoneNumber" id="phoneNumber" placeholder="Phone Number (e.g., +27123456789)" required>
                </div>
                <div class="error" id="phoneNumber-error" aria-live="polite"></div>
                <div class="form">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" name="password" id="password" placeholder="Password" required>
                </div>
                <div class="error" id="password-error" aria-live="polite"></div>
                <div id="error-container" class="error" style="display: none;"></div>
                <div class="check">
                    <label>
                        <input type="checkbox" name="terms" id="terms" required>
                        I agree to the <a href="../pages/terms.php">Terms & Conditions</a>
                    </label>
                </div>
                <div id="submit">
                    <button type="submit" class="normal">Next</button>
                </div>
            </form>
        </div>
    </section>

    <script src="../assets/js/userInputValidation.js"></script>
</body>
</html>