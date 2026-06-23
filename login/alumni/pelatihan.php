<?php
$page_title = 'Pelatihan';
include 'header.php';

$uid = $_SESSION['id_login'];
$pesan = isset($_GET['pesan']) ? $_GET['pesan'] : '';
$tab = isset($_GET['tab']) ? $_GET['tab'] : 'tersedia';

// Proses daftar pelatihan
if (isset($_GET['daftar'])) {
    $pid = (int)$_GET['daftar'];
    $pel = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM pelatihan WHERE id=$pid AND status='aktif'"));
    $jml = mysqli_fetch_row(mysqli_query($koneksi, "SELECT COUNT(*) FROM peserta_pelatihan WHERE pelatihan_id=$pid AND status_verifikasi!='ditolak'"))[0];
    $cek = mysqli_fetch_row(mysqli_query($koneksi, "SELECT id FROM peserta_pelatihan WHERE user_id=$uid AND pelatihan_id=$pid"));

    if (!$pel) {
        $pesan = 'Pelatihan tidak ditemukan atau sudah tidak aktif.';
    } elseif ($cek) {
        $pesan = 'Anda sudah terdaftar di pelatihan ini.';
    } elseif ($jml >= $pel['kuota']) {
        $pesan = 'Maaf, kuota pelatihan ini sudah penuh.';
    } else {
        mysqli_query($koneksi, "INSERT INTO peserta_pelatihan (user_id, pelatihan_id, tanggal_daftar, status_verifikasi) VALUES ($uid, $pid, NOW(), 'menunggu')");
        $pesan = 'Berhasil mendaftar! Menunggu verifikasi admin.';
    }
    header("location: pelatihan.php?tab=$tab&pesan=" . urlencode($pesan));
    exit;
}
?>

<?php if ($pesan): ?>
  <div class="alert alert-info alert-dismissible fade show">
    <i class="bi bi-info-circle me-2"></i><?= htmlspecialchars($pesan) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>

<!-- Tab navigasi -->
<ul class="nav nav-tabs mb-0">
  <li class="nav-item">
    <a class="nav-link <?= $tab==='tersedia'?'active':'' ?>" href="pelatihan.php?tab=tersedia">
      <i class="bi bi-journal-bookmark me-1"></i>Pelatihan Tersedia
    </a>
  </li>
  <li class="nav-item">
    <a class="nav-link <?= $tab==='saya'?'active':'' ?>" href="pelatihan.php?tab=saya">
      <i class="bi bi-person-check me-1"></i>Pendaftaran Saya
    </a>
  </li>
</ul>

<div class="card" style="border-radius:0 0 12px 12px">

