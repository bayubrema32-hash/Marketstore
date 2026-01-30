<?php
session_start();
include 'config/database.php';

$id = $_GET['id'];
?>

<h2>Upload Bukti Pembayaran</h2>

<form method="POST" action="payment_process.php" enctype="multipart/form-data">
    <input type="hidden" name="order_id" value="<?= $id ?>">
    <input type="file" name="proof" required><br><br>
    <button type="submit">Upload</button>
</form>
