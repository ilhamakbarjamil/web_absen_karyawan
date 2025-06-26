<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'karyawan') {
    header('Location: index.php');
    exit();
}

include 'config/database.php';
include 'templates/header.php';

$karyawan_id = $_SESSION['user_id'];

// Filter berdasarkan bulan dan tahun
$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

// Query untuk mendapatkan riwayat absensi dengan filter
$stmt = $pdo->prepare("SELECT * FROM absensi 
                      WHERE karyawan_id = ? 
                      AND MONTH(tanggal) = ? 
                      AND YEAR(tanggal) = ?
                      ORDER BY tanggal DESC");
$stmt->execute([$karyawan_id, $bulan, $tahun]);
$absensi = $stmt->fetchAll();

// Hitung statistik kehadiran
$total_hadir = 0;
$total_terlambat = 0;
$total_tanpa_checkout = 0;

foreach ($absensi as $absen) {
    if ($absen['status'] === 'Terlambat') {
        $total_terlambat++;
    } else {
        $total_hadir++;
    }
    if (!$absen['jam_keluar']) {
        $total_tanpa_checkout++;
    }
}
?>

<style>
    /* Attendance History Styles */
    .attendance-history {
        max-width: 1200px;
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
    
    .filter-container {
        background: white;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        margin-bottom: 30px;
    }
    
    .filter-form {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        align-items: center;
    }
    
    .filter-group {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .filter-group label {
        font-weight: 600;
        color: #555;
    }
    
    .filter-select {
        padding: 10px 15px;
        border: 2px solid #e0e8ff;
        border-radius: 8px;
        font-size: 16px;
        background: white;
    }
    
    .filter-btn {
        background: linear-gradient(135deg, #4361ee, #3f37c9);
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
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
    
    .history-table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }
    
    .history-table thead {
        background: linear-gradient(135deg, #4361ee, #3f37c9);
        color: white;
    }
    
    .history-table th {
        padding: 18px 15px;
        text-align: left;
        font-weight: 600;
        font-size: 16px;
    }
    
    .history-table td {
        padding: 16px 15px;
        border-bottom: 1px solid #eef2f7;
        color: #555;
    }
    
    .history-table tbody tr:last-child td {
        border-bottom: none;
    }
    
    .history-table tbody tr:hover {
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
        margin-top: 20px;
    }
    
    .export-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 15px rgba(76, 175, 80, 0.4);
    }
    
    @media (max-width: 768px) {
        .filter-form {
            flex-direction: column;
            align-items: stretch;
        }
        
        .filter-group {
            width: 100%;
        }
        
        .filter-select {
            width: 100%;
        }
        
        .page-header {
            flex-direction: column;
            align-items: flex-start;
            gap: 15px;
        }
        
        .history-table {
            display: block;
            overflow-x: auto;
        }
    }
</style>

<div class="attendance-history">
    <div class="page-header">
        <h1 class="page-title">
            <i class="fas fa-history"></i>
            Riwayat Absensi
        </h1>
        <div class="current-period">
            <p style="font-size: 18px; font-weight: 500; color: #4361ee;">
                Periode: <?= date('F Y', mktime(0, 0, 0, $bulan, 1, $tahun)) ?>
            </p>
        </div>
    </div>
    
    <div class="filter-container">
        <form method="get" class="filter-form">
            <div class="filter-group">
                <label for="bulan">Bulan:</label>
                <select id="bulan" name="bulan" class="filter-select">
                    <?php for ($i = 1; $i <= 12; $i++): ?>
                        <option value="<?= $i ?>" <?= $bulan == $i ? 'selected' : '' ?>>
                            <?= date('F', mktime(0, 0, 0, $i, 1)) ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            
            <div class="filter-group">
                <label for="tahun">Tahun:</label>
                <select id="tahun" name="tahun" class="filter-select">
                    <?php for ($i = date('Y') - 2; $i <= date('Y') + 1; $i++): ?>
                        <option value="<?= $i ?>" <?= $tahun == $i ? 'selected' : '' ?>>
                            <?= $i ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            
            <button type="submit" class="filter-btn">
                <i class="fas fa-filter"></i> Filter Data
            </button>
        </form>
    </div>
    
    <div class="stats-container">
        <div class="stat-card hadir">
            <i class="fas fa-check-circle stat-icon"></i>
            <div class="stat-value"><?= $total_hadir ?></div>
            <div class="stat-label">Hari Hadir</div>
        </div>
        
        <div class="stat-card terlambat">
            <i class="fas fa-clock stat-icon"></i>
            <div class="stat-value"><?= $total_terlambat ?></div>
            <div class="stat-label">Keterlambatan</div>
        </div>
        
        <div class="stat-card tanpa-checkout">
            <i class="fas fa-exclamation-triangle stat-icon"></i>
            <div class="stat-value"><?= $total_tanpa_checkout ?></div>
            <div class="stat-label">Tanpa Check-out</div>
        </div>
    </div>
    
    <div class="table-container">
        <table class="history-table">
            <thead>
                <tr>
                    <th>Tanggal</th>
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
                            <td class="time-cell">
                                <?= $absen['jam_masuk'] ? $absen['jam_masuk'] : '-' ?>
                            </td>
                            <td class="time-cell">
                                <?= $absen['jam_keluar'] ? $absen['jam_keluar'] : '-' ?>
                            </td>
                            <td>
                                <?= $durasi ? $durasi : '-' ?>
                            </td>
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
                        <td colspan="5">
                            <div class="no-data">
                                <i class="fas fa-database"></i>
                                <h3>Tidak Ada Data Absensi</h3>
                                <p>Tidak ditemukan riwayat absensi untuk periode yang dipilih</p>
                            </div>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        
        <a href="#" class="export-btn">
            <i class="fas fa-file-export"></i> Ekspor ke Excel
        </a>
    </div>
</div>

<?php include 'templates/footer.php'; ?>