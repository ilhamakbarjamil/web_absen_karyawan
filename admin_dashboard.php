<?php
include_once 'auth.php';
checkRole(['admin']); // Hanya admin yang bisa akses

include_once 'templates/header.php';
?>

<h2>Halo, Admin <?php echo htmlspecialchars($_SESSION['nama_karyawan']); ?>!</h2>
<p>Selamat datang di dashboard administrasi sistem absensi.</p>

<div style="margin-top: 30px; text-align: center;">
    <p>Gunakan menu navigasi di atas untuk mengelola karyawan dan melihat laporan absensi.</p>
</div>

<?php
include_once 'templates/footer.php';
$conn->close();
?>