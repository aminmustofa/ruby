<?php
session_start();
require_once 'config/database.php';
$conn = connectDB();

// Check if search query exists
$search_query = isset($_GET['search_query']) ? $_GET['search_query'] : '';

// Prepare query based on search query
$query = "SELECT * FROM products";
if (!empty($search_query)) {
    $query .= " WHERE name LIKE ? OR description LIKE ?";
}

// Prepare and execute query
$stmt = $conn->prepare($query);
if (!empty($search_query)) {
    $search_param = "%" . $search_query . "%";
    $stmt->bind_param("ss", $search_param, $search_param);
}
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ruby Parfum</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="logo">
                <a href="index.php" class="text-decoration-none text-dark">
                    <h1>Ruby Parfum</h1>
                </a>
            </div>
            <div class="search-bar">
                <!-- Search Form -->
                <form method="GET" action="index.php">
                    <input type="text" name="search_query" placeholder="Search products..." value="<?php echo htmlspecialchars($search_query); ?>">
                    <button type="submit">Search</button>
                </form>
            </div>
            <div class="nav-links">
                <a href="cart.php">Cart</a>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <!-- Jika pengguna sudah login, tampilkan link ke Akun dan Order History -->
                    <a href="account.php">Akun</a>  <!-- Link ke halaman Akun -->
                    <a href="order_history.php">Order History</a>  <!-- Link ke Order History -->
                    <a href="logout.php">Logout</a>  <!-- Link untuk logout -->
                <?php else: ?>
                    <!-- Jika pengguna belum login, tampilkan link ke Login -->
                    <a href="login.php">Login</a>  <!-- Link ke Login -->
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <main>
        <div class="products-grid">
            <?php if ($result->num_rows > 0): ?>
                <?php while($product = $result->fetch_assoc()): ?>
                <div class="product-card">
                    <!-- Link gambar produk dan nama produk mengarah ke halaman detail produk -->
                    <a href="product_details.php?id=<?php echo $product['id']; ?>">
                        <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                        class="card-img-top" 
                             alt="<?php echo htmlspecialchars($product['name']); ?>">
                    </a>
                        <!-- Nama produk -->
                        <a href="product_details.php?id=<?php echo $product['id']; ?>" class="text-decoration-none">
                            <h5 class="card-title text-dark"><?php echo htmlspecialchars($product['name']); ?></h5>
                        </a>
                    <p class="price">Rp.<?php echo number_format($product['price'], 2); ?></p>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No products found matching your search.</p>
            <?php endif; ?>
        </div>
    </main>

    <footer>
        <p>&copy; 2024 Ruby Parfum. All rights reserved.</p>
    </footer>
    
    <script src="assets/js/main.js"></script>
    <script src="assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
