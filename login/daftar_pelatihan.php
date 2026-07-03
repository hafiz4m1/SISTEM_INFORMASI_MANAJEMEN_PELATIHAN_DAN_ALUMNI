<?php
// daftar_pelatihan.php - taruh di login/ sejajar login.php
// Bisa diakses tanpa login, tapi untuk daftar harus login dulu
session_start();
include 'koneksi.php';

$pesan  = isset($_GET['pesan']) ? $_GET['pesan'] : '';
$error  = isset($_GET['error']) ? $_GET['error'] : '';

// Proses pendaftaran (harus sudah login sebagai peserta)
if (isset($_GET['daftar']) && isset($_SESSION['logged_in']) && $_SESSION['level'] === 'peserta') {
    $pid = (int)$_GET['daftar'];
    $uid = $_SESSION['id_login'];

    $pel  = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM pelatihan WHERE id=$pid AND status='aktif'"));
    $jml  = mysqli_fetch_row(mysqli_query($koneksi, "SELECT COUNT(*) FROM peserta_pelatihan WHERE pelatihan_id=$pid AND status_verifikasi != 'ditolak'"))[0];
    $cek  = mysqli_fetch_row(mysqli_query($koneksi, "SELECT id FROM peserta_pelatihan WHERE user_id=$uid AND pelatihan_id=$pid"));

    if (!$pel) {
        header("location: daftar_pelatihan.php?error=Pelatihan tidak ditemukan."); exit;
    } elseif ($cek) {
        header("location: daftar_pelatihan.php?error=Anda sudah mendaftar pelatihan ini."); exit;
    } elseif ($jml >= $pel['kuota']) {
        header("location: daftar_pelatihan.php?error=Maaf, kuota pelatihan ini sudah penuh."); exit;
    } else {
        mysqli_query($koneksi, "INSERT INTO peserta_pelatihan
            (user_id, pelatihan_id, tanggal_daftar, status_verifikasi)
            VALUES ($uid, $pid, NOW(), 'menunggu')");
        header("location: daftar_pelatihan.php?pesan=Pendaftaran berhasil! Menunggu verifikasi admin."); exit;
    }
}

// Ambil semua pelatihan aktif
$search = isset($_GET['q']) ? mysqli_real_escape_string($koneksi, $_GET['q']) : '';
$where  = $search ? "AND (p.nama_pelatihan LIKE '%$search%' OR p.jenis LIKE '%$search%')" : '';

$pelatihan = mysqli_query($koneksi, "
    SELECT p.*, ui.name as nama_instruktur,
        (SELECT COUNT(*) FROM peserta_pelatihan WHERE pelatihan_id=p.id AND status_verifikasi!='ditolak') as jml_peserta
    FROM pelatihan p
    JOIN instruktur i ON p.instruktur_id=i.id
    JOIN users ui ON i.user_id=ui.id
    WHERE p.status='aktif' $where
    ORDER BY p.tanggal_mulai ASC
");

// Cek status pendaftaran user jika sudah login
$pendaftaran_saya = [];
if (isset($_SESSION['logged_in']) && $_SESSION['level'] === 'peserta') {
    $uid = $_SESSION['id_login'];
    $q   = mysqli_query($koneksi, "SELECT pelatihan_id, status_verifikasi FROM peserta_pelatihan WHERE user_id=$uid");
    while ($r = mysqli_fetch_assoc($q)) {
        $pendaftaran_saya[$r['pelatihan_id']] = $r['status_verifikasi'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Daftar Pelatihan | BPPMDDTT Banjarmasin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    :root { --primary: #223D5C; --primary-dark: #1A2D48; --gold: #D4A473; --teal: #4F9DB5; }
    body { background: #f4f6fb; font-size: 14px; }
    .navbar { background: var(--primary); }
    .navbar-brand { color: #fff !important; font-weight: 700; font-size: 15px; }
    .nav-btn { font-size: 13px; border-radius: 8px; padding: 6px 18px; font-weight: 600; }
    .btn-primary { background-color: var(--primary); border-color: var(--primary); }
    .btn-primary:hover { background-color: var(--primary-dark); border-color: var(--primary-dark); }
    .btn-warning { background-color: var(--gold) !important; border-color: var(--gold) !important; color: #fff !important; }
    .btn-warning:hover { background-color: #c99560 !important; }
    .pel-card { border: none; border-radius: 12px; box-shadow: 0 1px 8px rgba(0,0,0,.07); height: 100%; transition: transform .15s, box-shadow .15s; }
    .pel-card:hover { transform: translateY(-3px); box-shadow: 0 4px 20px rgba(0,0,0,.1); }
    .status-badge { font-size: 11px; padding: 4px 10px; border-radius: 20px; font-weight: 600; }
    .hero { background: linear-gradient(135deg, var(--primary) 0%, var(--teal) 100%); color: #fff; padding: 40px 0 30px; }
    .hero h2 { font-size: 24px; font-weight: 700; }
    .badge-primary { background-color: var(--primary) !important; }
    .text-primary { color: var(--primary) !important; }
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg sticky-top">
  <div class="container">
    <a class="navbar-brand" href="../index.php">AMMDA Pelatih &amp; Alumni · BPPMDDTT</a>
    <div class="d-flex gap-2 ms-auto">
      <?php if (isset($_SESSION['logged_in'])): ?>
        <?php
        $dash = ['admin'=>'admin','instruktur'=>'instruktur','alumni'=>'alumni','peserta'=>'peserta'];
        $role = $_SESSION['level'];
        ?>
        <a href="<?= $dash[$role] ?>/index.php" class="btn btn-outline-light nav-btn">Dashboard</a>
        <a href="logout.php" class="btn btn-warning nav-btn">Keluar</a>
      <?php else: ?>
        <a href="login.php" class="btn btn-outline-light nav-btn">Masuk</a>
        <a href="register.php" class="btn btn-warning nav-btn">Daftar</a>
      <?php endif; ?>
    </div>
  </div>
</nav>

<!-- Hero -->
<div class="hero">
  <div class="container">
    <h2><i class="bi bi-journal-bookmark me-2"></i>Pelatihan Tersedia</h2>
    <p class="mb-3" style="opacity:.8">Daftarkan diri Anda ke program pelatihan BPPMDDTT Banjarmasin</p>
    <form class="d-flex gap-2" method="GET" style="max-width:400px">
      <input type="search" name="q" class="form-control" placeholder="Cari pelatihan..."
             value="<?= htmlspecialchars($search) ?>">
      <button class="btn btn-warning fw-semibold">Cari</button>
    </form>
  </div>
</div>

<div class="container py-4">

  <?php if ($pesan): ?>
    <div class="alert alert-success alert-dismissible fade show">
      <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($pesan) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show">
      <i class="bi bi-exclamation-circle-fill me-2"></i><?= htmlspecialchars($error) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <!-- Status pendaftaran saya -->
  <?php if (!empty($pendaftaran_saya)): ?>
  <div class="card mb-4" style="border:none;border-radius:12px;box-shadow:0 1px 8px rgba(0,0,0,.07)">
    <div class="card-header fw-semibold" style="background:#fff;border-radius:12px 12px 0 0">
      <i class="bi bi-list-check me-2 text-primary"></i>Status Pendaftaran Saya
    </div>
    <div class="card-body p-3">
      <div class="row g-2">
        <?php foreach ($pendaftaran_saya as $pid => $status):
          $p = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT nama_pelatihan FROM pelatihan WHERE id=$pid"));
          $badge = ['menunggu'=>'warning','diterima'=>'success','ditolak'=>'danger'];
          $label = ['menunggu'=>'Menunggu Verifikasi','diterima'=>'Diterima','ditolak'=>'Ditolak'];
        ?>
        <div class="col-md-6">
          <div class="d-flex align-items-center gap-2 p-2 rounded" style="background:#f8f9fb;font-size:13px">
            <i class="bi bi-journal-bookmark text-primary"></i>
            <div class="flex-fill"><?= htmlspecialchars($p['nama_pelatihan'] ?? '-') ?></div>
            <span class="badge bg-<?= $badge[$status] ?> status-badge"><?= $label[$status] ?></span>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- Daftar Pelatihan -->
  <div class="row g-3">
    <?php $count = 0; while ($p = mysqli_fetch_assoc($pelatihan)): $count++;
      $sisa   = $p['kuota'] - $p['jml_peserta'];
      $status = $pendaftaran_saya[$p['id']] ?? null;
    ?>
    <div class="col-md-6 col-lg-4">
      <div class="card pel-card">
        <div class="card-body p-4">
          <div class="d-flex justify-content-between align-items-start mb-2">
            <span class="badge bg-primary bg-opacity-10 text-primary" style="font-size:11px"><?= htmlspecialchars($p['jenis'] ?? 'Umum') ?></span>
            <span class="badge <?= $sisa>0?'bg-success':'bg-danger' ?> bg-opacity-10 <?= $sisa>0?'text-success':'text-danger' ?>" style="font-size:11px">
              <?= $sisa > 0 ? "$sisa tempat" : 'Penuh' ?>
            </span>
          </div>
          <h6 class="fw-bold mb-2"><?= htmlspecialchars($p['nama_pelatihan']) ?></h6>
          <div style="font-size:12px;color:#6b7280" class="mb-3">
            <div class="mb-1"><i class="bi bi-person me-1"></i><?= htmlspecialchars($p['nama_instruktur']) ?></div>
            <div class="mb-1"><i class="bi bi-calendar me-1"></i><?= date('d M Y', strtotime($p['tanggal_mulai'])) ?> - <?= date('d M Y', strtotime($p['tanggal_selesai'])) ?></div>
            <?php if ($p['lokasi']): ?>
            <div class="mb-1"><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($p['lokasi']) ?></div>
            <?php endif; ?>
            <div><i class="bi bi-people me-1"></i><?= $p['jml_peserta'] ?>/<?= $p['kuota'] ?> peserta terdaftar</div>
          </div>

          <?php if (!isset($_SESSION['logged_in'])): ?>
            <a href="login.php" class="btn btn-sm btn-primary w-100">
              <i class="bi bi-box-arrow-in-right me-1"></i> Masuk untuk Daftar
            </a>
          <?php elseif ($_SESSION['level'] !== 'peserta'): ?>
            <button class="btn btn-sm btn-secondary w-100" disabled>Hanya untuk Peserta</button>
          <?php elseif ($status === 'menunggu'): ?>
            <button class="btn btn-sm btn-warning w-100" disabled>
              <i class="bi bi-hourglass-split me-1"></i> Menunggu Verifikasi
            </button>
          <?php elseif ($status === 'diterima'): ?>
            <button class="btn btn-sm btn-success w-100" disabled>
              <i class="bi bi-check-circle me-1"></i> Sudah Diterima
            </button>
          <?php elseif ($status === 'ditolak'): ?>
            <button class="btn btn-sm btn-danger w-100" disabled>
              <i class="bi bi-x-circle me-1"></i> Ditolak
            </button>
          <?php elseif ($sisa > 0): ?>
            <a href="daftar_pelatihan.php?daftar=<?= $p['id'] ?>" class="btn btn-sm btn-primary w-100"
               onclick="return confirm('Daftar pelatihan ini?')">
              <i class="bi bi-plus-lg me-1"></i> Daftar Sekarang
            </a>
          <?php else: ?>
            <button class="btn btn-sm btn-secondary w-100" disabled>Kuota Penuh</button>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <?php endwhile; ?>

    <?php if ($count === 0): ?>
      <div class="col-12">
        <div class="card p-5 text-center" style="border:none;border-radius:12px">
          <i class="bi bi-calendar-x text-muted" style="font-size:48px"></i>
          <h6 class="mt-3 text-muted">Tidak ada pelatihan tersedia saat ini</h6>
        </div>
      </div>
    <?php endif; ?>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
