<?php
session_start();
include 'koneksi.php';

$alumni = mysqli_query($koneksi, "SELECT a.id, u.name, COALESCE(a.tempat_lahir, a.alamat, '-') AS asal, a.tanggal_lulus, a.jenis_kelamin, (SELECT COUNT(*) FROM peserta_pelatihan pp WHERE pp.user_id=a.user_id) AS jml_pelatihan FROM alumni a JOIN users u ON a.user_id=u.id ORDER BY jml_pelatihan DESC, u.name ASC LIMIT 6");
$instruktur = mysqli_query($koneksi, "SELECT i.id, u.name, COALESCE(i.bidang_keahlian, i.pendidikan, u.email, '-') AS keahlian, (SELECT COUNT(*) FROM pelatihan p WHERE p.instruktur_id=i.id) AS jml_pelatihan, (SELECT COUNT(*) FROM peserta_pelatihan pp JOIN pelatihan p ON pp.pelatihan_id=p.id WHERE p.instruktur_id=i.id) AS jml_peserta FROM instruktur i JOIN users u ON i.user_id=u.id ORDER BY jml_pelatihan DESC, u.name ASC LIMIT 6");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Info Alumni & Pelatih | BPPMDDTT</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    :root { --primary: #223D5C; --primary-dark: #1A2D48; --card-bg: #fff; }
    body { background:#f4f6fb; color:#1a2942; font-family:Segoe UI, sans-serif; }
    .navbar { background: var(--primary); }
    .navbar-brand, .navbar-nav .nav-link { color:#fff !important; }
    .nav-link:hover { color:#e2e8f0 !important; }
    .btn-primary { background: var(--primary); border-color: var(--primary); }
    .btn-primary:hover { background: var(--primary-dark); border-color: var(--primary-dark); }
    .album-card { border:none; border-radius:16px; box-shadow:0 10px 30px rgba(34,61,92,.12); overflow:hidden; }
    .album-card .card-img-top { min-height:180px; background:#e9ecef; display:flex; align-items:center; justify-content:center; color:#6c757d; font-size:14px; }
    .album-card .badge-pill { border-radius:999px; }
    .section-title { font-size:1.75rem; font-weight:700; margin-bottom:.25rem; }
    .section-sub { color:#6b7280; margin-bottom:1.75rem; }
    .info-row { gap:1rem; }
    .meta-item { font-size:.85rem; color:#6b7280; }
    .meta-item span { font-weight:600; color:#1a2942; }
    @media (max-width:767px) { .section-title { font-size:1.4rem; } }
  </style>
</head>
<body>
<nav class="navbar navbar-expand-lg sticky-top">
  <div class="container">
    <a class="navbar-brand" href="../index.php">BPPMDDTT | Info Alumni & Pelatih</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navMenu">
      <ul class="navbar-nav ms-auto align-items-lg-center">
        <li class="nav-item"><a class="nav-link" href="#alumni">Alumni</a></li>
        <li class="nav-item"><a class="nav-link" href="#instruktur">Pelatih</a></li>
        <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
      </ul>
    </div>
  </div>
</nav>

<main class="py-5">
  <div class="container">
    <div class="text-center mb-5">
      <h1 class="section-title">Data Alumni dan Pelatih</h1>
      <p class="section-sub mx-auto" style="max-width:680px;">Menampilkan informasi nama, asal, pelatihan, dan detail instruktur dalam format kartu album yang bersih dan mudah dibaca.</p>
    </div>

    <section id="alumni" class="mb-5">
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-start mb-4 gap-3">
        <div>
          <small class="text-uppercase text-primary fw-semibold">Alumni</small>
          <h2 class="h4 mt-2">Data Alumni Teratas</h2>
          <p class="text-muted mb-0">Informasi alumni dengan jumlah pelatihan terbanyak dan data ringkas.</p>
        </div>
        <a href="semua_alumni.php" class="btn btn-outline-primary">Lihat Semua Alumni</a>
      </div>
      <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
        <?php while ($row = mysqli_fetch_assoc($alumni)): ?>
        <div class="col">
          <div class="card album-card h-100">
            <div class="card-img-top">Foto Alumni</div>
            <div class="card-body">
              <h5 class="card-title mb-2"><?= htmlspecialchars($row['name']) ?></h5>
              <p class="text-muted mb-2">Asal: <?= htmlspecialchars($row['asal']) ?></p>
              <div class="d-flex flex-wrap info-row mb-3">
                <div class="meta-item">Pelatihan: <span><?= $row['jml_pelatihan'] ?></span></div>
                <div class="meta-item">Lulus: <span><?= $row['tanggal_lulus'] ? date('d M Y', strtotime($row['tanggal_lulus'])) : '-' ?></span></div>
                <div class="meta-item">JK: <span><?= $row['jenis_kelamin'] ?: '-' ?></span></div>
              </div>
              <a href="alumni_detail_publik.php?id=<?= $row['id'] ?>" class="btn btn-primary btn-sm">Detail Alumni</a>
            </div>
          </div>
        </div>
        <?php endwhile; ?>
      </div>
    </section>

    <section id="instruktur">
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-start mb-4 gap-3">
        <div>
          <small class="text-uppercase text-primary fw-semibold">Pelatih</small>
          <h2 class="h4 mt-2">Data Instruktur Terbaik</h2>
          <p class="text-muted mb-0">Ringkasan pelatih dengan jumlah pelatihan dan peserta terbanyak.</p>
        </div>
        <a href="semua_pelatih.php" class="btn btn-outline-primary">Lihat Semua Pelatih</a>
      </div>
      <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
        <?php while ($row = mysqli_fetch_assoc($instruktur)): ?>
        <div class="col">
          <div class="card album-card h-100">
            <div class="card-img-top">Foto Pelatih</div>
            <div class="card-body">
              <h5 class="card-title mb-2"><?= htmlspecialchars($row['name']) ?></h5>
              <p class="text-muted mb-2">Keahlian: <?= htmlspecialchars($row['keahlian']) ?></p>
              <div class="d-flex flex-wrap info-row mb-3">
                <div class="meta-item">Pelatihan: <span><?= $row['jml_pelatihan'] ?></span></div>
                <div class="meta-item">Peserta: <span><?= $row['jml_peserta'] ?></span></div>
              </div>
              <a href="pelatih_detail_publik.php?id=<?= $row['id'] ?>" class="btn btn-primary btn-sm">Detail Pelatih</a>
            </div>
          </div>
        </div>
        <?php endwhile; ?>
      </div>
    </section>
  </div>
</main>

<footer class="py-4 bg-white border-top">
  <div class="container text-center text-muted" style="font-size:.9rem;">
    &copy; <?= date('Y') ?> BPPMDDTT Banjarmasin · Sistem Informasi Alumni & Pelatih
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>