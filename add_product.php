<?php
include 'auth.php';
include '../config/database.php';

// ambil kategori
$cat = mysqli_query($conn, "SELECT * FROM categories");

if (isset($_POST['save'])) {

    $name  = trim($_POST['name'] ?? '');
    $price = (float)$_POST['price'];
    $stock = (int)$_POST['stock'];
    $category_id = (int)$_POST['category_id'];
    $description = trim($_POST['description'] ?? '');
    $rating = (float)$_POST['rating'];

    // upload gambar
    $img = basename($_FILES['image']['name']);
    $tmp = $_FILES['image']['tmp_name'];

    if (!is_dir("../uploads")) {
        mkdir("../uploads");
    }

    move_uploaded_file($tmp, "../uploads/$img");

    $insert_product = $conn->prepare("INSERT INTO products
        (name, price, stock, description, rating, image, category_id)
        VALUES
        (?, ?, ?, ?, ?, ?, ?)
    ");
    $insert_product->bind_param("sdisdsdi", $name, $price, $stock, $description, $rating, $img, $category_id);
    $insert_product->execute();
    $insert_product->close();

    header("Location: product.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Tambah Produk</title>
    <!-- BOOTSTRAP CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
    body {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .container {
        background: rgba(255,255,255,0.9);
        border-radius: 15px;
        padding: 30px;
        margin-top: 50px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        backdrop-filter: blur(10px);
        max-width: 600px;
    }

    .form-control {
        border-radius: 10px;
        border: 1px solid #ddd;
        padding: 12px;
        margin-bottom: 15px;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }

    .btn {
        border-radius: 10px;
        padding: 12px 30px;
        transition: all 0.3s ease;
    }

    .btn:hover {
        transform: scale(1.05);
    }
    </style>
</head>
<body>

<div class="container">
<h2 class="mb-4">➕ Tambah Produk Baru</h2>

<form method="POST" enctype="multipart/form-data">
    <div class="mb-3">
        <label class="form-label">Nama Produk</label>
        <input type="text" name="name" class="form-control" placeholder="Masukkan nama produk" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Harga</label>
        <input type="number" name="price" class="form-control" placeholder="Masukkan harga produk" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Stok</label>
        <input type="number" name="stock" class="form-control" placeholder="Masukkan jumlah stok" required>
    </div>

    <div class="mb-3">
        <label class="form-label">Kategori</label>
        <select name="category_id" class="form-control" required>
            <option value="">-- Pilih Kategori --</option>
            <?php while($c = mysqli_fetch_assoc($cat)): ?>
                <option value="<?= $c['id'] ?>">
                    <?= htmlspecialchars($c['name']) ?>
                </option>
            <?php endwhile; ?>
        </select>
    </div>

    <div class="mb-3">
        <label class="form-label">Deskripsi Produk</label>
        <textarea name="description" class="form-control" rows="4" placeholder="Masukkan deskripsi lengkap produk..."></textarea>
        <small class="text-muted">Deskripsikan produk secara detail untuk membantu pelanggan memahami produk ini</small>
    </div>

    <div class="mb-3">
        <label class="form-label">Rating Produk</label>
        <input type="number" name="rating" class="form-control" placeholder="0.0" min="0" max="5" step="0.1" value="0.0" required>
        <small class="text-muted">Rating dari 0.0 sampai 5.0 (contoh: 4.5)</small>
    </div>

    <div class="mb-3">
        <label class="form-label">Gambar Produk</label>
        <input type="file" name="image" class="form-control" accept="image/*" required>
        <small class="text-muted">Upload gambar produk dengan format JPG, PNG, atau GIF</small>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" name="save" class="btn btn-success">💾 Simpan Produk</button>
        <a href="product.php" class="btn btn-secondary">⬅ Kembali</a>
    </div>
</form>

</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
