<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Absensi Karyawan</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header>
        <h1>Sistem Absensi Karyawan</h1>
        <nav>
            <?php if (isset($_SESSION['user_id'])): ?>
                <ul>
                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <li><a href="admin_dashboard.php">Dashboard Admin</a></li>
                        <li><a href="kelola_karyawan.php">Kelola Karyawan</a></li>
                        <li><a href="laporan_absensi.php">Laporan Absensi</a></li>
                    <?php else: ?>
                        <li><a href="karyawan_dashboard.php">Dashboard Karyawan</a></li>
                        <li><a href="riwayat_absensi.php">Riwayat Absensi</a></li>
                    <?php endif; ?>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            <?php endif; ?>
        </nav>
    </header>
    <main>