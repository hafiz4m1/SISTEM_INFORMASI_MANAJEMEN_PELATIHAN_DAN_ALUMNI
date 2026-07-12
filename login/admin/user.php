<?php
$page_title = 'Manajemen User';
include '../koneksi.php';

$pesan  = isset($_GET['pesan']) ? $_GET['pesan'] : '';
$errors = [];

// Hapus user - HARUS SEBELUM include header.php
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    if (isset($_SESSION['id_login']) && $id == $_SESSION['id_login']) {
        $pesan = 'Tidak bisa menghapus akun sendiri.';
    } else {
        // Disable foreign key checks sementara
        mysqli_query($koneksi, "SET FOREIGN_KEY_CHECKS=0");
        
        // Hapus data terkait dalam urutan yang tepat
        mysqli_query($koneksi, "DELETE FROM tracer_study WHERE alumni_id IN (SELECT id FROM alumni WHERE user_id=$id)");
        mysqli_query($koneksi, "DELETE FROM alumni WHERE user_id=$id");
        mysqli_query($koneksi, "DELETE FROM peserta_pelatihan WHERE user_id=$id");
        mysqli_query($koneksi, "DELETE FROM instruktur WHERE user_id=$id");
        
        // Hapus user
        $result       = mysqli_query($koneksi, "DELETE FROM users WHERE id=$id");
        $affected     = mysqli_affected_rows($koneksi); // simpan SEBELUM query lain
        $delete_error = mysqli_error($koneksi);

        // Enable foreign key checks kembali
        mysqli_query($koneksi, "SET FOREIGN_KEY_CHECKS=1");
        
        if ($result && $affected > 0) {
            header("Location: user.php?pesan=User berhasil dihapus."); 
            exit;
        } else {
            $pesan = 'Gagal menghapus user: ' . $delete_error;
        }
    }
}

// Toggle aktif - HARUS SEBELUM include header.php
if (isset($_GET['toggle'])) {
    $id  = (int)$_GET['toggle'];
    $cur = mysqli_fetch_row(mysqli_query($koneksi, "SELECT is_active FROM users WHERE id=$id"))[0];
    $new = $cur ? 0 : 1;
    mysqli_query($koneksi, "UPDATE users SET is_active=$new WHERE id=$id");
    header("Location: user.php?pesan=Status user berhasil diubah."); 
    exit;
}

// Tambah user - HARUS SEBELUM include header.php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah'])) {
    $name  = mysqli_real_escape_string($koneksi, $_POST['name']);
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    $pass  = $_POST['password'];
    $role  = mysqli_real_escape_string($koneksi, $_POST['role']);

    if (!$name)  $errors[] = 'Nama wajib diisi.';
    if (!$email) $errors[] = 'Email wajib diisi.';
    if (!$pass || strlen($pass) < 6) $errors[] = 'Password minimal 6 karakter.';
    if (!in_array($role, ['admin','instruktur','alumni','kepala','peserta'])) $errors[] = 'Role tidak valid.';

    $cek = mysqli_fetch_row(mysqli_query($koneksi, "SELECT COUNT(*) FROM users WHERE email='$email'"))[0];
    if ($cek > 0) $errors[] = 'Email sudah terdaftar.';

    if (!$errors) {
        $hash = password_hash($pass, PASSWORD_BCRYPT);
        $result = mysqli_query($koneksi, "INSERT INTO users (name, email, password, role) VALUES ('$name','$email','$hash','$role')");
        if ($result) {
            header("Location: user.php?pesan=User berhasil ditambahkan."); 
            exit;
        } else {
            $errors[] = 'Gagal menambahkan user. Coba lagi.';
        }
    }
}

// Sekarang baru include header SETELAH semua redirect selesai
include 'header.php';

$search = isset($_GET['q']) ? mysqli_real_escape_string($koneksi, $_GET['q']) : '';
$where  = $search ? "WHERE name LIKE '%$search%' OR email LIKE '%$search%'" : '';
$users  = mysqli_query($koneksi, "SELECT * FROM users $where ORDER BY created_at DESC");
?>

<?php if ($pesan): ?>
  <div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($pesan) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>
<?php if ($errors): ?>
  <div class="alert alert-danger"><ul class="mb-0 ps-3"><?php foreach($errors as $e) echo "<li>$e</li>"; ?></ul></div>
<?php endif; ?>

<div class="row g-3">
  <!-- Form Tambah -->
  <div class="col-lg-4">
    <div class="card">
      <div class="card-header">Tambah User Baru</div>
      <div class="card-body p-4">
        <form method="POST">
          <div class="mb-3">
            <label class="form-label fw-semibold">Nama Lengkap</label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Email</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Password</label>
            <input type="password" name="password" class="form-control" placeholder="Min. 6 karakter">
          </div>
          <div class="mb-4">
            <label class="form-label fw-semibold">Role</label>
            <select name="role" class="form-select">
              <option value="peserta">Peserta</option>
              <option value="alumni">Alumni</option>
              <option value="instruktur">Instruktur</option>
              <option value="admin">Admin</option>
              <option value="kepala">Kepala</option>
            </select>
          </div>
          <button type="submit" name="tambah" class="btn btn-primary w-100">Tambah User</button>
        </form>
      </div>
    </div>
  </div>

  <!-- Tabel User -->
  <div class="col-lg-8">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <span>Daftar User</span>
        <form class="d-flex" method="GET">
          <input type="search" name="q" class="form-control form-control-sm" placeholder="Cari nama / email..." value="<?= htmlspecialchars($search) ?>">
          <button class="btn btn-sm btn-outline-secondary ms-1">Cari</button>
        </form>
      </div>
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead><tr><th>No</th><th>Nama</th><th>Email</th><th>Role</th><th>Status</th><th>Aksi</th></tr></thead>
          <tbody>
          <?php $no = 1; while ($row = mysqli_fetch_assoc($users)): ?>
            <tr>
              <td><?= $no++ ?></td>
              <td><?= htmlspecialchars($row['name']) ?></td>
              <td><small><?= htmlspecialchars($row['email']) ?></small></td>
              <td>
                <?php $rc=['admin'=>'danger','instruktur'=>'info','alumni'=>'success','peserta'=>'secondary']; ?>
                <span class="badge bg-<?= $rc[$row['role']] ?? 'secondary' ?>"><?= ucfirst($row['role']) ?></span>
              </td>
              <td>
                <a href="user.php?toggle=<?= $row['id'] ?>" class="badge <?= $row['is_active'] ? 'bg-success' : 'bg-secondary' ?> text-decoration-none">
                  <?= $row['is_active'] ? 'Aktif' : 'Nonaktif' ?>
                </a>
              </td>
              <td>
                <?php if (isset($_SESSION['id_login']) && $row['id'] != $_SESSION['id_login']): ?>
                  <a href="user.php?hapus=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger"
                     onclick="return konfirmasiHapus('user.php?hapus=<?= $row['id'] ?>', '<?= htmlspecialchars($row['name']) ?>')"><i class="bi bi-trash"></i></a>
                <?php else: ?>
                  <span class="text-muted" style="font-size:12px">Anda</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php include 'footer.php'; ?>