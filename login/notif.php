<?php
/**
 * notif.php - Helper Notifikasi
 * Include di header.php masing-masing role
 * Taruh di folder login/ (sejajar koneksi.php)
 */

$notif_list = [];

// ============================================================
// NOTIFIKASI UNTUK ALUMNI
// ============================================================
if (isset($_SESSION['level']) && $_SESSION['level'] === 'alumni') {
    $uid = $_SESSION['id_login'];
    $al  = mysqli_fetch_assoc(mysqli_query($koneksi,
        "SELECT id FROM alumni WHERE user_id=$uid"));
    $aid = $al['id'] ?? 0;

    if ($aid > 0) {
        // 1. Tracer study belum diisi
        $ts = mysqli_fetch_row(mysqli_query($koneksi,
            "SELECT COUNT(*) FROM tracer_study
             WHERE alumni_id=$aid AND status_pengisian='belum_diisi'"))[0];
        if ($ts > 0) {
            $notif_list[] = [
                'type'  => 'warning',
                'icon'  => 'bi-clipboard-x',
                'pesan' => 'Anda memiliki tracer study yang belum diisi.',
                'link'  => 'tracer.php',
                'label' => 'Isi Sekarang',
            ];
        }

        // 2. RKTL jatuh tempo dalam 7 hari
        $rktl_jt = mysqli_fetch_row(mysqli_query($koneksi,
            "SELECT COUNT(*) FROM rktl
             WHERE alumni_id=$aid
             AND tgl_pendampingan BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
             AND status != 'selesai'"))[0];
        if ($rktl_jt > 0) {
            $notif_list[] = [
                'type'  => 'danger',
                'icon'  => 'bi-alarm',
                'pesan' => "$rktl_jt RKTL jatuh tempo dalam 7 hari.",
                'link'  => 'rktl.php',
                'label' => 'Lihat RKTL',
            ];
        }

        // 3. Ada rekomendasi baru yang belum dilihat
        $rek = mysqli_fetch_row(mysqli_query($koneksi,
            "SELECT COUNT(*) FROM rekomendasi
             WHERE alumni_id=$aid AND is_dilihat=0"))[0];
        if ($rek > 0) {
            $notif_list[] = [
                'type'  => 'info',
                'icon'  => 'bi-stars',
                'pesan' => "$rek rekomendasi pelatihan baru untuk Anda.",
                'link'  => 'rekomendasi.php',
                'label' => 'Lihat',
            ];
        }
    }
}

// ============================================================
// NOTIFIKASI UNTUK INSTRUKTUR
// ============================================================
elseif (isset($_SESSION['level']) && $_SESSION['level'] === 'instruktur') {
    $instr = mysqli_fetch_assoc(mysqli_query($koneksi,
        "SELECT id FROM instruktur WHERE user_id={$_SESSION['id_login']}"));
    $iid = $instr['id'] ?? 0;

    if ($iid > 0) {
        // 1. Peserta belum dinilai
        $belum = mysqli_fetch_row(mysqli_query($koneksi,
            "SELECT COUNT(*) FROM peserta_pelatihan pp
             JOIN pelatihan p ON pp.pelatihan_id=p.id
             WHERE p.instruktur_id=$iid AND pp.status_lulus='belum_dinilai'"))[0];
        if ($belum > 0) {
            $notif_list[] = [
                'type'  => 'warning',
                'icon'  => 'bi-pencil-square',
                'pesan' => "$belum peserta belum dinilai.",
                'link'  => 'peserta.php',
                'label' => 'Input Nilai',
            ];
        }

        // 2. RKTL jatuh tempo dalam 7 hari
        $rktl_jt = mysqli_fetch_row(mysqli_query($koneksi,
            "SELECT COUNT(*) FROM rktl
             WHERE instruktur_id=$iid
             AND tgl_pendampingan BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
             AND status != 'selesai'"))[0];
        if ($rktl_jt > 0) {
            $notif_list[] = [
                'type'  => 'danger',
                'icon'  => 'bi-alarm',
                'pesan' => "$rktl_jt pendampingan RKTL jatuh tempo dalam 7 hari.",
                'link'  => 'rktl.php',
                'label' => 'Lihat RKTL',
            ];
        }

        // 3. RKTL yang sudah lewat jatuh tempo
        $rktl_lewat = mysqli_fetch_row(mysqli_query($koneksi,
            "SELECT COUNT(*) FROM rktl
             WHERE instruktur_id=$iid
             AND tgl_pendampingan < CURDATE()
             AND status NOT IN ('selesai')"))[0];
        if ($rktl_lewat > 0) {
            $notif_list[] = [
                'type'  => 'danger',
                'icon'  => 'bi-exclamation-triangle',
                'pesan' => "$rktl_lewat pendampingan RKTL sudah melewati jadwal.",
                'link'  => 'rktl.php',
                'label' => 'Segera Isi',
            ];
        }
    }
}

// ============================================================
// NOTIFIKASI UNTUK ADMIN
// ============================================================
elseif (isset($_SESSION['level']) && $_SESSION['level'] === 'admin') {

    // 1. Tracer belum terisi
    $ts_belum = mysqli_fetch_row(mysqli_query($koneksi,
        "SELECT COUNT(*) FROM tracer_study WHERE status_pengisian='belum_diisi'"))[0];
    if ($ts_belum > 0) {
        $notif_list[] = [
            'type'  => 'warning',
            'icon'  => 'bi-clipboard-x',
            'pesan' => "$ts_belum alumni belum mengisi tracer study.",
            'link'  => 'tracer.php',
            'label' => 'Lihat',
        ];
    }

    // 2. RKTL terhambat
    $rktl_hambat = mysqli_fetch_row(mysqli_query($koneksi,
        "SELECT COUNT(*) FROM rktl WHERE status='terhambat'"))[0];
    if ($rktl_hambat > 0) {
        $notif_list[] = [
            'type'  => 'danger',
            'icon'  => 'bi-x-circle',
            'pesan' => "$rktl_hambat RKTL alumni dalam status terhambat.",
            'link'  => 'rktl.php?filter=terhambat',
            'label' => 'Lihat',
        ];
    }

    // 3. Pelatihan aktif yang sudah lewat tanggal selesai
    $pel_lewat = mysqli_fetch_row(mysqli_query($koneksi,
        "SELECT COUNT(*) FROM pelatihan
         WHERE status='aktif' AND tanggal_selesai < CURDATE()"))[0];
    if ($pel_lewat > 0) {
        $notif_list[] = [
            'type'  => 'warning',
            'icon'  => 'bi-calendar-x',
            'pesan' => "$pel_lewat pelatihan sudah selesai tapi status masih aktif.",
            'link'  => 'pelatihan.php',
            'label' => 'Update Status',
        ];
    }

    // 4. Rekomendasi belum digenerate
    $rek_kosong = mysqli_fetch_row(mysqli_query($koneksi,
        "SELECT COUNT(*) FROM alumni a
         WHERE NOT EXISTS (SELECT 1 FROM rekomendasi r WHERE r.alumni_id=a.id)"))[0];
    if ($rek_kosong > 0) {
        $notif_list[] = [
            'type'  => 'info',
            'icon'  => 'bi-stars',
            'pesan' => "$rek_kosong alumni belum memiliki rekomendasi pelatihan.",
            'link'  => 'rekomendasi.php',
            'label' => 'Generate',
        ];
    }
}

// Total notifikasi (untuk badge)
$total_notif = count($notif_list);
?>
