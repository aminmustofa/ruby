<?php
session_start();
require_once 'config/database.php';
$conn = connectDB();

// Cek apakah ada parameter order_id
if (!isset($_GET['order_id'])) {
    header('Location: index.php');
    exit;
}

$orderId = $_GET['order_id'];

// Ambil data pesanan
$query = "SELECT * FROM orders WHERE id = $orderId";
$orderResult = $conn->query($query);
$order = $orderResult->fetch_assoc();

// Ambil detail pesanan
$orderDetailsQuery = "SELECT od.*, p.name, p.price FROM order_details od
                      JOIN products p ON od.product_id = p.id WHERE od.order_id = $orderId";
$orderDetailsResult = $conn->query($orderDetailsQuery);

// Proses pengunggahan bukti pembayaran
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['payment_proof']['tmp_name'];
        $fileName = $_FILES['payment_proof']['name'];
        $fileSize = $_FILES['payment_proof']['size'];
        $fileType = $_FILES['payment_proof']['type'];

        // Tentukan folder untuk menyimpan file
        $uploadDir = 'uploads/payment_proofs/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // Tentukan nama file yang akan disimpan
        $filePath = $uploadDir . basename($fileName);

        // Pindahkan file ke folder upload
        if (move_uploaded_file($fileTmpPath, $filePath)) {
            // Update status pesanan dan tambahkan bukti pembayaran
            $updateQuery = "UPDATE orders SET payment_proof = ?, status = 'waiting_for_approval' WHERE id = ?";
            $updateStmt = $conn->prepare($updateQuery);
            $updateStmt->bind_param("si", $filePath, $orderId);

            if ($updateStmt->execute()) {
                echo "<p>Your payment proof has been uploaded successfully. We will verify it shortly.</p>";
            } else {
                echo "<p>Error updating payment proof. Please try again later.</p>";
            }

            $updateStmt->close();
        } else {
            echo "<p>Error uploading the payment proof file.</p>";
        }
    } else {
        echo "<p>No file uploaded or there was an error uploading the file.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation | Ruby Parfum</title>
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
        </nav>
    </header>

    <main>
        <div class="order-confirmation">
            <h2>Order Confirmation</h2>
            <p>Thank you for your order, <?php echo htmlspecialchars($order['name']); ?>!</p>
            <p>Your order has been successfully placed. We will process it shortly.</p>
            <h3>Order Details</h3>
            <table>
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($detail = $orderDetailsResult->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($detail['name']); ?></td>
                        <td>$<?php echo number_format($detail['price'], 2); ?></td>
                        <td><?php echo $detail['quantity']; ?></td>
                        <td>$<?php echo number_format($detail['subtotal'], 2); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            <p><strong>Total Price:</strong> $<?php echo number_format($order['total_price'], 2); ?></p>

            <!-- Form Upload Bukti Pembayaran -->
            <h3>Upload Payment Proof</h3>
            <?php if (empty($order['payment_proof'])): ?>
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="payment_proof" class="form-label">Upload Bukti Pembayaran</label>
                    <input type="file" class="form-control" name="payment_proof" id="payment_proof" required>
                </div>
                <button type="submit" class="btn btn-primary">Konfirmasi Pembayaran</button>
            </form>
            <?php else: ?>
            <p>Anda sudah mengunggah bukti pembayaran. Status saat ini: <strong>Menunggu Verifikasi</strong>.</p>
            <a href="<?php echo htmlspecialchars($order['payment_proof']); ?>" target="_blank" class="btn btn-secondary">Lihat Bukti Pembayaran</a>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <p>&copy; 2024 Ruby Parfum. All rights reserved.</p>
    </footer>
</body>
</html>
