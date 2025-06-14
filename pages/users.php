<?php
session_start();
require_once '../includes/db_connection.php';

// Check if user is admin
if (!isset($_SESSION['isAdmin']) || $_SESSION['isAdmin'] != 1) {
    $_SESSION['errors'] = ["Access denied. Admin privileges required."];
    header("Location: ../root/index.php");
    exit;
}

try {
    $stmt = $conn->prepare("
        SELECT userId, firstname, lastname, email, street_address, suburb, postal_code, province, city, 
               profile_picture, created_at, phoneNumber, is_seller 
        FROM users 
        ORDER BY created_at DESC
    ");
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $_SESSION['errors'] = ["Error fetching users: " . $e->getMessage()];
    $result = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<?php
$pageTitle = "All Users - Admin";
include('../includes/head.php');
?>
<head>
    <link rel="stylesheet" href="../assets/css/myListing.css">
    <link rel="stylesheet" href="../assets/css/sidebar.css">
</head>
<body id="mylist">

    <section id="sidebar">
        <ul>
            <li id="logo"><a href="../root/index.php"><img src="../assets/images/Logo/Baobab_favicon.png" alt="Baobab logo"></a></li>
            <li><a href="../pages/adminDashboard.php?userId=<?php echo htmlspecialchars($_SESSION['userId']); ?>"><i class="bi bi-grid-fill"></i>Dashboard</a></li>
            <li><a href="../pages/users.php" class="active"><i class="fa-solid fa-users"></i>Users</a></li>
            <li><a href="../pages/totalProducts.php"><i class="fa-solid fa-box"></i>Products</a></li>
            <li><a href="../pages/admin_payouts.php"><i class="bi bi-arrow-left-right"></i>Transactions</a></li>
            <li><a href="../pages/support.php"><i class="fa-solid fa-message"></i>Messages</a></li>
            <li><a href="../pages/analytics.php"><i class="fa-solid fa-chart-simple"></i>Analytics</a></li>
            <li><a href="../pages/admins.php"><i class="fa-solid fa-user-tie"></i>Admins</a></li>
            <li><a href=""><i class="fa-solid fa-gear"></i>Settings</a></li>
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
                    <h1>All Users</h1>
                </div>
                <p>Manage all users on the platform</p>
                <div class="table-container">
                    <table id="listingTable">
                        <thead id="tableHeading">
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Location</th>
                                <th>Is Seller</th>
                                <th>Registered Date</th>
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
                                            if (!empty($row['profile_picture'])) {
                                                $images = json_decode($row['profile_picture'], true);
                                                if (is_array($images) && !empty($images)) {
                                                    $cleanPath = ltrim($images[0], '/');
                                                    $imagePath = '../' . htmlspecialchars($cleanPath);
                                                } elseif (!is_array($images)) {
                                                    $cleanPath = ltrim($row['profile_picture'], '/');
                                                    $imagePath = '../' . htmlspecialchars($cleanPath);
                                                }
                                            }
                                            ?>
                                            <img src="<?php echo $imagePath; ?>" alt="Profile Picture" width="50" height="50" style="object-fit: cover; border-radius: 4px; margin-right: 10px;">
                                            <?php echo htmlspecialchars($row['firstname'] . ' ' . $row['lastname']); ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($row['email']); ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($row['phoneNumber']); ?>
                                        </td>
                                        <td>
                                            <?php echo htmlspecialchars($row['street_address'] . ', ' . $row['suburb']); ?>
                                            <?php echo htmlspecialchars($row['city'] . ', ' . $row['province']); ?>
                                        </td>
                                        <td>
                                            <?php echo $row['is_seller'] ? 'Yes' : 'No'; ?>
                                        </td>
                                        <td>
                                            <?php echo date('Y-m-d', strtotime($row['created_at'])); ?>
                                        </td>
                                        <td>
                                            <select name="action" class="action-select" data-user-id="<?php echo htmlspecialchars($row['userId']); ?>">
                                                <option value="">Select Action</option>
                                                <option value="View">View</option>
                                                <option value="Delete">Delete</option>
                                            </select>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7">No users found.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const actionSelects = document.querySelectorAll('.action-select');

            actionSelects.forEach(select => {
                select.addEventListener('change', function () {
                    const action = this.value;
                    const userId = this.getAttribute('data-user-id'); 

                    if (action === 'View') {
                        window.location.href = `../pages/profile.php`;
                    } else if (action === 'Delete') {
                        if (confirm('Are you sure you want to delete this user?')) {
                            fetch('../api/Listing/deleteUser.php', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded',
                                },
                                body: `userId=${userId}`
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.success) {
                                    alert(data.message);
                                    this.closest('tr').remove();
                                    const tbody = document.querySelector('#tabledata');
                                    if (tbody.children.length === 0) {
                                        tbody.innerHTML = '<tr><td colspan="7">No users found.</td></tr>';
                                    }
                                } else {
                                    alert(data.message);
                                }
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                alert('An error occurred while deleting the user: ' + error.message);
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