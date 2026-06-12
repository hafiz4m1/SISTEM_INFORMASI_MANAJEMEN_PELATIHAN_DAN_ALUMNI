<?php
$page_title = 'Tracer Study';
include '../koneksi.php';

// Kirim tracer ke semua alumni yang belum pernah menerima SEBELUM include header
if (isset($_GET['kirim_semua'])) {
    $alumni_list = mysqli_query($koneksi, "SELECT id FROM alumni");
    $kirim = 0;
    while ($a = mysqli_fetch_assoc($alumni_list)) {
        $cek = mysqli_fetch_row(mysqli_query($koneksi, "SELECT COUNT(*) FROM tracer_study WHERE alumni_id={$a['id']}"));
        if ($cek[0] == 0) {
            mysqli_query($koneksi, "INSERT INTO tracer_study (alumni_id, status_pengisian) VALUES ({$a['id']}, 'belum_diisi')");
            $kirim++;
        }
    }
    header("Location: tracer.php?pesan=Tracer study berhasil dikirim ke $kirim alumni.");
    exit;
}

include 'header.php';

$pesan = isset($_GET['pesan']) ? $_GET['pesan'] : '';

// Statistik
$stat = mysqli_fetch_assoc(mysqli_query($koneksi, "
    SELECT
        COUNT(*) as total,
        SUM(status_pengisian='sudah_diisi') as terisi,
        SUM(status_pekerjaan='bekerja') as bekerja,
        SUM(status_pekerjaan='wirausaha') as wirausaha,
        SUM(status_pekerjaan='belum_bekerja') as belum_kerja,
        SUM(status_pekerjaan='melanjutkan_studi') as studi,
        AVG(relevansi_pelatihan) as avg_relevansi
    FROM tracer_study
"));

$data = mysqli_query($koneksi, "
    SELECT ts.*, u.name as nama_alumni
    FROM tracer_study ts
    JOIN alumni a ON ts.alumni_id = a.id
    JOIN users u ON a.user_id = u.id
    ORDER BY ts.tanggal_kirim DESC
");
?>

<?php if ($pesan): ?>
  <div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($pesan) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<!-- Stat -->
<div class="row g-3 mb-4">
  <div class="col-sm-4 col-lg-2">
    <div class="stat-card bg-white shadow-sm">
      <div class="icon bg-primary bg-opacity-10 text-primary"><i class="bi bi-send"></i></div>
      <div><div class="val text-primary"><?= $stat['total'] ?></div><div class="lbl">Terkirim</div></div>
    </div>
  </div>
  <div class="col-sm-4 col-lg-2">
    <div class="stat-card bg-white shadow-sm">
      <div class="icon bg-success bg-opacity-10 text-success"><i class="bi bi-check2-circle"></i></div>
      <div><div class="val text-success"><?= $stat['terisi'] ?></div><div class="lbl">Terisi</div></div>
    </div>
  </div>
  <div class="col-sm-4 col-lg-2">
    <div class="stat-card bg-white shadow-sm">
      <div class="icon bg-info bg-opacity-10 text-info"><i class="bi bi-briefcase"></i></div>
      <div><div class="val text-info"><?= $stat['bekerja'] ?></div><div class="lbl">Bekerja</div></div>
    </div>
  </div>
  <div class="col-sm-4 col-lg-2">
    <div class="stat-card bg-white shadow-sm">
      <div class="icon bg-warning bg-opacity-10 text-warning"><i class="bi bi-shop"></i></div>
      <div><div class="val text-warning"><?= $stat['wirausaha'] ?></div><div class="lbl">Wirausaha</div></div>
    </div>
  </div>
  <div class="col-sm-4 col-lg-2">
    <div class="stat-card bg-white shadow-sm">
      <div class="icon bg-danger bg-opacity-10 text-danger"><i class="bi bi-hourglass-split"></i></div>
      <div><div class="val text-danger"><?= $stat['belum_kerja'] ?></div><div class="lbl">Belum Kerja</div></div>
    </div>
  </div>
  <div class="col-sm-4 col-lg-2">
    <div class="stat-card bg-white shadow-sm">
      <div class="icon bg-secondary bg-opacity-10 text-secondary"><i class="bi bi-star-half"></i></div>
      <div><div class="val"><?= number_format($stat['avg_relevansi'] ?? 0, 1) ?></div><div class="lbl">Avg Relevansi</div></div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <span>Data Tracer Study</span>
    <a href="tracer.php?kirim_semua=1" class="btn btn-sm btn-primary"
       onclick="return confirm('Kirim tracer study ke semua alumni yang belum menerima?')">
      <i class="bi bi-send"></i> Kirim ke Alumni
    </a>
  </div>
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead><tr><th>#</th><th>Alumni</th><th>Tgl Kirim</th><th>Status</th><th>Pekerjaan</th><th>Perusahaan</th><th>Relevansi</th></tr></thead>
      <tbody>
      <?php $no = 1; while ($row = mysqli_fetch_assoc($data)): ?>
        <tr>
          <td><?= $no++ ?></td>
          <td><?= htmlspecialchars($row['nama_alumni']) ?></td>
          <td><?= date('d M Y', strtotime($row['tanggal_kirim'])) ?></td>
          <td>
            <?php $sb=['sudah_diisi'=>'success','belum_diisi'=>'secondary','terkirim'=>'warning']; ?>
            <span class="badge bg-<?= $sb[$row['status_pengisian']] ?? 'secondary' ?>"><?= str_replace('_',' ', $row['status_pengisian']) ?></span>
          </td>
          <td><?= $row['status_pekerjaan'] ? ucfirst(str_replace('_',' ',$row['status_pekerjaan'])) : '-' ?></td>
          <td><?= htmlspecialchars($row['nama_perusahaan'] ?? '-') ?></td>
          <td><?= $row['relevansi_pelatihan'] ? $row['relevansi_pelatihan'].'/5' : '-' ?></td>
        </tr>
      <?php endwhile; ?>
      <?php if (mysqli_num_rows($data) === 0): ?>
        <tr><td colspan="7" class="text-center text-muted py-4">Belum ada data tracer study</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include 'footer.php'; ?>
