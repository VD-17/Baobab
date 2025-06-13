<?php
    session_start();
    require_once("../includes/db_connection.php");

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

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Store form data in session
        $_SESSION['productName'] = $_POST['productName'];
        $_SESSION['description'] = $_POST['description'];
        $_SESSION['productCategory'] = $_POST['productCategory'];
        $_SESSION['subCategory'] = $_POST['subCategory'];
        $_SESSION['quality'] = $_POST['quality'];
        $_SESSION['price'] = $_POST['price'];
        $_SESSION['delivery'] = $_POST['delivery'];

        // Handle image uploads
        $image_paths = uploadImages($_FILES, $_SESSION);
        if ($image_paths !== null) {
            $_SESSION['productPicturePath'] = $image_paths;
            $_SESSION['hasProductPicture'] = true;
        } else {
            $_SESSION['productPicturePath'] = null;
            $_SESSION['hasProductPicture'] = false;
        }

        // Handle video upload
        $video_path = uploadVideo($_FILES, $_SESSION);
        if ($video_path !== null) {
            $_SESSION['productVideoPath'] = $video_path;
            $_SESSION['hasProductVideo'] = true;
        } else {
            $_SESSION['productVideoPath'] = null;
            $_SESSION['hasProductVideo'] = false;
        }

        header("Location: ../api/Listing/processListing.php"); 
        exit();
    }
?>

<!DOCTYPE html>
<html lang="en">
<?php 
    $pageTitle = "List Products";
    include('../includes/head.php'); 
?>
<head>
    <link rel="stylesheet" href="../assets/css/listing.css"> 
</head>
<body class="listing">
    <?php include('../includes/header.php'); ?>
    <section id="productListing">
        <div id="productHeading">
            <h1>List Products</h1>
            <h4>Seamlessly upload manage, and share your product effortlessly</h4>
            <!-- <p>Read the Listing Guidelines <a href="">here</a>.</p>  -->
        </div>
        <div id="product">
            <div id="createListing">
                <h2>Create Listing</h2>
                <h4>Design and launch your product with ease and efficiency</h4>
            </div>
            <?php if (isset($_SESSION['errors'])): ?>
                <div class="errors" style="color: red; margin-bottom: 20px;">
                    <?php foreach ($_SESSION['errors'] as $error): ?>
                        <p>⚠️ <?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                    <?php unset($_SESSION['errors']); ?>
                </div>
            <?php endif; ?>
            <form id="productForm" method="POST" action="listing.php" enctype="multipart/form-data"> 
                <div id="productDescription">
                    <fieldset class="form">
                        <legend>Product Name:</legend>
                        <input type="text" name="productName" id="productName" placeholder="Product Name" required>
                        <div class="error" id="productName-error" aria-live="polite"></div>
                    </fieldset>

                    <fieldset class="form description">
                        <legend>Description:</legend>
                        <textarea id="description" name="description" placeholder="Write product description.." required></textarea>
                        <div class="error" id="description-error" aria-live="polite"></div>
                    </fieldset> 
                    
                    <fieldset class="form">
                        <legend>Category:</legend>
                        <input type="text" name="productCategory" id="productCategory" list="category" placeholder="Enter Category Name" required>
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
                        <input type="text" name="subCategory" id="subCategory" list="sub" placeholder="Enter Sub-Category Name">
                        <datalist id="sub">
                            <option value="">
                        </datalist>
                    </fieldset>   
                    
                    <fieldset class="form">
                        <legend>Condition:</legend>
                        <input type="text" name="quality" id="quality" list="condition" placeholder="Enter Product Condition" required>
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
                        <input type="number" name="price" id="price" placeholder="Price" min="0" step="0.01" required>
                        <div class="error" id="price-error" aria-live="polite"></div>
                    </fieldset> 

                    <fieldset class="form">
                        <legend>Delivery Method:</legend>
                        <input type="text" name="delivery" id="delivery" list="deliveryMethod" placeholder="Enter Delivery Method" required>
                        <datalist id="deliveryMethod">
                            <option value="MeetUp">
                            <!-- <option value="Courier Service">
                            <option value="Drop Off">
                            <option value="Third-Party Delivery Service (Uber, DoorDash)"> -->
                        </datalist>
                        <div class="error" id="delivery-error" aria-live="polite"></div>
                    </fieldset>

                    <fieldset class="form form-file-upload">
                        <legend>Product Image:</legend>
                        <label for="productPicture" class="file-upload-label">
                            <i class="fas fa-image"></i>
                            <span>Upload Product Pictures</span>
                        </label>
                        <input type="file" name="productPicture[]" id="productPicture" accept="image/jpeg,image/png,image/jpg" multiple required>
                        <div id="imagePreview" style="display: flex; flex-wrap: wrap; gap: 10px; margin-top: 10px;"></div>
                    </fieldset> 
                    <div class="error" id="productPicture-error" aria-live="polite"></div>
                    
                    <!-- <fieldset class="form form-file-upload">
                        <legend>Product Video:</legend>
                        <label for="productVideo" class="file-upload-label">
                            <i class="fas fa-video"></i>
                            <span>Upload Product Video (If available)</span>
                        </label>
                        <input type="file" name="productVideo" id="productVideo" accept="video/mp4,video/webm,video/ogg">
                        <div id="videoPreview" style="margin-top: 10px;"></div>
                    </fieldset>  
                    <div class="error" id="productVideo-error" aria-live="polite"></div> -->
                    
                    <div id="next">
                        <button type="submit" class="normal">Upload</button>
                    </div>
                </div>
            </form>
        </div>
    </section>
    <script src="../assets/js/listingValidation.js"></script>
</body>
</html>