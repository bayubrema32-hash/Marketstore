<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>STEP 4: Test Checkout Lengkap</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            margin: 20px auto;
        }
        .step-box {
            background: #f8f9fa;
            border-left: 5px solid #667eea;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
        }
        .step-title {
            font-size: 1.3em;
            font-weight: bold;
            color: #667eea;
            margin-bottom: 10px;
        }
        .code-box {
            background: #2c3e50;
            color: #ecf0f1;
            padding: 15px;
            border-radius: 8px;
            overflow-x: auto;
            font-family: monospace;
            font-size: 0.85em;
            margin: 10px 0;
        }
        .success-msg {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 15px;
            border-radius: 8px;
            margin: 10px 0;
        }
        .error-msg {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 15px;
            border-radius: 8px;
            margin: 10px 0;
        }
        .warning-msg {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 8px;
            margin: 10px 0;
        }
        h1 {
            color: #667eea;
            border-bottom: 3px solid #667eea;
            padding-bottom: 15px;
            margin-bottom: 30px;
        }
        .check-item {
            padding: 10px;
            margin: 5px 0;
            border-left: 3px solid #28a745;
            background: #f0f9f0;
        }
        .menu-links {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin: 20px 0;
        }
        .menu-links a {
            padding: 10px 20px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: 0.3s;
        }
        .menu-links a:hover {
            background: #764ba2;
            color: white;
        }
    </style>
</head>
<body>

