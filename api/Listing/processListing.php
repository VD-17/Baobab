<?php
session_start();
require_once("../../includes/db_connection.php");

if (!isset($_SESSION['userId'])) {
    $_SESSION['errors'] = ['You must be logged in to list a product.'];
    exit();
}

$userId = $_SESSION['userId'];
$productName = $_SESSION['productName'] ?? '';
$description = $_SESSION['description'] ?? '';
$productCategory = $_SESSION['productCategory'] ?? '';
$subCategory = $_SESSION['subCategory'] ?? '';
$quality = $_SESSION['quality'] ?? '';
$price = $_SESSION['price'] ?? '';
$delivery = $_SESSION['delivery'] ?? '';
$productPicture = $_SESSION['productPicturePath'] ?? null;
$productVideo = $_SESSION['productVideoPath'] ?? null;

$errors = [];

// Your validation code here...
if (empty($productName)) {
    $errors[] = 'Product Name is required.';
} else if (strlen($productName) > 255) {
    $errors[] = 'Product Name must be less than 255 characters.';
}

if (empty($description)) {
    $errors[] = 'Description is required.';
} else if (strlen($description) < 10) {
    $errors[] = 'Description must be at least 10 characters long.';
}

if (empty($productCategory)) {
    $errors[] = 'Select a product category.';
}

if (empty($quality)) {
    $errors[] = 'Select product condition.';
}

if (empty($price)) {
    $errors[] = 'Price is required.';
} else if (!is_numeric($price) || floatval($price) < 0) {
    $errors[] = 'Price must be a positive number.';
} else if (!preg_match('/^\d+(\.\d{1,2})?$/', $price)) {
    $errors[] = 'Price must have at most 2 decimal places.';
}

if (empty($delivery)) {
    $errors[] = 'Select preferred delivery method';
}

if (empty($productPicture)) {
    $errors[] = 'At least one product image is required.';
}

if (!empty($_SESSION['upload_error'])) {
    $errors[] = $_SESSION['upload_error'];
}

if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    $_SESSION['form_data'] = [
        'productName' => $productName,
        'description' => $description,
        'productCategory' => $productCategory,
        'subCategory' => $subCategory,
        'quality' => $quality,
        'price' => $price,
        'delivery' => $delivery
    ];
    header('Location: ../../pages/listing.php');
    exit();
}

try {
    $conn->beginTransaction(); 

    $productPictureJson = is_array($productPicture) ? json_encode($productPicture) : $productPicture;

    // Debug: Check the userId
    echo "Debug - UserID: " . $userId . "<br>";
    
    $stmt = $conn->prepare("INSERT INTO products 
        (userId, productName, description, productCategory, subCategory, quality, price, productPicture, productVideo, deliveryMethod, status) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    $status = 'Active';

    $result = $stmt->execute([
        $userId,
        $productName,
        $description,
        $productCategory,
        $subCategory,
        $quality,
        $price,
        $productPictureJson,
        $productVideo,
        $delivery,
        $status
    ]);

    if (!$result) {
        throw new Exception("Failed to insert product");
    }

    echo "Debug - Product inserted successfully<br>";

    // Fix: Make sure you're using the correct column name
    $seller_stmt = $conn->prepare("UPDATE users SET is_seller = 1 WHERE userId = ?");
    $result2 = $seller_stmt->execute([$userId]);
    
    if (!$result2) {
        echo "Debug - Failed to update user as seller<br>";
    } else {
        echo "Debug - User updated as seller<br>";
    }

    $conn->commit();
    
    echo "Debug - Transaction committed<br>";

    // Clear session data
    unset($_SESSION['productName']);
    unset($_SESSION['description']);
    unset($_SESSION['productCategory']);
    unset($_SESSION['subCategory']);
    unset($_SESSION['quality']);
    unset($_SESSION['price']);
    unset($_SESSION['delivery']);
    unset($_SESSION['productPicturePath']);
    unset($_SESSION['productVideoPath']);
    unset($_SESSION['upload_error']);
    unset($_SESSION['errors']);
    unset($_SESSION['form_data']);

    // Debug: Check bank details
    $bank_check_stmt = $conn->prepare("SELECT id FROM seller_bank_details WHERE userId = ?");
    $bank_check_stmt->execute([$userId]);
    $bankDetailsCount = $bank_check_stmt->rowCount();
    
    echo "Debug - Bank details count: " . $bankDetailsCount . "<br>";

    if ($bankDetailsCount > 0) {
        echo "Debug - Redirecting to myListing.php<br>";
        // Use absolute URL or fix the relative path
        header("Location: ../../pages/myListing.php");
    } else {
        echo "Debug - Redirecting to user_bank_details.php<br>";
        // Use absolute URL or fix the relative path
        header("Location: ../../pages/user_bank_details.php");
    }
    exit();

} catch (PDOException $e) {
    $conn->rollback();
    echo "Database Error: " . $e->getMessage();
    $_SESSION['errors'] = ["Error saving product: " . $e->getMessage()];
    exit();
} catch (Exception $e) {
    $conn->rollback();
    echo "General Error: " . $e->getMessage();
    exit();
}
?>