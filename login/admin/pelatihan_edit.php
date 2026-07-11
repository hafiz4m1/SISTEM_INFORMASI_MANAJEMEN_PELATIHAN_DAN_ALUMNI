<?php
ob_start();
$page_title = 'Edit Pelatihan';
include '../koneksi.php';
include 'header.php';

$id   = (int)($_GET['id'] ?? 0);
$data = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM pelatihan WHERE id=$id"));
if (!$data) { echo '<div class="alert alert-danger">Data tidak ditemukan.</div>'; include 'footer.php'; exit; }

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama      = mysqli_real_escape_string($koneksi, $_POST['nama_pelatihan']);
    $jenis     = mysqli_real_escape_string($koneksi, $_POST['jenis']);
    $desc      = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
    $tgl_mulai = mysqli_real_escape_string($koneksi, $_POST['tanggal_mulai']);
    $tgl_akhir = mysqli_real_escape_string($koneksi, $_POST['tanggal_selesai']);
    $kuota     = (int)$_POST['kuota'];
    $instr_id  = (int)$_POST['instruktur_id'];
    $lokasi    = mysqli_real_escape_string($koneksi, $_POST['lokasi']);
    $status    = mysqli_real_escape_string($koneksi, $_POST['status']);

    if (!$nama) $errors[] = 'Nama pelatihan wajib diisi.';
    if ($tgl_akhir < $tgl_mulai) $errors[] = 'Tanggal selesai tidak boleh sebelum tanggal mulai.';

    if (!$errors) {
        mysqli_query($koneksi, "UPDATE pelatihan SET
            nama_pelatihan='$nama', jenis='$jenis', deskripsi='$desc',
            tanggal_mulai='$tgl_mulai', tanggal_selesai='$tgl_akhir',
            kuota=$kuota, instruktur_id=$instr_id, lokasi='$lokasi', status='$status'
            WHERE id=$id");
        ob_end_clean();
        header("Location: pelatihan.php?pesan=Pelatihan berhasil diperbarui.");
        exit;
    }
    // Isi ulang dari POST jika ada error
    $data = array_merge($data, $_POST);
}

$instruktur = mysqli_query($koneksi, "SELECT i.id, u.name FROM instruktur i JOIN users u ON i.user_id=u.id ORDER BY u.name");
?>

<div class="d-flex align-items-center gap-2 mb-3">
  <a href="pelatihan.php" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
  <h6 class="mb-0 fw-semibold">Edit Pelatihan</h6>
</div>

<?php if ($errors): ?>
  <div class="alert alert-danger"><ul class="mb-0 ps-3"><?php foreach($errors as $e) echo "<li>$e</li>"; ?></ul></div>
<?php endif; ?>

<div class="card">
  <div class="card-body p-4">
    <form method="POST">
      <div class="row g-3">
        <div class="col-md-8">
          <label class="form-label fw-semibold">Nama Pelatihan</label>
          <input type="text" name="nama_pelatihan" class="form-control" value="<?= htmlspecialchars($data['nama_pelatihan']) ?>" required>
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Jenis</label>
          <input type="text" name="jenis" class="form-control" value="<?= htmlspecialchars($data['jenis'] ?? '') ?>">
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Tanggal Mulai</label>
          <input type="date" name="tanggal_mulai" class="form-control" value="<?= htmlspecialchars($data['tanggal_mulai']) ?>" required>
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Tanggal Selesai</label>
          <input type="date" name="tanggal_selesai" class="form-control" value="<?= htmlspecialchars($data['tanggal_selesai']) ?>" required>
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Kuota</label>
          <input type="number" name="kuota" class="form-control" value="<?= (int)$data['kuota'] ?>" min="1" required>
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Instruktur</label>
          <select name="instruktur_id" class="form-select" required>
            <option value="">-- Pilih --</option>
            <?php while ($i = mysqli_fetch_assoc($instruktur)): ?>
              <option value="<?= $i['id'] ?>" <?= $data['instruktur_id'] == $i['id'] ? 'selected' : '' ?>><?= htmlspecialchars($i['name']) ?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="col-md-4">
          <label class="form-label fw-semibold">Lokasi</label>
          <input type="text" name="lokasi" class="form-control" value="<?= htmlspecialchars($data['lokasi'] ?? '') ?>">
        </div>
        <div class="col-md-2">
          <label class="form-label fw-semibold">Status</label>
          <select name="status" class="form-select">
            <?php foreach(['aktif','selesai','dibatalkan'] as $s): ?>
              <option value="<?= $s ?>" <?= $data['status']===$s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-12">
          <label class="form-label fw-semibold">Deskripsi</label>
          <textarea name="deskripsi" class="form-control" rows="3"><?= htmlspecialchars($data['deskripsi'] ?? '') ?></textarea>
        </div>
        <div class="col-12 d-flex gap-2">
          <button type="submit" class="btn btn-primary px-4">Simpan Perubahan</button>
          <a href="pelatihan.php" class="btn btn-outline-secondary">Batal</a>
        </div>
      </div>
    </form>
  </div>
</div>

<?php ob_end_flush(); include 'footer.php'; ?>