<div class="container" style="max-width: 1000px;">
    <h1><i class="bi bi-diagram-2"></i> STEP 4: Test Checkout Lengkap</h1>

    <div class="warning-msg">
        <strong>⚠️ PENTING!</strong> Sebelum mulai testing, pastikan Anda sudah:
        <ul style="margin: 10px 0 0 0;">
            <li>Login sebagai customer atau sudah membuat akun customer</li>
            <li>Minimal ada 1 product di database dengan stock > 0</li>
            <li>Database tables (orders, order_items) sudah dibuat dengan benar</li>
        </ul>
    </div>

    <div class="menu-links">
        <a href="debug_orders.php"><i class="bi bi-bug"></i> Debug Database</a>
        <a href="admin/test_order.php"><i class="bi bi-flask"></i> Test Order Admin</a>
        <a href="admin/verify_orders.php"><i class="bi bi-clipboard-check"></i> Verify Orders</a>
        <a href="index.php"><i class="bi bi-shop"></i> Marketplace</a>
    </div>

    <!-- STEP 4.1 -->
    <div class="step-box">
        <div class="step-title">📋 STEP 4.1: Persiapan</div>
        <ol>
            <li><strong>Login sebagai Customer</strong>
                <div class="check-item">
                    <i class="bi bi-check-circle"></i> Akses: <code>auth/login.php</code><br>
                    <i class="bi bi-check-circle"></i> Gunakan akun customer biasa (bukan admin)
                </div>
            </li>
            <li><strong>Cek Products</strong>
                <div class="check-item">
                    <i class="bi bi-check-circle"></i> Lihat products di homepage/index.php<br>
                    <i class="bi bi-check-circle"></i> Pastikan ada product dengan stock > 0
                </div>
            </li>
            <li><strong>Cek Database</strong>
                <div class="check-item">
                    <i class="bi bi-check-circle"></i> Akses: <code>debug_orders.php</code><br>
                    <i class="bi bi-check-circle"></i> Verifikasi table structure benar<br>
                    <i class="bi bi-check-circle"></i> Lihat berapa order yang sudah ada
                </div>
            </li>
        </ol>
    </div>

    <!-- STEP 4.2 -->
    <div class="step-box">
        <div class="step-title">🛒 STEP 4.2: Add to Cart</div>
        <ol>
            <li><strong>Pilih Product di Homepage</strong>
                <div class="check-item">
                    <i class="bi bi-check-circle"></i> Buka: <code>index.php</code><br>
                    <i class="bi bi-check-circle"></i> Klik "Tambah ke Keranjang" di salah satu produk
                </div>
            </li>
            <li><strong>Atur Quantity (Optional)</strong>
                <div class="check-item">
                    <i class="bi bi-check-circle"></i> Bisa ubah quantity di form<br>
                    <i class="bi bi-check-circle"></i> Minimal 1 item, maksimal sesuai stock
                </div>
            </li>
            <li><strong>Lihat Cart</strong>
                <div class="check-item">
                    <i class="bi bi-check-circle"></i> Klik "Keranjang" di navbar<br>
                    <i class="bi bi-check-circle"></i> Verifikasi product sudah ada<br>
                    <i class="bi bi-check-circle"></i> Lihat subtotal + total sudah terhitung
                </div>
            </li>
        </ol>
    </div>

    <!-- STEP 4.3 -->
    <div class="step-box">
        <div class="step-title">💳 STEP 4.3: Checkout Process</div>
        <ol>
            <li><strong>Klik Checkout</strong>
                <div class="check-item">
                    <i class="bi bi-check-circle"></i> Di halaman cart.php, klik tombol "Checkout"<br>
                    <i class="bi bi-check-circle"></i> Akan dibawa ke checkout.php
                </div>
            </li>
            <li><strong>Isi Form Lengkap</strong>
                <div class="check-item">
                    <i class="bi bi-check-circle"></i> <strong>Nama Lengkap:</strong> Minimal 3 karakter (contoh: "John Doe")<br>
                    <i class="bi bi-check-circle"></i> <strong>Nomor Telepon:</strong> Minimal 10 digit (contoh: "08123456789")<br>
                    <i class="bi bi-check-circle"></i> <strong>Alamat Lengkap:</strong> Minimal 10 karakter (contoh: "Jalan Merdeka No 123, Kota")<br>
                    <i class="bi bi-check-circle"></i> <strong>Kota/Provinsi:</strong> Pilih dari dropdown atau isi manual<br>
                    <i class="bi bi-check-circle"></i> <strong>Kode Pos:</strong> 5 digit (contoh: "12190")
                </div>
            </li>
            <li><strong>Pilih Kurir Pengiriman</strong>
                <div class="check-item">
                    <i class="bi bi-check-circle"></i> Opsi: JNE, J&T, SiCepat, AnterAja<br>
                    <i class="bi bi-check-circle"></i> Setiap kurir punya biaya berbeda<br>
                    <i class="bi bi-check-circle"></i> Total akan update otomatis (subtotal + ongkir)
                </div>
            </li>
            <li><strong>Pilih Metode Pembayaran</strong>
                <div class="check-item">
                    <i class="bi bi-check-circle"></i> <strong>Transfer Bank:</strong> Tunjukkan nomor rekening<br>
                    <i class="bi bi-check-circle"></i> <strong>QRIS:</strong> Tampilkan QR code<br>
                    <i class="bi bi-check-circle"></i> <strong>COD (Cash on Delivery):</strong> Bayar saat diterima
                </div>
            </li>
            <li><strong>Review Order</strong>
                <div class="check-item">
                    <i class="bi bi-check-circle"></i> Di sisi kanan ada "Order Summary"<br>
                    <i class="bi bi-check-circle"></i> Cek: Subtotal + Ongkir = Total<br>
                    <i class="bi bi-check-circle"></i> Contoh: Rp200.000 + Rp15.000 = Rp215.000
                </div>
            </li>
            <li><strong>Setujui Syarat & Ketentuan</strong>
                <div class="check-item">
                    <i class="bi bi-check-circle"></i> Centang checkbox "Saya setuju dengan syarat dan ketentuan"
                </div>
            </li>
            <li><strong>Klik "Buat Pesanan"</strong>
                <div class="check-item">
                    <i class="bi bi-check-circle"></i> Akan muncul confirmation dialog<br>
                    <i class="bi bi-check-circle"></i> Verifikasi data sekali lagi<br>
                    <i class="bi bi-check-circle"></i> Klik OK untuk confirm
                </div>
            </li>
        </ol>
    </div>

    <!-- STEP 4.4 -->
    <div class="step-box">
        <div class="step-title">✅ STEP 4.4: Verify Order Created</div>
        <ol>
            <li><strong>Cek Success Page</strong>
                <div class="check-item">
                    <i class="bi bi-check-circle"></i> Jika berhasil, akan redirect ke success.php<br>
                    <i class="bi bi-check-circle"></i> Muncul pesan: "✨ Selamat Anda Telah Berbelanja!"<br>
                    <i class="bi bi-check-circle"></i> Tampilkan Order ID: #[nomor]<br>
                    <i class="bi bi-check-circle"></i> Tampilkan Detail Pesanan dengan items
                </div>
            </li>
            <li><strong>Lihat Struk Pembayaran</strong>
                <div class="check-item">
                    <i class="bi bi-check-circle"></i> Klik tombol "Lihat Struk Pembayaran"<br>
                    <i class="bi bi-check-circle"></i> Lihat detail lengkap di receipt.php
                </div>
            </li>
            <li><strong>Lihat Pesanan Saya</strong>
                <div class="check-item">
                    <i class="bi bi-check-circle"></i> Klik tombol "Lihat Pesanan Saya"<br>
                    <i class="bi bi-check-circle"></i> Lihat order muncul di user/orders.php
                </div>
            </li>
        </ol>
    </div>

    <!-- STEP 4.5 -->
    <div class="step-box">
        <div class="step-title">🔍 STEP 4.5: Database Verification</div>
        <ol>
            <li><strong>Akses Debug Page</strong>
                <div class="check-item">
                    <i class="bi bi-check-circle"></i> Buka: <code>debug_orders.php</code>
                </div>
            </li>
            <li><strong>Cek Recent Orders Table</strong>
                <div class="check-item">
                    <i class="bi bi-check-circle"></i> Lihat tabel "Recent Orders"<br>
                    <i class="bi bi-check-circle"></i> Order ID baru harus muncul di sini<br>
                    <i class="bi bi-check-circle"></i> Total harus bukan NULL dan > 0
                </div>
            </li>
            <li><strong>Gunakan SQL untuk Verify</strong>
                <div class="check-item">
                    <i class="bi bi-check-circle"></i> Di database tool (PHPMyAdmin), jalankan query:
                </div>
                <div class="code-box">
