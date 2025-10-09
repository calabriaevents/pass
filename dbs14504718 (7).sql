-- phpMyAdmin SQL Dump
-- version 4.9.11
-- https://www.phpmyadmin.net/
--
-- Host: db5018301966.hosting-data.io
-- Creato il: Set 30, 2025 alle 21:11
-- Versione del server: 8.0.36
-- Versione PHP: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dbs14504718`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `articles`
--

CREATE TABLE `articles` (
  `id` int NOT NULL,
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
  `featured` tinyint(1) DEFAULT '0',
  `views` int DEFAULT '0',
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `allow_user_uploads` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `json_data` json DEFAULT NULL,
  `hero_image` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `articles`
--

INSERT INTO `articles` (`id`, `title`, `slug`, `content`, `excerpt`, `featured_image`, `gallery_images`, `category_id`, `province_id`, `city_id`, `author`, `status`, `featured`, `views`, `latitude`, `longitude`, `allow_user_uploads`, `created_at`, `json_data`, `hero_image`, `logo`) VALUES
(3, 'La \'Nduja: Il Piccante Orgoglio di Spilinga', 'la-nduja-il-piccante-orgoglio-di-spilinga', 'La \'nduja è un salume piccante spalmabile originario di Spilinga, piccolo borgo in provincia di Vibo Valentia. Questa prelibatezza, ottenuta da carni suine e peperoncino calabrese, rappresenta l\'essenza della tradizione gastronomica locale. La sua preparazione segue ancora oggi ricette tramandate di generazione in generazione, utilizzando solo ingredienti locali di altissima qualità.', 'La \'nduja di Spilinga, salume piccante simbolo della gastronomia calabrese.', NULL, NULL, 3, 5, NULL, 'Giuseppe Calabrese', 'published', 1, 0, '38.65000000', '15.90000000', 1, '2025-09-08 20:04:52', NULL, NULL, NULL),
(4, 'Tropea: La Perla del Tirreno', 'tropea-la-perla-del-tirreno', 'Tropea è universalmente riconosciuta come una delle località balneari più belle d\'Italia. Arroccata su un promontorio a strapiombo sul mare, offre uno dei panorami più suggestivi della Calabria. Le sue spiagge di sabbia bianca, bagnate da acque cristalline, e il centro storico ricco di chiese e palazzi nobiliari, fanno di Tropea una meta imperdibile per chi visita la regione.', 'Tropea, perla del Tirreno con spiagge da sogno e centro storico mozzafiato.', NULL, NULL, 4, 5, 14, 'Maria Costantino', 'published', 1, 1, '38.67730000', '15.89840000', 1, '2025-09-08 20:04:52', NULL, NULL, NULL),
(6, 'asfasf', 'asfasf', 'asfasf', '', NULL, '[\"uploads/articles/gallery_68cfb42c41988.png\", \"uploads/articles/gallery_68cfb42c419dc.png\", \"uploads/articles/gallery_68cfb42c41a5d.png\", \"uploads/articles/gallery_68cfb42c41ad8.png\", \"uploads/articles/gallery_68cfb42c41b20.png\"]', 27, 2, 7, 'Admin', 'published', 0, 11, NULL, NULL, 1, '2025-09-21 08:15:40', NULL, NULL, NULL),
(7, 'aaaaaaaaaaaaaaa', 'aaaaaaaaaaaaaaa', 'aaaaaaaaaaaaaaaaaaaaaaa', '', NULL, '[\"uploads/articles/gallery_68d443e78c873.jpg\", \"uploads/articles/gallery_68d443e78c8c8.jpg\", \"uploads/articles/gallery_68d443e78c91f.jpg\", \"uploads/articles/gallery_68d443e78c96a.jpg\", \"uploads/articles/gallery_68d443e78c9be.jpg\"]', 26, 1, 7, 'Admin', 'published', 0, 4, NULL, NULL, 1, '2025-09-24 19:17:59', NULL, NULL, NULL),
(8, 'vbbbbbbbbbbbbbbbb', 'vbbbbbbbbbbbbbbbb', 'bbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbbb', '', NULL, '[\"uploads/articles/gallery_68d478181098f.jpg\", \"uploads/articles/gallery_68d47818117f7.jpg\", \"uploads/articles/gallery_68d478181269f.jpg\", \"uploads/articles/gallery_68d4781813598.jpg\", \"uploads/articles/gallery_68d4781813f8d.jpg\", \"uploads/articles/gallery_68d4781814dd8.jpg\"]', 26, 2, 7, 'Admin', 'published', 0, 6, NULL, NULL, 1, '2025-09-24 19:52:20', NULL, NULL, NULL),
(9, 'sfdsadasdsad', 'sfdsadasdsad', 'dsfasdfasfascasc', '', NULL, '[\"uploads/articles/gallery_68d57ec8169a2.jpg\", \"uploads/articles/gallery_68d57ec816c47.jpg\", \"uploads/articles/gallery_68d57ec816ce6.jpg\", \"uploads/articles/gallery_68d57ec816e67.jpg\", \"uploads/articles/gallery_68d57ec8170dd.jpg\", \"uploads/articles/gallery_68d57ec8182ef.jpg\"]', 26, 2, 7, 'Admin', 'published', 0, 2, NULL, NULL, 1, '2025-09-25 17:41:28', NULL, NULL, NULL),
(11, 'adfasdfadfasf', 'adfasdfadfasf', 'afafasfasfasf', '', NULL, '[\"uploads/articles/gallery_68d59ddf059bb.jpg\", \"uploads/articles/gallery_68d59ddf068b7.jpg\", \"uploads/articles/gallery_68d59ddf07895.jpg\", \"uploads/articles/gallery_68d59ddf08ab8.jpg\", \"uploads/articles/gallery_68d59ddf097f2.jpg\", \"uploads/articles/gallery_68d59ddf0a7ed.jpg\"]', 26, 2, 7, 'Admin', 'published', 0, 3, NULL, NULL, 1, '2025-09-25 19:54:07', '{\"address\": \"asfasfaf\", \"services\": {\"custom\": \"asfasf\", \"predefined\": [\"Massaggi\", \"Bagno Turco\"]}, \"maps_link\": \"\", \"treatments\": \"asfasfasf\", \"price_range\": \"asfasfaf\", \"opening_hours\": \"asfasfasf\", \"contact_details\": {\"email\": \"afasfaf@gmail.com\", \"phone\": \"124124124\"}}', 'uploads/articles/hero_68d59ddf045af.jpg', 'uploads/articles/logo_68d59ddf05809.jpg'),
(12, '12123123', '12123123', '123123123234', '', NULL, '[\"uploads/articles/gallery_68d5a9d0bcc0f.jpg\", \"uploads/articles/gallery_68d5a9d0bdab6.jpg\", \"uploads/articles/gallery_68d5a9d0beb22.jpg\", \"uploads/articles/gallery_68d5a9d0bfc70.jpg\"]', 26, 2, 7, 'Admin', 'published', 0, 2, NULL, NULL, 1, '2025-09-25 20:45:04', NULL, NULL, NULL),
(14, '11111111111111', '11111111111111', '111111111111', '', NULL, '[\"uploads/articles/gallery_68d5bb49315a4.jpg\", \"uploads/articles/gallery_68d5bb49324f4.jpg\", \"uploads/articles/gallery_68d5bb49334b3.jpg\", \"uploads/articles/gallery_68d5bb4934543.jpg\"]', 26, 2, 7, 'Admin', 'published', 0, 36, NULL, NULL, 1, '2025-09-25 21:59:37', '{\"address\": \"111111\", \"services\": {\"custom\": \"111111111\", \"predefined\": [\"Massaggi\", \"Bagno Turco\"]}, \"maps_link\": \"\", \"treatments\": \"111111111\", \"price_range\": \"111111111111\", \"opening_hours\": \"111111111111\", \"contact_details\": {\"email\": \"111111111@gmail.com\", \"phone\": \"11111111\"}}', 'uploads/articles/hero_68d5bb49310c6.jpg', 'uploads/articles/logo_68d5bb49313d2.jpg');

-- --------------------------------------------------------

--
-- Struttura della tabella `businesses`
--

CREATE TABLE `businesses` (
  `id` int NOT NULL,
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
  `verified` tinyint(1) DEFAULT '0',
  `featured` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `businesses`
--

INSERT INTO `businesses` (`id`, `name`, `email`, `phone`, `website`, `description`, `category_id`, `province_id`, `city_id`, `address`, `latitude`, `longitude`, `status`, `subscription_type`, `logo_path`, `cover_image_path`, `business_hours`, `social_links`, `verified`, `featured`, `created_at`) VALUES
(1, 'ca services di andrea cavaliere', 'info@caservices.it', '3345075668', '', 'attivita di pubblicita e sposorizzazioni', NULL, 2, NULL, 'via falcone borsellino 3', NULL, NULL, 'approved', 'free', NULL, NULL, NULL, NULL, 0, 0, '2025-09-08 20:14:22'),
(2, 'asdasd', 'aasdasd@gmail.com', '', '', 'asdsadas', NULL, 2, NULL, '', NULL, NULL, 'pending', 'basic', NULL, NULL, NULL, NULL, 0, 0, '2025-09-08 20:21:24'),
(3, 'asdsadsad', 'asdsad@gmail.com', '', '', 'adfadfadf', NULL, 1, NULL, '', NULL, NULL, 'approved', 'free', NULL, NULL, NULL, NULL, 0, 0, '2025-09-08 20:22:16'),
(4, 'asdasdasd', 'asdasd@gmail.com', '', '', 'asfsdfsdf', NULL, 2, NULL, '', NULL, NULL, 'pending', 'basic', NULL, NULL, NULL, NULL, 0, 0, '2025-09-08 20:23:10'),
(5, 'aasfdasfasfas', 'asfasf@gmail.com', '', '', 'asdasfasf', NULL, 1, NULL, '', NULL, NULL, 'approved', 'free', NULL, NULL, NULL, NULL, 0, 0, '2025-09-08 20:44:54'),
(6, 'afafafasf', '3254325235@gmail.com', '', '', 'asqw3rq3r', NULL, 4, NULL, '', NULL, NULL, 'approved', 'free', NULL, NULL, NULL, NULL, 0, 0, '2025-09-08 21:55:14'),
(7, 'Attività di Test 2025-09-09 00:05:29', 'testuser_68bf53296a925@example.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'approved', 'free', NULL, NULL, NULL, NULL, 0, 0, '2025-09-08 22:05:29'),
(8, 'awsfwqafrwqf', 'ascfrtgde@gmail.com', '', '', 'edfewf', NULL, 3, NULL, '', NULL, NULL, 'approved', 'free', NULL, NULL, NULL, NULL, 0, 0, '2025-09-08 22:17:06'),
(9, 'edfsdfsdf', 'rehth435@gmail.com', '', '', 'asfafaf', NULL, 2, NULL, '', NULL, NULL, 'approved', 'free', NULL, NULL, NULL, NULL, 0, 0, '2025-09-08 22:45:04'),
(10, 'asfasfasfaf', 'asfasfwq@gmail.com', '', '', 'asdfq3fasfe', NULL, 1, NULL, '', NULL, NULL, 'approved', 'free', NULL, NULL, NULL, NULL, 0, 0, '2025-09-09 21:41:19'),
(11, 'afasfasf', 'bambolo@gmail.com', '2431241235235', '', 'adfqerq341234sd', NULL, 2, NULL, '', NULL, NULL, 'approved', 'free', NULL, NULL, NULL, NULL, 0, 0, '2025-09-09 21:47:14'),
(12, 'sdfsdfsdf', 'wrgw324asdf@gmail.com', '', '', 'sdfsdf32r23r', NULL, 4, NULL, '', NULL, NULL, 'pending', 'basic', NULL, NULL, NULL, NULL, 0, 0, '2025-09-09 22:47:35'),
(13, '3123r3412', '124124124@gmail.com', '', '', 'wefsdfadf', NULL, 4, NULL, '', NULL, NULL, 'approved', 'basic', NULL, NULL, NULL, NULL, 0, 0, '2025-09-09 22:48:20'),
(14, 'andrea', 'giggiolino@gmail.com', '', '', 'daf3weredf', NULL, 2, NULL, '', NULL, NULL, 'approved', 'basic', NULL, NULL, NULL, NULL, 0, 0, '2025-09-09 23:01:33');

-- --------------------------------------------------------

--
-- Struttura della tabella `business_activity_log`
--

CREATE TABLE `business_activity_log` (
  `id` int NOT NULL,
  `business_id` int NOT NULL,
  `user_id` int DEFAULT NULL,
  `action` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `business_packages`
--

CREATE TABLE `business_packages` (
  `id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `price` decimal(10,2) NOT NULL,
  `package_type` enum('subscription','consumption') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'subscription',
  `duration_months` int DEFAULT '12',
  `consumption_credits` int DEFAULT NULL,
  `features` json DEFAULT NULL,
  `stripe_price_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_path` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1',
  `sort_order` int DEFAULT '0',
  `max_listings` int DEFAULT NULL,
  `max_photos` int DEFAULT NULL,
  `analytics_included` tinyint(1) DEFAULT '0',
  `priority_support` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `business_packages`
