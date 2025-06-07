<?php
session_start();
require_once '../includes/db_connection.php';
require_once '../includes/analytics_functions.php';

// Check if user is admin 
if (!isset($_SESSION['isAdmin']) || $_SESSION['isAdmin'] != 1) {
    $_SESSION['errors'] = ["Access denied. Admin privileges required."];
    header("Location: ../root/index.php");
    exit;
}

$userId = (int)$_SESSION['userId'];

// Get analytics data
$analytics = new Analytics($conn);
$dashboardData = $analytics->getDashboardMetrics();
$recentActivities = $analytics->getRecentActivities(8);
$systemStatus = $analytics->getSystemStatus();

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
    error_log("Profile header error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<?php
    $pageTitle = "Admin Dashboard";
    include('../includes/head.php');
?>
<head>
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <link rel="stylesheet" href="../assets/css/adminDashboard.css">
</head>
<body id="dashboard2">
    <section id="sidebar">
        <ul>
            <li id="logo"><a href="../root/index.php"><img src="../assets/images/Logo/Baobab_favicon.png" alt="Baobab logo"></a>
                <p><a href="../root/index.php"><- Back to Home</a></p>
            </li>
            <li><a href="../pages/adminDashboard.php?userId=<?php echo $_SESSION['userId']; ?>" class="active"><i class="bi bi-grid-fill"></i>Dashboard</a></li>
            <li><a href="../pages/users.php"><i class="fa-solid fa-users"></i>Users</a></li>
            <li><a href="../pages/totalProducts.php"><i class="fa-solid fa-box"></i>Products</a></li>
            <li><a href="../pages/admin_payouts.php"><i class="bi bi-arrow-left-right"></i>Transactions</a></li>
            <li><a href="../pages/support.php"><i class="fa-solid fa-message"></i>Messages</a></li>
            <li><a href="../pages/analytics.php"><i class="fa-solid fa-chart-simple"></i>Analytics</a></li>
            <li><a href="../pages/admins.php"><i class="fa-solid fa-user-tie"></i>Admins</a></li>
            <li><a href="../pages/adminSettings.php"><i class="fa-solid fa-gear"></i>Settings</a></li>
        </ul>
    </section>

    <section id="top-section">
        <div id="quickAccess">
            <div class="box" onclick="window.location.href='users.php'">
                <i class="fa-solid fa-users"></i>
                <h6>Total Users</h6>
                <h5 id="totalUsers"><?php echo number_format($dashboardData['total_users']); ?></h5>
                <p id="increase-user-percentage">
                    <?php echo $dashboardData['user_growth'] >= 0 ? '+' : ''; ?>
                    <?php echo number_format($dashboardData['user_growth'], 1); ?>% this month
                </p>
            </div>
            <div class="box" onclick="window.location.href='totalProducts.php'">
                <i class="fa-solid fa-box"></i>
                <h6>Active Listings</h6>
                <h5 id="totalListing"><?php echo number_format($dashboardData['active_listings']); ?></h5>
                <p id="increase-listing-percentage">
                    <?php echo $dashboardData['listing_growth'] >= 0 ? '+' : ''; ?>
                    <?php echo number_format($dashboardData['listing_growth'], 1); ?>% this month
                </p>
            </div>
            <div class="box" onclick="window.location.href='admin_payouts.php'">
                <i class="bi bi-arrow-left-right"></i>
                <h6>Transactions</h6>
                <h5 id="totalTransactions"><?php echo number_format($dashboardData['total_transactions']); ?></h5>
                <p id="increase-transaction-percentage">
                    <?php echo $dashboardData['transaction_growth'] >= 0 ? '+' : ''; ?>
                    <?php echo number_format($dashboardData['transaction_growth'], 1); ?>% this month
                </p>
            </div>
            <div class="box" onclick="window.location.href='support.php'">
                <i class="fa-solid fa-message"></i>
                <h6>Messages</h6>
                <h5 id="totalMessages"><?php echo number_format($dashboardData['total_messages']); ?></h5>
                <p id="increase-message-percentage">
                    <?php echo $dashboardData['message_growth'] >= 0 ? '+' : ''; ?>
                    <?php echo number_format($dashboardData['message_growth'], 1); ?>% this month
                </p>
            </div>
        </div>
    </section>

    <section id="bottom-left-section">
        <h4>Recent Activity</h4>
        <p>Latest actions across the marketplace</p>
        <div id="activities">
            <?php if (!empty($recentActivities)): ?>
                <table class="activities-table">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Activity</th>
                            <th>Date & Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentActivities as $activity): ?>
                            <tr class="activity-row">
                                <td class="activity-icon">
                                    <?php
                                    switch($activity['type']) {
                                        case 'user_registered':
                                            echo '<i class="fa-solid fa-user-plus"></i>';
                                            break;
                                        case 'product_listed':
                                            echo '<i class="fa-solid fa-box"></i>';
                                            break;
                                        case 'order_placed':
                                            echo '<i class="fa-solid fa-shopping-cart"></i>';
                                            break;
                                        case 'message_sent':
                                            echo '<i class="fa-solid fa-message"></i>';
                                            break;
                                        default:
                                            echo '<i class="fa-solid fa-circle"></i>';
                                    }
                                    ?>
                                </td>
                                <td class="activity-description">
                                    <?php echo htmlspecialchars($activity['description']); ?>
                                </td>
                                <td class="activity-time">
                                    <?php echo date('M j, Y g:i A', strtotime($activity['timestamp'])); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-activity">
                    <p>No recent activity found.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <section id="bottom-right-section">
        <h4>System Status</h4>
        <p>Current service status and health</p>
        <div id="services">
            <div id="service1">
                <h5>User Authentication</h5>
                <p id="status1" class="status-<?php echo strtolower($systemStatus['authentication']); ?>">
                    <?php echo $systemStatus['authentication']; ?>
                </p>
            </div>
            <div id="service2">
                <h5>Payment Processing</h5>
                <p id="status2" class="status-<?php echo strtolower($systemStatus['payment']); ?>">
                    <?php echo $systemStatus['payment']; ?>
                </p>
            </div>
            <div id="service3">
                <h5>Messaging System</h5>
                <p id="status3" class="status-<?php echo strtolower($systemStatus['messaging']); ?>">
                    <?php echo $systemStatus['messaging']; ?>
                </p>
            </div>
            <div id="service4">
                <h5>Search Indexing</h5>
                <p id="status4" class="status-<?php echo strtolower($systemStatus['search']); ?>">
                    <?php echo $systemStatus['search']; ?>
                </p>
            </div>
            <div id="service5">
                <h5>File Storage</h5>
                <p id="status5" class="status-<?php echo strtolower($systemStatus['storage']); ?>">
                    <?php echo $systemStatus['storage']; ?>
                </p>
            </div>
        </div>
    </section>
</body>
</html>