<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'karyawan') {
    header('Location: index.php');
    exit();
}

include 'config/database.php';
include 'templates/header.php';

$karyawan_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM absensi WHERE karyawan_id = ? ORDER BY tanggal DESC");
$stmt->execute([$karyawan_id]);
$absensi = $stmt->fetchAll();
?>

<h2>Riwayat Absensi</h2>
<table>
    <thead>
        <tr>
            <th>Tanggal</th>
            <th>Jam Masuk</th>
            <th>Jam Keluar</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($absensi as $absen): ?>
            <tr>
                <td><?= $absen['tanggal'] ?></td>
                <td><?= $absen['jam_masuk'] ?></td>
                <td><?= $absen['jam_keluar'] ? $absen['jam_keluar'] : 'Belum Check-out' ?></td>
                <td><?= $absen['status'] ? $absen['status'] : 'Hadir' ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include 'templates/footer.php'; ?>