<?php
include_once 'auth.php';
checkRole(['admin']);

include_once 'templates/header.php';

$message = '';

// Proses Tambah/Edit Karyawan
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nip = $_POST['nip'];
    $nama_karyawan = $_POST['nama_karyawan'];
    $posisi = $_POST['posisi'];
    $role = $_POST['role'];
    $id_karyawan = $_POST['id_karyawan'] ?? '';

    if (empty($id_karyawan)) { // Tambah Karyawan Baru
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO karyawan (nip, nama_karyawan, password, posisi, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $nip, $nama_karyawan, $password, $posisi, $role);
        if ($stmt->execute()) {
            $message = "<div class='message success'>Karyawan berhasil ditambahkan!</div>";
        } else {
            $message = "<div class='message error'>Gagal menambahkan karyawan: " . $stmt->error . "</div>";
        }
    } else { // Edit Karyawan
        $sql = "UPDATE karyawan SET nip = ?, nama_karyawan = ?, posisi = ?, role = ? WHERE id_karyawan = ?";
        if (!empty($_POST['password'])) { // Jika password diisi, update password
            $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
            $sql = "UPDATE karyawan SET nip = ?, nama_karyawan = ?, password = ?, posisi = ?, role = ? WHERE id_karyawan = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssssi", $nip, $nama_karyawan, $password, $posisi, $role, $id_karyawan);
        } else {
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssi", $nip, $nama_karyawan, $posisi, $role, $id_karyawan);
        }

        if ($stmt->execute()) {
            $message = "<div class='message success'>Data karyawan berhasil diperbarui!</div>";
        } else {
            $message = "<div class='message error'>Gagal memperbarui data karyawan: " . $stmt->error . "</div>";
        }
    }
    $stmt->close();
}

// Proses Hapus Karyawan
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $id_karyawan_to_delete = $_GET['id'];
    $stmt = $conn->prepare("DELETE FROM karyawan WHERE id_karyawan = ?");
    $stmt->bind_param("i", $id_karyawan_to_delete);
    if ($stmt->execute()) {
        $message = "<div class='message success'>Karyawan berhasil dihapus!</div>";
    } else {
        $message = "<div class='message error'>Gagal menghapus karyawan: " . $stmt->error . "</div>";
    }
    $stmt->close();
}

// Ambil data karyawan untuk ditampilkan
$result = $conn->query("SELECT id_karyawan, nip, nama_karyawan, posisi, role FROM karyawan ORDER BY nama_karyawan ASC");
?>

<h2>Kelola Karyawan</h2>

<?php echo $message; ?>

<div class="form-container" style="max-width: 600px;">
    <h3><?php echo (isset($_GET['action']) && $_GET['action'] == 'edit') ? 'Edit Karyawan' : 'Tambah Karyawan Baru'; ?></h3>
    <?php
    $edit_karyawan = null;
    if (isset($_GET['action']) && $_GET['action'] == 'edit' && isset($_GET['id'])) {
        $stmt_edit = $conn->prepare("SELECT id_karyawan, nip, nama_karyawan, posisi, role FROM karyawan WHERE id_karyawan = ?");
        $stmt_edit->bind_param("i", $_GET['id']);
        $stmt_edit->execute();
        $result_edit = $stmt_edit->get_result();
        if ($result_edit->num_rows == 1) {
            $edit_karyawan = $result_edit->fetch_assoc();
        }
        $stmt_edit->close();
    }
    ?>
    <form action="kelola_karyawan.php" method="POST">
        <input type="hidden" name="id_karyawan" value="<?php echo $edit_karyawan['id_karyawan'] ?? ''; ?>">
        <div class="form-group">
            <label for="nip">NIP:</label>
            <input type="text" id="nip" name="nip" value="<?php echo $edit_karyawan['nip'] ?? ''; ?>" required>
        </div>
        <div class="form-group">
            <label for="nama_karyawan">Nama Karyawan:</label>
            <input type="text" id="nama_karyawan" name="nama_karyawan" value="<?php echo $edit_karyawan['nama_karyawan'] ?? ''; ?>" required>
        </div>
        <div class="form-group">
            <label for="password">Kata Sandi: <?php echo (isset($edit_karyawan)) ? '(Biarkan kosong jika tidak ingin diubah)' : ''; ?></label>
            <input type="password" id="password" name="password" <?php echo (isset($edit_karyawan)) ? '' : 'required'; ?>>
        </div>
        <div class="form-group">
            <label for="posisi">Posisi:</label>
            <input type="text" id="posisi" name="posisi" value="<?php echo $edit_karyawan['posisi'] ?? ''; ?>" required>
        </div>
        <div class="form-group">
            <label for="role">Peran:</label>
            <select name="role" id="role" required>
                <option value="karyawan" <?php echo (isset($edit_karyawan) && $edit_karyawan['role'] == 'karyawan') ? 'selected' : ''; ?>>Karyawan</option>
                <option value="admin" <?php echo (isset($edit_karyawan) && $edit_karyawan['role'] == 'admin') ? 'selected' : ''; ?>>Admin</option>
            </select>
        </div>
        <button type="submit"><?php echo (isset($edit_karyawan)) ? 'Perbarui Karyawan' : 'Tambah Karyawan'; ?></button>
        <?php if (isset($edit_karyawan)): ?>
            <a href="kelola_karyawan.php" class="button" style="background-color: #6c757d; display: block; text-align: center; margin-top: 10px; text-decoration: none;">Batal Edit</a>
        <?php endif; ?>
    </form>
</div>

<h3>Daftar Karyawan</h3>
<?php if ($result->num_rows > 0): ?>
    <table>
        <thead>
            <tr>
                <th>NIP</th>
                <th>Nama Karyawan</th>
                <th>Posisi</th>
                <th>Peran</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['nip']); ?></td>
                    <td><?php echo htmlspecialchars($row['nama_karyawan']); ?></td>
                    <td><?php echo htmlspecialchars($row['posisi']); ?></td>
                    <td><?php echo htmlspecialchars($row['role']); ?></td>
                    <td class="action-buttons">
                        <a href="kelola_karyawan.php?action=edit&id=<?php echo $row['id_karyawan']; ?>" class="button edit">Edit</a>
                        <a href="kelola_karyawan.php?action=delete&id=<?php echo $row['id_karyawan']; ?>" class="button delete" onclick="return confirm('Yakin ingin menghapus karyawan ini?');">Hapus</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php else: ?>
    <p>Belum ada data karyawan.</p>
<?php endif; ?>

<?php
include_once 'templates/footer.php';
$conn->close();
?>