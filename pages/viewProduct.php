<?php
    session_start();

     error_log("Session variables in viewProduct: " . print_r($_SESSION, true));

    require_once '../includes/db_connection.php';

    $productId = isset($_GET['productId']) ? (int)$_GET['productId'] : 0;

    try {
        // Get product data first
        $stmt = $conn->prepare("
            SELECT p.id, p.productName AS name, p.description, p.price, p.productCategory AS category,
                p.productPicture AS image_path, p.productVideo AS video_path, p.userId AS user,
                u.firstname, u.lastname AS userLastName, u.profile_picture, u.city AS location
            FROM products p
            LEFT JOIN users u ON p.userId = u.userId
            WHERE p.id = :productId 
        ");
        $stmt->execute(['productId' => $productId]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!empty($product['image_path'])) {
            $decoded = json_decode($product['image_path'], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded) && !empty($decoded)) {
                $images = array_map(function($img) {
                    return '../' . ltrim(htmlspecialchars($img), '/');
                }, $decoded);
                $imagePath = $images[0];
            } else {
                $cleanPath = ltrim($product['image_path'], '/');
                $imagePath = '../' . htmlspecialchars($cleanPath);
                $images = [$imagePath];
            }
        }

        $videos = [];
        $videoPath = '';
        if (!empty($product['video_path'])) {
            $cleanPath = ltrim($product['video_path'], '/');
            $videoPath = '../' . htmlspecialchars($cleanPath);
            $videos = [$videoPath];
        }

        $profileImageSrc = '../assets/images/Welcome/default_profile.jpg';
        if (!empty($product['profile_picture'])) {
            $cleanPath = ltrim($product['profile_picture'], '/');
            $profileImageSrc = '../' . htmlspecialchars($cleanPath) . '?' . mt_rand();
        }

        // Get reviews separately
        $stmt = $conn->prepare("
            SELECT r.message, r.rating, r.created_at AS date,
                u.firstname, u.lastname AS userLastName
            FROM reviews r
            LEFT JOIN users u ON r.userId = u.userId  
            WHERE r.productId = :productId
            ORDER BY r.created_at DESC
        ");
        $stmt->execute(['productId' => $productId]);
        $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($productId <= 0 || !$product) {
            $_SESSION['errors'] = ["No product found for ID: $productId"];
        }
    } catch (PDOException $e) {
        $_SESSION['errors'] = ["Error fetching products: " . $e->getMessage()];
        $product = null;
        $reviews = [];
    }

    // Calculate ratings from reviews array
    $ratings = array_column($reviews, 'rating');
    $totalReviews = count(array_filter($ratings));
    $averageRating = $totalReviews > 0 ? array_sum($ratings) / $totalReviews : 0;
    $ratingCounts = array_count_values(array_filter($ratings));
?>

<!DOCTYPE html>
<html lang="en">
<?php
$pageTitle = "View Product";
include('../includes/head.php');
?>
<head>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <link rel="stylesheet" href="../assets/css/viewProduct.css">
</head>
<body id="view">
    <?php include('../includes/header.php'); ?>

    <section class="section-p1" id="back">
        <a href="../pages/shop.php"><i class="fa-solid fa-arrow-left"></i> Back to Shop</a>
    </section>

    <?php if (isset($_SESSION['errors']) && !empty($_SESSION['errors'])): ?>
        <section class="section-p1">
            <div class="error-messages">
                <?php foreach ($_SESSION['errors'] as $error): ?>
                    <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
                <?php unset($_SESSION['errors']); ?>
            </div>
        </section>
    <?php endif; ?>

    <?php if ($product): ?>
        <section id="seller" class="section-p1">
            <?php
            // Ensure we're using the seller's profile image, not the current user's
            $sellerProfileImage = '../assets/images/Welcome/default_profile.jpg';
            if (!empty($product['profile_picture'])) {
                $cleanPath = ltrim($product['profile_picture'], '/');
                $sellerProfileImage = '../' . htmlspecialchars($cleanPath) . '?' . mt_rand();
            }
            ?>
            <img src="<?php echo $sellerProfileImage; ?>" alt="Seller Profile" width="80" height="80" style="border-radius: 50%;">
            <h5><a href="../pages/profile.php"><?php echo htmlspecialchars($product['firstname'] . ' ' . $product['userLastName']); ?></a></h5>
            <p><?php echo htmlspecialchars($product['location']); ?></p>
        </section>

        <section id="prodetails" class="section-p1">
            <div class="single-pro-image">
                <div id="images">
                    <img src="<?php echo $imagePath; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                    <?php if (!empty($product['video_path'])): ?>
                        <video id="main-video" controls style="display: none;" width="100%">
                            <source src="<?php echo $videoPath; ?>" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                    <?php endif; ?>
                    <?php if (count($images) > 1 || !empty($videoPath)): ?>
                        <div class="small-img-group">
                            <?php foreach ($images as $img): ?>
                                <div class="small-img-col">
                                    <img src="<?php echo $img; ?>" alt="Thumbnail" width="100%" class="small-img">
                                </div>
                            <?php endforeach; ?>
                            <?php if (!empty($videoPath)): ?>
                                <div class="small-img-col">
                                    <img src="<?php echo $videoPath; ?>" alt="Video Thumbnail" width="100%" class="small-img">
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="single-pro-details"> 
                <div id="product_details">
                    <h5><a href="#"><?php echo htmlspecialchars($product['category']); ?></a></h5>
                    <h4><?php echo htmlspecialchars($product['name']); ?></h4>
                    <h2>R<?php echo number_format($product['price'], 2); ?></h2>
                </div>
                <div id="btn" class="normal">
                    <button id="contactSellerBtn" class="normal">Contact Seller</button>
                    <form action="../pages/payment.php" method="post" style="display: inline;">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        <input type="hidden" name="quantity" value="1" id="payNowQuantity">
                        <button type="submit" class="normal">Pay Now</button>
                    </form>
                    <?php if (!empty($product)): ?>
                        <form action="../pages/add_to_cart.php" method="POST">
                            <input type="hidden" name="product_id" value="<?= htmlspecialchars($product['id']) ?>">
                            <input type="number" name="quantity" value="1" min="1" required id="quan">
                            <input type="hidden" name="redirect_url" value="<?= htmlspecialchars($_SERVER['REQUEST_URI']) ?>">
                            <button type="submit" class="normal">Add to Cart</button>
                        </form>
                    <?php else: ?>
                        <p>Product not found or unavailable.</p>
                    <?php endif; ?>
                </div>

                <div id="messageContainer">
                    <div id="messageHeader">
                        <h3>Chat with <span id="receiverName"></span></h3>
                        <button onclick="closeChat()" style="float: right; background: #dc3545; color: white; border: none; padding: 5px 10px; border-radius: 4px;">Close</button>
                    </div>
                    
                    <div id="messagesList"></div>
                    
                    <div class="message-input">
                        <input type="text" id="messageText" placeholder="Type a message..." onkeypress="checkEnter(event)">
                        <button onclick="sendMessage()" class="normal">Send</button>
                    </div>
                </div>

                <div id="details">
                    <h4>Description</h4>
                    <span><?php echo htmlspecialchars($product['description']); ?></span>
                </div>
                <div id="places-to-meet">
                    <div id="places">

                    </div>
                </div>
                <div id="comment">
                    <h4>Leave a Review</h4>
                    <hr>
                    <form id="reviewForm" action="../pages/review.php?productId=<?php echo $product['id']; ?>" method="post">
                        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                        <input type="text" id="reviewer" name="reviewer" placeholder="Enter your name" required>
                        <textarea name="message" id="message" placeholder="Enter Message" required></textarea>
                        <h5>
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fa-solid fa-star stars submit_star" data-rating="<?php echo $i; ?>"></i>
                            <?php endfor; ?>
                        </h5>
                        <input type="hidden" name="rating" id="selected_rating" value="0">
                        <button type="submit" class="normal">Submit</button>
                    </form>
                </div>
            </div>
        </section>

        <section id="reviews">
            <div id="averageRating">
                <div id="total">
                    <h3>Product Ratings & Reviews</h3>
                    <hr>
                    <h4 id="totalRatings"><?php echo number_format($averageRating, 1); ?></h4>
                    <div id="stars">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="fa-solid fa-star main_star <?php echo $i <= ceil($averageRating) ? 'text-warning' : 'star-light'; ?>"></i>
                        <?php endfor; ?>
                    </div>
                    <span id="total_review_rating"><?php echo $totalReviews; ?> reviews</span>
                </div>
                <div class="progress">
                    <?php for ($i = 5; $i >= 1; $i--): ?>
                        <div class="holder">
                            <div>
                                <div class="progress-left"><?php echo $i; ?> <i class="fa-solid fa-star"></i></div>
                                <div class="progress-right">
                                    <span class="total_<?php echo $i; ?>_star_review"><?php echo isset($ratingCounts[$i]) ? $ratingCounts[$i] : 0; ?></span> Reviews
                                </div>
                                <div class="progress2">
                                    <div class="progress-bar" id="<?php echo $i; ?>_star_progress" style="width: <?php echo $totalReviews > 0 ? (isset($ratingCounts[$i]) ? ($ratingCounts[$i] / $totalReviews) * 100 : 0) : 0; ?>%;"></div> 
                                </div>
                            </div>
                        </div>
                    <?php endfor; ?>
                </div>
            </div>
        </section>

        <section id="displayReviews" class="section-p1">
            <?php if (!empty($reviews)): ?>
                <?php foreach ($reviews as $review): ?>
                    <?php if (!empty($review['message'])): ?>
                        <div class="review-item">
                            <p id="name"><?php echo htmlspecialchars($review['firstname'] . ' ' . $review['userLastName']); ?></p>
                            <p id="reviewStar"><?php echo htmlspecialchars($review['rating']); ?> <i class="fa-solid fa-star"></i></p>
                            <span id="review"><?php echo htmlspecialchars($review['message']); ?></span>
                            <div id="like">
                                <button><i class="fa-solid fa-thumbs-up"></i></button>
                                <input type="number" id="numLike" name="numLike" value="0">
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No reviews yet.</p>
            <?php endif; ?>
        </section>

    <?php endif; ?>

    <section class="similar-products-section">
        <h2>SIMILAR Category PRODUCTS</h2>
        <?php
        if ($product) {
            $category = $product['category'] ?? '';
            
            $stmt = $conn->prepare("SELECT id, productName AS name, price, productCategory AS category, productPicture AS image_path FROM products WHERE productCategory = ? AND id != ? LIMIT 4");
            $stmt->execute([$category, $productId]);
            $similarProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        ?>
        
        <?php if (!empty($similarProducts)): ?>
            <div class="similar-products-grid">
                <?php foreach ($similarProducts as $similarProduct): ?>
                    <div class="product-item">
                        <?php
                        // Handle image path similar to main product
                        $similarImagePath = '../assets/images/default-product.jpg'; // Default image
                        if (!empty($similarProduct['image_path'])) {
                            $decoded = json_decode($similarProduct['image_path'], true);
                            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded) && !empty($decoded)) {
                                $similarImagePath = '../' . ltrim(htmlspecialchars($decoded[0]), '/');
                            } else {
                                $cleanPath = ltrim($similarProduct['image_path'], '/');
                                $similarImagePath = '../' . htmlspecialchars($cleanPath);
                            }
                        }
                        ?>
                        <a href="viewProduct.php?productId=<?php echo $similarProduct['id']; ?>">
                            <img src="<?php echo $similarImagePath; ?>" 
                                alt="<?php echo htmlspecialchars($similarProduct['name']); ?>"
                                loading="lazy">
                            <h4><?php echo htmlspecialchars($similarProduct['name']); ?></h4>
                            <p>R<?php echo number_format($similarProduct['price'], 2); ?></p>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-products-message">
                <p>No similar products found in this category.</p>
            </div>
        <?php endif; ?>
    </section>

    <?php include('../includes/footer.php'); ?>

    <script>
        // Debug function to check session
        function getCurrentUserId() {
            const userId = <?php 
                if (isset($_SESSION['userId'])) {
                    echo $_SESSION['userId'];
                } elseif (isset($_SESSION['user_id'])) {
                    echo $_SESSION['user_id'];
                } elseif (isset($_SESSION['id'])) {
                    echo $_SESSION['id'];
                } else {
                    echo 'null';
                }
            ?>;
            
            console.log('Current user ID:', userId);
            return userId;
        }

        function closeChat() {
            document.getElementById('messageContainer').style.display = 'none';
            if (typeof messageInterval !== 'undefined' && messageInterval) {
                clearInterval(messageInterval);
            }
        }

        // Initialize contact seller functionality
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Page loaded, current user ID:', getCurrentUserId());
            
            const contactBtn = document.getElementById('contactSellerBtn');
            if (contactBtn) {
                contactBtn.addEventListener('click', function() {
                    console.log('Contact seller button clicked');
                    
                    // Check if user is logged in
                    if (getCurrentUserId() === null) {
                        alert('Please log in to contact the seller.');
                        return;
                    }
                    
                    const sellerId = <?php echo $product ? $product['user'] : 0; ?>;
                    const sellerName = "<?php echo $product ? htmlspecialchars($product['firstname'] . ' ' . $product['userLastName']) : ''; ?>";
                    
                    console.log('Seller ID:', sellerId, 'Seller Name:', sellerName);
                    
                    // Check if trying to contact themselves
                    if (getCurrentUserId() === sellerId) {
                        alert('You cannot contact yourself.');
                        return;
                    }
                    
                    // Show the message container
                    document.getElementById('messageContainer').style.display = 'block';
                    
                    // Start the chat (this function is in messaging.js)
                    if (typeof startChat === 'function') {
                        startChat(sellerId, sellerName);
                    } else {
                        console.error('startChat function not found - check if messaging.js is loaded');
                    }
                });
            }
        });
    </script>
    
    <script src="../assets/js/review.js"></script>
    <script src="../assets/js/messaging.js"></script> 
    <script src="../assets/js/viewProduct.js"></script>
</body>
</html>