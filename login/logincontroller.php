<?php
if (!defined('DirBlock')) {
    die('Direct Access is not permitted.');
}

session_start();
include 'koneksi.php';

if (isset($_POST['login'])) {

    $email    = mysqli_real_escape_string($koneksi, $_POST['email']);
    $password = $_POST['password'];

    // Cari user berdasarkan email + aktif
    $result = mysqli_query($koneksi, "SELECT * FROM users WHERE email='$email' AND is_active=1");

    if (mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_assoc($result);

        if (password_verify($password, $row['password'])) {

            // Simpan session
            $_SESSION['logged_in'] = true;
            $_SESSION['id_login']  = $row['id'];
            $_SESSION['nama']      = $row['name'];
            $_SESSION['email']     = $row['email'];
            $_SESSION['level']     = $row['role'];
            $_SESSION['status']    = 'sudah_login';

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
                    header("location: login.php?pesan=Role tidak dikenali.");
                    break;
                
            }
            exit;

        } else {
            header("location: login.php?pesan=Email atau password salah.");
            exit;
        }

    } else {
        header("location: login.php?pesan=Email atau password salah.");
        exit;
    }
}
?>
