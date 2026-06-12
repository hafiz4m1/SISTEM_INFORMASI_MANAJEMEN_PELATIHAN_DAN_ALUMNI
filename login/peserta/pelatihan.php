<?php
$page_title = 'Pelatihan';
include 'header.php';

$uid   = $_SESSION['id_login'];
$pesan = isset($_GET['pesan']) ? $_GET['pesan'] : '';

if (isset($_GET['daftar'])) {
    $pid = (int)$_GET['daftar'];
    $pel = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM pelatihan WHERE id=$pid AND status='aktif'"));
    $jml = mysqli_fetch_row(mysqli_query($koneksi, "SELECT COUNT(*) FROM peserta_pelatihan WHERE pelatihan_id=$pid"))[0];
    $cek = mysqli_fetch_row(mysqli_query($koneksi, "SELECT id FROM peserta_pelatihan WHERE user_id=$uid AND pelatihan_id=$pid"));

    if (!$pel) {
        $pesan = 'Pelatihan tidak ditemukan atau sudah tidak aktif.';
    } elseif ($cek) {
        $pesan = 'Anda sudah terdaftar di pelatihan ini.';
    } elseif ($jml >= $pel['kuota']) {
        $pesan = 'Maaf, kuota pelatihan ini sudah penuh.';
    } else {
        mysqli_query($koneksi, "INSERT INTO peserta_pelatihan (user_id, pelatihan_id, tanggal_daftar) VALUES ($uid,$pid,NOW())");
        $pesan = 'Berhasil mendaftar pelatihan ' . htmlspecialchars($pel['nama_pelatihan']) . '!';
    }
}

$tab = isset($_GET['tab']) ? $_GET['tab'] : 'tersedia';
?>

<?php if ($pesan): ?>
  <div class="alert alert-info alert-dismissible fade show"><?= htmlspecialchars($pesan) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<ul class="nav nav-tabs mb-3">
  <li class="nav-item"><a class="nav-link <?= $tab==='tersedia'?'active':'' ?>" href="pelatihan.php?tab=tersedia">Pelatihan Tersedia</a></li>
  <li class="nav-item"><a class="nav-link <?= $tab==='saya'?'active':'' ?>" href="pelatihan.php?tab=saya">Pelatihan Saya</a></li>
</ul>

<?php if ($tab === 'tersedia'): ?>
<div class="row g-3">
  <?php
  $data = mysqli_query($koneksi, "
      SELECT p.*, u.name as nama_instruktur,
          (SELECT COUNT(*) FROM peserta_pelatihan WHERE pelatihan_id=p.id) as jml_peserta,
          (SELECT id FROM peserta_pelatihan WHERE user_id=$uid AND pelatihan_id=p.id) as sudah_daftar
      FROM pelatihan p
      JOIN instruktur i ON p.instruktur_id=i.id
      JOIN users u ON i.user_id=u.id
      WHERE p.status='aktif'
      ORDER BY p.tanggal_mulai ASC
  ");
  $count=0;
  while ($p = mysqli_fetch_assoc($data)): $count++;
  $sisa = $p['kuota'] - $p['jml_peserta'];
  ?>
  <div class="col-md-6 col-lg-4">
    <div class="card h-100">
      <div class="card-body p-4">
        <div class="d-flex justify-content-between mb-2">
          <span class="badge bg-primary"><?= htmlspecialchars($p['jenis'] ?? 'Umum') ?></span>
          <span class="badge <?= $sisa>0?'bg-success':'bg-danger' ?>"><?= $sisa>0?"$sisa sisa":'Penuh' ?></span>
        </div>
        <h6 class="fw-bold mb-2"><?= htmlspecialchars($p['nama_pelatihan']) ?></h6>
        <div style="font-size:12px;color:#6b7280" class="mb-3">
          <div class="mb-1"><i class="bi bi-person me-1"></i><?= htmlspecialchars($p['nama_instruktur']) ?></div>
          <div class="mb-1"><i class="bi bi-calendar me-1"></i><?= date('d M Y', strtotime($p['tanggal_mulai'])) ?> - <?= date('d M Y', strtotime($p['tanggal_selesai'])) ?></div>
          <?php if ($p['lokasi']): ?><div><i class="bi bi-geo-alt me-1"></i><?= htmlspecialchars($p['lokasi']) ?></div><?php endif; ?>
        </div>
        <?php if ($p['sudah_daftar']): ?>
          <button class="btn btn-sm btn-success w-100" disabled><i class="bi bi-check-circle"></i> Sudah Terdaftar</button>
        <?php elseif ($sisa > 0): ?>
          <a href="pelatihan.php?daftar=<?= $p['id'] ?>&tab=tersedia" class="btn btn-sm btn-primary w-100"
             onclick="return confirm('Daftar pelatihan ini?')"><i class="bi bi-plus-lg"></i> Daftar Sekarang</a>
        <?php else: ?>
          <button class="btn btn-sm btn-secondary w-100" disabled>Kuota Penuh</button>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php endwhile; ?>
  <?php if ($count===0): ?>
    <div class="col-12"><div class="card p-5 text-center"><i class="bi bi-calendar-x text-muted" style="font-size:48px"></i><h6 class="mt-3 text-muted">Tidak ada pelatihan aktif</h6></div></div>
  <?php endif; ?>
</div>

<?php else: ?>
<div class="card">
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead><tr><th>#</th><th>Pelatihan</th><th>Instruktur</th><th>Tgl Mulai</th><th>Kehadiran</th><th>Nilai</th><th>Status</th></tr></thead>
      <tbody>
      <?php
      $data = mysqli_query($koneksi, "
          SELECT pp.*, p.nama_pelatihan, p.tanggal_mulai, u.name as nama_instruktur
          FROM peserta_pelatihan pp
          JOIN pelatihan p ON pp.pelatihan_id=p.id
          JOIN instruktur i ON p.instruktur_id=i.id
          JOIN users u ON i.user_id=u.id
          WHERE pp.user_id=$uid ORDER BY p.tanggal_mulai DESC
      ");
      $no=1; while ($p = mysqli_fetch_assoc($data)):
      ?>
        <tr>
          <td><?= $no++ ?></td>
          <td><?= htmlspecialchars($p['nama_pelatihan']) ?></td>
          <td><?= htmlspecialchars($p['nama_instruktur']) ?></td>
          <td><?= date('d M Y', strtotime($p['tanggal_mulai'])) ?></td>
          <td>
            <?php $kh=['hadir'=>'success','tidak_hadir'=>'danger','izin'=>'warning']; ?>
            <span class="badge bg-<?= $kh[$p['status_kehadiran']]??'secondary' ?>"><?= str_replace('_',' ',$p['status_kehadiran']) ?></span>
          </td>
          <td><?= $p['nilai'] ?? '-' ?></td>
          <td>
            <?php $sl=['lulus'=>'success','tidak_lulus'=>'danger','belum_dinilai'=>'secondary']; ?>
            <span class="badge bg-<?= $sl[$p['status_lulus']]??'secondary' ?>"><?= str_replace('_',' ',$p['status_lulus']) ?></span>
          </td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>

<?php include 'footer.php'; ?>
