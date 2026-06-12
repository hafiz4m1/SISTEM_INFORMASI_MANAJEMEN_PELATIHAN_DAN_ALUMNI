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

    // Update/insert data alumni (peserta menggunakan tabel alumni untuk data pribadi)
    $peserta_id = mysqli_fetch_row(mysqli_query($koneksi,
        "SELECT id FROM alumni WHERE user_id={$_SESSION['id_login']}"))[0] ?? 0;

    if ($peserta_id > 0) {
        mysqli_query($koneksi, "UPDATE alumni SET
            nik='$nik', tempat_lahir='$tempat_lahir', tanggal_lahir='$tgl_lahir',
            jenis_kelamin='$jk', alamat='$alamat', telepon='$telepon'
            WHERE id=$peserta_id");
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

$uid = $_SESSION['id_login'];
$pesan = isset($_GET['pesan']) ? $_GET['pesan'] : '';

// Ambil data terbaru
$user   = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM users WHERE id=$uid"));
$peserta = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM alumni WHERE user_id=$uid"));

// Hitung statistik peserta
$jml_pelatihan = mysqli_fetch_row(mysqli_query($koneksi,
    "SELECT COUNT(*) FROM peserta_pelatihan WHERE user_id=$uid"))[0];
$jml_lulus = mysqli_fetch_row(mysqli_query($koneksi,
    "SELECT COUNT(*) FROM peserta_pelatihan WHERE user_id=$uid AND status_lulus='lulus'"))[0];
$jml_sertifikat = mysqli_fetch_row(mysqli_query($koneksi,
    "SELECT COUNT(*) FROM peserta_pelatihan WHERE user_id=$uid AND sertifikat_url IS NOT NULL"))[0];
?>

<?php if ($pesan): ?>
  <div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($pesan) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="row g-3">
  <!-- Avatar & info singkat -->
  <div class="col-lg-3">
    <div class="card p-4 text-center">
      <div class="mb-3">
        <div style="width:80px;height:80px;background:#e3f2fd;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto;font-size:34px;color:#1976d2">
          <i class="bi bi-person"></i>
        </div>
      </div>
      <h6 class="fw-bold mb-1"><?= htmlspecialchars($user['name']) ?></h6>
      <small class="text-muted"><?= htmlspecialchars($user['email']) ?></small>
      <hr>
      <span class="badge bg-primary">Peserta</span>
      <div style="margin-top:12px;border-top:1px solid #e5e9f0;padding-top:12px">
        <small class="text-muted d-block">Pelatihan Diikuti</small>
        <strong style="font-size:20px;color:#1976d2"><?= $jml_pelatihan ?></strong>
      </div>
      <div style="margin-top:12px;border-top:1px solid #e5e9f0;padding-top:12px">
        <small class="text-muted d-block">Lulus</small>
        <strong style="font-size:20px;color:#43a047"><?= $jml_lulus ?></strong>
      </div>
      <div style="margin-top:12px;border-top:1px solid #e5e9f0;padding-top:12px">
        <small class="text-muted d-block">Sertifikat</small>
        <strong style="font-size:20px;color:#fb8c00"><?= $jml_sertifikat ?></strong>
      </div>
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
              <input type="text" name="nik" class="form-control" maxlength="16" value="<?= htmlspecialchars($peserta['nik'] ?? '') ?>">
            </div>
            <div class="col-md-5">
              <label class="form-label fw-semibold">Tempat Lahir</label>
              <input type="text" name="tempat_lahir" class="form-control" value="<?= htmlspecialchars($peserta['tempat_lahir'] ?? '') ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Tanggal Lahir</label>
              <input type="date" name="tanggal_lahir" class="form-control" value="<?= $peserta['tanggal_lahir'] ?? '' ?>">
            </div>
            <div class="col-md-3">
              <label class="form-label fw-semibold">Jenis Kelamin</label>
              <select name="jenis_kelamin" class="form-select">
                <option value="">-- Pilih --</option>
                <option value="L" <?= ($peserta['jenis_kelamin'] ?? '') === 'L' ? 'selected' : '' ?>>Laki-laki</option>
                <option value="P" <?= ($peserta['jenis_kelamin'] ?? '') === 'P' ? 'selected' : '' ?>>Perempuan</option>
              </select>
            </div>
            <div class="col-md-8">
              <label class="form-label fw-semibold">Alamat</label>
              <textarea name="alamat" class="form-control" rows="2"><?= htmlspecialchars($peserta['alamat'] ?? '') ?></textarea>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Nomor Telepon</label>
              <input type="tel" name="telepon" class="form-control" value="<?= htmlspecialchars($peserta['telepon'] ?? '') ?>">
            </div>
            <div class="col-12">
              <button type="submit" class="btn btn-primary px-4"><i class="bi bi-check-circle"></i> Simpan Perubahan</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include 'footer.php'; ?>