<?php if ($tab === 'tersedia'): ?>
  <!-- Daftar pelatihan aktif yang bisa didaftar alumni -->
  <?php
  $data = mysqli_query($koneksi, "
    SELECT p.*, ui.name as nama_instruktur,
      (SELECT COUNT(*) FROM peserta_pelatihan WHERE pelatihan_id=p.id AND status_verifikasi!='ditolak') as jml_peserta,
      (SELECT id FROM peserta_pelatihan WHERE pelatihan_id=p.id AND user_id=$uid LIMIT 1) as sudah_daftar
    FROM pelatihan p
    JOIN instruktur i ON p.instruktur_id=i.id
    JOIN users ui ON i.user_id=ui.id
    WHERE p.status='aktif'
    ORDER BY p.tanggal_mulai ASC
  ");
  ?>
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead>
        <tr><th>#</th><th>Nama Pelatihan</th><th>Instruktur</th><th>Tanggal</th><th>Lokasi</th><th>Kuota</th><th>Aksi</th></tr>
      </thead>
      <tbody>
      <?php $no=1; while ($row = mysqli_fetch_assoc($data)): ?>
        <tr>
          <td><?= $no++ ?></td>
          <td>
            <strong><?= htmlspecialchars($row['nama_pelatihan']) ?></strong><br>
            <span class="badge bg-primary bg-opacity-10 text-primary" style="font-size:10px"><?= htmlspecialchars($row['jenis']??'Umum') ?></span>
          </td>
          <td><small><?= htmlspecialchars($row['nama_instruktur']) ?></small></td>
          <td>
            <small><?= date('d M Y', strtotime($row['tanggal_mulai'])) ?></small><br>
            <small class="text-muted">s/d <?= date('d M Y', strtotime($row['tanggal_selesai'])) ?></small>
          </td>
          <td><small><?= htmlspecialchars($row['lokasi']??'-') ?></small></td>
          <td>
            <?php $sisa = $row['kuota'] - $row['jml_peserta']; ?>
            <span class="badge <?= $sisa>0?'bg-success':'bg-danger' ?> bg-opacity-10 <?= $sisa>0?'text-success':'text-danger' ?>">
              <?= $sisa>0 ? "$sisa tempat" : 'Penuh' ?>
            </span>
          </td>
          <td>
            <?php if ($row['sudah_daftar']): ?>
              <span class="badge bg-secondary">Sudah Daftar</span>
            <?php elseif ($sisa > 0): ?>
              <a href="pelatihan.php?daftar=<?= $row['id'] ?>&tab=tersedia"
                 class="btn btn-sm btn-primary"
                 onclick="return confirm('Daftar pelatihan <?= htmlspecialchars($row['nama_pelatihan']) ?>?')">
                <i class="bi bi-plus-lg me-1"></i>Daftar
              </a>
            <?php else: ?>
              <button class="btn btn-sm btn-outline-secondary" disabled>Penuh</button>
            <?php endif; ?>
          </td>
        </tr>
      <?php endwhile; ?>
      <?php if (mysqli_num_rows($data) === 0): ?>
        <tr><td colspan="7">
          <div class="empty-state"><i class="bi bi-calendar-x"></i><p>Belum ada pelatihan aktif saat ini</p></div>
        </td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>

<?php else: ?>
  <!-- Riwayat pendaftaran saya -->
  <?php
  $data = mysqli_query($koneksi, "
    SELECT pp.*, p.nama_pelatihan, p.jenis, p.tanggal_mulai, p.tanggal_selesai, p.lokasi,
      ui.name as nama_instruktur, uv.name as nama_verifikator
    FROM peserta_pelatihan pp
    JOIN pelatihan p ON pp.pelatihan_id=p.id
    JOIN instruktur i ON p.instruktur_id=i.id
    JOIN users ui ON i.user_id=ui.id
    LEFT JOIN users uv ON pp.diverifikasi_oleh=uv.id
    WHERE pp.user_id=$uid
    ORDER BY pp.tanggal_daftar DESC
  ");
  ?>
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead>
        <tr><th>#</th><th>Nama Pelatihan</th><th>Instruktur</th><th>Tgl Daftar</th><th>Nilai</th><th>Status Verifikasi</th><th>Status Lulus</th></tr>
      </thead>
      <tbody>
      <?php $no=1; while ($row = mysqli_fetch_assoc($data)): ?>
        <tr>
          <td><?= $no++ ?></td>
          <td>
            <strong><?= htmlspecialchars($row['nama_pelatihan']) ?></strong><br>
            <small class="text-muted"><?= date('d M Y', strtotime($row['tanggal_mulai'])) ?></small>
          </td>
          <td><small><?= htmlspecialchars($row['nama_instruktur']) ?></small></td>
          <td><small><?= date('d M Y', strtotime($row['tanggal_daftar'])) ?></small></td>
          <td>
            <?php if ($row['nilai'] !== null): ?>
              <strong><?= $row['nilai'] ?></strong>
            <?php else: ?>
              <small class="text-muted">-</small>
            <?php endif; ?>
          </td>
          <td>
            <?php
            $sv = $row['status_verifikasi'];
            $svMap = ['menunggu'=>['warning','Menunggu'],'diterima'=>['success','Diterima'],'ditolak'=>['danger','Ditolak']];
            $svd = $svMap[$sv] ?? ['secondary','?'];
            ?>
            <span class="badge bg-<?= $svd[0] ?> bg-opacity-15 text-<?= $svd[0] ?>"><?= $svd[1] ?></span>
            <?php if ($sv==='ditolak' && $row['alasan_tolak']): ?>
              <br><small class="text-danger"><?= htmlspecialchars($row['alasan_tolak']) ?></small>
            <?php endif; ?>
          </td>
          <td>
            <?php
            $sl = $row['status_lulus'];
            $slMap = ['belum_dinilai'=>['secondary','Belum Dinilai'],'lulus'=>['success','Lulus'],'tidak_lulus'=>['danger','Tidak Lulus']];
            $sld = $slMap[$sl] ?? ['secondary','?'];
            ?>
            <span class="badge bg-<?= $sld[0] ?> bg-opacity-15 text-<?= $sld[0] ?>"><?= $sld[1] ?></span>
          </td>
        </tr>
      <?php endwhile; ?>
      <?php if (mysqli_num_rows($data) === 0): ?>
        <tr><td colspan="7">
          <div class="empty-state"><i class="bi bi-inbox"></i><p>Belum ada riwayat pendaftaran</p></div>
        </td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>

</div>

<?php include 'footer.php'; ?>
