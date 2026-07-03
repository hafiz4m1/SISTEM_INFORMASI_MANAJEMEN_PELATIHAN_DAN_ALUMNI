<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['level'] !== 'peserta') {
    header("location: ../login.php?pesan=Anda harus login sebagai peserta.");
    exit;
}
$current = basename($_SERVER['PHP_SELF']);
include_once '../koneksi.php'; // koneksi.php ada di login/
include_once '../security.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Aplikasi Monitoring dan Manajemen Data Alumni, Pelatih BPPMDDTT Banjarmasin">
  <meta name="author" content="M. Hafiz Nuril Ikhsan">
  <title><?= $page_title ?? 'Peserta' ?> | Aplikasi Monitoring Alumni &amp; Pelatih</title>
  <link rel="shortcut icon" type="image/x-icon" href="../assets/images/favicon.ico">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/global.css">
  <style>
    :root { --sidebar-w:230px; --sidebar-bg:#223D5C; --accent:#D4A473; --primary: #223D5C; --primary-dark: #1A2D48; --gold: #D4A473; --teal: #4F9DB5; }
    body { background:#f4f6fb; font-size:14px; }
    #sidebar { position:fixed; top:0; left:0; bottom:0; width:var(--sidebar-w);
      background:var(--sidebar-bg); display:flex; flex-direction:column; z-index:100; }
    #sidebar .brand { padding:20px 16px 16px; border-bottom:1px solid rgba(255,255,255,.08); }
    #sidebar .brand h6 { color:#fff; font-weight:600; margin:0; font-size:13px; line-height:1.4; }
    #sidebar .brand small { color:rgba(255,255,255,.45); font-size:11px; }
    #sidebar .nav-label { color:rgba(255,255,255,.35); font-size:10px; font-weight:600;
      letter-spacing:.08em; text-transform:uppercase; padding:16px 16px 6px; }
    #sidebar a.nav-link { color:rgba(255,255,255,.65); padding:9px 16px; border-radius:6px;
      margin:1px 8px; display:flex; align-items:center; gap:10px; font-size:13px; transition:all .15s; }
    #sidebar a.nav-link:hover, #sidebar a.nav-link.active { background:var(--accent); color:var(--sidebar-bg); font-weight:600; }
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
    .btn-primary { background-color: var(--primary); border-color: var(--primary); }
    .btn-primary:hover { background-color: var(--primary-dark); border-color: var(--primary-dark); }
  </style>
</head>
<body>

<div id="sidebar">
  <div class="brand">
    <h6>Portal Peserta</h6>
    <small>BPPMDDTT Banjarmasin</small>
  </div>
  <div class="nav-label">Menu</div>
  <a href="index.php" class="nav-link <?= $current==='index.php'?'active':'' ?>"><i class="bi bi-speedometer2"></i> Dashboard</a>
  <a href="profile.php" class="nav-link <?= $current==='profile.php'?'active':'' ?>"><i class="bi bi-person-circle"></i> Profil Saya</a>
  <a href="pelatihan.php" class="nav-link <?= $current==='pelatihan.php'?'active':'' ?>"><i class="bi bi-journal-bookmark"></i> Pelatihan</a>
  <a href="sertifikat.php" class="nav-link <?= $current==='sertifikat.php'?'active':'' ?>"><i class="bi bi-award"></i> Sertifikat</a>
  <div class="nav-label">Pengaturan</div>
  <a href="../ganti_password.php" class="nav-link"><i class="bi bi-shield-lock"></i> Ganti Password</a>
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
      &nbsp;·&nbsp; <span class="badge bg-primary" style="font-size:11px">Peserta</span>
    </div>
  </div>
  <div class="content-area">
