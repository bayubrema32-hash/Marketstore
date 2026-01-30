<?php
session_start();
include '../config/database.php';

$error = '';

if (isset($_POST['login'])) {
    $email = trim($_POST['email'] ?? '');
    $pass  = $_POST['password'] ?? '';

    // Gunakan prepared statement untuk keamanan
    $q = $conn->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
    $q->bind_param("s", $email);
    $q->execute();
    $result = $q->get_result();
    $u = $result->fetch_assoc();

    if ($u && password_verify($pass, $u['password'])) {
        $_SESSION['user'] = $u;

        if ($u['role'] == 'super_admin') {
            header("Location: ../superadmin/dashboard.php");
        } elseif ($u['role'] == 'admin') {
            header("Location: ../admin/dashboard.php");
        } else {
            header("Location: ../index.php");
        }
        exit;
    } else {
        $error = 'Login gagal! Email atau password salah.';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login MarketStore</title>
    <!-- BOOTSTRAP CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- OPTIONAL ICON -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

    <style>
    body {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }

    .login-container {
        background: rgba(255,255,255,0.9);
        border-radius: 15px;
        padding: 40px;
        box-shadow: 0 15px 35px rgba(0,0,0,0.1);
        backdrop-filter: blur(10px);
        max-width: 400px;
        width: 100%;
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

    .btn-primary {
        background: linear-gradient(45deg, #667eea, #764ba2);
        border: none;
        border-radius: 10px;
        padding: 12px;
        width: 100%;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        transform: scale(1.05);
        box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
    }

    h2 {
        color: #333;
        text-align: center;
        margin-bottom: 30px;
    }

    .register-link {
        text-align: center;
        margin-top: 20px;
    }

    .register-link a {
        color: #667eea;
        text-decoration: none;
    }

    .register-link a:hover {
        text-decoration: underline;
    }
    </style>
</head>
<body>

<div class="login-container">
<h2>🔐 Login MarketStore</h2>

<?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?php echo htmlspecialchars($error); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<form method="POST">
    <input type="email" name="email" class="form-control" placeholder="Email" required>
    <input type="password" name="password" class="form-control" placeholder="Password" required>
    <button type="submit" name="login" class="btn btn-primary">🔐 Login</button>
</form>

<div class="register-link">
    <p>Belum punya akun? <a href="registrasi.php">Daftar di sini</a></p>
</div>

</div>

</body>
</html>
