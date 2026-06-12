<?php
/**
 * buat_assets.php
 * Jalankan sekali untuk membuat struktur folder assets
 * Taruh di folder login/ lalu akses di browser
 * HAPUS setelah dijalankan!
 */

$base = __DIR__;
$folders = [
    $base . '/assets',
    $base . '/assets/css',
    $base . '/assets/js',
    $base . '/assets/images',
];

foreach ($folders as $folder) {
    if (!file_exists($folder)) {
        mkdir($folder, 0755, true);
        echo "✅ Folder dibuat: $folder<br>";
    } else {
        echo "⚠️ Sudah ada: $folder<br>";
    }
}

// Buat favicon sederhana (1x1 pixel ICO)
$favicon_path = $base . '/assets/images/favicon.ico';
if (!file_exists($favicon_path)) {
    // Favicon sederhana 16x16 ICO
    $ico_data = base64_decode(
        'AAABAAEAEBAAAAEAIABoBAAAFgAAACgAAAAQAAAAIAAAAAEAIAAAAAAAAAQAAAAAAAAAAAAAAAAA' .
        'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA' .
        'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA' .
        'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAIlRMLwAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA' .
        'AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA='
    );
    file_put_contents($favicon_path, $ico_data);
    echo "✅ Favicon dibuat<br>";
}

echo "<br><strong>Sekarang:</strong><br>";
echo "1. Copy <code>global.css</code> ke <code>login/assets/css/global.css</code><br>";
echo "2. Copy <code>global.js</code> ke <code>login/assets/js/global.js</code><br>";
echo "3. Tambahkan di semua <code>header.php</code>:<br>";
echo "<code>&lt;link rel='stylesheet' href='../assets/css/global.css'&gt;</code><br>";
echo "4. Tambahkan di semua <code>footer.php</code>:<br>";
echo "<code>&lt;script src='../assets/js/global.js'&gt;&lt;/script&gt;</code><br>";
echo "<br><strong style='color:red'>⚠️ Hapus file ini setelah dijalankan!</strong>";
?>
