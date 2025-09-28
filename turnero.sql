-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               10.4.32-MariaDB - mariadb.org binary distribution
-- Server OS:                    Win64
-- HeidiSQL Version:             12.11.0.7065
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;


-- Dumping database structure for turnero
CREATE DATABASE IF NOT EXISTS `turnero` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;
USE `turnero`;

-- Dumping structure for table turnero.ads
CREATE TABLE IF NOT EXISTS `ads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `media_type` enum('image','video') NOT NULL,
  `url` varchar(255) NOT NULL,
  `duration_sec` int(11) DEFAULT 8,
  `enabled` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table turnero.ads: ~3 rows (approximately)
REPLACE INTO `ads` (`id`, `media_type`, `url`, `duration_sec`, `enabled`) VALUES
	(1, 'image', '/turnero/publicidad/1.jpg', 7, 1),
	(2, 'image', '/turnero/publicidad/2.jpg', 7, 1),
	(3, 'image', '/turnero/publicidad/mi_logo.png', 8, 1);

-- Dumping structure for table turnero.queues
CREATE TABLE IF NOT EXISTS `queues` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `prefix` varchar(10) DEFAULT 'C',
  `pad` tinyint(4) NOT NULL DEFAULT 3,
  `logo` varchar(255) DEFAULT NULL,
  `current_number` int(11) DEFAULT 0,
  `last_number` int(11) DEFAULT 0,
  `reset_daily` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table turnero.queues: ~2 rows (approximately)
REPLACE INTO `queues` (`id`, `name`, `prefix`, `pad`, `logo`, `current_number`, `last_number`, `reset_daily`) VALUES
	(1, 'Carnicería', 'C', 3, NULL, 14, 1, 1),
	(2, '', 'C', 3, NULL, 0, 0, 1);

-- Dumping structure for table turnero.tickets
CREATE TABLE IF NOT EXISTS `tickets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `queue_id` int(11) NOT NULL,
  `number` int(11) NOT NULL,
  `printed_at` datetime DEFAULT current_timestamp(),
  `status` enum('waiting','called','served','cancelled') DEFAULT 'waiting',
  `called_at` datetime DEFAULT NULL,
  `served_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_ticket` (`queue_id`,`number`),
  KEY `idx_tickets_called_at` (`queue_id`,`called_at`),
  KEY `idx_qn` (`queue_id`,`number`),
  CONSTRAINT `tickets_ibfk_1` FOREIGN KEY (`queue_id`) REFERENCES `queues` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=76 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Dumping data for table turnero.tickets: ~16 rows (approximately)
REPLACE INTO `tickets` (`id`, `queue_id`, `number`, `printed_at`, `status`, `called_at`, `served_at`, `created_at`) VALUES
	(50, 2, 1, '2025-09-18 19:44:19', 'waiting', NULL, NULL, '2025-09-18 19:44:19'),
	(61, 1, 1, '2025-09-19 14:54:01', 'called', '2025-09-19 14:54:09', NULL, '2025-09-19 14:54:01'),
	(62, 1, 2, '2025-09-19 14:54:05', 'called', '2025-09-19 15:20:32', NULL, '2025-09-19 14:54:05'),
	(63, 1, 3, '2025-09-19 15:20:28', 'called', '2025-09-19 15:20:36', NULL, '2025-09-19 15:20:28'),
	(64, 1, 4, '2025-09-19 15:20:51', 'called', '2025-09-19 15:21:02', NULL, '2025-09-19 15:20:51'),
	(65, 1, 5, '2025-09-19 15:20:54', 'called', '2025-09-19 15:21:09', NULL, '2025-09-19 15:20:54'),
	(66, 1, 6, '2025-09-19 15:20:55', 'called', '2025-09-19 15:21:16', NULL, '2025-09-19 15:20:55'),
	(67, 1, 7, '2025-09-19 15:20:58', 'called', '2025-09-19 15:50:17', '2025-09-19 15:21:36', '2025-09-19 15:20:58'),
	(68, 1, 8, '2025-09-19 15:49:26', 'served', '2025-09-19 15:50:07', '2025-09-19 15:50:15', '2025-09-19 15:49:26'),
	(69, 1, 9, '2025-09-19 15:49:28', 'called', '2025-09-19 15:49:34', NULL, '2025-09-19 15:49:28'),
	(70, 1, 10, '2025-09-19 17:31:10', 'called', '2025-09-19 17:31:15', NULL, '2025-09-19 17:31:10'),
	(71, 1, 11, '2025-09-20 10:27:52', 'served', '2025-09-20 10:28:01', '2025-09-20 10:28:08', '2025-09-20 10:27:52'),
	(72, 1, 12, '2025-09-20 11:43:53', 'called', '2025-09-20 14:57:18', NULL, '2025-09-20 11:43:53'),
	(73, 1, 13, '2025-09-20 14:56:53', 'called', '2025-09-20 14:57:24', NULL, '2025-09-20 14:56:53'),
	(74, 1, 14, '2025-09-20 14:57:00', 'served', '2025-09-20 14:57:29', '2025-09-20 14:57:56', '2025-09-20 14:57:00'),
	(75, 1, 15, '2025-09-20 14:57:57', 'waiting', NULL, NULL, '2025-09-20 14:57:57');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
