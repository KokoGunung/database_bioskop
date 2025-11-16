-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 16, 2025 at 04:40 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `database_bioskop`
--

-- --------------------------------------------------------

--
-- Table structure for table `film`
--

CREATE TABLE `film` (
  `id_film` varchar(10) NOT NULL,
  `id_studio` varchar(10) NOT NULL,
  `judul` varchar(70) DEFAULT NULL,
  `genre` varchar(20) DEFAULT NULL,
  `durasi` time DEFAULT NULL,
  `rating` int(11) DEFAULT NULL,
  `sinopsis` text DEFAULT NULL,
  `harga` int(11) NOT NULL,
  `mulai_tayang` date DEFAULT NULL,
  `selesai_tayang` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `film`
--

INSERT INTO `film` (`id_film`, `id_studio`, `judul`, `genre`, `durasi`, `rating`, `sinopsis`, `harga`, `mulai_tayang`, `selesai_tayang`) VALUES
('FA001', 'ST03', 'THE RUNNING MAN', 'Aksi', '02:13:00', 17, '0', 65000, '2025-11-18', '2025-11-25'),
('FA002', 'ST03', 'PREDATOR: BADLANDS', 'Aksi', '01:47:00', 13, '0', 70000, '2025-11-01', '2025-11-08'),
('FD001', 'ST01', 'SAMPAI TITIK TERAKHIRMU', 'Drama', '01:54:00', 13, '0', 55000, '2025-11-16', '2025-11-23'),
('FF001', 'ST02', 'WICKED: FOR GOOD', 'Fantasi', '02:17:00', 13, '0', 40000, '2025-11-16', '2025-11-23'),
('FH001', 'ST02', 'KUNCEN', 'Horror', '01:48:00', 13, '0', 45000, '2025-11-16', '2025-11-23'),
('FT001', 'ST01', 'NOW YOU SEE ME: NOW YOU DONT', 'Thriller', '01:52:00', 13, '0', 50000, '2025-11-15', '2025-11-22');

-- --------------------------------------------------------

--
-- Table structure for table `jadwal_tayang`
--

CREATE TABLE `jadwal_tayang` (
  `id_jadwal` varchar(10) NOT NULL,
  `id_film` varchar(10) NOT NULL,
  `jam_mulai` time DEFAULT NULL,
  `jam_selesai` time DEFAULT NULL,
  `tanggal` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jadwal_tayang`
--

INSERT INTO `jadwal_tayang` (`id_jadwal`, `id_film`, `jam_mulai`, `jam_selesai`, `tanggal`) VALUES
('JFT1001', 'FT001', '18:30:00', '20:00:00', '2025-11-16');

-- --------------------------------------------------------

--
-- Table structure for table `kursi`
--

CREATE TABLE `kursi` (
  `nomor_kursi` varchar(10) NOT NULL,
  `id_studio` varchar(10) NOT NULL,
  `posisi` varchar(3) DEFAULT NULL,
  `status` enum('TERSEDIA','RUSAK') DEFAULT 'TERSEDIA'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kursi`
--

INSERT INTO `kursi` (`nomor_kursi`, `id_studio`, `posisi`, `status`) VALUES
('K001', 'ST01', 'A1', 'TERSEDIA'),
('K002', 'ST01', 'A2', 'TERSEDIA'),
('K003', 'ST01', 'A3', 'TERSEDIA'),
('K004', 'ST01', 'A4', 'TERSEDIA'),
('K005', 'ST01', 'A5', 'TERSEDIA'),
('K006', 'ST01', 'A6', 'TERSEDIA'),
('K007', 'ST01', 'A7', 'TERSEDIA'),
('K008', 'ST01', 'A8', 'TERSEDIA'),
('K009', 'ST01', 'A9', 'TERSEDIA'),
('K010', 'ST01', 'B1', 'TERSEDIA'),
('K011', 'ST01', 'B2', 'TERSEDIA'),
('K012', 'ST01', 'B3', 'TERSEDIA'),
('K013', 'ST01', 'B4', 'TERSEDIA'),
('K014', 'ST01', 'B5', 'TERSEDIA'),
('K015', 'ST01', 'B6', 'TERSEDIA'),
('K016', 'ST01', 'B7', 'TERSEDIA'),
('K017', 'ST01', 'B8', 'TERSEDIA'),
('K018', 'ST01', 'B9', 'TERSEDIA'),
('K019', 'ST01', 'C1', 'TERSEDIA'),
('K020', 'ST01', 'C2', 'TERSEDIA'),
('K021', 'ST01', 'C3', 'TERSEDIA'),
('K022', 'ST01', 'C4', 'TERSEDIA'),
('K023', 'ST01', 'C5', 'TERSEDIA'),
('K024', 'ST01', 'C6', 'TERSEDIA'),
('K025', 'ST01', 'C7', 'TERSEDIA'),
('K026', 'ST01', 'C8', 'TERSEDIA'),
('K027', 'ST01', 'C9', 'TERSEDIA'),
('K028', 'ST01', 'D1', 'TERSEDIA'),
('K029', 'ST01', 'D2', 'TERSEDIA'),
('K030', 'ST01', 'D3', 'TERSEDIA'),
('K031', 'ST01', 'D4', 'TERSEDIA'),
('K032', 'ST01', 'D5', 'TERSEDIA'),
('K033', 'ST01', 'D6', 'TERSEDIA'),
('K034', 'ST01', 'D7', 'TERSEDIA'),
('K035', 'ST01', 'D8', 'TERSEDIA'),
('K036', 'ST01', 'D9', 'TERSEDIA'),
('K037', 'ST01', 'E1', 'TERSEDIA'),
('K038', 'ST01', 'E2', 'TERSEDIA'),
('K039', 'ST01', 'E3', 'TERSEDIA'),
('K040', 'ST01', 'E4', 'TERSEDIA'),
('K041', 'ST01', 'E5', 'TERSEDIA'),
('K042', 'ST01', 'E6', 'TERSEDIA'),
('K043', 'ST01', 'E7', 'TERSEDIA'),
('K044', 'ST01', 'E8', 'TERSEDIA'),
('K045', 'ST01', 'E9', 'TERSEDIA'),
('K046', 'ST01', 'F1', 'TERSEDIA'),
('K047', 'ST01', 'F2', 'TERSEDIA'),
('K048', 'ST01', 'F3', 'TERSEDIA'),
('K049', 'ST01', 'F4', 'TERSEDIA'),
('K050', 'ST01', 'F5', 'TERSEDIA'),
('K051', 'ST01', 'F6', 'TERSEDIA'),
('K052', 'ST01', 'F7', 'TERSEDIA'),
('K053', 'ST01', 'F8', 'TERSEDIA'),
('K054', 'ST01', 'F9', 'TERSEDIA'),
('K055', 'ST01', 'G1', 'TERSEDIA'),
('K056', 'ST01', 'G2', 'TERSEDIA'),
('K057', 'ST01', 'G3', 'TERSEDIA'),
('K058', 'ST01', 'G4', 'TERSEDIA'),
('K059', 'ST01', 'G5', 'TERSEDIA'),
('K060', 'ST01', 'G6', 'TERSEDIA'),
('K061', 'ST01', 'G7', 'TERSEDIA'),
('K062', 'ST01', 'G8', 'TERSEDIA'),
('K063', 'ST01', 'G9', 'TERSEDIA'),
('K064', 'ST01', 'H1', 'TERSEDIA'),
('K065', 'ST01', 'H2', 'TERSEDIA'),
('K066', 'ST01', 'H3', 'TERSEDIA'),
('K067', 'ST01', 'H4', 'TERSEDIA');

-- --------------------------------------------------------

--
-- Table structure for table `pembayaran`
--

CREATE TABLE `pembayaran` (
  `id_pembayaran` varchar(8) NOT NULL,
  `id_pemesanan` varchar(10) NOT NULL,
  `metode_bayar` enum('Gopay','OVO','Shopee-Pay','Bank Transfer','QRIS') DEFAULT NULL,
  `total_bayar` int(11) DEFAULT NULL,
  `status_bayar` enum('GAGAL','SUKSES','DALAM PROSES') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pembayaran`
--

INSERT INTO `pembayaran` (`id_pembayaran`, `id_pemesanan`, `metode_bayar`, `total_bayar`, `status_bayar`) VALUES
('PB636647', 'PM96449123', 'QRIS', 200000, 'SUKSES'),
('PB820561', 'PM04911680', 'Bank Transfer', 50000, 'SUKSES'),
('PB956823', 'PM06910258', 'Shopee-Pay', 100000, 'SUKSES');

-- --------------------------------------------------------

--
-- Table structure for table `pemesanan`
--

CREATE TABLE `pemesanan` (
  `id_pemesanan` varchar(10) NOT NULL,
  `id_penonton` int(11) NOT NULL,
  `tanggal_pesan` datetime DEFAULT NULL,
  `jumlah_tiket` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pemesanan`
--

INSERT INTO `pemesanan` (`id_pemesanan`, `id_penonton`, `tanggal_pesan`, `jumlah_tiket`) VALUES
('PM04911680', 1, '2025-11-16 21:55:11', 1),
('PM06910258', 1, '2025-11-16 22:28:30', 2),
('PM96449123', 1, '2025-11-16 19:34:09', 4);

-- --------------------------------------------------------

--
-- Table structure for table `penonton`
--

CREATE TABLE `penonton` (
  `id_penonton` int(11) NOT NULL,
  `tanggal_daftar` datetime DEFAULT current_timestamp(),
  `email` varchar(50) DEFAULT NULL,
  `password` varchar(50) NOT NULL,
  `nama` varchar(50) DEFAULT NULL,
  `nomor_hp` varchar(15) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `penonton`
--

INSERT INTO `penonton` (`id_penonton`, `tanggal_daftar`, `email`, `password`, `nama`, `nomor_hp`) VALUES
(1, '2025-11-13 21:08:03', 'jujuso@gmail.com', 'juso123', 'Jujuso', '083256153422'),
(3, '2025-11-13 21:18:19', 'bramsaputra@gmail.com', 'bram123', 'Bram Saputra', '083542981453'),
(4, '2025-11-13 22:41:47', 'narendra23@gmail.com', 'narendra321', 'Narendra Widyatama', '086357281652');

-- --------------------------------------------------------

--
-- Table structure for table `studio`
--

CREATE TABLE `studio` (
  `id_studio` varchar(10) NOT NULL,
  `nama_studio` varchar(50) DEFAULT NULL,
  `kapasitas` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `studio`
--

INSERT INTO `studio` (`id_studio`, `nama_studio`, `kapasitas`) VALUES
('ST01', 'Studio 1', 62),
('ST02', 'Studio 2', 50),
('ST03', 'Studio 3', 31);

-- --------------------------------------------------------

--
-- Table structure for table `tiket`
--

CREATE TABLE `tiket` (
  `id_tiket` varchar(10) NOT NULL,
  `nomor_kursi` varchar(10) NOT NULL,
  `id_pemesanan` varchar(10) NOT NULL,
  `id_jadwal` varchar(10) NOT NULL,
  `harga` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tiket`
--

INSERT INTO `tiket` (`id_tiket`, `nomor_kursi`, `id_pemesanan`, `id_jadwal`, `harga`) VALUES
('TK15B0AA75', 'K001', 'PM06910258', 'JFT1001', 50000),
('TK6919e5d3', 'K009', 'PM04911680', 'JFT1001', 50000),
('TK96470198', 'K004', 'PM96449123', 'JFT1001', 50000),
('TK96470749', 'K005', 'PM96449123', 'JFT1001', 50000),
('TK96470767', 'K007', 'PM96449123', 'JFT1001', 50000),
('TK96470921', 'K006', 'PM96449123', 'JFT1001', 50000),
('TK9D16D395', 'K002', 'PM06910258', 'JFT1001', 50000);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `film`
--
ALTER TABLE `film`
  ADD PRIMARY KEY (`id_film`),
  ADD KEY `id_studio_fk` (`id_studio`);

--
-- Indexes for table `jadwal_tayang`
--
ALTER TABLE `jadwal_tayang`
  ADD PRIMARY KEY (`id_jadwal`),
  ADD KEY `id_film_fk` (`id_film`);

--
-- Indexes for table `kursi`
--
ALTER TABLE `kursi`
  ADD PRIMARY KEY (`nomor_kursi`),
  ADD KEY `kursi_studio` (`id_studio`);

--
-- Indexes for table `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD PRIMARY KEY (`id_pembayaran`),
  ADD KEY `id_pemesanan_fk` (`id_pemesanan`);

--
-- Indexes for table `pemesanan`
--
ALTER TABLE `pemesanan`
  ADD PRIMARY KEY (`id_pemesanan`),
  ADD KEY `pemesanan_penonton` (`id_penonton`);

--
-- Indexes for table `penonton`
--
ALTER TABLE `penonton`
  ADD PRIMARY KEY (`id_penonton`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `studio`
--
ALTER TABLE `studio`
  ADD PRIMARY KEY (`id_studio`);

--
-- Indexes for table `tiket`
--
ALTER TABLE `tiket`
  ADD PRIMARY KEY (`id_tiket`),
  ADD KEY `tiket_pemesanan` (`id_pemesanan`),
  ADD KEY `tiket_jadwal` (`id_jadwal`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `jadwal_tayang`
--
ALTER TABLE `jadwal_tayang`
  ADD CONSTRAINT `jadwal_film` FOREIGN KEY (`id_film`) REFERENCES `film` (`id_film`);

--
-- Constraints for table `kursi`
--
ALTER TABLE `kursi`
  ADD CONSTRAINT `kursi_studio` FOREIGN KEY (`id_studio`) REFERENCES `studio` (`id_studio`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD CONSTRAINT `pembayaran_pemesanan` FOREIGN KEY (`id_pemesanan`) REFERENCES `pemesanan` (`id_pemesanan`);

--
-- Constraints for table `pemesanan`
--
ALTER TABLE `pemesanan`
  ADD CONSTRAINT `pemesanan_penonton` FOREIGN KEY (`id_penonton`) REFERENCES `penonton` (`id_penonton`);

--
-- Constraints for table `tiket`
--
ALTER TABLE `tiket`
  ADD CONSTRAINT `tiket_jadwal` FOREIGN KEY (`id_jadwal`) REFERENCES `jadwal_tayang` (`id_jadwal`),
  ADD CONSTRAINT `tiket_pemesanan` FOREIGN KEY (`id_pemesanan`) REFERENCES `pemesanan` (`id_pemesanan`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
