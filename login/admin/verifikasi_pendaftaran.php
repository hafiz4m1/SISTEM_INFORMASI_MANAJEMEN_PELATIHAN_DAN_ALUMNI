<?php
ob_start();
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['level'] !== 'admin') {
    header("location: ../login.php"); exit;
}
include '../koneksi.php';
include '../email_helper.php';

$pesan = isset($_GET['pesan']) ? $_GET['pesan'] : '';

// Handler untuk terima semua
if (isset($_GET['aksi']) && $_GET['aksi'] === 'terima_semua') {
    $admin = $_SESSION['id_login'];
    
    // Set timeout lebih lama untuk email
    set_time_limit(300);
    
    // Ambil semua pendaftaran yang menunggu
    $pending = mysqli_query($koneksi, "
        SELECT pp.*, u.name as nama_peserta, u.email as email_peserta,
            p.nama_pelatihan, p.lokasi,
            DATE_FORMAT(p.tanggal_mulai, '%d %M %Y') as tgl_mulai_fmt
        FROM peserta_pelatihan pp
        JOIN users u ON pp.user_id=u.id
        JOIN pelatihan p ON pp.pelatihan_id=p.id
        WHERE pp.status_verifikasi='menunggu'
    ");
    
    $count = 0;
    while ($pp = mysqli_fetch_assoc($pending)) {
        mysqli_query($koneksi, "UPDATE peserta_pelatihan SET
            status_verifikasi='diterima', tgl_verifikasi=NOW(), diverifikasi_oleh=$admin
            WHERE id={$pp['id']}");
        notifVerifikasiDiterima(
            $pp['email_peserta'], $pp['nama_peserta'],
            $pp['nama_pelatihan'], $pp['tgl_mulai_fmt'],
            $pp['lokasi'] ?? 'BPPMDDTT Banjarmasin'
        );
        $count++;
    }
    
    header("location: verifikasi_pendaftaran.php?pesan=Berhasil menerima $count pendaftaran. Email notifikasi terkirim."); exit;
}

if (isset($_GET['aksi']) && isset($_GET['id'])) {
    $id    = (int)$_GET['id'];
    $aksi  = $_GET['aksi'];
    $admin = $_SESSION['id_login'];

    $pp = mysqli_fetch_assoc(mysqli_query($koneksi, "
        SELECT pp.*, u.name as nama_peserta, u.email as email_peserta,
            p.nama_pelatihan, p.lokasi,
            DATE_FORMAT(p.tanggal_mulai, '%d %M %Y') as tgl_mulai_fmt
        FROM peserta_pelatihan pp
        JOIN users u ON pp.user_id=u.id
        JOIN pelatihan p ON pp.pelatihan_id=p.id
        WHERE pp.id=$id
    "));

    if ($aksi === 'terima' && $pp) {
        mysqli_query($koneksi, "UPDATE peserta_pelatihan SET
            status_verifikasi='diterima', tgl_verifikasi=NOW(), diverifikasi_oleh=$admin
            WHERE id=$id");
        notifVerifikasiDiterima(
            $pp['email_peserta'], $pp['nama_peserta'],
            $pp['nama_pelatihan'], $pp['tgl_mulai_fmt'],
            $pp['lokasi'] ?? 'BPPMDDTT Banjarmasin'
        );
        header("location: verifikasi_pendaftaran.php?pesan=Pendaftaran diterima. Email notifikasi terkirim."); exit;

    } elseif ($aksi === 'tolak' && $pp) {
        $alasan = mysqli_real_escape_string($koneksi, $_POST['alasan'] ?? 'Tidak memenuhi syarat.');
        mysqli_query($koneksi, "UPDATE peserta_pelatihan SET
            status_verifikasi='ditolak', alasan_tolak='$alasan',
            tgl_verifikasi=NOW(), diverifikasi_oleh=$admin
            WHERE id=$id");
        notifVerifikasiDitolak($pp['email_peserta'], $pp['nama_peserta'], $pp['nama_pelatihan'], $alasan);
        header("location: verifikasi_pendaftaran.php?pesan=Pendaftaran ditolak. Email notifikasi terkirim."); exit;
    }
}

$filter = isset($_GET['filter']) ? $_GET['filter'] : 'menunggu';
if (!in_array($filter, ['menunggu','diterima','ditolak','semua'])) $filter = 'menunggu';
$where  = $filter !== 'semua' ? "WHERE pp.status_verifikasi='$filter'" : '';

$data = mysqli_query($koneksi, "
    SELECT pp.*, u.name as nama_peserta, u.email,
        p.nama_pelatihan, p.tanggal_mulai, p.kuota,
        (SELECT COUNT(*) FROM peserta_pelatihan WHERE pelatihan_id=p.id AND status_verifikasi='diterima') as jml_diterima,
        uv.name as nama_verifikator
    FROM peserta_pelatihan pp
    JOIN users u ON pp.user_id=u.id
    JOIN pelatihan p ON pp.pelatihan_id=p.id
    LEFT JOIN users uv ON pp.diverifikasi_oleh=uv.id
    $where
    ORDER BY pp.tanggal_daftar DESC
");

$jml_menunggu = mysqli_fetch_row(mysqli_query($koneksi,
    "SELECT COUNT(*) FROM peserta_pelatihan WHERE status_verifikasi='menunggu'"))[0];

$page_title = 'Verifikasi Pendaftaran';
include 'header.php';
?>

<?php if ($pesan): ?>
  <div class="alert alert-success alert-dismissible fade show">
    <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($pesan) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>

<?php if ($jml_menunggu > 0): ?>
  <div class="alert d-flex align-items-center gap-3 mb-3" style="background:#fff3cd;border:1px solid #ffc107;border-radius:10px;padding:12px 16px">
    <i class="bi bi-hourglass-split text-warning fs-5"></i>
    <div style="flex:1"><strong><?= $jml_menunggu ?> pendaftaran menunggu verifikasi.</strong></div>
    <div class="btn btn-sm btn-success" 
       onclick="if(confirm('Proses ini mungkin membutuhkan waktu 1-2 menit untuk mengirim email ke semua peserta.\n\nLanjutkan?')) { document.getElementById('loading-overlay').style.display='flex'; window.location.href='verifikasi_pendaftaran.php?aksi=terima_semua&filter=menunggu'; }">
      <i class="bi bi-check-lg me-1"></i>Terima Semua
    </div>
  </div>
<?php endif; ?>

<ul class="nav nav-tabs mb-0">
  <?php foreach (['menunggu'=>'Menunggu','diterima'=>'Diterima','ditolak'=>'Ditolak','semua'=>'Semua'] as $k=>$v): ?>
    <li class="nav-item">
      <a class="nav-link <?= $filter===$k?'active':'' ?>" href="verifikasi_pendaftaran.php?filter=<?= $k ?>">
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
        <tr><th>#</th><th>Peserta</th><th>Pelatihan</th><th>Tgl Daftar</th><th>Kuota</th><th>Status</th><th>Verifikator</th><th>Aksi</th></tr>
      </thead>
      <tbody>
      <?php $no=1; while ($row = mysqli_fetch_assoc($data)): ?>
        <tr>
          <td><?= $no++ ?></td>
          <td><strong><?= htmlspecialchars($row['nama_peserta']) ?></strong><br><small class="text-muted"><?= htmlspecialchars($row['email']) ?></small></td>
          <td><?= htmlspecialchars($row['nama_pelatihan']) ?><br><small class="text-muted"><?= date('d M Y', strtotime($row['tanggal_mulai'])) ?></small></td>
          <td><?= date('d M Y H:i', strtotime($row['tanggal_daftar'])) ?></td>
          <td><span class="badge bg-secondary"><?= $row['jml_diterima'] ?>/<?= $row['kuota'] ?></span></td>
          <td>
            <?php $sb=['menunggu'=>'warning','diterima'=>'success','ditolak'=>'danger'];
                  $sl=['menunggu'=>'Menunggu','diterima'=>'Diterima','ditolak'=>'Ditolak']; ?>
            <span class="badge bg-<?= $sb[$row['status_verifikasi']] ?>"><?= $sl[$row['status_verifikasi']] ?></span>
            <?php if ($row['alasan_tolak']): ?><br><small class="text-muted"><?= htmlspecialchars($row['alasan_tolak']) ?></small><?php endif; ?>
          </td>
          <td><small><?= $row['nama_verifikator'] ? htmlspecialchars($row['nama_verifikator']) : '-' ?></small></td>
          <td>
            <?php if ($row['status_verifikasi'] === 'menunggu'): ?>
              <a href="verifikasi_pendaftaran.php?aksi=terima&id=<?= $row['id'] ?>&filter=<?= $filter ?>"
                 class="btn btn-sm btn-success mb-1"
                 onclick="return confirm('Terima dan kirim email ke <?= htmlspecialchars($row['nama_peserta']) ?>?')">
                <i class="bi bi-check-lg"></i> Terima
              </a>
              <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#modalTolak"
                      data-id="<?= $row['id'] ?>" data-nama="<?= htmlspecialchars($row['nama_peserta']) ?>">
                <i class="bi bi-x-lg"></i> Tolak
              </button>
            <?php else: ?>
              <span class="text-muted" style="font-size:12px">Sudah diproses</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endwhile; ?>
      <?php if (mysqli_num_rows($data) === 0): ?>
        <tr><td colspan="8" class="text-center text-muted py-4">Tidak ada data pendaftaran</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="modal fade" id="modalTolak" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h6 class="modal-title fw-semibold text-danger"><i class="bi bi-x-circle me-2"></i>Tolak Pendaftaran</h6>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" id="formTolak">
        <div class="modal-body">
          <p style="font-size:13px">Tolak pendaftaran <strong id="m_nama"></strong>? Email notifikasi akan dikirim otomatis.</p>
          <label class="form-label fw-semibold" style="font-size:13px">Alasan Penolakan</label>
          <textarea name="alasan" class="form-control" rows="3" placeholder="Tuliskan alasan..." required></textarea>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-danger">Tolak & Kirim Email</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.getElementById('modalTolak').addEventListener('show.bs.modal', function(e) {
  const btn = e.relatedTarget;
  document.getElementById('m_nama').textContent = btn.dataset.nama;
  document.getElementById('formTolak').action = 'verifikasi_pendaftaran.php?aksi=tolak&id=' + btn.dataset.id + '&filter=<?= $filter ?>';
});
</script>

<?php include 'footer.php'; ?>