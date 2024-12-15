<?php
session_start();
require_once 'config/database.php';
$conn = connectDB();

// Mengambil produk dari database jika diperlukan (misal untuk menampilkan nama atau harga)
$cartItems = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];

if (count($cartItems) > 0) {
    $productIds = implode(',', array_keys($cartItems));
    $query = "SELECT * FROM products WHERE id IN ($productIds)";
    $result = $conn->query($query);
    $products = [];
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart | Ruby Parfum</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <header>
        <nav class="navbar">
        <div class="logo">
                <a href="index.php" class="text-decoration-none text-dark">
                    <h1>Ruby Parfum</h1>
                </a>
            </div>
            <div class="nav-links">
                <a href="index.php">Home</a>
            </div>
        </nav>
    </header>

    <main class="py-5">
    <div class="container">
        <h2 class="text-center mb-4">Your Shopping Cart</h2>

        <?php if (count($cartItems) > 0): ?>
            <div class="cart-table-wrapper">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $totalPrice = 0;
                        foreach ($products as $product):
                            $quantity = $cartItems[$product['id']];
                            $subtotal = $product['price'] * $quantity;
                            $totalPrice += $subtotal;
                        ?>
                        <tr>
                            <td>
                                <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>" width="50" class="me-2">
                                <?php echo htmlspecialchars($product['name']); ?>
                            </td>
                            <td>Rp.<?php echo number_format($product['price'], 2); ?></td>
                            <td>
                                <form method="POST" action="update_cart.php" class="d-flex align-items-center">
                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                    <button type="submit" name="action" value="decrease" class="btn btn-outline-secondary btn-sm">-</button>
                                    <span class="mx-2"><?php echo $quantity; ?></span>
                                    <button type="submit" name="action" value="increase" class="btn btn-outline-secondary btn-sm">+</button>
                                </form>
                            </td>
                            <td>Rp.<?php echo number_format($subtotal, 2); ?></td>
                            <td>
                                <form method="POST" action="remove_from_cart.php">
                                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Remove</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <h5>Total: Rp.<?php echo number_format($totalPrice, 2); ?></h5>
                        <a href="checkout.php" class="btn btn-success">Proceed to Checkout</a>
                    </div>
                </div>
            <?php else: ?>
                <p class="text-center">Your cart is empty. Add some products to your cart!</p>
            <?php endif; ?>
        </div>
    </main>




    <footer>
        <p>&copy; 2024 Ruby Parfum. All rights reserved.</p>
    </footer>

    <script src="assets/js/main.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
