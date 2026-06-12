<?php
ob_start();
$page_title = 'Profil Saya';
include '../koneksi.php';
include 'header.php';

// Process form setelah session tersedia
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nik          = mysqli_real_escape_string($koneksi, $_POST['nik']);
    $tempat_lahir = mysqli_real_escape_string($koneksi, $_POST['tempat_lahir']);
    $tgl_lahir    = $_POST['tanggal_lahir'];
    $jk           = $_POST['jenis_kelamin'];
    $alamat       = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    $telepon      = mysqli_real_escape_string($koneksi, $_POST['telepon']);
    $nama         = mysqli_real_escape_string($koneksi, $_POST['name']);

    // Update/insert data alumni
    $alumni_id = mysqli_fetch_row(mysqli_query($koneksi,
        "SELECT id FROM alumni WHERE user_id={$_SESSION['id_login']}"))[0] ?? 0;

    if ($alumni_id > 0) {
        mysqli_query($koneksi, "UPDATE alumni SET
            nik='$nik', tempat_lahir='$tempat_lahir', tanggal_lahir='$tgl_lahir',
            jenis_kelamin='$jk', alamat='$alamat', telepon='$telepon'
            WHERE id=$alumni_id");
    } else {
        // Buat record alumni jika belum ada
        mysqli_query($koneksi, "INSERT INTO alumni (user_id, nik, tempat_lahir, tanggal_lahir, jenis_kelamin, alamat, telepon)
            VALUES ({$_SESSION['id_login']},'$nik','$tempat_lahir','$tgl_lahir','$jk','$alamat','$telepon')");
    }

    // Update nama di tabel users
    mysqli_query($koneksi, "UPDATE users SET name='$nama' WHERE id={$_SESSION['id_login']}");
    $_SESSION['nama'] = $nama;

    ob_end_clean();
    header("Location: profile.php?pesan=Profil berhasil diperbarui."); exit;
}

$pesan  = isset($_GET['pesan']) ? $_GET['pesan'] : '';
$errors = [];

// Ambil data terbaru
$user   = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM users WHERE id={$_SESSION['id_login']}"));
$alumni = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM alumni WHERE user_id={$_SESSION['id_login']}"));
?>

<?php if ($pesan): ?>
  <div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($pesan) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="row g-3">
  <!-- Avatar & info singkat -->
  <div class="col-lg-3">
    <div class="card p-4 text-center">
      <div class="mb-3">
        <div style="width:80px;height:80px;background:#e8f5e9;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto;font-size:34px;color:#134e35">
          <i class="bi bi-person"></i>
        </div>
      </div>
      <h6 class="fw-bold mb-1"><?= htmlspecialchars($user['name']) ?></h6>
      <small class="text-muted"><?= htmlspecialchars($user['email']) ?></small>
      <hr>
      <span class="badge bg-success">Alumni</span>
      <?php if ($alumni && $alumni['tanggal_lulus']): ?>
        <div class="mt-2" style="font-size:12px;color:#6b7280">Lulus: <?= date('d M Y', strtotime($alumni['tanggal_lulus'])) ?></div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Form Edit -->
  <div class="col-lg-9">
    <div class="card">
      <div class="card-header">Edit Profil</div>
      <div class="card-body p-4">
        <form method="POST">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Nama Lengkap</label>
              <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">NIK</label>
              <input type="text" name="nik" class="form-control" maxlength="16" value="<?= htmlspecialchars($alumni['nik'] ?? '') ?>">
            </div>
            <div class="col-md-5">
              <label class="form-label fw-semibold">Tempat Lahir</label>
              <input type="text" name="tempat_lahir" class="form-control" value="<?= htmlspecialchars($alumni['tempat_lahir'] ?? '') ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Tanggal Lahir</label>
              <input type="date" name="tanggal_lahir" class="form-control" value="<?= $alumni['tanggal_lahir'] ?? '' ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Jenis Kelamin</label>
              <select name="jenis_kelamin" class="form-select">
                <option value="">-- Pilih --</option>
                <option value="L" <?= ($alumni['jenis_kelamin'] ?? '') === 'L' ? 'selected' : '' ?>>Laki-laki</option>
                <option value="P" <?= ($alumni['jenis_kelamin'] ?? '') === 'P' ? 'selected' : '' ?>>Perempuan</option>
              </select>
            </div>
            <div class="col-md-8">
              <label class="form-label fw-semibold">Alamat</label>
              <textarea name="alamat" class="form-control" rows="2"><?= htmlspecialchars($alumni['alamat'] ?? '') ?></textarea>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">No. Telepon</label>
              <input type="text" name="telepon" class="form-control" value="<?= htmlspecialchars($alumni['telepon'] ?? '') ?>">
            </div>
            <div class="col-12">
              <button type="submit" class="btn btn-success px-4">Simpan Perubahan</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include 'footer.php'; ?>
