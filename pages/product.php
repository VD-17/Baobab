<!-- <section id="product1" class="section-p1">
    <div class="pro-container">
        <?php if (count($result) > 0): ?>
            <?php foreach ($result as $row): ?>
                <div class="pro" onclick="window.location.href='../pages/viewProduct.php?productId=<?php echo $row['id']; ?>&page=<?php echo isset($_GET['page']) ? (int)$_GET['page'] : 1; ?>';">
                    <div id="categoryName">
                        <a href="#"><?php echo htmlspecialchars($row['category']); ?></a>
                    </div>
                    <?php
                    $imagePath = '/Uploads/product_pictures/default.jpg';
                    if (!empty($row['image_path'])) {
                        $images = json_decode($row['image_path'], true);
                        if (is_array($images) && !empty($images)) {
                            $imagePath = htmlspecialchars($images[0]);
                        } elseif (!is_array($images)) {
                            $imagePath = htmlspecialchars($row['image_path']);
                        }
                    }
                    ?>
                    <img src="<?php echo $imagePath; ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
                    <div class="des">
                        <div class="des1">
                            <span><?php echo htmlspecialchars($row['location'] ?? 'Unknown'); ?></span>
                            <h5><?php echo htmlspecialchars($row['name']); ?></h5>
                            <h4>R<?php echo number_format($row['price'], 2); ?></h4>
                        </div>
                        <div class="des2">
                            <div class="addToCart">
                                <a href="../pages/cart.php"><i class="fa-solid fa-cart-shopping"></i></a>
                            </div>
                            <div class="fav">
                                <a href=""><i class="fa-regular fa-heart"></i></a>
                            </div>
                            <div id="star">
                                <?php
                                $averageRating = $row['average_rating'] ?? 0;
                                for ($i = 1; $i <= 5; $i++):
                                ?>
                                    <i class="fa-solid fa-star <?php echo $i <= round($averageRating) ? 'text-warning' : ''; ?>"></i>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No products found.</p>
        <?php endif; ?>
    </div>
</section> -->

<section id="product1" class="section-p1">
    <div class="pro-container">
        <?php if (count($result) > 0): ?>
            <?php foreach ($result as $row): ?>
                <div class="pro" data-product-id="<?php echo $row['id']; ?>">
                    <div id="categoryName">
                        <a href="../pages/category.php?category=<?php echo urlencode($row['category']); ?>">
                            <?php echo htmlspecialchars($row['category']); ?>
                        </a>
                    </div>
                    
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
                    
                    <div class="product-image" onclick="viewProduct(<?php echo $row['id']; ?>)">
                        <img src="<?php echo $imagePath; ?>" 
                             alt="<?php echo htmlspecialchars($row['name']); ?>"
                             onerror="this.src='../assets/images/default.jpg'">
                        
                        <!-- Quick view overlay -->
                        <!-- <div class="quick-view">
                            <span>Quick View</span>
                        </div> -->
                    </div>
                    
                    <div class="des">
                        <div class="des1">
                            <span class="location">
                                <i class="fa-solid fa-location-dot"></i>
                                <?php echo htmlspecialchars($row['location'] ?? 'Unknown'); ?>
                            </span>
                            <h5 class="product-name" onclick="viewProduct(<?php echo $row['id']; ?>)">
                                <?php echo htmlspecialchars($row['name']); ?>
                            </h5>
                            <h4 class="price">R<?php echo number_format($row['price'], 2); ?></h4>
                        </div>
                        
                        <div class="des2">
                            <div class="product-actions">
                                <!-- Add to Cart Form -->
                                <?php if (isset($_SESSION['userId'])): ?>
                                    <form action="../pages/add_to_cart.php" method="POST" class="inline-form">
                                        <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                                        <input type="hidden" name="quantity" value="1">
                                        <input type="hidden" name="redirect_url" value="<?php echo $_SERVER['REQUEST_URI']; ?>">
                                        <button type="submit" class="action-btn add-to-cart" title="Add to Cart">
                                            <i class="fa-solid fa-cart-shopping"></i>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <a href="../pages/signIn.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" 
                                       class="action-btn add-to-cart" title="Login to Add to Cart">
                                        <i class="fa-solid fa-cart-shopping"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <!-- Add to Favorites Form -->
                                <?php if (isset($_SESSION['userId'])): ?>
                                    <form action="../pages/toggle_favorite.php" method="POST" class="inline-form">
                                        <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                                        <input type="hidden" name="redirect_url" value="<?php echo $_SERVER['REQUEST_URI']; ?>">
                                        <button type="submit" class="action-btn favorite" title="Add to Favorites">
                                            <?php
                                            // Check if product is already in favorites
                                            $isFavorite = false;
                                            if (isset($_SESSION['userId'])) {
                                                try {
                                                    $favStmt = $conn->prepare("SELECT id FROM favorites WHERE userId = ? AND productId = ?");
                                                    $favStmt->execute([$_SESSION['userId'], $row['id']]);
                                                    $isFavorite = $favStmt->rowCount() > 0;
                                                } catch (PDOException $e) {
                                                    // Handle error silently
                                                }
                                            }
                                            ?>
                                            <i class="<?php echo $isFavorite ? 'fa-solid' : 'fa-regular'; ?> fa-heart"></i>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <a href="../pages/login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" 
                                       class="action-btn favorite" title="Login to Add to Favorites">
                                        <i class="fa-regular fa-heart"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                            
                            <div class="rating">
                                <?php
                                $averageRating = $row['average_rating'] ?? 0;
                                $fullStars = floor($averageRating);
                                $hasHalfStar = ($averageRating - $fullStars) >= 0.5;
                                $emptyStars = 5 - $fullStars - ($hasHalfStar ? 1 : 0);
                                ?>
                                
                                <?php for ($i = 0; $i < $fullStars; $i++): ?>
                                    <i class="fa-solid fa-star filled"></i>
                                <?php endfor; ?>
                                
                                <?php if ($hasHalfStar): ?>
                                    <i class="fa-solid fa-star-half-stroke filled"></i>
                                <?php endif; ?>
                                
                                <?php for ($i = 0; $i < $emptyStars; $i++): ?>
                                    <i class="fa-regular fa-star"></i>
                                <?php endfor; ?>
                                
                                <span class="rating-text">(<?php echo number_format($averageRating, 1); ?>)</span>
                            </div>
                        </div>
                    </div>
                    
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-products">
                <i class="fa-solid fa-box-open"></i>
                <h3>No products found</h3>
                <p>Try adjusting your search criteria or browse our categories.</p>
                <a href="../pages/shop.php" class="btn-primary">Browse All Products</a>
            </div>
        <?php endif; ?>
    </div>
</section>

<script>
function viewProduct(productId) {
    const currentPage = <?php echo isset($_GET['page']) ? (int)$_GET['page'] : 1; ?>;
    window.location.href = `../pages/viewProduct.php?productId=${productId}&page=${currentPage}`;
}
</script>