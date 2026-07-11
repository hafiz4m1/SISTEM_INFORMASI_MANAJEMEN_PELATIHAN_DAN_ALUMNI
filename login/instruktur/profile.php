<?php
ob_start();
$page_title = 'Profil Saya';
include '../koneksi.php';
include '../security.php';
include 'header.php';

$errors = [];
$instruktur_id = mysqli_fetch_row(mysqli_query($koneksi,
    "SELECT id FROM instruktur WHERE user_id={$_SESSION['id_login']}"))[0] ?? 0;
$checkFotoColumn = mysqli_query($koneksi, "SHOW COLUMNS FROM instruktur LIKE 'foto'");
if (!$checkFotoColumn || mysqli_num_rows($checkFotoColumn) === 0) {
    mysqli_query($koneksi, "ALTER TABLE instruktur ADD COLUMN foto varchar(255) DEFAULT NULL");
}

// Process form setelah session tersedia
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bidang_keahlian = mysqli_real_escape_string($koneksi, $_POST['bidang_keahlian']);
    $pendidikan      = mysqli_real_escape_string($koneksi, $_POST['pendidikan']);
    $kontak          = mysqli_real_escape_string($koneksi, $_POST['kontak']);
    $nama            = mysqli_real_escape_string($koneksi, $_POST['name']);

    $foto_lama = mysqli_fetch_assoc(mysqli_query($koneksi,
        "SELECT foto FROM instruktur WHERE user_id={$_SESSION['id_login']}"))['foto'] ?? '';
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

    if ($instruktur_id > 0) {
        mysqli_query($koneksi, "UPDATE instruktur SET
            bidang_keahlian='$bidang_keahlian', pendidikan='$pendidikan', kontak='$kontak', foto='$foto_baru'
            WHERE id=$instruktur_id");
    } else {
        mysqli_query($koneksi, "INSERT INTO instruktur (user_id, bidang_keahlian, pendidikan, kontak, foto)
            VALUES ({$_SESSION['id_login']},'$bidang_keahlian','$pendidikan','$kontak','$foto_baru')");
        $instruktur_id = mysqli_insert_id($koneksi);
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
        <?php if (!empty($instruktur_data['foto']) && file_exists(__DIR__ . '/../assets/images/profiles/' . $instruktur_data['foto'])): ?>
          <img src="../assets/images/profiles/<?= e($instruktur_data['foto']) ?>" alt="Foto Profil" style="width:80px;height:80px;border-radius:50%;object-fit:cover;display:block;margin:0 auto;">
        <?php else: ?>
          <div style="width:80px;height:80px;background:#f3e8ff;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto;font-size:34px;color:#6a3090">
            <i class="bi bi-person-badge"></i>
          </div>
        <?php endif; ?>
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
        <form method="POST" enctype="multipart/form-data">
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
            <div class="col-md-12">
              <label class="form-label fw-semibold">Foto Profil</label>
              <input type="file" name="foto" class="form-control" accept="image/png,image/jpeg">
              <small class="text-muted">JPEG/PNG maksimal 2MB. Kosongkan jika tidak ingin mengganti.</small>
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
