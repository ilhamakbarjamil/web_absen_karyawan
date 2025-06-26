<?php
include_once 'auth.php';
checkRole(['admin']);

include_once 'templates/header.php';

$tanggal_mulai = isset($_GET['tanggal_mulai']) ? $_GET['tanggal_mulai'] : date("Y-m-01");
$tanggal_akhir = isset($_GET['tanggal_akhir']) ? $_GET['tanggal_akhir'] : date("Y-m-d");
$cari_nama = isset($_GET['cari_nama']) ? $_GET['cari_nama'] : '';

$sql = "SELECT a.tanggal_absensi, k.nip, k.nama_karyawan, a.jam_masuk, a.jam_keluar, a.status_masuk, a.status_keluar 
        FROM absensi a
        JOIN karyawan k ON a.id_karyawan = k.id_karyawan
        WHERE a.tanggal_absensi BETWEEN ? AND ?";
$params = [$tanggal_mulai, $tanggal_akhir];
$types = "ss";

if (!empty($cari_nama)) {
    $sql .= " AND k.nama_karyawan LIKE ?";
    $params[] = '%' . $cari_nama . '%';
    $types .= "s";
}

$sql .= " ORDER BY a.tanggal_absensi DESC, k.nama_karyawan ASC";

$stmt = $conn->prepare($sql);
if ($stmt === false) {
    die("Error preparing statement: " . $conn->error);
}
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();
?>

<h2>Laporan Absensi Karyawan</h2>

<form action="" method="GET" style="margin-bottom: 20px;">
    <div class="form-group" style="display: inline-block; width: auto; margin-right: 10px;">
        <label for="tanggal_mulai">Dari Tanggal:</label>
        <input type="date" id="tanggal_mulai" name="tanggal_mulai" value="<?php echo htmlspecialchars($tanggal_mulai); ?>" required>
    </div>
    <div class="form-group" style="display: inline-block; width: auto; margin-right: 10px;">
        <label for="tanggal_akhir">Sampai Tanggal:</label>
        <input type="date" id="tanggal_akhir" name="tanggal_akhir" value="<?php echo htmlspecialchars($tanggal_akhir); ?>" required>
    </div>
    <div class="form-group" style="display: inline-block; width: auto; margin-right: 10px;">
        <label for="cari_nama">Cari Nama:</label>
        <input type="text" id="cari_nama" name="cari_nama" value="<?php echo htmlspecialchars($cari_nama); ?>" placeholder="Nama Karyawan">
    </div>
    <button type="submit" style="width: auto; padding: 10px 20px;">Tampilkan Laporan</button>
</form>

<?php if ($result->num_rows > 0): ?>
    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>NIP</th>
                <th>Nama Karyawan</th>
                <th>Jam Masuk</th>
                <th>Jam Keluar</th>
                <th>Status Masuk</th>
                <th>Status Keluar</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo date("d M Y", strtotime($row['tanggal_absensi'])); ?></td>
                    <td><?php echo htmlspecialchars($row['nip']); ?></td>
                    <td><?php echo htmlspecialchars($row['nama_karyawan']); ?></td>
                    <td><?php echo date("H:i", strtotime($row['jam_masuk'])); ?></td>
                    <td><?php echo $row['jam_keluar'] ? date("H:i", strtotime($row['jam_keluar'])) : '-'; ?></td>
                    <td><?php echo htmlspecialchars($row['status_masuk']); ?></td>
                    <td><?php echo $row['status_keluar'] ? htmlspecialchars($row['status_keluar']) : '-'; ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>Tidak ada data absensi untuk kriteria yang dipilih.</p>
<?php endif; ?>

<?php
$stmt->close();
include_once 'templates/footer.php';
$conn->close();
?>