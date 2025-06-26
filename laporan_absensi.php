<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

include 'config/database.php';
include 'templates/header.php';

$tanggal_awal = isset($_GET['tanggal_awal']) ? $_GET['tanggal_awal'] : date('Y-m-01');
$tanggal_akhir = isset($_GET['tanggal_akhir']) ? $_GET['tanggal_akhir'] : date('Y-m-d');
$karyawan_id = isset($_GET['karyawan_id']) ? $_GET['karyawan_id'] : '';

$stmt_karyawan = $pdo->query("SELECT id, nama FROM karyawan");
$karyawan_list = $stmt_karyawan->fetchAll();

$sql = "SELECT a.tanggal, k.nama, a.jam_masuk, a.jam_keluar, a.status 
        FROM absensi a 
        JOIN karyawan k ON a.karyawan_id = k.id 
        WHERE a.tanggal BETWEEN ? AND ?";
$params = [$tanggal_awal, $tanggal_akhir];

if (!empty($karyawan_id)) {
    $sql .= " AND a.karyawan_id = ?";
    $params[] = $karyawan_id;
}

$sql .= " ORDER BY a.tanggal DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$absensi = $stmt->fetchAll();
?>

<h2>Laporan Absensi</h2>
<form method="get" class="filter-form">
    <div class="form-group">
        <label for="tanggal_awal">Tanggal Awal:</label>
        <input type="date" name="tanggal_awal" value="<?= $tanggal_awal ?>">
    </div>
    <div class="form-group">
        <label for="tanggal_akhir">Tanggal Akhir:</label>
        <input type="date" name="tanggal_akhir" value="<?= $tanggal_akhir ?>">
    </div>
    <div class="form-group">
        <label for="karyawan_id">Karyawan:</label>
        <select name="karyawan_id">
            <option value="">Semua Karyawan</option>
            <?php foreach ($karyawan_list as $k): ?>
                <option value="<?= $k['id'] ?>" <?= $karyawan_id == $k['id'] ? 'selected' : '' ?>><?= $k['nama'] ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <button type="submit">Filter</button>
</form>

<table>
    <thead>
        <tr>
            <th>Tanggal</th>
            <th>Nama Karyawan</th>
            <th>Jam Masuk</th>
            <th>Jam Keluar</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($absensi as $absen): ?>
            <tr>
                <td><?= $absen['tanggal'] ?></td>
                <td><?= $absen['nama'] ?></td>
                <td><?= $absen['jam_masuk'] ?></td>
                <td><?= $absen['jam_keluar'] ? $absen['jam_keluar'] : '-' ?></td>
                <td><?= $absen['status'] ? $absen['status'] : 'Hadir' ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php include 'templates/footer.php'; ?>