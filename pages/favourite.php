<?php
    session_start();
    require_once '../includes/db_connection.php';

    if (!isset($_SESSION['userId'])) {
        $_SESSION['errors'] = ['Please log in to view your favorites.'];
        header('Location: ../pages/login.php?redirect=' . urlencode('../pages/favourite.php'));
        exit();
    }

    $userId = (int)$_SESSION['userId'];

    // Initialize variables
    $result = [];

    try {
        // Fetch all favorite products - No pagination
        $stmt = $conn->prepare("
            SELECT p.id, 
                   p.productName AS name, 
                   p.price, 
                   p.productCategory AS category, 
                   p.productPicture AS image_path, 
                   u.city AS location,
                   f.created_at
            FROM products p
            INNER JOIN favorites f ON p.id = f.productId
            LEFT JOIN users u ON p.userId = u.userId
            WHERE f.userId = ?
            ORDER BY f.created_at DESC
        ");
        
        $stmt->execute([$userId]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
        error_log("Error fetching favorites: " . $e->getMessage());
        $_SESSION['errors'] = ['Error fetching favorites: ' . $e->getMessage()];
        $result = [];
    }
?>

<!DOCTYPE html>
<html lang="en">
<?php  
    $pageTitle = "My Favourites"; 
    include('../includes/head.php');  
?> 
<head>
    <link rel="stylesheet" href="../assets/css/favourite.css"> 
</head>
<body>
    <?php include('../includes/header.php'); ?>
    
    <section id="page-header" class="about-header">
        <h2>#My Favourites</h2>
        <p>View Your Favs here!</p>
    </section>

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

    <?php if (isset($_SESSION['success']) && !empty($_SESSION['success'])): ?>
        <section class="section-p1">
            <div class="success-messages">
                <?php foreach ($_SESSION['success'] as $message): ?>
                    <p style="color: green;"><?php echo htmlspecialchars($message); ?></p>
                <?php endforeach; ?>
                <?php unset($_SESSION['success']); ?>
            </div>
        </section>
    <?php endif; ?>

    <?php include '../pages/product.php'; ?>

    <!-- <section class="section-p1">
        <table id="listingTable"> 
            <thead id="tableHeading"> 
                <tr> 
                    <th>Item</th> 
                    <th>Price</th> 
                    <th>Category</th> 
                    <th>Location</th> 
                    <th>Date Added</th> 
                    <th>Actions</th> 
                </tr> 
            </thead> 
            <tbody id="tabledata"> 
                <?php if (count($result) > 0): ?>
                    <?php foreach ($result as $row): ?>
                        <tr>
                            <td>
                                <?php 
                                $imagePath = '/uploads/product_pictures/default.jpg';
                                if (!empty($row['image_path'])) {
                                    $images = json_decode($row['image_path'], true);
                                    if (json_last_error() === JSON_ERROR_NONE && is_array($images) && !empty($images)) {
                                        $imagePath = htmlspecialchars($images[0]);
                                    } elseif (!is_array($images)) {
                                        $imagePath = htmlspecialchars($row['image_path']);
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
                                <?php echo htmlspecialchars($row['location'] ?? 'N/A'); ?>
                            </td>
                            <td>
                                <?php 
                                if (!empty($row['created_at'])) {
                                    echo date('Y-m-d', strtotime($row['created_at'])); 
                                } else {
                                    echo 'N/A';
                                }
                                ?>
                            </td>
                            <td>
                                <select name="action" class="action-select" data-product-id="<?php echo $row['id']; ?>">
                                    <option value="">Select Action</option>
                                    <option value="Add-to-Cart">Add to Cart</option>
                                    <option value="Remove">Remove from Favorites</option>
                                </select>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">You have no favorite products yet. <a href="../pages/shop.php">Browse products</a> to add some favorites!</td>
                    </tr>
                <?php endif; ?>
            </tbody> 
        </table>
    </section> -->

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const actionSelects = document.querySelectorAll('.action-select');
        
        actionSelects.forEach(select => {
            select.addEventListener('change', function() {
                const action = this.value;
                const productId = this.dataset.productId;
                
                if (action === 'Remove') {
                    if (confirm('Are you sure you want to remove this item from your favorites?')) {
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = '../handlers/toggle_favorite.php';
                        
                        const productInput = document.createElement('input');
                        productInput.type = 'hidden';
                        productInput.name = 'product_id';
                        productInput.value = productId;
                        
                        const redirectInput = document.createElement('input');
                        redirectInput.type = 'hidden';
                        redirectInput.name = 'redirect_url';
                        redirectInput.value = window.location.href;
                        
                        form.appendChild(productInput);
                        form.appendChild(redirectInput);
                        document.body.appendChild(form);
                        form.submit();
                    }
                }
                this.value = '';
            });
        });
    });
    </script>
</body>
</html> 