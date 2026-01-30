<?php
/**
 * Konfigurasi Bank Details
 * Ganti dengan data rekening Bank Anda yang sebenarnya
 */

// Konfigurasi Bank
$BANK_ACCOUNTS = [
    'bca' => [
        'bank_name' => 'Bank Central Asia (BCA)',
        'account_number' => '1234567890',  // GANTI DENGAN NOMOR REKENING BCA ANDA
        'account_holder' => 'Nama Pemilik Toko',  // GANTI DENGAN NAMA ANDA
        'branch' => 'Jakarta Pusat',
        'swift_code' => 'BCAINIDJA',
        'icon' => '🏦'
    ],
    'mandiri' => [
        'bank_name' => 'Bank Mandiri',
        'account_number' => '1400123456789',  // GANTI DENGAN NOMOR REKENING MANDIRI ANDA
        'account_holder' => 'Nama Pemilik Toko',  // GANTI DENGAN NAMA ANDA
        'branch' => 'Jakarta',
        'swift_code' => 'BMRIIDJA',
        'icon' => '🏧'
    ],
    'bni' => [
        'bank_name' => 'Bank Negara Indonesia (BNI)',
        'account_number' => '0123456789',  // GANTI DENGAN NOMOR REKENING BNI ANDA
        'account_holder' => 'Nama Pemilik Toko',  // GANTI DENGAN NAMA ANDA
        'branch' => 'Jakarta',
        'swift_code' => 'BNIAIDJA',
        'icon' => '🏛️'
    ]
];

// Konfigurasi Toko
$STORE_INFO = [
    'name' => 'MarketStore',
    'email' => 'info@marketstore.com',  // GANTI DENGAN EMAIL ANDA
    'phone' => '+62-XXX-XXX-XXXX',  // GANTI DENGAN NO TELEPON ANDA
    'address' => 'Jl. Contoh No. 123, Jakarta',  // GANTI DENGAN ALAMAT ANDA
    'city' => 'Jakarta',
    'province' => 'DKI Jakarta',
    'postal_code' => '12345'
];

// Konfigurasi Metode Pembayaran
$PAYMENT_METHODS = [
    'qris' => [
        'enabled' => true,
        'name' => 'QRIS',
        'description' => 'Scan dengan aplikasi mobile banking',
        'icon' => '📱',
        'instructions' => [
            '1. Buka aplikasi mobile banking Anda (BCA, Mandiri, BNI, dll)',
            '2. Pilih menu "Scan QRIS" atau "Bayar dengan QRIS"',
            '3. Arahkan kamera ke kode QR di layar',
            '4. Konfirmasi pembayaran sebesar Rp[AMOUNT]',
            '5. Tunggu notifikasi pembayaran berhasil'
        ]
    ],
    'transfer' => [
        'enabled' => true,
        'name' => 'Transfer Bank',
        'description' => 'Transfer melalui rekening bank pilihan',
        'icon' => '🏦',
        'instructions' => [
            '1. Pilih salah satu rekening bank di atas',
            '2. Buka aplikasi mobile banking atau ATM Anda',
            '3. Pilih menu Transfer atau Bayar',
            '4. Masukkan nomor rekening tujuan',
            '5. Masukkan jumlah: Rp[AMOUNT]',
            '6. Gunakan kode referensi [REFERENCE] sebagai berita transfer',
            '7. Konfirmasi dan tunggu transaksi selesai',
            '8. Kami akan verifikasi pembayaran dalam 1-2 jam'
        ]
    ],
    'cod' => [
        'enabled' => true,
        'name' => 'Cash On Delivery (COD)',
        'description' => 'Bayar saat barang sampai',
        'icon' => '💵',
        'instructions' => [
            '1. Siapkan uang tunai sebesar Rp[AMOUNT]',
            '2. Tunggu kurir mengantarkan barang ke alamat Anda',
            '3. Verifikasi barang sebelum melakukan pembayaran',
            '4. Bayarkan uang ke kurir',
            '5. Mintalah bukti pembayaran dari kurir'
        ]
    ]
];

