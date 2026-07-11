<?php
ob_start();
$page_title = 'Edit Alumni';
include_once '../koneksi.php';
include_once '../security.php';
include 'header.php';

$id = (int)($_GET['id'] ?? 0);
$data = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT a.*, u.name, u.email, u.is_active FROM alumni a JOIN users u ON a.user_id=u.id WHERE a.id=$id"));
if (!$data) {
    echo '<div class="alert alert-danger">Data tidak ditemukan.</div>';
    include 'footer.php';
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name          = mysqli_real_escape_string($koneksi, $_POST['name']);
    $email         = mysqli_real_escape_string($koneksi, $_POST['email']);
    $nik           = mysqli_real_escape_string($koneksi, $_POST['nik']);
    $tempat_lahir  = mysqli_real_escape_string($koneksi, $_POST['tempat_lahir']);
    $tanggal_lahir = mysqli_real_escape_string($koneksi, $_POST['tanggal_lahir']);
    $jenis_kelamin = mysqli_real_escape_string($koneksi, $_POST['jenis_kelamin']);
    $alamat        = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    $telepon       = mysqli_real_escape_string($koneksi, $_POST['telepon']);
    $tanggal_lulus = mysqli_real_escape_string($koneksi, $_POST['tanggal_lulus']);

    if (!$name)  $errors[] = 'Nama wajib diisi.';
    if (!$email) $errors[] = 'Email wajib diisi.';
    if ($jenis_kelamin && !in_array($jenis_kelamin, ['L','P'])) $errors[] = 'Jenis kelamin tidak valid.';

    $cek = mysqli_fetch_row(mysqli_query($koneksi,
        "SELECT COUNT(*) FROM users WHERE email='$email' AND id != {$data['user_id']}"))[0];
    if ($cek > 0) $errors[] = 'Email sudah terdaftar oleh user lain.';

    if (!$errors) {
        mysqli_query($koneksi, "UPDATE users SET name='$name', email='$email' WHERE id={$data['user_id']}");
        mysqli_query($koneksi, "UPDATE alumni SET nik='$nik', tempat_lahir='$tempat_lahir', tanggal_lahir='$tanggal_lahir', jenis_kelamin='$jenis_kelamin', alamat='$alamat', telepon='$telepon', tanggal_lulus='$tanggal_lulus' WHERE id=$id");
        ob_end_clean();
        header("Location: alumni.php?pesan=Data alumni berhasil diperbarui.");
        exit;
    }
    $data = array_merge($data, $_POST);
}
?>

<div class="d-flex align-items-center gap-2 mb-3">
  <a href="alumni.php" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
  <h6 class="mb-0 fw-semibold">Edit Alumni</h6>
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
          <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($data['name']) ?>" required>
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Email</label>
          <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($data['email']) ?>" required>
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">NIK</label>
          <input type="text" name="nik" class="form-control" value="<?= htmlspecialchars($data['nik'] ?? '') ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Tempat Lahir</label>
          <input type="text" name="tempat_lahir" class="form-control" value="<?= htmlspecialchars($data['tempat_lahir'] ?? '') ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Tanggal Lahir</label>
          <input type="date" name="tanggal_lahir" class="form-control" value="<?= htmlspecialchars($data['tanggal_lahir'] ?? '') ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Jenis Kelamin</label>
          <select name="jenis_kelamin" class="form-select">
            <option value="">-- Pilih --</option>
            <option value="L" <?= ($data['jenis_kelamin'] ?? '') === 'L' ? 'selected' : '' ?>>Laki-laki</option>
            <option value="P" <?= ($data['jenis_kelamin'] ?? '') === 'P' ? 'selected' : '' ?>>Perempuan</option>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">No. Telepon</label>
          <input type="text" name="telepon" class="form-control" value="<?= htmlspecialchars($data['telepon'] ?? '') ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Tanggal Lulus</label>
          <input type="date" name="tanggal_lulus" class="form-control" value="<?= htmlspecialchars($data['tanggal_lulus'] ?? '') ?>">
        </div>
        <div class="col-12">
          <label class="form-label fw-semibold">Alamat</label>
          <textarea name="alamat" class="form-control" rows="3"><?= htmlspecialchars($data['alamat'] ?? '') ?></textarea>
        </div>
        <div class="col-12 d-flex gap-2">
          <button type="submit" class="btn btn-primary px-4">Simpan Perubahan</button>
          <a href="alumni.php" class="btn btn-outline-secondary">Batal</a>
        </div>
      </div>
    </form>
  </div>
</div>

<?php include 'footer.php'; ?>