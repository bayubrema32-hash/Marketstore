<?php
session_start();
include '../../config/database.php';

if (!isset($_SESSION['user'])) {
    header("Location: ../../auth/login.php");
}

if (isset($_POST['simpan'])) {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $desc = $_POST['description'];

    mysqli_query($conn, "INSERT INTO products VALUES (
        null,'$name','$price','$stock','$desc'
    )");

    header("Location: index.php");
}
?>

<h2>Tambah Produk</h2>

<form method="POST">
    Nama Produk <br>
    <input type="text" name="name" required><br><br>

    Harga <br>
    <input type="number" name="price" required><br><br>

    Stok <br>
    <input type="number" name="stock" required><br><br>

    Deskripsi <br>
    <textarea name="description"></textarea><br><br>

    <button name="simpan">Simpan</button>
</form>

<a href="index.php">⬅ Kembali</a>
