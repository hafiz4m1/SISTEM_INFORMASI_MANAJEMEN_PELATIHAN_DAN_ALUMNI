<?php
ob_start();
session_start();
include 'koneksi.php';

// Tentukan mode: logged_in atau guest (lupa password)
$is_logged_in = isset($_SESSION['logged_in']) && $_SESSION['logged_in'];
$uid = $_SESSION['id_login'] ?? null;

// Jika guest mode (lupa password), ambil email dari POST
$email_input = $_GET['email'] ?? $_POST['email'] ?? '';

$pesan_sukses = '';
$pesan_error = '';

// Process form ganti password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ganti_password'])) {
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    $password_lama = $_POST['password_lama'] ?? '';
    $password_baru = mysqli_real_escape_string($koneksi, $_POST['password_baru']);
    $password_konfirm = mysqli_real_escape_string($koneksi, $_POST['password_konfirm']);

    // Cari user berdasarkan email
    $user = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM users WHERE email='$email'"));

    if (!$user) {
        $pesan_error = "Email tidak terdaftar dalam sistem.";
    }
    // Jika logged in, validasi password lama
    elseif ($is_logged_in && !password_verify($password_lama, $user['password'])) {
        $pesan_error = "Password lama yang Anda masukkan salah.";
    }
    // Validasi password baru dan konfirmasi
    elseif ($password_baru !== $password_konfirm) {
        $pesan_error = "Password baru dan konfirmasi password tidak sesuai.";
    }
    // Validasi panjang password baru
    elseif (strlen($password_baru) < 6) {
        $pesan_error = "Password baru harus minimal 6 karakter.";
    }
    // Update password
    else {
        $password_hash = password_hash($password_baru, PASSWORD_BCRYPT);
        $update = mysqli_query($koneksi, "UPDATE users SET password='$password_hash' WHERE email='$email'");

        if ($update) {
            $pesan_sukses = "Password berhasil diubah! Silakan login kembali.";
            $_POST['email'] = '';
            $_POST['password_lama'] = '';
            $_POST['password_baru'] = '';
            $_POST['password_konfirm'] = '';
            // Redirect ke login jika guest mode
            if (!$is_logged_in) {
                header("Refresh: 3; url=login.php");
            }
        } else {
            $pesan_error = "Terjadi kesalahan saat mengubah password. Silakan coba lagi.";
        }
    }
}

