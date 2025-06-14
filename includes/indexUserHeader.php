<?php
// Get user's profile information
    $headerProfileImageSrc = "assets/images/Welcome/default_profile.jpg"; // Default
    $headerUserName = "User"; // Default

    if (!isset($_SESSION['userId']) || empty($_SESSION['userId'])) {
        // If no userId, redirect to login or show guest header
        include('guestHeader.php');
        return;
    }

    $userId = $_SESSION['userId'];

    // Get user's name and profile picture
    try {
        $sqlUser = "SELECT firstname, lastname, profile_picture, is_admin FROM users WHERE userId = :userId";
        $stmtUser = $conn->prepare($sqlUser);
        $stmtUser->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmtUser->execute();

        if ($stmtUser->rowCount() > 0) {
            $rowUser = $stmtUser->fetch(PDO::FETCH_ASSOC);
            $headerUserName = $rowUser['firstname'] . ' ' . $rowUser['lastname'];
            $isAdmin = $rowUser['is_admin'];
            
            // Check if user has uploaded profile picture
            $sqlImg = "SELECT status FROM profileimg WHERE userId = :userId";
            $stmtImg = $conn->prepare($sqlImg);
            $stmtImg->bindParam(':userId', $userId, PDO::PARAM_INT);
            $stmtImg->execute();

            if ($stmtImg->rowCount() > 0) {
                $rowImg = $stmtImg->fetch(PDO::FETCH_ASSOC);
                if ($rowImg['status'] == 0 && !empty($rowUser['profile_picture'])) {
                    $headerProfileImageSrc = $rowUser['profile_picture'] . "?" . mt_rand();
                }
            }
        }
    } catch (PDOException $e) {
        // Log error, but continue with defaults
        error_log("Profile header error: " . $e->getMessage());
    }

    try {
        $user_id = isset($_SESSION['userId']) ? $_SESSION['userId'] : 
                (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 
                (isset($_SESSION['id']) ? $_SESSION['id'] : null));

        $unread_message_count = 0;
        $unread_notification_count = 0;

        if ($user_id) {
            // Unread messages count
            $stmt = $conn->prepare("
                SELECT COUNT(*) as unread_count
                FROM messages m
                WHERE m.receiver_id = ? AND m.is_read = FALSE
            ");
            $stmt->execute([$user_id]);
            $unread_message_count = $stmt->fetch(PDO::FETCH_ASSOC)['unread_count'];

            // Unread notifications count (assuming notifications are tied to unread messages)
            // You can modify this if notifications are stored differently
            $stmt = $conn->prepare("
                SELECT COUNT(*) as unread_count
                FROM messages m
                WHERE m.receiver_id = ? AND m.is_read = FALSE
            ");
            $stmt->execute([$user_id]);
            $unread_notification_count = $stmt->fetch(PDO::FETCH_ASSOC)['unread_count'];
        }
    } catch (PDOException $e) {
        $_SESSION['errors'] = ["Error fetching counts: " . $e->getMessage()];
    }
    
?>

<!DOCTYPE html>
<html lang="en">
<?php 
    include('head.php')
?>
<head>
    <link rel="stylesheet" href="assets/css/header.css">
</head>
<body>
    <section id="header">
        <a href="index.php">
            <img src="assets/images/Logo/logo (1).png" alt="Baobab's Logo" id="logoImg">
        </a>
        <div id="searchbar"> 
            <form action="pages/search_handler.php" method="GET" style="display: flex; align-items: center; position: relative; width: 100%;">
                <i class="fa-solid fa-magnifying-glass" id="search"></i>
                <input class="search" type="search" id="searchInput" name="query" 
                    placeholder="Search products or users" 
                    value="<?php echo isset($_GET['query']) ? htmlspecialchars($_GET['query']) : ''; ?>"
                    autocomplete="off">
                
                <div class="search-icons">
                    <button type="submit">
                        <i class="bi bi-arrow-return-left"></i>
                    </button>
                </div>
                
                <div id="autocomplete-results" class="autocomplete-dropdown"></div>
            </form>
        </div>
        <div id="navbar">
            <li class="profile-dropdown">
                <a href="#" class="profile-link">
                    <img src="<?php echo htmlspecialchars($headerProfileImageSrc); ?>" alt="Profile Picture" class="profile-pic-small">
                    <span class="username"><?php echo htmlspecialchars($headerUserName); ?></span>
                    <i class="fa-solid fa-chevron-down"></i>
                </a>
                <div class="dropdown-content">
                    <?php if ($isAdmin == 1): ?>
                        <a href="pages/adminDashboard.php?userId=<?php echo $_SESSION['userId']; ?>">Admin Dashboard</a>
                        <a href="pages/userDashboard.php?userId=<?php echo $_SESSION['userId']; ?>">User Dashboard</a>
                    <?php else: ?>
                        <a href="pages/userDashboard.php?userId=<?php echo $_SESSION['userId']; ?>">My Dashboard</a>
                    <?php endif; ?>
                    <a href="pages/myListing.php?userId=<?php echo $_SESSION['userId']; ?>">My Listings</a>
                    <a href="pages/settings.php">Settings</php>
                    <hr>
                    <a href="api/authentication/logout.php">Logout</a>
                </div>
            </li>
            <li><a href="pages/cart.php?userId=<?php echo $_SESSION['userId']; ?>"><i class="fa-solid fa-cart-shopping"></i></a></li>
            <li><a href="pages/notification.php?userId=<?php echo $_SESSION['userId']; ?>">
                <i class="fa-solid fa-bell"></i>
                <?php if ($unread_notification_count > 0): ?>
                    <sup class="unread-indicator"><?php echo $unread_notification_count; ?></sup>
                <?php endif; ?>
            </a></li>
            <li><a href="pages/conversation.php?userId=<?php echo $_SESSION['userId']; ?>">
                <i class="fa-solid fa-message"></i>
                <?php if ($unread_message_count > 0): ?>
                    <sup class="unread-indicator"><?php echo $unread_message_count; ?></sup>
                <?php endif; ?>
            </a></li>
            <li><a href="pages/favourite.php?userId=<?php echo $_SESSION['userId']; ?>"><i class="fa-solid fa-heart"></i></a></li>
            <button class="white" onclick="window.location.href='pages/listing.php?userId=<?php echo $_SESSION['userId']; ?>'">Sell</button>
        </div>
    </section>

    <script src="assets/js/search.js"></script>
</body>
</html>