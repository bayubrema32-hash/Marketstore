<?php
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: auth/login.php");
    exit;
}

$id  = $_POST['id'];
$qty = $_POST['quantity'] ?? $_POST['qty'] ?? 1;
$buy_now = isset($_POST['buy_now']);

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

if (isset($_SESSION['cart'][$id])) {
    $_SESSION['cart'][$id] += $qty;
} else {
    $_SESSION['cart'][$id] = $qty;
}

// Redirect based on action
if ($buy_now) {
    header("Location: checkout.php");
} else {
    header("Location: index.php");
}
exit;
