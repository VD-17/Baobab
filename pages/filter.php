<?php
session_start();
require_once '../includes/db_connection.php';

$searchQuery = isset($_GET['query']) ? trim($_GET['query']) : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';
$minPrice = isset($_GET['min_price']) ? (float)$_GET['min_price'] : 0;
$maxPrice = isset($_GET['max_price']) ? (float)$_GET['max_price'] : 0;
$location = isset($_GET['location']) ? $_GET['location'] : '';
$sortBy = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$productsPerPage = 20;
$offset = ($page - 1) * $productsPerPage;

// Get all categories for filter dropdown
try {
    $categoryStmt = $conn->prepare("SELECT DISTINCT productCategory FROM products WHERE productCategory IS NOT NULL ORDER BY productCategory");
    $categoryStmt->execute();
    $categories = $categoryStmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $categories = [];
}

// Get all locations
try {
    $locationStmt = $conn->prepare("SELECT DISTINCT city FROM users WHERE city IS NOT NULL ORDER BY city");
    $locationStmt->execute();
    $locations = $locationStmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $locations = [];
}

// Build search query with filters
$whereConditions = [];
$params = [];

// Text search
if (!empty($searchQuery)) {
    $whereConditions[] = "(p.productName LIKE :query OR p.description LIKE :query OR p.productCategory LIKE :query)";
    $params[':query'] = '%' . $searchQuery . '%';
}

// Category filter
if (!empty($category)) {
    $whereConditions[] = "p.productCategory = :category";
    $params[':category'] = $category;
}

// Price range filter
if ($minPrice > 0) {
    $whereConditions[] = "p.price >= :minPrice";
    $params[':minPrice'] = $minPrice;
}
if ($maxPrice > 0) {
    $whereConditions[] = "p.price <= :maxPrice";
    $params[':maxPrice'] = $maxPrice;
}

// Location filter
if (!empty($location)) {
    $whereConditions[] = "u.city = :location";
    $params[':location'] = $location;
}

// Build WHERE clause
$whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';

// Sorting
$orderClause = "ORDER BY ";
switch ($sortBy) {
    case 'price_low':
        $orderClause .= "p.price ASC";
        break;
    case 'price_high':
        $orderClause .= "p.price DESC";
        break;
    case 'oldest':
        $orderClause .= "p.created_at ASC";
        break;
    case 'name':
        $orderClause .= "p.productName ASC";
        break;
    default: // newest
        $orderClause .= "p.created_at DESC";
        break;
}

