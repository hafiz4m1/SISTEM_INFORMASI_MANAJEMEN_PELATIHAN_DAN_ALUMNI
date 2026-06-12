<?php
$page_title = 'Manajemen Pelatihan';
include '../koneksi.php';
include 'header.php';

$pesan = '';
if (isset($_GET['pesan'])) $pesan = $_GET['pesan'];

// Hapus
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    mysqli_query($koneksi, "DELETE FROM pelatihan WHERE id=$id");
    header("location: pelatihan.php?pesan=Data pelatihan berhasil dihapus.");
    exit;
}

$search = isset($_GET['q']) ? mysqli_real_escape_string($koneksi, $_GET['q']) : '';
$where  = $search ? "WHERE p.nama_pelatihan LIKE '%$search%'" : '';

$data = mysqli_query($koneksi, "
    SELECT p.*, u.name as nama_instruktur,
        (SELECT COUNT(*) FROM peserta_pelatihan WHERE pelatihan_id=p.id) as jml_peserta
    FROM pelatihan p
    JOIN instruktur i ON p.instruktur_id = i.id
    JOIN users u ON i.user_id = u.id
    $where
    ORDER BY p.tanggal_mulai DESC
");
?>

<?php if ($pesan): ?>
  <div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($pesan) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
    <span>Daftar Pelatihan</span>
    <div class="d-flex gap-2">
      <form class="d-flex" method="GET">
        <input type="search" name="q" class="form-control form-control-sm" placeholder="Cari pelatihan..." value="<?= htmlspecialchars($search) ?>">
        <button class="btn btn-sm btn-outline-secondary ms-1">Cari</button>
      </form>
      <a href="pelatihan_add.php" class="btn btn-sm btn-primary"><i class="bi bi-plus-lg"></i> Tambah</a>
    </div>
  </div>
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead>
        <tr>
          <th>#</th><th>Nama Pelatihan</th><th>Instruktur</th>
          <th>Tgl Mulai</th><th>Tgl Selesai</th><th>Peserta</th>
          <th>Status</th><th>Aksi</th>
        </tr>
      </thead>
      <tbody>
      <?php $no = 1; while ($row = mysqli_fetch_assoc($data)): ?>
        <tr>
          <td><?= $no++ ?></td>
          <td><?= htmlspecialchars($row['nama_pelatihan']) ?><br><small class="text-muted"><?= htmlspecialchars($row['jenis'] ?? '') ?></small></td>
          <td><?= htmlspecialchars($row['nama_instruktur']) ?></td>
          <td><?= date('d M Y', strtotime($row['tanggal_mulai'])) ?></td>
          <td><?= date('d M Y', strtotime($row['tanggal_selesai'])) ?></td>
          <td><span class="badge bg-secondary"><?= $row['jml_peserta'] ?>/<?= $row['kuota'] ?></span></td>
          <td>
            <?php $badge=['aktif'=>'success','selesai'=>'secondary','dibatalkan'=>'danger']; ?>
            <span class="badge bg-<?= $badge[$row['status']] ?? 'secondary' ?>"><?= ucfirst($row['status']) ?></span>
          </td>
          <td>
            <a href="pelatihan_edit.php?id=<?= $row['id'] ?>" class="btn btn-xs btn-outline-warning btn-sm"><i class="bi bi-pencil"></i></a>
            <a href="pelatihan.php?hapus=<?= $row['id'] ?>" class="btn btn-xs btn-outline-danger btn-sm"
               onclick="return confirm('Hapus pelatihan ini?')"><i class="bi bi-trash"></i></a>
          </td>
        </tr>
      <?php endwhile; ?>
      <?php if (mysqli_num_rows($data) === 0): ?>
        <tr><td colspan="8" class="text-center text-muted py-4">Tidak ada data pelatihan</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include 'footer.php'; ?>
