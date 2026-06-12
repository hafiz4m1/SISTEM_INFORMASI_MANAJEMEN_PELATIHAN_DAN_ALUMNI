<?php
$page_title = 'Laporan';
include 'header.php';

$tgl_dari   = isset($_GET['dari'])   ? $_GET['dari']   : date('Y-01-01');
$tgl_sampai = isset($_GET['sampai']) ? $_GET['sampai'] : date('Y-m-d');
$jenis      = isset($_GET['jenis'])  ? $_GET['jenis']  : 'pelatihan';

$tabs = [
    'pelatihan'  => ['bi-journal-bookmark', 'Pelatihan'],
    'peserta'    => ['bi-people',           'Peserta'],
    'alumni'     => ['bi-mortarboard',      'Alumni'],
    'tracer'     => ['bi-clipboard-data',   'Tracer Study'],
    'rktl'       => ['bi-clipboard2-check', 'RKTL'],
    'kelulusan'  => ['bi-bar-chart',        'Kelulusan'],
];
?>

<!-- Filter -->
<div class="card mb-3">
  <div class="card-body py-2 px-3 d-flex align-items-center flex-wrap gap-3">
    <form class="d-flex align-items-center gap-2 flex-wrap" method="GET">
      <input type="hidden" name="jenis" value="<?= $jenis ?>">
      <label class="fw-semibold" style="font-size:13px">Periode:</label>
      <input type="date" name="dari"   class="form-control form-control-sm" value="<?= $tgl_dari ?>"   style="width:140px">
      <span style="font-size:13px">s/d</span>
      <input type="date" name="sampai" class="form-control form-control-sm" value="<?= $tgl_sampai ?>" style="width:140px">
      <button class="btn btn-sm btn-primary">Filter</button>
      <a href="laporan.php?jenis=<?= $jenis ?>" class="btn btn-sm btn-outline-secondary">Reset</a>
    </form>
    <a href="../admin/laporan_cetak.php?jenis=<?= $jenis ?>&dari=<?= $tgl_dari ?>&sampai=<?= $tgl_sampai ?>"
       target="_blank" class="btn btn-sm btn-success ms-auto">
      <i class="bi bi-printer me-1"></i> Cetak Laporan
    </a>
  </div>
</div>

<!-- Tab -->
<ul class="nav nav-tabs mb-0">
  <?php foreach ($tabs as $k => $v): ?>
  <li class="nav-item">
    <a class="nav-link <?= $jenis===$k?'active':'' ?>"
       href="laporan.php?jenis=<?= $k ?>&dari=<?= $tgl_dari ?>&sampai=<?= $tgl_sampai ?>">
      <i class="bi <?= $v[0] ?> me-1"></i><?= $v[1] ?>
    </a>
  </li>
  <?php endforeach; ?>
</ul>

