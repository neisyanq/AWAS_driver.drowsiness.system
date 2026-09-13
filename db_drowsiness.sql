-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.4.3 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.8.0.6908
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for db_drowsiness
CREATE DATABASE IF NOT EXISTS `db_drowsiness` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `db_drowsiness`;

-- Dumping structure for table db_drowsiness.drowsiness_events
CREATE TABLE IF NOT EXISTS `drowsiness_events` (
  `id` int NOT NULL AUTO_INCREMENT,
  `trip_id` int NOT NULL,
  `event_type` enum('YAWNING','MILD_FATIGUE','DROWSY','SEVERE_DROWSINESS','MICROSLEEP') NOT NULL,
  `attention_score` int NOT NULL,
  `dsi_status` varchar(50) NOT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `trip_id` (`trip_id`),
  CONSTRAINT `drowsiness_events_ibfk_1` FOREIGN KEY (`trip_id`) REFERENCES `trips` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table db_drowsiness.drowsiness_events: ~0 rows (approximately)

-- Dumping structure for table db_drowsiness.emergency_contacts
CREATE TABLE IF NOT EXISTS `emergency_contacts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `nama_kontak` varchar(100) NOT NULL,
  `no_hp` varchar(20) NOT NULL,
  `hubungan` varchar(50) NOT NULL,
  `is_primary` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `emergency_contacts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table db_drowsiness.emergency_contacts: ~0 rows (approximately)

-- Dumping structure for table db_drowsiness.emergency_events
CREATE TABLE IF NOT EXISTS `emergency_events` (
  `id` int NOT NULL AUTO_INCREMENT,
  `trip_id` int NOT NULL,
  `severity` enum('WARNING','CRITICAL','UNRESPONSIVE') NOT NULL,
  `status` enum('ACTIVE','ACKNOWLEDGED','RESOLVED') DEFAULT 'ACTIVE',
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `resolved_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `trip_id` (`trip_id`),
  CONSTRAINT `emergency_events_ibfk_1` FOREIGN KEY (`trip_id`) REFERENCES `trips` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table db_drowsiness.emergency_events: ~0 rows (approximately)

-- Dumping structure for table db_drowsiness.gps_tracking
CREATE TABLE IF NOT EXISTS `gps_tracking` (
  `id` int NOT NULL AUTO_INCREMENT,
  `trip_id` int NOT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `speed` int DEFAULT '0',
  `arah_angin` varchar(50) DEFAULT NULL,
  `timestamp` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `trip_id` (`trip_id`),
  CONSTRAINT `gps_tracking_ibfk_1` FOREIGN KEY (`trip_id`) REFERENCES `trips` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table db_drowsiness.gps_tracking: ~0 rows (approximately)

-- Dumping structure for table db_drowsiness.log_pelanggaran
CREATE TABLE IF NOT EXISTS `log_pelanggaran` (
  `id` int NOT NULL AUTO_INCREMENT,
  `waktu` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` varchar(50) NOT NULL,
  `attention_score` int NOT NULL,
  `dsi` varchar(50) NOT NULL,
  `yawning` varchar(10) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table db_drowsiness.log_pelanggaran: ~5 rows (approximately)
INSERT INTO `log_pelanggaran` (`id`, `waktu`, `status`, `attention_score`, `dsi`, `yawning`) VALUES
	(1, '2026-06-25 12:10:19', 'WAITING', 100, 'WAITING', 'NO'),
	(2, '2026-06-25 12:14:09', 'WAITING', 100, 'WAITING', 'NO'),
	(3, '2026-06-25 12:14:45', 'WAITING', 100, 'WAITING', 'NO'),
	(4, '2026-08-09 15:35:28', 'WAITING', 100, 'WAITING', 'NO'),
	(5, '2026-08-10 10:42:42', 'WAITING', 100, 'WAITING', 'NO');

-- Dumping structure for table db_drowsiness.trips
CREATE TABLE IF NOT EXISTS `trips` (
  `id` int NOT NULL AUTO_INCREMENT,
  `driver_id` int NOT NULL,
  `vehicle_id` int NOT NULL,
  `trip_id` varchar(50) NOT NULL,
  `start_location` varchar(255) NOT NULL,
  `destination` varchar(255) NOT NULL,
  `start_time` datetime NOT NULL,
  `end_time` datetime DEFAULT NULL,
  `status` enum('ongoing','paused','completed','emergency') DEFAULT 'ongoing',
  `final_safety_score` int DEFAULT '100',
  PRIMARY KEY (`id`),
  UNIQUE KEY `trip_id` (`trip_id`),
  KEY `driver_id` (`driver_id`),
  KEY `vehicle_id` (`vehicle_id`),
  CONSTRAINT `trips_ibfk_1` FOREIGN KEY (`driver_id`) REFERENCES `users` (`id`),
  CONSTRAINT `trips_ibfk_2` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table db_drowsiness.trips: ~0 rows (approximately)

-- Dumping structure for table db_drowsiness.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama_lengkap` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `no_hp` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('driver','admin') DEFAULT 'driver',
  `foto_profil` varchar(255) DEFAULT 'default.png',
  `foto_verifikasi` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table db_drowsiness.users: ~0 rows (approximately)
INSERT INTO `users` (`id`, `nama_lengkap`, `username`, `email`, `no_hp`, `password`, `role`, `foto_profil`, `foto_verifikasi`, `created_at`) VALUES
	(1, 'Neisya Nur Qoyimah', 'neisyanq', 'neisya@awas.com', '08123456789', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'driver', 'default.png', NULL, '2026-08-08 01:16:12');

-- Dumping structure for table db_drowsiness.vehicles
CREATE TABLE IF NOT EXISTS `vehicles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `vehicle_id` varchar(50) NOT NULL,
  `nama_kendaraan` varchar(100) NOT NULL,
  `merek` varchar(50) NOT NULL,
  `model` varchar(50) NOT NULL,
  `tahun` int NOT NULL,
  `warna` varchar(30) NOT NULL,
  `jenis_kendaraan` varchar(50) NOT NULL,
  `plat_nomor` varchar(20) NOT NULL,
  `no_rangka` varchar(100) DEFAULT NULL,
  `no_mesin` varchar(100) DEFAULT NULL,
  `jenis_bbm` varchar(30) DEFAULT NULL,
  `foto_kendaraan` varchar(255) DEFAULT 'default_car.png',
  `status` enum('active','maintenance','inactive') DEFAULT 'active',
  PRIMARY KEY (`id`),
  UNIQUE KEY `vehicle_id` (`vehicle_id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `vehicles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Dumping data for table db_drowsiness.vehicles: ~0 rows (approximately)
INSERT INTO `vehicles` (`id`, `user_id`, `vehicle_id`, `nama_kendaraan`, `merek`, `model`, `tahun`, `warna`, `jenis_kendaraan`, `plat_nomor`, `no_rangka`, `no_mesin`, `jenis_bbm`, `foto_kendaraan`, `status`) VALUES
	(1, 1, 'VHC-001', 'Toyota Avanza', 'Toyota', 'Avanza', 2020, 'Hitam', 'Mobil', 'L 1234 NQ', NULL, NULL, NULL, 'default_car.png', 'active');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
