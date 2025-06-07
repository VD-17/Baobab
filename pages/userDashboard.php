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
    <section id="sidebar">
        <ul>
            <li id="logo"><img src="../assets/images/Logo/Baobab_favicon.png" alt="Baobab logo">
                <p><a href="../root/index.php"><- Back to Home</a></p>
            </li>
            <li><a href="../pages/userDashboard.php?userId=<?php echo $_SESSION['userId']; ?>" class="active"><i class="bi bi-grid-fill"></i>Dashboard</a></li>
            <li><a href="../pages/editProfile.php"><i class="fa-solid fa-circle-user"></i>My Profile</a></li>
            <li><a href="../pages/myListing.php?userId=<?php echo $_SESSION['userId']; ?>"><i class="fa-solid fa-list-check"></i>My Listings</a></li>
            <li><a href="../pages/userTransaction.php?userId=<?php echo $_SESSION['userId']; ?>"><i class="fa-solid fa-list-check"></i>Transactions</a></li>
            <li><a href="../pages/conversation.php?userId=<?php echo $_SESSION['userId']; ?>"><i class="fa-solid fa-message"></i>Messages</a></li>
            <li><a href="../pages/settings.php?userId=<?php echo $_SESSION['userId']; ?>"><i class="fa-solid fa-gear"></i>Setting</a></li>
        </ul>
    </section>

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
            </div>
        </div>
    </section>

    <section id="bottom-section">
        <div id="quickAccess">
            <div class="box" onclick="window.location.href='../pages/editProfile.php?userId=<?php echo $_SESSION['userId']; ?>'">
                <i class="fa-solid fa-user"></i>
                <h5>Edit Profile</h5>
            </div>
            <div class="box" onclick="window.location.href='../pages/myListing.php?userId=<?php echo $_SESSION['userId']; ?>'">
                <i class="fa-solid fa-list-check"></i>
                <h5>My Listings</h5>
            </div>
            <div class="box" onclick="window.location.href='../pages/profile.php?userId=<?php echo $_SESSION['userId']; ?>'">
                <i class="fa-solid fa-list-check"></i>
                <h5>My Profile</h5>
            </div>
            <div class="box" onclick="window.location.href='../page/settings.php?userId=<?php echo $_SESSION['userId']; ?>'">
                <i class="fa-solid fa-gear"></i>
                <h5>Settings</h5>
            </div>
        </div>
    </section>

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

        <section id="Products on Listing">
            <h3>Products confirmation</h3>
            <div id="display-Listing-confirmation">

            </div>
        </section>

        <section id="Products bought">
            <h3>Products Bought confirmation</h3>
            <div id="display-bought-product-confirmation">

            </div>
        </section>
    </div>
</body>
</html>