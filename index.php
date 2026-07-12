<?php
/**
 * index.php - Landing page yang disempurnakan
 * Taruh di root sisinfoalumni/ (bukan di login/)
 */
session_start();

// Redirect jika sudah login
if (isset($_SESSION['logged_in'])) {
    $dash = ['admin'=>'admin','instruktur'=>'instruktur','alumni'=>'alumni','peserta'=>'peserta','kepala'=>'kepala'];
    header("location: login/" . ($dash[$_SESSION['level']] ?? 'peserta') . "/index.php"); exit;
}

include 'login/koneksi.php';
if (file_exists(__DIR__ . '/login/avatar_helper.php')) {
    include_once 'login/avatar_helper.php';
}
if (!function_exists('avatarSvg')) {
    // Fallback ringan kalau avatar_helper.php belum ada di folder login/
    function avatarSvg(string $name, int $size = 96, bool $rounded = true): string {
        return '<div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:#e9ecef;color:#9aa5b1;font-size:'.round($size*0.4).'px;border-radius:'.($rounded?'50%':'12px').'"><i class="bi bi-person"></i></div>';
    }
}

// Statistik dengan error handling
$total_pelatihan = 0;
$total_alumni    = 0;
$total_peserta   = 0;
$total_lulus     = 0;

$result = mysqli_query($koneksi, "SELECT COUNT(*) FROM pelatihan");
if ($result) {
    $total_pelatihan = mysqli_fetch_row($result)[0];
}

$result = mysqli_query($koneksi, "SELECT COUNT(*) FROM alumni");
if ($result) {
    $total_alumni = mysqli_fetch_row($result)[0];
}

$result = mysqli_query($koneksi, "SELECT COUNT(DISTINCT user_id) FROM peserta_pelatihan");
if ($result) {
    $total_peserta = mysqli_fetch_row($result)[0];
}

$result = mysqli_query($koneksi, "SELECT COUNT(*) FROM peserta_pelatihan WHERE status_lulus='lulus'");
if ($result) {
    $total_lulus = mysqli_fetch_row($result)[0];
}

$total_instruktur = 0;
$result = mysqli_query($koneksi, "SELECT COUNT(*) FROM instruktur");
if ($result) {
    $total_instruktur = mysqli_fetch_row($result)[0];
}

