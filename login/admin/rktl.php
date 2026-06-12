<?php
$page_title = 'Monitoring RKTL';
include '../koneksi.php';
include 'header.php';

// Statistik
$stat = mysqli_fetch_assoc(mysqli_query($koneksi, "
    SELECT
        COUNT(*) as total,
        SUM(status='belum_mulai') as belum,
        SUM(status='berjalan') as berjalan,
        SUM(status='selesai') as selesai,
        SUM(status='terhambat') as terhambat,
        AVG(progres) as avg_progres
    FROM rktl
"));

$filter = isset($_GET['filter']) ? $_GET['filter'] : 'semua';
$where  = '';
if ($filter === 'belum')     $where = "WHERE r.status='belum_mulai'";
if ($filter === 'berjalan')  $where = "WHERE r.status='berjalan'";
if ($filter === 'selesai')   $where = "WHERE r.status='selesai'";
if ($filter === 'terhambat') $where = "WHERE r.status='terhambat'";

$data = mysqli_query($koneksi, "
    SELECT r.*, u.name as nama_alumni, p.nama_pelatihan,
        ui.name as nama_instruktur
    FROM rktl r
    JOIN alumni a ON r.alumni_id = a.id
    JOIN users u ON a.user_id = u.id
    JOIN pelatihan p ON r.pelatihan_id = p.id
    JOIN instruktur i ON r.instruktur_id = i.id
    JOIN users ui ON i.user_id = ui.id
    $where
    ORDER BY r.tgl_pendampingan ASC
");
?>

<!-- Stat -->
<div class="row g-3 mb-4">
  <div class="col-sm-4 col-lg-2">
    <div class="stat-card bg-white shadow-sm">
      <div class="icon bg-primary bg-opacity-10 text-primary"><i class="bi bi-list-check"></i></div>
      <div><div class="val text-primary"><?= $stat['total'] ?></div><div class="lbl">Total RKTL</div></div>
    </div>
  </div>
  <div class="col-sm-4 col-lg-2">
    <div class="stat-card bg-white shadow-sm">
      <div class="icon bg-secondary bg-opacity-10 text-secondary"><i class="bi bi-hourglass"></i></div>
      <div><div class="val"><?= $stat['belum'] ?></div><div class="lbl">Belum Mulai</div></div>
    </div>
  </div>
  <div class="col-sm-4 col-lg-2">
    <div class="stat-card bg-white shadow-sm">
      <div class="icon bg-primary bg-opacity-10 text-primary"><i class="bi bi-arrow-repeat"></i></div>
      <div><div class="val text-primary"><?= $stat['berjalan'] ?></div><div class="lbl">Berjalan</div></div>
    </div>
  </div>
  <div class="col-sm-4 col-lg-2">
    <div class="stat-card bg-white shadow-sm">
      <div class="icon bg-success bg-opacity-10 text-success"><i class="bi bi-check-circle"></i></div>
      <div><div class="val text-success"><?= $stat['selesai'] ?></div><div class="lbl">Selesai</div></div>
    </div>
  </div>
  <div class="col-sm-4 col-lg-2">
    <div class="stat-card bg-white shadow-sm">
      <div class="icon bg-danger bg-opacity-10 text-danger"><i class="bi bi-x-circle"></i></div>
      <div><div class="val text-danger"><?= $stat['terhambat'] ?></div><div class="lbl">Terhambat</div></div>
    </div>
  </div>
  <div class="col-sm-4 col-lg-2">
    <div class="stat-card bg-white shadow-sm">
      <div class="icon bg-info bg-opacity-10 text-info"><i class="bi bi-bar-chart"></i></div>
      <div><div class="val text-info"><?= round($stat['avg_progres'] ?? 0) ?>%</div><div class="lbl">Rata Progres</div></div>
    </div>
  </div>
</div>

<!-- Filter -->
<ul class="nav nav-tabs mb-3">
  <?php foreach (['semua'=>'Semua','belum'=>'Belum Mulai','berjalan'=>'Berjalan','terhambat'=>'Terhambat','selesai'=>'Selesai'] as $k=>$v): ?>
    <li class="nav-item">
      <a class="nav-link <?= $filter===$k?'active':'' ?>" href="rktl.php?filter=<?= $k ?>"><?= $v ?></a>
    </li>
  <?php endforeach; ?>
</ul>

<div class="card">
  <div class="card-header">Monitoring RKTL Alumni</div>
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead>
        <tr><th>#</th><th>Alumni</th><th>Pelatihan</th><th>Instruktur</th><th>Tgl Pendampingan</th><th>Progres</th><th>Status</th><th>Catatan</th></tr>
      </thead>
      <tbody>
      <?php $no=1; while ($row = mysqli_fetch_assoc($data)): ?>
        <?php $lewat = $row['tgl_pendampingan'] && strtotime($row['tgl_pendampingan']) < time() && $row['status'] !== 'selesai'; ?>
        <tr class="<?= $lewat?'table-warning':'' ?>">
          <td><?= $no++ ?></td>
          <td><?= htmlspecialchars($row['nama_alumni']) ?></td>
          <td><small><?= htmlspecialchars($row['nama_pelatihan']) ?></small></td>
          <td><small><?= htmlspecialchars($row['nama_instruktur']) ?></small></td>
          <td>
            <?= $row['tgl_pendampingan'] ? date('d M Y', strtotime($row['tgl_pendampingan'])) : '-' ?>
            <?php if ($lewat): ?><br><small class="text-danger">Jatuh tempo</small><?php endif; ?>
          </td>
          <td>
            <div class="progress" style="height:8px;width:80px;border-radius:4px">
              <div class="progress-bar <?= $row['progres']>=100?'bg-success':($row['progres']>=50?'bg-primary':'bg-warning') ?>"
                   style="width:<?= $row['progres'] ?>%"></div>
            </div>
            <small class="text-muted"><?= $row['progres'] ?>%</small>
          </td>
          <td>
            <?php
            $sb=['belum_mulai'=>'secondary','berjalan'=>'primary','selesai'=>'success','terhambat'=>'danger'];
            $sl=['belum_mulai'=>'Belum Mulai','berjalan'=>'Berjalan','selesai'=>'Selesai','terhambat'=>'Terhambat'];
            ?>
            <span class="badge bg-<?= $sb[$row['status']]??'secondary' ?>"><?= $sl[$row['status']]??'-' ?></span>
          </td>
          <td><small class="text-muted"><?= $row['catatan'] ? htmlspecialchars(substr($row['catatan'],0,60)).'...' : '-' ?></small></td>
        </tr>
      <?php endwhile; ?>
      <?php if (mysqli_num_rows($data) === 0): ?>
        <tr><td colspan="8" class="text-center text-muted py-4">Belum ada data RKTL</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include 'footer.php'; ?>