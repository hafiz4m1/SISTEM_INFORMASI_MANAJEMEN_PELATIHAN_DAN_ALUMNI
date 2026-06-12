<?php
session_start();
session_unset();
session_destroy();
header("location: login.php?pesan=Anda telah keluar dari sistem.");
exit;
?>
