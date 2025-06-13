<?php
    session_start();
    require_once '../includes/db_connection.php';

    if (!isset($_SESSION['userId'])) {
        header('Location: ../pages/signIn.php');
        exit();
    }

    $userId = (int)$_SESSION['userId'];

    try {
        // Get cart items for the specific user
        $stmt = $conn->prepare("
            SELECT c.id as cart_id, c.quantity, 
                   p.id, p.productName AS name, p.price, 
                   p.productPicture AS image_path
            FROM cart c
            INNER JOIN products p ON c.productId = p.id
            WHERE c.userId = ?
        ");
        $stmt->execute([$userId]);
        $cartItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Calculate totals
        $subtotal = 0;
        foreach ($cartItems as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }
        $total = $subtotal; // Assuming free shipping

    } catch (PDOException $e) {
        $_SESSION['errors'] = ["Error fetching cart items: " . $e->getMessage()];
        $cartItems = [];
        $subtotal = 0;
        $total = 0;
    }
?>

<!DOCTYPE html>
<html lang="en">
<?php  
    $pageTitle = "My Cart"; 
    include('../includes/head.php');  
?> 
<head>
    <link rel="stylesheet" href="../assets/css/cart.css"> 
</head>
<body>
    <?php include('../includes/header.php'); ?>

    <section id="page-header" class="about-header">
        <h2>#Happy Shopping</h2>
        <p>LEAVE A MESSAGE, We love to hear from you!</p>
    </section>

    <section id="cart" class="section-p1">
        <table width="100%">
            <thead>
                <tr>
                    <td>Remove</td>
                    <td>IMAGE</td>
                    <td>PRODUCT</td>
                    <td>PRICE</td>
                    <td>QUANTITY</td>
                    <td>SUBTOTAL</td>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($cartItems)): ?>
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 20px;">
                            Your cart is empty
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($cartItems as $item): ?>
                        <tr>
                            <td>
                                <a href="remove_from_cart.php?cart_id=<?php echo $item['cart_id']; ?>" class="remove-item">
                                    <i class="far fa-times-circle"></i>
                                </a>
                            </td>
                            <td>
                                <?php
                                    $imagePath = '../assets/images/default.jpg';
                                    if (!empty($item['image_path'])) {
                                        $images = json_decode($item['image_path'], true);
                                        if (is_array($images) && !empty($images)) {
                                            $imagePath = '../' . htmlspecialchars($images[0]);
                                        } elseif (!is_array($images)) {
                                            $imagePath = '../' . htmlspecialchars($item['image_path']);
                                        }
                                    }
                                ?>
                                <img src="<?php echo $imagePath; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" style="width: 80px; height: 80px; object-fit: cover;">
                            </td>
                            <td><?php echo htmlspecialchars($item['name']); ?></td>
                            <td>R<?php echo number_format($item['price'], 2); ?></td>
                            <td>
                                <input type="number" value="<?php echo $item['quantity']; ?>" min="1" 
                                       onchange="updateQuantity(<?php echo $item['cart_id']; ?>, this.value)">
                            </td>
                            <td>R<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </section>

    <section id="cart-add" class="section-p1">
        <div id="subtotal">
            <h3>Cart Totals</h3>
            <table>
                <tr>
                    <td>Cart Subtotal</td>
                    <td>R<?php echo number_format($subtotal, 2); ?></td>
                </tr>
                <tr>
                    <td>Shipping</td>
                    <td>Free</td>
                </tr>
                <tr>
                    <td><strong>Total</strong></td>
                    <td><strong>R<?php echo number_format($total, 2); ?></strong></td>
                </tr>
            </table>
            <?php if (!empty($cartItems)): ?>
                <button class="normal" onclick="window.location.href='../pages/payment.php?userId=<?php echo $_SESSION['userId']; ?>'">
                    Proceed to checkout
                </button>
            <?php endif; ?>
        </div>
    </section>

    <script>
        function updateQuantity(cartId, quantity) {
            if (quantity < 1) {
                alert('Quantity must be at least 1');
                return;
            }
            
            fetch('update_cart_quantity.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    cart_id: cartId,
                    quantity: quantity
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Error updating quantity');
                }
            });
        }
    </script>
</body>
</html>