<?php
$page_title = 'Manajemen Peserta';
include '../koneksi.php';
include 'header.php';

$pesan = isset($_GET['pesan']) ? $_GET['pesan'] : '';

// Filter berdasarkan pelatihan
$filter_pel = isset($_GET['pelatihan_id']) ? (int)$_GET['pelatihan_id'] : 0;
$where = $filter_pel ? "WHERE pp.pelatihan_id=$filter_pel" : '';

$data = mysqli_query($koneksi, "
    SELECT pp.*, u.name as nama_peserta, u.email,
        p.nama_pelatihan, p.tanggal_mulai
    FROM peserta_pelatihan pp
    JOIN users u ON pp.user_id = u.id
    JOIN pelatihan p ON pp.pelatihan_id = p.id
    $where
    ORDER BY p.tanggal_mulai DESC, u.name ASC
");

$list_pel = mysqli_query($koneksi, "SELECT id, nama_pelatihan FROM pelatihan ORDER BY tanggal_mulai DESC");
?>

<?php if ($pesan): ?>
  <div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($pesan) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
    <span>Daftar Peserta Pelatihan</span>
    <form class="d-flex gap-2" method="GET">
      <select name="pelatihan_id" class="form-select form-select-sm" style="min-width:200px">
        <option value="">Semua Pelatihan</option>
        <?php while ($p = mysqli_fetch_assoc($list_pel)): ?>
          <option value="<?= $p['id'] ?>" <?= $filter_pel == $p['id'] ? 'selected' : '' ?>><?= htmlspecialchars($p['nama_pelatihan']) ?></option>
        <?php endwhile; ?>
      </select>
      <button class="btn btn-sm btn-outline-secondary">Filter</button>
    </form>
  </div>
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead>
        <tr><th>#</th><th>Nama Peserta</th><th>Pelatihan</th><th>Tgl Mulai</th><th>Kehadiran</th><th>Nilai</th><th>Status</th><th>Aksi</th></tr>
      </thead>
      <tbody>
      <?php $no = 1; while ($row = mysqli_fetch_assoc($data)): ?>
        <tr>
          <td><?= $no++ ?></td>
          <td><?= htmlspecialchars($row['nama_peserta']) ?><br><small class="text-muted"><?= htmlspecialchars($row['email']) ?></small></td>
          <td><?= htmlspecialchars($row['nama_pelatihan']) ?></td>
          <td><?= date('d M Y', strtotime($row['tanggal_mulai'])) ?></td>
          <td>
            <?php $kh=['hadir'=>'success','tidak_hadir'=>'danger','izin'=>'warning']; ?>
            <span class="badge bg-<?= $kh[$row['status_kehadiran']] ?? 'secondary' ?>"><?= str_replace('_',' ', $row['status_kehadiran']) ?></span>
          </td>
          <td><?= $row['nilai'] ?? '-' ?></td>
          <td>
            <?php $sl=['lulus'=>'success','tidak_lulus'=>'danger','belum_dinilai'=>'secondary']; ?>
            <span class="badge bg-<?= $sl[$row['status_lulus']] ?? 'secondary' ?>"><?= str_replace('_',' ', $row['status_lulus']) ?></span>
          </td>
          <td>
            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalNilai"
              data-id="<?= $row['id'] ?>" data-nama="<?= htmlspecialchars($row['nama_peserta']) ?>"
              data-nilai="<?= $row['nilai'] ?>" data-status="<?= $row['status_lulus'] ?>"
              data-kehadiran="<?= $row['status_kehadiran'] ?>">
              <i class="bi bi-pencil-square"></i>
            </button>
          </td>
        </tr>
      <?php endwhile; ?>
      <?php if (mysqli_num_rows($data) === 0): ?>
        <tr><td colspan="8" class="text-center text-muted py-4">Tidak ada data peserta</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal Input Nilai -->
<div class="modal fade" id="modalNilai" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title fw-semibold">Input Nilai Peserta</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="peserta_nilai.php">
        <div class="modal-body">
          <input type="hidden" name="id" id="m_id">
          <div class="mb-3">
            <label class="form-label fw-semibold">Peserta</label>
            <input type="text" id="m_nama" class="form-control" readonly>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Kehadiran</label>
            <select name="status_kehadiran" id="m_kehadiran" class="form-select">
              <option value="hadir">Hadir</option>
              <option value="tidak_hadir">Tidak Hadir</option>
              <option value="izin">Izin</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Nilai (0-100)</label>
            <input type="number" name="nilai" id="m_nilai" class="form-control" min="0" max="100" step="0.01">
          </div>
          <div class="mb-1">
            <label class="form-label fw-semibold">Status Kelulusan</label>
            <select name="status_lulus" id="m_status" class="form-select">
              <option value="belum_dinilai">Belum Dinilai</option>
              <option value="lulus">Lulus</option>
              <option value="tidak_lulus">Tidak Lulus</option>
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.getElementById('modalNilai').addEventListener('show.bs.modal', function(e) {
  var btn = e.relatedTarget;
  document.getElementById('m_id').value       = btn.dataset.id;
  document.getElementById('m_nama').value     = btn.dataset.nama;
  document.getElementById('m_nilai').value    = btn.dataset.nilai;
  document.getElementById('m_kehadiran').value = btn.dataset.kehadiran;
  document.getElementById('m_status').value   = btn.dataset.status;
});
</script>

<?php include 'footer.php'; ?>
