/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

CREATE DATABASE IF NOT EXISTS `easyresto` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `easyresto`;

CREATE TABLE IF NOT EXISTS `detail_transaksi` (
  `id_detail` int NOT NULL AUTO_INCREMENT,
  `id_transaksi` int DEFAULT NULL,
  `id_menu` int DEFAULT NULL,
  `jumlah` int NOT NULL,
  `subtotal` int DEFAULT '0',
  PRIMARY KEY (`id_detail`),
  KEY `id_transaksi` (`id_transaksi`),
  KEY `id_menu` (`id_menu`),
  CONSTRAINT `detail_transaksi_ibfk_1` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id_transaksi`),
  CONSTRAINT `detail_transaksi_ibfk_2` FOREIGN KEY (`id_menu`) REFERENCES `menu` (`id_menu`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `detail_transaksi` (`id_detail`, `id_transaksi`, `id_menu`, `jumlah`, `subtotal`) VALUES
	(1, 1, 6, 1, 22000),
	(2, 1, 10, 1, 34000),
	(3, 1, 16, 1, 15000),
	(4, 1, 15, 1, 17000),
	(5, 2, 20, 1, 29000),
	(6, 2, 11, 1, 8000),
	(7, 2, 18, 1, 18000),
	(8, 3, 6, 1, 22000),
	(9, 3, 23, 1, 28000),
	(10, 3, 11, 1, 8000),
	(11, 3, 18, 1, 18000),
	(12, 4, 6, 1, 22000),
	(13, 4, 19, 1, 32000),
	(14, 4, 17, 1, 20000),
	(15, 5, 6, 1, 22000),
	(16, 5, 20, 1, 29000),
	(17, 5, 14, 1, 12000),
	(18, 6, 10, 1, 34000),
	(19, 6, 15, 1, 17000),
	(20, 6, 17, 1, 20000),
	(21, 7, 6, 1, 22000),
	(22, 7, 21, 1, 33000),
	(23, 8, 10, 1, 34000),
	(24, 8, 6, 1, 22000);

CREATE TABLE IF NOT EXISTS `kategori_menu` (
  `id_kategori` int NOT NULL AUTO_INCREMENT,
  `nama_kategori` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  PRIMARY KEY (`id_kategori`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `kategori_menu` (`id_kategori`, `nama_kategori`) VALUES
	(1, 'Makanan'),
	(2, 'Minuman'),
	(3, 'Makanan Penutup');

CREATE TABLE `laporan_penjualan` (
	`id_transaksi` INT(10) NOT NULL,
	`nama_pelanggan` VARCHAR(100) NOT NULL COLLATE 'utf8mb4_general_ci',
	`tanggal` TIMESTAMP NOT NULL,
	`nama_menu` VARCHAR(100) NOT NULL COLLATE 'utf8mb4_general_ci',
	`nama_kategori` VARCHAR(50) NOT NULL COLLATE 'utf8mb4_general_ci',
	`jumlah` INT(10) NOT NULL,
	`subtotal` BIGINT(19) NOT NULL,
	`ppn` DECIMAL(22,0) NOT NULL,
	`service` DECIMAL(22,0) NOT NULL,
	`total_permenu` DECIMAL(22,0) NOT NULL
) ENGINE=MyISAM;

CREATE TABLE IF NOT EXISTS `menu` (
  `id_menu` int NOT NULL AUTO_INCREMENT,
  `nama_menu` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `harga` int NOT NULL,
  `id_kategori` int DEFAULT NULL,
  `foto` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id_menu`),
  KEY `id_kategori` (`id_kategori`),
  CONSTRAINT `menu_ibfk_1` FOREIGN KEY (`id_kategori`) REFERENCES `kategori_menu` (`id_kategori`)
) ENGINE=InnoDB AUTO_INCREMENT=54 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `menu` (`id_menu`, `nama_menu`, `harga`, `id_kategori`, `foto`) VALUES
	(1, 'Nasi Goreng Spesial', 25000, 1, '693953a7b2f81.jpg'),
	(2, 'Ayam Bakar Madu', 30000, 1, '6939533de260d.jpg'),
	(3, 'Sate Ayam', 28000, 1, '693952fedc3f8.jpg'),
	(4, 'Mie Goreng Jawa', 23000, 1, '693952155ae58.jpg'),
	(5, 'Soto Ayam Lamongan', 27000, 1, '693951f58ec2f.jpg'),
	(6, 'Bakso Urat', 22000, 1, '693951bc6934d.jpg'),
	(7, 'Nasi Uduk Komplit', 26000, 1, '6939513214882.jpg'),
	(8, 'Rawon Daging', 35000, 1, '693950cb3da70.jpg'),
	(9, 'Rendang Padang', 38000, 1, '693950656bb06.webp'),
	(10, 'Ikan Bakar Rica', 34000, 1, '693950278e7b3.jpg'),
	(11, 'Es Teh Manis', 8000, 2, '69394de395e83.jpg'),
	(12, 'Es Jeruk Segar', 10000, 2, '69394dc991750.jpg'),
	(13, 'Jus Alpukat', 15000, 2, '69394e0044145.jpg'),
	(14, 'Kopi Hitam Panas', 12000, 2, '69394e159008b.jpg'),
	(15, 'Milkshake Coklat', 17000, 2, '69394e84ecb0e.webp'),
	(16, 'Puding Coklat', 15000, 3, '693950126c9bd.webp'),
	(17, 'Cheesecake', 20000, 3, '69394da12d165.jpg'),
	(18, 'Brownies Lumer', 18000, 3, '69394d7ce4b86.jpg'),
	(19, 'Nasi Goreng Seafood', 32000, 1, '69394eb9cf0b5.jpg'),
	(20, 'Ayam Penyet Sambal Ijo', 29000, 1, '69394e66cf782.jpg'),
	(21, 'Nasi Campur Bali', 33000, 1, '69394e9e21263.jpg'),
	(22, 'Tongseng Kambing', 36000, 1, '69394ede7f429.jpg'),
	(23, 'Mie Aceh', 28000, 1, '69394e5aca4ae.jpg'),
	(53, 'Jus Mangga', 10000, 2, '693aa4d09b591._edit.webp');

