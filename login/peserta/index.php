<?php
$page_title = 'Dashboard';
include 'header.php';

$uid = $_SESSION['id_login'];

$jml_pelatihan = mysqli_fetch_row(mysqli_query($koneksi,
    "SELECT COUNT(*) FROM peserta_pelatihan WHERE user_id=$uid"))[0];
$jml_lulus = mysqli_fetch_row(mysqli_query($koneksi,
    "SELECT COUNT(*) FROM peserta_pelatihan WHERE user_id=$uid AND status_lulus='lulus'"))[0];
$jml_sertifikat = mysqli_fetch_row(mysqli_query($koneksi,
    "SELECT COUNT(*) FROM peserta_pelatihan WHERE user_id=$uid AND sertifikat_url IS NOT NULL"))[0];

$pelatihan_tersedia = mysqli_query($koneksi, "
    SELECT p.*, u.name as nama_instruktur,
        (SELECT COUNT(*) FROM peserta_pelatihan WHERE pelatihan_id=p.id) as jml_peserta
    FROM pelatihan p
    JOIN instruktur i ON p.instruktur_id=i.id
    JOIN users u ON i.user_id=u.id
    WHERE p.status='aktif'
    AND p.id NOT IN (SELECT pelatihan_id FROM peserta_pelatihan WHERE user_id=$uid)
    ORDER BY p.tanggal_mulai ASC LIMIT 3
");

$pelatihan_saya = mysqli_query($koneksi, "
    SELECT pp.*, p.nama_pelatihan, p.tanggal_mulai
    FROM peserta_pelatihan pp
    JOIN pelatihan p ON pp.pelatihan_id=p.id
    WHERE pp.user_id=$uid
    ORDER BY p.tanggal_mulai DESC LIMIT 5
");
?>

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
      <div><div class="val text-success"><?= $jml_lulus ?></div><div class="lbl">Lulus</div></div>
    </div>
  </div>
  <div class="col-sm-4">
    <div class="stat-card bg-white shadow-sm">
      <div class="icon bg-warning bg-opacity-10 text-warning"><i class="bi bi-award"></i></div>
      <div><div class="val text-warning"><?= $jml_sertifikat ?></div><div class="lbl">Sertifikat</div></div>
    </div>
  </div>
</div>

<div class="row g-3">
  <div class="col-lg-5">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        Pelatihan Tersedia
        <a href="pelatihan.php" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
      </div>
      <div class="card-body p-0">
        <?php $count=0; while ($p = mysqli_fetch_assoc($pelatihan_tersedia)): $count++; ?>
          <div class="p-3 border-bottom">
            <div class="fw-semibold mb-1" style="font-size:13px"><?= htmlspecialchars($p['nama_pelatihan']) ?></div>
            <div style="font-size:12px;color:#6b7280" class="mb-2">
              <i class="bi bi-calendar me-1"></i><?= date('d M Y', strtotime($p['tanggal_mulai'])) ?>
              &nbsp;·&nbsp;<i class="bi bi-people me-1"></i><?= $p['jml_peserta'] ?>/<?= $p['kuota'] ?>
            </div>
            <a href="pelatihan.php?daftar=<?= $p['id'] ?>" class="btn btn-sm btn-primary"
               onclick="return confirm('Daftar pelatihan ini?')">
              <i class="bi bi-plus-lg"></i> Daftar
            </a>
          </div>
        <?php endwhile; ?>
        <?php if ($count===0): ?>
          <p class="text-muted text-center py-4 mb-0">Tidak ada pelatihan tersedia</p>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="col-lg-7">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        Pelatihan Saya
        <a href="pelatihan.php?tab=saya" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
      </div>
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead><tr><th>Pelatihan</th><th>Tgl Mulai</th><th>Nilai</th><th>Status</th></tr></thead>
          <tbody>
          <?php while ($p = mysqli_fetch_assoc($pelatihan_saya)): ?>
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
          <?php if ($jml_pelatihan==0): ?>
            <tr><td colspan="4" class="text-center text-muted py-3">Belum ada pelatihan</td></tr>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php include 'footer.php'; ?>
