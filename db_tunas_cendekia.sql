-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 21, 2026 at 03:58 AM
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
-- Database: `db_tunas_cendekia`
--
CREATE DATABASE IF NOT EXISTS `db_tunas_cendekia` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `db_tunas_cendekia`;

-- --------------------------------------------------------

--
-- Table structure for table `akun`
--

CREATE TABLE IF NOT EXISTS `akun` (
  `id_akun` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `peran` enum('Guru','Siswa','Admin') NOT NULL,
  PRIMARY KEY (`id_akun`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `akun`
--

INSERT INTO `akun` (`id_akun`, `username`, `password`, `peran`) VALUES
(1, 'admin01', 'admin123', 'Admin'),
(2, 'guru01', 'guru123', 'Guru'),
(3, 'siswa01', 'siswa123', 'Siswa'),
(4, 'guru02', 'guru123', 'Guru'),
(5, 'siswa02', 'siswa123', 'Siswa')
ON DUPLICATE KEY UPDATE `username`=`username`;

-- --------------------------------------------------------

--
-- Table structure for table `guru`
--

CREATE TABLE IF NOT EXISTS `guru` (
  `id_guru` int(11) NOT NULL AUTO_INCREMENT,
  `id_akun` int(11) NOT NULL,
  `nama_guru` varchar(100) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `pendidikan_terakhir` varchar(100) DEFAULT NULL,
  `bidang_mapel` varchar(100) DEFAULT NULL,
  `no_telepon` varchar(15) DEFAULT NULL,
  PRIMARY KEY (`id_guru`),
  UNIQUE KEY `id_akun_2` (`id_akun`),
  KEY `id_akun` (`id_akun`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `guru`
--

INSERT INTO `guru` (`id_guru`, `id_akun`, `nama_guru`, `alamat`, `pendidikan_terakhir`, `bidang_mapel`, `no_telepon`) VALUES
(1, 2, 'Meysindi Saskia', 'Purwokerto, Jawa Tengah', 'pengembangan masyarakat islam', 'Calistung', '081930658762'),
(8, 4, 'Alvina Anissa', 'Kalimanah Purbalingga jawa tengah', 'S1', 'Calistung, Bahasa arab, Semua Mapel', '085640087858')
ON DUPLICATE KEY UPDATE `id_guru`=`id_guru`;

-- --------------------------------------------------------

--
-- Table structure for table `mata_pelajaran`
--

CREATE TABLE IF NOT EXISTS `mata_pelajaran` (
  `id_mapel` int(11) NOT NULL AUTO_INCREMENT,
  `nama_mapel` varchar(100) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  PRIMARY KEY (`id_mapel`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `mata_pelajaran`
--

INSERT INTO `mata_pelajaran` (`id_mapel`, `nama_mapel`, `deskripsi`) VALUES
(1, 'calistung', ' Mata Pelajaran calistung'),
(2, 'Matematika', 'Mata pelajaran matematika'),
(3, 'Bahasa Inggris', 'Mata pelajaran bahasa Inggris')
ON DUPLICATE KEY UPDATE `id_mapel`=`id_mapel`;

-- --------------------------------------------------------

--
-- Table structure for table `kelas`
--

CREATE TABLE IF NOT EXISTS `kelas` (
  `id_kelas` int(11) NOT NULL AUTO_INCREMENT,
  `nama_kelas` varchar(100) NOT NULL,
  `tingkat` varchar(30) NOT NULL,
  `biaya` decimal(12,2) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `status_kelas` enum('Aktif','Tidak Aktif') NOT NULL DEFAULT 'Aktif',
  PRIMARY KEY (`id_kelas`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kelas`
--

INSERT INTO `kelas` (`id_kelas`, `nama_kelas`, `tingkat`, `biaya`, `deskripsi`, `status_kelas`) VALUES
(1, 'Calistung', 'PAUD/TK', 100000.00, 'Bimbingan membaca, menulis, dan berhitung', 'Aktif'),
(2, 'Kelas 1 SD', 'SD', 150000.00, 'Bimbingan belajar kelas 1 SD', 'Aktif'),
(3, 'Kelas 2 SD', 'SD', 150000.00, 'Bimbingan belajar kelas 2 SD', 'Aktif'),
(4, 'Kelas 3 SD', 'SD', 150000.00, 'Bimbingan belajar kelas 3 SD', 'Aktif'),
(5, 'Kelas 4 SD', 'SD', 175000.00, 'Bimbingan belajar kelas 4 SD', 'Aktif'),
(6, 'Kelas 5 SD', 'SD', 175000.00, 'Bimbingan belajar kelas 5 SD', 'Aktif'),
(7, 'Kelas 6 SD', 'SD', 200000.00, 'Bimbingan belajar kelas 6 SD', 'Aktif'),
(8, 'Kelas 7 SMP', 'SMP', 200000.00, 'Bimbingan belajar kelas 7 SMP', 'Aktif'),
(9, 'Kelas 8 SMP', 'SMP', 225000.00, 'Bimbingan belajar kelas 8 SMP', 'Aktif'),
(10, 'Kelas 9 SMP', 'SMP', 250000.00, 'Bimbingan belajar kelas 9 SMP', 'Aktif'),
(11, 'Kelas 10 SMA', 'SMA', 250000.00, 'Bimbingan belajar kelas 10 SMA', 'Aktif'),
(12, 'Kelas 11 SMA', 'SMA', 275000.00, 'Bimbingan belajar kelas 11 SMA', 'Aktif'),
(13, 'Kelas 12 SMA', 'SMA', 300000.00, 'Bimbingan belajar kelas 12 SMA', 'Aktif')
ON DUPLICATE KEY UPDATE `id_kelas`=`id_kelas`;

-- --------------------------------------------------------

--
-- Table structure for table `siswa`
--

CREATE TABLE IF NOT EXISTS `siswa` (
  `id_siswa` int(11) NOT NULL AUTO_INCREMENT,
  `id_akun` int(11) NOT NULL,
  `nama_siswa` varchar(100) NOT NULL,
  `jenis_Kelamin` enum('perempuan','laki-laki') NOT NULL,
  `tanggal_lahir` date NOT NULL,
  `alamat` varchar(255) NOT NULL,
  `no_telepon` varchar(20) NOT NULL,
  `tanggal_daftar` date NOT NULL,
  PRIMARY KEY (`id_siswa`),
  UNIQUE KEY `id_akun_2` (`id_akun`),
  KEY `id_akun` (`id_akun`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `siswa`
--

INSERT INTO `siswa` (`id_siswa`, `id_akun`, `nama_siswa`, `jenis_Kelamin`, `tanggal_lahir`, `alamat`, `no_telepon`, `tanggal_daftar`) VALUES
(1, 3, 'Andi Pratama', 'laki-laki', '2012-05-15', 'Purwokerto', '081234567890', '2026-10-26'),
(2, 5, 'Iput Novitasari', 'perempuan', '2012-05-15', 'Purwokerto', '081234567890', '2026-10-26')
ON DUPLICATE KEY UPDATE `id_siswa`=`id_siswa`;

-- --------------------------------------------------------

--
-- Table structure for table `jadwal`
--

CREATE TABLE IF NOT EXISTS `jadwal` (
  `id_jadwal` int(11) NOT NULL AUTO_INCREMENT,
  `id_guru` int(11) NOT NULL,
  `id_mapel` int(11) NOT NULL,
  `hari` enum('Senin','Selasa','Rabu','Kamis','Jumat','Sabtu','Minggu') NOT NULL,
  `jam_mulai` time NOT NULL,
  `jam_selesai` time NOT NULL,
  `tanggal_belajar` date DEFAULT NULL,
  `id_kelas` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_jadwal`),
  KEY `id_guru` (`id_guru`),
  KEY `id_mapel` (`id_mapel`),
  KEY `id_kelas` (`id_kelas`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jadwal`
--

INSERT INTO `jadwal` (`id_jadwal`, `id_guru`, `id_mapel`, `hari`, `jam_mulai`, `jam_selesai`, `tanggal_belajar`, `id_kelas`) VALUES
(14, 1, 1, 'Senin', '08:00:00', '09:30:00', '2026-11-05', 1),
(17, 8, 2, 'Senin', '10:00:00', '11:30:00', '2026-11-26', 2),
(20, 1, 1, 'Senin', '08:00:00', '09:30:00', '2026-11-09', 1),
(21, 8, 2, 'Selasa', '10:00:00', '11:30:00', '2026-11-10', 2)
ON DUPLICATE KEY UPDATE `id_jadwal`=`id_jadwal`;

-- --------------------------------------------------------

--
-- Table structure for table `kehadiran`
--

CREATE TABLE IF NOT EXISTS `kehadiran` (
  `id_kehadiran` int(11) NOT NULL AUTO_INCREMENT,
  `id_jadwal` int(11) NOT NULL,
  `id_siswa` int(11) NOT NULL,
  `tanggal_kehadiran` date NOT NULL,
  `jam_masuk` time DEFAULT NULL,
  `status_kehadiran` enum('Hadir','Izin','Sakit','Alpa') NOT NULL,
  PRIMARY KEY (`id_kehadiran`),
  KEY `id_jadwal` (`id_jadwal`),
  KEY `id_siswa` (`id_siswa`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kehadiran`
--

INSERT INTO `kehadiran` (`id_kehadiran`, `id_jadwal`, `id_siswa`, `tanggal_kehadiran`, `jam_masuk`, `status_kehadiran`) VALUES
(3, 14, 2, '2026-11-03', '14:21:06', 'Alpa'),
(4, 14, 2, '2026-11-03', '14:21:06', 'Izin'),
(5, 14, 2, '2026-11-03', '14:21:06', 'Hadir'),
(6, 17, 1, '2026-11-04', '14:29:41', 'Sakit')
ON DUPLICATE KEY UPDATE `id_kehadiran`=`id_kehadiran`;

-- --------------------------------------------------------

--
-- Table structure for table `latihan_soal`
--

CREATE TABLE IF NOT EXISTS `latihan_soal` (
  `id_latihan` int(11) NOT NULL AUTO_INCREMENT,
  `id_guru` int(11) NOT NULL,
  `id_mapel` int(11) NOT NULL,
  `judul_latihan` varchar(150) NOT NULL,
  `tanggal_dibuat` date NOT NULL,
  `tautan_latihan` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_latihan`),
  KEY `id_guru` (`id_guru`),
  KEY `id_mapel` (`id_mapel`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `latihan_soal` (`id_latihan`, `id_guru`, `id_mapel`, `judul_latihan`, `tanggal_dibuat`, `tautan_latihan`) VALUES
(1, 1, 1, 'Latihan Membaca & Menghitung Kata', '2026-11-10', 'https://forms.google.com/contoh-latihan-calistung'),
(2, 8, 2, 'Latihan Aljabar dan Logaritma', '2026-11-12', 'https://quizizz.com/join?gc=contoh-matematika')
ON DUPLICATE KEY UPDATE `id_latihan`=`id_latihan`;

-- --------------------------------------------------------

--
-- Table structure for table `hasil_latihan`
--

CREATE TABLE IF NOT EXISTS `hasil_latihan` (
  `id_hasil` int(11) NOT NULL AUTO_INCREMENT,
  `id_siswa` int(11) NOT NULL,
  `id_latihan` int(11) NOT NULL,
  `nilai` decimal(5,2) NOT NULL,
  `tanggal_pengerjaan` date NOT NULL,
  PRIMARY KEY (`id_hasil`),
  KEY `id_siswa` (`id_siswa`),
  KEY `id_latihan` (`id_latihan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `hasil_latihan` (`id_hasil`, `id_siswa`, `id_latihan`, `nilai`, `tanggal_pengerjaan`) VALUES
(1, 1, 2, 88.50, '2026-11-15'),
(2, 2, 1, 95.00, '2026-11-14')
ON DUPLICATE KEY UPDATE `id_hasil`=`id_hasil`;

-- --------------------------------------------------------

--
-- Table structure for table `pendaftaran`
--

CREATE TABLE IF NOT EXISTS `pendaftaran` (
  `id_pendaftaran` int(11) NOT NULL AUTO_INCREMENT,
  `id_siswa` int(11) NOT NULL,
  `id_kelas` int(11) NOT NULL,
  `tanggal_daftar` date NOT NULL,
  `status_pendaftaran` enum('Menunggu','Diterima','Ditolak') DEFAULT 'Menunggu',
  PRIMARY KEY (`id_pendaftaran`),
  KEY `id_siswa` (`id_siswa`),
  KEY `id_kelas` (`id_kelas`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pendaftaran`
--

INSERT INTO `pendaftaran` (`id_pendaftaran`, `id_siswa`, `id_kelas`, `tanggal_daftar`, `status_pendaftaran`) VALUES
(1, 1, 1, '2026-11-02', 'Diterima'),
(2, 2, 2, '2026-11-02', 'Diterima'),
(3, 1, 1, '2026-11-07', 'Diterima'),
(4, 2, 2, '2026-11-07', 'Diterima'),
(5, 1, 3, '2026-11-07', 'Diterima')
ON DUPLICATE KEY UPDATE `id_pendaftaran`=`id_pendaftaran`;

-- --------------------------------------------------------

--
-- Table structure for table `pembayaran`
--

CREATE TABLE IF NOT EXISTS `pembayaran` (
  `id_pembayaran` int(11) NOT NULL AUTO_INCREMENT,
  `id_pendaftaran` int(11) NOT NULL,
  `tanggal_bayar` date NOT NULL,
  `jumlah_bayar` decimal(12,2) NOT NULL,
  `metode_bayar` enum('Tunai','Transfer','E-Wallet') NOT NULL,
  `status_pembayaran` enum('Menunggu','Lunas','Ditolak') NOT NULL DEFAULT 'Menunggu',
  `bukti_pembayaran` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id_pembayaran`),
  KEY `id_pendaftaran` (`id_pendaftaran`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pembayaran`
--

INSERT INTO `pembayaran` (`id_pembayaran`, `id_pendaftaran`, `tanggal_bayar`, `jumlah_bayar`, `metode_bayar`, `status_pembayaran`, `bukti_pembayaran`) VALUES
(1, 1, '2026-11-02', 150000.00, 'Transfer', 'Lunas', 'bukti_transfer_001.jpg'),
(2, 2, '2026-11-02', 200000.00, 'Tunai', 'Lunas', NULL)
ON DUPLICATE KEY UPDATE `id_pembayaran`=`id_pembayaran`;

--
-- Constraints for dumped tables
--

ALTER TABLE `guru`
  ADD CONSTRAINT `fk_guru_akun` FOREIGN KEY (`id_akun`) REFERENCES `akun` (`id_akun`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `hasil_latihan`
  ADD CONSTRAINT `hasil_latihan_ibfk_1` FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id_siswa`),
  ADD CONSTRAINT `hasil_latihan_ibfk_2` FOREIGN KEY (`id_latihan`) REFERENCES `latihan_soal` (`id_latihan`);

ALTER TABLE `jadwal`
  ADD CONSTRAINT `jadwal_ibfk_2` FOREIGN KEY (`id_guru`) REFERENCES `guru` (`id_guru`),
  ADD CONSTRAINT `jadwal_ibfk_3` FOREIGN KEY (`id_mapel`) REFERENCES `mata_pelajaran` (`id_mapel`),
  ADD CONSTRAINT `jadwal_ibfk_4` FOREIGN KEY (`id_kelas`) REFERENCES `kelas` (`id_kelas`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `kehadiran`
  ADD CONSTRAINT `kehadiran_ibfk_1` FOREIGN KEY (`id_jadwal`) REFERENCES `jadwal` (`id_jadwal`),
  ADD CONSTRAINT `kehadiran_ibfk_2` FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id_siswa`);

ALTER TABLE `latihan_soal`
  ADD CONSTRAINT `latihan_soal_ibfk_1` FOREIGN KEY (`id_guru`) REFERENCES `guru` (`id_guru`),
  ADD CONSTRAINT `latihan_soal_ibfk_2` FOREIGN KEY (`id_mapel`) REFERENCES `mata_pelajaran` (`id_mapel`);

ALTER TABLE `pembayaran`
  ADD CONSTRAINT `pembayaran_ibfk_1` FOREIGN KEY (`id_pendaftaran`) REFERENCES `pendaftaran` (`id_pendaftaran`);

ALTER TABLE `pendaftaran`
  ADD CONSTRAINT `pendaftaran_ibfk_1` FOREIGN KEY (`id_siswa`) REFERENCES `siswa` (`id_siswa`),
  ADD CONSTRAINT `pendaftaran_ibfk_2` FOREIGN KEY (`id_kelas`) REFERENCES `kelas` (`id_kelas`);

ALTER TABLE `siswa`
  ADD CONSTRAINT `fk_siswa_akun` FOREIGN KEY (`id_akun`) REFERENCES `akun` (`id_akun`) ON DELETE CASCADE ON UPDATE CASCADE;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
