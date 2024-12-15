<?php
// Mulai sesi
session_start();

// Hapus semua data sesi
session_unset();

// Hancurkan sesi
session_destroy();

// Redirect ke halaman utama atau login
header("Location: index.php"); // Bisa diganti dengan "login.php" jika lebih baik mengarah ke halaman login
exit;
?>
