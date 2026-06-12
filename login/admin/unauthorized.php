<?php http_response_code(403); ?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>403 - Akses Ditolak | BPPMDDTT</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body { background:#f4f6fb; }
    .error-wrap { min-height:100vh; display:flex; align-items:center; justify-content:center; }
    .error-card { max-width:480px; text-align:center; padding:48px 32px; background:#fff;
      border-radius:16px; box-shadow:0 4px 24px rgba(0,0,0,.08); }
    .error-code { font-size:96px; font-weight:800; color:#dc3545; line-height:1; }
    .error-title { font-size:22px; font-weight:700; color:#1a2942; margin:12px 0 8px; }
    .error-desc  { font-size:14px; color:#6b7280; margin-bottom:28px; }
  </style>
</head>
<body>
<div class="error-wrap">
  <div class="error-card">
    <div class="error-code"><i class="bi bi-shield-x" style="font-size:80px;color:#dc3545"></i></div>
    <div class="error-title">Akses Ditolak</div>
    <div class="error-desc">Anda tidak memiliki izin untuk mengakses halaman ini.</div>
    <div class="d-flex gap-2 justify-content-center">
      <a href="javascript:history.back()" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Kembali
      </a>
      <a href="../login.php" class="btn btn-primary">
        <i class="bi bi-box-arrow-in-right me-1"></i> Login
      </a>
    </div>
  </div>
</div>
</body>
</html>
