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

// Pagination setup
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

try {
    // Get total count of orders
    $countStmt = $conn->prepare("
        SELECT COUNT(DISTINCT o.id) as total_orders
        FROM orders o
        WHERE o.buyer_id = ?
    ");
    $countStmt->execute([$userId]);
    $totalOrders = $countStmt->fetch(PDO::FETCH_ASSOC)['total_orders'];
    $totalPages = ceil($totalOrders / $limit);

    // Fetch all orders with items
    $stmtOrders = $conn->prepare("
        SELECT 
            o.id as order_id,
            o.order_number,
            o.total_amount,
            o.payment_status,
            o.created_at as order_date,
            seller.firstname as seller_firstname,
            seller.lastname as seller_lastname,
            seller.userId as seller_id,
            GROUP_CONCAT(
                CONCAT(p.productName, '|', oi.quantity, '|', oi.item_price, '|', p.productPicture)
                SEPARATOR ';;'
            ) as order_items
        FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        JOIN products p ON p.id = oi.product_id
        JOIN users seller ON seller.userId = o.seller_id
        WHERE o.buyer_id = ?
        GROUP BY o.id
        ORDER BY o.created_at DESC
        LIMIT ? OFFSET ?
    ");
    $stmtOrders->execute([$userId, $limit, $offset]);
    $orders = $stmtOrders->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Orders fetch error: " . $e->getMessage());
    $orders = [];
    $totalPages = 0;
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
                    <p>Showing <?php echo count($orders); ?> of <?php echo $totalOrders; ?> orders</p>
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
                                <a href="../pages/profile.php?userId=<?php echo $order['seller_id']; ?>">
                                    <?php echo htmlspecialchars($order['seller_firstname'] . ' ' . $order['seller_lastname']); ?>
                                </a>
                            </p>
                        </div>

                        <div class="order-items">
                            <?php 
                            $items = explode(';;', $order['order_items']);
                            foreach ($items as $item): 
                                $itemData = explode('|', $item);
                                if (count($itemData) >= 4):
                            ?>
                                <div class="order-item">
                                    <div class="item-image">
                                        <img src="../<?php echo htmlspecialchars($itemData[3]); ?>" 
                                             alt="<?php echo htmlspecialchars($itemData[0]); ?>">
                                    </div>
                                    <div class="item-details">
                                        <h4><?php echo htmlspecialchars($itemData[0]); ?></h4>
                                        <p>Quantity: <?php echo $itemData[1]; ?></p>
                                        <p class="item-price">R<?php echo number_format($itemData[2], 2); ?> each</p>
                                    </div>
                                </div>
                            <?php 
                                endif;
                            endforeach; 
                            ?>
                        </div>

                        <div class="order-actions">
                            <button class="btn-secondary" onclick="contactSeller(<?php echo $order['seller_id']; ?>)">
                                Contact Seller
                            </button>
                            <?php if ($order['payment_status'] === 'paid'): ?>
                                <button class="btn-primary" onclick="leaveReview(<?php echo $order['order_id']; ?>)">
                                    Leave Review
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>

                <!-- Pagination -->
                <?php if ($totalPages > 1): ?>
                    <div class="pagination">
                        <?php if ($page > 1): ?>
                            <a href="?page=<?php echo $page - 1; ?>" class="page-btn">← Previous</a>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <a href="?page=<?php echo $i; ?>" 
                               class="page-btn <?php echo $i === $page ? 'active' : ''; ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>

                        <?php if ($page < $totalPages): ?>
                            <a href="?page=<?php echo $page + 1; ?>" class="page-btn">Next →</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

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