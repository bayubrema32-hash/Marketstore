<?php
include '../../config/database.php';

$id = (int)$_GET['id'];
$delete_stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
$delete_stmt->bind_param("i", $id);
$delete_stmt->execute();
$delete_stmt->close();

header("Location: index.php");