// Konfigurasi Ongkir (Shipping Cost)
$SHIPPING_COSTS = [
    'jne' => [
        'name' => 'JNE Regular',
        'cost' => 15000,
        'est_time' => '3-5 hari'
    ],
    'jne_express' => [
        'name' => 'JNE Express',
        'cost' => 25000,
        'est_time' => '1-2 hari'
    ],
    'tiki' => [
        'name' => 'TIKI Regular',
        'cost' => 12000,
        'est_time' => '3-5 hari'
    ],
    'tiki_express' => [
        'name' => 'TIKI Express',
        'cost' => 22000,
        'est_time' => '1-2 hari'
    ],
    'pos' => [
        'name' => 'Pos Reguler',
        'cost' => 10000,
        'est_time' => '5-7 hari'
    ]
];

// Status Color Mapping
$STATUS_COLORS = [
    'pending' => [
        'color' => '#ffc107',
        'bg' => '#fff3cd',
        'label' => 'Menunggu Pembayaran',
        'icon' => '⏳'
    ],
    'processing' => [
        'color' => '#17a2b8',
        'bg' => '#d1ecf1',
        'label' => 'Diproses',
        'icon' => '⚙️'
    ],
    'completed' => [
        'color' => '#28a745',
        'bg' => '#d4edda',
        'label' => 'Dikonfirmasi',
        'icon' => '✓'
    ],
    'delivered' => [
        'color' => '#20c997',
        'bg' => '#c3e6cb',
        'label' => 'Terkirim',
        'icon' => '📦'
    ]
];

// QRIS Configuration (jika menggunakan API real nanti)
$QRIS_CONFIG = [
    'enabled' => true,
    'provider' => 'mock',  // Ganti dengan 'xendit', 'midtrans', dll jika ada API real
    'api_key' => 'test_key',  // Ganti dengan API key real
    'api_secret' => 'test_secret'  // Ganti dengan API secret real
];

// Email Configuration (jika ingin setup email)
$EMAIL_CONFIG = [
    'enabled' => false,  // Ubah ke true jika sudah setup SMTP
    'smtp_host' => 'smtp.gmail.com',
    'smtp_port' => 587,
    'smtp_user' => 'email@gmail.com',
    'smtp_password' => 'app_password',
    'from_email' => 'noreply@marketstore.com',
    'from_name' => 'MarketStore'
];

// Notifikasi Settings
$NOTIFICATION_CONFIG = [
    'send_order_confirmation' => true,
    'send_payment_instructions' => true,
    'send_shipment_notification' => true,
    'send_delivery_confirmation' => true,
    'send_sms' => false,  // Ubah ke true jika ingin SMS
    'admin_email' => 'admin@marketstore.com'
];

// Function untuk mendapatkan bank details
function getBankDetails($bank_code) {
    global $BANK_ACCOUNTS;
    return $BANK_ACCOUNTS[$bank_code] ?? null;
}

// Function untuk mendapatkan payment method info
function getPaymentMethodInfo($method) {
    global $PAYMENT_METHODS;
    return $PAYMENT_METHODS[$method] ?? null;
}

// Function untuk mendapatkan status label dan color
function getStatusInfo($status) {
    global $STATUS_COLORS;
    return $STATUS_COLORS[strtolower($status)] ?? ['color' => '#999', 'bg' => '#f0f0f0', 'label' => 'Unknown'];
}

// Function untuk format instructions dengan amount dan reference
function formatInstructions($method, $amount, $reference = null) {
    global $PAYMENT_METHODS;
    
    $payment_method = $PAYMENT_METHODS[$method] ?? null;
    if (!$payment_method) return [];
    
    $instructions = $payment_method['instructions'];
    
    // Replace placeholders
    $formatted = [];
    foreach ($instructions as $instruction) {
        $instruction = str_replace('[AMOUNT]', number_format($amount), $instruction);
        $instruction = str_replace('[REFERENCE]', $reference ?? 'REF123', $instruction);
        $formatted[] = $instruction;
    }
    
    return $formatted;
}

?>
