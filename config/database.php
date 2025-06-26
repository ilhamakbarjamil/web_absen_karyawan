<?php
// Konfigurasi Database
define('DB_HOST', 'localhost'); // Sesuaikan jika host database Anda berbeda
define('DB_USER', 'adminphp');     // Username database PhpMyAdmin Anda
define('DB_PASS', 'passwordku123');         // Password database PhpMyAdmin Anda (kosong jika default XAMPP/WAMP)
define('DB_NAME', 'db_absensi_karyawan'); // Nama database yang Anda buat

// Koneksi ke Database
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Periksa Koneksi
if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}
?>