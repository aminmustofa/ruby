<?php
session_start();
require_once 'config/database.php';
$conn = connectDB();

// Pastikan pengguna sudah login
if (!isset($_SESSION['user_id'])) {
    die("User not logged in.");
}

$userId = $_SESSION['user_id'];
$orderId = $_GET['order_id'] ?? null;

// Validasi `order_id`
if (!$orderId || !is_numeric($orderId)) {
    die("Invalid Order ID.");
}

// Ambil data pesanan
$query = "SELECT * FROM orders WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $orderId, $userId);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    die("Order not found or you do not have permission to confirm this payment.");
}

// Proses pengunggahan bukti pembayaran
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['payment_proof'])) {
    $fileTmpPath = $_FILES['payment_proof']['tmp_name'];
    $fileName = $_FILES['payment_proof']['name'];
    $fileSize = $_FILES['payment_proof']['size'];
    $fileType = pathinfo($fileName, PATHINFO_EXTENSION);

    // Validasi file
    $allowedExtensions = ['jpg', 'jpeg', 'png', 'pdf'];
    if (!in_array(strtolower($fileType), $allowedExtensions)) {
        die("<p class='text-danger'>Invalid file type. Only JPG, PNG, and PDF files are allowed.</p>");
    }

    if ($fileSize > 5 * 1024 * 1024) {
        die("<p class='text-danger'>File size exceeds the maximum limit of 5MB.</p>");
    }

    // Tentukan folder penyimpanan file
    $uploadDir = 'uploads/payment_proofs/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    $filePath = $uploadDir . uniqid('proof_', true) . '.' . $fileType;

    // Pindahkan file ke folder upload
    if (move_uploaded_file($fileTmpPath, $filePath)) {
        // Perbarui status pesanan di database
        $updateQuery = "UPDATE orders SET payment_proof = ?, status = 'pending' WHERE id = ? AND user_id = ?";
        $updateStmt = $conn->prepare($updateQuery);

        if ($updateStmt) {
            $updateStmt->bind_param("sii", $filePath, $orderId, $userId);

            if ($updateStmt->execute()) {
                echo "<p class='text-success'>Payment proof uploaded successfully. Your payment is under verification.</p>";
            } else {
                die("Failed to update status: " . $updateStmt->error);
            }

            $updateStmt->close();
        } else {
            die("Failed to prepare update query: " . $conn->error);
        }
    } else {
        die("Failed to upload file.");
    }
}

$stmt->close();
$conn->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Confirmation</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="#">Payment Confirmation</a>
        <div class="navbar-nav ml-auto">
            <a class="nav-item nav-link" href="index.php">Home</a>
            <a class="nav-item nav-link" href="order_history.php">Order History</a>
            <a class="nav-item nav-link" href="logout.php">Logout</a>
        </div>
    </nav>

    <!-- Konten -->
    <div class="container mt-5">
        <h2>Confirm Payment for Order #<?php echo htmlspecialchars($order['id']); ?></h2>
        <p>Status: <strong><?php echo htmlspecialchars($order['status']); ?></strong></p>

        <?php if (isset($successMessage)): ?>
            <div class="alert alert-success"><?php echo $successMessage; ?></div>
        <?php elseif (isset($errorMessage)): ?>
            <div class="alert alert-danger"><?php echo $errorMessage; ?></div>
        <?php endif; ?>

        <?php if (empty($order['payment_proof'])): ?>
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="payment_proof" class="form-label">Upload Payment Proof</label>
                    <input type="file" class="form-control" name="payment_proof" id="payment_proof" required>
                </div>
                <button type="submit" class="btn btn-primary">Submit Payment</button>
            </form>
        <?php else: ?>
            <p class="text-success">You have already uploaded payment proof. Status is: <strong>Waiting for Verification</strong>.</p>
            <a href="<?php echo htmlspecialchars($order['payment_proof']); ?>" target="_blank" class="btn btn-secondary">View Payment Proof</a>
        <?php endif; ?>
    </div>

    <footer class="bg-light py-4 mt-5">
        <p class="text-center">&copy; 2024 Payment System. All rights reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
