<?php
include 'config/database.php';
session_start();

// Cek login
if (!isset($_SESSION['user'])) {
    header("Location: auth/login.php");
    exit;
}

// Cek parameter ID produk
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: index.php");
    exit;
}

$product_id = (int)$_GET['id'];

// Ambil data produk
$query = $conn->prepare("SELECT p.*, c.name as category_name FROM products p
          LEFT JOIN categories c ON p.category_id = c.id
          WHERE p.id = ?");
$query->bind_param("i", $product_id);
$query->execute();
$result = $query->get_result();

if (!$result || $result->num_rows == 0) {
    header("Location: index.php");
    exit;
}

$product = $result->fetch_assoc();
$query->close();

// Get cart count
$cart_count = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['name']) ?> - MarketStore</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <style>
    :root {
        --primary-dark: #0a0a0a;
        --secondary-dark: #1a1a1a;
        --accent-gray: #2a2a2a;
        --light-gray: #404040;
        --text-light: #e0e0e0;
        --text-muted: #a0a0a0;
        --border-color: #333333;
        --shadow-color: rgba(0, 0, 0, 0.5);
        --gradient-primary: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        --gradient-secondary: linear-gradient(135deg, #434343 0%, #000000 100%);
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        background: var(--primary-dark);
        color: var(--text-light);
        font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        line-height: 1.6;
        overflow-x: hidden;
    }

    /* Custom Scrollbar */
    ::-webkit-scrollbar {
        width: 8px;
    }

    ::-webkit-scrollbar-track {
        background: var(--primary-dark);
    }

    ::-webkit-scrollbar-thumb {
        background: var(--light-gray);
        border-radius: 4px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: var(--text-muted);
    }

    /* Navbar */
    .navbar {
        background: var(--secondary-dark) !important;
        backdrop-filter: blur(20px);
        border-bottom: 1px solid var(--border-color);
        box-shadow: 0 2px 20px var(--shadow-color);
        padding: 1rem 0;
        transition: all 0.3s ease;
    }

    .navbar-brand {
        font-weight: 700;
        font-size: 1.5rem;
        color: var(--text-light) !important;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .navbar-brand:hover {
        color: #ffffff !important;
        transform: scale(1.02);
    }

    .navbar-nav .nav-link {
        color: var(--text-muted) !important;
        font-weight: 500;
        padding: 0.5rem 1rem;
        margin: 0 0.25rem;
        border-radius: 8px;
        transition: all 0.3s ease;
        position: relative;
    }

    .navbar-nav .nav-link:hover {
        color: var(--text-light) !important;
        background: var(--accent-gray);
        transform: translateY(-1px);
    }

    .navbar-nav .nav-link.active {
        color: #ffffff !important;
        background: var(--gradient-primary);
    }

    /* Cart Badge */
    .cart-badge {
        position: relative;
        display: inline-block;
    }

    .cart-badge .badge {
        position: absolute;
        top: -8px;
        right: -8px;
        background: linear-gradient(45deg, #ff4757, #ff3838);
        color: white;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.7rem;
        font-weight: bold;
        border: 2px solid var(--secondary-dark);
    }

    /* Product Detail Container */
    .product-detail-container {
        padding: 2rem 0;
        min-height: 100vh;
    }

    .product-detail-card {
        background: var(--secondary-dark);
        border: 1px solid var(--border-color);
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 20px 60px var(--shadow-color);
        margin-bottom: 2rem;
    }

    /* Product Images */
    .product-images {
        position: relative;
        background: var(--accent-gray);
        padding: 2rem;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 400px;
    }

    .main-image {
        max-width: 100%;
        max-height: 400px;
        object-fit: contain;
        border-radius: 15px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        transition: all 0.4s ease;
    }

    .main-image:hover {
        transform: scale(1.05);
    }

    /* Product Info */
    .product-info {
        padding: 3rem;
    }

    .product-title {
        font-size: 2.5rem;
        font-weight: 800;
        color: var(--text-light);
        margin-bottom: 1rem;
        background: linear-gradient(135deg, #ffffff 0%, #e0e0e0 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .product-category {
        display: inline-block;
        background: var(--gradient-primary);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 25px;
        font-size: 0.9rem;
        font-weight: 600;
        margin-bottom: 1.5rem;
    }

    .product-price {
        font-size: 3rem;
        font-weight: 900;
        color: #2a5298;
        margin-bottom: 1rem;
        text-shadow: 0 2px 4px rgba(0,0,0,0.2);
    }

    .product-meta {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .meta-item {
        background: var(--accent-gray);
        padding: 1rem;
        border-radius: 12px;
        text-align: center;
        border: 1px solid var(--border-color);
    }

    .meta-label {
        font-size: 0.8rem;
        color: var(--text-muted);
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.5rem;
    }

    .meta-value {
        font-size: 1.2rem;
        font-weight: 700;
        color: var(--text-light);
    }

    .rating-display {
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .rating-display .bi {
        font-size: 1rem;
    }

    .rating-number {
        margin-left: 0.5rem;
        font-size: 0.9rem;
        color: var(--text-muted);
        font-weight: 500;
    }

    .stock-indicator {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.5rem;
        border-radius: 25px;
        font-weight: 600;
        font-size: 0.9rem;
        margin-bottom: 2rem;
    }

    .stock-high {
        background: rgba(40, 167, 69, 0.2);
        color: #28a745;
        border: 1px solid rgba(40, 167, 69, 0.3);
    }

    .stock-medium {
        background: rgba(255, 193, 7, 0.2);
        color: #ffc107;
        border: 1px solid rgba(255, 193, 7, 0.3);
    }

    .stock-low {
        background: rgba(220, 53, 69, 0.2);
        color: #dc3545;
        border: 1px solid rgba(220, 53, 69, 0.3);
    }

    /* Product Description */
    .product-description {
        background: var(--accent-gray);
        border: 1px solid var(--border-color);
        border-radius: 15px;
        padding: 2rem;
        margin-bottom: 2rem;
    }

    .description-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--text-light);
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .description-content {
        color: var(--text-muted);
        line-height: 1.8;
        font-size: 1rem;
    }

    /* Action Buttons */
    .action-buttons {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .btn-add-cart {
        background: var(--gradient-primary);
        border: none;
        border-radius: 15px;
        padding: 1rem 2rem;
        color: white;
        font-weight: 700;
        font-size: 1.1rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        position: relative;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .btn-add-cart::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: left 0.5s;
    }

    .btn-add-cart:hover::before {
        left: 100%;
    }

    .btn-add-cart:hover {
        transform: translateY(-3px) scale(1.02);
        box-shadow: 0 15px 35px rgba(42, 82, 152, 0.4);
    }

    .btn-buy-now {
        background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
        border: none;
        border-radius: 15px;
        padding: 1rem 2rem;
        color: white;
        font-weight: 700;
        font-size: 1.1rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        position: relative;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .btn-buy-now:hover {
        transform: translateY(-3px) scale(1.02);
        box-shadow: 0 15px 35px rgba(255, 107, 107, 0.4);
    }

    .btn-back {
        background: var(--accent-gray);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 0.75rem 1.5rem;
        color: var(--text-light);
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-back:hover {
        background: var(--light-gray);
        transform: translateY(-2px);
        color: var(--text-light);
        text-decoration: none;
    }

    /* Related Products */
    .related-products {
        margin-top: 3rem;
    }

    .related-title {
        font-size: 1.8rem;
        font-weight: 700;
        color: var(--text-light);
        margin-bottom: 2rem;
        text-align: center;
    }

    .related-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 1.5rem;
    }

    .related-card {
        background: var(--accent-gray);
        border: 1px solid var(--border-color);
        border-radius: 15px;
        overflow: hidden;
        transition: all 0.3s ease;
        text-decoration: none;
        color: var(--text-light);
    }

    .related-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px var(--shadow-color);
        text-decoration: none;
        color: var(--text-light);
    }

    .related-image {
        width: 100%;
        height: 150px;
        object-fit: cover;
        transition: all 0.3s ease;
    }

    .related-card:hover .related-image {
        transform: scale(1.1);
    }

    .related-content {
        padding: 1rem;
    }

    .related-title {
        font-size: 0.9rem;
        font-weight: 600;
        margin-bottom: 0.5rem;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .related-price {
        font-size: 1rem;
        font-weight: 700;
        color: #2a5298;
    }

    /* Animations */
    .fade-in {
        animation: fadeIn 0.8s ease-out;
    }

    .slide-up {
        animation: slideUp 0.8s ease-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Loading Animation */
    .loading-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: var(--primary-dark);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        transition: opacity 0.3s ease;
    }

    .loading-overlay.hidden {
        opacity: 0;
        pointer-events: none;
    }

    .spinner {
        width: 50px;
        height: 50px;
        border: 3px solid var(--border-color);
        border-top: 3px solid #2a5298;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Responsive */
    @media (max-width: 768px) {
        .product-info {
            padding: 2rem 1.5rem;
        }

        .product-title {
            font-size: 2rem;
        }

        .product-price {
            font-size: 2.5rem;
        }

        .action-buttons {
            grid-template-columns: 1fr;
        }

        .product-meta {
            grid-template-columns: 1fr;
        }

        .related-grid {
            grid-template-columns: 1fr;
        }
    }

    /* Quantity Selector */
    .quantity-selector {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .quantity-btn {
        width: 40px;
        height: 40px;
        border: 1px solid var(--border-color);
        background: var(--accent-gray);
        color: var(--text-light);
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        font-weight: bold;
    }

    .quantity-btn:hover {
        background: var(--light-gray);
        transform: scale(1.1);
    }

    .quantity-input {
        width: 60px;
        height: 40px;
        text-align: center;
        border: 1px solid var(--border-color);
        background: var(--secondary-dark);
        color: var(--text-light);
        border-radius: 10px;
        font-weight: bold;
    }
    </style>
</head>
<body>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="spinner"></div>
</div>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark fixed-top">
    <div class="container">
        <a class="navbar-brand" href="index.php">
            <i class="bi bi-shop"></i>
            MarketStore
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="index.php">
                        <i class="bi bi-house"></i> Beranda
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="cart.php">
                        <i class="bi bi-cart3"></i> Keranjang
                        <?php if ($cart_count > 0): ?>
                        <span class="badge"><?= $cart_count ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="user/orders.php">
                        <i class="bi bi-receipt"></i> Pesanan Saya
                    </a>
                </li>
                <?php if ($_SESSION['user']['role'] !== 'customer'): ?>
                <li class="nav-item">
                    <a class="nav-link" href="admin/dashboard.php">
                        <i class="bi bi-gear"></i> Admin Panel
                    </a>
                </li>
                <?php endif; ?>
            </ul>

            <div class="d-flex align-items-center gap-2">
                <div class="user-info d-none d-lg-flex align-items-center">
                    <div class="user-avatar">
                        <?= strtoupper(substr($_SESSION['user']['name'], 0, 1)) ?>
                    </div>
                    <div>
                        <div class="fw-bold small"><?= htmlspecialchars($_SESSION['user']['name']) ?></div>
                        <div class="text-muted small"><?= htmlspecialchars($_SESSION['user']['role']) ?></div>
                    </div>
                </div>
                <a href="auth/logout.php" class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-box-arrow-right"></i>
                    <span class="d-none d-lg-inline">Logout</span>
                </a>
            </div>
        </div>
    </div>
</nav>

<!-- Product Detail -->
<div class="product-detail-container">
    <div class="container">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php" class="text-decoration-none">Beranda</a></li>
                <li class="breadcrumb-item"><a href="index.php?cat=<?= $product['category_id'] ?>" class="text-decoration-none"><?= htmlspecialchars($product['category_name']) ?></a></li>
                <li class="breadcrumb-item active text-muted" aria-current="page"><?= htmlspecialchars($product['name']) ?></li>
            </ol>
        </nav>

        <div class="product-detail-card fade-in">
            <div class="row g-0">
                <!-- Product Images -->
                <div class="col-lg-6">
                    <div class="product-images">
                        <img src="uploads/<?= $product['image'] ?>" alt="<?= htmlspecialchars($product['name']) ?>" class="main-image">
                    </div>
                </div>

                <!-- Product Info -->
                <div class="col-lg-6">
                    <div class="product-info">
                        <h1 class="product-title"><?= htmlspecialchars($product['name']) ?></h1>

                        <span class="product-category">
                            <i class="bi bi-tag"></i> <?= htmlspecialchars($product['category_name']) ?>
                        </span>

                        <div class="product-price">
                            Rp<?= number_format($product['price'], 0, ',', '.') ?>
                        </div>

                        <!-- Product Meta -->
                        <div class="product-meta">
                            <div class="meta-item">
                                <div class="meta-label">Stok Tersedia</div>
                                <div class="meta-value"><?= $product['stock'] ?> Unit</div>
                            </div>
                            <div class="meta-item">
                                <div class="meta-label">Rating Produk</div>
                                <div class="meta-value">
                                    <div class="rating-display">
                                        <?php
                                        $rating = $product['rating'] ?? 0;
                                        for ($i = 1; $i <= 5; $i++) {
                                            if ($i <= $rating) {
                                                echo '<i class="bi bi-star-fill text-warning"></i>';
                                            } elseif ($i - 0.5 <= $rating) {
                                                echo '<i class="bi bi-star-half text-warning"></i>';
                                            } else {
                                                echo '<i class="bi bi-star text-muted"></i>';
                                            }
                                        }
                                        ?>
                                        <span class="rating-number">(<?= number_format($rating, 1) ?>)</span>
                                    </div>
                                </div>
                            </div>
                            <div class="meta-item">
                                <div class="meta-label">Kategori</div>
                                <div class="meta-value"><?= htmlspecialchars($product['category_name']) ?></div>
                            </div>
                            <div class="meta-item">
                                <div class="meta-label">ID Produk</div>
                                <div class="meta-value">#<?= $product['id'] ?></div>
                            </div>
                        </div>

                        <!-- Stock Indicator -->
                        <?php
                        $stock_class = 'stock-high';
                        $stock_text = 'Stok Tersedia';
                        if ($product['stock'] <= 5) {
                            $stock_class = 'stock-low';
                            $stock_text = 'Stok Terbatas';
                        } elseif ($product['stock'] <= 15) {
                            $stock_class = 'stock-medium';
                            $stock_text = 'Stok Sedang';
                        }
                        ?>
                        <div class="stock-indicator <?= $stock_class ?>">
                            <i class="bi bi-circle-fill"></i>
                            <?= $stock_text ?> (<?= $product['stock'] ?> unit)
                        </div>

                        <!-- Quantity Selector -->
                        <div class="quantity-selector">
                            <span class="fw-bold">Jumlah:</span>
                            <button class="quantity-btn" onclick="changeQuantity(-1)">-</button>
                            <input type="number" class="quantity-input" id="quantity" value="1" min="1" max="<?= $product['stock'] ?>">
                            <button class="quantity-btn" onclick="changeQuantity(1)">+</button>
                        </div>

                        <!-- Action Buttons -->
                        <div class="action-buttons">
                            <form method="POST" action="cart.add.php" onsubmit="addToCart(this)">
                                <input type="hidden" name="id" value="<?= $product['id'] ?>">
                                <input type="hidden" name="quantity" id="cart_quantity" value="1">
                                <button type="submit" class="btn-add-cart w-100">
                                    <i class="bi bi-cart-plus"></i> Tambah ke Keranjang
                                </button>
                            </form>

                            <button onclick="buyNow()" class="btn-buy-now w-100">
                                <i class="bi bi-lightning"></i> Beli Sekarang
                            </button>
                        </div>

                        <!-- Back Button -->
                        <a href="index.php" class="btn-back">
                            <i class="bi bi-arrow-left"></i> Kembali ke Beranda
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product Description -->
        <div class="product-description slide-up">
            <h3 class="description-title">
                <i class="bi bi-info-circle"></i> Deskripsi Produk
            </h3>
            <div class="description-content">
                <?php if (!empty($product['description'])): ?>
                    <?= nl2br(htmlspecialchars($product['description'])) ?>
                <?php else: ?>
                    <p class="text-muted">Deskripsi produk belum tersedia.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Related Products -->
        <?php
        $related_query = "SELECT * FROM products WHERE category_id = {$product['category_id']} AND id != {$product['id']} LIMIT 4";
        $related_result = mysqli_query($conn, $related_query);
        if (mysqli_num_rows($related_result) > 0):
        ?>
        <div class="related-products slide-up">
            <h3 class="related-title">Produk Terkait</h3>
            <div class="related-grid">
                <?php while($related = mysqli_fetch_assoc($related_result)): ?>
                <a href="product_detail.php?id=<?= $related['id'] ?>" class="related-card">
                    <img src="uploads/<?= $related['image'] ?>" alt="<?= htmlspecialchars($related['name']) ?>" class="related-image">
                    <div class="related-content">
                        <div class="related-title"><?= htmlspecialchars($related['name']) ?></div>
                        <div class="related-price">Rp<?= number_format($related['price'], 0, ',', '.') ?></div>
                    </div>
                </a>
                <?php endwhile; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Loading overlay
window.addEventListener('load', function() {
    setTimeout(() => {
        document.getElementById('loadingOverlay').classList.add('hidden');
    }, 500);
});

// Quantity selector
function changeQuantity(delta) {
    const input = document.getElementById('quantity');
    const cartQuantity = document.getElementById('cart_quantity');
    let value = parseInt(input.value) + delta;

    if (value < 1) value = 1;
    if (value > <?= $product['stock'] ?>) value = <?= $product['stock'] ?>;

    input.value = value;
    cartQuantity.value = value;
}

document.getElementById('quantity').addEventListener('change', function() {
    const cartQuantity = document.getElementById('cart_quantity');
    let value = parseInt(this.value);

    if (value < 1) value = 1;
    if (value > <?= $product['stock'] ?>) value = <?= $product['stock'] ?>;

    this.value = value;
    cartQuantity.value = value;
});

// Add to cart animation
function addToCart(form) {
    const button = form.querySelector('.btn-add-cart');
    const originalText = button.innerHTML;

    button.innerHTML = '<i class="bi bi-check-circle"></i> Ditambahkan!';
    button.style.background = 'linear-gradient(135deg, #28a745 0%, #20c997 100%)';

    setTimeout(() => {
        button.innerHTML = originalText;
        button.style.background = '';
    }, 2000);
}

// Buy now function
function buyNow() {
    const quantity = document.getElementById('quantity').value;
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'cart.add.php';

    const idInput = document.createElement('input');
    idInput.type = 'hidden';
    idInput.name = 'id';
    idInput.value = '<?= $product['id'] ?>';

    const qtyInput = document.createElement('input');
    qtyInput.type = 'hidden';
    qtyInput.name = 'quantity';
    qtyInput.value = quantity;

    const buyNowInput = document.createElement('input');
    buyNowInput.type = 'hidden';
    buyNowInput.name = 'buy_now';
    buyNowInput.value = '1';

    form.appendChild(idInput);
    form.appendChild(qtyInput);
    form.appendChild(buyNowInput);

    document.body.appendChild(form);
    form.submit();
}

// Smooth scrolling for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Navbar scroll effect
window.addEventListener('scroll', function() {
    const navbar = document.querySelector('.navbar');
    if (window.scrollY > 50) {
        navbar.style.background = 'rgba(26, 26, 26, 0.95)';
        navbar.style.backdropFilter = 'blur(20px)';
    } else {
        navbar.style.background = 'var(--secondary-dark)';
    }
});

// Intersection Observer for animations
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
        }
    });
}, observerOptions);

document.querySelectorAll('.slide-up').forEach(el => {
    el.style.opacity = '0';
    el.style.transform = 'translateY(30px)';
    el.style.transition = 'all 0.8s ease-out';
    observer.observe(el);
});
</script>

</body>
</html>