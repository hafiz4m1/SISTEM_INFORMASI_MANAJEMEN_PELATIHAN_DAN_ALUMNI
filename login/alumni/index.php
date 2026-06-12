<?php
$page_title = 'Dashboard';
include '../koneksi.php';
include_once '../notif.php';
include 'header.php';

// Statistik alumni ini
$jml_pelatihan = mysqli_fetch_row(mysqli_query($koneksi,
    "SELECT COUNT(*) FROM peserta_pelatihan WHERE user_id={$_SESSION['id_login']}"))[0];

$jml_lulus = mysqli_fetch_row(mysqli_query($koneksi,
    "SELECT COUNT(*) FROM peserta_pelatihan WHERE user_id={$_SESSION['id_login']} AND status_lulus='lulus'"))[0];

$jml_rekomendasi = mysqli_fetch_row(mysqli_query($koneksi,
    "SELECT COUNT(*) FROM rekomendasi WHERE alumni_id=$alumni_id AND is_dilihat=0"))[0];

$tracer = mysqli_fetch_assoc(mysqli_query($koneksi,
    "SELECT * FROM tracer_study WHERE alumni_id=$alumni_id ORDER BY tanggal_kirim DESC LIMIT 1"));

// Riwayat pelatihan terbaru
$pelatihan = mysqli_query($koneksi,
    "SELECT p.nama_pelatihan, p.tanggal_mulai, pp.nilai, pp.status_lulus
     FROM peserta_pelatihan pp
     JOIN pelatihan p ON pp.pelatihan_id=p.id
     WHERE pp.user_id={$_SESSION['id_login']}
     ORDER BY p.tanggal_mulai DESC LIMIT 5");

// Rekomendasi terbaru
$rekomendasi = mysqli_query($koneksi,
    "SELECT r.*, p.nama_pelatihan, p.tanggal_mulai, p.lokasi
     FROM rekomendasi r
     JOIN pelatihan p ON r.pelatihan_id=p.id
     WHERE r.alumni_id=$alumni_id
     ORDER BY r.skor DESC LIMIT 3");
?>

<!-- Alert tracer study jika belum diisi -->
<?php if ($tracer && $tracer['status_pengisian'] === 'belum_diisi'): ?>
<div class="alert d-flex align-items-center gap-3 mb-4"
     style="background:#fff8e1;border:1px solid #ffc107;border-radius:10px;padding:14px 18px">
  <i class="bi bi-exclamation-triangle-fill text-warning fs-4"></i>
  <div>
    <strong>Anda memiliki tracer study yang belum diisi!</strong><br>
    <small class="text-muted">Bantu kami meningkatkan kualitas pelatihan dengan mengisi tracer study.</small>
  </div>
  <a href="tracer.php" class="btn btn-sm btn-warning ms-auto">Isi Sekarang</a>
</div>
<?php endif; ?>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
  <div class="col-sm-4">
    <div class="stat-card bg-white shadow-sm">
      <div class="icon bg-primary bg-opacity-10 text-primary"><i class="bi bi-journal-bookmark"></i></div>
      <div><div class="val text-primary"><?= $jml_pelatihan ?></div><div class="lbl">Pelatihan Diikuti</div></div>
    </div>
  </div>
  <div class="col-sm-4">
    <div class="stat-card bg-white shadow-sm">
      <div class="icon bg-success bg-opacity-10 text-success"><i class="bi bi-patch-check"></i></div>
      <div><div class="val text-success"><?= $jml_lulus ?></div><div class="lbl">Pelatihan Lulus</div></div>
    </div>
  </div>
  <div class="col-sm-4">
    <div class="stat-card bg-white shadow-sm">
      <div class="icon bg-warning bg-opacity-10 text-warning"><i class="bi bi-stars"></i></div>
      <div><div class="val text-warning"><?= $jml_rekomendasi ?></div><div class="lbl">Rekomendasi Baru</div></div>
    </div>
  </div>
</div>

<div class="row g-3">
  <!-- Riwayat Pelatihan -->
  <div class="col-lg-7">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        Riwayat Pelatihan Terakhir
        <a href="pelatihan.php" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
      </div>
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead><tr><th>Pelatihan</th><th>Tgl Mulai</th><th>Nilai</th><th>Status</th></tr></thead>
          <tbody>
          <?php while ($p = mysqli_fetch_assoc($pelatihan)): ?>
            <tr>
              <td><?= htmlspecialchars($p['nama_pelatihan']) ?></td>
              <td><?= date('d M Y', strtotime($p['tanggal_mulai'])) ?></td>
              <td><?= $p['nilai'] ?? '-' ?></td>
              <td>
                <?php $sl=['lulus'=>'success','tidak_lulus'=>'danger','belum_dinilai'=>'secondary']; ?>
                <span class="badge bg-<?= $sl[$p['status_lulus']] ?? 'secondary' ?>"><?= str_replace('_',' ',$p['status_lulus']) ?></span>
              </td>
            </tr>
          <?php endwhile; ?>
          <?php if ($jml_pelatihan == 0): ?>
            <tr><td colspan="4" class="text-center text-muted py-3">Belum ada pelatihan yang diikuti</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <!-- Rekomendasi -->
  <div class="col-lg-5">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        Rekomendasi Pelatihan
        <a href="rekomendasi.php" class="btn btn-sm btn-outline-success">Lihat Semua</a>
      </div>
      <div class="card-body p-0">
        <?php $rek_count = 0; while ($r = mysqli_fetch_assoc($rekomendasi)): $rek_count++; ?>
          <div class="d-flex align-items-start gap-3 p-3 border-bottom">
            <div class="icon bg-success bg-opacity-10 text-success" style="width:40px;height:40px;border-radius:8px;display:flex;align-items:center;justify-content:center;flex-shrink:0">
              <i class="bi bi-bookmark-star"></i>
            </div>
            <div>
              <div class="fw-semibold" style="font-size:13px"><?= htmlspecialchars($r['nama_pelatihan']) ?></div>
              <small class="text-muted"><?= date('d M Y', strtotime($r['tanggal_mulai'])) ?> · Skor <?= $r['skor'] ?>%</small>
            </div>
          </div>
        <?php endwhile; ?>
        <?php if ($rek_count === 0): ?>
          <p class="text-muted text-center py-4 mb-0">Belum ada rekomendasi</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php include 'footer.php'; ?>
