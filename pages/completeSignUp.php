<?php 
    session_start();
    require_once("../includes/db_connection.php");

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $_SESSION['streetAddress'] = $_POST['streetAddress'];
        $_SESSION['suburb'] = $_POST['suburb'];
        $_SESSION['postalCode'] = $_POST['postalCode'];
        $_SESSION['city'] = $_POST['city'];
        $_SESSION['province'] = $_POST['province'];

        if (isset($_FILES['profilePicture']) && $_FILES['profilePicture']['error'] == UPLOAD_ERR_OK) {
            $target_dir = "../uploads/profile_pictures/";
            
            // Create the target directory if it doesn't exist
            if (!file_exists($target_dir)) {
                if (!mkdir($target_dir, 0755, true)) {
                    $_SESSION['upload_error'] = "Failed to create upload directory.";
                    $_SESSION['profilePicturePath'] = null;
                }
            } elseif (!is_writable($target_dir)) {
                $_SESSION['upload_error'] = "Upload directory is not writable.";
            }

            if (!isset($_SESSION['upload_error'])) {
                $original_filename = basename($_FILES["profilePicture"]["name"]);
                $file_extension = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));
                
                // Create a unique filename
                $safe_firstname = isset($_SESSION['firstname']) ? preg_replace("/[^a-zA-Z0-9_]/", "", $_SESSION['firstname']) : 'user';
                $safe_lastname = isset($_SESSION['lastname']) ? preg_replace("/[^a-zA-Z0-9_]/", "", $_SESSION['lastname']) : 'name';
                $unique_id = uniqid();
                $new_filename = $safe_firstname . "_" . $safe_lastname . "_" . $unique_id . "." . $file_extension;
                $target_file_path_on_server = $target_dir . $new_filename;

                // Validate file type
                $allowed_types = ['jpg', 'jpeg', 'png'];
                if (in_array($file_extension, $allowed_types)) {
                    // Fixed file size check - now matches error message (5MB)
                    if ($_FILES["profilePicture"]["size"] <= 5000000) { 
                        // Verify it's actually an image
                        $check = getimagesize($_FILES["profilePicture"]["tmp_name"]);
                        if ($check !== false) {
                            if (move_uploaded_file($_FILES["profilePicture"]["tmp_name"], $target_file_path_on_server)) {
                                $_SESSION['profilePicturePath'] = "uploads/profile_pictures/" . $new_filename;
                                $_SESSION['hasProfilePicture'] = true;
                            } else {
                                $_SESSION['upload_error'] = "Sorry, there was an error uploading your file.";
                                $_SESSION['profilePicturePath'] = null;
                            }
                        } else {
                            $_SESSION['upload_error'] = "File is not a valid image.";
                            $_SESSION['profilePicturePath'] = null;
                        }
                    } else {
                        $_SESSION['upload_error'] = "Sorry, your file is too large (max 5MB).";
                        $_SESSION['profilePicturePath'] = null;
                    }
                } else {
                    $_SESSION['upload_error'] = "Sorry, only JPG, JPEG & PNG files are allowed.";
                    $_SESSION['profilePicturePath'] = null;
                }
            }
        } else if ($_FILES['profilePicture']['error'] != UPLOAD_ERR_NO_FILE) {
            $_SESSION['upload_error'] = "File upload error code: " . $_FILES['profilePicture']['error'];
            $_SESSION['profilePicturePath'] = null;
        } else {
            $_SESSION['profilePicturePath'] = null;
        }

        header("Location: ../api/authentication/register.php"); 
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
                <h1>Complete SignUp</h1>
                <?php
                    // Display upload error if it exists
                    if (isset($_SESSION['upload_error'])) {
                        echo '<p style="color: red;">' . htmlspecialchars($_SESSION['upload_error']) . '</p>';
                        unset($_SESSION['upload_error']);
                    }
                ?>
            </div>
            <form id="signupForm2" action="completeSignUp.php" method="post" enctype="multipart/form-data">
                <div class="form">
                    <i class="fas fa-map-marker-alt"></i>
                    <input type="text" name="streetAddress" id="streetAddress" placeholder="Street Address" required>
                </div>
                <div class="error" id="streetAddress-error" aria-live="polite"></div>
                <div class="form-row">
                    <div class="form">
                        <i class="fas fa-map"></i>
                        <input type="text" name="suburb" id="suburb" placeholder="Suburb" required>
                    </div>
                    <div class="form">
                        <i class="fas fa-mail-bulk"></i>
                        <input type="text" name="postalCode" id="postalCode" placeholder="Postal Code" required>
                    </div>
                </div>
                <div class="error" id="suburb-error" aria-live="polite"></div>
                <div class="error" id="postalCode-error" aria-live="polite"></div>
                <div class="form-row">
                    <div class="form">
                        <i class="fas fa-globe"></i>
                        <select name="province" id="province">
                            <option value="">Select Province</option>
                            <option value="Eastern Cape">Eastern Cape</option>
                            <option value="Free State">Free State</option>
                            <option value="Gauteng">Gauteng</option>
                            <option value="KwaZulu-Natal">KwaZulu-Natal</option>
                            <option value="Limpopo">Limpopo</option>
                            <option value="Mpumalanga">Mpumalanga</option>
                            <option value="Northern Cape">Northern Cape</option>
                            <option value="North West">North West</option>
                            <option value="Western Cape">Western Cape</option>
                        </select>
                    </div>
                    <div class="form">
                        <i class="fas fa-city"></i>
                        <select name="city" id="city">
                            <option value="">Select City</option>
                        </select>
                    </div>
                </div>
                <div class="error" id="province-error" aria-live="polite"></div>
                <div class="error" id="city-error" aria-live="polite"></div>
                <div class="form form-file-upload">
                    <label for="profilePicture" class="file-upload-label">
                        <i class="fas fa-image"></i>
                        <span>Upload Profile Picture</span>
                    </label>
                    <input type="file" name="profilePicture" id="profilePicture" accept="image/jpeg,image/png,image/jpg">
                </div>
                <div class="error" id="profilePicture-error" aria-live="polite"></div>
                <div id="error-container" class="error" style="display: none;"></div>
                <div id="next" class="normal">
                    <button type="submit" class="normal">Complete</button>
                </div>
            </form>
        </div>
    </section>
    <script src="../assets/js/userInputValidation.js"></script>
</body>
</html>