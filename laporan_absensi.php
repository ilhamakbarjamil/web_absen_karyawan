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

// Hitung statistik
$total_absensi = count($absensi);
$hadir = 0;
$terlambat = 0;
$tanpa_checkout = 0;

foreach ($absensi as $absen) {
    if ($absen['status'] === 'Terlambat') {
        $terlambat++;
    } else {
        $hadir++;
    }
    if (!$absen['jam_keluar']) {
        $tanpa_checkout++;
    }
}
?>

<style>
    /* Attendance Report Styles */
    .report-container {
        max-width: 1400px;
        margin: 0 auto;
        padding: 20px;
    }
    
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        padding-bottom: 15px;
        border-bottom: 2px solid #4361ee;
    }
    
    .page-title {
        font-size: 28px;
        color: #2c3e50;
        display: flex;
        align-items: center;
        gap: 15px;
    }
    
    .page-title i {
        color: #4361ee;
        font-size: 32px;
    }
    
    .report-period {
        font-size: 18px;
        font-weight: 500;
        color: #4361ee;
        background: #f0f5ff;
        padding: 8px 15px;
        border-radius: 8px;
    }
    
    .filter-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        margin-bottom: 30px;
    }
    
    .filter-header {
        font-size: 22px;
        color: #2c3e50;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .filter-header i {
        color: #4361ee;
    }
    
    .filter-form {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
    }
    
    .filter-group {
        display: flex;
        flex-direction: column;
    }
    
    .filter-group label {
        font-weight: 600;
        color: #555;
        margin-bottom: 8px;
    }
    
    .filter-control {
        padding: 12px 15px;
        border: 2px solid #e0e8ff;
        border-radius: 8px;
        font-size: 16px;
        background: white;
    }
    
    .filter-control:focus {
        border-color: #4361ee;
        outline: none;
        box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
    }
    
    .filter-btn {
        background: linear-gradient(135deg, #4361ee, #3f37c9);
        color: white;
        border: none;
        padding: 12px 25px;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        font-size: 16px;
        margin-top: 25px;
    }
    
    .filter-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 15px rgba(67, 97, 238, 0.4);
    }
    
    .stats-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        text-align: center;
        position: relative;
        overflow: hidden;
    }
    
    .stat-card::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 5px;
        height: 100%;
    }
    
    .stat-card.total::before {
        background: #4361ee;
    }
    
    .stat-card.hadir::before {
        background: #4CAF50;
    }
    
    .stat-card.terlambat::before {
        background: #FF9800;
    }
    
    .stat-card.tanpa-checkout::before {
        background: #F44336;
    }
    
    .stat-icon {
        font-size: 40px;
        margin-bottom: 15px;
    }
    
    .stat-card.total .stat-icon {
        color: #4361ee;
    }
    
    .stat-card.hadir .stat-icon {
        color: #4CAF50;
    }
    
    .stat-card.terlambat .stat-icon {
        color: #FF9800;
    }
    
    .stat-card.tanpa-checkout .stat-icon {
        color: #F44336;
    }
    
    .stat-value {
        font-size: 32px;
        font-weight: 700;
        margin: 10px 0;
    }
    
    .stat-card.total .stat-value {
        color: #4361ee;
    }
    
    .stat-card.hadir .stat-value {
        color: #4CAF50;
    }
    
    .stat-card.terlambat .stat-value {
        color: #FF9800;
    }
    
    .stat-card.tanpa-checkout .stat-value {
        color: #F44336;
    }
    
    .stat-label {
        font-size: 16px;
        color: #555;
    }
    
    .report-actions {
        display: flex;
        justify-content: flex-end;
        gap: 15px;
        margin-bottom: 20px;
    }
    
    .export-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 25px;
        background: linear-gradient(135deg, #4CAF50, #2E7D32);
        color: white;
        text-decoration: none;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
        font-size: 16px;
    }
    
    .export-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 15px rgba(76, 175, 80, 0.4);
    }
    
    .print-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 25px;
        background: linear-gradient(135deg, #2196F3, #0d47a1);
        color: white;
        text-decoration: none;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
        font-size: 16px;
    }
    
    .print-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 15px rgba(33, 150, 243, 0.4);
    }
    
    .report-table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }
    
    .report-table thead {
        background: linear-gradient(135deg, #4361ee, #3f37c9);
        color: white;
    }
    
    .report-table th {
        padding: 18px 15px;
        text-align: left;
        font-weight: 600;
        font-size: 16px;
    }
    
    .report-table td {
        padding: 16px 15px;
        border-bottom: 1px solid #eef2f7;
        color: #555;
    }
    
    .report-table tbody tr:last-child td {
        border-bottom: none;
    }
    
    .report-table tbody tr:hover {
        background: #f8fbff;
    }
    
    .day-info {
        display: flex;
        flex-direction: column;
    }
    
    .day-name {
        font-size: 12px;
        color: #777;
        text-transform: uppercase;
    }
    
    .time-cell {
        font-weight: 500;
    }
    
    .status-cell {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 14px;
        font-weight: 500;
    }
    
    .status-hadir {
        background: #e8f5e9;
        color: #388e3c;
    }
    
    .status-terlambat {
        background: #fff3e0;
        color: #ef6c00;
    }
    
    .status-lainnya {
        background: #e3f2fd;
        color: #1976d2;
    }
    
    .no-data {
        text-align: center;
        padding: 40px;
        color: #777;
        font-style: italic;
    }
    
    .no-data i {
        font-size: 50px;
        margin-bottom: 15px;
        color: #e0e8ff;
    }
    
    @media (max-width: 768px) {
        .page-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
        }
        
        .filter-form {
            grid-template-columns: 1fr;
        }
        
        .report-table {
            display: block;
            overflow-x: auto;
        }
        
        .report-actions {
            flex-direction: column;
            align-items: flex-end;
        }
    }
