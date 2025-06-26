<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

include 'config/database.php';
include 'templates/header.php';

$stmt = $pdo->query("SELECT COUNT(*) FROM karyawan");
$total_karyawan = $stmt->fetchColumn();

$tanggal_sekarang = date('Y-m-d');
$stmt = $pdo->prepare("SELECT COUNT(*) FROM absensi WHERE tanggal = ?");
$stmt->execute([$tanggal_sekarang]);
$absensi_hari_ini = $stmt->fetchColumn();
?>

<div class="dashboard-admin">
    <h2>Dashboard Admin</h2>
    <p>Selamat datang, <?= $_SESSION['nama'] ?></p>
    <div class="stats">
        <div class="stat-card">
            <h3>Total Karyawan</h3>
            <p><?= $total_karyawan ?></p>
        </div>
        <div class="stat-card">
            <h3>Absensi Hari Ini</h3>
            <p><?= $absensi_hari_ini ?></p>
        </div>
    </div>
    <div class="actions">
        <a href="kelola_karyawan.php" class="btn">Kelola Karyawan</a>
        <a href="laporan_absensi.php" class="btn">Lihat Laporan Absensi</a>
    </div>
</div>

<?php include 'templates/footer.php'; ?>