<?php
session_start();
require_once '../includes/db_connection.php';
require_once '../includes/analytics_functions.php';

// Check if user is admin - Updated to match your database structure
// if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
//     $_SESSION['errors'] = ["Access denied. Admin privileges required."];
//     header("Location: ../root/index.php");
//     exit;
// }

$userId = (int)$_SESSION['userId']; 

// Get analytics data
$analytics = new Analytics($conn);
$dashboardData = $analytics->getDashboardData();
$monthlyGrowth = $analytics->getMonthlyGrowth();
$categoryDistribution = $analytics->getCategoryDistribution();
$deviceTraffic = $analytics->getDeviceTraffic();
$userActivity = $analytics->getWeeklyUserActivity();
$topSellers = $analytics->getTopSellers();
?>

<!DOCTYPE html>
<html lang="en">
<?php
$pageTitle = "Admin Dashboard";
include('../includes/head.php');
?>
<head>
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <link rel="stylesheet" href="../assets/css/analytics.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <section id="sidebar">
        <ul>
            <li id="logo"><a href="../root/index.php"><img src="../assets/images/Logo/Baobab_favicon.png" alt="Baobab logo"></a></li>
            <li><a href="../pages/adminDashboard.php?userId=<?php echo $_SESSION['userId']; ?>"><i class="bi bi-grid-fill"></i>Dashboard</a></li>
            <li><a href="../pages/users.php"><i class="fa-solid fa-users"></i>Users</a></li>
            <li><a href="../pages/totalProducts.php"><i class="fa-solid fa-box"></i>Products</a></li>
            <li><a href="../pages/admin_payouts.php"><i class="bi bi-arrow-left-right"></i>Transactions</a></li>
            <li><a href="../pages/support.php"><i class="fa-solid fa-message"></i>Messages</a></li>
            <li><a href="../pages/analytics.php" class="active"><i class="fa-solid fa-chart-simple"></i>Analytics</a></li>
            <li><a href="../pages/admins.php"><i class="fa-solid fa-user-tie"></i>Admins</a></li>
            <li><a href="../pages/adminSettings.php"><i class="fa-solid fa-gear"></i>Settings</a></li>
        </ul>
    </section>

    <section id="graph">
        <section id="heading">
        <h2>Analytics</h2>
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
                <h6>Completed Transactions</h6>
                <h5 id="totalCompletedTransactions"><?php echo number_format($dashboardData['completed_transactions']); ?></h5>
                <p id="increase-transaction-percentage">
                    <?php echo $dashboardData['transaction_growth'] >= 0 ? '+' : ''; ?>
                    <?php echo number_format($dashboardData['transaction_growth'], 1); ?>% this month
                </p>
            </div>
        </div>
    </section>

    <section id="middle-section">
        <div id="middle-left">
            <h3>Monthly Growth</h3>
            <p>User registration, listings, and transactions</p>
            <div id="line-graph">
                <canvas id="monthlyGrowthChart"></canvas>
            </div>
        </div>
        <div id="middle-right">
            <h3>Listings by Category</h3>
            <p>Distribution of active listings by category</p>
            <div id="pie-chart">
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
    </section>

    <section id="bottom-section">
        <div id="bottom-left">
            <h3>Traffic by Device</h3>
            <p>User access distribution by device type</p>
            <div id="pie-chart">
                <canvas id="deviceChart"></canvas>
            </div>
        </div>
        <!-- <div id="bottom-right">
            <h3>Seller Performance Metrics</h3>
            <p>Weekly active users and engagement</p>
            <div id="bar-graph">
                <canvas id="userActivityChart"></canvas>
            </div>
        </div> -->
        <div id="bottom-right">
            <section id="top-sellers">
                <h3>Top Sellers</h3>
                <p>Performance by earnings and order count</p>
                <?php if (!empty($topSellers['labels'])): ?>
                    <div id="bar-graph">
                        <canvas id="topSellersChart"></canvas>
                    </div>
                <?php else: ?>
                    <div class="no-data">
                        <p>No seller data available yet.</p>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </section>
    </section>

    <script>
        // Color scheme
        const colors = {
            primary: '#FF9F1C',
            secondary: '#080357',
            tertiary: '#3C426F',
            accent: '#D6FFB7'
        };

        // Monthly Growth Chart
        const monthlyGrowthCtx = document.getElementById('monthlyGrowthChart').getContext('2d');
        new Chart(monthlyGrowthCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($monthlyGrowth['labels']); ?>,
                datasets: [
                    {
                        label: 'Users',
                        data: <?php echo json_encode($monthlyGrowth['users']); ?>,
                        borderColor: colors.primary,
                        backgroundColor: colors.primary + '20',
                        tension: 0.4
                    },
                    {
                        label: 'Listings',
                        data: <?php echo json_encode($monthlyGrowth['listings']); ?>,
                        borderColor: colors.secondary,
                        backgroundColor: colors.secondary + '20',
                        tension: 0.4
                    },
                    {
                        label: 'Transactions',
                        data: <?php echo json_encode($monthlyGrowth['transactions']); ?>,
                        borderColor: colors.tertiary,
                        backgroundColor: colors.tertiary + '20',
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Category Distribution Chart
        const categoryCtx = document.getElementById('categoryChart').getContext('2d');
        new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($categoryDistribution['labels']); ?>,
                datasets: [{
                    data: <?php echo json_encode($categoryDistribution['data']); ?>,
                    backgroundColor: [
                        colors.primary,
                        colors.secondary,
                        colors.tertiary,
                        colors.accent,
                        '#FF6B6B',
                        '#4ECDC4',
                        '#45B7D1',
                        '#96CEB4',
                        '#FFEAA7'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                    }
                }
            }
        });

        // Device Traffic Chart
        const deviceCtx = document.getElementById('deviceChart').getContext('2d');
        new Chart(deviceCtx, {
            type: 'pie',
            data: {
                labels: <?php echo json_encode($deviceTraffic['labels']); ?>,
                datasets: [{
                    data: <?php echo json_encode($deviceTraffic['data']); ?>,
                    backgroundColor: [
                        colors.primary,
                        colors.secondary,
                        colors.tertiary
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                }
            }
        });

        // User Activity Chart
        const userActivityCtx = document.getElementById('userActivityChart').getContext('2d');
        new Chart(userActivityCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($userActivity['labels']); ?>,
                datasets: [{
                    label: 'Active Users',
                    data: <?php echo json_encode($userActivity['data']); ?>,
                    backgroundColor: colors.primary,
                    borderColor: colors.secondary,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Top Sellers Chart (add this after other charts)
        <?php if (!empty($topSellers['labels'])): ?>
        const topSellersCtx = document.getElementById('topSellersChart').getContext('2d');
        new Chart(topSellersCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($topSellers['labels']); ?>,
                datasets: [
                    {
                        label: 'Order Count',
                        data: <?php echo json_encode($topSellers['orders']); ?>,
                        backgroundColor: colors.primary,
                        borderColor: colors.primary,
                        borderWidth: 1,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Total Earnings (R)',
                        type: 'line',
                        data: <?php echo json_encode($topSellers['earnings']); ?>,
                        borderColor: colors.secondary,
                        backgroundColor: colors.secondary + '20',
                        fill: false,
                        tension: 0.4,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                if (context.dataset.label === 'Total Earnings (R)') {
                                    return context.dataset.label + ': R' + context.parsed.y.toFixed(2);
                                }
                                return context.dataset.label + ': ' + context.parsed.y;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Number of Orders'
                        },
                        ticks: {
                            stepSize: 1
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Earnings (R)'
                        },
                        grid: {
                            drawOnChartArea: false,
                        },
                        ticks: {
                            callback: function(value) {
                                return 'R' + value.toFixed(2);
                            }
                        }
                    }
                }
            }
        });
        <?php endif; ?>
    </script>
</body>
</html>