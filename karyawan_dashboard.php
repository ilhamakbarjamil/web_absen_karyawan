<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'karyawan') {
    header('Location: index.php');
    exit();
}

include 'config/database.php';
include 'templates/header.php';

$tanggal_sekarang = date('Y-m-d');
$karyawan_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT * FROM absensi WHERE karyawan_id = ? AND tanggal = ?");
$stmt->execute([$karyawan_id, $tanggal_sekarang]);
$absensi_hari_ini = $stmt->fetch();

$disabled_masuk = false;
$disabled_keluar = true;
$sudah_absen_keluar = false;

if ($absensi_hari_ini) {
    $disabled_masuk = true;
    if ($absensi_hari_ini['jam_keluar']) {
        $disabled_keluar = true;
        $sudah_absen_keluar = true;
    } else {
        $disabled_keluar = false;
    }
}
?>

<div class="dashboard-karyawan">
    <h2>Selamat Datang, <?= $_SESSION['nama'] ?></h2>
    <p>Email: <?= $_SESSION['email'] ?></p>
    <div class="absensi-form">
        <form action="absensi_proses.php" method="post">
            <input type="hidden" name="action" value="check_in">
            <button type="submit" <?= $disabled_masuk ? 'disabled' : '' ?>>Check In</button>
        </form>
        <form action="absensi_proses.php" method="post">
            <input type="hidden" name="action" value="check_out">
            <button type="submit" <?= $disabled_keluar ? 'disabled' : '' ?>>Check Out</button>
        </form>
    </div>
    <?php if ($disabled_masuk): ?>
        <p>Anda sudah melakukan check-in pada: <?= $absensi_hari_ini['jam_masuk'] ?></p>
    <?php endif; ?>
    <?php if ($sudah_absen_keluar): ?>
        <p>Anda sudah melakukan check-out pada: <?= $absensi_hari_ini['jam_keluar'] ?></p>
    <?php endif; ?>
</div>

<?php include 'templates/footer.php'; ?>