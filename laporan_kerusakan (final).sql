-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 28 Nov 2025 pada 14.38
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `laporan_kerusakan`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `laporan_kerusakan`
--

CREATE TABLE `laporan_kerusakan` (
  `id_laporan` int(11) NOT NULL,
  `id_user` int(11) NOT NULL,
  `id_peralatan` int(11) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `tanggal_lapor` date DEFAULT curdate(),
  `status` enum('belum diperbaiki','sedang diperbaiki','selesai') DEFAULT 'belum diperbaiki'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `laporan_kerusakan`
--

INSERT INTO `laporan_kerusakan` (`id_laporan`, `id_user`, `id_peralatan`, `deskripsi`, `tanggal_lapor`, `status`) VALUES
(6, 5, 16, 'gregreg', '2025-11-28', 'selesai');

-- --------------------------------------------------------

--
-- Struktur dari tabel `peralatan`
--

CREATE TABLE `peralatan` (
  `id_peralatan` int(11) NOT NULL,
  `nama_peralatan` varchar(100) NOT NULL,
  `jenis` varchar(100) DEFAULT NULL,
  `lokasi` varchar(100) DEFAULT NULL,
  `kondisi` enum('baik','rusak','dalam perbaikan') DEFAULT 'baik'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `peralatan`
--

INSERT INTO `peralatan` (`id_peralatan`, `nama_peralatan`, `jenis`, `lokasi`, `kondisi`) VALUES
(16, 'advan workplus', 'Komputer & Laptop', 'Ruang IT', 'baik'),
(18, 'visual fault locator', 'Jaringan', 'Ruang IT', 'dalam perbaikan'),
(19, 'printer apeos 77', 'Perangkat Cetak & Scan', 'it', 'rusak'),
(20, 'ergr', 'Perangkat Cetak & Scan', 'dsfdf', 'rusak');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `nama` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','pelapor') DEFAULT NULL,
  `departemen` varchar(100) NOT NULL,
  `no_hp` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `nama`, `email`, `password`, `role`, `departemen`, `no_hp`, `created_at`, `updated_at`) VALUES
(4, 'Administrator', 'admin@example.com', '$2y$10$/uXqAs2TfW804GWghtt48u2.6Wfo894Yoq6Zq4fLC9K7MQ9/XdkrC', 'admin', 'IT Department', '081234567890', '2025-11-28 01:43:58', '2025-11-28 01:43:58'),
(5, 'ivan sandrea', 'ivan@gmail.com', '$2y$10$En1lrqg2BNM46SYifmIr2uVAlX24B.lzBEwhjxeiHDZfU/U7QGhoe', 'pelapor', 'QP Departemen', '123456789012', '2025-11-28 02:14:57', '2025-11-28 02:14:57'),
(7, 'Suriyani', 'yani@gmail.com', '$2y$10$.VJ9SbSaJ/.oKugyBniruOIfaKiVS0d6v8eaN6Z/jhLLpRVx8wDRW', 'pelapor', 'IT Departemen', '121212121212', '2025-11-28 07:38:13', '2025-11-28 12:44:36'),
(8, 'Reyhan Aditia Perdana', 'ryhn@gmail.com', '$2y$10$/.u6o.J34ALiGV/b8SJfmeS2TuSPwVfuJwMMVOfjPA7FHVxJF/EXK', 'pelapor', 'IT Departemen', '082392124188', '2025-11-28 12:45:41', '2025-11-28 12:45:41');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `laporan_kerusakan`
--
ALTER TABLE `laporan_kerusakan`
  ADD PRIMARY KEY (`id_laporan`),
  ADD KEY `id_peralatan` (`id_peralatan`),
  ADD KEY `fk_laporan_user` (`id_user`);

--
-- Indeks untuk tabel `peralatan`
--
ALTER TABLE `peralatan`
  ADD PRIMARY KEY (`id_peralatan`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `laporan_kerusakan`
--
ALTER TABLE `laporan_kerusakan`
  MODIFY `id_laporan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT untuk tabel `peralatan`
--
ALTER TABLE `peralatan`
  MODIFY `id_peralatan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `laporan_kerusakan`
--
ALTER TABLE `laporan_kerusakan`
  ADD CONSTRAINT `fk_laporan_user` FOREIGN KEY (`id_user`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `laporan_kerusakan_ibfk_2` FOREIGN KEY (`id_peralatan`) REFERENCES `peralatan` (`id_peralatan`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
