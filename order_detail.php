<?php
session_start();
require_once 'config/database.php';
$conn = connectDB();

// Periksa apakah pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['order_id'])) {
    header('Location: order_history.php');
    exit;
}

$orderId = $_GET['order_id'];

// Ambil detail pesanan berdasarkan order_id
$orderQuery = "SELECT o.id as order_id, o.total_price, o.payment_method, o.status, o.created_at, u.username
               FROM orders o
               JOIN users u ON o.user_id = u.id
               WHERE o.id = ? AND o.user_id = ?";

$orderStmt = $conn->prepare($orderQuery);
$orderStmt->bind_param("ii", $orderId, $_SESSION['user_id']);
$orderStmt->execute();
$orderResult = $orderStmt->get_result();

$order = $orderResult->fetch_assoc();

if (!$order) {
    // Pesanan tidak ditemukan
    header('Location: order_history.php');
    exit;
}

// Ambil detail produk dalam pesanan
$orderDetailQuery = "SELECT od.product_id, p.name, od.quantity, od.price
                     FROM order_details od
                     JOIN products p ON od.product_id = p.id
                     WHERE od.order_id = ?";
$orderDetailStmt = $conn->prepare($orderDetailQuery);
$orderDetailStmt->bind_param("i", $orderId);
$orderDetailStmt->execute();
$orderDetailsResult = $orderDetailStmt->get_result();

$orderDetails = [];
while ($detail = $orderDetailsResult->fetch_assoc()) {
    $orderDetails[] = $detail;
}

$orderDetailStmt->close();
$orderStmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details | Ruby Parfum</title>
    <link rel="stylesheet" href="assets/css/style.css">
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
                <a href="cart.php">Cart</a>
                <a href="order_history.php">Order History</a>
                <a href="logout.php">Logout</a>
            </div>
        </nav>
    </header>

    <main>
        <div class="order-detail-container">
            <h2>Order #<?php echo $order['order_id']; ?> Details</h2>

            <div class="order-info">
                <p><strong>Order Date:</strong> <?php echo date('Y-m-d H:i', strtotime($order['created_at'])); ?></p>
                <p><strong>Username:</strong> <?php echo $order['username']; ?></p>
                <p><strong>Total Price:</strong> $<?php echo number_format($order['total_price'], 2); ?></p>
                <p><strong>Payment Method:</strong> <?php echo ucfirst($order['payment_method']); ?></p>
                <p><strong>Status:</strong> <?php echo ucfirst($order['status']); ?></p>
            </div>

            <h3>Ordered Products</h3>
            <table class="order-detail-table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($orderDetails as $detail): ?>
                        <tr>
                            <td><?php echo $detail['name']; ?></td>
                            <td><?php echo $detail['quantity']; ?></td>
                            <td>$<?php echo number_format($detail['price'], 2); ?></td>
                            <td>$<?php echo number_format($detail['quantity'] * $detail['price'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

    <footer>
        <p>&copy; 2024 Ruby Parfum. All rights reserved.</p>
    </footer>
</body>
</html>
