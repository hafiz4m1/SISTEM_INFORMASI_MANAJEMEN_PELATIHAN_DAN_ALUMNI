<?php
$page_title = 'Riwayat Pelatihan';
include 'header.php';
?>

<div class="card">
  <div class="card-header">Riwayat Pelatihan Saya</div>
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead>
        <tr><th>#</th><th>Nama Pelatihan</th><th>Instruktur</th><th>Tgl Mulai</th><th>Tgl Selesai</th><th>Kehadiran</th><th>Nilai</th><th>Status</th><th>Sertifikat</th></tr>
      </thead>
      <tbody>
      <?php
      $data = mysqli_query($koneksi, "
          SELECT pp.*, p.nama_pelatihan, p.jenis, p.tanggal_mulai, p.tanggal_selesai, p.lokasi,
              u.name as nama_instruktur
          FROM peserta_pelatihan pp
          JOIN pelatihan p ON pp.pelatihan_id = p.id
          JOIN instruktur i ON p.instruktur_id = i.id
          JOIN users u ON i.user_id = u.id
          WHERE pp.user_id = {$_SESSION['id_login']}
          ORDER BY p.tanggal_mulai DESC
      ");
      $no = 1;
      while ($row = mysqli_fetch_assoc($data)):
      ?>
        <tr>
          <td><?= $no++ ?></td>
          <td>
            <?= htmlspecialchars($row['nama_pelatihan']) ?>
            <?php if ($row['jenis']): ?><br><small class="text-muted"><?= htmlspecialchars($row['jenis']) ?></small><?php endif; ?>
          </td>
          <td><?= htmlspecialchars($row['nama_instruktur']) ?></td>
          <td><?= date('d M Y', strtotime($row['tanggal_mulai'])) ?></td>
          <td><?= date('d M Y', strtotime($row['tanggal_selesai'])) ?></td>
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
            <?php if ($row['sertifikat_url']): ?>
              <a href="<?= htmlspecialchars($row['sertifikat_url']) ?>" class="btn btn-sm btn-outline-success" target="_blank"><i class="bi bi-download"></i></a>
            <?php else: ?>
              <span class="text-muted" style="font-size:12px">-</span>
            <?php endif; ?>
          </td>
        </tr>
      <?php endwhile; ?>
      <?php if (mysqli_num_rows($data) === 0): ?>
        <tr><td colspan="9" class="text-center text-muted py-4">Belum ada pelatihan yang diikuti</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include 'footer.php'; ?>
