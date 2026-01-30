<?php
session_start();
include 'config/database.php';

// Check if user is authenticated
if (!isset($_SESSION['user'])) {
    header("Location: auth/login.php");
    exit;
}

// Only allow admin to access this
if ($_SESSION['user']['role'] !== 'admin') {
    die("Unauthorized access");
}

echo "<h2>Database Schema Check</h2>";

// Check orders table structure
echo "<h3>Orders Table Structure:</h3>";
$result = $conn->query("DESCRIBE orders");
if ($result) {
    echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Check order_items table structure
echo "<h3>Order Items Table Structure:</h3>";
$result = $conn->query("DESCRIBE order_items");
if ($result) {
    echo "<table border='1'><tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Check recent orders
echo "<h3>Recent Orders (last 5):</h3>";
$result = $conn->query("SELECT id, user_id, total, status, created_at FROM orders ORDER BY created_at DESC LIMIT 5");
if ($result) {
    echo "<table border='1'><tr><th>ID</th><th>User ID</th><th>Total</th><th>Status</th><th>Created</th></tr>";
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
    echo "Error: " . $conn->error;
}

echo "<br><a href='admin/dashboard.php'>Back to Admin Dashboard</a>";
?>
