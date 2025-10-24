/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.5.29-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: db5018301966.hosting-data.io    Database: dbs14504718
-- ------------------------------------------------------
-- Server version	8.0.36

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
-- Table structure for table `activities`
--

DROP TABLE IF EXISTS `activities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `activities` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nomeAttivita` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `linkDestinazione` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `logoUrl` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dataFineVisualizzazione` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `activities`
--

LOCK TABLES `activities` WRITE;
/*!40000 ALTER TABLE `activities` DISABLE KEYS */;
/*!40000 ALTER TABLE `activities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `admin_notifications`
--

DROP TABLE IF EXISTS `admin_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `admin_notifications` (
  `id` int NOT NULL AUTO_INCREMENT,
  `type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `message` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `link` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_read` tinyint NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `is_read_idx` (`is_read`),
  KEY `type_idx` (`type`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admin_notifications`
--

LOCK TABLES `admin_notifications` WRITE;
/*!40000 ALTER TABLE `admin_notifications` DISABLE KEYS */;
INSERT INTO `admin_notifications` VALUES (1,'event_suggestion','Nuovo evento suggerito: 22222','suggerimenti-eventi.php',0,'2025-09-30 23:12:26'),(2,'new_business','Nuova attività registrata: ewrwerwer','business.php?action=edit&id=15',0,'2025-09-30 23:12:59');
/*!40000 ALTER TABLE `admin_notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `articles`
--

DROP TABLE IF EXISTS `articles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `articles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `excerpt` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `featured_image` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gallery_images` json DEFAULT NULL,
  `category_id` int NOT NULL,
  `province_id` int DEFAULT NULL,
  `city_id` int DEFAULT NULL,
  `author` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('draft','published','archived') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'published',
  `featured` tinyint DEFAULT '0',
  `views` int DEFAULT '0',
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `allow_user_uploads` tinyint DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `json_data` json DEFAULT NULL,
  `hero_image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `google_maps_iframe` text COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `category_id` (`category_id`),
  KEY `province_id` (`province_id`),
  KEY `city_id` (`city_id`),
  KEY `status` (`status`),
  KEY `featured` (`featured`),
  CONSTRAINT `articles_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  CONSTRAINT `articles_ibfk_2` FOREIGN KEY (`province_id`) REFERENCES `provinces` (`id`) ON DELETE SET NULL,
  CONSTRAINT `articles_ibfk_3` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=33 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `articles`
--

LOCK TABLES `articles` WRITE;
/*!40000 ALTER TABLE `articles` DISABLE KEYS */;
INSERT INTO `articles` VALUES (3,'La \'Nduja: Il Piccante Orgoglio di Spilinga','la-nduja-il-piccante-orgoglio-di-spilinga','La \'nduja è un salume piccante spalmabile originario di Spilinga, piccolo borgo in provincia di Vibo Valentia. Questa prelibatezza, ottenuta da carni suine e peperoncino calabrese, rappresenta l\'essenza della tradizione gastronomica locale. La sua preparazione segue ancora oggi ricette tramandate di generazione in generazione, utilizzando solo ingredienti locali di altissima qualità.','La \'nduja di Spilinga, salume piccante simbolo della gastronomia calabrese.','articles/featured/img_68eed3707b35b_1760482160.webp','[\"articles/gallery/img_68eed370a980f_1760482160.webp\", \"articles/gallery/img_68eed370b0c86_1760482160.webp\", \"articles/gallery/img_68eed370e9550_1760482160.webp\"]',3,5,7,'Giuseppe Calabrese','published',1,0,38.65000000,15.90000000,1,'2025-09-08 20:04:52','2025-10-14 22:49:21','{\"seo\": {\"meta_title\": \"\", \"meta_keywords\": \"\", \"meta_description\": \"\"}}',NULL,NULL,NULL),(4,'Tropea: La Perla del Tirreno','tropea-la-perla-del-tirreno','Tropea è universalmente riconosciuta come una delle località balneari più belle d\'Italia. Arroccata su un promontorio a strapiombo sul mare, offre uno dei panorami più suggestivi della Calabria. Le sue spiagge di sabbia bianca, bagnate da acque cristalline, e il centro storico ricco di chiese e palazzi nobiliari, fanno di Tropea una meta imperdibile per chi visita la regione.','Tropea, perla del Tirreno con spiagge da sogno e centro storico mozzafiato.',NULL,NULL,4,5,14,'Maria Costantino','published',1,1,38.67730000,15.89840000,1,'2025-09-08 20:04:52','2025-10-10 10:26:57',NULL,NULL,NULL,NULL),(6,'asfasf','asfasf','asfasf','',NULL,'[\"uploads/articles/gallery_68cfb42c41988.png\", \"uploads/articles/gallery_68cfb42c419dc.png\", \"uploads/articles/gallery_68cfb42c41a5d.png\", \"uploads/articles/gallery_68cfb42c41ad8.png\", \"uploads/articles/gallery_68cfb42c41b20.png\"]',27,2,7,'Admin','published',0,11,NULL,NULL,1,'2025-09-21 08:15:40','2025-10-10 10:26:57',NULL,NULL,NULL,NULL),(7,'aaaaaaaaaaaaaaa','aaaaaaaaaaaaaaa','aaaaaaaaaaaaaaaaaaaaaaa','',NULL,'[\"uploads/articles/gallery_68d443e78c873.jpg\", \"uploads/articles/gallery_68d443e78c8c8.jpg\", \"uploads/articles/gallery_68d443e78c91f.jpg\", \"uploads/articles/gallery_68d443e78c96a.jpg\", \"uploads/articles/gallery_68d443e78c9be.jpg\"]',26,1,7,'Admin','published',0,6,NULL,NULL,1,'2025-09-24 19:17:59','2025-10-10 11:08:53','{\"seo\": {\"meta_title\": \"\", \"meta_keywords\": \"\", \"meta_description\": \"\"}, \"address\": \"\", \"services\": {\"custom\": \"\"}, \"maps_link\": \"\", \"treatments\": \"\", \"price_range\": \"\", \"opening_hours\": \"\", \"contact_details\": {\"email\": \"\", \"phone\": \"\"}}',NULL,'uploads/articles/logo_68e63daedd683.jpg',NULL),(8,'vbbbbbbbbbbbbbbbb','vbbbbbbbbbbbbbbbb','bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb','',NULL,'[\"uploads/articles/gallery_68d478181098f.jpg\", \"uploads/articles/gallery_68d47818117f7.jpg\", \"uploads/articles/gallery_68d478181269f.jpg\", \"uploads/articles/gallery_68d4781813598.jpg\", \"uploads/articles/gallery_68d4781813f8d.jpg\", \"uploads/articles/gallery_68d4781814dd8.jpg\"]',26,2,7,'Admin','published',0,6,NULL,NULL,1,'2025-09-24 19:52:20','2025-10-10 10:26:57','{\"seo\": {\"meta_title\": \"\", \"meta_keywords\": \"\", \"meta_description\": \"\"}, \"address\": \"\", \"services\": {\"custom\": \"\"}, \"maps_link\": \"\", \"treatments\": \"\", \"price_range\": \"\", \"opening_hours\": \"\", \"contact_details\": {\"email\": \"\", \"phone\": \"\"}}',NULL,'uploads/articles/logo_68e63da3e5099.jpg',NULL),(9,'sfdsadasdsad','sfdsadasdsad','dsfasdfasfascasc','',NULL,'[\"uploads/articles/gallery_68d57ec8169a2.jpg\", \"uploads/articles/gallery_68d57ec816c47.jpg\", \"uploads/articles/gallery_68d57ec816ce6.jpg\", \"uploads/articles/gallery_68d57ec816e67.jpg\", \"uploads/articles/gallery_68d57ec8170dd.jpg\", \"uploads/articles/gallery_68d57ec8182ef.jpg\"]',26,2,7,'Admin','published',0,2,NULL,NULL,1,'2025-09-25 17:41:28','2025-10-10 10:26:57','{\"seo\": {\"meta_title\": \"\", \"meta_keywords\": \"\", \"meta_description\": \"\"}, \"address\": \"\", \"services\": {\"custom\": \"\"}, \"maps_link\": \"\", \"treatments\": \"\", \"price_range\": \"\", \"opening_hours\": \"\", \"contact_details\": {\"email\": \"\", \"phone\": \"\"}}',NULL,'uploads/articles/logo_68e63d98b54b9.jpg',NULL),(11,'adfasdfadfasf','adfasdfadfasf','afafasfasfasf','',NULL,'[\"uploads/articles/gallery_68d59ddf059bb.jpg\", \"uploads/articles/gallery_68d59ddf068b7.jpg\", \"uploads/articles/gallery_68d59ddf07895.jpg\", \"uploads/articles/gallery_68d59ddf08ab8.jpg\", \"uploads/articles/gallery_68d59ddf097f2.jpg\", \"uploads/articles/gallery_68d59ddf0a7ed.jpg\"]',26,2,7,'Admin','published',0,3,NULL,NULL,1,'2025-09-25 19:54:07','2025-10-10 10:26:57','{\"address\": \"asfasfaf\", \"services\": {\"custom\": \"asfasf\", \"predefined\": [\"Massaggi\", \"Bagno Turco\"]}, \"maps_link\": \"\", \"treatments\": \"asfasfasf\", \"price_range\": \"asfasfaf\", \"opening_hours\": \"asfasfasf\", \"contact_details\": {\"email\": \"afasfaf@gmail.com\", \"phone\": \"124124124\"}}','uploads/articles/hero_68d59ddf045af.jpg','uploads/articles/logo_68d59ddf05809.jpg',NULL),(12,'12123123','12123123','123123123234','',NULL,'[\"uploads/articles/gallery_68d5a9d0bcc0f.jpg\", \"uploads/articles/gallery_68d5a9d0bdab6.jpg\", \"uploads/articles/gallery_68d5a9d0beb22.jpg\", \"uploads/articles/gallery_68d5a9d0bfc70.jpg\"]',26,2,7,'Admin','published',0,2,NULL,NULL,1,'2025-09-25 20:45:04','2025-10-10 10:26:57',NULL,NULL,NULL,NULL),(14,'11111111111111','11111111111111','111111111111','',NULL,'[\"uploads/articles/gallery_68d5bb49315a4.jpg\", \"uploads/articles/gallery_68d5bb49324f4.jpg\", \"uploads/articles/gallery_68d5bb49334b3.jpg\", \"uploads/articles/gallery_68d5bb4934543.jpg\"]',26,2,7,'Admin','published',0,51,NULL,NULL,1,'2025-09-25 21:59:37','2025-10-10 10:26:57','{\"seo\": {\"meta_title\": \"\", \"meta_keywords\": \"\", \"meta_description\": \"\"}, \"address\": \"\", \"services\": {\"custom\": \"\"}, \"maps_link\": \"\", \"treatments\": \"\", \"price_range\": \"\", \"opening_hours\": \"\", \"contact_details\": {\"email\": \"\", \"phone\": \"\"}}',NULL,'uploads/articles/logo_68e63d8d83fd1.jpg',NULL),(15,'aaaaaaaaaaaaaaaa','aaaaaaaaaaaaaaaa','aaaaaaaaaaaaaaaa','',NULL,'[\"uploads/articles/gallery/img_68e374e5198ea_1759737061.webp\", \"uploads/articles/gallery/img_68e374e5d7d5b_1759737061.webp\", \"uploads/articles/gallery/img_68e374e66e308_1759737062.webp\", \"uploads/articles/gallery/img_68e374e705288_1759737063.webp\", \"uploads/articles/gallery/img_68e374e773ae9_1759737063.webp\", \"uploads/articles/gallery/img_68e374e84a5a1_1759737064.webp\", \"uploads/articles/gallery/img_68e374e90480c_1759737065.webp\"]',24,2,7,'Admin','published',0,1,NULL,NULL,1,'2025-10-06 07:51:05','2025-10-10 10:26:57','{\"seo\": {\"meta_title\": \"aaaaaaaaa\", \"meta_keywords\": \"sasas, asdfasf\", \"meta_description\": \"asasasasasasasassa\"}, \"address\": \"aaaaaaa\", \"maps_link\": \"\", \"exhibitions\": \"aaaaaaaaaaaa\", \"ticket_info\": \"\", \"opening_hours\": \"aaaaaaaaa\"}','uploads/articles/hero/img_68e374e4389e9_1759737060.webp','uploads/articles/logos/img_68e374e4efa0b_1759737060.webp',NULL),(16,'11111111','111','1111','11111','uploads/articles/featured/img_68e37f3d4c95d_1759739709.webp','[\"uploads/articles/gallery/img_68e37f3d5200e_1759739709.webp\", \"uploads/articles/gallery/img_68e37f3ddcace_1759739709.webp\", \"uploads/articles/gallery/img_68e37f3e749e7_1759739710.webp\", \"uploads/articles/gallery/img_68e37f3ee1f02_1759739710.webp\", \"uploads/articles/gallery/img_68e37f3fb7d04_1759739711.webp\", \"uploads/articles/gallery/img_68e37f4071da7_1759739712.webp\", \"uploads/articles/gallery/img_68e37f414845f_1759739713.webp\", \"uploads/articles/gallery/img_68e37f414e0d2_1759739713.webp\", \"uploads/articles/gallery/img_68e37f4153ac7_1759739713.webp\", \"uploads/articles/gallery/img_68e37f41cc7a7_1759739713.webp\", \"uploads/articles/gallery/img_68e37f41cdfe6_1759739713.webp\", \"uploads/articles/gallery/img_68e37f41cfbe3_1759739713.webp\", \"uploads/articles/gallery/img_68e37f41d14d9_1759739713.webp\", \"uploads/articles/gallery/img_68e37f41d2fed_1759739713.webp\"]',3,2,7,'Admin','published',0,1,NULL,NULL,1,'2025-10-06 08:35:13','2025-10-10 10:26:57','{\"seo\": {\"meta_title\": \"\", \"meta_keywords\": \"13131313\", \"meta_description\": \"\\\\1111111111111\"}}','','',NULL),(17,'sdfsdfsdfsdf','sdfsdfsdfsdf','dsfsdfsdfwer23rfdsffsdfq3rasdfdasfadsfrr32r4','','','[\"uploads/articles/gallery/img_68e3867dbd4ba_1759741565.webp\", \"uploads/articles/gallery/img_68e3867e4d6a2_1759741566.webp\", \"uploads/articles/gallery/img_68e3867e54ca5_1759741566.webp\", \"uploads/articles/gallery/img_68e3867e95047_1759741566.webp\", \"uploads/articles/gallery/img_68e3867ec169d_1759741566.webp\", \"uploads/articles/gallery/img_68e3867f95961_1759741567.webp\", \"uploads/articles/gallery/img_68e386802522c_1759741568.webp\", \"uploads/articles/gallery/img_68e38680e6a19_1759741568.webp\", \"uploads/articles/gallery/img_68e386817dec3_1759741569.webp\", \"uploads/articles/gallery/img_68e3868215be2_1759741570.webp\", \"uploads/articles/gallery/img_68e38682834b1_1759741570.webp\", \"uploads/articles/gallery/img_68e386835b79b_1759741571.webp\", \"uploads/articles/gallery/img_68e3868418cf2_1759741572.webp\", \"uploads/articles/gallery/img_68e386841ead9_1759741572.webp\", \"uploads/articles/gallery/img_68e386842468f_1759741572.webp\"]',34,2,7,'Admin','published',0,1,NULL,NULL,1,'2025-10-06 09:06:12','2025-10-10 10:26:57','{\"seo\": {\"meta_title\": \"edfssdfsdf23resdsf\", \"meta_keywords\": \"sdf23resdfsdf\", \"meta_description\": \"sdfgsdr32wqfsdf32qr\"}, \"p_iva\": \"32234234\", \"address\": \"sdfsdfsdf\", \"products\": \"sdfsdf\", \"maps_link\": \"\", \"workshops\": \"sdfsdfsdf\", \"contact_details\": {\"email\": \"sdfasfaw@gmail.com\", \"phone\": \"2342342344\"}}','uploads/articles/hero/img_68e3867ce9131_1759741564.webp','uploads/articles/logos/img_68e3867dac4b1_1759741565.webp',NULL),(18,'efewsf','dsfsdf','sdfsdfsdf','sdfsdfsdf','uploads/articles/img_68e3902b49465_1759744043.webp','[\"uploads/articles/gallery/img_68e3902b810b2_1759744043.webp\", \"uploads/articles/gallery/img_68e3902b88755_1759744043.webp\", \"uploads/articles/gallery/img_68e3902bc86dc_1759744043.webp\", \"uploads/articles/gallery/img_68e3902c022b4_1759744044.webp\", \"uploads/articles/gallery/img_68e3902cc84bc_1759744044.webp\", \"uploads/articles/gallery/img_68e3902d57786_1759744045.webp\"]',4,2,7,'Admin','published',0,0,NULL,NULL,1,'2025-10-06 09:47:26','2025-10-10 10:26:57','{\"seo\": {\"meta_title\": \"sdfsdf3qredf\", \"meta_keywords\": \"sdfsdfsdf\", \"meta_description\": \"sdfq3rfsdf\"}}',NULL,NULL,NULL),(19,'frsdgfrdg','dfgfdg','dfgdfgfdgfdg','fdgfdgfdgdg','uploads/articles/featured_68e39237c9881.jpg','[\"uploads/articles/gallery_68e39237c9b6f.jpg\", \"uploads/articles/gallery_68e39237c9c0d.jpg\", \"uploads/articles/gallery_68e39237c9d9d.jpg\", \"uploads/articles/gallery_68e39237c9ffd.jpg\"]',4,2,7,'Admin','published',0,3,NULL,NULL,1,'2025-10-06 09:56:07','2025-10-10 10:26:57','{\"seo\": {\"meta_title\": \"sdfsdfsdff\", \"meta_keywords\": \"sdfsdf\", \"meta_description\": \"sdfsdfsdf\"}}',NULL,NULL,NULL),(20,'asdsadsad','sadasd','asdasdsadasdsad','asdasdasdsad','articles/featured/img_68e429dee3a53_1759783390.webp','[\"articles/gallery/img_68e429df652aa_1759783391.webp\", \"articles/gallery/img_68e429dfeebde_1759783391.webp\", \"articles/gallery/img_68e429e083a9d_1759783392.webp\", \"articles/gallery/img_68e429e0efe6d_1759783392.webp\", \"articles/gallery/img_68e429e1c4962_1759783393.webp\", \"articles/gallery/img_68e429e2807a4_1759783394.webp\"]',3,2,7,'Admin','published',0,5,NULL,NULL,1,'2025-10-06 20:43:15','2025-10-14 08:14:41','{\"seo\": {\"meta_title\": \"asdsadsadsad\", \"meta_keywords\": \"asdasdasd\", \"meta_description\": \"sadsadsadasdsasad\"}}','','',NULL),(21,'edsfeswfsef','edsfeswfsef','asdasdsadsadsdasad3qer3qraefasfd','',NULL,'[\"article_68e44987d9d7e20b65c6c.webp\", \"article_68e44989491de549f0c19.webp\", \"article_68e4498ac300f4d13b8d6.webp\", \"article_68e4498c38c280f9e8cf3.webp\", \"article_68e4498d61e193317e1c0.webp\", \"article_68e4498ec8155413ac503.webp\", \"article_68e4498fe0d724ecb0a44.webp\"]',27,2,7,'Admin','published',0,1,NULL,NULL,1,'2025-10-06 22:58:25','2025-10-10 10:26:57','{\"seo\": {\"meta_title\": \"asdasdasdsad\", \"meta_keywords\": \"\", \"meta_description\": \"asdsadsaddas\"}, \"maps_link\": \"\", \"ticket_info\": \"Ingresso libero\", \"opening_hours\": \"sefseff\"}','article_68e44987567684ca5acd0.webp',NULL,NULL),(22,'sgsgsg','sgsgsg','sdfsdf23resfef','',NULL,'[\"uploads/articles/gallery_68e4aa24e5d64.jpeg\", \"uploads/articles/gallery_68e4aa24e5dd6.jpeg\", \"uploads/articles/gallery_68e4aa24e5e3c.jpeg\", \"uploads/articles/gallery_68e4aa24e5ea0.png\", \"uploads/articles/gallery_68e4aa24e5f13.png\"]',39,NULL,NULL,'Admin','published',0,3,NULL,NULL,1,'2025-10-07 05:50:28','2025-10-10 10:26:57','{\"seo\": {\"meta_title\": \"\", \"meta_keywords\": \"\", \"meta_description\": \"\"}, \"prices\": \"\", \"duration\": \"\", \"services\": {\"custom\": \"\"}, \"contact_details\": {\"email\": \"\", \"phone\": \"\"}}',NULL,'uploads/articles/logo_68e4dc23404ce.jpg',NULL),(23,'fbgfdgfdb','fbgfdgfdb','esfesfsdfsdfsdf','',NULL,'[\"uploads/articles/gallery_68e4dc7932e20.jpg\", \"uploads/articles/gallery_68e4dc7933d8e.jpg\", \"uploads/articles/gallery_68e4dc7934dac.jpg\", \"uploads/articles/gallery_68e4dc7935e62.jpg\"]',26,2,7,'Admin','published',0,1,NULL,NULL,1,'2025-10-07 09:25:13','2025-10-10 10:26:57','{\"seo\": {\"meta_title\": \"\", \"meta_keywords\": \"\", \"meta_description\": \"\"}, \"address\": \"\", \"services\": {\"custom\": \"\"}, \"maps_link\": \"\", \"treatments\": \"\", \"price_range\": \"\", \"opening_hours\": \"\", \"contact_details\": {\"email\": \"\", \"phone\": \"\"}}',NULL,'uploads/articles/logo_68e4e47483e99.jpg',NULL),(24,'qwdwqdqwdq','qwdwqdqwd','qasdsadsadsad','',NULL,'[\"uploads/articles/gallery_68e4e58ef2476.jpg\", \"uploads/articles/gallery_68e4e58ef3382.jpg\", \"uploads/articles/gallery_68e4e58f00115.jpg\", \"uploads/articles/gallery_68e4e58f01149.jpg\", \"uploads/articles/gallery_68e4e58f01c49.jpg\"]',26,2,7,'Admin','published',0,0,NULL,NULL,1,'2025-10-07 10:03:59','2025-10-10 10:26:57','{\"seo\": {\"meta_title\": \"\", \"meta_keywords\": \"\", \"meta_description\": \"\"}, \"address\": \"\", \"services\": {\"custom\": \"\"}, \"maps_link\": \"\", \"treatments\": \"\", \"price_range\": \"\", \"opening_hours\": \"\", \"contact_details\": {\"email\": \"\", \"phone\": \"\"}}',NULL,'articles/logos/img_68e67ee994029_1759936233.webp',NULL),(25,'aaaaaaaaaaaaaa','aaaaaaaaaaaaaa','aaaaaaaaaaaa','',NULL,NULL,26,2,7,'Admin','published',0,0,NULL,NULL,1,'2025-10-08 10:01:57','2025-10-10 10:26:57','{\"seo\": {\"meta_title\": \"\", \"meta_keywords\": \"\", \"meta_description\": \"\"}, \"address\": \"\", \"services\": {\"custom\": \"\"}, \"maps_link\": \"\", \"treatments\": \"\", \"price_range\": \"\", \"opening_hours\": \"\", \"contact_details\": {\"email\": \"\", \"phone\": \"\"}}',NULL,'articles/logos/img_68e7cdda82245_1760021978.webp',NULL),(26,'bbbbbbbbbbbbbbbb','bbbbbbbbbbbbb','bbbbbbbbbbbbbb','bbbbbbbbbbb','uploads/articles/featured_68e64b0c75b8d.jpg','[\"uploads/articles/gallery_68e64b0c75dbb.jpg\", \"uploads/articles/gallery_68e64b0c75e58.jpg\", \"uploads/articles/gallery_68e64b0c75ff3.jpg\", \"uploads/articles/gallery_68e64b0c76287.jpg\", \"uploads/articles/gallery_68e64b0c77578.jpg\", \"uploads/articles/gallery_68e64b0c78077.jpg\", \"uploads/articles/gallery_68e64b0c79207.jpg\"]',3,2,7,'Admin','published',0,1,NULL,NULL,1,'2025-10-08 11:29:16','2025-10-10 10:26:57','{\"seo\": {\"meta_title\": \"bbbbbbbbbbbbbbbb\", \"meta_keywords\": \"\", \"meta_description\": \"bbbbbbbbbbbbbbbbbbbbb\"}}',NULL,NULL,NULL),(27,'hhhhhhhhhhhhhhhhhhh','hhhhhhhhhhhhhhhhhhh','hhhhhhhhhhhhhhhhh','',NULL,'[\"articles/gallery/img_68e7ceb160c9c_1760022193.webp\", \"articles/gallery/img_68e7ceb21ec8d_1760022194.webp\", \"articles/gallery/img_68e7ceb2a20d6_1760022194.webp\", \"articles/gallery/img_68e7ceb3323f4_1760022195.webp\", \"articles/gallery/img_68e7ceb3a6521_1760022195.webp\", \"articles/gallery/img_68e7ceb46e373_1760022196.webp\", \"articles/gallery/img_68e7ceb51e182_1760022197.webp\"]',26,2,7,'Admin','published',0,7,NULL,NULL,1,'2025-10-09 15:03:17','2025-10-10 10:26:57','{\"seo\": {\"meta_title\": \"\", \"meta_keywords\": \"\", \"meta_description\": \"\"}, \"address\": \"\", \"services\": {\"custom\": \"\"}, \"maps_link\": \"\", \"treatments\": \"\", \"price_range\": \"\", \"opening_hours\": \"\", \"contact_details\": {\"email\": \"\", \"phone\": \"\"}}',NULL,'articles/logos/img_68e821b690a27_1760043446.webp',NULL),(28,'555555555555555555555','555555555555555555555','555555555555','',NULL,'[\"articles/gallery/img_68e82394b87e7_1760043924.webp\", \"articles/gallery/img_68e8239577626_1760043925.webp\", \"articles/gallery/img_68e8239606343_1760043926.webp\", \"articles/gallery/img_68e823968ab56_1760043926.webp\", \"articles/gallery/img_68e823970a7c8_1760043927.webp\", \"articles/gallery/img_68e82397c63c4_1760043927.webp\"]',26,2,7,'Admin','published',0,1,NULL,NULL,1,'2025-10-09 21:05:28','2025-10-10 10:26:57','{\"seo\": {\"meta_title\": \"\", \"meta_keywords\": \"\", \"meta_description\": \"\"}, \"address\": \"\", \"services\": {\"custom\": \"\"}, \"maps_link\": \"\", \"treatments\": \"\", \"price_range\": \"\", \"opening_hours\": \"\", \"contact_details\": {\"email\": \"\", \"phone\": \"\"}}',NULL,'articles/logos/img_68e8315de4e5b_1760047453.webp',NULL),(29,'66666666666666','66666666666666','6666666666666','',NULL,'[\"articles/gallery/img_68e8a5e41962c_1760077284.webp\", \"articles/gallery/img_68e8a5e497510_1760077284.webp\", \"articles/gallery/img_68e8a5e49ec9a_1760077284.webp\", \"articles/gallery/img_68e8a5e4d7ca6_1760077284.webp\", \"articles/gallery/img_68e8a5e5124e9_1760077285.webp\", \"articles/gallery/img_68e8a5e5cc0d4_1760077285.webp\"]',26,2,7,'Admin','published',0,29,NULL,NULL,1,'2025-10-09 21:07:29','2025-10-11 14:42:10','{\"seo\": {\"meta_title\": \"6666666666\", \"meta_keywords\": \"\", \"meta_description\": \"6666666666666\"}, \"address\": \"7777777\", \"services\": {\"custom\": \"777777777\", \"predefined\": [\"Massaggi\", \"Piscina Termale\", \"Bagno Turco\"]}, \"maps_link\": \"\", \"treatments\": \"777777777777777777\", \"price_range\": \"77777777777\", \"opening_hours\": \"777777777777\", \"contact_details\": {\"email\": \"fsdgsdfeaf@gmail.com\", \"phone\": \"345345345345\"}}','articles/hero/img_68e8a5e3d0bca_1760077283.webp','articles/logos/img_68e8a5e413d3c_1760077284.webp',NULL),(30,'zzzzzzzzzzzzzzzzz','zzzzzzzzzzzzzzzzz','zzzzzzzzzzzzzzzz','',NULL,'[\"articles/gallery/img_68ed5d94efece_1760386452.webp\", \"articles/gallery/img_68ed5d95781f0_1760386453.webp\", \"articles/gallery/img_68ed5d957f71a_1760386453.webp\", \"articles/gallery/img_68ed5d95b72ca_1760386453.webp\"]',26,2,7,'Admin','published',0,17,NULL,NULL,1,'2025-10-10 15:08:49','2025-10-20 22:04:28','{\"seo\": {\"meta_title\": \"\", \"meta_keywords\": \"\", \"meta_description\": \"\"}, \"address\": \"\", \"services\": {\"custom\": \"\"}, \"maps_link\": \"\", \"treatments\": \"\", \"price_range\": \"\", \"opening_hours\": \"\", \"contact_details\": {\"email\": \"\", \"phone\": \"\"}}','articles/hero/img_68ed5d94d5cc3_1760386452.webp','articles/logos/img_68ed5d94e89fb_1760386452.webp',NULL),(31,'aaaaaaaaaaaaa','aaaaaaaaaaaaa','aaaaaaaaaaaaaaaaaa','',NULL,'[\"articles/gallery/img_68ed75d0a8fa4_1760392656.webp\"]',28,2,7,'Admin','published',0,0,NULL,NULL,1,'2025-10-13 21:57:37','2025-10-13 21:57:51','{\"seo\": {\"meta_title\": \"\", \"meta_keywords\": \"\", \"meta_description\": \"\"}, \"stops\": \"\", \"services\": {\"custom\": \"\"}}','articles/hero/img_68ed75df77ecc_1760392671.webp',NULL,NULL),(32,'nnnnnnnnnnnnnnn','nnnnnnnnnnnnnnn','sdfsdfsdf','',NULL,'[\"articles/gallery/img_68f6b237b1e9b_1760997943.webp\", \"articles/gallery/img_68f6b2387083d_1760997944.webp\", \"articles/gallery/img_68f6b238f303c_1760997944.webp\"]',26,2,7,'Admin','published',0,30,NULL,NULL,1,'2025-10-15 10:08:55','2025-10-22 10:07:22','{\"seo\": {\"meta_title\": \"\", \"meta_keywords\": \"\", \"meta_description\": \"\"}, \"address\": \"\", \"services\": {\"custom\": \"\"}, \"maps_link\": \"\", \"treatments\": \"\", \"price_range\": \"\", \"opening_hours\": \"\", \"contact_details\": {\"email\": \"\", \"phone\": \"\"}}','articles/hero/img_68f6b2379d321_1760997943.webp','articles/logos/img_68f6b237afbca_1760997943.webp','');
/*!40000 ALTER TABLE `articles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `business_activity_log`
--

DROP TABLE IF EXISTS `business_activity_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `business_activity_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `business_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `action` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `business_id` (`business_id`),
  KEY `user_id` (`user_id`),
  KEY `action` (`action`),
  KEY `created_at` (`created_at`),
  CONSTRAINT `business_activity_log_ibfk_1` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `business_activity_log_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `business_activity_log`
--

LOCK TABLES `business_activity_log` WRITE;
/*!40000 ALTER TABLE `business_activity_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `business_activity_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `business_packages`
--

DROP TABLE IF EXISTS `business_packages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `business_packages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `price` decimal(10,2) NOT NULL,
  `package_type` enum('subscription','consumption') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'subscription',
  `duration_months` int DEFAULT '12',
  `consumption_credits` int DEFAULT NULL,
  `features` json DEFAULT NULL,
  `stripe_price_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_path` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint DEFAULT '1',
  `sort_order` int DEFAULT '0',
  `max_listings` int DEFAULT NULL,
  `max_photos` int DEFAULT NULL,
  `analytics_included` tinyint DEFAULT '0',
  `priority_support` tinyint DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `business_packages`
--

LOCK TABLES `business_packages` WRITE;
/*!40000 ALTER TABLE `business_packages` DISABLE KEYS */;
INSERT INTO `business_packages` VALUES (1,'Gratuito','Inserimento base della tua attività',0.00,'subscription',12,NULL,'[\"Scheda attività base\", \"Contatti e orari\", \"Visibilità nella ricerca\", \"1 foto principale\", \"Descrizione base\"]',NULL,NULL,1,1,1,1,0,0,'2025-09-08 20:04:52'),(2,'Business','Pacchetto completo per la tua attività',29.99,'subscription',12,NULL,'[\"Tutto del piano Gratuito\", \"Foto illimitate\", \"Descrizione estesa\", \"Badge verificato\", \"Statistiche visualizzazioni\", \"Orari dettagliati\", \"Link social\", \"Supporto email\"]',NULL,NULL,1,2,3,10,1,0,'2025-09-08 20:04:52'),(3,'Premium','Massima visibilità e funzionalità avanzate',59.99,'subscription',12,NULL,'[\"Tutto del piano Business\", \"Posizione privilegiata\", \"Articoli sponsorizzati\", \"Analytics avanzate\", \"Supporto prioritario\", \"Eventi promozionali\", \"Video gallery\", \"SEO avanzato\"]',NULL,NULL,1,3,10,50,1,1,'2025-09-08 20:04:52'),(4,'Pacchetto Boost Base','Crediti per promozioni e visibilità',19.99,'consumption',NULL,50,'[\"50 crediti per promozioni\", \"Evidenzia la tua attività\", \"Boost temporaneo nelle ricerche\", \"Validità 6 mesi\"]',NULL,NULL,1,4,NULL,NULL,0,0,'2025-09-08 20:04:52'),(5,'Pacchetto Boost Pro','Più crediti per massima visibilità',49.99,'consumption',NULL,150,'[\"150 crediti per promozioni\", \"Evidenzia la tua attività\", \"Boost temporaneo nelle ricerche\", \"Analytics premium\", \"Validità 12 mesi\"]',NULL,NULL,1,5,NULL,NULL,1,0,'2025-09-08 20:04:52'),(6,'Pacchetto Boost Enterprise','Crediti illimitati per grandi aziende',99.99,'consumption',NULL,500,'[\"500 crediti per promozioni\", \"Evidenzia la tua attività\", \"Boost temporaneo nelle ricerche\", \"Analytics avanzate\", \"Supporto dedicato\", \"Validità 12 mesi\"]',NULL,NULL,1,6,NULL,NULL,1,1,'2025-09-08 20:04:52'),(7,'prova','sdafdsf\r\nasdfadf\r\nasfrgwe\r\n',10.99,'consumption',12,20,'\"\"','',NULL,1,0,NULL,NULL,0,0,'2025-09-10 08:24:58'),(8,'fafqe3fgwsqaf','asfqaegfqe',1000.00,'subscription',12,NULL,'[\"aaaaaaaaaaaaaaa\", \"aaaaaaaaaaaaaa\", \"aaaaaaaaaaaaa\"]','',NULL,1,0,NULL,NULL,0,0,'2025-09-10 19:15:05'),(9,'22222222','22222',0.10,'subscription',36,NULL,'[\"22222\"]','prod_TAVFjzknVTrnRY',NULL,1,0,NULL,NULL,0,0,'2025-10-03 14:46:01');
/*!40000 ALTER TABLE `business_packages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `business_sessions`
--

DROP TABLE IF EXISTS `business_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `business_sessions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `business_id` int NOT NULL,
  `user_id` int NOT NULL,
  `session_token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` timestamp NOT NULL,
  `last_activity` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `session_token` (`session_token`),
  KEY `business_id` (`business_id`),
  KEY `user_id` (`user_id`),
  KEY `expires_at` (`expires_at`),
  CONSTRAINT `business_sessions_ibfk_1` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `business_sessions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `business_sessions`
--

LOCK TABLES `business_sessions` WRITE;
/*!40000 ALTER TABLE `business_sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `business_sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `businesses`
--

DROP TABLE IF EXISTS `businesses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `businesses` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `website` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `category_id` int DEFAULT NULL,
  `province_id` int DEFAULT NULL,
  `city_id` int DEFAULT NULL,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `status` enum('pending','approved','rejected','suspended') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `subscription_type` enum('free','basic','premium') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'free',
  `logo_path` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cover_image_path` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `business_hours` json DEFAULT NULL,
  `social_links` json DEFAULT NULL,
  `verified` tinyint DEFAULT '0',
  `featured` tinyint DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `category_id` (`category_id`),
  KEY `province_id` (`province_id`),
  KEY `city_id` (`city_id`),
  KEY `status` (`status`),
  KEY `subscription_type` (`subscription_type`),
  CONSTRAINT `businesses_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `businesses_ibfk_2` FOREIGN KEY (`province_id`) REFERENCES `provinces` (`id`) ON DELETE SET NULL,
  CONSTRAINT `businesses_ibfk_3` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `businesses`
--

LOCK TABLES `businesses` WRITE;
/*!40000 ALTER TABLE `businesses` DISABLE KEYS */;
INSERT INTO `businesses` VALUES (1,'ca services di andrea cavaliere','info@caservices.it','3345075668','','attivita di pubblicita e sposorizzazioni',NULL,2,NULL,'via falcone borsellino 3',NULL,NULL,'approved','free',NULL,NULL,NULL,NULL,0,0,'2025-09-08 20:14:22','2025-10-10 10:26:57'),(2,'asdasd','aasdasd@gmail.com','','','asdsadas',NULL,2,NULL,'',NULL,NULL,'pending','basic',NULL,NULL,NULL,NULL,0,0,'2025-09-08 20:21:24','2025-10-10 10:26:57'),(3,'asdsadsad','asdsad@gmail.com','','','adfadfadf',NULL,1,NULL,'',NULL,NULL,'approved','free',NULL,NULL,NULL,NULL,0,0,'2025-09-08 20:22:16','2025-10-10 10:26:57'),(4,'asdasdasd','asdasd@gmail.com','','','asfsdfsdf',NULL,2,NULL,'',NULL,NULL,'pending','basic',NULL,NULL,NULL,NULL,0,0,'2025-09-08 20:23:10','2025-10-10 10:26:57'),(5,'aasfdasfasfas','asfasf@gmail.com','','','asdasfasf',NULL,1,NULL,'',NULL,NULL,'approved','free',NULL,NULL,NULL,NULL,0,0,'2025-09-08 20:44:54','2025-10-10 10:26:57'),(6,'afafafasf','3254325235@gmail.com','','','asqw3rq3r',NULL,4,NULL,'',NULL,NULL,'approved','free',NULL,NULL,NULL,NULL,0,0,'2025-09-08 21:55:14','2025-10-10 10:26:57'),(7,'Attività di Test 2025-09-09 00:05:29','testuser_68bf53296a925@example.com',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'approved','free',NULL,NULL,NULL,NULL,0,0,'2025-09-08 22:05:29','2025-10-10 10:26:57'),(8,'awsfwqafrwqf','ascfrtgde@gmail.com','','','edfewf',NULL,3,NULL,'',NULL,NULL,'approved','free',NULL,NULL,NULL,NULL,0,0,'2025-09-08 22:17:06','2025-10-10 10:26:57'),(9,'edfsdfsdf','rehth435@gmail.com','','','asfafaf',NULL,2,NULL,'',NULL,NULL,'approved','free',NULL,NULL,NULL,NULL,0,0,'2025-09-08 22:45:04','2025-10-10 10:26:57'),(10,'asfasfasfaf','asfasfwq@gmail.com','','','asdfq3fasfe',NULL,1,NULL,'',NULL,NULL,'approved','free',NULL,NULL,NULL,NULL,0,0,'2025-09-09 21:41:19','2025-10-10 10:26:57'),(11,'afasfasf','bambolo@gmail.com','2431241235235','','adfqerq341234sd',NULL,2,NULL,'',NULL,NULL,'approved','free',NULL,NULL,NULL,NULL,0,0,'2025-09-09 21:47:14','2025-10-10 10:26:57'),(12,'sdfsdfsdf','wrgw324asdf@gmail.com','','','sdfsdf32r23r',NULL,4,NULL,'',NULL,NULL,'pending','basic',NULL,NULL,NULL,NULL,0,0,'2025-09-09 22:47:35','2025-10-10 10:26:57'),(13,'3123r3412','124124124@gmail.com','','','wefsdfadf',NULL,4,NULL,'',NULL,NULL,'approved','basic',NULL,NULL,NULL,NULL,0,0,'2025-09-09 22:48:20','2025-10-10 10:26:57'),(14,'andrea','giggiolino@gmail.com','','','daf3weredf',NULL,2,NULL,'',NULL,NULL,'approved','basic',NULL,NULL,NULL,NULL,0,0,'2025-09-09 23:01:33','2025-10-10 10:26:57'),(15,'ewrwerwer','werw43324@gmail.com','','','sdfsdf wsedefwe5423tresdfwer',14,2,NULL,'',NULL,NULL,'approved','free',NULL,NULL,NULL,NULL,0,0,'2025-09-30 23:12:59','2025-10-10 10:26:57'),(16,'sadasdasd','asdase12qe@gmail.com','','','1111111111111111111111111',33,2,NULL,'',NULL,NULL,'approved','free',NULL,NULL,NULL,NULL,0,0,'2025-10-01 21:34:03','2025-10-10 10:26:57'),(18,'1111111','cavaliere.andrea69@gmail.com','33333333','','32423423sdaasdasd3ewqsdsadasd',33,2,NULL,'32234',NULL,NULL,'pending','free',NULL,NULL,NULL,NULL,0,0,'2025-10-03 14:47:20','2025-10-10 10:26:57'),(20,'passione calabria','info@passionecalabria.it','','','sono l&#039;admin',20,2,NULL,'',NULL,NULL,'approved','free',NULL,NULL,NULL,NULL,0,0,'2025-10-08 06:03:32','2025-10-10 10:26:57');
/*!40000 ALTER TABLE `businesses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `icon` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (3,'Gastronomia','Assapora i sapori autentici della tradizione','🍝','2025-09-08 20:04:52'),(4,'Mare e Coste','Le più belle spiagge e località balneari','🏖️','2025-09-08 20:04:52'),(14,'Sport e Avventura','Attività sportive e outdoor','🚴','2025-09-08 20:04:52'),(20,'Hotel e Alloggi','Dove dormire in Calabria','🏨','2025-09-08 20:04:52'),(24,'Arte e Cultura','','','2025-09-11 10:43:57'),(25,'Attività Sportive e Avventura','','','2025-09-11 10:44:05'),(26,' Benessere e Relax','','','2025-09-11 10:44:13'),(27,'Chiese e Santuari','','','2025-09-11 10:44:31'),(28,' Itinerari Tematici','','','2025-09-11 10:44:42'),(29,'Musei e Gallerie','','','2025-09-11 10:44:50'),(30,'Parchi e Aree Verdi','','','2025-09-11 10:44:59'),(31,'Patrimonio Storico','','','2025-09-11 10:45:07'),(32,'Piazze e Vie Storiche','','','2025-09-11 10:45:19'),(33,'Ristorazione','','','2025-09-11 10:45:27'),(34,'Shopping e Artigianato','','','2025-09-11 10:45:41'),(35,'Siti Archeologici','','','2025-09-11 10:45:49'),(37,'Stabilimenti Balneari','','','2025-09-11 10:46:04'),(38,'Teatri e Anfiteatri','','','2025-09-11 10:46:12'),(39,'Tour e Guide','','','2025-09-11 10:46:19'),(40,'Trasporti','','','2025-09-11 10:46:27');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cities`
--

DROP TABLE IF EXISTS `cities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cities` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `province_id` int NOT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `hero_image` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Maps_link` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gallery_images` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `province_id` (`province_id`),
  CONSTRAINT `cities_ibfk_1` FOREIGN KEY (`province_id`) REFERENCES `provinces` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cities`
--

LOCK TABLES `cities` WRITE;
/*!40000 ALTER TABLE `cities` DISABLE KEYS */;
INSERT INTO `cities` VALUES (1,'Catanzaro',1,38.90980000,16.59690000,'Capoluogo di regione','cities/hero/img_68ecba1baad8f_1760344603.webp','','[\"cities/gallery/img_68e8dad5883dc_1760090837.webp\", \"cities/gallery/img_68e8dad58f4fd_1760090837.webp\", \"cities/gallery/img_68e8dad5c7f17_1760090837.webp\", \"cities/gallery/img_68e8dad602731_1760090838.webp\"]','2025-09-08 20:04:52'),(2,'Lamezia Terme',1,38.96480000,16.31290000,'Importante centro della piana',NULL,NULL,NULL,'2025-09-08 20:04:52'),(3,'Soverato',1,38.69180000,16.55130000,'Perla dello Ionio',NULL,NULL,NULL,'2025-09-08 20:04:52'),(4,'Sellia Marina',1,38.81960000,16.73390000,'Località balneare ionica',NULL,NULL,NULL,'2025-09-08 20:04:52'),(5,'Cosenza',2,39.29480000,16.25420000,'Città dei Bruzi',NULL,NULL,NULL,'2025-09-08 20:04:52'),(6,'Rossano',2,39.57610000,16.63140000,'Città della liquirizia',NULL,NULL,NULL,'2025-09-08 20:04:52'),(7,'Paola',2,39.36560000,16.03780000,'Città di San Francesco',NULL,NULL,NULL,'2025-09-08 20:04:52'),(8,'Scalea',2,39.81470000,15.79390000,'Riviera dei Cedri',NULL,NULL,NULL,'2025-09-08 20:04:52'),(9,'Diamante',2,NULL,NULL,'Città del peperoncino',NULL,'<iframe src=\"https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3084.936184820103!2d16.036974076362178!3d39.357681719413705!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x133fa4c9f6f9b12b%3A0xf2d6f1fb3382c373!2sVia%20Giovanni%20Falcone%20e%20Paolo%20Borsellino%2C%203%2C%2087027%20Paola%20CS!5e0!3m2!1sit!2sit!4v1760446048739!5m2!1sit!2sit\" width=\"600\" height=\"450\" style=\"border:0;\" allowfullscreen=\"\" loading=\"lazy\" referrerpolicy=\"no-referrer-when-downgrade\"></iframe>',NULL,'2025-09-08 20:04:52'),(10,'Crotone',3,39.08470000,17.12520000,'Antica Kroton',NULL,NULL,NULL,'2025-09-08 20:04:52'),(11,'Cirò Marina',3,39.37260000,17.12830000,'Terra del vino Cirò','cities/hero/img_68e8fd7fe68fe_1760099711.webp','','[\"cities/gallery/img_68e8f769b2f95_1760098153.webp\", \"cities/gallery/img_68e8f769ba663_1760098153.webp\", \"cities/gallery/img_68e8f769f3265_1760098153.webp\", \"cities/gallery/img_68e8f76a2cb84_1760098154.webp\", \"cities/gallery/img_68e8fd8073e1b_1760099712.webp\", \"cities/gallery/img_68e8fd810386d_1760099713.webp\", \"cities/gallery/img_68e8fd81884b1_1760099713.webp\", \"cities/gallery/img_68e8fd820771b_1760099714.webp\", \"cities/gallery/img_68e8fd82c534e_1760099714.webp\"]','2025-09-08 20:04:52'),(12,'Reggio Calabria',4,38.10980000,15.65160000,'Città dei Bronzi',NULL,NULL,NULL,'2025-09-08 20:04:52'),(13,'Scilla',4,38.24760000,15.71720000,'Borgo marinaro sullo Stretto',NULL,NULL,NULL,'2025-09-08 20:04:52'),(14,'Tropea',5,38.67730000,15.89760000,'Perla del Tirreno',NULL,NULL,NULL,'2025-09-08 20:04:52'),(15,'Vibo Valentia',5,38.67590000,16.10180000,'Antica Hipponion',NULL,NULL,NULL,'2025-09-08 20:04:52'),(16,'Pizzo',5,38.73470000,16.15690000,'Città del tartufo',NULL,NULL,NULL,'2025-09-08 20:04:52');
/*!40000 ALTER TABLE `cities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `comments`
--

DROP TABLE IF EXISTS `comments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `comments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `article_id` int DEFAULT NULL,
  `city_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `author_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `author_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `rating` int DEFAULT NULL,
  `status` enum('pending','approved','rejected','spam') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `article_id` (`article_id`),
  KEY `user_id` (`user_id`),
  KEY `status` (`status`),
  KEY `idx_comments_city_id` (`city_id`),
  CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_comments_city_id` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comments`
--

LOCK TABLES `comments` WRITE;
/*!40000 ALTER TABLE `comments` DISABLE KEYS */;
INSERT INTO `comments` VALUES (1,NULL,7,NULL,'dfsdf234124','asfq3412@gmail.com','dsafsdfg234qasf',4,'approved','2025-09-22 19:35:10'),(2,11,NULL,NULL,'andrea','foignjsd@gmail.com','safasfasfa',4,'approved','2025-09-25 20:41:32'),(3,14,NULL,NULL,'vxcvxcv','cxvxcvxcv@gmail.com','xzcsafdcc',4,'approved','2025-09-25 22:48:23'),(4,14,NULL,NULL,'asdsadasda','sdasddas@gamail.com','asdsadda',4,'approved','2025-09-29 22:21:08'),(5,14,NULL,NULL,'56436t254','2452352@gmail.com','35235235',4,'approved','2025-09-29 22:30:17'),(6,14,NULL,NULL,'1111','11111@gmail.com','2222',3,'approved','2025-09-30 22:04:26');
/*!40000 ALTER TABLE `comments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `comuni`
--

DROP TABLE IF EXISTS `comuni`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `comuni` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(255) NOT NULL,
  `provincia` varchar(255) NOT NULL,
  `importo_pagato` decimal(10,2) NOT NULL,
  `data_pagamento` date NOT NULL,
  `data_scadenza` date NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `comuni`
--

LOCK TABLES `comuni` WRITE;
/*!40000 ALTER TABLE `comuni` DISABLE KEYS */;
/*!40000 ALTER TABLE `comuni` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `consumption_purchases`
--

DROP TABLE IF EXISTS `consumption_purchases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `consumption_purchases` (
  `id` int NOT NULL AUTO_INCREMENT,
  `business_id` int NOT NULL,
  `package_id` int NOT NULL,
  `stripe_payment_intent_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `credits_purchased` int NOT NULL,
  `credits_remaining` int NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL,
  `status` enum('pending','completed','cancelled','refunded') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `purchased_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `business_id` (`business_id`),
  KEY `package_id` (`package_id`),
  KEY `status` (`status`),
  CONSTRAINT `consumption_purchases_ibfk_1` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `consumption_purchases_ibfk_2` FOREIGN KEY (`package_id`) REFERENCES `business_packages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `consumption_purchases`
--

LOCK TABLES `consumption_purchases` WRITE;
/*!40000 ALTER TABLE `consumption_purchases` DISABLE KEYS */;
INSERT INTO `consumption_purchases` VALUES (1,9,4,NULL,50,50,19.99,'completed','2025-09-09 22:20:27',NULL),(2,9,5,NULL,150,140,49.99,'completed','2025-09-09 22:20:32',NULL),(3,14,4,NULL,50,0,19.99,'completed','2025-09-09 23:02:20',NULL);
/*!40000 ALTER TABLE `consumption_purchases` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `credit_usage`
--

DROP TABLE IF EXISTS `credit_usage`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `credit_usage` (
  `id` int NOT NULL AUTO_INCREMENT,
  `business_id` int NOT NULL,
  `purchase_id` int NOT NULL,
  `service_type` enum('promotion','feature_boost','priority_listing','analytics') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `service_description` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `credits_used` int NOT NULL,
  `used_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `business_id` (`business_id`),
  KEY `purchase_id` (`purchase_id`),
  CONSTRAINT `credit_usage_ibfk_1` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `credit_usage_ibfk_2` FOREIGN KEY (`purchase_id`) REFERENCES `consumption_purchases` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `credit_usage`
--

LOCK TABLES `credit_usage` WRITE;
/*!40000 ALTER TABLE `credit_usage` DISABLE KEYS */;
INSERT INTO `credit_usage` VALUES (1,14,3,'promotion','Riduzione manuale da admin: gestione social',1,'2025-09-09 23:18:01'),(2,14,3,'promotion','Riduzione manuale da admin: set',49,'2025-09-09 23:18:56'),(3,9,2,'promotion','Riduzione manuale da admin: test prova',10,'2025-09-11 07:19:16');
/*!40000 ALTER TABLE `credit_usage` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `events`
--

DROP TABLE IF EXISTS `events`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `events` (
  `id` int NOT NULL AUTO_INCREMENT,
  `titolo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `nomeAttivita` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `descrizione` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `categoria` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provincia` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `citta` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `dataEvento` datetime NOT NULL,
  `orarioInizio` time DEFAULT NULL,
  `costoIngresso` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Gratuito',
  `imageUrl` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `linkMappaGoogle` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `linkPreviewMappaEmbed` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `linkContattoPrenotazioni` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_data_evento` (`dataEvento`),
  KEY `idx_citta` (`citta`),
  KEY `idx_provincia` (`provincia`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `events`
--

LOCK TABLES `events` WRITE;
/*!40000 ALTER TABLE `events` DISABLE KEYS */;
/*!40000 ALTER TABLE `events` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `home_sections`
--

DROP TABLE IF EXISTS `home_sections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `home_sections` (
  `id` int NOT NULL AUTO_INCREMENT,
  `section_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subtitle` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `image_path` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_visible` tinyint DEFAULT '1',
  `sort_order` int DEFAULT '0',
  `custom_data` json DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `section_name` (`section_name`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `home_sections`
--

LOCK TABLES `home_sections` WRITE;
/*!40000 ALTER TABLE `home_sections` DISABLE KEYS */;
INSERT INTO `home_sections` VALUES (1,'hero','Esplora la Calabria','Mare cristallino e storia millenaria','Immergiti nella bellezza della Calabria','/uploads/1761041086_arco magno san nicola arcella.jpg',1,1,'{\"button1_link\": \"categorie.php\", \"button1_text\": \"Scopri la Calabria\", \"button2_link\": \"mappa.php\", \"button2_text\": \"Visualizza Mappa\"}','2025-10-21 10:04:49'),(2,'categories','Esplora per Categoria','','Scopri la Calabria attraverso le sue diverse sfaccettature','',1,2,NULL,'2025-10-10 10:26:57'),(3,'provinces','Esplora le Province','','Ogni provincia calabrese custodisce tesori unici','',1,3,NULL,'2025-10-10 10:26:57'),(4,'map','Mappa Interattiva','','Naviga attraverso la Calabria con la nostra mappa interattiva','',1,4,NULL,'2025-10-10 10:26:57'),(5,'cta','Vuoi far Conoscere la Tua Calabria?','','Unisciti alla nostra community!','',1,5,NULL,'2025-10-10 10:26:57'),(6,'newsletter','Resta Connesso con la Calabria','','Iscriviti alla nostra newsletter','',1,6,NULL,'2025-10-10 10:26:57');
/*!40000 ALTER TABLE `home_sections` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `newsletter_subscribers`
--

DROP TABLE IF EXISTS `newsletter_subscribers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `newsletter_subscribers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `interests` json DEFAULT NULL,
  `status` enum('active','confirmed','unsubscribed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `confirmation_token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `confirmed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `newsletter_subscribers`
--

LOCK TABLES `newsletter_subscribers` WRITE;
/*!40000 ALTER TABLE `newsletter_subscribers` DISABLE KEYS */;
/*!40000 ALTER TABLE `newsletter_subscribers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `place_suggestions`
--

DROP TABLE IF EXISTS `place_suggestions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `place_suggestions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `category_id` int DEFAULT NULL,
  `province_id` int DEFAULT NULL,
  `city_id` int DEFAULT NULL,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `suggested_by_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `suggested_by_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `images` json DEFAULT NULL,
  `status` enum('pending','approved','rejected') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `admin_notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  KEY `province_id` (`province_id`),
  KEY `city_id` (`city_id`),
  CONSTRAINT `place_suggestions_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `place_suggestions_ibfk_2` FOREIGN KEY (`province_id`) REFERENCES `provinces` (`id`) ON DELETE SET NULL,
  CONSTRAINT `place_suggestions_ibfk_3` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `place_suggestions`
--

LOCK TABLES `place_suggestions` WRITE;
/*!40000 ALTER TABLE `place_suggestions` DISABLE KEYS */;
INSERT INTO `place_suggestions` VALUES (1,'sgsdfds','sdgsdgsdgsdgsdgsdgsd',NULL,NULL,NULL,'sdgsdg',NULL,NULL,'sdgsdg','sdgsdg@gmail.com','[\"uploads/suggestions/suggestion_68e27e73226f97.61240514.png\"]','approved','','2025-10-05 14:19:31'),(2,'dsafsdfdsaf','sdfsdfsdfw44r23rfsdafadfq3efasdf',NULL,NULL,NULL,'sdfsdfsdfsdf',NULL,NULL,'sedfsdfr234312','dsafadfaw@gmail.com','[\"uploads/suggestions/img_68e385fac1068_1759741434.webp\", \"uploads/suggestions/img_68e385fac86ed_1759741434.webp\", \"uploads/suggestions/img_68e385fb1501b_1759741435.webp\"]','pending',NULL,'2025-10-06 09:03:55');
/*!40000 ALTER TABLE `place_suggestions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `provinces`
--

DROP TABLE IF EXISTS `provinces`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `provinces` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `image_path` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `provinces`
--

LOCK TABLES `provinces` WRITE;
/*!40000 ALTER TABLE `provinces` DISABLE KEYS */;
INSERT INTO `provinces` VALUES (1,'Catanzaro','Capoluogo di regione, cuore della Calabria tra due mari','provinces/img_68ecb3295340d_1760342825.webp','2025-09-08 20:04:52'),(2,'Cosenza','La provincia più estesa, ricca di storia e natura','provinces/img_68e8d3927e853_1760088978.webp','2025-09-08 20:04:52'),(3,'Crotone','Terra di Pitagora, tra mare cristallino e archeologia',NULL,'2025-09-08 20:04:52'),(4,'Reggio Calabria','La punta dello stivale, affacciata sullo Stretto di Messina',NULL,'2025-09-08 20:04:52'),(5,'Vibo Valentia','Piccola provincia ricca di tradizioni marinare e gastronomiche',NULL,'2025-09-08 20:04:52');
/*!40000 ALTER TABLE `provinces` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'text',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `key` (`key`)
) ENGINE=InnoDB AUTO_INCREMENT=163 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES (1,'site_name','Passione Calabria','text','2025-09-08 20:04:52','2025-10-11 15:39:39'),(2,'site_description','La tua guida alla Calabria','text','2025-09-08 20:04:52','2025-10-11 15:39:39'),(3,'contact_email','info@passionecalabria.it','text','2025-09-08 20:04:52','2025-10-11 15:39:39'),(4,'contact_phone','+39 3345075668','text','2025-09-08 20:04:52','2025-10-11 15:39:39'),(5,'google_recaptcha_v2_site_key','','text','2025-09-08 20:04:52','2025-10-11 15:39:39'),(6,'google_recaptcha_v2_secret_key','','text','2025-09-08 20:04:52','2025-10-11 15:39:39'),(7,'google_recaptcha_v3_site_key','','text','2025-09-08 20:04:52','2025-10-11 15:39:39'),(8,'google_recaptcha_v3_secret_key','','text','2025-09-08 20:04:52','2025-10-11 15:39:39'),(9,'stripe_publishable_key','pk_live_51P8KYNGE0MBrmT2i9aqtdtH479U8JnoOU23H6fWhN7sX00BImp8BKblJ2CnXGr5Num85b6rAp2MogukGqsMxHXAY00wQpLCF7Y','text','2025-09-08 20:04:52','2025-10-11 15:39:39'),(10,'stripe_secret_key','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','text','2025-09-08 20:04:52','2025-10-11 15:39:39'),(11,'google_analytics_id','','text','2025-09-08 20:04:52','2025-10-11 15:39:39'),(12,'app_store_link','','text','2025-09-08 20:04:52','2025-10-11 15:39:39'),(13,'app_store_image','','text','2025-09-08 20:04:52','2025-10-11 15:39:39'),(14,'play_store_link','','text','2025-09-08 20:04:52','2025-10-11 15:39:39'),(15,'play_store_image','','text','2025-09-08 20:04:52','2025-10-11 15:39:39'),(16,'vai_app_link','https://evento.passionecalabria.it/','text','2025-09-08 20:04:52','2025-10-11 15:39:39'),(17,'suggerisci_evento_link','https://prova.passionecalabria.it/suggerisci-evento.php','text','2025-09-08 20:04:52','2025-10-11 15:39:39'),(18,'hero_title','Esplora la Calabria','text','2025-09-08 20:04:52','2025-10-11 15:39:39'),(19,'hero_subtitle','Mare cristallino e storia millenaria','text','2025-09-08 20:04:52','2025-10-11 15:39:39'),(20,'hero_description','Immergiti nella bellezza della Calabria, con le sue spiagge da sogno, il centro storico affascinante e i panorami mozzafiato dalla rupe.','text','2025-09-08 20:04:52','2025-10-11 15:39:39'),(21,'hero_image','/placeholder-hero.jpg','text','2025-09-08 20:04:52','2025-10-11 15:39:39'),(22,'business_registration_enabled','1','text','2025-09-08 20:04:52','2025-10-11 15:39:39'),(23,'business_auto_approval','0','text','2025-09-08 20:04:52','2025-10-11 15:39:39'),(24,'max_free_businesses_per_email','1','text','2025-09-08 20:04:52','2025-10-11 15:39:39'),(25,'maintenance_enabled','0','text','2025-09-21 09:53:59','2025-10-21 19:22:03'),(26,'maintenance_message','Sito in manutenzione. Torneremo presto!','text','2025-09-21 09:53:59','2025-10-21 19:22:03'),(99,'tinymce_api_key','ppt10hdahm53zes2rpge01lv9xvw5vnbplrjhasi0bed1v9h','text','2025-10-11 15:32:09','2025-10-11 15:39:39'),(154,'logoAppUrl','uploads_protected/logo/app-logo.png','text','2025-10-16 20:46:31','2025-10-16 20:46:31'),(155,'linkInstagram','','text','2025-10-16 20:46:31','2025-10-16 20:46:31'),(156,'linkFacebook','','text','2025-10-16 20:46:31','2025-10-16 20:46:31'),(157,'linkSitoWeb','','text','2025-10-16 20:46:31','2025-10-16 20:46:31'),(158,'linkIscriviAttivita','','text','2025-10-16 20:46:31','2025-10-16 20:46:31');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `static_pages`
--

DROP TABLE IF EXISTS `static_pages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `static_pages` (
  `id` int NOT NULL AUTO_INCREMENT,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `meta_title` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_published` tinyint DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `static_pages`
--

LOCK TABLES `static_pages` WRITE;
/*!40000 ALTER TABLE `static_pages` DISABLE KEYS */;
INSERT INTO `static_pages` VALUES (1,'chi-siamo','Chi Siamo','<h1>Chi Siamo</h1><p>Benvenuti in Passione Calabria, il portale dedicato alla scoperta di una delle regioni più affascinanti d\'Italia.</p><p>La nostra missione è far conoscere la vera essenza della Calabria: dalle spiagge cristalline della Costa degli Dei ai borghi medievali dell\'entroterra, dalle tradizioni gastronomiche millenarie alle meraviglie naturali dei parchi nazionali.</p>','Chi Siamo - Passione Calabria','Scopri chi siamo e la nostra missione per promuovere la bellezza e le tradizioni della Calabria.',1,'2025-09-08 20:04:52','2025-10-10 10:26:57'),(2,'privacy-policy','Privacy Policy','<h1>Privacy Policy</h1><p>Questa privacy policy descrive come raccogliamo, utilizziamo e proteggiamo le tue informazioni personali.</p><h2>Raccolta delle Informazioni</h2><p>Raccogliamo informazioni quando ti registri al nostro sito, ti iscrivi alla newsletter o compili un modulo.</p><h2>Utilizzo delle Informazioni</h2><p>Le informazioni raccolte vengono utilizzate per migliorare l\'esperienza utente e fornire servizi personalizzati.</p>','Privacy Policy - Passione Calabria','La nostra politica sulla privacy e protezione dei dati personali.',1,'2025-09-08 20:04:52','2025-10-10 10:26:57'),(3,'termini-servizio','Termini di Servizio','<h1>Termini di Servizio</h1><p>Questi termini e condizioni governano il tuo uso del nostro sito web e servizi.</p><h2>Accettazione dei Termini</h2><p>Utilizzando il nostro sito, accetti di essere vincolato da questi termini di servizio.</p><h2>Servizi per le Attività</h2><p>Le attività commerciali possono registrarsi e gestire la propria presenza attraverso la nostra piattaforma.</p>','Termini di Servizio - Passione Calabria','I termini e condizioni per l\'utilizzo del nostro sito web e servizi.',1,'2025-09-08 20:04:52','2025-10-10 10:26:57'),(4,'contatti','Contatti','<h1>Contatti</h1><p>Siamo sempre felici di sentire da voi! Ecco come potete raggiungerci:</p><h2>Informazioni di Contatto</h2><p><strong>Email:</strong> info@passionecalabria.it</p><p><strong>Telefono:</strong> +39 XXX XXX XXXX</p><p><strong>Indirizzo:</strong> Via Roma, 123 - 88100 Catanzaro (CZ)</p><h2>Per le Attività</h2><p>Per supporto tecnico o commerciale relativo alla registrazione delle attività, contattaci all\'indirizzo: business@passionecalabria.it</p>','Contatti - Passione Calabria','Come contattarci per informazioni, collaborazioni o segnalazioni.',1,'2025-09-08 20:04:52','2025-10-10 10:26:57'),(5,'cookie-policy','Cookie Policy','<h1>Cookie Policy</h1><p>Questo sito utilizza cookies per migliorare la tua esperienza di navigazione.</p><h2>Cosa sono i Cookies</h2><p>I cookies sono piccoli file di testo che vengono memorizzati sul tuo dispositivo quando visiti un sito web.</p><h2>Tipologie di Cookies</h2><p>Utilizziamo cookies tecnici per il funzionamento del sito e cookies di analisi per migliorare i nostri servizi.</p>','Cookie Policy - Passione Calabria','La nostra politica sui cookies e come li utilizziamo.',1,'2025-09-08 20:04:52','2025-10-10 10:26:57');
/*!40000 ALTER TABLE `static_pages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subscriptions`
--

DROP TABLE IF EXISTS `subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `subscriptions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `business_id` int NOT NULL,
  `package_id` int NOT NULL,
  `stripe_subscription_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pending','active','cancelled','expired') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `auto_renew` tinyint DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `business_id` (`business_id`),
  KEY `package_id` (`package_id`),
  KEY `status` (`status`),
  CONSTRAINT `subscriptions_ibfk_1` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE,
  CONSTRAINT `subscriptions_ibfk_2` FOREIGN KEY (`package_id`) REFERENCES `business_packages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subscriptions`
--

LOCK TABLES `subscriptions` WRITE;
/*!40000 ALTER TABLE `subscriptions` DISABLE KEYS */;
INSERT INTO `subscriptions` VALUES (1,1,1,NULL,'active','2025-09-08 22:14:22','2026-09-08 22:14:22',0.00,1,'2025-09-08 20:14:22','2025-10-10 10:26:57'),(2,3,1,NULL,'active','2025-09-08 22:22:16','2026-09-08 22:22:16',0.00,1,'2025-09-08 20:22:16','2025-10-10 10:26:57'),(3,5,1,NULL,'active','2025-09-08 22:44:54','2026-09-08 22:44:54',0.00,1,'2025-09-08 20:44:54','2025-10-10 10:26:57'),(4,6,1,NULL,'active','2025-09-08 23:55:14','2026-09-08 23:55:14',0.00,1,'2025-09-08 21:55:14','2025-10-10 10:26:57'),(5,7,1,NULL,'active','2025-09-09 00:05:29','2026-09-09 00:05:29',0.00,1,'2025-09-08 22:05:29','2025-10-10 10:26:57'),(6,8,1,NULL,'active','2025-09-09 00:17:06','2026-09-09 00:17:06',0.00,1,'2025-09-08 22:17:06','2025-10-10 10:26:57'),(7,9,1,NULL,'cancelled','2025-09-09 00:45:04','2026-09-09 00:45:04',0.00,1,'2025-09-08 22:45:04','2025-10-10 10:26:57'),(8,10,1,NULL,'active','2025-09-09 23:41:19','2026-09-09 23:41:19',0.00,1,'2025-09-09 21:41:19','2025-10-10 10:26:57'),(9,11,1,NULL,'cancelled','2025-09-09 23:47:14','2026-09-09 23:47:14',0.00,1,'2025-09-09 21:47:14','2025-10-10 10:26:57'),(10,11,2,NULL,'cancelled','2025-09-09 23:59:00','2026-09-09 23:59:00',NULL,1,'2025-09-09 21:59:00','2025-10-10 10:26:57'),(11,11,1,NULL,'cancelled','2025-09-09 23:59:09','2026-09-09 23:59:09',NULL,1,'2025-09-09 21:59:09','2025-10-10 10:26:57'),(12,11,1,NULL,'active','2025-09-10 00:03:15','2026-09-10 00:03:15',NULL,1,'2025-09-09 22:03:15','2025-10-10 10:26:57'),(13,9,2,NULL,'cancelled','2025-09-10 00:46:00','2026-09-10 00:46:00',NULL,1,'2025-09-09 22:46:00','2025-10-10 10:26:57'),(14,14,2,NULL,'expired','2025-09-10 01:01:40','2026-09-10 01:01:40',29.99,1,'2025-09-09 23:01:40','2025-10-10 10:26:57'),(15,9,1,NULL,'active','2025-09-10 10:31:25','2026-09-10 10:31:25',NULL,1,'2025-09-10 08:31:25','2025-10-10 10:26:57'),(16,15,1,NULL,'active','2025-10-01 01:12:59','2026-10-01 01:12:59',0.00,1,'2025-09-30 23:12:59','2025-10-10 10:26:57'),(17,16,1,NULL,'active','2025-10-01 23:34:03','2026-10-01 23:34:03',0.00,1,'2025-10-01 21:34:03','2025-10-10 10:26:57'),(18,20,1,NULL,'active','2025-10-08 08:03:32','2026-10-08 08:03:32',0.00,1,'2025-10-08 06:03:32','2025-10-10 10:26:57');
/*!40000 ALTER TABLE `subscriptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_uploads`
--

DROP TABLE IF EXISTS `user_uploads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_uploads` (
  `id` int NOT NULL AUTO_INCREMENT,
  `article_id` int DEFAULT NULL,
  `city_id` int DEFAULT NULL,
  `user_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `image_path` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `original_filename` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` enum('pending','approved','rejected') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `admin_notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `article_id` (`article_id`),
  KEY `idx_city_id` (`city_id`),
  CONSTRAINT `fk_user_uploads_city_id` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_uploads_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_uploads`
--

LOCK TABLES `user_uploads` WRITE;
/*!40000 ALTER TABLE `user_uploads` DISABLE KEYS */;
INSERT INTO `user_uploads` VALUES (1,NULL,7,'rsdgrg','gwrsgwesg@gmail.com','uploads/user_photos/68d1a471d3fe0_1758569585.jpg','IMG_1793.jpg','asfeqfqefwefrwedfcasd','approved',NULL,'2025-09-22 19:33:05'),(2,NULL,7,'asfasfae12','321412@gmail.com','uploads/user_photos/68d1a4c43532e_1758569668.jpg','praia di focu.jpg','wefwdfsr234124','approved',NULL,'2025-09-22 19:34:28'),(3,NULL,11,'aefadfadf','ar32qr4awsd@gmail.com','uploads/user_photos/68d1c33349f1c_1758577459.jpg','agrilao stemma 2.jpg','afsadrw3  5321reafaw4ar','approved',NULL,'2025-09-22 21:44:19'),(4,NULL,11,'pino','cavallo@gmail.com','user_photos/img_68e8f7d603252_1760098262.webp','woman-567021-1280.webp','','approved',NULL,'2025-10-10 12:11:02');
/*!40000 ALTER TABLE `user_uploads` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `first_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('user','admin','editor','business') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'user',
  `status` enum('active','inactive','banned') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `avatar` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `role` (`role`),
  KEY `status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin@passionecalabria.it','$2y$10$example.hash.here',NULL,NULL,'Amministratore','admin','active',NULL,NULL,'2025-09-08 20:04:52'),(2,'asfasf@gmail.com','$2y$10$GmM9NZwflHNDZiqdq9PTSO/qG2.JysiXvmIzYkJfFUZ29t8pE5GUe',NULL,NULL,'aasfdasfasfas','business','active',NULL,'2025-10-20 15:53:58','2025-09-08 20:44:54'),(3,'3254325235@gmail.com','$2y$10$TolyJmigfv.egAf/a1t.4.I.WTI9fI9KNBngrAP2vGi1Zmo.f1AgC',NULL,NULL,'afafafasf','business','active',NULL,NULL,'2025-09-08 21:55:14'),(4,'testuser_68bf53296a925@example.com','$2y$10$uhYDNx5oHJydGwJVxZL4TunswdoQ3oz5qGgWuC6G555LjOiUG.i8e',NULL,NULL,'Attività di Test 2025-09-09 00:05:29','user','active',NULL,NULL,'2025-09-08 22:05:29'),(5,'ascfrtgde@gmail.com','$2y$10$yHPwoj09lbMR9BYEDnMp1.3TJ94W8INs9spzoRqrdS2QWoZuNDeOi',NULL,NULL,'awsfwqafrwqf','business','active',NULL,NULL,'2025-09-08 22:17:06'),(6,'rehth435@gmail.com','$2y$10$Fa4u7WXlWz5NzosPj8r6FuCWHslEWbM2aoLZqg.Kww9qLaDcAIY4a',NULL,NULL,'edfsdfsdf','admin','active',NULL,'2025-10-20 10:37:40','2025-09-08 22:45:04'),(7,'asfasfwq@gmail.com','$2y$10$YX5o5vZ38mMf7pWEV6wVJ.nfqZe3lzsvEORhMc984KeTT2OuQm5py',NULL,NULL,'asfasfasfaf','business','active',NULL,NULL,'2025-09-09 21:41:19'),(8,'bambolo@gmail.com','$2y$10$DFyhgRNh2bdX7j8cOIR40OJbm/r6qGS80hHTM40hxE9J2Ui1PJdNS',NULL,NULL,'afasfasf','business','active',NULL,NULL,'2025-09-09 21:47:14'),(9,'wrgw324asdf@gmail.com','$2y$10$tvUQVihPg/P2bIz7xTQzKuwAZ8zfQQokPWWb2Fj3wY5Q98XKWSplO',NULL,NULL,'sdfsdfsdf','business','active',NULL,NULL,'2025-09-09 22:47:35'),(10,'124124124@gmail.com','$2y$10$W3QIxiLO3ZDt8YMYa.E3PuvzysdlghRjvIyRUFTXdvpwF/KeRXfH6',NULL,NULL,'3123r3412','business','active',NULL,NULL,'2025-09-09 22:48:20'),(11,'giggiolino@gmail.com','$2y$10$/xlBk5cGoJPpbA8xL3u54OVbDqd0EuGhChnDyvVudaO5TG9.Smj9W',NULL,NULL,'andrea','business','active',NULL,'2025-09-09 23:18:24','2025-09-09 23:01:33'),(12,'werw43324@gmail.com','$2y$10$tvtcWp8cIQcm8Dv2RKd11uzPXuw7Sy/gBTtsGoL/2uFnqsVDcvZxm',NULL,NULL,'ewrwerwer','business','active',NULL,NULL,'2025-09-30 23:12:59'),(13,'asdase12qe@gmail.com','$2y$10$SjWIeX1gfUpDLEvt6EQlGO4AuSln7OGDL/URzNZWwAlls/A6UlS4a',NULL,NULL,'sadasdasd','business','active',NULL,NULL,'2025-10-01 21:34:03'),(15,'cavaliere.andrea69@gmail.com','$2y$10$ps/CymDWo74TofkXJN0g3.CwzP8hnYm2vhaVcKs9Flxel3fFh5F5K',NULL,NULL,'1111111','business','active',NULL,'2025-10-03 14:50:18','2025-10-03 14:47:20'),(17,'info@passionecalabria.it','$2y$10$94g6CdwPoAAeKBn6zJULZ.X8baUbmNc3zm3psrAxepbnZDHuvP1Iy',NULL,NULL,'passione calabria','admin','active',NULL,'2025-10-22 14:37:14','2025-10-08 06:03:32');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `visitor_stats`
--

DROP TABLE IF EXISTS `visitor_stats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `visitor_stats` (
  `stat_date` date NOT NULL,
  `daily_visits` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`stat_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `visitor_stats`
--

LOCK TABLES `visitor_stats` WRITE;
/*!40000 ALTER TABLE `visitor_stats` DISABLE KEYS */;
/*!40000 ALTER TABLE `visitor_stats` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-10-22 17:15:38
