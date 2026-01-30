<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] != 'super_admin') {
    header("Location: ../auth/login.php");
    exit;
}

// Handle form submissions
if (isset($_POST['add_admin'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $add_stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'admin')");
    $add_stmt->bind_param("sss", $name, $email, $password);
    $add_stmt->execute();
    $add_stmt->close();
    header("Location: manage_admins.php");
    exit;
}

if (isset($_POST['update_admin'])) {
    $id = (int)$_POST['id'];
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');

    if (!empty($_POST['password'])) {
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $update_stmt = $conn->prepare("UPDATE users SET name=?, email=?, password=? WHERE id=?");
        $update_stmt->bind_param("sssi", $name, $email, $password, $id);
        $update_stmt->execute();
        $update_stmt->close();
    } else {
        $update_stmt = $conn->prepare("UPDATE users SET name=?, email=? WHERE id=?");
        $update_stmt->bind_param("ssi", $name, $email, $id);
        $update_stmt->execute();
        $update_stmt->close();
    }
    header("Location: manage_admins.php");
    exit;
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $delete_stmt = $conn->prepare("DELETE FROM users WHERE id=? AND role='admin'");
    $delete_stmt->bind_param("i", $id);
    $delete_stmt->execute();
    $delete_stmt->close();
    header("Location: manage_admins.php");
    exit;
}

// Get all admins
$admins = mysqli_query($conn, "SELECT * FROM users WHERE role='admin' ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Admin - Super Admin</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <style>
    body {
        background: linear-gradient(135deg, #0f0f0f 0%, #1a1a1a 50%, #2d2d2d 100%);
        color: #ffffff;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        min-height: 100vh;
    }

    .navbar {
        background: linear-gradient(135deg, #000000 0%, #333333 100%) !important;
        border-bottom: 1px solid #444;
    }

    .card {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 15px;
        backdrop-filter: blur(10px);
        color: #ffffff;
    }

    .table {
        background: rgba(255, 255, 255, 0.05);
        border-radius: 10px;
        overflow: hidden;
    }

    .table th {
        background: rgba(255, 255, 255, 0.1);
        border: none;
        color: #ffffff;
    }

    .table td {
        border: none;
        color: #ffffff;
    }

    .btn-custom {
        background: linear-gradient(45deg, #667eea, #764ba2);
        border: none;
        border-radius: 10px;
        color: white;
        transition: all 0.3s ease;
    }

    .btn-custom:hover {
        transform: scale(1.05);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        color: white;
    }

    .btn-danger-custom {
        background: linear-gradient(45deg, #ff6b6b, #ee5a52);
        border: none;
        border-radius: 10px;
        color: white;
        transition: all 0.3s ease;
    }

    .btn-danger-custom:hover {
        transform: scale(1.05);
        box-shadow: 0 5px 15px rgba(238, 90, 82, 0.4);
        color: white;
    }

    .form-control {
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 10px;
        color: #ffffff;
    }

    .form-control:focus {
        background: rgba(255, 255, 255, 0.15);
        border-color: #667eea;
        color: #ffffff;
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }

    .modal-content {
        background: rgba(30, 30, 30, 0.95);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: #ffffff;
    }
    </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark">
  <div class="container">
    <a class="navbar-brand fw-bold" href="dashboard.php">
      <i class="bi bi-arrow-left"></i> Kembali ke Dashboard
    </a>
    <div class="d-flex align-items-center gap-2">
      <button class="btn btn-custom btn-sm" data-bs-toggle="modal" data-bs-target="#addAdminModal">
        <i class="bi bi-plus-circle"></i> Tambah Admin
      </button>
    </div>
  </div>
</nav>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-person-badge"></i> Kelola Admin</h2>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Tanggal Dibuat</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no = 1; while($admin = mysqli_fetch_assoc($admins)): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><strong><?= htmlspecialchars($admin['name']) ?></strong></td>
                            <td><?= htmlspecialchars($admin['email']) ?></td>
                            <td><?= date('d/m/Y H:i', strtotime($admin['created_at'] ?? 'now')) ?></td>
                            <td>
                                <button class="btn btn-warning btn-sm me-2" onclick="editAdmin(<?= $admin['id'] ?>, '<?= htmlspecialchars($admin['name']) ?>', '<?= htmlspecialchars($admin['email']) ?>')">
                                    <i class="bi bi-pencil"></i> Edit
                                </button>
                                <a href="?delete=<?= $admin['id'] ?>" class="btn btn-danger-custom btn-sm" onclick="return confirm('Yakin ingin menghapus admin ini?')">
                                    <i class="bi bi-trash"></i> Hapus
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Admin Modal -->
<div class="modal fade" id="addAdminModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-circle"></i> Tambah Admin Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="add_admin" class="btn btn-custom">Tambah Admin</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Admin Modal -->
<div class="modal fade" id="editAdminModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil"></i> Edit Admin</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id" id="edit_id">
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap</label>
                        <input type="text" name="name" id="edit_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" id="edit_email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password Baru (kosongkan jika tidak diubah)</label>
                        <input type="password" name="password" class="form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" name="update_admin" class="btn btn-custom">Update Admin</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
function editAdmin(id, name, email) {
    document.getElementById('edit_id').value = id;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_email').value = email;
    new bootstrap.Modal(document.getElementById('editAdminModal')).show();
}
</script>

</body>
</html>