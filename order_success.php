<?php
session_start();
if (!isset($_SESSION['order_id'])) {
    // Jika session order_id tidak ada, arahkan pengguna kembali ke halaman utama atau error page
    header('Location: index.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Success | Ruby Parfum</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
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
        <div class="container text-center">
            <div class="card shadow-sm p-4">
                <h2 class="mb-4">Thank You for Your Order!</h2>
                <p>Your order has been successfully placed. We are processing it and will notify you once it is shipped.</p>

                <div class="order-summary mt-4">
                    <h3>Order Summary</h3>
                    <p><strong>Order ID:</strong> #<?php echo isset($_SESSION['order_id']) ? $_SESSION['order_id'] : 'N/A'; ?></p>
                    <p><strong>Total Price:</strong> Rp.<?php echo isset($_SESSION['total_price']) ? number_format($_SESSION['total_price'], 2) : '0.00'; ?></p>
                    <p><strong>Payment Method:</strong> <?php echo isset($_SESSION['payment_method']) ? $_SESSION['payment_method'] : 'N/A'; ?></p>
                </div>
                <div class="continue-shopping mt-4">
                    <a href="payment_method.php" class="btn btn-primary">Cara Pembayaran</a>
                </div>

                <div class="thank-you-message mt-4">
                    <p>We appreciate your business and hope to serve you again soon.</p>
                    <p>If you have any questions, feel free to <a href="contact.php">contact us</a>.</p>
                </div>

                <div class="continue-shopping mt-4">
                    <a href="index.php" class="btn btn-primary">Continue Shopping</a>
                </div>
            </div>
        </div>
    </main>

    <footer class="bg-dark text-white text-center py-3">
        <p>&copy; 2024 Ruby Parfum. All rights reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
