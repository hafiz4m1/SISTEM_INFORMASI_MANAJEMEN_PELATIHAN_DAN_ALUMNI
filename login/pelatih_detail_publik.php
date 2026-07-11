<?php
session_start();
include 'koneksi.php';
include 'security.php';

// Pastikan kolom foto ada di tabel instruktur (mengikuti pola di instruktur/profile.php)
$checkFotoColumn = mysqli_query($koneksi, "SHOW COLUMNS FROM instruktur LIKE 'foto'");
if (mysqli_num_rows($checkFotoColumn) === 0) {
    mysqli_query($koneksi, "ALTER TABLE instruktur ADD COLUMN foto varchar(255) DEFAULT NULL");
}

// Validasi id dari URL
$id = isset($_GET['id']) ? $_GET['id'] : null;
if (!validasiAngka($id, 1)) {
    header("location: semua_pelatih.php");
    exit;
}
$id = (int) $id;

// Ambil data instruktur + user
$q = mysqli_query($koneksi, "
    SELECT i.*, u.name, u.email
    FROM instruktur i
    JOIN users u ON i.user_id = u.id
    WHERE i.id = $id
    LIMIT 1
");
$instruktur = mysqli_fetch_assoc($q);

if (!$instruktur) {
    http_response_code(404);
}

if ($instruktur) {
    // Daftar pelatihan yang diampu instruktur ini
    $pelatihan = mysqli_query($koneksi, "
        SELECT p.id, p.nama_pelatihan, p.jenis, p.tanggal_mulai, p.tanggal_selesai,
               p.lokasi, p.status, p.kuota,
               (SELECT COUNT(*) FROM peserta_pelatihan pp WHERE pp.pelatihan_id = p.id) AS jml_peserta
        FROM pelatihan p
        WHERE p.instruktur_id = $id
        ORDER BY p.tanggal_mulai DESC
    ");
    $jml_pelatihan = mysqli_num_rows($pelatihan);

    $totalPeserta = mysqli_fetch_assoc(mysqli_query($koneksi, "
        SELECT COUNT(*) AS total
        FROM peserta_pelatihan pp
        JOIN pelatihan p ON pp.pelatihan_id = p.id
        WHERE p.instruktur_id = $id
    "))['total'];
}

$statusLabel = [
    'aktif'       => ['Aktif', 'bg-success bg-opacity-10 text-success'],
    'selesai'     => ['Selesai', 'bg-secondary bg-opacity-10 text-secondary'],
    'dibatalkan'  => ['Dibatalkan', 'bg-danger bg-opacity-10 text-danger'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $instruktur ? e($instruktur['name']) . ' - Detail Pelatih' : 'Pelatih Tidak Ditemukan' ?> | BPPMDDTT</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    :root { --primary: #223D5C; --primary-dark: #1A2D48; --gold: #D4A473; }
    body { background:#f4f6fb; color:#1a2942; font-family:Segoe UI, sans-serif; }
    .navbar { background: var(--primary); }
    .navbar-brand, .navbar-nav .nav-link { color:#fff !important; }
    .nav-link:hover { color:#e2e8f0 !important; }
    .btn-primary { background: var(--primary); border-color: var(--primary); }
    .btn-primary:hover { background: var(--primary-dark); border-color: var(--primary-dark); }
    .profile-card { border:none; border-radius:16px; box-shadow:0 10px 30px rgba(34,61,92,.10); }
    .avatar { width:110px; height:110px; border-radius:50%; object-fit:cover; background:#e9ecef; display:flex; align-items:center; justify-content:center; color:#9aa5b1; font-size:2.5rem; margin:0 auto; }
    .stat-box { background:#f4f6fb; border-radius:12px; padding:14px 10px; text-align:center; }
    .stat-box .num { font-size:1.3rem; font-weight:700; color:var(--primary); }
    .stat-box .label { font-size:.78rem; color:#6b7280; }
    .table thead th { text-transform:uppercase; letter-spacing:.05em; font-size:.75rem; color:#6b7280; }
    .status-badge { border-radius:999px; padding:.3rem .7rem; font-size:.78rem; font-weight:600; }
  </style>
</head>
<body>
<nav class="navbar navbar-expand-lg sticky-top">
  <div class="container">
    <a class="navbar-brand" href="Infoalumni_pelatih.php">BPPMDDTT | Detail Pelatih</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navMenu">
      <ul class="navbar-nav ms-auto align-items-lg-center">
        <li class="nav-item"><a class="nav-link" href="semua_pelatih.php">Semua Pelatih</a></li>
        <li class="nav-item"><a class="nav-link" href="Infoalumni_pelatih.php">Kembali</a></li>
      </ul>
    </div>
  </div>
</nav>

<main class="py-5">
  <div class="container" style="max-width:900px;">

    <?php if (!$instruktur): ?>

      <div class="text-center py-5">
        <i class="bi bi-person-x" style="font-size:3rem; color:#c9d2dc;"></i>
        <h4 class="mt-3">Data pelatih tidak ditemukan</h4>
        <p class="text-muted">Pelatih yang Anda cari mungkin sudah tidak tersedia.</p>
        <a href="semua_pelatih.php" class="btn btn-primary mt-2">Lihat Semua Pelatih</a>
      </div>

    <?php else: ?>

      <a href="Infoalumni_pelatih.php" class="text-decoration-none text-muted d-inline-flex align-items-center mb-3" style="font-size:.9rem;">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Info Alumni & Pelatih
      </a>

      <div class="card profile-card mb-4">
        <div class="card-body p-4 p-md-5">
          <div class="row align-items-center g-4">
            <div class="col-md-3 text-center">
              <?php if (!empty($instruktur['foto']) && file_exists(__DIR__ . '/../assets/images/profiles/' . $instruktur['foto'])): ?>
                <img src="../assets/images/profiles/<?= e($instruktur['foto']) ?>" alt="Foto <?= e($instruktur['name']) ?>" class="avatar">
              <?php else: ?>
                <div class="avatar"><i class="bi bi-person-badge"></i></div>
              <?php endif; ?>
            </div>
            <div class="col-md-9">
              <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold mb-2">Pelatih / Instruktur</span>
              <h2 class="h4 mb-1"><?= e($instruktur['name']) ?></h2>
              <p class="text-muted mb-3">
                <?= e($instruktur['bidang_keahlian'] ?: '-') ?>
                <?php if (!empty($instruktur['pendidikan'])): ?>
                  &middot; <?= e($instruktur['pendidikan']) ?>
                <?php endif; ?>
              </p>
              <div class="row row-cols-2 g-2" style="max-width:320px;">
                <div class="col"><div class="stat-box"><div class="num"><?= $jml_pelatihan ?></div><div class="label">Pelatihan Diampu</div></div></div>
                <div class="col"><div class="stat-box"><div class="num"><?= $totalPeserta ?></div><div class="label">Total Peserta</div></div></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="card profile-card">
        <div class="card-body p-4">
          <h5 class="mb-3"><i class="bi bi-journal-bookmark text-primary me-2"></i>Pelatihan yang Diampu</h5>
          <?php if ($jml_pelatihan === 0): ?>
            <p class="text-muted mb-0">Belum mengampu pelatihan apapun.</p>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead>
                  <tr>
                    <th>Nama Pelatihan</th>
                    <th>Jenis</th>
                    <th>Tanggal</th>
                    <th>Peserta</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <?php while ($p = mysqli_fetch_assoc($pelatihan)):
                    [$label, $class] = $statusLabel[$p['status']] ?? [ucfirst($p['status']), 'bg-secondary bg-opacity-10 text-secondary'];
                  ?>
                  <tr>
                    <td class="fw-semibold"><?= e($p['nama_pelatihan']) ?></td>
                    <td><?= e($p['jenis'] ?: '-') ?></td>
                    <td><?= date('d M Y', strtotime($p['tanggal_mulai'])) ?> - <?= date('d M Y', strtotime($p['tanggal_selesai'])) ?></td>
                    <td><?= $p['jml_peserta'] ?> / <?= $p['kuota'] ?></td>
                    <td><span class="status-badge <?= $class ?>"><?= e($label) ?></span></td>
                  </tr>
                  <?php endwhile; ?>
                </tbody>
              </table>
            </div>
          <?php endif; ?>
        </div>
      </div>

    <?php endif; ?>

  </div>
</main>

<footer class="py-4 bg-white border-top mt-4">
  <div class="container text-center text-muted" style="font-size:.9rem;">
    &copy; <?= date('Y') ?> BPPMDDTT Banjarmasin · Sistem Informasi Alumni & Pelatih
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
