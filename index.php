<?php
session_start();
include_once 'config/database.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nip = $_POST['nip'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT id_karyawan, nip, password, nama_karyawan, role FROM karyawan WHERE nip = ?");
    $stmt->bind_param("s", $nip);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id_karyawan'];
            $_SESSION['nip'] = $user['nip'];
            $_SESSION['nama_karyawan'] = $user['nama_karyawan'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] == 'admin') {
                header("Location: admin_dashboard.php");
            } else {
                header("Location: karyawan_dashboard.php");
            }
            exit();
        } else {
            $message = "<div class='message error'>NIP atau Kata Sandi salah.</div>";
        }
    } else {
        $message = "<div class='message error'>NIP atau Kata Sandi salah.</div>";
    }
    $stmt->close();
}

include_once 'templates/header.php';
?>

<div class="form-container">
    <h2>Login Sistem Absensi</h2>
    <?php echo $message; ?>
    <form action="index.php" method="POST">
        <div class="form-group">
            <label for="nip">NIP:</label>
            <input type="text" id="nip" name="nip" required>
        </div>
        <div class="form-group">
            <label for="password">Kata Sandi:</label>
            <input type="password" id="password" name="password" required>
        </div>
        <button type="submit">Login</button>
    </form>
</div>

<?php
include_once 'templates/footer.php';
$conn->close();
?>