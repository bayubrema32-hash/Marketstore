<?php
session_start();
include 'config/database.php';

if (!isset($_SESSION['user'])) {
    header("Location: auth/login.php");
    exit;
}

$order_id = (int)($_GET['order_id'] ?? 0);

if (!$order_id) {
    header("Location: user/orders.php");
    exit;
}

$user_id = $_SESSION['user']['id'];

// Get order details with prepared statement
$order_query = $conn->prepare("SELECT * FROM orders WHERE id=? AND user_id=?");
$order_query->bind_param("ii", $order_id, $user_id);
$order_query->execute();
$order_result = $order_query->get_result();
$order = $order_result->fetch_assoc();
$order_query->close();

if (!$order) {
    header("Location: user/orders.php");
    exit;
}

$message = '';
$error = '';

if (isset($_POST['upload'])) {
    $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
    $max_size = 5 * 1024 * 1024; // 5MB

    if (!empty($_FILES['payment_proof']['name'])) {
        $file_type = $_FILES['payment_proof']['type'];
        $file_size = $_FILES['payment_proof']['size'];
        $file_name = $_FILES['payment_proof']['name'];
        $file_tmp = $_FILES['payment_proof']['tmp_name'];

        // Validate file type
        if (!in_array($file_type, $allowed_types)) {
            $error = "Format file tidak didukung. Gunakan JPG, PNG, atau JPEG.";
        }
        // Validate file size
        elseif ($file_size > $max_size) {
            $error = "Ukuran file terlalu besar. Maksimal 5MB.";
        }
        else {
            // Generate unique filename
            $extension = pathinfo($file_name, PATHINFO_EXTENSION);
            $new_filename = 'payment_' . $order_id . '_' . time() . '.' . $extension;

            // Create uploads directory if not exists
            if (!is_dir("uploads/payments")) {
                mkdir("uploads/payments", 0777, true);
            }

            $upload_path = "uploads/payments/" . $new_filename;

            if (move_uploaded_file($file_tmp, $upload_path)) {
                // Update order with payment proof using prepared statement
                $update_stmt = $conn->prepare("UPDATE orders SET payment_proof=?, status='processing' WHERE id=?");
                $status = 'processing';
                $update_stmt->bind_param("si", $upload_path, $order_id);
                if ($update_stmt->execute()) {
                    $message = "✨ Selamat Anda Telah Berbelanja! ✨ Bukti pembayaran berhasil diupload! Pesanan Anda sedang diproses.";
                    $update_stmt->close();
                } else {
                    $error = "Gagal menyimpan data pembayaran.";
                }
            } else {
                $error = "Gagal mengupload file.";
            }
        }
    } else {
        $error = "Silakan pilih file bukti pembayaran.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Bukti Pembayaran - MarketStore</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <style>
    :root {
        --primary-dark: #0a0a0a;
        --secondary-dark: #1a1a1a;
        --accent-gray: #2a2a2a;
        --text-light: #e0e0e0;
        --text-muted: #a0a0a0;
        --border-color: #333333;
        --success-color: #28a745;
        --danger-color: #dc3545;
        --warning-color: #ffc107;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        background: var(--primary-dark);
        color: var(--text-light);
        font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        min-height: 100vh;
    }

    .upload-container {
        max-width: 600px;
        margin: 2rem auto;
        padding: 2rem;
    }

    .upload-card {
        background: var(--secondary-dark);
        border: 1px solid var(--border-color);
        border-radius: 20px;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(0,0,0,0.5);
    }

    .upload-header {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        color: white;
        padding: 2rem;
        text-align: center;
    }

    .upload-header h2 {
        margin-bottom: 0.5rem;
        font-weight: 700;
    }

    .upload-body {
        padding: 2rem;
    }

    .order-info {
        background: var(--accent-gray);
        border-radius: 15px;
        padding: 1.5rem;
        margin-bottom: 2rem;
        border: 1px solid var(--border-color);
    }

    .order-info h5 {
        color: var(--text-light);
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .info-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.5rem;
        padding-bottom: 0.5rem;
        border-bottom: 1px solid var(--border-color);
    }

    .info-row:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }

    .info-label {
        color: var(--text-muted);
        font-weight: 500;
    }

    .info-value {
        color: var(--text-light);
        font-weight: 600;
    }

    .upload-area {
        border: 2px dashed var(--border-color);
        border-radius: 15px;
        padding: 2rem;
        text-align: center;
        transition: all 0.3s ease;
        cursor: pointer;
        background: var(--accent-gray);
        margin-bottom: 1.5rem;
    }

    .upload-area:hover {
        border-color: #2a5298;
        background: rgba(42, 82, 152, 0.1);
    }

    .upload-area.dragover {
        border-color: #2a5298;
        background: rgba(42, 82, 152, 0.2);
        transform: scale(1.02);
    }

    .upload-icon {
        font-size: 3rem;
        color: var(--text-muted);
        margin-bottom: 1rem;
        transition: all 0.3s ease;
    }

    .upload-area:hover .upload-icon {
        color: #2a5298;
        transform: scale(1.1);
    }

    .upload-text {
        color: var(--text-muted);
        margin-bottom: 0.5rem;
    }

    .upload-hint {
        font-size: 0.9rem;
        color: var(--text-muted);
    }

    .file-input {
        display: none;
    }

    .file-preview {
        margin-top: 1rem;
        padding: 1rem;
        background: rgba(42, 82, 152, 0.1);
        border-radius: 10px;
        border: 1px solid #2a5298;
        display: none;
    }

    .file-preview.show {
        display: block;
    }

    .file-info {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .file-icon {
        font-size: 2rem;
        color: #2a5298;
    }

    .file-details h6 {
        color: var(--text-light);
        margin-bottom: 0.25rem;
    }

    .file-details small {
        color: var(--text-muted);
    }

    .btn-upload {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        border: none;
        border-radius: 12px;
        padding: 1rem 2rem;
        color: white;
        font-weight: 600;
        width: 100%;
        transition: all 0.3s ease;
    }

    .btn-upload:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(42, 82, 152, 0.4);
    }

    .btn-upload:disabled {
        background: var(--text-muted);
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    .alert {
        border-radius: 12px;
        border: none;
        margin-bottom: 1.5rem;
    }

    .alert-success {
        background: rgba(40, 167, 69, 0.2);
        color: #28a745;
        border: 1px solid rgba(40, 167, 69, 0.3);
    }

    .alert-danger {
        background: rgba(220, 53, 69, 0.2);
        color: #dc3545;
        border: 1px solid rgba(220, 53, 69, 0.3);
    }

    .btn-back {
        background: var(--accent-gray);
        border: 1px solid var(--border-color);
        border-radius: 12px;
        padding: 0.75rem 1.5rem;
        color: var(--text-light);
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
    }

    .btn-back:hover {
        background: var(--light-gray);
        color: var(--text-light);
        text-decoration: none;
    }

    @media (max-width: 768px) {
        .upload-container {
            margin: 1rem;
            padding: 1rem;
        }

        .upload-area {
            padding: 1.5rem;
        }

        .info-row {
            flex-direction: column;
            gap: 0.25rem;
        }
    }
    </style>
</head>
<body>

<div class="upload-container">
    <div class="upload-card">
        <div class="upload-header">
            <i class="bi bi-cloud-upload display-4 mb-3"></i>
            <h2>Upload Bukti Pembayaran</h2>
            <p class="mb-2">Order #<?= $order['id'] ?></p>
            <p class="mb-0" style="font-size: 1.1rem; font-weight: 700; color: #28a745;">✨ Selamat Anda Telah Berbelanja! ✨</p>
        </div>

        <div class="upload-body">
            <?php if ($message): ?>
            <div class="alert alert-success">
                <i class="bi bi-check-circle"></i> <?= $message ?>
            </div>
            <?php endif; ?>

            <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle"></i> <?= $error ?>
            </div>
            <?php endif; ?>

            <!-- Order Information -->
            <div class="order-info">
                <h5><i class="bi bi-info-circle"></i> Informasi Pesanan</h5>
                <div class="info-row">
                    <span class="info-label">Total Pembayaran:</span>
                    <span class="info-value">Rp<?= number_format($order['total'], 0, ',', '.') ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Metode Pembayaran:</span>
                    <span class="info-value">Transfer Bank</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Status:</span>
                    <span class="info-value"><?= ucfirst($order['status']) ?></span>
                </div>
            </div>

            <!-- Bank Information -->
            <div class="order-info">
                <h5><i class="bi bi-bank"></i> Rekening Tujuan</h5>
                <div class="info-row">
                    <span class="info-label">BCA:</span>
                    <span class="info-value">1234567890 a.n. MarketStore</span>
                </div>
                <div class="info-row">
                    <span class="info-label">Mandiri:</span>
                    <span class="info-value">0987654321 a.n. MarketStore</span>
                </div>
                <div class="info-row">
                    <span class="info-label">BNI:</span>
                    <span class="info-value">1122334455 a.n. MarketStore</span>
                </div>
                <div class="info-row">
                    <span class="info-label">BRI:</span>
                    <span class="info-value">5566778899 a.n. MarketStore</span>
                </div>
            </div>

            <?php if ($order['status'] == 'pending' && !$order['payment_proof']): ?>
            <!-- Upload Form -->
            <form method="POST" enctype="multipart/form-data" id="uploadForm">
                <div class="upload-area" onclick="document.getElementById('payment_proof').click()">
                    <div class="upload-icon">
                        <i class="bi bi-cloud-upload"></i>
                    </div>
                    <div class="upload-text">Klik untuk memilih file atau drag & drop</div>
                    <div class="upload-hint">Format: JPG, PNG, JPEG | Max: 5MB</div>
                </div>

                <input type="file" name="payment_proof" id="payment_proof" class="file-input" accept="image/*" required>

                <div class="file-preview" id="filePreview">
                    <div class="file-info">
                        <div class="file-icon">
                            <i class="bi bi-file-earmark-image"></i>
                        </div>
                        <div class="file-details">
                            <h6 id="fileName">Nama File</h6>
                            <small id="fileSize">Ukuran File</small>
                        </div>
                    </div>
                </div>

                <button type="submit" name="upload" class="btn-upload" id="uploadBtn" disabled>
                    <i class="bi bi-cloud-upload"></i> Upload Bukti Pembayaran
                </button>
            </form>
            <?php elseif ($order['payment_proof']): ?>
            <div class="alert alert-success">
                <i class="bi bi-check-circle"></i> Bukti pembayaran sudah diupload dan sedang diproses.
            </div>
            <div class="text-center">
                <img src="<?= $order['payment_proof'] ?>" alt="Bukti Pembayaran" class="img-fluid rounded" style="max-width: 300px;">
            </div>
            <?php endif; ?>

            <div class="text-center mt-3">
                <a href="user/orders.php" class="btn-back">
                    <i class="bi bi-arrow-left"></i> Kembali ke Pesanan Saya
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
// File upload preview
document.getElementById('payment_proof').addEventListener('change', function(e) {
    const file = e.target.files[0];
    const preview = document.getElementById('filePreview');
    const uploadBtn = document.getElementById('uploadBtn');

    if (file) {
        // Validate file type
        const allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
        if (!allowedTypes.includes(file.type)) {
            alert('Format file tidak didukung. Gunakan JPG, PNG, atau JPEG.');
            e.target.value = '';
            return;
        }

        // Validate file size (5MB)
        if (file.size > 5 * 1024 * 1024) {
            alert('Ukuran file terlalu besar. Maksimal 5MB.');
            e.target.value = '';
            return;
        }

        // Show preview
        document.getElementById('fileName').textContent = file.name;
        document.getElementById('fileSize').textContent = formatFileSize(file.size);
        preview.classList.add('show');
        uploadBtn.disabled = false;
    } else {
        preview.classList.remove('show');
        uploadBtn.disabled = true;
    }
});

// Drag and drop functionality
const uploadArea = document.querySelector('.upload-area');
const fileInput = document.getElementById('payment_proof');

uploadArea.addEventListener('dragover', (e) => {
    e.preventDefault();
    uploadArea.classList.add('dragover');
});

uploadArea.addEventListener('dragleave', () => {
    uploadArea.classList.remove('dragover');
});

uploadArea.addEventListener('drop', (e) => {
    e.preventDefault();
    uploadArea.classList.remove('dragover');

    const files = e.dataTransfer.files;
    if (files.length > 0) {
        fileInput.files = files;
        fileInput.dispatchEvent(new Event('change'));
    }
});

// Format file size
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Form validation
document.getElementById('uploadForm').addEventListener('submit', function(e) {
    const fileInput = document.getElementById('payment_proof');
    if (!fileInput.files[0]) {
        e.preventDefault();
        alert('Silakan pilih file bukti pembayaran terlebih dahulu.');
    }
});
</script>

</body>
</html>