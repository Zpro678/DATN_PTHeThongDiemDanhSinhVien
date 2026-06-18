-- MySQL dump 10.13  Distrib 8.4.9, for Linux (x86_64)
--
-- Host: localhost    Database: db_quan_ly_diem_danh
-- ------------------------------------------------------
-- Server version	8.4.9

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `attendance_records`
--

DROP TABLE IF EXISTS `attendance_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `attendance_records` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `class_session_id` bigint unsigned NOT NULL,
  `class_member_id` bigint unsigned NOT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `is_verified` tinyint(1) NOT NULL DEFAULT '0',
  `check_in_time` timestamp NULL DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `device_fingerprint` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `distance_meters` decimal(8,2) DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `attendance_records_class_session_id_class_member_id_unique` (`class_session_id`,`class_member_id`),
  KEY `attendance_records_class_member_id_foreign` (`class_member_id`),
  KEY `attendance_records_class_session_id_status_index` (`class_session_id`,`status`),
  CONSTRAINT `attendance_records_class_member_id_foreign` FOREIGN KEY (`class_member_id`) REFERENCES `class_members` (`id`) ON DELETE CASCADE,
  CONSTRAINT `attendance_records_class_session_id_foreign` FOREIGN KEY (`class_session_id`) REFERENCES `class_sessions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attendance_records`
--

LOCK TABLES `attendance_records` WRITE;
/*!40000 ALTER TABLE `attendance_records` DISABLE KEYS */;
INSERT INTO `attendance_records` VALUES (1,1,1,'absent',1,NULL,'244.43.108.211','38beaf614640d85d94bebb0bdb72e46b91345401372ff4e63520ce4a6946f9da',40.36,NULL,'2026-06-18 03:04:11','2026-06-18 03:04:11',NULL),(2,1,2,'late',1,'2026-06-04 07:20:00','250.150.26.141','ed960f2bcf5ea2673064145dd4a933ae3763d8092962089cb4d701c54ff33084',22.16,NULL,'2026-06-18 03:04:11','2026-06-18 03:04:11',NULL),(3,1,3,'present',1,'2026-06-04 07:00:00','131.91.69.243','2932e98f3f91ed7fe90b15ee72546e7a40dda43cd465c12069f5b6b6483689b9',5.03,NULL,'2026-06-18 03:04:11','2026-06-18 03:04:11',NULL),(4,1,4,'present',1,'2026-06-04 07:00:00','25.138.63.247','1efe40f793dcac6239570af96a525975f9369c166d7d21cb446975f6fb7942ef',50.70,NULL,'2026-06-18 03:04:11','2026-06-18 03:04:11',NULL),(5,1,5,'present',1,'2026-06-04 07:00:00','224.102.189.13','629ea8795a93dbbeb7cc21ca3b24c124f9b5dc9c656d0d2d0ceb03744caf1471',35.72,NULL,'2026-06-18 03:04:11','2026-06-18 03:04:11',NULL),(6,1,6,'present',1,'2026-06-04 07:00:00','175.147.52.5','c721b19da533b68c742f6fb466907636a5fa9cc67a413e276c7c82b64f506e34',16.63,NULL,'2026-06-18 03:04:11','2026-06-18 03:04:11',NULL),(7,1,7,'present',1,'2026-06-04 07:00:00','83.29.140.221','44172df3d489eaf245038213010f1e2c07e59eaff50e736d0099f4a35fc4d008',95.07,NULL,'2026-06-18 03:04:11','2026-06-18 03:04:11',NULL),(8,1,8,'present',1,'2026-06-04 07:00:00','252.161.50.201','994a41a7059df16cf8e6225f2e08fe3354df7fa24e5fe74b8eb713bdafacd8ad',72.63,NULL,'2026-06-18 03:04:11','2026-06-18 03:04:11',NULL),(9,1,9,'absent',1,NULL,'83.185.163.247','d7042ba7d02c23b5b2ac172c662e2faa23f189cab3bdc48fd9d3da5972dbb959',85.20,NULL,'2026-06-18 03:04:11','2026-06-18 03:04:11',NULL),(10,1,10,'late',1,'2026-06-04 07:20:00','169.212.200.142','4b9bb5560dc925a556b5875cf5a75b4714eca830f19c6147a066f7833bf82d5f',23.39,NULL,'2026-06-18 03:04:11','2026-06-18 03:04:11',NULL),(11,1,11,'present',1,'2026-06-04 07:00:00','69.113.133.2','64c44060fc40ef0a05589317801695b8415b9f9f98bb8e319f3f24a065939d7c',16.05,NULL,'2026-06-18 03:04:11','2026-06-18 03:04:11',NULL),(12,1,12,'present',1,'2026-06-04 07:00:00','207.243.52.200','6b9ac9e260a9b7aeaa5a3f28ae7d890f149edba1778882afbf36f9a947d95bf9',61.06,NULL,'2026-06-18 03:04:11','2026-06-18 03:04:11',NULL),(13,2,1,'late',1,'2026-06-11 07:20:00','97.10.86.238','e08c88778f4e8ef9b9e5dfac31935ebc7b3fc8d6947ac94196a7198e5dd49cc4',66.67,NULL,'2026-06-18 03:04:11','2026-06-18 03:04:11',NULL),(14,2,2,'present',1,'2026-06-11 07:00:00','140.189.7.116','c719d766cafab5570218388e2213de56d5661b6f8e1ed91b31ffc8faf0996448',53.12,NULL,'2026-06-18 03:04:11','2026-06-18 03:04:11',NULL),(15,2,3,'present',1,'2026-06-11 07:00:00','204.155.28.13','e9934c50d13ea40a821efffb1af44c990d9c93aed680752dc7dc7a1ca584218a',5.43,NULL,'2026-06-18 03:04:11','2026-06-18 03:04:11',NULL),(16,2,4,'present',1,'2026-06-11 07:00:00','228.230.200.92','cc93feb64b37f685be4a42cb06391fd6627700836dce561aae88b838ee3b67e1',53.36,NULL,'2026-06-18 03:04:11','2026-06-18 03:04:11',NULL),(17,2,5,'excused',1,NULL,'132.137.39.248','350b35859d756f781ba30e6161c52a5106b64e65d41b7b212bbc223480921cec',14.14,'Đơn xin nghỉ đã được duyệt.','2026-06-18 03:04:11','2026-06-18 03:04:12',NULL),(18,2,6,'present',1,'2026-06-11 07:00:00','24.132.254.244','8bdd404cd8a8014b5ad85af683d3df1b679b67502eb9c60a790bc43de89af2b0',55.84,NULL,'2026-06-18 03:04:11','2026-06-18 03:04:11',NULL),(19,2,7,'present',1,'2026-06-11 07:00:00','138.59.5.39','2839a85e8952d43fc347d15a6b209f95ce9cae69f7c61ab779a5361abd25ebde',83.71,NULL,'2026-06-18 03:04:11','2026-06-18 03:04:11',NULL),(20,2,8,'absent',1,NULL,'146.180.144.36','e008f117e4b58eb60113d49a38d6c5e24666a8adbd357e473d9284687402ea0d',65.97,NULL,'2026-06-18 03:04:11','2026-06-18 03:04:11',NULL),(21,2,9,'late',1,'2026-06-11 07:20:00','72.89.84.7','ace93b55086abc720d156088f414c77d0f41dc7cd1598b8ba3d8c53c97203054',46.55,NULL,'2026-06-18 03:04:11','2026-06-18 03:04:11',NULL),(22,2,10,'present',1,'2026-06-11 07:00:00','212.118.91.60','f76817d3a3071210cd79c17684a669b56cbb384653b03f21f3bab50b2d6f5dad',17.36,NULL,'2026-06-18 03:04:11','2026-06-18 03:04:11',NULL),(23,2,11,'present',1,'2026-06-11 07:00:00','137.202.75.113','e16b64e4241458e7be103c302fafb2944f46a163d98bb950471e4c512a32344f',64.88,NULL,'2026-06-18 03:04:11','2026-06-18 03:04:11',NULL),(24,2,12,'present',1,'2026-06-11 07:00:00','118.222.39.244','dea28ed320d9dec79fff34ff25a3061d3465e4c7cef6eac5ef63657107607b60',76.09,NULL,'2026-06-18 03:04:11','2026-06-18 03:04:11',NULL),(25,3,1,'present',1,'2026-06-18 07:00:00','248.67.123.241','b6aaf520cacee67fe030497d9664c70e8fd2113836b59201c0411f9d413dcfed',116.19,NULL,'2026-06-18 03:04:11','2026-06-18 03:04:11',NULL),(26,3,2,'present',1,'2026-06-18 07:00:00','135.74.224.116','ba358a3bc9f97748ea496073b3a8f5277ac6714f53193a34dfd7ee3f1c7dcf97',8.82,NULL,'2026-06-18 03:04:11','2026-06-18 03:04:11',NULL),(27,3,3,'present',1,'2026-06-18 07:00:00','191.7.120.214','c66048550b12b80db2589bf28ab5b1676d0f17ebb0627d62e0e9cbe016408d36',5.00,NULL,'2026-06-18 03:04:11','2026-06-18 03:04:11',NULL),(28,3,4,'present',1,'2026-06-18 07:00:00','210.147.143.192','5de14a9f86afacd6afccae17e2afeafc873b73b920b5499bf219d7bf47723d7a',68.46,NULL,'2026-06-18 03:04:11','2026-06-18 03:04:11',NULL),(29,3,5,'present',1,'2026-06-18 07:00:00','203.161.156.197','31e010679d4493605b9310596c361fe2364c91199567093029b8cc9143ced738',26.36,NULL,'2026-06-18 03:04:11','2026-06-18 03:04:11',NULL),(30,3,6,'present',1,'2026-06-18 07:00:00','221.129.251.191','1fe6867d2e6ebfbecfa0bfe224b23faed6a900e3f95dad3ff3332b00336eabc8',42.89,NULL,'2026-06-18 03:04:12','2026-06-18 03:04:12',NULL),(31,3,7,'absent',1,NULL,'1.53.186.188','9c2ae48b0b9ca8a43b4f53bf496cc651f73c86526f7124fdd301b8bfc4b7eb47',35.03,NULL,'2026-06-18 03:04:12','2026-06-18 03:04:12',NULL),(32,3,8,'late',1,'2026-06-18 07:20:00','159.152.232.74','0d6251fb97c4940a185167d3e72e7530dc1aff18a64fa5b20f5f1284e92f45ed',91.13,NULL,'2026-06-18 03:04:12','2026-06-18 03:04:12',NULL),(33,3,9,'present',1,'2026-06-18 07:00:00','224.101.148.89','1384afa6fe4761876f1ecdf4569a599f0b48c0c73c3baa06a04b851d45ba5501',9.92,NULL,'2026-06-18 03:04:12','2026-06-18 03:04:12',NULL),(34,3,10,'present',1,'2026-06-18 07:00:00','2.131.12.182','57e66cebef30360fdc7674282232edde89bd3e7fe90c0f1d1b502c31d17fa377',118.70,NULL,'2026-06-18 03:04:12','2026-06-18 03:04:12',NULL),(35,3,11,'present',1,'2026-06-18 07:00:00','199.174.58.10','cf6c63b080418f225cd5a1b1c57f3f2e7f26c2edc41e948dd7c00a0e46ee8823',4.98,NULL,'2026-06-18 03:04:12','2026-06-18 03:04:12',NULL),(36,3,12,'present',1,'2026-06-18 07:00:00','20.195.132.58','00a805960302ae4eb90bd6551e71c1b49ddf28f23f7992e990cfd8bad354cdd4',5.11,NULL,'2026-06-18 03:04:12','2026-06-18 03:04:12',NULL);
/*!40000 ALTER TABLE `attendance_records` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `attendance_summaries`
--

DROP TABLE IF EXISTS `attendance_summaries`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `attendance_summaries` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `class_id` bigint unsigned NOT NULL,
  `class_member_id` bigint unsigned NOT NULL,
  `total_present` int unsigned NOT NULL DEFAULT '0',
  `total_late` int unsigned NOT NULL DEFAULT '0',
  `total_absent` int unsigned NOT NULL DEFAULT '0',
  `total_excused` int unsigned NOT NULL DEFAULT '0',
  `is_banned_from_exam` tinyint(1) NOT NULL DEFAULT '0',
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `attendance_summaries_class_id_class_member_id_unique` (`class_id`,`class_member_id`),
  KEY `attendance_summaries_class_member_id_foreign` (`class_member_id`),
  CONSTRAINT `attendance_summaries_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `attendance_summaries_class_member_id_foreign` FOREIGN KEY (`class_member_id`) REFERENCES `class_members` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attendance_summaries`
--

LOCK TABLES `attendance_summaries` WRITE;
/*!40000 ALTER TABLE `attendance_summaries` DISABLE KEYS */;
INSERT INTO `attendance_summaries` VALUES (1,1,1,1,1,1,0,0,'2026-06-18 03:04:12'),(2,1,2,2,1,0,0,0,'2026-06-18 03:04:12'),(3,1,3,3,0,0,0,0,'2026-06-18 03:04:12'),(4,1,4,3,0,0,0,0,'2026-06-18 03:04:12'),(5,1,5,2,0,0,1,0,'2026-06-18 03:04:12'),(6,1,6,3,0,0,0,0,'2026-06-18 03:04:12'),(7,1,7,2,0,1,0,0,'2026-06-18 03:04:12'),(8,1,8,1,1,1,0,0,'2026-06-18 03:04:12'),(9,1,9,1,1,1,0,0,'2026-06-18 03:04:12'),(10,1,10,2,1,0,0,0,'2026-06-18 03:04:12'),(11,1,11,3,0,0,0,0,'2026-06-18 03:04:12'),(12,1,12,3,0,0,0,0,'2026-06-18 03:04:12');
/*!40000 ALTER TABLE `attendance_summaries` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `class_id` bigint unsigned DEFAULT NULL,
  `action` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `table_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `row_id` bigint unsigned DEFAULT NULL,
  `old_values` json DEFAULT NULL,
  `new_values` json DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `audit_logs_user_id_created_at_index` (`user_id`,`created_at`),
  KEY `audit_logs_class_id_created_at_index` (`class_id`,`created_at`),
  KEY `audit_logs_table_name_row_id_index` (`table_name`,`row_id`),
  CONSTRAINT `audit_logs_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE SET NULL,
  CONSTRAINT `audit_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
