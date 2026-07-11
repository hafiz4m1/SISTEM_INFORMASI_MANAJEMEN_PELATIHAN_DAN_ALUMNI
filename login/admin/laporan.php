<?php
$page_title = 'Laporan';
include 'header.php';

// Filter tanggal
$tgl_dari  = isset($_GET['dari'])  ? $_GET['dari']  : '2024-01-01';
$tgl_sampai = isset($_GET['sampai']) ? $_GET['sampai'] : date('Y-m-d');
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tgl_dari))   $tgl_dari  = '2024-01-01';
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tgl_sampai)) $tgl_sampai = date('Y-m-d');
$tab_aktif = isset($_GET['tab']) ? $_GET['tab'] : 'pelatihan';

// ============================================================
// DATA LAPORAN
// ============================================================

// 1. Pelatihan
$lap_pelatihan = mysqli_query($koneksi, "
    SELECT p.*, ui.name as nama_instruktur,
        COUNT(DISTINCT pp.user_id) as jml_peserta,
        SUM(pp.status_lulus='lulus') as jml_lulus,
        SUM(pp.status_lulus='tidak_lulus') as jml_tidak_lulus,
        SUM(pp.status_lulus='belum_dinilai') as jml_belum,
        AVG(pp.nilai) as rata_nilai
    FROM pelatihan p
    JOIN instruktur i ON p.instruktur_id=i.id
    JOIN users ui ON i.user_id=ui.id
    LEFT JOIN peserta_pelatihan pp ON pp.pelatihan_id=p.id
    WHERE p.tanggal_mulai BETWEEN '$tgl_dari' AND '$tgl_sampai'
    GROUP BY p.id
    ORDER BY p.tanggal_mulai DESC
");

// 2. Peserta
$lap_peserta = mysqli_query($koneksi, "
    SELECT pp.*, u.name as nama_peserta, u.email,
        p.nama_pelatihan, p.tanggal_mulai, p.tanggal_selesai,
        ui.name as nama_instruktur
    FROM peserta_pelatihan pp
    JOIN users u ON pp.user_id=u.id
    JOIN pelatihan p ON pp.pelatihan_id=p.id
    JOIN instruktur i ON p.instruktur_id=i.id
    JOIN users ui ON i.user_id=ui.id
    WHERE p.tanggal_mulai BETWEEN '$tgl_dari' AND '$tgl_sampai'
    ORDER BY p.tanggal_mulai DESC, u.name ASC
");

// 3. Alumni
$lap_alumni = mysqli_query($koneksi, "
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
    GROUP BY a.id
    ORDER BY u.name ASC
");

// 4. Tracer Study
$lap_tracer = mysqli_query($koneksi, "
    SELECT ts.*, u.name as nama_alumni,
        p_join.nama_pelatihan_pertama
    FROM tracer_study ts
    JOIN alumni a ON ts.alumni_id=a.id
    JOIN users u ON a.user_id=u.id
    LEFT JOIN (
        SELECT pp.user_id, p.nama_pelatihan as nama_pelatihan_pertama
        FROM peserta_pelatihan pp
        JOIN pelatihan p ON pp.pelatihan_id=p.id
        WHERE pp.status_lulus='lulus'
        ORDER BY p.tanggal_selesai ASC
    ) p_join ON p_join.user_id=a.user_id
    WHERE ts.status_pengisian='sudah_diisi'
    ORDER BY ts.tanggal_isi DESC
");

// Statistik tracer untuk grafik
$stat_kerja = [];
$q_stat = mysqli_query($koneksi, "SELECT status_pekerjaan, COUNT(*) as jml FROM tracer_study WHERE status_pengisian='sudah_diisi' GROUP BY status_pekerjaan");
while ($s = mysqli_fetch_assoc($q_stat)) $stat_kerja[$s['status_pekerjaan']] = $s['jml'];

// 5. RKTL
$lap_rktl = mysqli_query($koneksi, "
    SELECT r.*, u.name as nama_alumni, p.nama_pelatihan,
        ui.name as nama_instruktur
    FROM rktl r
    JOIN alumni a ON r.alumni_id=a.id
    JOIN users u ON a.user_id=u.id
    JOIN pelatihan p ON r.pelatihan_id=p.id
    JOIN instruktur i ON r.instruktur_id=i.id
    JOIN users ui ON i.user_id=ui.id
    ORDER BY r.tgl_pendampingan ASC
");

// 6. Rekomendasi
$lap_rek = mysqli_query($koneksi, "
    SELECT r.*, u.name as nama_alumni, p.nama_pelatihan, p.jenis,
        p.tanggal_mulai
    FROM rekomendasi r
    JOIN alumni a ON r.alumni_id=a.id
    JOIN users u ON a.user_id=u.id
    JOIN pelatihan p ON r.pelatihan_id=p.id
    ORDER BY r.skor DESC, u.name ASC
");

// 7. Kelulusan
$lap_lulus = mysqli_query($koneksi, "
    SELECT p.nama_pelatihan, p.jenis, p.tanggal_mulai, p.tanggal_selesai,
        ui.name as nama_instruktur,
        COUNT(DISTINCT pp.user_id) as total_peserta,
        SUM(pp.status_lulus='lulus') as lulus,
        SUM(pp.status_lulus='tidak_lulus') as tidak_lulus,
        SUM(pp.status_lulus='belum_dinilai') as belum_dinilai,
        ROUND(SUM(pp.status_lulus='lulus') / COUNT(DISTINCT pp.user_id) * 100, 1) as pct_lulus,
        AVG(pp.nilai) as rata_nilai,
        MAX(pp.nilai) as nilai_max,
        MIN(CASE WHEN pp.nilai IS NOT NULL THEN pp.nilai END) as nilai_min
    FROM pelatihan p
    JOIN instruktur i ON p.instruktur_id=i.id
    JOIN users ui ON i.user_id=ui.id
    LEFT JOIN peserta_pelatihan pp ON pp.pelatihan_id=p.id
    WHERE p.tanggal_mulai BETWEEN '$tgl_dari' AND '$tgl_sampai'
    GROUP BY p.id
    HAVING total_peserta > 0
    ORDER BY pct_lulus DESC
");
?>

<style>
  @media print {
    #sidebar, #topbar, .toolbar-laporan, .nav-tabs, .filter-form { display: none !important; }
    #main { margin-left: 0 !important; }
    .content-area { padding: 0 !important; }
    .tab-content > .tab-pane { display: block !important; opacity: 1 !important; }
    .tab-content > .tab-pane:not(.active-print) { display: none !important; }
    .card { box-shadow: none !important; border: 1px solid #ddd !important; }
    .print-header { display: block !important; }
  }
  .print-header { display: none; text-align: center; margin-bottom: 16px; }
  .print-header h4 { font-size: 16px; font-weight: 700; }
  .print-header p  { font-size: 12px; color: #666; }
  .tab-label { font-size: 13px; }
  .table-lap thead th { font-size: 11px; background: #f8f9fb; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; color: #6b7280; }
  .table-lap td { font-size: 12px; vertical-align: middle; }
  .progress-sm { height: 6px; border-radius: 3px; width: 60px; }
</style>

<!-- Filter & Toolbar -->
<div class="card mb-3 filter-form">
  <div class="card-body py-2 px-3 d-flex align-items-center flex-wrap gap-3">
    <form class="d-flex align-items-center gap-2 flex-wrap" method="GET">
      <input type="hidden" name="tab" value="<?= $tab_aktif ?>">
      <label class="fw-semibold" style="font-size:13px">Periode:</label>
      <input type="date" name="dari" class="form-control form-control-sm" value="<?= $tgl_dari ?>" style="width:140px">
      <span style="font-size:13px">s/d</span>
      <input type="date" name="sampai" class="form-control form-control-sm" value="<?= $tgl_sampai ?>" style="width:140px">
      <button class="btn btn-sm btn-primary">Filter</button>
      <a href="laporan.php?tab=<?= $tab_aktif ?>" class="btn btn-sm btn-outline-secondary">Reset</a>
    </form>
    <a href="laporan_cetak.php?jenis=<?= $tab_aktif ?>&dari=<?= $tgl_dari ?>&sampai=<?= $tgl_sampai ?>"
       target="_blank" class="btn btn-sm btn-success ms-auto">
      <i class="bi bi-printer me-1"></i> Print Laporan Formal
    </a>
  </div>
</div>

<!-- Tab Navigasi -->
<ul class="nav nav-tabs mb-0" id="tabLaporan">
  <?php
  $tabs = [
    'pelatihan'   => ['bi-journal-bookmark','Pelatihan'],
    'peserta'     => ['bi-people','Peserta'],
    'alumni'      => ['bi-mortarboard','Alumni'],
    'tracer'      => ['bi-clipboard-data','Tracer Study'],
    'rktl'        => ['bi-clipboard2-check','RKTL'],
    'rekomendasi' => ['bi-stars','Rekomendasi'],
    'kelulusan'   => ['bi-bar-chart','Kelulusan'],
  ];
  foreach ($tabs as $key => $val):
  ?>
  <li class="nav-item">
    <a class="nav-link <?= $tab_aktif===$key?'active':'' ?> tab-label"
       href="laporan.php?tab=<?= $key ?>&dari=<?= $tgl_dari ?>&sampai=<?= $tgl_sampai ?>">
      <i class="bi <?= $val[0] ?> me-1"></i><?= $val[1] ?>
    </a>
  </li>
  <?php endforeach; ?>
</ul>

<div class="card" style="border-radius: 0 0 12px 12px">
  <div class="card-body p-0">

    <!-- Print Header (hanya muncul saat print) -->
    <div class="print-header p-4">
      <h4>BPPMDDTT Banjarmasin</h4>
      <h5>Laporan <?= $tabs[$tab_aktif][1] ?></h5>
      <p>Periode: <?= date('d M Y', strtotime($tgl_dari)) ?> s/d <?= date('d M Y', strtotime($tgl_sampai)) ?></p>
      <p>Dicetak: <?= date('d M Y H:i') ?></p>
    </div>

    <!-- ===== TAB 1: PELATIHAN ===== -->
    <?php if ($tab_aktif === 'pelatihan'): ?>
    <div class="table-responsive">
      <table class="table table-lap table-hover mb-0">
        <thead><tr>
          <th>#</th><th>Nama Pelatihan</th><th>Jenis</th><th>Instruktur</th>
          <th>Tgl Mulai</th><th>Tgl Selesai</th><th>Peserta</th>
          <th>Lulus</th><th>Tidak Lulus</th><th>Rata Nilai</th><th>Status</th>
        </tr></thead>
        <tbody>
        <?php $no=1; while ($r = mysqli_fetch_assoc($lap_pelatihan)): ?>
          <tr>
            <td><?= $no++ ?></td>
            <td class="fw-semibold"><?= htmlspecialchars($r['nama_pelatihan']) ?></td>
            <td><?= htmlspecialchars($r['jenis']??'-') ?></td>
            <td><?= htmlspecialchars($r['nama_instruktur']) ?></td>
            <td><?= date('d M Y',strtotime($r['tanggal_mulai'])) ?></td>
            <td><?= date('d M Y',strtotime($r['tanggal_selesai'])) ?></td>
            <td><?= $r['jml_peserta'] ?></td>
            <td><span class="badge bg-success"><?= $r['jml_lulus'] ?></span></td>
            <td><span class="badge bg-danger"><?= $r['jml_tidak_lulus'] ?></span></td>
            <td><?= $r['rata_nilai'] ? number_format($r['rata_nilai'],1) : '-' ?></td>
            <td>
              <?php $sb=['aktif'=>'success','selesai'=>'secondary','dibatalkan'=>'danger']; ?>
              <span class="badge bg-<?= $sb[$r['status']]??'secondary' ?>"><?= ucfirst($r['status']) ?></span>
            </td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>

    <!-- ===== TAB 2: PESERTA ===== -->
    <?php elseif ($tab_aktif === 'peserta'): ?>
    <div class="table-responsive">
      <table class="table table-lap table-hover mb-0">
        <thead><tr>
          <th>#</th><th>Nama Peserta</th><th>Email</th><th>Pelatihan</th>
          <th>Instruktur</th><th>Tgl Mulai</th><th>Kehadiran</th><th>Nilai</th><th>Status</th>
        </tr></thead>
        <tbody>
        <?php $no=1; while ($r = mysqli_fetch_assoc($lap_peserta)): ?>
          <tr>
            <td><?= $no++ ?></td>
            <td class="fw-semibold"><?= htmlspecialchars($r['nama_peserta']) ?></td>
            <td><?= htmlspecialchars($r['email']) ?></td>
            <td><?= htmlspecialchars($r['nama_pelatihan']) ?></td>
            <td><?= htmlspecialchars($r['nama_instruktur']) ?></td>
            <td><?= date('d M Y',strtotime($r['tanggal_mulai'])) ?></td>
            <td>
              <?php $kh=['hadir'=>'success','tidak_hadir'=>'danger','izin'=>'warning']; ?>
              <span class="badge bg-<?= $kh[$r['status_kehadiran']]??'secondary' ?>"><?= str_replace('_',' ',$r['status_kehadiran']) ?></span>
            </td>
            <td><?= $r['nilai']??'-' ?></td>
            <td>
              <?php $sl=['lulus'=>'success','tidak_lulus'=>'danger','belum_dinilai'=>'secondary']; ?>
              <span class="badge bg-<?= $sl[$r['status_lulus']]??'secondary' ?>"><?= str_replace('_',' ',$r['status_lulus']) ?></span>
            </td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>

    <!-- ===== TAB 3: ALUMNI ===== -->
    <?php elseif ($tab_aktif === 'alumni'): ?>
    <div class="table-responsive">
      <table class="table table-lap table-hover mb-0">
        <thead><tr>
          <th>#</th><th>Nama Alumni</th><th>Email</th><th>Tgl Lulus</th>
          <th>Jml Pelatihan</th><th>Kompetensi</th><th>Status Pekerjaan</th><th>Perusahaan</th>
        </tr></thead>
        <tbody>
        <?php $no=1; while ($r = mysqli_fetch_assoc($lap_alumni)): ?>
          <tr>
            <td><?= $no++ ?></td>
            <td class="fw-semibold"><?= htmlspecialchars($r['name']) ?></td>
            <td><?= htmlspecialchars($r['email']) ?></td>
            <td><?= $r['tanggal_lulus'] ? date('d M Y',strtotime($r['tanggal_lulus'])) : '-' ?></td>
            <td><?= $r['jml_pelatihan'] ?></td>
            <td><small><?= htmlspecialchars($r['kompetensi']??'-') ?></small></td>
            <td>
              <?php if ($r['status_pekerjaan']): ?>
                <?php $sp=['bekerja'=>'success','wirausaha'=>'info','belum_bekerja'=>'warning','melanjutkan_studi'=>'primary']; ?>
                <span class="badge bg-<?= $sp[$r['status_pekerjaan']]??'secondary' ?>"><?= ucfirst(str_replace('_',' ',$r['status_pekerjaan'])) ?></span>
              <?php else: ?><span class="text-muted">-</span><?php endif; ?>
            </td>
            <td><?= htmlspecialchars($r['nama_perusahaan']??'-') ?></td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>

    <!-- ===== TAB 4: TRACER STUDY ===== -->
    <?php elseif ($tab_aktif === 'tracer'): ?>

    <!-- Grafik ringkasan -->
    <div class="p-3 border-bottom">
      <div class="row g-3 align-items-center">
        <?php
        $labels_kerja = ['bekerja'=>'Bekerja','wirausaha'=>'Wirausaha/Usaha Desa','belum_bekerja'=>'Belum Bekerja','melanjutkan_studi'=>'Lanjut Studi'];
        $colors_kerja = ['bekerja'=>'#1d9e75','wirausaha'=>'#1a4c8e','belum_bekerja'=>'#ffc107','melanjutkan_studi'=>'#6f42c1'];
        foreach ($labels_kerja as $k=>$v):
          $jml = $stat_kerja[$k] ?? 0;
          $total_ts = array_sum($stat_kerja);
          $pct = $total_ts > 0 ? round($jml/$total_ts*100) : 0;
        ?>
        <div class="col-6 col-md-3">
          <div class="text-center p-2 rounded" style="background:#f8f9fb">
            <div style="font-size:22px;font-weight:700;color:<?= $colors_kerja[$k] ?>"><?= $jml ?></div>
            <div style="font-size:11px;color:#6b7280"><?= $v ?></div>
            <div style="font-size:11px;color:#9ca3af"><?= $pct ?>%</div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table table-lap table-hover mb-0">
        <thead><tr>
          <th>#</th><th>Alumni</th><th>Status Pekerjaan</th><th>Instansi/Usaha/Desa</th>
          <th>Jabatan/Peran</th><th>Penghasilan</th><th>Relevansi</th><th>Waktu Tunggu</th><th>Tgl Isi</th>
        </tr></thead>
        <tbody>
        <?php $no=1; while ($r = mysqli_fetch_assoc($lap_tracer)): ?>
          <tr>
            <td><?= $no++ ?></td>
            <td class="fw-semibold"><?= htmlspecialchars($r['nama_alumni']) ?></td>
            <td>
              <?php $sp=['bekerja'=>'success','wirausaha'=>'info','belum_bekerja'=>'warning','melanjutkan_studi'=>'primary']; ?>
              <span class="badge bg-<?= $sp[$r['status_pekerjaan']]??'secondary' ?>"><?= str_replace(['bekerja','wirausaha','belum_bekerja','melanjutkan_studi'],['Bekerja','Wirausaha/Usaha Desa','Belum Bekerja','Lanjut Studi'], $r['status_pekerjaan']??'-') ?></span>
            </td>
            <td><?= htmlspecialchars($r['nama_perusahaan']??'-') ?></td>
            <td><?= htmlspecialchars($r['jabatan']??'-') ?></td>
            <td><?= htmlspecialchars($r['gaji_range']??'-') ?></td>
            <td>
              <?php for ($i=1;$i<=5;$i++) echo '<i class="bi bi-star'.($i<=($r['relevansi_pelatihan']??0)?'-fill text-warning':' text-muted').'" style="font-size:11px"></i>'; ?>
              <small><?= $r['relevansi_pelatihan']??'-' ?>/5</small>
            </td>
            <td><?= ($r['waktu_tunggu_kerja']??null) !== null ? $r['waktu_tunggu_kerja'].' bln' : '-' ?></td>
            <td><?= $r['tanggal_isi'] ? date('d M Y',strtotime($r['tanggal_isi'])) : '-' ?></td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>

    <!-- ===== TAB 5: RKTL ===== -->
    <?php elseif ($tab_aktif === 'rktl'): ?>
    <div class="table-responsive">
      <table class="table table-lap table-hover mb-0">
        <thead><tr>
          <th>#</th><th>Alumni</th><th>Pelatihan</th><th>Instruktur</th>
          <th>Tgl Pendampingan</th><th>Progres</th><th>Status</th><th>Catatan</th>
        </tr></thead>
        <tbody>
        <?php $no=1; while ($r = mysqli_fetch_assoc($lap_rktl)): ?>
          <?php $lewat = $r['tgl_pendampingan'] && strtotime($r['tgl_pendampingan']) < time() && $r['status'] !== 'selesai'; ?>
          <tr class="<?= $lewat?'table-warning':'' ?>">
            <td><?= $no++ ?></td>
            <td class="fw-semibold"><?= htmlspecialchars($r['nama_alumni']) ?></td>
            <td><?= htmlspecialchars($r['nama_pelatihan']) ?></td>
            <td><?= htmlspecialchars($r['nama_instruktur']) ?></td>
            <td><?= $r['tgl_pendampingan'] ? date('d M Y',strtotime($r['tgl_pendampingan'])) : '-' ?></td>
            <td>
              <div class="progress progress-sm">
                <div class="progress-bar <?= $r['progres']>=100?'bg-success':($r['progres']>=50?'bg-primary':'bg-warning') ?>"
                     style="width:<?= $r['progres'] ?>%"></div>
              </div>
              <small><?= $r['progres'] ?>%</small>
            </td>
            <td>
              <?php $sb=['belum_mulai'=>'secondary','berjalan'=>'primary','selesai'=>'success','terhambat'=>'danger']; ?>
              <?php $sl=['belum_mulai'=>'Belum Mulai','berjalan'=>'Berjalan','selesai'=>'Selesai','terhambat'=>'Terhambat']; ?>
              <span class="badge bg-<?= $sb[$r['status']]??'secondary' ?>"><?= $sl[$r['status']]??'-' ?></span>
            </td>
            <td><small class="text-muted"><?= $r['catatan'] ? htmlspecialchars(substr($r['catatan'],0,50)).'...' : '-' ?></small></td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>

    <!-- ===== TAB 6: REKOMENDASI ===== -->
    <?php elseif ($tab_aktif === 'rekomendasi'): ?>
    <div class="table-responsive">
      <table class="table table-lap table-hover mb-0">
        <thead><tr>
          <th>#</th><th>Alumni</th><th>Pelatihan Direkomendasikan</th>
          <th>Kategori</th><th>Tgl Mulai</th><th>Skor</th><th>Alasan</th><th>Dilihat</th>
        </tr></thead>
        <tbody>
        <?php $no=1; while ($r = mysqli_fetch_assoc($lap_rek)): ?>
          <tr>
            <td><?= $no++ ?></td>
            <td class="fw-semibold"><?= htmlspecialchars($r['nama_alumni']) ?></td>
            <td><?= htmlspecialchars($r['nama_pelatihan']) ?></td>
            <td><span class="badge bg-primary bg-opacity-10 text-primary" style="font-size:11px"><?= htmlspecialchars($r['jenis']??'-') ?></span></td>
            <td><?= date('d M Y',strtotime($r['tanggal_mulai'])) ?></td>
            <td><span class="badge bg-primary"><?= $r['skor'] ?>%</span></td>
            <td><small class="text-muted"><?= htmlspecialchars(substr($r['alasan']??'',0,60)) ?>...</small></td>
            <td><?= $r['is_dilihat'] ? '<span class="badge bg-success">Ya</span>' : '<span class="badge bg-secondary">Belum</span>' ?></td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>

    <!-- ===== TAB 7: KELULUSAN ===== -->
    <?php elseif ($tab_aktif === 'kelulusan'): ?>
    <div class="table-responsive">
      <table class="table table-lap table-hover mb-0">
        <thead><tr>
          <th>#</th><th>Nama Pelatihan</th><th>Instruktur</th><th>Tgl Selesai</th>
          <th>Total Peserta</th><th>Lulus</th><th>Tidak Lulus</th>
          <th>% Lulus</th><th>Rata Nilai</th><th>Nilai Max</th><th>Nilai Min</th>
        </tr></thead>
        <tbody>
        <?php $no=1; while ($r = mysqli_fetch_assoc($lap_lulus)): ?>
          <tr>
            <td><?= $no++ ?></td>
            <td class="fw-semibold"><?= htmlspecialchars($r['nama_pelatihan']) ?></td>
            <td><?= htmlspecialchars($r['nama_instruktur']) ?></td>
            <td><?= date('d M Y',strtotime($r['tanggal_selesai'])) ?></td>
            <td><?= $r['total_peserta'] ?></td>
            <td><span class="badge bg-success"><?= $r['lulus'] ?></span></td>
            <td><span class="badge bg-danger"><?= $r['tidak_lulus'] ?></span></td>
            <td>
              <div class="progress progress-sm mb-1">
                <div class="progress-bar bg-success" style="width:<?= $r['pct_lulus'] ?>%"></div>
              </div>
              <small><?= $r['pct_lulus'] ?>%</small>
            </td>
            <td><?= $r['rata_nilai'] ? number_format($r['rata_nilai'],1) : '-' ?></td>
            <td><?= $r['nilai_max']??'-' ?></td>
            <td><?= $r['nilai_min']??'-' ?></td>
          </tr>
        <?php endwhile; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>

  </div>
</div>

<script>
function cetakLaporan() {
  // Tambahkan class active-print ke tab aktif
  document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active-print'));
  const aktif = document.querySelector('.tab-pane.show.active');
  if (aktif) aktif.classList.add('active-print');
  window.print();
}
</script>

<?php include 'footer.php'; ?>