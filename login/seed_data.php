<?php
/**
 * SEED DATA - Data dummy untuk testing
 * Jalankan sekali, lalu HAPUS file ini!
 */
include 'koneksi.php';

$log = [];

// ============================================================
// 1. USERS
// ============================================================
$users = [
    ['Administrator',           'admin@bppmddtt.go.id',        'admin'],
    ['Drs. Haji Mulyadi, M.Pd', 'kepala@bppmddtt.go.id',      'kepala'],
    ['Budi Santoso',            'instruktur1@bppmddtt.go.id', 'instruktur'],
    ['Siti Rahayu',             'instruktur2@bppmddtt.go.id', 'instruktur'],
    ['Ahmad Fauzi',             'alumni1@bppmddtt.go.id',     'alumni'],
    ['Dewi Lestari',            'alumni2@bppmddtt.go.id',     'alumni'],
    ['Rizky Pratama',           'alumni3@bppmddtt.go.id',     'alumni'],
    ['Nur Hidayah',             'alumni4@bppmddtt.go.id',     'alumni'],
    ['Eko Wahyudi',             'peserta1@bppmddtt.go.id',    'peserta'],
    ['Fitria Anggraini',        'peserta2@bppmddtt.go.id',    'peserta'],
];

$user_ids = [];
// Hapus admin lama jika ada (untuk reset password)
mysqli_query($koneksi, "DELETE FROM users WHERE email='admin@bppmddtt.go.id'");

foreach ($users as $u) {
    $name  = mysqli_real_escape_string($koneksi, $u[0]);
    $email = mysqli_real_escape_string($koneksi, $u[1]);
    $role  = $u[2];
    // Password berbeda untuk admin
    $pass  = ($role === 'admin') ? 'admin123' : 'password123';
    $hash  = password_hash($pass, PASSWORD_BCRYPT);

    // Skip jika email sudah ada (kecuali admin yang sudah dihapus)
    $cek = mysqli_fetch_row(mysqli_query($koneksi, "SELECT id FROM users WHERE email='$email'"));
    if ($cek) {
        $user_ids[$email] = $cek[0];
        $log[] = "⚠️ User $email sudah ada, skip.";
        continue;
    }

    mysqli_query($koneksi, "INSERT INTO users (name, email, password, role) VALUES ('$name','$email','$hash','$role')");
    $user_ids[$email] = mysqli_insert_id($koneksi);
    if ($role === 'admin') {
        $log[] = "✅ Admin user dibuat/di-reset: password = admin123";
    } else {
        $log[] = "✅ User ditambahkan: $name ($role)";
    }
}