INSERT INTO `audit_logs` VALUES (1,23,1,'LOGIN_SUCCESS','attendance_records',415,'{\"status\": \"absent\"}','{\"status\": \"present\"}','18.105.27.60','Mozilla/5.0 (compatible; MSIE 11.0; Windows NT 6.0; Trident/5.1)','2026-06-18 03:04:12'),(2,23,1,'ATTENDANCE_UPDATED','attendance_records',708,'{\"status\": \"absent\"}','{\"status\": \"present\"}','93.52.28.247','Mozilla/5.0 (compatible; MSIE 10.0; Windows CE; Trident/3.1)','2026-06-18 03:04:12'),(3,23,1,'SUBSCRIPTION_UPGRADED','attendance_records',579,'{\"status\": \"absent\"}','{\"status\": \"present\"}','171.131.118.21','Mozilla/5.0 (Windows; U; Windows NT 6.0) AppleWebKit/531.12.6 (KHTML, like Gecko) Version/4.1 Safari/531.12.6','2026-06-18 03:04:12'),(4,23,1,'LOGIN_SUCCESS','attendance_records',10,'{\"status\": \"absent\"}','{\"status\": \"present\"}','124.43.231.166','Mozilla/5.0 (Windows 98; Win 9x 4.90; en-US; rv:1.9.2.20) Gecko/20181027 Firefox/36.0','2026-06-18 03:04:12'),(5,22,NULL,'DEMO_DATA_SEEDED',NULL,NULL,NULL,'{\"course_class_id\": 1}','243.41.3.15','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/535.2 (KHTML, like Gecko) Chrome/90.0.4447.88 Safari/535.2 EdgA/90.01130.27','2026-06-18 03:04:12');
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `check_in_scans`
--

