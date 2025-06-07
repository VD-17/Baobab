<?php
    session_start();
    require_once '../includes/db_connection.php';
?>

<!DOCTYPE html>
<html lang="en">
<?php
    $pageTitle = "User Dashboard Products";
    include('../includes/head.php');
?>
<head>
    <link rel="stylesheet" href="../assets/css/sidebar.css">
</head>
<body>
    <section id="sidebar">
        <ul>
            <li id="logo"><img src="../assets/images/Logo/Baobab_favicon.png" alt="Baobab logo"></li>
            <li><a href="../pages/userDashboard.php?userId=<?php echo $_SESSION['userId']; ?>"><i class="bi bi-grid-fill"></i>Dashboard</a></li>
            <li><a href="../pages/editProfile.php?userId=<?php echo $_SESSION['userId']; ?>"><i class="fa-solid fa-circle-user"></i>My Profile</a></li>
            <li><a href="../pages/myListing.php?userId=<?php echo $_SESSION['userId']; ?>"><i class="fa-solid fa-list-check"></i>My Listings</a></li>
            <li><a href="../pages/favourite.php?userId=<?php echo $_SESSION['userId']; ?>"><i class="fa-solid fa-heart"></i>Favourites</a></li>
            <li><a href="../pages/conversation.php?userId=<?php echo $_SESSION['userId']; ?>"><i class="fa-solid fa-message"></i>Messages</a></li>
            <li><a href="../pages/settings.php?userId=<?php echo $_SESSION['userId']; ?>"><i class="fa-solid fa-gear"></i>Setting</a></li>
        </ul>
    </section>

    <div class="wrapper">
        <div class="section">
            <div id="box-area">
                <h2></h2>
            </div>
        </div>
    </div>
</body>
</html>