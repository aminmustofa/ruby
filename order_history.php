<?php
session_start();
require_once 'config/database.php';
$conn = connectDB();

// Jika pengguna tidak login, redirect ke login page
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$userId = $_SESSION['user_id'];

// Ambil data pesanan dari database
$query = "SELECT id, status, total_price, payment_method, payment_proof FROM orders WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

    <!-- Navbar -->
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

    <div class="container mt-5">
        <h2>Order History</h2>
    <!-- Tambahkan tombol setelah informasi pesanan -->
    <div class="mt-4">
        <a href="payment_method.php" class="btn btn-success">Cara Pembayaran</a>
    </div>

        <?php if ($result->num_rows > 0): ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Status</th>
                    <th>Total Price</th>
                    <th>Payment Method</th>
                    <th>Payment Proof</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($order = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($order['id']); ?></td>
                    <td>
                        <?php
                        switch ($order['status']) {
                            case 'pending':
                                echo '<span class="badge bg-secondary">Pending</span>';
                                break;
                            case 'waiting_for_approval':
                                echo '<span class="badge bg-warning">Waiting for Approval</span>';
                                break;
                            case 'confirmed':
                            case 'terkonfirmasi':
                                echo '<span class="badge bg-success">Confirmed</span>';
                                break;
                            default:
                                echo '<span class="badge bg-danger">Unknown Status</span>';
                        }
                        ?>
                    </td>
                    <td>IDR <?php echo number_format($order['total_price'], 0, ',', '.'); ?></td>
                    <td><?php echo htmlspecialchars($order['payment_method']); ?></td>
                    <td>
                        <?php 
                        if (!empty($order['payment_proof'])) {
                            echo '<a href="' . htmlspecialchars($order['payment_proof']) . '" target="_blank">Lihat Bukti Pembayaran</a>';
                        } else {
                            echo 'Belum ada bukti pembayaran';
                        }
                        ?>
                    </td>
                    <td>
                        <?php if ($order['status'] == 'pending'): ?>
                            <a href="confirm_payment.php?order_id=<?php echo $order['id']; ?>" class="btn btn-primary">Konfirmasi Pembayaran</a>
                        <?php elseif ($order['status'] == 'pending'): ?>
                            <span class="badge bg-warning">Menunggu Verifikasi</span>
                        <?php elseif ($order['status'] == 'confirmed' || $order['status'] == 'terkonfirmasi'): ?>
                            <span class="badge bg-success">Pembayaran Dikonfirmasi</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
            <p>No orders found.</p>
        <?php endif; ?>
    </div>

    <footer class="bg-light py-4 mt-5">
        <p class="text-center">&copy; 2024 Ruby Parfum. All rights reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