SELECT id, user_id, total, shipping_cost, 
       status, payment_method, created_at 
FROM orders 
ORDER BY id DESC LIMIT 1;
                </div>
                <div class="check-item" style="margin-top: 10px;">
                    <i class="bi bi-check-circle"></i> Pastikan hasil menampilkan order terbaru<br>
                    <i class="bi bi-check-circle"></i> Kolom <code>total</code> HARUS terisi (bukan NULL)
                </div>
            </li>
            <li><strong>Verifikasi Order Items</strong>
                <div class="check-item">
                    <i class="bi bi-check-circle"></i> Jalankan query:
                </div>
                <div class="code-box">
SELECT oi.id, oi.order_id, oi.product_id, 
       oi.price, oi.qty, p.name 
FROM order_items oi
JOIN products p ON oi.product_id = p.id
WHERE oi.order_id = [order_id_dari_query_diatas];
                </div>
                <div class="check-item" style="margin-top: 10px;">
                    <i class="bi bi-check-circle"></i> Harus muncul items yang dibeli<br>
                    <i class="bi bi-check-circle"></i> Price dan qty harus correct
                </div>
            </li>
        </ol>
    </div>

    <!-- STEP 4.6 -->
    <div class="step-box">
        <div class="step-title">🔧 STEP 4.6: Troubleshooting</div>
        <div class="error-msg">
            <strong>❌ Jika Order Tidak Masuk:</strong>
        </div>
        <ol>
            <li><strong>Cek Error Message</strong>
                <div class="check-item">
                    <i class="bi bi-check-circle"></i> Di checkout.php akan muncul error message<br>
                    <i class="bi bi-check-circle"></i> Catat error message tersebut
                </div>
            </li>
            <li><strong>Cek Server Logs</strong>
                <div class="code-box">
<!-- Di localhost XAMPP: -->
C:\xampp\apache\logs\error.log
C:\xampp\php\php_errors.log

