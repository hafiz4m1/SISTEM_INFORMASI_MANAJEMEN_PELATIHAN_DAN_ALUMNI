<?php
$page_title = 'Dashboard';
include '../koneksi.php';
include_once '../notif.php';
include 'header.php';

// Statistik
$total_pelatihan = mysqli_fetch_row(mysqli_query($koneksi, "SELECT COUNT(*) FROM pelatihan"))[0];
$total_alumni    = mysqli_fetch_row(mysqli_query($koneksi, "SELECT COUNT(*) FROM alumni"))[0];
$total_peserta   = mysqli_fetch_row(mysqli_query($koneksi, "SELECT COUNT(*) FROM peserta_pelatihan"))[0];
$total_tracer    = mysqli_fetch_row(mysqli_query($koneksi, "SELECT COUNT(*) FROM tracer_study WHERE status_pengisian='sudah_diisi'"))[0];

// Pelatihan terbaru
$q_pel = mysqli_query($koneksi, "SELECT p.*, i.user_id,
    (SELECT name FROM users WHERE id=i.user_id) as nama_instruktur
    FROM pelatihan p
    JOIN instruktur i ON p.instruktur_id = i.id
    ORDER BY p.created_at DESC LIMIT 5");

// Tracer study terbaru
$q_tracer = mysqli_query($koneksi, "SELECT ts.*, u.name as nama_alumni
    FROM tracer_study ts
    JOIN alumni a ON ts.alumni_id = a.id
    JOIN users u ON a.user_id = u.id
    WHERE ts.status_pengisian = 'sudah_diisi'
    ORDER BY ts.tanggal_isi DESC LIMIT 5");
?>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
  <div class="col-sm-6 col-xl-3">
    <div class="stat-card bg-white shadow-sm">
      <div class="icon bg-primary bg-opacity-10 text-primary"><i class="bi bi-journal-bookmark"></i></div>
      <div>
        <div class="val text-primary"><?= $total_pelatihan ?></div>
        <div class="lbl">Total Pelatihan</div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="stat-card bg-white shadow-sm">
      <div class="icon bg-success bg-opacity-10 text-success"><i class="bi bi-mortarboard"></i></div>
      <div>
        <div class="val text-success"><?= $total_alumni ?></div>
        <div class="lbl">Total Alumni</div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="stat-card bg-white shadow-sm">
      <div class="icon bg-warning bg-opacity-10 text-warning"><i class="bi bi-people"></i></div>
      <div>
        <div class="val text-warning"><?= $total_peserta ?></div>
        <div class="lbl">Total Peserta</div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-xl-3">
    <div class="stat-card bg-white shadow-sm">
      <div class="icon bg-info bg-opacity-10 text-info"><i class="bi bi-clipboard-check"></i></div>
      <div>
        <div class="val text-info"><?= $total_tracer ?></div>
        <div class="lbl">Tracer Terisi</div>
      </div>
    </div>
  </div>
</div>

<div class="row g-3">
  <!-- Pelatihan Terbaru -->
  <div class="col-lg-7">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        Pelatihan Terbaru
        <a href="pelatihan.php" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
      </div>
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead><tr><th>Nama Pelatihan</th><th>Instruktur</th><th>Tgl Mulai</th><th>Status</th></tr></thead>
          <tbody>
          <?php while ($row = mysqli_fetch_assoc($q_pel)): ?>
            <tr>
              <td><?= htmlspecialchars($row['nama_pelatihan']) ?></td>
              <td><?= htmlspecialchars($row['nama_instruktur']) ?></td>
              <td><?= date('d M Y', strtotime($row['tanggal_mulai'])) ?></td>
              <td>
                <?php
                $badge = ['aktif'=>'success','selesai'=>'secondary','dibatalkan'=>'danger'];
                $b = $badge[$row['status']] ?? 'secondary';
                ?>
                <span class="badge bg-<?= $b ?>"><?= ucfirst($row['status']) ?></span>
              </td>
            </tr>
          <?php endwhile; ?>
          <?php if (mysqli_num_rows($q_pel) === 0): ?>
            <tr><td colspan="4" class="text-center text-muted py-3">Belum ada data pelatihan</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Tracer Study Terbaru -->
  <div class="col-lg-5">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        Tracer Study Terbaru
        <a href="tracer.php" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
      </div>
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead><tr><th>Alumni</th><th>Status Kerja</th><th>Tgl Isi</th></tr></thead>
          <tbody>
          <?php while ($row = mysqli_fetch_assoc($q_tracer)): ?>
            <tr>
              <td><?= htmlspecialchars($row['nama_alumni']) ?></td>
              <td>
                <?php
                $st = ['bekerja'=>'success','wirausaha'=>'info','belum_bekerja'=>'warning','melanjutkan_studi'=>'primary'];
                $label = str_replace('_',' ', $row['status_pekerjaan'] ?? '-');
                $b2 = $st[$row['status_pekerjaan']] ?? 'secondary';
                ?>
                <span class="badge bg-<?= $b2 ?>"><?= ucfirst($label) ?></span>
              </td>
              <td><?= $row['tanggal_isi'] ? date('d M Y', strtotime($row['tanggal_isi'])) : '-' ?></td>
            </tr>
          <?php endwhile; ?>
          <?php if (mysqli_num_rows($q_tracer) === 0): ?>
            <tr><td colspan="3" class="text-center text-muted py-3">Belum ada tracer terisi</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php include 'footer.php'; ?>
