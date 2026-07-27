/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19-11.8.6-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: EVisa
-- ------------------------------------------------------
-- Server version	11.8.6-MariaDB-5ubuntu0.1 from Ubuntu

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;

--
-- Table structure for table `PassApp`
--

DROP TABLE IF EXISTS `PassApp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `PassApp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `application_id` varchar(36) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `dob` date NOT NULL,
  `validated_by_officer` tinyint(1) DEFAULT 0,
  `place_of_birth` varchar(255) NOT NULL,
  `nationality` varchar(255) NOT NULL,
  `genre` enum('Male','Female') NOT NULL,
  `marital_status` enum('Single','Maried','Divorced') NOT NULL,
  `occupation` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(255) NOT NULL,
  `Emergency_contact` text NOT NULL,
  `Passport_type` enum('ordinary','diplomatic','service') NOT NULL,
  `duration` int(11) NOT NULL,
  `status` enum('submitted','under review','issues found','documents verified','background checks complete','appointment scheduled','approved','rejected') DEFAULT 'submitted',
  `appointment_date` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `application_id` (`application_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `PassApp`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `PassApp` WRITE;
/*!40000 ALTER TABLE `PassApp` DISABLE KEYS */;
/*!40000 ALTER TABLE `PassApp` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `VisaApp`
--

DROP TABLE IF EXISTS `VisaApp`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `VisaApp` (
  `application_id` varchar(32) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `passport_number` varchar(50) NOT NULL,
  `dob` date NOT NULL,
  `nationality` varchar(50) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(100) NOT NULL,
  `address` text NOT NULL,
  `visa_type` varchar(50) NOT NULL,
  `duration` varchar(20) NOT NULL,
  `photo_path` varchar(255) NOT NULL,
  `validated_by_officer` tinyint(1) DEFAULT 0,
  `passport_scan_path` varchar(255) NOT NULL,
  `support_doc_path` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`application_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `VisaApp`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `VisaApp` WRITE;
/*!40000 ALTER TABLE `VisaApp` DISABLE KEYS */;
INSERT INTO `VisaApp` VALUES
('2ffbbf07e2947e83c169c31115fdfee1','jospin','kas','OP1025820','2024-08-01','congolese','0704247032','jospin8kas@icloud.com','masai lodge road 25','student','2years','/var/www/html/EvisaProject/Backend/Uploads/2ffbbf07e2947e83c169c31115fdfee1_photo.jpeg',0,'/var/www/html/EvisaProject/Backend/Uploads/2ffbbf07e2947e83c169c31115fdfee1_passport.pdf','/var/www/html/EvisaProject/Backend/Uploads/2ffbbf07e2947e83c169c31115fdfee1_support.pdf');
/*!40000 ALTER TABLE `VisaApp` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `documents`
--

DROP TABLE IF EXISTS `documents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `documents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `application_id` varchar(36) NOT NULL,
  `application_type` enum('visa','passport') NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `status` enum('pending','valid','non_conforming') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `uploaded_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `documents`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `documents` WRITE;
/*!40000 ALTER TABLE `documents` DISABLE KEYS */;
/*!40000 ALTER TABLE `documents` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `id_number` varchar(50) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `dob` date NOT NULL,
  `email` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `status` enum('active','inactive','pending') DEFAULT 'pending',
  `role` enum('applicant','officer','admin') NOT NULL DEFAULT 'applicant',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `id_number` (`id_number`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES
(21,'96325874125','jospin','kas','0704247032','2025-08-01','jos@pin.com','pino','$2y$12$2e46woIEAlgYyzNSRznYy.CjIQHGpnXUJlnShypanOmurA3VAfEoO','pending','admin','2025-08-27 18:11:18','2025-08-27 18:11:18'),
(22,'0704247032258','jospin','kasereka','0704247032','2025-08-01','pino@pino.com','com','$2y$12$ccl20g5T8WcB1mOV5xtnluOv.eKA/M/Eejd7.PMySt5benFtMdZQK','active','admin','2025-08-27 18:19:29','2025-08-27 18:19:29'),
(23,'07042470321','jospin','kas','0704247032','2025-08-09','mobateli@gmail.com','masai lodge road 25','$2y$12$5Ron4w35guaSEZuDWasYI.t472RWvWp3wG.eGuRNwb1f5Axliajdm','pending','applicant','2025-08-28 20:31:09','2025-08-28 20:31:09'),
(24,'100003254','officer','officer','20000321456','2025-08-01','officer@officer.com','masai lodge road 25','$2y$12$m9n02MZWBS0UasjVc/Z9IuAdiU4rdp6FYLpaR6f7ylmDansBHv7r6','active','officer','2025-08-28 22:49:07','2025-08-28 22:49:07'),
(26,'1023654789','jospin','kas','0704247032','2025-08-08','12jospin8kas@icloud.com','masai lodge road 25','$2y$12$EKBALGwGvTcIMlgLqrPnD.mp7kdWbCe4Y.NMHCkWyAX7yQ/zXb0Z6','pending','applicant','2025-08-29 00:17:21','2025-08-29 00:17:21'),
(27,'0123654789','jospin','kas','07042470322','2025-05-14','jos@jos.com','cuea','$2y$12$0UyHBFpwWZwGY0j0FqMmJuK7eca/1NO8y5bRGikEDUdLoazmOf0WG','active','applicant','2025-08-29 07:14:01','2025-08-29 07:14:01'),
(28,'0147852369','jospin','kas','0704247032','2025-08-01','jospin8kas@icloud1.com','masai lodge road 25','$2y$12$OGMAPDNt2eCJG.oG..qvD.cojxLmmiWt6V8bvT7/GSmL64xNmdd86','pending','applicant','2025-08-29 07:19:27','2025-08-29 07:19:27'),
(29,'01236547896','justin','just','0123654789','2025-09-03','just@just.com','masai lodge road 25','$2y$12$RvrHNlDk9bi.4ndY.JppVuTkj3cYdbK99VelJsB/jZDoI6btOkhFC','pending','applicant','2025-09-16 20:56:53','2025-09-16 20:56:53'),
(30,'852369741','pazu','prince','0113530113','1996-02-06','pazu@gmail.com','masai lodge road 25','$2y$12$q.gf3HTtd0vAU18LgB6t/.S7bLI.W85ibT5M3Mf/ejEmKK993o.eC','active','applicant','2026-02-21 12:28:35','2026-02-21 12:28:35');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2026-07-27 16:21:16
