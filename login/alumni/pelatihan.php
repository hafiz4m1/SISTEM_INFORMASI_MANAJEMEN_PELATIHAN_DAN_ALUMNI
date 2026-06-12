<?php
// Proses POST harus di paling atas sebelum ada output HTML
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['level'] !== 'instruktur') {
    header("location: ../login.php"); exit;
}
include '../koneksi.php';

// Ambil instruktur_id
$instruktur = mysqli_fetch_assoc(mysqli_query($koneksi,
    "SELECT * FROM instruktur WHERE user_id={$_SESSION['id_login']}"));
$instruktur_id = $instruktur['id'] ?? 0;

// Proses simpan nilai
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pp_id     = (int)$_POST['pp_id'];
    $nilai     = mysqli_real_escape_string($koneksi, $_POST['nilai']);
    $kehadiran = mysqli_real_escape_string($koneksi, $_POST['status_kehadiran']);
    $status    = mysqli_real_escape_string($koneksi, $_POST['status_lulus']);

    // Pastikan peserta ini milik pelatihan instruktur yang login
    $cek = mysqli_fetch_row(mysqli_query($koneksi, "
        SELECT pp.id FROM peserta_pelatihan pp
        JOIN pelatihan p ON pp.pelatihan_id=p.id
        WHERE pp.id=$pp_id AND p.instruktur_id=$instruktur_id
    "));

    if ($cek) {
        mysqli_query($koneksi, "UPDATE peserta_pelatihan SET
            nilai='$nilai', status_kehadiran='$kehadiran', status_lulus='$status'
            WHERE id=$pp_id");

        // =====================================================
        // KIRIM EMAIL NOTIFIKASI NILAI KE PESERTA/ALUMNI
        // =====================================================
        if ($status !== 'belum_dinilai') {
            include_once '../email_helper.php';
            $user_data = mysqli_fetch_assoc(mysqli_query($koneksi,
                "SELECT u.email, u.name, p.nama_pelatihan
                 FROM peserta_pelatihan pp
                 JOIN users u ON pp.user_id=u.id
                 JOIN pelatihan p ON pp.pelatihan_id=p.id
                 WHERE pp.id=$pp_id"));
            if ($user_data) {
                notifNilaiLulus(
                    $user_data['email'],
                    $user_data['name'],
                    $user_data['nama_pelatihan'],
                    (float)$nilai,
                    $status
                );
            }
        }
        // =====================================================

        // =====================================================
        // OTOMATIS JADIKAN ALUMNI JIKA STATUS LULUS
        // =====================================================
        if ($status === 'lulus') {
            $pp = mysqli_fetch_assoc(mysqli_query($koneksi,
                "SELECT pp.user_id, pp.pelatihan_id, p.tanggal_selesai, p.instruktur_id
                 FROM peserta_pelatihan pp
                 JOIN pelatihan p ON pp.pelatihan_id=p.id
                 WHERE pp.id=$pp_id"));

            if ($pp) {
                $user_id       = $pp['user_id'];
                $pel_id        = $pp['pelatihan_id'];
                $tgl_lulus     = $pp['tanggal_selesai'];
                $instr_id      = $pp['instruktur_id'];

                // Buat atau ambil alumni_id
                $cek_alumni = mysqli_fetch_row(mysqli_query($koneksi,
                    "SELECT id FROM alumni WHERE user_id=$user_id"));
                if (!$cek_alumni) {
                    mysqli_query($koneksi, "INSERT INTO alumni (user_id, tanggal_lulus)
                        VALUES ($user_id, '$tgl_lulus')");
                    $alumni_id = mysqli_insert_id($koneksi);
                    mysqli_query($koneksi, "UPDATE users SET role='alumni' WHERE id=$user_id");
                } else {
                    $alumni_id = $cek_alumni[0];
                }

                // Buat RKTL jika belum ada
                $tgl_pendampingan = date('Y-m-d', strtotime($tgl_lulus . ' +3 months'));
                $cek_rktl = mysqli_fetch_row(mysqli_query($koneksi,
                    "SELECT id FROM rktl WHERE alumni_id=$alumni_id AND pelatihan_id=$pel_id"));
                if (!$cek_rktl) {
                    mysqli_query($koneksi, "INSERT INTO rktl
                        (alumni_id, pelatihan_id, instruktur_id, rencana, tgl_pendampingan, status)
                        VALUES ($alumni_id, $pel_id, $instr_id, 'Belum diisi', '$tgl_pendampingan', 'belum_mulai')");
                }

                // Buat tracer study jika belum ada
                $cek_tracer = mysqli_fetch_row(mysqli_query($koneksi,
                    "SELECT id FROM tracer_study WHERE alumni_id=$alumni_id"));
                if (!$cek_tracer) {
                    mysqli_query($koneksi, "INSERT INTO tracer_study (alumni_id, status_pengisian)
                        VALUES ($alumni_id, 'belum_diisi')");
                }
            }
        }
        // =====================================================

        $pesan_redirect = urlencode('Nilai berhasil disimpan.' . ($status === 'lulus' ? ' Peserta otomatis menjadi Alumni.' : ''));
        header("location: peserta.php?pelatihan_id={$_POST['pelatihan_id']}&pesan=$pesan_redirect");
        exit;
    }
}

// Set page title untuk header
$page_title = 'Kelola Peserta';
?>
<?php include 'header.php'; ?>

<?php $pesan = isset($_GET['pesan']) ? $_GET['pesan'] : ''; ?>
<?php if ($pesan): ?>
  <div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($pesan) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<?php
// Filter pelatihan
$filter_pid = isset($_GET['pelatihan_id']) ? (int)$_GET['pelatihan_id'] : 0;
$where = $filter_pid ? "AND pp.pelatihan_id=$filter_pid" : '';

$data = mysqli_query($koneksi, "
    SELECT pp.*, u.name as nama_peserta, u.email,
        p.nama_pelatihan, p.id as pid
    FROM peserta_pelatihan pp
    JOIN users u ON pp.user_id=u.id
    JOIN pelatihan p ON pp.pelatihan_id=p.id
    WHERE p.instruktur_id=$instruktur_id $where
    ORDER BY p.tanggal_mulai DESC, u.name ASC
");

$list_pel = mysqli_query($koneksi, "SELECT id, nama_pelatihan FROM pelatihan WHERE instruktur_id=$instruktur_id ORDER BY tanggal_mulai DESC");
?>

<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
    <span>Daftar Peserta</span>
    <form class="d-flex gap-2" method="GET">
      <select name="pelatihan_id" class="form-select form-select-sm" style="min-width:220px">
        <option value="">Semua Pelatihan Saya</option>
        <?php while ($p = mysqli_fetch_assoc($list_pel)): ?>
          <option value="<?= $p['id'] ?>" <?= $filter_pid==$p['id']?'selected':'' ?>><?= htmlspecialchars($p['nama_pelatihan']) ?></option>
        <?php endwhile; ?>
      </select>
      <button class="btn btn-sm btn-outline-secondary">Filter</button>
    </form>
  </div>
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead><tr><th>#</th><th>Nama Peserta</th><th>Pelatihan</th><th>Kehadiran</th><th>Nilai</th><th>Status</th><th>Aksi</th></tr></thead>
      <tbody>
      <?php $no=1; while ($row = mysqli_fetch_assoc($data)): ?>
        <tr>
          <td><?= $no++ ?></td>
          <td><?= htmlspecialchars($row['nama_peserta']) ?><br><small class="text-muted"><?= htmlspecialchars($row['email']) ?></small></td>
          <td><?= htmlspecialchars($row['nama_pelatihan']) ?></td>
          <td>
            <?php $kh=['hadir'=>'success','tidak_hadir'=>'danger','izin'=>'warning']; ?>
            <span class="badge bg-<?= $kh[$row['status_kehadiran']] ?? 'secondary' ?>"><?= str_replace('_',' ',$row['status_kehadiran']) ?></span>
          </td>
          <td><?= $row['nilai'] ?? '-' ?></td>
          <td>
            <?php $sl=['lulus'=>'success','tidak_lulus'=>'danger','belum_dinilai'=>'secondary']; ?>
            <span class="badge bg-<?= $sl[$row['status_lulus']] ?? 'secondary' ?>"><?= str_replace('_',' ',$row['status_lulus']) ?></span>
          </td>
          <td>
            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalNilai"
              data-ppid="<?= $row['id'] ?>"
              data-pid="<?= $row['pid'] ?>"
              data-nama="<?= htmlspecialchars($row['nama_peserta']) ?>"
              data-nilai="<?= $row['nilai'] ?>"
              data-kehadiran="<?= $row['status_kehadiran'] ?>"
              data-status="<?= $row['status_lulus'] ?>">
              <i class="bi bi-pencil-square"></i> Nilai
            </button>
          </td>
        </tr>
      <?php endwhile; ?>
      <?php if (mysqli_num_rows($data) === 0): ?>
        <tr><td colspan="7" class="text-center text-muted py-4">Tidak ada peserta</td></tr>
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
      <form method="POST">
        <div class="modal-body">
          <input type="hidden" name="pp_id" id="m_ppid">
          <input type="hidden" name="pelatihan_id" id="m_pid">
          <div class="mb-3">
            <label class="form-label fw-semibold">Peserta</label>
            <input type="text" id="m_nama" class="form-control" readonly>
          </div>
          <div class="mb-3">
            <label class="form-label fw-semibold">Status Kehadiran</label>
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
          <button type="submit" class="btn btn-primary">Simpan Nilai</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
document.getElementById('modalNilai').addEventListener('show.bs.modal', function(e) {
  var btn = e.relatedTarget;
  document.getElementById('m_ppid').value      = btn.dataset.ppid;
  document.getElementById('m_pid').value       = btn.dataset.pid;
  document.getElementById('m_nama').value      = btn.dataset.nama;
  document.getElementById('m_nilai').value     = btn.dataset.nilai;
  document.getElementById('m_kehadiran').value = btn.dataset.kehadiran;
  document.getElementById('m_status').value    = btn.dataset.status;
});
</script>

<?php include 'footer.php'; ?>