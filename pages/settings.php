<?php 
session_start();
require_once '../includes/db_connection.php';

// Check if user is logged in
if (!isset($_SESSION['userId'])) {
    header('Location: ../pages/signIn.php');
    exit();
} 

$user_id = $_SESSION['userId'];
$check_bank_stmt = $conn->prepare("SELECT id, account_holder_name, bank_name, account_number, is_verified FROM seller_bank_details WHERE userId = ?");
$check_bank_stmt->execute([$user_id]);
$user_bank_details = $check_bank_stmt->fetch(PDO::FETCH_ASSOC);
?>

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

    <button class="mobile-menu-toggle" onclick="toggleSidebar()">
        <i class="fa-solid fa-bars"></i>
    </button>
    
    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" onclick="closeSidebar()"></div>
    
    <?php include('../includes/sidebar.php'); ?>


    <div class="settings-container">
        <section id="main-heading">
            <h2>Settings</h2>
        </section>

        <section id="notification">
            <div class="heading">
                <i class="fa-solid fa-bell"></i>
                <h3>Notifications Settings</h3>
                <p>Control how you receive notifications</p>
            </div>
            <div class="settings-content">
                <div class="setting">
                    <div class="set-info">
                        <h6>New message</h6>
                        <p>Get push notifications for new messages</p>
                    </div>
                    <div class="toggle-wrapper">
                        <label class="switch">
                            <input type="checkbox" id="newMessageToggle" data-setting="newMessage">
                            <span class="slider round"></span>
                        </label>
                    </div>
                </div>
            </div>
            <button type="button" class="white save-btn" data-section="notification">
                <i class="fa-solid fa-floppy-disk"></i>
                Save Notifications Settings
            </button>
        </section>

        <section id="privacy">
            <div class="heading">
                <i class="fa-solid fa-eye"></i>
                <h3>Privacy Settings</h3>
                <p>Control your privacy and visibility</p>
            </div>
            <div class="settings-content">
                <div class="setting">
                    <div class="set-info">
                        <h6>Show email address</h6>
                        <p>Allow other users to see your email address</p>
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
                        <h6>Show phone number</h6>
                        <p>Allow other users to see your phone number</p>
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
                        <h6>Show location</h6>
                        <p>Display your general location to other users</p>
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
                Save Privacy Settings
            </button>
        </section>

        <section id="security">
            <div class="heading">
                <i class="fa-solid fa-shield"></i>
                <h3>Security</h3>
                <p>Manage your account security</p>
            </div>
            <div class="settings-content">
                <form id="passwordForm" action="../api/authentication/passwordUpdate.php" method="post">
                    <div class="password-field">
                        <label for="currentPassword">Current Password</label>
                        <div class="input-group">
                            <input type="password" name="current" id="currentPassword" placeholder="Enter your current password" required>
                            <button type="button" class="toggle-password" data-target="currentPassword">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                    </div>
                    <div class="password-field">
                        <label for="newPassword">New Password</label>
                        <div class="input-group">
                            <input type="password" name="new" id="newPassword" placeholder="Enter your new password" required>
                            <button type="button" class="toggle-password" data-target="newPassword">
                                <i class="fa-solid fa-eye"></i>
                            </button>
                        </div>
                        <div class="password-requirements">
                            <small>Password must be at least 8 characters long</small>
                        </div>
                    </div>
                    <button type="submit" class="white">
                        <i class="fa-solid fa-lock"></i>
                        Change Password
                    </button>
                </form>
            </div>
        </section>

        <section id="payment">
            <div class="heading">
                <i class="fa-solid fa-credit-card"></i>
                <h3>Payment Methods</h3>
                <p>Manage your payment information</p>
            </div>
            <div class="settings-content">
                <div class="payment-info">
                    <?php
                    
                    if ($user_bank_details) {
                        // User has bank details - show current info and edit option
                        echo '<div class="current-payment-method">';
                        echo '<h5><i class="bi bi-check-circle-fill text-success"></i> Current Payment Method</h5>';
                        echo '<div class="bank-details-summary">';
                        echo '<p><strong>Account Holder:</strong> ' . htmlspecialchars($user_bank_details['account_holder_name']) . '</p>';
                        echo '<p><strong>Bank:</strong> ' . htmlspecialchars($user_bank_details['bank_name']) . '</p>';
                        echo '<p><strong>Account Number:</strong> ****' . substr($user_bank_details['account_number'], -4) . '</p>';
                        // echo '<p><strong>Status:</strong> ';
                        // if ($user_bank_details['is_verified']) {
                        //     echo '<span class="badge bg-success">Verified</span>';
                        // } else {
                        //     echo '<span class="badge bg-warning">Pending Verification</span>';
                        // }
                        echo '</p>';
                        echo '</div>';
                        echo '</div>';
                    } else {
                        // No bank details - show add prompt
                        echo '<p>Add and manage your payment methods for secure transactions</p>';
                        echo '<div class="alert alert-info">';
                        echo '<i class="bi bi-info-circle"></i> ';
                        echo 'You need to add your bank details to receive payments as a seller.';
                        echo '</div>';
                    }
                    ?>
                </div>
            </div>
            
            <?php if ($user_bank_details): ?>
                <!-- User has bank details - show edit button -->
                <div class="payment-actions">
                    <button type="button" class="white">
                        <a href="../pages/edit_bank_details.php">
                            <i class="bi bi-pencil-square"></i>
                            Edit Payment Method
                        </a>
                    </button>
                    <!-- <button type="button" class="white secondary">
                        <a href="../pages/bank_verification_help.php">
                            <i class="bi bi-question-circle"></i>
                            Verification Help
                        </a>
                    </button> -->
                </div>
            <?php else: ?>
                <!-- No bank details - show add button -->
                <button type="button" class="white">
                    <a href="../pages/user_bank_details.php">
                        <i class="bi bi-wallet"></i>
                        Add Payment Method
                    </a>
                </button>
            <?php endif; ?>
        </section>

        <section id="delete">
            <div class="heading">
                <i class="fa-solid fa-trash-alt"></i>
                <h3>Delete Account</h3>
                <p>Permanent actions that can't be undone</p>
            </div>
            <div class="settings-content">
                <div class="warning-box">
                    <i class="fa-solid fa-exclamation-triangle"></i>
                    <div>
                        <h6>Warning</h6>
                        <p>Deleting your account will permanently remove all your data, listings, and transaction history. This action cannot be undone.</p>
                    </div>
                </div>
            </div>
            <button type="button" class="white danger" id="deleteAccountBtn">
                <i class="fa-solid fa-trash"></i>
                Delete Account
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