<?php
session_start();
require_once 'includes/db_connection.php';

// Pagination settings
$productsPerPage = 20;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $productsPerPage;

try {
    $countStmt = $conn->prepare("SELECT COUNT(*) FROM products");
    $countStmt->execute();
    $totalProducts = $countStmt->fetchColumn();
    $totalPages = ceil($totalProducts / $productsPerPage);

    $stmt = $conn->prepare("
        SELECT p.id, p.productName AS name, p.price, p.productCategory AS category, 
            p.productPicture AS image_path, p.productVideo AS video_path, 
            u.city AS location,
            (SELECT AVG(r.rating) FROM reviews r WHERE r.productId = p.id) AS average_rating
        FROM products p
        LEFT JOIN users u ON p.userId = u.userId
        ORDER BY p.created_at DESC
        LIMIT :limit OFFSET :offset
    ");
    $stmt->bindValue(':limit', $productsPerPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $conn->prepare("
        SELECT r.message, r.rating, r.created_at AS date,
            u.firstname, u.lastname AS userLastName
        FROM reviews r
        LEFT JOIN users u ON r.userId = u.userId  
        ORDER BY r.created_at DESC
    ");
    $stmt->execute();
    $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $_SESSION['errors'] = ["Error fetching products: " . $e->getMessage()];
    $result = [];
}
?>


<!DOCTYPE html>
<html lang="en">
<?php 
    include('includes/indexHead.php');
?>
<head>
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="assets/css/shop.css">
</head>
<body>
    <?php include('includes/indexHeader.php'); ?>

    <?php if (isset($_SESSION['cart_success'])): ?>
        <div id="cart-notification" class="notification success-notification show">
            <div class="notification-content">
                <i class="fa-solid fa-check-circle"></i>
                <span>Product added to cart successfully!</span>
            </div>
            <button class="notification-close" onclick="closeNotification()">
                <i class="fa-solid fa-times"></i>
            </button>
        </div>
        <?php unset($_SESSION['cart_success']); ?>
    <?php endif; ?>

    <section id="section1" class="padding1">
        <div class="hero-backdrop">
            <img src="assets/images/Headers/about.jpg" alt="Hero Background">
            <div class="backdrop-overlay"></div>
        </div>

        <div id="about">
            <div id="baobab">
                <h2>
                    <img src="assets/images/Logo/Baobab_favicon.png" alt="">
                    SA <span> BEST </span> MARKETPLACE
                </h2>
            </div>
            <div id="moreInfo">
                <h1>Buy & Sell <span>Anything</span> in South Africa</h1>
                <h5>Join <span>thousands</span> of South Africans buying and selling goods locally, safe, simple, and hassle-free.</h5>
            </div>
            <div id="action">
                <button class="normal"><a href="pages/shop.php">Shop Now <i class="fa-solid fa-arrow-right"></i></a></button>
                <button class="white"><a href="pages/listing.php">Start Selling</a></button>
            </div>
            <p>Trusted by Customers</p>
        </div>
        <div id="category">
            <div id="category-carousel">
                <div class="cat">
                    <div class="content">
                        <div class="image">
                            <img src="assets/images/Headers/electronics.jpg" alt="Electronic Category Image">
                        </div>
                        <div class="cat-name">
                            <div>
                                <h4 id="name">ELECTRONICS</h4>
                                <p><span id="num-item"></span><i class="fa-solid fa-plus"></i> Items</p>
                            </div>
                            <div>
                                <a href="pages/electronics.php"><i class="bi bi-arrow-up-right-circle"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="cat">
                    <div class="content">
                        <div class="image">
                            <img src="assets/images/Headers/vehicle.jpg" alt="Vehicle Category Image">
                        </div>
                        <div class="cat-name">
                            <div>
                                <h4 id="name">VEHICLE</h4>
                                <p><span id="num-item"></span><i class="fa-solid fa-plus"></i> Items</p>
                            </div>
                            <div>
                                <a href="pages/vehicle.php"><i class="bi bi-arrow-up-right-circle"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="cat">
                    <div class="content">
                        <div class="image">
                            <img src="assets/images/Headers/home.png" alt="Home Category Image">
                        </div>
                        <div class="cat-name">
                            <div>
                                <h4>HOME</h4>
                                <p><span id="num-item"></span><i class="fa-solid fa-plus"></i> Items</p>
                            </div>
                            <div>
                                <a href="pages/home.php"><i class="bi bi-arrow-up-right-circle"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="cat">
                    <div class="content">
                        <div class="image">
                            <img src="assets/images/Headers/fashion.jpg" alt="Fashion Category Image">
                        </div>
                        <div class="cat-name">
                            <div>
                                <h4>FASHION</h4>
                                <p><span id="num-item"></span><i class="fa-solid fa-plus"></i> Items</p>
                            </div>
                            <div>
                                <a href="pages/fashion.php"><i class="bi bi-arrow-up-right-circle"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="cat">
                    <div class="content">
                        <div class="image">
                            <img src="assets/images/Headers/furniture2.jpg" alt="Furniture Category Image">
                        </div>
                        <div class="cat-name">
                            <div>
                                <h4>FURNITURE</h4>
                                <p><span id="num-item"></span><i class="fa-solid fa-plus"></i> Items</p>
                            </div>
                            <div>
                                <a href="pages/furniture.php"><i class="bi bi-arrow-up-right-circle"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="cat">
                    <div class="content">
                        <div class="image">
                            <img src="assets/images/Headers/toys2.jpeg" alt="Toys & Games Category Image">
                        </div>
                        <div class="cat-name">
                            <div>
                                <h4>TOYS & GAMES</h4>
                                <p><span id="num-item"></span><i class="fa-solid fa-plus"></i> Items</p>
                            </div>
                            <div>
                                <a href="pages/toys-games.php"><i class="bi bi-arrow-up-right-circle"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="cat">
                    <div class="content">
                        <div class="image">
                            <img src="assets/images/Headers/sports.jpg" alt="Outdoor & Sports Category Image">
                        </div>
                        <div class="cat-name">
                            <div>
                                <h4>OUTDOOR & SPORTS</h4>
                                <p><span id="num-item"></span><i class="fa-solid fa-plus"></i> Items</p>
                            </div>
                            <div>
                                <a href="pages/outdoor-sports.php"><i class="bi bi-arrow-up-right-circle"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="cat">
                    <div class="content">
                        <div class="image">
                            <img src="assets/images/Headers/antiques.jpg" alt="Antiques & Collectibles Category Image">
                        </div>
                        <div class="cat-name">
                            <div>
                                <h4>ANTIQUES & COLLECTIBLES</h4>
                                <p><span id="num-item"></span><i class="fa-solid fa-plus"></i> Items</p>
                            </div>
                            <div>
                                <a href="pages/antiques-collectibles.php"><i class="bi bi-arrow-up-right-circle"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="cat">
                    <div class="content">
                        <div class="image">
                            <img src="assets/images/Headers/books2.jpg" alt="Books Category Image">
                        </div>
                        <div class="cat-name">
                            <div>
                                <h4>Books</h4>
                                <p><span id="num-item"></span><i class="fa-solid fa-plus"></i> Items</p>
                            </div>
                            <div>
                                <a href="pages/books.php"><i class="bi bi-arrow-up-right-circle"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- <div id="next-previous">
                <i class="fa-regular fa-circle-left"></i>
                <i class="fa-regular fa-circle-right"></i>
            </div> -->
        </div>
    </section>

    <!-- <section id="section2">
        <div class="heading">
            <h3>TOP PRODUCTS</h3>
            <h6>Check out popular products on Baobab</h6>
        </div>
        <div id="grid">
            <div id="column1">
                <div>
                    <img src="uploads/product_pictures/img_Vidhi_Maisuria_6839a36f3911e_1748607855_5766_1.jpg" alt="">
                </div>
                <div>
                    <img src="uploads/product_pictures/img_Ethan_Carter_6843385e314be_1749235806_5521_1.jpeg" alt="">
                </div>
                <div>
                    <img src="uploads/product_pictures/img_Vidhi_Maisuriya_684345519afd8_1749239121_4656_1.jpeg" alt="">
                </div>
            </div>
            <div id="column2">
                <div>
                    <img src="uploads/product_pictures/img_Vidhi_Maisuria_6839a48153e9b_1748608129_2547_1.jpg" alt="">
                </div>
                <div>
                    <img src="uploads/product_pictures/img_Michael_Johnson_684337403746d_1749235520_1833_1.jpeg" alt="">
                </div>
                <div>
                    <img src="uploads/product_pictures/img_Sophia_Martinez_68433a81de1e1_1749236353_9134_1.jpeg" alt="">
                </div>
            </div>
            <div id="column3">
                <div>
                    <img src="uploads/product_pictures/img_Olivia_Thompson_68433cf9b51c8_1749236985_2952_1.jpeg" alt="">
                </div>
                <div>
                    <img src="uploads/product_pictures/img_Vidhi_Maisuriya_68434220c27da_1749238304_7027_1.jpeg" alt="">
                </div>
                <div>
                    <img src="uploads/product_pictures/img_Sophia_Martinez_684339d5861cc_1749236181_9457_1.jpeg" alt="">
                </div>
            </div>
    </section> -->

    <section id="section2">
        <div class="heading">
            <h3>TOP PRODUCTS</h3>
            <h6>Check out popular products on Baobab</h6>
        </div>
        <div id="grid">
            <div id="column1">
                <div class="product-item" data-product-id="123">
                    <img src="uploads/product_pictures/img_Vidhi_Maisuria_6839a36f3911e_1748607855_5766_1.jpg" alt="">
                    <div class="product-overlay">
                        <button class="view-product-btn" onclick="viewProduct(6)">
                            <i class="fas fa-eye"></i> View Product
                        </button>
                    </div>
                </div>
                <div class="product-item" data-product-id="124">
                    <img src="uploads/product_pictures/img_Ethan_Carter_6843385e314be_1749235806_5521_1.jpeg" alt="">
                    <div class="product-overlay">
                        <button class="view-product-btn" onclick="viewProduct(14)">
                            <i class="fas fa-eye"></i> View Product
                        </button>
                    </div>
                </div>
                <div class="product-item" data-product-id="125">
                    <img src="uploads/product_pictures/img_Vidhi_Maisuriya_684345519afd8_1749239121_4656_1.jpeg" alt="">
                    <div class="product-overlay">
                        <button class="view-product-btn" onclick="viewProduct(31)">
                            <i class="fas fa-eye"></i> View Product
                        </button>
                    </div>
                </div>
            </div>
            <div id="column2">
                <div class="product-item" data-product-id="126">
                    <img src="uploads/product_pictures/img_Vidhi_Maisuria_6839a48153e9b_1748608129_2547_1.jpg" alt="">
                    <div class="product-overlay">
                        <button class="view-product-btn" onclick="viewProduct(7)">
                            <i class="fas fa-eye"></i> View Product
                        </button>
                    </div>
                </div>
                <div class="product-item" data-product-id="127">
                    <img src="uploads/product_pictures/img_Michael_Johnson_684337403746d_1749235520_1833_1.jpeg" alt="">
                    <div class="product-overlay">
                        <button class="view-product-btn" onclick="viewProduct(12)">
                            <i class="fas fa-eye"></i> View Product
                        </button>
                    </div>
                </div>
                <div class="product-item" data-product-id="128">
                    <img src="uploads/product_pictures/img_Sophia_Martinez_68433a81de1e1_1749236353_9134_1.jpeg" alt="">
                    <div class="product-overlay">
                        <button class="view-product-btn" onclick="viewProduct(17)">
                            <i class="fas fa-eye"></i> View Product
                        </button>
                    </div>
                </div>
            </div>
            <div id="column3">
                <div class="product-item" data-product-id="129">
                    <img src="uploads/product_pictures/img_Olivia_Thompson_68433cf9b51c8_1749236985_2952_1.jpeg" alt="">
                    <div class="product-overlay">
                        <button class="view-product-btn" onclick="viewProduct(21)">
                            <i class="fas fa-eye"></i> View Product
                        </button>
                    </div>
                </div>
                <div class="product-item" data-product-id="130">
                    <img src="uploads/product_pictures/img_Vidhi_Maisuriya_68434220c27da_1749238304_7027_1.jpeg" alt="">
                    <div class="product-overlay">
                        <button class="view-product-btn" onclick="viewProduct(30)">
                            <i class="fas fa-eye"></i> View Product
                        </button>
                    </div>
                </div>
                <div class="product-item" data-product-id="131">
                    <img src="uploads/product_pictures/img_Sophia_Martinez_684339d5861cc_1749236181_9457_1.jpeg" alt="">
                    <div class="product-overlay">
                        <button class="view-product-btn" onclick="viewProduct(16)">
                            <i class="fas fa-eye"></i> View Product
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section id="section3" class="section-p1">
        <div class="heading">
            <h3>BROWSE POPULAR CATEGORIES</h3>
        </div>
        <div id="feature">
            <div class="fe-box">
                <div id="gif1"></div>
                <h6><a href="pages/electronics.php">electronics</a></h6>
            </div>
            <div class="fe-box">
                <div id="gif2"></div>
                <h6><a href="pages/vehicle.php">Vehicle</a></h6>
            </div>
            <div class="fe-box">
                <div id="gif3"></div>
                <h6><a href="pages/home.php">Home</a></h6>
            </div>
            <div class="fe-box">
                <div id="gif4"></div>
                <h6><a href="pages/fashion.php">Fashion</a></h6>
            </div>
            <!-- <div class="fe-box">
                <div id="gif5"></div>
                <h6><a href="pages/furniture.php">Furniture</a></h6>
            </div> -->
            <div class="fe-box">
                <div id="gif6"></div>
                <h6><a href="pages/category.php">More</a></h6>
            </div>
        </div>
    </section>

    <section id="section4">
        <div class="heading">
            <h3>BROWSE TOP SELLER PRODUCTS</h3>
        </div>
        <?php include('pages/indexProduct.php'); ?> 
        <div id="browse">
            <button class="normal"><a href="pages/shop.php">Browse More <i class="fa-solid fa-arrow-right"></i></a></button>
        </div>
    </section>

    <section id="section5">
        <div class="heading">
            <h3>What <span>Our Users Say</span></h3>
        </div>
        <div id="reviews">
            <?php if (!empty($reviews)): ?>
                <?php foreach ($reviews as $review): ?>
                    <?php if (!empty($review['message'])): ?>
                        <div class="review-item">
                            <p id="name"><?php echo htmlspecialchars($review['firstname'] . ' ' . $review['userLastName']); ?></p>
                            <p id="reviewStar"><?php echo htmlspecialchars($review['rating']); ?> <i class="fa-solid fa-star"></i></p>
                            <span id="review"><?php echo htmlspecialchars($review['message']); ?></span>
                            <div id="like">
                                <button><i class="fa-solid fa-thumbs-up"></i></button>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No reviews yet.</p>
        <?php endif; ?>
        </div>
    </section>

    <section id="section6">
        <div class="heading">
            <h5>Faqs</h5>
            <h3>QUESTIONS <span><a href="pages/faq.php">LOOK HERE</a></span></h3>
        </div>
        <div id="faq">
            <button class="accordion">How do I list an Item for sale on Baobab?</button>
            <div class="panel">
                <p>To list an item, create an account, click "Sell", list your item by uploading clear images, write a detailed description, and set a price of your product. Specify the item's condition and category. Review and list your product.</p>
            </div>
            <button class="accordion">What payment method are accepted on Baobab?</button>
            <div class="panel">
                <p>Baobab uses PayFast as their payment Gateway. PayFast supports various payment method such as credit/debit cards and in some cases Cash on delivery or in-person payments</p>
            </div>
            <button class="accordion">How does shipping work on Baobab?</button>
            <div class="panel">
                <p>Shipping is usually arranged between the buyer and the seller on our messaging platform. The seller will typically specify their shipping options and costs in the product listing. You can discuss shipping details and preferences with the seller directly.</p>
            </div>
            <button class="accordion">Is there a fee for selling items on Baobab?</button>
            <div class="panel">
                <p>Listing products on Baobab is free.</p>
            </div>
            <button class="accordion">How can I ensure a safe transaction on Baobab?</button>
            <div class="panel">
                <p>We recommend using our built-in secure payment system to ensure transaction safety. While this platform provides a safe space for transactions. It is important to excercise cautions. Avoid making payments outside of the platform's secure system.</p>
            </div>
        </div>
    </section>

    <!-- <section id="newsletter" class="section-p1 section-m1">
        <div class="newstext">
            <h4>Sign Up For Newsletters</h4>
            <p>Get E-main updates about out latest shop and <span>special offers</span></p>
        </div>
        <div class="form">
            <input type="text" placeholder="Your email address">
            <button class="normal">Sign Up</button>
        </div>
    </section> -->

    <section id="section8">
        <div class="heading">
            <h3>HOW BAOBAB WORKS</h3>
        </div>
        <div id="box">
            <div class="work">
                <div id="gif7"></div>
                <h6>Create an Account</h6>
                <p>Sign up and complete your profile to start buying and selling.</p>
            </div>
            <div class="work">
                <div id="gif8"></div>
                <h6>List Your Items</h6>
                <p>Easily upload photos, add descriptions and set your price.</p>
            </div>
            <div class="work">
                <div id="gif9"></div>
                <h6>Connect with Users</h6>
                <p>Chat securely within our platform to arrange details.</p>
            </div>
            <div class="work">
                <div id="gif10"></div>
                <h6>Complete the Sale</h6>
                <p>Meet safely or use our secure payment options to finalize.</p>
            </div>
        </div>
        <div id="backToTop">
            <button class="normal"><a href="#section1">Get Started Now</a></button>
        </div>
    </section>

    <section id="section9">
        <div id="safety">
            <h5><i class="fa-solid fa-shield"></i> Buy & Sell Safety Guidelines</h5>
            <h6>We prioritize your safety in every transaction. Follow these <a href="pages/safety.php">tips</a> to ensure a secure buying and selling experience.</h6>
        </div>
    </section>

    <?php include('includes/indexFooter.php'); ?>

    <script src="assets/js/script.js"></script>
    <script>
        function viewProduct(productId) {
            // Add loading state to button
            const button = event.target.closest('.view-product-btn');
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
            button.disabled = true;
            
            // Navigate to product page
            window.location.href = `pages/viewProduct.php?userId=<?php echo $_SESSION['userId']; ?>&productId=${productId}`;
        }
    </script>
    <script src="assets/js/shop.js"></script>
</body>
</html>