<!-- Atau buka debug_orders.php dan lihat "Recent Orders" -->
                </div>
            </li>
            <li><strong>Cek Database Connection</strong>
                <div class="check-item">
                    <i class="bi bi-check-circle"></i> Buka: <code>debug_orders.php</code><br>
                    <i class="bi bi-check-circle"></i> Harus ada pesan "✅ Database connected successfully"<br>
                    <i class="bi bi-check-circle"></i> Jika ada error, cek database name dan credentials di config/database.php
                </div>
            </li>
            <li><strong>Cek Table Structure</strong>
                <div class="check-item">
                    <i class="bi bi-check-circle"></i> Di debug_orders.php lihat table structure<br>
                    <i class="bi bi-check-circle"></i> Column "total" harus type DECIMAL atau FLOAT<br>
                    <i class="bi bi-check-circle"></i> Jika ada yang salah, gunakan setup_orders_table.sql
                </div>
            </li>
            <li><strong>Test Insert Manual</strong>
                <div class="check-item">
                    <i class="bi bi-check-circle"></i> Di debug_orders.php klik "Test Insert Order"<br>
                    <i class="bi bi-check-circle"></i> Jika berhasil, prepared statement OK<br>
                    <i class="bi bi-check-circle"></i> Jika gagal, check database constraints (foreign key, etc)
                </div>
            </li>
        </ol>
    </div>

    <!-- STEP 4.7 -->
    <div class="step-box">
        <div class="step-title">📝 STEP 4.7: Monitoring</div>
        <ol>
            <li><strong>Lihat Logs</strong>
                <div class="check-item">
                    <i class="bi bi-check-circle"></i> File: <code>logs/checkout.log</code><br>
                    <i class="bi bi-check-circle"></i> Berisi log setiap order yang dibuat/gagal<br>
                    <i class="bi bi-check-circle"></i> Format: [timestamp] | ✅ Order #[id] | User: [id] | Total: [amount]
                </div>
            </li>
            <li><strong>Monitor Admin Panel</strong>
                <div class="check-item">
                    <i class="bi bi-check-circle"></i> Login sebagai admin<br>
                    <i class="bi bi-check-circle"></i> Buka admin/verify_orders.php<br>
                    <i class="bi bi-check-circle"></i> Lihat stats dan order list real-time
                </div>
            </li>
            <li><strong>Test Multiple Orders</strong>
                <div class="check-item">
                    <i class="bi bi-check-circle"></i> Ulangi STEP 4.2-4.5 dengan berbagai kombinasi<br>
                    <i class="bi bi-check-circle"></i> Test dengan kurir berbeda (ongkir berbeda)<br>
                    <i class="bi bi-check-circle"></i> Test dengan payment method berbeda<br>
                    <i class="bi bi-check-circle"></i> Pastikan semua tersimpan dengan benar
                </div>
            </li>
        </ol>
    </div>

    <!-- Summary -->
    <div class="success-msg">
        <h5><i class="bi bi-check-circle"></i> Checklist Sukses</h5>
        <ul style="margin: 10px 0;">
            <li>✅ Order muncul di success.php dengan Order ID</li>
            <li>✅ Order muncul di database (debug_orders.php)</li>
            <li>✅ Kolom <code>total</code> terisi (bukan NULL)</li>
            <li>✅ Order items tersimpan dengan harga dan qty correct</li>
            <li>✅ Stock produk berkurang sesuai qty yang dibeli</li>
            <li>✅ Order muncul di user/orders.php (customer bisa lihat)</li>
            <li>✅ Order muncul di admin/orders.php (admin bisa lihat)</li>
            <li>✅ Logs tersimpan di logs/checkout.log</li>
        </ul>
    </div>

    <div style="margin-top: 40px; text-align: center; padding: 20px; background: #f8f9fa; border-radius: 8px;">
        <h5>Butuh Bantuan?</h5>
        <p>Jika ada masalah, cek:</p>
        <div class="menu-links" style="justify-content: center;">
            <a href="debug_orders.php"><i class="bi bi-bug"></i> Debug Database</a>
            <a href="logs/checkout.log" target="_blank"><i class="bi bi-file-earmark-text"></i> View Logs</a>
            <a href="admin/verify_orders.php"><i class="bi bi-clipboard-check"></i> Verify Orders</a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