DROP TABLE IF EXISTS `check_in_scans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `check_in_scans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `class_session_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `student_code_attempt` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `scan_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload_signature` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_valid` tinyint(1) NOT NULL DEFAULT '1',
  `fail_reason` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `device_fingerprint` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `scanned_at` timestamp NOT NULL,
  PRIMARY KEY (`id`),
  KEY `check_in_scans_class_session_id_scanned_at_index` (`class_session_id`,`scanned_at`),
  KEY `check_in_scans_user_id_scanned_at_index` (`user_id`,`scanned_at`),
  KEY `check_in_scans_device_fingerprint_scanned_at_index` (`device_fingerprint`,`scanned_at`),
  CONSTRAINT `check_in_scans_class_session_id_foreign` FOREIGN KEY (`class_session_id`) REFERENCES `class_sessions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `check_in_scans_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `check_in_scans`
--

LOCK TABLES `check_in_scans` WRITE;
/*!40000 ALTER TABLE `check_in_scans` DISABLE KEYS */;
INSERT INTO `check_in_scans` VALUES (1,1,24,'SV000001','qr','7d812458e3c569d30e0d59a74cb6fa895b9c1f92549a39b299b77bf1f7c0d4d2',1,NULL,'207.203.254.218','22f865d791a43751d93dcb95e862ae360afaf46b0e75c955250307dc70157aab','2026-06-04 07:00:00'),(2,1,25,'SV000002','gps','63703b080a800dd5846f68939b8875dc0fb0225e26dfaed0bdcdf83b7b8fdf6f',1,NULL,'83.248.253.220','f26537a0c03266ee795b4fbec65dd8a446aa452bfeabc1f4ae8404e980409e8d','2026-06-04 07:01:00'),(3,1,26,'SV000003','gps','9ddbed087fc5d4705a36888d6cb540d0aa2a09361324035e3b2c1508cc667a12',1,NULL,'76.150.24.196','827a488b7d821dfb1dd7b5c9f3d9ee49eac169e71a9deeca1cac37b40aaa2232','2026-06-04 07:02:00'),(4,1,27,'SV000004','link','ce6ca746e33c33a842f3167de74a216b6eaa7f943f8fbb238ec8fd06997f2b66',1,NULL,'3.123.2.172','b9ad87782e43c696ab6d7e5a1d65101122022e53987571fe938f2a66da10b869','2026-06-04 07:03:00'),(5,1,28,'SV000005','gps','31a26734ca5a664f9404926a539ad83daa49ec3a2aa1f56e6100f8fe21cf69d7',1,NULL,'59.65.214.205','ab8d8265a16b9a9948264bccfff388770dc0697a662c7a2c91307b54f0d7af2f','2026-06-04 07:04:00'),(6,2,24,'SV000001','link','fe7b8e3e9df45c9a87f0b7b738277be6ed4102565c4ded44b9ee13b468569a06',1,NULL,'96.34.84.37','0b60355df5e8058dcc4ab7ea0a073ff8497608195065a48919b5db2452434960','2026-06-11 07:00:00'),(7,2,25,'SV000002','qr','aaeebe3c41150c243f481c2d2ff72bbc946acdae9e21833413dd6df99b02abfc',1,NULL,'92.5.219.118','dc37c2ddd6d2735c873d1ec003c1e3d8cdb2f089a15ef232622cfb8d5e76c383','2026-06-11 07:01:00'),(8,2,26,'SV000003','qr','a6b3709dad0f2af2c4dee361ccf0e1d21cae1a3d9b1b2ecdaf088860176a8d55',1,NULL,'113.84.197.176','93f4635e5d03f742a44500777238c3211287d413157be674485db772c1b65e58','2026-06-11 07:02:00'),(9,2,27,'SV000004','gps','f354d498c9c4591b2b2bec6cfc2d1ac9bcacf8806080f899a21f5617cfea9b41',1,NULL,'90.171.185.224','bd5117cd24f169c3c370006b28e1f1214f0ee23cbf48645bf558f607cb3c93ac','2026-06-11 07:03:00'),(10,2,28,'SV000005','link','a3bbb031fd64107cd1f5f53b55e9d9ea50c02cfede654d94fc128e7a179cb132',1,NULL,'206.132.145.216','d8d3e232d972c24280d8547579ab2ca75c911a68c7d2534a16a999c06f54ed31','2026-06-11 07:04:00'),(11,3,24,'SV000001','gps','1058ec5667204a51005a85558e03600cfb8765ae6bccee3b9e85218104f52540',1,NULL,'77.41.158.89','4d68c2ac8621f3406c905e15598d5000dd4254004fec7c0c7ab6b4572f38d94b','2026-06-18 07:00:00'),(12,3,25,'SV000002','link','302f10300d52ef70cf24d3b6663187fbc2536c501cdefc79c4908d57ddf227b2',1,NULL,'212.227.99.61','086442ff3bc0d3d52b20d56d84fb7d2c04cc17a622f9b047c1cc3a61d2d90c2c','2026-06-18 07:01:00'),(13,3,26,'SV000003','gps','3a88dfaa695cae7bf34407e00b15c4587498af51c3a4fc888b74753f33a568df',1,NULL,'146.55.31.23','e10a869d625b01fc6d811c140dbd0b8fae92537a5e579acc6da2c8d7b1448332','2026-06-18 07:02:00'),(14,3,27,'SV000004','qr','069bc50bab70969fd963221d975bebc3f46d7726554d59e4a290d3d1de470378',1,NULL,'172.143.217.155','6113e59b492eec22f0ea99725cd2a16c2cbf2eb6c441e222c3d472d756d9be89','2026-06-18 07:03:00'),(15,3,28,'SV000005','link','4a941918d68c7d42b4888f88da2c2ad1b5aaf7b2527ca6a0d9bd93dcfbfca4ca',1,NULL,'66.174.64.34','c8cb2aad85530ab56fdf65a083a3b6c4fb46ac4b2b3d9fa80c20bac3c057d29b','2026-06-18 07:04:00');
/*!40000 ALTER TABLE `check_in_scans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `class_join_requests`
--

DROP TABLE IF EXISTS `class_join_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `class_join_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `class_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `student_code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `class_join_requests_user_id_foreign` (`user_id`),
  KEY `class_join_requests_class_id_status_index` (`class_id`,`status`),
  KEY `class_join_requests_status_index` (`status`),
  CONSTRAINT `class_join_requests_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `class_join_requests_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `class_join_requests`
--

LOCK TABLES `class_join_requests` WRITE;
/*!40000 ALTER TABLE `class_join_requests` DISABLE KEYS */;
INSERT INTO `class_join_requests` VALUES (1,1,36,'SV999999','Sinh viên chờ duyệt','pending','2026-06-18 03:04:11','2026-06-18 03:04:11');
/*!40000 ALTER TABLE `class_join_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `class_members`
--

DROP TABLE IF EXISTS `class_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `class_members` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `class_id` bigint unsigned NOT NULL,
  `student_code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `class_members_class_id_student_code_unique` (`class_id`,`student_code`),
  UNIQUE KEY `class_members_class_id_user_id_unique` (`class_id`,`user_id`),
  KEY `class_members_user_id_index` (`user_id`),
  KEY `class_members_status_index` (`status`),
  CONSTRAINT `class_members_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `class_members_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `class_members`
--

LOCK TABLES `class_members` WRITE;
/*!40000 ALTER TABLE `class_members` DISABLE KEYS */;
INSERT INTO `class_members` VALUES (1,1,'SV000001','Lục Quân',24,'active','2026-06-18 03:04:11','2026-06-18 03:04:11',NULL),(2,1,'SV000002','Vương Mai Sinh',25,'active','2026-06-18 03:04:11','2026-06-18 03:04:11',NULL),(3,1,'SV000003','Phó Lập Đoàn',26,'active','2026-06-18 03:04:11','2026-06-18 03:04:11',NULL),(4,1,'SV000004','Trác Văn',27,'active','2026-06-18 03:04:11','2026-06-18 03:04:11',NULL),(5,1,'SV000005','Lều Đài',28,'active','2026-06-18 03:04:11','2026-06-18 03:04:11',NULL),(6,1,'SV000006','Cụ. Ngô Nhung',29,'active','2026-06-18 03:04:11','2026-06-18 03:04:11',NULL),(7,1,'SV000007','Em. Cát Minh',30,'active','2026-06-18 03:04:11','2026-06-18 03:04:11',NULL),(8,1,'SV000008','Chú. Bàng Trí',31,'active','2026-06-18 03:04:11','2026-06-18 03:04:11',NULL),(9,1,'SV000009','Em. Bạc Khánh Lộc',32,'active','2026-06-18 03:04:11','2026-06-18 03:04:11',NULL),(10,1,'SV000010','Bà. Vũ Miên',33,'active','2026-06-18 03:04:11','2026-06-18 03:04:11',NULL),(11,1,'SV000011','Vừ Khiêm',34,'active','2026-06-18 03:04:11','2026-06-18 03:04:11',NULL),(12,1,'SV000012','Bá Uyên',35,'active','2026-06-18 03:04:11','2026-06-18 03:04:11',NULL);
/*!40000 ALTER TABLE `class_members` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `class_sessions`
--

DROP TABLE IF EXISTS `class_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `class_sessions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `class_id` bigint unsigned NOT NULL,
  `created_by` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` date NOT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `qr_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `token_expires_at` timestamp NULL DEFAULT NULL,
  `gps_latitude` decimal(10,8) DEFAULT NULL,
  `gps_longitude` decimal(11,8) DEFAULT NULL,
  `gps_radius` int unsigned DEFAULT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `class_sessions_qr_token_unique` (`qr_token`),
  KEY `class_sessions_created_by_foreign` (`created_by`),
  KEY `class_sessions_class_id_date_index` (`class_id`,`date`),
  KEY `class_sessions_class_id_created_at_index` (`class_id`,`created_at`),
  KEY `class_sessions_qr_token_token_expires_at_index` (`qr_token`,`token_expires_at`),
  CONSTRAINT `class_sessions_class_id_foreign` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `class_sessions_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `class_sessions`
--

LOCK TABLES `class_sessions` WRITE;
/*!40000 ALTER TABLE `class_sessions` DISABLE KEYS */;
INSERT INTO `class_sessions` VALUES (1,1,23,'Buổi 1 - Tổng quan Laravel','2026-06-04','07:00:00','09:30:00','24b56e30-bc05-4ce7-b205-0ed729143adc','2026-06-18 03:03:11',10.76262200,106.66017200,100,'closed','2026-06-18 03:04:11','2026-06-18 03:04:11',NULL),(2,1,23,'Buổi 2 - Eloquent ORM','2026-06-11','07:00:00','09:30:00','e292f588-fdf9-44ff-bac8-7752ded2d4a7','2026-06-18 03:03:11',10.76262200,106.66017200,100,'closed','2026-06-18 03:04:11','2026-06-18 03:04:11',NULL),(3,1,23,'Buổi 3 - Livewire','2026-06-18','07:00:00','09:30:00','4201ab5a-b25c-40b5-a85e-baafe22f4508','2026-06-18 03:19:11',10.76262200,106.66017200,100,'active','2026-06-18 03:04:11','2026-06-18 03:04:11',NULL);
/*!40000 ALTER TABLE `class_sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `classes`
--

DROP TABLE IF EXISTS `classes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `classes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `owner_user_id` bigint unsigned NOT NULL,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `subject_code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `semester` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `require_approval` tinyint(1) NOT NULL DEFAULT '0',
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `total_sessions` int unsigned NOT NULL DEFAULT '15',
  `lessons_per_session` int unsigned NOT NULL DEFAULT '3',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `classes_code_unique` (`code`),
  KEY `classes_owner_user_id_foreign` (`owner_user_id`),
  KEY `classes_status_index` (`status`),
  CONSTRAINT `classes_owner_user_id_foreign` FOREIGN KEY (`owner_user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `classes`
--

LOCK TABLES `classes` WRITE;
/*!40000 ALTER TABLE `classes` DISABLE KEYS */;
INSERT INTO `classes` VALUES (1,23,'WEB-2026-01','Lập trình Web nâng cao','Ipsam cupiditate reiciendis alias ipsa dicta repellat rerum ea.','WEB401','HK2 2025-2026',1,'active',15,3,'2026-06-18 03:04:11','2026-06-18 03:04:11',NULL),(2,23,'DB-2025-01','Cơ sở dữ liệu','Totam adipisci dolores magnam vel dolore alias at.','DB301','HK1 2025-2026',1,'archived',15,3,'2026-06-18 03:04:11','2026-06-18 03:04:11',NULL);
/*!40000 ALTER TABLE `classes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `leave_requests`
--

DROP TABLE IF EXISTS `leave_requests`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `leave_requests` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `class_member_id` bigint unsigned NOT NULL,
  `class_session_id` bigint unsigned NOT NULL,
  `reason` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `proof_image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `rejected_reason` text COLLATE utf8mb4_unicode_ci,
  `reviewed_by` bigint unsigned DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `leave_requests_class_member_id_class_session_id_unique` (`class_member_id`,`class_session_id`),
  KEY `leave_requests_reviewed_by_foreign` (`reviewed_by`),
  KEY `leave_requests_class_session_id_status_index` (`class_session_id`,`status`),
  KEY `leave_requests_status_index` (`status`),
  CONSTRAINT `leave_requests_class_member_id_foreign` FOREIGN KEY (`class_member_id`) REFERENCES `class_members` (`id`) ON DELETE CASCADE,
  CONSTRAINT `leave_requests_class_session_id_foreign` FOREIGN KEY (`class_session_id`) REFERENCES `class_sessions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `leave_requests_reviewed_by_foreign` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `leave_requests`
--

LOCK TABLES `leave_requests` WRITE;
/*!40000 ALTER TABLE `leave_requests` DISABLE KEYS */;
INSERT INTO `leave_requests` VALUES (1,5,2,'Nghỉ ốm có xác nhận y tế.',NULL,'approved',NULL,23,'2026-06-18 03:04:12','2026-06-18 03:04:12');
/*!40000 ALTER TABLE `leave_requests` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_06_04_121801_create_permission_tables',1),(5,'2026_06_18_000100_extend_users_table_for_attendance',2),(6,'2026_06_18_000200_create_classes_table',2),(7,'2026_06_18_000300_create_class_membership_tables',2),(8,'2026_06_18_000400_create_attendance_tables',2),(9,'2026_06_18_000500_create_audit_and_scan_tables',2),(10,'2026_06_18_000600_create_billing_tables',2),(11,'2026_06_18_000700_create_system_utility_tables',2);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_permissions`
--

DROP TABLE IF EXISTS `model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_permissions`
--

LOCK TABLES `model_has_permissions` WRITE;
/*!40000 ALTER TABLE `model_has_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `model_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_roles`
--

DROP TABLE IF EXISTS `model_has_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_roles` (
  `role_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_roles`
--

LOCK TABLES `model_has_roles` WRITE;
/*!40000 ALTER TABLE `model_has_roles` DISABLE KEYS */;
/*!40000 ALTER TABLE `model_has_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `notifiable_id` bigint unsigned NOT NULL,
  `data` json NOT NULL,
  `read_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `notifications_notifiable_type_notifiable_id_index` (`notifiable_type`,`notifiable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` VALUES ('0c84f5b3-eeb4-4bc8-a3bc-76683b7eb4c7','App\\Notifications\\AttendanceReminder','App\\Models\\User',24,'{\"url\": \"/classes\", \"title\": \"Nhắc lịch điểm danh\", \"message\": \"Officiis optio ut perferendis illum adipisci.\"}',NULL,'2026-06-18 03:04:12','2026-06-18 03:04:12'),('2589a2fe-e064-45b7-861c-44416ca65bbf','App\\Notifications\\AttendanceReminder','App\\Models\\User',24,'{\"url\": \"/classes\", \"title\": \"Nhắc lịch điểm danh\", \"message\": \"Perspiciatis enim sint suscipit ex quis et rem.\"}',NULL,'2026-06-18 03:04:12','2026-06-18 03:04:12'),('72046962-8817-4923-91bb-9d5c73d737c7','App\\Notifications\\AttendanceReminder','App\\Models\\User',23,'{\"url\": \"/classes\", \"title\": \"Lớp học đã sẵn sàng\", \"message\": \"Dữ liệu điểm danh mẫu đã được khởi tạo.\"}',NULL,'2026-06-18 03:04:12','2026-06-18 03:04:12'),('cb95bab7-aeaa-49e9-8999-96f0a30b5625','App\\Notifications\\AttendanceReminder','App\\Models\\User',24,'{\"url\": \"/classes\", \"title\": \"Nhắc lịch điểm danh\", \"message\": \"Cum impedit cum vel nihil.\"}',NULL,'2026-06-18 03:04:12','2026-06-18 03:04:12');
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `plans`
--

DROP TABLE IF EXISTS `plans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `plans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `max_classes` int unsigned NOT NULL,
  `can_export_excel` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `plans_code_unique` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `plans`
--

LOCK TABLES `plans` WRITE;
/*!40000 ALTER TABLE `plans` DISABLE KEYS */;
INSERT INTO `plans` VALUES (1,'FREE','Miễn phí',0.00,2,0,'2026-06-18 03:04:10'),(2,'PRO','Chuyên nghiệp',199000.00,30,1,'2026-06-18 03:04:10');
/*!40000 ALTER TABLE `plans` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_has_permissions`
--

DROP TABLE IF EXISTS `role_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_has_permissions`
--

LOCK TABLES `role_has_permissions` WRITE;
/*!40000 ALTER TABLE `role_has_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `role_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subscriptions`
--

DROP TABLE IF EXISTS `subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `subscriptions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `plan_id` bigint unsigned NOT NULL,
  `start_date` timestamp NOT NULL,
  `end_date` timestamp NULL DEFAULT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `subscriptions_plan_id_foreign` (`plan_id`),
  KEY `subscriptions_user_id_status_index` (`user_id`,`status`),
  KEY `subscriptions_status_index` (`status`),
  CONSTRAINT `subscriptions_plan_id_foreign` FOREIGN KEY (`plan_id`) REFERENCES `plans` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `subscriptions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subscriptions`
--

LOCK TABLES `subscriptions` WRITE;
/*!40000 ALTER TABLE `subscriptions` DISABLE KEYS */;
INSERT INTO `subscriptions` VALUES (1,23,2,'2026-06-18 03:04:12','2027-06-18 03:04:12','active','2026-06-18 03:04:12','2026-06-18 03:04:12');
/*!40000 ALTER TABLE `subscriptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `transactions`
--

DROP TABLE IF EXISTS `transactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `transactions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `transaction_code` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `partner_reference_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `transactions_transaction_code_unique` (`transaction_code`),
  KEY `transactions_user_id_created_at_index` (`user_id`,`created_at`),
  KEY `transactions_status_index` (`status`),
  CONSTRAINT `transactions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `transactions`
--

LOCK TABLES `transactions` WRITE;
/*!40000 ALTER TABLE `transactions` DISABLE KEYS */;
INSERT INTO `transactions` VALUES (1,23,199000.00,'payos','TXN-KTVEFU0NQOZB','536bd12a-6e1e-48b2-85db-b698cc50bb71','success','2026-06-18 03:04:12');
/*!40000 ALTER TABLE `transactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_devices`
--

DROP TABLE IF EXISTS `user_devices`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_devices` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `fcm_token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `device_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_active_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_devices_fcm_token_unique` (`fcm_token`),
  KEY `user_devices_user_id_last_active_at_index` (`user_id`,`last_active_at`),
  CONSTRAINT `user_devices_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_devices`
--

LOCK TABLES `user_devices` WRITE;
/*!40000 ALTER TABLE `user_devices` DISABLE KEYS */;
INSERT INTO `user_devices` VALUES (1,23,'SNU5vC1ONigBsY4sHXXPUmekW1gF0zLt9vgS6CEqF1qicizgd4P8o3z37ACSdyPx4pSeAsDRs6BrtIP7n6261eKc7jSFn9p9iifd9gUeRagz6fj4kZbw7fQxoVte4i1BHN1HUONL8JEGbqjG57Sx5ZKLM0guD7WLAV7uD30E13edeVbwKJfJ','Android App','2026-06-18 03:04:12','2026-06-18 03:04:12'),(2,24,'oMzymZ8JkA4O4xz2TKH2CZeOOLIXvIMRsKtAdjIoIkUMvHRFXRZiXIj2eJLDmikggeItrTlpCTWKUwgjv5IsRSEXfbKk8BEhyX5flfIa8eglxHSNBynu8btbVQ24QI7NCs174nHfZHnSQNnoyXPhXuvnTby7aTjWQgOhf3uxQnc6lD3vYNW4','Android App','2026-06-18 03:04:12','2026-06-18 03:04:12'),(3,25,'1kXyyh8ZaOKDOMnkgFR47BCDnZwORA9fhBeK1XDmJCTa5XIYcU4Yw1JohNa6pV3rqVE6p8DN7eG7QJTCHlWNo2w1duICcPEh7cD4Uts5cwlDFkBtQJbpy0btiVkQTQGmTHWgUBF3kxNav01JhAyG8tjafDCBRjfkYU2pg48CdJbKKhnxS6cI','Safari on iPhone','2026-06-18 03:04:12','2026-06-18 03:04:12'),(4,26,'Aw0adAJ21AxKC6CEsTc1tDMsjwi6IzEM28YkxHao4muQkTGEQlQgxAGWzyQvjqoOVOOVSQlduGDkgm0ae49Bf89lyerBwbpfl17Y4Zn5jDYNHoBh60Dp692oB8yGhd4SScq9EOuBwFrgjmyS9qbAek21883rTd7s2MhnEsgOaFglJ7ePsRSx','Android App','2026-06-18 03:04:12','2026-06-18 03:04:12');
/*!40000 ALTER TABLE `user_devices` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `is_admin` tinyint(1) NOT NULL DEFAULT '0',
  `google_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avatar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_google_id_unique` (`google_id`),
  KEY `users_code_index` (`code`),
  KEY `users_status_index` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (21,0,NULL,NULL,'hieu','minhhieut947@gmail.com',NULL,'$2y$12$C13ttiAdRCR/Lwrh..vXj.a.JLQa14E//gogCvD2yemaST80CqBAe',NULL,'active',NULL,'2026-06-17 18:26:17','2026-06-17 18:26:17',NULL),(22,1,NULL,'ADMIN001','Quản trị hệ thống','admin@example.com','2026-06-18 03:04:10','$2y$12$jNvFWGcE3lr47vpWlVVrS.mV6ukS7Bq6XpUtS9mEyc61RWH8jlvLa',NULL,'active','t6W75NIZqS','2026-06-18 03:04:11','2026-06-18 03:04:11',NULL),(23,0,NULL,'GV001','Nguyễn Minh Giảng Viên','teacher@example.com','2026-06-18 03:04:11','$2y$12$jNvFWGcE3lr47vpWlVVrS.mV6ukS7Bq6XpUtS9mEyc61RWH8jlvLa',NULL,'active','A0pI9H369K','2026-06-18 03:04:11','2026-06-18 03:04:11',NULL),(24,0,NULL,'SV000001','Lục Quân','student1@example.com','2026-06-18 03:04:11','$2y$12$jNvFWGcE3lr47vpWlVVrS.mV6ukS7Bq6XpUtS9mEyc61RWH8jlvLa',NULL,'active','VxDKmRyxQ1','2026-06-18 03:04:11','2026-06-18 03:04:11',NULL),(25,0,NULL,'SV000002','Vương Mai Sinh','student2@example.com','2026-06-18 03:04:11','$2y$12$jNvFWGcE3lr47vpWlVVrS.mV6ukS7Bq6XpUtS9mEyc61RWH8jlvLa',NULL,'active','vpTQAXarRm','2026-06-18 03:04:11','2026-06-18 03:04:11',NULL),(26,0,NULL,'SV000003','Phó Lập Đoàn','student3@example.com','2026-06-18 03:04:11','$2y$12$jNvFWGcE3lr47vpWlVVrS.mV6ukS7Bq6XpUtS9mEyc61RWH8jlvLa',NULL,'active','RXIGulLVNR','2026-06-18 03:04:11','2026-06-18 03:04:11',NULL),(27,0,NULL,'SV000004','Trác Văn','student4@example.com','2026-06-18 03:04:11','$2y$12$jNvFWGcE3lr47vpWlVVrS.mV6ukS7Bq6XpUtS9mEyc61RWH8jlvLa',NULL,'active','DAWL39kqwl','2026-06-18 03:04:11','2026-06-18 03:04:11',NULL),(28,0,NULL,'SV000005','Lều Đài','student5@example.com','2026-06-18 03:04:11','$2y$12$jNvFWGcE3lr47vpWlVVrS.mV6ukS7Bq6XpUtS9mEyc61RWH8jlvLa',NULL,'active','7YKc36IChZ','2026-06-18 03:04:11','2026-06-18 03:04:11',NULL),(29,0,NULL,'SV000006','Cụ. Ngô Nhung','student6@example.com','2026-06-18 03:04:11','$2y$12$jNvFWGcE3lr47vpWlVVrS.mV6ukS7Bq6XpUtS9mEyc61RWH8jlvLa',NULL,'active','Fj6moImwnS','2026-06-18 03:04:11','2026-06-18 03:04:11',NULL),(30,0,NULL,'SV000007','Em. Cát Minh','student7@example.com','2026-06-18 03:04:11','$2y$12$jNvFWGcE3lr47vpWlVVrS.mV6ukS7Bq6XpUtS9mEyc61RWH8jlvLa',NULL,'active','e4b71hvyOT','2026-06-18 03:04:11','2026-06-18 03:04:11',NULL),(31,0,NULL,'SV000008','Chú. Bàng Trí','student8@example.com','2026-06-18 03:04:11','$2y$12$jNvFWGcE3lr47vpWlVVrS.mV6ukS7Bq6XpUtS9mEyc61RWH8jlvLa',NULL,'active','D2wKTg2vQd','2026-06-18 03:04:11','2026-06-18 03:04:11',NULL),(32,0,NULL,'SV000009','Em. Bạc Khánh Lộc','student9@example.com','2026-06-18 03:04:11','$2y$12$jNvFWGcE3lr47vpWlVVrS.mV6ukS7Bq6XpUtS9mEyc61RWH8jlvLa',NULL,'active','sOZ0PwnZYA','2026-06-18 03:04:11','2026-06-18 03:04:11',NULL),(33,0,NULL,'SV000010','Bà. Vũ Miên','student10@example.com','2026-06-18 03:04:11','$2y$12$jNvFWGcE3lr47vpWlVVrS.mV6ukS7Bq6XpUtS9mEyc61RWH8jlvLa',NULL,'active','ein1iSg9oc','2026-06-18 03:04:11','2026-06-18 03:04:11',NULL),(34,0,NULL,'SV000011','Vừ Khiêm','student11@example.com','2026-06-18 03:04:11','$2y$12$jNvFWGcE3lr47vpWlVVrS.mV6ukS7Bq6XpUtS9mEyc61RWH8jlvLa',NULL,'active','bCZxiAFTEM','2026-06-18 03:04:11','2026-06-18 03:04:11',NULL),(35,0,NULL,'SV000012','Bá Uyên','student12@example.com','2026-06-18 03:04:11','$2y$12$jNvFWGcE3lr47vpWlVVrS.mV6ukS7Bq6XpUtS9mEyc61RWH8jlvLa',NULL,'active','0RTrx9yoHH','2026-06-18 03:04:11','2026-06-18 03:04:11',NULL),(36,0,NULL,'SV999999','Sinh viên chờ duyệt','applicant@example.com','2026-06-18 03:04:11','$2y$12$jNvFWGcE3lr47vpWlVVrS.mV6ukS7Bq6XpUtS9mEyc61RWH8jlvLa',NULL,'active','u1rG0BNtHX','2026-06-18 03:04:11','2026-06-18 03:04:11',NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'db_quan_ly_diem_danh'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-06-18  3:16:02
