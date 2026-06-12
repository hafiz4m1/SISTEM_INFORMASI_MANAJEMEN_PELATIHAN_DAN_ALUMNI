<?php
ob_start();
$page_title = 'RKTL Alumni';
include '../koneksi.php';
include 'header.php';

// Proses simpan RKTL
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rktl_id        = (int)$_POST['rktl_id'];
    $rencana        = mysqli_real_escape_string($koneksi, $_POST['rencana']);
    $target_waktu   = $_POST['target_waktu'];
    $progres        = (int)$_POST['progres'];
    $status         = mysqli_real_escape_string($koneksi, $_POST['status']);
    $catatan        = mysqli_real_escape_string($koneksi, $_POST['catatan']);
    $tgl_verifikasi = date('Y-m-d');

    // Pastikan RKTL ini milik pelatihan instruktur yang login
    $cek = mysqli_fetch_row(mysqli_query($koneksi,
        "SELECT r.id FROM rktl r WHERE r.id=$rktl_id AND r.instruktur_id=$instruktur_id"));

    if ($cek) {
        mysqli_query($koneksi, "UPDATE rktl SET
            rencana='$rencana', target_waktu='$target_waktu',
            progres=$progres, status='$status',
            catatan='$catatan', tgl_verifikasi='$tgl_verifikasi'
            WHERE id=$rktl_id");
        $_SESSION['pesan'] = 'RKTL berhasil disimpan.';
        ob_end_clean();
        header("Location: rktl.php"); exit;
    }
}

$pesan  = isset($_SESSION['pesan']) ? $_SESSION['pesan'] : (isset($_GET['pesan']) ? $_GET['pesan'] : '');
if (isset($_SESSION['pesan'])) unset($_SESSION['pesan']);
$errors = [];

// Filter
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'semua';
$where_status = '';
if ($filter === 'pending')   $where_status = "AND r.status = 'belum_mulai'";
if ($filter === 'berjalan')  $where_status = "AND r.status = 'berjalan'";
if ($filter === 'selesai')   $where_status = "AND r.status = 'selesai'";
if ($filter === 'terhambat') $where_status = "AND r.status = 'terhambat'";

