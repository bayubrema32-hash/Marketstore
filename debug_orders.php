<?php
session_start();
include 'config/database.php';

// Check database connection
if (!$conn) {
    die("❌ Database connection FAILED: " . mysqli_connect_error());
}

echo "✅ Database connected successfully<br><br>";

// Check orders table structure
echo "<h3>📋 Table: ORDERS</h3>";
$result = $conn->query("DESCRIBE orders");
if (!$result) {
    die("❌ Orders table tidak ada! Error: " . $conn->error);
}

echo "<table border='1' cellpadding='10'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['Field'] . "</td>";
    echo "<td>" . $row['Type'] . "</td>";
    echo "<td>" . $row['Null'] . "</td>";
    echo "<td>" . $row['Key'] . "</td>";
    echo "<td>" . ($row['Default'] ?? 'NULL') . "</td>";
    echo "</tr>";
}
echo "</table>";

// Check order_items table structure
echo "<h3>📦 Table: ORDER_ITEMS</h3>";
$result = $conn->query("DESCRIBE order_items");
if (!$result) {
    die("❌ Order_items table tidak ada! Error: " . $conn->error);
}

echo "<table border='1' cellpadding='10'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['Field'] . "</td>";
    echo "<td>" . $row['Type'] . "</td>";
    echo "<td>" . $row['Null'] . "</td>";
    echo "<td>" . $row['Key'] . "</td>";
    echo "<td>" . ($row['Default'] ?? 'NULL') . "</td>";
    echo "</tr>";
}
echo "</table>";

// Count total orders
echo "<h3>📊 Statistics</h3>";
$result = $conn->query("SELECT COUNT(*) as total FROM orders");
$row = $result->fetch_assoc();
echo "Total orders: <strong>" . $row['total'] . "</strong><br>";

// Show recent 5 orders
echo "<h3>📈 Recent Orders</h3>";
$result = $conn->query("SELECT id, user_id, total, status, created_at FROM orders ORDER BY id DESC LIMIT 5");
if ($result->num_rows > 0) {
    echo "<table border='1' cellpadding='10'>";
    echo "<tr><th>ID</th><th>User ID</th><th>Total</th><th>Status</th><th>Created</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . $row['user_id'] . "</td>";
        echo "<td>" . $row['total'] . "</td>";
        echo "<td>" . $row['status'] . "</td>";
        echo "<td>" . $row['created_at'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "❌ Belum ada order sama sekali<br>";
}

// Test INSERT
echo "<h3>🧪 Test INSERT Order</h3>";
if (isset($_SESSION['user'])) {
    echo "User ID: " . $_SESSION['user']['id'] . "<br>";
    echo "User Name: " . $_SESSION['user']['name'] . "<br>";
    
    if (isset($_POST['test_insert'])) {
        $user_id = $_SESSION['user']['id'];
        $total = 250000;
        $status = 'pending';
        $name = 'Test User';
        $phone = '08123456789';
        $address = 'Test Address 123';
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
            echo "❌ Prepare failed: " . $conn->error . "<br>";
        } else {
            echo "✅ Prepare success<br>";
            
            $stmt->bind_param(
                "idsssssssdsss",
                $user_id, $total, $status, $name, $phone, $address,
                $province, $city, $postal, $courier, $cost, $payment, $notes
            );
            
            echo "✅ Bind param success<br>";
            
            if ($stmt->execute()) {
                echo "✅ Execute success!<br>";
                echo "Insert ID: " . $stmt->insert_id . "<br>";
                $stmt->close();
                echo "<div style='background: #d4edda; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
                echo "✅ Order inserted successfully! Check orders table<br>";
                echo "</div>";
            } else {
                echo "❌ Execute failed: " . $stmt->error . "<br>";
                $stmt->close();
            }
        }
    }
} else {
    echo "⚠️ Please login first<br>";
}

?>

<form method="POST" style="margin: 20px 0;">
    <button type="submit" name="test_insert" style="padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer;">
        🧪 Test Insert Order
    </button>
</form>

<a href="admin/dashboard.php" style="padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; display: inline-block;">
    ← Back to Dashboard
</a>
