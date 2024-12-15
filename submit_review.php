<?php
// submit-review.php
session_start();
require_once 'config/database.php';
$conn = connectDB();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $product_id = $_POST['product_id'];
    $rating = $_POST['rating'];
    $comment = $_POST['comment'];

    $query = "INSERT INTO reviews (product_id, user_id, rating, comment) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('iiis', $product_id, $user_id, $rating, $comment);

    if ($stmt->execute()) {
        $_SESSION['success_message'] = 'Ulasan berhasil dikirim!';
    } else {
        $_SESSION['error_message'] = 'Gagal mengirim ulasan. Coba lagi.';
    }
    header('Location: product_details.php?id=' . $product_id); // Redirect back to product page
    exit();
}
?>