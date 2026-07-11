-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 05, 2026 at 05:00 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_bppmddtt`
--

-- --------------------------------------------------------

--
-- Table structure for table `alumni`
--

CREATE TABLE `alumni` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `nik` varchar(20) DEFAULT NULL,
  `tempat_lahir` varchar(100) DEFAULT NULL,
  `tanggal_lahir` date DEFAULT NULL,
  `jenis_kelamin` enum('L','P') DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `telepon` varchar(20) DEFAULT NULL,
  `tanggal_lulus` date DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `alumni`
--

INSERT INTO `alumni` (`id`, `user_id`, `nik`, `tempat_lahir`, `tanggal_lahir`, `jenis_kelamin`, `alamat`, `telepon`, `tanggal_lulus`, `foto`, `created_at`, `updated_at`) VALUES
(1, 4, '6308011234560001', 'Banjarmasin', '1995-03-15', 'L', 'Jl. A. Yani No. 10, Banjarmasin', '081234567001', '2024-03-05', NULL, '2026-06-02 00:27:11', '2026-06-02 00:27:11'),
(2, 5, '6308012345670002', 'Martapura', '1997-07-22', 'P', 'Jl. Veteran No. 5, Banjarbaru', '081234567002', '2024-05-14', NULL, '2026-06-02 00:27:11', '2026-06-02 00:27:11'),
(3, 6, '6308013456780003', 'Barabai', '1996-11-30', 'L', 'Jl. Sudirman No. 20, Barabai', '081234567003', '2024-07-19', NULL, '2026-06-02 00:27:11', '2026-06-02 00:27:11'),
(4, 7, '6308014567890004', 'Pelaihari', '1998-05-08', 'P', 'Jl. Diponegoro No. 7, Pelaihari', '081234567004', '2024-09-06', NULL, '2026-06-02 00:27:11', '2026-06-02 00:27:11'),
(5, 10, '6308015678901005', 'Banjarmasin', '1994-02-10', 'P', 'Jl. Lambung Mangkurat No. 15', '082345678901', '2024-04-19', NULL, '2026-06-02 00:27:11', '2026-06-02 00:27:11'),
(6, 11, '6308016789012006', 'Banjarbaru', '1993-08-25', 'L', 'Jl. Jenderal Ahmad Yani No. 25', '082345678902', '2024-06-05', NULL, '2026-06-02 00:27:11', '2026-06-02 00:27:11'),
(7, 12, '6308017890123007', 'Martapura', '1996-01-12', 'P', 'Jl. Merdeka No. 8, Martapura', '082345678903', '2024-08-24', NULL, '2026-06-02 00:27:11', '2026-06-02 00:27:11'),
(8, 13, '6308018901234008', 'Banjar Baru', '1995-09-03', 'L', 'Jl. Soekarno No. 30, Banjar', '082345678904', '2024-10-14', NULL, '2026-06-02 00:27:11', '2026-06-02 00:27:11'),
(9, 14, '6308019012345009', 'Pelaihari', '1997-04-20', 'P', 'Jl. Gatot Subroto No. 12', '082345678905', '2025-02-19', NULL, '2026-06-02 00:27:11', '2026-06-02 00:27:11'),
(10, 15, '6308020123456010', 'Banjarmasin', '1994-12-07', 'L', 'Jl. Jalan Dahlia No. 5', '082345678906', '2024-03-05', NULL, '2026-06-02 00:27:11', '2026-06-02 00:27:11'),
(11, 16, '6308021504930011', 'Kandangan', '1993-04-15', 'L', 'Jl. Pahlawan No. 3, Kandangan, HSS', '083456789011', '2024-07-19', NULL, '2026-01-15 00:00:00', '2026-01-15 00:00:00'),
(12, 18, '6308022809960012', 'Amuntai', '1996-09-28', 'P', 'Jl. Pemuda No. 7, Amuntai, HSU', '083456789012', '2024-09-06', NULL, '2026-01-15 00:00:00', '2026-01-15 00:00:00'),
(13, 9, NULL, NULL, NULL, NULL, NULL, NULL, '2025-03-09', NULL, '2026-06-11 08:13:44', '2026-06-11 08:13:44'),
(14, 19, NULL, NULL, NULL, NULL, NULL, NULL, '2025-03-09', NULL, '2026-06-11 08:13:51', '2026-06-11 08:13:51'),
(15, 20, NULL, NULL, NULL, NULL, NULL, NULL, '2025-03-09', NULL, '2026-06-11 08:14:10', '2026-06-11 08:14:10');

-- --------------------------------------------------------

--
-- Table structure for table `alumni_kompetensi`
--

CREATE TABLE `alumni_kompetensi` (
  `id` int(10) UNSIGNED NOT NULL,
  `alumni_id` int(10) UNSIGNED NOT NULL,
  `kompetensi_id` int(10) UNSIGNED NOT NULL,
  `sumber` enum('pelatihan','mandiri','pengalaman_kerja') NOT NULL DEFAULT 'pelatihan',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `alumni_kompetensi`
--

INSERT INTO `alumni_kompetensi` (`id`, `alumni_id`, `kompetensi_id`, `sumber`, `created_at`) VALUES
(1, 1, 1, 'pelatihan', '2026-06-02 00:27:11'),
(2, 1, 5, 'pelatihan', '2026-06-02 00:27:11'),
(3, 1, 7, 'pelatihan', '2026-06-02 00:27:11'),
(4, 2, 2, 'pelatihan', '2026-06-02 00:27:11'),
(5, 2, 3, 'pelatihan', '2026-06-02 00:27:11'),
(6, 3, 5, 'pelatihan', '2026-06-02 00:27:11'),
(7, 3, 4, 'mandiri', '2026-06-02 00:27:11'),
(8, 4, 1, 'pelatihan', '2026-06-02 00:27:11'),
(9, 4, 2, 'pelatihan', '2026-06-02 00:27:11'),
(10, 4, 6, 'pelatihan', '2026-06-02 00:27:11');

-- --------------------------------------------------------

--
-- Table structure for table `instruktur`
--

CREATE TABLE `instruktur` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `nik` varchar(20) DEFAULT NULL,
  `bidang_keahlian` varchar(150) NOT NULL,
  `pendidikan` varchar(100) DEFAULT NULL,
  `kontak` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `instruktur`
--

INSERT INTO `instruktur` (`id`, `user_id`, `nik`, `bidang_keahlian`, `pendidikan`, `kontak`, `created_at`, `updated_at`) VALUES
(1, 2, NULL, 'Pengembangan Masyarakat', 'S2 Sosiologi', '081234560001', '2026-06-02 00:27:11', '2026-06-02 00:27:11'),
(2, 3, NULL, 'Teknologi Informasi', 'S2 Komputer', '081234560002', '2026-06-02 00:27:11', '2026-06-02 00:27:11');

-- --------------------------------------------------------

--
-- Table structure for table `kepala`
--

CREATE TABLE `kepala` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `user_id` int(10) UNSIGNED NOT NULL,
  `nip` varchar(30) DEFAULT NULL,
  `nama_lengkap` varchar(150) NOT NULL,
  `jabatan` varchar(150) NOT NULL DEFAULT 'Kepala BPPMDDTT Banjarmasin',
  `pangkat` varchar(100) DEFAULT NULL,
  `golongan` varchar(20) DEFAULT NULL,
  `tanda_tangan` varchar(255) DEFAULT NULL COMMENT 'Path file tanda tangan (opsional)',
  `is_aktif` tinyint(1) NOT NULL DEFAULT 1,
  `mulai_jabatan` date DEFAULT NULL,
  `selesai_jabatan` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci AUTO_INCREMENT=2;

--
-- Dumping data for table `kepala`
--

INSERT INTO `kepala` (`id`, `user_id`, `nip`, `nama_lengkap`, `jabatan`, `pangkat`, `golongan`, `tanda_tangan`, `is_aktif`, `mulai_jabatan`, `selesai_jabatan`, `created_at`, `updated_at`) VALUES
(1, 21, '197001012000031001', 'NURCHOLIS, S.Tr. A.B', 'Kepala Balai Pelatihan dan Pemberdayaan Masyarakat Desa', '', '', NULL, 0, '2026-06-29', NULL, '2026-06-29 01:53:23', '2026-06-29 01:53:23');

-- --------------------------------------------------------

--
-- Table structure for table `kompetensi`
--

CREATE TABLE `kompetensi` (
  `id` int(10) UNSIGNED NOT NULL,
  `nama_kompetensi` varchar(150) NOT NULL,
  `kategori` varchar(100) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `kompetensi`
--

INSERT INTO `kompetensi` (`id`, `nama_kompetensi`, `kategori`, `deskripsi`, `created_at`) VALUES
(1, 'Pengolahan Data Excel', 'Teknologi Informasi', NULL, '2026-06-02 00:27:11'),
(2, 'Manajemen Keuangan Desa', 'Keuangan', NULL, '2026-06-02 00:27:11'),
(3, 'Kewirausahaan', 'Bisnis', NULL, '2026-06-02 00:27:11'),
(4, 'Komunikasi Publik', 'Soft Skill', NULL, '2026-06-02 00:27:11'),
(5, 'Pemasaran Digital', 'Pemasaran', NULL, '2026-06-02 00:27:11'),
(6, 'Pengelolaan BUMDes', 'Manajemen', NULL, '2026-06-02 00:27:11'),
(7, 'Sistem Informasi Desa', 'Teknologi Informasi', NULL, '2026-06-02 00:27:11');

-- --------------------------------------------------------

--
-- Table structure for table `pelatihan`
--

CREATE TABLE `pelatihan` (
  `id` int(10) UNSIGNED NOT NULL,
  `nama_pelatihan` varchar(200) NOT NULL,
  `jenis` varchar(100) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `tanggal_mulai` date NOT NULL,
  `tanggal_selesai` date NOT NULL,
  `kuota` smallint(6) NOT NULL DEFAULT 30,
  `instruktur_id` int(10) UNSIGNED NOT NULL,
  `lokasi` varchar(200) DEFAULT NULL,
  `status` enum('aktif','selesai','dibatalkan') NOT NULL DEFAULT 'aktif',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pelatihan`
