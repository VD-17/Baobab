<?php 
    session_start();

    require_once("../includes/db_connection.php");

    if (!isset($_SESSION['isAdmin']) || $_SESSION['isAdmin'] != 1) {
        $_SESSION['errors'] = ["Access denied. Admin privileges required."];
        header("Location: ../root/index.php");
        exit;
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $_SESSION['firstname'] = $_POST['firstname'];
        $_SESSION['lastname'] = $_POST['lastname'];
        $_SESSION['email'] = $_POST['email'];
        $_SESSION['phoneNumber'] = $_POST['phoneNumber'];
        $_SESSION['street_address'] = $_POST['street_address']; 
        $_SESSION['suburb'] = $_POST['suburb'];
        $_SESSION['postal_code'] = $_POST['postal_code'];
        $_SESSION['city'] = $_POST['city'];
        $_SESSION['province'] = $_POST['province'];
        $_SESSION['password'] = $_POST['password'];

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
        } else if (isset($_FILES['profilePicture']) && $_FILES['profilePicture']['error'] != UPLOAD_ERR_NO_FILE) {
            $_SESSION['upload_error'] = "File upload error code: " . $_FILES['profilePicture']['error'];
            $_SESSION['profilePicturePath'] = null;
        } else {
            $_SESSION['profilePicturePath'] = null;
        }

        header("Location: ../api/authentication/newAdmin.php");
        exit();
    }
?>

<!DOCTYPE html>
<html lang="en">
<?php 
    $pageTitle = "Add New Admin";
    include('../includes/head.php'); 
?>
<head>
    <link rel="stylesheet" href="../assets/css/newAdmin.css"> 
</head>
<body id="add">

    <div>
        <h2>Add New Admin</h2>
    </div>
    
    <!-- Display errors if any -->
    <?php if (isset($_SESSION['errors']) && !empty($_SESSION['errors'])): ?>
        <div class="error-messages">
            <?php foreach ($_SESSION['errors'] as $error): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endforeach; ?>
        </div>
        <?php unset($_SESSION['errors']); ?>
    <?php endif; ?>

    <!-- Display success message if any -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="success-message">
            <?php echo htmlspecialchars($_SESSION['success']); ?>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    
    <section>
        <div id="formInput">
            <form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post" id="userForm" enctype="multipart/form-data">
                <div class="profile">
                    <label for="firstname">First Name</label>
                    <i class="fa-solid fa-user"></i>
                    <input type="text" name="firstname" id="firstname" placeholder="First Name" required>
                </div>
                <div class="error" id="firstname-error" aria-live="polite"></div>
                <div class="profile">
                    <label for="lastname">Last Name</label>
                    <i class="fa-solid fa-user"></i>
                    <input type="text" name="lastname" id="lastname" placeholder="Last Name" required>
                </div>
                <div class="error" id="lastname-error" aria-live="polite"></div>
                <div class="profile">
                    <label for="email">Email</label>
                    <i class="fa-solid fa-envelope"></i>
                    <input type="email" name="email" id="email" placeholder="Email" required>
                </div>
                <div class="error" id="email-error" aria-live="polite"></div>
                <div class="profile">
                    <label for="phoneNumber">Phone Number</label>
                    <i class="fas fa-phone"></i>
                    <input type="text" name="phoneNumber" id="phoneNumber" placeholder="Phone Number">
                </div>
                <div class="error" id="phoneNumber-error" aria-live="polite"></div>
                <div class="profile">
                    <div id="address">
                        <div id="street">
                            <label for="street_address">Street Address</label>
                            <i class="fas fa-map-marker-alt"></i>
                            <input type="text" name="street_address" id="street_address" placeholder="Street Address">
                        </div>
                        <div class="error" id="streetAddress-error" aria-live="polite"></div>
                        <div id="suburb">
                            <label for="suburb">Suburb</label>
                            <i class="fas fa-map"></i>
                            <input type="text" name="suburb" id="suburb" placeholder="Suburb">
                        </div>
                        <div class="error" id="suburb-error" aria-live="polite"></div>
                        <div id="code">
                            <label for="postal_code">Postal Code</label>
                            <i class="fas fa-mail-bulk"></i>
                            <input type="text" name="postal_code" id="postal_code" placeholder="Postal Code">
                        </div>
                        <div class="error" id="postalCode-error" aria-live="polite"></div>
                        <div id="city">
                            <label for="city">City</label>
                            <i class="fas fa-city"></i>
                            <input type="text" name="city" id="city" placeholder="City">
                        </div>
                        <div class="error" id="city-error" aria-live="polite"></div>
                        <div id="province">
                            <label for="province">Province</label>
                            <i class="fas fa-globe"></i>
                            <input type="text" name="province" id="province" placeholder="Province">
                        </div>
                        <div class="error" id="province-error" aria-live="polite"></div>
                    </div>
                </div>
                <div class="profile">
                    <label for="profilePicture">Profile Picture</label>
                    <input type="file" name="profilePicture" id="profilePicture" accept="image/*">
                </div>
                <div class="error" id="profilePicture-error" aria-live="polite"></div>
                <div class="password">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" placeholder="Password" required>
                </div>
                <div class="error" id="password-error" aria-live="polite"></div>
                <button type="submit" class="normal">Add Admin</button>
            </form>
        </div>
    </section>

    <script src="../assets/js/userInputValidation.js"></script>
</body>
</html>