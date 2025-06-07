<?php
    session_start();
    require_once("../includes/db_connection.php");

    if (!isset($_SESSION['userId'])) {
        $_SESSION['errors'] = ['You must be logged in.'];
        header('Location: ../pages/signIn.php?redirect=shipping.php');
        exit();
    }

    $userId = $_SESSION['userId'];

    if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
        $_SESSION['errors'] = ['Invalid product ID'];
        header('Location: ../pages/listing.php'); 
        exit();
    }

    $productId = $_GET['id'];

    try {
        $stmt = $conn->prepare("SELECT userId, street_address, suburb, postal_code, province, city FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            $_SESSION['errors'] = ['You are not authorized to list this product.'];
            header('Location: ../pages/listing.php');
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['errors'] = ["Error fetching user's location: " . $e->getMessage()];
        header('Location: ../pages/listing.php');
        exit();
    }

    $formData = $_SESSION['form_data'] ?? [
        'street_address' => $user['street_address'],
        'suburb' => $user['suburb'],
        'postal_code' => $user['postal_code'],
        'province' => $user['province'],
        'city' => $user['city'],
    ];
    $errors = $_SESSION['errors'] ?? [];
    unset($_SESSION['form_data'], $_SESSION['errors']);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $street_address = $_POST['street_address'] ?? '';
        $suburb = $_POST['suburb'] ?? '';
        $postal_code = $_POST['postal_code'] ?? '';
        $province = $_POST['province'] ?? '';
        $city = $_POST['city'] ?? '';

        $errors = [];

        if (empty($street_address)) {
            $errors[] = 'Street Address is required.';
        } 

        if (empty($postal_code)) {
            $errors[] = 'Postal Code is required.';
        }

        if (empty($province)) {
            $errors[] = 'Province is required.';
        }

        if (empty($city)) {
            $errors[] = 'City is required.';
        }

        if (!empty($errors)) {
            $_SESSION['errors'] = $errors;
            $_SESSION['form_data'] = [
                'street_address' => $street_address,
                'suburb' => $suburb,
                'postal_code' => $postal_code,
                'province' => $province,
                'city' => $city
            ];
            header("Location: shipping.php?id=$userId");
            exit();
        }

        try {
            $conn->beginTransaction();
            $stmt = $conn->prepare("UPDATE products SET 
                street_address = ?, suburb = ?, postal_code = ?, province = ?, 
                city = ? WHERE id = ? AND userId = ?");
            $stmt->execute([
                $street_address,
                $suburb,
                $postal_code,
                $province,
                $city,
                $productId,
                $userId
            ]);
            $conn->commit();
            $_SESSION['message'] = "Shipping details stored successfully.";
            header('Location: ../pages/billing.php');
            exit();
        } catch (PDOException $e) {
            $conn->rollback();
            $_SESSION['errors'] = ["Error storing shipping details: " . $e->getMessage()];
            header("Location: shipping.php?id=$userId");
            exit();
        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<?php 
    $pageTitle = "Shipping Details";
    include('../includes/head.php'); 
?>
<head>
    <link rel="stylesheet" href="../assets/css/listing.css"> 
</head>
<body class="listing">
    <?php include('../includes/header.php'); ?>
    <section id="productListing">
        <div id="productHeading">
            <h1>Shipping</h1>
            <p>Enter your shipping details for shipment</p>
        </div>
        <div id="product">
            <?php if (!empty($errors)): ?>
                <div class="errors" style="color: red; margin-bottom: 20px;">
                    <?php foreach ($errors as $error): ?>
                        <p>⚠️ <?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            <form id="productForm" method="POST" action="shipping.php?id=<?php echo $productId; ?>" enctype="multipart/form-data"> 
                <div id="productDescription">
                    <fieldset class="form">
                        <legend>Street Address:</legend>
                        <input type="text" name="street_address" id="street_address" value="<?php echo htmlspecialchars($formData['street_address']); ?>" required>
                        <div class="error" id="street_address-error" aria-live="polite"></div>
                    </fieldset>

                    <fieldset class="form">
                        <legend>Suburb:</legend>
                        <input type="text" id="suburb" name="suburb" value="<?php echo htmlspecialchars($formData['suburb']); ?>">
                        <div class="error" id="suburb-error" aria-live="polite"></div>
                    </fieldset> 

                    <fieldset class="form">
                        <legend>Postal Code:</legend>
                        <input type="text" id="postal_code" name="postal_code" value="<?php echo htmlspecialchars($formData['postal_code']); ?>" required>
                        <div class="error" id="postal_code-error" aria-live="polite"></div>
                    </fieldset> 

                    <fieldset class="form">
                        <legend>Province:</legend>
                        <input type="text" name="province" id="province" list="provinces" value="<?php echo htmlspecialchars($formData['province']); ?>" required>
                        <datalist id="provinces">
                            <option value="Eastern Cape">Eastern Cape</option>
                            <option value="Free State">Free State</option>
                            <option value="Gauteng">Gauteng</option>
                            <option value="KwaZulu-Natal">KwaZulu-Natal</option>
                            <option value="Limpopo">Limpopo</option>
                            <option value="Mpumalanga">Mpumalanga</option>
                            <option value="Northern Cape">Northern Cape</option>
                            <option value="North West">North West</option>
                            <option value="Western Cape">Western Cape</option>
                        </datalist>
                        <div class="error" id="province-error" aria-live="polite"></div>
                    </fieldset>

                    <fieldset class="form">
                        <legend>City:</legend>
                        <input type="text" name="city" id="city" list="cities" value="<?php echo htmlspecialchars($formData['city']); ?>" required>
                        <datalist id="cities">
                            <?php
                                $provinceCityMap = [
                                    'Eastern Cape' => ['Port Elizabeth', 'East London'],
                                    'Free State' => ['Bloemfontein'],
                                    'Gauteng' => ['Johannesburg', 'Pretoria'],
                                    'KwaZulu-Natal' => ['Durban'],
                                    'Limpopo' => ['Polokwane'],
                                    'Mpumalanga' => ['Nelspruit'],
                                    'Northern Cape' => ['Kimberley'],
                                    'North West' => ['Mahikeng'],
                                    'Western Cape' => ['Cape Town']
                                ];
                                if (!empty($formData['province']) && isset($provinceCityMap[$formData['province']])) {
                                    foreach ($provinceCityMap[$formData['province']] as $cityOption) {
                                        $selected = ($formData['city'] === $cityOption) ? 'selected' : '';
                                        echo "<option value=\"$cityOption\">$cityOption</option>";
                                    }
                                }
                            ?>
                        </datalist>
                        <div class="error" id="city-error" aria-live="polite"></div>
                    </fieldset>   
                    
                    <div id="next">
                        <button type="submit" class="normal">Next</button>
                    </div>
                </div>
            </form>
        </div>
    </section>
    <script src="../assets/js/editProductValidation.js"></script>
</body>
</html>