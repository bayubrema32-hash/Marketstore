<?php
session_start();
include '../../config/database.php';

$id = (int)$_GET['id'];
$get_product = $conn->prepare("SELECT * FROM products WHERE id = ?");
$get_product->bind_param("i", $id);
$get_product->execute();
$result = $get_product->get_result();
$data = $result->fetch_assoc();
$get_product->close();

if (isset($_POST['update'])) {
    $name = trim($_POST['name'] ?? '');
    $price = (float)$_POST['price'];
    $stock = (int)$_POST['stock'];
    $description = trim($_POST['description'] ?? '');
    
    $update_stmt = $conn->prepare("UPDATE products SET
        name=?,
        price=?,
        stock=?,
        description=? WHERE id=?"
    );
    $update_stmt->bind_param("sdisi", $name, $price, $stock, $description, $id);
    $update_stmt->execute();
    $update_stmt->close();
    header("Location: index.php");
}
?>

<h2>Edit Produk</h2>

<form method="POST">
    Nama <br>
    <input type="text" name="name" value="<?= $data['name'] ?>"><br><br>

    Harga <br>
    <input type="number" name="price" value="<?= $data['price'] ?>"><br><br>

    Stok <br>
    <input type="number" name="stock" value="<?= $data['stock'] ?>"><br><br>

    Deskripsi <br>
    <textarea name="description"><?= $data['description'] ?></textarea><br><br>

    <button name="update">Update</button>
</form>

<a href="index.php">⬅ Kembali</a>
