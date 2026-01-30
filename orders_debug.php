<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'super_admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Get comprehensive statistics
$all_orders = $conn->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc()['count'];
$pending = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status IN ('pending', 'processing')")->fetch_assoc()['count'];
$completed = $conn->query("SELECT COUNT(*) as count FROM orders WHERE status IN ('completed', 'delivered')")->fetch_assoc()['count'];
$total_revenue_all = $conn->query("SELECT SUM(total) as sum FROM orders")->fetch_assoc()['sum'] ?? 0;
$total_revenue_completed = $conn->query("SELECT SUM(total) as sum FROM orders WHERE status IN ('completed', 'delivered')")->fetch_assoc()['sum'] ?? 0;

// Get all orders
$orders = $conn->query("
    SELECT o.*, u.name as user_name, u.email,
           COUNT(oi.id) as item_count,
           SUM(oi.qty) as total_qty
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    LEFT JOIN order_items oi ON o.id = oi.order_id
    GROUP BY o.id
    ORDER BY o.created_at DESC
");

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Dashboard - Orders</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
        }
        .stat-number {
            font-size: 2.5em;
            font-weight: bold;
        }
        h1 {
            color: #667eea;
            margin-bottom: 30px;
            border-bottom: 3px solid #667eea;
            padding-bottom: 15px;
        }
        .status-pending {
            background: #ffc107;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.85em;
        }
        .status-processing {
            background: #17a2b8;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.85em;
        }
        .status-completed {
            background: #28a745;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.85em;
        }
        .status-delivered {
            background: #007bff;
            color: white;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.85em;
        }
        table {
            font-size: 0.9em;
        }
        .alert-info {
            background: #e7f3ff;
            border-left: 5px solid #2196F3;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
    </style>
</head>
<body>

<div class="container" style="max-width: 1400px;">
    <h1><i class="bi bi-speedometer2"></i> Super Admin Dashboard - Orders & Revenue</h1>

    <div class="alert-info">
        <strong>ℹ️ Info:</strong> Dashboard ini menampilkan SEMUA orders tanpa filter status.
        Order baru dengan status "pending" atau "processing" akan terlihat di sini.
    </div>

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-number"><?= $all_orders ?></div>
                <div>Total Orders</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-number"><?= $pending ?></div>
                <div>Pending/Processing</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-number"><?= $completed ?></div>
                <div>Completed/Delivered</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-number">Rp<?= number_format($total_revenue_all / 1000000, 1) ?>M</div>
                <div>Total Revenue (ALL)</div>
            </div>
        </div>
    </div>

    <!-- Revenue breakdown -->
    <div class="alert alert-success" role="alert">
        <strong>Revenue Breakdown:</strong><br>
        Total Revenue (ALL orders): Rp<?= number_format($total_revenue_all) ?><br>
        Revenue (Completed only): Rp<?= number_format($total_revenue_completed) ?>
    </div>

    <!-- Orders Table -->
    <h2 style="color: #667eea; margin-top: 40px; margin-bottom: 20px;">
        <i class="bi bi-list-check"></i> All Orders (Total: <?= $all_orders ?>)
    </h2>

    <div class="table-responsive">
        <table class="table table-striped table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Email</th>
                    <th>Items</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Courier</th>
                    <th>Payment</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($orders && $orders->num_rows > 0): ?>
                    <?php while ($order = $orders->fetch_assoc()): ?>
                    <tr>
                        <td><strong>#<?= $order['id'] ?></strong></td>
                        <td><?= htmlspecialchars($order['user_name'] ?? 'Unknown') ?></td>
                        <td><small><?= htmlspecialchars($order['email'] ?? '-') ?></small></td>
                        <td><?= $order['item_count'] ?? 0 ?> items</td>
                        <td>
                            <strong>Rp<?= number_format($order['total']) ?></strong>
                            <?php if ($order['total'] === null): ?>
                                <span style="color: red;"> (NULL!)</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="status-<?= str_replace(' ', '-', strtolower($order['status'])) ?>">
                                <?= ucfirst($order['status']) ?>
                            </span>
                        </td>
                        <td><?= strtoupper($order['shipping_courier'] ?? '-') ?></td>
                        <td><?= ucfirst($order['payment_method'] ?? '-') ?></td>
                        <td><small><?= date('d M Y H:i', strtotime($order['created_at'])) ?></small></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="9" class="text-center">❌ Tidak ada orders</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Navigation -->
    <div style="margin-top: 30px; display: flex; gap: 10px;">
        <a href="dashboard.php" class="btn btn-primary">
            <i class="bi bi-arrow-left"></i> Back to Dashboard
        </a>
        <a href="all_transactions.php" class="btn btn-info">
            <i class="bi bi-list-check"></i> All Transactions
        </a>
        <a href="../debug_checkout.php" class="btn btn-warning">
            <i class="bi bi-bug"></i> Debug Checkout
        </a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