--

INSERT INTO `business_packages` (`id`, `name`, `description`, `price`, `package_type`, `duration_months`, `consumption_credits`, `features`, `stripe_price_id`, `image_path`, `is_active`, `sort_order`, `max_listings`, `max_photos`, `analytics_included`, `priority_support`, `created_at`) VALUES
(1, 'Gratuito', 'Inserimento base della tua attività', '0.00', 'subscription', 12, NULL, '[\"Scheda attività base\", \"Contatti e orari\", \"Visibilità nella ricerca\", \"1 foto principale\", \"Descrizione base\"]', NULL, NULL, 1, 1, 1, 1, 0, 0, '2025-09-08 20:04:52'),
(2, 'Business', 'Pacchetto completo per la tua attività', '29.99', 'subscription', 12, NULL, '[\"Tutto del piano Gratuito\", \"Foto illimitate\", \"Descrizione estesa\", \"Badge verificato\", \"Statistiche visualizzazioni\", \"Orari dettagliati\", \"Link social\", \"Supporto email\"]', NULL, NULL, 1, 2, 3, 10, 1, 0, '2025-09-08 20:04:52'),
(3, 'Premium', 'Massima visibilità e funzionalità avanzate', '59.99', 'subscription', 12, NULL, '[\"Tutto del piano Business\", \"Posizione privilegiata\", \"Articoli sponsorizzati\", \"Analytics avanzate\", \"Supporto prioritario\", \"Eventi promozionali\", \"Video gallery\", \"SEO avanzato\"]', NULL, NULL, 1, 3, 10, 50, 1, 1, '2025-09-08 20:04:52'),
(4, 'Pacchetto Boost Base', 'Crediti per promozioni e visibilità', '19.99', 'consumption', NULL, 50, '[\"50 crediti per promozioni\", \"Evidenzia la tua attività\", \"Boost temporaneo nelle ricerche\", \"Validità 6 mesi\"]', NULL, NULL, 1, 4, NULL, NULL, 0, 0, '2025-09-08 20:04:52'),
(5, 'Pacchetto Boost Pro', 'Più crediti per massima visibilità', '49.99', 'consumption', NULL, 150, '[\"150 crediti per promozioni\", \"Evidenzia la tua attività\", \"Boost temporaneo nelle ricerche\", \"Analytics premium\", \"Validità 12 mesi\"]', NULL, NULL, 1, 5, NULL, NULL, 1, 0, '2025-09-08 20:04:52'),
(6, 'Pacchetto Boost Enterprise', 'Crediti illimitati per grandi aziende', '99.99', 'consumption', NULL, 500, '[\"500 crediti per promozioni\", \"Evidenzia la tua attività\", \"Boost temporaneo nelle ricerche\", \"Analytics avanzate\", \"Supporto dedicato\", \"Validità 12 mesi\"]', NULL, NULL, 1, 6, NULL, NULL, 1, 1, '2025-09-08 20:04:52'),
(7, 'prova', 'sdafdsf\r\nasdfadf\r\nasfrgwe\r\n', '10.99', 'consumption', 12, 20, '\"\"', '', NULL, 1, 0, NULL, NULL, 0, 0, '2025-09-10 08:24:58'),
(8, 'fafqe3fgwsqaf', 'asfqaegfqe', '1000.00', 'subscription', 12, NULL, '[\"aaaaaaaaaaaaaaa\", \"aaaaaaaaaaaaaa\", \"aaaaaaaaaaaaa\"]', '', NULL, 1, 0, NULL, NULL, 0, 0, '2025-09-10 19:15:05');

