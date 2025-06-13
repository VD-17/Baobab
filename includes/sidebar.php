<section id="sidebar">
    <ul>
        <li id="logo"><a href="../root/index.php"><img src="../assets/images/Logo/Baobab_favicon.png" alt="Baobab logo"></a></li>
        <li><a href="../pages/userDashboard.php?userId=<?php echo $_SESSION['userId']; ?>"><i class="bi bi-grid-fill"></i>Dashboard</a></li>
        <li><a href="../pages/editProfile.php?userId=<?php echo $_SESSION['userId']; ?>"><i class="fa-solid fa-circle-user"></i>My Profile</a></li>
        <li><a href="../pages/myListing.php?userId=<?php echo $_SESSION['userId']; ?>"><i class="fa-solid fa-list-check"></i>My Listings</a></li>
        <li><a href="../pages/viewOrders.php?php echo $_SESSION['userId']; ?>"><i class="fa-solid fa-heart"></i>My Orders</a></li>
        <li><a href="../pages/settings.php?userId=<?php echo $_SESSION['userId']; ?>"><i class="fa-solid fa-gear"></i>Setting</a></li>
    </ul>
</section>