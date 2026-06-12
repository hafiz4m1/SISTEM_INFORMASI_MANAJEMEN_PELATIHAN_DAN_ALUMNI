<?php
$page_title = 'Dashboard';
include 'header.php';

// Statistik ringkasan
$total_pelatihan  = mysqli_fetch_row(mysqli_query($koneksi, "SELECT COUNT(*) FROM pelatihan"))[0];
$total_alumni     = mysqli_fetch_row(mysqli_query($koneksi, "SELECT COUNT(*) FROM alumni"))[0];
$total_peserta    = mysqli_fetch_row(mysqli_query($koneksi, "SELECT COUNT(DISTINCT user_id) FROM peserta_pelatihan"))[0];
$total_lulus      = mysqli_fetch_row(mysqli_query($koneksi, "SELECT COUNT(*) FROM peserta_pelatihan WHERE status_lulus='lulus'"))[0];
$total_tracer     = mysqli_fetch_row(mysqli_query($koneksi, "SELECT COUNT(*) FROM tracer_study WHERE status_pengisian='sudah_diisi'"))[0];
$total_rktl_selesai = mysqli_fetch_row(mysqli_query($koneksi, "SELECT COUNT(*) FROM rktl WHERE status='selesai'"))[0];

// Laporan menunggu pengesahan
$lap_menunggu = 0;
$cek = mysqli_query($koneksi, "SHOW TABLES LIKE 'persetujuan_laporan'");
if ($cek && mysqli_num_rows($cek) > 0) {
    $lap_menunggu = mysqli_fetch_row(mysqli_query($koneksi,
        "SELECT COUNT(*) FROM persetujuan_laporan WHERE status='menunggu'"))[0];
}

