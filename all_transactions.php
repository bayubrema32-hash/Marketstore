<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'super_admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Handle status update
if (isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $status = trim($_POST['status'] ?? '');
    $update_status = $conn->prepare("UPDATE orders SET status=? WHERE id=?");
    $update_status->bind_param("si", $status, $order_id);
    $update_status->execute();
    $update_status->close();
    header("Location: all_transactions.php");
    exit;
}

// Get all orders with user details
$query = "SELECT o.*, u.name as user_name, u.email as user_email
          FROM orders o
          LEFT JOIN users u ON o.user_id = u.id
          ORDER BY o.created_at DESC";
$orders = mysqli_query($conn, $query);

// Get order statistics
$total_orders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM orders"))['count'];
$pending_orders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM orders WHERE status='pending' OR status='processing'"))['count'];
$completed_orders = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM orders WHERE status='completed' OR status='delivered'"))['count'];
$total_revenue = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(total) as total FROM orders"))['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Semua Transaksi - Super Admin</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <style>
    body {
        background: linear-gradient(135deg, #0f0f0f 0%, #1a1a1a 50%, #2d2d2d 100%);
        color: #ffffff;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        min-height: 100vh;
    }

    .navbar {
        background: linear-gradient(135deg, #000000 0%, #333333 100%) !important;
        border-bottom: 1px solid #444;
    }

    .card {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 15px;
        backdrop-filter: blur(10px);
        color: #ffffff;
    }

    .stats-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
    }

    .table {
        background: rgba(255, 255, 255, 0.05);
        border-radius: 10px;
        overflow: hidden;
    }

    .table th {
        background: rgba(255, 255, 255, 0.1);
        border: none;
        color: #ffffff;
    }

    .table td {
        border: none;
        color: #ffffff;
    }

    .status-badge {
        padding: 4px 8px;
        border-radius: 20px;
        font-size: 0.8em;
        font-weight: 500;
    }

    .status-pending { background: #ffa726; color: #000; }
    .status-processing { background: #42a5f5; color: white; }
    .status-completed { background: #66bb6a; color: white; }
    .status-cancelled { background: #ef5350; color: white; }

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

    .modal-content {
        background: rgba(30, 30, 30, 0.95);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: #ffffff;
    }

    .order-details {
        background: rgba(255, 255, 255, 0.05);
        border-radius: 10px;
        padding: 15px;
        margin-top: 10px;
    }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark">
  <div class="container">
    <a class="navbar-brand fw-bold" href="dashboard.php">
      <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
    </a>
    <div class="d-flex align-items-center gap-2">
      <span class="text-light">Total Transaksi: <strong><?= number_format($total_orders) ?></strong></span>
    </div>
  </div>
</nav>

<div class="container mt-4">
    <h2 class="mb-4"><i class="bi bi-graph-up"></i> Semua Transaksi</h2>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card stats-card text-white h-100">
                <div class="card-body text-center">
                    <i class="bi bi-receipt display-4 mb-2"></i>
                    <h4 class="card-title"><?= number_format($total_orders) ?></h4>
                    <p class="card-text">Total Order</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stats-card text-white h-100">
                <div class="card-body text-center">
                    <i class="bi bi-clock display-4 mb-2"></i>
                    <h4 class="card-title"><?= number_format($pending_orders) ?></h4>
                    <p class="card-text">Pending</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stats-card text-white h-100">
                <div class="card-body text-center">
                    <i class="bi bi-check-circle display-4 mb-2"></i>
                    <h4 class="card-title"><?= number_format($completed_orders) ?></h4>
                    <p class="card-text">Completed</p>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="card stats-card text-white h-100">
                <div class="card-body text-center">
                    <i class="bi bi-cash-coin display-4 mb-2"></i>
                    <h4 class="card-title">Rp<?= number_format($total_revenue) ?></h4>
                    <p class="card-text">Total Revenue</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Orders Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($order = mysqli_fetch_assoc($orders)): ?>
                        <tr>
                            <td><strong>#<?= $order['id'] ?></strong></td>
                            <td>
                                <div>
                                    <strong><?= htmlspecialchars($order['user_name']) ?></strong><br>
                                    <small class="text-muted"><?= htmlspecialchars($order['user_email']) ?></small>
                                </div>
                            </td>
                            <td><strong>Rp<?= number_format($order['total']) ?></strong></td>
                            <td>
                                <span class="status-badge status-<?= $order['status'] ?>">
                                    <?= ucfirst($order['status']) ?>
                                </span>
                            </td>
                            <td><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                            <td>
                                <button class="btn btn-info btn-sm me-2" onclick="viewOrderDetails(<?= $order['id'] ?>)">
                                    <i class="bi bi-eye"></i> Detail
                                </button>
                                <button class="btn btn-warning btn-sm" onclick="updateStatus(<?= $order['id'] ?>, '<?= $order['status'] ?>')">
                                    <i class="bi bi-pencil"></i> Status
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Order Details Modal -->
<div class="modal fade" id="orderDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-receipt"></i> Detail Order #<span id="orderId"></span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="orderDetailsContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<!-- Update Status Modal -->
<div class="modal fade" id="updateStatusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil"></i> Update Status Order</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="order_id" id="status_order_id">
                    <div class="mb-3">
                        <label class="form-label">Status Baru</label>
                        <select name="status" id="status_select" class="form-select" style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); color: white;">
                            <option value="pending">Pending</option>
                            <option value="processing">Processing</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="update_status" class="btn btn-custom">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
function viewOrderDetails(orderId) {
    // Load order details via AJAX (simplified version)
    fetch(`../admin/order_detail.php?id=${orderId}`)
        .then(response => response.text())
        .then(data => {
            document.getElementById('orderId').textContent = orderId;
            document.getElementById('orderDetailsContent').innerHTML = `
                <div class="text-center">
                    <i class="bi bi-receipt display-4 text-primary mb-3"></i>
                    <p>Detail order akan dimuat dari sistem...</p>
                    <p class="text-muted">Order ID: ${orderId}</p>
                </div>
            `;
            new bootstrap.Modal(document.getElementById('orderDetailsModal')).show();
        });
}

function updateStatus(orderId, currentStatus) {
    document.getElementById('status_order_id').value = orderId;
    document.getElementById('status_select').value = currentStatus;
    new bootstrap.Modal(document.getElementById('updateStatusModal')).show();
}
</script>

</body>
</html>