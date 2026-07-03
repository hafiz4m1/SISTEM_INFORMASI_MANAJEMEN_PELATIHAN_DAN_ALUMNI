<?php
// HARUS di paling atas sebelum ada output HTML apapun
define('DirBlock', true);
include 'logincontroller.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - BPPMDDTT Banjarmasin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    :root { --primary: #223D5C; --primary-dark: #1A2D48; --gold: #D4A473; --teal: #4F9DB5; }
    body { background: #f4f6fb; }
    .login-card {
      max-width: 420px;
      margin: 80px auto;
      border: none;
      border-radius: 12px;
      box-shadow: 0 4px 24px rgba(0,0,0,0.08);
    }
    .login-header {
      background: var(--primary);
      color: #fff;
      border-radius: 12px 12px 0 0;
      padding: 24px 32px 28px;
      text-align: center;
    }
    .login-header h5 { font-weight: 600; margin: 8px 0 4px 0; font-size: 16px; }
    .login-header small { opacity: .75; }
    .login-body { padding: 28px 32px; }
    .btn-primary { background-color: var(--primary); border-color: var(--primary); }
    .btn-primary:hover { background-color: var(--primary-dark); border-color: var(--primary-dark); }
    .logo-container {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 16px;
      margin-bottom: 20px;
    }
    .logo-container img {
      height: 60px;
      object-fit: contain;
    }
  </style>
</head>
<body>

<a href="../index.php" class="btn btn-outline-secondary" style="position: absolute; top: 20px; left: 20px; font-size: 14px;">
  <i class="bi bi-arrow-left"></i> Kembali
</a>

<div class="card login-card">
  <div class="login-header">
    <div class="logo-container">
      <img src="../assets/images/LOGO-KEMENTRANS-Bulat.png" alt="Kementerian Transmigrasi">
      <img src="../assets/images/Logo-kementrian.png" alt="BPPMDDTT" style="height:55px">
    </div>
    <h5>Aplikasi Monitoring dan Manajemen Data Alumni, Pelatih</h5>
    <small>BPPMDDTT Banjarmasin</small>
  </div>
  <div class="login-body">

    <?php if (isset($_GET['pesan'])): ?>
      <div class="alert alert-danger py-2"><?= htmlspecialchars($_GET['pesan']) ?></div>
    <?php endif; ?>

    <form action="" method="POST">
      <div class="mb-3">
        <label class="form-label fw-semibold">Email</label>
        <input type="email" name="email" class="form-control"
               value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>"
               placeholder="email@bppmddtt.go.id" required>
      </div>
      <div class="mb-4">
        <label class="form-label fw-semibold">Password</label>
        <input type="password" name="password" class="form-control"
               placeholder="••••••••" required>
      </div>
      <button type="submit" name="login" class="btn btn-primary w-100 fw-semibold">Masuk</button>
    </form>

    <hr class="my-3">
    <div class="text-center" style="font-size:13px;color:#6b7280">
      <div class="mb-2">
        Belum punya akun?
        <a href="register.php" class="text-primary fw-semibold">Daftar di sini</a>
      </div>
      <div>
        <a href="ganti_password.php" class="text-danger fw-semibold" style="text-decoration:none;">Lupa Password?</a>
      </div>
    </div>

  </div>
</div>

</body>
</html>
