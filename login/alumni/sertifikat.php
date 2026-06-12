<?php
$page_title = 'Sertifikat Saya';
include 'header.php';

$data = mysqli_query($koneksi, "
    SELECT pp.*, p.nama_pelatihan, p.jenis, p.tanggal_mulai, p.tanggal_selesai,
        p.lokasi, u.name as nama_instruktur
    FROM peserta_pelatihan pp
    JOIN pelatihan p ON pp.pelatihan_id = p.id
    JOIN instruktur i ON p.instruktur_id = i.id
    JOIN users u ON i.user_id = u.id
    WHERE pp.user_id = {$_SESSION['id_login']}
    AND pp.status_lulus = 'lulus'
    ORDER BY p.tanggal_selesai DESC
");

$rows = [];
while ($row = mysqli_fetch_assoc($data)) $rows[] = $row;
$count = count($rows);
?>

<?php if ($count === 0): ?>
  <div class="card p-5 text-center">
    <i class="bi bi-award text-muted" style="font-size:56px"></i>
    <h6 class="mt-3 mb-1">Belum Ada Sertifikat</h6>
    <p class="text-muted mb-0">Sertifikat akan muncul setelah Anda dinyatakan lulus dari pelatihan.</p>
  </div>

<?php else: ?>

  <!-- Ringkasan -->
  <div class="row g-3 mb-4">
    <div class="col-sm-6">
      <div class="stat-card bg-white shadow-sm">
        <div class="icon bg-warning bg-opacity-10 text-warning"><i class="bi bi-award-fill"></i></div>
        <div><div class="val text-warning"><?= $count ?></div><div class="lbl">Total Sertifikat</div></div>
      </div>
    </div>
    <div class="col-sm-6">
      <div class="stat-card bg-white shadow-sm">
        <div class="icon bg-success bg-opacity-10 text-success"><i class="bi bi-printer"></i></div>
        <div><div class="val text-success"><?= $count ?></div><div class="lbl">Siap Dicetak</div></div>
      </div>
    </div>
  </div>

  <!-- Daftar Sertifikat -->
  <div class="row g-3">
    <?php foreach ($rows as $row): ?>
    <div class="col-md-6 col-lg-4">
      <div class="card h-100" style="border-radius:14px;box-shadow:0 2px 12px rgba(0,0,0,.07)">
        <!-- Header kartu -->
        <div style="background:linear-gradient(135deg,#1a4c8e,#0d3060);border-radius:14px 14px 0 0;padding:24px;text-align:center">
          <div style="width:60px;height:60px;background:rgba(255,255,255,.15);border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 12px;font-size:28px">🏆</div>
          <div style="color:rgba(255,255,255,.7);font-size:11px;text-transform:uppercase;letter-spacing:.06em">Sertifikat Kelulusan</div>
          <div style="color:#fff;font-weight:700;font-size:14px;margin-top:4px;line-height:1.3"><?= htmlspecialchars($row['nama_pelatihan']) ?></div>
        </div>

        <!-- Body kartu -->
        <div class="card-body p-3">
          <div style="font-size:12px;color:#6b7280" class="mb-3">
            <div class="mb-1 d-flex align-items-center gap-2">
              <i class="bi bi-person text-primary"></i>
              <span><?= htmlspecialchars($row['nama_instruktur']) ?></span>
            </div>
            <div class="mb-1 d-flex align-items-center gap-2">
              <i class="bi bi-calendar text-primary"></i>
              <span><?= date('d M Y', strtotime($row['tanggal_mulai'])) ?> - <?= date('d M Y', strtotime($row['tanggal_selesai'])) ?></span>
            </div>
            <?php if ($row['lokasi']): ?>
            <div class="mb-1 d-flex align-items-center gap-2">
              <i class="bi bi-geo-alt text-primary"></i>
              <span><?= htmlspecialchars($row['lokasi']) ?></span>
            </div>
            <?php endif; ?>
            <div class="d-flex align-items-center gap-2">
              <i class="bi bi-star-fill text-warning"></i>
              <span>Nilai: <strong><?= $row['nilai'] ?? '-' ?></strong>
              &nbsp;·&nbsp;
              <?php
              $nilai = $row['nilai'];
              $predikat = $nilai >= 90 ? 'Sangat Memuaskan' : ($nilai >= 80 ? 'Memuaskan' : ($nilai >= 70 ? 'Cukup' : 'Lulus'));
              ?>
              <span class="badge bg-success" style="font-size:10px"><?= $predikat ?></span>
              </span>
            </div>
          </div>

          <a href="sertifikat_cetak.php?id=<?= $row['id'] ?>" target="_blank"
             class="btn btn-warning w-100 btn-sm fw-semibold">
            <i class="bi bi-printer me-1"></i> Cetak / Simpan PDF
          </a>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

<?php endif; ?>

<?php include 'footer.php'; ?>
