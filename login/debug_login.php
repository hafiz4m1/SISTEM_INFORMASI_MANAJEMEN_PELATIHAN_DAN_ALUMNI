<?php
include 'koneksi.php';

$email    = 'admin@bppmddtt.go.id';
$password = 'admin123';

echo "<pre style='font-family:monospace;font-size:14px;padding:20px'>";

$result = mysqli_query($koneksi, "SELECT * FROM users WHERE email='$email'");
echo "1. Jumlah user dengan email '$email': " . mysqli_num_rows($result) . "\n\n";

if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    echo "2. Data user:\n";
    echo "   - id       : " . $row['id'] . "\n";
    echo "   - name     : " . $row['name'] . "\n";
    echo "   - role     : " . $row['role'] . "\n";
    echo "   - is_active: " . $row['is_active'] . "\n";
    echo "   - password : " . $row['password'] . "\n\n";

    $verify = password_verify($password, $row['password']);
    echo "3. password_verify = " . ($verify ? "TRUE ✅" : "FALSE ❌") . "\n\n";

    if (!$verify) {
        $new_hash = password_hash($password, PASSWORD_BCRYPT);
        mysqli_query($koneksi, "UPDATE users SET password='$new_hash', is_active=1 WHERE email='$email'");
        echo "4. Hash diperbaiki otomatis ✅\n";
        echo "   Sekarang coba login dengan:\n";
        echo "   Email    : $email\n";
        echo "   Password : $password\n";
    } else {
        echo "4. Password sudah benar ✅\n";
        echo "   Pastikan logincontroller.php sudah pakai versi terbaru.\n";
    }
} else {
    echo "❌ User tidak ditemukan! Membuat ulang...\n";
    $hash = password_hash($password, PASSWORD_BCRYPT);
    $q = mysqli_query($koneksi, "INSERT INTO users (name, email, password, role, is_active)
        VALUES ('Administrator', '$email', '$hash', 'admin', 1)");
    echo $q ? "✅ User admin berhasil dibuat. Coba login sekarang!" : "❌ Gagal: " . mysqli_error($koneksi);
}

echo "</pre>";
?>
