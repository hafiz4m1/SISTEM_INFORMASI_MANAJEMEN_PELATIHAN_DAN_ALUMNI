<?php
$page_title = 'Manajemen Alumni';
include '../koneksi.php';
include 'header.php';

$search = isset($_GET['q']) ? mysqli_real_escape_string($koneksi, $_GET['q']) : '';
$where  = $search ? "WHERE u.name LIKE '%$search%' OR u.email LIKE '%$search%'" : '';

$data = mysqli_query($koneksi, "
    SELECT a.*, u.name, u.email,
        (SELECT COUNT(*) FROM peserta_pelatihan pp WHERE pp.user_id=a.user_id) as jml_pelatihan,
        (SELECT COUNT(*) FROM tracer_study ts WHERE ts.alumni_id=a.id AND ts.status_pengisian='sudah_diisi') as jml_tracer
    FROM alumni a
    JOIN users u ON a.user_id = u.id
    $where
    ORDER BY u.name ASC
");
?>

<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
    <span>Daftar Alumni</span>
    <form class="d-flex" method="GET">
      <input type="search" name="q" class="form-control form-control-sm" placeholder="Cari nama / email..." value="<?= htmlspecialchars($search) ?>">
      <button class="btn btn-sm btn-outline-secondary ms-1">Cari</button>
    </form>
  </div>
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead>
        <tr><th>#</th><th>Nama</th><th>Email</th><th>Tgl Lulus</th><th>Pelatihan</th><th>Tracer</th><th>Aksi</th></tr>
      </thead>
      <tbody>
      <?php $no = 1; while ($row = mysqli_fetch_assoc($data)): ?>
        <tr>
          <td><?= $no++ ?></td>
          <td><?= htmlspecialchars($row['name']) ?></td>
          <td><small><?= htmlspecialchars($row['email']) ?></small></td>
          <td><?= $row['tanggal_lulus'] ? date('d M Y', strtotime($row['tanggal_lulus'])) : '-' ?></td>
          <td><span class="badge bg-primary"><?= $row['jml_pelatihan'] ?> pelatihan</span></td>
          <td>
            <?php if ($row['jml_tracer'] > 0): ?>
              <span class="badge bg-success"><i class="bi bi-check-circle"></i> Terisi</span>
            <?php else: ?>
              <span class="badge bg-secondary">Belum</span>
            <?php endif; ?>
          </td>
          <td>
            <a href="alumni_detail.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-primary me-1"><i class="bi bi-eye"></i></a>
            <a href="alumni_edit.php?id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-warning"><i class="bi bi-pencil"></i></a>
          </td>
        </tr>
      <?php endwhile; ?>
      <?php if (mysqli_num_rows($data) === 0): ?>
        <tr><td colspan="7" class="text-center text-muted py-4">Tidak ada data alumni</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include 'footer.php'; ?>
