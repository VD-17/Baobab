<?php
session_start();
require_once '../includes/db_connection.php';

// Check if user is admin
if (!isset($_SESSION['isAdmin']) || $_SESSION['isAdmin'] != 1) {
    $_SESSION['errors'] = ["Access denied. Admin privileges required."];
    header("Location: ../root/index.php");
    exit;
}

try {
    $stmt = $conn->prepare("
        SELECT 
            m.id,
            m.sender_id, 
            m.receiver_id, 
            m.message, 
            m.sent_at, 
            m.is_read,
            sender.firstName as sender_first_name,
            sender.lastName as sender_last_name,
            sender.email as sender_email,
            receiver.firstName as receiver_first_name,
            receiver.lastName as receiver_last_name,
            receiver.email as receiver_email
        FROM messages m
        LEFT JOIN users sender ON m.sender_id = sender.userId
        LEFT JOIN users receiver ON m.receiver_id = receiver.userId
        WHERE m.receiver_id = 7
        ORDER BY m.sent_at DESC
    ");
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['errors'] = ["Error fetching messages: " . $e->getMessage()];
    $result = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<?php
$pageTitle = "All Messages - Admin";
include('../includes/head.php');
?>
<head>
    <link rel="stylesheet" href="../assets/css/myListing.css">
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <style>
        .status-read { color: green; font-weight: bold; }
        .status-unread { color: red; font-weight: bold; }
        .message-preview { 
            max-width: 250px; 
            overflow: hidden; 
            white-space: nowrap; 
            text-overflow: ellipsis; 
        }
    </style>
</head>
<body id="analytics">

    <section id="sidebar">
        <ul>
            <li id="logo"><a href="../root/index.php"><img src="../assets/images/Logo/Baobab_favicon.png" alt="Baobab logo"></a></li>
            <li><a href="../pages/adminDashboard.php?userId=<?php echo htmlspecialchars($_SESSION['userId']); ?>"><i class="bi bi-grid-fill"></i>Dashboard</a></li>
            <li><a href="../pages/users.php"><i class="fa-solid fa-users"></i>Users</a></li>
            <li><a href="../pages/totalProducts.php"><i class="fa-solid fa-box"></i>Products</a></li>
            <li><a href="../pages/admin_payouts.php"><i class="bi bi-arrow-left-right"></i>Transactions</a></li>
            <li><a href="../pages/support.php" class="active"><i class="fa-solid fa-message"></i>Messages</a></li>
            <li><a href="../pages/analytics.php"><i class="fa-solid fa-chart-simple"></i>Analytics</a></li>
            <li><a href="../pages/admins.php"><i class="fa-solid fa-user-tie"></i>Admins</a></li>
            <li><a href="../pages/adminSettings.php"><i class="fa-solid fa-gear"></i>Settings</a></li>
        </ul>
    </section>

    <div id="wholeListing">
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

        <?php if (isset($_SESSION['message'])): ?>
            <section class="section-p1">
                <div class="success-messages">
                    <p style="color: green;"><?php echo htmlspecialchars($_SESSION['message']); ?></p>
                    <?php unset($_SESSION['message']); ?>
                </div>
            </section>
        <?php endif; ?>

        <section class="section-p1">
            <div id="myListing">
                <div id="heading">
                    <h1>Support Messages</h1>
                </div>
                <p>Manage all Support messages sent to Admin</p>
                <table id="listingTable">
                    <thead id="tableHeading">
                        <tr>
                            <th style="color: #080357;">From</th>
                            <th style="color: #080357;">To</th>
                            <th style="color: #080357;">Message</th>
                            <th style="color: #080357;">Date & Time</th>
                            <th style="color: #080357;">Status</th>
                            <th style="color: #080357;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="tabledata">
                        <?php if (count($result) > 0): ?>
                            <?php foreach ($result as $row): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($row['sender_first_name'] . ' ' . $row['sender_last_name']); ?></strong><br>
                                        <small><?php echo htmlspecialchars($row['sender_email']); ?></small> 
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($row['receiver_first_name'] . ' ' . $row['receiver_last_name']); ?></strong><br>
                                        <small><?php echo htmlspecialchars($row['receiver_email']); ?></small>
                                    </td>
                                    <td class="message-preview">
                                        <?php echo htmlspecialchars($row['message']); ?>
                                    </td>
                                    <td>
                                        <?php echo date('M j, Y', strtotime($row['sent_at'])); ?><br>
                                        <small><?php echo date('g:i A', strtotime($row['sent_at'])); ?></small>
                                    </td>
                                    <td>
                                        <span class="<?php echo $row['is_read'] ? 'status-read' : 'status-unread'; ?>">
                                            <?php echo $row['is_read'] ? 'Read' : 'Unread'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="white view-btn" 
                                                data-sender="<?php echo htmlspecialchars($row['sender_id']); ?>"
                                                data-message-id="<?php echo htmlspecialchars($row['id']); ?>" style="color: #FF9F1C;">
                                            View
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6">No messages found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const viewButtons = document.querySelectorAll('.view-btn');

            viewButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const senderId = this.getAttribute('data-sender');
                    const messageId = this.getAttribute('data-message-id');
                    window.location.href = `../pages/conversation.php?sender_id=${senderId}&message_id=${messageId}`;
                });
            });
        });
    </script>
</body>
</html>