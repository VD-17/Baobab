<!DOCTYPE html>
<html lang="en">
<?php
$pageTitle = "Settings";
include('../includes/head.php');
?>
<head>
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <link rel="stylesheet" href="../assets/css/settings.css">
</head>
<body id="userSetting">
    <?php
        // Check for messages and display them
        $hasSuccess = isset($_SESSION['success_message']);
        $hasError = isset($_SESSION['error_message']);
        
        if ($hasSuccess || $hasError) {
            echo '<div id="messageContainer" style="position: fixed; top: 20px; right: 20px; z-index: 1000;">';
            
            if ($hasSuccess) {
                echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['success_message']) . '</div>';
                unset($_SESSION['success_message']);
            }
            
            if ($hasError) {
                echo '<div class="alert alert-error">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
                unset($_SESSION['error_message']);
            }
            
            echo '</div>';
        }
    ?>
    <section id="sidebar">
        <ul>
            <li id="logo"><a href="../root/index.php"><img src="../assets/images/Logo/Baobab_favicon.png" alt="Baobab logo"></a></li>
            <li><a href="../pages/adminDashboard.php?userId=<?php echo $_SESSION['userId']; ?>"><i class="bi bi-grid-fill"></i>Dashboard</a></li>
            <li><a href="../pages/users.php"><i class="fa-solid fa-users"></i>Users</a></li>
            <li><a href="../pages/totalProducts.php"><i class="fa-solid fa-box"></i>Products</a></li>
            <li><a href="../pages/admin_payouts.php"><i class="bi bi-arrow-left-right"></i>Transactions</a></li>
            <li><a href="../pages/support.php"><i class="fa-solid fa-message"></i>Messages</a></li>
            <li><a href="../pages/analytics.php"><i class="fa-solid fa-chart-simple"></i>Analytics</a></li>
            <li><a href="../pages/admins.php"><i class="fa-solid fa-user-tie"></i>Admins</a></li>
            <li><a href="../pages/adminSettings.php" class="active"><i class="fa-solid fa-gear"></i>Settings</a></li>
        </ul>
    </section>


    <div class="settings-container">
        <section id="main-heading">
            <h2>Settings</h2>
        </section>

        <section id="general">
            <div class="heading">
                <i class="fa-solid fa-gears"></i>
                <h3>General Settings</h3>
                <p>Manage your marketplace general settings</p>
            </div>
            <div class="settings-content">
                <div class="setting">
                    <div class="set-info">
                        <label for="supportEmail">Support Email</label>
                        <input type="text" name="supportEmail" id="supportEmail" value="support@baobab.com">
                    </div>
                    <div class="set-info">
                        <label for="contact">Support Phone</label>
                        <input type="text" name="contact" id="contact" value="+27 12 345 6789">
                    </div>
                </div>
                <div class="setting">
                    <div class="set-info">
                        <h6>Maintenance Mode</h6>
                        <p>When enabled, the site will display a maintenance message to users.</p>
                    </div>
                    <div class="toggle-wrapper">
                        <label class="switch">
                            <input type="checkbox" id="showEmailToggle" data-setting="showEmail">
                            <span class="slider round"></span>
                        </label>
                    </div>
                </div>
                <div class="setting">
                    <div class="set-info">
                        <h6>Allow New Registrations</h6>
                        <p>When disabled, new users cannot register on the platform.</p>
                    </div>
                    <div class="toggle-wrapper">
                        <label class="switch">
                            <input type="checkbox" id="showEmailToggle" data-setting="showEmail">
                            <span class="slider round"></span>
                        </label>
                    </div>
                </div>
                <div class="setting">
                    <div class="set-info">
                        <h6>Allow New Listings</h6>
                        <p>When disabled, users cannot create new listings.</p>
                    </div>
                    <div class="toggle-wrapper">
                        <label class="switch">
                            <input type="checkbox" id="showEmailToggle" data-setting="showEmail">
                            <span class="slider round"></span>
                        </label>
                    </div>
                </div>
            </div>
            <button type="button" class="white save-btn" data-section="notification">
                <i class="fa-solid fa-floppy-disk"></i>
                Save General Settings
            </button>
        </section>

        <section id="notification">
            <div class="heading">
                <i class="fa-solid fa-bell"></i>
                <h3>Notifications Settings</h3>
                <p>Configure system and email notifications</p>
            </div>
            <div class="settings-content">
                <h4>Admin Notifications</h4>
                <div class="setting">
                    <div class="set-info">
                        <h6>New User Registrations</h6>
                    </div>
                    <div class="toggle-wrapper">
                        <label class="switch">
                            <input type="checkbox" id="showEmailToggle" data-setting="showEmail">
                            <span class="slider round"></span>
                        </label>
                    </div>
                </div>
                <div class="setting">
                    <div class="set-info">
                        <h6>New Listings</h6>
                    </div>
                    <div class="toggle-wrapper">
                        <label class="switch">
                            <input type="checkbox" id="showEmailToggle" data-setting="showEmail">
                            <span class="slider round"></span>
                        </label>
                    </div>
                </div>
                <div class="setting">
                    <div class="set-info">
                        <h6>Support Requests</h6>
                    </div>
                    <div class="toggle-wrapper">
                        <label class="switch">
                            <input type="checkbox" id="showEmailToggle" data-setting="showEmail">
                            <span class="slider round"></span>
                        </label>
                    </div>
                </div>

                <h4>System Notifications</h4>
                <div class="setting">
                    <div class="set-info">
                        <h6>Scheduled Maintenance</h6>
                    </div>
                    <div class="toggle-wrapper">
                        <label class="switch">
                            <input type="checkbox" id="showPhoneToggle" data-setting="showPhone">
                            <span class="slider round"></span>
                        </label>
                    </div>
                </div>
                <div class="setting">
                    <div class="set-info">
                        <h6>System Errors</h6>
                    </div>
                    <div class="toggle-wrapper">
                        <label class="switch">
                            <input type="checkbox" id="showLocationToggle" data-setting="showLocation">
                            <span class="slider round"></span>
                        </label>
                    </div>
                </div>
                <div class="setting">
                    <div class="set-info">
                        <h6>Backup Completion</h6>
                    </div>
                    <div class="toggle-wrapper">
                        <label class="switch">
                            <input type="checkbox" id="showLocationToggle" data-setting="showLocation">
                            <span class="slider round"></span>
                        </label>
                    </div>
                </div>
            </div>
            <button type="button" class="white save-btn" data-section="privacy">
                <i class="fa-solid fa-floppy-disk"></i>
                Save Notification Settings
            </button>
        </section>
    </div>

    <!-- Success/Error Message Container -->
    <div id="messageContainer"></div>

    <!-- Scripts -->
     <script>
        // Auto-hide server alerts after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const serverAlert = document.getElementById('serverAlert');
            if (serverAlert) {
                setTimeout(() => {
                    serverAlert.style.animation = 'slideOutAlert 0.3s ease';
                    setTimeout(() => serverAlert.remove(), 300);
                }, 5000);
            }
        });
    </script>
    <script src="../assets/js/settings.js"></script>
</body>
</html>