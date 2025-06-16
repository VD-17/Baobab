<?php  
session_start();
require_once '../includes/db_connection.php';

// Check if user is admin
if (!isset($_SESSION['isAdmin']) || $_SESSION['isAdmin'] != 1) {
    $_SESSION['errors'] = ["Access denied. Admin privileges required."];
    header("Location: ../index.php");
    exit;
}

try {
    $stmt = $conn->prepare("
        SELECT p.id, p.userId, p.productName AS name, p.price, p.productCategory AS category, 
               p.status, p.created_at, p.productPicture AS image_path,
               u.firstname, u.lastname, u.email
        FROM products p
        LEFT JOIN users u ON p.userId = u.userId
        ORDER BY p.created_at DESC
    ");
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['errors'] = ["Error fetching products: " . $e->getMessage()];
    $result = [];
} 
?>
 
<!DOCTYPE html> 
<html lang="en"> 
<?php  
$pageTitle = "All Products - Admin"; 
include('../includes/head.php');  
?> 
<head>
    <link rel="stylesheet" href="../assets/css/myListing.css">
    <link rel="stylesheet" href="../assets/css/sidebar.css">
</head>
<body id="mylist">  
    <button class="mobile-menu-toggle" onclick="toggleSidebar()">
        <i class="fa-solid fa-bars"></i>
    </button>

    <div class="sidebar-overlay" onclick="closeSidebar()"></div>

    <section id="sidebar">
        <ul>
            <li id="logo"><a href="../index.php"><img src="../assets/images/Logo/Baobab_favicon.png" alt="Baobab logo"></a></li>
            <li><a href="../pages/adminDashboard.php?userId=<?php echo $_SESSION['userId']; ?>"><i class="bi bi-grid-fill"></i>Dashboard</a></li>
            <li><a href="../pages/users.php"><i class="fa-solid fa-users"></i>Users</a></li>
            <li><a href="../pages/totalProducts.php" class="active"><i class="fa-solid fa-box"></i>Products</a></li>
            <li><a href="../pages/admin_payouts.php"><i class="bi bi-arrow-left-right"></i>Transactions</a></li>
            <li><a href="../pages/support.php"><i class="fa-solid fa-message"></i>Messages</a></li>
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
                    <h1>All Product Listings</h1>
                </div>
                <p>Manage all products on the platform</p> 
                <div class="table-container">
                    <table id="listingTable"> 
                        <thead id="tableHeading"> 
                            <tr> 
                                <th>Item</th> 
                                <th>Price</th> 
                                <th>Category</th>
                                <th>Seller</th>
                                <th>Status</th> 
                                <th>Date</th> 
                                <th>Actions</th> 
                            </tr> 
                        </thead> 
                        <tbody id="tabledata"> 
                            <?php if (count($result) > 0): ?>
                                <?php foreach ($result as $row): ?>
                                    <tr>
                                        <td>
                                            <?php 
                                            $imagePath = '../assets/images/default.jpg';
                                            if (!empty($row['image_path'])) {
                                                $images = json_decode($row['image_path'], true);
                                                if (is_array($images) && !empty($images)) {
                                                    $cleanPath = ltrim($images[0], '/');
                                                    $imagePath = '../' . htmlspecialchars($cleanPath);
                                                } elseif (!is_array($images)) {
                                                    $cleanPath = ltrim($row['image_path'], '/');
                                                    $imagePath = '../' . htmlspecialchars($cleanPath);
                                                }
                                            }
                                            ?>
                                            <img src="<?php echo $imagePath; ?>" alt="Product Image" width="50" height="50" style="object-fit: cover; border-radius: 4px; margin-right: 10px;">
                                            <?php echo htmlspecialchars($row['name']); ?>
                                        </td>
                                        <td>
                                            R<?php echo number_format($row['price'], 2); ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($row['category']); ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($row['firstname'] . ' ' . $row['lastname']); ?><br>
                                            <small style="color: #666;"><?php echo htmlspecialchars($row['email']); ?></small>
                                        </td>
                                        <td>
                                            <span class="status-badge status-<?php echo strtolower($row['status']); ?>">
                                                <?php echo htmlspecialchars($row['status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php echo date('Y-m-d', strtotime($row['created_at'])); ?>
                                        </td>
                                        <td>
                                            <select name="action" class="action-select" data-product-id="<?php echo $row['id']; ?>" data-user-id="<?php echo $row['userId']; ?>">
                                                <option value="">Select Action</option>
                                                <option value="View">View</option>
                                                <option value="Approve" <?php echo $row['status'] === 'approved' ? 'style="display:none;"' : ''; ?>>Approve</option>
                                                <option value="Reject" <?php echo $row['status'] === 'rejected' ? 'style="display:none;"' : ''; ?>>Reject</option>
                                                <option value="Delete">Delete</option>
                                            </select>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7">No products found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody> 
                    </table>
                </div> 
            </div> 
        </section>
    </div>

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

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const actionSelects = document.querySelectorAll('.action-select');

            actionSelects.forEach(select => {
                select.addEventListener('change', function () {
                    const action = this.value;
                    const productId = this.getAttribute('data-product-id');
                    const sellerId = this.getAttribute('data-user-id');

                    if (action === 'View') {
                        window.location.href = `../pages/viewProduct.php?productId=${productId}`;
                    } else if (action === 'Delete') {
                        if (confirm('Are you sure you want to delete this product?')) {
                            fetch('../api/Listing/deleteProduct.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded',
                                },
                                body: `productId=${productId}`
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    alert(data.message);
                                    this.closest('tr').remove();
                                    const tbody = document.querySelector('#tabledata');
                                    if (tbody.children.length === 0) {
                                        tbody.innerHTML = '<tr><td colspan="7">No products found.</td></tr>';
                                    }
                                } else {
                                    alert(data.message);
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                alert('An error occurred while deleting the product: ' + error.message);
                            });
                        }
                    } else if (action === 'Approve' || action === 'Reject') {
                        const newStatus = action.toLowerCase() + 'd';
                        if (confirm(`Are you sure you want to ${action.toLowerCase()} this product?`)) {
                            fetch('../api/admin/updateProductStatus.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded',
                                },
                                body: `productId=${productId}&status=${newStatus}`
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    alert(data.message);
                                    // Update the status in the table
                                    const statusCell = this.closest('tr').querySelector('.status-badge');
                                    statusCell.textContent = newStatus;
                                    statusCell.className = `status-badge status-${newStatus}`;
                                    
                                    // Hide the action that was just performed
                                    const option = this.querySelector(`option[value="${action}"]`);
                                    option.style.display = 'none';
                                } else {
                                    alert(data.message);
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                alert('An error occurred while updating the product: ' + error.message);
                            });
                        }
                    }
                    this.value = '';
                });
            });
        });
    </script>
</body> 
</html>