<?php
session_start();
require_once 'config/database.php';
$conn = connectDB();

// Mendapatkan ID produk dari parameter URL
$product_id = isset($_GET['id']) ? $_GET['id'] : null;

if ($product_id) {
    // Fetch product details from database
    $query = "SELECT * FROM products WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
}
// Fetch reviews for the product
if ($product_id) { 
    // Mengambil 5 ulasan terbaru untuk produk tertentu
    $reviewQuery = "SELECT r.rating, r.comment, u.username 
                    FROM reviews r 
                    JOIN users u ON r.user_id = u.id 
                    WHERE r.product_id = ? 
                    ORDER BY r.created_at DESC 
                    LIMIT 5"; // Batasi hanya 5 ulasan yang ditampilkan
    $reviewStmt = $conn->prepare($reviewQuery);
    $reviewStmt->bind_param('i', $product_id);
    $reviewStmt->execute();
    $reviewsResult = $reviewStmt->get_result();
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
            <div class="nav-links">
            <a href="cart.php">Cart</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <!-- Jika user sudah login, tampilkan link 'Akun' -->
                <a href="account.php">Akun</a>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <!-- Jika user belum login, tampilkan link 'Login' -->
                <a href="login.php">Login</a>
            <?php endif; ?>
        </div>

        </nav>
    </header>

    <main class="container">
    <?php 
    // Tampilkan notifikasi jika ada
    if (isset($_SESSION['success_message'])): 
    ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo $_SESSION['success_message']; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php unset($_SESSION['success_message']); ?>
    <?php endif; ?>

    <?php if ($product): ?>
    <div class="row">
        <!-- Gambar Produk -->
        <div class="col-md-4">
            <div class="border rounded p-3">
                <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                     class="img-fluid rounded shadow-sm" 
                     alt="<?php echo htmlspecialchars($product['name']); ?>">
            </div>
        </div>

        <!-- Detail Produk -->
        <div class="col-md-8">
            <h1 class="fw-bold mb-3"><?php echo htmlspecialchars($product['name']); ?></h1>
            <p class="text-success fs-3 fw-bold">Rp.<?php echo number_format($product['price'], 2); ?></p>
            <p class="text-muted">Stok tersedia: <span class="fw-bold"><?php echo $product['stock']; ?></span></p>

            <hr>

            <!-- Jumlah Pembelian -->
            <form method="post" action="add-to-cart.php" class="d-flex align-items-center">
                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                <label for="quantity" class="me-2 fw-bold">Jumlah:</label>
                <div class="input-group mb-3" style="width: 120px;">
                    <button class="btn btn-outline-secondary" type="button" id="decrease">-</button>
                    <input type="number" id="quantity" name="quantity" class="form-control text-center" value="1" min="1" max="<?php echo $product['stock']; ?>">
                    <button class="btn btn-outline-secondary" type="button" id="increase">+</button>
                </div>
                <button type="submit" class="btn btn-primary btn-lg ms-3">
                    <i class="bi bi-cart-plus"></i> Tambahkan ke Keranjang
                </button>
            </form>
        </div>
    </div>

    <hr class="my-5">

    <!-- Tabs untuk Deskripsi dan Informasi Tambahan -->
    <ul class="nav nav-tabs" id="productTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="description-tab" data-bs-toggle="tab" data-bs-target="#description" type="button" role="tab" aria-controls="description" aria-selected="true">
                Deskripsi
            </button>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="reviews-tab" data-bs-toggle="tab" data-bs-target="#reviews" type="button" role="tab" aria-controls="reviews" aria-selected="false">
                Ulasan
            </button>
        </li>
    </ul>
    <div class="tab-content border rounded-bottom p-4" id="productTabsContent">
        <!-- Deskripsi Produk -->
        <div class="tab-pane fade show active" id="description" role="tabpanel" aria-labelledby="description-tab">
            <h5 class="fw-bold">Detail Produk</h5>
            <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
        </div>
        <!-- Ulasan Produk -->
    <div class="tab-pane fade" id="reviews" role="tabpanel" aria-labelledby="reviews-tab">
        <h5 class="fw-bold mb-4">Ulasan Pelanggan</h5>

        <div class="reviews-container" style="max-height: 300px; overflow-y: auto;">
            <?php if ($reviewsResult->num_rows > 0): ?>
                <?php while ($review = $reviewsResult->fetch_assoc()): ?>
                    <div class="review mb-4 p-3 border rounded shadow-sm">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-bold"><?php echo htmlspecialchars($review['username']); ?></span>
                            <span class="text-warning">
                                <?php for ($i = 0; $i < $review['rating']; $i++): ?>
                                    <i class="bi bi-star-fill"></i>
                                <?php endfor; ?>
                                <?php for ($i = $review['rating']; $i < 5; $i++): ?>
                                    <i class="bi bi-star"></i>
                                <?php endfor; ?>
                            </span>
                        </div>
                        <p class="text-muted"><?php echo nl2br(htmlspecialchars($review['comment'])); ?></p>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="alert alert-info">
                    <p>Belum ada ulasan untuk produk ini.</p>
                </div>
            <?php endif; ?>
        </div>

    <!-- Jika lebih dari 5 ulasan, tambahkan tombol untuk melihat lebih banyak -->
    <?php if ($reviewsResult->num_rows > 5): ?>
        <button class="btn btn-outline-primary mt-3">Lihat Ulasan Lainnya</button>
    <?php endif; ?>


    <?php if (isset($_SESSION['user_id'])): ?>
        <h5 class="fw-bold mt-4">Tambahkan Ulasan</h5>
        <form method="post" action="submit_review.php">
            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">

            <!-- Rating Section -->
            <div class="mb-3">
                <label for="rating" class="form-label">Rating</label>
                <div id="rating" class="d-flex">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <label class="star-label">
                            <input type="radio" name="rating" value="<?php echo $i; ?>" class="btn-check" id="rating-<?php echo $i; ?>" autocomplete="off">
                            <i class="bi bi-star" data-index="<?php echo $i; ?>" style="font-size: 1.5rem;"></i>
                        </label>
                    <?php endfor; ?>
                </div>
            </div>


            <!-- Comment Section -->
            <div class="mb-3">
                <label for="comment" class="form-label">Komentar</label>
                <textarea name="comment" id="comment" class="form-control" rows="3" required></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Kirim Ulasan</button>
        </form>
    <?php else: ?>
        <div class="alert alert-warning mt-3">
            Anda harus <a href="login.php">login</a> untuk menulis ulasan.
        </div>
    <?php endif; ?>
    </div>
        </div>


    </div>
    <?php else: ?>
    <div class="text-center my-5">
        <h2 class="text-danger">Produk tidak ditemukan</h2>
        <a href="index.php" class="btn btn-outline-primary">Kembali ke Beranda</a>
    </div>
    <?php endif; ?>
</main>



    <footer class="bg-light text-center py-4 mt-4 border-top">
        <p>&copy; 2024 Ruby Parfum. All rights reserved.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.getElementById('increase').addEventListener('click', function () {
        const qtyInput = document.getElementById('quantity');
        const maxQty = parseInt(qtyInput.getAttribute('max'));
        if (parseInt(qtyInput.value) < maxQty) {
            qtyInput.value = parseInt(qtyInput.value) + 1;
        }
    });

    document.getElementById('decrease').addEventListener('click', function () {
        const qtyInput = document.getElementById('quantity');
        if (parseInt(qtyInput.value) > 1) {
            qtyInput.value = parseInt(qtyInput.value) - 1;
        }
    });
</script>
<script>
    // Ambil semua elemen bintang
    const stars = document.querySelectorAll('#rating i');

    // Saat pengguna menghover bintang, kita ubah warnanya
    stars.forEach(star => {
        star.addEventListener('mouseover', function () {
            const index = parseInt(star.getAttribute('data-index'));
            updateStarColors(index);
        });

        star.addEventListener('mouseout', function () {
            const selectedRating = document.querySelector('input[name="rating"]:checked');
            const selectedIndex = selectedRating ? selectedRating.value : 0;
            updateStarColors(selectedIndex);
        });
    });

    function updateStarColors(rating) {
        stars.forEach(star => {
            const index = parseInt(star.getAttribute('data-index'));
            if (index <= rating) {
                star.style.color = "#ffcc00"; // Bintang terisi
            } else {
                star.style.color = "#ddd"; // Bintang kosong
            }
        });
    }
</script>

</body>
</html>
