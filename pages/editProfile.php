<?php 
    session_start();

    require_once("../includes/db_connection.php");

    if (!isset($_SESSION['userId'])) {
        $_SESSION['errors'] = ['You must be logged in to edit a product.'];
        header('Location: ../pages/signIn.php?redirect=myListing.php');
        exit();
    }

    $userId = $_SESSION['userId'];
    $profileImageSrc = "../assets/images/default-profile.png"; // Default image

    try {
        $sqlUser = "SELECT firstname, lastname, email, street_address, suburb, postal_code, province, city, profile_picture, created_at, phoneNumber, bio FROM users WHERE userId = :userId";
        $stmtUser = $conn->prepare($sqlUser);
        $stmtUser->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmtUser->execute();

        if ($stmtUser->rowCount() > 0) {
            $rowUser = $stmtUser->fetch(PDO::FETCH_ASSOC);
            $userName = $rowUser['firstname'] . ' ' . $rowUser['lastname'];
            $firstname = $rowUser['firstname'];
            $lastname = $rowUser['lastname'];
            $email = $rowUser['email'];
            $phoneNumber = $rowUser['phoneNumber'];
            $street_address = $rowUser['street_address'];
            $suburb = $rowUser['suburb'];
            $postal_code = $rowUser['postal_code'];
            $province = $rowUser['province'];
            $city = $rowUser['city'];
            $bio = $rowUser['bio'] ?? '';
            $created_at = date('Y-m-d', strtotime($rowUser['created_at']));
            
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
?>

<!DOCTYPE html>
<html lang="en">
<?php 
    $pageTitle = "My Profile";
    include('../includes/head.php'); 
?>
<head>
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <link rel="stylesheet" href="../assets/css/userProfile.css"> 
</head>
<body id="user">
    <button class="mobile-menu-toggle" onclick="toggleSidebar()">
        <i class="fa-solid fa-bars"></i>
    </button>
    
    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" onclick="closeSidebar()"></div>
    
    <?php include('../includes/sidebar.php'); ?>

    <div>
        <h2>My Profile</h2>
    </div>
    
    <section>
        <div id="left">
            <div id="editProfilePic">
                <img src="<?php echo htmlspecialchars($profileImageSrc); ?>" alt="Profile Picture" class="profile-pic">
                <i class="fa-solid fa-upload"></i>
            </div>
            <div id="name">
                <span class="username"><?php echo htmlspecialchars($userName); ?></span>
                <p>Member since <?php echo htmlspecialchars($created_at); ?></p>
            </div>
            <div id="bio">
                <textarea name="bio" id="bio" placeholder="Add your Bio"><?php echo htmlspecialchars($bio); ?></textarea>
            </div>
        </div>
        <div id="right">
            <form action="../api/authentication/edit.php" method="post" id="userForm" enctype="multipart/form-data">
                <div class="profile">
                    <label for="firstname">First Name</label>
                    <i class="fa-solid fa-user"></i>
                    <input type="text" name="firstname" id="firstname" value="<?php echo htmlspecialchars($firstname); ?>">
                </div>
                <div class="profile">
                    <label for="lastname">Last Name</label>
                    <i class="fa-solid fa-user"></i>
                    <input type="text" name="lastname" id="lastname" value="<?php echo htmlspecialchars($lastname); ?>">
                </div>
                <div class="profile">
                    <label for="email">Email</label>
                    <i class="fa-solid fa-envelope"></i>
                    <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($email); ?>">
                </div>
                <div class="profile">
                    <label for="phoneNumber">Phone Number</label>
                    <i class="fas fa-phone"></i>
                    <input type="text" name="phoneNumber" id="phoneNumber" value="<?php echo htmlspecialchars($phoneNumber); ?>">
                </div>
                <div class="profile">
                    <div id="address">
                        <div id="street">
                            <label for="street_address">Street Address</label>
                            <i class="fas fa-map-marker-alt"></i>
                            <input type="text" name="street_address" id="street_address" value="<?php echo htmlspecialchars($street_address); ?>">
                        </div>
                        <div id="suburb">
                            <label for="suburb">Suburb</label>
                            <i class="fas fa-map"></i>
                            <input type="text" name="suburb" id="suburb" value="<?php echo htmlspecialchars($suburb); ?>">
                        </div>
                        <div id="code">
                            <label for="postal_code">Postal Code</label>
                            <i class="fas fa-mail-bulk"></i>
                            <input type="text" name="postal_code" id="postal_code" value="<?php echo htmlspecialchars($postal_code); ?>">
                        </div>
                        <div id="city">
                            <label for="city">City</label>
                            <i class="fas fa-city"></i>
                            <input type="text" name="city" id="city" value="<?php echo htmlspecialchars($city); ?>">
                        </div>
                        <div id="province">
                            <label for="province">Province</label>
                            <i class="fas fa-globe"></i>
                            <input type="text" name="province" id="province" value="<?php echo htmlspecialchars($province); ?>">
                        </div>
                    </div>
                </div>
                <div class="profile">
                    <label for="profilePicture">Profile Picture</label>
                    <input type="file" name="profilePicture" id="profilePicture" accept="image/*">
                </div>
                <button type="submit" class="normal">Save Changes</button>
            </form>
        </div>
    </section>

    <script>
        function toggleSidebar() {
            const sidebar = document.querySelector('section ul');
            const overlay = document.querySelector('.sidebar-overlay');
            const toggleBtn = document.querySelector('.mobile-menu-toggle');
            
            if (sidebar && overlay) {
                sidebar.classList.toggle('active');
                overlay.classList.toggle('active');
                
                // Change icon based on sidebar state
                const icon = toggleBtn.querySelector('i');
                if (sidebar.classList.contains('active')) {
                    icon.className = 'fa-solid fa-times';
                } else {
                    icon.className = 'fa-solid fa-bars';
                }
            }
        }

        function closeSidebar() {
            const sidebar = document.querySelector('section ul');
            const overlay = document.querySelector('.sidebar-overlay');
            const toggleBtn = document.querySelector('.mobile-menu-toggle');
            
            if (sidebar && overlay) {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
                
                // Reset icon
                const icon = toggleBtn.querySelector('i');
                icon.className = 'fa-solid fa-bars';
            }
        }

        // Close sidebar when clicking on a link (optional)
        document.addEventListener('DOMContentLoaded', function() {
            const sidebarLinks = document.querySelectorAll('section ul li a');
            
            sidebarLinks.forEach(link => {
                link.addEventListener('click', function() {
                    if (window.innerWidth <= 1024) {
                        closeSidebar();
                    }
                });
            });
            
            // Close sidebar when window is resized to desktop
            window.addEventListener('resize', function() {
                if (window.innerWidth > 1024) {
                    closeSidebar();
                }
            });
        });
    </script>
</body>
</html>