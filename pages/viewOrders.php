<?php
session_start();
require_once '../includes/db_connection.php';

if (!isset($_SESSION['userId'])) {
    $_SESSION['errors'] = ["You must be logged in to view your orders."];
    header("Location: ../pages/signIn.php");
    exit;
}

$userId = (int)$_SESSION['userId'];

try {
    // Get user info for header
    $sqlUser = "SELECT firstname, lastname, profile_picture FROM users WHERE userId = :userId";
    $stmtUser = $conn->prepare($sqlUser);
    $stmtUser->bindParam(':userId', $userId, PDO::PARAM_INT);
    $stmtUser->execute();

    if ($stmtUser->rowCount() > 0) {
        $rowUser = $stmtUser->fetch(PDO::FETCH_ASSOC);
        $userName = $rowUser['firstname'] . ' ' . $rowUser['lastname'];
        
        // Check for profile picture
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
    error_log("Profile header error: " . $e->getMessage());
}

try {
    // Get total count of orders
    $countStmt = $conn->prepare("
        SELECT COUNT(*) as total_orders
        FROM orders o
        WHERE o.buyer_id = ?
    ");
    $countStmt->execute([$userId]);
    $totalOrdersResult = $countStmt->fetch(PDO::FETCH_ASSOC);
    $totalOrders = $totalOrdersResult['total_orders'];

    // Fetch all orders for the user
    $stmtOrders = $conn->prepare("
        SELECT 
            o.id as order_id,
            o.order_number,
            o.total_amount,
            o.payment_status,
            o.created_at as order_date,
            COALESCE(u.firstname, 'Unknown') as seller_firstname,
            COALESCE(u.lastname, 'Seller') as seller_lastname,
            o.seller_id as seller_id
        FROM orders o
        LEFT JOIN users u ON u.userId = o.seller_id
        WHERE o.buyer_id = ?
        ORDER BY o.created_at DESC
    ");
    
    $stmtOrders->execute([$userId]);
    $orders = $stmtOrders->fetchAll(PDO::FETCH_ASSOC);

    // Fetch order items for each order
    foreach ($orders as &$order) {
        $itemStmt = $conn->prepare("
            SELECT 
                COALESCE(p.productName, 'Product Unavailable') as productName,
                oi.quantity,
                oi.item_price,
                COALESCE(p.productPicture, 'assets/images/no-image.png') as productPicture
            FROM order_items oi
            LEFT JOIN products p ON p.id = oi.product_id
            WHERE oi.order_id = ?
        ");
        $itemStmt->execute([$order['order_id']]);
        $order['items'] = $itemStmt->fetchAll(PDO::FETCH_ASSOC);
    }

} catch (PDOException $e) {
    error_log("Orders fetch error: " . $e->getMessage());
    $orders = [];
    $totalOrders = 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<?php
    $pageTitle = "My Orders";
    include('../includes/head.php');
?>
<head>
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <link rel="stylesheet" href="../assets/css/viewOrders.css">
</head>
<body id="orders-page">
    <button class="mobile-menu-toggle" onclick="toggleSidebar()">
        <i class="fa-solid fa-bars"></i>
    </button>
    
    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" onclick="closeSidebar()"></div>
 
    <?php include('../includes/sidebar.php'); ?>

    <main id="main-content">
        <div class="page-header">
            <div class="user-info">
                <img src="<?php echo htmlspecialchars($profileImageSrc ?? '../assets/images/default-profile.png'); ?>" 
                     alt="Profile Picture" class="profile-pic">
                <div>
                    <h2>My Orders</h2>
                    <p>View all your purchase history</p>
                </div>
            </div>
        </div>

        <div class="orders-container">
            <?php if (!empty($orders)): ?>
                <div class="orders-summary">
                    <p>Total Orders: <?php echo $totalOrders; ?></p>
                </div>

                <?php foreach ($orders as $order): ?>
                    <div class="order-card">
                        <div class="order-header">
                            <div class="order-info">
                                <h3>Order #<?php echo htmlspecialchars($order['order_number']); ?></h3>
                                <p class="order-date">Ordered on <?php echo date('F d, Y \a\t g:i A', strtotime($order['order_date'])); ?></p>
                            </div>
                            <div class="order-status">
                                <span class="status-badge status-<?php echo strtolower($order['payment_status']); ?>">
                                    <?php echo ucfirst($order['payment_status']); ?>
                                </span>
                                <p class="order-total">Total: R<?php echo number_format($order['total_amount'], 2); ?></p>
                            </div>
                        </div>

                        <div class="seller-info">
                            <p><strong>Seller:</strong> 
                                <?php if ($order['seller_firstname'] !== 'Unknown'): ?>
                                    <a href="../pages/profile.php?userId=<?php echo $order['seller_id']; ?>">
                                        <?php echo htmlspecialchars($order['seller_firstname'] . ' ' . $order['seller_lastname']); ?>
                                    </a>
                                <?php else: ?>
                                    <span>Unknown Seller (ID: <?php echo $order['seller_id']; ?>)</span>
                                <?php endif; ?>
                            </p>
                        </div>

                        <div class="order-items">
                            <?php if (!empty($order['items'])): ?>
                                <?php foreach ($order['items'] as $item): ?>
                                    <div class="order-item">
                                        <!-- <div class="item-image">
                                            <img src="../<?php echo htmlspecialchars($item['productPicture']); ?>" 
                                                 alt="<?php echo htmlspecialchars($item['productName']); ?>"
                                                 onerror="this.src='../assets/images/no-image.png'">
                                        </div> -->
                                        <div class="item-details">
                                            <h4><?php echo htmlspecialchars($item['productName']); ?></h4>
                                            <p>Quantity: <?php echo (int)$item['quantity']; ?></p>
                                            <p class="item-price">R<?php echo number_format($item['item_price'], 2); ?> each</p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p>No items found for this order.</p>
                            <?php endif; ?>
                        </div>

                        <div class="order-actions">
                            <?php if ($order['seller_firstname'] !== 'Unknown'): ?>
                                <button class="btn-secondary" onclick="contactSeller(<?php echo $order['seller_id']; ?>)">
                                    Contact Seller
                                </button>
                            <?php endif; ?>
                            <?php if ($order['payment_status'] === 'paid'): ?>
                                <button class="btn-primary" onclick="leaveReview(<?php echo $order['order_id']; ?>)">
                                    Leave Review
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>

            <?php else: ?>
                <div class="no-orders">
                    <div class="no-orders-content">
                        <i class="fa-solid fa-shopping-bag"></i>
                        <h3>No Orders Yet</h3>
                        <p>You haven't made any purchases yet. Start shopping to see your orders here!</p>
                        <a href="../pages/shop.php" class="btn-primary">Start Shopping</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script>
        function contactSeller(sellerId) {
            window.location.href = `../pages/conversation.php?userId=${sellerId}`;
        }

        function leaveReview(orderId) {
            // You can implement a review modal or redirect to review page
            alert('Review functionality to be implemented');
        }
    </script>

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