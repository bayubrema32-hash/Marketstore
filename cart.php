<?php
session_start();
include 'config/database.php';

if (!isset($_SESSION['user'])) {
    header("Location: auth/login.php");
    exit;
}

$cart = $_SESSION['cart'] ?? [];
?>

<!DOCTYPE html>
<html>
<head>
    <title>Keranjang Belanja</title>
    <!-- BOOTSTRAP CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- OPTIONAL ICON -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <style>
    body {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .container {
        background: rgba(255,255,255,0.9);
        border-radius: 15px;
        padding: 30px;
        margin-top: 50px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        backdrop-filter: blur(10px);
    }

    table {
        background: rgba(255,255,255,0.8);
        border-radius: 10px;
        overflow: hidden;
    }

    th {
        background: linear-gradient(45deg, #667eea, #764ba2);
        color: white;
    }

    .btn {
        transition: all 0.3s ease;
    }

    .btn:hover {
        transform: scale(1.05);
    }
    </style>
</head>
<body>

<div class="container">
<h1>🛒 Keranjang Belanja</h1>

<a href="index.php" class="btn btn-secondary mb-3">⬅ Kembali ke Market</a>
<hr>

<?php if (empty($cart)): ?>
    <p>Keranjang masih kosong.</p>
<?php else: ?>

<table class="table table-striped">
<thead>
<tr>
    <th>Produk</th>
    <th>Harga</th>
    <th>Qty</th>
    <th>Subtotal</th>
</tr>
</thead>
<tbody>

<?php
$total = 0;
foreach ($cart as $id => $qty):
    $id = (int)$id;
    $qty = (int)$qty;
    $p = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $p->bind_param("i", $id);
    $p->execute();
    $result = $p->get_result();
    $product = $result->fetch_assoc();
    $p->close();
    
    if ($product) {
        $subtotal = $product['price'] * $qty;
        $total += $subtotal;
?>
<tr>
    <td><?= htmlspecialchars($product['name']) ?></td>
    <td>Rp<?= number_format($product['price']) ?></td>
    <td><?= $qty ?></td>
    <td>Rp<?= number_format($subtotal) ?></td>
</tr>
<?php } endforeach; ?>

<tr class="table-info">
    <td colspan="3"><b>Total</b></td>
    <td><b>Rp<?= number_format($total) ?></b></td>
</tr>
</tbody>
</table>

<br>
<a href="checkout.php" class="btn btn-success">🧾 Checkout</a>

<?php endif; ?>

</div>

</body>
</html>
