<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit();
}

include 'config/database.php';
include 'templates/header.php';

// Tambah karyawan
if (isset($_POST['tambah'])) {
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role'];

    try {
        $stmt = $pdo->prepare("INSERT INTO karyawan (nama, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nama, $email, $password, $role]);
        $_SESSION['success'] = "Karyawan berhasil ditambahkan";
    } catch (PDOException $e) {
        $_SESSION['error'] = "Gagal menambahkan karyawan: " . $e->getMessage();
    }
}

// Hapus karyawan
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

// Ambil data karyawan
$stmt = $pdo->query("SELECT * FROM karyawan");
$karyawan = $stmt->fetchAll();
?>

<style>
    /* Kelola Karyawan Styles */
    .manage-employee {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px;
    }
    
    .page-title {
        font-size: 28px;
        color: #2c3e50;
        margin-bottom: 25px;
        padding-bottom: 15px;
        border-bottom: 2px solid #4361ee;
        display: flex;
        align-items: center;
        gap: 15px;
    }
    
    .page-title i {
        color: #4361ee;
        font-size: 32px;
    }
    
    .alert {
        padding: 15px;
        border-radius: 8px;
        margin-bottom: 25px;
        font-size: 16px;
    }
    
    .alert-success {
        background: #d4edda;
        color: #155724;
        border-left: 4px solid #28a745;
    }
    
    .alert-error {
        background: #f8d7da;
        color: #721c24;
        border-left: 4px solid #dc3545;
    }
    
    .add-employee-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 25px;
        background: linear-gradient(135deg, #4361ee, #3f37c9);
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        font-size: 16px;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-bottom: 20px;
    }
    
    .add-employee-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 15px rgba(67, 97, 238, 0.4);
    }
    
    .add-employee-form {
        background: white;
        border-radius: 12px;
        padding: 30px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        margin-bottom: 30px;
        display: none;
        animation: fadeIn 0.4s ease-out;
    }
    
    .form-title {
        font-size: 22px;
        color: #2c3e50;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .form-title i {
        color: #4361ee;
    }
    
    .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 600;
        color: #555;
    }
    
    .form-control {
        width: 100%;
        padding: 14px;
        border: 2px solid #e0e8ff;
        border-radius: 8px;
        font-size: 16px;
        transition: all 0.3s ease;
    }
    
    .form-control:focus {
        border-color: #4361ee;
        outline: none;
        box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
    }
    
    .submit-btn {
        background: linear-gradient(135deg, #4361ee, #3f37c9);
        color: white;
        border: none;
        padding: 14px 25px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 16px;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-top: 10px;
    }
    
    .submit-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 15px rgba(67, 97, 238, 0.4);
    }
    
    .employee-table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }
    
    .employee-table thead {
        background: linear-gradient(135deg, #4361ee, #3f37c9);
        color: white;
    }
    
    .employee-table th {
        padding: 18px 15px;
        text-align: left;
        font-weight: 600;
        font-size: 16px;
    }
    
    .employee-table td {
        padding: 16px 15px;
        border-bottom: 1px solid #eef2f7;
        color: #555;
    }
    
    .employee-table tbody tr:last-child td {
        border-bottom: none;
    }
    
    .employee-table tbody tr:hover {
        background: #f8fbff;
    }
    
    .role-badge {
        display: inline-block;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 14px;
        font-weight: 500;
    }
    
    .badge-admin {
        background: #e0f7fa;
        color: #0097a7;
    }
    
    .badge-karyawan {
        background: #e8f5e9;
        color: #388e3c;
    }
    
    .action-buttons {
        display: flex;
        gap: 10px;
    }
    
    .delete-btn {
        background: #ffebee;
        color: #c62828;
        border: none;
        padding: 8px 15px;
        border-radius: 6px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    
    .delete-btn:hover {
        background: #ffcdd2;
        transform: translateY(-2px);
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    @media (max-width: 768px) {
        .employee-table {
            display: block;
            overflow-x: auto;
        }
        
        .page-title {
            font-size: 24px;
        }
        
        .form-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<div class="manage-employee">
    <h1 class="page-title">
        <i class="fas fa-users-cog"></i>
        Kelola Data Karyawan
    </h1>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i> <?= $_SESSION['success'] ?>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i> <?= $_SESSION['error'] ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    
    <button class="add-employee-btn" onclick="toggleForm('tambahForm')">
        <i class="fas fa-user-plus"></i> Tambah Karyawan Baru
    </button>
    
    <div id="tambahForm" class="add-employee-form">
        <h3 class="form-title">
            <i class="fas fa-user-edit"></i> Form Tambah Karyawan
        </h3>
        <form method="post">
            <div class="form-grid">
                <div class="form-group">
                    <label for="nama">Nama Lengkap</label>
                    <input type="text" id="nama" name="nama" class="form-control" placeholder="Masukkan nama lengkap" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="Masukkan email" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Buat password" required>
                </div>
                
                <div class="form-group">
                    <label for="role">Peran (Role)</label>
                    <select id="role" name="role" class="form-control" required>
                        <option value="karyawan">Karyawan</option>
                        <option value="admin">Administrator</option>
                    </select>
                </div>
            </div>
            <button type="submit" name="tambah" class="submit-btn">
                <i class="fas fa-save"></i> Simpan Data Karyawan
            </button>
        </form>
    </div>
    
    <div class="table-responsive">
        <table class="employee-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Peran</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($karyawan as $k): ?>
                    <tr>
                        <td><?= $k['id'] ?></td>
                        <td><?= htmlspecialchars($k['nama']) ?></td>
                        <td><?= htmlspecialchars($k['email']) ?></td>
                        <td>
                            <span class="role-badge <?= $k['role'] === 'admin' ? 'badge-admin' : 'badge-karyawan' ?>">
                                <?= $k['role'] === 'admin' ? 'Administrator' : 'Karyawan' ?>
                            </span>
                        </td>
                        <td>
                            <div class="action-buttons">
                                <a href="kelola_karyawan.php?hapus=<?= $k['id'] ?>" class="delete-btn" onclick="return confirm('Apakah Anda yakin ingin menghapus karyawan ini?')">
                                    <i class="fas fa-trash-alt"></i> Hapus
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function toggleForm(formId) {
    var form = document.getElementById(formId);
    if (form.style.display === "none" || form.style.display === "") {
        form.style.display = "block";
        // Scroll ke form
        form.scrollIntoView({ behavior: 'smooth', block: 'start' });
    } else {
        form.style.display = "none";
    }
}
</script>

<?php include 'templates/footer.php'; ?>