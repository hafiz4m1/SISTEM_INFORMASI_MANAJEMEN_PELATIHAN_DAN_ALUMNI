<?php
ob_start();
$page_title = 'Profil Saya';
include '../koneksi.php';
include 'header.php';

// Process form setelah session tersedia
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bidang_keahlian = mysqli_real_escape_string($koneksi, $_POST['bidang_keahlian']);
    $pendidikan      = mysqli_real_escape_string($koneksi, $_POST['pendidikan']);
    $kontak          = mysqli_real_escape_string($koneksi, $_POST['kontak']);
    $nama            = mysqli_real_escape_string($koneksi, $_POST['name']);

    // Update data instruktur
    if ($instruktur_id > 0) {
        mysqli_query($koneksi, "UPDATE instruktur SET
            bidang_keahlian='$bidang_keahlian', pendidikan='$pendidikan', kontak='$kontak'
            WHERE id=$instruktur_id");
    } else {
        mysqli_query($koneksi, "INSERT INTO instruktur (user_id, bidang_keahlian, pendidikan, kontak)
            VALUES ({$_SESSION['id_login']},'$bidang_keahlian','$pendidikan','$kontak')");
    }

    // Update nama di tabel users
    mysqli_query($koneksi, "UPDATE users SET name='$nama' WHERE id={$_SESSION['id_login']}");
    $_SESSION['nama'] = $nama;

    ob_end_clean();
    header("Location: profile.php?pesan=Profil berhasil diperbarui."); exit;
}

$pesan  = isset($_GET['pesan']) ? $_GET['pesan'] : '';

// Ambil data terbaru
$user      = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM users WHERE id={$_SESSION['id_login']}"));
$instruktur_data = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM instruktur WHERE user_id={$_SESSION['id_login']}"));

// Hitung statistik
$jml_pelatihan = mysqli_fetch_row(mysqli_query($koneksi,
    "SELECT COUNT(*) FROM pelatihan WHERE instruktur_id=$instruktur_id"))[0];
$jml_peserta = mysqli_fetch_row(mysqli_query($koneksi,
    "SELECT COUNT(*) FROM peserta_pelatihan pp
     JOIN pelatihan p ON pp.pelatihan_id=p.id
     WHERE p.instruktur_id=$instruktur_id"))[0];
?>

<?php if ($pesan): ?>
  <div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($pesan) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="row g-3">
  <!-- Avatar & info singkat -->
  <div class="col-lg-3">
    <div class="card p-4 text-center">
      <div class="mb-3">
        <div style="width:80px;height:80px;background:#f3e8ff;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto;font-size:34px;color:#6a3090">
          <i class="bi bi-person-badge"></i>
        </div>
      </div>
      <h6 class="fw-bold mb-1"><?= htmlspecialchars($user['name']) ?></h6>
      <small class="text-muted"><?= htmlspecialchars($user['email']) ?></small>
      <hr>
      <span class="badge" style="background:#6a3090">Instruktur</span>
      <div style="margin-top:12px;border-top:1px solid #e5e9f0;padding-top:12px">
        <small class="text-muted d-block">Total Pelatihan</small>
        <strong style="font-size:20px;color:#6a3090"><?= $jml_pelatihan ?></strong>
      </div>
      <div style="margin-top:12px;border-top:1px solid #e5e9f0;padding-top:12px">
        <small class="text-muted d-block">Total Peserta</small>
        <strong style="font-size:20px;color:#6a3090"><?= $jml_peserta ?></strong>
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
            <div class="col-md-12">
              <label class="form-label fw-semibold">Nama Lengkap</label>
              <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Email</label>
              <input type="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" disabled>
              <small class="text-muted">Email tidak bisa diubah</small>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Nomor Kontak</label>
              <input type="tel" name="kontak" class="form-control" value="<?= htmlspecialchars($instruktur_data['kontak'] ?? '') ?>" placeholder="Contoh: 081234567890">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Bidang Keahlian</label>
              <input type="text" name="bidang_keahlian" class="form-control" value="<?= htmlspecialchars($instruktur_data['bidang_keahlian'] ?? '') ?>" placeholder="Contoh: Pengembangan Masyarakat">
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Pendidikan</label>
              <input type="text" name="pendidikan" class="form-control" value="<?= htmlspecialchars($instruktur_data['pendidikan'] ?? '') ?>" placeholder="Contoh: S2 Sosiologi">
            </div>
            <div class="col-12">
              <button type="submit" class="btn btn-success px-4"><i class="bi bi-check-circle"></i> Simpan Perubahan</button>
            </div>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include 'footer.php'; ?>
