<?php
session_start();
require_once '../includes/db_connection.php';

// Check if user is logged in
$user_id = isset($_SESSION['userId']) ? $_SESSION['userId'] : 
           (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 
           (isset($_SESSION['id']) ? $_SESSION['id'] : null));

if (!$user_id) {
    $_SESSION['errors'] = ["Please log in to view notifications"];
    header("Location: ../pages/signIn.php");
    exit;
}

try {
    // Get unread message notifications
    $stmt = $conn->prepare("
        SELECT 
            m.id as message_id,
            m.sender_id,
            m.message,
            m.sent_at,
            u.userId,
            u.firstname,
            u.lastname,
            u.profile_picture
        FROM messages m
        JOIN users u ON u.userId = m.sender_id
        WHERE m.receiver_id = ? AND m.is_read = FALSE
        ORDER BY m.sent_at DESC
    ");
    $stmt->execute([$user_id]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $_SESSION['errors'] = ["Error fetching notifications: " . $e->getMessage()];
    $notifications = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<?php
$pageTitle = "Notifications";
include('../includes/head.php');
?>
<head>
    <link rel="stylesheet" href="../assets/css/notifications.css">
    <style>
        .notifications-container {
            max-width: 800px;
            margin: 20px auto;
            padding: 0 20px;
        }

        .notification-list {
            border: 1px solid #ddd;
            border-radius: 8px;
            background: #fff;
        }

        .notification-item {
            padding: 15px;
            border-bottom: 1px solid #ddd;
            display: flex;
            align-items: center;
            cursor: pointer;
            transition: background 0.2s;
        }

        .notification-item:hover {
            background: #f9f9f9;
        }

        .notification-item:last-child {
            border-bottom: none;
        }

        .profile-pic {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            margin-right: 15px;
        }

        .notification-info {
            flex: 1;
        }

        .notification-sender {
            font-weight: bold;
            margin: 0;
        }

        .notification-message {
            color: #666;
            margin: 5px 0 0;
            font-size: 0.9em;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 500px;
        }

        .notification-time {
            color: #999;
            font-size: 0.8em;
            margin-left: 10px;
        }

        .no-notifications {
            text-align: center;
            padding: 20px;
            color: #666;
            font-style: italic;
        }
    </style>
</head>
<body>
    <?php include('../includes/header.php'); ?>

    <section class="notifications-container section-p1">
        <h2>Notifications</h2>

        <?php if (isset($_SESSION['errors']) && !empty($_SESSION['errors'])): ?>
            <div class="error-messages">
                <?php foreach ($_SESSION['errors'] as $error): ?>
                    <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
                <?php unset($_SESSION['errors']); ?>
            </div>
        <?php endif; ?>

        <div class="notification-list">
            <?php if (!empty($notifications)): ?>
                <?php foreach ($notifications as $notification): ?>
                    <?php
                    // Use the profile picture from the notification user
                    $profileImageSrc = '../assets/images/Welcome/default_profile.jpg';
                    if (!empty($notification['profile_picture'])) {
                        $cleanPath = ltrim($notification['profile_picture'], '/');
                        $profileImageSrc = '../' . htmlspecialchars($cleanPath) . '?' . mt_rand();
                    }
                    ?>
                    <div class="notification-item" 
                         data-sender-id="<?php echo $notification['sender_id']; ?>" 
                         data-message-id="<?php echo $notification['message_id']; ?>">
                        <img src="<?php echo $profileImageSrc; ?>" alt="Profile Picture" class="profile-pic">
                        <div class="notification-info">
                            <h3 class="notification-sender">
                                <?php echo htmlspecialchars($notification['firstname'] . ' ' . $notification['lastname']); ?>
                            </h3>
                            <p class="notification-message">
                                <?php echo htmlspecialchars($notification['message']); ?>
                            </p>
                        </div>
                        <div class="notification-actions">
                            <button class="normal" onclick="openConversation(<?php echo $notification['sender_id']; ?>)">Open</button>
                        </div>
                        <span class="notification-time">
                            <?php echo date('M d, H:i', strtotime($notification['sent_at'])); ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-notifications">No new notifications.</div>
            <?php endif; ?>
        </div>
    </section>

    <script> 
        document.addEventListener('DOMContentLoaded', function() {
            const notificationItems = document.querySelectorAll('.notification-item');

            notificationItems.forEach(item => {
                item.addEventListener('click', function() {
                    const senderId = this.getAttribute('data-sender-id');
                    const messageId = this.getAttribute('data-message-id');

                    // Mark message as read
                    fetch('../api/messaging.php?action=mark_read', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ message_id: messageId })
                    })
                    .catch(error => console.error('Error:', error));

                    // Navigate to conversation
                    window.location.href = `../pages/conversation.php?sender_id=${senderId}`;
                });
            });
        });

        function openConversation(senderId) {
            window.location.href = `../pages/conversation.php?sender_id=${senderId}`;
        }
    </script>

    <?php include('../includes/footer.php'); ?>

</body>
</html>