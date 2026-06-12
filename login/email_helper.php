<?php
/**
 * email_helper.php - Helper kirim email notifikasi
 * Taruh di login/ sejajar dengan koneksi.php
 */

require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';
require_once __DIR__ . '/email_config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/**
 * Fungsi utama kirim email
 */
function kirimEmail(string $to, string $toName, string $subject, string $body): bool
{
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = MAIL_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = MAIL_USERNAME;
        $mail->Password   = MAIL_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = MAIL_PORT;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
        $mail->addAddress($to, $toName);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = templateEmail($subject, $body);
        $mail->AltBody = strip_tags($body);

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email gagal ke $to: " . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Template HTML email yang konsisten
 */
function templateEmail(string $judul, string $konten): string
{
    return '
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
  body { margin:0; padding:0; background:#f4f6fb; font-family:Arial,sans-serif; font-size:14px; color:#1a2942; }
  .wrap { max-width:560px; margin:32px auto; background:#fff; border-radius:12px; overflow:hidden; box-shadow:0 2px 12px rgba(0,0,0,.08); }
  .header { background:linear-gradient(135deg,#1a4c8e,#0d3060); padding:28px 32px; text-align:center; }
  .header h2 { color:#fff; margin:0; font-size:18px; font-weight:700; }
  .header p  { color:rgba(255,255,255,.7); margin:4px 0 0; font-size:12px; }
  .body { padding:28px 32px; }
  .body p { line-height:1.7; color:#4b5563; margin:0 0 12px; }
  .btn { display:inline-block; background:#1a4c8e; color:#fff !important; padding:10px 24px; border-radius:8px; text-decoration:none; font-weight:600; font-size:13px; margin:12px 0; }
  .info-box { background:#f8f9fb; border-left:4px solid #1a4c8e; border-radius:4px; padding:12px 16px; margin:16px 0; font-size:13px; }
  .info-box .label { color:#6b7280; font-size:11px; text-transform:uppercase; letter-spacing:.04em; }
  .info-box .value { font-weight:600; color:#1a2942; margin-top:2px; }
  .footer { background:#f8f9fb; padding:16px 32px; text-align:center; font-size:11px; color:#9ca3af; border-top:1px solid #f0f0f0; }
  .badge-success { background:#d4edda; color:#155724; padding:3px 10px; border-radius:20px; font-size:12px; font-weight:600; }
  .badge-danger  { background:#f8d7da; color:#721c24; padding:3px 10px; border-radius:20px; font-size:12px; font-weight:600; }
  .badge-warning { background:#fff3cd; color:#856404; padding:3px 10px; border-radius:20px; font-size:12px; font-weight:600; }
</style>
</head>
<body>
<div class="wrap">
  <div class="header">
    <h2>BPPMDDTT Banjarmasin</h2>
    <p>Balai Pelatihan dan Pemberdayaan Masyarakat Desa, Daerah Tertinggal dan Transmigrasi</p>
  </div>
  <div class="body">
    <h3 style="margin:0 0 16px;font-size:16px;color:#1a2942">' . $judul . '</h3>
    ' . $konten . '
  </div>
  <div class="footer">
    Email ini dikirim otomatis oleh sistem BPPMDDTT Banjarmasin.<br>
    Jangan balas email ini. &copy; ' . date('Y') . ' BPPMDDTT Banjarmasin.
  </div>
</div>
</body>
</html>';
}

// ============================================================
// FUNGSI NOTIFIKASI PER EVENT
// ============================================================

/**
 * 1. Notifikasi pendaftaran berhasil (ke peserta)
 */
function notifPendaftaranBerhasil(string $email, string $nama, string $nama_pelatihan, string $tgl_mulai): bool
{
    $body = "
    <p>Halo <strong>$nama</strong>,</p>
    <p>Pendaftaran Anda telah berhasil diterima. Berikut detail pelatihan yang Anda daftarkan:</p>
    <div class='info-box'>
        <div class='label'>Pelatihan</div>
        <div class='value'>$nama_pelatihan</div>
        <div class='label' style='margin-top:8px'>Tanggal Mulai</div>
        <div class='value'>$tgl_mulai</div>
        <div class='label' style='margin-top:8px'>Status</div>
        <div class='value'><span class='badge-warning'>Menunggu Verifikasi Admin</span></div>
    </div>
    <p>Anda akan mendapat email konfirmasi setelah admin memverifikasi pendaftaran Anda.</p>
    <p>Terima kasih telah mendaftar!</p>";

    return kirimEmail($email, $nama, 'Pendaftaran Pelatihan Berhasil - BPPMDDTT', $body);
}

/**
 * 2. Notifikasi verifikasi diterima (ke peserta)
 */
function notifVerifikasiDiterima(string $email, string $nama, string $nama_pelatihan, string $tgl_mulai, string $lokasi): bool
{
    $body = "
    <p>Halo <strong>$nama</strong>,</p>
    <p>Selamat! Pendaftaran Anda telah <strong>diverifikasi dan diterima</strong> oleh admin.</p>
    <div class='info-box'>
        <div class='label'>Pelatihan</div>
        <div class='value'>$nama_pelatihan</div>
        <div class='label' style='margin-top:8px'>Tanggal Mulai</div>
        <div class='value'>$tgl_mulai</div>
        <div class='label' style='margin-top:8px'>Lokasi</div>
        <div class='value'>$lokasi</div>
        <div class='label' style='margin-top:8px'>Status</div>
        <div class='value'><span class='badge-success'>✓ Diterima</span></div>
    </div>
    <p>Harap hadir tepat waktu sesuai jadwal yang telah ditentukan. Selamat mengikuti pelatihan!</p>";

    return kirimEmail($email, $nama, 'Pendaftaran Pelatihan Diterima ✓ - BPPMDDTT', $body);
}

/**
 * 3. Notifikasi verifikasi ditolak (ke peserta)
 */
function notifVerifikasiDitolak(string $email, string $nama, string $nama_pelatihan, string $alasan): bool
{
    $body = "
    <p>Halo <strong>$nama</strong>,</p>
    <p>Mohon maaf, pendaftaran Anda untuk pelatihan berikut <strong>tidak dapat diterima</strong>:</p>
    <div class='info-box'>
        <div class='label'>Pelatihan</div>
        <div class='value'>$nama_pelatihan</div>
        <div class='label' style='margin-top:8px'>Status</div>
        <div class='value'><span class='badge-danger'>✗ Ditolak</span></div>
        <div class='label' style='margin-top:8px'>Alasan</div>
        <div class='value'>$alasan</div>
    </div>
    <p>Anda dapat mendaftar pelatihan lain yang tersedia. Silakan kunjungi sistem kami untuk melihat pelatihan lainnya.</p>";

    return kirimEmail($email, $nama, 'Pendaftaran Pelatihan Ditolak - BPPMDDTT', $body);
}

/**
 * 4. Notifikasi admin ada pendaftar baru
 */
function notifAdminPendaftarBaru(string $email_admin, string $nama_peserta, string $nama_pelatihan, string $tgl_daftar): bool
{
    $body = "
    <p>Ada pendaftaran pelatihan baru yang memerlukan verifikasi Anda.</p>
    <div class='info-box'>
        <div class='label'>Nama Peserta</div>
        <div class='value'>$nama_peserta</div>
        <div class='label' style='margin-top:8px'>Pelatihan</div>
        <div class='value'>$nama_pelatihan</div>
        <div class='label' style='margin-top:8px'>Tanggal Daftar</div>
        <div class='value'>$tgl_daftar</div>
        <div class='label' style='margin-top:8px'>Status</div>
        <div class='value'><span class='badge-warning'>Menunggu Verifikasi</span></div>
    </div>
    <p>Silakan login ke sistem untuk memverifikasi pendaftaran ini.</p>";

    return kirimEmail($email_admin, 'Admin BPPMDDTT', 'Pendaftaran Baru Menunggu Verifikasi - BPPMDDTT', $body);
}

/**
 * 5. Notifikasi nilai & kelulusan (ke peserta/alumni)
 */
function notifNilaiLulus(string $email, string $nama, string $nama_pelatihan, float $nilai, string $status): bool
{
    $status_text = $status === 'lulus'
        ? "<span class='badge-success'>✓ Lulus</span>"
        : "<span class='badge-danger'>✗ Tidak Lulus</span>";

    $pesan_tambahan = $status === 'lulus'
        ? '<p>Selamat! Anda telah berhasil menyelesaikan pelatihan dan dinyatakan <strong>LULUS</strong>. Sertifikat Anda dapat diunduh melalui sistem.</p>'
        : '<p>Mohon maaf, Anda dinyatakan tidak lulus pada pelatihan ini. Semangat untuk pelatihan berikutnya!</p>';

    $body = "
    <p>Halo <strong>$nama</strong>,</p>
    <p>Hasil penilaian pelatihan Anda telah diinput oleh instruktur.</p>
    <div class='info-box'>
        <div class='label'>Pelatihan</div>
        <div class='value'>$nama_pelatihan</div>
        <div class='label' style='margin-top:8px'>Nilai</div>
        <div class='value' style='font-size:24px;color:#1a4c8e'>$nilai</div>
        <div class='label' style='margin-top:8px'>Status Kelulusan</div>
        <div class='value'>$status_text</div>
    </div>
    $pesan_tambahan";

    return kirimEmail($email, $nama, 'Hasil Penilaian Pelatihan - BPPMDDTT', $body);
}

/**
 * 6. Notifikasi tracer study (ke alumni)
 */
function notifTracerStudy(string $email, string $nama): bool
{
    $body = "
    <p>Halo <strong>$nama</strong>,</p>
    <p>Anda memiliki <strong>formulir Tracer Study</strong> yang perlu diisi. Tracer Study membantu kami meningkatkan kualitas pelatihan untuk alumni selanjutnya.</p>
    <p>Mohon luangkan waktu 5 menit untuk mengisi formulir ini. Informasi Anda sangat berharga bagi kami.</p>
    <p>Silakan login ke sistem untuk mengisi Tracer Study:</p>
    <p style='text-align:center'><a href='http://localhost/sisinfoalumni/login/login.php' class='btn'>Isi Tracer Study Sekarang</a></p>
    <p style='font-size:12px;color:#9ca3af'>Jika tombol tidak berfungsi, salin link berikut ke browser Anda.</p>";

    return kirimEmail($email, $nama, 'Mohon Isi Tracer Study - BPPMDDTT', $body);
}

/**
 * 7. Notifikasi RKTL jatuh tempo (ke alumni)
 */
function notifRktlJatuhTempo(string $email, string $nama, string $nama_pelatihan, string $tgl_pendampingan): bool
{
    $body = "
    <p>Halo <strong>$nama</strong>,</p>
    <p>Jadwal pendampingan <strong>Rencana Kerja Tindak Lanjut (RKTL)</strong> Anda akan segera tiba.</p>
    <div class='info-box'>
        <div class='label'>Pelatihan</div>
        <div class='value'>$nama_pelatihan</div>
        <div class='label' style='margin-top:8px'>Tanggal Pendampingan</div>
        <div class='value'>$tgl_pendampingan</div>
    </div>
    <p>Instruktur Anda akan melakukan pendampingan dan mengisi progres RKTL Anda. Pastikan Anda siap dengan laporan perkembangan rencana kerja Anda.</p>";

    return kirimEmail($email, $nama, 'Pengingat Pendampingan RKTL - BPPMDDTT', $body);
}
?>
