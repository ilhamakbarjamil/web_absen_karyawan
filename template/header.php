<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Absensi Karyawan</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div class="container">
        <header>
            <h1>Sistem Absensi Karyawan</h1>
            <nav>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="<?php echo ($_SESSION['role'] == 'admin') ? 'admin_dashboard.php' : 'karyawan_dashboard.php'; ?>">Dashboard</a>
                    <?php if ($_SESSION['role'] == 'admin'): ?>
                        <a href="kelola_karyawan.php">Kelola Karyawan</a>
                        <a href="laporan_absensi.php">Laporan Absensi</a>
                    <?php endif; ?>
                    <?php if ($_SESSION['role'] == 'karyawan'): ?>
                        <a href="riwayat_absensi.php">Riwayat Absensi</a>
                    <?php endif; ?>
                    <a href="logout.php">Logout</a>
                <?php else: ?>
                    <a href="index.php">Login</a>
                <?php endif; ?>
            </nav>
        </header>
        <main>