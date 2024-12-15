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

// Ambil data keranjang
$cartItems = $_SESSION['cart'];

// Ambil ID produk dari keranjang
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

// Ambil data pembayaran dari form
$paymentMethod = $_POST['payment_method'] ?? '';

// Query untuk insert transaksi
$insertQuery = "INSERT INTO orders (user_id, total_price, payment_method, status) VALUES (?, ?, ?, 'pending')";
$insertStmt = $conn->prepare($insertQuery);
if ($insertStmt === false) {
    die('Error preparing insert statement: ' . $conn->error);
}

$insertStmt->bind_param("ids", $userId, $totalPrice, $paymentMethod); // Perhatikan tipe parameter

// Eksekusi query untuk menyimpan order
if ($insertStmt->execute()) {
    $orderId = $insertStmt->insert_id;

    // Insert order details
    foreach ($products as $product) {
        $quantity = $cartItems[$product['id']];
        $subtotal = $product['price'] * $quantity;

        // Insert each product in order details
        $insertDetailQuery = "INSERT INTO order_details (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
        $insertDetailStmt = $conn->prepare($insertDetailQuery);
        $insertDetailStmt->bind_param("iiid", $orderId, $product['id'], $quantity, $subtotal);
        $insertDetailStmt->execute();
        $insertDetailStmt->close();
    }

    // Simpan informasi pesanan ke dalam session
    $_SESSION['order_id'] = $orderId;  // ID pesanan dari insert query
    $_SESSION['total_price'] = $totalPrice;  // Total harga pesanan
    $_SESSION['payment_method'] = $paymentMethod;  // Metode pembayaran

    // Hapus keranjang setelah checkout
    unset($_SESSION['cart']);

    header('Location: order_success.php');
    exit;

} else {
    echo 'Error while processing your order: ' . $conn->error;
}

$insertStmt->close();
$conn->close();
?>
