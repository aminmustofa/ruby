<?php
session_start();

// Pastikan ada session cart
if (isset($_SESSION['cart'])) {
    // Periksa apakah product_id dan action dikirimkan
    if (isset($_POST['product_id']) && isset($_POST['action'])) {
        $productId = (int) $_POST['product_id'];
        $action = $_POST['action'];
        $quantity = isset($_POST['quantity']) ? (int) $_POST['quantity'] : 1;

        // Cek apakah produk ada di dalam keranjang
        if (isset($_SESSION['cart'][$productId])) {
            // Update kuantitas berdasarkan aksi
            if ($action === 'increase') {
                $_SESSION['cart'][$productId]++;
            } elseif ($action === 'decrease' && $_SESSION['cart'][$productId] > 1) {
                $_SESSION['cart'][$productId]--;
            }
        }

        // Arahkan kembali ke halaman keranjang
        header('Location: cart.php');
        exit;
    }
}
?>
