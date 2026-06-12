<?php
$page_title = 'Detail Alumni';
include '../koneksi.php';
include 'header.php';

$id   = (int)($_GET['id'] ?? 0);
$alumni = mysqli_fetch_assoc(mysqli_query($koneksi, "
    SELECT a.*, u.name, u.email FROM alumni a JOIN users u ON a.user_id=u.id WHERE a.id=$id"));
if (!$alumni) { echo '<div class="alert alert-danger">Data tidak ditemukan.</div>'; include 'footer.php'; exit; }

$pelatihan = mysqli_query($koneksi, "
    SELECT p.nama_pelatihan, p.tanggal_mulai, pp.nilai, pp.status_lulus, pp.sertifikat_url
    FROM peserta_pelatihan pp
    JOIN pelatihan p ON pp.pelatihan_id=p.id
    WHERE pp.user_id={$alumni['user_id']}
    ORDER BY p.tanggal_mulai DESC");

$tracer = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM tracer_study WHERE alumni_id=$id AND status_pengisian='sudah_diisi' ORDER BY tanggal_isi DESC LIMIT 1"));

$kompetensi = mysqli_query($koneksi, "
    SELECT k.nama_kompetensi, k.kategori, ak.sumber
    FROM alumni_kompetensi ak JOIN kompetensi k ON ak.kompetensi_id=k.id
    WHERE ak.alumni_id=$id");
?>

<div class="d-flex align-items-center gap-2 mb-3">
  <a href="alumni.php" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i></a>
  <h6 class="mb-0 fw-semibold">Detail Alumni</h6>
</div>

<div class="row g-3">
  <!-- Profil -->
  <div class="col-lg-4">
    <div class="card p-4 text-center">
      <div class="mb-3"><div style="width:72px;height:72px;background:#e8eef5;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto;font-size:30px;color:#1a4c8e"><i class="bi bi-person"></i></div></div>
      <h6 class="fw-bold mb-1"><?= htmlspecialchars($alumni['name']) ?></h6>
      <small class="text-muted"><?= htmlspecialchars($alumni['email']) ?></small>
      <hr>
      <div class="text-start" style="font-size:13px">
        <div class="mb-2"><span class="text-muted">NIK:</span> <?= htmlspecialchars($alumni['nik'] ?? '-') ?></div>
        <div class="mb-2"><span class="text-muted">Tgl Lahir:</span> <?= $alumni['tanggal_lahir'] ? date('d M Y', strtotime($alumni['tanggal_lahir'])) : '-' ?></div>
        <div class="mb-2"><span class="text-muted">Alamat:</span> <?= htmlspecialchars($alumni['alamat'] ?? '-') ?></div>
        <div class="mb-2"><span class="text-muted">Telepon:</span> <?= htmlspecialchars($alumni['telepon'] ?? '-') ?></div>
        <div><span class="text-muted">Tgl Lulus:</span> <?= $alumni['tanggal_lulus'] ? date('d M Y', strtotime($alumni['tanggal_lulus'])) : '-' ?></div>
      </div>
    </div>
  </div>

  <div class="col-lg-8">
    <!-- Riwayat Pelatihan -->
    <div class="card mb-3">
      <div class="card-header">Riwayat Pelatihan</div>
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead><tr><th>Pelatihan</th><th>Tgl Mulai</th><th>Nilai</th><th>Status</th></tr></thead>
          <tbody>
          <?php while ($p = mysqli_fetch_assoc($pelatihan)): ?>
            <tr>
              <td><?= htmlspecialchars($p['nama_pelatihan']) ?></td>
              <td><?= date('d M Y', strtotime($p['tanggal_mulai'])) ?></td>
              <td><?= $p['nilai'] ?? '-' ?></td>
              <td><span class="badge bg-<?= $p['status_lulus']==='lulus' ? 'success' : ($p['status_lulus']==='tidak_lulus' ? 'danger' : 'secondary') ?>"><?= str_replace('_',' ', $p['status_lulus']) ?></span></td>
            </tr>
          <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Tracer Study -->
    <div class="card mb-3">
      <div class="card-header">Hasil Tracer Study</div>
      <div class="card-body" style="font-size:13px">
        <?php if ($tracer): ?>
          <div class="row g-2">
            <div class="col-6"><span class="text-muted">Status Pekerjaan:</span><br><strong><?= ucfirst(str_replace('_',' ',$tracer['status_pekerjaan'] ?? '-')) ?></strong></div>
            <div class="col-6"><span class="text-muted">Perusahaan:</span><br><strong><?= htmlspecialchars($tracer['nama_perusahaan'] ?? '-') ?></strong></div>
            <div class="col-6"><span class="text-muted">Jabatan:</span><br><strong><?= htmlspecialchars($tracer['jabatan'] ?? '-') ?></strong></div>
            <div class="col-6"><span class="text-muted">Relevansi Pelatihan:</span><br><strong><?= $tracer['relevansi_pelatihan'] ?? '-' ?>/5</strong></div>
            <div class="col-12"><span class="text-muted">Saran:</span><br><?= htmlspecialchars($tracer['saran'] ?? '-') ?></div>
          </div>
        <?php else: ?>
          <p class="text-muted mb-0">Belum mengisi tracer study.</p>
        <?php endif; ?>
      </div>
    </div>

    <!-- Kompetensi -->
    <div class="card">
      <div class="card-header">Kompetensi</div>
      <div class="card-body">
        <?php $kmp_count = 0; while ($k = mysqli_fetch_assoc($kompetensi)): $kmp_count++; ?>
          <span class="badge bg-primary bg-opacity-10 text-primary me-1 mb-1 p-2"><?= htmlspecialchars($k['nama_kompetensi']) ?></span>
        <?php endwhile; ?>
        <?php if ($kmp_count === 0): ?><p class="text-muted mb-0">Belum ada data kompetensi.</p><?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php include 'footer.php'; ?>
