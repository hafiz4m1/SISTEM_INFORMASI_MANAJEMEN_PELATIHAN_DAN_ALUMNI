<?php
$page_title = 'RKTL Saya';
include 'header.php';

$data = mysqli_query($koneksi, "
    SELECT r.*, p.nama_pelatihan, p.tanggal_selesai,
        u.name as nama_instruktur
    FROM rktl r
    JOIN pelatihan p ON r.pelatihan_id = p.id
    JOIN instruktur i ON r.instruktur_id = i.id
    JOIN users u ON i.user_id = u.id
    WHERE r.alumni_id = $alumni_id
    ORDER BY r.created_at DESC
");
?>

<div class="row g-3">
<?php $count=0; while ($row = mysqli_fetch_assoc($data)): $count++;
$sb = ['belum_mulai'=>'secondary','berjalan'=>'primary','selesai'=>'success','terhambat'=>'danger'];
$sl = ['belum_mulai'=>'Belum Mulai','berjalan'=>'Berjalan','selesai'=>'Selesai','terhambat'=>'Terhambat'];
$lewat = $row['tgl_pendampingan'] && strtotime($row['tgl_pendampingan']) < time() && $row['status'] !== 'selesai';
?>
  <div class="col-lg-6">
    <div class="card h-100" style="<?= $lewat?'border-left:4px solid #ffc107':'' ?>">
      <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-start mb-3">
          <div>
            <h6 class="fw-bold mb-1"><?= htmlspecialchars($row['nama_pelatihan']) ?></h6>
            <small class="text-muted">Instruktur: <?= htmlspecialchars($row['nama_instruktur']) ?></small>
          </div>
          <span class="badge bg-<?= $sb[$row['status']]??'secondary' ?>"><?= $sl[$row['status']]??'-' ?></span>
        </div>

        <!-- Progres bar -->
        <div class="mb-3">
          <div class="d-flex justify-content-between mb-1">
            <small class="text-muted fw-semibold">Progres RKTL</small>
            <small class="fw-bold"><?= $row['progres'] ?>%</small>
          </div>
          <div class="progress" style="height:10px;border-radius:6px">
            <div class="progress-bar <?= $row['progres']>=100?'bg-success':($row['progres']>=50?'bg-primary':'bg-warning') ?>"
                 style="width:<?= $row['progres'] ?>%;border-radius:6px"></div>
          </div>
        </div>

        <div style="font-size:13px" class="mb-3">
          <div class="mb-2">
            <span class="text-muted">Rencana Kerja:</span><br>
            <span><?= nl2br(htmlspecialchars($row['rencana'])) ?></span>
          </div>
          <div class="row g-2">
            <div class="col-6">
              <span class="text-muted">Target Waktu:</span><br>
              <strong><?= $row['target_waktu'] ? date('d M Y', strtotime($row['target_waktu'])) : '-' ?></strong>
            </div>
            <div class="col-6">
              <span class="text-muted">Tgl Pendampingan:</span><br>
              <strong <?= $lewat?'style="color:#dc3545"':'' ?>>
                <?= $row['tgl_pendampingan'] ? date('d M Y', strtotime($row['tgl_pendampingan'])) : '-' ?>
                <?= $lewat ? ' ⚠️' : '' ?>
              </strong>
            </div>
          </div>
        </div>

        <?php if ($row['catatan']): ?>
          <div class="p-3 rounded" style="background:#f8f9fb;font-size:13px">
            <span class="text-muted fw-semibold"><i class="bi bi-chat-left-text me-1"></i>Catatan Instruktur:</span><br>
            <?= nl2br(htmlspecialchars($row['catatan'])) ?>
          </div>
          <?php if ($row['tgl_verifikasi']): ?>
            <small class="text-muted mt-1 d-block">Diverifikasi: <?= date('d M Y', strtotime($row['tgl_verifikasi'])) ?></small>
          <?php endif; ?>
        <?php else: ?>
          <div class="p-3 rounded text-muted text-center" style="background:#f8f9fb;font-size:13px">
            <i class="bi bi-clock me-1"></i>Menunggu pendampingan dari instruktur
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
<?php endwhile; ?>
<?php if ($count === 0): ?>
  <div class="col-12">
    <div class="card p-5 text-center">
      <i class="bi bi-clipboard2-x text-muted" style="font-size:48px"></i>
      <h6 class="mt-3 mb-1">Belum Ada RKTL</h6>
      <p class="text-muted mb-0">RKTL akan muncul setelah Anda dinyatakan lulus dari pelatihan.</p>
    </div>
  </div>
<?php endif; ?>
</div>

<?php include 'footer.php'; ?>
