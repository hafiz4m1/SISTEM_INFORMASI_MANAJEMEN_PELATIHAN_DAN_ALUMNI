<?php
ob_start();
$page_title = 'Profil Saya';
include '../koneksi.php';
include '../security.php';
include 'header.php';

// Process form setelah session tersedia
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nik          = mysqli_real_escape_string($koneksi, $_POST['nik']);
    $tempat_lahir = mysqli_real_escape_string($koneksi, $_POST['tempat_lahir']);
    $tgl_lahir    = mysqli_real_escape_string($koneksi, $_POST['tanggal_lahir']);
    $jk           = mysqli_real_escape_string($koneksi, $_POST['jenis_kelamin']);
    $alamat       = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    $telepon      = mysqli_real_escape_string($koneksi, $_POST['telepon']);
    $nama         = mysqli_real_escape_string($koneksi, $_POST['name']);

    $alumni_id = mysqli_fetch_row(mysqli_query($koneksi,
        "SELECT id FROM alumni WHERE user_id={$_SESSION['id_login']}"))[0] ?? 0;
    $foto_lama = mysqli_fetch_assoc(mysqli_query($koneksi,
        "SELECT foto FROM alumni WHERE user_id={$_SESSION['id_login']}"))['foto'] ?? '';
    $foto_baru = $foto_lama;

    if (!empty($_FILES['foto']['name'])) {
        $upload_errors = validasiUpload($_FILES['foto'], ['jpg','jpeg','png'], 2);
        if (empty($upload_errors)) {
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            $uploadDir = __DIR__ . '/../assets/images/profiles';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $filename = time() . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
            $target = $uploadDir . '/' . $filename;
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $target)) {
                if ($foto_lama && file_exists($uploadDir . '/' . $foto_lama)) {
                    @unlink($uploadDir . '/' . $foto_lama);
                }
                $foto_baru = $filename;
            } else {
                $errors[] = 'Gagal menyimpan foto profil.';
            }
        } else {
            $errors = array_merge($errors, $upload_errors);
        }
    }

    if ($alumni_id > 0) {
        mysqli_query($koneksi, "UPDATE alumni SET
            nik='$nik', tempat_lahir='$tempat_lahir', tanggal_lahir='$tgl_lahir',
            jenis_kelamin='$jk', alamat='$alamat', telepon='$telepon', foto='$foto_baru'
            WHERE id=$alumni_id");
    } else {
        mysqli_query($koneksi, "INSERT INTO alumni (user_id, nik, tempat_lahir, tanggal_lahir, jenis_kelamin, alamat, telepon, foto)
            VALUES ({$_SESSION['id_login']},'$nik','$tempat_lahir','$tgl_lahir','$jk','$alamat','$telepon','$foto_baru')");
    }

    mysqli_query($koneksi, "UPDATE users SET name='$nama' WHERE id={$_SESSION['id_login']}");
    $_SESSION['nama'] = $nama;

    if (empty($errors)) {
        ob_end_clean();
        header("Location: profile.php?pesan=Profil berhasil diperbarui."); exit;
    }
}

$pesan  = isset($_GET['pesan']) ? $_GET['pesan'] : '';

// Ambil data terbaru
$user   = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM users WHERE id={$_SESSION['id_login']}"));
$alumni = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM alumni WHERE user_id={$_SESSION['id_login']}"));
?>

<?php if ($pesan): ?>
  <div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($pesan) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>
<?php if (!empty($errors)): ?>
  <div class="alert alert-danger">
    <ul class="mb-0">
      <?php foreach ($errors as $error): ?>
        <li><?= htmlspecialchars($error) ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<div class="row g-3">
  <!-- Avatar & info singkat -->
  <div class="col-lg-3">
    <div class="card p-4 text-center">
      <div class="mb-3">
        <?php if (!empty($alumni['foto']) && file_exists(__DIR__ . '/../assets/images/profiles/' . $alumni['foto'])): ?>
          <img src="../assets/images/profiles/<?= e($alumni['foto']) ?>" alt="Foto Profil" style="width:80px;height:80px;border-radius:50%;object-fit:cover;display:block;margin:0 auto;">
        <?php else: ?>
          <div style="width:80px;height:80px;background:#e8f5e9;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto;font-size:34px;color:#134e35">
            <i class="bi bi-person"></i>
          </div>
        <?php endif; ?>
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
        <form method="POST" enctype="multipart/form-data">
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
              <input type="date" name="tanggal_lahir" class="form-control" value="<?= htmlspecialchars($alumni['tanggal_lahir'] ?? '') ?>">
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
            <div class="col-md-12">
              <label class="form-label fw-semibold">Foto Profil</label>
              <input type="file" name="foto" class="form-control" accept="image/png,image/jpeg">
              <small class="text-muted">JPEG/PNG maksimal 2MB. Kosongkan jika tidak ingin mengganti.</small>
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