<?php
$page_title = 'Dashboard';
include '../koneksi.php';
include_once '../notif.php';
include 'header.php';

// Statistik utama
$total_pelatihan  = mysqli_fetch_row(mysqli_query($koneksi, "SELECT COUNT(*) FROM pelatihan"))[0];
$total_alumni     = mysqli_fetch_row(mysqli_query($koneksi, "SELECT COUNT(*) FROM alumni"))[0];
$total_peserta    = mysqli_fetch_row(mysqli_query($koneksi, "SELECT COUNT(DISTINCT user_id) FROM peserta_pelatihan"))[0];
$total_tracer     = mysqli_fetch_row(mysqli_query($koneksi, "SELECT COUNT(*) FROM tracer_study WHERE status_pengisian='sudah_diisi'"))[0];
$pel_aktif        = mysqli_fetch_row(mysqli_query($koneksi, "SELECT COUNT(*) FROM pelatihan WHERE status='aktif'"))[0];
$menunggu_verif   = mysqli_fetch_row(mysqli_query($koneksi, "SELECT COUNT(*) FROM peserta_pelatihan WHERE status_verifikasi='menunggu'"))[0];
$rktl_selesai     = mysqli_fetch_row(mysqli_query($koneksi, "SELECT COUNT(*) FROM rktl WHERE status='selesai'"))[0];
$total_lulus      = mysqli_fetch_row(mysqli_query($koneksi, "SELECT COUNT(*) FROM peserta_pelatihan WHERE status_lulus='lulus'"))[0];

// Data untuk chart sebaran status pekerjaan alumni
$chart_kerja = [];
$q_chart = mysqli_query($koneksi, "SELECT status_pekerjaan, COUNT(*) as jml FROM tracer_study WHERE status_pengisian='sudah_diisi' AND status_pekerjaan IS NOT NULL GROUP BY status_pekerjaan");
while ($r = mysqli_fetch_assoc($q_chart)) $chart_kerja[] = $r;

