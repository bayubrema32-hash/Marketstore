<?php
session_start();
include '../../config/database.php';

if (!isset($_SESSION['user']) || 
   ($_SESSION['user']['role'] != 'admin' && $_SESSION['user']['role'] != 'super_admin')) {
    header("Location: ../../auth/login.php");
}
?>

<h2>Data Produk</h2>

<a href="tambah.php">+ Tambah Produk</a>
<br><br>

<table border="1" cellpadding="10">
<tr>
    <th>No</th>
    <th>Nama</th>
    <th>Harga</th>
    <th>Stok</th>
    <th>Aksi</th>
</tr>

<?php
$no = 1;
$q = mysqli_query($conn, "SELECT * FROM products");
while ($p = mysqli_fetch_assoc($q)) {
?>
<tr>
    <td><?= $no++ ?></td>
    <td><?= $p['name'] ?></td>
    <td>Rp <?= number_format($p['price']) ?></td>
    <td><?= $p['stock'] ?></td>
    <td>
        <a href="edit.php?id=<?= $p['id'] ?>">Edit</a> |
        <a href="hapus.php?id=<?= $p['id'] ?>" onclick="return confirm('Hapus produk?')">Hapus</a>
    </td>
</tr>
<?php } ?>

</table>

<br>
<a href="../dashboard.php">⬅ Kembali Dashboard</a>
