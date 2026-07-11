<?php
ob_start();
$page_title = 'Edit Kepala Balai';
include_once '../koneksi.php';
include_once '../security.php';
include 'header.php';

$id = (int)($_GET['id'] ?? 0);
$data = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT k.*, u.email FROM kepala k JOIN users u ON k.user_id=u.id WHERE k.id=$id"));
if (!$data) {
    echo '<div class="alert alert-danger">Data tidak ditemukan.</div>';
    include 'footer.php';
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama     = mysqli_real_escape_string($koneksi, $_POST['nama_lengkap']);
    $nip      = mysqli_real_escape_string($koneksi, $_POST['nip']);
    $jabatan  = mysqli_real_escape_string($koneksi, $_POST['jabatan']);
    $pangkat  = mysqli_real_escape_string($koneksi, $_POST['pangkat']);
    $golongan = mysqli_real_escape_string($koneksi, $_POST['golongan']);
    $mulai    = mysqli_real_escape_string($koneksi, $_POST['mulai_jabatan']);
    $email    = mysqli_real_escape_string($koneksi, $_POST['email']);

    if (!$nama)  $errors[] = 'Nama lengkap wajib diisi.';
    if (!$email) $errors[] = 'Email wajib diisi.';

    $cek = mysqli_fetch_row(mysqli_query($koneksi,
        "SELECT COUNT(*) FROM users WHERE email='$email' AND id != {$data['user_id']}"))[0];
    if ($cek > 0) $errors[] = 'Email sudah terdaftar oleh user lain.';

    if (!$errors) {
        mysqli_query($koneksi, "UPDATE users SET name='$nama', email='$email' WHERE id={$data['user_id']}");
        mysqli_query($koneksi, "UPDATE kepala SET nip='$nip', nama_lengkap='$nama', jabatan='$jabatan', pangkat='$pangkat', golongan='$golongan', mulai_jabatan='$mulai' WHERE id=$id");
        ob_end_clean();
        header("Location: kepala.php?pesan=Data kepala berhasil diperbarui.");
        exit;
    }
    $data = array_merge($data, $_POST);
}
?>

<div class="d-flex align-items-center gap-2 mb-3">
  <a href="kepala.php" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
  <h6 class="mb-0 fw-semibold">Edit Kepala Balai</h6>
</div>

<?php if ($errors): ?>
  <div class="alert alert-danger"><ul class="mb-0 ps-3"><?php foreach($errors as $e) echo "<li>$e</li>"; ?></ul></div>
<?php endif; ?>

<div class="card">
  <div class="card-body p-4">
    <form method="POST">
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label fw-semibold">Nama Lengkap</label>
          <input type="text" name="nama_lengkap" class="form-control" value="<?= htmlspecialchars($data['nama_lengkap']) ?>" required>
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Email</label>
          <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($data['email']) ?>" required>
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">NIP</label>
          <input type="text" name="nip" class="form-control" value="<?= htmlspecialchars($data['nip'] ?? '') ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Pangkat</label>
          <input type="text" name="pangkat" class="form-control" value="<?= htmlspecialchars($data['pangkat'] ?? '') ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Golongan</label>
          <input type="text" name="golongan" class="form-control" value="<?= htmlspecialchars($data['golongan'] ?? '') ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Jabatan</label>
          <input type="text" name="jabatan" class="form-control" value="<?= htmlspecialchars($data['jabatan'] ?? '') ?>">
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Mulai Menjabat</label>
          <input type="date" name="mulai_jabatan" class="form-control" value="<?= htmlspecialchars($data['mulai_jabatan'] ?? date('Y-m-d')) ?>">
        </div>
        <div class="col-12 d-flex gap-2">
          <button type="submit" class="btn btn-primary px-4">Simpan Perubahan</button>
          <a href="kepala.php" class="btn btn-outline-secondary">Batal</a>
        </div>
      </div>
    </form>
  </div>
</div>

<?php ob_end_flush(); include 'footer.php'; ?>