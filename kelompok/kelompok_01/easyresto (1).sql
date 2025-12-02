-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 02 Des 2025 pada 13.41
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
-- Database: `easyresto`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `detail_transaksi`
--

CREATE TABLE `detail_transaksi` (
  `id_detail` int(11) NOT NULL,
  `id_transaksi` int(11) DEFAULT NULL,
  `id_menu` int(11) DEFAULT NULL,
  `jumlah` int(11) NOT NULL,
  `subtotal` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `detail_transaksi`
--

INSERT INTO `detail_transaksi` (`id_detail`, `id_transaksi`, `id_menu`, `jumlah`, `subtotal`) VALUES
(1, 1, 1, 2, 50000),
(2, 1, 11, 1, 8000),
(3, 1, 16, 1, 15000),
(4, 2, 3, 1, 28000),
(5, 2, 13, 2, 30000),
(6, 2, 18, 1, 18000),
(7, 3, 1, 1, 25000),
(8, 3, 13, 1, 15000),
(9, 3, 17, 1, 20000),
(10, 4, 6, 1, 22000);

--
-- Trigger `detail_transaksi`
--
DELIMITER $$
CREATE TRIGGER `trg_subtotal_insert` BEFORE INSERT ON `detail_transaksi` FOR EACH ROW BEGIN
  DECLARE harga_menu INT;
  SELECT harga INTO harga_menu FROM menu WHERE id_menu = NEW.id_menu;
  SET NEW.subtotal = harga_menu * NEW.jumlah;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Struktur dari tabel `kategori_menu`
--

CREATE TABLE `kategori_menu` (
  `id_kategori` int(11) NOT NULL,
  `nama_kategori` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `kategori_menu`
--

INSERT INTO `kategori_menu` (`id_kategori`, `nama_kategori`) VALUES
(1, 'Makanan'),
(2, 'Minuman'),
(3, 'Makanan Penutup');

-- --------------------------------------------------------

--
-- Stand-in struktur untuk tampilan `laporan_penjualan`
-- (Lihat di bawah untuk tampilan aktual)
--
CREATE TABLE `laporan_penjualan` (
`id_transaksi` int(11)
,`nama_pelanggan` varchar(100)
,`tanggal` timestamp
,`nama_menu` varchar(100)
,`nama_kategori` varchar(50)
,`jumlah` int(11)
,`subtotal` bigint(21)
,`ppn` decimal(22,0)
,`service` decimal(22,0)
,`total_permenu` decimal(22,0)
);

-- --------------------------------------------------------

--
-- Struktur dari tabel `menu`
--

CREATE TABLE `menu` (
  `id_menu` int(11) NOT NULL,
  `nama_menu` varchar(100) NOT NULL,
  `harga` int(11) NOT NULL,
  `id_kategori` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `menu`
--

INSERT INTO `menu` (`id_menu`, `nama_menu`, `harga`, `id_kategori`) VALUES
(1, 'Nasi Goreng Spesial', 25000, 1),
(2, 'Ayam Bakar Madu', 30000, 1),
(3, 'Sate Ayam', 28000, 1),
(4, 'Mie Goreng Jawa', 23000, 1),
(5, 'Soto Ayam Lamongan', 27000, 1),
(6, 'Bakso Urat', 22000, 1),
(7, 'Nasi Uduk Komplit', 26000, 1),
(8, 'Rawon Daging', 35000, 1),
(9, 'Rendang Padang', 38000, 1),
(10, 'Ikan Bakar Rica', 34000, 1),
(11, 'Es Teh Manis', 8000, 2),
(12, 'Es Jeruk Segar', 10000, 2),
(13, 'Jus Alpukat', 15000, 2),
(14, 'Kopi Hitam Panas', 12000, 2),
(15, 'Milkshake Coklat', 17000, 2),
(16, 'Puding Coklat', 15000, 3),
(17, 'Cheesecake', 20000, 3),
(18, 'Brownies Lumer', 18000, 3),
(19, 'Nasi Goreng Seafood', 32000, 1),
(20, 'Ayam Penyet Sambal Ijo', 29000, 1),
(21, 'Nasi Campur Bali', 33000, 1),
(22, 'Tongseng Kambing', 36000, 1),
(23, 'Mie Aceh', 28000, 1),
(24, 'Nasi Pecel Madiun', 25000, 1),
(25, 'Lontong Sayur', 24000, 1),
(26, 'Bebek Goreng Sambal Matah', 37000, 1),
(27, 'Nasi Liwet Solo', 30000, 1),
(28, 'Ayam Geprek Keju', 29000, 1),
(29, 'Iga Bakar BBQ', 40000, 1),
(30, 'Sop Buntut', 42000, 1),
(31, 'Es Kopi Susu Gula Aren', 18000, 2),
(32, 'Es Cincau Hijau', 9000, 2),
(33, 'Teh Tarik Hangat', 11000, 2),
(34, 'Jus Mangga', 14000, 2),
(35, 'Jus Melon', 13000, 2),
(36, 'Jus Jeruk Nipis', 10000, 2),
(37, 'Soda Gembira', 16000, 2),
(38, 'Air Mineral Botol', 6000, 2),
(39, 'Wedang Jahe', 12000, 2),
(40, 'Jus Stroberi', 14000, 2),
(41, 'Lemon Tea Dingin', 11000, 2),
(42, 'Cappuccino', 18000, 2),
(43, 'Jus Nanas', 13000, 2),
(44, 'Jus Sirsak', 14000, 2),
(45, 'Jus Tomat', 13000, 2),
(46, 'Pancake Pisang', 16000, 3),
(47, 'Es Krim Vanilla', 15000, 3),
(48, 'Fruit Salad', 17000, 3),
(49, 'Tiramisu', 22000, 3),
(50, 'Kue Lapis Legit', 18000, 3),
(51, 'Donat Tabur Gula', 10000, 3),
(52, 'Klepon Tradisional', 8000, 3);

-- --------------------------------------------------------

--
-- Struktur dari tabel `transaksi`
--

CREATE TABLE `transaksi` (
  `id_transaksi` int(11) NOT NULL,
  `nama_pelanggan` varchar(100) NOT NULL,
  `tanggal` timestamp NOT NULL DEFAULT current_timestamp(),
  `subtotal` int(11) DEFAULT 0,
  `ppn` int(11) DEFAULT 0,
  `service` int(11) DEFAULT 0,
  `total` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `transaksi`
--

INSERT INTO `transaksi` (`id_transaksi`, `nama_pelanggan`, `tanggal`, `subtotal`, `ppn`, `service`, `total`) VALUES
(1, 'Andi', '2025-11-05 09:41:28', 73000, 7300, 1825, 82125),
(2, 'Budi', '2025-11-05 09:41:28', 76000, 7600, 1900, 85500),
(3, 'Alya', '2025-11-05 10:53:17', 60000, 6000, 1500, 60000),
(4, 'Naya', '2025-11-05 18:11:56', 22000, 2200, 550, 24750);

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id_user` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','owner','kasir') NOT NULL,
  `nama` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id_user`, `username`, `password`, `role`, `nama`) VALUES
(1, 'admin', 'admin123', 'admin', 'Admin User'),
(2, 'owner', 'owner123', 'owner', 'Owner User'),
(3, 'kasir', 'kasir123', 'kasir', 'Kasir User');

-- --------------------------------------------------------

--
-- Struktur untuk view `laporan_penjualan`
--
DROP TABLE IF EXISTS `laporan_penjualan`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `laporan_penjualan`  AS SELECT `t`.`id_transaksi` AS `id_transaksi`, `t`.`nama_pelanggan` AS `nama_pelanggan`, `t`.`tanggal` AS `tanggal`, `m`.`nama_menu` AS `nama_menu`, `k`.`nama_kategori` AS `nama_kategori`, `d`.`jumlah` AS `jumlah`, `m`.`harga`* `d`.`jumlah` AS `subtotal`, round(`m`.`harga` * `d`.`jumlah` * 0.10,0) AS `ppn`, round(`m`.`harga` * `d`.`jumlah` * 0.025,0) AS `service`, round(`m`.`harga` * `d`.`jumlah` * 1.125,0) AS `total_permenu` FROM (((`transaksi` `t` join `detail_transaksi` `d` on(`t`.`id_transaksi` = `d`.`id_transaksi`)) join `menu` `m` on(`d`.`id_menu` = `m`.`id_menu`)) join `kategori_menu` `k` on(`m`.`id_kategori` = `k`.`id_kategori`)) ORDER BY `t`.`id_transaksi` ASC, `d`.`id_detail` ASC ;

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD PRIMARY KEY (`id_detail`),
  ADD KEY `id_transaksi` (`id_transaksi`),
  ADD KEY `id_menu` (`id_menu`);

--
-- Indeks untuk tabel `kategori_menu`
--
ALTER TABLE `kategori_menu`
  ADD PRIMARY KEY (`id_kategori`);

--
-- Indeks untuk tabel `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`id_menu`),
  ADD KEY `id_kategori` (`id_kategori`);

--
-- Indeks untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id_transaksi`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  MODIFY `id_detail` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT untuk tabel `kategori_menu`
--
ALTER TABLE `kategori_menu`
  MODIFY `id_kategori` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT untuk tabel `menu`
--
ALTER TABLE `menu`
  MODIFY `id_menu` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT untuk tabel `transaksi`
--
ALTER TABLE `transaksi`
  MODIFY `id_transaksi` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD CONSTRAINT `detail_transaksi_ibfk_1` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id_transaksi`),
  ADD CONSTRAINT `detail_transaksi_ibfk_2` FOREIGN KEY (`id_menu`) REFERENCES `menu` (`id_menu`);

--
-- Ketidakleluasaan untuk tabel `menu`
--
ALTER TABLE `menu`
  ADD CONSTRAINT `menu_ibfk_1` FOREIGN KEY (`id_kategori`) REFERENCES `kategori_menu` (`id_kategori`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
