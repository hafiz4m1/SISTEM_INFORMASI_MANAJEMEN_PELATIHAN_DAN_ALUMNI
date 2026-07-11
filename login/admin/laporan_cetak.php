<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['level'] !== 'admin') {
    header("location: ../login.php"); exit;
}
include '../koneksi.php';
// Ambil data kepala yang aktif untuk pengesahan laporan
$kepala = [];
$cek_tabel = mysqli_query($koneksi, "SHOW TABLES LIKE 'kepala'");
if ($cek_tabel && mysqli_num_rows($cek_tabel) > 0) {
    $q_kepala = mysqli_query($koneksi,
        "SELECT k.*, u.name, u.name as nama_lengkap FROM kepala k JOIN users u ON k.user_id=u.id WHERE k.is_aktif=1 LIMIT 1");
    if ($q_kepala) $kepala = mysqli_fetch_assoc($q_kepala) ?: [];
}


$tgl_dari   = isset($_GET['dari'])   ? $_GET['dari']   : date('Y-01-01');
$tgl_sampai = isset($_GET['sampai']) ? $_GET['sampai'] : date('Y-m-d');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tgl_dari))   $tgl_dari   = date('Y-01-01');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tgl_sampai)) $tgl_sampai = date('Y-m-d');
$jenis      = isset($_GET['jenis'])  ? $_GET['jenis']  : 'pelatihan';

$judul_laporan = [
    'pelatihan'   => 'Laporan Data Pelatihan',
    'peserta'     => 'Laporan Data Peserta Pelatihan',
    'alumni'      => 'Laporan Data Alumni',
    'tracer'      => 'Laporan Hasil Tracer Study',
    'rktl'        => 'Laporan Monitoring RKTL',
    'rekomendasi' => 'Laporan Rekomendasi Pelatihan',
    'kelulusan'   => 'Laporan Tingkat Kelulusan',
];

// ============================================================
// AMBIL DATA SESUAI JENIS
// ============================================================

