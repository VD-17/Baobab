<!-- <?Php
    session_start();
    require_once '../../includes/db_connection.php';
    require_once '../../includes/functions.php';

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        
        try {
            // Get user data including is_admin field
            $sql = "SELECT userId, firstname, lastname, email, password, is_admin FROM users WHERE email = :email";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();
            
            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Verify password
                if (password_verify($password, $user['password'])) {
                    
                    // Set session variables
                    $_SESSION['userId'] = $user['userId'];
                    $_SESSION['email'] = $user['email'];
                    $_SESSION['isAdmin'] = $user['is_admin']; 
                    $_SESSION['firstname'] = $user['firstname'];
                    $_SESSION['lastname'] = $user['lastname'];
                    
                    // Redirect based on admin status
                    if ($user['is_admin'] == 1) {
                        header("Location: ../../pages/adminDashboard.php");
                    } else {
                        header("Location: ../../index.php");
                    }
                    exit();
                    
                } else {
                    $_SESSION['errors'] = ["Invalid email or password."];
                    header("Location: ../../pages/signIn.php");
                    exit();
                }
            } else {
                $_SESSION['errors'] = ["Invalid email or password."];
                header("Location: ../../pages/signIn.php");
                exit();
            }
            
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            $_SESSION['errors'] = ["An error occurred during login. Please try again."];
            header("Location: ../../pages/signIn.php");
            exit();
        }
    }

    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // $rememberMe = $_SESSION['rememberMe'] ?? 0;
    // $rememberMeToken = $rememberMe ? bin2hex(random_bytes(32)) : null;

    $errors = [];

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format.';
        exit;
    }

    if (empty($password) || strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
        exit;
    }

    try {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user || !password_verify($password, $user['password'])) {
            echo json_encode(['success' => false, 'message' => 'Incorrect password.']);
            exit;
        }

        $_SESSION['userId'] = $user['userId'];
        $_SESSION['firstname'] = $user['firstname'];
        $_SESSION['lastname'] = $user['lastname'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['logged_in'] = true;

        header('Location: ../../index.php'); 
        exit();

        // Handle Remember Me
        // if ($rememberMe && $rememberMeToken) {
        //     $updateStmt = $conn->prepare("UPDATE users SET rememberMe = :remember_me, rememberMeToken = :remember_me_token WHERE userId = :userId");
        //     $updateStmt->execute([
        //         'remember_me' => 1,
        //         'token' => $token,
        //         'id' => $user['id']
        //     ]);
        //     setcookie('remember_me_token', $rememberMeToken, time() + (30 * 24 * 60 *60), '/', '', true, true);
        // } else {
        //     // Clear any existing token if Remember Me is not checked
        //     $updateStmt = $conn->prepare("UPDATE users SET rememberMe = :remember_me, rememberMeToken = :remember_me_token WHERE userId = :userId");
        //     $updateStmt->execute([
        //         'remember_me' => 0,
        //         'remember_me_token' => null,
        //         'userId' => $user['userId']
        //     ]);
        //     setcookie('remember_me_token', '', time() - 3600, '/'); // Clear cookie
        // }

        // $stmt = $conn->prepare("INSERT INTO users 
        //     (remember_me, remember_me_token) 
        //     VALUES (?, ?)");

        // $stmt->execute([
        //     $rememberMe,
        //     $rememberMeToken
        // ]);

        // if ($rememberMe && $rememberMeToken) {
        //     setcookie('remember_me_token', $rememberMeToken, time() + (30 * 24 * 60 *60), '/', '', true, true);
        // }

        // session_unset();
        // session_destroy();
        // header('Location: ../../index.php'); 
    }
    catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => `Login failed. Please try again later.`]);
    }
?> -->

<?php
session_start();
require_once '../../includes/db_connection.php';
require_once '../../includes/functions.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = sanitizeInput($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $errors = [];

    // Validation
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format.';
    }

    if (empty($password) || strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }

    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header("Location: ../../pages/signIn.php");
        exit();
    }

    try {
        // Get user data including is_admin field
        $sql = "SELECT userId, firstname, lastname, email, password, is_admin FROM users WHERE email = :email";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();
        
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Verify password
            if (password_verify($password, $user['password'])) {
                
                // Set session variables
                $_SESSION['userId'] = $user['userId'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['isAdmin'] = $user['is_admin']; 
                $_SESSION['firstname'] = $user['firstname'];
                $_SESSION['lastname'] = $user['lastname'];
                $_SESSION['logged_in'] = true;
                
                // Redirect based on admin status
                if ($user['is_admin'] == 1) {
                    header("Location: ../../pages/adminDashboard.php");
                } else {
                    header("Location: ../../index.php");
                }
                exit();
                
            } else {
                $_SESSION['errors'] = ["Invalid email or password."];
                header("Location: ../../pages/signIn.php");
                exit();
            }
        } else {
            $_SESSION['errors'] = ["Invalid email or password."];
            header("Location: ../../pages/signIn.php");
            exit();
        }
        
    } catch (PDOException $e) {
        error_log("Login error: " . $e->getMessage());
        $_SESSION['errors'] = ["An error occurred during login. Please try again."];
        header("Location: ../../pages/signIn.php");
        exit();
    }
}
?>