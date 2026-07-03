<?php
session_start();
include 'koneksi.php';

if (isset($_SESSION['logged_in'])) {
    switch ($_SESSION['level']) {
        case 'admin':      header("location: admin/index.php"); exit;
        case 'instruktur': header("location: instruktur/index.php"); exit;
        case 'alumni':     header("location: alumni/index.php"); exit;
        default:           header("location: peserta/index.php"); exit;
    }
}

$errors  = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama         = trim(mysqli_real_escape_string($koneksi, $_POST['nama']));
    $email        = trim(mysqli_real_escape_string($koneksi, $_POST['email']));
    $password     = $_POST['password'];
    $konfirm      = $_POST['konfirmasi'];
    $role         = $_POST['role'];
    $nik          = mysqli_real_escape_string($koneksi, $_POST['nik'] ?? '');
    $tempat_lahir = mysqli_real_escape_string($koneksi, $_POST['tempat_lahir'] ?? '');
    $tgl_lahir    = $_POST['tanggal_lahir'] ?? '';
    $jk           = $_POST['jenis_kelamin'] ?? '';
    $alamat       = mysqli_real_escape_string($koneksi, $_POST['alamat'] ?? '');
    $telepon      = mysqli_real_escape_string($koneksi, $_POST['telepon'] ?? '');

    if (!$nama)   $errors[] = 'Nama lengkap wajib diisi.';
    if (!$email)  $errors[] = 'Email wajib diisi.';
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'Format email tidak valid.';
    if (strlen($password) < 6)        $errors[] = 'Password minimal 6 karakter.';
    if ($password !== $konfirm)       $errors[] = 'Konfirmasi password tidak cocok.';
    if (!in_array($role, ['peserta','alumni'])) $errors[] = 'Role tidak valid.';

    if (!$errors) {
        $cek = mysqli_fetch_row(mysqli_query($koneksi, "SELECT COUNT(*) FROM users WHERE email='$email'"))[0];
        if ($cek > 0) $errors[] = 'Email sudah terdaftar. Gunakan email lain.';
    }

    if (!$errors) {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        mysqli_query($koneksi, "INSERT INTO users (name, email, password, role, is_active)
            VALUES ('$nama','$email','$hash','$role',1)");
        $user_id = mysqli_insert_id($koneksi);

        if ($role === 'alumni') {
            mysqli_query($koneksi, "INSERT INTO alumni
                (user_id, nik, tempat_lahir, tanggal_lahir, jenis_kelamin, alamat, telepon)
                VALUES ($user_id,'$nik','$tempat_lahir','$tgl_lahir','$jk','$alamat','$telepon')");
        }

        $success = 'Pendaftaran berhasil! Silakan login.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Daftar - BPPMDDTT Banjarmasin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    :root { --primary: #223D5C; --primary-dark: #1A2D48; --gold: #D4A473; --teal: #4F9DB5; }
    body { background: #f4f6fb; }
    .register-card {
      max-width: 600px;
      margin: 40px auto 60px;
      border: none;
      border-radius: 12px;
      box-shadow: 0 4px 24px rgba(0,0,0,0.08);
    }
    .register-header {
      background: var(--primary);
      color: #fff;
      border-radius: 12px 12px 0 0;
      padding: 24px 32px 18px;
    }
    .register-header h5 { font-weight: 600; margin: 0; font-size: 16px; }
    .register-header small { opacity: .75; }
    .register-body { padding: 24px 32px 32px; }
    .role-card {
      border: 2px solid #e5e9f0;
      border-radius: 10px;
      padding: 12px 16px;
      cursor: pointer;
      transition: all .15s;
      display: flex;
      align-items: center;
      gap: 12px;
    }
    .role-card:hover  { border-color: var(--primary); background: #f0f5ff; }
    .role-card.selected { border-color: var(--primary); background: #eef3fb; }
    .role-card .icon  { width: 40px; height: 40px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0; }
    .role-card .role-title { font-weight: 600; font-size: 13px; color: #1a2942; }
    .role-card .role-desc  { font-size: 11px; color: #6b7280; }
    .section-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #6b7280; margin: 20px 0 10px; padding-bottom: 6px; border-bottom: 1px solid #f0f0f0; }
    .btn-primary { background-color: var(--primary); border-color: var(--primary); }
    .btn-primary:hover { background-color: var(--primary-dark); border-color: var(--primary-dark); }
  </style>
</head>
<body>

<div class="card register-card">
  <div class="register-header">
    <h5>Daftar Akun Baru</h5>
    <small>Aplikasi Monitoring dan Manajemen Data Alumni, Pelatih · BPPMDDTT Banjarmasin</small>
  </div>
  <div class="register-body">

    <?php if ($success): ?>
      <div class="alert alert-success text-center">
        <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($success) ?>
        <div class="mt-2"><a href="login.php" class="btn btn-success btn-sm px-4">Login Sekarang</a></div>
      </div>
    <?php else: ?>

    <?php if ($errors): ?>
      <div class="alert alert-danger py-2">
        <ul class="mb-0 ps-3">
          <?php foreach ($errors as $e): ?>
            <li style="font-size:13px"><?= htmlspecialchars($e) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form method="POST">

      <!-- Pilih Role -->
      <div class="section-label">Daftar Sebagai</div>
      <input type="hidden" name="role" id="roleInput" value="<?= htmlspecialchars($_POST['role'] ?? 'peserta') ?>">
      <div class="d-flex gap-2 mb-1">
        <div class="role-card flex-fill <?= (!isset($_POST['role']) || $_POST['role']==='peserta') ? 'selected' : '' ?>"
             onclick="pilihRole('peserta', this)">
          <div class="icon" style="background:#e8f0fe">🎓</div>
          <div>
            <div class="role-title">Peserta</div>
            <div class="role-desc">Ingin mengikuti pelatihan</div>
          </div>
        </div>
        <div class="role-card flex-fill <?= (isset($_POST['role']) && $_POST['role']==='alumni') ? 'selected' : '' ?>"
             onclick="pilihRole('alumni', this)">
          <div class="icon" style="background:#e8f5e9">🏆</div>
          <div>
            <div class="role-title">Alumni</div>
            <div class="role-desc">Sudah pernah ikut pelatihan</div>
          </div>
        </div>
      </div>

      <!-- Data Akun -->
      <div class="section-label">Data Akun</div>
      <div class="row g-3">
        <div class="col-12">
          <label class="form-label fw-semibold" style="font-size:13px">Nama Lengkap <span class="text-danger">*</span></label>
          <input type="text" name="nama" class="form-control form-control-sm"
                 value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>"
                 placeholder="Masukkan nama lengkap" required>
        </div>
        <div class="col-12">
          <label class="form-label fw-semibold" style="font-size:13px">Email <span class="text-danger">*</span></label>
          <input type="email" name="email" class="form-control form-control-sm"
                 value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                 placeholder="contoh@email.com" required>
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold" style="font-size:13px">Password <span class="text-danger">*</span></label>
          <input type="password" name="password" id="passInput" class="form-control form-control-sm"
                 placeholder="Minimal 6 karakter" required>
          <div id="passStrength" class="mt-1" style="font-size:11px"></div>
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold" style="font-size:13px">Konfirmasi Password <span class="text-danger">*</span></label>
          <input type="password" name="konfirmasi" id="konfirmasi" class="form-control form-control-sm"
                 placeholder="Ulangi password" required>
          <div id="matchInfo" class="mt-1" style="font-size:11px"></div>
        </div>
      </div>

      <!-- Data Diri -->
      <div class="section-label">Data Diri</div>
      <div class="row g-3">
        <div class="col-md-6">
          <label class="form-label fw-semibold" style="font-size:13px">NIK</label>
          <input type="text" name="nik" class="form-control form-control-sm" maxlength="16"
                 value="<?= htmlspecialchars($_POST['nik'] ?? '') ?>"
                 placeholder="16 digit NIK">
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold" style="font-size:13px">No. Telepon</label>
          <input type="text" name="telepon" class="form-control form-control-sm"
                 value="<?= htmlspecialchars($_POST['telepon'] ?? '') ?>"
                 placeholder="08xxxxxxxxxx">
        </div>
        <div class="col-md-5">
          <label class="form-label fw-semibold" style="font-size:13px">Tempat Lahir</label>
          <input type="text" name="tempat_lahir" class="form-control form-control-sm"
                 value="<?= htmlspecialchars($_POST['tempat_lahir'] ?? '') ?>"
                 placeholder="Kota kelahiran">
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold" style="font-size:13px">Tanggal Lahir</label>
          <input type="date" name="tanggal_lahir" class="form-control form-control-sm"
                 value="<?= htmlspecialchars($_POST['tanggal_lahir'] ?? '') ?>">
        </div>
        <div class="col-md-3">
          <label class="form-label fw-semibold" style="font-size:13px">Jenis Kelamin</label>
          <select name="jenis_kelamin" class="form-select form-select-sm">
            <option value="">-- Pilih --</option>
            <option value="L" <?= ($_POST['jenis_kelamin'] ?? '') === 'L' ? 'selected' : '' ?>>Laki-laki</option>
            <option value="P" <?= ($_POST['jenis_kelamin'] ?? '') === 'P' ? 'selected' : '' ?>>Perempuan</option>
          </select>
        </div>
        <div class="col-12">
          <label class="form-label fw-semibold" style="font-size:13px">Alamat</label>
          <textarea name="alamat" class="form-control form-control-sm" rows="2"
                    placeholder="Alamat lengkap"><?= htmlspecialchars($_POST['alamat'] ?? '') ?></textarea>
        </div>
      </div>

      <div class="mt-4">
        <button type="submit" class="btn btn-primary w-100 fw-semibold">
          <i class="bi bi-person-plus me-1"></i> Daftar Sekarang
        </button>
      </div>
      <div class="text-center mt-3" style="font-size:13px;color:#6b7280">
        Sudah punya akun? <a href="login.php" class="text-primary fw-semibold">Login di sini</a>
      </div>
    </form>

    <?php endif; ?>
  </div>
</div>

<script>
function pilihRole(role, el) {
  document.querySelectorAll('.role-card').forEach(c => c.classList.remove('selected'));
  el.classList.add('selected');
  document.getElementById('roleInput').value = role;
}

// Cek kekuatan password
document.getElementById('passInput').addEventListener('input', function() {
  const v  = this.value;
  const el = document.getElementById('passStrength');
  if (!v) { el.textContent = ''; return; }
  if (v.length < 6)  { el.textContent = '⚠️ Terlalu pendek'; el.style.color = '#dc3545'; }
  else if (v.length < 10) { el.textContent = '✓ Cukup'; el.style.color = '#fd7e14'; }
  else { el.textContent = '✓ Kuat'; el.style.color = '#198754'; }
  const k = document.getElementById('konfirmasi').value;
  if (k) cekMatch(v, k);
});

document.getElementById('konfirmasi').addEventListener('input', function() {
  cekMatch(document.getElementById('passInput').value, this.value);
});

function cekMatch(p, k) {
  const el = document.getElementById('matchInfo');
  if (!k) { el.textContent = ''; return; }
  if (p === k) { el.innerHTML = '<span style="color:#198754">✓ Password cocok</span>'; }
  else         { el.innerHTML = '<span style="color:#dc3545">✗ Tidak cocok</span>'; }
}
</script>

</body>
</html>