// Statistik tracer
$stat_kerja = mysqli_fetch_assoc(mysqli_query($koneksi, "
    SELECT
        SUM(status_pekerjaan='bekerja') as bekerja,
        SUM(status_pekerjaan='wirausaha') as wirausaha,
        SUM(status_pekerjaan='belum_bekerja') as belum_kerja,
        SUM(status_pekerjaan='melanjutkan_studi') as studi,
        ROUND(AVG(relevansi_pelatihan),1) as avg_relevansi
    FROM tracer_study WHERE status_pengisian='sudah_diisi'
"));

// Pelatihan terbaru
$pelatihan = mysqli_query($koneksi, "
    SELECT p.*, ui.name as nama_instruktur,
        COUNT(DISTINCT pp.user_id) as jml_peserta,
        SUM(pp.status_lulus='lulus') as jml_lulus,
        ROUND(AVG(pp.nilai),1) as rata_nilai
    FROM pelatihan p
    JOIN instruktur i ON p.instruktur_id=i.id
    JOIN users ui ON i.user_id=ui.id
    LEFT JOIN peserta_pelatihan pp ON pp.pelatihan_id=p.id
    GROUP BY p.id
    ORDER BY p.created_at DESC LIMIT 5
");
?>

<!-- Alert laporan menunggu -->
<?php if ($lap_menunggu > 0): ?>
<div class="alert d-flex align-items-center gap-3 mb-4"
     style="background:#fff3cd;border:1px solid #ffc107;border-radius:10px;padding:14px 18px">
  <i class="bi bi-patch-check text-warning fs-4"></i>
  <div>
    <strong><?= $lap_menunggu ?> laporan menunggu pengesahan Anda.</strong><br>
    <small class="text-muted">Segera tinjau dan sahkan laporan yang masuk.</small>
  </div>
  <a href="pengesahan.php" class="btn btn-sm btn-warning ms-auto">Lihat Sekarang</a>
</div>
<?php endif; ?>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
  <div class="col-sm-4 col-lg-2">
    <div class="stat-card bg-white shadow-sm">
      <div class="icon bg-primary bg-opacity-10 text-primary"><i class="bi bi-journal-bookmark"></i></div>
      <div><div class="val text-primary"><?= $total_pelatihan ?></div><div class="lbl">Pelatihan</div></div>
    </div>
  </div>
  <div class="col-sm-4 col-lg-2">
    <div class="stat-card bg-white shadow-sm">
      <div class="icon bg-success bg-opacity-10 text-success"><i class="bi bi-mortarboard"></i></div>
      <div><div class="val text-success"><?= $total_alumni ?></div><div class="lbl">Alumni</div></div>
    </div>
  </div>
  <div class="col-sm-4 col-lg-2">
    <div class="stat-card bg-white shadow-sm">
      <div class="icon bg-warning bg-opacity-10 text-warning"><i class="bi bi-people"></i></div>
      <div><div class="val text-warning"><?= $total_peserta ?></div><div class="lbl">Peserta</div></div>
    </div>
  </div>
  <div class="col-sm-4 col-lg-2">
    <div class="stat-card bg-white shadow-sm">
      <div class="icon bg-info bg-opacity-10 text-info"><i class="bi bi-patch-check"></i></div>
      <div><div class="val text-info"><?= $total_lulus ?></div><div class="lbl">Lulus</div></div>
    </div>
  </div>
  <div class="col-sm-4 col-lg-2">
    <div class="stat-card bg-white shadow-sm">
      <div class="icon bg-success bg-opacity-10 text-success"><i class="bi bi-clipboard-check"></i></div>
      <div><div class="val text-success"><?= $total_tracer ?></div><div class="lbl">Tracer Terisi</div></div>
    </div>
  </div>
  <div class="col-sm-4 col-lg-2">
    <div class="stat-card bg-white shadow-sm">
      <div class="icon bg-secondary bg-opacity-10 text-secondary"><i class="bi bi-clipboard2-check"></i></div>
      <div><div class="val text-secondary"><?= $total_rktl_selesai ?></div><div class="lbl">RKTL Selesai</div></div>
    </div>
  </div>
</div>

<div class="row g-3">
  <!-- Statistik Alumni -->
  <div class="col-lg-4">
    <div class="card">
      <div class="card-header">Status Alumni Pasca Pelatihan</div>
      <div class="card-body p-3">
        <?php
        $items = [
          ['Bekerja',       $stat_kerja['bekerja']    ?? 0, 'success'],
          ['Wirausaha',     $stat_kerja['wirausaha']   ?? 0, 'info'],
          ['Belum Bekerja', $stat_kerja['belum_kerja'] ?? 0, 'warning'],
          ['Lanjut Studi',  $stat_kerja['studi']       ?? 0, 'primary'],
        ];
        $total_ts = array_sum(array_column($items, 1));
        foreach ($items as $item):
          $pct = $total_ts > 0 ? round($item[1]/$total_ts*100) : 0;
        ?>
        <div class="mb-3">
          <div class="d-flex justify-content-between mb-1" style="font-size:13px">
            <span><?= $item[0] ?></span>
            <span class="fw-semibold"><?= $item[1] ?> (<?= $pct ?>%)</span>
          </div>
          <div class="progress" style="height:8px;border-radius:4px">
            <div class="progress-bar bg-<?= $item[2] ?>" style="width:<?= $pct ?>%"></div>
          </div>
        </div>
        <?php endforeach; ?>
        <hr class="my-2">
        <div class="d-flex justify-content-between" style="font-size:13px">
          <span class="text-muted">Rata-rata relevansi pelatihan</span>
          <span class="fw-semibold text-warning">⭐ <?= $stat_kerja['avg_relevansi'] ?? '-' ?>/5</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Pelatihan Terbaru -->
  <div class="col-lg-8">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        Pelatihan Terbaru
        <a href="laporan.php" class="btn btn-sm btn-outline-primary">Lihat Laporan</a>
      </div>
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead><tr><th>Pelatihan</th><th>Instruktur</th><th>Tgl Mulai</th><th>Peserta</th><th>Lulus</th><th>Rata Nilai</th><th>Status</th></tr></thead>
          <tbody>
          <?php while ($p = mysqli_fetch_assoc($pelatihan)): ?>
            <tr>
              <td><?= htmlspecialchars($p['nama_pelatihan']) ?></td>
              <td><?= htmlspecialchars($p['nama_instruktur']) ?></td>
              <td><?= date('d M Y', strtotime($p['tanggal_mulai'])) ?></td>
              <td><?= $p['jml_peserta'] ?></td>
              <td><span class="badge bg-success"><?= $p['jml_lulus'] ?></span></td>
              <td><?= $p['rata_nilai'] ?? '-' ?></td>
              <td>
                <?php $sb=['aktif'=>'success','selesai'=>'secondary','dibatalkan'=>'danger']; ?>
                <span class="badge bg-<?= $sb[$p['status']]??'secondary' ?>"><?= ucfirst($p['status']) ?></span>
              </td>
            </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php include 'footer.php'; ?>
