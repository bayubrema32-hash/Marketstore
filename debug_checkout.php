<?php
session_start();
include 'config/database.php';

// Check if user logged in
if (!isset($_SESSION['user'])) {
    die("❌ TIDAK LOGIN! Silakan login dulu.");
}

echo "<h2>🧪 DEBUG CHECKOUT PROCESS</h2>";

// 1. Check Database Connection
echo "<h3>1. Database Connection</h3>";
if ($conn) {
    echo "✅ Database connected<br>";
} else {
    die("❌ Database connection failed: " . mysqli_connect_error());
}

// 2. Check Orders Table
echo "<h3>2. Orders Table Structure</h3>";
$result = $conn->query("DESCRIBE orders");
if ($result) {
    echo "✅ Orders table exists<br>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>Field</th><th>Type</th><th>Null</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr><td>" . $row['Field'] . "</td><td>" . $row['Type'] . "</td><td>" . $row['Null'] . "</td></tr>";
    }
    echo "</table>";
} else {
    die("❌ Orders table error: " . $conn->error);
}

// 3. Check Cart
echo "<h3>3. Current Cart Session</h3>";
$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) {
    echo "⚠️ Cart kosong! Tambahkan product dulu.<br>";
} else {
    echo "✅ Cart ada " . count($cart) . " item(s)<br>";
    echo "<pre>";
    print_r($cart);
    echo "</pre>";
}

// 4. Check Recent Orders
echo "<h3>4. Recent Orders di Database</h3>";
$result = $conn->query("SELECT id, user_id, total, status, created_at FROM orders ORDER BY id DESC LIMIT 5");
if ($result && $result->num_rows > 0) {
    echo "✅ Ada " . $result->num_rows . " orders terbaru:<br>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>User ID</th><th>Total</th><th>Status</th><th>Created</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr><td>" . $row['id'] . "</td><td>" . $row['user_id'] . "</td><td>" . number_format($row['total']) . "</td><td>" . $row['status'] . "</td><td>" . $row['created_at'] . "</td></tr>";
    }
    echo "</table>";
} else {
    echo "⚠️ Belum ada order di database<br>";
}

// 5. Test INSERT
echo "<h3>5. Test INSERT Order</h3>";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['test_insert'])) {
    $user_id = $_SESSION['user']['id'];
    $total = 250000;
    $status = 'pending';
    $name = 'Test User';
    $phone = '08123456789';
    $address = 'Test Address';
    $province = 'Test Province';
    $city = 'Test City';
    $postal = '12345';
    $courier = 'jne';
    $cost = 15000;
    $payment = 'transfer';
    $notes = 'Test order';
    
    $stmt = $conn->prepare("
        INSERT INTO orders (
            user_id, total, status,
            shipping_name, shipping_phone, shipping_address,
            shipping_province, shipping_city, shipping_postal,
            shipping_courier, shipping_cost, payment_method, order_notes, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    
    if (!$stmt) {
        echo "❌ Prepare failed: " . $conn->error;
    } else {
        $stmt->bind_param(
            "idsssssssdsss",
            $user_id, $total, $status, $name, $phone, $address,
            $province, $city, $postal, $courier, $cost, $payment, $notes
        );
        
        if ($stmt->execute()) {
            echo "✅ Test INSERT SUCCESS! Order ID: " . $stmt->insert_id . "<br>";
            $stmt->close();
        } else {
            echo "❌ Execute failed: " . $stmt->error;
            $stmt->close();
        }
    }
}

// 6. Check checkout_process.php
echo "<h3>6. Checkout Process File</h3>";
if (file_exists('checkout_process.php')) {
    echo "✅ checkout_process.php exists<br>";
} else {
    echo "❌ checkout_process.php NOT FOUND!<br>";
}

// 7. Check logs
echo "<h3>7. Checkout Logs</h3>";
if (file_exists('logs/checkout.log')) {
    echo "✅ Log file exists<br>";
    $log_content = file_get_contents('logs/checkout.log');
    echo "<textarea style='width:100%; height:200px;'>" . htmlspecialchars($log_content) . "</textarea>";
} else {
    echo "⚠️ Log file doesn't exist yet<br>";
}

// 8. Test Form
echo "<h3>8. Test Form</h3>";
?>

<form method="POST">
    <button type="submit" name="test_insert" style="padding:10px 20px; background:#28a745; color:white; border:none; cursor:pointer; border-radius:5px;">
        🧪 Test INSERT Order
    </button>
</form>

<h3>9. Complete Checkout Test</h3>
<form action="checkout_process.php" method="POST">
    <input type="hidden" name="checkout" value="1">
    <input type="hidden" name="shipping_name" value="Test Customer">
    <input type="hidden" name="shipping_phone" value="08123456789">
    <input type="hidden" name="shipping_address" value="Jalan Test 123, Jakarta">
    <input type="hidden" name="shipping_province" value="DKI Jakarta">
    <input type="hidden" name="shipping_city" value="Jakarta Pusat">
    <input type="hidden" name="shipping_postal" value="12190">
    <input type="hidden" name="shipping_courier" value="jne">
    <input type="hidden" name="shipping_cost" value="15000">
    <input type="hidden" name="payment_method" value="transfer">
    <input type="hidden" name="order_notes" value="Test order">
    
    <button type="submit" style="padding:10px 20px; background:#007bff; color:white; border:none; cursor:pointer; border-radius:5px;">
        🛒 Full Checkout Test (Requires cart items!)
    </button>
</form>

<hr>
<a href="index.php">← Back to Home</a>
