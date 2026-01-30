<?php
session_start();
include 'config/database.php';

if (!isset($_SESSION['user'])) {
    header("Location: auth/login.php");
    exit;
}

if (!isset($_POST['checkout'])) {
    header("Location: checkout.php");
    exit;
}

$cart = $_SESSION['cart'] ?? [];
$user_id = $_SESSION['user']['id'];

if (empty($cart)) {
    header("Location: cart.php");
    exit;
}

// Validasi input
$shipping_data = [
    'name' => trim($_POST['shipping_name'] ?? ''),
    'phone' => trim($_POST['shipping_phone'] ?? ''),
    'address' => trim($_POST['shipping_address'] ?? ''),
    'province' => trim($_POST['shipping_province'] ?? ''),
    'city' => trim($_POST['shipping_city'] ?? ''),
    'postal' => trim($_POST['shipping_postal'] ?? ''),
    'courier' => trim($_POST['shipping_courier'] ?? ''),
    'cost' => (float)($_POST['shipping_cost'] ?? 0),
    'payment_method' => trim($_POST['payment_method'] ?? ''),
    'notes' => trim($_POST['order_notes'] ?? '')
];

// Validasi data yang diperlukan
if (empty($shipping_data['name']) || strlen($shipping_data['name']) < 3) {
    $_SESSION['checkout_error'] = 'Nama lengkap tidak valid!';
    header("Location: checkout.php");
    exit;
}

if (empty($shipping_data['phone']) || strlen($shipping_data['phone']) < 10) {
    $_SESSION['checkout_error'] = 'Nomor telepon tidak valid!';
    header("Location: checkout.php");
    exit;
}

if (empty($shipping_data['address']) || strlen($shipping_data['address']) < 10) {
    $_SESSION['checkout_error'] = 'Alamat tidak valid!';
    header("Location: checkout.php");
    exit;
}

if (empty($shipping_data['province']) || empty($shipping_data['city']) || empty($shipping_data['postal'])) {
    $_SESSION['checkout_error'] = 'Semua field alamat harus diisi!';
    header("Location: checkout.php");
    exit;
}

if (empty($shipping_data['courier'])) {
    $_SESSION['checkout_error'] = 'Pilih kurir pengiriman!';
    header("Location: checkout.php");
    exit;
}

if (empty($shipping_data['payment_method'])) {
    $_SESSION['checkout_error'] = 'Pilih metode pembayaran!';
    header("Location: checkout.php");
    exit;
}

// Calculate totals
$subtotal = 0;
foreach ($cart as $id => $qty) {
    $id = (int)$id;
    $qty = (int)$qty;
    $p = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $p->bind_param("i", $id);
    $p->execute();
    $result = $p->get_result();
    $product = $result->fetch_assoc();
    $p->close();
    if ($product) {
        $subtotal += $product['price'] * $qty;
    }
}

$shipping_cost = $shipping_data['cost'];
$total = $subtotal + $shipping_cost;

// Validasi total
if ($total <= 0) {
    $_SESSION['checkout_error'] = 'Total pesanan tidak valid!';
    header("Location: checkout.php");
    exit;
}

// Set initial status based on payment method
$initial_status = 'pending';
if ($shipping_data['payment_method'] == 'qris' || $shipping_data['payment_method'] == 'cod') {
    $initial_status = 'processing';
}

