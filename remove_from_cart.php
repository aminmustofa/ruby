<?php
session_start();

// Pastikan produk yang dihapus ada dalam keranjang
if (isset($_POST['product_id']) && isset($_SESSION['cart'])) {
    $productId = (int) $_POST['product_id'];

    // Periksa jika produk ada di keranjang
    if (isset($_SESSION['cart'][$productId])) {
        // Hapus produk dari keranjang
        unset($_SESSION['cart'][$productId]);
    }
}

// Arahkan kembali ke halaman keranjang
header('Location: cart.php');
exit;
?>
