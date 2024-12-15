<?php
session_start();
require_once 'config/database.php';
$conn = connectDB();

// Cek apakah keranjang kosong
if (empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit;
}

// Ambil data pengguna yang sedang login
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];

    // Query untuk mengambil data pengguna
    $userQuery = "SELECT name, email, address, phone FROM users WHERE id = ?";
    $userStmt = $conn->prepare($userQuery);

    if ($userStmt === false) {
        // Jika terjadi error saat prepare, tampilkan pesan error
        die('Error preparing statement: ' . $conn->error);
    }

    // Bind parameter dan eksekusi query
    $userStmt->bind_param("i", $userId);
    $userStmt->execute();

    $userResult = $userStmt->get_result();

    if ($userResult->num_rows > 0) {
        $user = $userResult->fetch_assoc();
    } else {
        // Jika pengguna tidak ditemukan
        header('Location: login.php');
        exit;
    }

    $userStmt->close();
} else {
    // Jika pengguna tidak login
    header('Location: login.php');
    exit;
}

$cartItems = $_SESSION['cart'];
$productIds = implode(',', array_keys($cartItems));
$query = "SELECT * FROM products WHERE id IN ($productIds)";
$result = $conn->query($query);
$products = [];
while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}

// Hitung total harga
$totalPrice = 0;
foreach ($products as $product) {
    $quantity = $cartItems[$product['id']];
    $totalPrice += $product['price'] * $quantity;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout | Ruby Parfum</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="logo">
                    <h1>Ruby Parfum</h1>
                </a>
            </div>
            <div class="search-bar">
                <input type="text" placeholder="Search products...">
                <button type="submit">Search</button>
            </div>
            <div class="nav-links">
                <a href="cart.php">Cart</a>
                <a href="logout.php">Logout</a>
            </div>
        </nav>
    </header>

    <main>
        <div class="checkout-container">
            <h2>Checkout</h2>

            <div class="checkout-section">
                <h3>Your Order</h3>
                <table class="checkout-table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): 
                            $quantity = $cartItems[$product['id']];
                            $subtotal = $product['price'] * $quantity;
                        ?>
                        <tr>
                            <td>
                                <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($product['name']); ?>" width="50">
                                <?php echo htmlspecialchars($product['name']); ?>
                            </td>
                            <td>Rp.<?php echo number_format($product['price'], 2); ?></td>
                            <td><?php echo $quantity; ?></td>
                            <td>Rp.<?php echo number_format($subtotal, 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="checkout-summary">
                    <p><strong>Total Price:</strong> Rp.<?php echo number_format($totalPrice, 2); ?></p>
                </div>
            </div>

            <div class="checkout-section">
                <h3>Payment Method</h3>
                <form action="process_checkout.php" method="POST">
                    <label for="payment-method">Choose a payment method:</label>
                    <select id="payment-method" name="payment_method" required>
                        <option value="bank_transfer">Bank Transfer</option>
                    </select>

                    <button type="submit" class="checkout-btn">Complete Checkout</button>
                </form>
            </div>
        </div>
    </main>

    <footer>
        <p>&copy; 2024 Ruby Parfum. All rights reserved.</p>
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html>
