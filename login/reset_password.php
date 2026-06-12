<?php
/**
 * Script reset password admin - HAPUS FILE INI SETELAH DIPAKAI!
 */
include 'koneksi.php';

$email    = 'admin@bppmddtt.go.id';
$password = 'admin123';
$hash     = password_hash($password, PASSWORD_BCRYPT);

$result = mysqli_query($koneksi, "UPDATE users SET password='$hash' WHERE email='$email'");

if ($result && mysqli_affected_rows($koneksi) > 0) {
    echo "<p style='color:green;font-family:sans-serif'>✅ Password berhasil direset!<br>
    Email: <b>$email</b><br>
    Password: <b>$password</b><br><br>
    <b style='color:red'>⚠️ Segera hapus file ini setelah login berhasil!</b></p>";
} else {
    echo "<p style='color:red;font-family:sans-serif'>❌ Gagal. Pastikan email <b>$email</b> ada di database.<br>
    Error: " . mysqli_error($koneksi) . "</p>";
}
?>