// Pelatihan aktif
$pelatihan = mysqli_query($koneksi, "
    SELECT p.*, ui.name as nama_instruktur,
        (SELECT COUNT(*) FROM peserta_pelatihan WHERE pelatihan_id=p.id AND status_verifikasi!='ditolak') as jml_peserta
    FROM pelatihan p
    JOIN instruktur i ON p.instruktur_id=i.id
    JOIN users ui ON i.user_id=ui.id
    WHERE p.status='aktif'
    ORDER BY p.tanggal_mulai ASC LIMIT 6
");

// Sebaran alumni
$sebaran_data = [];
$q = mysqli_query($koneksi, "SELECT tempat_lahir as kota, COUNT(*) as jumlah FROM alumni WHERE tempat_lahir IS NOT NULL AND tempat_lahir!='' GROUP BY tempat_lahir ORDER BY jumlah DESC LIMIT 8");
while ($q && ($r = mysqli_fetch_assoc($q))) $sebaran_data[] = $r;

$kategori_data = [];
$q2 = mysqli_query($koneksi, "SELECT p.jenis, COUNT(DISTINCT a.id) as jml FROM peserta_pelatihan pp JOIN pelatihan p ON pp.pelatihan_id=p.id JOIN alumni a ON a.user_id=pp.user_id WHERE p.jenis IS NOT NULL GROUP BY p.jenis ORDER BY jml DESC");
while ($q2 && ($r = mysqli_fetch_assoc($q2))) $kategori_data[] = $r;

$alumni_info = mysqli_query($koneksi, "SELECT a.id, u.name, COALESCE(NULLIF(a.tempat_lahir, ''), 'Kalimantan Selatan') as asal, (SELECT COUNT(*) FROM peserta_pelatihan pp WHERE pp.user_id=a.user_id) as jml_pelatihan FROM alumni a JOIN users u ON a.user_id=u.id ORDER BY jml_pelatihan DESC LIMIT 3");
$instruktur_info = mysqli_query($koneksi, "SELECT i.id, u.name, COALESCE(NULLIF(i.bidang_keahlian, ''), 'Instruktur BPPMDDTT') as asal, (SELECT COUNT(*) FROM pelatihan p WHERE p.instruktur_id=i.id) as jml_pelatihan, (SELECT COUNT(*) FROM peserta_pelatihan pp JOIN pelatihan p ON pp.pelatihan_id=p.id WHERE p.instruktur_id=i.id) as jml_peserta FROM instruktur i JOIN users u ON i.user_id=u.id ORDER BY jml_pelatihan DESC LIMIT 3");

// Top pelatihan
$top_pelatihan = mysqli_query($koneksi, "
    SELECT p.nama_pelatihan, p.jenis,
        COUNT(DISTINCT pp.user_id) as jml_peserta,
        AVG(ts.relevansi_pelatihan) as avg_relevansi,
        COUNT(DISTINCT CASE WHEN pp.status_lulus='lulus' THEN pp.user_id END) as jml_lulus
    FROM pelatihan p
    JOIN peserta_pelatihan pp ON pp.pelatihan_id=p.id
    LEFT JOIN alumni a ON a.user_id=pp.user_id
    LEFT JOIN tracer_study ts ON ts.alumni_id=a.id AND ts.status_pengisian='sudah_diisi'
    GROUP BY p.id HAVING jml_peserta > 0
    ORDER BY avg_relevansi DESC, jml_lulus DESC LIMIT 10
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Aplikasi Monitoring dan Manajemen Data Alumni, Pelatih | BPPMDDTT Banjarmasin</title>
  <meta name="description" content="Platform digital pengelolaan pelatihan, peserta, dan alumni BPPMDDTT Banjarmasin">
  <meta property="og:type" content="website">
  <meta property="og:title" content="Aplikasi Monitoring dan Manajemen Data Alumni, Pelatih | BPPMDDTT Banjarmasin">
  <meta property="og:description" content="Platform digital pengelolaan pelatihan, peserta, dan alumni BPPMDDTT Banjarmasin">
  <meta property="og:image" content="login/assets/images/balai1.jpg">
  <meta property="og:locale" content="id_ID">
  <link rel="shortcut icon" href="login/assets/images/favicon.ico">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
  <style>
    :root { --primary:#1a4c8e; --primary-dark:#0d3060; }
    body { font-size:14px; color:#1a2942; scroll-behavior:smooth; }

    /* Navbar */
    .navbar { background:var(--primary); padding:12px 0; position:sticky; top:0; z-index:100;
      box-shadow:0 2px 8px rgba(0,0,0,.15); }
    .navbar-brand { color:#fff !important; font-weight:700; font-size:15px; }
    .navbar-brand span { opacity:.7; font-weight:400; }
    .navbar-logo { height:32px; object-fit:contain; margin-right:10px; opacity:.95; }
    .nav-btn { font-size:13px; border-radius:8px; padding:6px 20px; font-weight:600; }

    /* Hero */
    .hero { background:linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
      color:#fff; padding:80px 0 60px; position:relative; overflow:hidden; }
    .hero::before { content:''; position:absolute; top:-50%; right:-10%;
      width:600px; height:600px; border-radius:50%;
      background:rgba(255,255,255,.04); pointer-events:none; }
    .hero h1 { font-size:34px; font-weight:800; line-height:1.25; }
    .hero p  { font-size:15px; opacity:.85; max-width:560px; }
    .hero-badge { background:rgba(255,255,255,.15); color:#fff; font-size:12px;
      border-radius:20px; padding:4px 14px; display:inline-block; margin-bottom:16px; }
    .hero-btns .btn { font-size:14px; padding:10px 28px; border-radius:10px; font-weight:600; }
    .hero-logo-badge { background:rgba(255,255,255,.12); border:1px solid rgba(255,255,255,.2);
      border-radius:12px; padding:10px 14px; display:inline-flex; align-items:center; gap:8px;
      margin-bottom:16px; font-size:12px; width:fit-content; }
    .hero-logo-badge img { height:28px; object-fit:contain; }

    /* Stats */
    .stat-item { text-align:center; padding:24px 16px; }
    .stat-item .val { font-size:42px; font-weight:800; color:var(--primary); line-height:1; }
    .stat-item .lbl { font-size:13px; color:#6b7280; margin-top:4px; }
    .stat-item .ico { font-size:28px; margin-bottom:8px; }

    /* Section */
    .section-badge { font-size:12px; font-weight:700; text-transform:uppercase;
      letter-spacing:.08em; color:var(--primary); margin-bottom:6px; }
    .section-title { font-size:24px; font-weight:800; color:#1a2942; margin-bottom:8px; }
    .section-sub   { font-size:14px; color:#6b7280; max-width:560px; }

    /* Pelatihan card */
    .pel-card { border:none; border-radius:14px; box-shadow:0 2px 12px rgba(0,0,0,.07);
      height:100%; transition:transform .2s, box-shadow .2s; }
    .pel-card:hover { transform:translateY(-4px); box-shadow:0 8px 28px rgba(0,0,0,.12); }
    .pel-card .card-body { padding:22px; }
    .pel-kuota { font-size:11px; border-radius:20px; padding:3px 10px; font-weight:600; }

    /* Fitur */
    .fitur-card { border:none; border-radius:14px; padding:28px 24px;
      box-shadow:0 2px 12px rgba(0,0,0,.06); height:100%; transition:transform .2s; }
    .fitur-card:hover { transform:translateY(-3px); }
    .fitur-icon { width:52px; height:52px; border-radius:12px; display:flex;
      align-items:center; justify-content:center; font-size:24px; margin-bottom:14px; }

    /* Tentang Kami */
    .tentang-photo { position:relative; margin-bottom:34px; }
    .tentang-photo img { width:100%; height:340px; object-fit:cover; border-radius:20px;
      box-shadow:0 16px 36px rgba(26,76,142,.18); }
    .tentang-float-card { position:absolute; bottom:-22px; left:24px; background:#fff;
      border-radius:14px; padding:14px 20px; box-shadow:0 10px 26px rgba(0,0,0,.14);
      display:flex; align-items:center; gap:12px; }
    .tentang-float-card .ico { width:44px; height:44px; border-radius:10px; background:var(--primary);
      color:#fff; display:flex; align-items:center; justify-content:center; font-size:20px; flex-shrink:0; }
    .tentang-float-card .val { font-size:18px; font-weight:800; color:#1a2942; line-height:1.1; }
    .tentang-float-card .lbl { font-size:11px; color:#6b7280; }
    .tentang-corner-badge { position:absolute; top:16px; right:16px; background:rgba(26,76,142,.92);
      color:#fff; font-size:11px; font-weight:700; letter-spacing:.04em; text-transform:uppercase;
      border-radius:20px; padding:6px 14px; backdrop-filter:blur(2px); }

    .mini-stat-row { display:flex; gap:28px; margin:20px 0 24px; flex-wrap:wrap; }
    .mini-stat .val { font-size:26px; font-weight:800; color:var(--primary); line-height:1; }
    .mini-stat .lbl { font-size:12px; color:#6b7280; margin-top:3px; }

    .visi-list { list-style:none; padding:0; margin:0 0 26px; }
    .visi-list li { display:flex; align-items:flex-start; gap:10px; margin-bottom:10px;
      font-size:13.5px; color:#374151; }
    .visi-list li i { color:#1d9e75; font-size:16px; margin-top:2px; flex-shrink:0; }

    .layanan-card { border:none; border-radius:14px; padding:24px 20px; height:100%;
      background:#fff; box-shadow:0 2px 12px rgba(0,0,0,.06); transition:transform .2s, box-shadow .2s; }
    .layanan-card:hover { transform:translateY(-4px); box-shadow:0 10px 26px rgba(0,0,0,.1); }
    .layanan-num { font-size:11px; font-weight:800; color:#c3cedb; letter-spacing:.05em; margin-bottom:10px; }

    /* Alumni & Pelatih Unggulan */
    .unggulan-card { background:#fff; border-radius:14px; padding:16px; display:flex;
      align-items:center; gap:14px; box-shadow:0 2px 12px rgba(0,0,0,.06); transition:transform .2s, box-shadow .2s; }
    .unggulan-card:hover { transform:translateY(-3px); box-shadow:0 8px 22px rgba(0,0,0,.1); }
    .unggulan-avatar { width:52px; height:52px; border-radius:50%; overflow:hidden; flex-shrink:0; background:#e9ecef; }
    .unggulan-avatar img { width:100%; height:100%; object-fit:cover; }
    .unggulan-name { font-weight:700; font-size:14px; color:#1a2942; margin-bottom:2px; }
    .unggulan-asal { font-size:12px; color:#6b7280; }
    .unggulan-badge { font-size:11px; font-weight:700; color:var(--primary); background:#eef2f7;
      border-radius:20px; padding:3px 10px; white-space:nowrap; }
    .unggulan-col-title { font-size:13px; font-weight:700; color:#1a2942; margin-bottom:14px;
      display:flex; align-items:center; gap:8px; }
    .unggulan-col-title i { color:var(--primary); font-size:16px; }

    /* Top 10 */
    .rank-num { width:28px; height:28px; border-radius:50%; display:inline-flex;
      align-items:center; justify-content:center; font-size:12px; font-weight:700; flex-shrink:0; }
    .rank-1 { background:#FFD700; color:#7a5c00; }
    .rank-2 { background:#C0C0C0; color:#444; }
    .rank-3 { background:#CD7F32; color:#5a3510; }
    .rank-n { background:#f0f0f0; color:#555; }
    .stars  { color:#ffc107; font-size:12px; }

    /* Chart */
    .chart-wrap { background:#fff; border-radius:14px; padding:20px;
      box-shadow:0 2px 12px rgba(0,0,0,.06); }

    /* CTA */
    .cta-section { background:linear-gradient(135deg, var(--primary), var(--primary-dark));
      color:#fff; border-radius:20px; padding:52px 40px; text-align:center; }
    .cta-section h3 { font-size:26px; font-weight:800; margin-bottom:8px; }
    .cta-section p  { opacity:.85; margin-bottom:24px; }

    /* Footer */
    footer { background:#1a2942; color:rgba(255,255,255,.6); padding:40px 0 24px; }
    footer .footer-brand { font-size:15px; font-weight:700; color:#fff; margin-bottom:6px; }
    footer a { color:rgba(255,255,255,.6); text-decoration:none; }
    footer a:hover { color:#fff; }
    footer .footer-bottom { border-top:1px solid rgba(255,255,255,.1); margin-top:28px; padding-top:20px;
      font-size:12px; text-align:center; }

    /* Scroll reveal — konten SELALU tampil secara default (tidak bergantung JS/timing sama sekali).
       Kelas .visible hanya menambah animasi geser halus kalau sempat ditambahkan oleh JS,
       tapi kontennya sendiri tidak pernah disembunyikan. */
    .reveal { opacity:1; transform:none; }
    .reveal.pre-anim { opacity:0; transform:translateY(20px); }
    .reveal.visible { opacity:1; transform:none; transition:all .6s ease; }
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg">
  <div class="container">
    <a class="navbar-brand d-flex align-items-center" href="index.php">
      <img src="assets/images/LOGO-KEMENTRANS-Bulat.png" alt="Kementerian Transmigrasi" class="navbar-logo">
      <span>Monitoring Alumni &amp; Pelatih <span style="opacity:.6">· BPPMDDTT</span></span>
    </a>
    <div class="d-flex align-items-center gap-2 ms-auto">
      <a href="login/login.php"    class="btn btn-outline-light nav-btn">Login</a>
      <a href="login/register.php" class="btn btn-warning nav-btn">Daftar</a>
    </div>
  </div>
</nav>

<!-- Hero -->
<section class="hero">
  <div class="container">
    <div class="row align-items-center">
      <div class="col-lg-7">
        <div class="hero-logo-badge">
          <img src="assets/images/LOGO-KEMENTRANS-Bulat.png" alt="Kementerian Transmigrasi">
          <span>Kementerian Desa, PDT, dan Transmigrasi</span>
        </div>
        <h1>Sistem Informasi<br>Manajemen Pelatihan<br>&amp; Alumni</h1>
        <p class="mt-3 mb-4">Platform digital untuk pengelolaan data pelatihan, peserta, dan alumni
          Balai Pelatihan dan Pemberdayaan Masyarakat Desa, Daerah Tertinggal, dan Transmigrasi Banjarmasin.</p>
        <div class="hero-btns d-flex gap-3 flex-wrap">
          <a href="login/daftar_pelatihan.php" class="btn btn-warning">
            <i class="bi bi-journal-bookmark me-1"></i> Lihat Pelatihan
          </a>
          <a href="login/Infoalumni_pelatih.php" class="btn btn-outline-light">
            <i class="bi bi-people me-1"></i> Info Alumni &amp; Pelatih
          </a>
          <a href="login/register.php" class="btn btn-outline-light">
            <i class="bi bi-person-plus me-1"></i> Daftar Sekarang
          </a>
        </div>
      </div>
      <div class="col-lg-5 d-none d-lg-block text-end">
        <i class="bi bi-building-fill-check" style="font-size:140px;opacity:.1;color:#fff"></i>
      </div>
    </div>
  </div>
</section>

<!-- Statistik -->
<section class="py-4 bg-white border-bottom">
  <div class="container">
    <div class="row g-0 divide-x">
      <?php
      $stats = [
        ['bi-journal-bookmark-fill','text-primary',$total_pelatihan,'Total Pelatihan'],
        ['bi-mortarboard-fill','text-success',$total_alumni,'Total Alumni'],
        ['bi-people-fill','text-warning',$total_peserta,'Total Peserta'],
        ['bi-patch-check-fill','text-info',$total_lulus,'Peserta Lulus'],
      ];
      foreach ($stats as $s):
      ?>
      <div class="col-6 col-md-3">
        <div class="stat-item">
          <div class="ico <?= $s[1] ?>"><i class="bi <?= $s[0] ?>"></i></div>
          <div class="val"><?= number_format($s[2]) ?></div>
          <div class="lbl"><?= $s[3] ?></div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- Tentang -->
<section id="tentang" class="py-5" style="background:#f4f6fb">
  <div class="container reveal">
    <div class="row align-items-start g-5">
      <div class="col-lg-6">
        <div class="tentang-photo">
          <img src="assets/images/balai1.jpg" alt="Gedung BPPMDDTT Banjarmasin">
          <span class="tentang-corner-badge"><i class="bi bi-geo-alt-fill me-1"></i>Banjarmasin</span>
          <div class="tentang-float-card">
            <div class="ico"><i class="bi bi-mortarboard"></i></div>
            <div>
              <div class="val"><?= number_format($total_alumni) ?>+</div>
              <div class="lbl">Alumni Terdata</div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-6 pt-lg-2">
        <div class="section-badge">Tentang Kami</div>
        <h2 class="section-title">Mitra Pengembangan SDM Desa & Transmigrasi di Kalimantan Selatan</h2>
        <p class="section-sub mb-0" style="max-width:100%">
          Balai Pelatihan dan Pemberdayaan Masyarakat Desa, Daerah Tertinggal, dan Transmigrasi
          (BPPMDDTT) Banjarmasin berada di bawah Kementerian Desa, Pembangunan Daerah Tertinggal,
          dan Transmigrasi. Kami menyelenggarakan pelatihan berbasis kompetensi bagi aparatur desa,
          kader masyarakat, dan calon transmigran, lalu memantau perkembangan alumni pasca pelatihan
          melalui sistem digital ini — mulai dari tracer study, rencana kerja tindak lanjut (RKTL),
          hingga rekomendasi pelatihan lanjutan.
        </p>

        <div class="mini-stat-row">
          <div class="mini-stat">
            <div class="val"><?= number_format($total_pelatihan) ?></div>
            <div class="lbl">Pelatihan Terselenggara</div>
          </div>
          <div class="mini-stat">
            <div class="val"><?= number_format($total_instruktur) ?></div>
            <div class="lbl">Instruktur Aktif</div>
          </div>
          <div class="mini-stat">
            <div class="val"><?= number_format($total_peserta) ?></div>
            <div class="lbl">Peserta Terdaftar</div>
          </div>
        </div>

        <ul class="visi-list">
          <li><i class="bi bi-check-circle-fill"></i><span><strong>Visi:</strong> Menjadi balai pelatihan rujukan dalam pemberdayaan masyarakat desa, daerah tertinggal, dan transmigrasi di Kalimantan.</span></li>
          <li><i class="bi bi-check-circle-fill"></i><span><strong>Misi:</strong> Menyelenggarakan pelatihan berbasis kompetensi serta mengawal alumni agar hasil pelatihan benar-benar diterapkan di lapangan.</span></li>
        </ul>

        <div class="d-flex gap-3 flex-wrap">
          <a href="https://bppmtbjm.my.canva.site/" target="_blank" rel="noopener noreferrer" class="btn btn-primary">
            <i class="bi bi-box-arrow-up-right me-1"></i> Website Resmi
          </a>
          <a href="#pelatihan" class="btn btn-outline-primary">
            <i class="bi bi-journal-bookmark me-1"></i> Lihat Program Pelatihan
          </a>
        </div>
      </div>
    </div>

    <!-- Layanan / Fitur Sistem -->
    <div class="mt-5 pt-3">
      <div class="text-center mb-4">
        <div class="section-badge">Layanan Kami</div>
        <h2 class="section-title">Yang Bisa Anda Lakukan di Sistem Ini</h2>
      </div>
      <div class="row g-3">
        <?php
        $fitur = [
          ['01','bi-journal-bookmark','#e8f0fe','#1a4c8e','Manajemen Pelatihan','Kelola jadwal, kuota, dan instruktur pelatihan secara terpusat dan real-time.'],
          ['02','bi-mortarboard','#e8f5e9','#1d9e75','Data Alumni Digital','Rekam jejak setiap alumni tersimpan rapi, mulai dari kompetensi hingga riwayat pelatihan.'],
          ['03','bi-clipboard-data','#fff8e1','#f59e0b','Tracer Study Otomatis','Kuesioner keterserapan kerja dikirim otomatis ke alumni untuk mengukur dampak pelatihan.'],
          ['04','bi-stars','#f0f4ff','#6366f1','Rekomendasi Cerdas','Sistem menyarankan pelatihan lanjutan berdasarkan kompetensi yang sudah dimiliki alumni.'],
          ['05','bi-patch-check','#fdf2f8','#d946ef','RKTL & Pendampingan','Pantau rencana kerja tindak lanjut alumni selama masa pendampingan pasca pelatihan.'],
          ['06','bi-file-earmark-bar-graph','#fff1f2','#e11d48','Laporan & Pengesahan','Laporan lengkap disusun otomatis dan disahkan langsung oleh Kepala Balai.'],
        ];
        foreach ($fitur as $f):
        ?>
        <div class="col-md-6 col-lg-4">
          <div class="layanan-card">
            <div class="layanan-num"><?= $f[0] ?></div>
            <div class="fitur-icon" style="background:<?= $f[2] ?>;color:<?= $f[3] ?>">
              <i class="bi <?= $f[1] ?>"></i>
            </div>
            <div class="fw-semibold mb-1" style="font-size:14px"><?= $f[4] ?></div>
            <div style="font-size:12.5px;color:#6b7280"><?= $f[5] ?></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<!-- Pelatihan Tersedia -->
<section id="pelatihan" class="py-5 bg-white">
  <div class="container reveal">
    <div class="d-flex justify-content-between align-items-end mb-4">
      <div>
        <div class="section-badge">Pelatihan</div>
        <h2 class="section-title mb-0">Pelatihan Tersedia</h2>
      </div>
      <a href="login/daftar_pelatihan.php" class="btn btn-outline-primary btn-sm">
        Lihat Semua <i class="bi bi-arrow-right ms-1"></i>
      </a>
    </div>
    <div class="row g-3">
      <?php
      $count = 0;
      while ($pelatihan && ($p = mysqli_fetch_assoc($pelatihan))): $count++;
      $sisa = $p['kuota'] - $p['jml_peserta'];
      ?>
      <div class="col-md-6 col-lg-4">
        <div class="card pel-card">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start mb-2">
              <span class="badge bg-primary bg-opacity-10 text-primary pel-kuota">
                <?= htmlspecialchars($p['jenis'] ?? 'Umum') ?>
              </span>
              <span class="badge <?= $sisa>0?'bg-success':'bg-danger' ?> bg-opacity-10 <?= $sisa>0?'text-success':'text-danger' ?> pel-kuota">
                <?= $sisa>0 ? "$sisa tempat" : 'Penuh' ?>
              </span>
            </div>
            <h6 class="fw-bold mb-2"><?= htmlspecialchars($p['nama_pelatihan']) ?></h6>
            <div style="font-size:12px;color:#6b7280" class="mb-3">
              <div class="mb-1"><i class="bi bi-person me-1"></i><?= htmlspecialchars($p['nama_instruktur']) ?></div>
              <div class="mb-1"><i class="bi bi-calendar me-1"></i><?= date('d M Y', strtotime($p['tanggal_mulai'])) ?> — <?= date('d M Y', strtotime($p['tanggal_selesai'])) ?></div>
              <?php if ($p['lokasi']): ?>
              <div><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($p['lokasi']) ?></div>
              <?php endif; ?>
            </div>
            <a href="login/<?= isset($_SESSION['logged_in']) ? 'peserta/pelatihan.php' : 'register.php' ?>"
               class="btn btn-sm btn-primary w-100 fw-semibold">
              <?= $sisa>0 ? '<i class="bi bi-plus-lg me-1"></i>Daftar Sekarang' : '<i class="bi bi-x-circle me-1"></i>Kuota Penuh' ?>
            </a>
          </div>
        </div>
      </div>
      <?php endwhile; ?>
      <?php if ($count===0): ?>
        <div class="col-12 text-center text-muted py-5">
          <i class="bi bi-calendar-x" style="font-size:48px;opacity:.3"></i>
          <p class="mt-2">Belum ada pelatihan aktif saat ini</p>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- Alumni & Pelatih Unggulan -->
<section class="py-5" style="background:#f4f6fb">
  <div class="container reveal">
    <div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-2">
      <div>
        <div class="section-badge">Alumni &amp; Pelatih</div>
        <h2 class="section-title mb-0">Wajah di Balik Data</h2>
      </div>
      <a href="login/Infoalumni_pelatih.php" class="btn btn-outline-primary btn-sm">
        Lihat Semua <i class="bi bi-arrow-right ms-1"></i>
      </a>
    </div>
    <div class="row g-4">
      <div class="col-lg-6">
        <div class="unggulan-col-title"><i class="bi bi-mortarboard"></i> Alumni Paling Aktif</div>
        <div class="d-flex flex-column gap-3">
          <?php
          $ada_alumni = false;
          while ($alumni_info && ($a = mysqli_fetch_assoc($alumni_info))): $ada_alumni = true;
          ?>
          <a href="login/alumni_detail_publik.php?id=<?= (int)$a['id'] ?>" class="text-decoration-none">
            <div class="unggulan-card">
              <div class="unggulan-avatar"><?= avatarSvg($a['name'], 52, true) ?></div>
              <div class="flex-grow-1">
                <div class="unggulan-name"><?= htmlspecialchars($a['name']) ?></div>
                <div class="unggulan-asal"><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($a['asal']) ?></div>
              </div>
              <span class="unggulan-badge"><?= (int)$a['jml_pelatihan'] ?> Pelatihan</span>
            </div>
          </a>
          <?php endwhile; ?>
          <?php if (!$ada_alumni): ?>
            <p class="text-muted mb-0" style="font-size:13px">Belum ada data alumni.</p>
          <?php endif; ?>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="unggulan-col-title"><i class="bi bi-person-workspace"></i> Pelatih Paling Berpengalaman</div>
        <div class="d-flex flex-column gap-3">
          <?php
          $ada_instruktur = false;
          while ($instruktur_info && ($i = mysqli_fetch_assoc($instruktur_info))): $ada_instruktur = true;
          ?>
          <a href="login/pelatih_detail_publik.php?id=<?= (int)$i['id'] ?>" class="text-decoration-none">
            <div class="unggulan-card">
              <div class="unggulan-avatar"><?= avatarSvg($i['name'], 52, true) ?></div>
              <div class="flex-grow-1">
                <div class="unggulan-name"><?= htmlspecialchars($i['name']) ?></div>
                <div class="unggulan-asal"><i class="bi bi-people me-1"></i><?= (int)$i['jml_peserta'] ?> peserta dibimbing</div>
              </div>
              <span class="unggulan-badge"><?= (int)$i['jml_pelatihan'] ?> Pelatihan</span>
            </div>
          </a>
          <?php endwhile; ?>
          <?php if (!$ada_instruktur): ?>
            <p class="text-muted mb-0" style="font-size:13px">Belum ada data pelatih.</p>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Grafik Sebaran -->
<section id="statistik" class="py-5" style="background:#f4f6fb">
  <div class="container reveal">
    <div class="section-badge">Statistik</div>
    <h2 class="section-title">Sebaran Alumni</h2>
    <p class="section-sub mb-4">Distribusi alumni berdasarkan kota asal dan kategori pelatihan</p>
    <div class="row g-4">
      <div class="col-lg-7">
        <div class="chart-wrap">
          <p class="fw-semibold mb-3" style="font-size:14px">
            <i class="bi bi-geo-alt-fill text-primary me-2"></i>Alumni per Kabupaten/Kota
          </p>
          <canvas id="chartSebaran" height="200"></canvas>
        </div>
      </div>
      <div class="col-lg-5">
        <div class="chart-wrap">
          <p class="fw-semibold mb-3" style="font-size:14px">
            <i class="bi bi-pie-chart-fill text-success me-2"></i>Alumni per Kategori Pelatihan
          </p>
          <canvas id="chartKategori" height="200"></canvas>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- Top 10 Pelatihan -->
<section id="peringkat" class="py-5 bg-white">
  <div class="container reveal">
    <div class="section-badge">Peringkat</div>
    <h2 class="section-title">Top 10 Pelatihan Paling Berpengaruh</h2>
    <p class="section-sub mb-4">Berdasarkan rata-rata relevansi dari hasil tracer study alumni</p>
    <div class="card border-0 shadow-sm">
      <div class="table-responsive">
        <table class="table mb-0" style="font-size:13px">
          <thead style="background:#1a2942;color:#fff">
            <tr><th>#</th><th>Nama Pelatihan</th><th>Kategori</th><th>Peserta</th><th>Lulus</th><th>Relevansi</th></tr>
          </thead>
          <tbody>
          <?php
          $no=1;
          while ($top_pelatihan && ($row = mysqli_fetch_assoc($top_pelatihan))):
            $rc = $no===1?'rank-1':($no===2?'rank-2':($no===3?'rank-3':'rank-n'));
            $rel = $row['avg_relevansi'] ? round($row['avg_relevansi'],1) : null;
          ?>
          <tr style="<?= $no<=3?'background:#fafafa':'' ?>">
            <td><span class="rank-num <?= $rc ?>"><?= $no ?></span></td>
            <td class="fw-semibold"><?= htmlspecialchars($row['nama_pelatihan']) ?></td>
            <td><span class="badge bg-primary bg-opacity-10 text-primary" style="font-size:11px"><?= htmlspecialchars($row['jenis']??'-') ?></span></td>
            <td><?= $row['jml_peserta'] ?></td>
            <td><span class="badge bg-success bg-opacity-10 text-success"><?= $row['jml_lulus'] ?></span></td>
            <td>
              <?php if ($rel): ?>
                <span class="stars"><?php for($i=1;$i<=5;$i++) echo '<i class="bi bi-star'.($i<=$rel?'-fill':'').'"></i>'; ?></span>
                <small class="text-muted ms-1"><?= $rel ?>/5</small>
              <?php else: ?><small class="text-muted">-</small><?php endif; ?>
            </td>
          </tr>
          <?php $no++; endwhile; ?>
          <?php if ($no===1): ?><tr><td colspan="6" class="text-center text-muted py-4">Belum ada data</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</section>

<!-- CTA -->
<section class="py-5" style="background:#f4f6fb">
  <div class="container reveal">
    <div class="cta-section">
      <h3>Bergabung Sekarang</h3>
      <p>Daftarkan diri Anda dan ikuti program pelatihan BPPMDDTT Banjarmasin</p>
      <div class="d-flex gap-3 justify-content-center flex-wrap">
        <a href="login/register.php" class="btn btn-warning btn-lg fw-semibold px-5">
          <i class="bi bi-person-plus me-1"></i> Daftar Gratis
        </a>
        <a href="login/login.php" class="btn btn-outline-light btn-lg px-5">
          <i class="bi bi-box-arrow-in-right me-1"></i> Sudah Punya Akun
        </a>
      </div>
    </div>
  </div>
</section>

<!-- Footer -->
<footer>
  <div class="container">
    <div class="row g-4">
      <div class="col-md-5">
        <div class="footer-brand">Aplikasi Monitoring dan Manajemen Data Alumni, Pelatih</div>
        <p style="font-size:13px;margin-bottom:12px">BPPMDDTT Banjarmasin · Kementerian Desa, Pembangunan Daerah Tertinggal, dan Transmigrasi</p>
        <a href="https://bppmddtt-banjarmasin.kemendesa.go.id/" target="_blank" rel="noopener noreferrer" style="font-size:12px">
          <i class="bi bi-globe me-1"></i>bppmddtt-banjarmasin.kemendesa.go.id
        </a>
      </div>
      <div class="col-md-3">
        <div class="fw-semibold text-white mb-2" style="font-size:13px">Menu</div>
        <div style="font-size:13px;line-height:2">
          <a href="login/daftar_pelatihan.php" class="d-block">Daftar Pelatihan</a>
          <a href="login/login.php" class="d-block">Login</a>
          <a href="login/register.php" class="d-block">Registrasi</a>
          <a href="login/Infoalumni_pelatih.php" class="d-block">Info Alumni/Pelatih</a>
        </div>
      </div>
      <div class="col-md-4">
        <div class="fw-semibold text-white mb-2" style="font-size:13px">Akses Portal</div>
        <div style="font-size:13px;line-height:2">
          <a href="login/admin/index.php" class="d-block"><i class="bi bi-shield me-1"></i>Portal Admin</a>
          <a href="login/instruktur/index.php" class="d-block"><i class="bi bi-person-workspace me-1"></i>Portal Instruktur</a>
          <a href="login/alumni/index.php" class="d-block"><i class="bi bi-mortarboard me-1"></i>Portal Alumni</a>
          <a href="login/kepala/index.php" class="d-block"><i class="bi bi-person-badge me-1"></i>Portal Kepala</a>
        </div>
      </div>
    </div>
    <div class="footer-bottom">
      &copy; <?= date('Y') ?> M. Hafiz Nuril Ikhsan · BPPMDDTT Banjarmasin · All rights reserved.
    </div>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Charts — dibungkus try/catch supaya kalau CDN Chart.js gagal dimuat / error,
// tidak ikut menggagalkan skrip lain (scroll reveal) di bawahnya.
try {
  const colors = ['#1a4c8e','#1d9e75','#d85a30','#ba7517','#993556','#534ab7','#0f6e56','#639922'];
  const sebaranLabels = <?= json_encode(array_column($sebaran_data,'kota')) ?>;
  const sebaranData   = <?= json_encode(array_column($sebaran_data,'jumlah')) ?>;
  const kategoriLabels= <?= json_encode(array_column($kategori_data,'jenis')) ?>;
  const kategoriData  = <?= json_encode(array_column($kategori_data,'jml')) ?>;

  if (typeof Chart !== 'undefined') {
    const elSebaran  = document.getElementById('chartSebaran');
    const elKategori = document.getElementById('chartKategori');

    if (elSebaran) {
      new Chart(elSebaran, {
        type:'bar',
        data:{ labels:sebaranLabels.length?sebaranLabels:['Belum ada data'],
          datasets:[{label:'Alumni',data:sebaranData.length?sebaranData:[0],backgroundColor:colors,borderRadius:6}]},
        options:{indexAxis:'y',responsive:true,plugins:{legend:{display:false}},
          scales:{x:{grid:{display:false},ticks:{font:{size:11}}},y:{grid:{display:false},ticks:{font:{size:11}}}}}
      });
    }

    if (elKategori) {
      new Chart(elKategori, {
        type:'doughnut',
        data:{ labels:kategoriLabels.length?kategoriLabels:['Belum ada data'],
          datasets:[{data:kategoriData.length?kategoriData:[1],backgroundColor:colors,borderWidth:2,borderColor:'#fff'}]},
        options:{responsive:true,plugins:{legend:{position:'bottom',labels:{font:{size:11},padding:12}}},cutout:'65%'}
      });
    }
  } else {
    console.warn('Chart.js gagal dimuat, grafik statistik dilewati.');
  }
} catch (err) {
  console.error('Gagal membuat grafik:', err);
}
</script>

<script>
// Scroll reveal — animasi ini murni "bonus" tampilan. Konten sendiri SUDAH
// terlihat dari awal lewat CSS (.reveal default opacity:1), jadi walau
// baris di bawah ini gagal total, halaman tetap tampil normal.
try {
  const revealEls = document.querySelectorAll('.reveal');
  revealEls.forEach(el => el.classList.add('pre-anim'));

  const observer = new IntersectionObserver(entries => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        e.target.classList.remove('pre-anim');
        e.target.classList.add('visible');
      }
    });
  }, { threshold: 0.1 });
  revealEls.forEach(el => observer.observe(el));

  // Jaring pengaman tambahan: paksa tampil penuh setelah 1.5 detik.
  setTimeout(() => revealEls.forEach(el => {
    el.classList.remove('pre-anim');
    el.classList.add('visible');
  }), 1500);
} catch (err) {
  document.querySelectorAll('.reveal').forEach(el => {
    el.classList.remove('pre-anim');
    el.classList.add('visible');
  });
}
</script>
</body>
</html>