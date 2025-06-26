<?php
include_once 'auth.php';
checkRole(['karyawan']);

include_once 'templates/header.php';

$user_id = $_SESSION['user_id'];
$filter_bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date("m");
$filter_tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date("Y");

$sql = "SELECT tanggal_absensi, jam_masuk, jam_keluar, status_masuk, status_keluar 
        FROM absensi 
        WHERE id_karyawan = ? AND MONTH(tanggal_absensi) = ? AND YEAR(tanggal_absensi) = ? 
        ORDER BY tanggal_absensi DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("iii", $user_id, $filter_bulan, $filter_tahun);
$stmt->execute();
$result = $stmt->get_result();
?>

<h2>Riwayat Absensi Anda</h2>

<form action="" method="GET" style="margin-bottom: 20px;">
    <div class="form-group" style="display: inline-block; width: auto; margin-right: 10px;">
        <label for="bulan">Bulan:</label>
        <select name="bulan" id="bulan">
            <?php for ($i = 1; $i <= 12; $i++): ?>
                <option value="<?php echo sprintf('%02d', $i); ?>" <?php echo ($filter_bulan == $i) ? 'selected' : ''; ?>>
                    <?php echo date("F", mktime(0, 0, 0, $i, 10)); ?>
                </option>
            <?php endfor; ?>
        </select>
    </div>
    <div class="form-group" style="display: inline-block; width: auto; margin-right: 10px;">
        <label for="tahun">Tahun:</label>
        <select name="tahun" id="tahun">
            <?php for ($i = date("Y"); $i >= date("Y") - 5; $i--): ?>
                <option value="<?php echo $i; ?>" <?php echo ($filter_tahun == $i) ? 'selected' : ''; ?>>
                    <?php echo $i; ?>
                </option>
            <?php endfor; ?>
        </select>
    </div>
    <button type="submit" style="width: auto; padding: 10px 20px;">Filter</button>
</form>

<?php if ($result->num_rows > 0): ?>
    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
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
                    <td><?php echo date("H:i", strtotime($row['jam_masuk'])); ?></td>
                    <td><?php echo $row['jam_keluar'] ? date("H:i", strtotime($row['jam_keluar'])) : '-'; ?></td>
                    <td><?php echo htmlspecialchars($row['status_masuk']); ?></td>
                    <td><?php echo $row['status_keluar'] ? htmlspecialchars($row['status_keluar']) : '-'; ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>Tidak ada riwayat absensi untuk periode ini.</p>
<?php endif; ?>

<?php
$stmt->close();
include_once 'templates/footer.php';
$conn->close();
?>