<?php
/**
 * cek_foto.php - Helper cek dan copy foto balai
 * Akses SEKALI di browser: http://localhost/.../sisinfoalumni/login/cek_foto.php
 * HAPUS file ini setelah dijalankan!
 */
echo "<h2>Cek Foto Balai BPPMDDTT</h2>";

$base = __DIR__ . '/assets/images/';
$fotos = ['balai1.jpg', 'balai2.jpg', 'balai3.jpg'];

echo "<h3>Status folder: <code>login/assets/images/</code></h3>";
echo "<p>Path lengkap: <code>$base</code></p>";

if (!file_exists($base)) {
    mkdir($base, 0755, true);
    echo "<p style='color:green'>✅ Folder dibuat otomatis</p>";
} else {
    echo "<p style='color:green'>✅ Folder sudah ada</p>";
}

echo "<h3>Status foto:</h3>";
foreach ($fotos as $foto) {
    $path = $base . $foto;
    if (file_exists($path)) {
        $size = round(filesize($path) / 1024, 1);
        echo "<p style='color:green'>✅ $foto ada ($size KB)</p>";
    } else {
        echo "<p style='color:red'>❌ $foto TIDAK ADA - silakan copy ke folder ini</p>";
    }
}

echo "<hr>";
echo "<h3>Cara fix:</h3>";
echo "<ol>";
echo "<li>Rename foto kamu: <br>
  <code>94993.jpg → balai1.jpg</code> (gedung depan)<br>
  <code>95001.jpg → balai2.jpg</code> (ruang pelatihan)<br>
  <code>94999.jpg → balai3.jpg</code> (aula luar)</li>";
echo "<li>Copy ketiga foto ke folder:<br><code>" . $base . "</code></li>";
echo "<li>Refresh halaman ini untuk verifikasi</li>";
echo "<li>Hapus file <code>cek_foto.php</code> ini setelah selesai</li>";
echo "</ol>";

echo "<h3>Test tampilan foto:</h3>";
foreach ($fotos as $i => $foto) {
    $path = $base . $foto;
    if (file_exists($path)) {
        echo "<img src='assets/images/$foto' style='width:200px;height:120px;object-fit:cover;margin:4px;border-radius:8px'>";
    }
}

echo "<hr><p style='color:red'><strong>⚠️ HAPUS FILE INI SETELAH SELESAI!</strong></p>";
?>