// Pelatihan terbaru
$q_pel = mysqli_query($koneksi, "
    SELECT p.*, ui.name as nama_instruktur,
        (SELECT COUNT(*) FROM peserta_pelatihan WHERE pelatihan_id=p.id AND status_verifikasi='diterima') as jml_peserta
    FROM pelatihan p
    JOIN instruktur i ON p.instruktur_id=i.id
    JOIN users ui ON i.user_id=ui.id
    ORDER BY p.created_at DESC LIMIT 5
");

// Tracer terbaru
$q_tracer = mysqli_query($koneksi, "
    SELECT ts.*, u.name as nama_alumni
    FROM tracer_study ts
    JOIN alumni a ON ts.alumni_id=a.id
    JOIN users u ON a.user_id=u.id
    WHERE ts.status_pengisian='sudah_diisi'
    ORDER BY ts.tanggal_isi DESC LIMIT 5
");

// RKTL mendekati deadline
$q_rktl = mysqli_query($koneksi, "
    SELECT r.*, u.name as nama_alumni, p.nama_pelatihan
    FROM rktl r
    JOIN alumni a ON r.alumni_id=a.id
    JOIN users u ON a.user_id=u.id
    JOIN pelatihan p ON r.pelatihan_id=p.id
    WHERE r.status != 'selesai'
    ORDER BY r.tgl_pendampingan ASC LIMIT 4
");
?>

<!-- Galeri Foto Balai -->
<div class="row g-3 mb-4">
  <div class="col-md-4">
    <div style="border-radius:14px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.08);height:160px;position:relative">
      <img src="../assets/images/balai1.jpg" alt="Gedung BPPMDDTT Banjarmasin"
           style="width:100%;height:100%;object-fit:cover;display:block">
      <div style="position:absolute;bottom:0;left:0;right:0;background:linear-gradient(transparent,rgba(0,0,0,.6));padding:10px 12px;color:#fff;font-size:12px;font-weight:600">
        Gedung BPPMDDTT Banjarmasin
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div style="border-radius:14px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.08);height:160px;position:relative">
      <img src="../assets/images/balai2.jpg" alt="Ruang Pelatihan"
           style="width:100%;height:100%;object-fit:cover;display:block">
      <div style="position:absolute;bottom:0;left:0;right:0;background:linear-gradient(transparent,rgba(0,0,0,.6));padding:10px 12px;color:#fff;font-size:12px;font-weight:600">
        Ruang Pelatihan
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div style="border-radius:14px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.08);height:160px;position:relative">
      <img src="../assets/images/balai3.jpg" alt="Aula BPPMDDTT"
           style="width:100%;height:100%;object-fit:cover;display:block">
      <div style="position:absolute;bottom:0;left:0;right:0;background:linear-gradient(transparent,rgba(0,0,0,.6));padding:10px 12px;color:#fff;font-size:12px;font-weight:600">
        Aula BPPMDDTT
      </div>
    </div>
  </div>
</div>

<!-- Alert verifikasi pending -->
<?php if ($menunggu_verif > 0): ?>
<div class="alert d-flex align-items-center gap-3 mb-3" style="background:#fffbeb;border:1px solid #fde68a;border-radius:12px">
  <i class="bi bi-hourglass-split text-warning fs-5"></i>
  <div style="flex:1;font-size:13px"><strong><?= $menunggu_verif ?> pendaftaran</strong> menunggu verifikasi.</div>
  <a href="verifikasi_pendaftaran.php" class="btn btn-sm btn-warning fw-semibold">Proses Sekarang</a>
</div>
<?php endif; ?>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
  <?php
  $stats = [
    ['bi-journal-bookmark-fill','#e8f0fe','#1a4c8e',$total_pelatihan,'Total Pelatihan','pelatihan.php'],
    ['bi-mortarboard-fill','#e8f5e9','#1d9e75',$total_alumni,'Total Alumni','alumni.php'],
    ['bi-people-fill','#fff3e0','#f59e0b',$total_peserta,'Total Peserta','peserta.php'],
    ['bi-clipboard-check-fill','#f3e8ff','#7c3aed',$total_tracer,'Tracer Terisi','tracer.php'],
    ['bi-broadcast','#e0f2fe','#0284c7',$pel_aktif,'Pelatihan Aktif','pelatihan.php'],
    ['bi-patch-check-fill','#f0fdf4','#16a34a',$total_lulus,'Total Lulus','peserta.php'],
    ['bi-graph-up','#fdf2f8','#c026d3',$rktl_selesai,'RKTL Selesai','rktl.php'],
    ['bi-clock-history','#fff1f2','#e11d48',$menunggu_verif,'Menunggu Verif','verifikasi_pendaftaran.php'],
  ];
  foreach ($stats as $s):
  ?>
  <div class="col-6 col-md-4 col-xl-3">
    <a href="<?= $s[5] ?>" style="text-decoration:none">
      <div class="stat-card bg-white shadow-sm" style="cursor:pointer">
        <div class="icon" style="background:<?= $s[1] ?>;color:<?= $s[2] ?>;width:48px;height:48px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0">
          <i class="bi <?= $s[0] ?>"></i>
        </div>
        <div>
          <div class="val" style="color:<?= $s[2] ?>"><?= $s[3] ?></div>
          <div class="lbl"><?= $s[4] ?></div>
        </div>
      </div>
    </a>
  </div>
  <?php endforeach; ?>
</div>

<!-- Charts + Tabel -->
<div class="row g-3 mb-3">
  <!-- Chart status pekerjaan alumni -->
  <div class="col-lg-4">
    <div class="card h-100">
      <div class="card-header">
        <i class="bi bi-pie-chart-fill me-2 text-primary"></i>Status Pekerjaan Alumni
      </div>
      <div class="card-body d-flex align-items-center justify-content-center" style="min-height:220px">
        <?php if (empty($chart_kerja)): ?>
          <div class="empty-state"><i class="bi bi-pie-chart"></i><p>Belum ada data tracer study</p></div>
        <?php else: ?>
          <canvas id="chartKerja" style="max-height:220px"></canvas>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Pelatihan Terbaru -->
  <div class="col-lg-8">
    <div class="card h-100">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-journal-bookmark me-2 text-primary"></i>Pelatihan Terbaru</span>
        <a href="pelatihan.php" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
      </div>
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead><tr><th>Nama Pelatihan</th><th>Instruktur</th><th>Tgl Mulai</th><th>Peserta</th><th>Status</th></tr></thead>
          <tbody>
          <?php while ($row = mysqli_fetch_assoc($q_pel)): ?>
            <tr>
              <td class="fw-semibold"><?= htmlspecialchars($row['nama_pelatihan']) ?></td>
              <td><small><?= htmlspecialchars($row['nama_instruktur']) ?></small></td>
              <td><small><?= date('d M Y', strtotime($row['tanggal_mulai'])) ?></small></td>
              <td><span class="badge bg-secondary bg-opacity-15 text-secondary"><?= $row['jml_peserta'] ?>/<?= $row['kuota'] ?></span></td>
              <td>
                <?php $bc=['aktif'=>['success','Aktif'],'selesai'=>['secondary','Selesai'],'dibatalkan'=>['danger','Dibatalkan']];
                      $b=$bc[$row['status']]??['secondary','?']; ?>
                <span class="badge bg-<?= $b[0] ?> bg-opacity-15 text-<?= $b[0] ?>"><?= $b[1] ?></span>
              </td>
            </tr>
          <?php endwhile; ?>
          <?php if (mysqli_num_rows($q_pel)===0): ?>
            <tr><td colspan="5" class="text-center text-muted py-4">Belum ada data</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<div class="row g-3">
  <!-- Tracer Study Terbaru -->
  <div class="col-lg-6">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-clipboard-data me-2 text-purple"></i>Tracer Study Terbaru</span>
        <a href="tracer.php" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
      </div>
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead><tr><th>Alumni</th><th>Status Kerja</th><th>Relevansi</th><th>Tgl Isi</th></tr></thead>
          <tbody>
          <?php while ($row = mysqli_fetch_assoc($q_tracer)): ?>
            <tr>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <div class="avatar-init" style="width:28px;height:28px;font-size:11px">
                    <?= strtoupper(substr($row['nama_alumni'],0,1)) ?>
                  </div>
                  <span class="fw-semibold" style="font-size:13px"><?= htmlspecialchars($row['nama_alumni']) ?></span>
                </div>
              </td>
              <td>
                <?php $sc=['bekerja'=>'success','wirausaha'=>'info','belum_bekerja'=>'warning','melanjutkan_studi'=>'primary'];
                      $sl=['bekerja'=>'Bekerja','wirausaha'=>'Wirausaha','belum_bekerja'=>'Blm Bekerja','melanjutkan_studi'=>'Lanjut Studi'];
                      $sp=$row['status_pekerjaan']??'-'; ?>
                <span class="badge bg-<?= $sc[$sp]??'secondary' ?> bg-opacity-15 text-<?= $sc[$sp]??'secondary' ?>"><?= $sl[$sp]??ucfirst($sp) ?></span>
              </td>
              <td>
                <?php if ($row['relevansi_pelatihan']): ?>
                  <span style="color:#f59e0b;font-size:13px">
                    <?php for($i=1;$i<=5;$i++) echo '<i class="bi bi-star'.($i<=$row['relevansi_pelatihan']?'-fill':'').'"></i>'; ?>
                  </span>
                <?php else: ?><small class="text-muted">-</small><?php endif; ?>
              </td>
              <td><small class="text-muted"><?= $row['tanggal_isi'] ? date('d M Y', strtotime($row['tanggal_isi'])) : '-' ?></small></td>
            </tr>
          <?php endwhile; ?>
          <?php if (mysqli_num_rows($q_tracer)===0): ?>
            <tr><td colspan="4" class="text-center text-muted py-4">Belum ada data</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Monitoring RKTL -->
  <div class="col-lg-6">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <span><i class="bi bi-clipboard2-check me-2 text-success"></i>Monitoring RKTL</span>
        <a href="rktl.php" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
      </div>
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead><tr><th>Alumni</th><th>Pelatihan</th><th>Progres</th><th>Status</th></tr></thead>
          <tbody>
          <?php while ($row = mysqli_fetch_assoc($q_rktl)): ?>
            <tr>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <div class="avatar-init" style="width:28px;height:28px;font-size:11px">
                    <?= strtoupper(substr($row['nama_alumni'],0,1)) ?>
                  </div>
                  <span style="font-size:12px;font-weight:600"><?= htmlspecialchars($row['nama_alumni']) ?></span>
                </div>
              </td>
              <td><small class="text-muted"><?= htmlspecialchars(substr($row['nama_pelatihan'],0,25)) ?>...</small></td>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <div class="progress flex-grow-1" style="height:6px">
                    <div class="progress-bar bg-success" style="width:<?= $row['progres'] ?>%"></div>
                  </div>
                  <small style="font-size:11px;font-weight:600;min-width:30px"><?= $row['progres'] ?>%</small>
                </div>
              </td>
              <td>
                <?php $rs=['selesai'=>['success','Selesai'],'berjalan'=>['info','Berjalan'],'terhambat'=>['danger','Terhambat'],'belum_mulai'=>['secondary','Belum']];
                      $rv=$rs[$row['status']]??['secondary','?']; ?>
                <span class="badge bg-<?= $rv[0] ?> bg-opacity-15 text-<?= $rv[0] ?>"><?= $rv[1] ?></span>
              </td>
            </tr>
          <?php endwhile; ?>
          <?php if (mysqli_num_rows($q_rktl)===0): ?>
            <tr><td colspan="4" class="text-center text-muted py-4">Belum ada data RKTL</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php if (!empty($chart_kerja)): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const chartLabels = <?= json_encode(array_map(function($r){ return str_replace(['_','-'],[' ',' '], $r['status_pekerjaan']??'Lainnya'); }, $chart_kerja)) ?>;
const chartData   = <?= json_encode(array_column($chart_kerja, 'jml')) ?>;
new Chart(document.getElementById('chartKerja'), {
  type: 'doughnut',
  data: {
    labels: chartLabels,
    datasets: [{ data: chartData, backgroundColor: ['#1d9e75','#1a4c8e','#f59e0b','#7c3aed'], borderWidth: 2, borderColor: '#fff' }]
  },
  options: { responsive: true, cutout: '65%', plugins: { legend: { position: 'bottom', labels: { font: { size: 11 }, padding: 10 } } } }
});
</script>
<?php endif; ?>

<?php include 'footer.php'; ?>
