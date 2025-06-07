<?php
session_start();
require_once '../includes/db_connection.php';

// Check if user is logged in
$user_id = isset($_SESSION['userId']) ? $_SESSION['userId'] : 
           (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 
           (isset($_SESSION['id']) ? $_SESSION['id'] : null));

if (!$user_id) {
    $_SESSION['errors'] = ["Please log in to view conversations"];
    header("Location: ../pages/signIn.php");
    exit;
}

try {
    // Get all conversations for the current user
    $stmt = $conn->prepare("
        SELECT 
            c.id,
            c.user1_id,
            c.user2_id,
            c.last_message_at,
            u.userId,
            u.firstname,
            u.lastname,
            u.profile_picture,
            (SELECT COUNT(*) 
             FROM messages m 
             WHERE m.receiver_id = ? 
             AND (m.sender_id = u.userId)
             AND m.is_read = FALSE) as unread_count,
            (SELECT message 
             FROM messages m 
             WHERE (m.sender_id = u.userId AND m.receiver_id = ?) 
                OR (m.sender_id = ? AND m.receiver_id = u.userId)
             ORDER BY m.sent_at DESC 
             LIMIT 1) as last_message
        FROM conversations c
        JOIN users u ON (u.userId = c.user1_id OR u.userId = c.user2_id) 
        AND u.userId != ?
        WHERE c.user1_id = ? OR c.user2_id = ?
        ORDER BY c.last_message_at DESC
    ");
    $stmt->execute([$user_id, $user_id, $user_id, $user_id, $user_id, $user_id]);
    $conversations = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $profileImageSrc = '../assets/images/Welcome/default_profile.jpg';
    if (!empty($product['profile_picture'])) {
        $cleanPath = ltrim($product['profile_picture'], '/');
        $profileImageSrc = '../' . htmlspecialchars($cleanPath) . '?' . mt_rand();
    }

} catch (PDOException $e) {
    $_SESSION['errors'] = ["Error fetching conversations: " . $e->getMessage()];
    $conversations = [];
}

if (isset($_GET['chat_with'])) {
    $chat_with_id = $_GET['chat_with'];
    $stmt = $conn->prepare("SELECT userId, firstname, lastname FROM users WHERE userId = ?");
    $stmt->execute([$chat_with_id]);
    $chat_user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($chat_user) {
        $chat_user_name = htmlspecialchars($chat_user['firstname'] . ' ' . $chat_user['lastname']);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<?php
$pageTitle = "Conversations";
include('../includes/head.php');
?>
<head>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <link rel="stylesheet" href="../assets/css/conversation.css">
</head>
<body>
    <?php include('../includes/header.php'); ?>

    <section class="conversations-container section-p1">
        <h2>Your Conversations</h2>

        <?php if (isset($_SESSION['errors']) && !empty($_SESSION['errors'])): ?>
            <div class="error-messages">
                <?php foreach ($_SESSION['errors'] as $error): ?>
                    <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
                <?php unset($_SESSION['errors']); ?>
            </div>
        <?php endif; ?>

        <div class="conversation-list">
            <?php if (!empty($conversations)): ?>
                <?php foreach ($conversations as $conv): ?>
                    <?php
                    // Use the profile picture from the conversation user
                    $sellerProfileImage = '../assets/images/Welcome/default_profile.jpg';
                    if (!empty($conv['profile_picture'])) {
                        $cleanPath = ltrim($conv['profile_picture'], '/');
                        $sellerProfileImage = '../' . htmlspecialchars($cleanPath) . '?' . mt_rand();
                    }
                    ?>
                    <div class="conversation-item" data-user-id="<?php echo $conv['userId']; ?>" 
                         data-user-name="<?php echo htmlspecialchars($conv['firstname'] . ' ' . $conv['lastname']); ?>">
                        <img src="<?php echo $sellerProfileImage; ?>" alt="Profile Picture" width="50" height="50" style="border-radius: 50%;">
                        <div class="conversation-info">
                            <h3 class="conversation-name"><?php echo htmlspecialchars($conv['firstname'] . ' ' . $conv['lastname']); ?></h3>
                            <p class="last-message"><?php echo htmlspecialchars($conv['last_message'] ?? 'No messages yet'); ?></p>
                        </div>
                        <?php if ($conv['unread_count'] > 0): ?>
                            <span class="unread-count"><?php echo $conv['unread_count']; ?></span>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-conversations">No conversations yet.</div>
            <?php endif; ?>
        </div>

        <div class="message-container">
            <div class="message-header">
                <h3>Chat with <span id="receiverName"></span></h3>
                <!-- <button onclick="close()" style="background: #dc3545; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor:pointer">Close</button> -->
            </div>
            
            <div class="messages-list" id="messagesList"></div>
            
            <div class="message-input">
                <input type="text" id="messageText" placeholder="Type a message..." onkeypress="checkEnter(event)">
                <button onclick="sendMessage()">Send</button>
            </div>
        </div>
    </section>

    <script>
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

        document.addEventListener('DOMContentLoaded', function() {
            <?php if (isset($chat_with_id) && isset($chat_user_name)): ?>
                document.querySelector('.message-container').style.display = 'block';
                startChat('<?php echo $chat_with_id; ?>', '<?php echo $chat_user_name; ?>');
            <?php endif; ?>
            
            const conversationItems = document.querySelectorAll('.conversation-item');
            
            conversationItems.forEach(item => {
                item.addEventListener('click', function() {
                    console.log('Conversation item clicked:', this);
                    const userId = this.getAttribute('data-user-id');
                    const userName = this.getAttribute('data-user-name');
                    console.log('userId:', userId, 'userName:', userName);
                    if (userId && userName) {
                        document.querySelector('.message-container').style.display = 'block';
                        startChat(userId, userName);
                    } else {
                        console.error('Missing userId or userName:', { userId, userName });
                    }
                });
            });

            // Check for new messages periodically
            function checkNewMessages() {
                fetch('../api/messaging.php?action=check_notifications')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && data.unread_count > 0) {
                            // Update conversation list with new unread counts
                            fetchConversationList();
                        }
                    })
                    .catch(error => console.error('Error checking notifications:', error));
            }

            // Function to refresh conversation list
            function fetchConversationList() {
                fetch('../api/messaging.php?action=get_conversations')
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            const conversationList = document.querySelector('.conversation-list');
                            conversationList.innerHTML = '';

                            if (data.conversations.length > 0) {
                                data.conversations.forEach(conv => {
                                    const profilePic = conv.profile_picture ? 
                                        (() => {
                                            try {
                                                const decoded = JSON.parse(conv.profile_picture);
                                                return Array.isArray(decoded) && decoded.length ? decoded[0] : conv.profile_picture;
                                            } catch (e) {
                                                return conv.profile_picture || '../uploads/profile_pictures/default.jpg';
                                            }
                                        })() : '../uploads/profile_pictures/default.jpg';

                                    const div = document.createElement('div');
                                    div.className = 'conversation-item';
                                    div.setAttribute('data-user-id', conv.userId);
                                    div.setAttribute('data-user-name', `${conv.firstname} ${conv.lastname}`);
                                    div.innerHTML = `
                                        <img src="${profilePic}" alt="Profile" class="profile-pic">
                                        <div class="conversation-info">
                                            <h3 class="conversation-name">${conv.firstname} ${conv.lastname}</h3>
                                            <p class="last-message">${conv.last_message || 'No messages yet'}</p>
                                        </div>
                                        ${conv.unread_count > 0 ? `<span class="unread-count">${conv.unread_count}</span>` : ''}
                                    `;
                                    conversationList.appendChild(div);

                                    // Reattach click listeners
                                    div.addEventListener('click', function() {
                                        document.querySelector('.message-container').style.display = 'block';
                                        startChat(conv.userId, `${conv.firstname} ${conv.lastname}`);
                                    });
                                });
                            } else {
                                conversationList.innerHTML = '<div class="no-conversations">No conversations yet.</div>';
                            }
                        }
                    })
                    .catch(error => console.error('Error refreshing conversations:', error));
            }

            // Check for notifications every 10 seconds
            setInterval(checkNewMessages, 10000);
        });
    </script>
    <script src="../assets/js/messaging.js"></script>
</body>
</html>