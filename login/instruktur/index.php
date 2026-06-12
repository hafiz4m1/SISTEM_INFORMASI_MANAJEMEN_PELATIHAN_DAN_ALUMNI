<?php
$page_title = 'Dashboard';
include 'header.php';
include '../koneksi.php';
include_once '../notif.php';

$jml_pelatihan = mysqli_fetch_row(mysqli_query($koneksi,
    "SELECT COUNT(*) FROM pelatihan WHERE instruktur_id=$instruktur_id"))[0];
$jml_peserta = mysqli_fetch_row(mysqli_query($koneksi,
    "SELECT COUNT(*) FROM peserta_pelatihan pp
     JOIN pelatihan p ON pp.pelatihan_id=p.id
     WHERE p.instruktur_id=$instruktur_id"))[0];
$jml_lulus = mysqli_fetch_row(mysqli_query($koneksi,
    "SELECT COUNT(*) FROM peserta_pelatihan pp
     JOIN pelatihan p ON pp.pelatihan_id=p.id
     WHERE p.instruktur_id=$instruktur_id AND pp.status_lulus='lulus'"))[0];
$jml_belum_nilai = mysqli_fetch_row(mysqli_query($koneksi,
    "SELECT COUNT(*) FROM peserta_pelatihan pp
     JOIN pelatihan p ON pp.pelatihan_id=p.id
     WHERE p.instruktur_id=$instruktur_id AND pp.status_lulus='belum_dinilai'"))[0];

// Pelatihan terbaru
$pelatihan = mysqli_query($koneksi, "
    SELECT p.*,
        (SELECT COUNT(*) FROM peserta_pelatihan WHERE pelatihan_id=p.id) as jml_peserta
    FROM pelatihan p
    WHERE p.instruktur_id=$instruktur_id
    ORDER BY p.tanggal_mulai DESC LIMIT 5
");
?>

<!-- Alert jika ada peserta belum dinilai -->
<?php if ($jml_belum_nilai > 0): ?>
<div class="alert d-flex align-items-center gap-3 mb-4"
     style="background:#fff3cd;border:1px solid #ffc107;border-radius:10px;padding:14px 18px">
  <i class="bi bi-exclamation-triangle-fill text-warning fs-4"></i>
  <div>
    <strong><?= $jml_belum_nilai ?> peserta belum dinilai</strong><br>
    <small class="text-muted">Silakan input nilai peserta yang sudah selesai mengikuti pelatihan.</small>
  </div>
  <a href="peserta.php" class="btn btn-sm btn-warning ms-auto">Input Nilai</a>
</div>
<?php endif; ?>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
  <div class="col-sm-3">
    <div class="stat-card bg-white shadow-sm">
      <div class="icon" style="background:#f3e8ff;color:#6a3090"><i class="bi bi-journal-bookmark"></i></div>
      <div><div class="val" style="color:#6a3090"><?= $jml_pelatihan ?></div><div class="lbl">Total Pelatihan</div></div>
    </div>
  </div>
  <div class="col-sm-3">
    <div class="stat-card bg-white shadow-sm">
      <div class="icon bg-primary bg-opacity-10 text-primary"><i class="bi bi-people"></i></div>
      <div><div class="val text-primary"><?= $jml_peserta ?></div><div class="lbl">Total Peserta</div></div>
    </div>
  </div>
  <div class="col-sm-3">
    <div class="stat-card bg-white shadow-sm">
      <div class="icon bg-success bg-opacity-10 text-success"><i class="bi bi-patch-check"></i></div>
      <div><div class="val text-success"><?= $jml_lulus ?></div><div class="lbl">Peserta Lulus</div></div>
    </div>
  </div>
  <div class="col-sm-3">
    <div class="stat-card bg-white shadow-sm">
      <div class="icon bg-warning bg-opacity-10 text-warning"><i class="bi bi-hourglass-split"></i></div>
      <div><div class="val text-warning"><?= $jml_belum_nilai ?></div><div class="lbl">Belum Dinilai</div></div>
    </div>
  </div>
</div>

<!-- Daftar Pelatihan -->
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    Pelatihan Saya
    <a href="pelatihan.php" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
  </div>
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead><tr><th>Nama Pelatihan</th><th>Tgl Mulai</th><th>Tgl Selesai</th><th>Peserta</th><th>Status</th><th>Aksi</th></tr></thead>
      <tbody>
      <?php while ($p = mysqli_fetch_assoc($pelatihan)): ?>
        <tr>
          <td><?= htmlspecialchars($p['nama_pelatihan']) ?></td>
          <td><?= date('d M Y', strtotime($p['tanggal_mulai'])) ?></td>
          <td><?= date('d M Y', strtotime($p['tanggal_selesai'])) ?></td>
          <td><span class="badge bg-secondary"><?= $p['jml_peserta'] ?>/<?= $p['kuota'] ?></span></td>
          <td>
            <?php $badge=['aktif'=>'success','selesai'=>'secondary','dibatalkan'=>'danger']; ?>
            <span class="badge bg-<?= $badge[$p['status']] ?? 'secondary' ?>"><?= ucfirst($p['status']) ?></span>
          </td>
          <td>
            <a href="peserta.php?pelatihan_id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-people"></i> Peserta</a>
          </td>
        </tr>
      <?php endwhile; ?>
      <?php if ($jml_pelatihan == 0): ?>
        <tr><td colspan="6" class="text-center text-muted py-4">Belum ada pelatihan</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include 'footer.php'; ?>