try {
    // Count total results
    $countSql = "SELECT COUNT(*) FROM products p LEFT JOIN users u ON p.userId = u.userId $whereClause";
    $countStmt = $conn->prepare($countSql);
    foreach ($params as $key => $value) {
        $countStmt->bindValue($key, $value);
    }
    $countStmt->execute();
    $totalProducts = $countStmt->fetchColumn();
    $totalPages = ceil($totalProducts / $productsPerPage);

    // Get filtered products
    $sql = "
        SELECT p.id, p.productName AS name, p.price, p.productCategory AS category, 
               p.productPicture AS image_path, p.created_at, u.city AS location,
               CONCAT(u.firstname, ' ', u.lastname) AS seller_name,
               (SELECT AVG(r.rating) FROM reviews r WHERE r.productId = p.id) AS average_rating
        FROM products p
        LEFT JOIN users u ON p.userId = u.userId
        $whereClause
        $orderClause
        LIMIT :limit OFFSET :offset
    ";
    
    $stmt = $conn->prepare($sql);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':limit', $productsPerPage, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $_SESSION['errors'] = ["Search error: " . $e->getMessage()];
    $products = [];
    $totalProducts = 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<?php
$pageTitle = "Search Results";
include('../includes/head.php');
?>
<head>
    <link rel="stylesheet" href="../assets/css/shop.css">
    <link rel="stylesheet" href="../assets/css/filters.css">
</head>
<body>
    <?php include('../includes/header.php'); ?>

    <div class="search-container">
        <!-- Filters Sidebar -->
        <aside class="filters-sidebar">
            <h3>Filters</h3>
            <form id="filterForm" method="GET" action="">
                <!-- Keep search query -->
                <input type="hidden" name="query" value="<?php echo htmlspecialchars($searchQuery); ?>">
                
                <!-- Category Filter -->
                <div class="filter-group">
                    <h4>Category</h4>
                    <select name="category" onchange="applyFilters()">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat); ?>" 
                                    <?php echo ($category === $cat) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Price Range Filter -->
                <div class="filter-group">
                    <h4>Price Range</h4>
                    <div class="price-inputs">
                        <input type="number" name="min_price" placeholder="Min" 
                               value="<?php echo $minPrice > 0 ? $minPrice : ''; ?>" 
                               onchange="applyFilters()">
                        <span>to</span>
                        <input type="number" name="max_price" placeholder="Max" 
                               value="<?php echo $maxPrice > 0 ? $maxPrice : ''; ?>" 
                               onchange="applyFilters()">
                    </div>
                    <!-- Price Range Slider (Optional) -->
                    <div class="price-slider">
                        <input type="range" id="priceRange" min="0" max="10000" step="100" 
                               value="<?php echo $maxPrice > 0 ? $maxPrice : 10000; ?>" 
                               oninput="updatePriceDisplay(this.value)">
                        <div class="price-display">Max: R<span id="priceValue"><?php echo $maxPrice > 0 ? $maxPrice : 10000; ?></span></div>
                    </div>
                </div>

                <!-- Location Filter -->
                <div class="filter-group">
                    <h4>Location</h4>
                    <select name="location" onchange="applyFilters()">
                        <option value="">All Locations</option>
                        <?php foreach ($locations as $loc): ?>
                            <option value="<?php echo htmlspecialchars($loc); ?>" 
                                    <?php echo ($location === $loc) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($loc); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Clear Filters Button -->
                <button type="button" class="clear-filters" onclick="clearFilters()">
                    Clear All Filters
                </button>
            </form>
        </aside>

        <!-- Main Content -->
        <main class="search-results">
            <!-- Search Header -->
            <div class="search-header">
                <div class="search-info">
                    <h2>
                        <?php if (!empty($searchQuery)): ?>
                            Search Results for "<?php echo htmlspecialchars($searchQuery); ?>"
                        <?php else: ?>
                            All Products
                        <?php endif; ?>
                    </h2>
                    <p><?php echo $totalProducts; ?> products found</p>
                </div>

                <!-- Sort Options -->
                <div class="sort-options">
                    <label for="sort">Sort by:</label>
                    <select name="sort" id="sort" onchange="applySort(this.value)">
                        <option value="newest" <?php echo ($sortBy === 'newest') ? 'selected' : ''; ?>>Newest First</option>
                        <option value="oldest" <?php echo ($sortBy === 'oldest') ? 'selected' : ''; ?>>Oldest First</option>
                        <option value="price_low" <?php echo ($sortBy === 'price_low') ? 'selected' : ''; ?>>Price: Low to High</option>
                        <option value="price_high" <?php echo ($sortBy === 'price_high') ? 'selected' : ''; ?>>Price: High to Low</option>
                        <option value="name" <?php echo ($sortBy === 'name') ? 'selected' : ''; ?>>Name A-Z</option>
                    </select>
                </div>
            </div>

            <!-- Active Filters Display -->
            <div class="active-filters">
                <?php if (!empty($category)): ?>
                    <span class="filter-tag">
                        Category: <?php echo htmlspecialchars($category); ?>
                        <a href="<?php echo removeFilter('category'); ?>">×</a>
                    </span>
                <?php endif; ?>
                
                <?php if ($minPrice > 0 || $maxPrice > 0): ?>
                    <span class="filter-tag">
                        Price: R<?php echo $minPrice; ?> - R<?php echo $maxPrice > 0 ? $maxPrice : '∞'; ?>
                        <a href="<?php echo removeFilter('price'); ?>">×</a>
                    </span>
                <?php endif; ?>
                
                <?php if (!empty($location)): ?>
                    <span class="filter-tag">
                        Location: <?php echo htmlspecialchars($location); ?>
                        <a href="<?php echo removeFilter('location'); ?>">×</a>
                    </span>
                <?php endif; ?>
            </div>

            <!-- Products Grid -->
            <!-- <div class="products-grid">
                <?php if (!empty($products)): ?>
                    <?php foreach ($products as $product): ?>
                        <div class="product-card">
                            <div class="product-image">
                                <?php 
                                $imagePath = '../assets/images/default.jpg';
                                if (!empty($product['image_path'])) {
                                    $images = json_decode($product['image_path'], true);
                                    if (is_array($images) && !empty($images)) {
                                        $cleanPath = ltrim($images[0], '/');
                                        $imagePath = '../' . htmlspecialchars($cleanPath);
                                    }
                                }
                                ?>
                                <img src="<?php echo $imagePath; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                            </div>
                            <div class="product-info">
                                <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                                <p class="price">R<?php echo number_format($product['price'], 2); ?></p>
                                <p class="category"><?php echo htmlspecialchars($product['category']); ?></p>
                                <p class="location"><?php echo htmlspecialchars($product['location'] ?? 'Location not specified'); ?></p>
                                <p class="seller">by <?php echo htmlspecialchars($product['seller_name']); ?></p>
                                <?php if ($product['average_rating']): ?>
                                    <p class="rating"><?php echo number_format($product['average_rating'], 1); ?>/5 ⭐</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="no-results">
                        <h3>No products found</h3>
                        <p>Try adjusting your filters or search terms.</p>
                    </div>
                <?php endif; ?>
            </div> -->

            <?php include('../pages/product.php'); ?>


            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="<?php echo buildUrl(['page' => $page - 1]); ?>">&laquo; Previous</a>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <a href="<?php echo buildUrl(['page' => $i]); ?>" 
                           <?php echo ($i == $page) ? 'class="active"' : ''; ?>>
                           <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $totalPages): ?>
                        <a href="<?php echo buildUrl(['page' => $page + 1]); ?>">Next &raquo;</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <?php include('../includes/footer.php'); ?>

    <script src="../assets/js/filters.js"></script>
</body>
</html>

<?php
// Helper functions
function buildUrl($params = []) {
    $currentParams = $_GET;
    $newParams = array_merge($currentParams, $params);
    return '?' . http_build_query($newParams);
}

function removeFilter($filterType) {
    $currentParams = $_GET;
    switch ($filterType) {
        case 'category':
            unset($currentParams['category']);
            break;
        case 'price':
            unset($currentParams['min_price'], $currentParams['max_price']);
            break;
        case 'location':
            unset($currentParams['location']);
            break;
    }
    return '?' . http_build_query($currentParams);
}
?>