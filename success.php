<?php
session_start();
include 'config/database.php';

if (!isset($_SESSION['user'])) {
    header("Location: auth/login.php");
    exit;
}

$order_id = $_SESSION['last_order_id'] ?? null;

if (!$order_id) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user']['id'];

// Get order details with prepared statement
$order_query = $conn->prepare("SELECT * FROM orders WHERE id=? AND user_id=?");
$order_query->bind_param("ii", $order_id, $user_id);
$order_query->execute();
$order_result = $order_query->get_result();
$order = $order_result->fetch_assoc();
$order_query->close();

if (!$order) {
    header("Location: index.php");
    exit;
}

// Get order items
$items_query = $conn->prepare("SELECT oi.*, p.name, p.image FROM order_items oi
                JOIN products p ON oi.product_id = p.id
                WHERE oi.order_id=?");
$items_query->bind_param("i", $order_id);
$items_query->execute();
$items_result = $items_query->get_result();
$order_items = [];
while ($item = $items_result->fetch_assoc()) {
    $order_items[] = $item;
}
$items_query->close();

// Clear last order ID and get success message
$success_message = $_SESSION['checkout_success'] ?? 'Pesanan Anda berhasil dibuat!';
unset($_SESSION['last_order_id']);
unset($_SESSION['checkout_success']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Berhasil - MarketStore</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <style>
    :root {
        --success-gradient: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --dark-gradient: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    }

    body {
        background:
            radial-gradient(circle at 20% 50%, rgba(67, 233, 123, 0.2) 0%, transparent 50%),
            radial-gradient(circle at 80% 20%, rgba(56, 249, 215, 0.2) 0%, transparent 50%),
            radial-gradient(circle at 40% 80%, rgba(102, 126, 234, 0.2) 0%, transparent 50%),
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
            url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="success-grain" width="100" height="100" patternUnits="userSpaceOnUse"><circle cx="25" cy="25" r="1" fill="rgba(67,233,123,0.1)"/><circle cx="75" cy="75" r="1" fill="rgba(56,249,215,0.1)"/><circle cx="50" cy="10" r="0.5" fill="rgba(102,126,234,0.05)"/><circle cx="90" cy="40" r="0.5" fill="rgba(102,126,234,0.05)"/></pattern></defs><rect width="100" height="100" fill="url(%23success-grain)"/></svg>');
        pointer-events: none;
        z-index: -1;
    }

    .success-header {
        background: var(--success-gradient);
        border-radius: 20px;
        padding: 40px;
        text-align: center;
        position: relative;
        overflow: hidden;
        margin-bottom: 30px;
        box-shadow: 0 20px 40px rgba(67, 233, 123, 0.3);
    }

    .success-header::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: conic-gradient(from 0deg, transparent, rgba(255,255,255,0.2), transparent);
        animation: rotate 20s linear infinite;
    }

    .success-icon {
        font-size: 5rem;
        color: white;
        margin-bottom: 20px;
        animation: bounce 2s infinite;
        position: relative;
        z-index: 1;
    }

    @keyframes bounce {
        0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
        40% { transform: translateY(-10px); }
        60% { transform: translateY(-5px); }
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
        transform: translateY(-5px);
        box-shadow:
            0 30px 60px rgba(0, 0, 0, 0.4),
            0 0 0 1px rgba(255, 255, 255, 0.2) inset;
    }

    .order-summary {
        background: var(--primary-gradient);
        border-radius: 15px;
        padding: 25px;
        color: white;
        margin-bottom: 20px;
        position: relative;
        overflow: hidden;
    }

    .order-summary::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: conic-gradient(from 0deg, transparent, rgba(255,255,255,0.1), transparent);
        animation: rotate 15s linear infinite;
    }

    .product-item {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 15px;
        padding: 15px;
        margin-bottom: 15px;
        backdrop-filter: blur(10px);
        transition: all 0.3s ease;
    }

    .product-item:hover {
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

    .status-badge {
        padding: 8px 16px;
        border-radius: 20px;
        font-weight: 500;
        text-transform: uppercase;
        font-size: 0.8em;
    }

    .status-pending { background: #ffa726; color: #000; }
    .status-processing { background: #42a5f5; color: white; }
    .status-completed { background: #66bb6a; color: white; }
    .status-cancelled { background: #ef5350; color: white; }

    .shipping-info {
        background: rgba(255, 255, 255, 0.05);
        border-radius: 15px;
        padding: 20px;
        margin-bottom: 20px;
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
        background: rgba(67, 233, 123, 0.1);
        animation: float 20s infinite linear;
    }

    .shape:nth-child(1) {
        width: 100px;
        height: 100px;
        top: 10%;
        left: 10%;
        animation-delay: 0s;
    }

    .shape:nth-child(2) {
        width: 80px;
        height: 80px;
        top: 20%;
        right: 10%;
        animation-delay: 5s;
    }

    .shape:nth-child(3) {
        width: 120px;
        height: 120px;
        bottom: 10%;
        left: 20%;
        animation-delay: 10s;
    }

    .shape:nth-child(4) {
        width: 60px;
        height: 60px;
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

    @keyframes rotate {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
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

    .payment-method-badge {
        background: var(--primary-gradient);
        color: white;
        padding: 10px 20px;
        border-radius: 25px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
    }

    .payment-instruction {
        margin-top: 20px;
    }

    .qris-proof, .cod-proof {
        text-align: center;
        padding: 20px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 15px;
        margin-top: 15px;
    }

    .bank-details {
        background: rgba(255, 255, 255, 0.1);
        padding: 15px;
        border-radius: 10px;
        font-family: monospace;
        text-align: center;
    }

    .bank-item {
        margin-bottom: 10px;
        line-height: 1.6;
    }
    </style>
</head>
<body>

<div class="floating-shapes">
    <div class="shape"></div>
    <div class="shape"></div>
    <div class="shape"></div>
    <div class="shape"></div>
</div>

<div class="container mt-5 fade-in">
    <!-- Success Header -->
    <div class="success-header">
        <div class="success-icon">
            <i class="bi bi-check-circle-fill"></i>
        </div>
        <h1 class="mb-2">🎉 Pesanan Berhasil Dibuat!</h1>
        <p class="mb-3 fs-4" style="font-weight: 700; color: white; text-shadow: 2px 2px 4px rgba(0,0,0,0.2);">✨ Selamat Anda Telah Berbelanja! ✨</p>
        <p class="mb-2 fs-5">Terima kasih telah berbelanja di MarketStore</p>
        <p class="mb-2"><strong><?= htmlspecialchars($success_message) ?></strong></p>
        <p class="mb-0">Order ID: <strong>#<?= $order['id'] ?></strong></p>
    </div>

    <div class="row">
        <!-- Order Details -->
        <div class="col-lg-8 mb-4">
            <!-- Order Summary -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-receipt"></i> Detail Pesanan</h5>
                </div>
                <div class="card-body">
                    <div class="stagger-animation">
                        <?php foreach ($order_items as $item): ?>
                        <div class="product-item d-flex align-items-center">
                            <img src="uploads/<?= $item['image'] ?>" alt="<?= $item['name'] ?>" class="product-image me-3">
                            <div class="flex-grow-1">
                                <h6 class="mb-1"><?= htmlspecialchars($item['name']) ?></h6>
                                <small class="text-muted">Qty: <?= $item['qty'] ?> × Rp<?= number_format($item['price']) ?></small>
                            </div>
                            <div class="text-end">
                                <strong>Rp<?= number_format($item['price'] * $item['qty']) ?></strong>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Shipping Information -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-truck"></i> Informasi Pengiriman</h5>
                </div>
                <div class="card-body">
                    <div class="shipping-info">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Nama Penerima</h6>
                                <p class="mb-2"><?= htmlspecialchars($order['shipping_name']) ?></p>

                                <h6>Nomor Telepon</h6>
                                <p class="mb-2"><?= htmlspecialchars($order['shipping_phone']) ?></p>

                                <h6>Kurir</h6>
                                <p class="mb-2">
                                    <span class="badge bg-primary">
                                        <?= strtoupper($order['shipping_courier']) ?> - Rp<?= number_format($order['shipping_cost']) ?>
                                    </span>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <h6>Alamat Lengkap</h6>
                                <p class="mb-2">
                                    <?= htmlspecialchars($order['shipping_address']) ?><br>
                                    <?= htmlspecialchars($order['shipping_city']) ?>, <?= htmlspecialchars($order['shipping_province']) ?><br>
                                    Kode Pos: <?= htmlspecialchars($order['shipping_postal']) ?>
                                </p>
                            </div>
                        </div>
                    </div>

                    <?php if (!empty($order['order_notes'])): ?>
                    <div class="mt-3">
                        <h6>Catatan Pesanan</h6>
                        <p class="text-muted fst-italic">"<?= htmlspecialchars($order['order_notes']) ?>"</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Order Summary & Actions -->
        <div class="col-lg-4">
            <!-- Order Summary Card -->
            <div class="order-summary mb-4">
                <h4 class="mb-3">Ringkasan Pembayaran</h4>
                <?php 
                    $subtotal_calc = 0;
                    foreach ($order_items as $item) {
                        $subtotal_calc += $item['price'] * $item['qty'];
                    }
                ?>
                <div class="d-flex justify-content-between mb-2">
                    <span>Subtotal:</span>
                    <span>Rp<?= number_format($subtotal_calc) ?></span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span>Ongkir (<?= strtoupper($order['shipping_courier']) ?>):</span>
                    <span>Rp<?= number_format($order['shipping_cost']) ?></span>
                </div>
                <hr style="border-color: rgba(255,255,255,0.3);">
                <div class="d-flex justify-content-between">
                    <strong>Total Pembayaran:</strong>
                    <strong style="font-size: 1.3rem; color: #43e97b;">Rp<?= number_format($order['total']) ?></strong>
                </div>
            </div>

            <!-- Status & Payment -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0"><i class="bi bi-info-circle"></i> Status Pesanan & Pembayaran</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 text-center mb-3">
                            <div class="status-badge status-<?= $order['status'] ?> mb-2">
                                <?= ucfirst($order['status']) ?>
                            </div>
                            <small class="text-muted">Status Pesanan</small>
                        </div>
                        <div class="col-md-6 text-center mb-3">
                            <div class="payment-method-badge mb-2">
                                <i class="bi bi-<?= $order['payment_method'] == 'qris' ? 'qr-code-scan' : ($order['payment_method'] == 'transfer' ? 'bank' : 'cash') ?>"></i>
                                <?= ucfirst($order['payment_method']) ?>
                            </div>
                            <small class="text-muted">Metode Pembayaran</small>
                        </div>
                    </div>

                    <?php if ($order['payment_method'] == 'qris'): ?>
                    <div class="payment-instruction qris-instruction">
                        <div class="alert alert-success">
                            <h6><i class="bi bi-check-circle"></i> Pembayaran QRIS Berhasil!</h6>
                            <p class="mb-2">Pesanan Anda telah dikonfirmasi dan akan segera diproses.</p>
                            <div class="qris-proof">
                                <div class="qris-code mb-3">
                                    <img src="assets/qris_code.svg" alt="QRIS Code" style="width: 150px; height: 150px; border-radius: 10px; border: 2px solid #000;">
                                </div>
                                <p class="text-success fw-bold">QRIS Payment Verified</p>
                                <small class="text-muted">ID Transaksi: QR<?= str_pad($order['id'], 8, '0', STR_PAD_LEFT) ?></small>
                                <div class="mt-2">
                                    <small class="text-muted">Total: Rp<?= number_format($order['total'], 0, ',', '.') ?></small>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php elseif ($order['payment_method'] == 'transfer'): ?>
                    <div class="payment-instruction transfer-instruction">
                        <div class="alert alert-warning">
                            <h6><i class="bi bi-exclamation-triangle"></i> Menunggu Konfirmasi Pembayaran</h6>
                            <p class="mb-3">Silakan transfer ke rekening berikut dan upload bukti pembayaran:</p>
                            <div class="bank-details">
                                <div class="bank-item">
                                    <strong>BCA:</strong> 1234567890 a.n. MarketStore<br>
                                    <strong>Mandiri:</strong> 0987654321 a.n. MarketStore<br>
                                    <strong>BNI:</strong> 1122334455 a.n. MarketStore<br>
                                    <strong>BRI:</strong> 5566778899 a.n. MarketStore
                                </div>
                                <div class="mt-3">
                                    <strong>Nominal: Rp<?= number_format($order['total'], 0, ',', '.') ?></strong>
                                </div>
                            </div>
                            <div class="text-center mt-3">
                                <a href="upload_payment.php?order_id=<?= $order['id'] ?>" class="btn btn-warning">
                                    <i class="bi bi-cloud-upload"></i> Upload Bukti Pembayaran
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php elseif ($order['payment_method'] == 'cod'): ?>
                    <div class="payment-instruction cod-instruction">
                        <div class="alert alert-info">
                            <h6><i class="bi bi-truck"></i> Cash on Delivery (COD)</h6>
                            <p class="mb-2">Anda akan membayar saat kurir mengantar pesanan.</p>
                            <div class="cod-proof">
                                <i class="bi bi-cash display-4 text-info mb-2"></i>
                                <p class="text-info fw-bold">Bayar Saat Diterima</p>
                                <small class="text-muted">Pastikan uang pas untuk memudahkan transaksi</small>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="card">
                <div class="card-body text-center">
                    <h5 class="mb-3">Apa Selanjutnya?</h5>
                    <div class="d-grid gap-2">
                        <?php if ($_SESSION['user']['role'] !== 'customer'): ?>
                            <!-- For Admin/Superadmin -->
                            <a href="admin/dashboard.php" class="btn btn-3d">
                                <i class="bi bi-speedometer2"></i> Kembali ke Dashboard
                            </a>
                            <a href="admin/orders.php" class="btn btn-outline-light">
                                <i class="bi bi-list-check"></i> Lihat Semua Pesanan
                            </a>
                        <?php else: ?>
                            <!-- For Customer -->
                            <a href="receipt.php?order_id=<?= $order['id'] ?>" class="btn btn-3d">
                                <i class="bi bi-receipt"></i> Lihat Struk Pembayaran
                            </a>
                            <a href="user/orders.php" class="btn btn-outline-light">
                                <i class="bi bi-list-check"></i> Lihat Pesanan Saya
                            </a>
                            <a href="index.php" class="btn btn-outline-light">
                                <i class="bi bi-shop"></i> Lanjut Belanja
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Add confetti effect
function createConfetti() {
    const colors = ['#667eea', '#764ba2', '#43e97b', '#38f9d7', '#fa709a', '#fee140'];
    for (let i = 0; i < 50; i++) {
        setTimeout(() => {
            const confetti = document.createElement('div');
            confetti.style.position = 'fixed';
            confetti.style.width = '10px';
            confetti.style.height = '10px';
            confetti.style.background = colors[Math.floor(Math.random() * colors.length)];
            confetti.style.left = Math.random() * 100 + 'vw';
            confetti.style.top = '-10px';
            confetti.style.borderRadius = '50%';
            confetti.style.zIndex = '9999';
            confetti.style.animation = 'fall 3s linear forwards';
            document.body.appendChild(confetti);

            setTimeout(() => confetti.remove(), 3000);
        }, i * 50);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    createConfetti();

    // Add stagger animation
    const elements = document.querySelectorAll('.stagger-animation > *');
    elements.forEach((el, index) => {
        el.style.animationDelay = (index * 0.1) + 's';
    });
});

@keyframes fall {
    to {
        transform: translateY(100vh) rotate(360deg);
        opacity: 0;
    }
}
</script>

</body>
</html>
