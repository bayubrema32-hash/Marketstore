<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['user']['id'];

// Get user's orders
$query = $conn->prepare("SELECT * FROM orders WHERE user_id=? ORDER BY created_at DESC");
$query->bind_param("i", $user_id);
$query->execute();
$orders_result = $query->get_result();
$orders = [];
while ($order = $orders_result->fetch_assoc()) {
    $orders[] = $order;
}
$query->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Saya - MarketStore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 30px 15px;
        }

        .main-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            padding: 30px;
        }

        .page-title {
            color: #667eea;
            margin-bottom: 30px;
            font-weight: 700;
            border-bottom: 3px solid #667eea;
            padding-bottom: 15px;
        }

        .order-card {
            border: 1px solid #e0e0e0;
            border-radius: 12px;
            margin-bottom: 20px;
            overflow: hidden;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .order-card:hover {
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.3);
            transform: translateY(-3px);
        }

        .order-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 25px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .order-number {
            font-size: 18px;
            font-weight: 700;
        }

        .order-date {
            font-size: 13px;
            opacity: 0.9;
        }

        .order-body {
            padding: 25px;
        }

        .order-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .info-box {
            padding: 12px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 3px solid #667eea;
        }

        .info-label {
            font-size: 11px;
            color: #666;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .info-value {
            font-size: 15px;
            font-weight: 700;
            color: #333;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-pending {
            background-color: #ffd700;
            color: #333;
        }

        .status-processing {
            background-color: #4da6ff;
            color: white;
        }

        .status-completed {
            background-color: #28a745;
            color: white;
        }

        .status-delivered {
            background-color: #20c997;
            color: white;
        }

        .order-items {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .item-summary {
            font-size: 13px;
            color: #666;
        }

        .item-count {
            font-weight: 700;
            color: #667eea;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .btn-action {
            padding: 10px 15px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .btn-detail {
            background: #667eea;
            color: white;
        }

        .btn-detail:hover {
            background: #5568d3;
            transform: scale(1.05);
        }

        .btn-receipt {
            background: #20c997;
            color: white;
        }

        .btn-receipt:hover {
            background: #1aa179;
            transform: scale(1.05);
        }

        .btn-track {
            background: #ff9800;
            color: white;
        }

        .btn-track:hover {
            background: #e68900;
            transform: scale(1.05);
        }

        .no-orders {
            text-align: center;
            padding: 50px 20px;
            color: #999;
        }

        .no-orders i {
            font-size: 60px;
            opacity: 0.3;
            margin-bottom: 20px;
            display: block;
        }

        .back-btn {
            display: inline-block;
            margin-bottom: 20px;
            padding: 10px 20px;
            background: #667eea;
            color: white;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 600;
        }

        .back-btn:hover {
            background: #5568d3;
            transform: scale(1.05);
        }

        .total-amount {
            font-size: 18px;
            font-weight: 700;
            color: #667eea;
        }

        @media (max-width: 768px) {
            .order-header {
                flex-direction: column;
                align-items: flex-start;
            }

            .order-info-grid {
                grid-template-columns: 1fr;
            }

            .action-buttons {
                flex-direction: column;
            }

            .btn-action {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
<div class="container-lg">
    <div class="main-container">
        <a href="../index.php" class="back-btn"><i class="fas fa-arrow-left"></i> Kembali Belanja</a>
        
        <h2 class="page-title"><i class="fas fa-box"></i> Pesanan Saya</h2>
        
        <?php if(count($orders) > 0): ?>
            <?php foreach($orders as $order): ?>
                <!-- Count items for this order -->
                <?php
                    $items_count_query = $conn->prepare("SELECT COUNT(*) as count FROM order_items WHERE order_id=?");
                    $items_count_query->bind_param("i", $order['id']);
                    $items_count_query->execute();
                    $items_count_result = $items_count_query->get_result();
                    $items_count = $items_count_result->fetch_assoc()['count'];
                    $items_count_query->close();
                ?>
                
                <div class="order-card">
                    <div class="order-header">
                        <div>
                            <div class="order-number">Pesanan #<?= $order['id'] ?></div>
                            <div class="order-date"><?= date('d M Y, H:i', strtotime($order['created_at'])) ?></div>
                        </div>
                        <div>
                            <span class="status-badge status-<?= strtolower($order['status']) ?>">
                                <?php 
                                    $status_labels = [
                                        'pending' => 'Menunggu Pembayaran',
                                        'processing' => 'Diproses',
                                        'completed' => 'Dikonfirmasi',
                                        'delivered' => 'Terkirim'
                                    ];
                                    echo $status_labels[strtolower($order['status'])] ?? ucfirst($order['status']);
                                ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="order-body">
                        <div class="order-info-grid">
                            <div class="info-box">
                                <div class="info-label">💰 Total</div>
                                <div class="total-amount">Rp<?= number_format($order['total']) ?></div>
                            </div>
                            
                            <div class="info-box">
                                <div class="info-label">📦 Produk</div>
                                <div class="info-value"><?= $items_count ?> Item</div>
                            </div>
                            
                            <div class="info-box">
                                <div class="info-label">🚚 Kurir</div>
                                <div class="info-value"><?= htmlspecialchars($order['shipping_courier']) ?></div>
                            </div>
                            
                            <div class="info-box">
                                <div class="info-label">💳 Pembayaran</div>
                                <div class="info-value" style="font-size: 13px;">
                                    <?php
                                        $icons = ['qris' => '📱', 'transfer' => '🏦', 'cod' => '💵'];
                                        $icon = $icons[strtolower($order['payment_method'])] ?? '💳';
                                        echo $icon . ' ' . ucfirst($order['payment_method']);
                                    ?>
                                </div>
                            </div>
                        </div>

                        <div class="order-items">
                            <div class="item-summary">
                                <i class="fas fa-check-circle"></i> 
                                <span class="item-count"><?= $items_count ?> item</span> 
                                dalam pesanan ini
                            </div>
                        </div>
                        
                        <div class="action-buttons">
                            <a href="../order_detail.php?id=<?= $order['id'] ?>" class="btn-action btn-detail">
                                <i class="fas fa-eye"></i> Detail Pesanan
                            </a>
                            <a href="receipt.php?id=<?= $order['id'] ?>" class="btn-action btn-receipt">
                                <i class="fas fa-receipt"></i> Struk
                            </a>
                            <a href="#" class="btn-action btn-track" onclick="alert('Fitur tracking akan datang segera!')">
                                <i class="fas fa-map"></i> Lacak
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-orders">
                <i class="fas fa-inbox"></i>
                <p><strong>Belum Ada Pesanan</strong></p>
                <p style="font-size: 14px; margin-top: 10px;">Mulai berbelanja sekarang!</p>
                <a href="../index.php" class="back-btn" style="margin-top: 20px;">
                    <i class="fas fa-plus"></i> Belanja Sekarang
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 15px;
        backdrop-filter: blur(10px);
        transition: all 0.3s ease;
    }

    .card:hover {
        transform: translateY(-5px);
        background: rgba(255, 255, 255, 0.08);
    }

    .status-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.8em;
        font-weight: 500;
    }

    .status-pending { background: #ffa726; color: #000; }
    .status-processing { background: #42a5f5; color: white; }
    .status-completed { background: #66bb6a; color: white; }
    .status-cancelled { background: #ef5350; color: white; }

    .order-card {
        margin-bottom: 20px;
    }

    .order-header {
        background: linear-gradient(45deg, #667eea, #764ba2);
        color: white;
        padding: 15px;
        border-radius: 15px 15px 0 0;
    }

    .btn-custom {
        background: linear-gradient(45deg, #667eea, #764ba2);
        border: none;
        border-radius: 10px;
        color: white;
        transition: all 0.3s ease;
    }

    .btn-custom:hover {
        transform: scale(1.05);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        color: white;
    }

    .empty-state {
        text-align: center;
        padding: 50px 20px;
        color: rgba(255, 255, 255, 0.6);
    }

    .empty-state i {
        font-size: 4rem;
        margin-bottom: 20px;
        opacity: 0.5;
    }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark">
  <div class="container">
    <a class="navbar-brand fw-bold" href="../index.php">
      <i class="bi bi-arrow-left"></i> MarketStore
    </a>
    <div class="d-flex align-items-center gap-2">
      <span class="text-light">Pesanan Saya</span>
    </div>
  </div>
</nav>

<div class="container mt-4">
    <h2 class="mb-4"><i class="bi bi-list-check"></i> Riwayat Pesanan</h2>

    <?php if (empty($orders)): ?>
    <div class="empty-state">
        <i class="bi bi-receipt"></i>
        <h4>Belum ada pesanan</h4>
        <p>Anda belum melakukan pemesanan apapun</p>
        <a href="../index.php" class="btn btn-custom">Mulai Belanja</a>
    </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($orders as $order): ?>
            <div class="col-lg-6 mb-4">
                <div class="card order-card">
                    <div class="order-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-1">Order #<?= $order['id'] ?></h5>
                                <small><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></small>
                            </div>
                            <span class="status-badge status-<?= $order['status'] ?>">
                                <?= ucfirst($order['status']) ?>
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <h6>Detail Pengiriman</h6>
                                <p class="mb-1"><strong><?= htmlspecialchars($order['shipping_name']) ?></strong></p>
                                <p class="mb-1 small text-muted">
                                    <?= htmlspecialchars($order['shipping_address']) ?><br>
                                    <?= htmlspecialchars($order['shipping_city']) ?>, <?= htmlspecialchars($order['shipping_province']) ?>
                                </p>
                                <p class="mb-1 small">
                                    <i class="bi bi-truck"></i> <?= strtoupper($order['shipping_courier']) ?>
                                </p>
                            </div>
                            <div class="col-md-4 text-end">
                                <h5 class="text-primary">Rp<?= number_format($order['total']) ?></h5>
                                <small class="text-muted">
                                    Ongkir: Rp<?= number_format($order['shipping_cost']) ?>
                                </small>
                            </div>
                        </div>

                        <hr style="border-color: rgba(255,255,255,0.1);">

                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                Pembayaran: <?= ucfirst($order['payment_method']) ?>
                            </small>
                            <div class="d-flex gap-2">
                                <a href="../receipt.php?order_id=<?= $order['id'] ?>" class="btn btn-outline-light btn-sm">
                                    <i class="bi bi-receipt"></i> Struk
                                </a>
                                <button class="btn btn-custom btn-sm" onclick="viewOrderDetail(<?= $order['id'] ?>)">
                                    <i class="bi bi-eye"></i> Detail
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Order Detail Modal -->
<div class="modal fade" id="orderDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="background: rgba(30,30,30,0.95); color: white;">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-receipt"></i> Detail Pesanan</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="orderDetailContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
function viewOrderDetail(orderId) {
    // This would typically load order details via AJAX
    // For now, we'll show a placeholder
    document.getElementById('orderDetailContent').innerHTML = `
        <div class="text-center">
            <i class="bi bi-receipt display-4 text-primary mb-3"></i>
            <p>Detail pesanan #${orderId} akan dimuat...</p>
            <p class="text-muted">Fitur lengkap akan diimplementasikan</p>
        </div>
    `;
    new bootstrap.Modal(document.getElementById('orderDetailModal')).show();
}
</script>

</body>
</html>