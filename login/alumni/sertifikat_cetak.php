<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['level'] !== 'alumni') {
    header("location: ../login.php"); exit;
}
include '../koneksi.php';

$pp_id = (int)($_GET['id'] ?? 0);

// Ambil data peserta pelatihan + alumni
$data = mysqli_fetch_assoc(mysqli_query($koneksi, "
    SELECT pp.*, p.nama_pelatihan, p.jenis, p.tanggal_mulai, p.tanggal_selesai, p.lokasi,
        u.name as nama_alumni,
        ui.name as nama_instruktur,
        a.nik
    FROM peserta_pelatihan pp
    JOIN pelatihan p ON pp.pelatihan_id = p.id
    JOIN instruktur i ON p.instruktur_id = i.id
    JOIN users u ON pp.user_id = u.id
    JOIN users ui ON i.user_id = ui.id
    LEFT JOIN alumni a ON a.user_id = pp.user_id
    WHERE pp.id = $pp_id
    AND pp.user_id = {$_SESSION['id_login']}
    AND pp.status_lulus = 'lulus'
"));

if (!$data) {
    die('<div style="text-align:center;padding:40px;font-family:sans-serif">
        <h3>Sertifikat tidak ditemukan</h3>
        <a href="sertifikat.php">Kembali</a>
    </div>');
}

// Generate nomor sertifikat unik
$no_sertifikat = 'BPPMDDTT/' . date('Y') . '/' . str_pad($pp_id, 5, '0', STR_PAD_LEFT);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sertifikat - <?= htmlspecialchars($data['nama_alumni']) ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      background: #f0f0f0;
      font-family: 'Inter', sans-serif;
      display: flex;
      flex-direction: column;
      align-items: center;
      min-height: 100vh;
      padding: 30px 20px;
    }

    /* Toolbar (tidak ikut print) */
    .toolbar {
      background: #1a2942;
      color: #fff;
      padding: 12px 24px;
      border-radius: 10px;
      display: flex;
      align-items: center;
      gap: 16px;
      margin-bottom: 24px;
      width: 100%;
      max-width: 900px;
    }
    .toolbar span { flex: 1; font-size: 14px; opacity: .75; }
    .toolbar button {
      background: #1a4c8e; color: #fff; border: none;
      padding: 8px 20px; border-radius: 6px; font-size: 13px;
      font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 6px;
    }
    .toolbar button:hover { background: #123570; }
    .toolbar a {
      color: rgba(255,255,255,.6); font-size: 13px; text-decoration: none;
      display: flex; align-items: center; gap: 4px;
    }
    .toolbar a:hover { color: #fff; }

    /* Sertifikat */
    .sertifikat {
      width: 900px;
      min-height: 636px;
      background: #fff;
      position: relative;
      overflow: hidden;
      box-shadow: 0 8px 40px rgba(0,0,0,.15);
    }

    /* Border ornamen */
    .sertifikat::before {
      content: '';
      position: absolute;
      inset: 16px;
      border: 2px solid #c8a84b;
      z-index: 1;
      pointer-events: none;
    }
    .sertifikat::after {
      content: '';
      position: absolute;
      inset: 20px;
      border: 1px solid #e8d080;
      z-index: 1;
      pointer-events: none;
    }

    /* Background pattern */
    .bg-pattern {
      position: absolute;
      inset: 0;
      background:
        radial-gradient(circle at 0% 0%, rgba(26,76,142,.06) 0%, transparent 50%),
        radial-gradient(circle at 100% 100%, rgba(26,76,142,.06) 0%, transparent 50%);
    }

    /* Watermark */
    .watermark {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      font-size: 120px;
      color: rgba(26,76,142,.04);
      font-weight: 900;
      white-space: nowrap;
      z-index: 0;
      letter-spacing: 8px;
    }

    /* Content */
    .content {
      position: relative;
      z-index: 2;
      padding: 48px 64px;
      text-align: center;
    }

    .header-logos {
      display: flex;
      justify-content: center;
      align-items: center;
      gap: 16px;
      margin-bottom: 20px;
    }
    .logo-placeholder {
      width: 64px; height: 64px;
      background: linear-gradient(135deg, #1a4c8e, #0d3060);
      border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      color: #fff; font-size: 28px;
    }
    .instansi {
      text-align: left;
    }
    .instansi .nama { font-size: 13px; font-weight: 700; color: #1a2942; line-height: 1.3; }
    .instansi .sub  { font-size: 11px; color: #6b7280; }

    .divider-gold {
      width: 100%;
      height: 3px;
      background: linear-gradient(90deg, transparent, #c8a84b, #f0d060, #c8a84b, transparent);
      margin: 16px 0;
    }
    .divider-thin {
      width: 100%;
      height: 1px;
      background: linear-gradient(90deg, transparent, #c8a84b, transparent);
      margin: 8px 0;
    }

    .judul-sertifikat {
      font-family: 'Playfair Display', serif;
      font-size: 38px;
      font-weight: 700;
      color: #1a4c8e;
      letter-spacing: 4px;
      text-transform: uppercase;
      margin: 16px 0 4px;
    }
    .subjudul {
      font-size: 12px;
      color: #6b7280;
      letter-spacing: 2px;
      text-transform: uppercase;
    }

    .diberikan-kepada {
      font-size: 13px;
      color: #6b7280;
      margin: 24px 0 8px;
      letter-spacing: 1px;
    }
    .nama-penerima {
      font-family: 'Playfair Display', serif;
      font-size: 44px;
      font-weight: 700;
      color: #1a2942;
      border-bottom: 2px solid #c8a84b;
      display: inline-block;
      padding-bottom: 4px;
      margin-bottom: 16px;
    }

    .keterangan {
      font-size: 13px;
      color: #4b5563;
      line-height: 1.8;
      max-width: 600px;
      margin: 0 auto 20px;
    }
    .keterangan strong { color: #1a2942; }

    .detail-box {
      display: inline-flex;
      gap: 32px;
      background: #f8f6f0;
      border: 1px solid #e8d080;
      border-radius: 8px;
      padding: 12px 24px;
      margin: 12px 0;
    }
    .detail-item { text-align: center; }
    .detail-item .label { font-size: 10px; color: #9ca3af; text-transform: uppercase; letter-spacing: .06em; }
    .detail-item .value { font-size: 13px; font-weight: 600; color: #1a2942; margin-top: 2px; }

    .nilai-badge {
      display: inline-block;
      background: linear-gradient(135deg, #c8a84b, #f0d060);
      color: #5a3c00;
      font-weight: 700;
      font-size: 14px;
      padding: 4px 16px;
      border-radius: 20px;
      margin: 8px 0 20px;
    }

    .ttd-area {
      display: flex;
      justify-content: space-between;
      align-items: flex-end;
      margin-top: 24px;
      padding: 0 40px;
    }
    .ttd-box { text-align: center; }
    .ttd-box .ttd-line {
      width: 160px;
      border-bottom: 1.5px solid #1a2942;
      margin: 48px auto 4px;
    }
    .ttd-box .ttd-nama { font-size: 13px; font-weight: 600; color: #1a2942; }
    .ttd-box .ttd-jabatan { font-size: 11px; color: #6b7280; }

    .no-sertifikat {
      font-size: 10px;
      color: #9ca3af;
      margin-top: 16px;
      letter-spacing: .04em;
    }

    /* Corner ornaments */
    .corner {
      position: absolute;
      width: 60px;
      height: 60px;
      z-index: 2;
    }
    .corner-tl { top: 24px; left: 24px; border-top: 3px solid #c8a84b; border-left: 3px solid #c8a84b; }
    .corner-tr { top: 24px; right: 24px; border-top: 3px solid #c8a84b; border-right: 3px solid #c8a84b; }
    .corner-bl { bottom: 24px; left: 24px; border-bottom: 3px solid #c8a84b; border-left: 3px solid #c8a84b; }
    .corner-br { bottom: 24px; right: 24px; border-bottom: 3px solid #c8a84b; border-right: 3px solid #c8a84b; }

    /* Print */
    @media print {
      body { background: #fff; padding: 0; }
      .toolbar { display: none !important; }
      .sertifikat { box-shadow: none; width: 100%; }
      @page { size: A4 landscape; margin: 0; }
    }
  </style>
</head>
<body>

<!-- Toolbar -->
<div class="toolbar">
  <a href="sertifikat.php">&#8592; Kembali</a>
  <span>Sertifikat - <?= htmlspecialchars($data['nama_alumni']) ?></span>
  <button onclick="window.print()">🖨️ Print / Save PDF</button>
</div>

<!-- Sertifikat -->
<div class="sertifikat">
  <div class="bg-pattern"></div>
  <div class="watermark">BPPMDDTT</div>

  <!-- Corner ornaments -->
  <div class="corner corner-tl"></div>
  <div class="corner corner-tr"></div>
  <div class="corner corner-bl"></div>
  <div class="corner corner-br"></div>

  <div class="content">
    <!-- Header -->
    <div class="header-logos">
      <div class="logo-placeholder">🏛️</div>
      <div class="instansi">
        <div class="nama">BALAI PELATIHAN DAN PEMBERDAYAAN MASYARAKAT DESA</div>
        <div class="sub">Daerah Tertinggal dan Transmigrasi · Banjarmasin</div>
        <div class="sub">Kementerian Desa, Pembangunan Daerah Tertinggal, dan Transmigrasi</div>
      </div>
    </div>

    <div class="divider-gold"></div>
    <div class="divider-thin"></div>

    <div class="judul-sertifikat">Sertifikat</div>
    <div class="subjudul">Kelulusan Pelatihan</div>

    <div class="diberikan-kepada">Diberikan kepada</div>
    <div class="nama-penerima"><?= htmlspecialchars($data['nama_alumni']) ?></div>

    <div class="keterangan">
      Telah berhasil mengikuti dan menyelesaikan pelatihan
      <strong>"<?= htmlspecialchars($data['nama_pelatihan']) ?>"</strong>
      yang diselenggarakan oleh BPPMDDTT Banjarmasin
    </div>

    <div class="detail-box">
      <div class="detail-item">
        <div class="label">Tanggal Pelaksanaan</div>
        <div class="value"><?= date('d M Y', strtotime($data['tanggal_mulai'])) ?> - <?= date('d M Y', strtotime($data['tanggal_selesai'])) ?></div>
      </div>
      <?php if ($data['lokasi']): ?>
      <div class="detail-item">
        <div class="label">Lokasi</div>
        <div class="value"><?= htmlspecialchars($data['lokasi']) ?></div>
      </div>
      <?php endif; ?>
      <div class="detail-item">
        <div class="label">Instruktur</div>
        <div class="value"><?= htmlspecialchars($data['nama_instruktur']) ?></div>
      </div>
    </div>

    <div class="nilai-badge">Nilai: <?= $data['nilai'] ?> &nbsp;·&nbsp; Predikat: <?= $data['nilai'] >= 90 ? 'Sangat Memuaskan' : ($data['nilai'] >= 80 ? 'Memuaskan' : ($data['nilai'] >= 70 ? 'Cukup' : 'Lulus')) ?></div>

    <div class="divider-thin"></div>

    <!-- TTD -->
    <div class="ttd-area">
      <div class="ttd-box">
        <div class="ttd-jabatan">Banjarmasin, <?= date('d F Y', strtotime($data['tanggal_selesai'])) ?></div>
        <div class="ttd-line"></div>
        <div class="ttd-nama">Kepala BPPMDDTT Banjarmasin</div>
        <div class="ttd-jabatan">NIP. ____________________</div>
      </div>
      <div class="ttd-box" style="text-align:center">
        <div class="no-sertifikat">No. Sertifikat</div>
        <div style="font-size:12px;font-weight:600;color:#1a2942;letter-spacing:.04em"><?= $no_sertifikat ?></div>
      </div>
      <div class="ttd-box">
        <div class="ttd-jabatan">Instruktur Pelatihan</div>
        <div class="ttd-line"></div>
        <div class="ttd-nama"><?= htmlspecialchars($data['nama_instruktur']) ?></div>
        <div class="ttd-jabatan">Instruktur BPPMDDTT</div>
      </div>
    </div>

  </div>
</div>

</body>
</html>
