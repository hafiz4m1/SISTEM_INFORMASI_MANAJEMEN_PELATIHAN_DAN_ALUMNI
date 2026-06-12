<?php
$page_title = 'Pelatihan Saya';
include 'header.php';

// Proses selesaikan pelatihan
if (isset($_GET['selesai'])) {
    $pid = (int)$_GET['selesai'];
    // Pastikan pelatihan ini milik instruktur yang login
    $cek = mysqli_fetch_row(mysqli_query($koneksi,
        "SELECT id FROM pelatihan WHERE id=$pid AND instruktur_id=$instruktur_id AND status='aktif'"));
    if ($cek) {
        mysqli_query($koneksi, "UPDATE pelatihan SET status='selesai' WHERE id=$pid");
        header("location: pelatihan.php?pesan=Pelatihan berhasil diselesaikan."); exit;
    }
}

$pesan = isset($_GET['pesan']) ? $_GET['pesan'] : '';

$data = mysqli_query($koneksi, "
    SELECT p.*,
        (SELECT COUNT(*) FROM peserta_pelatihan WHERE pelatihan_id=p.id) as jml_peserta,
        (SELECT COUNT(*) FROM peserta_pelatihan WHERE pelatihan_id=p.id AND status_lulus='lulus') as jml_lulus,
        (SELECT COUNT(*) FROM peserta_pelatihan WHERE pelatihan_id=p.id AND status_lulus='belum_dinilai') as jml_belum
    FROM pelatihan p
    WHERE p.instruktur_id=$instruktur_id
    ORDER BY p.tanggal_mulai DESC
");
?>

<?php if ($pesan): ?>
  <div class="alert alert-success alert-dismissible fade show">
    <?= htmlspecialchars($pesan) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>

<div class="card">
  <div class="card-header">Daftar Pelatihan Saya</div>
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead>
        <tr><th>#</th><th>Nama Pelatihan</th><th>Tgl Mulai</th><th>Tgl Selesai</th><th>Peserta</th><th>Lulus</th><th>Belum Dinilai</th><th>Status</th><th>Aksi</th></tr>
      </thead>
      <tbody>
      <?php $no=1; while ($p = mysqli_fetch_assoc($data)): ?>
        <tr>
          <td><?= $no++ ?></td>
          <td><?= htmlspecialchars($p['nama_pelatihan']) ?><br><small class="text-muted"><?= $p['jenis'] ?></small></td>
          <td><?= date('d M Y', strtotime($p['tanggal_mulai'])) ?></td>
          <td><?= date('d M Y', strtotime($p['tanggal_selesai'])) ?></td>
          <td><?= $p['jml_peserta'] ?>/<?= $p['kuota'] ?></td>
          <td><span class="badge bg-success"><?= $p['jml_lulus'] ?></span></td>
          <td>
            <?php if ($p['jml_belum'] > 0): ?>
              <span class="badge bg-warning text-dark"><?= $p['jml_belum'] ?></span>
            <?php else: ?>
              <span class="badge bg-secondary">0</span>
            <?php endif; ?>
          </td>
          <td>
            <?php $badge=['aktif'=>'success','selesai'=>'secondary','dibatalkan'=>'danger']; ?>
            <span class="badge bg-<?= $badge[$p['status']] ?? 'secondary' ?>"><?= ucfirst($p['status']) ?></span>
          </td>
          <td class="d-flex gap-1">
            <a href="peserta.php?pelatihan_id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary">
              <i class="bi bi-people"></i> Peserta
            </a>
            <?php if ($p['status'] === 'aktif'): ?>
              <a href="pelatihan.php?selesai=<?= $p['id'] ?>" class="btn btn-sm btn-outline-success"
                 onclick="return confirm('Tandai pelatihan \'<?= htmlspecialchars($p['nama_pelatihan']) ?>\' sebagai selesai?')">
                <i class="bi bi-check-circle"></i> Selesai
              </a>
            <?php endif; ?>
          </td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include 'footer.php'; ?>