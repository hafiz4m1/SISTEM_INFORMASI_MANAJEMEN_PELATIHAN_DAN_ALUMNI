<?php
/**
 * cron_notif_rktl.php
 * Script untuk kirim notifikasi RKTL jatuh tempo
 * Taruh di login/ dan jalankan via Windows Task Scheduler setiap hari
 *
 * Cara setup Task Scheduler di Windows:
 * 1. Buka Task Scheduler
 * 2. Create Basic Task → beri nama "Notif RKTL BPPMDDTT"
 * 3. Trigger: Daily → jam 08:00
 * 4. Action: Start a Program
 *    Program: C:\xampp\php\php.exe
 *    Arguments: C:\xampp\htdocs\nama_project\sisinfoalumni\login\cron_notif_rktl.php
 * 5. Finish
 */

include __DIR__ . '/koneksi.php';
include __DIR__ . '/email_helper.php';

$log = [];

// Ambil RKTL yang jatuh tempo 7 hari ke depan dan belum dikirim notifikasi
$rktl_list = mysqli_query($koneksi, "
    SELECT r.*, u.email, u.name as nama_alumni,
        p.nama_pelatihan,
        DATE_FORMAT(r.tgl_pendampingan, '%d %M %Y') as tgl_fmt
    FROM rktl r
    JOIN alumni a ON r.alumni_id = a.id
    JOIN users u ON a.user_id = u.id
    JOIN pelatihan p ON r.pelatihan_id = p.id
    WHERE r.tgl_pendampingan BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
    AND r.status NOT IN ('selesai')
");

$terkirim = 0;
while ($row = mysqli_fetch_assoc($rktl_list)) {
    $hasil = notifRktlJatuhTempo(
        $row['email'],
        $row['nama_alumni'],
        $row['nama_pelatihan'],
        $row['tgl_fmt']
    );
    if ($hasil) {
        $terkirim++;
        $log[] = "✅ Email RKTL terkirim ke {$row['nama_alumni']} ({$row['email']})";
    } else {
        $log[] = "❌ Gagal kirim ke {$row['nama_alumni']} ({$row['email']})";
    }
}

// Kirim juga notifikasi tracer study yang belum diisi
$tracer_list = mysqli_query($koneksi, "
    SELECT ts.*, u.email, u.name
    FROM tracer_study ts
    JOIN alumni a ON ts.alumni_id = a.id
    JOIN users u ON a.user_id = u.id
    WHERE ts.status_pengisian = 'belum_diisi'
");

while ($row = mysqli_fetch_assoc($tracer_list)) {
    $hasil = notifTracerStudy($row['email'], $row['name']);
    if ($hasil) {
        $terkirim++;
        $log[] = "✅ Email Tracer Study terkirim ke {$row['name']} ({$row['email']})";
    }
}

// Tampilkan log jika diakses via browser
if (php_sapi_name() !== 'cli') {
    echo "<pre style='font-family:monospace;padding:20px'>";
    echo "=== Notifikasi RKTL & Tracer Study ===\n";
    echo "Tanggal: " . date('d M Y H:i') . "\n";
    echo "Total terkirim: $terkirim\n\n";
    foreach ($log as $l) echo $l . "\n";
    echo "</pre>";
} else {
    echo "Notifikasi terkirim: $terkirim\n";
}