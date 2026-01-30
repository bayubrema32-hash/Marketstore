<?php
session_start();
include 'config/database.php';

if (!isset($_SESSION['user'])) {
    header("Location: auth/login.php");
    exit;
}

$order_id = $_GET['id'] ?? null;

if (!$order_id) {
    header("Location: orders.php");
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
    header("Location: orders.php");
    exit;
}

// Get order items
$items_query = $conn->prepare("SELECT oi.*, p.name FROM order_items oi
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

// Get user details
$user_query = $conn->prepare("SELECT * FROM users WHERE id=?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user_result = $user_query->get_result();
$user = $user_result->fetch_assoc();
$user_query->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk Pesanan #<?= $order_id ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @media print {
            body {
                background: white;
                padding: 0;
            }
            .receipt {
                box-shadow: none;
            }
            .btn, .no-print {
                display: none;
            }
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            font-family: 'Courier New', monospace;
        }

        .receipt {
            background: white;
            max-width: 500px;
            margin: 0 auto;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }

        .receipt-header {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 2px dashed #333;
            margin-bottom: 20px;
        }

        .receipt-header h1 {
            font-size: 24px;
            margin-bottom: 5px;
            color: #333;
        }

        .receipt-header p {
            margin: 0;
            font-size: 12px;
            color: #666;
        }

        .receipt-section {
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px dashed #ccc;
        }

        .receipt-section:last-child {
            border-bottom: none;
        }

        .section-title {
            font-weight: bold;
            font-size: 12px;
            text-transform: uppercase;
            margin-bottom: 10px;
            color: #333;
        }

        .receipt-row {
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            margin-bottom: 5px;
        }

        .receipt-row.item {
            flex-direction: column;
            margin-bottom: 10px;
        }

        .item-name {
            font-weight: bold;
        }

        .item-detail {
            font-size: 11px;
            color: #666;
            margin-left: 10px;
        }

        .item-price {
            font-weight: bold;
            text-align: right;
        }

        .receipt-total {
            font-weight: bold;
            font-size: 14px;
            display: flex;
            justify-content: space-between;
            background: #f0f0f0;
            padding: 10px;
            border-radius: 5px;
        }

        .receipt-footer {
            text-align: center;
            font-size: 11px;
            color: #666;
            margin-top: 20px;
        }

        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: bold;
            background: #667eea;
            color: white;
            margin-bottom: 10px;
        }

        .action-buttons {
            margin-top: 20px;
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        .btn-action {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
            font-weight: bold;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .btn-print {
            background: #667eea;
            color: white;
        }

        .btn-print:hover {
            background: #5568d3;
        }

        .btn-back {
            background: #f0f0f0;
            color: #333;
            border: 1px solid #ccc;
        }

        .btn-back:hover {
            background: #e0e0e0;
        }

        .kode-unik {
            text-align: center;
            font-size: 10px;
            color: #999;
            margin-top: 10px;
            font-style: italic;
        }
    </style>
</head>
<body>

<div class="receipt">
    <!-- Header -->
    <div class="receipt-header">
        <h1>🛍️ MARKETSTORE</h1>
        <p>Toko Online Terpercaya</p>
    </div>

    <!-- Order Info -->
    <div class="receipt-section">
        <div class="section-title">Informasi Pesanan</div>
        <div class="receipt-row">
            <span>No. Pesanan:</span>
            <span><strong>#<?= $order_id ?></strong></span>
        </div>
        <div class="receipt-row">
            <span>Tanggal:</span>
            <span><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></span>
        </div>
        <div class="receipt-row">
            <span>Status:</span>
            <span><strong><?= ucfirst($order['status']) ?></strong></span>
        </div>
    </div>

    <!-- Customer Info -->
    <div class="receipt-section">
        <div class="section-title">Data Pelanggan</div>
        <div class="receipt-row">
            <span>Nama:</span>
            <span><?= htmlspecialchars($order['shipping_name']) ?></span>
        </div>
        <div class="receipt-row">
            <span>Telepon:</span>
            <span><?= htmlspecialchars($order['shipping_phone']) ?></span>
        </div>
        <div class="receipt-row">
            <span>Email:</span>
            <span style="font-size: 10px;"><?= htmlspecialchars($user['email'] ?? 'N/A') ?></span>
        </div>
    </div>

    <!-- Shipping Address -->
    <div class="receipt-section">
        <div class="section-title">Alamat Pengiriman</div>
        <div class="receipt-row">
            <span><?= htmlspecialchars($order['shipping_address']) ?></span>
        </div>
        <div class="receipt-row">
            <span><?= htmlspecialchars($order['shipping_city']) ?>, <?= htmlspecialchars($order['shipping_province']) ?> <?= htmlspecialchars($order['shipping_postal']) ?></span>
        </div>
        <div class="receipt-row">
            <span>Kurir: <strong><?= htmlspecialchars($order['shipping_courier']) ?></strong></span>
        </div>
    </div>

    <!-- Items -->
    <div class="receipt-section">
        <div class="section-title">Detail Pesanan</div>
        <?php foreach($order_items as $item): ?>
            <div class="receipt-row item">
                <div class="item-name"><?= htmlspecialchars($item['name']) ?></div>
                <div class="item-detail">
                    <?= $item['quantity'] ?> x Rp<?= number_format($item['price']) ?> = 
                    <span class="item-price">Rp<?= number_format($item['price'] * $item['quantity']) ?></span>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Totals -->
    <div class="receipt-section">
        <div class="receipt-row">
            <span>Subtotal</span>
            <span>Rp<?= number_format($order['total'] - $order['shipping_cost']) ?></span>
        </div>
        <div class="receipt-row">
            <span>Ongkir</span>
            <span>Rp<?= number_format($order['shipping_cost']) ?></span>
        </div>
        <div class="receipt-total" style="margin-top: 10px;">
            <span>TOTAL</span>
            <span>Rp<?= number_format($order['total']) ?></span>
        </div>
    </div>

    <!-- Payment Method -->
    <div class="receipt-section">
        <div class="section-title">Metode Pembayaran</div>
        <div class="receipt-row">
            <span><?= ucfirst($order['payment_method']) ?></span>
        </div>
        <div class="receipt-row">
            <span>Status:</span>
            <span><?= ucfirst($order['payment_status'] ?? 'Pending') ?></span>
        </div>
    </div>

    <!-- Footer -->
    <div class="receipt-footer">
        <p>Terima kasih telah berbelanja di MarketStore!</p>
        <p>Kami siap melayani Anda dengan sebaik-baiknya</p>
        <div class="kode-unik">
            Kode: <?= strtoupper(bin2hex(substr(md5($order_id), 0, 6))) ?>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="action-buttons no-print">
        <button class="btn-action btn-print" onclick="window.print()">
            🖨️ Cetak Struk
        </button>
        <a href="orders.php" class="btn-action btn-back">
            ← Kembali
        </a>
    </div>
</div>

</body>
</html>