// Insert order with prepared statement
$insert_order = $conn->prepare("
    INSERT INTO orders (
        user_id, total, status,
        shipping_name, shipping_phone, shipping_address,
        shipping_province, shipping_city, shipping_postal,
        shipping_courier, shipping_cost, payment_method, order_notes, created_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
");

if (!$insert_order) {
    $error_detail = "Prepare failed: " . $conn->error . "\nQuery: INSERT INTO orders (...)";
    error_log($error_detail);
    $_SESSION['checkout_error'] = 'Database error: ' . $conn->error;
    header("Location: checkout.php");
    exit;
}

// Log before binding
error_log("Checkout: About to bind params. User: $user_id, Total: $total, Name: " . $shipping_data['name']);

// Bind parameters
$bind_result = $insert_order->bind_param(
    "idsssssssdsss",
    $user_id,                           // i
    $total,                             // d
    $initial_status,                    // s
    $shipping_data['name'],             // s
    $shipping_data['phone'],            // s
    $shipping_data['address'],          // s
    $shipping_data['province'],         // s
    $shipping_data['city'],             // s
    $shipping_data['postal'],           // s
    $shipping_data['courier'],          // s
    $shipping_data['cost'],             // d
    $shipping_data['payment_method'],   // s
    $shipping_data['notes']             // s
);

if (!$bind_result) {
    $error_detail = "Bind param failed: " . $insert_order->error;
    error_log($error_detail);
    $_SESSION['checkout_error'] = 'Parameter binding error: ' . $insert_order->error;
    $insert_order->close();
    header("Location: checkout.php");
    exit;
}

error_log("Checkout: Bind params success, executing INSERT...");

// Execute insert
$execute_result = $insert_order->execute();

if (!$execute_result) {
    $error_detail = "Execute failed: " . $insert_order->error;
    error_log($error_detail);
    $_SESSION['checkout_error'] = 'Gagal membuat pesanan: ' . $insert_order->error;
    $insert_order->close();
    header("Location: checkout.php");
    exit;
}

$order_id = $insert_order->insert_id;
$insert_order->close();

if (!$order_id) {
    $_SESSION['checkout_error'] = 'Order created but no ID returned!';
    error_log("ERROR: Order inserted but insert_id is 0");
    header("Location: checkout.php");
    exit;
}

error_log("✅ Checkout: Order #$order_id created successfully. Total: $total");

// Log order creation
$log_message = date('Y-m-d H:i:s') . " | ✅ Order #$order_id | User: $user_id | Total: $total | Items: " . count($cart) . "\n";
file_put_contents('logs/checkout.log', $log_message, FILE_APPEND);

// Insert order items and update stock
foreach ($cart as $id => $qty) {
    $id = (int)$id;
    $qty = (int)$qty;
    $p = $conn->prepare("SELECT * FROM products WHERE id = ?");
    if (!$p) {
        die("Prepare product select failed: " . $conn->error);
    }
    $p->bind_param("i", $id);
    $p->execute();
    $result = $p->get_result();
    $product = $result->fetch_assoc();
    $p->close();

    if ($product) {
        // Insert order item with prepared statement
        $insert_item = $conn->prepare("
            INSERT INTO order_items (order_id, product_id, price, qty)
            VALUES (?, ?, ?, ?)
        ");
        if (!$insert_item) {
            $_SESSION['checkout_error'] = 'Gagal menyimpan item pesanan: ' . $conn->error;
            header("Location: checkout.php");
            exit;
        }
        $price = (float)$product['price'];
        $insert_item->bind_param("idii", $order_id, $id, $price, $qty);
        if (!$insert_item->execute()) {
            $item_error = "Gagal execute item pesanan: " . $insert_item->error;
            error_log($item_error);
            $_SESSION['checkout_error'] = $item_error;
            header("Location: checkout.php");
            exit;
        }
        $insert_item->close();
        error_log("✅ Order Item added: Product #$id | Price: $price | Qty: $qty");

        // Update stock with prepared statement
        $new_stock = $product['stock'] - $qty;
        $update_stock = $conn->prepare("UPDATE products SET stock = ? WHERE id = ?");
        if (!$update_stock) {
            $_SESSION['checkout_error'] = 'Gagal update stok: ' . $conn->error;
            header("Location: checkout.php");
            exit;
        }
        $update_stock->bind_param("ii", $new_stock, $id);
        if (!$update_stock->execute()) {
            $_SESSION['checkout_error'] = 'Gagal update stok produk: ' . $update_stock->error;
            header("Location: checkout.php");
            exit;
        }
        $update_stock->close();
    }
}

// Clear cart
unset($_SESSION['cart']);

// Store order ID for payment gateway
$_SESSION['last_order_id'] = $order_id;
$_SESSION['checkout_success'] = 'Pesanan Anda berhasil dibuat! Order ID: #' . $order_id;

// Redirect to payment gateway (STEP 4)
header("Location: payment_gateway.php");
exit;
?>
