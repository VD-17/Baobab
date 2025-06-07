<?php
// ob_start(); 
session_start();
require_once '../includes/db_connection.php';
// ob_clean(); 

header('Content-Type: application/json');

if (!isset($_SESSION['userId'])) {
    echo json_encode(['error' => 'You must be logged in to submit a review.']);
    exit;
}

$productId = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;

// Handle review submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['action'])) {
    $reviewer = $_POST['userName'] ?? '';
    $message = $_POST['userMessage'] ?? '';
    $rating = isset($_POST['rating_value']) ? (int)$_POST['rating_value'] : 0;

    if (empty($reviewer) || empty($message) || $productId <= 0 || $rating < 1 || $rating > 5) {
        echo json_encode(['error' => 'All fields are required, product ID must be valid, and rating must be between 1 and 5.']);
        exit;
    }

    try {
        // Check if user already reviewed this product
        $checkStmt = $conn->prepare("SELECT COUNT(*) FROM reviews WHERE productId = ? AND userId = ?");
        $checkStmt->execute([$productId, $_SESSION['userId']]);
        $existingReview = $checkStmt->fetchColumn();

        if ($existingReview > 0) {
            echo json_encode(['error' => 'You have already reviewed this product.']);
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO reviews (productId, userId, reviewer, message, rating, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
        $stmt->execute([$productId, $_SESSION['userId'], $reviewer, $message, $rating]);
        
        echo json_encode(['success' => 'Review submitted successfully.']);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Error submitting review: ' . $e->getMessage()]);
    }
    exit;
}

// Handle load data request
if (isset($_POST['action']) && $_POST['action'] === 'load_data') {
    if ($productId <= 0) {
        echo json_encode(['error' => 'Invalid product ID.']);
        exit;
    }

    try {
        $stmt = $conn->prepare("SELECT r.id as reviewId, r.rating, r.message, r.userId, r.created_at, u.firstname, u.lastname 
                                FROM reviews r 
                                LEFT JOIN users u ON r.userId = u.userId 
                                WHERE r.productId = ? 
                                ORDER BY r.created_at DESC");
        $stmt->execute([$productId]);
        $reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $totalReviews = count($reviews);
        $totalRatings_avg = 0;
        $ratingCounts = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];
        $ratingsList = [];

        foreach ($reviews as $row) {
            $rating = (int)$row['rating'];
            $totalRatings_avg += $rating;
            $ratingCounts[$rating]++;
            $ratingsList[] = [
                'review_id' => $row['reviewId'],
                'name' => trim($row['firstname'] . ' ' . $row['lastname']),
                'rating' => $rating,
                'message' => $row['message'],
                'datetime' => date('jS \of F Y h:i:s A', strtotime($row['created_at']))
            ];
        }

        $averageUserRatings = $totalReviews > 0 ? $totalRatings_avg / $totalReviews : 0;

        $output = [
            'averageRatings' => number_format($averageUserRatings, 1),
            'totalReviews' => $totalReviews,
            'totalRatings5' => $ratingCounts[5],
            'totalRatings4' => $ratingCounts[4],
            'totalRatings3' => $ratingCounts[3],
            'totalRatings2' => $ratingCounts[2],
            'totalRatings1' => $ratingCounts[1],
            'ratingsList' => $ratingsList
        ];

        echo json_encode($output);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Error fetching reviews: ' . $e->getMessage()]);
    }
    exit;
}

echo json_encode(['error' => 'Invalid request.']);
exit;
?>