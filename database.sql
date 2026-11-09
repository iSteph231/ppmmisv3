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

/*
-- Dumping database structure for ppmmisv3
CREATE DATABASE IF NOT EXISTS `ppmmisv3` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `ppmmisv3`;
*/
-- Dumping structure for table ppmmisv3.cache
CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table ppmmisv3.cache: ~0 rows (approximately)

-- Dumping structure for table ppmmisv3.cache_locks
CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table ppmmisv3.cache_locks: ~0 rows (approximately)

-- Dumping structure for table ppmmisv3.facility_requests
CREATE TABLE IF NOT EXISTS `facility_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `request_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `facility` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `requested_date` date NOT NULL,
  `purpose` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `before_photo_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `after_photo_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `facility_requests_request_number_unique` (`request_number`),
  KEY `facility_requests_user_id_created_at_index` (`user_id`,`created_at`),
  KEY `facility_requests_status_index` (`status`),
  CONSTRAINT `facility_requests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table ppmmisv3.facility_requests: ~0 rows (approximately)

-- Dumping structure for table ppmmisv3.failed_jobs
CREATE TABLE IF NOT EXISTS `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table ppmmisv3.failed_jobs: ~0 rows (approximately)

-- Dumping structure for table ppmmisv3.inspection_reports
CREATE TABLE IF NOT EXISTS `inspection_reports` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `work_request_id` bigint unsigned NOT NULL,
  `report_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `scheduled_date` datetime NOT NULL,
  `inspection_notes` text COLLATE utf8mb4_unicode_ci,
  `status` enum('pending','approved','completed','cancelled') COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `findings` text COLLATE utf8mb4_unicode_ci,
  `observation` text COLLATE utf8mb4_unicode_ci,
  `recommendations` text COLLATE utf8mb4_unicode_ci,
  `actual_inspection_date` datetime DEFAULT NULL,
  `inspected_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `inspection_reports_report_number_unique` (`report_number`),
  KEY `inspection_reports_work_request_id_foreign` (`work_request_id`),
  KEY `inspection_reports_inspected_by_foreign` (`inspected_by`),
  CONSTRAINT `inspection_reports_inspected_by_foreign` FOREIGN KEY (`inspected_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `inspection_reports_work_request_id_foreign` FOREIGN KEY (`work_request_id`) REFERENCES `work_requests` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table ppmmisv3.inspection_reports: ~0 rows (approximately)

-- Dumping structure for table ppmmisv3.jobs
CREATE TABLE IF NOT EXISTS `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` smallint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table ppmmisv3.jobs: ~0 rows (approximately)

-- Dumping structure for table ppmmisv3.job_batches
CREATE TABLE IF NOT EXISTS `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table ppmmisv3.job_batches: ~0 rows (approximately)

-- Dumping structure for table ppmmisv3.maintenance_schedules
CREATE TABLE IF NOT EXISTS `maintenance_schedules` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `scheduled_date` date NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `completed_at` timestamp NULL DEFAULT NULL,
  `activity` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `maintenance_in_charge` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `engineer_in_charge` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remarks` text COLLATE utf8mb4_unicode_ci,
  `completion_notes` text COLLATE utf8mb4_unicode_ci,
  `month_year` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table ppmmisv3.maintenance_schedules: ~0 rows (approximately)

-- Dumping structure for table ppmmisv3.migrations
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table ppmmisv3.migrations: ~28 rows (approximately)
INSERT IGNORE INTO `migrations` (`id`, `migration`, `batch`) VALUES
	(1, '0001_01_01_000000_create_users_table', 1),
	(2, '0001_01_01_000001_create_cache_table', 1),
	(3, '0001_01_01_000002_create_jobs_table', 1),
	(4, '2026_04_24_042855_add_role_to_users_table', 1),
	(5, '2026_04_24_055441_create_notifications_table', 1),
	(6, '2026_04_24_061654_add_role_and_active_to_users_table', 1),
	(7, '2026_04_24_062233_create_work_requests_table', 1),
	(8, '2026_04_24_062824_add_columns_to_users_table', 1),
	(9, '2026_04_28_080517_add_user_id_to_work_requests_table', 1),
	(10, '2026_04_28_152605_fix_work_requests_status_enum', 1),
	(11, '2026_05_01_103134_remove_campus_from_work_requests_table', 1),
	(12, '2026_05_01_113103_add_scheduled_fields_to_work_requests_table', 1),
	(13, '2026_05_04_142741_create_inspection_reports_table', 1),
	(14, '2026_05_04_145812_add_approved_status_to_inspection_reports_table', 1),
	(15, '2026_05_05_003054_create_maintenance_schedules_table', 1),
	(16, '2026_05_12_131108_drop_campus_from_maintenance_schedules_table', 1),
	(17, '2026_05_12_193133_add_status_to_maintenance_schedules_table', 1),
	(18, '2026_05_12_211751_add_completion_notes_to_maintenance_schedules_table', 1),
	(19, '2026_05_13_205842_add_otp_fields_to_users_table', 1),
	(20, '2026_05_14_165901_remove_otp_fields_from_users_table', 1),
	(21, '2026_05_24_145826_update_work_requests_work_type_enum_for_ocular_inspection', 1),
	(22, '2026_05_24_205746_add_observation_to_inspection_reports_table', 1),
	(23, '2026_05_24_210550_make_work_requests_description_nullable', 1),
	(24, '2026_09_24_000000_create_facility_requests_table', 1),
	(25, '2026_10_06_052045_add_usage_photos_to_facility_requests_table', 1),
	(26, '2026_11_03_141219_normalize_user_roles', 2),
	(27, '2026_11_03_141314_restrict_user_roles', 3),
	(28, '2026_11_03_141756_add_engineer_to_user_roles', 4);

-- Dumping structure for table ppmmisv3.notifications
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `detail` text COLLATE utf8mb4_unicode_ci,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'general',
  `is_read` tinyint(1) NOT NULL DEFAULT '0',
  `related_id` bigint unsigned DEFAULT NULL,
  `related_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_user_id_is_read_index` (`user_id`,`is_read`),
  KEY `notifications_created_at_index` (`created_at`),
  CONSTRAINT `notifications_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table ppmmisv3.notifications: ~4 rows (approximately)
INSERT IGNORE INTO `notifications` (`id`, `user_id`, `title`, `message`, `detail`, `type`, `is_read`, `related_id`, `related_type`, `created_at`, `updated_at`) VALUES
	(1, 1, 'Welcome to PPMMIS', 'Your account has been created successfully. You can now create maintenance requests.', NULL, 'info', 0, NULL, NULL, '2026-10-09 07:06:29', '2026-10-09 07:06:29'),
	(2, 1, 'System Ready', 'The maintenance system is now fully operational. Submit your requests anytime.', NULL, 'success', 0, NULL, NULL, '2026-10-09 07:06:29', '2026-10-09 07:06:29'),
	(3, 17, 'Welcome to PPMMIS', 'Your account has been created successfully. You can now create maintenance requests.', NULL, 'info', 0, NULL, NULL, '2026-11-03 06:19:05', '2026-11-03 06:19:05'),
	(4, 17, 'System Ready', 'The maintenance system is now fully operational. Submit your requests anytime.', NULL, 'success', 0, NULL, NULL, '2026-11-03 06:19:05', '2026-11-03 06:19:05');

-- Dumping structure for table ppmmisv3.password_reset_tokens
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table ppmmisv3.password_reset_tokens: ~0 rows (approximately)

-- Dumping structure for table ppmmisv3.sessions
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table ppmmisv3.sessions: ~4 rows (approximately)
INSERT IGNORE INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
	('349ovk2m90PsFQcIpkfGcVEXRv5nNCcK0JqiiciH', 17, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJMVnpnYWxCMXFDYU9Rb0hjd1VyWkwyN0RiS0JmNGdQUlNUTXE1YTExIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9kYXNoYm9hcmQiLCJyb3V0ZSI6ImRhc2hib2FyZCJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX0sImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjoxN30=', 1793686749),
	('GMkTLNgTCEPWY85gakHwPdsY3FgIRavQ0CiBvqTg', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJWYzNvQ0xid21OcjdaTmlEc21lZmRtTDdlZFJ2SldyMFJpMGdKdnJqIiwiX2ZsYXNoIjp7Im5ldyI6W10sIm9sZCI6W119LCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvMTI3LjAuMC4xOjgwMDBcL2xvZ2luIiwicm91dGUiOiJsb2dpbiJ9fQ==', 1791529972),
	('sV7W8T75bajDFTTkxHmLNPT8Z9bQT5HXaGDweQp2', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.141.0 Chrome/150.0.7871.250 Electron/43.7.7 Safari/537.36', 'eyJfdG9rZW4iOiJnTFMzZERkTzgzRjRtdW1pRmdHdk9Ybkd2MXRBSTBBbkRYY0xMUXRYIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwIiwicm91dGUiOiJob21lIn0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfX0=', 1793686190),
	('YEzfFGuEs8q9C5S2azrLfIxgtNgAR7nwxAxhpr5W', 1, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJhVlhMQ29kcHBBWHZ5c0FxdXhHQnVGcGZva1Vmc0JDdnBZdDdJUFBpIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC91c2Vycz9wYWdlPTEiLCJyb3V0ZSI6InVzZXJzLmluZGV4In0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfSwibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiOjF9', 1793687052);

-- Dumping structure for table ppmmisv3.users
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `role` enum('admin','user','engineer') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_verified` tinyint(1) NOT NULL DEFAULT '0',
  `department` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone_number` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table ppmmisv3.users: ~16 rows (approximately)
INSERT IGNORE INTO `users` (`id`, `name`, `email`, `email_verified_at`, `password`, `remember_token`, `created_at`, `updated_at`, `role`, `is_active`, `is_verified`, `department`, `phone_number`) VALUES
	(1, 'John Admin', 'admin@psu.edu.ph', NULL, '$2y$12$/iwoqu2XXzz6fgwmGJozDum7FFg7XlJJJ1zRThX0v1BDdrgcMIxTG', NULL, '2026-10-09 07:06:08', '2026-10-09 07:06:08', 'admin', 1, 0, 'IT Department', '123-456-7890'),
	(2, 'John Doe', 'johndoe@psu.edu.ph', NULL, '$2y$12$JFzbl6a3mgbZHWHY/WGv0..dNfQysoSjP9QLmf.F1uXNSTL/qLwKC', NULL, '2026-10-09 07:06:09', '2026-10-09 07:06:09', 'user', 1, 0, 'Facilities', '123-456-7891'),
	(3, 'Jane Smith', 'jane@psu.edu.ph', NULL, '$2y$12$2E2hYIh8O5hk7zW50e7sTO0KhL/9KubPTGu4jXCVYN8nsWbS0L8KS', NULL, '2026-10-09 07:06:09', '2026-10-09 07:06:09', 'user', 1, 0, 'Operations', '123-456-7892'),
	(4, 'Mike Maintenance', 'mike@psu.edu.ph', NULL, '$2y$12$t4dCOw7cxlXkOnXDJnarQe3sWbcsEH3qhdGrxmhM4Tk04DMOCQiQm', NULL, '2026-10-09 07:06:09', '2026-10-09 07:06:09', 'user', 1, 0, 'Maintenance', '123-456-7893'),
	(5, 'Sarah Johnson', 'sarah@psu.edu.ph', NULL, '$2y$12$WcPwzWSC8.4N9a00rn4H3OMmiNH7Flt1n62tVWCDcFc630Fjje7WC', NULL, '2026-10-09 07:06:09', '2026-10-09 07:06:09', 'user', 1, 0, 'HR', '123-456-7894'),
	(6, 'Robert Chen', 'robert@psu.edu.ph', NULL, '$2y$12$D/rYYCFqfINQ03REOrCm9O9uzrV4ISR6fWSXa05Zv8WG93hXLAGhq', NULL, '2026-10-09 07:06:09', '2026-10-09 07:06:09', 'user', 1, 0, 'Engineering', '123-456-7895'),
	(7, 'Merlin Fahey', 'oberbrunner.lonie@example.org', NULL, '$2y$12$khFQrCuq/ugKv0dMG7p4Ee.WBuwWEUCAqn/Nu0tnTyPKARGsa.EJS', NULL, '2026-10-09 07:06:12', '2026-10-09 07:06:12', 'user', 1, 0, 'Operations', '(732) 861-7094'),
	(8, 'Dr. Joshua Watsica Jr.', 'lee81@example.com', NULL, '$2y$12$2wJsNN0fro7sVab4OWBzDe.7ZZRSFTLrenSCWb2Mf/Uxwx7W9x4fS', NULL, '2026-10-09 07:06:12', '2026-10-09 07:06:12', 'user', 1, 0, 'Operations', '1-480-248-9918'),
	(9, 'Marcelo Klocko', 'ianderson@example.com', NULL, '$2y$12$QR8OlnJy7Thi8ClgDcxlrejsAzE5839YF.xpGhaluO7csY4tG0O12', NULL, '2026-10-09 07:06:12', '2026-10-09 07:06:12', 'user', 1, 0, 'Operations', '754.226.9602'),
	(10, 'Prof. Lilla Mohr', 'pansy51@example.org', NULL, '$2y$12$MT2TEJn5Fc3UsaX8u54Fee/0FrXzLdYmmBf21.Da1IQQw41Z2EOIK', NULL, '2026-10-09 07:06:12', '2026-10-09 07:06:12', 'user', 1, 0, 'HR', '848-445-3520'),
	(11, 'Nathan Blanda', 'lind.bulah@example.org', NULL, '$2y$12$Xw.TG9q6.ANpGLfYyS9Yq.QNaOjrdtq28nfy6fB0JTY7Ljy1lrHoK', NULL, '2026-10-09 07:06:12', '2026-10-09 07:06:12', 'user', 1, 0, 'IT', '(541) 623-8614'),
	(12, 'River Hauck PhD', 'nhayes@example.org', NULL, '$2y$12$70k5ViQNRxSbOKPhSwgjzOhLisUnaOVV8ZSlVmgdfYLunHCTmt.Qi', NULL, '2026-10-09 07:06:12', '2026-10-09 07:06:12', 'user', 1, 0, 'HR', '+1-220-503-7594'),
	(13, 'Mr. Howell Wiegand II', 'hettinger.anibal@example.org', NULL, '$2y$12$CCEyFW7HgrJ.iToS1pSrdORK7fagC.VVoib7VbxYkAHtGQsQAy07K', NULL, '2026-10-09 07:06:12', '2026-10-09 07:06:12', 'user', 1, 0, 'Facilities', '341-377-6417'),
	(14, 'Titus Rowe', 'lottie.botsford@example.org', NULL, '$2y$12$jkYKbYLPg7sG0K/CRdP1ieaEXON45SnQqI8eJ32roFSZOmbCl26b2', NULL, '2026-10-09 07:06:12', '2026-10-09 07:06:12', 'user', 1, 0, 'HR', '470.505.0645'),
	(15, 'Prof. Schuyler Kessler I', 'cummerata.mittie@example.org', NULL, '$2y$12$/V3OiOpGb8oJI6j5dGF.4OSgby//z5/ABJYnc14xESRSmX4Elk3Tu', NULL, '2026-10-09 07:06:13', '2026-10-09 07:06:13', 'user', 1, 0, 'Facilities', '770-838-1057'),
	(16, 'Prof. Maurine Robel II', 'vicky26@example.net', NULL, '$2y$12$/2N6LLfFDrsgw9hhV0Sf3.xkJ2i7NwMLBIyjT5w/YidQKuQnQohk.', NULL, '2026-10-09 07:06:13', '2026-10-09 07:06:13', 'user', 1, 0, 'Operations', '1-806-831-0828'),
	(17, 'Wyeth P', '22as1345_ms@psu.edu.ph', NULL, '$2y$12$xIQd2z9UuXNAdVFjNtX4Gu/Thc0vtjIBXiLxE2LWJ.FqwsHtvPicG', NULL, '2026-11-03 06:19:03', '2026-11-03 06:22:32', 'engineer', 1, 1, NULL, NULL);

-- Dumping structure for table ppmmisv3.work_requests
CREATE TABLE IF NOT EXISTS `work_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `department` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `building_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `office_room` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `work_type` enum('ocular_inspection','installation','repair','replacement','others') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ocular_details` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `installation_details` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `repair_details` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `replacement_details` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `others_details` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pending','approved','completed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `requester_id` bigint unsigned DEFAULT NULL,
  `requester_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `assigned_to` bigint unsigned DEFAULT NULL,
  `assigned_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `request_number` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `admin_notes` text COLLATE utf8mb4_unicode_ci,
  `scheduled_date` datetime DEFAULT NULL,
  `inspection_notes` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `work_requests_request_number_unique` (`request_number`),
  KEY `work_requests_requester_id_foreign` (`requester_id`),
  KEY `work_requests_assigned_to_foreign` (`assigned_to`),
  KEY `work_requests_status_index` (`status`),
  KEY `work_requests_work_type_index` (`work_type`),
  KEY `work_requests_created_at_index` (`created_at`),
  KEY `work_requests_user_id_foreign` (`user_id`),
  CONSTRAINT `work_requests_assigned_to_foreign` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `work_requests_requester_id_foreign` FOREIGN KEY (`requester_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `work_requests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table ppmmisv3.work_requests: ~0 rows (approximately)

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
