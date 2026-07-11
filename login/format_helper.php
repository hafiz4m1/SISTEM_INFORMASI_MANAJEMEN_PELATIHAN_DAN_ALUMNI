<?php
/**
 * format_helper.php - Helper format tampilan angka & mata uang (Rupiah)
 * Taruh di login/ sejajar koneksi.php
 * Include: include_once 'format_helper.php'; (atau '../format_helper.php' dari subfolder)
 */

if (!function_exists('formatRupiah')) {
    /**
     * Format angka murni jadi "Rp 1.000.000".
     * Aman dipakai untuk kolom penghasilan/nominal bertipe numerik di masa depan.
     */
    function formatRupiah($angka): string
    {
        if ($angka === null || $angka === '') return '-';
        if (!is_numeric($angka)) return htmlspecialchars((string)$angka, ENT_QUOTES, 'UTF-8');
        return 'Rp ' . number_format((float)$angka, 0, ',', '.');
    }
}

if (!function_exists('formatGajiRange')) {
    /**
     * Format kode range gaji hasil tracer study ('2-4 juta', '1-3 juta', dst)
     * menjadi teks Rupiah yang mudah dibaca ('Rp 2.000.000 - Rp 4.000.000').
     *
     * Dipakai parser pola (regex), bukan daftar tetap, supaya tetap benar
     * walau nilai di database sedikit berbeda dari 5 pilihan resmi di
     * <select name="gaji_range"> (misalnya data uji/seed manual seperti
     * '1-2 juta' atau '3-5 juta' yang bukan salah satu dari 5 opsi baku).
     */
    function formatGajiRange(?string $kode): string
    {
        if (!$kode) return '-';
        $kode = trim($kode);

        // Pola "X-Y juta" -> "Rp X.000.000 - Rp Y.000.000"
        if (preg_match('/^(\d+(?:[.,]\d+)?)\s*-\s*(\d+(?:[.,]\d+)?)\s*juta$/i', $kode, $m)) {
            $dari = (float) str_replace(',', '.', $m[1]);
            $sampai = (float) str_replace(',', '.', $m[2]);
            return 'Rp ' . number_format($dari * 1000000, 0, ',', '.')
                 . ' - Rp ' . number_format($sampai * 1000000, 0, ',', '.');
        }

        // Pola "< X juta" -> "Di bawah Rp X.000.000"
        if (preg_match('/^<\s*(\d+(?:[.,]\d+)?)\s*juta$/i', $kode, $m)) {
            $x = (float) str_replace(',', '.', $m[1]);
            return 'Di bawah Rp ' . number_format($x * 1000000, 0, ',', '.');
        }

        // Pola "> X juta" -> "Di atas Rp X.000.000"
        if (preg_match('/^>\s*(\d+(?:[.,]\d+)?)\s*juta$/i', $kode, $m)) {
            $x = (float) str_replace(',', '.', $m[1]);
            return 'Di atas Rp ' . number_format($x * 1000000, 0, ',', '.');
        }

        // Nilai tak dikenal (mis. sudah teks bebas) -> tampilkan apa adanya, aman dari XSS
        return htmlspecialchars($kode, ENT_QUOTES, 'UTF-8');
    }
}
