<?php
session_start();
include 'config/database.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM karyawan WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // Debugging output
    echo "<pre>";
    echo "Email yang dimasukkan: " . htmlspecialchars($email) . "\n";
    echo "Password yang dimasukkan: " . htmlspecialchars($password) . "\n";
    if ($user) {
        echo "User ditemukan:\n";
        print_r($user);
        echo "Password di database: " . $user['password'] . "\n";
    } else {
        echo "User tidak ditemukan\n";
    }
    echo "</pre>";

    // Login tanpa hashing
    if ($user && $password === $user['password']) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['nama'] = $user['nama'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['email'] = $user['email'];

        if ($user['role'] == 'admin') {
            header('Location: admin_dashboard.php');
        } else {
            header('Location: karyawan_dashboard.php');
        }
        exit();
    } else {
        $error = 'Email atau password salah!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - Sistem Absensi</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .debug-info {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-family: monospace;
            font-size: 14px;
            color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>Login (Debug Mode)</h2>
        <?php if ($error): ?>
            <p class="error"><?= $error ?></p>
        <?php endif; ?>
        <form action="index.php" method="post">
            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>">
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Login</button>
        </form>
        
        <div class="test-credentials">
            <h3>Akun Test:</h3>
            <p><strong>Admin:</strong> admin@example.com / admin123</p>
            <p><strong>Karyawan:</strong> karyawan1@example.com / user123</p>
        </div>
    </div>
</body>
</html>