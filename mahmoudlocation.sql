-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : jeu. 01 oct. 2026 à 19:07
-- Version du serveur : 9.1.0
-- Version de PHP : 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `mahmoudlocation`
--

-- --------------------------------------------------------

--
-- Structure de la table `category`
--

DROP TABLE IF EXISTS `category`;
CREATE TABLE IF NOT EXISTS `category` (
  `id` int NOT NULL AUTO_INCREMENT,
  `icon` varchar(50) DEFAULT NULL,
  `position` int NOT NULL,
  `is_active` tinyint NOT NULL,
  `slug` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `parent_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_CATEGORY_SLUG` (`slug`),
  KEY `IDX_64C19C1727ACA70` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `category`
--

INSERT INTO `category` (`id`, `icon`, `position`, `is_active`, `slug`, `created_at`, `updated_at`, `parent_id`) VALUES
(1, NULL, 0, 1, 'categorie', '2026-09-27 12:24:53', '2026-09-27 12:24:53', NULL),
(2, NULL, 0, 1, 'culturelle-historique', '2026-09-27 13:32:41', '2026-09-27 13:32:41', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `category_translation`
--

DROP TABLE IF EXISTS `category_translation`;
CREATE TABLE IF NOT EXISTS `category_translation` (
  `id` int NOT NULL AUTO_INCREMENT,
  `locale` varchar(5) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` longtext,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `category_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_CATEGORY_TRANSLATION_LOCALE` (`category_id`,`locale`),
  KEY `IDX_3F2070412469DE2` (`category_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `category_translation`
--

INSERT INTO `category_translation` (`id`, `locale`, `name`, `description`, `created_at`, `updated_at`, `category_id`) VALUES
(1, 'fr', 'categorie', NULL, '2026-09-27 12:24:53', '2026-09-27 12:24:53', 1),
(2, 'en', 'categorie', NULL, '2026-09-27 12:24:53', '2026-09-27 12:24:53', 1),
(3, 'es', 'categorie', NULL, '2026-09-27 12:24:53', '2026-09-27 12:24:53', 1),
(4, 'de', 'categorie', NULL, '2026-09-27 12:24:53', '2026-09-27 12:24:53', 1),
(5, 'it', 'categorie', NULL, '2026-09-27 12:24:53', '2026-09-27 12:24:53', 1),
(6, 'fr', 'Culturelle & Historique', NULL, '2026-09-27 13:32:41', '2026-09-27 13:32:41', 2),
(7, 'en', 'Culturelle & Historique', NULL, '2026-09-27 13:32:41', '2026-09-27 13:32:41', 2),
(8, 'es', 'Culturelle & Historique', NULL, '2026-09-27 13:32:41', '2026-09-27 13:32:41', 2),
(9, 'de', 'Culturelle & Historique', NULL, '2026-09-27 13:32:41', '2026-09-27 13:32:41', 2),
(10, 'it', 'Culturelle & Historique', NULL, '2026-09-27 13:32:41', '2026-09-27 13:32:41', 2);

-- --------------------------------------------------------

--
-- Structure de la table `contact`
--

DROP TABLE IF EXISTS `contact`;
CREATE TABLE IF NOT EXISTS `contact` (
  `id` int NOT NULL AUTO_INCREMENT,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `service_category` varchar(255) NOT NULL,
  `reservation_number` varchar(255) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `message` longtext NOT NULL,
  `created_at` datetime NOT NULL,
  `request_type` varchar(50) NOT NULL,
  `is_archived` tinyint NOT NULL DEFAULT '0',
  `is_pinned` tinyint NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `contact`
--

INSERT INTO `contact` (`id`, `first_name`, `last_name`, `email`, `phone`, `service_category`, `reservation_number`, `subject`, `message`, `created_at`, `request_type`, `is_archived`, `is_pinned`) VALUES
(1, 'ksontini', 'ahmed', 'med@gmail.com', '93313278', 'groupe', NULL, 'testzad fazd', 'teststsdazdazd zd azd faerfa', '2026-09-22 20:15:01', '', 0, 0),
(2, 'tttt', 'tttt', 'ttttttt@gmail.com', '93312783', 'transfert', NULL, 'azadadadzd', 'azda zda da zdazdazd da f a a azd', '2026-09-22 20:29:47', '', 0, 0),
(3, 'ahmed', 'Ksontini', 'ahmed@gmail.com', '93313278', 'autre', NULL, 'tesetst', 'setstse etse t zsreze zaedfz', '2026-09-22 21:19:17', '', 0, 0),
(4, 'hmed', 'hmeeed', 'hmeed@gmail.com', '93313278', 'location', 'fgzf', 'efzefzfz fzefzefz ef', 'zef zef ergzreth zertg earga ergf a', '2026-09-22 21:37:16', 'partnership', 0, 0),
(5, 'zdfzd', 'fzdfzdf', 'zdfzdfdzf@gmail.com', '12345678', 'autre', NULL, 'fedfgegegeg e', 'fgefge fg efg', '2026-09-22 21:45:55', 'partnership', 0, 0),
(6, 'ahmed', 'ksontini', 'ahmedksontini560@gmail.com', '93313278', 'transfert', NULL, 'teste de email', 'test contact email', '2026-09-25 20:07:19', 'reservation', 0, 0);

-- --------------------------------------------------------

--
-- Structure de la table `contact_message`
--

DROP TABLE IF EXISTS `contact_message`;
CREATE TABLE IF NOT EXISTS `contact_message` (
  `id` int NOT NULL AUTO_INCREMENT,
  `status` varchar(20) NOT NULL,
  `recipient` varchar(255) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `body` longtext NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `sent_at` datetime DEFAULT NULL,
  `attachment_name` varchar(255) DEFAULT NULL,
  `attachment_path` varchar(255) DEFAULT NULL,
  `contact_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_2C9211FEE7A1254A` (`contact_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Structure de la table `currency_rate`
--

DROP TABLE IF EXISTS `currency_rate`;
CREATE TABLE IF NOT EXISTS `currency_rate` (
  `id` int NOT NULL AUTO_INCREMENT,
  `code` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL,
  `symbol` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `rate` decimal(12,6) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_555B7C4D77153098` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `currency_rate`
--

INSERT INTO `currency_rate` (`id`, `code`, `symbol`, `rate`) VALUES
(1, 'TND', 'DT', 1.000000),
(2, 'EUR', '€', 0.300000),
(3, 'USD', '$', 0.350000);

-- --------------------------------------------------------

--
-- Structure de la table `destination`
--

DROP TABLE IF EXISTS `destination`;
CREATE TABLE IF NOT EXISTS `destination` (
  `id` int NOT NULL AUTO_INCREMENT,
  `region` varchar(255) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `is_active` tinyint NOT NULL,
  `slug` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_DESTINATION_SLUG` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `destination`
--

INSERT INTO `destination` (`id`, `region`, `latitude`, `longitude`, `is_active`, `slug`, `created_at`, `updated_at`) VALUES
(1, NULL, NULL, NULL, 1, 'destination', '2026-09-27 12:25:02', '2026-09-27 12:25:02'),
(2, NULL, NULL, NULL, 1, 'cap-bon', '2026-09-27 13:32:47', '2026-09-27 13:32:47');

-- --------------------------------------------------------

--
-- Structure de la table `destination_translation`
--

DROP TABLE IF EXISTS `destination_translation`;
CREATE TABLE IF NOT EXISTS `destination_translation` (
  `id` int NOT NULL AUTO_INCREMENT,
  `locale` varchar(5) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` longtext,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `destination_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_DESTINATION_TRANSLATION_LOCALE` (`destination_id`,`locale`),
  KEY `IDX_915BAE54816C6140` (`destination_id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `destination_translation`
--

INSERT INTO `destination_translation` (`id`, `locale`, `name`, `description`, `created_at`, `updated_at`, `destination_id`) VALUES
(1, 'fr', 'destination', NULL, '2026-09-27 12:25:02', '2026-09-27 12:25:02', 1),
(2, 'en', 'destination', NULL, '2026-09-27 12:25:02', '2026-09-27 12:25:02', 1),
(3, 'es', 'destination', NULL, '2026-09-27 12:25:02', '2026-09-27 12:25:02', 1),
(4, 'de', 'destination', NULL, '2026-09-27 12:25:02', '2026-09-27 12:25:02', 1),
(5, 'it', 'destination', NULL, '2026-09-27 12:25:02', '2026-09-27 12:25:02', 1),
(6, 'fr', 'Cap Bon', NULL, '2026-09-27 13:32:47', '2026-09-27 13:32:47', 2),
(7, 'en', 'Cap Bon', NULL, '2026-09-27 13:32:47', '2026-09-27 13:32:47', 2),
(8, 'es', 'Cap Bon', NULL, '2026-09-27 13:32:47', '2026-09-27 13:32:47', 2),
(9, 'de', 'Cap Bon', NULL, '2026-09-27 13:32:47', '2026-09-27 13:32:47', 2),
(10, 'it', 'Cap Bon', NULL, '2026-09-27 13:32:47', '2026-09-27 13:32:47', 2);

-- --------------------------------------------------------

--
-- Structure de la table `doctrine_migration_versions`
--

DROP TABLE IF EXISTS `doctrine_migration_versions`;
CREATE TABLE IF NOT EXISTS `doctrine_migration_versions` (
  `version` varchar(191) NOT NULL,
  `executed_at` datetime DEFAULT NULL,
  `execution_time` int DEFAULT NULL,
  PRIMARY KEY (`version`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `doctrine_migration_versions`
--

INSERT INTO `doctrine_migration_versions` (`version`, `executed_at`, `execution_time`) VALUES
('DoctrineMigrations\\Version20260913175740', '2026-09-13 17:57:52', 31),
('DoctrineMigrations\\Version20260914090000', '2026-09-14 20:50:02', 47),
('DoctrineMigrations\\Version20260916100000', '2026-09-16 17:30:43', 91),
('DoctrineMigrations\\Version20260916100001', '2026-09-16 17:30:58', 8),
('DoctrineMigrations\\Version20260916100002', '2026-09-16 18:18:17', 56),
('DoctrineMigrations\\Version20260917000000', '2026-09-17 20:05:17', 70),
('DoctrineMigrations\\Version20260917000001', '2026-09-17 21:30:26', 64),
('DoctrineMigrations\\Version20260917000002', '2026-09-17 21:36:09', 38),
('DoctrineMigrations\\Version20260917221431', '2026-09-17 22:14:39', 14),
('DoctrineMigrations\\Version20260922195025', '2026-09-22 19:53:37', 33),
('DoctrineMigrations\\Version20260922213414', '2026-09-22 21:34:24', 12),
('DoctrineMigrations\\Version20260925201421', '2026-09-25 20:14:32', 45),
('DoctrineMigrations\\Version20260925202015', '2026-09-25 20:22:14', 63),
('DoctrineMigrations\\Version20260926174012', '2026-09-26 17:40:51', 263),
('DoctrineMigrations\\Version20260926174219', '2026-09-26 17:43:35', 192);

-- --------------------------------------------------------

--
-- Structure de la table `excursion`
--

DROP TABLE IF EXISTS `excursion`;
CREATE TABLE IF NOT EXISTS `excursion` (
  `id` int NOT NULL AUTO_INCREMENT,
  `type` varchar(20) NOT NULL,
  `status` varchar(20) NOT NULL,
  `is_best_seller` tinyint NOT NULL,
  `is_featured` tinyint NOT NULL,
  `duration_hours` int DEFAULT NULL,
  `duration_minutes` int DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `price_adult` decimal(10,2) NOT NULL,
  `price_child` decimal(10,2) DEFAULT NULL,
  `price_converted` decimal(10,2) DEFAULT NULL,
  `min_participants` int NOT NULL,
  `max_participants` int NOT NULL,
  `cancellation_hours` int NOT NULL,
  `rating_average` decimal(3,2) DEFAULT NULL,
  `rating_count` int NOT NULL,
  `booking_count` int NOT NULL,
  `slug` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `category_id` int NOT NULL,
  `destination_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_EXCURSION_SLUG` (`slug`),
  KEY `IDX_9B08E72F12469DE2` (`category_id`),
  KEY `IDX_9B08E72F816C6140` (`destination_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `excursion`
--

INSERT INTO `excursion` (`id`, `type`, `status`, `is_best_seller`, `is_featured`, `duration_hours`, `duration_minutes`, `start_time`, `end_time`, `price_adult`, `price_child`, `price_converted`, `min_participants`, `max_participants`, `cancellation_hours`, `rating_average`, `rating_count`, `booking_count`, `slug`, `created_at`, `updated_at`, `category_id`, `destination_id`) VALUES
(1, 'type', 'status', 0, 1, 1, 30, '15:32:15', '28:32:15', 150.00, 100.00, 100.00, 1, 10, 24, 1.00, 1, 1, 'slug', '2026-10-01 18:32:14', '2026-10-01 18:32:14', 2, 1);

-- --------------------------------------------------------

--
-- Structure de la table `excursion_badge`
--

DROP TABLE IF EXISTS `excursion_badge`;
CREATE TABLE IF NOT EXISTS `excursion_badge` (
  `id` int NOT NULL AUTO_INCREMENT,
  `badge_type` varchar(20) NOT NULL,
  `is_active` tinyint NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `excursion_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_2C3477534AB4296F` (`excursion_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `excursion_badge`
--

INSERT INTO `excursion_badge` (`id`, `badge_type`, `is_active`, `created_at`, `updated_at`, `excursion_id`) VALUES
(1, 'badge_type', 1, '2026-10-01 18:34:46', '2026-10-01 18:34:46', 1);

-- --------------------------------------------------------

--
-- Structure de la table `excursion_badge_translation`
--

DROP TABLE IF EXISTS `excursion_badge_translation`;
CREATE TABLE IF NOT EXISTS `excursion_badge_translation` (
  `id` int NOT NULL AUTO_INCREMENT,
  `locale` varchar(5) NOT NULL,
  `text` varchar(100) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `excursion_badge_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_EXCURSION_BADGE_TRANSLATION_LOCALE` (`excursion_badge_id`,`locale`),
  KEY `IDX_73F56BE849714E71` (`excursion_badge_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `excursion_badge_translation`
--

INSERT INTO `excursion_badge_translation` (`id`, `locale`, `text`, `created_at`, `updated_at`, `excursion_badge_id`) VALUES
(1, 'fr', 'text', '2026-10-01 19:01:17', '2026-10-01 19:01:17', 1);

-- --------------------------------------------------------

--
-- Structure de la table `excursion_faq`
--

DROP TABLE IF EXISTS `excursion_faq`;
CREATE TABLE IF NOT EXISTS `excursion_faq` (
  `id` int NOT NULL AUTO_INCREMENT,
  `position` int NOT NULL,
  `is_active` tinyint NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `excursion_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_5E6CD5BF4AB4296F` (`excursion_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `excursion_faq`
--

INSERT INTO `excursion_faq` (`id`, `position`, `is_active`, `created_at`, `updated_at`, `excursion_id`) VALUES
(1, 1, 1, '2026-10-01 18:35:22', '2026-10-01 18:35:22', 1);

-- --------------------------------------------------------

--
-- Structure de la table `excursion_faq_translation`
--

DROP TABLE IF EXISTS `excursion_faq_translation`;
CREATE TABLE IF NOT EXISTS `excursion_faq_translation` (
  `id` int NOT NULL AUTO_INCREMENT,
  `locale` varchar(5) NOT NULL,
  `question` varchar(500) NOT NULL,
  `answer` longtext,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `excursion_faq_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_EXCURSION_FAQ_TRANSLATION_LOCALE` (`excursion_faq_id`,`locale`),
  KEY `IDX_E3ED0BE71008606A` (`excursion_faq_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `excursion_faq_translation`
--

INSERT INTO `excursion_faq_translation` (`id`, `locale`, `question`, `answer`, `created_at`, `updated_at`, `excursion_faq_id`) VALUES
(1, 'fr', 'question', 'answer', '2026-10-01 19:01:39', '2026-10-01 19:01:39', 1);

-- --------------------------------------------------------

--
-- Structure de la table `excursion_feature`
--

DROP TABLE IF EXISTS `excursion_feature`;
CREATE TABLE IF NOT EXISTS `excursion_feature` (
  `id` int NOT NULL AUTO_INCREMENT,
  `icon` varchar(50) NOT NULL,
  `position` int NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `excursion_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_C74BE8144AB4296F` (`excursion_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `excursion_feature`
--

INSERT INTO `excursion_feature` (`id`, `icon`, `position`, `created_at`, `updated_at`, `excursion_id`) VALUES
(1, 'icon', 1, '2026-10-01 18:35:41', '2026-10-01 18:35:41', 1);

-- --------------------------------------------------------

--
-- Structure de la table `excursion_feature_translation`
--

DROP TABLE IF EXISTS `excursion_feature_translation`;
CREATE TABLE IF NOT EXISTS `excursion_feature_translation` (
  `id` int NOT NULL AUTO_INCREMENT,
  `locale` varchar(5) NOT NULL,
  `title` varchar(255) NOT NULL,
  `subtitle` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `excursion_feature_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_EXCURSION_FEATURE_TRANSLATION_LOCALE` (`excursion_feature_id`,`locale`),
  KEY `IDX_C2C0F126AC72FA7` (`excursion_feature_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `excursion_feature_translation`
--

INSERT INTO `excursion_feature_translation` (`id`, `locale`, `title`, `subtitle`, `created_at`, `updated_at`, `excursion_feature_id`) VALUES
(1, 'fr', 'title', 'subtitle', '2026-10-01 19:04:20', '2026-10-01 19:04:20', 1);

-- --------------------------------------------------------

--
-- Structure de la table `excursion_image`
--

DROP TABLE IF EXISTS `excursion_image`;
CREATE TABLE IF NOT EXISTS `excursion_image` (
  `id` int NOT NULL AUTO_INCREMENT,
  `image_path` varchar(255) NOT NULL,
  `image_alt` varchar(255) DEFAULT NULL,
  `position` int NOT NULL,
  `is_main` tinyint NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `excursion_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_17F93B114AB4296F` (`excursion_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `excursion_image`
--

INSERT INTO `excursion_image` (`id`, `image_path`, `image_alt`, `position`, `is_main`, `created_at`, `updated_at`, `excursion_id`) VALUES
(1, 'https://depositphotos.com/photo/hikers-on-excursion-90755536.html', 'principal', 1, 1, '2026-10-01 18:35:57', '2026-10-01 18:35:57', 1);

-- --------------------------------------------------------

--
-- Structure de la table `excursion_image_translation`
--

DROP TABLE IF EXISTS `excursion_image_translation`;
CREATE TABLE IF NOT EXISTS `excursion_image_translation` (
  `id` int NOT NULL AUTO_INCREMENT,
  `locale` varchar(5) NOT NULL,
  `caption` varchar(255) DEFAULT NULL,
  `alt_text` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `excursion_image_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_EXCURSION_IMAGE_TRANSLATION_LOCALE` (`excursion_image_id`,`locale`),
  KEY `IDX_52DA12B18376A9E0` (`excursion_image_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `excursion_image_translation`
--

INSERT INTO `excursion_image_translation` (`id`, `locale`, `caption`, `alt_text`, `created_at`, `updated_at`, `excursion_image_id`) VALUES
(1, 'fr', 'caption', 'all text', '2026-10-01 19:04:39', '2026-10-01 19:04:39', 1);

-- --------------------------------------------------------

--
-- Structure de la table `excursion_inclusion`
--

DROP TABLE IF EXISTS `excursion_inclusion`;
CREATE TABLE IF NOT EXISTS `excursion_inclusion` (
  `id` int NOT NULL AUTO_INCREMENT,
  `type` varchar(20) NOT NULL,
  `icon` varchar(50) DEFAULT NULL,
  `position` int NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `excursion_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_C2A68FC54AB4296F` (`excursion_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `excursion_inclusion`
--

INSERT INTO `excursion_inclusion` (`id`, `type`, `icon`, `position`, `created_at`, `updated_at`, `excursion_id`) VALUES
(1, 'type', 'icon', 1, '2026-10-01 18:37:01', '2026-10-01 18:37:01', 1);

-- --------------------------------------------------------

--
-- Structure de la table `excursion_inclusion_translation`
--

DROP TABLE IF EXISTS `excursion_inclusion_translation`;
CREATE TABLE IF NOT EXISTS `excursion_inclusion_translation` (
  `id` int NOT NULL AUTO_INCREMENT,
  `locale` varchar(5) NOT NULL,
  `text` varchar(500) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `excursion_inclusion_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_EXCURSION_INCLUSION_TRANSLATION_LOCALE` (`excursion_inclusion_id`,`locale`),
  KEY `IDX_F4BDC890728B1300` (`excursion_inclusion_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `excursion_inclusion_translation`
--

INSERT INTO `excursion_inclusion_translation` (`id`, `locale`, `text`, `created_at`, `updated_at`, `excursion_inclusion_id`) VALUES
(2, 'fr', 'text', '2026-10-01 19:04:58', '2026-10-01 19:04:58', 1);

-- --------------------------------------------------------

--
-- Structure de la table `excursion_itinerary`
--

DROP TABLE IF EXISTS `excursion_itinerary`;
CREATE TABLE IF NOT EXISTS `excursion_itinerary` (
  `id` int NOT NULL AUTO_INCREMENT,
  `step_number` int NOT NULL,
  `time` time DEFAULT NULL,
  `duration` int DEFAULT NULL,
  `position` int NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `excursion_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_6C9514E74AB4296F` (`excursion_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `excursion_itinerary`
--

INSERT INTO `excursion_itinerary` (`id`, `step_number`, `time`, `duration`, `position`, `created_at`, `updated_at`, `excursion_id`) VALUES
(1, 1, '03:37:15', 1, 1, '2026-10-01 18:37:15', '2026-10-01 18:37:15', 1);

-- --------------------------------------------------------

--
-- Structure de la table `excursion_itinerary_translation`
--

DROP TABLE IF EXISTS `excursion_itinerary_translation`;
CREATE TABLE IF NOT EXISTS `excursion_itinerary_translation` (
  `id` int NOT NULL AUTO_INCREMENT,
  `locale` varchar(5) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` longtext,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `excursion_itinerary_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_EXCURSION_ITINERARY_TRANSLATION_LOCALE` (`excursion_itinerary_id`,`locale`),
  KEY `IDX_55239BA26427B3E7` (`excursion_itinerary_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `excursion_itinerary_translation`
--

INSERT INTO `excursion_itinerary_translation` (`id`, `locale`, `title`, `description`, `created_at`, `updated_at`, `excursion_itinerary_id`) VALUES
(1, 'fr', 'title', 'description', '2026-10-01 19:05:29', '2026-10-01 19:05:29', 1);

-- --------------------------------------------------------

--
-- Structure de la table `excursion_translation`
--

DROP TABLE IF EXISTS `excursion_translation`;
CREATE TABLE IF NOT EXISTS `excursion_translation` (
  `id` int NOT NULL AUTO_INCREMENT,
  `locale` varchar(5) NOT NULL,
  `title` varchar(255) NOT NULL,
  `subtitle` varchar(255) DEFAULT NULL,
  `description` longtext,
  `location` varchar(255) DEFAULT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` longtext,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `excursion_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_EXCURSION_TRANSLATION_LOCALE` (`excursion_id`,`locale`),
  KEY `IDX_D568992B4AB4296F` (`excursion_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `excursion_translation`
--

INSERT INTO `excursion_translation` (`id`, `locale`, `title`, `subtitle`, `description`, `location`, `meta_title`, `meta_description`, `created_at`, `updated_at`, `excursion_id`) VALUES
(1, 'fr', 'title', 'subtitle', 'description', 'location', 'meta title', 'meta description', '2026-10-01 18:37:45', '2026-10-01 18:37:45', 1);

-- --------------------------------------------------------

--
-- Structure de la table `messenger_messages`
--

DROP TABLE IF EXISTS `messenger_messages`;
CREATE TABLE IF NOT EXISTS `messenger_messages` (
  `id` bigint NOT NULL AUTO_INCREMENT,
  `body` longtext NOT NULL,
  `headers` longtext NOT NULL,
  `queue_name` varchar(190) NOT NULL,
  `created_at` datetime NOT NULL,
  `available_at` datetime NOT NULL,
  `delivered_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750` (`queue_name`,`available_at`,`delivered_at`,`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `messenger_messages`
--

INSERT INTO `messenger_messages` (`id`, `body`, `headers`, `queue_name`, `created_at`, `available_at`, `delivered_at`) VALUES
(1, 'O:36:\\\"Symfony\\\\Component\\\\Messenger\\\\Envelope\\\":2:{s:44:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Envelope\\0stamps\\\";a:1:{s:46:\\\"Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\\";a:1:{i:0;O:46:\\\"Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\\":1:{s:55:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\0busName\\\";s:21:\\\"messenger.bus.default\\\";}}}s:45:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Envelope\\0message\\\";O:51:\\\"Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\\":2:{s:60:\\\"\\0Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\0message\\\";O:28:\\\"Symfony\\\\Component\\\\Mime\\\\Email\\\":6:{i:0;N;i:1;N;i:2;s:4993:\\\"<!DOCTYPE html>\n<html lang=\\\"fr\\\">\n<head>\n    <meta charset=\\\"UTF-8\\\">\n    <meta name=\\\"viewport\\\" content=\\\"width=device-width, initial-scale=1.0\\\">\n    <title>Nouveau message de contact</title>\n    <style>\n        body {\n            font-family: \\\'Helvetica Neue\\\', Arial, sans-serif;\n            line-height: 1.6;\n            color: #333333;\n            background-color: #f8fafc;\n            margin: 0;\n            padding: 0;\n        }\n        .container {\n            max-width: 600px;\n            margin: 40px auto;\n            background-color: #ffffff;\n            border-radius: 12px;\n            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);\n            overflow: hidden;\n        }\n        .header {\n            background-color: #063B73;\n            color: #ffffff;\n            padding: 30px 20px;\n            text-align: center;\n        }\n        .header h1 {\n            margin: 0;\n            font-size: 24px;\n            font-weight: 600;\n        }\n        .header p {\n            margin: 10px 0 0;\n            opacity: 0.9;\n        }\n        .content {\n            padding: 30px 40px;\n        }\n        .section-title {\n            color: #0B5ED7;\n            font-size: 18px;\n            font-weight: 600;\n            margin-bottom: 20px;\n            border-bottom: 2px solid #e2e8f0;\n            padding-bottom: 10px;\n        }\n        .info-table {\n            width: 100%;\n            border-collapse: collapse;\n            margin-bottom: 30px;\n        }\n        .info-table td {\n            padding: 12px 0;\n            border-bottom: 1px solid #f1f5f9;\n        }\n        .info-table td.label {\n            font-weight: 600;\n            color: #475569;\n            width: 35%;\n        }\n        .info-table td.value {\n            color: #1e293b;\n        }\n        .message-box {\n            background-color: #f8fafc;\n            border: 1px solid #e2e8f0;\n            border-radius: 8px;\n            padding: 20px;\n            margin-top: 10px;\n            white-space: pre-wrap;\n            color: #334155;\n        }\n        .footer {\n            background-color: #f1f5f9;\n            padding: 20px;\n            text-align: center;\n            font-size: 12px;\n            color: #64748b;\n            border-top: 1px solid #e2e8f0;\n        }\n        @media only screen and (max-width: 600px) {\n            .container {\n                margin: 20px 10px;\n            }\n            .content {\n                padding: 20px;\n            }\n            .info-table td {\n                display: block;\n                width: 100%;\n            }\n            .info-table td.label {\n                padding-bottom: 4px;\n                border-bottom: none;\n            }\n            .info-table td.value {\n                padding-top: 0;\n                padding-bottom: 16px;\n            }\n        }\n    </style>\n</head>\n<body>\n    <div class=\\\"container\\\">\n        <div class=\\\"header\\\">\n            <h1>Nouveau message de contact</h1>\n            <p>LocationMahmoud</p>\n        </div>\n        \n        <div class=\\\"content\\\">\n            <h2 class=\\\"section-title\\\">Informations du visiteur</h2>\n            <table class=\\\"info-table\\\">\n                <tr>\n                    <td class=\\\"label\\\">Prénom :</td>\n                    <td class=\\\"value\\\">ksontini</td>\n                </tr>\n                <tr>\n                    <td class=\\\"label\\\">Nom :</td>\n                    <td class=\\\"value\\\">ahmed</td>\n                </tr>\n                <tr>\n                    <td class=\\\"label\\\">Email :</td>\n                    <td class=\\\"value\\\"><a href=\\\"mailto:med@gmail.com\\\" style=\\\"color: #0B5ED7; text-decoration: none;\\\">med@gmail.com</a></td>\n                </tr>\n                                <tr>\n                    <td class=\\\"label\\\">Téléphone :</td>\n                    <td class=\\\"value\\\"><a href=\\\"tel:93313278\\\" style=\\\"color: #0B5ED7; text-decoration: none;\\\">93313278</a></td>\n                </tr>\n                                <tr>\n                    <td class=\\\"label\\\">Service concerné :</td>\n                    <td class=\\\"value\\\">contact.form.service_groupe</td>\n                </tr>\n                                <tr>\n                    <td class=\\\"label\\\">Date d\\\'envoi :</td>\n                    <td class=\\\"value\\\">22/09/2026 20:15</td>\n                </tr>\n            </table>\n\n            <h2 class=\\\"section-title\\\">Détails du message</h2>\n            <div style=\\\"margin-bottom: 10px;\\\">\n                <span style=\\\"font-weight: 600; color: #475569;\\\">Sujet :</span>\n                <span style=\\\"color: #1e293b; margin-left: 10px;\\\">testzad fazd</span>\n            </div>\n            \n            <div class=\\\"message-box\\\">teststsdazdazd zd azd faerfa</div>\n        </div>\n        \n        <div class=\\\"footer\\\">\n            <p>Ce message a été envoyé automatiquement depuis le formulaire de contact du site LocationMahmoud.</p>\n            <p>&copy; 2026 LocationMahmoud. Tous droits réservés.</p>\n        </div>\n    </div>\n</body>\n</html>\n\\\";i:3;s:5:\\\"utf-8\\\";i:4;a:0:{}i:5;a:2:{i:0;O:37:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\\":2:{s:46:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\0headers\\\";a:4:{s:4:\\\"from\\\";a:1:{i:0;O:47:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:4:\\\"From\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:58:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\0addresses\\\";a:1:{i:0;O:30:\\\"Symfony\\\\Component\\\\Mime\\\\Address\\\":2:{s:39:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0address\\\";s:25:\\\"contact@tunisiatravel.com\\\";s:36:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0name\\\";s:0:\\\"\\\";}}}}s:2:\\\"to\\\";a:1:{i:0;O:47:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:2:\\\"To\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:58:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\0addresses\\\";a:1:{i:0;O:30:\\\"Symfony\\\\Component\\\\Mime\\\\Address\\\":2:{s:39:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0address\\\";s:26:\\\"ahmedksontini122@gmail.com\\\";s:36:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0name\\\";s:0:\\\"\\\";}}}}s:8:\\\"reply-to\\\";a:1:{i:0;O:47:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:8:\\\"Reply-To\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:58:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\0addresses\\\";a:1:{i:0;O:30:\\\"Symfony\\\\Component\\\\Mime\\\\Address\\\":2:{s:39:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0address\\\";s:13:\\\"med@gmail.com\\\";s:36:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0name\\\";s:0:\\\"\\\";}}}}s:7:\\\"subject\\\";a:1:{i:0;O:48:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\UnstructuredHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:7:\\\"Subject\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:55:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\UnstructuredHeader\\0value\\\";s:46:\\\"Nouveau message de contact — LocationMahmoud\\\";}}}s:49:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\0lineLength\\\";i:76;}i:1;N;}}s:61:\\\"\\0Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\0envelope\\\";N;}}', '[]', 'default', '2026-09-22 20:15:03', '2026-09-22 20:15:03', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `pickup_region`
--

DROP TABLE IF EXISTS `pickup_region`;
CREATE TABLE IF NOT EXISTS `pickup_region` (
  `id` int NOT NULL AUTO_INCREMENT,
  `slug` varchar(255) NOT NULL,
  `is_active` tinyint NOT NULL,
  `position` int NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_9D13BDA0989D9B62` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Structure de la table `pickup_region_translation`
--

DROP TABLE IF EXISTS `pickup_region_translation`;
CREATE TABLE IF NOT EXISTS `pickup_region_translation` (
  `id` int NOT NULL AUTO_INCREMENT,
  `locale` varchar(5) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` longtext,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `pickup_region_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_PICKUP_REGION_TRANSLATION_LOCALE` (`pickup_region_id`,`locale`),
  KEY `IDX_F17EEF8F6AEC09E6` (`pickup_region_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Structure de la table `reservation`
--

DROP TABLE IF EXISTS `reservation`;
CREATE TABLE IF NOT EXISTS `reservation` (
  `id` int NOT NULL AUTO_INCREMENT,
  `date` date NOT NULL,
  `adults_count` int NOT NULL,
  `children_count` int NOT NULL,
  `pickup_region` varchar(255) DEFAULT NULL,
  `pickup_address` longtext,
  `contact_name` varchar(255) NOT NULL,
  `contact_phone` varchar(50) NOT NULL,
  `contact_email` varchar(255) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `status` varchar(20) NOT NULL,
  `payment_status` varchar(20) NOT NULL,
  `payment_method` varchar(20) DEFAULT NULL,
  `notes` longtext,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `excursion_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_42C849554AB4296F` (`excursion_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Structure de la table `reservation_transfere`
--

DROP TABLE IF EXISTS `reservation_transfere`;
CREATE TABLE IF NOT EXISTS `reservation_transfere` (
  `id` int NOT NULL AUTO_INCREMENT,
  `transfere_id` int NOT NULL,
  `nom` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `prenom` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(180) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telephone` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `transfere_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pickup_location` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dropoff_location` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pickup_date` date NOT NULL,
  `pickup_time` time NOT NULL,
  `return_pickup_location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `return_dropoff_location` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `return_pickup_date` date DEFAULT NULL,
  `return_pickup_time` time DEFAULT NULL,
  `nombre_passagers` int NOT NULL,
  `nombre_bagages` int NOT NULL,
  `message` longtext COLLATE utf8mb4_unicode_ci,
  `prix_total` decimal(12,2) NOT NULL,
  `devise` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` datetime NOT NULL,
  `numero_vol` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `compagnie_vol` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_6EB9EDD6D4397B57` (`transfere_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `reservation_transfere`
--

INSERT INTO `reservation_transfere` (`id`, `transfere_id`, `nom`, `prenom`, `email`, `telephone`, `transfere_type`, `pickup_location`, `dropoff_location`, `pickup_date`, `pickup_time`, `return_pickup_location`, `return_dropoff_location`, `return_pickup_date`, `return_pickup_time`, `nombre_passagers`, `nombre_bagages`, `message`, `prix_total`, `devise`, `status`, `created_at`, `numero_vol`, `compagnie_vol`) VALUES
(1, 1, 'Test', 'OneWay', 'oneway2@example.com', '+21600000000', 'one_way', 'aireport', 'aireport', '2026-10-01', '10:30:00', NULL, NULL, NULL, NULL, 1, 0, NULL, 15.00, 'EUR', 'pending', '2026-09-17 21:32:13', NULL, NULL),
(2, 1, 'Test', 'Return', 'return@example.com', '+21600000000', 'return', 'aireport', 'aireport', '2026-10-10', '10:30:00', 'Hammamet Hotel', 'Tunis Airport', '2026-10-12', '18:00:00', 1, 0, NULL, 30.00, 'EUR', 'pending', '2026-09-17 21:32:19', NULL, NULL),
(3, 1, 'Nom Complet Test', 'Nom Complet Test', 'full-name2@example.com', '+21600000000', 'one_way', 'aireport', 'aireport', '2026-11-01', '10:00:00', NULL, NULL, NULL, NULL, 1, 0, NULL, 15.00, 'EUR', 'pending', '2026-09-17 21:55:12', NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `review`
--

DROP TABLE IF EXISTS `review`;
CREATE TABLE IF NOT EXISTS `review` (
  `id` int NOT NULL AUTO_INCREMENT,
  `customer_name` varchar(255) NOT NULL,
  `customer_location` varchar(255) DEFAULT NULL,
  `rating` int NOT NULL,
  `comment` longtext,
  `status` varchar(20) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `excursion_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_794381C64AB4296F` (`excursion_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Structure de la table `saison`
--

DROP TABLE IF EXISTS `saison`;
CREATE TABLE IF NOT EXISTS `saison` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_debut` date NOT NULL,
  `date_fin` date NOT NULL,
  `actif` tinyint(1) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `saison`
--

INSERT INTO `saison` (`id`, `nom`, `date_debut`, `date_fin`, `actif`) VALUES
(6, 'saison forte', '2026-06-15', '2026-09-15', 1);

-- --------------------------------------------------------

--
-- Structure de la table `transfere`
--

DROP TABLE IF EXISTS `transfere`;
CREATE TABLE IF NOT EXISTS `transfere` (
  `id` int NOT NULL AUTO_INCREMENT,
  `lieu_depart` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lieu_arrivee` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `actif` tinyint(1) NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `prix_normal` decimal(10,2) NOT NULL,
  `prix_saison_forte` decimal(10,2) NOT NULL,
  `description` longtext COLLATE utf8mb4_unicode_ci,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_TRANSFERE_SLUG` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `transfere`
--

INSERT INTO `transfere` (`id`, `lieu_depart`, `lieu_arrivee`, `actif`, `slug`, `prix_normal`, `prix_saison_forte`, `description`) VALUES
(1, 'aireport', 'aireport', 1, 'aireport-aireport', 50.00, 100.00, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `transfere_translation`
--

DROP TABLE IF EXISTS `transfere_translation`;
CREATE TABLE IF NOT EXISTS `transfere_translation` (
  `id` int NOT NULL AUTO_INCREMENT,
  `transfere_id` int NOT NULL,
  `locale` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lieu_depart` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lieu_arrivee` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `duree` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_TRANSFERE_TRANSLATION_LOCALE` (`transfere_id`,`locale`),
  KEY `IDX_992FF35BD4397B57` (`transfere_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `transfere_translation`
--

INSERT INTO `transfere_translation` (`id`, `transfere_id`, `locale`, `lieu_depart`, `lieu_arrivee`, `duree`) VALUES
(1, 1, 'fr', 'aireport', 'aireport', '40'),
(2, 1, 'en', 'airport', 'airport', '20'),
(3, 1, 'es', 'aeropuerto', 'aeropuerto', '30'),
(4, 1, 'de', 'Flughafen', 'Flughafen', '10'),
(5, 1, 'it', 'aeroporto', 'aeroporto', '50');

-- --------------------------------------------------------

--
-- Structure de la table `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE IF NOT EXISTS `user` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(180) NOT NULL,
  `roles` json NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_IDENTIFIER_EMAIL` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `user`
--

INSERT INTO `user` (`id`, `email`, `roles`, `password`) VALUES
(7, 'qa-excursion@test.local', '[\"ROLE_ADMIN\"]', '$2y$13$8bv/pSnLRsY0qtrGdfs7s.jOdZc0mujHYLTgdi0EyqQuFLc4ssh/O'),
(5, 'ahmed@gmail.com', '[\"ROLE_ADMIN\"]', '$2y$13$NTsSuCFvFACye9AgNhktJOqL99Ir3HJ4FvGKfHPk565bwePyr6JIq');

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `category`
--
ALTER TABLE `category`
  ADD CONSTRAINT `FK_64C19C1727ACA70` FOREIGN KEY (`parent_id`) REFERENCES `category` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `category_translation`
--
ALTER TABLE `category_translation`
  ADD CONSTRAINT `FK_3F2070412469DE2` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `contact_message`
--
ALTER TABLE `contact_message`
  ADD CONSTRAINT `FK_2C9211FEE7A1254A` FOREIGN KEY (`contact_id`) REFERENCES `contact` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `destination_translation`
--
ALTER TABLE `destination_translation`
  ADD CONSTRAINT `FK_915BAE54816C6140` FOREIGN KEY (`destination_id`) REFERENCES `destination` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `excursion`
--
ALTER TABLE `excursion`
  ADD CONSTRAINT `FK_9B08E72F12469DE2` FOREIGN KEY (`category_id`) REFERENCES `category` (`id`),
  ADD CONSTRAINT `FK_9B08E72F816C6140` FOREIGN KEY (`destination_id`) REFERENCES `destination` (`id`);

--
-- Contraintes pour la table `excursion_badge`
--
ALTER TABLE `excursion_badge`
  ADD CONSTRAINT `FK_2C3477534AB4296F` FOREIGN KEY (`excursion_id`) REFERENCES `excursion` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `excursion_badge_translation`
--
ALTER TABLE `excursion_badge_translation`
  ADD CONSTRAINT `FK_73F56BE849714E71` FOREIGN KEY (`excursion_badge_id`) REFERENCES `excursion_badge` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `excursion_faq`
--
ALTER TABLE `excursion_faq`
  ADD CONSTRAINT `FK_5E6CD5BF4AB4296F` FOREIGN KEY (`excursion_id`) REFERENCES `excursion` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `excursion_faq_translation`
--
ALTER TABLE `excursion_faq_translation`
  ADD CONSTRAINT `FK_E3ED0BE71008606A` FOREIGN KEY (`excursion_faq_id`) REFERENCES `excursion_faq` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `excursion_feature`
--
ALTER TABLE `excursion_feature`
  ADD CONSTRAINT `FK_C74BE8144AB4296F` FOREIGN KEY (`excursion_id`) REFERENCES `excursion` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `excursion_feature_translation`
--
ALTER TABLE `excursion_feature_translation`
  ADD CONSTRAINT `FK_C2C0F126AC72FA7` FOREIGN KEY (`excursion_feature_id`) REFERENCES `excursion_feature` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `excursion_image`
--
ALTER TABLE `excursion_image`
  ADD CONSTRAINT `FK_17F93B114AB4296F` FOREIGN KEY (`excursion_id`) REFERENCES `excursion` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `excursion_image_translation`
--
ALTER TABLE `excursion_image_translation`
  ADD CONSTRAINT `FK_52DA12B18376A9E0` FOREIGN KEY (`excursion_image_id`) REFERENCES `excursion_image` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `excursion_inclusion`
--
ALTER TABLE `excursion_inclusion`
  ADD CONSTRAINT `FK_C2A68FC54AB4296F` FOREIGN KEY (`excursion_id`) REFERENCES `excursion` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `excursion_inclusion_translation`
--
ALTER TABLE `excursion_inclusion_translation`
  ADD CONSTRAINT `FK_F4BDC890728B1300` FOREIGN KEY (`excursion_inclusion_id`) REFERENCES `excursion_inclusion` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `excursion_itinerary`
--
ALTER TABLE `excursion_itinerary`
  ADD CONSTRAINT `FK_6C9514E74AB4296F` FOREIGN KEY (`excursion_id`) REFERENCES `excursion` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `excursion_itinerary_translation`
--
ALTER TABLE `excursion_itinerary_translation`
  ADD CONSTRAINT `FK_55239BA26427B3E7` FOREIGN KEY (`excursion_itinerary_id`) REFERENCES `excursion_itinerary` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `excursion_translation`
--
ALTER TABLE `excursion_translation`
  ADD CONSTRAINT `FK_D568992B4AB4296F` FOREIGN KEY (`excursion_id`) REFERENCES `excursion` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `pickup_region_translation`
--
ALTER TABLE `pickup_region_translation`
  ADD CONSTRAINT `FK_F17EEF8F6AEC09E6` FOREIGN KEY (`pickup_region_id`) REFERENCES `pickup_region` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `reservation`
--
ALTER TABLE `reservation`
  ADD CONSTRAINT `FK_42C849554AB4296F` FOREIGN KEY (`excursion_id`) REFERENCES `excursion` (`id`);

--
-- Contraintes pour la table `reservation_transfere`
--
ALTER TABLE `reservation_transfere`
  ADD CONSTRAINT `FK_RESERVATION_TRANSFERE_TRANSFER` FOREIGN KEY (`transfere_id`) REFERENCES `transfere` (`id`) ON DELETE RESTRICT;

--
-- Contraintes pour la table `review`
--
ALTER TABLE `review`
  ADD CONSTRAINT `FK_794381C64AB4296F` FOREIGN KEY (`excursion_id`) REFERENCES `excursion` (`id`);

--
-- Contraintes pour la table `transfere_translation`
--
ALTER TABLE `transfere_translation`
  ADD CONSTRAINT `FK_TRANSFERE_TRANSLATION_TRANSFERE` FOREIGN KEY (`transfere_id`) REFERENCES `transfere` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
