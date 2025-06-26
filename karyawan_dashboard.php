<?php
include_once 'auth.php';
checkRole(['karyawan']); // Hanya karyawan yang bisa akses

include_once 'templates/header.php';
?>

<h2>Halo, <?php echo htmlspecialchars($_SESSION['nama_karyawan']); ?>!</h2>
<p>Selamat datang di dashboard absensi Anda.</p>
<p>Tanggal saat ini: **<?php echo date("d M Y"); ?>**</p>
<p>Waktu saat ini: **<?php echo date("H:i:s"); ?>**</p>

<div style="margin-top: 30px; text-align: center;">
    <?php
    // Cek apakah sudah absen masuk hari ini
    $today = date("Y-m-d");
    $stmt = $conn->prepare("SELECT id_absensi, jam_masuk, jam_keluar FROM absensi WHERE id_karyawan = ? AND tanggal_absensi = ?");
    $stmt->bind_param("is", $_SESSION['user_id'], $today);
    $stmt->execute();
    $result = $stmt->get_result();
    $absensi_hari_ini = $result->fetch_assoc();
    $stmt->close();

    if ($absensi_hari_ini) {
        echo "<p class='message success'>Anda sudah Absen Masuk hari ini pada pukul " . date("H:i", strtotime($absensi_hari_ini['jam_masuk'])) . ".</p>";
        if ($absensi_hari_ini['jam_keluar']) {
            echo "<p class='message success'>Anda sudah Absen Pulang hari ini pada pukul " . date("H:i", strtotime($absensi_hari_ini['jam_keluar'])) . ".</p>";
            echo "<button disabled style='background-color: #6c757d;'>Sudah Absen Masuk & Pulang</button>";
        } else {
            echo "<a href='absensi_proses.php?action=pulang' class='button' style='display: inline-block; text-decoration: none; background-color: #dc3545;'>Absen Pulang</a>";
        }
    } else {
        echo "<a href='absensi_proses.php?action=masuk' class='button' style='display: inline-block; text-decoration: none;'>Absen Masuk</a>";
    }
    ?>
</div>

<?php
include_once 'templates/footer.php';
$conn->close();
?>