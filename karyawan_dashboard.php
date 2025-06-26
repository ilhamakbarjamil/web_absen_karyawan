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

<style>
    /* Dashboard Karyawan Styles */
    .employee-dashboard {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }
    
    .welcome-card {
        background: linear-gradient(135deg, #4361ee, #3f37c9);
        border-radius: 16px;
        padding: 30px;
        color: white;
        margin-bottom: 30px;
        box-shadow: 0 6px 15px rgba(67, 97, 238, 0.3);
        position: relative;
        overflow: hidden;
    }
    
    .welcome-card::before {
        content: "";
        position: absolute;
        top: -50px;
        right: -50px;
        width: 200px;
        height: 200px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
    }
    
    .welcome-card h2 {
        font-size: 32px;
        margin-bottom: 10px;
        font-weight: 700;
    }
    
    .welcome-card p {
        font-size: 18px;
        opacity: 0.9;
        margin-bottom: 5px;
    }
    
    .welcome-card .user-email {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-top: 15px;
        font-size: 16px;
        background: rgba(255, 255, 255, 0.15);
        padding: 8px 15px;
        border-radius: 50px;
        width: fit-content;
    }
    
    .attendance-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(500px, 1fr));
        gap: 25px;
        margin-bottom: 30px;
    }
    
    .attendance-card {
        background: white;
        border-radius: 16px;
        padding: 30px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        text-align: center;
    }
    
    .attendance-card h3 {
        font-size: 24px;
        color: #2c3e50;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }
    
    .attendance-card h3 i {
        color: #4361ee;
        font-size: 28px;
    }
    
    .attendance-actions {
        display: flex;
        justify-content: center;
        gap: 20px;
        margin: 30px 0;
    }
    
    .attendance-btn {
        padding: 20px 40px;
        border-radius: 12px;
        font-size: 20px;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-width: 200px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    }
    
    .attendance-btn i {
        font-size: 40px;
        margin-bottom: 15px;
    }
    
    .checkin-btn {
        background: linear-gradient(135deg, #4CAF50, #2E7D32);
        color: white;
    }
    
    .checkin-btn:hover:not(:disabled) {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(76, 175, 80, 0.4);
    }
    
    .checkout-btn {
        background: linear-gradient(135deg, #F44336, #C62828);
        color: white;
    }
    
    .checkout-btn:hover:not(:disabled) {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(244, 67, 54, 0.4);
    }
    
    .attendance-btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }
    
    .attendance-status {
        background: #f8fbff;
        padding: 20px;
        border-radius: 12px;
        margin-top: 20px;
        border-left: 4px solid #4361ee;
    }
    
    .status-item {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #eef2f7;
    }
    
    .status-item:last-child {
        border-bottom: none;
    }
    
    .status-label {
        font-weight: 600;
        color: #555;
    }
    
    .status-value {
        font-weight: 500;
        color: #2c3e50;
    }
    
    .history-link {
        display: inline-block;
        margin-top: 30px;
        padding: 12px 25px;
        background: linear-gradient(135deg, #4361ee, #3f37c9);
        color: white;
        text-decoration: none;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
        font-size: 16px;
    }
    
    .history-link:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 15px rgba(67, 97, 238, 0.4);
    }
    
    .time-display {
        font-size: 18px;
        margin-top: 15px;
        font-weight: 500;
        color: #2c3e50;
        background: #f0f5ff;
        padding: 10px 15px;
        border-radius: 8px;
        display: inline-block;
    }
    
    .current-time {
        text-align: center;
        margin-top: 20px;
        font-size: 18px;
        color: #555;
        background: #f8f9fa;
        padding: 15px;
        border-radius: 12px;
    }
    
    @media (max-width: 768px) {
        .attendance-container {
            grid-template-columns: 1fr;
        }
        
        .attendance-actions {
            flex-direction: column;
            align-items: center;
        }
        
        .welcome-card h2 {
            font-size: 26px;
        }
        
        .attendance-btn {
            width: 100%;
            max-width: 300px;
        }
    }
</style>

<div class="employee-dashboard">
    <div class="welcome-card">
        <h2>Selamat Datang, <?= htmlspecialchars($_SESSION['nama']) ?></h2>
        <p>Anda login sebagai Karyawan - Sistem Absensi</p>
        <div class="user-email">
            <i class="fas fa-envelope"></i>
            <?= htmlspecialchars($_SESSION['email']) ?>
        </div>
    </div>
    
    <div class="attendance-container">
        <div class="attendance-card">
            <h3><i class="fas fa-fingerprint"></i> Absensi Hari Ini</h3>
            
            <div class="attendance-actions">
                <form action="absensi_proses.php" method="post" style="width: 100%;">
                    <input type="hidden" name="action" value="check_in">
                    <button type="submit" class="attendance-btn checkin-btn" <?= $disabled_masuk ? 'disabled' : '' ?>>
                        <i class="fas fa-sign-in-alt"></i>
                        CHECK IN
                    </button>
                </form>
                
                <form action="absensi_proses.php" method="post" style="width: 100%;">
                    <input type="hidden" name="action" value="check_out">
                    <button type="submit" class="attendance-btn checkout-btn" <?= $disabled_keluar ? 'disabled' : '' ?>>
                        <i class="fas fa-sign-out-alt"></i>
                        CHECK OUT
                    </button>
                </form>
            </div>
            
            <div class="attendance-status">
                <div class="status-item">
                    <span class="status-label">Tanggal:</span>
                    <span class="status-value"><?= date('d F Y') ?></span>
                </div>
                
                <?php if ($disabled_masuk): ?>
                    <div class="status-item">
                        <span class="status-label">Status Check-in:</span>
                        <span class="status-value">Sudah Dilakukan</span>
                    </div>
                    <div class="time-display">
                        <i class="fas fa-clock"></i> Pada: <?= $absensi_hari_ini['jam_masuk'] ?>
                    </div>
                <?php else: ?>
                    <div class="status-item">
                        <span class="status-label">Status Check-in:</span>
                        <span class="status-value">Belum Dilakukan</span>
                    </div>
                <?php endif; ?>
                
                <?php if ($sudah_absen_keluar): ?>
                    <div class="status-item">
                        <span class="status-label">Status Check-out:</span>
                        <span class="status-value">Sudah Dilakukan</span>
                    </div>
                    <div class="time-display">
                        <i class="fas fa-clock"></i> Pada: <?= $absensi_hari_ini['jam_keluar'] ?>
                    </div>
                <?php elseif ($disabled_masuk && !$sudah_absen_keluar): ?>
                    <div class="status-item">
                        <span class="status-label">Status Check-out:</span>
                        <span class="status-value">Belum Dilakukan</span>
                    </div>
                <?php else: ?>
                    <div class="status-item">
                        <span class="status-label">Status Check-out:</span>
                        <span class="status-value">Belum Tersedia</span>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="current-time">
                <i class="fas fa-clock"></i> Waktu Saat Ini: <span id="live-clock"><?= date('H:i:s') ?></span>
            </div>
        </div>
        
        <div class="attendance-card">
            <h3><i class="fas fa-history"></i> Riwayat & Informasi</h3>
            
            <div style="text-align: left; margin-bottom: 25px;">
                <p>Di sini Anda dapat melihat riwayat absensi dan informasi terkait kehadiran Anda.</p>
                <p>Pastikan untuk melakukan check-in dan check-out setiap hari kerja untuk menjaga kehadiran Anda tercatat dengan baik.</p>
            </div>
            
            <div class="attendance-status">
                <div class="status-item">
                    <span class="status-label">Total Kehadiran Bulan Ini:</span>
                    <span class="status-value">
                        <?php
                        $bulan_ini = date('m');
                        $tahun_ini = date('Y');
                        $stmt = $pdo->prepare("SELECT COUNT(*) FROM absensi 
                                              WHERE karyawan_id = ? 
                                              AND MONTH(tanggal) = ? 
                                              AND YEAR(tanggal) = ?");
                        $stmt->execute([$karyawan_id, $bulan_ini, $tahun_ini]);
                        $total_hadir = $stmt->fetchColumn();
                        echo $total_hadir . ' hari';
                        ?>
                    </span>
                </div>
                
                <div class="status-item">
                    <span class="status-label">Hari Kerja Bulan Ini:</span>
                    <span class="status-value">
                        <?php
                        // Hitung hari kerja (Senin-Jumat) dalam bulan ini
                        $hari_kerja = 0;
                        $hari_ini = new DateTime("first day of this month");
                        $akhir_bulan = new DateTime("last day of this month");
                        
                        while ($hari_ini <= $akhir_bulan) {
                            if ($hari_ini->format('N') < 6) { // 1-5 = Senin-Jumat
                                $hari_kerja++;
                            }
                            $hari_ini->modify('+1 day');
                        }
                        echo $hari_kerja . ' hari';
                        ?>
                    </span>
                </div>
            </div>
            
            <a href="riwayat_absensi.php" class="history-link">
                <i class="fas fa-list"></i> Lihat Riwayat Absensi Lengkap
            </a>
        </div>
    </div>
</div>

<script>
// Fungsi untuk menampilkan jam live
function updateClock() {
    const now = new Date();
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    const seconds = String(now.getSeconds()).padStart(2, '0');
    document.getElementById('live-clock').textContent = `${hours}:${minutes}:${seconds}`;
}

// Update jam setiap detik
setInterval(updateClock, 1000);

// Inisialisasi jam pertama kali
updateClock();
</script>

<?php include 'templates/footer.php'; ?>