CREATE TABLE IF NOT EXISTS `transaksi` (
  `id_transaksi` int NOT NULL AUTO_INCREMENT,
  `nama_pelanggan` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `tanggal` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `subtotal` int DEFAULT '0',
  `ppn` int DEFAULT '0',
  `service` int DEFAULT '0',
  `total` int DEFAULT '0',
  PRIMARY KEY (`id_transaksi`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `transaksi` (`id_transaksi`, `nama_pelanggan`, `tanggal`, `subtotal`, `ppn`, `service`, `total`) VALUES
	(1, 'zira', '2025-12-10 07:42:58', 88000, 8800, 2200, 99000),
	(2, 'naya', '2025-12-10 11:10:48', 55000, 5500, 1375, 61875),
	(3, 'rizal', '2025-12-10 11:33:02', 76000, 7600, 1900, 85500),
	(4, 'aya', '2025-12-11 05:00:09', 74000, 7400, 1850, 83250),
	(5, 'alya', '2025-12-11 11:48:03', 63000, 6300, 1575, 70875),
	(6, 'alya nayra', '2025-12-11 11:49:27', 71000, 7100, 1775, 79875),
	(7, 'alya', '2025-12-11 12:33:46', 55000, 5500, 1375, 61875),
	(8, 'gio', '2025-12-11 14:05:17', 56000, 5600, 1400, 63000);

CREATE TABLE IF NOT EXISTS `users` (
  `id_user` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `role` enum('admin','owner','kasir') COLLATE utf8mb4_general_ci NOT NULL,
  `nama` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `phone_number` varchar(15) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `profile_picture` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `users` (`id_user`, `username`, `password`, `role`, `nama`, `phone_number`, `profile_picture`) VALUES
	(1, 'admin', 'admin123', 'admin', 'Admin User', '082167436859', '../uploads/profil/693acfec15a92_1765461996.jpg'),
	(2, 'owner', 'owner123', 'owner', 'Owner User', NULL, '../uploads/profil/693abe00ab26c_1765457408.jpg'),
	(3, 'kasir', '6d2f2d182c03040daeddbd634291813b', 'kasir', 'Alya Nayra Syafiqa', '082167576527', 'uploads/profil/693aaf29773fc_1765453609.jpg');

SET @OLDTMP_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO';
DELIMITER //
CREATE TRIGGER `trg_subtotal_insert` BEFORE INSERT ON `detail_transaksi` FOR EACH ROW BEGIN
  DECLARE harga_menu INT;
  SELECT harga INTO harga_menu FROM menu WHERE id_menu = NEW.id_menu;
  SET NEW.subtotal = harga_menu * NEW.jumlah;
END//
DELIMITER ;
SET SQL_MODE=@OLDTMP_SQL_MODE;

DROP TABLE IF EXISTS `laporan_penjualan`;
CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `laporan_penjualan` AS select `t`.`id_transaksi` AS `id_transaksi`,`t`.`nama_pelanggan` AS `nama_pelanggan`,`t`.`tanggal` AS `tanggal`,`m`.`nama_menu` AS `nama_menu`,`k`.`nama_kategori` AS `nama_kategori`,`d`.`jumlah` AS `jumlah`,(`m`.`harga` * `d`.`jumlah`) AS `subtotal`,round(((`m`.`harga` * `d`.`jumlah`) * 0.10),0) AS `ppn`,round(((`m`.`harga` * `d`.`jumlah`) * 0.025),0) AS `service`,round(((`m`.`harga` * `d`.`jumlah`) * 1.125),0) AS `total_permenu` from (((`transaksi` `t` join `detail_transaksi` `d` on((`t`.`id_transaksi` = `d`.`id_transaksi`))) join `menu` `m` on((`d`.`id_menu` = `m`.`id_menu`))) join `kategori_menu` `k` on((`m`.`id_kategori` = `k`.`id_kategori`))) order by `t`.`id_transaksi`,`d`.`id_detail`;

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