if ($jenis === 'pelatihan') {
    $data = mysqli_query($koneksi, "
        SELECT p.*, ui.name as nama_instruktur,
            COUNT(DISTINCT pp.user_id) as jml_peserta,
            SUM(pp.status_lulus='lulus') as jml_lulus,
            SUM(pp.status_lulus='tidak_lulus') as jml_tidak_lulus,
            ROUND(AVG(pp.nilai),1) as rata_nilai
        FROM pelatihan p
        JOIN instruktur i ON p.instruktur_id=i.id
        JOIN users ui ON i.user_id=ui.id
        LEFT JOIN peserta_pelatihan pp ON pp.pelatihan_id=p.id
        WHERE p.tanggal_mulai BETWEEN '$tgl_dari' AND '$tgl_sampai'
        GROUP BY p.id ORDER BY p.tanggal_mulai ASC
    ");
}
elseif ($jenis === 'peserta') {
    $data = mysqli_query($koneksi, "
        SELECT pp.*, u.name as nama_peserta, u.email,
            p.nama_pelatihan, p.tanggal_mulai, p.tanggal_selesai,
            ui.name as nama_instruktur
        FROM peserta_pelatihan pp
        JOIN users u ON pp.user_id=u.id
        JOIN pelatihan p ON pp.pelatihan_id=p.id
        JOIN instruktur i ON p.instruktur_id=i.id
        JOIN users ui ON i.user_id=ui.id
        WHERE p.tanggal_mulai BETWEEN '$tgl_dari' AND '$tgl_sampai'
        ORDER BY p.tanggal_mulai ASC, u.name ASC
    ");
}
elseif ($jenis === 'alumni') {
    $data = mysqli_query($koneksi, "
        SELECT a.*, u.name, u.email,
            COUNT(DISTINCT pp.pelatihan_id) as jml_pelatihan,
            GROUP_CONCAT(DISTINCT k.nama_kompetensi SEPARATOR ', ') as kompetensi,
            ts.status_pekerjaan, ts.nama_perusahaan, ts.jabatan
        FROM alumni a
        JOIN users u ON a.user_id=u.id
        LEFT JOIN peserta_pelatihan pp ON pp.user_id=a.user_id
        LEFT JOIN alumni_kompetensi ak ON ak.alumni_id=a.id
        LEFT JOIN kompetensi k ON ak.kompetensi_id=k.id
        LEFT JOIN tracer_study ts ON ts.alumni_id=a.id AND ts.status_pengisian='sudah_diisi'
        GROUP BY a.id ORDER BY u.name ASC
    ");
}
elseif ($jenis === 'tracer') {
    $data = mysqli_query($koneksi, "
        SELECT ts.*, u.name as nama_alumni
        FROM tracer_study ts
        JOIN alumni a ON ts.alumni_id=a.id
        JOIN users u ON a.user_id=u.id
        WHERE ts.status_pengisian='sudah_diisi'
        ORDER BY ts.tanggal_isi DESC
    ");
    $stat = mysqli_fetch_assoc(mysqli_query($koneksi, "
        SELECT COUNT(*) as total,
            SUM(status_pekerjaan='bekerja') as bekerja,
            SUM(status_pekerjaan='wirausaha') as wirausaha,
            SUM(status_pekerjaan='belum_bekerja') as belum_kerja,
            SUM(status_pekerjaan='melanjutkan_studi') as studi,
            ROUND(AVG(relevansi_pelatihan),2) as avg_relevansi
        FROM tracer_study WHERE status_pengisian='sudah_diisi'
    "));
}
elseif ($jenis === 'rktl') {
    $data = mysqli_query($koneksi, "
        SELECT r.*, u.name as nama_alumni, p.nama_pelatihan, ui.name as nama_instruktur
        FROM rktl r
        JOIN alumni a ON r.alumni_id=a.id
        JOIN users u ON a.user_id=u.id
        JOIN pelatihan p ON r.pelatihan_id=p.id
        JOIN instruktur i ON r.instruktur_id=i.id
        JOIN users ui ON i.user_id=ui.id
        ORDER BY r.tgl_pendampingan ASC
    ");
}
elseif ($jenis === 'rekomendasi') {
    $data = mysqli_query($koneksi, "
        SELECT r.*, u.name as nama_alumni, p.nama_pelatihan, p.jenis, p.tanggal_mulai
        FROM rekomendasi r
        JOIN alumni a ON r.alumni_id=a.id
        JOIN users u ON a.user_id=u.id
        JOIN pelatihan p ON r.pelatihan_id=p.id
        ORDER BY r.skor DESC, u.name ASC
    ");
}
elseif ($jenis === 'kelulusan') {
    $data = mysqli_query($koneksi, "
        SELECT p.nama_pelatihan, p.jenis, p.tanggal_mulai, p.tanggal_selesai,
            ui.name as nama_instruktur,
            COUNT(DISTINCT pp.user_id) as total_peserta,
            SUM(pp.status_lulus='lulus') as lulus,
            SUM(pp.status_lulus='tidak_lulus') as tidak_lulus,
            SUM(pp.status_lulus='belum_dinilai') as belum_dinilai,
            ROUND(SUM(pp.status_lulus='lulus')/COUNT(DISTINCT pp.user_id)*100,1) as pct_lulus,
            ROUND(AVG(pp.nilai),1) as rata_nilai,
            MAX(pp.nilai) as nilai_max,
            MIN(CASE WHEN pp.nilai IS NOT NULL THEN pp.nilai END) as nilai_min
        FROM pelatihan p
        JOIN instruktur i ON p.instruktur_id=i.id
        JOIN users ui ON i.user_id=ui.id
        LEFT JOIN peserta_pelatihan pp ON pp.pelatihan_id=p.id
        WHERE p.tanggal_mulai BETWEEN '$tgl_dari' AND '$tgl_sampai'
        GROUP BY p.id HAVING total_peserta > 0
        ORDER BY pct_lulus DESC
    ");
}

// Hitung total baris
$rows = [];
while ($r = mysqli_fetch_assoc($data)) $rows[] = $r;
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title><?= $judul_laporan[$jenis] ?> | BPPMDDTT</title>
  <link href="https://fonts.googleapis.com/css2?family=Times+New+Roman&family=Arial&display=swap" rel="stylesheet">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
      font-family: Arial, sans-serif;
      font-size: 11pt;
      color: #000;
      background: #e8e8e8;
      padding: 20px;
    }

    /* Toolbar - tidak ikut print */
    .toolbar {
      background: #1a2942;
      color: #fff;
      padding: 10px 20px;
      display: flex;
      align-items: center;
      gap: 12px;
      margin-bottom: 16px;
      border-radius: 8px;
      max-width: 900px;
      margin-left: auto;
      margin-right: auto;
    }
    .toolbar span { flex: 1; font-size: 13px; opacity: .75; }
    .toolbar button {
      background: #1a4c8e; color: #fff; border: none;
      padding: 7px 18px; border-radius: 6px; font-size: 13px;
      font-weight: 600; cursor: pointer;
    }
    .toolbar button:hover { background: #123570; }
    .toolbar a { color: rgba(255,255,255,.7); font-size: 13px; text-decoration: none; }
    .toolbar a:hover { color: #fff; }

    /* Halaman kertas */
    .halaman {
      width: 900px;
      min-height: 1100px;
      background: #fff;
      margin: 0 auto;
      padding: 48px 56px;
      box-shadow: 0 4px 24px rgba(0,0,0,.15);
    }

    /* KOP SURAT */
    .kop {
      display: flex;
      align-items: center;
      gap: 20px;
      padding-bottom: 12px;
      border-bottom: 3px solid #1a2942;
      margin-bottom: 4px;
      justify-content: center;
      flex-direction: column;
      text-align: center;
    }
    .kop-logo {
      width: 70px; height: 70px;
      background: transparent;
      border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      color: #fff; font-size: 28px; flex-shrink: 0;
    }
    .kop-logo img {
      width: 100%;
      height: 100%;
      object-fit: contain;
    }
    .kop-teks { flex: 1; }
    .kop-teks .instansi-utama {
      font-size: 15pt; font-weight: bold;
      color: #1a2942; line-height: 1.2; text-transform: uppercase;
    }
    .kop-teks .instansi-sub {
      font-size: 10pt; color: #444; margin-top: 2px;
    }
    .kop-teks .instansi-kemendes {
      font-size: 9pt; color: #666;
    }
    .garis-kop-tipis {
      border-bottom: 1px solid #1a2942;
      margin-bottom: 20px;
    }

    /* JUDUL LAPORAN */
    .judul-area { text-align: center; margin-bottom: 20px; }
    .judul-area .judul { font-size: 14pt; font-weight: bold; text-transform: uppercase; color: #1a2942; }
    .judul-area .sub-judul { font-size: 10pt; color: #444; margin-top: 4px; }
    .judul-area .periode {
      display: inline-block;
      border: 1px solid #1a2942;
      padding: 3px 16px;
      border-radius: 20px;
      font-size: 9pt;
      color: #1a2942;
      margin-top: 6px;
    }

    /* RINGKASAN STATISTIK */
    .ringkasan {
      display: flex;
      gap: 0;
      border: 1px solid #ddd;
      border-radius: 6px;
      overflow: hidden;
      margin-bottom: 16px;
    }
    .ringkasan-item {
      flex: 1;
      text-align: center;
      padding: 10px 8px;
      border-right: 1px solid #ddd;
    }
    .ringkasan-item:last-child { border-right: none; }
    .ringkasan-item .angka { font-size: 18pt; font-weight: bold; color: #1a4c8e; }
    .ringkasan-item .label { font-size: 8pt; color: #666; margin-top: 2px; }

    /* TABEL */
    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 9pt;
      margin-bottom: 16px;
    }
    table thead tr {
      background: #1a2942;
      color: #fff;
    }
    table thead th {
      padding: 7px 8px;
      text-align: left;
      font-weight: 600;
      font-size: 8.5pt;
    }
    table tbody tr:nth-child(even) { background: #f5f7fa; }
    table tbody tr:hover { background: #eef2f8; }
    table tbody td {
      padding: 6px 8px;
      border-bottom: 1px solid #e8e8e8;
      vertical-align: middle;
    }
    table tbody tr:last-child td { border-bottom: 2px solid #1a2942; }

    /* Status badge (print-friendly) */
    .badge {
      display: inline-block;
      padding: 2px 8px;
      border-radius: 10px;
      font-size: 8pt;
      font-weight: 600;
    }
    .badge-success { background: #d4edda; color: #155724; }
    .badge-danger  { background: #f8d7da; color: #721c24; }
    .badge-warning { background: #fff3cd; color: #856404; }
    .badge-info    { background: #d1ecf1; color: #0c5460; }
    .badge-secondary { background: #e2e3e5; color: #383d41; }
    .badge-primary { background: #cce5ff; color: #004085; }

    /* Progress bar (print) */
    .prog-wrap { background: #e8e8e8; border-radius: 3px; height: 8px; width: 60px; display: inline-block; vertical-align: middle; }
    .prog-bar  { height: 8px; border-radius: 3px; }

    /* TTD AREA */
    .ttd-area {
      margin-top: 40px;
      display: flex;
      justify-content: flex-end;
    }
    .ttd-box { text-align: center; }
    .ttd-box .ttd-kota { font-size: 10pt; }
    .ttd-box .ttd-jabatan { font-size: 10pt; font-weight: bold; margin-top: 4px; }
    .ttd-box .ttd-line { width: 200px; border-bottom: 1px solid #000; margin: 60px auto 4px; }
    .ttd-box .ttd-nama { font-size: 10pt; font-weight: bold; }
    .ttd-box .ttd-nip  { font-size: 9pt; color: #444; }

    /* FOOTER HALAMAN */
    .footer-lap {
      margin-top: 24px;
      padding-top: 8px;
      border-top: 1px solid #ddd;
      display: flex;
      justify-content: space-between;
      font-size: 8pt;
      color: #888;
    }

    /* ===== PRINT STYLES ===== */
    @media print {
      body { background: #fff; padding: 0; }
      .toolbar { display: none !important; }
      .halaman { box-shadow: none; width: 100%; padding: 24px 32px; margin: 0; }
      @page { size: A4; margin: 1cm; }
      table { page-break-inside: auto; }
      tr { page-break-inside: avoid; }
    }
  </style>
</head>
<body>

<!-- Toolbar -->
<div class="toolbar">
  <a href="laporan.php?tab=<?= $jenis ?>&dari=<?= $tgl_dari ?>&sampai=<?= $tgl_sampai ?>">&#8592; Kembali</a>
  <span><?= $judul_laporan[$jenis] ?> · <?= date('d M Y',strtotime($tgl_dari)) ?> s/d <?= date('d M Y',strtotime($tgl_sampai)) ?></span>
  <button onclick="window.print()">🖨️ Print / Save PDF</button>
</div>

<!-- HALAMAN LAPORAN -->
<div class="halaman">

  <!-- KOP SURAT -->
  <div class="kop">
    <div class="kop-logo">
      <img src="../images/LOGO-KEMENTRANS-Bulat.png" alt="Logo BPPMDDTT">
    </div>
    <div class="kop-teks">
      <div class="instansi-utama">Balai Pelatihan dan Pemberdayaan Masyarakat Desa</div>
      <div class="instansi-sub">Daerah Tertinggal dan Transmigrasi Banjarmasin</div>
      <div class="instansi-kemendes">Kementerian Desa, Pembangunan Daerah Tertinggal, dan Transmigrasi Republik Indonesia</div>
    </div>
  </div>
  <div class="garis-kop-tipis"></div>

  <!-- JUDUL -->
  <div class="judul-area">
    <div class="judul"><?= $judul_laporan[$jenis] ?></div>
    <div class="sub-judul">BPPMDDTT Banjarmasin</div>
    <div class="periode">Periode: <?= date('d F Y',strtotime($tgl_dari)) ?> s/d <?= date('d F Y',strtotime($tgl_sampai)) ?></div>
  </div>

  <?php $total = count($rows); ?>

  <!-- RINGKASAN STATISTIK -->
  <?php if ($jenis === 'pelatihan'): ?>
  <div class="ringkasan">
    <div class="ringkasan-item"><div class="angka"><?= $total ?></div><div class="label">Total Pelatihan</div></div>
    <div class="ringkasan-item"><div class="angka"><?= array_sum(array_column($rows,'jml_peserta')) ?></div><div class="label">Total Peserta</div></div>
    <div class="ringkasan-item"><div class="angka"><?= array_sum(array_column($rows,'jml_lulus')) ?></div><div class="label">Total Lulus</div></div>
    <div class="ringkasan-item"><div class="angka"><?= count(array_filter($rows,fn($r)=>$r['status']==='aktif')) ?></div><div class="label">Pelatihan Aktif</div></div>
  </div>
  <?php elseif ($jenis === 'peserta'): ?>
  <div class="ringkasan">
    <div class="ringkasan-item"><div class="angka"><?= $total ?></div><div class="label">Total Data</div></div>
    <div class="ringkasan-item"><div class="angka"><?= count(array_filter($rows,fn($r)=>$r['status_lulus']==='lulus')) ?></div><div class="label">Lulus</div></div>
    <div class="ringkasan-item"><div class="angka"><?= count(array_filter($rows,fn($r)=>$r['status_lulus']==='tidak_lulus')) ?></div><div class="label">Tidak Lulus</div></div>
    <div class="ringkasan-item"><div class="angka"><?= count(array_filter($rows,fn($r)=>$r['status_kehadiran']==='hadir')) ?></div><div class="label">Hadir</div></div>
  </div>
  <?php elseif ($jenis === 'alumni'): ?>
  <div class="ringkasan">
    <div class="ringkasan-item"><div class="angka"><?= $total ?></div><div class="label">Total Alumni</div></div>
    <div class="ringkasan-item"><div class="angka"><?= count(array_filter($rows,fn($r)=>$r['status_pekerjaan']==='bekerja')) ?></div><div class="label">Bekerja</div></div>
    <div class="ringkasan-item"><div class="angka"><?= count(array_filter($rows,fn($r)=>$r['status_pekerjaan']==='wirausaha')) ?></div><div class="label">Wirausaha</div></div>
    <div class="ringkasan-item"><div class="angka"><?= count(array_filter($rows,fn($r)=>$r['status_pekerjaan']==='belum_bekerja')) ?></div><div class="label">Belum Bekerja</div></div>
  </div>
  <?php elseif ($jenis === 'tracer'): ?>
  <div class="ringkasan">
    <div class="ringkasan-item"><div class="angka"><?= $stat['total'] ?></div><div class="label">Terisi</div></div>
    <div class="ringkasan-item"><div class="angka"><?= $stat['bekerja'] ?></div><div class="label">Bekerja</div></div>
    <div class="ringkasan-item"><div class="angka"><?= $stat['wirausaha'] ?></div><div class="label">Wirausaha</div></div>
    <div class="ringkasan-item"><div class="angka"><?= $stat['belum_kerja'] ?></div><div class="label">Belum Bekerja</div></div>
    <div class="ringkasan-item"><div class="angka"><?= number_format($stat['avg_relevansi'],1) ?>/5</div><div class="label">Rata Relevansi</div></div>
  </div>
  <?php elseif ($jenis === 'rktl'): ?>
  <div class="ringkasan">
    <div class="ringkasan-item"><div class="angka"><?= $total ?></div><div class="label">Total RKTL</div></div>
    <div class="ringkasan-item"><div class="angka"><?= count(array_filter($rows,fn($r)=>$r['status']==='berjalan')) ?></div><div class="label">Berjalan</div></div>
    <div class="ringkasan-item"><div class="angka"><?= count(array_filter($rows,fn($r)=>$r['status']==='selesai')) ?></div><div class="label">Selesai</div></div>
    <div class="ringkasan-item"><div class="angka"><?= count(array_filter($rows,fn($r)=>$r['status']==='terhambat')) ?></div><div class="label">Terhambat</div></div>
  </div>
  <?php elseif ($jenis === 'kelulusan'): ?>
  <div class="ringkasan">
    <div class="ringkasan-item"><div class="angka"><?= $total ?></div><div class="label">Total Pelatihan</div></div>
    <div class="ringkasan-item"><div class="angka"><?= array_sum(array_column($rows,'total_peserta')) ?></div><div class="label">Total Peserta</div></div>
    <div class="ringkasan-item"><div class="angka"><?= array_sum(array_column($rows,'lulus')) ?></div><div class="label">Total Lulus</div></div>
    <div class="ringkasan-item">
      <?php $tot_p = array_sum(array_column($rows,'total_peserta')); $tot_l = array_sum(array_column($rows,'lulus')); ?>
      <div class="angka"><?= $tot_p > 0 ? round($tot_l/$tot_p*100,1) : 0 ?>%</div>
      <div class="label">Rata % Lulus</div>
    </div>
  </div>
  <?php endif; ?>

  <!-- ===== TABEL DATA ===== -->

  <?php if ($jenis === 'pelatihan'): ?>
  <table>
    <thead><tr><th>No</th><th>Nama Pelatihan</th><th>Jenis</th><th>Instruktur</th><th>Tgl Mulai</th><th>Tgl Selesai</th><th>Peserta</th><th>Lulus</th><th>Tidak Lulus</th><th>Rata Nilai</th><th>Status</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $i=>$r): ?>
      <tr>
        <td><?= $i+1 ?></td>
        <td><strong><?= htmlspecialchars($r['nama_pelatihan']) ?></strong></td>
        <td><?= htmlspecialchars($r['jenis']??'-') ?></td>
        <td><?= htmlspecialchars($r['nama_instruktur']) ?></td>
        <td><?= date('d/m/Y',strtotime($r['tanggal_mulai'])) ?></td>
        <td><?= date('d/m/Y',strtotime($r['tanggal_selesai'])) ?></td>
        <td style="text-align:center"><?= $r['jml_peserta'] ?></td>
        <td style="text-align:center"><span class="badge badge-success"><?= $r['jml_lulus'] ?></span></td>
        <td style="text-align:center"><span class="badge badge-danger"><?= $r['jml_tidak_lulus'] ?></span></td>
        <td style="text-align:center"><?= $r['rata_nilai']??'-' ?></td>
        <td><?php $sb=['aktif'=>'badge-success','selesai'=>'badge-secondary','dibatalkan'=>'badge-danger']; ?><span class="badge <?= $sb[$r['status']]??'badge-secondary' ?>"><?= ucfirst($r['status']) ?></span></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
    <tfoot>
      <tr style="font-weight:bold;background:#f6f6f6;">
        <td colspan="6">Jumlah</td>
        <td style="text-align:center"><?= array_sum(array_column($rows,'jml_peserta')) ?></td>
        <td style="text-align:center"><?= array_sum(array_column($rows,'jml_lulus')) ?></td>
        <td style="text-align:center"><?= array_sum(array_column($rows,'jml_tidak_lulus')) ?></td>
        <td style="text-align:center">-</td>
        <td></td>
      </tr>
    </tfoot>
  </table>

  <?php elseif ($jenis === 'peserta'): ?>
  <table>
    <thead><tr><th>No</th><th>Nama Peserta</th><th>Pelatihan</th><th>Instruktur</th><th>Tgl Mulai</th><th>Kehadiran</th><th>Nilai</th><th>Status Lulus</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $i=>$r): ?>
      <tr>
        <td><?= $i+1 ?></td>
        <td><strong><?= htmlspecialchars($r['nama_peserta']) ?></strong><br><small style="color:#666"><?= $r['email'] ?></small></td>
        <td><?= htmlspecialchars($r['nama_pelatihan']) ?></td>
        <td><?= htmlspecialchars($r['nama_instruktur']) ?></td>
        <td><?= date('d/m/Y',strtotime($r['tanggal_mulai'])) ?></td>
        <td><?php $kh=['hadir'=>'badge-success','tidak_hadir'=>'badge-danger','izin'=>'badge-warning']; ?><span class="badge <?= $kh[$r['status_kehadiran']]??'badge-secondary' ?>"><?= str_replace('_',' ',ucfirst($r['status_kehadiran'])) ?></span></td>
        <td style="text-align:center"><?= $r['nilai']??'-' ?></td>
        <td><?php $sl=['lulus'=>'badge-success','tidak_lulus'=>'badge-danger','belum_dinilai'=>'badge-secondary']; ?><span class="badge <?= $sl[$r['status_lulus']]??'badge-secondary' ?>"><?= str_replace('_',' ',ucfirst($r['status_lulus'])) ?></span></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>

  <?php elseif ($jenis === 'alumni'): ?>
  <table>
    <thead><tr><th>No</th><th>Nama Alumni</th><th>Tgl Lulus</th><th>Jml Pelatihan</th><th>Kompetensi</th><th>Status Pekerjaan</th><th>Perusahaan / Jabatan</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $i=>$r): ?>
      <tr>
        <td><?= $i+1 ?></td>
        <td><strong><?= htmlspecialchars($r['name']) ?></strong><br><small style="color:#666"><?= $r['email'] ?></small></td>
        <td><?= $r['tanggal_lulus'] ? date('d/m/Y',strtotime($r['tanggal_lulus'])) : '-' ?></td>
        <td style="text-align:center"><?= $r['jml_pelatihan'] ?></td>
        <td><small><?= htmlspecialchars($r['kompetensi']??'-') ?></small></td>
        <td>
          <?php if ($r['status_pekerjaan']): ?>
            <?php $sp=['bekerja'=>'badge-success','wirausaha'=>'badge-info','belum_bekerja'=>'badge-warning','melanjutkan_studi'=>'badge-primary']; ?>
            <span class="badge <?= $sp[$r['status_pekerjaan']]??'badge-secondary' ?>"><?= ucfirst(str_replace('_',' ',$r['status_pekerjaan'])) ?></span>
          <?php else: ?><span style="color:#888">-</span><?php endif; ?>
        </td>
        <td><?= htmlspecialchars($r['nama_perusahaan']??'-') ?><?= $r['jabatan'] ? ' / '.$r['jabatan'] : '' ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
    <tfoot>
      <tr style="font-weight:bold;background:#f6f6f6;">
        <td colspan="3">Jumlah Alumni</td>
        <td style="text-align:center"><?= array_sum(array_column($rows,'jml_pelatihan')) ?></td>
        <td colspan="3"></td>
      </tr>
    </tfoot>
  </table>

  <?php elseif ($jenis === 'tracer'): ?>
  <table>
    <thead><tr><th>No</th><th>Nama Alumni</th><th>Status Pekerjaan</th><th>Perusahaan</th><th>Jabatan</th><th>Gaji</th><th>Relevansi</th><th>Waktu Tunggu</th><th>Tgl Isi</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $i=>$r): ?>
      <tr>
        <td><?= $i+1 ?></td>
        <td><strong><?= htmlspecialchars($r['nama_alumni']) ?></strong></td>
        <td><?php $sp=['bekerja'=>'badge-success','wirausaha'=>'badge-info','belum_bekerja'=>'badge-warning','melanjutkan_studi'=>'badge-primary']; ?><span class="badge <?= $sp[$r['status_pekerjaan']]??'badge-secondary' ?>"><?= ucfirst(str_replace('_',' ',$r['status_pekerjaan']??'-')) ?></span></td>
        <td><?= htmlspecialchars($r['nama_perusahaan']??'-') ?></td>
        <td><?= htmlspecialchars($r['jabatan']??'-') ?></td>
        <td><?= htmlspecialchars($r['gaji_range']??'-') ?></td>
        <td style="text-align:center"><?= $r['relevansi_pelatihan']??'-' ?>/5</td>
        <td style="text-align:center"><?= ($r['waktu_tunggu_kerja']??null) !== null ? $r['waktu_tunggu_kerja'].' bln' : '-' ?></td>
        <td><?= $r['tanggal_isi'] ? date('d/m/Y',strtotime($r['tanggal_isi'])) : '-' ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
    <tfoot>
      <tr style="font-weight:bold;background:#f6f6f6;">
        <td colspan="6">Jumlah Terisi</td>
        <td style="text-align:center"><?= $total ?></td>
        <td colspan="2"></td>
      </tr>
    </tfoot>
  </table>

  <?php elseif ($jenis === 'rktl'): ?>
  <table>
    <thead><tr><th>No</th><th>Nama Alumni</th><th>Pelatihan</th><th>Instruktur</th><th>Tgl Pendampingan</th><th>Rencana Kerja</th><th>Progres</th><th>Status</th><th>Tgl Verifikasi</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $i=>$r): ?>
      <tr>
        <td><?= $i+1 ?></td>
        <td><strong><?= htmlspecialchars($r['nama_alumni']) ?></strong></td>
        <td><?= htmlspecialchars($r['nama_pelatihan']) ?></td>
        <td><?= htmlspecialchars($r['nama_instruktur']) ?></td>
        <td><?= $r['tgl_pendampingan'] ? date('d/m/Y',strtotime($r['tgl_pendampingan'])) : '-' ?></td>
        <td><small><?= htmlspecialchars(substr($r['rencana']??'',0,60)) ?>...</small></td>
        <td>
          <div class="prog-wrap"><div class="prog-bar" style="width:<?= $r['progres'] ?>%;background:<?= $r['progres']>=100?'#28a745':($r['progres']>=50?'#1a4c8e':'#ffc107') ?>"></div></div>
          <?= $r['progres'] ?>%
        </td>
        <td><?php $sb=['belum_mulai'=>'badge-secondary','berjalan'=>'badge-primary','selesai'=>'badge-success','terhambat'=>'badge-danger']; ?><span class="badge <?= $sb[$r['status']]??'badge-secondary' ?>"><?= ucfirst(str_replace('_',' ',$r['status'])) ?></span></td>
        <td><?= $r['tgl_verifikasi'] ? date('d/m/Y',strtotime($r['tgl_verifikasi'])) : '-' ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>

  <?php elseif ($jenis === 'rekomendasi'): ?>
  <table>
    <thead><tr><th>No</th><th>Nama Alumni</th><th>Pelatihan Direkomendasikan</th><th>Kategori</th><th>Tgl Mulai</th><th>Skor</th><th>Alasan</th><th>Dilihat</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $i=>$r): ?>
      <tr>
        <td><?= $i+1 ?></td>
        <td><strong><?= htmlspecialchars($r['nama_alumni']) ?></strong></td>
        <td><?= htmlspecialchars($r['nama_pelatihan']) ?></td>
        <td><?= htmlspecialchars($r['jenis']??'-') ?></td>
        <td><?= date('d/m/Y',strtotime($r['tanggal_mulai'])) ?></td>
        <td style="text-align:center"><span class="badge badge-primary"><?= $r['skor'] ?>%</span></td>
        <td><small><?= htmlspecialchars(substr($r['alasan']??'',0,80)) ?></small></td>
        <td style="text-align:center"><?= $r['is_dilihat'] ? '<span class="badge badge-success">Ya</span>' : '<span class="badge badge-secondary">Belum</span>' ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>

  <?php elseif ($jenis === 'kelulusan'): ?>
  <table>
    <thead><tr><th>No</th><th>Nama Pelatihan</th><th>Instruktur</th><th>Tgl Selesai</th><th>Total</th><th>Lulus</th><th>Tdk Lulus</th><th>% Lulus</th><th>Rata Nilai</th><th>Maks</th><th>Min</th></tr></thead>
    <tbody>
    <?php foreach ($rows as $i=>$r): ?>
      <tr>
        <td><?= $i+1 ?></td>
        <td><strong><?= htmlspecialchars($r['nama_pelatihan']) ?></strong></td>
        <td><?= htmlspecialchars($r['nama_instruktur']) ?></td>
        <td><?= date('d/m/Y',strtotime($r['tanggal_selesai'])) ?></td>
        <td style="text-align:center"><?= $r['total_peserta'] ?></td>
        <td style="text-align:center"><span class="badge badge-success"><?= $r['lulus'] ?></span></td>
        <td style="text-align:center"><span class="badge badge-danger"><?= $r['tidak_lulus'] ?></span></td>
        <td>
          <div class="prog-wrap"><div class="prog-bar" style="width:<?= min($r['pct_lulus'],100) ?>%;background:#28a745"></div></div>
          <?= $r['pct_lulus'] ?>%
        </td>
        <td style="text-align:center"><?= $r['rata_nilai']??'-' ?></td>
        <td style="text-align:center"><?= $r['nilai_max']??'-' ?></td>
        <td style="text-align:center"><?= $r['nilai_min']??'-' ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
    <tfoot>
      <tr style="font-weight:bold;background:#f6f6f6;">
        <td colspan="4">Jumlah</td>
        <td style="text-align:center"><?= array_sum(array_column($rows,'total_peserta')) ?></td>
        <td style="text-align:center"><?= array_sum(array_column($rows,'lulus')) ?></td>
        <td style="text-align:center"><?= array_sum(array_column($rows,'tidak_lulus')) ?></td>
        <td style="text-align:center"><?= array_sum(array_column($rows,'total_peserta')) ? round(array_sum(array_column($rows,'lulus'))/array_sum(array_column($rows,'total_peserta'))*100,1) : 0 ?>%</td>
        <td style="text-align:center">-</td>
        <td style="text-align:center"><?= $rows ? max(array_filter(array_column($rows,'nilai_max'),fn($v)=>$v!==null && $v!=='')) : '-' ?></td>
        <td style="text-align:center"><?= $rows ? min(array_filter(array_column($rows,'nilai_min'),fn($v)=>$v!==null && $v!=='')) : '-' ?></td>
      </tr>
    </tfoot>
  </table>
  <?php endif; ?>

  <!-- TTD + QR Code -->
  <?php
  // Generate kode unik laporan & simpan ke tabel persetujuan
  $kode_laporan = md5($jenis . $tgl_dari . $tgl_sampai . $_SESSION['id_login'] . date('YmdH'));
  $cek_tabel_pl = mysqli_query($koneksi, "SHOW TABLES LIKE 'persetujuan_laporan'");
  if ($cek_tabel_pl && mysqli_num_rows($cek_tabel_pl) > 0) {
      $kode_esc = mysqli_real_escape_string($koneksi, $kode_laporan);
      $cek_kode = mysqli_fetch_row(mysqli_query($koneksi,
          "SELECT id FROM persetujuan_laporan WHERE kode_laporan='$kode_esc'"));
      if (!$cek_kode) {
          mysqli_query($koneksi, "INSERT INTO persetujuan_laporan
              (kode_laporan, jenis, periode_dari, periode_sampai, dibuat_oleh)
              VALUES ('$kode_esc', '$jenis', '$tgl_dari', '$tgl_sampai', {$_SESSION['id_login']})");
      }
      // Cek apakah sudah disetujui
      $pl = mysqli_fetch_assoc(mysqli_query($koneksi,
          "SELECT pl.*, uk.name as nama_kepala, k.nip
           FROM persetujuan_laporan pl
           LEFT JOIN kepala k ON pl.kepala_id=k.id
           LEFT JOIN users uk ON k.user_id=uk.id
           WHERE pl.kode_laporan='$kode_esc'"));
  } else {
      $pl = null;
  }
  // URL halaman persetujuan untuk QR Code
  $base_url    = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'];
  $path_parts  = explode('/', $_SERVER['PHP_SELF']);
  array_pop($path_parts); array_pop($path_parts);
  $base_path   = implode('/', $path_parts);
  $qr_link     = $base_url . $base_path . '/kepala/setujui_laporan.php?kode=' . $kode_laporan;
  $qr_url      = "https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=" . urlencode($qr_link);
  ?>
  <div style="display:flex;justify-content:space-between;align-items:flex-end;margin-top:40px">
    <!-- Keterangan kiri -->
    <div style="font-size:10pt;color:#555">
      <p>Mengetahui,</p>
      <p style="margin-top:60px">________________________</p>
      <p style="font-size:9pt;color:#888">Pejabat terkait</p>
    </div>

    <!-- TTD Kepala kanan + QR Code -->
    <div style="text-align:center">
      <div style="font-size:10pt">Banjarmasin, <?= date('d F Y') ?></div>
      <div style="font-size:10pt;font-weight:bold;margin-top:4px"><?= htmlspecialchars($kepala['jabatan'] ?? 'Kepala Balai Pelatihan dan Pemberdayaan Masyarakat Desa') ?></div>
      <div style="font-size:9pt;color:#444;margin-bottom:10px">Daerah Tertinggal dan Transmigrasi Banjarmasin</div>
      <img src="<?= $qr_url ?>" alt="QR Code" style="width:110px;height:110px;border:1px solid #ddd;border-radius:4px;display:block;margin:0 auto 4px">
      <?php if ($pl && $pl['status'] === 'diterima'): ?>
        <div style="font-size:8pt;color:#16a34a;font-weight:bold;margin-top:4px">✓ Disetujui Kepala</div>
        <div style="font-size:7pt;color:#888"><?= date('d/m/Y H:i', strtotime($pl['tgl_diterima'])) ?></div>
        <div style="font-size:7pt;color:#888"><?= htmlspecialchars($pl['nama_kepala'] ?? '') ?></div>
      <?php else: ?>
        <div style="font-size:7pt;color:#aaa;margin-top:4px">Scan untuk persetujuan kepala</div>
      <?php endif; ?>
      <div style="width:220px;border-bottom:1px solid #000;margin:10px auto 4px"></div>
      <div style="font-size:10pt;font-weight:bold;text-transform:uppercase"><?= htmlspecialchars($kepala['nama_lengkap'] ?? '______________________________') ?></div>
      <div style="font-size:9pt;color:#444">NIP. <?= htmlspecialchars($kepala['nip'] ?? '______________________') ?></div>
      <?php if (!empty($kepala['pangkat']) && !empty($kepala['golongan'])): ?>
        <div style="font-size:9pt;color:#444"><?= htmlspecialchars($kepala['pangkat']) ?> / <?= htmlspecialchars($kepala['golongan']) ?></div>
      <?php endif; ?>
    </div>
  </div>

  <!-- FOOTER -->
  <div class="footer-lap">
    <span>Dicetak oleh: <?= htmlspecialchars($_SESSION['nama']) ?> · <?= date('d/m/Y H:i') ?></span>
    <span>BPPMDDTT Banjarmasin · Aplikasi Monitoring dan Manajemen Data Alumni, Pelatih</span>
    <span>Total Data: <?= $total ?> baris</span>
  </div>

</div><!-- end halaman -->

</body>
</html>