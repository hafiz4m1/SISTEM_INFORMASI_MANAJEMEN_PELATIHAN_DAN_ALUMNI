<?php
session_start();
include 'koneksi.php';
include 'security.php';

// Validasi id dari URL
$id = isset($_GET['id']) ? $_GET['id'] : null;
if (!validasiAngka($id, 1)) {
    header("location: semua_alumni.php");
    exit;
}
$id = (int) $id;

// Ambil data alumni + user
$q = mysqli_query($koneksi, "
    SELECT a.*, u.name, u.email
    FROM alumni a
    JOIN users u ON a.user_id = u.id
    WHERE a.id = $id
    LIMIT 1
");
$alumni = mysqli_fetch_assoc($q);

if (!$alumni) {
    http_response_code(404);
}

if ($alumni) {
    // Riwayat pelatihan yang diikuti alumni ini
    $riwayat = mysqli_query($koneksi, "
        SELECT p.nama_pelatihan, p.jenis, p.tanggal_mulai, p.tanggal_selesai,
               p.lokasi, pp.status_verifikasi
        FROM peserta_pelatihan pp
        JOIN pelatihan p ON pp.pelatihan_id = p.id
        WHERE pp.user_id = {$alumni['user_id']}
        ORDER BY p.tanggal_mulai DESC
    ");

    // Kompetensi alumni
    $kompetensi = mysqli_query($koneksi, "
        SELECT k.nama_kompetensi, k.kategori, ak.sumber
        FROM alumni_kompetensi ak
        JOIN kompetensi k ON ak.kompetensi_id = k.id
        WHERE ak.alumni_id = $id
        ORDER BY k.nama_kompetensi ASC
    ");

    $jml_pelatihan = mysqli_num_rows($riwayat);
}

$sumberLabel = [
    'pelatihan'         => 'Pelatihan',
    'mandiri'           => 'Belajar Mandiri',
    'pengalaman_kerja'  => 'Pengalaman Kerja',
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $alumni ? e($alumni['name']) . ' - Detail Alumni' : 'Alumni Tidak Ditemukan' ?> | BPPMDDTT</title>
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
    .btn-outline-primary { color: var(--primary); border-color: var(--primary); }
    .btn-outline-primary:hover { background: var(--primary); border-color: var(--primary); }
    .profile-card { border:none; border-radius:16px; box-shadow:0 10px 30px rgba(34,61,92,.10); }
    .avatar { width:110px; height:110px; border-radius:50%; object-fit:cover; background:#e9ecef; display:flex; align-items:center; justify-content:center; color:#9aa5b1; font-size:2.5rem; margin:0 auto; }
    .stat-box { background:#f4f6fb; border-radius:12px; padding:14px 10px; text-align:center; }
    .stat-box .num { font-size:1.3rem; font-weight:700; color:var(--primary); }
    .stat-box .label { font-size:.78rem; color:#6b7280; }
    .badge-kompetensi { background:#eef2f7; color:var(--primary); border-radius:999px; padding:.4rem .8rem; font-size:.8rem; font-weight:600; display:inline-flex; align-items:center; gap:.35rem; }
    .table thead th { text-transform:uppercase; letter-spacing:.05em; font-size:.75rem; color:#6b7280; }
    .status-badge { border-radius:999px; padding:.3rem .7rem; font-size:.78rem; font-weight:600; }
  </style>
</head>
<body>
<nav class="navbar navbar-expand-lg sticky-top">
  <div class="container">
    <a class="navbar-brand" href="Infoalumni_pelatih.php">BPPMDDTT | Detail Alumni</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navMenu">
      <ul class="navbar-nav ms-auto align-items-lg-center">
        <li class="nav-item"><a class="nav-link" href="semua_alumni.php">Semua Alumni</a></li>
        <li class="nav-item"><a class="nav-link" href="Infoalumni_pelatih.php">Kembali</a></li>
      </ul>
    </div>
  </div>
</nav>

<main class="py-5">
  <div class="container" style="max-width:900px;">

    <?php if (!$alumni): ?>

      <div class="text-center py-5">
        <i class="bi bi-person-x" style="font-size:3rem; color:#c9d2dc;"></i>
        <h4 class="mt-3">Data alumni tidak ditemukan</h4>
        <p class="text-muted">Alumni yang Anda cari mungkin sudah tidak tersedia.</p>
        <a href="semua_alumni.php" class="btn btn-primary mt-2">Lihat Semua Alumni</a>
      </div>

    <?php else: ?>

      <a href="Infoalumni_pelatih.php" class="text-decoration-none text-muted d-inline-flex align-items-center mb-3" style="font-size:.9rem;">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Info Alumni & Pelatih
      </a>

      <div class="card profile-card mb-4">
        <div class="card-body p-4 p-md-5">
          <div class="row align-items-center g-4">
            <div class="col-md-3 text-center">
              <?php if (!empty($alumni['foto']) && file_exists(__DIR__ . '/../assets/images/profiles/' . $alumni['foto'])): ?>
                <img src="../assets/images/profiles/<?= e($alumni['foto']) ?>" alt="Foto <?= e($alumni['name']) ?>" class="avatar">
              <?php else: ?>
                <div class="avatar"><i class="bi bi-person"></i></div>
              <?php endif; ?>
            </div>
            <div class="col-md-9">
              <span class="badge bg-primary bg-opacity-10 text-primary fw-semibold mb-2">Alumni</span>
              <h2 class="h4 mb-1"><?= e($alumni['name']) ?></h2>
              <p class="text-muted mb-3">
                <?= e($alumni['tempat_lahir'] ?: '-') ?>
                <?php if (!empty($alumni['tanggal_lahir'])): ?>
                  &middot; <?= date('d M Y', strtotime($alumni['tanggal_lahir'])) ?>
                <?php endif; ?>
              </p>
              <div class="row row-cols-3 g-2" style="max-width:420px;">
                <div class="col"><div class="stat-box"><div class="num"><?= $jml_pelatihan ?></div><div class="label">Pelatihan</div></div></div>
                <div class="col"><div class="stat-box"><div class="num"><?= $alumni['jenis_kelamin'] ?: '-' ?></div><div class="label">Jenis Kelamin</div></div></div>
                <div class="col"><div class="stat-box"><div class="num" style="font-size:1rem;"><?= $alumni['tanggal_lulus'] ? date('Y', strtotime($alumni['tanggal_lulus'])) : '-' ?></div><div class="label">Tahun Lulus</div></div></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="card profile-card mb-4">
        <div class="card-body p-4">
          <h5 class="mb-3"><i class="bi bi-award text-primary me-2"></i>Kompetensi</h5>
          <?php if (mysqli_num_rows($kompetensi) === 0): ?>
            <p class="text-muted mb-0">Belum ada data kompetensi.</p>
          <?php else: ?>
            <div class="d-flex flex-wrap gap-2">
              <?php while ($k = mysqli_fetch_assoc($kompetensi)): ?>
                <span class="badge-kompetensi">
                  <i class="bi bi-check-circle-fill"></i>
                  <?= e($k['nama_kompetensi']) ?>
                  <span class="text-muted fw-normal">&middot; <?= e($sumberLabel[$k['sumber']] ?? $k['sumber']) ?></span>
                </span>
              <?php endwhile; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>

      <div class="card profile-card">
        <div class="card-body p-4">
          <h5 class="mb-3"><i class="bi bi-journal-bookmark text-primary me-2"></i>Riwayat Pelatihan</h5>
          <?php if ($jml_pelatihan === 0): ?>
            <p class="text-muted mb-0">Belum pernah mengikuti pelatihan.</p>
          <?php else: ?>
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead>
                  <tr>
                    <th>Nama Pelatihan</th>
                    <th>Jenis</th>
                    <th>Tanggal</th>
                    <th>Lokasi</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <?php mysqli_data_seek($riwayat, 0); while ($r = mysqli_fetch_assoc($riwayat)):
                    $statusClass = [
                      'diterima' => 'bg-success bg-opacity-10 text-success',
                      'menunggu' => 'bg-warning bg-opacity-10 text-warning',
                      'ditolak'  => 'bg-danger bg-opacity-10 text-danger',
                    ][$r['status_verifikasi']] ?? 'bg-secondary bg-opacity-10 text-secondary';
                  ?>
                  <tr>
                    <td class="fw-semibold"><?= e($r['nama_pelatihan']) ?></td>
                    <td><?= e($r['jenis'] ?: '-') ?></td>
                    <td><?= date('d M Y', strtotime($r['tanggal_mulai'])) ?> - <?= date('d M Y', strtotime($r['tanggal_selesai'])) ?></td>
                    <td><?= e($r['lokasi'] ?: '-') ?></td>
                    <td><span class="status-badge <?= $statusClass ?>"><?= ucfirst($r['status_verifikasi']) ?></span></td>
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
