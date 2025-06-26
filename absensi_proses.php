<?php
include_once 'auth.php';
checkRole(['karyawan']); // Hanya karyawan yang bisa akses proses ini

$action = $_GET['action'] ?? '';
$user_id = $_SESSION['user_id'];
$today = date("Y-m-d");
$current_time = date("H:i:s");
$message = '';

if ($action == 'masuk') {
    // Cek apakah sudah absen masuk hari ini
    $stmt = $conn->prepare("SELECT id_absensi FROM absensi WHERE id_karyawan = ? AND tanggal_absensi = ?");
    $stmt->bind_param("is", $user_id, $today);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 0) {
        // Belum absen masuk, catat absensi baru
        $stmt_insert = $conn->prepare("INSERT INTO absensi (id_karyawan, tanggal_absensi, jam_masuk, status_masuk) VALUES (?, ?, ?, ?)");
        $status_masuk = 'Hadir'; // Default status
        $stmt_insert->bind_param("isss", $user_id, $today, $current_time, $status_masuk);
        if ($stmt_insert->execute()) {
            $message = "<div class='message success'>Absen Masuk berhasil dicatat pada " . $current_time . "!</div>";
        } else {
            $message = "<div class='message error'>Gagal mencatat absen masuk.</div>";
        }
        $stmt_insert->close();
    } else {
        $message = "<div class='message error'>Anda sudah absen masuk hari ini.</div>";
    }
    $stmt->close();
} elseif ($action == 'pulang') {
    // Cek apakah sudah absen masuk dan belum absen pulang hari ini
    $stmt = $conn->prepare("SELECT id_absensi, jam_keluar FROM absensi WHERE id_karyawan = ? AND tanggal_absensi = ?");
    $stmt->bind_param("is", $user_id, $today);
    $stmt->execute();
    $result = $stmt->get_result();
    $absensi = $result->fetch_assoc();

    if ($absensi && is_null($absensi['jam_keluar'])) {
        // Sudah absen masuk tapi belum absen pulang, update jam_keluar
        $stmt_update = $conn->prepare("UPDATE absensi SET jam_keluar = ?, status_keluar = ? WHERE id_absensi = ?");
        $status_pulang = 'Pulang Normal'; // Default status
        $stmt_update->bind_param("ssi", $current_time, $status_pulang, $absensi['id_absensi']);
        if ($stmt_update->execute()) {
            $message = "<div class='message success'>Absen Pulang berhasil dicatat pada " . $current_time . "!</div>";
        } else {
            $message = "<div class='message error'>Gagal mencatat absen pulang.</div>";
        }
        $stmt_update->close();
    } elseif ($absensi && !is_null($absensi['jam_keluar'])) {
        $message = "<div class='message error'>Anda sudah absen pulang hari ini.</div>";
    } else {
        $message = "<div class='message error'>Anda belum absen masuk hari ini.</div>";
    }
    $stmt->close();
} else {
    $message = "<div class='message error'>Aksi tidak valid.</div>";
}

// Setelah proses, arahkan kembali ke dashboard dengan pesan
$_SESSION['absensi_message'] = $message;
header("Location: karyawan_dashboard.php");
exit();
?>