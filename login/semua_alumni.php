<?php
include 'koneksi.php';

$alumni = mysqli_query($koneksi,
    "SELECT a.id, u.name, COALESCE(a.tempat_lahir, a.alamat, '-') AS asal,
            a.tanggal_lulus, a.jenis_kelamin,
            (SELECT COUNT(*) FROM peserta_pelatihan pp WHERE pp.user_id=a.user_id) AS jml_pelatihan
     FROM alumni a
     JOIN users u ON a.user_id=u.id
     ORDER BY u.name ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Semua Alumni | BPPMDDTT</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    :root { --primary: #223D5C; --card-bg: #fff; }
    body { background:#f4f6fb; color:#1a2942; font-family:Segoe UI, sans-serif; }
    .navbar { background: var(--primary); }
    .navbar-brand, .navbar-nav .nav-link { color:#fff !important; }
    .navbar-nav .nav-link:hover { color:#e2e8f0 !important; }
    .card { border:none; border-radius:16px; box-shadow:0 10px 28px rgba(34,61,92,.08); }
    .badge-pill { border-radius:999px; }
    .section-title { font-size:1.75rem; font-weight:700; margin-bottom:.25rem; }
    .section-sub { color:#6b7280; margin-bottom:1.75rem; }
    .table thead th { text-transform:uppercase; letter-spacing:.05em; font-size:.8rem; }
    @media (max-width:767px) { .section-title { font-size:1.4rem; } }
  </style>
</head>
<body>
<nav class="navbar navbar-expand-lg sticky-top">
  <div class="container">
    <a class="navbar-brand" href="Infoalumni_pelatih.php">BPPMDDTT | Semua Alumni</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navMenu">
      <ul class="navbar-nav ms-auto align-items-lg-center">
        <li class="nav-item"><a class="nav-link" href="Infoalumni_pelatih.php">Kembali</a></li>
      </ul>
    </div>
  </div>
</nav>
<main class="py-5">
  <div class="container">
    <div class="text-center mb-5">
      <p class="text-uppercase text-primary fw-semibold mb-2">Data Lengkap</p>
      <h1 class="section-title">Semua Alumni</h1>
      <p class="section-sub mx-auto" style="max-width:680px;">Menampilkan daftar semua alumni beserta asal, jumlah pelatihan yang diikuti, dan status kelulusan.</p>
    </div>
    <div class="card mb-4">
      <div class="card-body p-3">
        <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between">
          <div class="fw-semibold">Total Alumni: <?= mysqli_num_rows($alumni) ?></div>
          <a href="Infoalumni_pelatih.php" class="btn btn-outline-primary btn-sm">Kembali ke Info Alumni & Pelatih</a>
        </div>
      </div>
    </div>
    <div class="table-responsive">
      <table class="table table-hover align-middle">
        <thead class="bg-white">
          <tr>
            <th>No</th>
            <th>Nama</th>
            <th>Asal</th>
            <th>Pelatihan</th>
            <th>Tanggal Lulus</th>
            <th>Jenis Kelamin</th>
          </tr>
        </thead>
        <tbody>
          <?php $no = 1; while ($row = mysqli_fetch_assoc($alumni)): ?>
          <tr>
            <td><?= $no++ ?></td>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= htmlspecialchars($row['asal']) ?></td>
            <td><span class="badge bg-primary"><?= $row['jml_pelatihan'] ?></span></td>
            <td><?= $row['tanggal_lulus'] ? date('d M Y', strtotime($row['tanggal_lulus'])) : '-' ?></td>
            <td><?= htmlspecialchars($row['jenis_kelamin'] ?: '-') ?></td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>
<footer class="py-4 bg-white border-top">
  <div class="container text-center text-muted" style="font-size:.9rem;">
    &copy; <?= date('Y') ?> BPPMDDTT Banjarmasin
  </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
