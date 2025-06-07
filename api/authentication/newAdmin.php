<?php
    session_start();

    require_once("../../includes/db_connection.php");

    if (!isset($_SESSION['isAdmin']) || $_SESSION['isAdmin'] != 1) {
        $_SESSION['errors'] = ["Access denied. Admin privileges required."];
        header("Location: ../../root/index.php");
        exit;
    }

    $userId = $_SESSION['userId'];

    $firstname = $_SESSION['firstname'];
    $lastname = $_SESSION['lastname'];
    $email = $_SESSION['email'];
    $originalPassword = $_SESSION['password']; // Store original password for validation
    $password = password_hash($_SESSION['password'], PASSWORD_DEFAULT);
    $phone = $_SESSION['phoneNumber'];

    $street = $_SESSION['street_address'];
    $suburb = $_SESSION['suburb'];
    $postal = $_SESSION['postal_code'];
    $city = $_SESSION['city'];
    $province = $_SESSION['province'];
    $profilePicture = isset($_SESSION['profilePicturePath']) ? $_SESSION['profilePicturePath'] : null;

    $errors = [];

    // Validate required session variables exist
    if (!isset($_SESSION['firstname'], $_SESSION['lastname'], $_SESSION['email'], $_SESSION['password'], $_SESSION['phoneNumber'],
               $_SESSION['street_address'], $_SESSION['suburb'], $_SESSION['postal_code'], $_SESSION['city'], $_SESSION['province'])) {
        header("Location: ../../pages/addAdmins.php");
        exit();
    }

    // Validation rules
    if (empty($firstname) || !preg_match('/^[a-zA-Z\s]{2,}$/', $firstname)) {
        $errors[] = 'First name must be at least 2 letters.';
    }

    if (empty($lastname) || !preg_match('/^[a-zA-Z\s]{2,}$/', $lastname)) {
        $errors[] = 'Last name must be at least 2 letters.';
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format.';
    }

    // Validate original password, not hashed version
    if (empty($originalPassword) || strlen($originalPassword) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }

    if (!empty($street) && !preg_match('/^[a-zA-Z0-9\s.,-]+$/', $street)) {
        $errors[] = 'Street address can only contain letters, numbers, spaces, and common punctuation.';
    }

    if (!empty($suburb) && !preg_match('/^[a-zA-Z\s]+$/', $suburb)) {
        $errors[] = 'Suburb can only contain letters and spaces.';
    }

    // Fixed variable name from $postalCode to $postal
    if (!empty($postal) && !preg_match('/^\d{4,10}$/', $postal) && !preg_match('/^\d{5}-\d{4}$/', $postal)) {
        $errors[] = 'Postal code must be 4-10 digits or in ZIP+4 format (e.g., 12345-6789).';
    }

    if (!empty($province) && !preg_match('/^[a-zA-Z\s]+$/', $province)) {
        $errors[] = 'Province can only contain letters and spaces.';
    }

    if (!empty($city) && !preg_match('/^[a-zA-Z\s]+$/', $city)) {
        $errors[] = 'City can only contain letters and spaces.';
    }

    if (!empty($phone) && !preg_match('/^\+?\d{10,15}$/', $phone)) {
        $errors[] = 'Phone number must be in format (e.g., +27123456789).';
    }

    // Check for duplicate email
    if (empty($errors)) {
        try {
            $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetchColumn() > 0) {
                $errors[] = 'Email already exists.';
            }
        } catch (PDOException $e) {
            $errors[] = 'Error checking email: ' . $e->getMessage();
        }
    }

    // If there are errors, redirect back with error messages
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        $_SESSION['form_data'] = $_SESSION;
        header('location: ../../pages/addAdmins.php');
        exit;
    }

    // Insert new admin user
    try {
        $conn->beginTransaction();

        $stmt = $conn->prepare("INSERT INTO users 
            (firstname, lastname, email, phoneNumber, password, street_address, suburb, postal_code, city, province, profile_picture, is_seller, is_admin) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0, 1)");

        $stmt->execute([
            $firstname,
            $lastname,
            $email,
            $phone,
            $password,
            $street,
            $suburb,
            $postal,
            $city,
            $province,
            $profilePicture
        ]);

        $newUserId = $conn->lastInsertId();

        $_SESSION['userId'] = $newUserId;
        $_SESSION['firstname'] = $firstname;
        $_SESSION['lastname'] = $lastname;
        $_SESSION['email'] = $email;

        $profileStatus = isset($_SESSION['hasProfilePicture']) && $_SESSION['hasProfilePicture'] ? 0 : 1;
        $stmt2 = $conn->prepare("INSERT INTO profileimg (userId, status) VALUES (?, ?)");
        $stmt2->execute([$newUserId, $profileStatus]);
        
        $conn->commit();

        // Clean up session variables
        unset($_SESSION['firstname'], $_SESSION['lastname'], $_SESSION['email'], $_SESSION['password'], 
              $_SESSION['phoneNumber'], $_SESSION['street_address'], $_SESSION['suburb'], 
              $_SESSION['postal_code'], $_SESSION['city'], $_SESSION['province'], 
              $_SESSION['profilePicturePath'], $_SESSION['hasProfilePicture']);

        $_SESSION['success'] = 'Admin user created successfully.';
        header('location: ../../pages/admins.php'); 
        exit;
        
    } catch (PDOException $e) {
        $conn->rollback();
        $_SESSION['errors'] = ['Error saving user: ' . $e->getMessage()];
        header('location: ../../pages/addAdmins.php');
        exit;
    }
?>