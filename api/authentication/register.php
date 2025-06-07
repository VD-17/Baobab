<?php
    session_start();

    require_once("../../includes/db_connection.php");

    $userId = $_SESSION['userId'];

    $firstname = $_SESSION['firstname'];
    $lastname = $_SESSION['lastname'];
    $email = $_SESSION['email'];
    $password = password_hash($_SESSION['password'], PASSWORD_DEFAULT);
    $phone = $_SESSION['phoneNumber'];

    $street = $_SESSION['streetAddress'];
    $suburb = $_SESSION['suburb'];
    $postal = $_SESSION['postalCode'];
    $city = $_SESSION['city'];
    $province = $_SESSION['province'];
    $profilePicture = isset($_SESSION['profilePicturePath']) ? $_SESSION['profilePicturePath'] : null;

    $errors = [];

    if (empty($firstname) || !preg_match('/^[a-zA-Z\s]{2,}$/', $firstname)) {
        $errors[] = 'First name must be at least 2 letters.';
    }

    if (empty($lastname) || !preg_match('/^[a-zA-Z\s]{2,}$/', $lastname)) {
        $errors[] = 'Last name must be at least 2 letters.';
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format.';
    }

    if (empty($password) || strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }

    if (!empty($street) && !preg_match('/^[a-zA-Z0-9\s.,-]+$/', $street)) {
        $errors[] = 'Street address can only contain letters, numbers, spaces, and common punctuation.';
    }

    if (!empty($suburb) && !preg_match('/^[a-zA-Z\s]+$/', $suburb)) {
        $errors[] = 'Suburb can only contain letters and spaces.';
    }

    if (!empty($postalCode) && !preg_match('/^\d{4,10}$/', $postalCode) && !preg_match('/^\d{5}-\d{4}$/', $postalCode)) {
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


    if (!isset($_SESSION['firstname'], $_SESSION['lastname'], $_SESSION['email'], $_SESSION['password'], $_SESSION['phoneNumber'])) {
        header("Location: ../../pages/signUp.php");
        exit();
    }

    if (!isset($_SESSION['streetAddress'], $_SESSION['suburb'], $_SESSION['postalCode'], $_SESSION['city'], $_SESSION['province'])) {
        header("Location: ../../pages/completeSignUp.php");
        exit(); 
    }

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

    // If errors, redirect back to form
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        $_SESSION['form_data'] = $_SESSION; // Preserve form data
        header('location: ../../pages/signUp.php');
        exit;
    }

    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo "<p style='color:red;'>⚠️ $error</p>";
        }
        echo "<a href='../../pages/completeSignUp.php'>Go Back</a>";
        exit;
    }

    try {

        $conn->beginTransaction();

        $stmt = $conn->prepare("INSERT INTO users 
            (firstname, lastname, email, phoneNumber, password, street_address, suburb, postal_code, city, province, profile_picture) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

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

        // Clear session after successful registration
        session_unset();
        session_destroy();

        header('location: ../../pages/signIn.php'); 
        exit;
    } catch (PDOException $e) {
        $conn->rollback();
        die("Error saving user: " . $e->getMessage());
        // header("location: ../../pages/signUp.php");
    }
?>

<!-- <a href="../pages/signIn.php"></a> -->