<div class="card" style="border-radius:0 0 12px 12px">
  <div class="table-responsive">
  <?php

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
          GROUP BY p.id ORDER BY p.tanggal_mulai DESC
      ");
      echo '<table class="table table-hover mb-0">
        <thead><tr><th>#</th><th>Nama Pelatihan</th><th>Instruktur</th><th>Tgl Mulai</th><th>Tgl Selesai</th><th>Peserta</th><th>Lulus</th><th>Tidak Lulus</th><th>Rata Nilai</th><th>Status</th></tr></thead><tbody>';
      $no=1; while ($r = mysqli_fetch_assoc($data)):
        $sb=['aktif'=>'success','selesai'=>'secondary','dibatalkan'=>'danger'];
        echo "<tr><td>$no</td><td><strong>".htmlspecialchars($r['nama_pelatihan'])."</strong></td>
          <td>".htmlspecialchars($r['nama_instruktur'])."</td>
          <td>".date('d M Y',strtotime($r['tanggal_mulai']))."</td>
          <td>".date('d M Y',strtotime($r['tanggal_selesai']))."</td>
          <td>".$r['jml_peserta']."</td>
          <td><span class='badge bg-success'>".$r['jml_lulus']."</span></td>
          <td><span class='badge bg-danger'>".$r['jml_tidak_lulus']."</span></td>
          <td>".($r['rata_nilai']??'-')."</td>
          <td><span class='badge bg-".($sb[$r['status']]??'secondary')."'>".ucfirst($r['status'])."</span></td></tr>";
        $no++;
      endwhile;
      echo '</tbody></table>';

  } elseif ($jenis === 'alumni') {
      $data = mysqli_query($koneksi, "
          SELECT a.*, u.name, u.email,
              COUNT(DISTINCT pp.pelatihan_id) as jml_pelatihan,
              ts.status_pekerjaan, ts.nama_perusahaan, ts.jabatan
          FROM alumni a JOIN users u ON a.user_id=u.id
          LEFT JOIN peserta_pelatihan pp ON pp.user_id=a.user_id
          LEFT JOIN tracer_study ts ON ts.alumni_id=a.id AND ts.status_pengisian='sudah_diisi'
          GROUP BY a.id ORDER BY u.name ASC
      ");
      echo '<table class="table table-hover mb-0">
        <thead><tr><th>#</th><th>Nama Alumni</th><th>Tgl Lulus</th><th>Pelatihan</th><th>Status Kerja</th><th>Perusahaan</th></tr></thead><tbody>';
      $no=1; while ($r = mysqli_fetch_assoc($data)):
        $sp=['bekerja'=>'success','wirausaha'=>'info','belum_bekerja'=>'warning','melanjutkan_studi'=>'primary'];
        echo "<tr><td>$no</td><td><strong>".htmlspecialchars($r['name'])."</strong></td>
          <td>".($r['tanggal_lulus']?date('d M Y',strtotime($r['tanggal_lulus'])):'-')."</td>
          <td>".$r['jml_pelatihan']."</td>
          <td>".($r['status_pekerjaan']?"<span class='badge bg-".($sp[$r['status_pekerjaan']]??'secondary')."'>".ucfirst(str_replace('_',' ',$r['status_pekerjaan']))."</span>":'-')."</td>
          <td>".htmlspecialchars($r['nama_perusahaan']??'-')."</td></tr>";
        $no++;
      endwhile;
      echo '</tbody></table>';

  } elseif ($jenis === 'tracer') {
      $data = mysqli_query($koneksi, "
          SELECT ts.*, u.name as nama_alumni
          FROM tracer_study ts JOIN alumni a ON ts.alumni_id=a.id JOIN users u ON a.user_id=u.id
          WHERE ts.status_pengisian='sudah_diisi' ORDER BY ts.tanggal_isi DESC
      ");
      echo '<table class="table table-hover mb-0">
        <thead><tr><th>#</th><th>Alumni</th><th>Status Kerja</th><th>Perusahaan</th><th>Jabatan</th><th>Relevansi</th><th>Waktu Tunggu</th><th>Tgl Isi</th></tr></thead><tbody>';
      $no=1; $sp=['bekerja'=>'success','wirausaha'=>'info','belum_bekerja'=>'warning','melanjutkan_studi'=>'primary'];
      while ($r = mysqli_fetch_assoc($data)):
        echo "<tr><td>$no</td><td><strong>".htmlspecialchars($r['nama_alumni'])."</strong></td>
          <td><span class='badge bg-".($sp[$r['status_pekerjaan']]??'secondary')."'>".ucfirst(str_replace('_',' ',$r['status_pekerjaan']??'-'))."</span></td>
          <td>".htmlspecialchars($r['nama_perusahaan']??'-')."</td>
          <td>".htmlspecialchars($r['jabatan']??'-')."</td>
          <td>".($r['relevansi_pelatihan']??'-')."/5</td>
          <td>".($r['waktu_tunggu_kerja']!==null?$r['waktu_tunggu_kerja'].' bln':'-')."</td>
          <td>".($r['tanggal_isi']?date('d M Y',strtotime($r['tanggal_isi'])):'-')."</td></tr>";
        $no++;
      endwhile;
      echo '</tbody></table>';

  } elseif ($jenis === 'kelulusan') {
      $data = mysqli_query($koneksi, "
          SELECT p.nama_pelatihan, p.tanggal_mulai, p.tanggal_selesai,
              ui.name as nama_instruktur,
              COUNT(DISTINCT pp.user_id) as total_peserta,
              SUM(pp.status_lulus='lulus') as lulus,
              SUM(pp.status_lulus='tidak_lulus') as tidak_lulus,
              ROUND(SUM(pp.status_lulus='lulus')/COUNT(DISTINCT pp.user_id)*100,1) as pct_lulus,
              ROUND(AVG(pp.nilai),1) as rata_nilai
          FROM pelatihan p
          JOIN instruktur i ON p.instruktur_id=i.id
          JOIN users ui ON i.user_id=ui.id
          LEFT JOIN peserta_pelatihan pp ON pp.pelatihan_id=p.id
          WHERE p.tanggal_mulai BETWEEN '$tgl_dari' AND '$tgl_sampai'
          GROUP BY p.id HAVING total_peserta > 0
          ORDER BY pct_lulus DESC
      ");
      echo '<table class="table table-hover mb-0">
        <thead><tr><th>#</th><th>Pelatihan</th><th>Instruktur</th><th>Tgl Selesai</th><th>Total</th><th>Lulus</th><th>Tdk Lulus</th><th>% Lulus</th><th>Rata Nilai</th></tr></thead><tbody>';
      $no=1; while ($r = mysqli_fetch_assoc($data)):
        echo "<tr><td>$no</td><td><strong>".htmlspecialchars($r['nama_pelatihan'])."</strong></td>
          <td>".htmlspecialchars($r['nama_instruktur'])."</td>
          <td>".date('d M Y',strtotime($r['tanggal_selesai']))."</td>
          <td>".$r['total_peserta']."</td>
          <td><span class='badge bg-success'>".$r['lulus']."</span></td>
          <td><span class='badge bg-danger'>".$r['tidak_lulus']."</span></td>
          <td>".$r['pct_lulus']."%</td>
          <td>".($r['rata_nilai']??'-')."</td></tr>";
        $no++;
      endwhile;
      echo '</tbody></table>';

  } elseif ($jenis === 'rktl') {
      $data = mysqli_query($koneksi, "
          SELECT r.*, u.name as nama_alumni, p.nama_pelatihan, ui.name as nama_instruktur
          FROM rktl r JOIN alumni a ON r.alumni_id=a.id JOIN users u ON a.user_id=u.id
          JOIN pelatihan p ON r.pelatihan_id=p.id JOIN instruktur i ON r.instruktur_id=i.id
          JOIN users ui ON i.user_id=ui.id ORDER BY r.tgl_pendampingan ASC
      ");
      echo '<table class="table table-hover mb-0">
        <thead><tr><th>#</th><th>Alumni</th><th>Pelatihan</th><th>Instruktur</th><th>Tgl Pendampingan</th><th>Progres</th><th>Status</th></tr></thead><tbody>';
      $no=1; $sb=['belum_mulai'=>'secondary','berjalan'=>'primary','selesai'=>'success','terhambat'=>'danger'];
      $sl=['belum_mulai'=>'Belum Mulai','berjalan'=>'Berjalan','selesai'=>'Selesai','terhambat'=>'Terhambat'];
      while ($r = mysqli_fetch_assoc($data)):
        echo "<tr><td>$no</td><td><strong>".htmlspecialchars($r['nama_alumni'])."</strong></td>
          <td>".htmlspecialchars($r['nama_pelatihan'])."</td>
          <td>".htmlspecialchars($r['nama_instruktur'])."</td>
          <td>".($r['tgl_pendampingan']?date('d M Y',strtotime($r['tgl_pendampingan'])):'-')."</td>
          <td><div class='progress' style='height:6px;width:60px;border-radius:3px'>
            <div class='progress-bar bg-".($r['progres']>=100?'success':($r['progres']>=50?'primary':'warning'))."' style='width:".$r['progres']."%'></div>
          </div><small>".$r['progres']."%</small></td>
          <td><span class='badge bg-".($sb[$r['status']]??'secondary')."'>".($sl[$r['status']]??'-')."</span></td></tr>";
        $no++;
      endwhile;
      echo '</tbody></table>';

  } elseif ($jenis === 'peserta') {
      $data = mysqli_query($koneksi, "
          SELECT pp.*, u.name as nama_peserta, p.nama_pelatihan, p.tanggal_mulai
          FROM peserta_pelatihan pp JOIN users u ON pp.user_id=u.id
          JOIN pelatihan p ON pp.pelatihan_id=p.id
          WHERE p.tanggal_mulai BETWEEN '$tgl_dari' AND '$tgl_sampai'
          ORDER BY p.tanggal_mulai DESC, u.name ASC
      ");
      echo '<table class="table table-hover mb-0">
        <thead><tr><th>#</th><th>Nama Peserta</th><th>Pelatihan</th><th>Tgl Mulai</th><th>Kehadiran</th><th>Nilai</th><th>Status</th></tr></thead><tbody>';
      $no=1; $kh=['hadir'=>'success','tidak_hadir'=>'danger','izin'=>'warning'];
      $sl=['lulus'=>'success','tidak_lulus'=>'danger','belum_dinilai'=>'secondary'];
      while ($r = mysqli_fetch_assoc($data)):
        echo "<tr><td>$no</td><td><strong>".htmlspecialchars($r['nama_peserta'])."</strong></td>
          <td>".htmlspecialchars($r['nama_pelatihan'])."</td>
          <td>".date('d M Y',strtotime($r['tanggal_mulai']))."</td>
          <td><span class='badge bg-".($kh[$r['status_kehadiran']]??'secondary')."'>".str_replace('_',' ',ucfirst($r['status_kehadiran']))."</span></td>
          <td>".($r['nilai']??'-')."</td>
          <td><span class='badge bg-".($sl[$r['status_lulus']]??'secondary')."'>".str_replace('_',' ',ucfirst($r['status_lulus']))."</span></td></tr>";
        $no++;
      endwhile;
      echo '</tbody></table>';
  }
  ?>
  </div>
</div>

<?php include 'footer.php'; ?>