// Cek pendampingan yang jatuh tempo (dalam 7 hari ke depan)
$jatuh_tempo = mysqli_fetch_row(mysqli_query($koneksi,
    "SELECT COUNT(*) FROM rktl r
     WHERE r.instruktur_id=$instruktur_id
     AND r.tgl_pendampingan BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
     AND r.status != 'selesai'"))[0];

$data = mysqli_query($koneksi, "
    SELECT r.*, u.name as nama_alumni, p.nama_pelatihan, p.tanggal_selesai
    FROM rktl r
    JOIN alumni a ON r.alumni_id = a.id
    JOIN users u ON a.user_id = u.id
    JOIN pelatihan p ON r.pelatihan_id = p.id
    WHERE r.instruktur_id = $instruktur_id $where_status
    ORDER BY r.tgl_pendampingan ASC
");
?>

<?php if ($pesan): ?>
  <div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($pesan) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<?php if ($jatuh_tempo > 0): ?>
  <div class="alert d-flex align-items-center gap-3 mb-4"
       style="background:#fff3cd;border:1px solid #ffc107;border-radius:10px;padding:14px 18px">
    <i class="bi bi-alarm-fill text-warning fs-4"></i>
    <div>
      <strong><?= $jatuh_tempo ?> pendampingan RKTL jatuh tempo dalam 7 hari ke depan!</strong><br>
      <small class="text-muted">Segera lakukan pendampingan dan isi progres RKTL alumni.</small>
    </div>
  </div>
<?php endif; ?>

<!-- Filter Tab -->
<ul class="nav nav-tabs mb-3">
  <?php foreach (['semua'=>'Semua','pending'=>'Belum Mulai','berjalan'=>'Berjalan','terhambat'=>'Terhambat','selesai'=>'Selesai'] as $k=>$v): ?>
    <li class="nav-item">
      <a class="nav-link <?= $filter===$k?'active':'' ?>" href="rktl.php?filter=<?= $k ?>"><?= $v ?></a>
    </li>
  <?php endforeach; ?>
</ul>

<div class="card">
  <div class="card-header">Daftar RKTL Alumni</div>
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead>
        <tr><th>#</th><th>Alumni</th><th>Pelatihan</th><th>Tgl Pendampingan</th><th>Progres</th><th>Status</th><th>Aksi</th></tr>
      </thead>
      <tbody>
      <?php $no=1; while ($row = mysqli_fetch_assoc($data)): ?>
        <?php
        $lewat = strtotime($row['tgl_pendampingan']) < time() && $row['status'] !== 'selesai';
        $warna = $lewat ? 'table-warning' : '';
        ?>
        <tr class="<?= $warna ?>">
          <td><?= $no++ ?></td>
          <td><?= htmlspecialchars($row['nama_alumni']) ?></td>
          <td><small><?= htmlspecialchars($row['nama_pelatihan']) ?></small></td>
          <td>
            <?= $row['tgl_pendampingan'] ? date('d M Y', strtotime($row['tgl_pendampingan'])) : '-' ?>
            <?php if ($lewat): ?><br><small class="text-danger"><i class="bi bi-exclamation-circle"></i> Jatuh tempo</small><?php endif; ?>
          </td>
          <td>
            <div class="progress" style="height:8px;width:80px;border-radius:4px">
              <div class="progress-bar <?= $row['progres']>=100?'bg-success':($row['progres']>=50?'bg-primary':'bg-warning') ?>"
                   style="width:<?= $row['progres'] ?>%"></div>
            </div>
            <small class="text-muted"><?= $row['progres'] ?>%</small>
          </td>
          <td>
            <?php
            $sb = ['belum_mulai'=>'secondary','berjalan'=>'primary','selesai'=>'success','terhambat'=>'danger'];
            $sl = ['belum_mulai'=>'Belum Mulai','berjalan'=>'Berjalan','selesai'=>'Selesai','terhambat'=>'Terhambat'];
            ?>
            <span class="badge bg-<?= $sb[$row['status']]??'secondary' ?>"><?= $sl[$row['status']]??'-' ?></span>
          </td>
          <td>
            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalRktl"
              data-id="<?= $row['id'] ?>"
              data-nama="<?= htmlspecialchars($row['nama_alumni']) ?>"
              data-pelatihan="<?= htmlspecialchars($row['nama_pelatihan']) ?>"
              data-rencana="<?= htmlspecialchars($row['rencana']) ?>"
              data-target="<?= $row['target_waktu'] ?>"
              data-progres="<?= $row['progres'] ?>"
              data-status="<?= $row['status'] ?>"
              data-catatan="<?= htmlspecialchars($row['catatan'] ?? '') ?>">
              <i class="bi bi-pencil-square"></i> Isi RKTL
            </button>
          </td>
        </tr>
      <?php endwhile; ?>
      <?php if (mysqli_num_rows($data) === 0): ?>
        <tr><td colspan="7" class="text-center text-muted py-4">Tidak ada data RKTL</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal Isi RKTL -->
<div class="modal fade" id="modalRktl" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title fw-semibold">Isi / Update RKTL</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST">
        <div class="modal-body">
          <input type="hidden" name="rktl_id" id="m_id">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label fw-semibold">Alumni</label>
              <input type="text" id="m_nama" class="form-control" readonly>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-semibold">Pelatihan</label>
              <input type="text" id="m_pelatihan" class="form-control" readonly>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Rencana Kerja Tindak Lanjut <span class="text-danger">*</span></label>
              <textarea name="rencana" id="m_rencana" class="form-control" rows="4"
                placeholder="Tuliskan rencana kerja yang akan/sudah dilakukan alumni..."></textarea>
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Target Waktu</label>
              <input type="date" name="target_waktu" id="m_target" class="form-control">
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Progres (%)</label>
              <input type="number" name="progres" id="m_progres" class="form-control" min="0" max="100">
            </div>
            <div class="col-md-4">
              <label class="form-label fw-semibold">Status</label>
              <select name="status" id="m_status" class="form-select">
                <option value="belum_mulai">Belum Mulai</option>
                <option value="berjalan">Berjalan</option>
                <option value="selesai">Selesai</option>
                <option value="terhambat">Terhambat</option>
              </select>
            </div>
            <div class="col-12">
              <label class="form-label fw-semibold">Catatan Pendampingan</label>
              <textarea name="catatan" id="m_catatan" class="form-control" rows="3"
                placeholder="Catatan hasil pendampingan, hambatan, atau rekomendasi..."></textarea>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan RKTL</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.getElementById('modalRktl').addEventListener('show.bs.modal', function(e) {
  const btn = e.relatedTarget;
  document.getElementById('m_id').value        = btn.dataset.id;
  document.getElementById('m_nama').value      = btn.dataset.nama;
  document.getElementById('m_pelatihan').value = btn.dataset.pelatihan;
  document.getElementById('m_rencana').value   = btn.dataset.rencana;
  document.getElementById('m_target').value    = btn.dataset.target;
  document.getElementById('m_progres').value   = btn.dataset.progres;
  document.getElementById('m_status').value    = btn.dataset.status;
  document.getElementById('m_catatan').value   = btn.dataset.catatan;
});
</script>

<?php ob_end_flush(); include 'footer.php'; ?>
