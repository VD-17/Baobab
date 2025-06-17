<?php
    session_start();
    require_once '../includes/db_connection.php';

        if (!isset($_SESSION['userId'])) {
        $_SESSION['errors'] = ["You must be logged in to view your listings."];
        header("Location: ../pages/signIn.php");
        exit;
    }

    $userId = (int)$_SESSION['userId'];

    try {
        $sqlUser = "SELECT firstname, lastname, profile_picture FROM users WHERE userId = :userId";
        $stmtUser = $conn->prepare($sqlUser);
        $stmtUser->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmtUser->execute();

        if ($stmtUser->rowCount() > 0) {
            $rowUser = $stmtUser->fetch(PDO::FETCH_ASSOC);
            $userName = $rowUser['firstname'] . ' ' . $rowUser['lastname'];
            
            // Check if user has uploaded profile picture
            $sqlImg = "SELECT status FROM profileimg WHERE userId = :userId";
            $stmtImg = $conn->prepare($sqlImg);
            $stmtImg->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmtImg->execute();

            if ($stmtImg->rowCount() > 0) {
                $rowImg = $stmtImg->fetch(PDO::FETCH_ASSOC);
                if ($rowImg['status'] == 0 && !empty($rowUser['profile_picture'])) {
                    $profileImageSrc = "../" . $rowUser['profile_picture'] . "?" . mt_rand();
                }
            }
        }
    } catch (PDOException $e) {
        // Log error, but continue with defaults
        error_log("Profile header error: " . $e->getMessage());
    }

    try {
        // Fetch unread message notifications
        $stmtMessages = $conn->prepare("
            SELECT 
                m.id as message_id,
                m.sender_id,
                m.message,
                m.sent_at,
                u.firstname,
                u.lastname,
                u.profile_picture
            FROM messages m
            JOIN users u ON u.userId = m.sender_id
            WHERE m.receiver_id = ? AND m.is_read = FALSE
            ORDER BY m.sent_at DESC
            LIMIT 5
        ");
        $stmtMessages->execute([$userId]);
        $notifications = $stmtMessages->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        error_log("Dashboard error: " . $e->getMessage());
        $notifications = [];
    }

    try {
        // Fetch products bought by the user (from orders)
        $stmtBoughtProducts = $conn->prepare("
            SELECT 
                p.id,
                p.productName,
                p.price,
                p.productPicture,
                p.quality,
                oi.quantity,
                oi.subtotal,
                o.created_at as purchase_date,
                o.payment_status,
                seller.firstname as seller_firstname,
                seller.lastname as seller_lastname
            FROM order_items oi
            JOIN orders o ON o.id = oi.order_id
            JOIN products p ON p.id = oi.product_id
            JOIN users seller ON seller.userId = p.userId
            WHERE o.buyer_id = ? AND o.payment_status = 'paid'
            ORDER BY o.created_at DESC
            LIMIT 2
        ");
        $stmtBoughtProducts->execute([$userId]);
        $boughtProducts = $stmtBoughtProducts->fetchAll(PDO::FETCH_ASSOC);

        // Fetch products listed by the user
        $stmtListedProducts = $conn->prepare("
            SELECT 
                p.id,
                p.productName,
                p.price,
                p.productPicture,
                p.quality,
                p.productCategory,
                p.created_at,
                p.status,
                COUNT(f.id) as favorite_count,
                COUNT(DISTINCT o.id) as sales_count
            FROM products p
            LEFT JOIN favorites f ON f.productId = p.id
            LEFT JOIN order_items oi ON oi.product_id = p.id
            LEFT JOIN orders o ON o.id = oi.order_id AND o.payment_status = 'paid'
            WHERE p.userId = ?
            GROUP BY p.id
            ORDER BY p.created_at DESC
            LIMIT 2
        ");
        $stmtListedProducts->execute([$userId]);
        $listedProducts = $stmtListedProducts->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        error_log("Products fetch error: " . $e->getMessage());
        $boughtProducts = [];
        $listedProducts = [];
    }
?>

<!DOCTYPE html>
<html lang="en">
<?php
    $pageTitle = "User Dashboard Products";
    include('../includes/head.php');
?>
<head>
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <link rel="stylesheet" href="../assets/css/userDashboard.css">
</head>
<body id="dashboard">
    <button class="mobile-menu-toggle" onclick="toggleSidebar()">
        <i class="fa-solid fa-bars"></i>
    </button>
    
    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" onclick="closeSidebar()"></div>
    
    <?php include('../includes/sidebar.php'); ?>

    <div class="main-content">
        <section id="top-left-section-box">
            <div id="user">
                <h3 class="username"><?php echo htmlspecialchars($userName); ?></h3>
                <img src="<?php echo htmlspecialchars($profileImageSrc); ?>" alt="Profile Picture" class="profile-pic">
            </div>
            <div>
                <h4>What are we doing today?</h4>
            </div>
            <div id="quickLinks">
                <div class="links">
                    <p><a href="../pages/shop.php"><i class="bi bi-shop"></i>Explore products</a></p>
                    <p><a href="../pages/favourite.php"><i class="bi bi-bag-heart-fill"></i>View Favourites</a></p>
                </div>
                <div class="links">
                    <p><a href="../pages/listing.php"><i class="fa-solid fa-pen-to-square"></i>List Products</a></p>
                    <p><a href="../pages/conversation.php"><i class="fa-solid fa-message"></i>View Messages</a></p>
                    <p><a href="../index.php"><i class="fa-solid fa-house"></i>Go to Home</a></p>
                </div>
            </div>
        </section>

        <section id="bottom-section">
            <div id="quickAccess">
                <div class="box" onclick="window.location.href='../pages/editProfile.php?userId=<?php echo $_SESSION['userId']; ?>'">
                    <i class="fa-solid fa-user-pen"></i>
                    <h5>Edit Profile</h5>
                </div>
                <div class="box" onclick="window.location.href='../pages/profile.php?userId=<?php echo $_SESSION['userId']; ?>'">
                    <i class="fa-solid fa-user"></i>
                    <h5>My Profile</h5>
                </div>
                <div class="box" onclick="window.location.href='../pages/edit_bank_details.php?userId=<?php echo $_SESSION['userId']; ?>'">
                    <i class="fa-solid fa-money-check"></i>
                    <h5>Add/Edit Payment Method</h5>
                </div>
                <div class="box" onclick="window.location.href='../pages/settings.php?userId=<?php echo $_SESSION['userId']; ?>'">
                    <i class="fa-solid fa-gear"></i>
                    <h5>Settings</h5>
                </div>
            </div>
        </section>
    </div>

    <div id="right-section">
        <section id="notifications">
            <h3>Notifications</h3>
            <div id="display-notification">
                <?php if (!empty($notifications)): ?>
                    <?php foreach ($notifications as $notification): ?>
                        <div class="notification-item" 
                             data-sender-id="<?php echo $notification['sender_id']; ?>" 
                             data-message-id="<?php echo $notification['message_id']; ?>">
                            <div class="notification-info">
                                <p class="notification-sender">
                                    <?php echo htmlspecialchars($notification['firstname'] . ' ' . $notification['lastname']); ?>
                                </p>
                                <p class="notification-message">
                                    <?php echo htmlspecialchars($notification['message']); ?>
                                </p>
                            </div>
                            <span class="notification-time">
                                <?php echo date('M d, H:i', strtotime($notification['sent_at'])); ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-notifications">No new messages.</div>
                <?php endif; ?>
            </div>
            <button class="normal" onclick="window.location.href='../pages/notification.php'">View All</button>
        </section>

        <section id="Products bought">
            <h3>Products Bought</h3>
            <div id="display-bought-product-confirmation">
                <?php if (!empty($boughtProducts)): ?>
                    <?php foreach ($boughtProducts as $product): ?>
                        <div class="product-item">
                            <!-- <div class="product-image">
                                <img src="../<?php echo htmlspecialchars($product['productPicture']); ?>" 
                                    alt="<?php echo htmlspecialchars($product['productName']); ?>" 
                                    style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px;">
                            </div> -->
                            <div class="product-info">
                                <h5><?php echo htmlspecialchars($product['productName']); ?></h5>
                                <p class="product-price">R<?php echo number_format($product['subtotal'], 2); ?></p>
                                <p class="product-details">
                                    Qty: <?php echo $product['quantity']; ?> • 
                                    <?php echo htmlspecialchars($product['quality']); ?>
                                </p>
                                <p class="seller-info">
                                    Sold by: <?php echo htmlspecialchars($product['seller_firstname'] . ' ' . $product['seller_lastname']); ?>
                                </p>
                                <span class="product-date">
                                    Bought: <?php echo date('M d, Y', strtotime($product['purchase_date'])); ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-products">You haven't bought any products yet.</div>
                <?php endif; ?>
            </div>
            <button class="normal" onclick="window.location.href='../pages/viewOrders.php'">View All Orders</button>
        </section>

        <section id="Products on Listing">
            <h3>Products on Sale</h3>
            <div id="display-Listing-confirmation">
                <?php if (!empty($listedProducts)): ?>
                    <?php foreach ($listedProducts as $product): ?>
                        <div class="product-item">
                            <div class="product-info">
                                <h5><?php echo htmlspecialchars($product['productName']); ?></h5>
                                <div class="product-stats">
                                    <span class="favorites"><?php echo $product['favorite_count']; ?> favorites</span>
                                    <span class="sold"><?php echo $product['sales_count']; ?> sold</span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-products">You haven't listed any products yet.</div>
                <?php endif; ?>
            </div>
            <button class="normal" onclick="window.location.href='../pages/myListing.php?userId=<?php echo $_SESSION['userId']; ?>'">View All Listings</button>
        </section>
    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.querySelector('section ul');
            const overlay = document.querySelector('.sidebar-overlay');
            const toggleBtn = document.querySelector('.mobile-menu-toggle');
            
            if (sidebar && overlay) {
                sidebar.classList.toggle('active');
                overlay.classList.toggle('active');
                
                // Change icon based on sidebar state
                const icon = toggleBtn.querySelector('i');
                if (sidebar.classList.contains('active')) {
                    icon.className = 'fa-solid fa-times';
                } else {
                    icon.className = 'fa-solid fa-bars';
                }
            }
        }

        function closeSidebar() {
            const sidebar = document.querySelector('section ul');
            const overlay = document.querySelector('.sidebar-overlay');
            const toggleBtn = document.querySelector('.mobile-menu-toggle');
            
            if (sidebar && overlay) {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
                
                // Reset icon
                const icon = toggleBtn.querySelector('i');
                icon.className = 'fa-solid fa-bars';
            }
        }

        // Close sidebar when clicking on a link (optional)
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarLinks = document.querySelectorAll('section ul li a');
            
            sidebarLinks.forEach(link => {
                link.addEventListener('click', function() {
                    if (window.innerWidth <= 1024) {
                        closeSidebar();
                    }
                });
            });
            
            // Close sidebar when window is resized to desktop
            window.addEventListener('resize', function() {
                if (window.innerWidth > 1024) {
                    closeSidebar();
                }
            });
        });
    </script>
</body>
</html>