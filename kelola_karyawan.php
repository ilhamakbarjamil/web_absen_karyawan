<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

include 'config/database.php';
include 'templates/header.php';

// if (isset($_POST['tambah'])) {
//     $nama = $_POST['nama'];
//     $email = $_POST['email'];
//     $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
//     $role = $_POST['role'];

//     try {
//         $stmt = $pdo->prepare("INSERT INTO karyawan (nama, email, password, role) VALUES (?, ?, ?, ?)");
//         $stmt->execute([$nama, $email, $password, $role]);
//         $_SESSION['success'] = "Karyawan berhasil ditambahkan";
//     } catch (PDOException $e) {
//         $_SESSION['error'] = "Gagal menambahkan karyawan: " . $e->getMessage();
//     }
// }

// Di bagian form tambah karyawan
if (isset($_POST['tambah'])) {
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $password = $_POST['password']; // Simpan sebagai plaintext
    $role = $_POST['role'];

    try {
        $stmt = $pdo->prepare("INSERT INTO karyawan (nama, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nama, $email, $password, $role]); // Simpan password plaintext
        $_SESSION['success'] = "Karyawan berhasil ditambahkan";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Gagal menambahkan karyawan: " . $e->getMessage();
    }
}

if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    try {
        $stmt = $pdo->prepare("DELETE FROM karyawan WHERE id = ?");
        $stmt->execute([$id]);
        $_SESSION['success'] = "Karyawan berhasil dihapus";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Gagal menghapus karyawan: " . $e->getMessage();
    }
    header('Location: kelola_karyawan.php');
    exit();
}



$stmt = $pdo->query("SELECT * FROM karyawan");
$karyawan = $stmt->fetchAll();
?>

<h2>Kelola Karyawan</h2>

<?php if (isset($_SESSION['success'])): ?>
    <div class="success"><?= $_SESSION['success'] ?></div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error'])): ?>
    <div class="error"><?= $_SESSION['error'] ?></div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<button onclick="toggleForm('tambahForm')">Tambah Karyawan</button>
<div id="tambahForm" style="display:none;">
    <form method="post">
        <div class="form-group">
            <label for="nama">Nama:</label>
            <input type="text" name="nama" required>
        </div>
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" name="email" required>
        </div>
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" name="password" required>
        </div>
        <div class="form-group">
            <label for="role">Role:</label>
            <select name="role" required>
                <option value="karyawan">Karyawan</option>
                <option value="admin">Admin</option>
            </select>
        </div>
        <button type="submit" name="tambah">Simpan</button>
    </form>
</div>

<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Nama</th>
            <th>Email</th>
            <th>Role</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($karyawan as $k): ?>
            <tr>
                <td><?= $k['id'] ?></td>
                <td><?= $k['nama'] ?></td>
                <td><?= $k['email'] ?></td>
                <td><?= $k['role'] ?></td>
                <td>
                    <a href="kelola_karyawan.php?hapus=<?= $k['id'] ?>" onclick="return confirm('Yakin ingin menghapus?')">Hapus</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<script>
function toggleForm(formId) {
    var form = document.getElementById(formId);
    if (form.style.display === "none") {
        form.style.display = "block";
    } else {
        form.style.display = "none";
    }
}
</script>

<?php include 'templates/footer.php'; ?>