-- --------------------------------------------------------

--
-- Struttura della tabella `business_sessions`
--

CREATE TABLE `business_sessions` (
  `id` int NOT NULL,
  `business_id` int NOT NULL,
  `user_id` int NOT NULL,
  `session_token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` timestamp NOT NULL,
  `last_activity` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `categories`
--

CREATE TABLE `categories` (
  `id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `icon` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `categories`
--

INSERT INTO `categories` (`id`, `name`, `description`, `icon`, `created_at`) VALUES
(3, 'Gastronomia', 'Assapora i sapori autentici della tradizione', '🍝', '2025-09-08 20:04:52'),
(4, 'Mare e Coste', 'Le più belle spiagge e località balneari', '🏖️', '2025-09-08 20:04:52'),
(14, 'Sport e Avventura', 'Attività sportive e outdoor', '🚴', '2025-09-08 20:04:52'),
(20, 'Hotel e Alloggi', 'Dove dormire in Calabria', '🏨', '2025-09-08 20:04:52'),
(24, 'Arte e Cultura', '', '', '2025-09-11 10:43:57'),
(25, 'Attività Sportive e Avventura', '', '', '2025-09-11 10:44:05'),
(26, ' Benessere e Relax', '', '', '2025-09-11 10:44:13'),
(27, 'Chiese e Santuari', '', '', '2025-09-11 10:44:31'),
(28, ' Itinerari Tematici', '', '', '2025-09-11 10:44:42'),
(29, 'Musei e Gallerie', '', '', '2025-09-11 10:44:50'),
(30, 'Parchi e Aree Verdi', '', '', '2025-09-11 10:44:59'),
(31, 'Patrimonio Storico', '', '', '2025-09-11 10:45:07'),
(32, 'Piazze e Vie Storiche', '', '', '2025-09-11 10:45:19'),
(33, 'Ristorazione', '', '', '2025-09-11 10:45:27'),
(34, 'Shopping e Artigianato', '', '', '2025-09-11 10:45:41'),
(35, 'Siti Archeologici', '', '', '2025-09-11 10:45:49'),
(37, 'Stabilimenti Balneari', '', '', '2025-09-11 10:46:04'),
(38, 'Teatri e Anfiteatri', '', '', '2025-09-11 10:46:12'),
(39, 'Tour e Guide', '', '', '2025-09-11 10:46:19'),
(40, 'Trasporti', '', '', '2025-09-11 10:46:27');

-- --------------------------------------------------------

--
-- Struttura della tabella `cities`
--

CREATE TABLE `cities` (
  `id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `province_id` int NOT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `hero_image` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Maps_link` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gallery_images` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `cities`
--

INSERT INTO `cities` (`id`, `name`, `province_id`, `latitude`, `longitude`, `description`, `hero_image`, `Maps_link`, `gallery_images`, `created_at`) VALUES
(1, 'Catanzaro', 1, '38.90980000', '16.59690000', 'Capoluogo di regione', NULL, NULL, NULL, '2025-09-08 20:04:52'),
(2, 'Lamezia Terme', 1, '38.96480000', '16.31290000', 'Importante centro della piana', NULL, NULL, NULL, '2025-09-08 20:04:52'),
(3, 'Soverato', 1, '38.69180000', '16.55130000', 'Perla dello Ionio', NULL, NULL, NULL, '2025-09-08 20:04:52'),
(4, 'Sellia Marina', 1, '38.81960000', '16.73390000', 'Località balneare ionica', NULL, NULL, NULL, '2025-09-08 20:04:52'),
(5, 'Cosenza', 2, '39.29480000', '16.25420000', 'Città dei Bruzi', NULL, NULL, NULL, '2025-09-08 20:04:52'),
(6, 'Rossano', 2, '39.57610000', '16.63140000', 'Città della liquirizia', NULL, NULL, NULL, '2025-09-08 20:04:52'),
(7, 'Paola', 2, '39.36560000', '16.03780000', 'Città di San Francesco', NULL, NULL, NULL, '2025-09-08 20:04:52'),
(8, 'Scalea', 2, '39.81470000', '15.79390000', 'Riviera dei Cedri', NULL, NULL, NULL, '2025-09-08 20:04:52'),
(9, 'Diamante', 2, '39.68270000', '15.82250000', 'Città del peperoncino', NULL, NULL, NULL, '2025-09-08 20:04:52'),
(10, 'Crotone', 3, '39.08470000', '17.12520000', 'Antica Kroton', NULL, NULL, NULL, '2025-09-08 20:04:52'),
(11, 'Cirò Marina', 3, '39.37260000', '17.12830000', 'Terra del vino Cirò', NULL, NULL, NULL, '2025-09-08 20:04:52'),
(12, 'Reggio Calabria', 4, '38.10980000', '15.65160000', 'Città dei Bronzi', NULL, NULL, NULL, '2025-09-08 20:04:52'),
(13, 'Scilla', 4, '38.24760000', '15.71720000', 'Borgo marinaro sullo Stretto', NULL, NULL, NULL, '2025-09-08 20:04:52'),
(14, 'Tropea', 5, '38.67730000', '15.89760000', 'Perla del Tirreno', NULL, NULL, NULL, '2025-09-08 20:04:52'),
(15, 'Vibo Valentia', 5, '38.67590000', '16.10180000', 'Antica Hipponion', NULL, NULL, NULL, '2025-09-08 20:04:52'),
(16, 'Pizzo', 5, '38.73470000', '16.15690000', 'Città del tartufo', NULL, NULL, NULL, '2025-09-08 20:04:52');

-- --------------------------------------------------------

--
-- Struttura della tabella `comments`
--

CREATE TABLE `comments` (
  `id` int NOT NULL,
  `article_id` int DEFAULT NULL,
  `city_id` int DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `author_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `author_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `rating` int DEFAULT NULL,
  `status` enum('pending','approved','rejected','spam') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `comments`
--

INSERT INTO `comments` (`id`, `article_id`, `city_id`, `user_id`, `author_name`, `author_email`, `content`, `rating`, `status`, `created_at`) VALUES
(1, NULL, 7, NULL, 'dfsdf234124', 'asfq3412@gmail.com', 'dsafsdfg234qasf', 4, 'approved', '2025-09-22 19:35:10'),
(2, 11, NULL, NULL, 'andrea', 'foignjsd@gmail.com', 'safasfasfa', 4, 'approved', '2025-09-25 20:41:32'),
(3, 14, NULL, NULL, 'vxcvxcv', 'cxvxcvxcv@gmail.com', 'xzcsafdcc', 4, 'approved', '2025-09-25 22:48:23'),
(4, 14, NULL, NULL, 'asdsadasda', 'sdasddas@gamail.com', 'asdsadda', 4, 'approved', '2025-09-29 22:21:08'),
(5, 14, NULL, NULL, '56436t254', '2452352@gmail.com', '35235235', 4, 'approved', '2025-09-29 22:30:17');

-- --------------------------------------------------------

--
-- Struttura della tabella `comuni`
--

CREATE TABLE `comuni` (
  `id` int NOT NULL,
  `nome` varchar(255) NOT NULL,
  `provincia` varchar(255) NOT NULL,
  `importo_pagato` decimal(10,2) NOT NULL,
  `data_pagamento` date NOT NULL,
  `data_scadenza` date NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dump dei dati per la tabella `comuni`
--

INSERT INTO `comuni` (`id`, `nome`, `provincia`, `importo_pagato`, `data_pagamento`, `data_scadenza`, `created_at`) VALUES
(2, 'paola', 'cosenza', '1500.00', '2025-09-24', '2026-09-24', '2025-09-22 23:04:35');

-- --------------------------------------------------------

--
-- Struttura della tabella `consumption_purchases`
--

CREATE TABLE `consumption_purchases` (
  `id` int NOT NULL,
  `business_id` int NOT NULL,
  `package_id` int NOT NULL,
  `stripe_payment_intent_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `credits_purchased` int NOT NULL,
  `credits_remaining` int NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL,
  `status` enum('pending','completed','cancelled','refunded') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `purchased_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `consumption_purchases`
--

INSERT INTO `consumption_purchases` (`id`, `business_id`, `package_id`, `stripe_payment_intent_id`, `credits_purchased`, `credits_remaining`, `amount_paid`, `status`, `purchased_at`, `expires_at`) VALUES
(1, 9, 4, NULL, 50, 50, '19.99', 'completed', '2025-09-09 22:20:27', NULL),
(2, 9, 5, NULL, 150, 140, '49.99', 'completed', '2025-09-09 22:20:32', NULL),
(3, 14, 4, NULL, 50, 0, '19.99', 'completed', '2025-09-09 23:02:20', NULL);

-- --------------------------------------------------------

--
-- Struttura della tabella `credit_usage`
--

CREATE TABLE `credit_usage` (
  `id` int NOT NULL,
  `business_id` int NOT NULL,
  `purchase_id` int NOT NULL,
  `service_type` enum('promotion','feature_boost','priority_listing','analytics') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `service_description` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `credits_used` int NOT NULL,
  `used_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `credit_usage`
--

INSERT INTO `credit_usage` (`id`, `business_id`, `purchase_id`, `service_type`, `service_description`, `credits_used`, `used_at`) VALUES
(1, 14, 3, '', 'Riduzione manuale da admin: gestione social', 1, '2025-09-09 23:18:01'),
(2, 14, 3, '', 'Riduzione manuale da admin: set', 49, '2025-09-09 23:18:56'),
(3, 9, 2, '', 'Riduzione manuale da admin: test prova', 10, '2025-09-11 07:19:16');

-- --------------------------------------------------------

--
-- Struttura della tabella `events`
--

CREATE TABLE `events` (
  `id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime DEFAULT NULL,
  `location` varchar(255) NOT NULL,
  `category_id` int DEFAULT NULL,
  `province_id` int DEFAULT NULL,
  `organizer` varchar(255) NOT NULL,
  `contact_email` varchar(255) NOT NULL,
  `contact_phone` varchar(50) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT '0.00',
  `status` varchar(20) NOT NULL DEFAULT 'pending',
  `source` varchar(20) NOT NULL DEFAULT 'suggestion',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dump dei dati per la tabella `events`
--

INSERT INTO `events` (`id`, `title`, `description`, `start_date`, `end_date`, `location`, `category_id`, `province_id`, `organizer`, `contact_email`, `contact_phone`, `website`, `price`, `status`, `source`, `created_at`) VALUES
(1, '11111111111111111', '111111111111', '2025-10-01 21:50:00', '2025-10-02 00:50:00', '1111111', 33, 3, '1111111111111', '1111@gmail.com', '1234124124', '', '0.00', 'active', 'suggestion', '2025-09-30 19:50:32'),
(2, 'Qqqqqqqq', 'Qqqqqqq', '2025-10-01 22:15:00', '2025-10-02 01:15:00', 'Qqqqq', 27, 3, 'Qqqqqq', 'qqqqqq@gmail.com', '461679467', '', '0.00', 'pending', 'suggestion', '2025-09-30 20:15:47');

-- --------------------------------------------------------

--
-- Struttura della tabella `home_sections`
--

CREATE TABLE `home_sections` (
  `id` int NOT NULL,
  `section_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subtitle` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `image_path` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_visible` tinyint(1) DEFAULT '1',
  `sort_order` int DEFAULT '0',
  `custom_data` json DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `home_sections`
--

INSERT INTO `home_sections` (`id`, `section_name`, `title`, `subtitle`, `description`, `image_path`, `is_visible`, `sort_order`, `custom_data`) VALUES
(1, 'hero', 'Esplora la Calabria', 'Mare cristallino e storia millenaria', 'Immergiti nella bellezza della Calabria', '/placeholder-hero.jpg', 1, 1, NULL),
(2, 'categories', 'Esplora per Categoria', '', 'Scopri la Calabria attraverso le sue diverse sfaccettature', '', 1, 2, NULL),
(3, 'provinces', 'Esplora le Province', '', 'Ogni provincia calabrese custodisce tesori unici', '', 1, 3, NULL),
(4, 'map', 'Mappa Interattiva', '', 'Naviga attraverso la Calabria con la nostra mappa interattiva', '', 1, 4, NULL),
(5, 'cta', 'Vuoi far Conoscere la Tua Calabria?', '', 'Unisciti alla nostra community!', '', 1, 5, NULL),
(6, 'newsletter', 'Resta Connesso con la Calabria', '', 'Iscriviti alla nostra newsletter', '', 1, 6, NULL);

-- --------------------------------------------------------

--
-- Struttura della tabella `newsletter_subscribers`
--

CREATE TABLE `newsletter_subscribers` (
  `id` int NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `interests` json DEFAULT NULL,
  `status` enum('active','confirmed','unsubscribed') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `confirmation_token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `confirmed_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `place_suggestions`
--

CREATE TABLE `place_suggestions` (
  `id` int NOT NULL,
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
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `provinces`
--

CREATE TABLE `provinces` (
  `id` int NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `image_path` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `provinces`
--

INSERT INTO `provinces` (`id`, `name`, `description`, `image_path`, `created_at`) VALUES
(1, 'Catanzaro', 'Capoluogo di regione, cuore della Calabria tra due mari', 'uploads/provinces/68c28cf24e27b-Screenshot 2024-11-05 123524.png', '2025-09-08 20:04:52'),
(2, 'Cosenza', 'La provincia più estesa, ricca di storia e natura', NULL, '2025-09-08 20:04:52'),
(3, 'Crotone', 'Terra di Pitagora, tra mare cristallino e archeologia', NULL, '2025-09-08 20:04:52'),
(4, 'Reggio Calabria', 'La punta dello stivale, affacciata sullo Stretto di Messina', NULL, '2025-09-08 20:04:52'),
(5, 'Vibo Valentia', 'Piccola provincia ricca di tradizioni marinare e gastronomiche', NULL, '2025-09-08 20:04:52');

-- --------------------------------------------------------

--
-- Struttura della tabella `settings`
--

CREATE TABLE `settings` (
  `id` int NOT NULL,
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'text',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `settings`
--

INSERT INTO `settings` (`id`, `key`, `value`, `type`, `created_at`) VALUES
(1, 'site_name', 'Passione Calabria', 'text', '2025-09-08 20:04:52'),
(2, 'site_description', 'La tua guida alla Calabria', 'text', '2025-09-08 20:04:52'),
(3, 'contact_email', 'info@passionecalabria.it', 'email', '2025-09-08 20:04:52'),
(4, 'contact_phone', '+39 XXX XXX XXXX', 'text', '2025-09-08 20:04:52'),
(5, 'google_recaptcha_v2_site_key', '', 'text', '2025-09-08 20:04:52'),
(6, 'google_recaptcha_v2_secret_key', '', 'password', '2025-09-08 20:04:52'),
(7, 'google_recaptcha_v3_site_key', '', 'text', '2025-09-08 20:04:52'),
(8, 'google_recaptcha_v3_secret_key', '', 'password', '2025-09-08 20:04:52'),
(9, 'stripe_publishable_key', '', 'text', '2025-09-08 20:04:52'),
(10, 'stripe_secret_key', '', 'password', '2025-09-08 20:04:52'),
(11, 'google_analytics_id', '', 'text', '2025-09-08 20:04:52'),
(12, 'app_store_link', '', 'text', '2025-09-08 20:04:52'),
(13, 'app_store_image', '', 'text', '2025-09-08 20:04:52'),
(14, 'play_store_link', '', 'text', '2025-09-08 20:04:52'),
(15, 'play_store_image', '', 'text', '2025-09-08 20:04:52'),
(16, 'vai_app_link', '', 'text', '2025-09-08 20:04:52'),
(17, 'suggerisci_evento_link', 'https://prova.passionecalabria.it/suggerisci-evento.php', 'text', '2025-09-08 20:04:52'),
(18, 'hero_title', 'Esplora la Calabria', 'text', '2025-09-08 20:04:52'),
(19, 'hero_subtitle', 'Mare cristallino e storia millenaria', 'text', '2025-09-08 20:04:52'),
(20, 'hero_description', 'Immergiti nella bellezza della Calabria, con le sue spiagge da sogno, il centro storico affascinante e i panorami mozzafiato dalla rupe.', 'textarea', '2025-09-08 20:04:52'),
(21, 'hero_image', '/placeholder-hero.jpg', 'text', '2025-09-08 20:04:52'),
(22, 'business_registration_enabled', '1', 'boolean', '2025-09-08 20:04:52'),
(23, 'business_auto_approval', '0', 'boolean', '2025-09-08 20:04:52'),
(24, 'max_free_businesses_per_email', '1', 'number', '2025-09-08 20:04:52'),
(25, 'maintenance_enabled', '0', 'text', '2025-09-21 09:53:59'),
(26, 'maintenance_message', 'Sito in manutenzione. Torneremo presto!', 'text', '2025-09-21 09:53:59');

-- --------------------------------------------------------

--
-- Struttura della tabella `static_pages`
--

CREATE TABLE `static_pages` (
  `id` int NOT NULL,
  `slug` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `meta_title` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `meta_description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `is_published` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `static_pages`
--

INSERT INTO `static_pages` (`id`, `slug`, `title`, `content`, `meta_title`, `meta_description`, `is_published`, `created_at`) VALUES
(1, 'chi-siamo', 'Chi Siamo', '<h1>Chi Siamo</h1><p>Benvenuti in Passione Calabria, il portale dedicato alla scoperta di una delle regioni più affascinanti d\'Italia.</p><p>La nostra missione è far conoscere la vera essenza della Calabria: dalle spiagge cristalline della Costa degli Dei ai borghi medievali dell\'entroterra, dalle tradizioni gastronomiche millenarie alle meraviglie naturali dei parchi nazionali.</p>', 'Chi Siamo - Passione Calabria', 'Scopri chi siamo e la nostra missione per promuovere la bellezza e le tradizioni della Calabria.', 1, '2025-09-08 20:04:52'),
(2, 'privacy-policy', 'Privacy Policy', '<h1>Privacy Policy</h1><p>Questa privacy policy descrive come raccogliamo, utilizziamo e proteggiamo le tue informazioni personali.</p><h2>Raccolta delle Informazioni</h2><p>Raccogliamo informazioni quando ti registri al nostro sito, ti iscrivi alla newsletter o compili un modulo.</p><h2>Utilizzo delle Informazioni</h2><p>Le informazioni raccolte vengono utilizzate per migliorare l\'esperienza utente e fornire servizi personalizzati.</p>', 'Privacy Policy - Passione Calabria', 'La nostra politica sulla privacy e protezione dei dati personali.', 1, '2025-09-08 20:04:52'),
(3, 'termini-servizio', 'Termini di Servizio', '<h1>Termini di Servizio</h1><p>Questi termini e condizioni governano il tuo uso del nostro sito web e servizi.</p><h2>Accettazione dei Termini</h2><p>Utilizzando il nostro sito, accetti di essere vincolato da questi termini di servizio.</p><h2>Servizi per le Attività</h2><p>Le attività commerciali possono registrarsi e gestire la propria presenza attraverso la nostra piattaforma.</p>', 'Termini di Servizio - Passione Calabria', 'I termini e condizioni per l\'utilizzo del nostro sito web e servizi.', 1, '2025-09-08 20:04:52'),
(4, 'contatti', 'Contatti', '<h1>Contatti</h1><p>Siamo sempre felici di sentire da voi! Ecco come potete raggiungerci:</p><h2>Informazioni di Contatto</h2><p><strong>Email:</strong> info@passionecalabria.it</p><p><strong>Telefono:</strong> +39 XXX XXX XXXX</p><p><strong>Indirizzo:</strong> Via Roma, 123 - 88100 Catanzaro (CZ)</p><h2>Per le Attività</h2><p>Per supporto tecnico o commerciale relativo alla registrazione delle attività, contattaci all\'indirizzo: business@passionecalabria.it</p>', 'Contatti - Passione Calabria', 'Come contattarci per informazioni, collaborazioni o segnalazioni.', 1, '2025-09-08 20:04:52'),
(5, 'cookie-policy', 'Cookie Policy', '<h1>Cookie Policy</h1><p>Questo sito utilizza cookies per migliorare la tua esperienza di navigazione.</p><h2>Cosa sono i Cookies</h2><p>I cookies sono piccoli file di testo che vengono memorizzati sul tuo dispositivo quando visiti un sito web.</p><h2>Tipologie di Cookies</h2><p>Utilizziamo cookies tecnici per il funzionamento del sito e cookies di analisi per migliorare i nostri servizi.</p>', 'Cookie Policy - Passione Calabria', 'La nostra politica sui cookies e come li utilizziamo.', 1, '2025-09-08 20:04:52');

-- --------------------------------------------------------

--
-- Struttura della tabella `subscriptions`
--

CREATE TABLE `subscriptions` (
  `id` int NOT NULL,
  `business_id` int NOT NULL,
  `package_id` int NOT NULL,
  `stripe_subscription_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` enum('pending','active','cancelled','expired') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `auto_renew` tinyint(1) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `subscriptions`
--

INSERT INTO `subscriptions` (`id`, `business_id`, `package_id`, `stripe_subscription_id`, `status`, `start_date`, `end_date`, `amount`, `auto_renew`, `created_at`) VALUES
(1, 1, 1, NULL, 'active', '2025-09-08 22:14:22', '2026-09-08 22:14:22', '0.00', 1, '2025-09-08 20:14:22'),
(2, 3, 1, NULL, 'active', '2025-09-08 22:22:16', '2026-09-08 22:22:16', '0.00', 1, '2025-09-08 20:22:16'),
(3, 5, 1, NULL, 'active', '2025-09-08 22:44:54', '2026-09-08 22:44:54', '0.00', 1, '2025-09-08 20:44:54'),
(4, 6, 1, NULL, 'active', '2025-09-08 23:55:14', '2026-09-08 23:55:14', '0.00', 1, '2025-09-08 21:55:14'),
(5, 7, 1, NULL, 'active', '2025-09-09 00:05:29', '2026-09-09 00:05:29', '0.00', 1, '2025-09-08 22:05:29'),
(6, 8, 1, NULL, 'active', '2025-09-09 00:17:06', '2026-09-09 00:17:06', '0.00', 1, '2025-09-08 22:17:06'),
(7, 9, 1, NULL, 'cancelled', '2025-09-09 00:45:04', '2026-09-09 00:45:04', '0.00', 1, '2025-09-08 22:45:04'),
(8, 10, 1, NULL, 'active', '2025-09-09 23:41:19', '2026-09-09 23:41:19', '0.00', 1, '2025-09-09 21:41:19'),
(9, 11, 1, NULL, 'cancelled', '2025-09-09 23:47:14', '2026-09-09 23:47:14', '0.00', 1, '2025-09-09 21:47:14'),
(10, 11, 2, NULL, 'cancelled', '2025-09-09 23:59:00', '2026-09-09 23:59:00', NULL, 1, '2025-09-09 21:59:00'),
(11, 11, 1, NULL, 'cancelled', '2025-09-09 23:59:09', '2026-09-09 23:59:09', NULL, 1, '2025-09-09 21:59:09'),
(12, 11, 1, NULL, 'active', '2025-09-10 00:03:15', '2026-09-10 00:03:15', NULL, 1, '2025-09-09 22:03:15'),
(13, 9, 2, NULL, 'cancelled', '2025-09-10 00:46:00', '2026-09-10 00:46:00', NULL, 1, '2025-09-09 22:46:00'),
(14, 14, 2, NULL, 'expired', '2025-09-10 01:01:40', '2026-09-10 01:01:40', '29.99', 1, '2025-09-09 23:01:40'),
(15, 9, 1, NULL, 'active', '2025-09-10 10:31:25', '2026-09-10 10:31:25', NULL, 1, '2025-09-10 08:31:25');

-- --------------------------------------------------------

--
-- Struttura della tabella `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `first_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('user','admin','editor','business') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'user',
  `status` enum('active','inactive','banned') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  `avatar` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `first_name`, `last_name`, `name`, `role`, `status`, `avatar`, `last_login`, `created_at`) VALUES
(1, 'admin@passionecalabria.it', '$2y$10$example.hash.here', NULL, NULL, 'Amministratore', 'admin', 'active', NULL, NULL, '2025-09-08 20:04:52'),
(2, 'asfasf@gmail.com', '$2y$10$GmM9NZwflHNDZiqdq9PTSO/qG2.JysiXvmIzYkJfFUZ29t8pE5GUe', NULL, NULL, 'aasfdasfasfas', 'business', 'active', NULL, '2025-09-08 22:25:00', '2025-09-08 20:44:54'),
(3, '3254325235@gmail.com', '$2y$10$TolyJmigfv.egAf/a1t.4.I.WTI9fI9KNBngrAP2vGi1Zmo.f1AgC', NULL, NULL, 'afafafasf', 'business', 'active', NULL, NULL, '2025-09-08 21:55:14'),
(4, 'testuser_68bf53296a925@example.com', '$2y$10$uhYDNx5oHJydGwJVxZL4TunswdoQ3oz5qGgWuC6G555LjOiUG.i8e', NULL, NULL, 'Attività di Test 2025-09-09 00:05:29', 'user', 'active', NULL, NULL, '2025-09-08 22:05:29'),
(5, 'ascfrtgde@gmail.com', '$2y$10$yHPwoj09lbMR9BYEDnMp1.3TJ94W8INs9spzoRqrdS2QWoZuNDeOi', NULL, NULL, 'awsfwqafrwqf', 'business', 'active', NULL, NULL, '2025-09-08 22:17:06'),
(6, 'rehth435@gmail.com', '$2y$10$Fa4u7WXlWz5NzosPj8r6FuCWHslEWbM2aoLZqg.Kww9qLaDcAIY4a', NULL, NULL, 'edfsdfsdf', 'business', 'active', NULL, '2025-09-24 19:06:40', '2025-09-08 22:45:04'),
(7, 'asfasfwq@gmail.com', '$2y$10$YX5o5vZ38mMf7pWEV6wVJ.nfqZe3lzsvEORhMc984KeTT2OuQm5py', NULL, NULL, 'asfasfasfaf', 'business', 'active', NULL, NULL, '2025-09-09 21:41:19'),
(8, 'bambolo@gmail.com', '$2y$10$DFyhgRNh2bdX7j8cOIR40OJbm/r6qGS80hHTM40hxE9J2Ui1PJdNS', NULL, NULL, 'afasfasf', 'business', 'active', NULL, NULL, '2025-09-09 21:47:14'),
(9, 'wrgw324asdf@gmail.com', '$2y$10$tvUQVihPg/P2bIz7xTQzKuwAZ8zfQQokPWWb2Fj3wY5Q98XKWSplO', NULL, NULL, 'sdfsdfsdf', 'business', 'active', NULL, NULL, '2025-09-09 22:47:35'),
(10, '124124124@gmail.com', '$2y$10$W3QIxiLO3ZDt8YMYa.E3PuvzysdlghRjvIyRUFTXdvpwF/KeRXfH6', NULL, NULL, '3123r3412', 'business', 'active', NULL, NULL, '2025-09-09 22:48:20'),
(11, 'giggiolino@gmail.com', '$2y$10$/xlBk5cGoJPpbA8xL3u54OVbDqd0EuGhChnDyvVudaO5TG9.Smj9W', NULL, NULL, 'andrea', 'business', 'active', NULL, '2025-09-09 23:18:24', '2025-09-09 23:01:33');

-- --------------------------------------------------------

--
-- Struttura della tabella `user_uploads`
--

CREATE TABLE `user_uploads` (
  `id` int NOT NULL,
  `article_id` int DEFAULT NULL,
  `city_id` int DEFAULT NULL,
  `user_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `image_path` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `original_filename` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `status` enum('pending','approved','rejected') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pending',
  `admin_notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dump dei dati per la tabella `user_uploads`
--

INSERT INTO `user_uploads` (`id`, `article_id`, `city_id`, `user_name`, `user_email`, `image_path`, `original_filename`, `description`, `status`, `admin_notes`, `created_at`) VALUES
(1, NULL, 7, 'rsdgrg', 'gwrsgwesg@gmail.com', 'uploads/user_photos/68d1a471d3fe0_1758569585.jpg', 'IMG_1793.jpg', 'asfeqfqefwefrwedfcasd', 'approved', NULL, '2025-09-22 19:33:05'),
(2, NULL, 7, 'asfasfae12', '321412@gmail.com', 'uploads/user_photos/68d1a4c43532e_1758569668.jpg', 'praia di focu.jpg', 'wefwdfsr234124', 'approved', NULL, '2025-09-22 19:34:28'),
(3, NULL, 11, 'aefadfadf', 'ar32qr4awsd@gmail.com', 'uploads/user_photos/68d1c33349f1c_1758577459.jpg', 'agrilao stemma 2.jpg', 'afsadrw3  5321reafaw4ar', 'approved', NULL, '2025-09-22 21:44:19');

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `articles`
--
ALTER TABLE `articles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `province_id` (`province_id`),
  ADD KEY `city_id` (`city_id`),
  ADD KEY `status` (`status`),
  ADD KEY `featured` (`featured`);

--
-- Indici per le tabelle `businesses`
--
ALTER TABLE `businesses`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `province_id` (`province_id`),
  ADD KEY `city_id` (`city_id`),
  ADD KEY `status` (`status`),
  ADD KEY `subscription_type` (`subscription_type`);

--
-- Indici per le tabelle `business_activity_log`
--
ALTER TABLE `business_activity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `business_id` (`business_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `action` (`action`),
  ADD KEY `created_at` (`created_at`);

--
-- Indici per le tabelle `business_packages`
--
ALTER TABLE `business_packages`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `business_sessions`
--
ALTER TABLE `business_sessions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `session_token` (`session_token`),
  ADD KEY `business_id` (`business_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `expires_at` (`expires_at`);

--
-- Indici per le tabelle `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indici per le tabelle `cities`
--
ALTER TABLE `cities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `province_id` (`province_id`);

--
-- Indici per le tabelle `comments`
--
ALTER TABLE `comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `article_id` (`article_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `status` (`status`),
  ADD KEY `idx_comments_city_id` (`city_id`);

--
-- Indici per le tabelle `comuni`
--
ALTER TABLE `comuni`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `consumption_purchases`
--
ALTER TABLE `consumption_purchases`
  ADD PRIMARY KEY (`id`),
  ADD KEY `business_id` (`business_id`),
  ADD KEY `package_id` (`package_id`),
  ADD KEY `status` (`status`);

--
-- Indici per le tabelle `credit_usage`
--
ALTER TABLE `credit_usage`
  ADD PRIMARY KEY (`id`),
  ADD KEY `business_id` (`business_id`),
  ADD KEY `purchase_id` (`purchase_id`);

--
-- Indici per le tabelle `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `province_id` (`province_id`);

--
-- Indici per le tabelle `home_sections`
--
ALTER TABLE `home_sections`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `section_name` (`section_name`);

--
-- Indici per le tabelle `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indici per le tabelle `place_suggestions`
--
ALTER TABLE `place_suggestions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`),
  ADD KEY `province_id` (`province_id`),
  ADD KEY `city_id` (`city_id`);

--
-- Indici per le tabelle `provinces`
--
ALTER TABLE `provinces`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indici per le tabelle `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `key` (`key`);

--
-- Indici per le tabelle `static_pages`
--
ALTER TABLE `static_pages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indici per le tabelle `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `business_id` (`business_id`),
  ADD KEY `package_id` (`package_id`),
  ADD KEY `status` (`status`);

--
-- Indici per le tabelle `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role` (`role`),
  ADD KEY `status` (`status`);

--
-- Indici per le tabelle `user_uploads`
--
ALTER TABLE `user_uploads`
  ADD PRIMARY KEY (`id`),
  ADD KEY `article_id` (`article_id`),
  ADD KEY `idx_city_id` (`city_id`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `articles`
--
ALTER TABLE `articles`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT per la tabella `businesses`
--
ALTER TABLE `businesses`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT per la tabella `business_activity_log`
--
ALTER TABLE `business_activity_log`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `business_packages`
--
ALTER TABLE `business_packages`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT per la tabella `business_sessions`
--
ALTER TABLE `business_sessions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `categories`
--
ALTER TABLE `categories`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT per la tabella `cities`
--
ALTER TABLE `cities`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT per la tabella `comments`
--
ALTER TABLE `comments`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT per la tabella `comuni`
--
ALTER TABLE `comuni`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT per la tabella `consumption_purchases`
--
ALTER TABLE `consumption_purchases`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT per la tabella `credit_usage`
--
ALTER TABLE `credit_usage`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT per la tabella `events`
--
ALTER TABLE `events`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT per la tabella `home_sections`
--
ALTER TABLE `home_sections`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT per la tabella `newsletter_subscribers`
--
ALTER TABLE `newsletter_subscribers`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `place_suggestions`
--
ALTER TABLE `place_suggestions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT per la tabella `provinces`
--
ALTER TABLE `provinces`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT per la tabella `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT per la tabella `static_pages`
--
ALTER TABLE `static_pages`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT per la tabella `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT per la tabella `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT per la tabella `user_uploads`
--
ALTER TABLE `user_uploads`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `articles`
--
ALTER TABLE `articles`
  ADD CONSTRAINT `articles_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `articles_ibfk_2` FOREIGN KEY (`province_id`) REFERENCES `provinces` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `articles_ibfk_3` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`) ON DELETE SET NULL;

--
-- Limiti per la tabella `businesses`
--
ALTER TABLE `businesses`
  ADD CONSTRAINT `businesses_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `businesses_ibfk_2` FOREIGN KEY (`province_id`) REFERENCES `provinces` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `businesses_ibfk_3` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`) ON DELETE SET NULL;

--
-- Limiti per la tabella `business_activity_log`
--
ALTER TABLE `business_activity_log`
  ADD CONSTRAINT `business_activity_log_ibfk_1` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `business_activity_log_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Limiti per la tabella `business_sessions`
--
ALTER TABLE `business_sessions`
  ADD CONSTRAINT `business_sessions_ibfk_1` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `business_sessions_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Limiti per la tabella `cities`
--
ALTER TABLE `cities`
  ADD CONSTRAINT `cities_ibfk_1` FOREIGN KEY (`province_id`) REFERENCES `provinces` (`id`) ON DELETE CASCADE;

--
-- Limiti per la tabella `comments`
--
ALTER TABLE `comments`
  ADD CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_comments_city_id` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`) ON DELETE CASCADE;

--
-- Limiti per la tabella `consumption_purchases`
--
ALTER TABLE `consumption_purchases`
  ADD CONSTRAINT `consumption_purchases_ibfk_1` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `consumption_purchases_ibfk_2` FOREIGN KEY (`package_id`) REFERENCES `business_packages` (`id`) ON DELETE CASCADE;

--
-- Limiti per la tabella `credit_usage`
--
ALTER TABLE `credit_usage`
  ADD CONSTRAINT `credit_usage_ibfk_1` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `credit_usage_ibfk_2` FOREIGN KEY (`purchase_id`) REFERENCES `consumption_purchases` (`id`) ON DELETE CASCADE;

--
-- Limiti per la tabella `place_suggestions`
--
ALTER TABLE `place_suggestions`
  ADD CONSTRAINT `place_suggestions_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `place_suggestions_ibfk_2` FOREIGN KEY (`province_id`) REFERENCES `provinces` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `place_suggestions_ibfk_3` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`) ON DELETE SET NULL;

--
-- Limiti per la tabella `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD CONSTRAINT `subscriptions_ibfk_1` FOREIGN KEY (`business_id`) REFERENCES `businesses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `subscriptions_ibfk_2` FOREIGN KEY (`package_id`) REFERENCES `business_packages` (`id`) ON DELETE CASCADE;

--
-- Limiti per la tabella `user_uploads`
--
ALTER TABLE `user_uploads`
  ADD CONSTRAINT `fk_user_uploads_city_id` FOREIGN KEY (`city_id`) REFERENCES `cities` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_uploads_ibfk_1` FOREIGN KEY (`article_id`) REFERENCES `articles` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
