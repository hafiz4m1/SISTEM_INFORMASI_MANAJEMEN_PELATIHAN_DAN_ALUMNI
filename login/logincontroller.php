<?php
if (!defined('DirBlock')) {
    die('Direct Access is not permitted.');
}

session_start();
include 'koneksi.php';
include 'security.php';

if (isset($_POST['login'])) {

    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

    // Cek rate limit (maksimal 5 percobaan dalam 15 menit)
    if (!cekRateLimit($ip)) {
        header("location: login.php?pesan=" . urlencode("Terlalu banyak percobaan login. Coba lagi dalam 15 menit."));
        exit;
    }

    // Validasi format email
    if (!validasiEmail($_POST['email'])) {
        tambahPercobaan($ip);
        header("location: login.php?pesan=" . urlencode("Format email tidak valid."));
        exit;
    }

    $email    = sanitasiDB($koneksi, $_POST['email']);
    $password = $_POST['password'];

    // Cari user berdasarkan email + aktif
    $result = mysqli_query($koneksi, "SELECT * FROM users WHERE email='$email' AND is_active=1");

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);

        if (password_verify($password, $row['password'])) {

            // Login berhasil - reset percobaan
            resetPercobaan($ip);

            // Simpan session
            $_SESSION['logged_in']     = true;
            $_SESSION['id_login']      = $row['id'];
            $_SESSION['nama']          = $row['name'];
            $_SESSION['email']         = $row['email'];
            $_SESSION['level']         = $row['role'];
            $_SESSION['status']        = 'sudah_login';
            $_SESSION['last_activity'] = time();

            // Log aktivitas
            logAktivitas($koneksi, 'LOGIN', 'Login berhasil sebagai ' . $row['role']);

            // Redirect sesuai role
            switch ($row['role']) {
                case 'admin':
                    header("location: admin/index.php");
                    break;
                case 'instruktur':
                    header("location: instruktur/index.php");
                    break;
                case 'alumni':
                    header("location: alumni/index.php");
                    break;
                case 'peserta':
                    header("location: peserta/index.php");
                    break;
                case 'kepala':
                    header("location: kepala/index.php");
                    break;
                default:
                    header("location: login.php?pesan=" . urlencode("Role tidak dikenali."));
                    break;

            }
            exit;

        } else {
            tambahPercobaan($ip);
            $sisa = sisaPercobaan($ip);
            $pesan = $sisa > 0
                ? "Email atau password salah. Sisa percobaan: $sisa"
                : "Terlalu banyak percobaan. Coba lagi dalam 15 menit.";
            header("location: login.php?pesan=" . urlencode($pesan));
            exit;
        }

    } else {
        tambahPercobaan($ip);
        $sisa = sisaPercobaan($ip);
        header("location: login.php?pesan=" . urlencode("Email atau password salah. Sisa percobaan: $sisa"));
        exit;
    }
}
?>