--

INSERT INTO `pelatihan` (`id`, `nama_pelatihan`, `jenis`, `deskripsi`, `tanggal_mulai`, `tanggal_selesai`, `kuota`, `instruktur_id`, `lokasi`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Pengembangan Kapasitas Aparatur Desa', 'Teknis', NULL, '2024-03-01', '2024-03-05', 30, 1, 'Aula BPPMDDTT', 'selesai', '2026-06-02 00:27:11', '2026-06-02 00:27:11'),
(2, 'Pengelolaan Keuangan Desa', 'Keuangan', NULL, '2024-05-10', '2024-05-14', 25, 1, 'Ruang Pelatihan A', 'selesai', '2026-06-02 00:27:11', '2026-06-02 00:27:11'),
(3, 'Pemasaran Digital untuk UMKM', 'Teknologi', NULL, '2024-07-15', '2024-07-19', 20, 2, 'Lab Komputer', 'selesai', '2026-06-02 00:27:11', '2026-06-02 00:27:11'),
(4, 'Kewirausahaan Berbasis Potensi Lokal', 'Bisnis', NULL, '2024-09-02', '2024-09-06', 30, 1, 'Aula BPPMDDTT', 'selesai', '2026-06-02 00:27:11', '2026-06-02 00:27:11'),
(5, 'Sistem Informasi Desa', 'Teknologi', NULL, '2025-01-10', '2025-01-14', 20, 2, 'Lab Komputer', 'aktif', '2026-06-02 00:27:11', '2026-06-02 00:27:11'),
(6, 'Manajemen BUMDes', 'Manajemen', NULL, '2025-03-05', '2025-03-09', 25, 1, 'Ruang Pelatihan B', 'selesai', '2026-06-02 00:27:11', '2026-06-11 08:14:32'),
(7, 'Pemberdayaan Perempuan Desa', 'Sosial', NULL, '2024-04-15', '2024-04-19', 28, 2, 'Aula BPPMDDTT', 'selesai', '2026-06-02 00:27:11', '2026-06-02 00:27:11'),
(8, 'Manajemen Risiko Bencana', 'Teknis', NULL, '2024-06-01', '2024-06-05', 22, 1, 'Ruang Pelatihan C', 'selesai', '2026-06-02 00:27:11', '2026-06-02 00:27:11'),
(9, 'Literasi Digital untuk Petani', 'Teknologi', NULL, '2024-08-20', '2024-08-24', 35, 2, 'Lab Komputer', 'selesai', '2026-06-02 00:27:11', '2026-06-02 00:27:11'),
(10, 'Penguatan Organisasi Masyarakat', 'Organisasi', NULL, '2024-10-10', '2024-10-14', 26, 1, 'Ruang Pelatihan A', 'selesai', '2026-06-02 00:27:11', '2026-06-02 00:27:11'),
(11, 'Pariwisata Berkelanjutan', 'Pariwisata', NULL, '2025-02-15', '2025-02-19', 24, 2, 'Aula BPPMDDTT', 'aktif', '2026-06-02 00:27:11', '2026-06-02 00:27:11');

-- --------------------------------------------------------

--
-- Table structure for table `persetujuan_laporan`
--

CREATE TABLE `persetujuan_laporan` (
  `id` int(10) UNSIGNED NOT NULL,
  `kode_laporan` varchar(64) NOT NULL COMMENT 'Kode unik laporan',
  `jenis` varchar(50) NOT NULL,
  `periode_dari` date NOT NULL,
  `periode_sampai` date NOT NULL,
  `kepala_id` int(10) UNSIGNED DEFAULT NULL,
  `status` enum('menunggu','diterima','ditolak') NOT NULL DEFAULT 'menunggu',
  `tgl_diterima` timestamp NULL DEFAULT NULL,
  `catatan` text DEFAULT NULL,
  `dibuat_oleh` int(10) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `persetujuan_laporan`
--

INSERT INTO `persetujuan_laporan` (`id`, `kode_laporan`, `jenis`, `periode_dari`, `periode_sampai`, `kepala_id`, `status`, `tgl_diterima`, `catatan`, `dibuat_oleh`, `created_at`) VALUES
(1, 'fe078854962f183ac5a35adaffe06921', 'alumni', '2026-01-01', '2026-06-02', 1, 'ditolak', '2026-07-04 15:38:18', 'we3qwe', 17, '2026-06-02 00:47:36'),
(2, '523f149a740fc8f17eee76c4ed404cc6', 'alumni', '2026-01-01', '2026-06-02', 1, 'ditolak', '2026-07-04 15:38:13', '2qwe2', 17, '2026-06-02 02:07:05'),
(3, 'aa203707d751b32a8fbdc50c6fcab0ea', 'tracer', '2026-01-01', '2026-06-02', 1, 'ditolak', '2026-07-04 15:38:15', 'qwew', 17, '2026-06-02 02:07:29'),
(4, 'c94e50ad2530c9736b02683791b83384', 'rekomendasi', '2026-01-01', '2026-06-02', 1, 'ditolak', '2026-07-04 15:38:11', 'we3qwe', 17, '2026-06-02 02:08:47'),
(5, '7c18aeb90fa19907efbebf84cff6acb6', 'pelatihan', '2024-01-01', '2026-06-29', 1, 'ditolak', '2026-07-04 15:38:07', 'we3q', 17, '2026-06-29 01:36:26'),
(6, 'e4eaae4a683d23e72daed2fb673f13a4', 'peserta', '2024-01-01', '2026-06-29', 1, 'ditolak', '2026-07-04 15:38:04', 'qwew', 17, '2026-06-29 01:36:48'),
(7, '5f7478af3c020f92625096e4da99ce3d', 'alumni', '2024-01-01', '2026-06-29', 1, 'ditolak', '2026-07-04 15:38:02', 'qweqw', 17, '2026-06-29 01:37:23'),
(8, 'cc77db88a8bb0e39d332a04e50268011', 'tracer', '2024-01-01', '2026-06-29', 1, 'diterima', '2026-07-04 15:37:55', NULL, 17, '2026-06-29 01:37:48'),
(9, 'acb3bf499e406f379d1306665777f3bd', 'rktl', '2024-01-01', '2026-06-29', 1, 'diterima', '2026-07-04 15:37:53', NULL, 17, '2026-06-29 01:39:09'),
(10, 'a709986394ad662735c62be83f67ac23', 'rekomendasi', '2024-01-01', '2026-06-29', 1, 'diterima', '2026-07-04 15:37:50', NULL, 17, '2026-06-29 01:39:22'),
(11, '0bb8b8e9389ac7eb4460128e9b4e9659', 'kelulusan', '2024-01-01', '2026-06-29', 1, 'diterima', '2026-07-04 15:37:45', NULL, 17, '2026-06-29 01:39:31'),
(12, 'e0a3581cbdf720612d5e774b57a57c61', 'tracer', '2024-01-01', '2026-07-03', 1, 'diterima', '2026-07-04 15:37:42', NULL, 17, '2026-07-03 14:12:15'),
(13, '599541e77eb3dc5eff73e152e4c46f7c', 'peserta', '2024-01-01', '2026-07-03', 1, 'diterima', '2026-07-04 15:37:39', NULL, 17, '2026-07-03 14:13:31'),
(14, '13f3866161ab1751f7894880fbc0c5f2', 'pelatihan', '2024-01-01', '2026-07-03', 1, 'diterima', '2026-07-04 15:35:44', NULL, 17, '2026-07-03 14:13:51');

-- --------------------------------------------------------

--
-- Table structure for table `peserta_pelatihan`
--

CREATE TABLE `peserta_pelatihan` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `pelatihan_id` int(10) UNSIGNED NOT NULL,
  `tanggal_daftar` date DEFAULT NULL,
  `status_verifikasi` enum('menunggu','diterima','ditolak') NOT NULL DEFAULT 'menunggu',
  `alasan_tolak` varchar(255) DEFAULT NULL,
  `tgl_verifikasi` timestamp NULL DEFAULT NULL,
  `diverifikasi_oleh` int(10) UNSIGNED DEFAULT NULL,
  `status_kehadiran` enum('hadir','tidak_hadir','izin') NOT NULL DEFAULT 'hadir',
  `nilai` decimal(5,2) DEFAULT NULL,
  `status_lulus` enum('lulus','tidak_lulus','belum_dinilai') NOT NULL DEFAULT 'belum_dinilai',
  `sertifikat_url` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `peserta_pelatihan`
--

INSERT INTO `peserta_pelatihan` (`id`, `user_id`, `pelatihan_id`, `tanggal_daftar`, `status_verifikasi`, `alasan_tolak`, `tgl_verifikasi`, `diverifikasi_oleh`, `status_kehadiran`, `nilai`, `status_lulus`, `sertifikat_url`, `created_at`, `updated_at`) VALUES
(1, 4, 1, '2026-06-02', 'diterima', NULL, '2026-06-02 00:35:30', 17, 'hadir', 85.50, 'lulus', NULL, '2026-06-02 00:27:11', '2026-06-02 00:35:30'),
(2, 4, 3, '2026-06-02', 'diterima', NULL, '2026-06-02 00:35:45', 17, 'hadir', 90.00, 'lulus', NULL, '2026-06-02 00:27:11', '2026-06-02 00:35:45'),
(3, 4, 5, '2026-06-02', 'diterima', NULL, '2026-06-02 00:35:57', 17, 'hadir', NULL, 'belum_dinilai', NULL, '2026-06-02 00:27:11', '2026-06-02 00:35:57'),
(4, 5, 2, '2026-06-02', 'diterima', NULL, '2026-06-02 00:35:38', 17, 'hadir', 78.00, 'lulus', NULL, '2026-06-02 00:27:11', '2026-06-02 00:35:38'),
(5, 5, 4, '2026-06-02', 'diterima', NULL, '2026-06-02 00:35:52', 17, 'hadir', 82.00, 'lulus', NULL, '2026-06-02 00:27:11', '2026-06-02 00:35:52'),
(6, 6, 3, '2026-06-02', 'diterima', NULL, '2026-06-02 00:35:47', 17, 'hadir', 75.00, 'lulus', NULL, '2026-06-02 00:27:11', '2026-06-02 00:35:47'),
(7, 6, 4, '2026-06-02', 'diterima', NULL, '2026-06-02 00:35:54', 17, 'izin', 60.00, 'tidak_lulus', NULL, '2026-06-02 00:27:11', '2026-06-02 00:35:54'),
(8, 7, 1, '2026-06-02', 'diterima', NULL, '2026-06-02 00:35:33', 17, 'hadir', 88.00, 'lulus', NULL, '2026-06-02 00:27:11', '2026-06-02 00:35:33'),
(9, 7, 2, '2026-06-02', 'diterima', NULL, '2026-06-02 00:35:41', 17, 'hadir', 91.00, 'lulus', NULL, '2026-06-02 00:27:11', '2026-06-02 00:35:41'),
(10, 8, 5, '2026-06-02', 'diterima', NULL, '2026-06-02 00:36:00', 17, 'hadir', NULL, 'belum_dinilai', NULL, '2026-06-02 00:27:11', '2026-06-02 00:36:00'),
(11, 9, 6, '2026-06-02', 'diterima', NULL, '2026-06-02 00:36:03', 17, 'hadir', 80.00, 'lulus', NULL, '2026-06-02 00:27:11', '2026-06-11 08:13:42'),
(12, 10, 7, '2026-06-02', 'diterima', NULL, '2026-06-02 00:36:05', 17, 'hadir', 86.00, 'lulus', NULL, '2026-06-02 00:27:11', '2026-06-02 00:36:05'),
(13, 11, 7, '2026-06-02', 'diterima', NULL, '2026-06-02 00:36:08', 17, 'hadir', 79.00, 'lulus', NULL, '2026-06-02 00:27:11', '2026-06-02 00:36:08'),
(14, 12, 8, '2026-06-02', 'diterima', NULL, '2026-06-02 00:36:13', 17, 'hadir', 92.00, 'lulus', NULL, '2026-06-02 00:27:11', '2026-06-02 00:36:13'),
(15, 13, 8, '2026-06-02', 'diterima', NULL, '2026-06-02 00:36:16', 17, 'hadir', 81.00, 'lulus', NULL, '2026-06-02 00:27:11', '2026-06-02 00:36:16'),
(16, 14, 9, '2026-06-02', 'diterima', NULL, '2026-06-02 00:36:21', 17, 'hadir', 87.00, 'lulus', NULL, '2026-06-02 00:27:11', '2026-06-02 00:36:21'),
(17, 15, 9, '2026-06-02', 'diterima', NULL, '2026-06-02 00:36:24', 17, 'hadir', 76.00, 'lulus', NULL, '2026-06-02 00:27:11', '2026-06-02 00:36:24'),
(18, 4, 7, '2026-06-02', 'diterima', NULL, '2026-06-02 00:36:11', 17, 'hadir', 89.00, 'lulus', NULL, '2026-06-02 00:27:11', '2026-06-02 00:36:11'),
(19, 5, 8, '2026-06-02', 'diterima', NULL, '2026-06-02 00:36:19', 17, 'hadir', 84.00, 'lulus', NULL, '2026-06-02 00:27:11', '2026-06-02 00:36:19'),
(20, 6, 9, '2026-06-02', 'diterima', NULL, '2026-06-02 00:36:27', 17, 'hadir', 73.00, 'lulus', NULL, '2026-06-02 00:27:11', '2026-06-02 00:36:27'),
(21, 7, 10, '2026-06-02', 'diterima', NULL, '2026-06-02 00:36:30', 17, 'hadir', 88.50, 'lulus', NULL, '2026-06-02 00:27:11', '2026-06-02 00:36:30'),
(22, 10, 10, '2026-06-02', 'diterima', NULL, '2026-06-02 00:36:32', 17, 'hadir', 80.00, 'lulus', NULL, '2026-06-02 00:27:11', '2026-06-02 00:36:32'),
(23, 11, 1, '2026-06-02', 'diterima', NULL, '2026-06-02 00:35:35', 17, 'hadir', 83.00, 'lulus', NULL, '2026-06-02 00:27:11', '2026-06-02 00:35:35'),
(24, 12, 2, '2026-06-02', 'diterima', NULL, '2026-06-02 00:35:43', 17, 'hadir', 77.50, 'lulus', NULL, '2026-06-02 00:27:11', '2026-06-02 00:35:43'),
(25, 13, 3, '2026-06-02', 'diterima', NULL, '2026-06-02 00:35:49', 17, 'hadir', 85.00, 'lulus', NULL, '2026-06-02 00:27:11', '2026-06-02 00:35:49'),
(26, 14, 11, '2026-06-02', 'diterima', NULL, '2026-06-02 00:33:55', 17, 'hadir', NULL, 'belum_dinilai', NULL, '2026-06-02 00:27:11', '2026-06-02 00:33:55'),
(27, 15, 11, '2026-06-02', 'diterima', NULL, '2026-06-02 00:36:35', 17, 'hadir', NULL, 'belum_dinilai', NULL, '2026-06-02 00:27:11', '2026-06-02 00:36:35'),
(28, 8, 11, '2026-06-02', 'diterima', NULL, '2026-06-10 12:16:21', 17, 'hadir', NULL, 'belum_dinilai', NULL, '2026-06-02 02:21:08', '2026-06-10 12:16:21'),
(29, 5, 1, '2024-02-20', 'diterima', NULL, '2024-02-20 23:00:00', 17, 'hadir', 80.00, 'lulus', NULL, '2024-02-19 16:00:00', '2024-02-20 16:00:00'),
(30, 10, 1, '2024-02-20', 'diterima', NULL, '2024-02-20 23:00:00', 17, 'hadir', 76.00, 'lulus', NULL, '2024-02-19 16:00:00', '2024-02-20 16:00:00'),
(31, 12, 1, '2024-02-20', 'diterima', NULL, '2024-02-20 23:00:00', 17, 'hadir', 83.00, 'lulus', NULL, '2024-02-19 16:00:00', '2024-02-20 16:00:00'),
(32, 13, 1, '2024-02-21', 'diterima', NULL, '2024-02-21 23:00:00', 17, 'hadir', 55.00, 'tidak_lulus', NULL, '2024-02-20 16:00:00', '2024-02-21 16:00:00'),
(33, 4, 2, '2024-05-01', 'diterima', NULL, '2024-05-01 23:00:00', 17, 'hadir', 87.00, 'lulus', NULL, '2024-04-30 16:00:00', '2024-05-01 16:00:00'),
(34, 10, 2, '2024-05-01', 'diterima', NULL, '2024-05-01 23:00:00', 17, 'hadir', 72.00, 'lulus', NULL, '2024-04-30 16:00:00', '2024-05-01 16:00:00'),
(35, 14, 2, '2024-05-01', 'diterima', NULL, '2024-05-01 23:00:00', 17, 'hadir', 79.00, 'lulus', NULL, '2024-04-30 16:00:00', '2024-05-01 16:00:00'),
(36, 16, 2, '2024-05-02', 'diterima', NULL, '2024-05-02 23:00:00', 17, 'izin', 0.00, 'tidak_lulus', NULL, '2024-05-01 16:00:00', '2024-05-02 16:00:00'),
(37, 7, 3, '2024-07-01', 'diterima', NULL, '2024-07-01 23:00:00', 17, 'hadir', 92.00, 'lulus', NULL, '2024-06-30 16:00:00', '2024-07-01 16:00:00'),
(38, 11, 3, '2024-07-01', 'diterima', NULL, '2024-07-01 23:00:00', 17, 'hadir', 81.00, 'lulus', NULL, '2024-06-30 16:00:00', '2024-07-01 16:00:00'),
(39, 15, 3, '2024-07-02', 'diterima', NULL, '2024-07-02 23:00:00', 17, 'hadir', 68.00, 'tidak_lulus', NULL, '2024-07-01 16:00:00', '2024-07-02 16:00:00'),
(40, 18, 3, '2024-07-02', 'diterima', NULL, '2024-07-02 23:00:00', 17, 'hadir', 85.00, 'lulus', NULL, '2024-07-01 16:00:00', '2024-07-02 16:00:00'),
(41, 4, 4, '2024-09-01', 'diterima', NULL, '2024-09-01 23:00:00', 17, 'hadir', 88.00, 'lulus', NULL, '2024-08-31 16:00:00', '2024-09-01 16:00:00'),
(42, 7, 4, '2024-09-01', 'diterima', NULL, '2024-09-01 23:00:00', 17, 'hadir', 90.00, 'lulus', NULL, '2024-08-31 16:00:00', '2024-09-01 16:00:00'),
(43, 13, 4, '2024-09-01', 'diterima', NULL, '2024-09-01 23:00:00', 17, 'hadir', 66.00, 'tidak_lulus', NULL, '2024-08-31 16:00:00', '2024-09-01 16:00:00'),
(44, 14, 4, '2024-09-02', 'diterima', NULL, '2024-09-02 23:00:00', 17, 'hadir', 77.00, 'lulus', NULL, '2024-09-01 16:00:00', '2024-09-02 16:00:00'),
(45, 16, 4, '2024-09-02', 'diterima', NULL, '2024-09-02 23:00:00', 17, 'hadir', 83.00, 'lulus', NULL, '2024-09-01 16:00:00', '2024-09-02 16:00:00'),
(46, 6, 5, '2024-12-20', 'diterima', NULL, '2024-12-20 23:00:00', 17, 'hadir', NULL, 'belum_dinilai', NULL, '2024-12-19 16:00:00', '2024-12-20 16:00:00'),
(47, 7, 5, '2024-12-20', 'diterima', NULL, '2024-12-20 23:00:00', 17, 'hadir', NULL, 'belum_dinilai', NULL, '2024-12-19 16:00:00', '2024-12-20 16:00:00'),
(48, 11, 5, '2024-12-21', 'diterima', NULL, '2024-12-21 23:00:00', 17, 'hadir', NULL, 'belum_dinilai', NULL, '2024-12-20 16:00:00', '2024-12-21 16:00:00'),
(49, 19, 5, '2024-12-22', 'diterima', NULL, '2026-06-11 08:20:06', 17, 'hadir', NULL, 'belum_dinilai', NULL, '2024-12-21 16:00:00', '2026-06-11 08:20:06'),
(50, 20, 5, '2024-12-22', 'diterima', NULL, '2026-06-11 08:20:08', 17, 'hadir', NULL, 'belum_dinilai', NULL, '2024-12-21 16:00:00', '2026-06-11 08:20:08'),
(51, 4, 6, '2025-02-20', 'diterima', NULL, '2025-02-20 23:00:00', 17, 'hadir', 80.00, 'lulus', NULL, '2025-02-19 16:00:00', '2026-06-11 08:13:13'),
(52, 5, 6, '2025-02-20', 'diterima', NULL, '2025-02-20 23:00:00', 17, 'hadir', 78.00, 'lulus', NULL, '2025-02-19 16:00:00', '2026-06-11 08:13:25'),
(53, 10, 6, '2025-02-20', 'diterima', NULL, '2025-02-20 23:00:00', 17, 'hadir', 90.00, 'lulus', NULL, '2025-02-19 16:00:00', '2026-06-11 08:13:56'),
(54, 16, 6, '2025-02-21', 'diterima', NULL, '2025-02-21 23:00:00', 17, 'hadir', 90.00, 'lulus', NULL, '2025-02-20 16:00:00', '2026-06-11 08:14:02'),
(55, 19, 6, '2025-02-22', 'diterima', NULL, '2026-06-11 08:20:10', 17, 'hadir', 80.00, 'lulus', NULL, '2025-02-21 16:00:00', '2026-06-11 08:20:10'),
(56, 20, 6, '2025-02-22', 'diterima', NULL, '2026-06-11 08:20:13', 17, 'hadir', 90.00, 'lulus', NULL, '2025-02-21 16:00:00', '2026-06-11 08:20:13'),
(57, 5, 7, '2024-04-01', 'diterima', NULL, '2024-04-01 23:00:00', 17, 'hadir', 88.00, 'lulus', NULL, '2024-03-31 16:00:00', '2024-04-01 16:00:00'),
(58, 7, 7, '2024-04-01', 'diterima', NULL, '2024-04-01 23:00:00', 17, 'hadir', 75.00, 'lulus', NULL, '2024-03-31 16:00:00', '2024-04-01 16:00:00'),
(59, 14, 7, '2024-04-02', 'diterima', NULL, '2024-04-02 23:00:00', 17, 'hadir', 81.00, 'lulus', NULL, '2024-04-01 16:00:00', '2024-04-02 16:00:00'),
(60, 18, 7, '2024-04-02', 'diterima', NULL, '2024-04-02 23:00:00', 17, 'izin', 58.00, 'tidak_lulus', NULL, '2024-04-01 16:00:00', '2024-04-02 16:00:00'),
(61, 4, 8, '2024-06-01', 'diterima', NULL, '2024-06-01 23:00:00', 17, 'hadir', 79.00, 'lulus', NULL, '2024-05-31 16:00:00', '2024-06-01 16:00:00'),
(62, 7, 8, '2024-06-01', 'diterima', NULL, '2024-06-01 23:00:00', 17, 'hadir', 85.00, 'lulus', NULL, '2024-05-31 16:00:00', '2024-06-01 16:00:00'),
(63, 11, 8, '2024-06-01', 'diterima', NULL, '2024-06-01 23:00:00', 17, 'hadir', 92.00, 'lulus', NULL, '2024-05-31 16:00:00', '2024-06-01 16:00:00'),
(64, 16, 8, '2024-06-02', 'diterima', NULL, '2024-06-02 23:00:00', 17, 'hadir', 73.00, 'lulus', NULL, '2024-06-01 16:00:00', '2024-06-02 16:00:00'),
(65, 4, 9, '2024-08-20', 'diterima', NULL, '2024-08-20 23:00:00', 17, 'hadir', 86.00, 'lulus', NULL, '2024-08-19 16:00:00', '2024-08-20 16:00:00'),
(66, 5, 9, '2024-08-20', 'diterima', NULL, '2024-08-20 23:00:00', 17, 'hadir', 90.00, 'lulus', NULL, '2024-08-19 16:00:00', '2024-08-20 16:00:00'),
(67, 11, 9, '2024-08-21', 'diterima', NULL, '2024-08-21 23:00:00', 17, 'hadir', 78.00, 'lulus', NULL, '2024-08-20 16:00:00', '2024-08-21 16:00:00'),
(68, 18, 9, '2024-08-21', 'diterima', NULL, '2024-08-21 23:00:00', 17, 'hadir', 83.00, 'lulus', NULL, '2024-08-20 16:00:00', '2024-08-21 16:00:00'),
(69, 4, 10, '2024-10-10', 'diterima', NULL, '2024-10-10 23:00:00', 17, 'hadir', 84.00, 'lulus', NULL, '2024-10-09 16:00:00', '2024-10-10 16:00:00'),
(70, 5, 10, '2024-10-10', 'diterima', NULL, '2024-10-10 23:00:00', 17, 'hadir', 78.00, 'lulus', NULL, '2024-10-09 16:00:00', '2024-10-10 16:00:00'),
(71, 12, 10, '2024-10-10', 'diterima', NULL, '2024-10-10 23:00:00', 17, 'hadir', 91.00, 'lulus', NULL, '2024-10-09 16:00:00', '2024-10-10 16:00:00'),
(72, 13, 10, '2024-10-11', 'diterima', NULL, '2024-10-11 23:00:00', 17, 'hadir', 67.00, 'tidak_lulus', NULL, '2024-10-10 16:00:00', '2024-10-11 16:00:00'),
(73, 16, 10, '2024-10-11', 'diterima', NULL, '2024-10-11 23:00:00', 17, 'hadir', 79.00, 'lulus', NULL, '2024-10-10 16:00:00', '2024-10-11 16:00:00'),
(74, 4, 11, '2025-02-15', 'diterima', NULL, '2025-02-15 23:00:00', 17, 'hadir', NULL, 'belum_dinilai', NULL, '2025-02-14 16:00:00', '2025-02-15 16:00:00'),
(75, 7, 11, '2025-02-15', 'diterima', NULL, '2025-02-15 23:00:00', 17, 'hadir', NULL, 'belum_dinilai', NULL, '2025-02-14 16:00:00', '2025-02-15 16:00:00'),
(76, 10, 11, '2025-02-16', 'diterima', NULL, '2025-02-16 23:00:00', 17, 'hadir', NULL, 'belum_dinilai', NULL, '2025-02-15 16:00:00', '2025-02-16 16:00:00'),
(77, 19, 11, '2025-02-17', 'diterima', NULL, '2026-06-11 08:20:15', 17, 'hadir', NULL, 'belum_dinilai', NULL, '2025-02-16 16:00:00', '2026-06-11 08:20:15');

-- --------------------------------------------------------

--
-- Table structure for table `rekomendasi`
--

CREATE TABLE `rekomendasi` (
  `id` int(10) UNSIGNED NOT NULL,
  `alumni_id` int(10) UNSIGNED NOT NULL,
  `pelatihan_id` int(10) UNSIGNED NOT NULL,
  `skor` decimal(5,2) DEFAULT NULL COMMENT 'skor relevansi dari engine rekomendasi',
  `alasan` text DEFAULT NULL,
  `is_dilihat` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rekomendasi`
--

INSERT INTO `rekomendasi` (`id`, `alumni_id`, `pelatihan_id`, `skor`, `alasan`, `is_dilihat`, `created_at`) VALUES
(11, 2, 5, 80.00, 'Pelatihan ini belum pernah diikuti dan sesuai dengan profil alumni.', 0, '2026-06-12 13:58:22'),
(12, 2, 11, 80.00, 'Pelatihan ini belum pernah diikuti dan sesuai dengan profil alumni.', 0, '2026-06-12 13:58:22'),
(13, 3, 11, 80.00, 'Pelatihan ini belum pernah diikuti dan sesuai dengan profil alumni.', 0, '2026-06-12 13:58:24'),
(14, 13, 5, 80.00, 'Pelatihan ini belum pernah diikuti dan sesuai dengan profil alumni.', 0, '2026-06-12 13:58:27'),
(15, 13, 11, 80.00, 'Pelatihan ini belum pernah diikuti dan sesuai dengan profil alumni.', 0, '2026-06-12 13:58:27'),
(16, 5, 5, 80.00, 'Pelatihan ini belum pernah diikuti dan sesuai dengan profil alumni.', 0, '2026-06-12 13:58:30'),
(17, 6, 11, 80.00, 'Pelatihan ini belum pernah diikuti dan sesuai dengan profil alumni.', 0, '2026-06-12 13:58:32'),
(18, 7, 5, 80.00, 'Pelatihan ini belum pernah diikuti dan sesuai dengan profil alumni.', 0, '2026-06-12 13:58:34'),
(19, 7, 11, 80.00, 'Pelatihan ini belum pernah diikuti dan sesuai dengan profil alumni.', 0, '2026-06-12 13:58:34'),
(20, 8, 5, 80.00, 'Pelatihan ini belum pernah diikuti dan sesuai dengan profil alumni.', 0, '2026-06-12 13:58:37'),
(21, 8, 11, 80.00, 'Pelatihan ini belum pernah diikuti dan sesuai dengan profil alumni.', 0, '2026-06-12 13:58:37'),
(22, 9, 5, 80.00, 'Pelatihan ini belum pernah diikuti dan sesuai dengan profil alumni.', 0, '2026-06-12 13:58:39'),
(23, 10, 5, 80.00, 'Pelatihan ini belum pernah diikuti dan sesuai dengan profil alumni.', 0, '2026-06-12 13:58:41'),
(24, 11, 5, 80.00, 'Pelatihan ini belum pernah diikuti dan sesuai dengan profil alumni.', 0, '2026-06-12 13:58:44'),
(25, 11, 11, 80.00, 'Pelatihan ini belum pernah diikuti dan sesuai dengan profil alumni.', 0, '2026-06-12 13:58:44'),
(26, 12, 5, 80.00, 'Pelatihan ini belum pernah diikuti dan sesuai dengan profil alumni.', 0, '2026-06-12 13:58:46'),
(27, 12, 11, 80.00, 'Pelatihan ini belum pernah diikuti dan sesuai dengan profil alumni.', 0, '2026-06-12 13:58:46'),
(28, 15, 11, 80.00, 'Pelatihan ini belum pernah diikuti dan sesuai dengan profil alumni.', 0, '2026-06-12 13:58:48');

-- --------------------------------------------------------

--
-- Table structure for table `rktl`
--

CREATE TABLE `rktl` (
  `id` int(10) UNSIGNED NOT NULL,
  `alumni_id` int(10) UNSIGNED NOT NULL,
  `pelatihan_id` int(10) UNSIGNED NOT NULL,
  `instruktur_id` int(10) UNSIGNED NOT NULL,
  `rencana` text NOT NULL COMMENT 'Rencana kerja yang akan dilakukan',
  `target_waktu` date DEFAULT NULL COMMENT 'Target waktu penyelesaian',
  `tgl_pendampingan` date DEFAULT NULL COMMENT 'Tanggal pendampingan (3 bulan setelah pelatihan)',
  `progres` tinyint(4) NOT NULL DEFAULT 0 COMMENT '0-100 persen progres',
  `status` enum('belum_mulai','berjalan','selesai','terhambat') NOT NULL DEFAULT 'belum_mulai',
  `catatan` text DEFAULT NULL COMMENT 'Catatan instruktur saat pendampingan',
  `tgl_verifikasi` date DEFAULT NULL COMMENT 'Tanggal instruktur verifikasi progres',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rktl`
--

INSERT INTO `rktl` (`id`, `alumni_id`, `pelatihan_id`, `instruktur_id`, `rencana`, `target_waktu`, `tgl_pendampingan`, `progres`, `status`, `catatan`, `tgl_verifikasi`, `created_at`, `updated_at`) VALUES
(1, 1, 6, 1, 'Belum diisi', '2026-06-11', '2025-06-09', 100, 'selesai', '', '2026-06-11', '2026-06-11 08:13:15', '2026-06-11 08:18:54'),
(2, 2, 6, 1, 'Belum diisi', '0000-00-00', '2025-06-09', 56, 'belum_mulai', '', '2026-06-11', '2026-06-11 08:13:27', '2026-06-11 08:19:03'),
(3, 13, 6, 1, 'Belum diisi', '0000-00-00', '2025-06-09', 70, 'belum_mulai', '', '2026-06-11', '2026-06-11 08:13:44', '2026-06-11 08:19:08'),
(4, 14, 6, 1, 'Belum diisi', '0000-00-00', '2025-06-09', 34, 'belum_mulai', '', '2026-06-11', '2026-06-11 08:13:51', '2026-06-11 08:19:15'),
(5, 5, 6, 1, 'Belum diisi', '0000-00-00', '2025-06-09', 20, 'belum_mulai', '', '2026-06-11', '2026-06-11 08:13:58', '2026-06-11 08:19:20'),
(6, 11, 6, 1, 'Belum diisi', '0000-00-00', '2025-06-09', 89, 'belum_mulai', '', '2026-06-11', '2026-06-11 08:14:04', '2026-06-11 08:19:24'),
(7, 15, 6, 1, 'Belum diisi', '0000-00-00', '2025-06-09', 30, 'belum_mulai', '', '2026-06-11', '2026-06-11 08:14:10', '2026-06-11 08:19:28');

-- --------------------------------------------------------

--
-- Table structure for table `tracer_study`
--

CREATE TABLE `tracer_study` (
  `id` int(10) UNSIGNED NOT NULL,
  `alumni_id` int(10) UNSIGNED NOT NULL,
  `tanggal_kirim` timestamp NOT NULL DEFAULT current_timestamp(),
  `tanggal_isi` timestamp NULL DEFAULT NULL,
  `status_pengisian` enum('terkirim','sudah_diisi','belum_diisi') NOT NULL DEFAULT 'terkirim',
  `status_pekerjaan` enum('bekerja','wirausaha','belum_bekerja','melanjutkan_studi') DEFAULT NULL,
  `nama_perusahaan` varchar(200) DEFAULT NULL,
  `jabatan` varchar(150) DEFAULT NULL,
  `bidang_usaha` varchar(150) DEFAULT NULL,
  `gaji_range` varchar(50) DEFAULT NULL,
  `relevansi_pelatihan` tinyint(4) DEFAULT NULL COMMENT '1-5: seberapa relevan pelatihan dengan pekerjaan',
  `waktu_tunggu_kerja` tinyint(4) DEFAULT NULL COMMENT 'bulan dari lulus sampai kerja',
  `saran` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tracer_study`
--

INSERT INTO `tracer_study` (`id`, `alumni_id`, `tanggal_kirim`, `tanggal_isi`, `status_pengisian`, `status_pekerjaan`, `nama_perusahaan`, `jabatan`, `bidang_usaha`, `gaji_range`, `relevansi_pelatihan`, `waktu_tunggu_kerja`, `saran`, `created_at`, `updated_at`) VALUES
(1, 1, '2026-06-01 16:27:11', '2026-06-01 16:27:11', 'sudah_diisi', 'wirausaha', 'Kelompok Tani Harapan Maju', 'Ketua Kelompok Tani', 'Pertanian/Perkebunan', '2-4 juta', 5, 1, 'Pelatihan sangat membantu dalam pengelolaan lahan pertanian organik', '2026-06-01 16:27:11', '2026-06-01 16:27:11'),
(2, 2, '2026-06-01 16:27:11', '2026-06-01 16:27:11', 'sudah_diisi', 'wirausaha', 'BUMDes Desa Makmur Sejahtera', 'Pengelola BUMDes', 'Usaha Desa/BUMDes', '2-4 juta', 5, 2, 'Ilmu manajemen usaha sangat berguna untuk mengelola BUMDes', '2026-06-01 16:27:11', '2026-06-01 16:27:11'),
(3, 3, '2026-06-01 16:27:11', '2026-06-01 16:27:11', 'sudah_diisi', 'bekerja', 'Kantor Desa Suka Maju', 'Staf Administrasi Desa', 'Pemerintah Desa', '2-3 juta', 4, 3, 'Pelatihan komputer dan administrasi sangat membantu pekerjaan di kantor desa', '2026-06-01 16:27:11', '2026-06-01 16:27:11'),
(4, 4, '2026-06-01 16:27:11', '2026-06-01 16:27:11', 'sudah_diisi', 'bekerja', 'Pemerintah Desa Karya Bakti', 'Kepala Dusun', 'Pemerintah Desa', '1-2 juta', 4, 2, 'Pelatihan kepemimpinan membantu dalam mengelola wilayah dusun', '2026-06-01 16:27:11', '2026-06-01 16:27:11'),
(5, 5, '2026-06-01 16:27:11', '2026-06-01 16:27:11', 'sudah_diisi', 'wirausaha', 'Kelompok Pengrajin Anyaman Rotan', 'Pengrajin/Wirausaha', 'Kerajinan Tangan', '1-3 juta', 5, 1, 'Pelatihan kewirausahaan membantu membuka usaha kerajinan di desa', '2026-06-01 16:27:11', '2026-06-01 16:27:11'),
(6, 6, '2026-06-01 16:27:11', '2026-06-01 16:27:11', 'sudah_diisi', 'wirausaha', 'Usaha Budidaya Ikan Lele Desa Sejahtera', 'Pemilik Usaha Budidaya', 'Perikanan/Budidaya', '2-4 juta', 4, 2, 'Ilmu budidaya yang diperoleh sangat diterapkan di kolam ikan', '2026-06-01 16:27:11', '2026-06-01 16:27:11'),
(7, 7, '2026-06-01 16:27:11', '2026-06-01 16:27:11', 'sudah_diisi', 'bekerja', 'Kawasan Transmigrasi Baru Harapan', 'Koordinator Warga Transmigran', 'Pemerintah/Transmigrasi', '1-2 juta', 5, 0, 'Pelatihan pemberdayaan sangat membantu dalam koordinasi warga transmigran baru', '2026-06-01 16:27:11', '2026-06-01 16:27:11'),
(8, 8, '2026-06-01 16:27:11', '2026-06-01 16:27:11', 'sudah_diisi', 'bekerja', 'Pemerintah Desa Maju Bersama', 'Kepala Desa', 'Pemerintah Desa', '3-5 juta', 5, 0, 'Pelatihan tata kelola desa sangat bermanfaat dalam menjalankan roda pemerintahan desa', '2026-06-01 16:27:11', '2026-06-01 16:27:11'),
(9, 9, '2026-06-01 16:27:11', '2026-06-01 16:27:11', 'sudah_diisi', 'wirausaha', 'Kelompok Wanita Tani Melati', 'Ketua KWT', 'Pertanian/Pengolahan Hasil', '1-3 juta', 4, 1, 'Pelatihan pengolahan hasil pertanian sangat membantu dalam meningkatkan nilai jual produk desa', '2026-06-01 16:27:11', '2026-06-01 16:27:11'),
(10, 10, '2026-06-01 16:27:11', '2026-06-01 16:27:11', 'sudah_diisi', 'wirausaha', 'UMKM Olahan Pangan Lokal Desa Harapan', 'Pemilik UMKM', 'Pengolahan Pangan', '2-3 juta', 4, 2, 'Pelatihan pengolahan pangan lokal membantu mengembangkan produk unggulan desa', '2026-06-01 16:27:11', '2026-06-01 16:27:11'),
(11, 13, '2026-06-11 08:13:44', NULL, 'belum_diisi', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-11 08:13:44', '2026-06-11 08:13:44'),
(12, 14, '2026-06-11 08:13:51', NULL, 'belum_diisi', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-11 08:13:51', '2026-06-11 08:13:51'),
(13, 11, '2026-06-11 08:14:04', NULL, 'belum_diisi', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-11 08:14:04', '2026-06-11 08:14:04'),
(14, 15, '2026-06-11 08:14:10', NULL, 'belum_diisi', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-11 08:14:10', '2026-06-11 08:14:10');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','instruktur','peserta','alumni','kepala') NOT NULL DEFAULT 'peserta',
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Drs. Haji Mulyadi, M.Pd', 'kepala@bppmddtt.go.id', '$2y$10$eSsRbc8Rzobcb69C6dby9uUrIWCk5yfCt8nx.kTExrsZruqNNcbZa', 'kepala', 1, '2026-06-02 00:27:10', '2026-06-02 00:27:10'),
(2, 'Budi Santoso', 'instruktur1@bppmddtt.go.id', '$2y$10$9nCMpZu9UyA0aPVfzBBoreLch5gjZWEnE.7bP55p6tBPZEQ78oorO', 'instruktur', 1, '2026-06-02 00:27:10', '2026-07-04 15:19:40'),
(3, 'Siti Rahayu', 'instruktur2@bppmddtt.go.id', '$2y$10$d.oW9xO9X/cIVVSVVLtj5OVfMTBR.jhzNzamr9hJWMZSi0xwDeYi.', 'instruktur', 1, '2026-06-02 00:27:10', '2026-06-02 00:27:10'),
(4, 'Ahmad Fauzi', 'alumni1@bppmddtt.go.id', '$2y$10$kfMYx8XfgPb59B1Ojm.lqu4sUwKXtvzhAevaaivPX4s3KQys2F.Ja', 'alumni', 1, '2026-06-02 00:27:10', '2026-07-04 15:16:00'),
(5, 'Dewi Lestari', 'alumni2@bppmddtt.go.id', '$2y$10$aTNeIMXvPUi9v54nR2hks.1YT84983Lwry5codbKf33zwpYAz7l5W', 'alumni', 1, '2026-06-02 00:27:10', '2026-06-02 00:27:10'),
(6, 'Rizky Pratama', 'alumni3@bppmddtt.go.id', '$2y$10$hjeW7FjrWQrv03iRHCyhH.broouGGV3Vfcmi4/FqrTOQ17JxMIq1m', 'alumni', 1, '2026-06-02 00:27:10', '2026-06-02 00:27:10'),
(7, 'Nur Hidayah', 'alumni4@bppmddtt.go.id', '$2y$10$pwWQaZ3NLQo3Mq1zNFp/0OZXHzh4zXTFxYfVbWa8bZbpIVQT9QjX6', 'alumni', 1, '2026-06-02 00:27:10', '2026-06-02 00:27:10'),
(8, 'Eko Wahyudi', 'peserta1@bppmddtt.go.id', '$2y$10$MunMK1dm2eb5IfLg3bRzRe8.BdKOx/1lIoWJPOX1.UXHOy1wvvdZe', 'peserta', 1, '2026-06-02 00:27:11', '2026-06-22 05:29:13'),
(9, 'Fitria Anggraini', 'peserta2@bppmddtt.go.id', '$2y$10$Jt7sx5HUPTOZBRTHeth2hu6PA6JbjNVa2AYJRkHVUCvyuOfJNYbom', 'alumni', 1, '2026-06-02 00:27:11', '2026-06-11 08:13:44'),
(10, 'Siti Minarti', 'alumni5@bppmddtt.go.id', '$2y$10$TByrW87cBC5B0poJkrou7.7e.prLXgnbHrIfEoRqyDL./t4dlBljW', 'alumni', 1, '2026-06-02 00:27:11', '2026-06-02 00:27:11'),
(11, 'Bambang Suryanto', 'alumni6@bppmddtt.go.id', '$2y$10$waCDNzLIL6rbq879QxgO2u7kvBmIBNvNiK4UcnLs914RUAuAig02u', 'alumni', 1, '2026-06-02 00:27:11', '2026-06-02 00:27:11'),
(12, 'Nila Kusuma', 'alumni7@bppmddtt.go.id', '$2y$10$yb4S9rqFZAfhVoqMYcBO4OxRLtGGQOOEhvJXt8ls8J0yfhY8d2v3S', 'alumni', 1, '2026-06-02 00:27:11', '2026-06-02 00:27:11'),
(13, 'Hendra Wijaya', 'alumni8@bppmddtt.go.id', '$2y$10$M2JSj2p6Skr60KwKZF9e7.JvLkUds7IiPZ.bhQCBMVZNk6j5gGK3q', 'alumni', 1, '2026-06-02 00:27:11', '2026-06-02 00:27:11'),
(14, 'Fiona Amelia Putri', 'alumni9@bppmddtt.go.id', '$2y$10$Pshk3KzUHmLz1.cQcMDq6Oyql1IJ3qM29mtM8O7Cn7nNmwjCdM67a', 'alumni', 1, '2026-06-02 00:27:11', '2026-06-02 00:27:11'),
(15, 'Dodi Irawan', 'alumni10@bppmddtt.go.id', '$2y$10$Pv9E3rxEdfJq22Ng1lOghe22JNwnByfLplHdlzzMYBLe9e9dZ/xTe', 'alumni', 1, '2026-06-02 00:27:11', '2026-06-02 00:27:11'),
(16, 'Suryadi Hasan', 'alumni11@bppmddtt.go.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'alumni', 1, '2026-01-15 00:00:00', '2026-01-15 00:00:00'),
(17, 'Administrator', 'admin@bppmddtt.go.id', '$2y$10$Oq.0Xi4RfpblGj/Es8RQ/OkwmfWYjWS7A2IMILXs7VwxwlFipApzq', 'admin', 1, '2026-06-02 00:32:07', '2026-06-02 00:32:07'),
(18, 'Rahmawati Dewi', 'alumni12@bppmddtt.go.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'alumni', 1, '2026-01-15 00:00:00', '2026-01-15 00:00:00'),
(19, 'Hendra Saputra', 'peserta7@bppmddtt.go.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'alumni', 1, '2026-02-01 00:00:00', '2026-06-11 08:13:51'),
(20, 'Yuliana Sari', 'peserta8@bppmddtt.go.id', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'alumni', 1, '2026-02-01 00:00:00', '2026-06-11 08:14:10'),
(21, 'NURCHOLIS, S.Tr. A.B', 'kepala1@bppmddtt.go.id', '$2y$10$HmecV8x5xQYM.IYzYe6D9uox7lWKNXaDA.Ov/4SqZbd1antQB7sW2', 'kepala', 1, '2026-06-29 01:53:23', '2026-07-04 16:02:39');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `alumni`
--
ALTER TABLE `alumni`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `alumni_kompetensi`
--
ALTER TABLE `alumni_kompetensi`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_alumni_kompetensi` (`alumni_id`,`kompetensi_id`),
  ADD KEY `fk_ak_kompetensi` (`kompetensi_id`);

--
-- Indexes for table `instruktur`
--
ALTER TABLE `instruktur`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_instruktur_user` (`user_id`);

--
-- Indexes for table `kepala`
--
ALTER TABLE `kepala`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `kompetensi`
--
ALTER TABLE `kompetensi`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pelatihan`
--
ALTER TABLE `pelatihan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_pelatihan_instruktur` (`instruktur_id`);

--
-- Indexes for table `persetujuan_laporan`
--
ALTER TABLE `persetujuan_laporan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `kode_laporan` (`kode_laporan`),
  ADD KEY `fk_pl_kepala` (`kepala_id`),
  ADD KEY `fk_pl_dibuat` (`dibuat_oleh`);

--
-- Indexes for table `peserta_pelatihan`
--
ALTER TABLE `peserta_pelatihan`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_peserta_pelatihan` (`user_id`,`pelatihan_id`),
  ADD KEY `fk_pp_pelatihan` (`pelatihan_id`),
  ADD KEY `fk_pp_verifikator` (`diverifikasi_oleh`);

--
-- Indexes for table `rekomendasi`
--
ALTER TABLE `rekomendasi`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_rek_alumni` (`alumni_id`),
  ADD KEY `fk_rek_pelatihan` (`pelatihan_id`);

--
-- Indexes for table `rktl`
--
ALTER TABLE `rktl`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_rktl` (`alumni_id`,`pelatihan_id`),
  ADD KEY `fk_rktl_pelatihan` (`pelatihan_id`),
  ADD KEY `fk_rktl_instruktur` (`instruktur_id`);

--
-- Indexes for table `tracer_study`
--
ALTER TABLE `tracer_study`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_ts_alumni` (`alumni_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `alumni`
--
ALTER TABLE `alumni`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `alumni_kompetensi`
--
ALTER TABLE `alumni_kompetensi`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `instruktur`
--
ALTER TABLE `instruktur`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `kepala`
--
ALTER TABLE `kepala`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `kompetensi`
--
ALTER TABLE `kompetensi`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `pelatihan`
--
ALTER TABLE `pelatihan`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `persetujuan_laporan`
--
ALTER TABLE `persetujuan_laporan`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `peserta_pelatihan`
--
ALTER TABLE `peserta_pelatihan`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT for table `rekomendasi`
--
ALTER TABLE `rekomendasi`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `rktl`
--
ALTER TABLE `rktl`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `tracer_study`
--
ALTER TABLE `tracer_study`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `alumni`
--
ALTER TABLE `alumni`
  ADD CONSTRAINT `fk_alumni_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `alumni_kompetensi`
--
ALTER TABLE `alumni_kompetensi`
  ADD CONSTRAINT `fk_ak_alumni` FOREIGN KEY (`alumni_id`) REFERENCES `alumni` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ak_kompetensi` FOREIGN KEY (`kompetensi_id`) REFERENCES `kompetensi` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `instruktur`
--
ALTER TABLE `instruktur`
  ADD CONSTRAINT `fk_instruktur_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `kepala`
--
ALTER TABLE `kepala`
  ADD CONSTRAINT `fk_kepala_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `pelatihan`
--
ALTER TABLE `pelatihan`
  ADD CONSTRAINT `fk_pelatihan_instruktur` FOREIGN KEY (`instruktur_id`) REFERENCES `instruktur` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `persetujuan_laporan`
--
ALTER TABLE `persetujuan_laporan`
  ADD CONSTRAINT `fk_pl_dibuat` FOREIGN KEY (`dibuat_oleh`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pl_kepala` FOREIGN KEY (`kepala_id`) REFERENCES `kepala` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `peserta_pelatihan`
--
ALTER TABLE `peserta_pelatihan`
  ADD CONSTRAINT `fk_pp_pelatihan` FOREIGN KEY (`pelatihan_id`) REFERENCES `pelatihan` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pp_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pp_verifikator` FOREIGN KEY (`diverifikasi_oleh`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `rekomendasi`
--
ALTER TABLE `rekomendasi`
  ADD CONSTRAINT `fk_rek_alumni` FOREIGN KEY (`alumni_id`) REFERENCES `alumni` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rek_pelatihan` FOREIGN KEY (`pelatihan_id`) REFERENCES `pelatihan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `rktl`
--
ALTER TABLE `rktl`
  ADD CONSTRAINT `fk_rktl_alumni` FOREIGN KEY (`alumni_id`) REFERENCES `alumni` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rktl_instruktur` FOREIGN KEY (`instruktur_id`) REFERENCES `instruktur` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_rktl_pelatihan` FOREIGN KEY (`pelatihan_id`) REFERENCES `pelatihan` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tracer_study`
--
ALTER TABLE `tracer_study`
  ADD CONSTRAINT `fk_ts_alumni` FOREIGN KEY (`alumni_id`) REFERENCES `alumni` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
