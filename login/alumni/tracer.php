<?php
$page_title = 'Tracer Study';
include 'header.php';

$pesan = isset($_GET['pesan']) ? $_GET['pesan'] : '';

// Ambil tracer study milik alumni ini
$tracer = mysqli_fetch_assoc(mysqli_query($koneksi,
    "SELECT * FROM tracer_study WHERE alumni_id=$alumni_id ORDER BY tanggal_kirim DESC LIMIT 1"));

// Proses submit form
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $tracer) {
    $tracer_id      = (int)$_POST['tracer_id'];
    $status_kerja   = mysqli_real_escape_string($koneksi, $_POST['status_pekerjaan']);
    $perusahaan     = mysqli_real_escape_string($koneksi, $_POST['nama_perusahaan']);
    $jabatan        = mysqli_real_escape_string($koneksi, $_POST['jabatan']);
    $bidang         = mysqli_real_escape_string($koneksi, $_POST['bidang_usaha']);
    $gaji           = mysqli_real_escape_string($koneksi, $_POST['gaji_range']);
    $relevansi      = (int)$_POST['relevansi_pelatihan'];
    $tunggu         = (int)$_POST['waktu_tunggu_kerja'];
    $saran          = mysqli_real_escape_string($koneksi, $_POST['saran']);

    mysqli_query($koneksi, "UPDATE tracer_study SET
        status_pengisian='sudah_diisi',
        tanggal_isi=NOW(),
        status_pekerjaan='$status_kerja',
        nama_perusahaan='$perusahaan',
        jabatan='$jabatan',
        bidang_usaha='$bidang',
        gaji_range='$gaji',
        relevansi_pelatihan=$relevansi,
        waktu_tunggu_kerja=$tunggu,
        saran='$saran'
        WHERE id=$tracer_id AND alumni_id=$alumni_id");

    header("location: tracer.php?pesan=Tracer study berhasil disimpan. Terima kasih!"); exit;
}
?>

<?php if ($pesan): ?>
  <div class="alert alert-success alert-dismissible fade show"><?= htmlspecialchars($pesan) ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
<?php endif; ?>

<?php if (!$tracer): ?>
  <div class="card p-5 text-center">
    <i class="bi bi-clipboard-x text-muted" style="font-size:48px"></i>
    <h6 class="mt-3 mb-1">Belum Ada Tracer Study</h6>
    <p class="text-muted mb-0">Admin belum mengirimkan formulir tracer study untuk Anda.</p>
  </div>

<?php elseif ($tracer['status_pengisian'] === 'sudah_diisi'): ?>
  <!-- Sudah diisi - tampilkan hasil -->
  <div class="alert alert-success mb-4">
    <i class="bi bi-check-circle-fill me-2"></i>
    Anda sudah mengisi tracer study pada <?= date('d M Y', strtotime($tracer['tanggal_isi'])) ?>.
  </div>
  <div class="card">
    <div class="card-header">Hasil Tracer Study Anda</div>
    <div class="card-body p-4">
      <div class="row g-3" style="font-size:14px">
        <div class="col-md-6"><span class="text-muted">Status Pekerjaan:</span><br><strong><?= ucfirst(str_replace('_',' ',$tracer['status_pekerjaan'] ?? '-')) ?></strong></div>
        <div class="col-md-6"><span class="text-muted">Nama Perusahaan:</span><br><strong><?= htmlspecialchars($tracer['nama_perusahaan'] ?? '-') ?></strong></div>
        <div class="col-md-6"><span class="text-muted">Jabatan:</span><br><strong><?= htmlspecialchars($tracer['jabatan'] ?? '-') ?></strong></div>
        <div class="col-md-6"><span class="text-muted">Bidang Usaha:</span><br><strong><?= htmlspecialchars($tracer['bidang_usaha'] ?? '-') ?></strong></div>
        <div class="col-md-4"><span class="text-muted">Range Gaji:</span><br><strong><?= htmlspecialchars($tracer['gaji_range'] ?? '-') ?></strong></div>
        <div class="col-md-4"><span class="text-muted">Relevansi Pelatihan:</span><br>
          <strong><?= $tracer['relevansi_pelatihan'] ?? '-' ?>/5</strong>
          <?php for ($i=1; $i<=5; $i++): ?>
            <i class="bi bi-star<?= $i <= ($tracer['relevansi_pelatihan'] ?? 0) ? '-fill text-warning' : ' text-muted' ?>" style="font-size:14px"></i>
          <?php endfor; ?>
        </div>
        <div class="col-md-4"><span class="text-muted">Waktu Tunggu Kerja:</span><br><strong><?= $tracer['waktu_tunggu_kerja'] ?? '-' ?> bulan</strong></div>
        <div class="col-12"><span class="text-muted">Saran:</span><br><?= htmlspecialchars($tracer['saran'] ?? '-') ?></div>
      </div>
    </div>
  </div>

<?php else: ?>
  <!-- Form isi tracer -->
  <div class="card">
    <div class="card-header">Form Tracer Study</div>
    <div class="card-body p-4">
      <p class="text-muted mb-4" style="font-size:13px">Mohon isi formulir ini dengan jujur. Data Anda membantu BPPMDDTT meningkatkan kualitas pelatihan.</p>
      <form method="POST">
        <input type="hidden" name="tracer_id" value="<?= $tracer['id'] ?>">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label fw-semibold">Status Pekerjaan Saat Ini <span class="text-danger">*</span></label>
            <select name="status_pekerjaan" class="form-select" id="statusKerja" required>
              <option value="">-- Pilih --</option>
              <option value="bekerja">Bekerja</option>
              <option value="wirausaha">Wirausaha / Mandiri</option>
              <option value="belum_bekerja">Belum Bekerja</option>
              <option value="melanjutkan_studi">Melanjutkan Studi</option>
            </select>
          </div>
          <div class="col-md-6" id="fieldTunggu">
            <label class="form-label fw-semibold">Waktu Tunggu Mendapat Kerja (bulan)</label>
            <input type="number" name="waktu_tunggu_kerja" class="form-control" min="0" max="60" placeholder="0 = langsung bekerja">
          </div>
          <div id="fieldPekerjaan">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label fw-semibold">Nama Perusahaan / Usaha</label>
                <input type="text" name="nama_perusahaan" class="form-control">
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Jabatan / Posisi</label>
                <input type="text" name="jabatan" class="form-control">
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Bidang Usaha / Industri</label>
                <input type="text" name="bidang_usaha" class="form-control">
              </div>
              <div class="col-md-6">
                <label class="form-label fw-semibold">Range Gaji</label>
                <select name="gaji_range" class="form-select">
                  <option value="">-- Pilih --</option>
                  <option value="< 2 juta">Di bawah Rp 2.000.000</option>
                  <option value="2-4 juta">Rp 2.000.000 - 4.000.000</option>
                  <option value="4-6 juta">Rp 4.000.000 - 6.000.000</option>
                  <option value="6-10 juta">Rp 6.000.000 - 10.000.000</option>
                  <option value="> 10 juta">Di atas Rp 10.000.000</option>
                </select>
              </div>
            </div>
          </div>
          <div class="col-12">
            <label class="form-label fw-semibold">Seberapa relevan pelatihan dengan pekerjaan Anda? <span class="text-danger">*</span></label>
            <div class="d-flex gap-2 mt-1">
              <?php for ($i=1; $i<=5; $i++): ?>
                <div class="form-check form-check-inline">
                  <input class="form-check-input" type="radio" name="relevansi_pelatihan" value="<?= $i ?>" id="rel<?= $i ?>" required>
                  <label class="form-check-label" for="rel<?= $i ?>"><?= $i ?></label>
                </div>
              <?php endfor; ?>
              <small class="text-muted align-self-center">(1 = Tidak relevan, 5 = Sangat relevan)</small>
            </div>
          </div>
          <div class="col-12">
            <label class="form-label fw-semibold">Saran untuk BPPMDDTT</label>
            <textarea name="saran" class="form-control" rows="3" placeholder="Saran, masukan, atau harapan Anda..."></textarea>
          </div>
          <div class="col-12">
            <button type="submit" class="btn btn-success px-4">Kirim Tracer Study</button>
          </div>
        </div>
      </form>
    </div>
  </div>
<?php endif; ?>

<script>
// Sembunyikan field pekerjaan jika status belum bekerja / studi
const statusKerja = document.getElementById('statusKerja');
const fieldPekerjaan = document.getElementById('fieldPekerjaan');
if (statusKerja) {
  statusKerja.addEventListener('change', function() {
    const hide = ['belum_bekerja','melanjutkan_studi'].includes(this.value);
    fieldPekerjaan.style.display = hide ? 'none' : 'block';
  });
}
</script>

<?php include 'footer.php'; ?>
