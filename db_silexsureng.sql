-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 06 Des 2025 pada 05.21
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_silexsureng`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `admin`
--

CREATE TABLE `admin` (
  `id_admin` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `admin`
--

INSERT INTO `admin` (`id_admin`, `username`, `password`, `nama`) VALUES
(1, 'admin', 'admin123', 'Admin Utama');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pelanggan`
--

CREATE TABLE `pelanggan` (
  `id_pelanggan` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `no_hp` varchar(15) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `dibuat_pada` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pelanggan`
--

INSERT INTO `pelanggan` (`id_pelanggan`, `nama`, `no_hp`, `email`, `alamat`, `username`, `password`, `dibuat_pada`) VALUES
(1, 'Fiqry', '085340202004', 'fiqrypratama@gmail.com', 'boepinang', '', '3re3r3', '2025-12-06 00:11:19'),
(3, 'yuni', '085340202005', 'yuni123@gmail.com', 'kolaka', 'Fiqry', 'fiqry123', '2025-12-06 02:09:16');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pembayaran`
--

CREATE TABLE `pembayaran` (
  `id_pembayaran` int(11) NOT NULL,
  `id_pemesanan` int(11) NOT NULL,
  `jumlah` decimal(12,2) NOT NULL,
  `metode` varchar(100) NOT NULL,
  `status` enum('menunggu','valid','ditolak') DEFAULT 'menunggu',
  `bukti` varchar(255) DEFAULT NULL,
  `notif_pelanggan` text DEFAULT NULL,
  `dibuat_pada` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pembayaran`
--

INSERT INTO `pembayaran` (`id_pembayaran`, `id_pemesanan`, `jumlah`, `metode`, `status`, `bukti`, `notif_pelanggan`, `dibuat_pada`) VALUES
(1, 1, 500000.00, 'Transfer (BCA)', 'menunggu', NULL, NULL, '2025-12-06 00:30:41');

-- --------------------------------------------------------

--
-- Struktur dari tabel `pemesanan`
--

CREATE TABLE `pemesanan` (
  `id_pemesanan` int(11) NOT NULL,
  `id_pelanggan` int(11) NOT NULL,
  `id_rute` int(11) NOT NULL,
  `id_sopir` int(11) DEFAULT NULL,
  `tanggal_berangkat` date NOT NULL,
  `jam_berangkat` time NOT NULL,
  `jumlah_penumpang` int(11) NOT NULL,
  `total_harga` decimal(12,2) NOT NULL,
  `status` enum('pending','dialokasikan','dalam_perjalanan','selesai','dibatalkan') DEFAULT 'pending',
  `dibuat_pada` timestamp NOT NULL DEFAULT current_timestamp(),
  `bukti_pembayaran` varchar(255) DEFAULT NULL,
  `status_pembayaran` enum('Belum Bayar','Menunggu Konfirmasi','Lunas','Ditolak') DEFAULT 'Belum Bayar'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `pemesanan`
--

INSERT INTO `pemesanan` (`id_pemesanan`, `id_pelanggan`, `id_rute`, `id_sopir`, `tanggal_berangkat`, `jam_berangkat`, `jumlah_penumpang`, `total_harga`, `status`, `dibuat_pada`, `bukti_pembayaran`, `status_pembayaran`) VALUES
(1, 1, 2, NULL, '2025-12-07', '09:00:00', 2, 500000.00, 'dialokasikan', '2025-12-06 00:30:41', '20251206014158_WhatsApp Image 2025-11-30 at 09.04.26_3349a4ca.jpg', 'Lunas'),
(2, 1, 2, NULL, '2025-12-07', '09:00:00', 1, 250000.00, 'dialokasikan', '2025-12-06 00:55:39', '20251206015548_WhatsApp Image 2025-11-30 at 09.04.26_3349a4ca.jpg', 'Lunas'),
(3, 3, 2, 2, '2025-12-07', '20:00:00', 1, 250000.00, 'selesai', '2025-12-06 02:10:32', NULL, 'Lunas'),
(4, 3, 2, 2, '2025-12-08', '09:00:00', 2, 500000.00, 'selesai', '2025-12-06 02:34:44', '20251206035151_WhatsApp Image 2025-11-30 at 09.04.26_3349a4ca.jpg', 'Lunas'),
(5, 3, 1, 2, '2025-12-07', '10:00:00', 1, 150000.00, 'dalam_perjalanan', '2025-12-06 03:01:19', '20251206040131_WhatsApp Image 2025-11-30 at 09.04.26_3349a4ca.jpg', 'Lunas');

-- --------------------------------------------------------

--
-- Struktur dari tabel `rute`
--

CREATE TABLE `rute` (
  `id_rute` int(11) NOT NULL,
  `asal` varchar(100) NOT NULL,
  `tujuan` varchar(100) NOT NULL,
  `harga` decimal(10,2) NOT NULL,
  `jadwal_keberangkatan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `rute`
--

INSERT INTO `rute` (`id_rute`, `asal`, `tujuan`, `harga`, `jadwal_keberangkatan`) VALUES
(1, 'Popalia', 'Kolaka', 150000.00, '08:00, 10:00, 14:00'),
(2, 'Popalia', 'Kendari', 250000.00, '09:00, 20:00'),
(3, 'Popalia', 'Morowali', 300000.00, '07:00, 13:00'),
(4, 'Bombana', 'Kolaka', 50000.00, '05:00, 09:00, 16:00');

-- --------------------------------------------------------

--
-- Struktur dari tabel `sopir`
--

CREATE TABLE `sopir` (
  `id_sopir` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `no_hp` varchar(15) NOT NULL,
  `no_sim` varchar(50) NOT NULL,
  `aktif` tinyint(1) DEFAULT 1,
  `dibuat_pada` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `sopir`
--

INSERT INTO `sopir` (`id_sopir`, `nama`, `username`, `password`, `no_hp`, `no_sim`, `aktif`, `dibuat_pada`) VALUES
(2, 'Budi Santoso', 'sopir1', '123', '08123456789', 'SIM-A-12345', 1, '2025-12-06 02:13:58');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id_admin`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indeks untuk tabel `pelanggan`
--
ALTER TABLE `pelanggan`
  ADD PRIMARY KEY (`id_pelanggan`),
  ADD UNIQUE KEY `no_hp` (`no_hp`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indeks untuk tabel `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD PRIMARY KEY (`id_pembayaran`),
  ADD UNIQUE KEY `id_pemesanan` (`id_pemesanan`);

--
-- Indeks untuk tabel `pemesanan`
--
ALTER TABLE `pemesanan`
  ADD PRIMARY KEY (`id_pemesanan`),
  ADD KEY `id_pelanggan` (`id_pelanggan`),
  ADD KEY `id_rute` (`id_rute`),
  ADD KEY `id_sopir` (`id_sopir`);

--
-- Indeks untuk tabel `rute`
--
ALTER TABLE `rute`
  ADD PRIMARY KEY (`id_rute`),
  ADD UNIQUE KEY `idx_asal_tujuan` (`asal`,`tujuan`);

--
-- Indeks untuk tabel `sopir`
--
ALTER TABLE `sopir`
  ADD PRIMARY KEY (`id_sopir`),
  ADD UNIQUE KEY `no_hp` (`no_hp`),
  ADD UNIQUE KEY `no_sim` (`no_sim`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `admin`
--
ALTER TABLE `admin`
  MODIFY `id_admin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `pelanggan`
--
ALTER TABLE `pelanggan`
  MODIFY `id_pelanggan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `pembayaran`
--
ALTER TABLE `pembayaran`
  MODIFY `id_pembayaran` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT untuk tabel `pemesanan`
--
ALTER TABLE `pemesanan`
  MODIFY `id_pemesanan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT untuk tabel `rute`
--
ALTER TABLE `rute`
  MODIFY `id_rute` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `sopir`
--
ALTER TABLE `sopir`
  MODIFY `id_sopir` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD CONSTRAINT `pembayaran_ibfk_1` FOREIGN KEY (`id_pemesanan`) REFERENCES `pemesanan` (`id_pemesanan`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `pemesanan`
--
ALTER TABLE `pemesanan`
  ADD CONSTRAINT `pemesanan_ibfk_1` FOREIGN KEY (`id_pelanggan`) REFERENCES `pelanggan` (`id_pelanggan`) ON DELETE CASCADE,
  ADD CONSTRAINT `pemesanan_ibfk_2` FOREIGN KEY (`id_rute`) REFERENCES `rute` (`id_rute`),
  ADD CONSTRAINT `pemesanan_ibfk_3` FOREIGN KEY (`id_sopir`) REFERENCES `sopir` (`id_sopir`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
