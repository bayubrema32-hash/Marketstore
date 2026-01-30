<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'super_admin') {
    header("Location: ../auth/login.php");
    exit;
}

// STATISTIK
$total_products = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) t FROM products"))['t'];
$total_users    = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) t FROM users WHERE role!='super_admin'"))['t'];
$total_orders   = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) t FROM orders"))['t'];
$total_revenue  = mysqli_fetch_assoc(mysqli_query($conn,"SELECT SUM(total) t FROM orders"))['t'] ?? 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Dashboard Super Admin</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

<style>
body{
    background: linear-gradient(135deg,#667eea,#764ba2);
    min-height:100vh;
    color:#fff;
    font-family:'Segoe UI',sans-serif;
}

.navbar{
    background:rgba(0,0,0,.25);
    backdrop-filter:blur(10px);
}

.card{
    border-radius:20px;
    border:none;
    transition:.4s;
}

.card:hover{
    transform:translateY(-8px);
    box-shadow:0 25px 60px rgba(0,0,0,.3);
}

/* STAT CARD */
.stat-card{
    background:linear-gradient(135deg,#43cea2,#185a9d);
    text-align:center;
    padding:30px;
    color:#fff;
}

.stat-card:nth-child(2){
    background:linear-gradient(135deg,#ff9a9e,#fad0c4);
    color:#000;
}
.stat-card:nth-child(3){
    background:linear-gradient(135deg,#fbc2eb,#a6c1ee);
    color:#000;
}
.stat-card:nth-child(4){
    background:linear-gradient(135deg,#f7971e,#ffd200);
    color:#000;
}

/* MENU CARD (BUKAN HITAM) */
.menu-admin{
    background:linear-gradient(135deg,#89f7fe,#66a6ff);
    color:#000;
}
.menu-produk{
    background:linear-gradient(135deg,#84fab0,#8fd3f4);
    color:#000;
}
.menu-transaksi{
    background:linear-gradient(135deg,#fccb90,#d57eeb);
    color:#000;
}

.menu-card{
    padding:35px;
    text-align:center;
    cursor:pointer;
}

.menu-card i{
    font-size:3rem;
    margin-bottom:10px;
}

.menu-card h5{
    font-weight:700;
}
</style>
</head>

<body>

<nav class="navbar navbar-dark px-4 py-3">
    <span class="navbar-brand fw-bold">
        <i class="bi bi-shield-lock"></i> Super Admin
    </span>
    <span>
        <?= htmlspecialchars($_SESSION['user']['name']) ?>
        <a href="../auth/logout.php" class="btn btn-danger btn-sm ms-2">Logout</a>
    </span>
</nav>

<div class="container mt-5">

<!-- STATISTIK -->
<div class="row g-4 mb-5">
    <div class="col-md-3">
        <div class="card stat-card">
            <i class="bi bi-box-seam fs-1"></i>
            <h3><?= $total_products ?></h3>
            <p>Total Produk</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <i class="bi bi-people fs-1"></i>
            <h3><?= $total_users ?></h3>
            <p>Total User</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <i class="bi bi-receipt fs-1"></i>
            <h3><?= $total_orders ?></h3>
            <p>Total Order</p>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card">
            <i class="bi bi-cash-stack fs-1"></i>
            <h3>Rp<?= number_format($total_revenue) ?></h3>
            <p>Total Revenue</p>
        </div>
    </div>
</div>

<!-- MENU -->
<div class="row g-4">
    <div class="col-md-4" onclick="location.href='manage_admins.php'">
        <div class="card menu-card menu-admin">
            <i class="bi bi-person-badge"></i>
            <h5>Kelola Admin</h5>
            <p>Tambah & atur admin</p>
        </div>
    </div>

    <div class="col-md-4" onclick="location.href='../admin/product.php'">
        <div class="card menu-card menu-produk">
            <i class="bi bi-boxes"></i>
            <h5>Kelola Produk</h5>
            <p>Manajemen katalog</p>
        </div>
    </div>

    <div class="col-md-4" onclick="location.href='all_transactions.php'">
        <div class="card menu-card menu-transaksi">
            <i class="bi bi-graph-up"></i>
            <h5>Semua Transaksi</h5>
            <p>Riwayat & pembayaran</p>
        </div>
    </div>

    <div class="col-md-4" onclick="location.href='orders_debug.php'">
        <div class="card menu-card" style="background:linear-gradient(135deg,#667eea,#764ba2); color:white;">
            <i class="bi bi-bug"></i>
            <h5>Orders Debug</h5>
            <p>Verifikasi semua orders</p>
        </div>
    </div>
</div>

</div>

</body>
</html>