</style>

<div class="report-container">
    <div class="page-header">
        <h1 class="page-title">
            <i class="fas fa-file-alt"></i>
            Laporan Absensi Karyawan
        </h1>
        <div class="report-period">
            <i class="fas fa-calendar-alt"></i>
            Periode: <?= date('d M Y', strtotime($tanggal_awal)) ?> - <?= date('d M Y', strtotime($tanggal_akhir)) ?>
        </div>
    </div>
    
    <div class="filter-card">
        <h3 class="filter-header">
            <i class="fas fa-filter"></i>
            Filter Laporan
        </h3>
        
        <form method="get" class="filter-form">
            <div class="filter-group">
                <label for="tanggal_awal">Tanggal Awal</label>
                <input type="date" id="tanggal_awal" name="tanggal_awal" class="filter-control" value="<?= $tanggal_awal ?>">
            </div>
            
            <div class="filter-group">
                <label for="tanggal_akhir">Tanggal Akhir</label>
                <input type="date" id="tanggal_akhir" name="tanggal_akhir" class="filter-control" value="<?= $tanggal_akhir ?>">
            </div>
            
            <div class="filter-group">
                <label for="karyawan_id">Karyawan</label>
                <select id="karyawan_id" name="karyawan_id" class="filter-control">
                    <option value="">Semua Karyawan</option>
                    <?php foreach ($karyawan_list as $k): ?>
                        <option value="<?= $k['id'] ?>" <?= $karyawan_id == $k['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($k['nama']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <button type="submit" class="filter-btn">
                <i class="fas fa-search"></i> Tampilkan Laporan
            </button>
        </form>
    </div>
    
    <div class="stats-container">
        <div class="stat-card total">
            <i class="fas fa-list-alt stat-icon"></i>
            <div class="stat-value"><?= $total_absensi ?></div>
            <div class="stat-label">Total Absensi</div>
        </div>
        
        <div class="stat-card hadir">
            <i class="fas fa-check-circle stat-icon"></i>
            <div class="stat-value"><?= $hadir ?></div>
            <div class="stat-label">Hadir Tepat Waktu</div>
        </div>
        
        <div class="stat-card terlambat">
            <i class="fas fa-clock stat-icon"></i>
            <div class="stat-value"><?= $terlambat ?></div>
            <div class="stat-label">Keterlambatan</div>
        </div>
        
        <div class="stat-card tanpa-checkout">
            <i class="fas fa-exclamation-triangle stat-icon"></i>
            <div class="stat-value"><?= $tanpa_checkout ?></div>
            <div class="stat-label">Tanpa Check-out</div>
        </div>
    </div>
    
    <div class="report-actions">
        <a href="#" class="print-btn" onclick="window.print()">
            <i class="fas fa-print"></i> Cetak Laporan
        </a>
        <a href="#" class="export-btn">
            <i class="fas fa-file-excel"></i> Ekspor ke Excel
        </a>
    </div>
    
    <div class="table-container">
        <table class="report-table">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Nama Karyawan</th>
                    <th>Jam Masuk</th>
                    <th>Jam Keluar</th>
                    <th>Durasi</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($absensi) > 0): ?>
                    <?php foreach ($absensi as $absen): 
                        // Hitung durasi kerja
                        $durasi = '';
                        if ($absen['jam_masuk'] && $absen['jam_keluar']) {
                            $masuk = new DateTime($absen['jam_masuk']);
                            $keluar = new DateTime($absen['jam_keluar']);
                            $selisih = $masuk->diff($keluar);
                            $durasi = $selisih->format('%h jam %i menit');
                        }
                        
                        // Tentukan nama hari
                        $tanggal = new DateTime($absen['tanggal']);
                        $nama_hari = $tanggal->format('l');
                        $hari_indonesia = [
                            'Monday' => 'Senin',
                            'Tuesday' => 'Selasa',
                            'Wednesday' => 'Rabu',
                            'Thursday' => 'Kamis',
                            'Friday' => 'Jumat',
                            'Saturday' => 'Sabtu',
                            'Sunday' => 'Minggu'
                        ];
                    ?>
                        <tr>
                            <td>
                                <div class="day-info">
                                    <span class="day-name"><?= $hari_indonesia[$nama_hari] ?></span>
                                    <?= date('d M Y', strtotime($absen['tanggal'])) ?>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($absen['nama']) ?></td>
                            <td class="time-cell"><?= $absen['jam_masuk'] ? $absen['jam_masuk'] : '-' ?></td>
                            <td class="time-cell"><?= $absen['jam_keluar'] ? $absen['jam_keluar'] : '-' ?></td>
                            <td><?= $durasi ? $durasi : '-' ?></td>
                            <td>
                                <?php if ($absen['status'] === 'Terlambat'): ?>
                                    <span class="status-cell status-terlambat">
                                        <i class="fas fa-clock"></i> Terlambat
                                    </span>
                                <?php elseif ($absen['status'] === 'Hadir' || empty($absen['status'])): ?>
                                    <span class="status-cell status-hadir">
                                        <i class="fas fa-check"></i> Hadir
                                    </span>
                                <?php else: ?>
                                    <span class="status-cell status-lainnya">
                                        <i class="fas fa-info-circle"></i> <?= $absen['status'] ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">
                            <div class="no-data">
                                <i class="fas fa-database"></i>
                                <h3>Tidak Ada Data Absensi</h3>
                                <p>Tidak ditemukan data absensi untuk filter yang dipilih</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'templates/footer.php'; ?>