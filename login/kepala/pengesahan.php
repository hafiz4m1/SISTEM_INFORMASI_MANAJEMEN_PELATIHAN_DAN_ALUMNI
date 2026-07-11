<?php
$page_title = 'Pengesahan Laporan';
include 'header.php';

$pesan = isset($_GET['pesan']) ? $_GET['pesan'] : '';

// Proses setujui/tolak
if (isset($_GET['aksi']) && isset($_GET['kode'])) {
    $kode  = mysqli_real_escape_string($koneksi, $_GET['kode']);
    $aksi  = $_GET['aksi'];
    $kpl   = mysqli_fetch_assoc(mysqli_query($koneksi,
        "SELECT id FROM kepala WHERE user_id={$_SESSION['id_login']}"));

    if ($kpl) {
        if ($aksi === 'setujui') {
            mysqli_query($koneksi, "UPDATE persetujuan_laporan SET
                status='diterima', kepala_id={$kpl['id']}, tgl_diterima=NOW()
                WHERE kode_laporan='$kode'");
            header("location: pengesahan.php?pesan=Laporan berhasil disahkan."); exit;
        } elseif ($aksi === 'tolak') {
            $catatan = mysqli_real_escape_string($koneksi, $_POST['catatan'] ?? '');
            mysqli_query($koneksi, "UPDATE persetujuan_laporan SET
                status='ditolak', kepala_id={$kpl['id']}, tgl_diterima=NOW(), catatan='$catatan'
                WHERE kode_laporan='$kode'");
            header("location: pengesahan.php?pesan=Laporan ditolak."); exit;
        }
    }
}

$filter = isset($_GET['filter']) ? $_GET['filter'] : 'menunggu';
if (!in_array($filter, ['menunggu','diterima','ditolak','semua'])) $filter = 'menunggu';
$where  = $filter !== 'semua' ? "WHERE pl.status='$filter'" : '';

$judul = [
    'pelatihan'=>'Laporan Pelatihan','peserta'=>'Laporan Peserta',
    'alumni'=>'Laporan Alumni','tracer'=>'Laporan Tracer Study',
    'rktl'=>'Laporan RKTL','rekomendasi'=>'Laporan Rekomendasi','kelulusan'=>'Laporan Kelulusan',
];

$data = mysqli_query($koneksi, "
    SELECT pl.*, u.name as nama_pembuat,
        k.nama_lengkap as nama_kepala
    FROM persetujuan_laporan pl
    LEFT JOIN users u ON pl.dibuat_oleh=u.id
    LEFT JOIN kepala k ON pl.kepala_id=k.id
    $where
    ORDER BY pl.created_at DESC
");

$jml_menunggu = mysqli_fetch_row(mysqli_query($koneksi,
    "SELECT COUNT(*) FROM persetujuan_laporan WHERE status='menunggu'"))[0];
?>

<?php if ($pesan): ?>
  <div class="alert alert-success alert-dismissible fade show">
    <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($pesan) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>

<?php if ($jml_menunggu > 0): ?>
  <div class="alert d-flex align-items-center gap-3 mb-3"
       style="background:#fff3cd;border:1px solid #ffc107;border-radius:10px;padding:12px 16px">
    <i class="bi bi-hourglass-split text-warning fs-5"></i>
    <div><strong><?= $jml_menunggu ?> laporan menunggu pengesahan Anda.</strong></div>
  </div>
<?php endif; ?>

<!-- Filter Tab -->
<ul class="nav nav-tabs mb-0">
  <?php foreach (['menunggu'=>'Menunggu','diterima'=>'Disahkan','ditolak'=>'Ditolak','semua'=>'Semua'] as $k=>$v): ?>
    <li class="nav-item">
      <a class="nav-link <?= $filter===$k?'active':'' ?>" href="pengesahan.php?filter=<?= $k ?>">
        <?= $v ?>
        <?php if ($k==='menunggu' && $jml_menunggu > 0): ?>
          <span class="badge bg-danger ms-1"><?= $jml_menunggu ?></span>
        <?php endif; ?>
      </a>
    </li>
  <?php endforeach; ?>
</ul>

<div class="card" style="border-radius:0 0 12px 12px">
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead>
        <tr><th>#</th><th>Jenis Laporan</th><th>Periode</th><th>Dibuat Oleh</th><th>Tgl Dibuat</th><th>Status</th><th>Tgl Disahkan</th><th>Aksi</th></tr>
      </thead>
      <tbody>
      <?php $no=1; while ($row = mysqli_fetch_assoc($data)): ?>
        <tr>
          <td><?= $no++ ?></td>
          <td><strong><?= htmlspecialchars($judul[$row['jenis']] ?? $row['jenis']) ?></strong></td>
          <td style="font-size:12px">
            <?= date('d M Y', strtotime($row['periode_dari'])) ?> -
            <?= date('d M Y', strtotime($row['periode_sampai'])) ?>
          </td>
          <td><?= htmlspecialchars($row['nama_pembuat'] ?? '-') ?></td>
          <td><?= date('d M Y H:i', strtotime($row['created_at'])) ?></td>
          <td>
            <?php
            $sb = ['menunggu'=>'warning','diterima'=>'success','ditolak'=>'danger'];
            $sl = ['menunggu'=>'Menunggu','diterima'=>'Disahkan','ditolak'=>'Ditolak'];
            ?>
            <span class="badge bg-<?= $sb[$row['status']] ?>"><?= $sl[$row['status']] ?></span>
          </td>
          <td><?= $row['tgl_diterima'] ? date('d M Y H:i', strtotime($row['tgl_diterima'])) : '-' ?></td>
          <td>
            <?php if ($row['status'] === 'menunggu'): ?>
              <a href="pengesahan.php?aksi=setujui&kode=<?= $row['kode_laporan'] ?>&filter=<?= $filter ?>"
                 class="btn btn-sm btn-success mb-1"
                 onclick="return confirm('Sahkan laporan ini?')">
                <i class="bi bi-check-lg"></i> Sahkan
              </a>
              <button class="btn btn-sm btn-danger"
                      data-bs-toggle="modal" data-bs-target="#modalTolak"
                      data-kode="<?= $row['kode_laporan'] ?>">
                <i class="bi bi-x-lg"></i> Tolak
              </button>
            <?php elseif ($row['status'] === 'diterima'): ?>
              <a href="../admin/laporan_cetak.php?jenis=<?= $row['jenis'] ?>&dari=<?= $row['periode_dari'] ?>&sampai=<?= $row['periode_sampai'] ?>"
                 target="_blank" class="btn btn-sm btn-outline-primary">
                <i class="bi bi-printer"></i> Cetak
              </a>
            <?php else: ?>
              <span class="text-muted" style="font-size:12px">Ditolak</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endwhile; ?>
      <?php if (mysqli_num_rows($data) === 0): ?>
        <tr><td colspan="8" class="text-center text-muted py-4">Tidak ada data pengesahan</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<!-- Modal Tolak -->
<div class="modal fade" id="modalTolak" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title fw-semibold text-danger"><i class="bi bi-x-circle me-2"></i>Tolak Laporan</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" id="formTolak">
        <div class="modal-body">
          <label class="form-label fw-semibold" style="font-size:13px">Alasan Penolakan</label>
          <textarea name="catatan" class="form-control" rows="3"
                    placeholder="Tuliskan alasan penolakan..." required></textarea>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-danger">Tolak Laporan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.getElementById('modalTolak').addEventListener('show.bs.modal', function(e) {
  const kode = e.relatedTarget.dataset.kode;
  document.getElementById('formTolak').action =
    'pengesahan.php?aksi=tolak&kode=' + kode + '&filter=<?= $filter ?>';
});
</script>

<?php include 'footer.php'; ?>