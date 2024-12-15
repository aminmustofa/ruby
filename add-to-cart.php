<?php
session_start();
require_once 'config/database.php';
$conn = connectDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'], $_POST['quantity'])) {
    $product_id = (int) $_POST['product_id'];
    $quantity = (int) $_POST['quantity'];

    // Pastikan jumlah minimum adalah 1
    if ($quantity < 1) {
        $quantity = 1;
    }

    // Simpan produk ke dalam session cart
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    if (isset($_SESSION['cart'][$product_id])) {
        $_SESSION['cart'][$product_id] += $quantity; // Tambahkan jumlah item
    } else {
        $_SESSION['cart'][$product_id] = $quantity; // Tambahkan produk baru
    }

    // Redirect kembali ke halaman detail produk dengan notifikasi
    $_SESSION['success_message'] = 'Produk berhasil ditambahkan ke keranjang!';
    header('Location: product_details.php?id=' . $product_id);
    exit;
}
?>
