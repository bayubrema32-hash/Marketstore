<?php
session_start();
include 'config/database.php';

if (!isset($_SESSION['user'])) {
    header("Location: auth/login.php");
    exit;
}

$cart = $_SESSION['cart'] ?? [];

if (empty($cart)) {
    header("Location: cart.php");
    exit;
}

// Ambil error message jika ada
$error_message = $_SESSION['checkout_error'] ?? null;
unset($_SESSION['checkout_error']);

$subtotal = 0;
$cart_items = [];
foreach ($cart as $id => $qty) {
    $id = (int)$id;
    $qty = (int)$qty;
    $p = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $p->bind_param("i", $id);
    $p->execute();
    $result = $p->get_result();
    $product = $result->fetch_assoc();
    if ($product) {
        $item_subtotal = $product['price'] * $qty;
        $subtotal += $item_subtotal;
        $cart_items[] = [
            'product' => $product,
            'qty' => $qty,
            'subtotal' => $item_subtotal
        ];
    }
    $p->close();
}

// Default shipping cost (will be updated by JavaScript)
$default_shipping = 15000;
$total = $subtotal + $default_shipping;

// Shipping costs
$shipping_options = [
    'jne' => ['name' => 'JNE', 'cost' => 15000, 'days' => '2-3 hari'],
    'jnt' => ['name' => 'J&T', 'cost' => 12000, 'days' => '1-2 hari'],
    'sicepat' => ['name' => 'SiCepat', 'cost' => 10000, 'days' => '2-4 hari'],
    'anteraja' => ['name' => 'AnterAja', 'cost' => 8000, 'days' => '1-3 hari']
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - MarketStore</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <style>
    :root {
        --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --secondary-gradient: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        --accent-gradient: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        --success-gradient: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        --warning-gradient: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        --dark-gradient: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    }

    body {
        background:
            radial-gradient(circle at 20% 50%, rgba(120, 119, 198, 0.3) 0%, transparent 50%),
            radial-gradient(circle at 80% 20%, rgba(255, 119, 198, 0.3) 0%, transparent 50%),
            radial-gradient(circle at 40% 80%, rgba(120, 219, 255, 0.3) 0%, transparent 50%),
            linear-gradient(135deg, #0f0f23 0%, #1a1a2e 50%, #16213e 100%);
        color: #ffffff;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        min-height: 100vh;
        overflow-x: hidden;
    }

    body::before {
        content: '';
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background:
            url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(255,255,255,0.03)"/><circle cx="75" cy="75" r="1" fill="rgba(255,255,255,0.03)"/><circle cx="50" cy="10" r="0.5" fill="rgba(255,255,255,0.02)"/><circle cx="90" cy="40" r="0.5" fill="rgba(255,255,255,0.02)"/></pattern></defs><rect width="100" height="100" fill="url(%23grain)"/></svg>');
        pointer-events: none;
        z-index: -1;
    }

    .navbar {
        background: rgba(255, 255, 255, 0.1) !important;
        backdrop-filter: blur(20px);
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        transform: translateZ(0);
    }

    .card {
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 20px;
        backdrop-filter: blur(20px);
        box-shadow:
            0 20px 40px rgba(0, 0, 0, 0.3),
            0 0 0 1px rgba(255, 255, 255, 0.1) inset;
        transform: translateZ(0);
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    .card:hover {
        transform: translateY(-10px) scale(1.02);
        box-shadow:
            0 30px 60px rgba(0, 0, 0, 0.4),
            0 0 0 1px rgba(255, 255, 255, 0.2) inset;
    }

    .checkout-header {
        background: var(--primary-gradient);
        border-radius: 20px 20px 0 0;
        padding: 30px;
        text-align: center;
        position: relative;
        overflow: hidden;
    }

    .checkout-header::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: conic-gradient(from 0deg, transparent, rgba(255,255,255,0.1), transparent);
        animation: rotate 20s linear infinite;
    }

    @keyframes rotate {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    .checkout-header h2 {
        position: relative;
        z-index: 1;
        font-weight: 700;
        text-shadow: 0 2px 4px rgba(0,0,0,0.3);
    }

    .form-control, .form-select {
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 15px;
        color: #ffffff;
        backdrop-filter: blur(10px);
        transition: all 0.3s ease;
        transform: translateZ(0);
    }

    .form-control:focus, .form-select:focus {
        background: rgba(255, 255, 255, 0.15);
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        color: #ffffff;
        transform: translateY(-2px);
    }

    .form-control::placeholder {
        color: rgba(255, 255, 255, 0.6);
    }

    .btn-3d {
        background: var(--primary-gradient);
        border: none;
        border-radius: 15px;
        padding: 15px 30px;
        color: white;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
        position: relative;
        overflow: hidden;
        transform: translateZ(0);
        transition: all 0.3s ease;
        box-shadow:
            0 10px 20px rgba(102, 126, 234, 0.3),
            0 0 0 1px rgba(255, 255, 255, 0.1) inset;
    }

    .btn-3d::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
        transition: left 0.5s;
    }

    .btn-3d:hover::before {
        left: 100%;
    }

    .btn-3d:hover {
        transform: translateY(-3px) scale(1.05);
        box-shadow:
            0 15px 30px rgba(102, 126, 234, 0.4),
            0 0 0 1px rgba(255, 255, 255, 0.2) inset;
    }

    .product-card {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 15px;
        padding: 15px;
        margin-bottom: 15px;
        backdrop-filter: blur(10px);
        transform: translateZ(0);
        transition: all 0.3s ease;
    }

    .product-card:hover {
        transform: translateX(5px);
        background: rgba(255, 255, 255, 0.08);
    }

    .product-image {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }

    .shipping-option {
        background: rgba(255, 255, 255, 0.05);
        border: 2px solid rgba(255, 255, 255, 0.1);
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 15px;
        cursor: pointer;
        transition: all 0.3s ease;
        transform: translateZ(0);
    }

    .shipping-option:hover {
        border-color: #667eea;
        background: rgba(102, 126, 234, 0.1);
        transform: translateY(-2px);
    }

    .shipping-option.selected {
        border-color: #667eea;
        background: rgba(102, 126, 234, 0.2);
        box-shadow: 0 0 20px rgba(102, 126, 234, 0.3);
    }

    .total-summary {
        background: var(--success-gradient);
        border-radius: 15px;
        padding: 25px;
        color: white;
        text-align: center;
        box-shadow: 0 10px 30px rgba(67, 233, 123, 0.3);
        position: relative;
        overflow: hidden;
    }

    .total-summary::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: conic-gradient(from 0deg, transparent, rgba(255,255,255,0.1), transparent);
        animation: rotate 15s linear infinite;
    }

    .total-summary h3 {
        position: relative;
        z-index: 1;
        font-size: 2.5rem;
        font-weight: 700;
        text-shadow: 0 2px 4px rgba(0,0,0,0.3);
    }

    .floating-shapes {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: -1;
    }

    .shape {
        position: absolute;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.05);
        animation: float 20s infinite linear;
    }

    .shape:nth-child(1) {
        width: 80px;
        height: 80px;
        top: 10%;
        left: 10%;
        animation-delay: 0s;
    }

    .shape:nth-child(2) {
        width: 60px;
        height: 60px;
        top: 20%;
        right: 10%;
        animation-delay: 5s;
    }

    .shape:nth-child(3) {
        width: 100px;
        height: 100px;
        bottom: 10%;
        left: 20%;
        animation-delay: 10s;
    }

    .shape:nth-child(4) {
        width: 40px;
        height: 40px;
        bottom: 20%;
        right: 20%;
        animation-delay: 15s;
    }

    @keyframes float {
        0% { transform: translateY(0px) rotate(0deg); }
        33% { transform: translateY(-30px) rotate(120deg); }
        66% { transform: translateY(20px) rotate(240deg); }
        100% { transform: translateY(0px) rotate(360deg); }
    }

    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }

    .pulse {
        animation: pulse 2s infinite;
    }

    .payment-option {
        margin: 0;
    }

    .payment-card {
        background: rgba(255, 255, 255, 0.1);
        border: 2px solid rgba(255, 255, 255, 0.2);
        border-radius: 15px;
        padding: 20px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        backdrop-filter: blur(10px);
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        min-height: 120px;
    }

    .payment-card:hover {
        border-color: #667eea;
        background: rgba(102, 126, 234, 0.2);
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
    }

    .form-check-input:checked + .form-check-label .payment-card {
        border-color: #667eea;
        background: rgba(102, 126, 234, 0.3);
        box-shadow: 0 0 20px rgba(102, 126, 234, 0.4);
    }

    .payment-icon {
        font-size: 2rem;
        color: #667eea;
        margin-bottom: 10px;
        transition: all 0.3s ease;
    }

    .form-check-input:checked + .form-check-label .payment-icon {
        color: #ffffff;
        transform: scale(1.1);
    }

    .step-indicator {
        display: flex;
        justify-content: center;
        margin-bottom: 30px;
    }

    .step {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 10px;
        position: relative;
        color: white;
        font-weight: bold;
    }

    .step.active {
        background: var(--primary-gradient);
        box-shadow: 0 0 20px rgba(102, 126, 234, 0.5);
    }

    .step::after {
        content: '';
        position: absolute;
        top: 50%;
        left: 100%;
        width: 30px;
        height: 2px;
        background: rgba(255, 255, 255, 0.3);
        margin-left: 10px;
    }

    .step:last-child::after {
        display: none;
    }

    .fade-in {
        animation: fadeIn 0.8s ease-out;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .stagger-animation > * {
        animation: fadeIn 0.8s ease-out;
    }

    .stagger-animation > *:nth-child(1) { animation-delay: 0.1s; }
    .stagger-animation > *:nth-child(2) { animation-delay: 0.2s; }
    .stagger-animation > *:nth-child(3) { animation-delay: 0.3s; }
    .stagger-animation > *:nth-child(4) { animation-delay: 0.4s; }
    </style>
</head>
<body>

<div class="floating-shapes">
    <div class="shape"></div>
    <div class="shape"></div>
    <div class="shape"></div>
    <div class="shape"></div>
</div>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php">
      <i class="bi bi-shop"></i> MarketStore
    </a>
    <div class="d-flex align-items-center gap-2">
      <a href="cart.php" class="btn btn-outline-light btn-sm">
        <i class="bi bi-cart3"></i> Keranjang
      </a>
    </div>
  </div>
</nav>

<div class="container mt-4 fade-in">
    <!-- Step Indicator -->
    <div class="step-indicator">
        <div class="step active">1</div>
        <div class="step active">2</div>
        <div class="step active">3</div>
    </div>

    <div class="row">
        <!-- Order Summary -->
        <div class="col-lg-4 mb-4">
            <div class="card">
                <div class="card-header checkout-header">
                    <h5 class="mb-0"><i class="bi bi-receipt"></i> Ringkasan Pesanan</h5>
                </div>
                <div class="card-body">
                    <div class="stagger-animation">
                        <?php foreach ($cart_items as $item): ?>
                        <div class="product-card d-flex align-items-center">
                            <img src="uploads/<?= $item['product']['image'] ?>" alt="<?= $item['product']['name'] ?>" class="product-image me-3">
                            <div class="flex-grow-1">
                                <h6 class="mb-1"><?= htmlspecialchars($item['product']['name']) ?></h6>
                                <small class="text-muted">Qty: <?= $item['qty'] ?> × Rp<?= number_format($item['product']['price']) ?></small>
                            </div>
                            <div class="text-end">
                                <strong>Rp<?= number_format($item['subtotal']) ?></strong>
                            </div>
                        </div>
                        <?php endforeach; ?>

                        <hr style="border-color: rgba(255,255,255,0.2);">

                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <span>Rp<?= number_format($subtotal) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2" id="shipping-cost">
                            <span>Ongkir:</span>
                            <span>Rp<?= number_format($default_shipping) ?></span>
                        </div>
                        <hr style="border-color: rgba(255,255,255,0.2);">
                        <div class="d-flex justify-content-between">
                            <strong>Total:</strong>
                            <strong id="total-amount">Rp<?= number_format($total) ?></strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Checkout Form -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header checkout-header">
                    <h2 class="mb-0"><i class="bi bi-credit-card"></i> Checkout</h2>
                </div>
                <div class="card-body">
                    <?php if ($error_message): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle-fill"></i> <strong>Error!</strong> <?= htmlspecialchars($error_message) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php endif; ?>

                    <form method="POST" action="checkout_process.php" id="checkoutForm">
                        <!-- Shipping Information -->
                        <div class="mb-4">
                            <h4 class="mb-3"><i class="bi bi-truck"></i> Informasi Pengiriman</h4>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nama Lengkap</label>
                                    <input type="text" name="shipping_name" class="form-control" value="<?= htmlspecialchars($_SESSION['user']['name']) ?>" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nomor Telepon</label>
                                    <input type="tel" name="shipping_phone" class="form-control" placeholder="081234567890" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Alamat Lengkap</label>
                                <textarea name="shipping_address" class="form-control" rows="3" placeholder="Jl. Contoh No. 123, RT/RW 01/02, Kelurahan, Kecamatan, Kota, Kode Pos" required></textarea>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Provinsi</label>
                                    <input type="text" name="shipping_province" class="form-control" placeholder="DKI Jakarta" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Kota</label>
                                    <input type="text" name="shipping_city" class="form-control" placeholder="Jakarta Pusat" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Kode Pos</label>
                                <input type="text" name="shipping_postal" class="form-control" placeholder="10110" required>
                            </div>
                        </div>

                        <!-- Shipping Method -->
                        <div class="mb-4">
                            <h4 class="mb-3"><i class="bi bi-box-seam"></i> Pilih Kurir Pengiriman</h4>
                            <div class="shipping-options">
                                <?php foreach ($shipping_options as $code => $option): ?>
                                <div class="shipping-option" data-courier="<?= $code ?>" data-cost="<?= $option['cost'] ?>">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong><?= $option['name'] ?></strong>
                                            <br>
                                            <small class="text-muted">Estimasi: <?= $option['days'] ?></small>
                                        </div>
                                        <div class="text-end">
                                            <strong>Rp<?= number_format($option['cost']) ?></strong>
                                            <br>
                                            <small class="text-muted">COD tersedia</small>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <input type="hidden" name="shipping_courier" id="selected_courier" value="jne" required>
                            <input type="hidden" name="shipping_cost" id="shipping_cost_input" value="<?= $default_shipping ?>">
                        </div>

                        <!-- Payment Method -->
                        <div class="mb-4">
                            <h4 class="mb-3"><i class="bi bi-wallet2"></i> Metode Pembayaran</h4>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <div class="form-check payment-option">
                                        <input class="form-check-input" type="radio" name="payment_method" value="qris" id="qris">
                                        <label class="form-check-label" for="qris">
                                            <div class="payment-card">
                                                <i class="bi bi-qr-code-scan payment-icon"></i>
                                                <strong>QRIS</strong><br>
                                                <small>Scan QR Code</small>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="form-check payment-option">
                                        <input class="form-check-input" type="radio" name="payment_method" value="transfer" id="transfer" checked>
                                        <label class="form-check-label" for="transfer">
                                            <div class="payment-card">
                                                <i class="bi bi-bank payment-icon"></i>
                                                <strong>Transfer Bank</strong><br>
                                                <small>BCA, Mandiri, BNI, BRI</small>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <div class="form-check payment-option">
                                        <input class="form-check-input" type="radio" name="payment_method" value="cod" id="cod">
                                        <label class="form-check-label" for="cod">
                                            <div class="payment-card">
                                                <i class="bi bi-cash payment-icon"></i>
                                                <strong>Cash on Delivery</strong><br>
                                                <small>Bayar saat diterima</small>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Order Notes -->
                        <div class="mb-4">
                            <h4 class="mb-3"><i class="bi bi-sticky"></i> Catatan Pesanan (Opsional)</h4>
                            <textarea name="order_notes" class="form-control" rows="3" placeholder="Tambahkan catatan khusus untuk pesanan Anda..."></textarea>
                        </div>

                        <!-- Terms and Conditions -->
                        <div class="mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="terms" name="terms" required>
                                <label class="form-check-label" for="terms">
                                    Saya menyetujui <a href="#" class="text-primary">Syarat & Ketentuan</a> dan <a href="#" class="text-primary">Kebijakan Privasi</a>
                                </label>
                            </div>
                        </div>

                        <!-- Total Summary -->
                        <div class="total-summary mb-4">
                            <h3 id="final-total">Rp<?= number_format($total) ?></h3>
                            <p class="mb-0">Total Pembayaran</p>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex gap-3">
                            <a href="cart.php" class="btn btn-outline-light flex-fill">
                                <i class="bi bi-arrow-left"></i> Kembali ke Keranjang
                            </a>
                            <button type="submit" name="checkout" class="btn btn-3d flex-fill pulse">
                                <i class="bi bi-check-circle"></i> Buat Pesanan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let selectedShipping = 'jne'; // Default value
    let shippingCost = <?= $default_shipping ?>;

    // Set default shipping values
    document.getElementById('selected_courier').value = selectedShipping;
    document.getElementById('shipping_cost_input').value = shippingCost;
    document.querySelectorAll('.shipping-option').forEach(option => {
        option.addEventListener('click', function() {
            // Remove selected class from all options
            document.querySelectorAll('.shipping-option').forEach(opt => {
                opt.classList.remove('selected');
            });

            // Add selected class to clicked option
            this.classList.add('selected');

            // Update selected courier
            selectedShipping = this.dataset.courier;
            shippingCost = parseInt(this.dataset.cost);

            document.getElementById('selected_courier').value = selectedShipping;
            document.getElementById('shipping_cost_input').value = shippingCost;

            // Update totals
            updateTotals();
        });
    });

    function updateTotals() {
        const subtotal = <?= $subtotal ?>;
        const total = subtotal + shippingCost;

        document.querySelector('#shipping-cost span:last-child').textContent = 'Rp' + shippingCost.toLocaleString('id-ID');
        document.getElementById('total-amount').textContent = 'Rp' + total.toLocaleString('id-ID');
        document.getElementById('final-total').textContent = 'Rp' + total.toLocaleString('id-ID');
    }

    // Form validation
    document.getElementById('checkoutForm').addEventListener('submit', function(e) {
        // Validasi nama lengkap
        const name = document.querySelector('input[name="shipping_name"]').value.trim();
        if (!name || name.length < 3) {
            e.preventDefault();
            alert('Nama lengkap harus diisi dengan minimal 3 karakter!');
            return false;
        }

        // Validasi nomor telepon
        const phone = document.querySelector('input[name="shipping_phone"]').value.trim();
        if (!phone || phone.length < 10) {
            e.preventDefault();
            alert('Nomor telepon tidak valid!');
            return false;
        }

        // Validasi alamat
        const address = document.querySelector('textarea[name="shipping_address"]').value.trim();
        if (!address || address.length < 10) {
            e.preventDefault();
            alert('Alamat lengkap harus diisi dengan minimal 10 karakter!');
            return false;
        }

        // Validasi provinsi
        const province = document.querySelector('input[name="shipping_province"]').value.trim();
        if (!province) {
            e.preventDefault();
            alert('Provinsi harus diisi!');
            return false;
        }

        // Validasi kota
        const city = document.querySelector('input[name="shipping_city"]').value.trim();
        if (!city) {
            e.preventDefault();
            alert('Kota harus diisi!');
            return false;
        }

        // Validasi kode pos
        const postal = document.querySelector('input[name="shipping_postal"]').value.trim();
        if (!postal || postal.length < 5) {
            e.preventDefault();
            alert('Kode pos tidak valid!');
            return false;
        }

        // Check if courier is selected
        const courier = document.getElementById('selected_courier').value;
        if (!courier) {
            e.preventDefault();
            alert('Silakan pilih kurir pengiriman terlebih dahulu!');
            return false;
        }

        // Check payment method
        const paymentMethod = document.querySelector('input[name="payment_method"]:checked');
        if (!paymentMethod) {
            e.preventDefault();
            alert('Silakan pilih metode pembayaran!');
            return false;
        }

        // Check terms
        const terms = document.getElementById('terms').checked;
        if (!terms) {
            e.preventDefault();
            alert('Silakan setujui syarat dan ketentuan terlebih dahulu!');
            return false;
        }

        // Show confirmation dialog
        const confirmed = confirm('✨ Apakah Anda yakin ingin melakukan pesanan ini?\n\nNama: ' + name + '\nKurir: ' + courier.toUpperCase() + '\nTotal: Rp' + document.getElementById('final-total').textContent.replace('Rp', ''));
        if (!confirmed) {
            e.preventDefault();
            return false;
        }

        // Show loading state
        const submitBtn = this.querySelector('button[type="submit"]');
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Memproses Pesanan...';
        submitBtn.disabled = true;
    });

    // Phone number formatting
    document.querySelector('input[name="shipping_phone"]').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value.startsWith('0')) {
            value = value.substring(1);
        }
        if (value.length > 0) {
            value = '0' + value;
        }
        e.target.value = value;
    });

    // Auto-fill province and city based on postal code (simplified)
    document.querySelector('input[name="shipping_postal"]').addEventListener('blur', function(e) {
        const postal = e.target.value;
        if (postal.length === 5) {
            // This would typically call an API, but for demo we'll use mock data
            if (postal.startsWith('10')) {
                document.querySelector('input[name="shipping_province"]').value = 'DKI Jakarta';
                document.querySelector('input[name="shipping_city"]').value = 'Jakarta Pusat';
            } else if (postal.startsWith('40')) {
                document.querySelector('input[name="shipping_province"]').value = 'Jawa Barat';
                document.querySelector('input[name="shipping_city"]').value = 'Bandung';
            }
        }
    });
});
</script>

</body>
</html>
