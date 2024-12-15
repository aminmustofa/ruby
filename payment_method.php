<?php
session_start();
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

    <main class="container py-5">
        <div class="payment-method-container">
            <h2>Payment Method: Bank Transfer</h2>
            <p>Thank you for choosing bank transfer as your payment method. Please follow the steps below to complete your payment:</p>

            <div class="payment-instructions">
                <h4>Langkah-langkah Pembayaran dengan Transfer Bank:</h4>
                <ul>
                    <li><strong>Langkah 1:</strong> Transfer jumlah total ke salah satu rekening bank berikut:</li>
                    <ul>
                        <li><strong>Nama Bank:</strong> Bank XYZ</li>
                        <li><strong>Nomor Rekening:</strong> 123-456-7890</li>
                        <li><strong>Nama Pemilik Rekening:</strong> Ruby Parfum Store</li>
                    </ul>
                    <li><strong>Langkah 2:</strong> Setelah melakukan transfer, ambil tangkapan layar atau screenshoot atau foto pembayaran anda.</li>
                    <li><strong>Langkah 3:</strong> Kirimkan konfirmasi pembayaran pada <strong>halaman order history.</li>
                    <li><strong>Langkah 4:</strong> Kami akan memverifikasi pembayaran Anda dan mengonfirmasi pesanan Anda. Anda akan menerima pemberitahuan pengiriman setelah pesanan Anda dikirim.</li>
                </ul>
            </div>


            <div class="btn-back">
                <a href="order_history.php" class="btn btn-primary">Back</a>
            </div>
        </div>
    </main>

    <footer>
        <p>&copy; 2024 Ruby Parfum. All rights reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
