<?php
    session_start();
    require_once("../includes/db_connection.php");

    // Check if user is logged in
    if (!isset($_SESSION['userId'])) {
        $_SESSION['errors'] = ['You must be logged in to view profiles.'];
        header('Location: ../pages/signIn.php?redirect=' . urlencode($_SERVER['REQUEST_URI']));
        exit();
    }

    // Get the profile user ID - either from URL parameter or current session
    $profileUserId = isset($_GET['userId']) ? (int)$_GET['userId'] : $_SESSION['userId'];
    $loggedInUserId = $_SESSION['userId'];
    $isOwnProfile = ($profileUserId === $loggedInUserId);

    // Initialize variables with defaults
    $userName = '';
    $bio = '';
    $created_at = '';
    $profileImageSrc = '../assets/images/default-profile.png';
    $ratings = 'No ratings yet';

    try {
        // Get user information for the profile we're viewing
        $sqlUser = "SELECT firstname, lastname, profile_picture, created_at, bio FROM users WHERE userId = :userId";
        $sqlReviews = "SELECT rating FROM reviews WHERE userId = :userId";
        $stmtUser = $conn->prepare($sqlUser);
        $stmtReviews = $conn->prepare($sqlReviews);
        $stmtUser->bindParam(':userId', $profileUserId, PDO::PARAM_INT);
        $stmtReviews->bindParam(':userId', $profileUserId, PDO::PARAM_INT);
        $stmtUser->execute();
        $stmtReviews->execute();

        if ($stmtUser->rowCount() > 0) {
            $rowUser = $stmtUser->fetch(PDO::FETCH_ASSOC);
            $userName = $rowUser['firstname'] . ' ' . $rowUser['lastname'];
            $bio = $rowUser['bio'] ?? '';
            $created_at = date('Y-m-d', strtotime($rowUser['created_at']));
            
            // Check if user has uploaded profile picture
            $sqlImg = "SELECT status FROM profileimg WHERE userId = :userId";
            $stmtImg = $conn->prepare($sqlImg);
            $stmtImg->bindParam(':userId', $profileUserId, PDO::PARAM_INT);
            $stmtImg->execute();

            if ($stmtImg->rowCount() > 0) {
                $rowImg = $stmtImg->fetch(PDO::FETCH_ASSOC);
                if ($rowImg['status'] == 0 && !empty($rowUser['profile_picture'])) {
                    $profileImageSrc = "../" . $rowUser['profile_picture'] . "?" . mt_rand();
                }
            }
        } else {
            // User not found
            $_SESSION['errors'] = ['User not found.'];
            header('Location: ../root/index.php');
            exit();
        }

        // Calculate average rating
        if ($stmtReviews->rowCount() > 0) {
            $ratingsArray = $stmtReviews->fetchAll(PDO::FETCH_COLUMN);
            $averageRating = array_sum($ratingsArray) / count($ratingsArray);
            $ratings = number_format($averageRating, 1) . '/5 ⭐';
        }

    } catch (PDOException $e) {
        error_log("Profile error: " . $e->getMessage());
        $_SESSION['errors'] = ['Error loading profile.'];
        header('Location: ../root/index.php');
        exit();
    }

    // Get user's products
    $productsPerPage = 16;
    $page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($page - 1) * $productsPerPage;

    try {
        // Count user's products
        $countStmt = $conn->prepare("SELECT COUNT(*) FROM products WHERE userId = :userId");
        $countStmt->bindParam(':userId', $profileUserId, PDO::PARAM_INT);
        $countStmt->execute();
        $totalProducts = $countStmt->fetchColumn();
        $totalPages = ceil($totalProducts / $productsPerPage);

        // Get user's products
        $stmt = $conn->prepare("
            SELECT p.id, p.productName AS name, p.price, p.productCategory AS category, 
                p.productPicture AS image_path, p.productVideo AS video_path, 
                u.city AS location,
                (SELECT AVG(r.rating) FROM reviews r WHERE r.productId = p.id) AS average_rating
            FROM products p
            LEFT JOIN users u ON p.userId = u.userId
            WHERE p.userId = :userId
            ORDER BY p.created_at DESC
            LIMIT :limit OFFSET :offset
        ");
        $stmt->bindParam(':userId', $profileUserId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $productsPerPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $_SESSION['errors'] = ["Error fetching products: " . $e->getMessage()];
        $result = [];
        $totalProducts = 0;
        $totalPages = 0;
    }
?>

<!DOCTYPE html>
<html lang="en">
<?php 
    $pageTitle = $isOwnProfile ? "My Profile" : $userName . "'s Profile";
    include('../includes/head.php'); 
?>
<head>
    <link rel="stylesheet" href="../assets/css/profile.css"> 
    <link rel="stylesheet" href="../assets/css/shop.css">
</head>
<body id="profile">
    <?php include('../includes/header.php'); ?>
    
    <!-- Add a header to show whose profile this is -->
    <?php if (!$isOwnProfile): ?>
        <div style="background: #f8f9fa; padding: 10px; text-align: center; margin-bottom: 20px;">
            <h3>Viewing <?php echo htmlspecialchars($userName); ?>'s Profile</h3>
            <a href="../root/index.php" style="color: #007bff;">← Back to Home</a>
        </div>
    <?php endif; ?>
    
    <section id="top">
        <div id="top-left">
            <div id="user">
                <div id="image">
                    <img src="<?php echo htmlspecialchars($profileImageSrc); ?>" alt="Profile Picture" class="profile-pic">
                </div>
                <div id="name">
                    <span class="username"><?php echo htmlspecialchars($userName); ?></span>
                </div>
            </div>
            <div>
                <p id="rating"><?php echo htmlspecialchars($ratings); ?></p>
            </div>
        </div>
        <div id="top-right">
            <div id="num_sold_products">
                <p><?php echo $totalProducts; ?> Products Listed</p>
            </div>
            <div id="describe_user">
                <div id="reliable">
                    <i class="fa-solid fa-shield-heart"></i>
                    <h6>Reliable</h6>
                </div>
                <div id="fast_responder">
                    <i class="fa-solid fa-clock"></i>
                    <h6>Fast Response</h6>
                </div>
            </div>
        </div>
    </section>

    <section id="middle">
        <h4>About</h4>
        <div id="time">
            <i class="fa-solid fa-calendar"></i>
            <p>Member since <?php echo htmlspecialchars($created_at); ?></p>
        </div>
        <div id="description">
            <h5>Description</h5>
            <span id="bio"><?php echo htmlspecialchars($bio); ?></span>
        </div>
        
        <!-- Show action buttons only if viewing someone else's profile -->
        <?php if (!$isOwnProfile): ?>
            <div style="margin-top: 20px;">
                <a href="../pages/conversation.php?userId=<?php echo $_SESSION['userId']; ?>&target=<?php echo $profileUserId; ?>" 
                   class="btn btn-primary" style="margin-right: 10px;">
                    <i class="fa-solid fa-message"></i> Send Message
                </a>
                <!-- Add other action buttons as needed -->
            </div>
        <?php endif; ?>
    </section>

    <section id="bottom">
        <div id="myProducts">
            <h2><?php echo $isOwnProfile ? 'My Products' : $userName . "'s Products"; ?></h2>
            
            <div class="products-grid">
                <?php if (!empty($result)): ?>
                    <?php foreach ($result as $product): ?>
                        <div class="product-card">
                            <div class="product-image">
                                <?php 
                                $imagePath = '../assets/images/default.jpg';
                                if (!empty($product['image_path'])) {
                                    $images = json_decode($product['image_path'], true);
                                    if (is_array($images) && !empty($images)) {
                                        $cleanPath = ltrim($images[0], '/');
                                        $imagePath = '../' . htmlspecialchars($cleanPath);
                                    } elseif (!is_array($images)) {
                                        $cleanPath = ltrim($product['image_path'], '/');
                                        $imagePath = '../' . htmlspecialchars($cleanPath);
                                    }
                                }
                                ?>
                                <img src="<?php echo $imagePath; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            </div>
                            <div class="product-info">
                                <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                                <p class="price">R<?php echo number_format($product['price'], 2); ?></p>
                                <p class="category"><?php echo htmlspecialchars($product['category']); ?></p>
                                <?php if ($product['location']): ?>
                                    <p class="location"><?php echo htmlspecialchars($product['location']); ?></p>
                                <?php endif; ?>
                                <?php if ($product['average_rating']): ?>
                                    <p class="rating"><?php echo number_format($product['average_rating'], 1); ?>/5 ⭐</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p><?php echo $isOwnProfile ? "You haven't listed any products yet." : $userName . " hasn't listed any products yet."; ?></p>
                <?php endif; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="?userId=<?php echo $profileUserId; ?>&page=<?php echo $i; ?>" 
                           <?php echo ($i == $page) ? 'class="active"' : ''; ?>>
                           <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
</body>
</html>