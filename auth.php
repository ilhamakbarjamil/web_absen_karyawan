<?php
session_start();
include_once 'config/database.php';

function checkLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: index.php");
        exit();
    }
}

function checkRole($allowedRoles) {
    checkLogin(); // Pastikan sudah login dulu
    if (!in_array($_SESSION['role'], $allowedRoles)) {
        echo "<script>alert('Anda tidak memiliki akses ke halaman ini!'); window.location.href='" . ($_SESSION['role'] == 'admin' ? 'admin_dashboard.php' : 'karyawan_dashboard.php') . "';</script>";
        exit();
    }
}
?>