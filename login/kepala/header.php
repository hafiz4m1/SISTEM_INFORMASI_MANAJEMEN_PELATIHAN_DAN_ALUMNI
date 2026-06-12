<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['level'] !== 'kepala') {
    header("location: ../login.php?pesan=Anda harus login sebagai kepala.");
    exit;
}
$current = basename($_SERVER['PHP_SELF']);
include_once '../koneksi.php';

// Ambil data kepala
$kepala_data = mysqli_fetch_assoc(mysqli_query($koneksi,
    "SELECT k.*, u.name, u.email FROM kepala k
     JOIN users u ON k.user_id=u.id
     WHERE k.user_id={$_SESSION['id_login']} AND k.is_aktif=1 LIMIT 1"));

// Notifikasi: laporan menunggu persetujuan
$jml_menunggu = 0;
$cek_tabel = mysqli_query($koneksi, "SHOW TABLES LIKE 'persetujuan_laporan'");
if ($cek_tabel && mysqli_num_rows($cek_tabel) > 0) {
    $jml_menunggu = mysqli_fetch_row(mysqli_query($koneksi,
        "SELECT COUNT(*) FROM persetujuan_laporan WHERE status='menunggu'"))[0];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Sistem Informasi Manajemen Pelatihan dan Alumni BPPMDDTT Banjarmasin">
  <meta name="author" content="M. Hafiz Nuril Ikhsan">
  <title><?= $page_title ?? 'Kepala' ?> | BPPMDDTT</title>
  <link rel="shortcut icon" type="image/x-icon" href="../assets/images/favicon.ico">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/global.css">
  <style>
    :root { --sidebar-w:240px; --sidebar-bg:#1a2942; --accent:#2c4a7c; }
    body { background:#f4f6fb; font-size:14px; }
    #sidebar { position:fixed; top:0; left:0; bottom:0; width:var(--sidebar-w);
      background:var(--sidebar-bg); display:flex; flex-direction:column; z-index:100; }
    #sidebar .brand { padding:20px 16px 16px; border-bottom:1px solid rgba(255,255,255,.08); }
    #sidebar .brand h6 { color:#fff; font-weight:600; margin:0; font-size:13px; line-height:1.4; }
    #sidebar .brand small { color:rgba(255,255,255,.45); font-size:11px; }
    #sidebar .brand .jabatan { color:rgba(255,255,255,.6); font-size:11px; margin-top:4px;
      background:rgba(255,255,255,.1); border-radius:4px; padding:2px 8px; display:inline-block; }
    #sidebar .nav-label { color:rgba(255,255,255,.35); font-size:10px; font-weight:600;
      letter-spacing:.08em; text-transform:uppercase; padding:16px 16px 6px; }
    #sidebar a.nav-link { color:rgba(255,255,255,.65); padding:9px 16px; border-radius:6px;
      margin:1px 8px; display:flex; align-items:center; gap:10px; font-size:13px; transition:all .15s; }
    #sidebar a.nav-link:hover, #sidebar a.nav-link.active { background:var(--accent); color:#fff; }
    #sidebar a.nav-link i { font-size:16px; width:18px; text-align:center; }
    #sidebar .sidebar-footer { margin-top:auto; padding:12px 16px; border-top:1px solid rgba(255,255,255,.08); }
    #sidebar .sidebar-footer a { color:rgba(255,255,255,.55); font-size:13px;
      display:flex; align-items:center; gap:8px; text-decoration:none; }
    #sidebar .sidebar-footer a:hover { color:#fff; }
    #main { margin-left:var(--sidebar-w); min-height:100vh; display:flex; flex-direction:column; }
    #topbar { background:#fff; border-bottom:1px solid #e5e9f0; padding:12px 24px;
      display:flex; align-items:center; justify-content:space-between; position:sticky; top:0; z-index:50; }
    #topbar .page-title { font-weight:600; font-size:15px; color:#1a2942; margin:0; }
    .content-area { padding:24px; flex:1; }
    .stat-card { border:none; border-radius:12px; padding:20px; display:flex; align-items:center; gap:16px; }
    .stat-card .icon { width:48px; height:48px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:22px; }
    .stat-card .val { font-size:26px; font-weight:700; line-height:1; }
    .stat-card .lbl { font-size:12px; color:#6b7280; margin-top:2px; }
    .card { border:none; border-radius:12px; box-shadow:0 1px 8px rgba(0,0,0,.06); }
    .card-header { background:#fff; border-bottom:1px solid #f0f0f0; font-weight:600; font-size:14px;
      border-radius:12px 12px 0 0 !important; padding:16px 20px; }
    .table thead th { background:#f8f9fb; font-size:12px; font-weight:600;
      text-transform:uppercase; letter-spacing:.04em; color:#6b7280; border-bottom:none; }
    .table td { vertical-align:middle; font-size:13px; }
  </style>
</head>
<body>

<div id="sidebar">
  <div class="brand">
    <h6><?= htmlspecialchars($kepala_data['nama_lengkap'] ?? $_SESSION['nama']) ?></h6>
    <small><?= htmlspecialchars($kepala_data['nip'] ?? '') ?></small><br>
    <span class="jabatan">Kepala Balai</span>
  </div>
  <div class="nav-label">Menu</div>
  <a href="index.php" class="nav-link <?= $current==='index.php'?'active':'' ?>">
    <i class="bi bi-speedometer2"></i> Dashboard
  </a>
  <a href="laporan.php" class="nav-link <?= $current==='laporan.php'?'active':'' ?>">
    <i class="bi bi-file-earmark-bar-graph"></i> Laporan
  </a>
  <a href="pengesahan.php" class="nav-link <?= $current==='pengesahan.php'?'active':'' ?>">
    <i class="bi bi-patch-check"></i> Pengesahan Laporan
    <?php if ($jml_menunggu > 0): ?>
      <span class="badge bg-danger ms-auto"><?= $jml_menunggu ?></span>
    <?php endif; ?>
  </a>
  <div class="nav-label">Akun</div>
  <a href="profil.php" class="nav-link <?= $current==='profil.php'?'active':'' ?>">
    <i class="bi bi-person-badge"></i> Profil Jabatan
  </a>
  <div class="sidebar-footer">
    <a href="../logout.php"><i class="bi bi-box-arrow-left"></i> Keluar</a>
  </div>
</div>

<div id="main">
  <div id="topbar">
    <button id="hamburger" title="Menu" style="background:none;border:none;font-size:20px;cursor:pointer;padding:0;color:#1a2942">
      <i class="bi bi-list"></i>
    </button>
    <h6 class="page-title"><?= $page_title ?? 'Dashboard' ?></h6>
    <div style="font-size:13px;color:#6b7280">
      Halo, <span style="font-weight:600;color:#1a2942"><?= htmlspecialchars($_SESSION['nama']) ?></span>
      &nbsp;·&nbsp;
      <span class="badge" style="font-size:11px;background:#1a2942;color:#fff">Kepala Balai</span>
    </div>
  </div>
  <div class="content-area">