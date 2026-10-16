-- MySQL dump 10.13  Distrib 5.7.24, for Linux (x86_64)
--
-- Host: 127.0.0.1    Database: spj_monitoring
-- ------------------------------------------------------
-- Server version	8.0.46-0ubuntu0.22.04.4

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `bendahara`
--

DROP TABLE IF EXISTS `bendahara`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bendahara` (
  `id_bendahara` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_pengajuan` bigint unsigned NOT NULL,
  `id_user` bigint unsigned DEFAULT NULL,
  `tahap` enum('PENGAJUAN_SPP','PEMBAYARAN_LANGSUNG','KONFIRMASI') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cara_bayar` enum('TRANSFER','TUNAI') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nominal` decimal(15,2) DEFAULT NULL,
  `nama_penerima` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_kwitansi` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `no_spp` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tgl_spp` date DEFAULT NULL,
  `no_spm` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tgl_transfer` date DEFAULT NULL,
  `no_sp2d` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tgl_sp2d` date DEFAULT NULL,
  `tanggal_proses` datetime NOT NULL,
  `id_status` bigint unsigned NOT NULL,
  `catatan` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `no_rekening` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama_bank` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `atas_nama_rekening` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id_bendahara`),
  KEY `bendahara_id_pengajuan_foreign` (`id_pengajuan`),
  KEY `bendahara_id_status_foreign` (`id_status`),
  KEY `bendahara_id_user_foreign` (`id_user`),
  CONSTRAINT `bendahara_id_pengajuan_foreign` FOREIGN KEY (`id_pengajuan`) REFERENCES `pengajuan` (`id_pengajuan`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `bendahara_id_status_foreign` FOREIGN KEY (`id_status`) REFERENCES `status_pencairan` (`id_status_pencairan`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `bendahara_id_user_foreign` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=65725 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bendahara`
--

LOCK TABLES `bendahara` WRITE;
/*!40000 ALTER TABLE `bendahara` DISABLE KEYS */;
INSERT INTO `bendahara` VALUES (65263,32681,20,'PENGAJUAN_SPP',NULL,NULL,NULL,NULL,'0987/12312/asdas1','2026-09-01',NULL,NULL,NULL,NULL,'2026-09-01 13:43:01',1,NULL,'2026-09-01 06:43:01',NULL,NULL,NULL),(65264,32681,20,'KONFIRMASI','TRANSFER',1000000.00,'Burhan',NULL,NULL,NULL,'1234556677','2026-09-01','5676577','2026-09-01','2026-09-01 13:46:49',1,NULL,'2026-09-01 06:46:49','9312093120930129','BRI','Burhan'),(65271,32693,20,'PEMBAYARAN_LANGSUNG','TUNAI',10000000.00,'Pak Angger','09876',NULL,NULL,NULL,'2026-09-01',NULL,NULL,'2026-09-01 14:18:55',1,'oke','2026-09-01 07:18:55',NULL,NULL,NULL),(65272,32694,20,'PENGAJUAN_SPP',NULL,NULL,NULL,NULL,'0000000000','2026-09-01',NULL,NULL,NULL,NULL,'2026-09-01 14:21:34',1,'oke','2026-09-01 07:21:34',NULL,NULL,NULL),(65309,32694,20,'KONFIRMASI','TUNAI',1000000.00,'Pak Hartono','09876',NULL,NULL,'0000000000','2026-09-01','76544566','2026-09-01','2026-09-01 14:36:20',1,NULL,'2026-09-01 07:36:20',NULL,NULL,NULL),(65310,32739,20,'PENGAJUAN_SPP',NULL,NULL,NULL,NULL,'0987/12312/asdas1','2026-09-03',NULL,NULL,NULL,NULL,'2026-09-01 14:44:51',1,NULL,'2026-09-01 07:44:51',NULL,NULL,NULL),(65330,32740,20,'PEMBAYARAN_LANGSUNG','TRANSFER',100000.00,'Sosialisasi',NULL,NULL,NULL,NULL,'2026-09-03',NULL,NULL,'2026-09-03 10:37:23',1,'oke','2026-09-03 03:37:23',NULL,NULL,NULL),(65447,32740,20,'PEMBAYARAN_LANGSUNG','TUNAI',400000.00,'ageng','090909090',NULL,NULL,NULL,'2026-09-03',NULL,NULL,'2026-09-03 10:58:37',1,'oke','2026-09-03 03:58:37',NULL,NULL,NULL),(65448,32740,20,'PEMBAYARAN_LANGSUNG','TUNAI',400000.00,'salsa','090909',NULL,NULL,NULL,'2026-09-03',NULL,NULL,'2026-09-03 10:58:37',1,'oke','2026-09-03 03:58:37',NULL,NULL,NULL),(65552,32739,20,'KONFIRMASI','TUNAI',50000.00,'gede','99',NULL,NULL,'0987/12312/asdas1/212','2026-09-03','777777','2026-09-03','2026-09-03 11:06:58',1,'oke','2026-09-03 04:06:58',NULL,NULL,NULL),(65553,32739,20,'KONFIRMASI','TUNAI',50000.00,'salsa','99',NULL,NULL,'0987/12312/asdas1/212','2026-09-03','777777','2026-09-03','2026-09-03 11:06:58',1,'oke','2026-09-03 04:06:58',NULL,NULL,NULL),(65554,32929,20,'PEMBAYARAN_LANGSUNG','TUNAI',100000.00,'ageng','99',NULL,NULL,NULL,'2026-09-03',NULL,NULL,'2026-09-03 11:24:17',1,NULL,'2026-09-03 04:24:17',NULL,NULL,NULL),(65555,32929,20,'PEMBAYARAN_LANGSUNG','TUNAI',50000.00,'salsa','99',NULL,NULL,NULL,'2026-09-03',NULL,NULL,'2026-09-03 11:24:17',1,NULL,'2026-09-03 04:24:17',NULL,NULL,NULL),(65556,32929,20,'PEMBAYARAN_LANGSUNG','TUNAI',50000.00,'ageng','888',NULL,NULL,NULL,'2026-09-03',NULL,NULL,'2026-09-03 11:26:04',1,NULL,'2026-09-03 04:26:04',NULL,NULL,NULL),(65557,32929,20,'PEMBAYARAN_LANGSUNG','TUNAI',450000.00,'salsa','888',NULL,NULL,NULL,'2026-09-03',NULL,NULL,'2026-09-03 11:26:04',1,NULL,'2026-09-03 04:26:04',NULL,NULL,NULL),(65618,32970,20,'PEMBAYARAN_LANGSUNG','TUNAI',70000.00,'ageng','09876/av/123',NULL,NULL,NULL,'2026-09-03',NULL,NULL,'2026-09-03 11:39:29',1,NULL,'2026-09-03 04:39:29',NULL,NULL,NULL),(65619,32970,20,'PEMBAYARAN_LANGSUNG','TUNAI',800000.00,'salsa','09876/av/123',NULL,NULL,NULL,'2026-09-03',NULL,NULL,'2026-09-03 11:39:29',1,NULL,'2026-09-03 04:39:29',NULL,NULL,NULL),(65692,32970,20,'PEMBAYARAN_LANGSUNG','TUNAI',500000.00,'ageng','09876/av/123',NULL,NULL,NULL,'2026-09-08',NULL,NULL,'2026-09-08 08:37:23',1,NULL,'2026-09-08 01:37:23',NULL,NULL,NULL),(65693,32970,20,'PEMBAYARAN_LANGSUNG','TUNAI',630000.00,'salsa','09876/av/123',NULL,NULL,NULL,'2026-09-08',NULL,NULL,'2026-09-08 08:37:23',1,NULL,'2026-09-08 01:37:23',NULL,NULL,NULL);
/*!40000 ALTER TABLE `bendahara` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'2014_10_11_000000_create_roles_table',1),(2,'2014_10_12_000000_create_users_table',1),(3,'2019_12_14_000001_create_personal_access_tokens_table',1),(4,'2026_08_18_000001_create_status_pengajuan_table',1),(5,'2026_08_18_000002_create_status_verifikasi_table',1),(6,'2026_08_18_000003_create_status_pencairan_table',1),(7,'2026_08_18_000004_create_pengajuan_table',1),(8,'2026_08_18_000005_create_verifikasi_table',1),(9,'2026_08_18_000006_create_ppk_table',1),(10,'2026_08_18_000007_create_bendahara_table',1),(11,'2026_08_18_000008_create_ppspm_table',1),(12,'2026_08_21_005823_add_metode_pembayaran_to_pengajuan_table',1),(13,'2026_08_21_005846_add_kolom_baru_to_ppk_table',1),(14,'2026_08_21_005856_add_kolom_baru_to_ppspm_table',1),(15,'2026_08_21_005911_update_bendahara_table_for_new_payment_flow',1),(16,'2026_08_21_074513_create_password_reset_tokens_table',1),(17,'2026_08_21_074939_add_remember_token_to_users_table',1),(18,'2026_08_24_014741_add_kolom_baru_to_pengajuan_table',2),(19,'2026_08_24_020203_make_perihal_nullable_in_pengajuan_table',3),(20,'2026_08_24_051832_add_rekening_to_bendahara_table',4),(21,'2026_08_24_051832_add_spm_sp2d_to_ppspm_table',4),(22,'2026_08_24_090000_align_status_pencairan_and_backfill_perihal',5),(23,'2026_08_26_100000_drop_no_hp_from_users_table',6),(24,'2026_09_01_090000_add_cara_bayar_to_bendahara_table',7),(25,'2026_09_03_090000_add_pencairan_parsial_support',8);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
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
-- Table structure for table `pengajuan`
--

DROP TABLE IF EXISTS `pengajuan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pengajuan` (
  `id_pengajuan` bigint unsigned NOT NULL AUTO_INCREMENT,
  `no_pengajuan` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `id_user` bigint unsigned NOT NULL,
  `tanggal_pengajuan` date NOT NULL,
  `perihal` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `keterangan` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_nominal` decimal(15,2) NOT NULL,
  `id_status` bigint unsigned NOT NULL,
  `metode_pembayaran` enum('LS_BENDAHARA','LS_PIHAK_KETIGA','UP_TUP') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `jenis_pencairan` enum('PENUH','PARSIAL') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `catatan_pengaju` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `bulan_kegiatan` date DEFAULT NULL,
  `tim_pengaju` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nama_penerima` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id_pengajuan`),
  UNIQUE KEY `pengajuan_no_pengajuan_unique` (`no_pengajuan`),
  KEY `pengajuan_id_user_foreign` (`id_user`),
  KEY `pengajuan_id_status_foreign` (`id_status`),
  CONSTRAINT `pengajuan_id_status_foreign` FOREIGN KEY (`id_status`) REFERENCES `status_pengajuan` (`id_status`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `pengajuan_id_user_foreign` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=33036 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pengajuan`
--

LOCK TABLES `pengajuan` WRITE;
/*!40000 ALTER TABLE `pengajuan` DISABLE KEYS */;
INSERT INTO `pengajuan` VALUES (32681,'PGJ-20260831-0001',29,'2026-08-31','Uang makan','Uang makan',1000000.00,11,'LS_BENDAHARA','PENUH',NULL,'2026-08-31 01:47:26','2026-09-01 06:46:49','2026-08-31','Pegawai','Pak Angger`'),(32693,'PGJ-20260901-0001',29,'2026-09-01','Komputer','Komputer',10000000.00,11,'UP_TUP','PENUH',NULL,'2026-09-01 07:16:29','2026-09-01 07:18:55','2026-09-01','Pranata','Pak Angger'),(32694,'PGJ-20260901-0002',29,'2026-09-01','oke','oke',1000000.00,11,'LS_BENDAHARA','PENUH',NULL,'2026-09-01 07:19:51','2026-09-01 07:36:20','2026-09-01','BPS','Pak Hartono'),(32739,'PGJ-20260901-0003',29,'2026-09-01','SE','SE',100000.00,11,'LS_PIHAK_KETIGA','PARSIAL',NULL,'2026-09-01 07:42:03','2026-09-03 04:06:58','2026-09-28','Produksi','Burhan'),(32740,'PGJ-20260901-0004',29,'2026-09-01','Beli konsumsi','Beli konsumsi',900000.00,11,'UP_TUP','PARSIAL',NULL,'2026-09-01 07:42:59','2026-09-03 03:58:37','2026-08-27','Produksi','Sosialisasi'),(32929,'PGJ-20260903-0001',29,'2026-09-03','SE','SE',10000000.00,12,'UP_TUP','PARSIAL',NULL,'2026-09-03 04:21:41','2026-09-03 04:24:17','2026-09-01','Produksi','Burhan'),(32970,'PGJ-20260903-0002',29,'2026-09-03','Perbaikan Gardu Listrik','Perbaikan Gardu Listrik',2000000.00,11,'UP_TUP','PARSIAL',NULL,'2026-09-03 04:37:36','2026-09-08 01:37:23','2026-09-01','Teknisi','Rahmadina');
/*!40000 ALTER TABLE `pengajuan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ppk`
--

DROP TABLE IF EXISTS `ppk`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ppk` (
  `id_ppk` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_pengajuan` bigint unsigned NOT NULL,
  `id_user` bigint unsigned DEFAULT NULL,
  `tanggal_proses` datetime NOT NULL,
  `id_status` bigint unsigned NOT NULL,
  `no_spm` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tgl_spm` date DEFAULT NULL,
  `catatan` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_ppk`),
  KEY `ppk_id_pengajuan_foreign` (`id_pengajuan`),
  KEY `ppk_id_status_foreign` (`id_status`),
  KEY `ppk_id_user_foreign` (`id_user`),
  CONSTRAINT `ppk_id_pengajuan_foreign` FOREIGN KEY (`id_pengajuan`) REFERENCES `pengajuan` (`id_pengajuan`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `ppk_id_status_foreign` FOREIGN KEY (`id_status`) REFERENCES `status_pencairan` (`id_status_pencairan`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `ppk_id_user_foreign` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=32755 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ppk`
--

LOCK TABLES `ppk` WRITE;
/*!40000 ALTER TABLE `ppk` DISABLE KEYS */;
INSERT INTO `ppk` VALUES (32634,32681,18,'2026-09-01 13:43:55',1,NULL,NULL,NULL,'2026-09-01 06:43:55'),(32636,32694,18,'2026-09-01 14:22:05',1,NULL,NULL,'oke','2026-09-01 07:22:05'),(32651,32739,18,'2026-09-01 14:45:20',1,NULL,NULL,NULL,'2026-09-01 07:45:20');
/*!40000 ALTER TABLE `ppk` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ppspm`
--

DROP TABLE IF EXISTS `ppspm`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `ppspm` (
  `id_ppspm` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_pengajuan` bigint unsigned NOT NULL,
  `id_user` bigint unsigned DEFAULT NULL,
  `tanggal_proses` datetime NOT NULL,
  `id_status` bigint unsigned NOT NULL,
  `tgl_ajukan_kemenkeu` date DEFAULT NULL,
  `catatan` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `no_spm` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tgl_spm` date DEFAULT NULL,
  `no_sp2d` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tgl_sp2d` date DEFAULT NULL,
  PRIMARY KEY (`id_ppspm`),
  KEY `ppspm_id_pengajuan_foreign` (`id_pengajuan`),
  KEY `ppspm_id_status_foreign` (`id_status`),
  KEY `ppspm_id_user_foreign` (`id_user`),
  CONSTRAINT `ppspm_id_pengajuan_foreign` FOREIGN KEY (`id_pengajuan`) REFERENCES `pengajuan` (`id_pengajuan`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `ppspm_id_status_foreign` FOREIGN KEY (`id_status`) REFERENCES `status_pencairan` (`id_status_pencairan`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `ppspm_id_user_foreign` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=32729 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ppspm`
--

LOCK TABLES `ppspm` WRITE;
/*!40000 ALTER TABLE `ppspm` DISABLE KEYS */;
INSERT INTO `ppspm` VALUES (32625,32681,19,'2026-09-01 13:45:48',1,'2026-09-01',NULL,'2026-09-01 06:45:48','1234556677','2026-09-01','5676577','2026-09-01'),(32627,32694,19,'2026-09-01 14:22:47',1,'2026-09-01','oke','2026-09-01 07:22:47','0000000000','2026-09-01',NULL,NULL),(32642,32739,19,'2026-09-01 14:46:14',1,'2026-09-01',NULL,'2026-09-01 07:46:14','0987/12312/asdas1/212','2026-09-01',NULL,NULL);
/*!40000 ALTER TABLE `ppspm` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id_role` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nama_role` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `keterangan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id_role`),
  UNIQUE KEY `roles_nama_role_unique` (`nama_role`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'pegawai','Pegawai/pemohon pengajuan pembayaran'),(2,'verifikator','Verifikator tahap 1, 2, atau 3'),(3,'ppk','Pejabat Pembuat Komitmen'),(4,'bendahara','Bendahara'),(5,'ppspm','PPSPM'),(6,'admin','Administrator aplikasi');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `status_pencairan`
--

DROP TABLE IF EXISTS `status_pencairan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `status_pencairan` (
  `id_status_pencairan` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kode_status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `keterangan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id_status_pencairan`),
  UNIQUE KEY `status_pencairan_kode_status_unique` (`kode_status`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `status_pencairan`
--

LOCK TABLES `status_pencairan` WRITE;
/*!40000 ALTER TABLE `status_pencairan` DISABLE KEYS */;
INSERT INTO `status_pencairan` VALUES (1,'ACC','ACC','Disetujui dan diteruskan ke tahap berikutnya.'),(2,'REVISI','Revisi','Dikembalikan untuk diperbaiki pengaju.'),(3,'DITOLAK','Ditolak','Pengajuan ditolak pada tahap pencairan.'),(4,'SELESAI','Selesai','Tahap pencairan selesai.'),(5,'MENUNGGU','Menunggu','Menunggu proses pencairan.');
/*!40000 ALTER TABLE `status_pencairan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `status_pengajuan`
--

DROP TABLE IF EXISTS `status_pengajuan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `status_pengajuan` (
  `id_status` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kode_status` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `keterangan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `urutan` tinyint unsigned NOT NULL,
  PRIMARY KEY (`id_status`),
  UNIQUE KEY `status_pengajuan_kode_status_unique` (`kode_status`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `status_pengajuan`
--

LOCK TABLES `status_pengajuan` WRITE;
/*!40000 ALTER TABLE `status_pengajuan` DISABLE KEYS */;
INSERT INTO `status_pengajuan` VALUES (1,'DIAJUKAN','Diajukan','Baru diajukan pegawai',1),(2,'VERIFIKASI_1','Verifikasi Tahap 1','Menunggu Verifikator 1',2),(3,'VERIFIKASI_2','Verifikasi Tahap 2','Menunggu Verifikator 2',3),(4,'VERIFIKASI_3','Verifikasi Tahap 3','Menunggu Verifikator 3',4),(5,'REVISI','Revisi','Dikembalikan ke pegawai',5),(6,'DITOLAK','Ditolak','Final, tidak lanjut',6),(7,'MENUNGGU_PENCAIRAN','Menunggu Pencairan','Lolos verifikasi, menunggu Bendahara memilih metode pembayaran',7),(8,'PROSES_PPK','Proses PPK','Menunggu PPK terbitkan SPM',8),(9,'PROSES_PPSPM','Proses PPSPM','Menunggu PPSPM ajukan ke Kemenkeu',9),(10,'PROSES_KONFIRMASI_BENDAHARA','Konfirmasi Bendahara','Menunggu konfirmasi akhir dari Bendahara',10),(11,'SELESAI','Selesai','Dana sudah cair ke penerima',12),(12,'PENCAIRAN_PARSIAL','Pencairan Sebagian','Sebagian nominal sudah dibayarkan, sisanya menunggu pembayaran berikutnya.',11);
/*!40000 ALTER TABLE `status_pengajuan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `status_verifikasi`
--

DROP TABLE IF EXISTS `status_verifikasi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `status_verifikasi` (
  `id_status_verifikasi` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kode_status` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `keterangan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id_status_verifikasi`),
  UNIQUE KEY `status_verifikasi_kode_status_unique` (`kode_status`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `status_verifikasi`
--

LOCK TABLES `status_verifikasi` WRITE;
/*!40000 ALTER TABLE `status_verifikasi` DISABLE KEYS */;
INSERT INTO `status_verifikasi` VALUES (1,'MENUNGGU','Menunggu','Belum diverifikasi.'),(2,'ACC','ACC','Pengajuan disetujui pada tahap verifikasi.'),(3,'REVISI','Revisi','Pengajuan harus diperbaiki oleh pegawai.'),(4,'TOLAK','Tolak','Pengajuan ditolak.');
/*!40000 ALTER TABLE `status_verifikasi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id_user` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nama_lengkap` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_role` bigint unsigned NOT NULL,
  `status_aktif` tinyint(1) NOT NULL DEFAULT '1',
  `urutan_verifikator` tinyint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_id_role_foreign` (`id_role`),
  CONSTRAINT `users_id_role_foreign` FOREIGN KEY (`id_role`) REFERENCES `roles` (`id_role`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (13,'Administrator','admin@bps-batanghari.com','$2y$12$ihkLGTF0Cw0dHKuQFmrHt.ZrUVLoHivQki/Kf2oHRF1cWP8zI5.Wy',NULL,6,1,NULL,'2026-08-26 07:24:38','2026-08-26 07:24:38'),(14,'Hartono','ton@bps.go.id','$2y$12$WDxg/o8K70eo5AIgXmv/4uD2q00unPTj9BgyLLc3vFzE993fT07g6',NULL,6,1,NULL,'2026-08-26 07:24:38','2026-08-26 07:24:38'),(15,'Ida Candra','idacandra-pppk@bps.go.id','$2y$12$Yjjaxe4CZ789k5DI/cXdsOUJiW4j/aAxpF3Fg4FaQCz5TnEbJSQ8W',NULL,2,1,1,'2026-08-26 07:24:38','2026-08-26 07:24:38'),(16,'Latif Fitriyanto','latiffitriyan-pppk@bps.go.id','$2y$12$p06fGeO/nw.Xdis5w2FtNeKbQoo8bZ8NnuNHP3V1b9I20qOVEijom',NULL,2,1,2,'2026-08-26 07:24:38','2026-08-26 07:24:38'),(17,'Lanna Sari Siregar','lannasari-pppk@bps.go.id','$2y$12$wBN0gWbFDlmjDQJYZz81Su.5QrwbokSVUQy2U0bXtLsBM79eMnGIK',NULL,2,1,3,'2026-08-26 07:24:38','2026-08-26 07:24:38'),(18,'Madik','madik@bps.go.id','$2y$12$j7bCNpP4c.ZHt0TaANosKOFENfXQDnvbX1sUIlbM1d3msrjcokwOu',NULL,3,1,NULL,'2026-08-26 07:24:38','2026-08-26 07:24:38'),(19,'Angger Halim Ismail','angger@bps.go.id','$2y$12$lX8bi7OLB3sS6J4SCASJ3uuJmyLXiTEhqn8BlHM1uKuROBDVgm3xi',NULL,5,1,NULL,'2026-08-26 07:24:38','2026-08-26 07:24:38'),(20,'Yusmiradewi','yusmira@bps.go.id','$2y$12$vUR2ZVqO9L9E8.HeZ9WKbunAnPfqjHYdQO9n1E94nLj9SrQiot2ei',NULL,4,1,NULL,'2026-08-26 07:24:38','2026-08-26 07:24:38'),(21,'A. Puadi','puadi@bps.go.id','$2y$12$ML5s2ALe/AWj9VG66cDEZOt649esMsIDwPiTyt4UaIg.L1/cOFdyy',NULL,1,1,NULL,'2026-08-26 07:24:38','2026-08-26 07:24:38'),(22,'Aida Devanty Putri','aida.devanty.putri@bps-batanghari.com','$2y$12$W5tzC23T36NDi0t7UfimGu5op5JbltCU.OjgToEvtj4cpFyjLhnJy',NULL,1,0,NULL,'2026-08-26 07:24:38','2026-08-26 07:24:38'),(23,'Alief Raditia Ali','raditia.ali@bps.go.id','$2y$12$kwE6CQ0oNx4y7KniGgDV9eDLvjB8C0lQS4Zl4ONp5069ksh9QtTs2',NULL,1,1,NULL,'2026-08-26 07:24:38','2026-08-26 07:24:38'),(24,'Dias Khusnul Khotimah','diaskhusnul@bps.go.id','$2y$12$qCs6dOfKF/s7Uc3iOpoMju7kvQzZc2MTa6aIhZaPGn1T/NI6id2nW',NULL,1,1,NULL,'2026-08-26 07:24:38','2026-08-26 07:24:38'),(25,'Disya Pratistaning Ratriatmaja','disya.pratista@bps.go.id','$2y$10$DBvfYQ6A1WB2MelwAH3wHeBKJ2uL9579Oh8/k8UjZHuEdLKkr7L9.',NULL,1,0,NULL,'2026-08-26 07:24:38','2026-08-26 07:24:38'),(26,'Doli Herdianto','doli@bps.go.id','$2y$12$S8H7LeHeL/C0jEcD8QaTMei54QNmTZxNORqUSop/RR4h8mhIS/7DW',NULL,1,1,NULL,'2026-08-26 07:24:38','2026-08-26 07:24:38'),(27,'Dwi Satria Firmansyah','satria.firmansyah@bps.go.id','$2y$12$ozTnGglialZ23136LV9o8Oo.wvTVbL/SWC7TiSEx4CJpHphDUrJ4m',NULL,1,1,NULL,'2026-08-26 07:24:38','2026-08-26 07:24:38'),(28,'Eka Julita Irmayanti','eka.julita.irmayanti@bps-batanghari.com','$2y$12$cC9yralAKqsLm1QH5/cnJeqcwBvzGzQDJzGau8aCQsYU559U0ksg6',NULL,1,0,NULL,'2026-08-26 07:24:38','2026-08-26 07:24:38'),(29,'Erisa','erisa@bps.go.id','$2y$12$ZK2T8Oe8XuWW/JA1kOdReehlNqcToEGn6B.Q3VgqBBK.g3xpEY276',NULL,1,1,NULL,'2026-08-26 07:24:38','2026-08-26 07:24:38'),(30,'Fathina Mufrodi','fathina.mufrodi@bps-batanghari.com','$2y$12$fiYOhk7tRqaL4XE7O0GiAO4RIUk3jtisxO1Ip19NNG.2MIPOTIKuC',NULL,1,0,NULL,'2026-08-26 07:24:38','2026-08-26 07:24:38'),(31,'Fitri Pratiwi','fitri.pratiwi@bps.go.id','$2y$12$AwLHTZe51A.CVVaMBo5B8.JIPQ37xD4/yffMOuNjvN5Thvc8E5L/i',NULL,1,1,NULL,'2026-08-26 07:24:38','2026-08-26 07:24:38'),(32,'Habib Asror','habibasror-pppk@bps.go.id','$2y$12$fMCVCtXWdr17.N9nh8egtOtHw6pz1IGutkL1N9c5olHup/S2JA7oS',NULL,1,1,NULL,'2026-08-26 07:24:38','2026-08-26 07:24:38'),(33,'Hanna Sajidha','hannasajidha@bps.go.id','$2y$12$1rrp5nytVxT7V6z3kgceqOQVXStgzYL0KkdmoTqHEJ1f/QJHl.7om',NULL,1,1,NULL,'2026-08-26 07:24:38','2026-08-26 07:24:38'),(34,'Hendra Rusmanto','hrusmanto@bps.go.id','$2y$12$oDK5FPQo83JuLKWTXldB8.QwvVFAD4rQSvmcXc5GN97RIFlNXsizO',NULL,1,1,NULL,'2026-08-26 07:24:38','2026-08-26 07:24:38'),(35,'Imelda Salsabila','imelda.salsabila@bps.go.id','$2y$12$KVx/9gs3L2Iam87SNLuM1ehztWM.A8RWuewYwKY4ofCviy2mmz/Ve',NULL,1,1,NULL,'2026-08-26 07:24:38','2026-08-26 07:24:38'),(36,'Isyak','isyak@bps.go.id','$2y$12$cBr69wDZsaJKVQ7DftZbfuoWoyVaq21sAJAHjX9RebsFWeGKgYZQa',NULL,1,1,NULL,'2026-08-26 07:24:38','2026-08-26 07:24:38'),(37,'Johani','johani@bps.go.id','$2y$12$ybty4pMqsliEEjdAEpg6meuzz9qurBrjk2f71g7Js/efLofji3KvS',NULL,1,1,NULL,'2026-08-26 07:24:38','2026-08-26 07:24:38'),(38,'Jupri','jupri@bps.go.id','$2y$12$4A.tytXo3CuMOw7KYYwrde7Na/avYyjFsg4IAxI1R2Tn5irh1e5r6',NULL,1,1,NULL,'2026-08-26 07:24:38','2026-08-26 07:24:38'),(39,'Linda Annisa','linda.annisa@bps.go.id','$2y$12$no0LqYXxZiG7lqFb/qJ.3e6Z0fMutd4.ZCzklHDIVdzYB1iRhZF7C',NULL,1,0,NULL,'2026-08-26 07:24:38','2026-08-26 07:24:38'),(40,'Marini Syafitri','marini.syafitri@bps.go.id','$2y$12$hCaWMrCp41Hg2JMIjYGBguFmeozZiBBcjnlwZuGQjSkpJcZPMwDg.',NULL,1,0,NULL,'2026-08-26 07:24:38','2026-08-26 07:24:38'),(41,'Maulana Pandudinata','maulana.pandudinata@bps.go.id','$2y$12$MMe0dsMZ1r4mfF57KjVE8OccfEctl02OfycFMjj1EAlglLjqS5CMq',NULL,1,1,NULL,'2026-08-26 07:24:38','2026-08-26 07:24:38'),(42,'Muhammad Al Fatih','muhammad.al.fatih@bps-batanghari.com','$2y$12$8JgSnIzcPc5rW.pfiByTs.Ue6IbtbQw1y277dI73n0XF.Xoqdut56',NULL,1,0,NULL,'2026-08-26 07:24:38','2026-08-26 07:24:38'),(43,'Nafisa Qurrotul Ayuni','nafisa.ayuni@bps.go.id','$2y$12$7cJqfyNQkRMZHImEZvuCVefBzyUvkjBWxF.cm/WyMxDq0G7yrc4yu',NULL,1,1,NULL,'2026-08-26 07:24:38','2026-08-26 07:24:38'),(44,'Nofer Satrial','nofersatrial-pppk@bps.go.id','$2y$12$.P6PRAxKuTlj4eMd69/8/uxIwl2sZmpLhpxlRs6C0k.AwM265OHuS',NULL,1,1,NULL,'2026-08-26 07:24:38','2026-08-26 07:24:38'),(45,'Rensy Hasibuan','rensy.hasibuan@bps.go.id','$2y$12$kSdqGAjeXzCSrYsDrHzK.uTU4gZbgBrF24dXrtRWQSpyge9XDS64y',NULL,1,1,NULL,'2026-08-26 07:24:38','2026-08-26 07:24:38'),(46,'Rina Agustina','rinagus@bps.go.id','$2y$12$xeNaWkEMiKB22L7d0NnkBOGZrljSGo2VqZWkWuCjinIVcjuxSQZe6',NULL,1,1,NULL,'2026-08-26 07:24:38','2026-08-26 07:24:38'),(47,'Septie Wulandary','septie@bps.go.id','$2y$12$KZYhaD9PuM0.WWFOFSfjGe/rD9KoGa/HE2sJDlYQzH2KB1Y3VITHi',NULL,1,1,NULL,'2026-08-26 07:24:38','2026-08-26 07:24:38'),(48,'Simon Christofher Nainggolan','simon.christofher@bps.go.id','$2y$12$TIpfkRbcUZoaNOV.hDjdr.Pg6d9J8YFVt3rJ01Pf5SIrAv2moBMlS',NULL,1,1,NULL,'2026-08-26 07:24:38','2026-08-26 07:24:38');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `verifikasi`
--

DROP TABLE IF EXISTS `verifikasi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `verifikasi` (
  `id_verifikasi` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_pengajuan` bigint unsigned NOT NULL,
  `id_verifikator` bigint unsigned NOT NULL,
  `tahap` tinyint unsigned NOT NULL,
  `tanggal_verifikasi` datetime NOT NULL,
  `id_status_verifikasi` bigint unsigned NOT NULL,
  `catatan` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_verifikasi`),
  KEY `verifikasi_id_verifikator_foreign` (`id_verifikator`),
  KEY `verifikasi_id_status_verifikasi_foreign` (`id_status_verifikasi`),
  KEY `verifikasi_id_pengajuan_tahap_index` (`id_pengajuan`,`tahap`),
  CONSTRAINT `verifikasi_id_pengajuan_foreign` FOREIGN KEY (`id_pengajuan`) REFERENCES `pengajuan` (`id_pengajuan`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `verifikasi_id_status_verifikasi_foreign` FOREIGN KEY (`id_status_verifikasi`) REFERENCES `status_verifikasi` (`id_status_verifikasi`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `verifikasi_id_verifikator_foreign` FOREIGN KEY (`id_verifikator`) REFERENCES `users` (`id_user`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=117267 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `verifikasi`
--

LOCK TABLES `verifikasi` WRITE;
/*!40000 ALTER TABLE `verifikasi` DISABLE KEYS */;
INSERT INTO `verifikasi` VALUES (117234,32681,15,1,'2026-09-01 13:33:04',2,NULL,'2026-09-01 06:33:04'),(117235,32681,16,2,'2026-09-01 13:33:32',2,'oke','2026-09-01 06:33:32'),(117236,32681,17,3,'2026-09-01 13:35:05',2,'acc','2026-09-01 06:35:05'),(117238,32693,15,1,'2026-09-01 14:17:03',2,'oke','2026-09-01 07:17:03'),(117239,32693,16,2,'2026-09-01 14:17:26',2,'oke','2026-09-01 07:17:26'),(117240,32693,17,3,'2026-09-01 14:17:49',2,'oke','2026-09-01 07:17:49'),(117241,32694,15,1,'2026-09-01 14:20:11',2,'oke','2026-09-01 07:20:11'),(117242,32694,16,2,'2026-09-01 14:20:30',2,'oke','2026-09-01 07:20:30'),(117243,32694,17,3,'2026-09-01 14:20:48',2,'oke','2026-09-01 07:20:48'),(117247,32739,15,1,'2026-09-01 14:42:30',2,NULL,'2026-09-01 07:42:30'),(117248,32740,15,1,'2026-09-01 14:43:36',2,'ok','2026-09-01 07:43:36'),(117249,32739,16,2,'2026-09-01 14:43:58',2,'acc','2026-09-01 07:43:58'),(117250,32739,17,3,'2026-09-01 14:44:15',2,'ok','2026-09-01 07:44:15'),(117251,32740,16,2,'2026-09-02 15:36:16',2,NULL,'2026-09-02 08:36:16'),(117252,32740,17,3,'2026-09-02 15:36:36',2,NULL,'2026-09-02 08:36:36'),(117259,32929,15,1,'2026-09-03 11:21:55',2,NULL,'2026-09-03 04:21:55'),(117260,32929,16,2,'2026-09-03 11:22:06',2,NULL,'2026-09-03 04:22:06'),(117261,32929,17,3,'2026-09-03 11:22:18',2,NULL,'2026-09-03 04:22:18'),(117263,32970,15,1,'2026-09-03 11:37:55',2,NULL,'2026-09-03 04:37:55'),(117264,32970,16,2,'2026-09-03 11:38:12',2,NULL,'2026-09-03 04:38:12'),(117265,32970,17,3,'2026-09-03 11:38:25',2,NULL,'2026-09-03 04:38:25');
/*!40000 ALTER TABLE `verifikasi` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-09  8:03:18
