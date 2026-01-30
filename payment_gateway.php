<?php
session_start();
include 'config/database.php';
include 'config/payment_config.php';

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

// Generate mock QRIS code
$qris_code = base64_encode("https://qris.id/mock?id=" . $order_id . "&amount=" . $order['total']);

// Remove old bank_accounts variable, menggunakan $BANK_ACCOUNTS dari config

// Handle payment submission
$payment_confirmed = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payment_method = trim($_POST['payment_method'] ?? '');
    $payment_confirmation = trim($_POST['confirmation'] ?? '');
    
    if ($payment_method && $payment_confirmation === 'yes') {
        // Update order status
        $update_query = $conn->prepare("UPDATE orders SET status=?, payment_status='pending' WHERE id=?");
        $new_status = ($payment_method === 'cod') ? 'pending' : 'processing';
        $update_query->bind_param("si", $new_status, $order_id);
        $update_query->execute();
        $update_query->close();
        
        $payment_confirmed = true;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gateway Pembayaran - MarketStore</title>
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

        .payment-container {
            max-width: 900px;
            margin: 0 auto;
        }

        .payment-header {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            text-align: center;
        }

        .payment-header h1 {
            color: #667eea;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .order-summary {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }

        .order-summary h3 {
            color: #667eea;
            margin-bottom: 20px;
            font-weight: 700;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid #f0f0f0;
            align-items: center;
        }

        .summary-row:last-child {
            border-bottom: none;
        }

        .summary-row.total {
            background: #f8f9fa;
            padding: 15px;
            margin-top: 15px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 18px;
        }

        .item-detail {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 20px;
        }

        .item-info {
            flex: 1;
        }

        .item-price {
            text-align: right;
            font-weight: 600;
            color: #667eea;
        }

        .payment-methods {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }

        .payment-methods h3 {
            color: #667eea;
            margin-bottom: 20px;
            font-weight: 700;
        }

        .payment-option {
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }

        .payment-option:hover {
            border-color: #667eea;
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.2);
            background: #f8f9fa;
        }

        .payment-option input[type="radio"] {
            display: none;
        }

        .payment-option input[type="radio"]:checked + .payment-content {
            color: #667eea;
        }

        .payment-option input[type="radio"]:checked ~ .check-mark {
            opacity: 1;
        }

        .payment-content {
            display: flex;
            align-items: center;
            gap: 15px;
            transition: color 0.3s ease;
        }

        .payment-icon {
            font-size: 40px;
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f0f0f0;
            border-radius: 10px;
        }

        .payment-info h4 {
            margin: 0 0 5px 0;
            font-weight: 700;
        }

        .payment-info p {
            margin: 0;
            font-size: 13px;
            color: #666;
        }

        .check-mark {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            width: 24px;
            height: 24px;
            border: 2px solid #ddd;
            border-radius: 50%;
            opacity: 0;
            transition: all 0.3s ease;
            background: white;
        }

        .payment-option input[type="radio"]:checked ~ .check-mark {
            opacity: 1;
            border-color: #667eea;
            background: #667eea;
        }

        .check-mark::after {
            content: '✓';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            font-size: 14px;
            font-weight: 700;
        }

        .payment-details {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin-top: 20px;
            display: none;
            animation: slideDown 0.3s ease;
        }

        .payment-details.active {
            display: block;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .qris-container {
            text-align: center;
        }

        .qris-image {
            width: 250px;
            height: 250px;
            border: 3px solid #667eea;
            border-radius: 10px;
            margin: 20px auto;
            padding: 10px;
            background: white;
        }

        .bank-list {
            list-style: none;
            padding: 0;
        }

        .bank-item {
            background: white;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 12px;
            border-left: 4px solid #667eea;
        }

        .bank-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
            font-weight: 700;
            color: #667eea;
        }

        .bank-details {
            background: #f0f0f0;
            padding: 12px;
            border-radius: 8px;
            margin: 10px 0;
            font-family: monospace;
            font-size: 14px;
            color: #333;
        }

        .bank-details-label {
            font-size: 12px;
            color: #666;
            font-family: Arial;
            margin-bottom: 5px;
        }

        .copy-btn {
            padding: 5px 10px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
            transition: all 0.3s ease;
        }

        .copy-btn:hover {
            background: #5568d3;
        }

        .cod-info {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
        }

        .cod-info p {
            margin: 0;
            color: #333;
        }

        .confirmation-section {
            background: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
        }

        .confirmation-section h3 {
            color: #667eea;
            margin-bottom: 20px;
            font-weight: 700;
        }

        .confirmation-box {
            background: #f8f9fa;
            border: 2px solid #667eea;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
        }

        .confirmation-box label {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            margin: 0;
            font-weight: 600;
            color: #333;
        }

        .confirmation-box input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
            accent-color: #667eea;
        }

        .btn-continue {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 20px;
        }

        .btn-continue:hover:not(:disabled) {
            transform: scale(1.05);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
        }

        .btn-continue:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .success-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.7);
            z-index: 9999;
            align-items: center;
            justify-content: center;
        }

        .success-modal.show {
            display: flex;
        }

        .success-content {
            background: white;
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            max-width: 500px;
            animation: popIn 0.5s ease;
        }

        @keyframes popIn {
            0% {
                transform: scale(0.8);
                opacity: 0;
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        .success-icon {
            font-size: 80px;
            color: #28a745;
            margin-bottom: 20px;
        }

        .success-content h2 {
            color: #333;
            margin-bottom: 15px;
            font-weight: 700;
        }

        .success-content p {
            color: #666;
            margin-bottom: 25px;
            line-height: 1.6;
        }

        .success-button {
            padding: 12px 30px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 700;
            transition: all 0.3s ease;
        }

        .success-button:hover {
            background: #218838;
            transform: scale(1.05);
        }

        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            gap: 10px;
        }

        .step {
            flex: 1;
            height: 4px;
            background: #e0e0e0;
            border-radius: 2px;
            position: relative;
        }

        .step.active {
            background: #667eea;
        }

        .step-label {
            text-align: center;
            color: #666;
            font-size: 12px;
            margin-top: 10px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
<div class="payment-container">
    <div class="step-indicator">
        <div class="step active" style="background: #667eea;"></div>
        <div class="step active" style="background: #667eea;"></div>
        <div class="step active" style="background: #667eea;"></div>
        <div class="step" style="background: #667eea;"></div>
    </div>
    <div class="step-label"><strong>STEP 4:</strong> Pilih Metode Pembayaran</div>

    <!-- Payment Header -->
    <div class="payment-header">
        <h1>🎉 Selamat! Pesanan Anda Berhasil Dibuat</h1>
        <p style="color: #666; margin: 0;">Nomor Pesanan: <strong>#<?= $order_id ?></strong></p>
    </div>

    <!-- Order Summary -->
    <div class="order-summary">
        <h3>📋 Ringkasan Pesanan</h3>
        
        <?php foreach($order_items as $item): ?>
            <div class="summary-row">
                <div class="item-detail">
                    <div class="item-info">
                        <strong><?= htmlspecialchars($item['name']) ?></strong>
                        <div style="font-size: 13px; color: #666;">Qty: <?= $item['quantity'] ?></div>
                    </div>
                </div>
                <div class="item-price">Rp<?= number_format($item['price'] * $item['quantity']) ?></div>
            </div>
        <?php endforeach; ?>

        <div class="summary-row total">
            <div>TOTAL PEMBAYARAN</div>
            <div style="color: #667eea;">Rp<?= number_format($order['total']) ?></div>
        </div>
    </div>

    <!-- Payment Methods -->
    <div class="payment-methods">
        <h3>💳 Pilih Metode Pembayaran</h3>
        
        <form method="POST" id="paymentForm">
            <!-- QRIS Payment -->
            <label class="payment-option">
                <input type="radio" name="payment_method" value="qris" onchange="showPaymentDetails('qris')">
                <div class="payment-content">
                    <div class="payment-icon">📱</div>
                    <div class="payment-info">
                        <h4>QRIS</h4>
                        <p>Scan dengan aplikasi mobile banking Anda</p>
                    </div>
                </div>
                <div class="check-mark"></div>
            </label>

            <div id="qris-details" class="payment-details">
                <div class="qris-container">
                    <p style="color: #333; margin-bottom: 15px;"><strong>Scan QR Code di bawah dengan aplikasi mobile banking Anda:</strong></p>
                    <svg class="qris-image" viewBox="0 0 200 200" xmlns="http://www.w3.org/2000/svg">
                        <!-- Mock QR Code -->
                        <rect width="200" height="200" fill="white"/>
                        <rect x="10" y="10" width="50" height="50" fill="black"/>
                        <rect x="140" y="10" width="50" height="50" fill="black"/>
                        <rect x="10" y="140" width="50" height="50" fill="black"/>
                        
                        <text x="100" y="105" text-anchor="middle" font-size="12" fill="black">
                            QRIS: <?= substr($order_id, 0, 6) ?>
                        </text>
                    </svg>
                    <p style="color: #666; font-size: 13px; margin-top: 15px;">
                        ID Transaksi: <strong><?= $order_id ?></strong><br>
                        Jumlah: <strong>Rp<?= number_format($order['total']) ?></strong>
                    </p>
                </div>
            </div>

            <!-- Bank Transfer Payment -->
            <label class="payment-option">
                <input type="radio" name="payment_method" value="transfer" onchange="showPaymentDetails('transfer')">
                <div class="payment-content">
                    <div class="payment-icon">🏦</div>
                    <div class="payment-info">
                        <h4>Transfer Bank</h4>
                        <p>Transfer melalui rekening bank pilihan Anda</p>
                    </div>
                </div>
                <div class="check-mark"></div>
            </label>

            <div id="transfer-details" class="payment-details">
                <p style="color: #333; margin-bottom: 20px;"><strong>Pilih bank untuk melihat nomor rekening:</strong></p>
                <ul class="bank-list">
                    <?php foreach($BANK_ACCOUNTS as $code => $bank): ?>
                        <li class="bank-item">
                            <div class="bank-header">
                                <span><?= $bank['icon'] ?> <?= $bank['bank_name'] ?></span>
                            </div>
                            <div>
                                <div class="bank-details-label">Nomor Rekening:</div>
                                <div class="bank-details">
                                    <?= $bank['account_number'] ?>
                                    <button type="button" class="copy-btn" onclick="copyToClipboard('<?= $bank['account_number'] ?>')">
                                        📋 Copy
                                    </button>
                                </div>
                            </div>
                            <div>
                                <div class="bank-details-label">Atas Nama:</div>
                                <div class="bank-details"><?= $bank['account_holder'] ?></div>
                            </div>
                            <?php if(!empty($bank['branch'])): ?>
                                <div>
                                    <div class="bank-details-label">Cabang:</div>
                                    <div class="bank-details"><?= $bank['branch'] ?></div>
                                </div>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <p style="color: #666; font-size: 13px; margin-top: 20px;">
                    <strong>⚠️ Penting:</strong> Gunakan kode unik berikut sebagai referensi: <strong><?= $order_id ?></strong><br>
                    Jumlah transfer: <strong>Rp<?= number_format($order['total']) ?></strong>
                </p>
            </div>

            <!-- COD Payment -->
            <label class="payment-option">
                <input type="radio" name="payment_method" value="cod" onchange="showPaymentDetails('cod')">
                <div class="payment-content">
                    <div class="payment-icon">💵</div>
                    <div class="payment-info">
                        <h4>Cash On Delivery (COD)</h4>
                        <p>Bayar saat barang sampai ke tangan Anda</p>
                    </div>
                </div>
                <div class="check-mark"></div>
            </label>

            <div id="cod-details" class="payment-details">
                <div class="cod-info">
                    <p>✅ <strong>Pembayaran akan dilakukan saat kurir mengantar barang ke alamat Anda</strong></p>
                    <p style="margin-top: 10px;">Pastikan uang tunai <strong>Rp<?= number_format($order['total']) ?></strong> telah disiapkan</p>
                </div>
            </div>

            <!-- Confirmation -->
            <div class="confirmation-section" style="margin-top: 30px;">
                <h3>✓ Konfirmasi Pembayaran</h3>
                <div class="confirmation-box">
                    <label>
                        <input type="checkbox" id="confirmationCheckbox" onchange="toggleConfirmButton()">
                        Saya telah memahami metode pembayaran dan siap melanjutkan
                    </label>
                </div>
                <button type="submit" class="btn-continue" id="continueBtn" disabled>
                    ✓ Lanjutkan Pembayaran →
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Success Modal -->
<div id="successModal" class="success-modal <?= $payment_confirmed ? 'show' : '' ?>">
    <div class="success-content">
        <div class="success-icon">✓</div>
        <h2>Pembayaran Dikonfirmasi!</h2>
        <p>Pesanan Anda <strong>#<?= $order_id ?></strong> telah dicatat.<br>Status pembayaran sedang kami verifikasi.</p>
        <a href="user/orders.php" class="success-button">Lihat Status Pesanan →</a>
    </div>
</div>

<script>
    function showPaymentDetails(method) {
        // Hide all payment details
        document.getElementById('qris-details').classList.remove('active');
        document.getElementById('transfer-details').classList.remove('active');
        document.getElementById('cod-details').classList.remove('active');
        
        // Show selected payment details
        document.getElementById(method + '-details').classList.add('active');
    }

    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(function() {
            alert('Nomor rekening disalin ke clipboard!');
        });
    }

    function toggleConfirmButton() {
        const checkbox = document.getElementById('confirmationCheckbox');
        const button = document.getElementById('continueBtn');
        button.disabled = !checkbox.checked;
    }

    // Auto show modal if payment confirmed
    <?php if($payment_confirmed): ?>
        setTimeout(function() {
            window.location.href = 'success_new.php';
        }, 3000);
    <?php endif; ?>
</script>
</body>
</html>