// ============================================================
// 2. INSTRUKTUR
// ============================================================
$instruktur_ids = [];
$instruktur_data = [
    [$user_ids['instruktur1@bppmddtt.go.id'], 'Pengembangan Masyarakat', 'S2 Sosiologi', '081234560001'],
    [$user_ids['instruktur2@bppmddtt.go.id'], 'Teknologi Informasi',    'S2 Komputer',  '081234560002'],
];
foreach ($instruktur_data as $i) {
    $cek = mysqli_fetch_row(mysqli_query($koneksi, "SELECT id FROM instruktur WHERE user_id={$i[0]}"));
    if ($cek) { $instruktur_ids[] = $cek[0]; continue; }
    mysqli_query($koneksi, "INSERT INTO instruktur (user_id, bidang_keahlian, pendidikan, kontak)
        VALUES ({$i[0]},'{$i[1]}','{$i[2]}','{$i[3]}')");
    $instruktur_ids[] = mysqli_insert_id($koneksi);
    $log[] = "✅ Instruktur ditambahkan: user_id={$i[0]}";
}

// ============================================================
// 3. PELATIHAN
// ============================================================
$pelatihan_data = [
    ['Pengembangan Kapasitas Aparatur Desa', 'Teknis',    '2024-03-01', '2024-03-05', 30, $instruktur_ids[0], 'Aula BPPMDDTT',  'selesai'],
    ['Pengelolaan Keuangan Desa',            'Keuangan',  '2024-05-10', '2024-05-14', 25, $instruktur_ids[0], 'Ruang Pelatihan A', 'selesai'],
    ['Pemasaran Digital untuk UMKM',         'Teknologi', '2024-07-15', '2024-07-19', 20, $instruktur_ids[1], 'Lab Komputer',   'selesai'],
    ['Kewirausahaan Berbasis Potensi Lokal', 'Bisnis',    '2024-09-02', '2024-09-06', 30, $instruktur_ids[0], 'Aula BPPMDDTT',  'selesai'],
    ['Sistem Informasi Desa',                'Teknologi', '2025-01-10', '2025-01-14', 20, $instruktur_ids[1], 'Lab Komputer',   'aktif'],
    ['Manajemen BUMDes',                     'Manajemen', '2025-03-05', '2025-03-09', 25, $instruktur_ids[0], 'Ruang Pelatihan B', 'aktif'],
    ['Pemberdayaan Perempuan Desa',          'Sosial',    '2024-04-15', '2024-04-19', 28, $instruktur_ids[1], 'Aula BPPMDDTT',  'selesai'],
    ['Manajemen Risiko Bencana',             'Teknis',    '2024-06-01', '2024-06-05', 22, $instruktur_ids[0], 'Ruang Pelatihan C', 'selesai'],
    ['Literasi Digital untuk Petani',        'Teknologi', '2024-08-20', '2024-08-24', 35, $instruktur_ids[1], 'Lab Komputer',   'selesai'],
    ['Penguatan Organisasi Masyarakat',      'Organisasi','2024-10-10', '2024-10-14', 26, $instruktur_ids[0], 'Ruang Pelatihan A', 'selesai'],
    ['Pariwisata Berkelanjutan',             'Pariwisata','2025-02-15', '2025-02-19', 24, $instruktur_ids[1], 'Aula BPPMDDTT',  'aktif'],
];

$pelatihan_ids = [];
foreach ($pelatihan_data as $p) {
    $nama   = mysqli_real_escape_string($koneksi, $p[0]);
    $jenis  = $p[1]; $tgl_m = $p[2]; $tgl_s = $p[3];
    $kuota  = $p[4]; $instr = $p[5]; $lok = $p[6]; $status = $p[7];
    $cek = mysqli_fetch_row(mysqli_query($koneksi, "SELECT id FROM pelatihan WHERE nama_pelatihan='$nama'"));
    if ($cek) { $pelatihan_ids[] = $cek[0]; continue; }
    mysqli_query($koneksi, "INSERT INTO pelatihan (nama_pelatihan, jenis, tanggal_mulai, tanggal_selesai, kuota, instruktur_id, lokasi, status)
        VALUES ('$nama','$jenis','$tgl_m','$tgl_s',$kuota,$instr,'$lok','$status')");
    $pelatihan_ids[] = mysqli_insert_id($koneksi);
    $log[] = "✅ Pelatihan ditambahkan: $nama";
}

// ============================================================
// 4. ALUMNI
// ============================================================
$alumni_emails = [
    'alumni1@bppmddtt.go.id' => ['6308011234560001', 'Banjarmasin', '1995-03-15', 'L', 'Jl. A. Yani No. 10, Banjarmasin', '081234567001', '2024-03-05'],
    'alumni2@bppmddtt.go.id' => ['6308012345670002', 'Martapura',   '1997-07-22', 'P', 'Jl. Veteran No. 5, Banjarbaru',   '081234567002', '2024-05-14'],
    'alumni3@bppmddtt.go.id' => ['6308013456780003', 'Barabai',     '1996-11-30', 'L', 'Jl. Sudirman No. 20, Barabai',    '081234567003', '2024-07-19'],
    'alumni4@bppmddtt.go.id' => ['6308014567890004', 'Pelaihari',   '1998-05-08', 'P', 'Jl. Diponegoro No. 7, Pelaihari', '081234567004', '2024-09-06'],
];

// Tambah lebih banyak alumni untuk report
$extra_alumni = [
    ['Siti Minarti',         'alumni5@bppmddtt.go.id', '6308015678901005', 'Banjarmasin', '1994-02-10', 'P', 'Jl. Lambung Mangkurat No. 15', '082345678901', '2024-04-19'],
    ['Bambang Suryanto',     'alumni6@bppmddtt.go.id', '6308016789012006', 'Banjarbaru',  '1993-08-25', 'L', 'Jl. Jenderal Ahmad Yani No. 25', '082345678902', '2024-06-05'],
    ['Nila Kusuma',          'alumni7@bppmddtt.go.id', '6308017890123007', 'Martapura',   '1996-01-12', 'P', 'Jl. Merdeka No. 8, Martapura', '082345678903', '2024-08-24'],
    ['Hendra Wijaya',        'alumni8@bppmddtt.go.id', '6308018901234008', 'Banjar Baru', '1995-09-03', 'L', 'Jl. Soekarno No. 30, Banjar', '082345678904', '2024-10-14'],
    ['Fiona Amelia Putri',   'alumni9@bppmddtt.go.id', '6308019012345009', 'Pelaihari',   '1997-04-20', 'P', 'Jl. Gatot Subroto No. 12', '082345678905', '2025-02-19'],
    ['Dodi Irawan',          'alumni10@bppmddtt.go.id','6308020123456010', 'Banjarmasin', '1994-12-07', 'L', 'Jl. Jalan Dahlia No. 5', '082345678906', '2024-03-05'],
];

foreach ($extra_alumni as $ea) {
    $name  = mysqli_real_escape_string($koneksi, $ea[0]);
    $email = $ea[1];
    $cek = mysqli_fetch_row(mysqli_query($koneksi, "SELECT id FROM users WHERE email='$email'"));
    if (!$cek) {
        $hash  = password_hash('password123', PASSWORD_BCRYPT);
        mysqli_query($koneksi, "INSERT INTO users (name, email, password, role) VALUES ('$name','$email','$hash','alumni')");
        $uid = mysqli_insert_id($koneksi);
        $alumni_emails[$email] = [$ea[2], $ea[3], $ea[4], $ea[5], $ea[6], $ea[7], $ea[8]];
    }
}

$alumni_ids = [];
foreach ($alumni_emails as $email => $data) {
    $uid = mysqli_fetch_row(mysqli_query($koneksi, "SELECT id FROM users WHERE email='$email'"))[0];
    $cek = mysqli_fetch_row(mysqli_query($koneksi, "SELECT id FROM alumni WHERE user_id=$uid"));
    if ($cek) { $alumni_ids[$email] = $cek[0]; continue; }
    mysqli_query($koneksi, "INSERT INTO alumni (user_id, nik, tempat_lahir, tanggal_lahir, jenis_kelamin, alamat, telepon, tanggal_lulus)
        VALUES ($uid,'{$data[0]}','{$data[1]}','{$data[2]}','{$data[3]}','{$data[4]}','{$data[5]}','{$data[6]}')");
    $alumni_ids[$email] = mysqli_insert_id($koneksi);
    $log[] = "✅ Alumni ditambahkan: $email";
}

// ============================================================
// 5. PESERTA PELATIHAN
// ============================================================
$pp_data = [
    // [user_id, pelatihan_id, kehadiran, nilai, status_lulus]
    [$user_ids['alumni1@bppmddtt.go.id'], $pelatihan_ids[0], 'hadir', 85.5, 'lulus'],
    [$user_ids['alumni1@bppmddtt.go.id'], $pelatihan_ids[2], 'hadir', 90.0, 'lulus'],
    [$user_ids['alumni1@bppmddtt.go.id'], $pelatihan_ids[4], 'hadir', null, 'belum_dinilai'],
    [$user_ids['alumni2@bppmddtt.go.id'], $pelatihan_ids[1], 'hadir', 78.0, 'lulus'],
    [$user_ids['alumni2@bppmddtt.go.id'], $pelatihan_ids[3], 'hadir', 82.0, 'lulus'],
    [$user_ids['alumni3@bppmddtt.go.id'], $pelatihan_ids[2], 'hadir', 75.0, 'lulus'],
    [$user_ids['alumni3@bppmddtt.go.id'], $pelatihan_ids[3], 'izin',  60.0, 'tidak_lulus'],
    [$user_ids['alumni4@bppmddtt.go.id'], $pelatihan_ids[0], 'hadir', 88.0, 'lulus'],
    [$user_ids['alumni4@bppmddtt.go.id'], $pelatihan_ids[1], 'hadir', 91.0, 'lulus'],
    [$user_ids['peserta1@bppmddtt.go.id'], $pelatihan_ids[4], 'hadir', null, 'belum_dinilai'],
    [$user_ids['peserta2@bppmddtt.go.id'], $pelatihan_ids[5], 'hadir', null, 'belum_dinilai'],
    // Tambah peserta untuk pelatihan tambahan (7-10 record per pelatihan)
    [mysqli_fetch_row(mysqli_query($koneksi, "SELECT id FROM users WHERE email='alumni5@bppmddtt.go.id'"))[0], $pelatihan_ids[6], 'hadir', 86.0, 'lulus'],
    [mysqli_fetch_row(mysqli_query($koneksi, "SELECT id FROM users WHERE email='alumni6@bppmddtt.go.id'"))[0], $pelatihan_ids[6], 'hadir', 79.0, 'lulus'],
    [mysqli_fetch_row(mysqli_query($koneksi, "SELECT id FROM users WHERE email='alumni7@bppmddtt.go.id'"))[0], $pelatihan_ids[7], 'hadir', 92.0, 'lulus'],
    [mysqli_fetch_row(mysqli_query($koneksi, "SELECT id FROM users WHERE email='alumni8@bppmddtt.go.id'"))[0], $pelatihan_ids[7], 'hadir', 81.0, 'lulus'],
    [mysqli_fetch_row(mysqli_query($koneksi, "SELECT id FROM users WHERE email='alumni9@bppmddtt.go.id'"))[0], $pelatihan_ids[8], 'hadir', 87.0, 'lulus'],
    [mysqli_fetch_row(mysqli_query($koneksi, "SELECT id FROM users WHERE email='alumni10@bppmddtt.go.id'"))[0], $pelatihan_ids[8], 'hadir', 76.0, 'lulus'],
    [$user_ids['alumni1@bppmddtt.go.id'], $pelatihan_ids[6], 'hadir', 89.0, 'lulus'],
    [$user_ids['alumni2@bppmddtt.go.id'], $pelatihan_ids[7], 'hadir', 84.0, 'lulus'],
    [$user_ids['alumni3@bppmddtt.go.id'], $pelatihan_ids[8], 'hadir', 73.0, 'lulus'],
    [$user_ids['alumni4@bppmddtt.go.id'], $pelatihan_ids[9], 'hadir', 88.5, 'lulus'],
    [mysqli_fetch_row(mysqli_query($koneksi, "SELECT id FROM users WHERE email='alumni5@bppmddtt.go.id'"))[0], $pelatihan_ids[9], 'hadir', 80.0, 'lulus'],
    [mysqli_fetch_row(mysqli_query($koneksi, "SELECT id FROM users WHERE email='alumni6@bppmddtt.go.id'"))[0], $pelatihan_ids[0], 'hadir', 83.0, 'lulus'],
    [mysqli_fetch_row(mysqli_query($koneksi, "SELECT id FROM users WHERE email='alumni7@bppmddtt.go.id'"))[0], $pelatihan_ids[1], 'hadir', 77.5, 'lulus'],
    [mysqli_fetch_row(mysqli_query($koneksi, "SELECT id FROM users WHERE email='alumni8@bppmddtt.go.id'"))[0], $pelatihan_ids[2], 'hadir', 85.0, 'lulus'],
    [mysqli_fetch_row(mysqli_query($koneksi, "SELECT id FROM users WHERE email='alumni9@bppmddtt.go.id'"))[0], $pelatihan_ids[10], 'hadir', null, 'belum_dinilai'],
    [mysqli_fetch_row(mysqli_query($koneksi, "SELECT id FROM users WHERE email='alumni10@bppmddtt.go.id'"))[0], $pelatihan_ids[10], 'hadir', null, 'belum_dinilai'],
];

foreach ($pp_data as $pp) {
    $uid = $pp[0]; $pid = $pp[1]; $kh = $pp[2];
    $nilai = $pp[3] !== null ? $pp[3] : 'NULL';
    $sl = $pp[4];
    if (!$uid || !$pid) continue;
    $cek = mysqli_fetch_row(mysqli_query($koneksi, "SELECT id FROM peserta_pelatihan WHERE user_id=$uid AND pelatihan_id=$pid"));
    if ($cek) continue;
    mysqli_query($koneksi, "INSERT INTO peserta_pelatihan (user_id, pelatihan_id, status_kehadiran, nilai, status_lulus, tanggal_daftar)
        VALUES ($uid, $pid, '$kh', $nilai, '$sl', NOW())");
}
$log[] = "✅ Data peserta pelatihan ditambahkan";

// ============================================================
// 6. KOMPETENSI
// ============================================================
$kompetensi_data = [
    ['Pengolahan Data Excel',     'Teknologi Informasi'],
    ['Manajemen Keuangan Desa',   'Keuangan'],
    ['Kewirausahaan',             'Bisnis'],
    ['Komunikasi Publik',         'Soft Skill'],
    ['Pemasaran Digital',         'Pemasaran'],
    ['Pengelolaan BUMDes',        'Manajemen'],
    ['Sistem Informasi Desa',     'Teknologi Informasi'],
];

$komp_ids = [];
foreach ($kompetensi_data as $k) {
    $nama = mysqli_real_escape_string($koneksi, $k[0]);
    $kat  = mysqli_real_escape_string($koneksi, $k[1]);
    $cek  = mysqli_fetch_row(mysqli_query($koneksi, "SELECT id FROM kompetensi WHERE nama_kompetensi='$nama'"));
    if ($cek) { $komp_ids[] = $cek[0]; continue; }
    mysqli_query($koneksi, "INSERT INTO kompetensi (nama_kompetensi, kategori) VALUES ('$nama','$kat')");
    $komp_ids[] = mysqli_insert_id($koneksi);
}
$log[] = "✅ Kompetensi ditambahkan";

// ============================================================
// 7. ALUMNI KOMPETENSI
// ============================================================
$ak_data = [
    [$alumni_ids['alumni1@bppmddtt.go.id'], $komp_ids[0], 'pelatihan'],
    [$alumni_ids['alumni1@bppmddtt.go.id'], $komp_ids[4], 'pelatihan'],
    [$alumni_ids['alumni1@bppmddtt.go.id'], $komp_ids[6], 'pelatihan'],
    [$alumni_ids['alumni2@bppmddtt.go.id'], $komp_ids[1], 'pelatihan'],
    [$alumni_ids['alumni2@bppmddtt.go.id'], $komp_ids[2], 'pelatihan'],
    [$alumni_ids['alumni3@bppmddtt.go.id'], $komp_ids[4], 'pelatihan'],
    [$alumni_ids['alumni3@bppmddtt.go.id'], $komp_ids[3], 'mandiri'],
    [$alumni_ids['alumni4@bppmddtt.go.id'], $komp_ids[0], 'pelatihan'],
    [$alumni_ids['alumni4@bppmddtt.go.id'], $komp_ids[1], 'pelatihan'],
    [$alumni_ids['alumni4@bppmddtt.go.id'], $komp_ids[5], 'pelatihan'],
];
foreach ($ak_data as $ak) {
    $cek = mysqli_fetch_row(mysqli_query($koneksi, "SELECT id FROM alumni_kompetensi WHERE alumni_id={$ak[0]} AND kompetensi_id={$ak[1]}"));
    if ($cek) continue;
    mysqli_query($koneksi, "INSERT INTO alumni_kompetensi (alumni_id, kompetensi_id, sumber) VALUES ({$ak[0]},{$ak[1]},'{$ak[2]}')");
}
$log[] = "✅ Alumni kompetensi ditambahkan";

// ============================================================
// 8. TRACER STUDY
// ============================================================
$tracer_data = [
    [$alumni_ids['alumni1@bppmddtt.go.id'], 'sudah_diisi', 'bekerja',          'PT Borneo Digital',     'Web Developer',      'Teknologi',  '4-6 juta', 5, 2, 'Pelatihan sudah sangat relevan, harap perbanyak pelatihan IT.'],
    [$alumni_ids['alumni2@bppmddtt.go.id'], 'sudah_diisi', 'wirausaha',         'Toko Online Dewi',      'Pemilik Usaha',       'Perdagangan','2-4 juta', 4, 1, 'Tambahkan materi kewirausahaan digital.'],
    [$alumni_ids['alumni3@bppmddtt.go.id'], 'sudah_diisi', 'bekerja',           'Dinas Pemberdayaan',    'Staf Administrasi',   'Pemerintahan','4-6 juta',4, 3, 'Pelatihan sudah baik, tingkatkan praktik lapangan.'],
    [$alumni_ids['alumni4@bppmddtt.go.id'], 'sudah_diisi', 'bekerja',           'Koperasi Maju Jaya',    'Manager Operasional', 'Koperasi',   '3-5 juta', 4, 2, 'Sangat relevan, pertahankan kualitas pelatihan.'],
    // Tambah lebih banyak tracer (7-10 completed entries)
    [mysqli_fetch_row(mysqli_query($koneksi, "SELECT id FROM alumni WHERE user_id IN (SELECT id FROM users WHERE email='alumni5@bppmddtt.go.id')"))[0], 'sudah_diisi', 'bekerja', 'Bank Kalsel', 'Customer Service', 'Keuangan', '3-5 juta', 5, 1, 'Pelatihan keuangan sangat membantu dalam bekerja di bank.'],
    [mysqli_fetch_row(mysqli_query($koneksi, "SELECT id FROM alumni WHERE user_id IN (SELECT id FROM users WHERE email='alumni6@bppmddtt.go.id')"))[0], 'sudah_diisi', 'wirausaha', 'Bengkel Mobil Jaya', 'Pemilik Bengkel', 'Otomotif', '5-7 juta', 3, 2, 'Manajemen BUMDes membantu mengorganisir usaha.'],
    [mysqli_fetch_row(mysqli_query($koneksi, "SELECT id FROM alumni WHERE user_id IN (SELECT id FROM users WHERE email='alumni7@bppmddtt.go.id')"))[0], 'sudah_diisi', 'melanjutkan_studi', null, null, null, null, 5, 0, 'Ingin melanjutkan ke S2 setelah menyelesaikan pelatihan.'],
    [mysqli_fetch_row(mysqli_query($koneksi, "SELECT id FROM alumni WHERE user_id IN (SELECT id FROM users WHERE email='alumni8@bppmddtt.go.id')"))[0], 'sudah_diisi', 'bekerja', 'Desa Sentosa', 'Kepala Desa', 'Pemerintahan', '6-8 juta', 5, 0, 'Pelatihan aparatur desa sangat bermanfaat untuk kepemimpinan.'],
    [mysqli_fetch_row(mysqli_query($koneksi, "SELECT id FROM alumni WHERE user_id IN (SELECT id FROM users WHERE email='alumni9@bppmddtt.go.id')"))[0], 'sudah_diisi', 'bekerja', 'LPKB Putri Kalimantan', 'Instruktur Pelatihan', 'Pendidikan', '4-6 juta', 4, 1, 'Ingin menjadi instruktur untuk berbagi ilmu dengan masyarakat.'],
    [mysqli_fetch_row(mysqli_query($koneksi, "SELECT id FROM alumni WHERE user_id IN (SELECT id FROM users WHERE email='alumni10@bppmddtt.go.id')"))[0], 'sudah_diisi', 'bekerja', 'CV Maju Maju Jaya', 'Supervisor Produksi', 'Manufaktur', '3-5 juta', 3, 2, 'Pengetahuan manajemen dari pelatihan diterapkan di tempat kerja.'],
];

foreach ($tracer_data as $t) {
    $alumni_id = $t[0];
    if (!$alumni_id) continue;
    $cek = mysqli_fetch_row(mysqli_query($koneksi, "SELECT id FROM tracer_study WHERE alumni_id=$alumni_id"));
    if ($cek) continue;
    if ($t[1] === 'sudah_diisi') {
        $perusahaan = mysqli_real_escape_string($koneksi, $t[3] ?? '');
        $jabatan    = mysqli_real_escape_string($koneksi, $t[4] ?? '');
        $bidang     = mysqli_real_escape_string($koneksi, $t[5] ?? '');
        $gaji       = mysqli_real_escape_string($koneksi, $t[6] ?? '');
        $saran      = mysqli_real_escape_string($koneksi, $t[10] ?? '');
        mysqli_query($koneksi, "INSERT INTO tracer_study
            (alumni_id, status_pengisian, tanggal_isi, status_pekerjaan, nama_perusahaan, jabatan, bidang_usaha, gaji_range, relevansi_pelatihan, waktu_tunggu_kerja, saran)
            VALUES ($alumni_id, 'sudah_diisi', NOW(), '{$t[2]}', '$perusahaan', '$jabatan', '$bidang', '$gaji', {$t[7]}, {$t[8]}, '$saran')");
    } else {
        mysqli_query($koneksi, "INSERT INTO tracer_study (alumni_id, status_pengisian) VALUES ($alumni_id, 'belum_diisi')");
    }
}
$log[] = "✅ Tracer study ditambahkan";

// ============================================================
// 8.5 RKTL (Routine Mentoring/Pendampingan)
// ============================================================
$rktl_data = [
    [$alumni_ids['alumni1@bppmddtt.go.id'], $pelatihan_ids[0], $instruktur_ids[0], '2024-04-15', 'Pendampingan pemahaman kebijakan desa', 'hadir', 'Peserta sangat antusias'],
    [$alumni_ids['alumni2@bppmddtt.go.id'], $pelatihan_ids[1], $instruktur_ids[0], '2024-06-10', 'Praktek pengelolaan laporan keuangan', 'hadir', 'Sudah dapat menerapkan di lapangan'],
    [$alumni_ids['alumni3@bppmddtt.go.id'], $pelatihan_ids[2], $instruktur_ids[1], '2024-08-05', 'Konsultasi strategi pemasaran digital', 'hadir', 'Mulai implementasi media sosial'],
    [$alumni_ids['alumni4@bppmddtt.go.id'], $pelatihan_ids[3], $instruktur_ids[0], '2024-10-12', 'Pendampingan pengembangan usaha kecil', 'hadir', 'Bisnis mulai berkembang'],
    [mysqli_fetch_row(mysqli_query($koneksi, "SELECT id FROM alumni WHERE user_id IN (SELECT id FROM users WHERE email='alumni5@bppmddtt.go.id')"))[0], $pelatihan_ids[6], $instruktur_ids[1], '2024-05-20', 'Evaluasi pemberdayaan perempuan', 'hadir', 'Program berjalan efektif'],
    [mysqli_fetch_row(mysqli_query($koneksi, "SELECT id FROM alumni WHERE user_id IN (SELECT id FROM users WHERE email='alumni6@bppmddtt.go.id')"))[0], $pelatihan_ids[7], $instruktur_ids[0], '2024-07-08', 'Sosialisasi manajemen risiko bencana', 'hadir', 'Masyarakat lebih siap menghadapi bencana'],
    [mysqli_fetch_row(mysqli_query($koneksi, "SELECT id FROM alumni WHERE user_id IN (SELECT id FROM users WHERE email='alumni7@bppmddtt.go.id')"))[0], $pelatihan_ids[8], $instruktur_ids[1], '2024-09-15', 'Mentoring literasi digital petani', 'hadir', 'Petani sudah menggunakan aplikasi'],
    [mysqli_fetch_row(mysqli_query($koneksi, "SELECT id FROM alumni WHERE user_id IN (SELECT id FROM users WHERE email='alumni8@bppmddtt.go.id')"))[0], $pelatihan_ids[9], $instruktur_ids[0], '2024-11-10', 'Pendampingan penguatan organisasi', 'hadir', 'Organisasi lebih terstruktur'],
    [mysqli_fetch_row(mysqli_query($koneksi, "SELECT id FROM alumni WHERE user_id IN (SELECT id FROM users WHERE email='alumni9@bppmddtt.go.id')"))[0], $pelatihan_ids[10], $instruktur_ids[1], '2025-03-10', 'Konsultasi strategi pariwisata berkelanjutan', 'hadir', 'Siap meluncurkan paket wisata lokal'],
    [mysqli_fetch_row(mysqli_query($koneksi, "SELECT id FROM alumni WHERE user_id IN (SELECT id FROM users WHERE email='alumni10@bppmddtt.go.id')"))[0], $pelatihan_ids[5], $instruktur_ids[0], '2025-02-25', 'Evaluasi manajemen BUMDes', 'hadir', 'Laporan keuangan terorganisir'],
];

foreach ($rktl_data as $r) {
    if (!$r[0] || !$r[1] || !$r[2]) continue;
    $tgl = $r[3];
    $cek = mysqli_fetch_row(mysqli_query($koneksi, "SELECT id FROM rktl WHERE alumni_id={$r[0]} AND pelatihan_id={$r[1]} AND tgl_pendampingan='$tgl'"));
    if ($cek) continue;
    $topik = mysqli_real_escape_string($koneksi, $r[4]);
    $hasil = mysqli_real_escape_string($koneksi, $r[6]);
    mysqli_query($koneksi, "INSERT INTO rktl (alumni_id, pelatihan_id, instruktur_id, tgl_pendampingan, topik, kehadiran, hasil)
        VALUES ({$r[0]},{$r[1]},{$r[2]},'$tgl','$topik','{$r[5]}','$hasil')");
}
$log[] = "✅ RKTL Pendampingan ditambahkan";

// ============================================================
// 9. REKOMENDASI
// ============================================================
$rek_data = [
    [$alumni_ids['alumni1@bppmddtt.go.id'], $pelatihan_ids[5], 85.00, 'Sesuai dengan kompetensi pengembangan masyarakat Anda.'],
    [$alumni_ids['alumni2@bppmddtt.go.id'], $pelatihan_ids[4], 80.00, 'Pelatihan Sistem Informasi Desa cocok untuk wirausaha digital.'],
    [$alumni_ids['alumni3@bppmddtt.go.id'], $pelatihan_ids[5], 78.00, 'Manajemen BUMDes relevan dengan pengalaman kerja Anda di pemerintahan.'],
    [$alumni_ids['alumni4@bppmddtt.go.id'], $pelatihan_ids[4], 90.00, 'Tingkatkan kompetensi digital Anda dengan pelatihan ini.'],
    [$alumni_ids['alumni4@bppmddtt.go.id'], $pelatihan_ids[5], 75.00, 'Pengelolaan BUMDes sesuai latar belakang keuangan Anda.'],
    // Tambah lebih banyak rekomendasi (7-10 records)
    [mysqli_fetch_row(mysqli_query($koneksi, "SELECT id FROM alumni WHERE user_id IN (SELECT id FROM users WHERE email='alumni5@bppmddtt.go.id')"))[0], $pelatihan_ids[10], 82.00, 'Pariwisata berkelanjutan sesuai dengan visi pengembangan daerah Anda.'],
    [mysqli_fetch_row(mysqli_query($koneksi, "SELECT id FROM alumni WHERE user_id IN (SELECT id FROM users WHERE email='alumni6@bppmddtt.go.id')"))[0], $pelatihan_ids[9], 79.00, 'Penguatan organisasi masyarakat cocok untuk meningkatkan partisipasi.'],
    [mysqli_fetch_row(mysqli_query($koneksi, "SELECT id FROM alumni WHERE user_id IN (SELECT id FROM users WHERE email='alumni7@bppmddtt.go.id')"))[0], $pelatihan_ids[8], 88.00, 'Literasi digital untuk petani akan meningkatkan produktivitas usaha Anda.'],
    [mysqli_fetch_row(mysqli_query($koneksi, "SELECT id FROM alumni WHERE user_id IN (SELECT id FROM users WHERE email='alumni8@bppmddtt.go.id')"))[0], $pelatihan_ids[7], 86.00, 'Manajemen risiko bencana penting untuk ketahanan masyarakat.'],
    [mysqli_fetch_row(mysqli_query($koneksi, "SELECT id FROM alumni WHERE user_id IN (SELECT id FROM users WHERE email='alumni9@bppmddtt.go.id')"))[0], $pelatihan_ids[6], 84.00, 'Pemberdayaan perempuan desa sesuai dengan fokus program kami.'],
];

foreach ($rek_data as $r) {
    if (!isset($r[0]) || !isset($r[1])) continue;
    $cek = mysqli_fetch_row(mysqli_query($koneksi, "SELECT id FROM rekomendasi WHERE alumni_id={$r[0]} AND pelatihan_id={$r[1]}"));
    if ($cek) continue;
    $alasan = mysqli_real_escape_string($koneksi, $r[3]);
    mysqli_query($koneksi, "INSERT INTO rekomendasi (alumni_id, pelatihan_id, skor, alasan) VALUES ({$r[0]},{$r[1]},{$r[2]},'$alasan')");
}
$log[] = "✅ Rekomendasi ditambahkan";

?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Seed Data</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="p-4" style="max-width:700px;margin:auto">
  <h5 class="fw-bold mb-3">Seed Data Selesai</h5>
  <?php foreach ($log as $l): ?>
    <div class="mb-1" style="font-size:14px"><?= $l ?></div>
  <?php endforeach; ?>
  <hr>
  <h6 class="fw-bold mt-3">Akun untuk Login Testing:</h6>
  <table class="table table-bordered table-sm mt-2" style="font-size:13px">
    <thead class="table-dark"><tr><th>Role</th><th>Email</th><th>Password</th></tr></thead>
    <tbody>
      <tr><td>Admin</td><td>admin@bppmddtt.go.id</td><td>admin123</td></tr>
      <tr><td>Instruktur</td><td>instruktur1@bppmddtt.go.id</td><td>password123</td></tr>
      <tr><td>Instruktur</td><td>instruktur2@bppmddtt.go.id</td><td>password123</td></tr>
      <tr><td>Alumni</td><td>alumni1@bppmddtt.go.id</td><td>password123</td></tr>
      <tr><td>Alumni</td><td>alumni2@bppmddtt.go.id</td><td>password123</td></tr>
      <tr><td>Alumni</td><td>alumni3@bppmddtt.go.id</td><td>password123</td></tr>
      <tr><td>Alumni</td><td>alumni4@bppmddtt.go.id</td><td>password123</td></tr>
      <tr><td>Alumni</td><td>alumni5@bppmddtt.go.id</td><td>password123</td></tr>
      <tr><td>Alumni</td><td>alumni6@bppmddtt.go.id</td><td>password123</td></tr>
      <tr><td>Alumni</td><td>alumni7@bppmddtt.go.id</td><td>password123</td></tr>
      <tr><td>Alumni</td><td>alumni8@bppmddtt.go.id</td><td>password123</td></tr>
      <tr><td>Alumni</td><td>alumni9@bppmddtt.go.id</td><td>password123</td></tr>
      <tr><td>Alumni</td><td>alumni10@bppmddtt.go.id</td><td>password123</td></tr>
      <tr><td>Peserta</td><td>peserta1@bppmddtt.go.id</td><td>password123</td></tr>
      <tr><td>Peserta</td><td>peserta2@bppmddtt.go.id</td><td>password123</td></tr>
    </tbody>
  </table>
  <div class="alert alert-danger mt-3"><strong>⚠️ Hapus file seed_data.php setelah selesai testing!</strong></div>
</body>
</html>
