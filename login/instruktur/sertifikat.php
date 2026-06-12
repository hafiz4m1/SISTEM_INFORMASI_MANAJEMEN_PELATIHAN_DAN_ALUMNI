<?php
$page_title = 'Sertifikat';
include 'header.php';

$uid  = $_SESSION['id_login'];
$data = mysqli_query($koneksi, "
    SELECT pp.*, p.nama_pelatihan, p.tanggal_mulai, p.tanggal_selesai,
        u.name as nama_instruktur
    FROM peserta_pelatihan pp
    JOIN pelatihan p ON pp.pelatihan_id=p.id
    JOIN instruktur i ON p.instruktur_id=i.id
    JOIN users u ON i.user_id=u.id
    WHERE pp.user_id=$uid AND pp.status_lulus='lulus'
    ORDER BY p.tanggal_selesai DESC
");
?>

<div class="row g-3">
<?php $count = 0; while ($row = mysqli_fetch_assoc($data)): $count++; ?>
  <div class="col-md-6 col-lg-4">
    <div class="card h-100 text-center p-4">
      <div class="mb-3">
        <div style="width:64px;height:64px;background:#fff8e1;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto;font-size:30px">🏆</div>
      </div>
      <h6 class="fw-bold mb-1"><?= htmlspecialchars($row['nama_pelatihan']) ?></h6>
      <small class="text-muted mb-3 d-block">
        <?= date('d M Y', strtotime($row['tanggal_mulai'])) ?> - <?= date('d M Y', strtotime($row['tanggal_selesai'])) ?>
      </small>
      <div class="mb-2" style="font-size:13px">
        <span class="text-muted">Instruktur:</span> <?= htmlspecialchars($row['nama_instruktur']) ?>
      </div>
      <div class="mb-3" style="font-size:13px">
        <span class="text-muted">Nilai:</span> <strong><?= $row['nilai'] ?? '-' ?></strong>
        &nbsp; <span class="badge bg-success">Lulus</span>
      </div>
      <?php if ($row['sertifikat_url']): ?>
        <a href="<?= htmlspecialchars($row['sertifikat_url']) ?>" target="_blank" class="btn btn-warning btn-sm">
          <i class="bi bi-download me-1"></i> Unduh Sertifikat
        </a>
      <?php else: ?>
        <button class="btn btn-outline-secondary btn-sm" disabled>
          <i class="bi bi-hourglass me-1"></i> Sertifikat Belum Tersedia
        </button>
      <?php endif; ?>
    </div>
  </div>
<?php endwhile; ?>
<?php if ($count === 0): ?>
  <div class="col-12">
    <div class="card p-5 text-center">
      <i class="bi bi-award text-muted" style="font-size:48px"></i>
      <h6 class="mt-3 mb-1">Belum Ada Sertifikat</h6>
      <p class="text-muted mb-0">Selesaikan pelatihan dan lulus untuk mendapatkan sertifikat.</p>
    </div>
  </div>
<?php endif; ?>
</div>

<?php include 'footer.php'; ?>
