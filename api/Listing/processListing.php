<?php
    session_start();
    require_once("../../includes/db_connection.php");

    if (!isset($_SESSION['userId'])) {
        $_SESSION['errors'] = ['You must be logged in to list a product.'];
        exit();
    }

    $userId = $_SESSION['userId'];
    $productId = $_SESSION['id'];
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
        $errors[] = 'Select perferred delivery method';
    }

    if (empty($productPicture)) {
        $errors[] = 'At least one product image is required.';
    }

    if (!empty($_SESSION['upload_error'])) {
        $errors[] = $_SESSION['upload_error'];
    }

    // Check for missing product details
    if (!isset($_SESSION['productName'], $_SESSION['description'], $_SESSION['productCategory'], $_SESSION['quality'], $_SESSION['price'])) {
        $_SESSION['message'] = "Missing product details.";
        // header("Location: ../pages/listing.php");
        exit();
    }

    // If errors, redirect back to form
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
        header('Location: ../pages/listing.php');
        exit();
    }

    try {
        $conn->beginTransaction(); 

        $productPictureJson = is_array($productPicture) ? json_encode($productPicture) : $productPicture;

        $stmt = $conn->prepare("INSERT INTO products 
            (userId, productName, description, productCategory, subCategory, quality, price, productPicture, productVideo, deliveryMethod, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $status = 'Active';

        $stmt->execute([
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

        $conn->commit();

        echo "<script type='text/javascript'>alert('Successfully inserted the products');</script>";

        unset($_SESSION['id']);
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

        $bank_check_stmt = $conn->prepare("SELECT id FROM seller_bank_details WHERE userId = ?");
        $bank_check_stmt->execute([$userId]);

        if ($bank_check_stmt->rowCount() > 0) {
            // User has bank details, redirect to myListings
            header("Location: ../../pages/myListing.php");
        } else {
            // User doesn't have bank details, redirect to bank details page
            header("Location: ../../pages/user_bank_details.php");
        }
        exit();
    } catch (PDOException $e) {
        $conn->rollback();
        $_SESSION['errors'] = ["Error saving product: " . $e->getMessage()];
        // header('Location: ../pages/listing.php');
        exit();
    }
?>