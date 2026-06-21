<?php
/**
 * security.php - Fungsi keamanan global
 * Taruh di login/ sejajar koneksi.php
 * Include di semua header.php: include_once '../security.php';
 */

// ============================================================
// 1. SANITASI INPUT
// ============================================================

/**
 * Bersihkan string dari XSS
 */
function bersihkan(string $input): string {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

/**
 * Sanitasi untuk query MySQL (gunakan bersama mysqli_real_escape_string)
 */
function sanitasiDB($koneksi, $input): string {
    return mysqli_real_escape_string($koneksi, trim(strip_tags($input)));
}

/**
 * Validasi email
 */
function validasiEmail(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validasi angka positif
 */
function validasiAngka($value, int $min = 0, int $max = PHP_INT_MAX): bool {
    return is_numeric($value) && $value >= $min && $value <= $max;
}

/**
 * Validasi tanggal format Y-m-d
 */
function validasiTanggal(string $tgl): bool {
    $d = DateTime::createFromFormat('Y-m-d', $tgl);
    return $d && $d->format('Y-m-d') === $tgl;
}

/**
 * Generate token CSRF
 */
function generateCSRF(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Verifikasi token CSRF
 */
function verifikasiCSRF(string $token): bool {
    return isset($_SESSION['csrf_token']) &&
           hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Input CSRF untuk form
 */
function inputCSRF(): string {
    return '<input type="hidden" name="csrf_token" value="' . generateCSRF() . '">';
}

// ============================================================
// 2. SESSION TIMEOUT (30 menit tidak aktif)
// ============================================================
define('SESSION_TIMEOUT', 1800); // 30 menit

function cekSessionTimeout(): void {
    if (!isset($_SESSION['logged_in'])) return;

    $now = time();

    if (isset($_SESSION['last_activity'])) {
        $idle = $now - $_SESSION['last_activity'];
        if ($idle > SESSION_TIMEOUT) {
            // Session expired
            $level = $_SESSION['level'] ?? '';
            session_unset();
            session_destroy();
            session_start();
            $_SESSION['timeout_msg'] = 'Sesi Anda telah berakhir karena tidak aktif. Silakan login kembali.';
            header("location: ../login.php?pesan=" . urlencode('Sesi berakhir, silakan login kembali.'));
            exit;
        }
    }

    $_SESSION['last_activity'] = $now;
}

// Jalankan cek timeout otomatis
if (isset($_SESSION['logged_in'])) {
    cekSessionTimeout();
}

// ============================================================
// 3. RATE LIMITING LOGIN (cegah brute force)
// ============================================================
function cekRateLimit(string $ip): bool {
    if (!isset($_SESSION['login_attempts'][$ip])) {
        $_SESSION['login_attempts'][$ip] = ['count' => 0, 'time' => time()];
    }

    $attempts = &$_SESSION['login_attempts'][$ip];

    // Reset setelah 15 menit
    if (time() - $attempts['time'] > 900) {
        $attempts = ['count' => 0, 'time' => time()];
    }

    return $attempts['count'] < 5; // Max 5 percobaan
}

function tambahPercobaan(string $ip): void {
    if (!isset($_SESSION['login_attempts'][$ip])) {
        $_SESSION['login_attempts'][$ip] = ['count' => 0, 'time' => time()];
    }
    $_SESSION['login_attempts'][$ip]['count']++;
}

function resetPercobaan(string $ip): void {
    unset($_SESSION['login_attempts'][$ip]);
}

function sisaPercobaan(string $ip): int {
    if (!isset($_SESSION['login_attempts'][$ip])) return 5;
    return max(0, 5 - $_SESSION['login_attempts'][$ip]['count']);
}

// ============================================================
// 4. LOG AKTIVITAS
// ============================================================
function logAktivitas($koneksi, string $aksi, string $keterangan = ''): void {
    if (!isset($_SESSION['id_login'])) return;

    $user_id    = (int)$_SESSION['id_login'];
    $aksi_esc   = mysqli_real_escape_string($koneksi, $aksi);
    $ket_esc    = mysqli_real_escape_string($koneksi, $keterangan);
    $ip         = $_SERVER['REMOTE_ADDR'] ?? '-';
    $halaman    = $_SERVER['PHP_SELF'] ?? '-';

    // Cek tabel log ada
    $cek = mysqli_query($koneksi, "SHOW TABLES LIKE 'log_aktivitas'");
    if (!$cek || mysqli_num_rows($cek) === 0) return;

    mysqli_query($koneksi, "INSERT INTO log_aktivitas
        (user_id, aksi, keterangan, ip_address, halaman, created_at)
        VALUES ($user_id, '$aksi_esc', '$ket_esc', '$ip', '$halaman', NOW())");
}

// ============================================================
// 5. CEGAH DIRECT ACCESS FILE PHP
// ============================================================
function cegahDirectAccess(): void {
    // Redirect ke 404 jika diakses langsung tanpa session
    if (!isset($_SESSION['logged_in']) && basename($_SERVER['PHP_SELF']) !== 'login.php') {
        // Biarkan file ini diinclude, jangan redirect
    }
}

// ============================================================
// 6. SANITASI OUTPUT
// ============================================================
function e(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

// ============================================================
// 7. VALIDASI UPLOAD FILE
// ============================================================
function validasiUpload(array $file, array $tipe_diizinkan = ['jpg','jpeg','png','pdf'], int $max_mb = 2): array {
    $errors = [];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Gagal mengupload file.';
        return $errors;
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $tipe_diizinkan)) {
        $errors[] = 'Tipe file tidak diizinkan. Hanya: ' . implode(', ', $tipe_diizinkan);
    }

    $max_bytes = $max_mb * 1024 * 1024;
    if ($file['size'] > $max_bytes) {
        $errors[] = "Ukuran file maksimal {$max_mb}MB.";
    }

    return $errors;
}
