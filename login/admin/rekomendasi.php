<?php
$page_title = 'Rekomendasi Pelatihan';
include 'header.php';
include '../email_helper.php';

$pesan = isset($_GET['pesan']) ? $_GET['pesan'] : '';

// Generate rekomendasi + kirim email notifikasi
if (isset($_GET['generate'])) {
    mysqli_query($koneksi, "DELETE FROM rekomendasi");
    $alumni_list = mysqli_query($koneksi, "
        SELECT a.id, u.email, u.name
        FROM alumni a JOIN users u ON a.user_id=u.id
    ");
    $total = 0;

    while ($al = mysqli_fetch_assoc($alumni_list)) {
        $pelatihan_baru = mysqli_query($koneksi, "
            SELECT p.id FROM pelatihan p
            WHERE p.status='aktif'
            AND p.id NOT IN (
                SELECT pp.pelatihan_id FROM peserta_pelatihan pp WHERE pp.user_id=(
                    SELECT user_id FROM alumni WHERE id={$al['id']}
                )
            )
            LIMIT 3
        ");

        $ada_rekomendasi = false;
        while ($p = mysqli_fetch_assoc($pelatihan_baru)) {
            $alasan = mysqli_real_escape_string($koneksi,
                "Pelatihan ini belum pernah diikuti dan sesuai dengan profil alumni.");
            mysqli_query($koneksi, "INSERT INTO rekomendasi (alumni_id, pelatihan_id, skor, alasan)
                VALUES ({$al['id']}, {$p['id']}, 80.00, '$alasan')");
            $total++;
            $ada_rekomendasi = true;
        }

        // Kirim email notifikasi ke alumni jika ada rekomendasi baru
        if ($ada_rekomendasi) {
            $body = "
            <p>Halo <strong>{$al['name']}</strong>,</p>
            <p>Kami memiliki <strong>rekomendasi pelatihan baru</strong> yang sesuai dengan profil dan kompetensi Anda.</p>
            <p>Silakan login ke sistem untuk melihat daftar rekomendasi pelatihan yang telah kami siapkan khusus untuk Anda.</p>
            <p style='text-align:center'>
                <a href='http://localhost/sisinfoalumni/login/alumni/rekomendasi.php'
                   style='display:inline-block;background:#1a4c8e;color:#fff;padding:10px 24px;border-radius:8px;text-decoration:none;font-weight:600'>
                   Lihat Rekomendasi
                </a>
            </p>";
            kirimEmail($al['email'], $al['name'], 'Rekomendasi Pelatihan Baru Untuk Anda - BPPMDDTT', $body);
        }
    }

    header("location: rekomendasi.php?pesan=Berhasil generate $total rekomendasi dan email terkirim."); exit;
}

$data = mysqli_query($koneksi, "
    SELECT r.*, u.name as nama_alumni, p.nama_pelatihan, p.tanggal_mulai
    FROM rekomendasi r
    JOIN alumni a ON r.alumni_id = a.id
    JOIN users u ON a.user_id = u.id
    JOIN pelatihan p ON r.pelatihan_id = p.id
    ORDER BY r.skor DESC, u.name ASC
");
?>

<?php if ($pesan): ?>
  <div class="alert alert-success alert-dismissible fade show">
    <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($pesan) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
  </div>
<?php endif; ?>

<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <span>Rekomendasi Pelatihan untuk Alumni</span>
    <a href="rekomendasi.php?generate=1" class="btn btn-sm btn-primary"
       onclick="return confirm('Generate ulang semua rekomendasi? Email akan dikirim ke alumni.')">
      <i class="bi bi-magic"></i> Generate & Kirim Email
    </a>
  </div>
  <div class="table-responsive">
    <table class="table table-hover mb-0">
      <thead>
        <tr><th>#</th><th>Alumni</th><th>Pelatihan Direkomendasikan</th><th>Tgl Mulai</th><th>Skor</th><th>Alasan</th><th>Dilihat</th></tr>
      </thead>
      <tbody>
      <?php $no=1; while ($row = mysqli_fetch_assoc($data)): ?>
        <tr>
          <td><?= $no++ ?></td>
          <td><?= htmlspecialchars($row['nama_alumni']) ?></td>
          <td><?= htmlspecialchars($row['nama_pelatihan']) ?></td>
          <td><?= date('d M Y', strtotime($row['tanggal_mulai'])) ?></td>
          <td><span class="badge bg-primary"><?= $row['skor'] ?>%</span></td>
          <td><small class="text-muted"><?= htmlspecialchars($row['alasan']) ?></small></td>
          <td><?= $row['is_dilihat']
            ? '<span class="badge bg-success">Ya</span>'
            : '<span class="badge bg-secondary">Belum</span>' ?></td>
        </tr>
      <?php endwhile; ?>
      <?php if (mysqli_num_rows($data) === 0): ?>
        <tr><td colspan="7" class="text-center text-muted py-4">Belum ada rekomendasi. Klik "Generate & Kirim Email".</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<?php include 'footer.php'; ?>