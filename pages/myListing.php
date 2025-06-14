<?php  
    session_start();
    require_once '../includes/db_connection.php';

    // Check if the user is logged in
    if (!isset($_SESSION['userId'])) {
        $_SESSION['errors'] = ["You must be logged in to view your listings."];
        header("Location: ../pages/signIn.php");
        exit;
    }

    $userId = (int)$_SESSION['userId'];

    try {
        // Fetch only the products belonging to the logged-in user
        $stmt = $conn->prepare("
            SELECT id, productName AS name, price, productCategory AS category, status, created_at, productPicture AS image_path 
            FROM products
            WHERE userId = :userId
            ORDER BY created_at DESC
        ");
        $stmt->execute(['userId' => $userId]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $_SESSION['errors'] = ["Error fetching your products: " . $e->getMessage()];
        $result = [];
    }
?> 
 
<!DOCTYPE html> 
<html lang="en"> 
<?php  
$pageTitle = "My Listings"; 
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
    
    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" onclick="closeSidebar()"></div>
    
    <?php include('../includes/sidebar.php'); ?>

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
                    <h1>My Product Listings</h1>
                    <button class="normal" onclick="window.location.href='../pages/listing.php'">
                        <i class="fa-solid fa-plus"></i> Sell
                    </button>
                </div>
                <div class="table-container">
                    <table id="listingTable"> 
                        <thead id="tableHeading"> 
                            <tr> 
                                <th>Item</th> 
                                <th>Price</th> 
                                <th>Category</th> 
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
                                            <img src="<?php echo $imagePath; ?>" alt="Product Image" width="50" height="50" style="object-fit: cover; border-radius: 4px;">
                                            <span><?php echo htmlspecialchars($row['name']); ?></span>
                                        </td>
                                        <td>R<?php echo number_format($row['price'], 2); ?></td>
                                        <td><?php echo htmlspecialchars($row['category']); ?></td>
                                        <td><?php echo htmlspecialchars($row['status']); ?></td>
                                        <td><?php echo date('Y-m-d', strtotime($row['created_at'])); ?></td>
                                        <td>
                                            <select name="action" class="action-select" data-product-id="<?php echo $row['id']; ?>">
                                                <option value="">Select Action</option>
                                                <option value="View">View</option>
                                                <option value="Edit">Edit</option>
                                                <option value="Delete">Delete</option>
                                            </select>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6">You have no products listed yet. <a href="../pages/listing.php">Add a product</a> to get started!</td>
                                </tr>
                            <?php endif; ?>
                        </tbody> 
                    </table>
                </div>
                <!-- <table id="listingTable"> 
                    <thead id="tableHeading"> 
                        <tr> 
                            <th>Item</th> 
                            <th>Price</th> 
                            <th>Category</th> 
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
                                                // Remove leading slash if present and add proper path
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
                                        <?php echo htmlspecialchars($row['status']); ?>
                                    </td>
                                    <td>
                                        <?php echo date('Y-m-d', strtotime($row['created_at'])); ?>
                                    </td>
                                    <td>
                                        <select name="action" class="action-select" data-product-id="<?php echo $row['id']; ?>">
                                            <option value="">Select Action</option>
                                            <option value="View">View</option>
                                            <option value="Edit">Edit</option>
                                            <option value="Delete">Delete</option>
                                        </select>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6">You have no products listed yet. <a href="../pages/listing.php">Add a product</a> to get started!</td>
                            </tr>
                        <?php endif; ?>
                    </tbody> 
                </table>  -->
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
                    const userId = <?php echo json_encode($_SESSION['userId']); ?>;

                    if (action === 'View') {
                        window.location.href = `../pages/viewProduct.php?productId=${productId}`;
                    } else if (action === 'Edit') {
                        // Fixed: Changed 'productId' to 'id' to match editProduct.php expectations
                        window.location.href = `../pages/editProduct.php?id=${productId}&userId=${userId}`;
                    } else if (action === 'Delete') {
                        if (confirm('Are you sure you want to delete this product?')) {
                            // Delete logic remains the same
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
                                        tbody.innerHTML = '<tr><td colspan="6">You have no products listed yet. <a href="../pages/listing.php">Add a product</a> to get started!</td></tr>';
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
                    }
                    this.value = '';
                });
            });
        });
    </script>
    <!-- <script src="../assets/js/myListing.js"></script>  -->
</body> 
</html>