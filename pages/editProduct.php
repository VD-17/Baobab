<?php
    session_start();
    require_once("../includes/db_connection.php");

    if (!isset($_SESSION['userId'])) {
        $_SESSION['errors'] = ['You must be logged in to edit a product.'];
        header('Location: ../pages/signIn.php?redirect=myListing.php');
        exit();
    }

    $userId = $_SESSION['userId'];

    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        $_SESSION['errors'] = ['Invalid product ID'];
        header('Location: ../pages/myListing.php');
        exit();
    }

    $productId = $_GET['id'];

    try {
        $stmt = $conn->prepare("SELECT id, userId, productName, description, productCategory, subCategory, quality, price, productPicture, productVideo, deliveryMethod, status FROM products WHERE id = ?");
        $stmt->execute([$productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            $_SESSION['errors'] = ['Product not found'];
            header('Location: ../pages/myListing.php');
            exit();
        }

        if ($product['userId'] != $userId) {
            $_SESSION['errors'] = ['You are not authorized to edit this product.'];
            header('Location: ../pages/myListing.php');
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['errors'] = ["Error fetching product: " . $e->getMessage()];
        header('Location: ../pages/myListing.php');
        exit();
    }

    $formData = $_SESSION['form_data'] ?? [
        'productName' => $product['productName'],
        'description' => $product['description'],
        'productCategory' => $product['productCategory'],
        'subCategory' => $product['subCategory'],
        'quality' => $product['quality'],
        'price' => $product['price'],
        'delivery' => $product['deliveryMethod'],
        'status' => $product['status'],
    ];
    $errors = $_SESSION['errors'] ?? [];
    unset($_SESSION['form_data'], $_SESSION['errors']);

    function uploadImages($files, $session_data) {
        if (!isset($files['productPicture']) || !is_array($files['productPicture']['name'])) {
            return null;
        }

        $target_dir = "../uploads/product_pictures/";
        $uploaded_paths = [];
        
        // Create directory if it doesn't exist
        if (!is_dir($target_dir) && !mkdir($target_dir, 0755, true) && !is_dir($target_dir)) {
            $_SESSION['picture_upload_error'] = "Failed to create upload directory.";
            return null;
        } 
        
        if (!is_writable($target_dir)) {
            $_SESSION['picture_upload_error'] = "Upload directory is not writable.";
            return null;
        }

        $allowed_extensions = ['jpg', 'jpeg', 'png'];
        $allowed_mimes = ['image/jpeg', 'image/png'];
        $total_files = count($files['productPicture']['name']);
        $master_unique_id = uniqid() . '_' . time() . '_' . mt_rand(1000, 9999);

        for ($i = 0; $i < $total_files; $i++) {
            if ($files['productPicture']['error'][$i] === UPLOAD_ERR_OK) {
                $original_filename = basename($files["productPicture"]["name"][$i]);
                $file_extension = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));
                $tmp_name = $files['productPicture']['tmp_name'][$i];
                $file_size = $files['productPicture']['size'][$i];
                
                // Validate file extension
                if (!in_array($file_extension, $allowed_extensions)) {
                    $_SESSION['picture_upload_error'] = "Only JPG, JPEG & PNG files are allowed.";
                    continue;
                }

                // Validate file size (5MB)
                if ($file_size > 5000000) {
                    $_SESSION['picture_upload_error'] = "One or more files are too large (max 5MB).";
                    continue;
                }

                // Validate MIME type
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $tmp_name);
                finfo_close($finfo);
                if (!in_array($mime, $allowed_mimes)) {
                    $_SESSION['picture_upload_error'] = "Invalid image type.";
                    continue;
                }

                // Validate image integrity
                $check = getimagesize($tmp_name);
                if ($check === false) {
                    $_SESSION['picture_upload_error'] = "One or more files are not valid images.";
                    continue;
                }

                // Create unique filename
                $safe_firstname = isset($session_data['firstname']) ? preg_replace("/[^a-zA-Z0-9_]/", "", $session_data['firstname']) : 'user';
                $safe_lastname = isset($session_data['lastname']) ? preg_replace("/[^a-zA-Z0-9_]/", "", $session_data['lastname']) : 'name';
                $new_filename = "img_" . $safe_firstname . "_" . $safe_lastname . "_" . $master_unique_id . "_" . ($i + 1) . "." . $file_extension;
                $target_file_path = $target_dir . $new_filename;

                // Move uploaded file
                if (move_uploaded_file($tmp_name, $target_file_path)) {
                    $uploaded_paths[] = "uploads/product_pictures/" . $new_filename;
                } else {
                    $_SESSION['picture_upload_error'] = "Error uploading file: $original_filename";
                }
            }
        }

        return !empty($uploaded_paths) ? $uploaded_paths : null;
    }

    function uploadVideo($files, $session_data) {
        if (!isset($files['productVideo']) || $files['productVideo']['error'] != UPLOAD_ERR_OK) {
            if (isset($files['productVideo']) && $files['productVideo']['error'] != UPLOAD_ERR_NO_FILE) {
                $_SESSION['upload_video_error'] = "Video upload error code: " . $files['productVideo']['error'];
            }
            return null;
        }

        $target_video_dir = "../uploads/product_videos/";

        // Create directory if it doesn't exist
        if (!file_exists($target_video_dir)) {
            if (!mkdir($target_video_dir, 0755, true)) {
                $_SESSION['upload_video_error'] = "Failed to create upload directory.";
                return null;
            }
        }
        
        if (!is_writable($target_video_dir)) {
            $_SESSION['upload_video_error'] = "Upload directory is not writable.";
            return null;
        }

        $original_video_filename = basename($files["productVideo"]["name"]);
        $video_file_extension = strtolower(pathinfo($original_video_filename, PATHINFO_EXTENSION));
        $video_tmp_name = $files["productVideo"]["tmp_name"];
        $video_file_size = $files["productVideo"]["size"];

        // Validate file extension
        $video_allowed_types = ['mp4', 'webm', 'ogg'];
        if (!in_array($video_file_extension, $video_allowed_types)) {
            $_SESSION['upload_video_error'] = "Sorry, only MP4, WEBM & OGG video files are allowed.";
            return null;
        }

        // Validate file size (50MB)
        if ($video_file_size > 50000000) {
            $_SESSION['upload_video_error'] = "Sorry, your video is too large (max 50MB).";
            return null;
        }

        // Validate MIME type
        $video_finfo = finfo_open(FILEINFO_MIME_TYPE);
        $video_mime_type = finfo_file($video_finfo, $video_tmp_name);
        finfo_close($video_finfo);

        $allowed_video_mime_types = ['video/mp4', 'video/webm', 'video/ogg'];
        if (!in_array($video_mime_type, $allowed_video_mime_types)) {
            $_SESSION['upload_video_error'] = "File is not a valid video format.";
            return null;
        }

        // Create unique filename
        $safe_video_firstname = isset($session_data['firstname']) ? preg_replace("/[^a-zA-Z0-9_]/", "", $session_data['firstname']) : 'user';
        $safe_video_lastname = isset($session_data['lastname']) ? preg_replace("/[^a-zA-Z0-9_]/", "", $session_data['lastname']) : 'name';
        $master_unique_id = uniqid() . '_' . time() . '_' . mt_rand(1000, 9999);
        $new_video_filename = "vid_" . $safe_video_firstname . "_" . $safe_video_lastname . "_" . $master_unique_id . "." . $video_file_extension;
        $target_file_path_on_server = $target_video_dir . $new_video_filename;

        // Move uploaded file
        if (move_uploaded_file($video_tmp_name, $target_file_path_on_server)) {
            return "uploads/product_videos/" . $new_video_filename;
        } else {
            $_SESSION['upload_video_error'] = "Sorry, there was an error uploading your video.";
            return null;
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $productName = $_POST['productName'] ?? '';
        $description = $_POST['description'] ?? '';
        $productCategory = $_POST['productCategory'] ?? '';
        $subCategory = $_POST['subCategory'] ?? '';
        $quality = $_POST['quality'] ?? '';
        $price = $_POST['price'] ?? '';
        $delivery = $_POST['delivery'] ?? '';
        $status = $_POST['status'] ?? $product['status'];
        $productPicture = $product['productPicture']; // Keep existing by default
        $productVideo = $product['productVideo']; // Keep existing by default

        $errors = [];

        // Validation
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

        // Handle image uploads if new files are provided
        if (!empty($_FILES['productPicture']['name'][0])) {
            $uploadedImages = uploadImages($_FILES, $_SESSION);
            if ($uploadedImages !== null) {
                // Delete old images
                $oldImages = json_decode($product['productPicture'], true);
                if (is_array($oldImages)) {
                    foreach ($oldImages as $oldImage) {
                        $oldImagePath = '../' . $oldImage; // Convert web path back to server path
                        if (file_exists($oldImagePath)) {
                            unlink($oldImagePath);
                        }
                    }
                }
                $productPicture = json_encode($uploadedImages);
            } else {
                if (isset($_SESSION['picture_upload_error'])) {
                    $errors[] = $_SESSION['picture_upload_error'];
                    unset($_SESSION['picture_upload_error']);
                }
            }
        }

        // Handle video upload if new file is provided
        if (!empty($_FILES['productVideo']['name'])) {
            $uploadedVideo = uploadVideo($_FILES, $_SESSION);
            if ($uploadedVideo !== null) {
                // Delete old video
                if ($product['productVideo']) {
                    $oldVideoPath = '../' . $product['productVideo']; // Convert web path back to server path
                    if (file_exists($oldVideoPath)) {
                        unlink($oldVideoPath);
                    }
                }
                $productVideo = $uploadedVideo;
            } else {
                if (isset($_SESSION['upload_video_error'])) {
                    $errors[] = $_SESSION['upload_video_error'];
                    unset($_SESSION['upload_video_error']);
                }
            }
        }

        // Check if we have at least one image (either existing or new)
        if (empty($productPicture) || $productPicture === 'null' || $productPicture === '[]') {
            $errors[] = 'At least one product image is required.';
        }

        // $image_paths = uploadImages($_FILES, $_SESSION);
        // if ($image_paths !== null) {
        //     $_SESSION['productPicturePath'] = $image_paths;
        //     $_SESSION['hasProductPicture'] = true;
        // } else {
        //     $_SESSION['productPicturePath'] = null;
        //     $_SESSION['hasProductPicture'] = false;
        // }

        // // Handle video upload
        // $video_path = uploadVideo($_FILES, $_SESSION);
        // if ($video_path !== null) {
        //     $_SESSION['productVideoPath'] = $video_path;
        //     $_SESSION['hasProductVideo'] = true;
        // } else {
        //     $_SESSION['productVideoPath'] = null;
        //     $_SESSION['hasProductVideo'] = false;
        // }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['form_data'] = [
                'productName' => $productName,
                'description' => $description,
                'productCategory' => $productCategory,
                'subCategory' => $subCategory,
                'quality' => $quality,
                'price' => $price,
                'delivery' => $delivery,
                'status' => $status
            ];
            header("Location: editProduct.php?id=$productId");
            exit();
        }

        try {
            $conn->beginTransaction();
            $stmt = $conn->prepare("UPDATE products SET 
                productName = ?, description = ?, productCategory = ?, subCategory = ?, 
                quality = ?, price = ?, productPicture = ?, productVideo = ?, 
                deliveryMethod = ?, status = ? WHERE id = ?");
            $stmt->execute([
                $productName,
                $description,
                $productCategory,
                $subCategory,
                $quality,
                $price,
                $productPicture,
                $productVideo,
                $delivery,
                $status,
                $productId
            ]);
            $conn->commit();
            $_SESSION['message'] = "Product updated successfully.";
            header('Location: ../pages/myListing.php');
            exit();
            error_log("Product updated successfully, redirecting to myListing.php");
        } catch (PDOException $e) {
            $conn->rollback();
            $_SESSION['errors'] = ["Error updating product: " . $e->getMessage()];
            header("Location: editProduct.php?id=$productId");
            exit();
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<?php 
    $pageTitle = "Edit Products";
    include('../includes/head.php'); 
?>
<head>
    <link rel="stylesheet" href="../assets/css/listing.css"> 
</head>
<body class="listing">
    <?php include('../includes/header.php'); ?>
    <section id="productListing">
        <div id="productHeading">
            <h1>Edit Product</h1>
            <p>Read the Listing Guidelines <a href="">here</a>.</p>
        </div>
        <div id="product">
            <?php if (!empty($errors)): ?>
                <div class="errors" style="color: red; margin-bottom: 20px;">
                    <?php foreach ($errors as $error): ?>
                        <p>⚠️ <?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <form id="productForm" method="POST" action="editProduct.php?id=<?php echo $productId; ?>" enctype="multipart/form-data"> 
                <div id="productDescription">
                    <fieldset class="form">
                        <legend>Product Name:</legend>
                        <input type="text" name="productName" id="productName" value="<?php echo htmlspecialchars($formData['productName']); ?>" required>
                        <div class="error" id="productName-error" aria-live="polite"></div>
                    </fieldset>

                    <fieldset class="form description">
                        <legend>Description:</legend>
                        <textarea id="description" name="description" required><?php echo htmlspecialchars($formData['description']); ?></textarea>
                        <div class="error" id="description-error" aria-live="polite"></div>
                    </fieldset> 
                    
                    <fieldset class="form">
                        <legend>Category:</legend> 
                        <input type="text" name="productCategory" id="productCategory" list="category" value="<?php echo htmlspecialchars($formData['productCategory']); ?>" required>
                        <datalist id="category">
                            <option value="Electronics">
                            <option value="Vehicle">
                            <option value="Home"> 
                            <option value="Fashion">
                            <option value="Furniture">
                            <option value="Toys & Games">
                            <option value="Outdoor & Sports">
                            <option value="Antiques & Collectibles">
                            <option value="Books">
                        </datalist>
                        <div class="error" id="productCategory-error" aria-live="polite"></div>
                        <input type="text" name="subCategory" id="subCategory" list="sub" value="<?php echo htmlspecialchars($formData['subCategory']); ?>">
                        <datalist id="sub">
                            <?php
                                $subcategories = [
                                    'Electronics' => ['Mobile phones', 'Laptops', 'Computers', 'Tablets', 'Cameras', 'Wearables(Smartwatches)', 'Accessories'],
                                    'Vehicle'=> ['Cars', 'Motorcycles', 'Bicycles', 'Trucks', 'Parts & Accessories'],
                                    'Home' => ['Home Decor', 'Kitchen', 'Appliances', 'Living Room', 'Bathroom', 'Garden'],
                                    'Fashion' => ['Men\'s Clothings', 'Women\'s Clothings', 'Kids\'s Clothings', 'Footwear', 'Jewelry', 'Bags', 'Watches', 'Sunglasses'],
                                    'Furniture' => ['Sofas', 'Chairs', 'Tables', 'Storage(Cupboards, shelves, cabinets)', 'Beds & mattresses'],
                                    'Toys & Games' => ['Board games', 'Video games', 'Action Figures', 'Puzzles', 'Dolls & Plush toys'],
                                    'Outdoor & Sports' => ['Camping equipment', 'Outdoor gear', 'Sports equipment', 'Gym/Fitness equipments'],
                                    'Antiques & Collectibles' => ['Arts', 'Coins & Currency', 'Stamps', 'Vintage Items'],
                                    'Books' => ['Educational & Academics', 'Fiction & Non-Fiction', 'Comics', 'Magazines']
                                ];
                                if (!empty($formData['productCategory']) && isset($subcategories[$formData['productCategory']])) {
                                    foreach ($subcategories[$formData['productCategory']] as $sub) {
                                        $selected = ($formData['subCategory'] === $sub) ? 'selected' : '';
                                        echo "<option value=\"$sub\" $selected>";
                                    }
                                }
                            ?>
                        </datalist>
                    </fieldset>   
                    
                    <fieldset class="form">
                        <legend>Condition:</legend>
                        <input type="text" name="quality" id="quality" list="condition" value="<?php echo htmlspecialchars($formData['quality']); ?>" required>
                        <datalist id="condition">
                            <option value="Brand New">
                            <option value="Like New">
                            <option value="Very Good">
                            <option value="Good">
                            <option value="Old">
                            <option value="Need Repair">
                        </datalist>
                        <div class="error" id="quality-error" aria-live="polite"></div>
                    </fieldset>

                    <fieldset class="form">
                        <legend>Pricing:</legend>
                        <input type="number" name="price" id="price" value="<?php echo htmlspecialchars($formData['price']); ?>" min="0" step="0.01" required>
                        <div class="error" id="price-error" aria-live="polite"></div>
                    </fieldset> 

                    <fieldset class="form">
                        <legend>Delivery Method:</legend>
                        <input type="text" name="delivery" id="delivery" list="deliveryMethod" value="<?php echo htmlspecialchars($formData['delivery']); ?>" required>
                        <datalist id="deliveryMethod">
                            <option value="MeetUp">
                        </datalist>
                        <div class="error" id="delivery-error" aria-live="polite"></div>
                    </fieldset>

                    <fieldset class="form form-file-upload">
                        <legend>Product Image:</legend>
                        <label for="productPicture" class="file-upload-label">
                            <i class="fas fa-image"></i>
                            <span>Replace Product Pictures</span>
                        </label>
                        <input type="file" name="productPicture[]" id="productPicture" accept="image/jpeg,image/png,image/jpg" multiple>
                        <p style="font-size: 12px; color: #666; margin-top: 5px;">
                            Note: Selecting new images will replace all current images
                        </p>
                        <div id="imagePreview"></div>
                    </fieldset> 
                    <div class="error" id="productPicture-error" aria-live="polite"></div>
                    
                    <!-- <fieldset class="form form-file-upload">
                        <legend>Product Video:</legend>
                        <label for="productVideo" class="file-upload-label">
                            <i class="fas fa-video"></i>
                            <span>Replace Product Video</span>
                        </label>
                        <input type="file" name="productVideo" id="productVideo" accept="video/mp4,video/webm,video/ogg">
                        <p style="font-size: 12px; color: #666; margin-top: 5px;">
                            Note: Selecting a new video will replace the current video
                        </p>
                        <div id="videoPreview"></div>
                    </fieldset>  
                    <div class="error" id="productVideo-error" aria-live="polite"></div> -->
                    
                    <div id="next">
                        <button type="submit" class="normal">Update Product</button>
                    </div>
                </div>
            </form>
        </div>
    </section>
    <!-- <script src="../assets/js/editProductValidation.js"></script>  -->
</body>
</html>