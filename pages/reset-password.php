<?php 
    session_start();
    require_once '../../includes/db_connection.php';

    $token = isset($_GET['token']) ? $_GET['token'] : '';
    if (empty($token)) {
        die("No token provided.");
    }

    $token_hash = hash("sha256", $token);

    try {
        $stmt = $conn->prepare("SELECT * FROM users WHERE reset_token_hash = :token_hash");
        $stmt->execute(['token_hash' => $token_hash]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user === false) {
            die("Token not found.");
        }

        if (strtotime($user["reset_token_expires_at"]) <= time()) {
            die("Token has expired.");
        }

        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $_SESSION['password'] = $_POST['password'];
            $_SESSION['reset_token'] = $token;
            header("Location: ../api/authentication/processResetPassword.php");
            exit();
        }
    } catch (PDOException $e) {
        echo "Database error: " . $e->getMessage();
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
                <h1>Reset Password</h1>
            </div>
            <form id="forgotPasswordform" action="../pages/reset-password.php" method="post">
                <div class="form">
                    <input type="hidden" name="token" id="token">
                </div>
                <div class="form">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" name="password" id="password" placeholder="Password" required>
                </div>
                <div class="error" id="password-error" aria-live="polite"></div>
                <div id="submit">
                    <button type="submit" class="normal">Send</button>
                </div>
            </form>
        </div>
    </section>

    <script src="../assets/js/userInputValidation.js"></script>
</body>
</html>