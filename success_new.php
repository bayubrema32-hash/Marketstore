<?php
session_start();
include 'config/database.php';

if (!isset($_SESSION['user'])) {
    header("Location: auth/login.php");
    exit;
}

$order_id = $_SESSION['last_order_id'] ?? $_GET['order'] ?? null;

if (!$order_id) {
    header("Location: index.php");
    exit;
}

$user_id = $_SESSION['user']['id'];

// Get order details
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

unset($_SESSION['last_order_id']);
unset($_SESSION['checkout_success']);

// Determine payment method icon and info
$payment_icons = [
    'qris' => ['icon' => '📱', 'name' => 'QRIS'],
    'transfer' => ['icon' => '🏦', 'name' => 'Transfer Bank'],
    'cod' => ['icon' => '💵', 'name' => 'Cash On Delivery']
];

$payment_info = $payment_icons[strtolower($order['payment_method'])] ?? ['icon' => '💳', 'name' => 'Pembayaran'];

// Status color mapping
$status_colors = [
    'pending' => '#ffc107',
    'processing' => '#17a2b8',
    'completed' => '#28a745',
    'delivered' => '#20c997'
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Berhasil - MarketStore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 30px 15px;
        }

        .container-success {
            max-width: 900px;
            margin: 0 auto;
        }

        .success-header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            margin-bottom: 30px;
            box-shadow: 0 10px 40px rgba(40, 167, 69, 0.3);
            color: white;
        }

        .success-icon {
            font-size: 80px;
            margin-bottom: 20px;
            animation: scaleIn 0.6s ease-out;
        }

        @keyframes scaleIn {
            0% {
                transform: scale(0);
            }
            50% {
                transform: scale(1.2);
            }
            100% {
                transform: scale(1);
            }
        }

        .success-header h1 {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .success-header p {
            font-size: 16px;
            opacity: 0.95;
            margin: 0;
        }

        .order-number {
            background: rgba(255,255,255,0.2);
            padding: 15px;
            border-radius: 10px;
            margin-top: 20px;
            font-weight: 700;
            font-size: 18px;
        }

        .card-main {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            margin-bottom: 30px;
            overflow: hidden;
        }

        .card-header-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 30px;
            font-weight: 700;
            font-size: 18px;
        }

        .card-body-custom {
            padding: 30px;
        }

        .payment-status-box {
            background: #f8f9fa;
            border-left: 5px solid #667eea;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }

        .payment-method-display {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 15px;
        }

        .payment-icon {
            font-size: 50px;
            width: 80px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: white;
            border-radius: 12px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.1);
        }

        .payment-details-box {
            flex: 1;
        }

        .payment-details-box h4 {
            color: #667eea;
            margin: 0 0 10px 0;
            font-weight: 700;
        }

        .payment-details-box p {
            margin: 0;
            color: #666;
            font-size: 14px;
        }

        .status-badge-custom {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 20px;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 12px;
            color: white;
            margin-bottom: 20px;
        }

        .order-summary-section {
            margin-bottom: 30px;
        }

        .order-summary-section h3 {
            color: #667eea;
            margin-bottom: 20px;
            font-weight: 700;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 10px;
        }

        .item-card {
            display: flex;
            gap: 15px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
            margin-bottom: 15px;
            align-items: center;
        }

        .item-image {
            width: 80px;
            height: 80px;
            border-radius: 10px;
            object-fit: cover;
            background: white;
            border: 2px solid #e0e0e0;
        }

        .item-info {
            flex: 1;
        }

        .item-info h5 {
            margin: 0 0 5px 0;
            color: #333;
            font-weight: 700;
        }

        .item-info p {
            margin: 0;
            color: #666;
            font-size: 13px;
        }

        .item-price {
            text-align: right;
        }

        .item-price .price {
            color: #667eea;
            font-weight: 700;
            font-size: 18px;
        }

        .item-price .qty {
            color: #999;
            font-size: 13px;
        }

        .summary-total {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }

        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #e0e0e0;
        }

        .total-row:last-child {
            border-bottom: none;
        }

        .total-row.grand-total {
            background: white;
            padding: 15px;
            margin: -20px -20px 0 -20px;
            border-radius: 0 0 10px 10px;
            font-weight: 700;
            font-size: 20px;
            color: #667eea;
        }

        .shipping-info {
            background: #f0f7ff;
            border-left: 4px solid #667eea;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .shipping-info h4 {
            color: #667eea;
            margin: 0 0 15px 0;
            font-weight: 700;
        }

        .shipping-detail-row {
            display: flex;
            margin-bottom: 10px;
            gap: 20px;
        }

        .shipping-detail-row:last-child {
            margin-bottom: 0;
        }

        .shipping-label {
            color: #666;
            font-weight: 600;
            width: 150px;
            font-size: 13px;
        }

        .shipping-value {
            color: #333;
            flex: 1;
            font-weight: 500;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        .btn-custom {
            flex: 1;
            min-width: 150px;
            padding: 15px 25px;
            border: none;
            border-radius: 10px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-size: 15px;
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary-custom:hover {
            transform: scale(1.05);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary-custom {
            background: #f0f0f0;
            color: #333;
            border: 2px solid #667eea;
        }

        .btn-secondary-custom:hover {
            background: #667eea;
            color: white;
        }

        .info-box {
            background: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .info-box p {
            margin: 0;
            color: #333;
            font-size: 14px;
            line-height: 1.6;
        }

        .payment-instruction {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .payment-instruction h5 {
            color: #856404;
            margin: 0 0 10px 0;
            font-weight: 700;
        }

        .payment-instruction p {
            margin: 0 0 10px 0;
            color: #856404;
            font-size: 14px;
            line-height: 1.6;
        }

        .payment-instruction p:last-child {
            margin-bottom: 0;
        }

        @media (max-width: 768px) {
            .success-header {
                padding: 30px 20px;
            }

            .success-header h1 {
                font-size: 24px;
            }

            .success-icon {
                font-size: 60px;
            }

            .card-body-custom {
                padding: 20px;
            }

            .shipping-detail-row {
                flex-direction: column;
                gap: 5px;
            }

            .shipping-label {
                width: 100%;
            }

            .action-buttons {
                flex-direction: column;
            }

            .btn-custom {
                width: 100%;
            }
        }
    </style>
</head>
<body>
<div class="container-success">
    <!-- Success Header -->
    <div class="success-header">
        <div class="success-icon">✓</div>
        <h1>Pesanan Berhasil Dibuat!</h1>
        <p>Terima kasih telah berbelanja di MarketStore</p>
        <div class="order-number">
            Nomor Pesanan: #<?= $order_id ?>
        </div>
    </div>

    <!-- Main Content -->
    <div class="card-main">
        <div class="card-header-custom">
            💳 Informasi Pembayaran
        </div>
        <div class="card-body-custom">
            <!-- Status Badge -->
            <div style="margin-bottom: 20px;">
                <span class="status-badge-custom" style="background-color: <?= $status_colors[$order['status']] ?? '#667eea' ?>;">
                    <?= ucfirst($order['status']) ?>
                </span>
            </div>

            <!-- Payment Method -->
            <div class="payment-status-box">
                <div class="payment-method-display">
                    <div class="payment-icon"><?= $payment_info['icon'] ?></div>
                    <div class="payment-details-box">
                        <h4>Metode Pembayaran</h4>
                        <p><?= $payment_info['name'] ?></p>
                    </div>
                </div>

                <!-- Payment Instructions -->
                <?php if(strtolower($order['payment_method']) === 'qris'): ?>
                    <div class="payment-instruction">
                        <h5>📱 Instruksi Pembayaran QRIS</h5>
                        <p>✓ Buka aplikasi mobile banking Anda (BCA, Mandiri, BNI, dll)</p>
                        <p>✓ Pilih menu "Scan QRIS" atau "Bayar dengan QRIS"</p>
                        <p>✓ Scan kode QR yang telah dikirimkan ke email Anda</p>
                        <p>✓ Konfirmasi pembayaran sejumlah <strong>Rp<?= number_format($order['total']) ?></strong></p>
                        <p>✓ Tunggu notifikasi pembayaran berhasil</p>
                    </div>
                <?php elseif(strtolower($order['payment_method']) === 'transfer'): ?>
                    <div class="payment-instruction">
                        <h5>🏦 Instruksi Transfer Bank</h5>
                        <p>✓ Transfer ke nomor rekening yang sudah dikirimkan ke email Anda</p>
                        <p>✓ Jumlah transfer: <strong>Rp<?= number_format($order['total']) ?></strong></p>
                        <p>✓ Sertakan kode referensi: <strong><?= $order_id ?></strong> di berita transfer</p>
                        <p>✓ Kami akan verifikasi pembayaran Anda dalam 1-2 jam</p>
                    </div>
                <?php elseif(strtolower($order['payment_method']) === 'cod'): ?>
                    <div class="payment-instruction">
                        <h5>💵 Cash On Delivery (COD)</h5>
                        <p>✓ Siapkan uang tunai: <strong>Rp<?= number_format($order['total']) ?></strong></p>
                        <p>✓ Pembayaran dilakukan saat kurir mengantarkan barang</p>
                        <p>✓ Pastikan uang pas atau siapkan kembalian</p>
                        <p>✓ Tunjukkan nomor pesanan ini: <strong>#<?= $order_id ?></strong></p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Info Box -->
            <div class="info-box">
                <p>
                    <strong>📧 Bukti pembayaran dan detail pesanan telah dikirim ke email Anda.</strong><br>
                    Silakan cek email atau hubungi customer service kami jika ada pertanyaan.
                </p>
            </div>
        </div>
    </div>

    <!-- Order Summary -->
    <div class="card-main">
        <div class="card-header-custom">
            📦 Ringkasan Pesanan
        </div>
        <div class="card-body-custom">
            <!-- Items -->
            <div class="order-summary-section">
                <h3>Produk Pesanan</h3>
                <?php foreach($order_items as $item): ?>
                    <div class="item-card">
                        <?php if(!empty($item['image'])): ?>
                            <img src="assets/img/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="item-image">
                        <?php else: ?>
                            <div class="item-image" style="background: #f0f0f0; display: flex; align-items: center; justify-content: center;">
                                📷
                            </div>
                        <?php endif; ?>
                        <div class="item-info">
                            <h5><?= htmlspecialchars($item['name']) ?></h5>
                            <p>Harga: Rp<?= number_format($item['price']) ?>/unit</p>
                        </div>
                        <div class="item-price">
                            <div class="price">Rp<?= number_format($item['price'] * $item['quantity']) ?></div>
                            <div class="qty"><?= $item['quantity'] ?> unit</div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div class="summary-total">
                    <div class="total-row">
                        <div>Subtotal</div>
                        <div>Rp<?= number_format($order['total'] - $order['shipping_cost']) ?></div>
                    </div>
                    <div class="total-row">
                        <div>Ongkir (<?= htmlspecialchars($order['shipping_courier']) ?>)</div>
                        <div>Rp<?= number_format($order['shipping_cost']) ?></div>
                    </div>
                    <div class="total-row grand-total">
                        <div>TOTAL PEMBAYARAN</div>
                        <div>Rp<?= number_format($order['total']) ?></div>
                    </div>
                </div>
            </div>

            <!-- Shipping Info -->
            <div class="order-summary-section">
                <h3>Alamat Pengiriman</h3>
                <div class="shipping-info">
                    <h4>📍 <?= htmlspecialchars($order['shipping_name']) ?></h4>
                    <div class="shipping-detail-row">
                        <div class="shipping-label">Nomor Telepon:</div>
                        <div class="shipping-value"><?= htmlspecialchars($order['shipping_phone']) ?></div>
                    </div>
                    <div class="shipping-detail-row">
                        <div class="shipping-label">Alamat:</div>
                        <div class="shipping-value"><?= htmlspecialchars($order['shipping_address']) ?></div>
                    </div>
                    <div class="shipping-detail-row">
                        <div class="shipping-label">Kota/Kabupaten:</div>
                        <div class="shipping-value"><?= htmlspecialchars($order['shipping_city']) ?>, <?= htmlspecialchars($order['shipping_province']) ?></div>
                    </div>
                    <div class="shipping-detail-row">
                        <div class="shipping-label">Kode Pos:</div>
                        <div class="shipping-value"><?= htmlspecialchars($order['shipping_postal']) ?></div>
                    </div>
                    <div class="shipping-detail-row">
                        <div class="shipping-label">Kurir:</div>
                        <div class="shipping-value"><?= htmlspecialchars($order['shipping_courier']) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="action-buttons">
        <a href="user/orders.php" class="btn-custom btn-primary-custom">
            <i class="fas fa-box"></i> Lacak Pesanan
        </a>
        <a href="index.php" class="btn-custom btn-secondary-custom">
            <i class="fas fa-arrow-left"></i> Kembali Belanja
        </a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
