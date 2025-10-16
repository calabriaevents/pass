-- phpMyAdmin SQL Dump
-- version 4.9.11
-- https://www.phpmyadmin.net/
--
-- Host: db5018286094.hosting-data.io
-- Creato il: Ott 16, 2025 alle 20:03
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
-- Database: `dbs14497088`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `activities`
--

CREATE TABLE `activities` (
  `id` int NOT NULL,
  `nomeAttivita` varchar(255) NOT NULL,
  `linkDestinazione` varchar(512) NOT NULL,
  `logoUrl` varchar(512) NOT NULL,
  `dataFineVisualizzazione` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `config`
--

CREATE TABLE `config` (
  `id` int NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dump dei dati per la tabella `config`
--

INSERT INTO `config` (`id`, `setting_key`, `setting_value`) VALUES
(1, 'logoAppUrl', 'logo/app-logo.png'),
(2, 'linkInstagram', 'https://passionecalabria.it'),
(3, 'linkFacebook', 'https://facebook.com'),
(4, 'linkSitoWeb', 'https://example.com'),
(5, 'linkIscriviAttivita', 'https://example.com/iscriviti');

-- --------------------------------------------------------

--
-- Struttura della tabella `events`
--

CREATE TABLE `events` (
  `id` int NOT NULL,
  `titolo` varchar(255) NOT NULL,
  `nomeAttivita` varchar(255) NOT NULL,
  `descrizione` text NOT NULL,
  `categoria` varchar(100) NOT NULL,
  `provincia` varchar(100) NOT NULL,
  `citta` varchar(100) NOT NULL,
  `dataEvento` datetime NOT NULL,
  `orarioInizio` time NOT NULL,
  `costoIngresso` varchar(100) NOT NULL,
  `imageUrl` varchar(512) NOT NULL,
  `linkMappaGoogle` varchar(512) NOT NULL,
  `linkPreviewMappaEmbed` text NOT NULL,
  `linkContattoPrenotazioni` varchar(512) DEFAULT NULL,
  `createdAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updatedAt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dump dei dati per la tabella `events`
--

INSERT INTO `events` (`id`, `titolo`, `nomeAttivita`, `descrizione`, `categoria`, `provincia`, `citta`, `dataEvento`, `orarioInizio`, `costoIngresso`, `imageUrl`, `linkMappaGoogle`, `linkPreviewMappaEmbed`, `linkContattoPrenotazioni`, `createdAt`) VALUES
(3, 'prova prova', 'aaaaaa', 'sdfgwegwqeewq', 'Sagra', 'Catanzaro', 'Lamezia Terme', '2025-07-26 21:00:00', '21:00:00', 'gratis', 'https://commerciale.cloudgestionale.eu/crm/dashboard', 'https://maps.app.goo.gl/CD3gkDpM8LaK9Nfs6', '<iframe src=\"https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3084.418525364364!2d16.025600476362616!3d39.36940261871567!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x133fa4dca8fbfc41%3A0x561b5a78652fd91e!2sVia%20S.%20Agata%2C%20109%2C%2087027%20Paola%20CS!5e0!3m2!1sit!2sit!4v1753474044874!5m2!1sit!2sit\" width=\"600\" height=\"450\" style=\"border:0;\" allowfullscreen=\"\" loading=\"lazy\" referrerpolicy=\"no-referrer-when-downgrade\"></iframe>', 'https://wa.me/3345075668', '2025-07-25 20:09:46'),
(9, 'evento rpova', 'prova', 'prova', 'Sport', 'Cosenza', 'Diamante', '2025-07-31 21:00:00', '21:00:00', 'gratis', 'immagini/28-07-2025/1753714833_arco magno san nicola arcella.jpg', 'https://maps.app.goo.gl/b3hUrtUnm9Q4sFFB6', '<iframe src=\"https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3084.4983850158324!2d16.0262984763626!3d39.367594618823425!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x133fa4c4d2167b6d%3A0x3f1d7a63822f4563!2sVia%20S.%20Agata%2C%2087027%20Paola%20CS!5e0!3m2!1sit!2sit!4v1753714821838!5m2!1sit!2sit\" width=\"600\" height=\"450\" style=\"border:0;\" allowfullscreen=\"\" loading=\"lazy\" referrerpolicy=\"no-referrer-when-downgrade\"></iframe>', '', '2025-07-28 15:00:33'),
(11, 'I° Trofeo Ping Pong Città di Paola', 'Palazzetto dello sport', 'Singolo M/F a cura di Pro Loco di Paola e associazioni sportive', 'Sport', 'Cosenza', 'Paola', '2025-08-11 09:30:00', '09:30:00', 'Gratuito', 'immagini/10-08-2025/1754850575_pingpong.jpg', 'https://maps.app.goo.gl/2zUuvq4L43UmUT647', '<iframe src=\"https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3084.1934330143395!2d16.02466297577338!3d39.374498271626116!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x133fa4e78ac89bc7%3A0xd6ca1012e6f4be2d!2sPalazzetto%20dello%20Sport!5e0!3m2!1sit!2sit!4v1754850558454!5m2!1sit!2sit\" width=\"600\" height=\"450\" style=\"border:0;\" allowfullscreen=\"\" loading=\"lazy\" referrerpolicy=\"no-referrer-when-downgrade\"></iframe>', '', '2025-08-10 18:29:35'),
(12, 'I° Trofeo di Ping Pong Città di Paola ', 'Palazzetto dello sport', 'Doppio M/F a cura di Pro Loco Paola e associazioni sportive', 'Sport', 'Cosenza', 'Paola', '2025-08-11 16:00:00', '16:00:00', 'Gratuito', 'immagini/10-08-2025/1754850795_pingpong.jpg', 'https://maps.app.goo.gl/2zUuvq4L43UmUT647', '<iframe src=\"https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3084.1934330143395!2d16.02466297577338!3d39.374498271626116!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x133fa4e78ac89bc7%3A0xd6ca1012e6f4be2d!2sPalazzetto%20dello%20Sport!5e0!3m2!1sit!2sit!4v1754850558454!5m2!1sit!2sit\" width=\"600\" height=\"450\" style=\"border:0;\" allowfullscreen=\"\" loading=\"lazy\" referrerpolicy=\"no-referrer-when-downgrade\"></iframe>', '', '2025-08-10 18:33:15'),
(13, 'XXXIV^ Edizione della SAGRA DA GRISPEDDA', 'Associazione di Quartiere \"Paola Sud\" - Fosse', 'XXXIV^ Edizione della SAGRA DA\r\nGRISPEDDA con la partecipazione di\r\nStefano Priolo', 'Sagra', 'Cosenza', 'Paola', '2025-08-11 20:00:00', '20:00:00', 'Gratuito', 'immagini/10-08-2025/1754852215_sagra-da-grispedda.jpg', 'https://maps.app.goo.gl/Z1aJCzhumjhvfkwHA', '<iframe src=\"https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3085.9767537422445!2d16.049486700000006!3d39.334112200000014!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x133fbb42fac54083%3A0xb3a9733795ea9dab!2sVia%20Fosse%2C%2087027%20Paola%20CS!5e0!3m2!1sit!2sit!4v1754852205175!5m2!1sit!2sit\" width=\"600\" height=\"450\" style=\"border:0;\" allowfullscreen=\"\" loading=\"lazy\" referrerpolicy=\"no-referrer-when-downgrade\"></iframe>', '', '2025-08-10 18:56:55'),
(14, 'Rassegna musicale “Malti urbani” - MaxForever', 'Brunnen Bier', 'Rassegna musicale (Tribute band 883) - Teatro M. Ganeri', 'Concerto', 'Cosenza', 'Paola', '2025-08-11 21:30:00', '21:30:00', 'Gratuito', 'immagini/10-08-2025/1754852539_marea-summer-festival.jpg', 'https://maps.app.goo.gl/wFKU5VSZa2i2cZ9JA', '<iframe src=\"https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3084.84900317475!2d16.03927535!3d39.3596559!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x133fa51f950a5163%3A0x297c69e4fade150c!2sTeatro%20Mauro%20Ganeri!5e0!3m2!1sit!2sit!4v1754852522986!5m2!1sit!2sit\" width=\"600\" height=\"450\" style=\"border:0;\" allowfullscreen=\"\" loading=\"lazy\" referrerpolicy=\"no-referrer-when-downgrade\"></iframe>', '', '2025-08-10 19:02:19'),
(15, 'II^ Edizione “Sandwich on the beach” ', 'Comitato di quartiere “La Petrulla”', 'II^ Edizione “Sandwich on the beach” a cura di Comitato di quartiere “La\r\nPetrulla”', 'Sagra', 'Cosenza', 'Paola', '2025-08-12 19:00:00', '19:00:00', 'Gratuito', 'immagini/10-08-2025/1754852954_IMG_6265_1170x.webp', 'https://maps.app.goo.gl/KTQCsbyGwfh3bJF29', '<iframe src=\"https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3085.762824212131!2d16.039628000000004!3d39.3389588!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x133fbb6b3fe3f693%3A0xd8c6477813d14a5e!2sZona%20Pennelli%2C%2087027%20Paola%20CS!5e0!3m2!1sit!2sit!4v1754852899863!5m2!1sit!2sit\" width=\"600\" height=\"450\" style=\"border:0;\" allowfullscreen=\"\" loading=\"lazy\" referrerpolicy=\"no-referrer-when-downgrade\"></iframe>', '', '2025-08-10 19:09:14'),
(16, 'Spettacolo musicale BRUSCOS’ BAND', 'Marco Brusco', 'Spettacolo musicale BRUSCOS’\r\nBAND: notte Paolana! ', 'Concerto', 'Cosenza', 'Paola', '2025-08-12 21:30:00', '21:30:00', 'Gratuito', 'immagini/10-08-2025/1754853108_marea-summer-festival.jpg', 'https://maps.app.goo.gl/grpefYVVQf6yKjU88', '<iframe src=\"https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3084.866804742105!2d16.041636000000004!3d39.35925280000001!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x133fa5ef00706195%3A0xc2a652fe24994089!2sPiazza%20del%20Popolo!5e0!3m2!1sit!2sit!4v1754853093892!5m2!1sit!2sit\" width=\"600\" height=\"450\" style=\"border:0;\" allowfullscreen=\"\" loading=\"lazy\" referrerpolicy=\"no-referrer-when-downgrade\"></iframe>', '', '2025-08-10 19:11:48'),
(17, 'Laboratorio aperto “Pezzi Unici”', 'Associazione Colpo e Stare Insieme Onlus', 'Laboratorio aperto “Pezzi Unici” a cura\r\ndi Associazione Colpo e Stare Insieme\r\nOnlus', 'Teatro', 'Cosenza', 'Paola', '2025-08-13 19:00:00', '19:00:00', 'Gratuito', 'immagini/10-08-2025/1754853301_Laboratori-per-Progetti-Pezzi-unici-Anffas-Mirandola.webp', 'https://maps.app.goo.gl/9osZbjweHHxMC7VX9', '<iframe src=\"https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3084.9125157825843!2d16.0383694!3d39.358217700000004!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x133fa5aaad991495%3A0x5ea45b44e1102daa!2sVilla%20Comunale%20Carlo%20Alberto%20Dalla%20Chiesa!5e0!3m2!1sit!2sit!4v1754853286611!5m2!1sit!2sit\" width=\"600\" height=\"450\" style=\"border:0;\" allowfullscreen=\"\" loading=\"lazy\" referrerpolicy=\"no-referrer-when-downgrade\"></iframe>', '', '2025-08-10 19:15:01'),
(18, 'II^ Edizione “Sandwich on the beach”', 'Comitato di quartiere “La Petrulla”', 'II^ Edizione “Sandwich on the beach” a\r\ncura di Comitato di quartiere “La\r\nPetrulla”\r\n', 'Sagra', 'Cosenza', 'Paola', '2025-08-13 19:00:00', '19:00:00', 'Gratuito', 'immagini/10-08-2025/1754853399_IMG_6265_1170x.webp', 'https://maps.app.goo.gl/KTQCsbyGwfh3bJF29', '<iframe src=\"https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3085.762824212131!2d16.039628000000004!3d39.3389588!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x133fbb6b3fe3f693%3A0xd8c6477813d14a5e!2sZona%20Pennelli%2C%2087027%20Paola%20CS!5e0!3m2!1sit!2sit!4v1754853385428!5m2!1sit!2sit\" width=\"600\" height=\"450\" style=\"border:0;\" allowfullscreen=\"\" loading=\"lazy\" referrerpolicy=\"no-referrer-when-downgrade\"></iframe>', '', '2025-08-10 19:16:39'),
(19, 'III^ Edizione Trupija summer festival', 'Trupija City Lab', 'III^ Edizione Trupija summer festival a\r\ncura di Trupija City Lab - Zona Pennelli\r\n', 'Feste', 'Cosenza', 'Paola', '2025-08-14 18:00:00', '18:00:00', 'Gratuito', 'immagini/10-08-2025/1754853645_marea-summer-festival.jpg', 'https://maps.app.goo.gl/KTQCsbyGwfh3bJF29', '<iframe src=\"https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3085.762824212131!2d16.039628000000004!3d39.3389588!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x133fbb6b3fe3f693%3A0xd8c6477813d14a5e!2sZona%20Pennelli%2C%2087027%20Paola%20CS!5e0!3m2!1sit!2sit!4v1754853631854!5m2!1sit!2sit\" width=\"600\" height=\"450\" style=\"border:0;\" allowfullscreen=\"\" loading=\"lazy\" referrerpolicy=\"no-referrer-when-downgrade\"></iframe>', '', '2025-08-10 19:20:45'),
(20, 'Aspettando FERRAGOSTO ', 'Comitato di quartiere Rocchetta', 'Aspettando FERRAGOSTO a cura del\r\nComitato di quartiere Rocchetta', 'Feste', 'Cosenza', 'Paola', '2025-08-14 21:00:00', '21:00:00', 'Gratuito', 'immagini/10-08-2025/1754853830_9d749b_64c41261a4104bc1af1be9dcedadc5d7~mv2.avif', 'https://maps.app.goo.gl/dZwh9eYgP6FzjF5PA', '<iframe src=\"https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3084.7729418183817!2d16.0410807!3d39.3613782!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x133fa4b7e42caf19%3A0xa765cd802f192a19!2sVia%20Rocchetta%2C%2087027%20Paola%20CS!5e0!3m2!1sit!2sit!4v1754853818006!5m2!1sit!2sit\" width=\"600\" height=\"450\" style=\"border:0;\" allowfullscreen=\"\" loading=\"lazy\" referrerpolicy=\"no-referrer-when-downgrade\"></iframe>', '', '2025-08-10 19:23:50'),
(21, 'Festa di FERRAGOSTO', 'Comune di Paola', '“Festa di FERRAGOSTO” special\r\nguest GERRY PULCI con vocalist\r\nBeppe Tuoto e dj Kevin Cosentino,\r\nCiusky, Vicious - Piazza IV Novembre', 'Feste', 'Cosenza', 'Paola', '2025-08-15 22:30:00', '22:30:00', 'Gratuito', 'immagini/10-08-2025/1754854086_marea-summer-festival.jpg', 'https://maps.app.goo.gl/SeD2qWmVXzt1XiaC7', '<iframe src=\"https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3084.8683327286158!2d16.041488699999984!3d39.3592182!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x133fa4b6250c5efd%3A0x187870c8bf415818!2sPiazza%20IV%20Novembre%2C%2087027%20Paola%20CS!5e0!3m2!1sit!2sit!4v1754854074674!5m2!1sit!2sit\" width=\"600\" height=\"450\" style=\"border:0;\" allowfullscreen=\"\" loading=\"lazy\" referrerpolicy=\"no-referrer-when-downgrade\"></iframe>', '', '2025-08-10 19:28:06'),
(22, 'Festival Mpiulato', 'cura di Pro Loco – Paola', 'Festival Mpiulato a cura di Pro Loco –\r\nPaola - Corso Garibaldi', 'Sagra', 'Cosenza', 'Paola', '2025-08-16 21:00:00', '21:00:00', 'Gratuito', 'immagini/10-08-2025/1754854225_images.jpeg', 'https://maps.app.goo.gl/qWmRj7BRB9kykApS6', '<iframe src=\"https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3084.8017891986756!2d16.041363199999985!3d39.36072500000001!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x133fa4c81ec01a35%3A0x13d275085bd3ed4e!2sCorso%20Garibaldi%2C%2087027%20Paola%20CS!5e0!3m2!1sit!2sit!4v1754854213272!5m2!1sit!2sit\" width=\"600\" height=\"450\" style=\"border:0;\" allowfullscreen=\"\" loading=\"lazy\" referrerpolicy=\"no-referrer-when-downgrade\"></iframe>', '', '2025-08-10 19:30:25'),
(23, 'XXXVI^ Edizione Regata Open Paola', ' Club Nautico Paola ASD', 'XXXVI^ Edizione Regata Open Paola\r\na cura di Club Nautico Paola ASD. Via Arenile - Spiaggia Le Vele', 'Sport', 'Cosenza', 'Paola', '2025-08-17 14:30:00', '14:30:00', 'Gratuito', 'immagini/10-08-2025/1754854551_ic_large_w900h500q100_regata-velica.jpg', 'https://maps.app.goo.gl/7nxjqMzRqvBQi69JA', '<iframe src=\"https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3086.2257381734007!2d16.0385499395832!3d39.3284707968391!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x133fbbe4dcf2b565%3A0xe7c33eab9f81f0ee!2sWater%20Sport%20Center%20%26%20Parasailing%20PAOLA%20Vola%20con%20il%20vento!5e0!3m2!1sit!2sit!4v1754854538332!5m2!1sit!2sit\" width=\"600\" height=\"450\" style=\"border:0;\" allowfullscreen=\"\" loading=\"lazy\" referrerpolicy=\"no-referrer-when-downgrade\"></iframe>', '', '2025-08-10 19:35:51'),
(24, 'Marea Summer Fest 2025', 'Amministrazione comunale e Associazione Turistica Pro Loco', 'Marea Summer Fest 2025 - Serata\r\ninaugurale a cura di Amministrazione\r\ncomunale e Associazione Turistica Pro\r\nLoco - Lungomare Zona Arenile', 'Feste', 'Cosenza', 'Paola', '2025-08-17 22:00:00', '22:00:00', 'Gratuito', 'immagini/10-08-2025/1754854728_images (1).jpeg', 'https://maps.app.goo.gl/kdjLHa365s3P28Xt5', '<iframe src=\"https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3085.130458501424!2d16.035255400000004!3d39.353282199999995!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x133fa4cca8146e27%3A0x7c63831161e046c5!2sVia%20Arenile%2C%2087027%20Paola%20CS!5e0!3m2!1sit!2sit!4v1754854713425!5m2!1sit!2sit\" width=\"600\" height=\"450\" style=\"border:0;\" allowfullscreen=\"\" loading=\"lazy\" referrerpolicy=\"no-referrer-when-downgrade\"></iframe>', '', '2025-08-10 19:38:48'),
(25, 'I^ Edizione “Premio Cittadini Illustri - Città di Paola”', 'Presidenza del Consiglio Comunale', 'I^ Edizione “Premio Cittadini Illustri -\r\nCittà di Paola” a cura della Presidenza\r\ndel Consiglio Comunale - Teatro M. Ganeri', 'Teatro', 'Cosenza', 'Paola', '2025-08-18 20:00:00', '20:00:00', 'Gratuito', 'immagini/10-08-2025/1754855304_Gemini_Generated_Image_366uah366uah366u.png', 'https://maps.app.goo.gl/8Do4AqqibNHsKCpQ9', '<iframe src=\"https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3084.8031229166045!2d16.041852899999995!3d39.3606948!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x133fa51f950a5163%3A0x297c69e4fade150c!2sTeatro%20Mauro%20Ganeri!5e0!3m2!1sit!2sit!4v1754855290502!5m2!1sit!2sit\" width=\"600\" height=\"450\" style=\"border:0;\" allowfullscreen=\"\" loading=\"lazy\" referrerpolicy=\"no-referrer-when-downgrade\"></iframe>', '', '2025-08-10 19:48:24'),
(26, 'I^ Edizione “Premio Cittadini Illustri - Città di Paola”', 'Presidenza del Consiglio Comunale', 'I^ Edizione “Premio Cittadini Illustri -\r\nCittà di Paola” a cura della Presidenza\r\ndel Consiglio Comunale - Teatro M. Ganeri', 'Teatro', 'Cosenza', 'Paola', '2025-08-18 20:00:00', '20:00:00', 'Gratuito', 'immagini/10-08-2025/1754855308_Gemini_Generated_Image_366uah366uah366u.png', 'https://maps.app.goo.gl/8Do4AqqibNHsKCpQ9', '<iframe src=\"https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3084.8031229166045!2d16.041852899999995!3d39.3606948!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x133fa51f950a5163%3A0x297c69e4fade150c!2sTeatro%20Mauro%20Ganeri!5e0!3m2!1sit!2sit!4v1754855290502!5m2!1sit!2sit\" width=\"600\" height=\"450\" style=\"border:0;\" allowfullscreen=\"\" loading=\"lazy\" referrerpolicy=\"no-referrer-when-downgrade\"></iframe>', '', '2025-08-10 19:48:28'),
(27, 'La Notte Blu', 'Associazione commercianti e Pro Loco Paola', 'La Notte Blu a cura di Associazione\r\ncommercianti e Pro Loco Paola - Paola centro', 'Feste', 'Cosenza', 'Paola', '2025-08-16 21:00:00', '21:00:00', 'Gratuito', 'immagini/10-08-2025/1754855517_InCollage_20230917_133213551-scaled.jpg', 'https://maps.app.goo.gl/SeD2qWmVXzt1XiaC7', '<iframe src=\"https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3084.8683327286158!2d16.041488699999984!3d39.3592182!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x133fa4b6250c5efd%3A0x187870c8bf415818!2sPiazza%20IV%20Novembre%2C%2087027%20Paola%20CS!5e0!3m2!1sit!2sit!4v1754855505256!5m2!1sit!2sit\" width=\"600\" height=\"450\" style=\"border:0;\" allowfullscreen=\"\" loading=\"lazy\" referrerpolicy=\"no-referrer-when-downgrade\"></iframe>', '', '2025-08-10 19:51:57'),
(28, 'La Notte Blu', 'Associazione commercianti e Pro Loco Paola', 'La Notte Blu a cura di Associazione\r\ncommercianti e Pro Loco Paola - Paola Centro', 'Feste', 'Cosenza', 'Paola', '2025-08-19 21:00:00', '21:00:00', 'Gratuito', 'immagini/19-08-2025/1755589769_Notte-Blu-a-Paola-1.jpg', 'https://maps.app.goo.gl/SeD2qWmVXzt1XiaC7', '<iframe src=\"https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3084.8683327286158!2d16.041488699999984!3d39.3592182!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x133fa4b6250c5efd%3A0x187870c8bf415818!2sPiazza%20IV%20Novembre%2C%2087027%20Paola%20CS!5e0!3m2!1sit!2sit!4v1755589751667!5m2!1sit!2sit\" width=\"600\" height=\"450\" style=\"border:0;\" allowfullscreen=\"\" loading=\"lazy\" referrerpolicy=\"no-referrer-when-downgrade\"></iframe>', '', '2025-08-19 07:49:29'),
(29, 'Commedia teatrale “Ne è valsa la pena”', 'APS EOS', 'Commedia teatrale “Ne è valsa la\r\npena” a cura di APS EOS\r\n', 'Teatro', 'Cosenza', 'Paola', '2025-08-20 21:00:00', '21:00:00', 'Gratuito', 'immagini/19-08-2025/1755589925_has-anyone-here-ever-been-to-an-improvised-jane-austen-v0-jnxg5jur7mgf1.webp', 'https://maps.app.goo.gl/8Do4AqqibNHsKCpQ9', '<iframe src=\"https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3084.8031229166045!2d16.041852899999995!3d39.3606948!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x133fa51f950a5163%3A0x297c69e4fade150c!2sTeatro%20Mauro%20Ganeri!5e0!3m2!1sit!2sit!4v1755589912953!5m2!1sit!2sit\" width=\"600\" height=\"450\" style=\"border:0;\" allowfullscreen=\"\" loading=\"lazy\" referrerpolicy=\"no-referrer-when-downgrade\"></iframe>', '', '2025-08-19 07:52:05'),
(30, 'III^ Edizione Trupija summer festival', 'Trupija City Lab', 'Lungomare centro\r\nIII^ Edizione Trupija summer festival -\r\nin Natale Estivo a cura di Trupija City\r\nLab', 'Feste', 'Cosenza', 'Paola', '2025-08-21 18:00:00', '18:00:00', 'Gratuito', 'immagini/19-08-2025/1755590094_trupja2024.jpg', 'https://maps.app.goo.gl/bCkcuvtUraDNRcew7', '<iframe src=\"https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3084.649536573047!2d16.0289268!3d39.3641724!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x133fa4ce4d7c65d3%3A0x8757381e8c3918ea!2sLungomare%20S.%20Francesco%20di%20Paola%2C%2087027%20Paola%20CS!5e0!3m2!1sit!2sit!4v1755590077877!5m2!1sit!2sit\" width=\"600\" height=\"450\" style=\"border:0;\" allowfullscreen=\"\" loading=\"lazy\" referrerpolicy=\"no-referrer-when-downgrade\"></iframe>', '', '2025-08-19 07:54:54'),
(31, 'Presentazione libro di Carmen Cervo PAULA CHJANU (IL DIALETTO PAOLANO)', 'Carmen Cervo', 'Presentazione libro di Carmen Cervo\r\nPAULA CHJANU (IL DIALETTO\r\nPAOLANO)', 'Teatro', 'Cosenza', 'Paola', '2025-08-21 20:30:00', '20:30:00', 'Gratuito', 'immagini/19-08-2025/1755590254_Bea_Charbonnier_Graziosi.jpg', 'https://maps.app.goo.gl/9osZbjweHHxMC7VX9', '<iframe src=\"https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3084.9125157825843!2d16.0383694!3d39.358217700000004!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x133fa5aaad991495%3A0x5ea45b44e1102daa!2sVilla%20Comunale%20Carlo%20Alberto%20Dalla%20Chiesa!5e0!3m2!1sit!2sit!4v1755590242889!5m2!1sit!2sit\" width=\"600\" height=\"450\" style=\"border:0;\" allowfullscreen=\"\" loading=\"lazy\" referrerpolicy=\"no-referrer-when-downgrade\"></iframe>', '', '2025-08-19 07:57:34'),
(32, 'Festival Margini - “Abitiamo Spazi e tempi”', 'Associazione Colpo, Brave ragazze, Croce Rossa italiana - Comitato di Paola, Associazione Culturale Tirrenide, Osservatoriomaree, Stare Insieme ONLUS ', 'Festival Margini - “Abitiamo Spazi e\r\ntempi” a cura di Associazione Colpo,\r\nBrave ragazze, Croce Rossa italiana -\r\nComitato di Paola, Associazione\r\nCulturale Tirrenide,\r\nOsservatoriomaree, Stare Insieme\r\nONLUS - Quartiere Cancello', 'Feste', 'Cosenza', 'Paola', '2025-08-22 20:00:00', '20:00:00', 'Gratuito', 'immagini/19-08-2025/1755590444_images.jpeg', 'https://maps.app.goo.gl/aurojckPGNYEbJgE7', '<iframe src=\"https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d12339.782060198888!2d16.03713870738515!3d39.357470436563766!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x133fa5006efe48b5%3A0x6b52b50ce3b96f13!2sQuartiere%20Cancello!5e0!3m2!1sit!2sit!4v1755590430347!5m2!1sit!2sit\" width=\"600\" height=\"450\" style=\"border:0;\" allowfullscreen=\"\" loading=\"lazy\" referrerpolicy=\"no-referrer-when-downgrade\"></iframe>', '', '2025-08-19 08:00:44'),
(33, 'Degustazione tartufo di Pizzo', 'Associazione ADA', 'Degustazione tartufo di Pizzo a cura di\r\nAssociazione ADA - Corso Roma', 'Sagra', 'Cosenza', 'Paola', '2025-08-22 21:00:00', '21:00:00', 'Gratuito', 'immagini/19-08-2025/1755590573_1592987200697-tartufo-pizzo-calabro-scaled.webp', 'https://maps.app.goo.gl/CZY3Z9TbxqpMdBHDA', '<iframe src=\"https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3084.9205529786377!2d16.039158500000013!3d39.358035699999995!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x133fa4ca33c87fdf%3A0xe76c3cfc25105c5c!2sCorso%20Roma%2C%2087027%20Paola%20CS!5e0!3m2!1sit!2sit!4v1755590561608!5m2!1sit!2sit\" width=\"600\" height=\"450\" style=\"border:0;\" allowfullscreen=\"\" loading=\"lazy\" referrerpolicy=\"no-referrer-when-downgrade\"></iframe>', '', '2025-08-19 08:02:53'),
(34, '“Laudato sii per nostra madre terra”', 'Ass. Custodi di Bellezza della città di Paola e Antico Borgo di Badia', 'Convegno “Laudato sii per nostra\r\nmadre terra” Don P. De Luca, Angelina\r\nMarcelli a cura di Ass. Custodi di\r\nBellezza della città di Paola e Antico\r\nBorgo di Badia', 'Teatro', 'Cosenza', 'Paola', '2025-08-23 18:30:00', '18:30:00', 'Gratuito', 'immagini/19-08-2025/1755590742_CustodireTerre_2024_qo.jpg', 'https://maps.app.goo.gl/d2mNgJLQmfW2R8HGA', '<iframe src=\"https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3084.8232301562184!2d16.040772800000003!3d39.36023949999999!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x133fa5b88e1646e3%3A0xf580bc26726ddbbb!2sIl%20Borgo%20di%20Francesco!5e0!3m2!1sit!2sit!4v1755590731672!5m2!1sit!2sit\" width=\"600\" height=\"450\" style=\"border:0;\" allowfullscreen=\"\" loading=\"lazy\" referrerpolicy=\"no-referrer-when-downgrade\"></iframe>', '', '2025-08-19 08:05:42'),
(35, 'Festival Margini - “Abitiamo Spazi e tempi”', 'Associazione Colpo, Brave ragazze, Croce Rossa italiana - Comitato di Paola, Associazione Culturale Tirrenide, Osservatoriomaree, Stare Insieme ONLUS', 'Festival Margini - “Abitiamo Spazi e\r\ntempi” a cura di Associazione Colpo,\r\nBrave ragazze, Croce Rossa italiana -\r\nComitato di Paola, Associazione\r\nCulturale Tirrenide,\r\nOsservatoriomaree, Stare Insieme\r\nONLUS - Quartiere Cancello', 'Feste', 'Cosenza', 'Paola', '2025-08-23 20:00:00', '20:00:00', 'Gratuito', 'immagini/19-08-2025/1755590875_images.jpeg', 'https://maps.app.goo.gl/BfPMN1K5itCHUXNi9', '<iframe src=\"https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3084.7626428708572!2d16.04348140000001!3d39.361611399999994!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x133fa5006efe48b5%3A0x6b52b50ce3b96f13!2sQuartiere%20Cancello!5e0!3m2!1sit!2sit!4v1755590864164!5m2!1sit!2sit\" width=\"600\" height=\"450\" style=\"border:0;\" allowfullscreen=\"\" loading=\"lazy\" referrerpolicy=\"no-referrer-when-downgrade\"></iframe>', '', '2025-08-19 08:07:55'),
(36, 'La Corrida dei Nasi Rossi', 'Associazione Nasi Rossi Gianfranco Contino', 'La Corrida dei Nasi Rossi a cura di\r\nAssociazione Nasi Rossi Gianfranco\r\nContino - Corso Roma', 'Teatro', 'Cosenza', 'Paola', '2025-08-23 20:30:00', '20:30:00', 'Gratuito', 'immagini/19-08-2025/1755591002_corsa-1.jpg', 'https://maps.app.goo.gl/5vwqv1F4cwgTE2Nj7', '<iframe src=\"https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3084.7626428708572!2d16.04348140000001!3d39.361611399999994!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x133fa4ca33c87fdf%3A0xe76c3cfc25105c5c!2sCorso%20Roma%2C%2087027%20Paola%20CS!5e0!3m2!1sit!2sit!4v1755590991623!5m2!1sit!2sit\" width=\"600\" height=\"450\" style=\"border:0;\" allowfullscreen=\"\" loading=\"lazy\" referrerpolicy=\"no-referrer-when-downgrade\"></iframe>', '', '2025-08-19 08:10:02'),
(37, 'BIMBOLANDIA', 'Cricca Sette Canali e con la collaborazione del Dopolavoro Ferroviario', 'BIMBOLANDIA a cura della Cricca\r\nSette Canali e con la collaborazione\r\ndel Dopolavoro Ferroviario - Corso Roma', 'Feste', 'Cosenza', 'Paola', '2025-08-24 17:00:00', '17:00:00', 'Gratuito', 'immagini/19-08-2025/1755591156_ic_large_w900h500q100_animazione-per-bambini.jpg', 'https://maps.app.goo.gl/CZY3Z9TbxqpMdBHDA', '<iframe src=\"https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3084.9205529786377!2d16.039158500000013!3d39.358035699999995!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x133fa4ca33c87fdf%3A0xe76c3cfc25105c5c!2sCorso%20Roma%2C%2087027%20Paola%20CS!5e0!3m2!1sit!2sit!4v1755591141770!5m2!1sit!2sit\" width=\"600\" height=\"450\" style=\"border:0;\" allowfullscreen=\"\" loading=\"lazy\" referrerpolicy=\"no-referrer-when-downgrade\"></iframe>', '', '2025-08-19 08:12:36'),
(38, 'Spettacolo musicale Duo Infinity', 'Commercianti del Centro', 'Spettacolo musicale Duo Infinity a cura\r\ndei Commercianti del Centro', 'Concerto', 'Cosenza', 'Paola', '2025-08-25 21:30:00', '21:30:00', 'Gratuito', 'immagini/19-08-2025/1755591597_musica.jpg', 'https://maps.app.goo.gl/SeD2qWmVXzt1XiaC7', '<iframe src=\"https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3084.8683327286158!2d16.041488699999984!3d39.3592182!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x133fa4b6250c5efd%3A0x187870c8bf415818!2sPiazza%20IV%20Novembre%2C%2087027%20Paola%20CS!5e0!3m2!1sit!2sit!4v1755591587235!5m2!1sit!2sit\" width=\"600\" height=\"450\" style=\"border:0;\" allowfullscreen=\"\" loading=\"lazy\" referrerpolicy=\"no-referrer-when-downgrade\"></iframe>', '', '2025-08-19 08:19:57'),
(39, '\"Confluenze di talenti in passerella\"', 'scuole di danza di Paola e con la partecipazione della Pro loco', '\"Confluenze di talenti in passerella\" a\r\ncura delle scuole di danza di Paola e\r\ncon la partecipazione della Pro loco - Lungomare', 'Sport', 'Cosenza', 'Paola', '2025-08-26 21:30:00', '21:30:00', 'Gratuito', 'immagini/19-08-2025/1755591734_danzaa.jpg', 'https://maps.app.goo.gl/bCkcuvtUraDNRcew7', '<iframe src=\"https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3084.649536573047!2d16.0289268!3d39.3641724!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x133fa4ce4d7c65d3%3A0x8757381e8c3918ea!2sLungomare%20S.%20Francesco%20di%20Paola%2C%2087027%20Paola%20CS!5e0!3m2!1sit!2sit!4v1755591721931!5m2!1sit!2sit\" width=\"600\" height=\"450\" style=\"border:0;\" allowfullscreen=\"\" loading=\"lazy\" referrerpolicy=\"no-referrer-when-downgrade\"></iframe>', '', '2025-08-19 08:22:14'),
(40, 'Spettacolo teatrale “W LE DONNE”', 'Compagnia teatrale Cilla giovani', 'Spettacolo teatrale “W LE DONNE” a\r\ncura della Compagnia teatrale Cilla\r\ngiovani - Teatro M. Ganeri', 'Teatro', 'Cosenza', 'Paola', '2025-08-27 21:00:00', '21:00:00', 'Gratuito', 'immagini/19-08-2025/1755591858_wledonne.jpg', 'https://maps.app.goo.gl/8Do4AqqibNHsKCpQ9', '<iframe src=\"https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3084.8031229166045!2d16.0418529!3d39.3606948!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x133fa51f950a5163%3A0x297c69e4fade150c!2sTeatro%20Mauro%20Ganeri!5e0!3m2!1sit!2sit!4v1755591847095!5m2!1sit!2sit\" width=\"600\" height=\"450\" style=\"border:0;\" allowfullscreen=\"\" loading=\"lazy\" referrerpolicy=\"no-referrer-when-downgrade\"></iframe>', '', '2025-08-19 08:24:18'),
(41, 'Lorelli musica dal vivo', 'Commercianti del Centro', 'Lorelli musica dal vivo a cura dei\r\nCommercianti del Centro - Piazza del Popolo', 'Concerto', 'Cosenza', 'Paola', '2025-08-28 21:30:00', '21:30:00', 'Gratuito', 'immagini/19-08-2025/1755592238_img-20190917-wa0000_2_81910-1570065179.jpeg', 'https://maps.app.goo.gl/grpefYVVQf6yKjU88', '<iframe src=\"https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3084.8668047421047!2d16.041636!3d39.35925280000002!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x133fa5ef00706195%3A0xc2a652fe24994089!2sPiazza%20del%20Popolo!5e0!3m2!1sit!2sit!4v1755592225983!5m2!1sit!2sit\" width=\"600\" height=\"450\" style=\"border:0;\" allowfullscreen=\"\" loading=\"lazy\" referrerpolicy=\"no-referrer-when-downgrade\"></iframe>', '', '2025-08-19 08:30:38'),
(42, 'Rassegna musicale “Malti urbani” - Precious', 'Brunnen Bier', 'Rassegna musicale “Malti urbani” -\r\nPrecious (Tribute band Depeche\r\nMode) a cura di Brunnen Bier - Teatro M. Ganeri\r\n', 'Concerto', 'Cosenza', 'Paola', '2025-08-29 21:30:00', '21:30:00', 'Gratuito', 'immagini/19-08-2025/1755592387_musica (1).jpg', 'https://maps.app.goo.gl/8Do4AqqibNHsKCpQ9', '<iframe src=\"https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3084.8031229166045!2d16.041852899999995!3d39.3606948!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x133fa51f950a5163%3A0x297c69e4fade150c!2sTeatro%20Mauro%20Ganeri!5e0!3m2!1sit!2sit!4v1755592376038!5m2!1sit!2sit\" width=\"600\" height=\"450\" style=\"border:0;\" allowfullscreen=\"\" loading=\"lazy\" referrerpolicy=\"no-referrer-when-downgrade\"></iframe>', '', '2025-08-19 08:33:07'),
(43, 'Rassegna musicale “Malti urbani”', 'Brunnen Bier', 'Rassegna musicale “Malti urbani” a\r\ncura di Brunnen Bier - Teatro M. Ganeri', 'Concerto', 'Cosenza', 'Paola', '2025-08-30 21:30:00', '21:30:00', 'Gratuito', 'immagini/19-08-2025/1755592470_musica (1).jpg', 'https://maps.app.goo.gl/8Do4AqqibNHsKCpQ9', '<iframe src=\"https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3084.8031229166045!2d16.041852899999995!3d39.3606948!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x133fa51f950a5163%3A0x297c69e4fade150c!2sTeatro%20Mauro%20Ganeri!5e0!3m2!1sit!2sit!4v1755592459365!5m2!1sit!2sit\" width=\"600\" height=\"450\" style=\"border:0;\" allowfullscreen=\"\" loading=\"lazy\" referrerpolicy=\"no-referrer-when-downgrade\"></iframe>', '', '2025-08-19 08:34:30'),
(44, 'Marco Serpa musica dal vivo', 'Commercianti del Centro', 'Marco Serpa musica dal vivo a cura di\r\nCommercianti del Centro - Piazza IV Novembre', 'Concerto', 'Cosenza', 'Paola', '2025-08-31 21:30:00', '21:30:00', 'Gratuito', 'immagini/19-08-2025/1755592609_ic_large_w900h500q100_marco-serpa.jpg', 'https://maps.app.goo.gl/SeD2qWmVXzt1XiaC7', '<iframe src=\"https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3084.8683327286158!2d16.041488699999984!3d39.3592182!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x133fa4b6250c5efd%3A0x187870c8bf415818!2sPiazza%20IV%20Novembre%2C%2087027%20Paola%20CS!5e0!3m2!1sit!2sit!4v1755592599635!5m2!1sit!2sit\" width=\"600\" height=\"450\" style=\"border:0;\" allowfullscreen=\"\" loading=\"lazy\" referrerpolicy=\"no-referrer-when-downgrade\"></iframe>', '', '2025-08-19 08:36:49');

-- --------------------------------------------------------

--
-- Struttura della tabella `locations`
--

CREATE TABLE `locations` (
  `id` int NOT NULL,
  `provincia` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `citta` varchar(255) COLLATE utf8mb4_general_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `locations`
--

INSERT INTO `locations` (`id`, `provincia`, `citta`) VALUES
(1, 'Catanzaro', 'Lamezia Terme'),
(2, 'Cosenza', 'acri'),
(12, 'Cosenza', 'albi'),
(6, 'Cosenza', 'Cosenza'),
(11, 'Cosenza', 'Diamante'),
(5, 'Cosenza', 'Paola'),
(7, 'Crotone', 'Caccuri'),
(3, 'Reggio di Calabria', 'Gioia Tauro');

-- --------------------------------------------------------

--
-- Struttura della tabella `login_attempts`
--

CREATE TABLE `login_attempts` (
  `id` int NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `attempt_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Struttura della tabella `visitor_stats`
--

CREATE TABLE `visitor_stats` (
  `stat_date` date NOT NULL,
  `daily_visits` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `visitor_stats`
--

INSERT INTO `visitor_stats` (`stat_date`, `daily_visits`) VALUES
('2025-07-27', 62),
('2025-07-28', 31),
('2025-07-29', 6),
('2025-07-30', 7),
('2025-07-31', 13),
('2025-08-01', 9),
('2025-08-02', 4),
('2025-08-03', 2),
('2025-08-04', 15),
('2025-08-05', 13),
('2025-08-06', 3),
('2025-08-07', 5),
('2025-08-08', 5),
('2025-08-09', 3),
('2025-08-10', 21),
('2025-08-11', 3),
('2025-08-12', 4),
('2025-08-13', 4),
('2025-08-15', 6),
('2025-08-16', 1),
('2025-08-17', 2),
('2025-08-18', 2),
('2025-08-19', 8),
('2025-08-20', 89),
('2025-08-21', 16),
('2025-08-22', 12),
('2025-08-23', 6),
('2025-08-24', 6),
('2025-08-25', 1),
('2025-08-26', 7),
('2025-08-27', 3),
('2025-08-28', 3),
('2025-08-31', 3),
('2025-09-01', 2),
('2025-09-02', 2),
('2025-09-04', 3),
('2025-09-05', 2),
('2025-09-07', 2),
('2025-09-08', 2),
('2025-09-09', 1),
('2025-09-10', 1),
('2025-09-11', 1),
('2025-09-14', 3),
('2025-09-15', 2),
('2025-09-17', 1),
('2025-09-20', 2),
('2025-09-25', 1),
('2025-09-27', 2),
('2025-09-28', 5),
('2025-09-29', 3),
('2025-10-02', 2),
('2025-10-03', 3),
('2025-10-04', 1),
('2025-10-05', 1),
('2025-10-06', 1),
('2025-10-07', 2),
('2025-10-08', 1),
('2025-10-09', 1),
('2025-10-11', 9),
('2025-10-12', 1),
('2025-10-13', 2);

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `activities`
--
ALTER TABLE `activities`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `config`
--
ALTER TABLE `config`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indici per le tabelle `events`
--
ALTER TABLE `events`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `locations`
--
ALTER TABLE `locations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_location` (`provincia`,`citta`);

--
-- Indici per le tabelle `login_attempts`
--
ALTER TABLE `login_attempts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ip_address_index` (`ip_address`);

--
-- Indici per le tabelle `visitor_stats`
--
ALTER TABLE `visitor_stats`
  ADD PRIMARY KEY (`stat_date`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `activities`
--
ALTER TABLE `activities`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT per la tabella `config`
--
ALTER TABLE `config`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=71;

--
-- AUTO_INCREMENT per la tabella `events`
--
ALTER TABLE `events`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- AUTO_INCREMENT per la tabella `locations`
--
ALTER TABLE `locations`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT per la tabella `login_attempts`
--
ALTER TABLE `login_attempts`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
