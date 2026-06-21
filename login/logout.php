<?php
session_start();
include 'koneksi.php';
include 'security.php';

// Log sebelum session dihapus
logAktivitas($koneksi, 'LOGOUT', 'Logout dari sistem');

session_unset();
session_destroy();
header("location: login.php?pesan=" . urlencode("Anda telah keluar dari sistem."));
exit;
?>
