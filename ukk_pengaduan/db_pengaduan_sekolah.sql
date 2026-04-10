-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Apr 02, 2026 at 12:27 AM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_pengaduan_sekolah`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id_admin` int NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_petugas` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id_admin`, `username`, `password`, `nama_petugas`) VALUES
(1, 'admin_roger', 'roger', 'Mas_Roger'),
(2, 'admin_sofia', 'sofia', 'Mba_Sofia');

-- --------------------------------------------------------

--
-- Table structure for table `aspirasi`
--

CREATE TABLE `aspirasi` (
  `id_aspirasi` int NOT NULL,
  `nis` char(10) NOT NULL,
  `id_kategori` int NOT NULL,
  `lokasi` varchar(50) NOT NULL,
  `keterangan` text NOT NULL,
  `foto` varchar(255) NOT NULL,
  `tanggal` date NOT NULL,
  `status` enum('menunggu','proses','selesai') NOT NULL DEFAULT 'menunggu',
  `feedback` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `aspirasi`
--

INSERT INTO `aspirasi` (`id_aspirasi`, `nis`, `id_kategori`, `lokasi`, `keterangan`, `foto`, `tanggal`, `status`, `feedback`) VALUES
(14, '1001', 2, 'Kantin', 'Sampah plastik menumpuk di area belakang meja makan.', '', '2026-03-31', 'selesai', 'siap nanti dibersihkan sama royyan'),
(15, '1002', 2, 'Kantin', 'Wastafel kantin mampet, air tergenang terus.', '', '2026-03-31', 'selesai', 'nanti dibenarkan'),
(16, '1003', 4, 'Lab Komputer', 'Komputer nomor 05 layarnya berkedip terus.', '', '2026-03-31', 'menunggu', ''),
(17, '1004', 4, 'Lab Komputer', 'Mouse di PC nomor 08 hilang, tolong dicek.', '', '2026-03-31', 'menunggu', ''),
(18, '1005', 5, 'Perpustakaan', 'AC di pojok kanan bunyinya berisik sekali.', '', '2026-03-31', 'selesai', 'Sudah diperbaiki teknisi tadi pagi.'),
(19, '1006', 5, 'Perpustakaan', 'Buku-buku di rak sejarah banyak yang robek.', '', '2026-03-31', 'menunggu', ''),
(20, '1007', 3, 'Parkiran', 'Lampu parkiran belakang mati kalau malam.', '', '2026-03-31', 'proses', ''),
(21, '1008', 3, 'Gerbang Depan', 'Gembok gerbang kecil sepertinya sudah mulai rusak.', '', '2026-03-31', 'menunggu', ''),
(22, '1009', 1, 'Kelas XII RPL 1', 'Proyektor kelas sering mati sendiri saat dipakai.', '', '2026-03-31', 'menunggu', ''),
(23, '1010', 1, 'Kelas XI TKJ 2', 'Jendela kelas ada yang pecah kacanya.', '', '2026-03-31', 'selesai', 'Kaca sudah diganti dengan yang baru.'),
(24, '1001', 1, 'Ruang Kelas XII RPL 1', 'Kursi di barisan belakang goyang, bahaya kalau diduduki.', '', '2026-03-31', 'menunggu', ''),
(25, '1002', 2, 'Toilet Putri', 'Air di toilet lantai 2 tidak mengalir sejak pagi.', '', '2026-03-31', 'proses', ''),
(26, '1003', 3, 'Area Parkir Motor', 'Banyak motor parkir sembarangan, jadi susah keluar.', '', '2026-03-31', 'menunggu', ''),
(27, '1004', 4, 'Lab Komputer', 'Kabel LAN di PC nomor 20 terkelupas.', '', '2026-03-31', 'menunggu', ''),
(28, '1005', 5, 'Perpustakaan', 'Sirkulasi udara kurang, kipas angin mati satu.', '', '2026-03-31', 'menunggu', ''),
(29, '1006', 2, 'Halaman Sekolah', 'Rumput liar di taman depan mulai tinggi-tinggi.', '', '2026-03-31', 'selesai', 'Sudah dipangkas oleh petugas kebersihan.'),
(30, '1007', 1, 'Tangga Utama', 'Pegangan tangga ada yang lepas bautnya.', '', '2026-03-31', 'menunggu', ''),
(31, '1008', 4, 'Bengkel TKJ', 'Tang potong di meja praktek banyak yang tumpul.', '', '2026-03-31', 'proses', ''),
(32, '1009', 2, 'Kantin', 'Harga makanan di kantin tolong diseragamkan.', '', '2026-03-31', 'menunggu', ''),
(33, '1010', 3, 'Pagar Belakang', 'Ada bagian pagar yang bolong di pojok belakang.', '', '2026-03-31', 'menunggu', '');

-- --------------------------------------------------------

--
-- Table structure for table `kategori`
--

CREATE TABLE `kategori` (
  `id_kategori` int NOT NULL,
  `nama_kategori` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `kategori`
--

INSERT INTO `kategori` (`id_kategori`, `nama_kategori`) VALUES
(1, 'Kelas'),
(2, 'Kebersihan'),
(3, 'Keamanan'),
(4, 'Laboratorium'),
(5, 'Perpustakaan');

-- --------------------------------------------------------

--
-- Table structure for table `siswa`
--

CREATE TABLE `siswa` (
  `nis` char(10) NOT NULL,
  `nama` varchar(50) NOT NULL,
  `kelas` varchar(10) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `siswa`
--

INSERT INTO `siswa` (`nis`, `nama`, `kelas`, `password`) VALUES
('1', 'allfer', 'Xll RPL', '25'),
('1001', 'Budi Santoso', 'XII RPL 1', '123'),
('1002', 'Siti Aminah', 'XII RPL 2', '123'),
('1003', 'Rian Hidayat', 'XI TKJ 1', '123'),
('1004', 'Lani Wijaya', 'XI TKJ 2', '123'),
('1005', 'Dewi Lestari', 'X MM 1', '123'),
('1006', 'Fajar Ramadhan', 'X MM 2', '123'),
('1007', 'Gita Permata', 'XII RPL 1', '123'),
('1008', 'Hadi Saputra', 'XI TKJ 1', '123'),
('1009', 'Indah Putri', 'X MM 1', '123'),
('1010', 'Jaka Tarub', 'XII RPL 2', '123');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id_admin`);

--
-- Indexes for table `aspirasi`
--
ALTER TABLE `aspirasi`
  ADD PRIMARY KEY (`id_aspirasi`);

--
-- Indexes for table `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id_kategori`);

--
-- Indexes for table `siswa`
--
ALTER TABLE `siswa`
  ADD PRIMARY KEY (`nis`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id_admin` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `aspirasi`
--
ALTER TABLE `aspirasi`
  MODIFY `id_aspirasi` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `kategori`
--
ALTER TABLE `kategori`
  MODIFY `id_kategori` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
