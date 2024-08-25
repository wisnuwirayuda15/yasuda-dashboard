-- --------------------------------------------------------
-- Host:                         localhost
-- Server version:               8.3.0 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.5.0.6677
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Dumping structure for table yasuda_dashboard.provinces
CREATE TABLE IF NOT EXISTS `provinces` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lat` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lng` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `google_place_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `provinces_code_unique` (`code`),
  KEY `provinces_name_index` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=38 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table yasuda_dashboard.provinces: ~37 rows (approximately)
INSERT INTO `provinces` (`id`, `code`, `name`, `lat`, `lng`, `google_place_id`, `created_at`, `updated_at`) VALUES
	(1, '11', 'Aceh', '4.695135', '96.7493993', 'ChIJvcR8UN-bOTARYMogsoCdAwE', '2024-08-24 07:03:58', '2024-08-24 07:03:58'),
	(2, '12', 'Sumatera Utara', '2.1153547', '99.5450974', 'ChIJhxxy61r51y8RC8vXCYE-p6w', '2024-08-24 07:03:58', '2024-08-24 07:03:58'),
	(3, '13', 'Sumatera Barat', '-0.7399397', '100.8000051', 'ChIJRUJ08Ey51C8RVTvVdblRsXA', '2024-08-24 07:03:58', '2024-08-24 07:03:58'),
	(4, '14', 'Riau', '0.2933469', '101.7068294', 'ChIJdz6xGVhXJy4Rsb21bJQCb4M', '2024-08-24 07:03:58', '2024-08-24 07:03:58'),
	(5, '15', 'Jambi', '-1.6101229', '103.6131203', 'ChIJO83is5qIJS4RDdmyCseZWtE', '2024-08-24 07:03:58', '2024-08-24 07:03:58'),
	(6, '16', 'Sumatera Selatan', '-3.3194374', '103.914399', 'ChIJLeo1PXWLEC4Rz8QB4gGB_Bg', '2024-08-24 07:03:58', '2024-08-24 07:03:58'),
	(7, '17', 'Bengkulu', '-3.7928451', '102.2607641', 'ChIJeZLjNx6wNi4R6qaQ53a1eaA', '2024-08-24 07:03:58', '2024-08-24 07:03:58'),
	(8, '18', 'Lampung', '-4.5585849', '105.4068079', 'ChIJpyKsUwF2Oy4RmrCJX8dYO48', '2024-08-24 07:03:58', '2024-08-24 07:03:58'),
	(9, '19', 'Kepulauan Bangka Belitung', '-2.7410513', '106.4405872', 'ChIJizmlLUMWFy4RuSOEsf04fhI', '2024-08-24 07:03:58', '2024-08-24 07:03:58'),
	(10, '21', 'Kepulauan Riau', '3.9456514', '108.1428669', 'ChIJAQuH1E1l2TERvCSFiXW1RnI', '2024-08-24 07:03:58', '2024-08-24 07:03:58'),
	(11, '31', 'DKI Jakarta', '-6.223592', '106.7317914', 'ChIJxfRFTqzwaS4R9jhKTRFByAQ', '2024-08-24 07:03:58', '2024-08-24 07:03:58'),
	(12, '32', 'Jawa Barat', '-7.090911', '107.668887', 'ChIJf0dSgjnmaC4Rfp2O_FSkGLw', '2024-08-24 07:03:58', '2024-08-24 07:03:58'),
	(13, '33', 'Jawa Tengah', '-7.150975', '110.1402594', 'ChIJ3RjVnJt1ZS4RRrztj53Rd8M', '2024-08-24 07:03:58', '2024-08-24 07:03:58'),
	(14, '34', 'Daerah Istimewa Yogyakarta', '-7.8753849', '110.4262088', 'ChIJxWtbvYdXei4R8LPIyrKSG20', '2024-08-24 07:03:58', '2024-08-24 07:03:58'),
	(15, '35', 'Jawa Timur', '-7.5360639', '112.2384017', 'ChIJxbXun_eToy0RULh8yvsLAwE', '2024-08-24 07:03:58', '2024-08-24 07:03:58'),
	(16, '36', 'Banten', '-6.4058172', '106.0640179', 'ChIJmbkNxNaKQS4R6bMai6ua074', '2024-08-24 07:03:58', '2024-08-24 07:03:58'),
	(17, '51', 'Bali', '-8.4095178', '115.188916', 'ChIJoQ8Q6NNB0S0RkOYkS7EPkSQ', '2024-08-24 07:03:58', '2024-08-24 07:03:58'),
	(18, '52', 'Nusa Tenggara Barat', '-8.6529334', '117.3616476', 'ChIJIe0SGpQNuC0RxXX30MzCZ2k', '2024-08-24 07:03:58', '2024-08-24 07:03:58'),
	(19, '53', 'Nusa Tenggara Timur', '-8.6573819', '121.0793705', 'ChIJlzbpqE3yUiwR4Br3yvsLAwE', '2024-08-24 07:03:58', '2024-08-24 07:03:58'),
	(20, '61', 'Kalimantan Barat', '-0.2787808', '111.4752851', 'ChIJu_7rjBcYBS4RoEghTO3sXM0', '2024-08-24 07:03:58', '2024-08-24 07:03:58'),
	(21, '62', 'Kalimantan Tengah', '-1.6814878', '113.3823545', 'ChIJP5a8hrK_4i0Rrmv1g2fV288', '2024-08-24 07:03:58', '2024-08-24 07:03:58'),
	(22, '63', 'Kalimantan Selatan', '-3.0926415', '115.2837585', 'ChIJRbTSvTm33S0RE8GXt1C2fhQ', '2024-08-24 07:03:58', '2024-08-24 07:03:58'),
	(23, '64', 'Kalimantan Timur', '0.5386586', '116.419389', 'ChIJkZxNlhBH8S0R13bjLx2wq8Q', '2024-08-24 07:03:58', '2024-08-24 07:03:58'),
	(24, '65', 'Kalimantan Utara', '3.0730929', '116.0413889', 'ChIJ9wvfNH0GDzIRiLlGaN3wERk', '2024-08-24 07:03:58', '2024-08-24 07:03:58'),
	(25, '71', 'Sulawesi Utara', '0.6246932', '123.9750018', 'ChIJMZ4GcEJ1hzIRNbgMmBcWiUU', '2024-08-24 07:03:58', '2024-08-24 07:03:58'),
	(26, '72', 'Sulawesi Tengah', '-1.4300254', '121.4456179', 'ChIJPS2aZckJiC0RmWLbjP0zbkM', '2024-08-24 07:03:58', '2024-08-24 07:03:58'),
	(27, '73', 'Sulawesi Selatan', '-3.6687994', '119.9740534', 'ChIJi75r_YD6DC0R8Br3yvsLAwE', '2024-08-24 07:03:58', '2024-08-24 07:03:58'),
	(28, '74', 'Sulawesi Tenggara', '-1.8479', '120.5279', 'ChIJMSoBqds3hS0RQnf0aNFRmrw', '2024-08-24 07:03:58', '2024-08-24 07:03:58'),
	(29, '75', 'Gorontalo', '0.5435442', '123.0567693', 'ChIJXeflmUcreTIRZ1kVIwlNzG0', '2024-08-24 07:03:58', '2024-08-24 07:03:58'),
	(30, '76', 'Sulawesi Barat', '-2.8441371', '119.2320784', 'ChIJCUS7VCTaki0R8nAzLyC_XOo', '2024-08-24 07:03:58', '2024-08-24 07:03:58'),
	(31, '81', 'Maluku', '-3.2384616', '130.1452734', 'ChIJ36EccLq8ES0RUZpkBNvoMF4', '2024-08-24 07:03:58', '2024-08-24 07:03:58'),
	(32, '82', 'Maluku Utara', '1.5709993', '127.8087693', 'ChIJszIkro6uni0RwBr3yvsLAwE', '2024-08-24 07:03:58', '2024-08-24 07:03:58'),
	(33, '91', 'Papua', '-4.269928', '138.0803529', 'ChIJc5L_qgQsO2gRc-bvXpxOqes', '2024-08-24 07:03:58', '2024-08-24 07:03:58'),
	(34, '92', 'Papua Barat', '-1.3361154', '133.1747162', 'ChIJLQviub0KVC0RYsvHxfjBSVM', '2024-08-24 07:03:58', '2024-08-24 07:03:58'),
	(35, '93', 'Papua Selatan', '-4.269928', '138.0803529', 'ChIJc5L_qgQsO2gRc-bvXpxOqes', '2024-08-24 07:03:58', '2024-08-24 07:03:58'),
	(36, '94', 'Papua Tengah', '-4.269928', '138.0803529', 'ChIJc5L_qgQsO2gRc-bvXpxOqes', '2024-08-24 07:03:58', '2024-08-24 07:03:58'),
	(37, '95', 'Papua Pegunungan', '-4.269928', '138.0803529', 'ChIJc5L_qgQsO2gRc-bvXpxOqes', '2024-08-24 07:03:58', '2024-08-24 07:03:58');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
