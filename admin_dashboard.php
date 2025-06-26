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

// Dapatkan karyawan yang sudah absen hari ini
$stmt = $pdo->prepare("SELECT k.nama FROM absensi a JOIN karyawan k ON a.karyawan_id = k.id WHERE a.tanggal = ?");
$stmt->execute([$tanggal_sekarang]);
$sudah_absen = $stmt->fetchAll(PDO::FETCH_COLUMN);
?>

<style>
    /* Dashboard Admin Styles */
    .dashboard-admin {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }

    .welcome-banner {
        background: linear-gradient(135deg, #4361ee, #3f37c9);
        border-radius: 12px;
        padding: 30px;
        margin-bottom: 30px;
        color: white;
        box-shadow: 0 6px 15px rgba(67, 97, 238, 0.3);
        position: relative;
        overflow: hidden;
    }

    .welcome-banner::before {
        content: "";
        position: absolute;
        top: -50px;
        right: -50px;
        width: 200px;
        height: 200px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
    }

    .welcome-banner h2 {
        font-size: 32px;
        margin-bottom: 10px;
        font-weight: 700;
    }

    .welcome-banner p {
        font-size: 18px;
        opacity: 0.9;
        margin-bottom: 0;
    }

    .stats-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 25px;
        margin-bottom: 30px;
    }

    .stat-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    }

    .stat-card::before {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        width: 5px;
        height: 100%;
        background: #4361ee;
    }

    .stat-card h3 {
        font-size: 20px;
        color: #555;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .stat-card h3 i {
        color: #4361ee;
        font-size: 24px;
    }

    .stat-card .stat-value {
        font-size: 42px;
        font-weight: 700;
        color: #4361ee;
        margin: 0;
        line-height: 1.2;
    }

    .stat-card .stat-info {
        font-size: 14px;
        color: #777;
        margin-top: 10px;
    }

    .actions-container {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 25px;
        margin-bottom: 30px;
    }

    .action-card {
        background: white;
        border-radius: 12px;
        padding: 30px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        text-align: center;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .action-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    }

    .action-card i {
        font-size: 60px;
        color: #4361ee;
        margin-bottom: 20px;
        display: block;
    }

    .action-card h3 {
        font-size: 22px;
        color: #333;
        margin-bottom: 15px;
    }

    .action-card p {
        color: #666;
        margin-bottom: 20px;
        font-size: 15px;
    }

    .btn-action {
        display: inline-block;
        padding: 12px 25px;
        background: linear-gradient(135deg, #4361ee, #3f37c9);
        color: white;
        text-decoration: none;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
        font-size: 16px;
    }

    .btn-action:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 15px rgba(67, 97, 238, 0.4);
    }

    .attendance-list {
        background: white;
        border-radius: 12px;
        padding: 30px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        margin-bottom: 30px;
    }

    .attendance-list h3 {
        font-size: 22px;
        color: #333;
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .attendance-list h3 i {
        color: #4361ee;
    }

    .employee-list {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
    }

    .employee-badge {
        background: #f0f5ff;
        padding: 10px 15px;
        border-radius: 50px;
        display: flex;
        align-items: center;
        gap: 10px;
        color: #4361ee;
        font-weight: 500;
        font-size: 14px;
        border: 1px solid #e0e8ff;
    }

    .employee-badge i {
        background: #4361ee;
        color: white;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 14px;
    }

    .empty-message {
        color: #777;
        font-style: italic;
        padding: 20px 0;
        text-align: center;
    }

    @media (max-width: 768px) {
        .stats-container,
        .actions-container {
            grid-template-columns: 1fr;
        }
        
        .welcome-banner h2 {
            font-size: 26px;
        }
        
        .stat-card .stat-value {
            font-size: 36px;
        }
    }
</style>

<div class="dashboard-admin">
    <div class="welcome-banner">
        <h2>Selamat Datang, <?= htmlspecialchars($_SESSION['nama']) ?></h2>
        <p>Anda login sebagai Administrator Sistem Absensi Karyawan</p>
    </div>
    
    <div class="stats-container">
        <div class="stat-card">
            <h3><i class="fas fa-users"></i> Total Karyawan</h3>
            <p class="stat-value"><?= $total_karyawan ?></p>
            <p class="stat-info">Jumlah karyawan terdaftar dalam sistem</p>
        </div>
        
        <div class="stat-card">
            <h3><i class="fas fa-calendar-check"></i> Absensi Hari Ini</h3>
            <p class="stat-value"><?= $absensi_hari_ini ?></p>
            <p class="stat-info">Jumlah absensi pada <?= date('d F Y') ?></p>
        </div>
        
        <div class="stat-card">
            <h3><i class="fas fa-user-clock"></i> Persentase Kehadiran</h3>
            <p class="stat-value"><?= $total_karyawan > 0 ? round(($absensi_hari_ini / $total_karyawan) * 100) : 0 ?>%</p>
            <p class="stat-info">Persentase kehadiran karyawan hari ini</p>
        </div>
    </div>
    
    <div class="actions-container">
        <div class="action-card">
            <i class="fas fa-user-cog"></i>
            <h3>Kelola Karyawan</h3>
            <p>Kelola data karyawan, tambah, edit, atau hapus akun karyawan</p>
            <a href="kelola_karyawan.php" class="btn-action">Buka Panel</a>
        </div>
        
        <div class="action-card">
            <i class="fas fa-file-alt"></i>
            <h3>Laporan Absensi</h3>
            <p>Lihat dan ekspor laporan absensi karyawan berdasarkan periode tertentu</p>
            <a href="laporan_absensi.php" class="btn-action">Lihat Laporan</a>
        </div>
    </div>
    
    <div class="attendance-list">
        <h3><i class="fas fa-clipboard-list"></i> Karyawan Yang Sudah Absen Hari Ini</h3>
        
        <?php if (count($sudah_absen) > 0): ?>
            <div class="employee-list">
                <?php foreach ($sudah_absen as $nama): ?>
                    <div class="employee-badge">
                        <i class="fas fa-user"></i>
                        <?= htmlspecialchars($nama) ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="empty-message">Belum ada karyawan yang absen hari ini</p>
        <?php endif; ?>
    </div>
</div>

<?php include 'templates/footer.php'; ?>