if ($is_logged_in) {
    $user = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM users WHERE id=$uid"));
    $email_input = $user['email'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ganti Password - BPPMDDTT Banjarmasin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body { background: #f4f6fb; }
    .card-container {
      max-width: 500px;
      margin: 40px auto;
    }
    .card { border: none; border-radius: 12px; box-shadow: 0 1px 8px rgba(0,0,0,.06); }
    .card-header {
      background: linear-gradient(135deg, #1a4c8e 0%, #1a3a6b 100%);
      color: #fff;
      border-radius: 12px 12px 0 0 !important;
      padding: 24px 20px;
    }
    .card-header h5 { font-weight: 600; margin: 0; font-size: 18px; }
    .card-header small { opacity: 0.85; }
    .form-label { font-weight: 600; font-size: 14px; }
    .btn-back {
      position: absolute;
      top: 20px;
      left: 20px;
    }
    .info-box {
      background: #f0f7ff;
      border-left: 4px solid #1a4c8e;
      padding: 12px 16px;
      border-radius: 4px;
      font-size: 13px;
      margin-bottom: 20px;
    }
    .password-strength {
      font-size: 12px;
      margin-top: 4px;
    }
    .password-strength.strong { color: #10b981; }
    .password-strength.medium { color: #f59e0b; }
    .password-strength.weak { color: #ef4444; }
  </style>
</head>
<body>

<a href="<?= $is_logged_in ? 'javascript:history.back()' : 'login.php' ?>" class="btn btn-outline-secondary btn-back">
  <i class="bi bi-arrow-left"></i> Kembali
</a>

<div class="card-container">
  <div class="card">
    <div class="card-header">
      <h5><i class="bi bi-shield-lock"></i> Ganti Password</h5>
      <small><?= $is_logged_in ? 'Ubah password akun Anda dengan aman' : 'Lupa Password? Ubah password Anda' ?></small>
    </div>
    <div class="card-body p-4">

      <?php if ($pesan_sukses): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <i class="bi bi-check-circle"></i> <?= htmlspecialchars($pesan_sukses) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>

      <?php if ($pesan_error): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <i class="bi bi-exclamation-circle"></i> <?= htmlspecialchars($pesan_error) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>

      <?php if ($is_logged_in && isset($user)): ?>
      <div class="info-box">
        <i class="bi bi-info-circle"></i> 
        Akun: <strong><?= htmlspecialchars($user['name']) ?></strong> 
        (<?= htmlspecialchars($user['email']) ?>)
      </div>
      <?php endif; ?>

      <form method="POST">
        <div class="mb-3">
          <label class="form-label">Email <span class="text-danger">*</span></label>
          <input type="email" name="email" class="form-control" 
                 value="<?= htmlspecialchars($email_input) ?>"
                 placeholder="Masukkan email Anda"
                 <?= $is_logged_in ? 'readonly' : '' ?>
                 required>
          <?php if (!$is_logged_in): ?>
          <small class="text-muted">Gunakan email yang terdaftar di sistem</small>
          <?php endif; ?>
        </div>

        <?php if ($is_logged_in): ?>
        <div class="mb-3">
          <label class="form-label">Password Lama <span class="text-danger">*</span></label>
          <div class="input-group">
            <input type="password" name="password_lama" class="form-control" 
                   id="pwd_lama"
                   placeholder="Masukkan password lama Anda"
                   required>
            <button class="btn btn-outline-secondary" type="button" id="toggle_lama">
              <i class="bi bi-eye"></i>
            </button>
          </div>
        </div>

        <hr>
        <?php endif; ?>

        <div class="mb-3">
          <label class="form-label">Password Baru <span class="text-danger">*</span></label>
          <div class="input-group">
            <input type="password" name="password_baru" class="form-control" 
                   id="pwd_baru"
                   placeholder="Masukkan password baru"
                   required>
            <button class="btn btn-outline-secondary" type="button" id="toggle_baru">
              <i class="bi bi-eye"></i>
            </button>
          </div>
          <div class="password-strength" id="strength"></div>
          <small class="text-muted d-block mt-2">Minimal 6 karakter</small>
        </div>

        <div class="mb-4">
          <label class="form-label">Konfirmasi Password Baru <span class="text-danger">*</span></label>
          <div class="input-group">
            <input type="password" name="password_konfirm" class="form-control" 
                   id="pwd_konfirm"
                   placeholder="Ulangi password baru Anda"
                   required>
            <button class="btn btn-outline-secondary" type="button" id="toggle_konfirm">
              <i class="bi bi-eye"></i>
            </button>
          </div>
          <div id="match-status"></div>
        </div>

        <button type="submit" name="ganti_password" class="btn btn-primary w-100 fw-semibold">
          <i class="bi bi-check-circle"></i> Ubah Password
        </button>
      </form>

    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Toggle password visibility
function setupToggle(toggleId, inputId) {
  const toggle = document.getElementById(toggleId);
  if (!toggle) return;
  
  toggle.addEventListener('click', function() {
    const input = document.getElementById(inputId);
    const icon = this.querySelector('i');
    if (input.type === 'password') {
      input.type = 'text';
      icon.classList.remove('bi-eye');
      icon.classList.add('bi-eye-slash');
    } else {
      input.type = 'password';
      icon.classList.add('bi-eye');
      icon.classList.remove('bi-eye-slash');
    }
  });
}

setupToggle('toggle_lama', 'pwd_lama');
setupToggle('toggle_baru', 'pwd_baru');
setupToggle('toggle_konfirm', 'pwd_konfirm');

// Password strength checker
const pwdBaru = document.getElementById('pwd_baru');
if (pwdBaru) {
  pwdBaru.addEventListener('keyup', function() {
    const pwd = this.value;
    const strength = document.getElementById('strength');
    
    if (pwd.length === 0) {
      strength.innerHTML = '';
      return;
    }
    
    let score = 0;
    if (pwd.length >= 6) score++;
    if (pwd.length >= 12) score++;
    if (/[a-z]/.test(pwd) && /[A-Z]/.test(pwd)) score++;
    if (/\d/.test(pwd)) score++;
    if (/[^a-zA-Z\d]/.test(pwd)) score++;
    
    if (score <= 2) {
      strength.className = 'password-strength weak';
      strength.innerHTML = '<i class="bi bi-exclamation-triangle"></i> Password lemah';
    } else if (score <= 3) {
      strength.className = 'password-strength medium';
      strength.innerHTML = '<i class="bi bi-info-circle"></i> Password sedang';
    } else {
      strength.className = 'password-strength strong';
      strength.innerHTML = '<i class="bi bi-check-circle"></i> Password kuat';
    }
  });
}

// Check password match
const pwdKonfirm = document.getElementById('pwd_konfirm');
if (pwdKonfirm) {
  pwdKonfirm.addEventListener('keyup', function() {
    const pwd_baru = document.getElementById('pwd_baru').value;
    const pwd_konfirm = this.value;
    const status = document.getElementById('match-status');
    
    if (pwd_konfirm.length === 0) {
      status.innerHTML = '';
      return;
    }
    
    if (pwd_baru === pwd_konfirm) {
      status.innerHTML = '<small class="text-success"><i class="bi bi-check-circle"></i> Password cocok</small>';
    } else {
      status.innerHTML = '<small class="text-danger"><i class="bi bi-x-circle"></i> Password tidak cocok</small>';
    }
  });
}
</script>

</body>
</html>
<?php ob_end_flush(); ?>
