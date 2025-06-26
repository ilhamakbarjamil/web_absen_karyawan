<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

include 'config/database.php';

$action = $_POST['action'];
$karyawan_id = $_SESSION['user_id'];
$tanggal_sekarang = date('Y-m-d');
$waktu_sekarang = date('H:i:s');

if ($action == 'check_in') {
    $stmt = $pdo->prepare("SELECT * FROM absensi WHERE karyawan_id = ? AND tanggal = ?");
    $stmt->execute([$karyawan_id, $tanggal_sekarang]);
    $absensi = $stmt->fetch();

    if (!$absensi) {
        $stmt = $pdo->prepare("INSERT INTO absensi (karyawan_id, tanggal, jam_masuk) VALUES (?, ?, ?)");
        $stmt->execute([$karyawan_id, $tanggal_sekarang, $waktu_sekarang]);
        $_SESSION['success'] = "Check-in berhasil pada $waktu_sekarang";
    }
} elseif ($action == 'check_out') {
    $stmt = $pdo->prepare("UPDATE absensi SET jam_keluar = ? WHERE karyawan_id = ? AND tanggal = ?");
    $stmt->execute([$waktu_sekarang, $karyawan_id, $tanggal_sekarang]);
    $_SESSION['success'] = "Check-out berhasil pada $waktu_sekarang";
}

header('Location: karyawan_dashboard.php');
exit();
?>