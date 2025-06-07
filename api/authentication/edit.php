<?php
session_start();
require_once("../../includes/db_connection.php");

if (!isset($_SESSION['userId'])) {
    $_SESSION['errors'] = ['You must be logged in to edit your profile.'];
    header('Location: ../../pages/signIn.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['userId'];
    $firstname = trim($_POST['firstname'] ?? '');
    $lastname = trim($_POST['lastname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phoneNumber = trim($_POST['phoneNumber'] ?? '');
    $street_address = trim($_POST['street_address'] ?? '');
    $suburb = trim($_POST['suburb'] ?? '');
    $postal_code = trim($_POST['postal_code'] ?? '');
    $province = trim($_POST['province'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $bio = trim($_POST['bio'] ?? '');

    $errors = [];

    // Validation
    if (empty($firstname)) {
        $errors[] = 'First Name is required.';
    } 

    if (!empty($bio) && strlen($bio) < 10) {
        $errors[] = 'Bio must be at least 10 characters long.';
    }

    if (empty($lastname)) {
        $errors[] = 'Last name is required.';
    }

    if (empty($email)) {
        $errors[] = 'Email is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if (empty($phoneNumber)) {
        $errors[] = 'Phone Number is required.';
    }

    if (empty($street_address)) {
        $errors[] = 'Street Address is required.';
    }

    if (empty($province)) {
        $errors[] = 'Province is required.';
    }

    if (empty($city)) {
        $errors[] = 'City is required.';
    }

    // Handle profile picture upload
    $profilePicturePath = null;
    if (!empty($_FILES['profilePicture']['name'])) {
        $uploadDir = '../../uploads/profile_pictures/';
        
        // Create directory if it doesn't exist
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/jpg'];
        $maxFileSize = 5 * 1024 * 1024; // 5MB

        $fileType = $_FILES['profilePicture']['type'];
        $fileSize = $_FILES['profilePicture']['size'];
        $fileName = uniqid() . '_' . basename($_FILES['profilePicture']['name']);
        $targetPath = $uploadDir . $fileName;

        if (!in_array($fileType, $allowedTypes)) {
            $errors[] = "Invalid file type. Please upload JPEG, PNG, or GIF images only.";
        } elseif ($fileSize > $maxFileSize) {
            $errors[] = "File too large. Maximum size is 5MB.";
        } elseif (move_uploaded_file($_FILES['profilePicture']['tmp_name'], $targetPath)) {
            $profilePicturePath = 'uploads/profile_pictures/' . $fileName;
        } else {
            $errors[] = "Error uploading profile picture.";
        }
    }

    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        $_SESSION['userForm'] = [
            'firstname' => $firstname,
            'lastname' => $lastname,
            'email' => $email,
            'phoneNumber' => $phoneNumber,
            'street_address' => $street_address,
            'suburb' => $suburb,
            'postal_code' => $postal_code,
            'province' => $province,
            'city' => $city,
            'bio' => $bio,
        ];
        header("Location: ../../pages/editProfile.php");
        exit();
    }

    try {
        $conn->beginTransaction();
        
        // Prepare SQL with or without profile picture update
        if ($profilePicturePath) {
            $sql = "UPDATE users SET 
                firstname = ?, lastname = ?, email = ?, street_address = ?, suburb = ?, postal_code = ?, 
                province = ?, city = ?, profile_picture = ?, phoneNumber = ?, bio = ?, updated_at = NOW() 
                WHERE userId = ?";
            $params = [
                $firstname, $lastname, $email, $street_address, $suburb, $postal_code,
                $province, $city, $profilePicturePath, $phoneNumber, $bio, $userId
            ];
        } else {
            $sql = "UPDATE users SET 
                firstname = ?, lastname = ?, email = ?, street_address = ?, suburb = ?, postal_code = ?, 
                province = ?, city = ?, phoneNumber = ?, bio = ?, updated_at = NOW() 
                WHERE userId = ?";
            $params = [
                $firstname, $lastname, $email, $street_address, $suburb, $postal_code,
                $province, $city, $phoneNumber, $bio, $userId
            ];
        }
        
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);
        
        $conn->commit();
        $_SESSION['message'] = "Profile updated successfully.";
        header('Location: ../../pages/userDashboard.php');
        exit();
        
    } catch (PDOException $e) {
        $conn->rollback();
        $_SESSION['errors'] = ["Error saving changes: " . $e->getMessage()];
        header("Location: ../../pages/editProfile.php");
        exit();
    }
}
?>