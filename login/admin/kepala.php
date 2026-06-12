<?php
$page_title = 'Manajemen Kepala Balai';
include 'header.php';

$pesan  = isset($_GET['pesan']) ? $_GET['pesan'] : '';
$errors = [];

// Aktifkan kepala baru (nonaktifkan yang lama otomatis via trigger)
if (isset($_GET['aktifkan'])) {
    $id = (int)$_GET['aktifkan'];
    mysqli_query($koneksi, "UPDATE kepala SET is_aktif=1, mulai_jabatan=CURDATE() WHERE id=$id");
    header("location: kepala.php?pesan=Kepala berhasil diaktifkan. Data laporan akan otomatis menggunakan kepala baru."); exit;
}

// Nonaktifkan kepala
if (isset($_GET['nonaktifkan'])) {
    $id = (int)$_GET['nonaktifkan'];
    mysqli_query($koneksi, "UPDATE kepala SET is_aktif=0, selesai_jabatan=CURDATE() WHERE id=$id");
    header("location: kepala.php?pesan=Kepala berhasil dinonaktifkan."); exit;
}

// Tambah kepala baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah'])) {
    $nama     = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);
    $nip      = mysqli_real_escape_string($koneksi, $_POST['nip']);
    $jabatan  = mysqli_real_escape_string($koneksi, $_POST['jabatan']);
    $pangkat  = mysqli_real_escape_string($koneksi, $_POST['pangkat']);
    $golongan = mysqli_real_escape_string($koneksi, $_POST['golongan']);
    $email    = mysqli_real_escape_string($koneksi, $_POST['email']);
    $mulai    = $_POST['mulai_jabatan'];

    if (!$nama)  $errors[] = 'Nama lengkap wajib diisi.';
    if (!$email) $errors[] = 'Email wajib diisi.';

    // Cek email duplikat
    $cek = mysqli_fetch_row(mysqli_query($koneksi,
        "SELECT COUNT(*) FROM users WHERE email='$email'"))[0];
    if ($cek > 0) $errors[] = 'Email sudah terdaftar.';

    if (!$errors) {
        // Buat user dengan role kepala
        $hash = password_hash('kepala123', PASSWORD_BCRYPT);
        mysqli_query($koneksi, "INSERT INTO users (name, email, password, role, is_active)
            VALUES ('$nama', '$email', '$hash', 'kepala', 1)");
        $uid = mysqli_insert_id($koneksi);

        // Insert ke tabel kepala (nonaktif dulu)
        mysqli_query($koneksi, "INSERT INTO kepala
            (user_id, nip, nama_lengkap, jabatan, pangkat, golongan, mulai_jabatan, is_aktif)
            VALUES ($uid, '$nip', '$nama', '$jabatan', '$pangkat', '$golongan', '$mulai', 0)");

        header("location: kepala.php?pesan=Data kepala berhasil ditambahkan. Klik Aktifkan untuk menjadikan kepala aktif."); exit;
    }
}

// Ambil semua data kepala
$data = mysqli_query($koneksi, "
    SELECT k.*, u.email, u.name
    FROM kepala k JOIN users u ON k.user_id=u.id
    ORDER BY k.is_aktif DESC, k.mulai_jabatan DESC
");
?>

<?php if ($pesan): ?>
  <div class="alert alert-success alert-dismissible fade show">
    <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($pesan) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>
<?php if ($errors): ?>
  <div class="alert alert-danger">
    <ul class="mb-0 ps-3"><?php foreach($errors as $e) echo "<li style='font-size:13px'>$e</li>"; ?></ul>
  </div>
<?php endif; ?>

<div class="alert alert-info py-2 mb-3" style="font-size:13px">
  <i class="bi bi-info-circle me-1"></i>
  Data kepala yang <strong>Aktif</strong> akan otomatis muncul sebagai pengesahan di semua laporan yang dicetak.
  Saat kepala baru diaktifkan, kepala lama otomatis dinonaktifkan.
</div>

<div class="row g-3">
  <!-- Form Tambah -->
  <div class="col-lg-4">
    <div class="card">
      <div class="card-header">Tambah Data Kepala</div>
      <div class="card-body p-4">
        <form method="POST">
          <div class="mb-3">
            <label class="form-label fw-semibold" style="font-size:13px">Nama Lengkap <span class="text-danger">*</span></label>
            <input type="text" name="nama_lengkap" class="form-control form-control-sm"
                   value="<?= htmlspecialchars($_POST['nama_lengkap'] ?? '') ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold" style="font-size:13px">Email <span class="text-danger">*</span></label>
            <input type="email" name="email" class="form-control form-control-sm"
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            <small class="text-muted">Password default: kepala123</small>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold" style="font-size:13px">NIP</label>
            <input type="text" name="nip" class="form-control form-control-sm" maxlength="30"
                   value="<?= htmlspecialchars($_POST['nip'] ?? '') ?>">
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold" style="font-size:13px">Jabatan</label>
            <input type="text" name="jabatan" class="form-control form-control-sm"
                   value="<?= htmlspecialchars($_POST['jabatan'] ?? 'Kepala Balai Pelatihan dan Pemberdayaan Masyarakat Desa') ?>">
          </div>
          <div class="mb-3">
            <div class="row g-2">
              <div class="col-6">
                <label class="form-label fw-semibold" style="font-size:13px">Pangkat</label>
                <input type="text" name="pangkat" class="form-control form-control-sm"
                       value="<?= htmlspecialchars($_POST['pangkat'] ?? '') ?>" placeholder="cth: Pembina">
              </div>
              <div class="col-6">
                <label class="form-label fw-semibold" style="font-size:13px">Golongan</label>
                <input type="text" name="golongan" class="form-control form-control-sm"
                       value="<?= htmlspecialchars($_POST['golongan'] ?? '') ?>" placeholder="cth: IV/a">
              </div>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold" style="font-size:13px">Mulai Menjabat</label>
            <input type="date" name="mulai_jabatan" class="form-control form-control-sm"
                   value="<?= $_POST['mulai_jabatan'] ?? date('Y-m-d') ?>">
          </div>
          <button type="submit" name="tambah" class="btn btn-primary w-100 btn-sm">
            <i class="bi bi-plus-lg me-1"></i> Tambah Kepala
          </button>
        </form>
      </div>
    </div>
  </div>

  <!-- Daftar Kepala -->
  <div class="col-lg-8">
    <div class="card">
      <div class="card-header">Daftar Kepala Balai</div>
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead>
            <tr><th>#</th><th>Nama Lengkap</th><th>NIP</th><th>Pangkat/Gol</th><th>Mulai</th><th>Selesai</th><th>Status</th><th>Aksi</th></tr>
          </thead>
          <tbody>
          <?php $no=1; while ($row = mysqli_fetch_assoc($data)): ?>
            <tr class="<?= $row['is_aktif'] ? 'table-success' : '' ?>">
              <td><?= $no++ ?></td>
              <td>
                <strong><?= htmlspecialchars($row['nama_lengkap']) ?></strong><br>
                <small class="text-muted"><?= htmlspecialchars($row['email']) ?></small>
              </td>
              <td><small><?= htmlspecialchars($row['nip'] ?? '-') ?></small></td>
              <td><small><?= htmlspecialchars($row['pangkat'] ?? '-') ?> / <?= htmlspecialchars($row['golongan'] ?? '-') ?></small></td>
              <td><?= $row['mulai_jabatan'] ? date('d M Y', strtotime($row['mulai_jabatan'])) : '-' ?></td>
              <td><?= $row['selesai_jabatan'] ? date('d M Y', strtotime($row['selesai_jabatan'])) : '<span class="text-muted">-</span>' ?></td>
              <td>
                <?php if ($row['is_aktif']): ?>
                  <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Aktif (Menjabat)</span>
                <?php else: ?>
                  <span class="badge bg-secondary">Tidak Aktif</span>
                <?php endif; ?>
              </td>
              <td>
                <?php if (!$row['is_aktif']): ?>
                  <a href="kepala.php?aktifkan=<?= $row['id'] ?>"
                     class="btn btn-sm btn-success"
                     onclick="return confirm('Aktifkan <?= htmlspecialchars($row['nama_lengkap']) ?> sebagai kepala? Kepala lama akan otomatis dinonaktifkan.')">
                    <i class="bi bi-person-check"></i> Aktifkan
                  </a>
                <?php else: ?>
                  <span class="text-muted" style="font-size:12px">Sedang Menjabat</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endwhile; ?>
          <?php if (mysqli_num_rows($data) === 0): ?>
            <tr><td colspan="8" class="text-center text-muted py-4">Belum ada data kepala</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php include 'footer.php'; ?>