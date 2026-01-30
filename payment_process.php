<?php
include 'config/database.php';

$order_id = $_POST['order_id'];
$file = $_FILES['proof']['name'];
$tmp = $_FILES['proof']['tmp_name'];

$path = "uploads/payments/".$file;
move_uploaded_file($tmp, $path);

mysqli_query($conn, "
    UPDATE orders 
    SET payment_proof='$file', status='diproses'
    WHERE id='$order_id'
");

header("Location: index.php");
