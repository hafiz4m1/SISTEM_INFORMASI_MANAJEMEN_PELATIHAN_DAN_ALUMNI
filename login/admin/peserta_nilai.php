<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (!isset($_SESSION['logged_in']) || !in_array($_SESSION['level'], ['admin','instruktur'])) {
    header("location: ../login.php"); exit;
}
include '../koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id        = (int)$_POST['id'];
    $nilai     = mysqli_real_escape_string($koneksi, $_POST['nilai']);
    $status    = mysqli_real_escape_string($koneksi, $_POST['status_lulus']);
    $kehadiran = mysqli_real_escape_string($koneksi, $_POST['status_kehadiran']);

    // Update nilai peserta
    mysqli_query($koneksi, "UPDATE peserta_pelatihan SET
        nilai='$nilai', status_lulus='$status', status_kehadiran='$kehadiran'
        WHERE id=$id");

    // =====================================================
    // OTOMATIS JADIKAN ALUMNI JIKA STATUS LULUS
    // =====================================================
    if ($status === 'lulus') {

        // Ambil data peserta ini
        $pp = mysqli_fetch_assoc(mysqli_query($koneksi,
            "SELECT pp.user_id, pp.pelatihan_id, p.tanggal_selesai, p.instruktur_id
             FROM peserta_pelatihan pp
             JOIN pelatihan p ON pp.pelatihan_id = p.id
             WHERE pp.id = $id"));

        if ($pp) {
            $user_id     = $pp['user_id'];
            $pel_id      = $pp['pelatihan_id'];
            $tgl_lulus   = $pp['tanggal_selesai'];
            $instruktur_id = $pp['instruktur_id'];

            // Cek apakah sudah jadi alumni
            $cek_alumni = mysqli_fetch_row(mysqli_query($koneksi,
                "SELECT id FROM alumni WHERE user_id=$user_id"));

            if (!$cek_alumni) {
                // Buat record alumni baru
                mysqli_query($koneksi, "INSERT INTO alumni (user_id, tanggal_lulus)
                    VALUES ($user_id, '$tgl_lulus')");
                $alumni_id = mysqli_insert_id($koneksi);

                // Update role user dari peserta ke alumni
                mysqli_query($koneksi, "UPDATE users SET role='alumni'
                    WHERE id=$user_id");
            } else {
                $alumni_id = $cek_alumni[0];
            }

            // Hitung tgl pendampingan RKTL (3 bulan setelah selesai pelatihan)
            $tgl_pendampingan = date('Y-m-d', strtotime($tgl_lulus . ' +3 months'));

            // Buat record RKTL jika belum ada
            $cek_rktl = mysqli_fetch_row(mysqli_query($koneksi,
                "SELECT id FROM rktl WHERE alumni_id=$alumni_id AND pelatihan_id=$pel_id"));

            if (!$cek_rktl) {
                mysqli_query($koneksi, "INSERT INTO rktl
                    (alumni_id, pelatihan_id, instruktur_id, rencana, tgl_pendampingan, status)
                    VALUES ($alumni_id, $pel_id, $instruktur_id,
                    'Belum diisi', '$tgl_pendampingan', 'belum_mulai')");
            }

            // Kirim tracer study jika belum ada
            $cek_tracer = mysqli_fetch_row(mysqli_query($koneksi,
                "SELECT id FROM tracer_study WHERE alumni_id=$alumni_id"));
            if (!$cek_tracer) {
                mysqli_query($koneksi, "INSERT INTO tracer_study (alumni_id, status_pengisian)
                    VALUES ($alumni_id, 'belum_diisi')");
            }
        }
    }
    // =====================================================
}

$redirect = isset($_POST['redirect']) ? $_POST['redirect'] : '../admin/peserta.php';
header("location: $redirect?pesan=Nilai berhasil disimpan." . ($status === 'lulus' ? ' Peserta otomatis menjadi Alumni.' : ''));
exit;
?>
