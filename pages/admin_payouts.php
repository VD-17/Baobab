<?php
session_start();
require_once '../includes/db_connection.php';

if (!isset($_SESSION['userId']) || !isAdmin($_SESSION['userId'])) {
    header('Location: ../pages/signIn.php');
    exit();
}

function isAdmin($userId) {
    global $conn;
    // Option 1: If you have a specific admin flag
    $stmt = $conn->prepare("SELECT is_admin FROM users WHERE userId = ?");
    $stmt->execute([$userId]);
    $user = $stmt->fetch();
    return $user && $user['is_admin'] == 1;
}

// Handle AJAX requests for payment processing
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'process_payout':
            $earning_id = $_POST['earning_id'] ?? null;
            if ($earning_id) {
                echo json_encode(processEarningPayout($earning_id));
            } else {
                echo json_encode(['success' => false, 'message' => 'Missing earning ID']);
            }
            exit;
            
        case 'bulk_process':
            $earning_ids = $_POST['earning_ids'] ?? [];
            echo json_encode(processBulkPayouts($earning_ids));
            exit;
    }
}

function processEarningPayout($earning_id) {
    global $conn;
    
    try {
        // Get earning details - joining with users table using is_seller
        $stmt = $conn->prepare("
            SELECT se.*, u.firstName, u.lastName, u.email, o.order_number
            FROM seller_earnings se
            JOIN users u ON se.seller_id = u.userId AND u.is_seller = 1
            JOIN orders o ON se.order_id = o.id
            WHERE se.id = ? AND se.payout_status = 'pending'
        ");
        $stmt->execute([$earning_id]);
        $earning = $stmt->fetch();
        
        if (!$earning) {
            return ['success' => false, 'message' => 'Earning not found, already processed, or seller not valid'];
        }
        
        // Here you would integrate with your actual payout system
        // For now, we'll just mark as processed
        $payout_id = 'PAYOUT_' . date('Ymd') . '_' . $earning_id;
        
        $update_stmt = $conn->prepare("
            UPDATE seller_earnings 
            SET payout_status = 'completed', 
                payout_id = ?,
                payout_date = NOW(),
                updated_at = NOW()
            WHERE id = ?
        ");
        $update_stmt->execute([$payout_id, $earning_id]);
        
        // Log the payout
        $log_stmt = $conn->prepare("
            INSERT INTO payout_logs (earning_id, seller_id, amount, payout_id, status, processed_by, created_at)
            VALUES (?, ?, ?, ?, 'completed', ?, NOW())
        ");
        $log_stmt->execute([
            $earning_id,
            $earning['seller_id'],
            $earning['amount'],
            $payout_id,
            $_SESSION['userId']
        ]);
        
        return [
            'success' => true, 
            'message' => 'Payout processed successfully',
            'payout_id' => $payout_id
        ];
        
    } catch (PDOException $e) {
        error_log("Payout processing error: " . $e->getMessage());
        return ['success' => false, 'message' => 'Database error occurred'];
    }
}

function processBulkPayouts($earning_ids) {
    $results = ['success' => 0, 'failed' => 0, 'errors' => []];
    
    foreach ($earning_ids as $earning_id) {
        $result = processEarningPayout($earning_id);
        if ($result['success']) {
            $results['success']++;
        } else {
            $results['failed']++;
            $results['errors'][] = "ID $earning_id: " . $result['message'];
        }
    }
    
    return $results;
}

// Get pending earnings for display
$pending_earnings = [];
$completed_earnings = [];

try {
    // Pending payouts - only for verified sellers
    $stmt = $conn->prepare("
        SELECT se.*, u.firstName, u.lastName, u.email, o.order_number, o.total_amount
        FROM seller_earnings se
        JOIN users u ON se.seller_id = u.userId AND u.is_seller = 1
        JOIN orders o ON se.order_id = o.id
        WHERE se.payout_status = 'pending'
        ORDER BY se.created_at DESC
    ");
    $stmt->execute();
    $pending_earnings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Recent completed payouts - only for verified sellers
    $stmt = $conn->prepare("
        SELECT se.*, u.firstName, u.lastName, u.email, o.order_number, o.total_amount
        FROM seller_earnings se
        JOIN users u ON se.seller_id = u.userId AND u.is_seller = 1
        JOIN orders o ON se.order_id = o.id
        WHERE se.payout_status = 'completed'
        ORDER BY se.payout_date DESC
        LIMIT 50
    ");
    $stmt->execute();
    $completed_earnings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Error fetching earnings: " . $e->getMessage());
}

// Calculate totals
$total_pending = array_sum(array_column($pending_earnings, 'amount'));
$total_completed = array_sum(array_column($completed_earnings, 'amount'));

// Get seller statistics
$seller_stats = [];
try {
    $stmt = $conn->prepare("
        SELECT 
            COUNT(*) as total_sellers,
            COUNT(CASE WHEN is_seller = 1 THEN 1 END) as active_sellers
        FROM users
    ");
    $stmt->execute();
    $seller_stats = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $seller_stats = ['total_sellers' => 0, 'active_sellers' => 0];
}
?>

<!DOCTYPE html>
<html lang="en">
<?php
    $pageTitle = "Admin - Seller Payouts";
    include('../includes/head.php');
?>
<head>
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <link rel="stylesheet" href="../assets/css/payouts.css">
</head>
<body>
    <section id="sidebar">
        <ul>
            <li id="logo"><img src="../assets/images/Logo/Baobab_favicon.png" alt="Baobab logo"></li>
            <li><a href="../pages/adminDashboard.php?userId=<?php echo $_SESSION['userId']; ?>"><i class="bi bi-grid-fill"></i>Dashboard</a></li>
            <li><a href="../pages/users.php"><i class="fa-solid fa-users"></i>Users</a></li>
            <li><a href="../pages/totalProducts.php"><i class="fa-solid fa-box"></i>Products</a></li>
            <li><a href="../pages/admin_payouts.php" class="active"><i class="bi bi-arrow-left-right"></i>Transactions</a></li>
            <li><a href="../pages/support.php"><i class="fa-solid fa-message"></i>Messages</a></li>
            <li><a href="../pages/analytics.php"><i class="fa-solid fa-chart-simple"></i>Analytics</a></li>
            <li><a href="../pages/admins.php"><i class="fa-solid fa-user-tie"></i>Admins</a></li>
            <li><a href="../pages/adminSettings.php"><i class="fa-solid fa-gear"></i>Settings</a></li>
        </ul>
    </section>

    <div class="admin-container">
        <h1>Seller Payouts Management</h1>
        
        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value">R<?php echo number_format($total_pending, 2); ?></div>
                <div>Pending Payouts</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo count($pending_earnings); ?></div>
                <div>Pending Count</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">R<?php echo number_format($total_completed, 2); ?></div>
                <div>Total Paid</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $seller_stats['active_sellers']; ?></div>
                <div>Active Sellers</div>
            </div>
        </div>

        <div id="alert-container"></div>

        <!-- Pending Payouts -->
        <h2>Pending Payouts</h2>
        <?php if (!empty($pending_earnings)): ?>
            <div class="bulk-actions">
                <button class="btn btn-success" onclick="selectAll()">Select All</button>
                <button class="btn btn-warning" onclick="processBulk()">Process Selected</button>
            </div>
            
            <table class="earnings-table">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="select-all"></th>
                        <th>Order #</th>
                        <th>Seller</th>
                        <th>Email</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pending_earnings as $earning): ?>
                    <tr>
                        <td><input type="checkbox" class="earning-checkbox" value="<?php echo $earning['id']; ?>"></td>
                        <td><?php echo htmlspecialchars($earning['order_number']); ?></td>
                        <td>
                            <?php echo htmlspecialchars($earning['firstName'] . ' ' . $earning['lastName']); ?>
                            <span class="seller-badge">SELLER</span>
                        </td>
                        <td><?php echo htmlspecialchars($earning['email']); ?></td>
                        <td>R<?php echo number_format($earning['amount'], 2); ?></td>
                        <td><?php echo date('Y-m-d H:i', strtotime($earning['created_at'])); ?></td>
                        <td>
                            <button class="btn btn-primary btn-small" onclick="processSingle(<?php echo $earning['id']; ?>)">
                                Process
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No pending payouts for verified sellers.</p>
        <?php endif; ?>

        <!-- Completed Payouts -->
        <h2>Recent Completed Payouts</h2>
        <?php if (!empty($completed_earnings)): ?>
            <table class="earnings-table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Seller</th>
                        <th>Amount</th>
                        <th>Payout ID</th>
                        <th>Payout Date</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($completed_earnings as $earning): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($earning['order_number']); ?></td>
                        <td>
                            <?php echo htmlspecialchars($earning['firstName'] . ' ' . $earning['lastName']); ?>
                            <span class="seller-badge">SELLER</span>
                        </td>
                        <td>R<?php echo number_format($earning['amount'], 2); ?></td>
                        <td><?php echo htmlspecialchars($earning['payout_id'] ?? 'N/A'); ?></td>
                        <td><?php echo $earning['payout_date'] ? date('Y-m-d H:i', strtotime($earning['payout_date'])) : 'N/A'; ?></td>
                        <td><span class="status-completed">Completed</span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No completed payouts found.</p>
        <?php endif; ?>
    </div>

    <script>
        function showAlert(message, type = 'success') {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type}`;
            alertDiv.textContent = message;
            
            const container = document.getElementById('alert-container');
            container.innerHTML = '';
            container.appendChild(alertDiv);
            
            setTimeout(() => {
                container.innerHTML = '';
            }, 5000);
        }

        function selectAll() {
            const selectAllCheckbox = document.getElementById('select-all');
            const checkboxes = document.querySelectorAll('.earning-checkbox');
            
            selectAllCheckbox.checked = !selectAllCheckbox.checked;
            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAllCheckbox.checked;
            });
        }

        function processSingle(earningId) {
            if (!confirm('Are you sure you want to process this payout?')) return;
            
            fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=process_payout&earning_id=${earningId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('Payout processed successfully!');
                    location.reload();
                } else {
                    showAlert(data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('An error occurred while processing the payout.', 'error');
            });
        }

        function processBulk() {
            const selectedIds = Array.from(document.querySelectorAll('.earning-checkbox:checked'))
                .map(checkbox => checkbox.value);
            
            if (selectedIds.length === 0) {
                showAlert('Please select at least one payout to process.', 'error');
                return;
            }
            
            if (!confirm(`Are you sure you want to process ${selectedIds.length} payouts?`)) return;
            
            fetch('', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=bulk_process&earning_ids=${JSON.stringify(selectedIds)}`
            })
            .then(response => response.json())
            .then(data => {
                showAlert(`Processed ${data.success} payouts successfully. ${data.failed} failed.`);
                if (data.errors.length > 0) {
                    console.log('Errors:', data.errors);
                }
                location.reload();
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('An error occurred while processing payouts.', 'error');
            });
        }
    </script>
</body>
</html>