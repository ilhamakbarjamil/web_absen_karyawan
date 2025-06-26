<?php
session_start();
require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $_SESSION['message'] = 'Username dan password harus diisi!';
        $_SESSION['message_type'] = 'danger';
        header('Location: index.php');
        exit();
    }

    try {
        // Cari user berdasarkan username
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND status = 'aktif'");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Login berhasil
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['jabatan'] = $user['jabatan'];
            $_SESSION['departemen'] = $user['departemen'];

            $_SESSION['message'] = 'Login berhasil! Selamat datang, ' . $user['nama_lengkap'];
            $_SESSION['message_type'] = 'success';

            // Redirect berdasarkan role
            if ($user['role'] == 'admin') {
                header('Location: admin_dashboard.php');
            } else {
                header('Location: karyawan_dashboard.php');
            }
            exit();
        } else {
            $_SESSION['message'] = 'Username atau password salah!';
            $_SESSION['message_type'] = 'danger';
            header('Location: index.php');
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['message'] = 'Terjadi kesalahan sistem. Silakan coba lagi.';
        $_SESSION['message_type'] = 'danger';
        header('Location: index.php');
        exit();
    }
} else {
    header('Location: index.php');
    exit();
}
?>