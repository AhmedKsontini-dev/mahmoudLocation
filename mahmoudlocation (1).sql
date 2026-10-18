-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : dim. 18 oct. 2026 à 14:44
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
-- Structure de la table `blog_article`
--

DROP TABLE IF EXISTS `blog_article`;
CREATE TABLE IF NOT EXISTS `blog_article` (
  `id` int NOT NULL AUTO_INCREMENT,
  `slug` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `image_alt` varchar(255) DEFAULT NULL,
  `author_name` varchar(40) DEFAULT NULL,
  `read_time` int NOT NULL,
  `status` varchar(20) NOT NULL,
  `is_featured` tinyint NOT NULL,
  `published_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_BLOG_ARTICLE_SLUG` (`slug`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Structure de la table `blog_article_category`
--

DROP TABLE IF EXISTS `blog_article_category`;
CREATE TABLE IF NOT EXISTS `blog_article_category` (
  `blog_article_id` int NOT NULL,
  `blog_category_id` int NOT NULL,
  PRIMARY KEY (`blog_article_id`,`blog_category_id`),
  KEY `IDX_27A7C64C9452A475` (`blog_article_id`),
  KEY `IDX_27A7C64CCB76011C` (`blog_category_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Structure de la table `blog_article_image`
--

DROP TABLE IF EXISTS `blog_article_image`;
CREATE TABLE IF NOT EXISTS `blog_article_image` (
  `id` int NOT NULL AUTO_INCREMENT,
  `article_id` int NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `image_alt` varchar(255) DEFAULT NULL,
  `position` int NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_C10CBC227294869C` (`article_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Structure de la table `blog_article_translation`
--

DROP TABLE IF EXISTS `blog_article_translation`;
CREATE TABLE IF NOT EXISTS `blog_article_translation` (
  `id` int NOT NULL AUTO_INCREMENT,
  `locale` varchar(5) NOT NULL,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `excerpt` longtext,
  `content` longtext NOT NULL,
  `seo_title` varchar(255) DEFAULT NULL,
  `seo_description` longtext,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `article_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_BLOG_ARTICLE_TRANSLATION_LOCALE` (`article_id`,`locale`),
  KEY `IDX_6CFC2FBD7294869C` (`article_id`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Structure de la table `blog_article_video`
--

DROP TABLE IF EXISTS `blog_article_video`;
CREATE TABLE IF NOT EXISTS `blog_article_video` (
  `id` int NOT NULL AUTO_INCREMENT,
  `article_id` int NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `video_path` varchar(255) DEFAULT NULL,
  `external_url` varchar(500) DEFAULT NULL,
  `thumbnail` varchar(255) DEFAULT NULL,
  `position` int NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_78F662517294869C` (`article_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Structure de la table `blog_category`
--

DROP TABLE IF EXISTS `blog_category`;
CREATE TABLE IF NOT EXISTS `blog_category` (
  `id` int NOT NULL AUTO_INCREMENT,
  `slug` varchar(255) NOT NULL,
  `is_active` tinyint NOT NULL,
  `position` int NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_BLOG_CATEGORY_SLUG` (`slug`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Structure de la table `blog_category_translation`
--

DROP TABLE IF EXISTS `blog_category_translation`;
CREATE TABLE IF NOT EXISTS `blog_category_translation` (
  `id` int NOT NULL AUTO_INCREMENT,
  `locale` varchar(5) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` longtext,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `category_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_BLOG_CATEGORY_TRANSLATION_LOCALE` (`category_id`,`locale`),
  KEY `IDX_B8BB560712469DE2` (`category_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Structure de la table `category`
--

DROP TABLE IF EXISTS `category`;
CREATE TABLE IF NOT EXISTS `category` (
  `id` int NOT NULL AUTO_INCREMENT,
  `icon` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `position` int NOT NULL,
  `is_active` tinyint NOT NULL,
  `slug` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `parent_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_CATEGORY_SLUG` (`slug`),
  KEY `IDX_64C19C1727ACA70` (`parent_id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `category`
--

INSERT INTO `category` (`id`, `icon`, `position`, `is_active`, `slug`, `created_at`, `updated_at`, `parent_id`) VALUES
(5, '🏛️', 0, 1, 'culture-patrimoine', '2026-10-18 13:50:56', '2026-10-18 13:50:56', 5),
(6, '🌴', 0, 1, 'nature-randonnee', '2026-10-18 13:51:03', '2026-10-18 13:51:03', 6),
(7, '🏖️', 0, 1, 'mer-plages', '2026-10-18 13:51:10', '2026-10-18 13:51:10', 7),
(8, '🐪', 0, 1, 'desert-sahara', '2026-10-18 13:51:20', '2026-10-18 13:51:20', 8),
(9, '🏜️', 0, 1, 'aventure', '2026-10-18 13:51:26', '2026-10-18 13:51:26', 9),
(10, '🏺', 0, 1, 'histoire-archeologie', '2026-10-18 13:51:31', '2026-10-18 13:51:31', 10),
(11, '🚤', 0, 1, 'excursions-en-bateau', '2026-10-18 13:51:41', '2026-10-18 13:51:41', 11),
(12, '🚙', 0, 1, 'circuits-prives', '2026-10-18 13:51:47', '2026-10-18 13:51:47', 12),
(13, '🏝️', 0, 1, 'iles-decouverte', '2026-10-18 13:52:03', '2026-10-18 13:52:03', 13),
(14, '🕌', 0, 1, 'villes-medinas', '2026-10-18 13:52:14', '2026-10-18 13:52:14', 14);

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
) ENGINE=InnoDB AUTO_INCREMENT=68 DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `category_translation`
--

INSERT INTO `category_translation` (`id`, `locale`, `name`, `description`, `created_at`, `updated_at`, `category_id`) VALUES
(18, 'fr', 'Culture & Patrimoine', NULL, '2026-10-18 13:50:56', '2026-10-18 13:50:56', 5),
(19, 'en', 'Culture & Heritage', NULL, '2026-10-18 13:50:56', '2026-10-18 13:50:56', 5),
(20, 'es', 'Culture & Patrimoine', NULL, '2026-10-18 13:50:56', '2026-10-18 13:50:56', 5),
(21, 'de', 'Culture & Patrimoine', NULL, '2026-10-18 13:50:56', '2026-10-18 13:50:56', 5),
(22, 'it', 'Culture & Patrimoine', NULL, '2026-10-18 13:50:56', '2026-10-18 13:50:56', 5),
(23, 'fr', 'Nature & Randonnée', NULL, '2026-10-18 13:51:03', '2026-10-18 13:51:03', 6),
(24, 'en', 'Nature & Hiking', NULL, '2026-10-18 13:51:03', '2026-10-18 13:51:03', 6),
(25, 'es', 'Nature & Randonnée', NULL, '2026-10-18 13:51:03', '2026-10-18 13:51:03', 6),
(26, 'de', 'Nature & Randonnée', NULL, '2026-10-18 13:51:03', '2026-10-18 13:51:03', 6),
(27, 'it', 'Nature & Randonnée', NULL, '2026-10-18 13:51:03', '2026-10-18 13:51:03', 6),
(28, 'fr', 'Mer & Plages', NULL, '2026-10-18 13:51:10', '2026-10-18 13:51:10', 7),
(29, 'en', 'Sea and Beaches', NULL, '2026-10-18 13:51:10', '2026-10-18 13:51:10', 7),
(30, 'es', 'Mer & Plages', NULL, '2026-10-18 13:51:10', '2026-10-18 13:51:10', 7),
(31, 'de', 'Mer & Plages', NULL, '2026-10-18 13:51:10', '2026-10-18 13:51:10', 7),
(32, 'it', 'Mer & Plages', NULL, '2026-10-18 13:51:10', '2026-10-18 13:51:10', 7),
(33, 'fr', 'Désert & Sahara', NULL, '2026-10-18 13:51:20', '2026-10-18 13:51:20', 8),
(34, 'en', 'Desert and Sahara', NULL, '2026-10-18 13:51:20', '2026-10-18 13:51:20', 8),
(35, 'es', 'Désert & Sahara', NULL, '2026-10-18 13:51:20', '2026-10-18 13:51:20', 8),
(36, 'de', 'Désert & Sahara', NULL, '2026-10-18 13:51:20', '2026-10-18 13:51:20', 8),
(37, 'it', 'Désert & Sahara', NULL, '2026-10-18 13:51:20', '2026-10-18 13:51:20', 8),
(38, 'fr', 'Aventure', NULL, '2026-10-18 13:51:26', '2026-10-18 13:51:26', 9),
(39, 'en', 'Adventure', NULL, '2026-10-18 13:51:26', '2026-10-18 13:51:26', 9),
(40, 'es', 'Aventure', NULL, '2026-10-18 13:51:26', '2026-10-18 13:51:26', 9),
(41, 'de', 'Aventure', NULL, '2026-10-18 13:51:26', '2026-10-18 13:51:26', 9),
(42, 'it', 'Aventure', NULL, '2026-10-18 13:51:26', '2026-10-18 13:51:26', 9),
(43, 'fr', 'Histoire & Archéologie', NULL, '2026-10-18 13:51:31', '2026-10-18 13:51:31', 10),
(44, 'en', 'History & Archaeology', NULL, '2026-10-18 13:51:31', '2026-10-18 13:51:31', 10),
(45, 'es', 'Histoire & Archéologie', NULL, '2026-10-18 13:51:31', '2026-10-18 13:51:31', 10),
(46, 'de', 'Histoire & Archéologie', NULL, '2026-10-18 13:51:31', '2026-10-18 13:51:31', 10),
(47, 'it', 'Histoire & Archéologie', NULL, '2026-10-18 13:51:31', '2026-10-18 13:51:31', 10),
(48, 'fr', 'Excursions en bateau', NULL, '2026-10-18 13:51:41', '2026-10-18 13:51:41', 11),
(49, 'en', 'Boat excursions', NULL, '2026-10-18 13:51:41', '2026-10-18 13:51:41', 11),
(50, 'es', 'Excursions en bateau', NULL, '2026-10-18 13:51:41', '2026-10-18 13:51:41', 11),
(51, 'de', 'Excursions en bateau', NULL, '2026-10-18 13:51:41', '2026-10-18 13:51:41', 11),
(52, 'it', 'Excursions en bateau', NULL, '2026-10-18 13:51:41', '2026-10-18 13:51:41', 11),
(53, 'fr', 'Circuits privés', NULL, '2026-10-18 13:51:47', '2026-10-18 13:51:47', 12),
(54, 'en', 'Private tours', NULL, '2026-10-18 13:51:47', '2026-10-18 13:51:47', 12),
(55, 'es', 'Circuits privés', NULL, '2026-10-18 13:51:47', '2026-10-18 13:51:47', 12),
(56, 'de', 'Circuits privés', NULL, '2026-10-18 13:51:47', '2026-10-18 13:51:47', 12),
(57, 'it', 'Circuits privés', NULL, '2026-10-18 13:51:47', '2026-10-18 13:51:47', 12),
(58, 'fr', 'Îles & Découverte', NULL, '2026-10-18 13:52:03', '2026-10-18 13:52:03', 13),
(59, 'en', 'Islands & Discovery', NULL, '2026-10-18 13:52:03', '2026-10-18 13:52:03', 13),
(60, 'es', 'Îles & Découverte', NULL, '2026-10-18 13:52:03', '2026-10-18 13:52:03', 13),
(61, 'de', 'Îles & Découverte', NULL, '2026-10-18 13:52:03', '2026-10-18 13:52:03', 13),
(62, 'it', 'Îles & Découverte', NULL, '2026-10-18 13:52:03', '2026-10-18 13:52:03', 13),
(63, 'fr', 'Villes & Médinas', NULL, '2026-10-18 13:52:14', '2026-10-18 13:52:14', 14),
(64, 'en', 'Cities & Medinas', NULL, '2026-10-18 13:52:14', '2026-10-18 13:52:14', 14),
(65, 'es', 'Villes & Médinas', NULL, '2026-10-18 13:52:14', '2026-10-18 13:52:14', 14),
(66, 'de', 'Villes & Médinas', NULL, '2026-10-18 13:52:14', '2026-10-18 13:52:14', 14),
(67, 'it', 'Villes & Médinas', NULL, '2026-10-18 13:52:14', '2026-10-18 13:52:14', 14);

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
  `is_read` tinyint NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `contact`
--

INSERT INTO `contact` (`id`, `first_name`, `last_name`, `email`, `phone`, `service_category`, `reservation_number`, `subject`, `message`, `created_at`, `request_type`, `is_archived`, `is_pinned`, `is_read`) VALUES
(1, 'ksontini', 'ahmed', 'med@gmail.com', '93313278', 'groupe', NULL, 'testzad fazd', 'teststsdazdazd zd azd faerfa', '2026-09-22 20:15:01', '', 0, 0, 1),
(2, 'tttt', 'tttt', 'ttttttt@gmail.com', '93312783', 'transfert', NULL, 'azadadadzd', 'azda zda da zdazdazd da f a a azd', '2026-09-22 20:29:47', '', 0, 0, 1),
(3, 'ahmed', 'Ksontini', 'ahmed@gmail.com', '93313278', 'autre', NULL, 'tesetst', 'setstse etse t zsreze zaedfz', '2026-09-22 21:19:17', '', 0, 0, 1),
(4, 'hmed', 'hmeeed', 'hmeed@gmail.com', '93313278', 'location', 'fgzf', 'efzefzfz fzefzefz ef', 'zef zef ergzreth zertg earga ergf a', '2026-09-22 21:37:16', 'partnership', 0, 0, 1),
(5, 'zdfzd', 'fzdfzdf', 'zdfzdfdzf@gmail.com', '12345678', 'autre', NULL, 'fedfgegegeg e', 'fgefge fg efg', '2026-09-22 21:45:55', 'partnership', 0, 0, 1),
(6, 'ahmed', 'ksontini', 'ahmedksontini560@gmail.com', '93313278', 'transfert', NULL, 'teste de email', 'test contact email', '2026-09-25 20:07:19', 'reservation', 0, 0, 1),
(7, 'ksontini', 'amine', 'ahmedksontini560@gmail.com', '93313278', 'location', '123', 'TESTSTST', 'TETSTSTETSTTE', '2026-10-17 16:40:56', 'information', 0, 0, 1);

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
  `contact_id` int DEFAULT NULL,
  `recipient_name` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_2C9211FEE7A1254A` (`contact_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `contact_message`
--

INSERT INTO `contact_message` (`id`, `status`, `recipient`, `subject`, `body`, `created_at`, `updated_at`, `sent_at`, `attachment_name`, `attachment_path`, `contact_id`, `recipient_name`) VALUES
(1, 'sent', 'ahmedksontini560@gmail.com', 'Re: teste de email', 'Bonjour ahmed,', '2026-10-17 15:20:51', '2026-10-17 15:20:51', '2026-10-17 15:20:51', NULL, NULL, 6, NULL),
(2, 'sent', 'ahmedksontini560@gmail.com', 'reserfvation', 'tyesssts', '2026-10-17 16:12:30', '2026-10-17 16:12:30', '2026-10-17 16:12:30', NULL, NULL, NULL, 'ahmed ksontini'),
(3, 'sent', 'ahmedksontini560@gmail.com', 'Re: teste de email', 'Bonjour ahmed,', '2026-10-17 16:12:41', '2026-10-17 16:12:41', '2026-10-17 16:12:41', NULL, NULL, 6, 'ahmed ksontini'),
(4, 'sent', 'ahmedksontini560@gmail.com', 'Re: TESTSTST', 'Bonjour ksontini, TESTETS TET E', '2026-10-17 16:41:29', '2026-10-17 16:41:29', '2026-10-17 16:41:29', NULL, NULL, 7, 'ksontini amine'),
(5, 'sent', 'ahmedksontini122@gmail.com', 'tete', 'tzetze zf zf', '2026-10-17 16:42:25', '2026-10-17 16:42:25', '2026-10-17 16:42:25', NULL, NULL, NULL, 'tetet');

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
(2, 'EUR', '€', 0.298507),
(3, 'USD', '$', 0.335570);

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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `destination`
--

INSERT INTO `destination` (`id`, `region`, `latitude`, `longitude`, `is_active`, `slug`, `created_at`, `updated_at`) VALUES
(3, 'Cap Bon Region', NULL, NULL, 1, 'cap-bon-region-fr', '2026-10-17 23:23:45', '2026-10-17 23:23:45');

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
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `destination_translation`
--

INSERT INTO `destination_translation` (`id`, `locale`, `name`, `description`, `created_at`, `updated_at`, `destination_id`) VALUES
(11, 'fr', 'Cap Bon Region  FR', 'Cap Bon Region description  FR', '2026-10-17 23:23:45', '2026-10-17 23:23:45', 3),
(12, 'en', 'Cap Bon Region description  ang', 'Cap Bon Region description  ANG', '2026-10-17 23:23:45', '2026-10-17 23:23:45', 3);

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
('DoctrineMigrations\\Version20260926173308', '2026-10-09 16:16:48', 23),
('DoctrineMigrations\\Version20260926174012', '2026-09-26 17:40:51', 263),
('DoctrineMigrations\\Version20260926174219', '2026-09-26 17:43:35', 192),
('DoctrineMigrations\\Version20261009161636', '2026-10-09 16:16:48', 59),
('DoctrineMigrations\\Version20261009170000', '2026-10-09 17:30:34', 27),
('DoctrineMigrations\\Version20261009201852', '2026-10-09 20:19:38', 98),
('DoctrineMigrations\\Version20261015102542', '2026-10-15 09:47:59', 35),
('DoctrineMigrations\\Version20261015111200', '2026-10-15 10:06:40', 99),
('DoctrineMigrations\\Version20261017162200', '2026-10-17 15:37:42', 71),
('DoctrineMigrations\\Version20261018120000', '2026-10-18 14:08:50', 11);

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
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `excursion`
--

INSERT INTO `excursion` (`id`, `type`, `status`, `is_best_seller`, `is_featured`, `duration_hours`, `duration_minutes`, `start_time`, `end_time`, `price_adult`, `price_child`, `price_converted`, `min_participants`, `max_participants`, `cancellation_hours`, `rating_average`, `rating_count`, `booking_count`, `slug`, `created_at`, `updated_at`, `category_id`, `destination_id`) VALUES
(12, 'custom', 'active', 0, 1, 8, 0, '08:00:00', '17:00:00', 220.00, 100.00, NULL, 1, 4, 48, NULL, 0, 0, 'excursions-d-une-journee-au-depart-de-tunisie', '2026-10-18 13:08:17', '2026-10-18 13:08:17', 14, 3);

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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `excursion_badge`
--

INSERT INTO `excursion_badge` (`id`, `badge_type`, `is_active`, `created_at`, `updated_at`, `excursion_id`) VALUES
(4, 'available_now', 1, '2026-10-18 13:08:18', '2026-10-18 13:08:18', 12);

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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `excursion_badge_translation`
--

INSERT INTO `excursion_badge_translation` (`id`, `locale`, `text`, `created_at`, `updated_at`, `excursion_badge_id`) VALUES
(4, 'fr', 'Populaire', '2026-10-18 13:08:18', '2026-10-18 13:08:18', 4),
(5, 'en', 'Popular', '2026-10-18 13:08:18', '2026-10-18 13:08:18', 4),
(6, 'it', 'Populaire', '2026-10-18 13:08:18', '2026-10-18 13:08:18', 4);

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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `excursion_faq`
--

INSERT INTO `excursion_faq` (`id`, `position`, `is_active`, `created_at`, `updated_at`, `excursion_id`) VALUES
(3, 0, 1, '2026-10-18 13:08:17', '2026-10-18 13:08:17', 12),
(4, 0, 1, '2026-10-18 13:08:17', '2026-10-18 13:08:17', 12);

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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `excursion_faq_translation`
--

INSERT INTO `excursion_faq_translation` (`id`, `locale`, `question`, `answer`, `created_at`, `updated_at`, `excursion_faq_id`) VALUES
(4, 'fr', 'Question 1', 'Reponse 1', '2026-10-18 13:08:17', '2026-10-18 13:08:17', 3),
(5, 'en', 'Question 1', 'Answer 1', '2026-10-18 13:08:17', '2026-10-18 13:08:17', 3),
(6, 'it', 'Question 1', 'reponse 1', '2026-10-18 13:08:17', '2026-10-18 13:08:17', 3),
(7, 'fr', 'question 2', 'reponse 2', '2026-10-18 13:08:17', '2026-10-18 13:08:17', 4),
(8, 'en', 'question 2', 'answer 2', '2026-10-18 13:08:17', '2026-10-18 13:08:17', 4),
(9, 'it', 'question 2', 'reponse 2', '2026-10-18 13:08:17', '2026-10-18 13:08:17', 4);

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
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `excursion_feature`
--

INSERT INTO `excursion_feature` (`id`, `icon`, `position`, `created_at`, `updated_at`, `excursion_id`) VALUES
(8, '✓', 0, '2026-10-18 13:08:17', '2026-10-18 13:08:17', 12),
(9, '✓', 0, '2026-10-18 13:08:17', '2026-10-18 13:08:17', 12),
(10, '✓', 0, '2026-10-18 13:08:17', '2026-10-18 13:08:17', 12);

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
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `excursion_feature_translation`
--

INSERT INTO `excursion_feature_translation` (`id`, `locale`, `title`, `subtitle`, `created_at`, `updated_at`, `excursion_feature_id`) VALUES
(14, 'fr', 'Non accessible en fauteuil roulant', NULL, '2026-10-18 13:08:17', '2026-10-18 13:08:17', 8),
(15, 'en', 'Not wheelchair accessible', NULL, '2026-10-18 13:08:17', '2026-10-18 13:08:17', 8),
(16, 'it', 'Non accessible en fauteuil roulant', NULL, '2026-10-18 13:08:17', '2026-10-18 13:08:17', 8),
(17, 'fr', 'Accessible en poussette', NULL, '2026-10-18 13:08:17', '2026-10-18 13:08:17', 9),
(18, 'en', 'Accessible with a stroller', NULL, '2026-10-18 13:08:17', '2026-10-18 13:08:17', 9),
(19, 'it', 'Accessible en poussette', NULL, '2026-10-18 13:08:17', '2026-10-18 13:08:17', 9),
(20, 'fr', 'Animaux aidants acceptés', NULL, '2026-10-18 13:08:17', '2026-10-18 13:08:17', 10),
(21, 'en', 'Assistance animals accepted', NULL, '2026-10-18 13:08:17', '2026-10-18 13:08:17', 10),
(22, 'it', 'Animaux aidants acceptés', NULL, '2026-10-18 13:08:17', '2026-10-18 13:08:17', 10);

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
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `excursion_image`
--

INSERT INTO `excursion_image` (`id`, `image_path`, `image_alt`, `position`, `is_main`, `created_at`, `updated_at`, `excursion_id`) VALUES
(12, '/uploads/excursions/caption-6ad4cadbb41d0.jpg', NULL, 1, 1, '2026-10-18 13:34:19', '2026-10-18 13:34:19', 12),
(13, '/uploads/excursions/caption-3-6ad4cadbb4471.jpg', NULL, 2, 0, '2026-10-18 13:34:19', '2026-10-18 13:34:19', 12),
(14, '/uploads/excursions/caption-1-6ad4cadbb45fd.jpg', NULL, 3, 0, '2026-10-18 13:34:19', '2026-10-18 13:34:19', 12),
(15, '/uploads/excursions/caption-2-6ad4cadbb47e9.jpg', NULL, 4, 0, '2026-10-18 13:34:19', '2026-10-18 13:34:19', 12);

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
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb3;

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
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `excursion_inclusion`
--

INSERT INTO `excursion_inclusion` (`id`, `type`, `icon`, `position`, `created_at`, `updated_at`, `excursion_id`) VALUES
(5, 'included', '✓', 0, '2026-10-18 13:08:17', '2026-10-18 13:08:17', 12),
(6, 'included', '✓', 0, '2026-10-18 13:08:17', '2026-10-18 13:08:17', 12),
(7, 'included', '✓', 0, '2026-10-18 13:08:17', '2026-10-18 13:08:17', 12),
(8, 'excluded', '✚', 0, '2026-10-18 13:08:17', '2026-10-18 13:08:17', 12);

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
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `excursion_inclusion_translation`
--

INSERT INTO `excursion_inclusion_translation` (`id`, `locale`, `text`, `created_at`, `updated_at`, `excursion_inclusion_id`) VALUES
(9, 'fr', 'La prise en charge est offerte dans tous les hôtels autour de Tunis', '2026-10-18 13:08:17', '2026-10-18 13:08:17', 5),
(10, 'en', 'Pick-up is offered from all hotels in the Tunis area.', '2026-10-18 13:08:17', '2026-10-18 13:08:17', 5),
(11, 'it', 'La prise en charge est offerte dans tous les hôtels autour de Tunis', '2026-10-18 13:08:17', '2026-10-18 13:08:17', 5),
(12, 'fr', 'Transport privé', '2026-10-18 13:08:17', '2026-10-18 13:08:17', 6),
(13, 'en', 'Private transport', '2026-10-18 13:08:17', '2026-10-18 13:08:17', 6),
(14, 'it', 'Transport privé', '2026-10-18 13:08:17', '2026-10-18 13:08:17', 6),
(15, 'fr', 'Entrée – Ruines de Carthage', '2026-10-18 13:08:17', '2026-10-18 13:08:17', 7),
(16, 'en', 'Entrance – Ruins of Carthage', '2026-10-18 13:08:17', '2026-10-18 13:08:17', 7),
(17, 'it', 'Entrée – Ruines de Carthage', '2026-10-18 13:08:17', '2026-10-18 13:08:17', 7),
(18, 'fr', 'Pourboires', '2026-10-18 13:08:17', '2026-10-18 13:08:17', 8),
(19, 'en', 'Gratuities', '2026-10-18 13:08:17', '2026-10-18 13:08:17', 8),
(20, 'it', 'Pourboires', '2026-10-18 13:08:17', '2026-10-18 13:08:17', 8);

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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `excursion_itinerary`
--

INSERT INTO `excursion_itinerary` (`id`, `step_number`, `time`, `duration`, `position`, `created_at`, `updated_at`, `excursion_id`) VALUES
(5, 1, '09:00:00', 90, 1, '2026-10-18 13:08:17', '2026-10-18 13:08:17', 12);

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
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `excursion_itinerary_translation`
--

INSERT INTO `excursion_itinerary_translation` (`id`, `locale`, `title`, `description`, `created_at`, `updated_at`, `excursion_itinerary_id`) VALUES
(8, 'fr', 'Médina de Tunis', 'ce labyrinthe tentaculaire de rues et ruelles anciennes est l\'une des médinas médiévales les plus impressionnantes d\'Afrique du Nord et l\'un des grands trésors de la Tunisie. Il abrite de nombreux souks couverts vendant de tout, des chaussures aux chicha, ainsi que des cafés animés, des ruelles pleines d\'artisans au travail et des zones résidentielles rythmées par de grandes portes peintes de couleurs vives. Des palais historiques, des hammams, des mosquées et des madrassas (écoles pour l\'étude du Coran) sont dispersés partout, beaucoup sont richement décorés de carreaux, de stuc sculpté et de colonnes de marbre.', '2026-10-18 13:08:17', '2026-10-18 13:08:17', 5),
(9, 'en', 'Medina of Tunis', 'This sprawling labyrinth of ancient streets and alleyways is one of the most impressive medieval medinas in North Africa and one of Tunisia’s great treasures. It is home to numerous covered souks selling everything from shoes to hookahs, as well as lively cafés, narrow lanes filled with artisans at work, and residential areas punctuated by large, brightly painted doors. Historic palaces, hammams, mosques, and madrasas (schools for the study of the Quran) are scattered throughout—many of them richly decorated with tiles, carved stucco, and marble columns.', '2026-10-18 13:08:17', '2026-10-18 13:08:17', 5),
(10, 'it', 'Médina de Tunis', NULL, '2026-10-18 13:08:17', '2026-10-18 13:08:17', 5);

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
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `excursion_translation`
--

INSERT INTO `excursion_translation` (`id`, `locale`, `title`, `subtitle`, `description`, `location`, `meta_title`, `meta_description`, `created_at`, `updated_at`, `excursion_id`) VALUES
(16, 'fr', 'Excursions d\'une journée au départ de Tunisie', 'Tour privée: Médina,Carthage ,Sidi Bou Saïd, musée Bardo', 'Tunis est une ville atmosphérique mais son charme est difficile à mesurer lorsque vous êtes coincé dans le bus ou aux prises avec la circulation. Imprégnez-vous de ses sites touristiques, notamment la médina, la vibrante La Goulette et l\'ancienne Carthage, classée au patrimoine mondial de l\'UNESCO, lors de cette visite privée, qui s\'arrête également à Sidi Bou Said. Réservez une voiture et un chauffeur seulement, ou surclassez le forfait de votre choix avec déjeuner, Guide et frais d\'entrée inclus.', 'Tour privée: Médina,Carthage ,Sidi Bou Saïd, musée Bardo', 'Tour privée: Médina,Carthage ,Sidi Bou Saïd, musée Bardo', 'Tour privée: Médina,Carthage ,Sidi Bou Saïd, musée Bardo', '2026-10-18 13:08:17', '2026-10-18 13:08:17', 12),
(17, 'en', 'Day trips from Tunisia', 'Private tour: Medina, Carthage, Sidi Bou Said, Bardo Museum', 'Tunis is an atmospheric city, but its charm can be hard to appreciate when you’re stuck on a bus or caught in traffic. Soak up its key sights—including the medina, the vibrant district of La Goulette, and the ancient, UNESCO-listed site of Carthage—on this private tour, which also includes a stop in Sidi Bou Said. You can book just a car and driver, or upgrade your package to include lunch, a guide, and all entrance fees.', 'Day trips from Tunisia', 'Day trips from Tunisia', 'Day trips from Tunisia', '2026-10-18 13:08:17', '2026-10-18 13:08:17', 12);

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
) ENGINE=MyISAM AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `messenger_messages`
--

INSERT INTO `messenger_messages` (`id`, `body`, `headers`, `queue_name`, `created_at`, `available_at`, `delivered_at`) VALUES
(1, 'O:36:\\\"Symfony\\\\Component\\\\Messenger\\\\Envelope\\\":2:{s:44:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Envelope\\0stamps\\\";a:1:{s:46:\\\"Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\\";a:1:{i:0;O:46:\\\"Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\\":1:{s:55:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\0busName\\\";s:21:\\\"messenger.bus.default\\\";}}}s:45:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Envelope\\0message\\\";O:51:\\\"Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\\":2:{s:60:\\\"\\0Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\0message\\\";O:28:\\\"Symfony\\\\Component\\\\Mime\\\\Email\\\":6:{i:0;N;i:1;N;i:2;s:4993:\\\"<!DOCTYPE html>\n<html lang=\\\"fr\\\">\n<head>\n    <meta charset=\\\"UTF-8\\\">\n    <meta name=\\\"viewport\\\" content=\\\"width=device-width, initial-scale=1.0\\\">\n    <title>Nouveau message de contact</title>\n    <style>\n        body {\n            font-family: \\\'Helvetica Neue\\\', Arial, sans-serif;\n            line-height: 1.6;\n            color: #333333;\n            background-color: #f8fafc;\n            margin: 0;\n            padding: 0;\n        }\n        .container {\n            max-width: 600px;\n            margin: 40px auto;\n            background-color: #ffffff;\n            border-radius: 12px;\n            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);\n            overflow: hidden;\n        }\n        .header {\n            background-color: #063B73;\n            color: #ffffff;\n            padding: 30px 20px;\n            text-align: center;\n        }\n        .header h1 {\n            margin: 0;\n            font-size: 24px;\n            font-weight: 600;\n        }\n        .header p {\n            margin: 10px 0 0;\n            opacity: 0.9;\n        }\n        .content {\n            padding: 30px 40px;\n        }\n        .section-title {\n            color: #0B5ED7;\n            font-size: 18px;\n            font-weight: 600;\n            margin-bottom: 20px;\n            border-bottom: 2px solid #e2e8f0;\n            padding-bottom: 10px;\n        }\n        .info-table {\n            width: 100%;\n            border-collapse: collapse;\n            margin-bottom: 30px;\n        }\n        .info-table td {\n            padding: 12px 0;\n            border-bottom: 1px solid #f1f5f9;\n        }\n        .info-table td.label {\n            font-weight: 600;\n            color: #475569;\n            width: 35%;\n        }\n        .info-table td.value {\n            color: #1e293b;\n        }\n        .message-box {\n            background-color: #f8fafc;\n            border: 1px solid #e2e8f0;\n            border-radius: 8px;\n            padding: 20px;\n            margin-top: 10px;\n            white-space: pre-wrap;\n            color: #334155;\n        }\n        .footer {\n            background-color: #f1f5f9;\n            padding: 20px;\n            text-align: center;\n            font-size: 12px;\n            color: #64748b;\n            border-top: 1px solid #e2e8f0;\n        }\n        @media only screen and (max-width: 600px) {\n            .container {\n                margin: 20px 10px;\n            }\n            .content {\n                padding: 20px;\n            }\n            .info-table td {\n                display: block;\n                width: 100%;\n            }\n            .info-table td.label {\n                padding-bottom: 4px;\n                border-bottom: none;\n            }\n            .info-table td.value {\n                padding-top: 0;\n                padding-bottom: 16px;\n            }\n        }\n    </style>\n</head>\n<body>\n    <div class=\\\"container\\\">\n        <div class=\\\"header\\\">\n            <h1>Nouveau message de contact</h1>\n            <p>LocationMahmoud</p>\n        </div>\n        \n        <div class=\\\"content\\\">\n            <h2 class=\\\"section-title\\\">Informations du visiteur</h2>\n            <table class=\\\"info-table\\\">\n                <tr>\n                    <td class=\\\"label\\\">Prénom :</td>\n                    <td class=\\\"value\\\">ksontini</td>\n                </tr>\n                <tr>\n                    <td class=\\\"label\\\">Nom :</td>\n                    <td class=\\\"value\\\">ahmed</td>\n                </tr>\n                <tr>\n                    <td class=\\\"label\\\">Email :</td>\n                    <td class=\\\"value\\\"><a href=\\\"mailto:med@gmail.com\\\" style=\\\"color: #0B5ED7; text-decoration: none;\\\">med@gmail.com</a></td>\n                </tr>\n                                <tr>\n                    <td class=\\\"label\\\">Téléphone :</td>\n                    <td class=\\\"value\\\"><a href=\\\"tel:93313278\\\" style=\\\"color: #0B5ED7; text-decoration: none;\\\">93313278</a></td>\n                </tr>\n                                <tr>\n                    <td class=\\\"label\\\">Service concerné :</td>\n                    <td class=\\\"value\\\">contact.form.service_groupe</td>\n                </tr>\n                                <tr>\n                    <td class=\\\"label\\\">Date d\\\'envoi :</td>\n                    <td class=\\\"value\\\">22/09/2026 20:15</td>\n                </tr>\n            </table>\n\n            <h2 class=\\\"section-title\\\">Détails du message</h2>\n            <div style=\\\"margin-bottom: 10px;\\\">\n                <span style=\\\"font-weight: 600; color: #475569;\\\">Sujet :</span>\n                <span style=\\\"color: #1e293b; margin-left: 10px;\\\">testzad fazd</span>\n            </div>\n            \n            <div class=\\\"message-box\\\">teststsdazdazd zd azd faerfa</div>\n        </div>\n        \n        <div class=\\\"footer\\\">\n            <p>Ce message a été envoyé automatiquement depuis le formulaire de contact du site LocationMahmoud.</p>\n            <p>&copy; 2026 LocationMahmoud. Tous droits réservés.</p>\n        </div>\n    </div>\n</body>\n</html>\n\\\";i:3;s:5:\\\"utf-8\\\";i:4;a:0:{}i:5;a:2:{i:0;O:37:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\\":2:{s:46:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\0headers\\\";a:4:{s:4:\\\"from\\\";a:1:{i:0;O:47:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:4:\\\"From\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:58:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\0addresses\\\";a:1:{i:0;O:30:\\\"Symfony\\\\Component\\\\Mime\\\\Address\\\":2:{s:39:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0address\\\";s:25:\\\"contact@tunisiatravel.com\\\";s:36:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0name\\\";s:0:\\\"\\\";}}}}s:2:\\\"to\\\";a:1:{i:0;O:47:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:2:\\\"To\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:58:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\0addresses\\\";a:1:{i:0;O:30:\\\"Symfony\\\\Component\\\\Mime\\\\Address\\\":2:{s:39:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0address\\\";s:26:\\\"ahmedksontini122@gmail.com\\\";s:36:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0name\\\";s:0:\\\"\\\";}}}}s:8:\\\"reply-to\\\";a:1:{i:0;O:47:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:8:\\\"Reply-To\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:58:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\0addresses\\\";a:1:{i:0;O:30:\\\"Symfony\\\\Component\\\\Mime\\\\Address\\\":2:{s:39:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0address\\\";s:13:\\\"med@gmail.com\\\";s:36:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0name\\\";s:0:\\\"\\\";}}}}s:7:\\\"subject\\\";a:1:{i:0;O:48:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\UnstructuredHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:7:\\\"Subject\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:55:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\UnstructuredHeader\\0value\\\";s:46:\\\"Nouveau message de contact — LocationMahmoud\\\";}}}s:49:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\0lineLength\\\";i:76;}i:1;N;}}s:61:\\\"\\0Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\0envelope\\\";N;}}', '[]', 'default', '2026-09-22 20:15:03', '2026-09-22 20:15:03', NULL),
(2, 'O:36:\\\"Symfony\\\\Component\\\\Messenger\\\\Envelope\\\":2:{s:44:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Envelope\\0stamps\\\";a:1:{s:46:\\\"Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\\";a:1:{i:0;O:46:\\\"Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\\":1:{s:55:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\0busName\\\";s:21:\\\"messenger.bus.default\\\";}}}s:45:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Envelope\\0message\\\";O:51:\\\"Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\\":2:{s:60:\\\"\\0Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\0message\\\";O:39:\\\"Symfony\\\\Bridge\\\\Twig\\\\Mime\\\\TemplatedEmail\\\":5:{i:0;s:38:\\\"emails/reservation_confirmed.html.twig\\\";i:1;N;i:2;a:2:{s:11:\\\"reservation\\\";O:31:\\\"App\\\\Entity\\\\ReservationTransfere\\\":24:{s:35:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0id\\\";i:5;s:42:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0transfere\\\";O:35:\\\"Proxies\\\\__CG__\\\\App\\\\Entity\\\\Transfere\\\":1:{s:24:\\\"\\0App\\\\Entity\\\\Transfere\\0id\\\";i:1;}s:36:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0nom\\\";s:13:\\\"Ahmedksontini\\\";s:39:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0prenom\\\";s:13:\\\"Ahmedksontini\\\";s:38:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0email\\\";s:26:\\\"ahmedksontini560@gmail.com\\\";s:42:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0telephone\\\";s:8:\\\"93313278\\\";s:46:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0transfereType\\\";E:41:\\\"App\\\\Enum\\\\ReservationTransfereType:ONE_WAY\\\";s:47:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0pickupLocation\\\";s:23:\\\"aireport Tunis Carthage\\\";s:48:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0dropoffLocation\\\";s:8:\\\"Hammamet\\\";s:43:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0pickupDate\\\";O:17:\\\"DateTimeImmutable\\\":3:{s:4:\\\"date\\\";s:26:\\\"2026-10-19 00:00:00.000000\\\";s:13:\\\"timezone_type\\\";i:3;s:8:\\\"timezone\\\";s:3:\\\"UTC\\\";}s:43:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0pickupTime\\\";O:17:\\\"DateTimeImmutable\\\":3:{s:4:\\\"date\\\";s:26:\\\"1970-01-01 22:03:00.000000\\\";s:13:\\\"timezone_type\\\";i:3;s:8:\\\"timezone\\\";s:3:\\\"UTC\\\";}s:53:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0returnPickupLocation\\\";N;s:54:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0returnDropoffLocation\\\";N;s:49:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0returnPickupDate\\\";N;s:49:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0returnPickupTime\\\";N;s:48:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0nombrePassagers\\\";i:1;s:46:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0nombreBagages\\\";i:2;s:40:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0message\\\";s:15:\\\"message de test\\\";s:42:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0numeroVol\\\";s:5:\\\"TU123\\\";s:45:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0compagnieVol\\\";s:9:\\\"Tunisaire\\\";s:42:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0prixTotal\\\";s:5:\\\"50.00\\\";s:39:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0devise\\\";s:3:\\\"TND\\\";s:39:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0status\\\";E:36:\\\"App\\\\Enum\\\\ReservationStatus:CONFIRMED\\\";s:42:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0createdAt\\\";O:17:\\\"DateTimeImmutable\\\":3:{s:4:\\\"date\\\";s:26:\\\"2026-10-11 20:00:37.000000\\\";s:13:\\\"timezone_type\\\";i:3;s:8:\\\"timezone\\\";s:3:\\\"UTC\\\";}}s:10:\\\"clientName\\\";s:27:\\\"Ahmedksontini Ahmedksontini\\\";}i:3;a:6:{i:0;N;i:1;N;i:2;N;i:3;N;i:4;a:0:{}i:5;a:2:{i:0;O:37:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\\":2:{s:46:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\0headers\\\";a:3:{s:4:\\\"from\\\";a:1:{i:0;O:47:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:4:\\\"From\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:58:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\0addresses\\\";a:1:{i:0;O:30:\\\"Symfony\\\\Component\\\\Mime\\\\Address\\\":2:{s:39:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0address\\\";s:26:\\\"ahmedksontini122@gmail.com\\\";s:36:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0name\\\";s:16:\\\"Location Mahmoud\\\";}}}}s:2:\\\"to\\\";a:1:{i:0;O:47:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:2:\\\"To\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:58:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\0addresses\\\";a:1:{i:0;O:30:\\\"Symfony\\\\Component\\\\Mime\\\\Address\\\":2:{s:39:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0address\\\";s:26:\\\"ahmedksontini560@gmail.com\\\";s:36:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0name\\\";s:27:\\\"Ahmedksontini Ahmedksontini\\\";}}}}s:7:\\\"subject\\\";a:1:{i:0;O:48:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\UnstructuredHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:7:\\\"Subject\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:55:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\UnstructuredHeader\\0value\\\";s:58:\\\"Confirmation de votre réservation #5 — Location Mahmoud\\\";}}}s:49:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\0lineLength\\\";i:76;}i:1;N;}}i:4;N;}s:61:\\\"\\0Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\0envelope\\\";N;}}', '[]', 'default', '2026-10-11 20:32:00', '2026-10-11 20:32:00', NULL),
(3, 'O:36:\\\"Symfony\\\\Component\\\\Messenger\\\\Envelope\\\":2:{s:44:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Envelope\\0stamps\\\";a:1:{s:46:\\\"Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\\";a:1:{i:0;O:46:\\\"Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\\":1:{s:55:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\0busName\\\";s:21:\\\"messenger.bus.default\\\";}}}s:45:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Envelope\\0message\\\";O:51:\\\"Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\\":2:{s:60:\\\"\\0Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\0message\\\";O:39:\\\"Symfony\\\\Bridge\\\\Twig\\\\Mime\\\\TemplatedEmail\\\":5:{i:0;s:38:\\\"emails/reservation_confirmed.html.twig\\\";i:1;N;i:2;a:3:{s:11:\\\"reservation\\\";O:31:\\\"App\\\\Entity\\\\ReservationTransfere\\\":24:{s:35:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0id\\\";i:5;s:42:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0transfere\\\";O:35:\\\"Proxies\\\\__CG__\\\\App\\\\Entity\\\\Transfere\\\":11:{s:24:\\\"\\0App\\\\Entity\\\\Transfere\\0id\\\";i:1;s:32:\\\"\\0App\\\\Entity\\\\Transfere\\0lieuDepart\\\";s:23:\\\"aireport Tunis Carthage\\\";s:33:\\\"\\0App\\\\Entity\\\\Transfere\\0lieuArrivee\\\";s:8:\\\"Hammamet\\\";s:33:\\\"\\0App\\\\Entity\\\\Transfere\\0description\\\";N;s:32:\\\"\\0App\\\\Entity\\\\Transfere\\0prixNormal\\\";s:5:\\\"50.00\\\";s:37:\\\"\\0App\\\\Entity\\\\Transfere\\0prixSaisonForte\\\";s:6:\\\"100.00\\\";s:27:\\\"\\0App\\\\Entity\\\\Transfere\\0actif\\\";b:1;s:26:\\\"\\0App\\\\Entity\\\\Transfere\\0slug\\\";s:17:\\\"aireport-aireport\\\";s:27:\\\"\\0App\\\\Entity\\\\Transfere\\0image\\\";s:44:\\\"/uploads/transferts/images-6ac94de7122fa.jpg\\\";s:34:\\\"\\0App\\\\Entity\\\\Transfere\\0translations\\\";O:33:\\\"Doctrine\\\\ORM\\\\PersistentCollection\\\":2:{s:13:\\\"\\0*\\0collection\\\";O:43:\\\"Doctrine\\\\Common\\\\Collections\\\\ArrayCollection\\\":1:{s:53:\\\"\\0Doctrine\\\\Common\\\\Collections\\\\ArrayCollection\\0elements\\\";a:5:{i:0;O:31:\\\"App\\\\Entity\\\\TransfereTranslation\\\":6:{s:35:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0id\\\";i:4;s:42:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0transfere\\\";r:13;s:39:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0locale\\\";s:2:\\\"de\\\";s:43:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0lieuDepart\\\";N;s:44:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0lieuArrivee\\\";N;s:38:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0duree\\\";N;}i:1;O:31:\\\"App\\\\Entity\\\\TransfereTranslation\\\":6:{s:35:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0id\\\";i:2;s:42:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0transfere\\\";r:13;s:39:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0locale\\\";s:2:\\\"en\\\";s:43:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0lieuDepart\\\";s:22:\\\"Tunis Carthage Airport\\\";s:44:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0lieuArrivee\\\";s:8:\\\"Hammamet\\\";s:38:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0duree\\\";s:6:\\\"60 min\\\";}i:2;O:31:\\\"App\\\\Entity\\\\TransfereTranslation\\\":6:{s:35:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0id\\\";i:3;s:42:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0transfere\\\";r:13;s:39:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0locale\\\";s:2:\\\"es\\\";s:43:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0lieuDepart\\\";N;s:44:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0lieuArrivee\\\";N;s:38:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0duree\\\";N;}i:3;O:31:\\\"App\\\\Entity\\\\TransfereTranslation\\\":6:{s:35:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0id\\\";i:1;s:42:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0transfere\\\";r:13;s:39:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0locale\\\";s:2:\\\"fr\\\";s:43:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0lieuDepart\\\";s:23:\\\"aireport Tunis Carthage\\\";s:44:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0lieuArrivee\\\";s:8:\\\"Hammamet\\\";s:38:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0duree\\\";s:6:\\\"60 min\\\";}i:4;O:31:\\\"App\\\\Entity\\\\TransfereTranslation\\\":6:{s:35:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0id\\\";i:5;s:42:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0transfere\\\";r:13;s:39:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0locale\\\";s:2:\\\"it\\\";s:43:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0lieuDepart\\\";N;s:44:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0lieuArrivee\\\";N;s:38:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0duree\\\";N;}}}s:14:\\\"\\0*\\0initialized\\\";b:1;}s:34:\\\"\\0App\\\\Entity\\\\Transfere\\0reservations\\\";O:33:\\\"Doctrine\\\\ORM\\\\PersistentCollection\\\":2:{s:13:\\\"\\0*\\0collection\\\";O:43:\\\"Doctrine\\\\Common\\\\Collections\\\\ArrayCollection\\\":1:{s:53:\\\"\\0Doctrine\\\\Common\\\\Collections\\\\ArrayCollection\\0elements\\\";a:0:{}}s:14:\\\"\\0*\\0initialized\\\";b:0;}}s:36:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0nom\\\";s:13:\\\"Ahmedksontini\\\";s:39:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0prenom\\\";s:13:\\\"Ahmedksontini\\\";s:38:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0email\\\";s:26:\\\"ahmedksontini560@gmail.com\\\";s:42:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0telephone\\\";s:8:\\\"93313278\\\";s:46:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0transfereType\\\";E:41:\\\"App\\\\Enum\\\\ReservationTransfereType:ONE_WAY\\\";s:47:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0pickupLocation\\\";s:23:\\\"aireport Tunis Carthage\\\";s:48:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0dropoffLocation\\\";s:8:\\\"Hammamet\\\";s:43:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0pickupDate\\\";O:17:\\\"DateTimeImmutable\\\":3:{s:4:\\\"date\\\";s:26:\\\"2026-10-19 00:00:00.000000\\\";s:13:\\\"timezone_type\\\";i:3;s:8:\\\"timezone\\\";s:3:\\\"UTC\\\";}s:43:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0pickupTime\\\";O:17:\\\"DateTimeImmutable\\\":3:{s:4:\\\"date\\\";s:26:\\\"1970-01-01 22:03:00.000000\\\";s:13:\\\"timezone_type\\\";i:3;s:8:\\\"timezone\\\";s:3:\\\"UTC\\\";}s:53:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0returnPickupLocation\\\";N;s:54:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0returnDropoffLocation\\\";N;s:49:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0returnPickupDate\\\";N;s:49:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0returnPickupTime\\\";N;s:48:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0nombrePassagers\\\";i:1;s:46:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0nombreBagages\\\";i:2;s:40:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0message\\\";s:15:\\\"message de test\\\";s:42:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0numeroVol\\\";s:5:\\\"TU123\\\";s:45:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0compagnieVol\\\";s:9:\\\"Tunisaire\\\";s:42:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0prixTotal\\\";s:5:\\\"50.00\\\";s:39:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0devise\\\";s:3:\\\"TND\\\";s:39:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0status\\\";E:36:\\\"App\\\\Enum\\\\ReservationStatus:CONFIRMED\\\";s:42:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0createdAt\\\";O:17:\\\"DateTimeImmutable\\\":3:{s:4:\\\"date\\\";s:26:\\\"2026-10-11 20:00:37.000000\\\";s:13:\\\"timezone_type\\\";i:3;s:8:\\\"timezone\\\";s:3:\\\"UTC\\\";}}s:10:\\\"clientName\\\";s:27:\\\"Ahmedksontini Ahmedksontini\\\";s:8:\\\"siteName\\\";s:16:\\\"Location Mahmoud\\\";}i:3;a:6:{i:0;N;i:1;N;i:2;N;i:3;N;i:4;a:0:{}i:5;a:2:{i:0;O:37:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\\":2:{s:46:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\0headers\\\";a:3:{s:4:\\\"from\\\";a:1:{i:0;O:47:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:4:\\\"From\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:58:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\0addresses\\\";a:1:{i:0;O:30:\\\"Symfony\\\\Component\\\\Mime\\\\Address\\\":2:{s:39:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0address\\\";s:26:\\\"ahmedksontini122@gmail.com\\\";s:36:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0name\\\";s:16:\\\"Location Mahmoud\\\";}}}}s:2:\\\"to\\\";a:1:{i:0;O:47:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:2:\\\"To\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:58:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\0addresses\\\";a:1:{i:0;O:30:\\\"Symfony\\\\Component\\\\Mime\\\\Address\\\":2:{s:39:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0address\\\";s:26:\\\"ahmedksontini560@gmail.com\\\";s:36:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0name\\\";s:27:\\\"Ahmedksontini Ahmedksontini\\\";}}}}s:7:\\\"subject\\\";a:1:{i:0;O:48:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\UnstructuredHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:7:\\\"Subject\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:55:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\UnstructuredHeader\\0value\\\";s:37:\\\"Confirmation de votre réservation #5\\\";}}}s:49:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\0lineLength\\\";i:76;}i:1;N;}}i:4;N;}s:61:\\\"\\0Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\0envelope\\\";N;}}', '[]', 'default', '2026-10-14 16:57:08', '2026-10-14 16:57:08', NULL),
(4, 'O:36:\\\"Symfony\\\\Component\\\\Messenger\\\\Envelope\\\":2:{s:44:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Envelope\\0stamps\\\";a:1:{s:46:\\\"Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\\";a:1:{i:0;O:46:\\\"Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\\":1:{s:55:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\0busName\\\";s:21:\\\"messenger.bus.default\\\";}}}s:45:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Envelope\\0message\\\";O:51:\\\"Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\\":2:{s:60:\\\"\\0Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\0message\\\";O:39:\\\"Symfony\\\\Bridge\\\\Twig\\\\Mime\\\\TemplatedEmail\\\":5:{i:0;s:38:\\\"emails/reservation_confirmed.html.twig\\\";i:1;N;i:2;a:3:{s:11:\\\"reservation\\\";O:31:\\\"App\\\\Entity\\\\ReservationTransfere\\\":24:{s:35:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0id\\\";i:6;s:42:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0transfere\\\";O:35:\\\"Proxies\\\\__CG__\\\\App\\\\Entity\\\\Transfere\\\":11:{s:24:\\\"\\0App\\\\Entity\\\\Transfere\\0id\\\";i:1;s:32:\\\"\\0App\\\\Entity\\\\Transfere\\0lieuDepart\\\";s:23:\\\"aireport Tunis Carthage\\\";s:33:\\\"\\0App\\\\Entity\\\\Transfere\\0lieuArrivee\\\";s:8:\\\"Hammamet\\\";s:33:\\\"\\0App\\\\Entity\\\\Transfere\\0description\\\";N;s:32:\\\"\\0App\\\\Entity\\\\Transfere\\0prixNormal\\\";s:5:\\\"50.00\\\";s:37:\\\"\\0App\\\\Entity\\\\Transfere\\0prixSaisonForte\\\";s:6:\\\"100.00\\\";s:27:\\\"\\0App\\\\Entity\\\\Transfere\\0actif\\\";b:1;s:26:\\\"\\0App\\\\Entity\\\\Transfere\\0slug\\\";s:17:\\\"aireport-aireport\\\";s:27:\\\"\\0App\\\\Entity\\\\Transfere\\0image\\\";s:44:\\\"/uploads/transferts/images-6ac94de7122fa.jpg\\\";s:34:\\\"\\0App\\\\Entity\\\\Transfere\\0translations\\\";O:33:\\\"Doctrine\\\\ORM\\\\PersistentCollection\\\":2:{s:13:\\\"\\0*\\0collection\\\";O:43:\\\"Doctrine\\\\Common\\\\Collections\\\\ArrayCollection\\\":1:{s:53:\\\"\\0Doctrine\\\\Common\\\\Collections\\\\ArrayCollection\\0elements\\\";a:5:{i:0;O:31:\\\"App\\\\Entity\\\\TransfereTranslation\\\":6:{s:35:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0id\\\";i:4;s:42:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0transfere\\\";r:13;s:39:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0locale\\\";s:2:\\\"de\\\";s:43:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0lieuDepart\\\";N;s:44:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0lieuArrivee\\\";N;s:38:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0duree\\\";N;}i:1;O:31:\\\"App\\\\Entity\\\\TransfereTranslation\\\":6:{s:35:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0id\\\";i:2;s:42:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0transfere\\\";r:13;s:39:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0locale\\\";s:2:\\\"en\\\";s:43:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0lieuDepart\\\";s:22:\\\"Tunis Carthage Airport\\\";s:44:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0lieuArrivee\\\";s:8:\\\"Hammamet\\\";s:38:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0duree\\\";s:6:\\\"60 min\\\";}i:2;O:31:\\\"App\\\\Entity\\\\TransfereTranslation\\\":6:{s:35:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0id\\\";i:3;s:42:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0transfere\\\";r:13;s:39:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0locale\\\";s:2:\\\"es\\\";s:43:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0lieuDepart\\\";N;s:44:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0lieuArrivee\\\";N;s:38:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0duree\\\";N;}i:3;O:31:\\\"App\\\\Entity\\\\TransfereTranslation\\\":6:{s:35:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0id\\\";i:1;s:42:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0transfere\\\";r:13;s:39:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0locale\\\";s:2:\\\"fr\\\";s:43:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0lieuDepart\\\";s:23:\\\"aireport Tunis Carthage\\\";s:44:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0lieuArrivee\\\";s:8:\\\"Hammamet\\\";s:38:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0duree\\\";s:6:\\\"60 min\\\";}i:4;O:31:\\\"App\\\\Entity\\\\TransfereTranslation\\\":6:{s:35:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0id\\\";i:5;s:42:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0transfere\\\";r:13;s:39:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0locale\\\";s:2:\\\"it\\\";s:43:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0lieuDepart\\\";N;s:44:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0lieuArrivee\\\";N;s:38:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0duree\\\";N;}}}s:14:\\\"\\0*\\0initialized\\\";b:1;}s:34:\\\"\\0App\\\\Entity\\\\Transfere\\0reservations\\\";O:33:\\\"Doctrine\\\\ORM\\\\PersistentCollection\\\":2:{s:13:\\\"\\0*\\0collection\\\";O:43:\\\"Doctrine\\\\Common\\\\Collections\\\\ArrayCollection\\\":1:{s:53:\\\"\\0Doctrine\\\\Common\\\\Collections\\\\ArrayCollection\\0elements\\\";a:0:{}}s:14:\\\"\\0*\\0initialized\\\";b:0;}}s:36:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0nom\\\";s:8:\\\"testatds\\\";s:39:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0prenom\\\";s:8:\\\"testatds\\\";s:38:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0email\\\";s:26:\\\"ahmedksontini457@gmail.com\\\";s:42:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0telephone\\\";s:7:\\\"9313278\\\";s:46:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0transfereType\\\";E:41:\\\"App\\\\Enum\\\\ReservationTransfereType:ONE_WAY\\\";s:47:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0pickupLocation\\\";s:22:\\\"Tunis Carthage Airport\\\";s:48:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0dropoffLocation\\\";s:8:\\\"Hammamet\\\";s:43:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0pickupDate\\\";O:17:\\\"DateTimeImmutable\\\":3:{s:4:\\\"date\\\";s:26:\\\"2026-10-22 00:00:00.000000\\\";s:13:\\\"timezone_type\\\";i:3;s:8:\\\"timezone\\\";s:3:\\\"UTC\\\";}s:43:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0pickupTime\\\";O:17:\\\"DateTimeImmutable\\\":3:{s:4:\\\"date\\\";s:26:\\\"1970-01-01 22:02:00.000000\\\";s:13:\\\"timezone_type\\\";i:3;s:8:\\\"timezone\\\";s:3:\\\"UTC\\\";}s:53:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0returnPickupLocation\\\";N;s:54:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0returnDropoffLocation\\\";N;s:49:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0returnPickupDate\\\";N;s:49:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0returnPickupTime\\\";N;s:48:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0nombrePassagers\\\";i:1;s:46:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0nombreBagages\\\";i:0;s:40:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0message\\\";N;s:42:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0numeroVol\\\";N;s:45:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0compagnieVol\\\";N;s:42:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0prixTotal\\\";s:5:\\\"50.00\\\";s:39:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0devise\\\";s:3:\\\"TND\\\";s:39:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0status\\\";E:36:\\\"App\\\\Enum\\\\ReservationStatus:CONFIRMED\\\";s:42:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0createdAt\\\";O:17:\\\"DateTimeImmutable\\\":3:{s:4:\\\"date\\\";s:26:\\\"2026-10-14 16:59:41.000000\\\";s:13:\\\"timezone_type\\\";i:3;s:8:\\\"timezone\\\";s:3:\\\"UTC\\\";}}s:10:\\\"clientName\\\";s:17:\\\"testatds testatds\\\";s:8:\\\"siteName\\\";s:16:\\\"Location Mahmoud\\\";}i:3;a:6:{i:0;N;i:1;N;i:2;N;i:3;N;i:4;a:0:{}i:5;a:2:{i:0;O:37:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\\":2:{s:46:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\0headers\\\";a:3:{s:4:\\\"from\\\";a:1:{i:0;O:47:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:4:\\\"From\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:58:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\0addresses\\\";a:1:{i:0;O:30:\\\"Symfony\\\\Component\\\\Mime\\\\Address\\\":2:{s:39:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0address\\\";s:26:\\\"ahmedksontini122@gmail.com\\\";s:36:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0name\\\";s:16:\\\"Location Mahmoud\\\";}}}}s:2:\\\"to\\\";a:1:{i:0;O:47:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:2:\\\"To\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:58:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\0addresses\\\";a:1:{i:0;O:30:\\\"Symfony\\\\Component\\\\Mime\\\\Address\\\":2:{s:39:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0address\\\";s:26:\\\"ahmedksontini457@gmail.com\\\";s:36:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0name\\\";s:17:\\\"testatds testatds\\\";}}}}s:7:\\\"subject\\\";a:1:{i:0;O:48:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\UnstructuredHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:7:\\\"Subject\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:55:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\UnstructuredHeader\\0value\\\";s:37:\\\"Confirmation de votre réservation #6\\\";}}}s:49:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\0lineLength\\\";i:76;}i:1;N;}}i:4;N;}s:61:\\\"\\0Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\0envelope\\\";N;}}', '[]', 'default', '2026-10-14 17:00:48', '2026-10-14 17:00:48', NULL),
(5, 'O:36:\\\"Symfony\\\\Component\\\\Messenger\\\\Envelope\\\":2:{s:44:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Envelope\\0stamps\\\";a:1:{s:46:\\\"Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\\";a:1:{i:0;O:46:\\\"Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\\":1:{s:55:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\0busName\\\";s:21:\\\"messenger.bus.default\\\";}}}s:45:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Envelope\\0message\\\";O:51:\\\"Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\\":2:{s:60:\\\"\\0Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\0message\\\";O:39:\\\"Symfony\\\\Bridge\\\\Twig\\\\Mime\\\\TemplatedEmail\\\":5:{i:0;s:38:\\\"emails/reservation_confirmed.html.twig\\\";i:1;N;i:2;a:3:{s:11:\\\"reservation\\\";O:31:\\\"App\\\\Entity\\\\ReservationTransfere\\\":24:{s:35:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0id\\\";i:7;s:42:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0transfere\\\";O:35:\\\"Proxies\\\\__CG__\\\\App\\\\Entity\\\\Transfere\\\":11:{s:24:\\\"\\0App\\\\Entity\\\\Transfere\\0id\\\";i:1;s:32:\\\"\\0App\\\\Entity\\\\Transfere\\0lieuDepart\\\";s:23:\\\"aireport Tunis Carthage\\\";s:33:\\\"\\0App\\\\Entity\\\\Transfere\\0lieuArrivee\\\";s:8:\\\"Hammamet\\\";s:33:\\\"\\0App\\\\Entity\\\\Transfere\\0description\\\";N;s:32:\\\"\\0App\\\\Entity\\\\Transfere\\0prixNormal\\\";s:5:\\\"50.00\\\";s:37:\\\"\\0App\\\\Entity\\\\Transfere\\0prixSaisonForte\\\";s:6:\\\"100.00\\\";s:27:\\\"\\0App\\\\Entity\\\\Transfere\\0actif\\\";b:1;s:26:\\\"\\0App\\\\Entity\\\\Transfere\\0slug\\\";s:17:\\\"aireport-aireport\\\";s:27:\\\"\\0App\\\\Entity\\\\Transfere\\0image\\\";s:44:\\\"/uploads/transferts/images-6ac94de7122fa.jpg\\\";s:34:\\\"\\0App\\\\Entity\\\\Transfere\\0translations\\\";O:33:\\\"Doctrine\\\\ORM\\\\PersistentCollection\\\":2:{s:13:\\\"\\0*\\0collection\\\";O:43:\\\"Doctrine\\\\Common\\\\Collections\\\\ArrayCollection\\\":1:{s:53:\\\"\\0Doctrine\\\\Common\\\\Collections\\\\ArrayCollection\\0elements\\\";a:5:{i:0;O:31:\\\"App\\\\Entity\\\\TransfereTranslation\\\":6:{s:35:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0id\\\";i:4;s:42:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0transfere\\\";r:13;s:39:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0locale\\\";s:2:\\\"de\\\";s:43:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0lieuDepart\\\";N;s:44:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0lieuArrivee\\\";N;s:38:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0duree\\\";N;}i:1;O:31:\\\"App\\\\Entity\\\\TransfereTranslation\\\":6:{s:35:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0id\\\";i:2;s:42:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0transfere\\\";r:13;s:39:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0locale\\\";s:2:\\\"en\\\";s:43:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0lieuDepart\\\";s:22:\\\"Tunis Carthage Airport\\\";s:44:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0lieuArrivee\\\";s:8:\\\"Hammamet\\\";s:38:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0duree\\\";s:6:\\\"60 min\\\";}i:2;O:31:\\\"App\\\\Entity\\\\TransfereTranslation\\\":6:{s:35:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0id\\\";i:3;s:42:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0transfere\\\";r:13;s:39:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0locale\\\";s:2:\\\"es\\\";s:43:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0lieuDepart\\\";N;s:44:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0lieuArrivee\\\";N;s:38:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0duree\\\";N;}i:3;O:31:\\\"App\\\\Entity\\\\TransfereTranslation\\\":6:{s:35:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0id\\\";i:1;s:42:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0transfere\\\";r:13;s:39:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0locale\\\";s:2:\\\"fr\\\";s:43:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0lieuDepart\\\";s:23:\\\"aireport Tunis Carthage\\\";s:44:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0lieuArrivee\\\";s:8:\\\"Hammamet\\\";s:38:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0duree\\\";s:6:\\\"60 min\\\";}i:4;O:31:\\\"App\\\\Entity\\\\TransfereTranslation\\\":6:{s:35:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0id\\\";i:5;s:42:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0transfere\\\";r:13;s:39:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0locale\\\";s:2:\\\"it\\\";s:43:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0lieuDepart\\\";N;s:44:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0lieuArrivee\\\";N;s:38:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0duree\\\";N;}}}s:14:\\\"\\0*\\0initialized\\\";b:1;}s:34:\\\"\\0App\\\\Entity\\\\Transfere\\0reservations\\\";O:33:\\\"Doctrine\\\\ORM\\\\PersistentCollection\\\":2:{s:13:\\\"\\0*\\0collection\\\";O:43:\\\"Doctrine\\\\Common\\\\Collections\\\\ArrayCollection\\\":1:{s:53:\\\"\\0Doctrine\\\\Common\\\\Collections\\\\ArrayCollection\\0elements\\\";a:0:{}}s:14:\\\"\\0*\\0initialized\\\";b:0;}}s:36:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0nom\\\";s:8:\\\"tettette\\\";s:39:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0prenom\\\";s:8:\\\"tettette\\\";s:38:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0email\\\";s:26:\\\"ahmedksontini122@gmail.com\\\";s:42:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0telephone\\\";s:8:\\\"93313278\\\";s:46:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0transfereType\\\";E:41:\\\"App\\\\Enum\\\\ReservationTransfereType:ONE_WAY\\\";s:47:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0pickupLocation\\\";s:23:\\\"aireport Tunis Carthage\\\";s:48:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0dropoffLocation\\\";s:8:\\\"Hammamet\\\";s:43:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0pickupDate\\\";O:17:\\\"DateTimeImmutable\\\":3:{s:4:\\\"date\\\";s:26:\\\"2026-10-29 00:00:00.000000\\\";s:13:\\\"timezone_type\\\";i:3;s:8:\\\"timezone\\\";s:3:\\\"UTC\\\";}s:43:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0pickupTime\\\";O:17:\\\"DateTimeImmutable\\\":3:{s:4:\\\"date\\\";s:26:\\\"1970-01-01 22:08:00.000000\\\";s:13:\\\"timezone_type\\\";i:3;s:8:\\\"timezone\\\";s:3:\\\"UTC\\\";}s:53:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0returnPickupLocation\\\";N;s:54:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0returnDropoffLocation\\\";N;s:49:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0returnPickupDate\\\";N;s:49:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0returnPickupTime\\\";N;s:48:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0nombrePassagers\\\";i:1;s:46:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0nombreBagages\\\";i:0;s:40:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0message\\\";N;s:42:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0numeroVol\\\";N;s:45:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0compagnieVol\\\";N;s:42:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0prixTotal\\\";s:5:\\\"50.00\\\";s:39:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0devise\\\";s:3:\\\"TND\\\";s:39:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0status\\\";E:36:\\\"App\\\\Enum\\\\ReservationStatus:CONFIRMED\\\";s:42:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0createdAt\\\";O:17:\\\"DateTimeImmutable\\\":3:{s:4:\\\"date\\\";s:26:\\\"2026-10-14 17:05:08.000000\\\";s:13:\\\"timezone_type\\\";i:3;s:8:\\\"timezone\\\";s:3:\\\"UTC\\\";}}s:10:\\\"clientName\\\";s:17:\\\"tettette tettette\\\";s:8:\\\"siteName\\\";s:16:\\\"Location Mahmoud\\\";}i:3;a:6:{i:0;N;i:1;N;i:2;N;i:3;N;i:4;a:0:{}i:5;a:2:{i:0;O:37:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\\":2:{s:46:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\0headers\\\";a:3:{s:4:\\\"from\\\";a:1:{i:0;O:47:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:4:\\\"From\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:58:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\0addresses\\\";a:1:{i:0;O:30:\\\"Symfony\\\\Component\\\\Mime\\\\Address\\\":2:{s:39:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0address\\\";s:26:\\\"ahmedksontini122@gmail.com\\\";s:36:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0name\\\";s:16:\\\"Location Mahmoud\\\";}}}}s:2:\\\"to\\\";a:1:{i:0;O:47:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:2:\\\"To\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:58:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\0addresses\\\";a:1:{i:0;O:30:\\\"Symfony\\\\Component\\\\Mime\\\\Address\\\":2:{s:39:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0address\\\";s:26:\\\"ahmedksontini122@gmail.com\\\";s:36:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0name\\\";s:17:\\\"tettette tettette\\\";}}}}s:7:\\\"subject\\\";a:1:{i:0;O:48:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\UnstructuredHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:7:\\\"Subject\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:55:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\UnstructuredHeader\\0value\\\";s:37:\\\"Confirmation de votre réservation #7\\\";}}}s:49:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\0lineLength\\\";i:76;}i:1;N;}}i:4;N;}s:61:\\\"\\0Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\0envelope\\\";N;}}', '[]', 'default', '2026-10-14 17:05:23', '2026-10-14 17:05:23', NULL);
INSERT INTO `messenger_messages` (`id`, `body`, `headers`, `queue_name`, `created_at`, `available_at`, `delivered_at`) VALUES
(6, 'O:36:\\\"Symfony\\\\Component\\\\Messenger\\\\Envelope\\\":2:{s:44:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Envelope\\0stamps\\\";a:1:{s:46:\\\"Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\\";a:1:{i:0;O:46:\\\"Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\\":1:{s:55:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\0busName\\\";s:21:\\\"messenger.bus.default\\\";}}}s:45:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Envelope\\0message\\\";O:51:\\\"Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\\":2:{s:60:\\\"\\0Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\0message\\\";O:39:\\\"Symfony\\\\Bridge\\\\Twig\\\\Mime\\\\TemplatedEmail\\\":5:{i:0;s:38:\\\"emails/reservation_cancelled.html.twig\\\";i:1;N;i:2;a:3:{s:11:\\\"reservation\\\";O:31:\\\"App\\\\Entity\\\\ReservationTransfere\\\":24:{s:35:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0id\\\";i:6;s:42:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0transfere\\\";O:35:\\\"Proxies\\\\__CG__\\\\App\\\\Entity\\\\Transfere\\\":11:{s:24:\\\"\\0App\\\\Entity\\\\Transfere\\0id\\\";i:1;s:32:\\\"\\0App\\\\Entity\\\\Transfere\\0lieuDepart\\\";s:23:\\\"aireport Tunis Carthage\\\";s:33:\\\"\\0App\\\\Entity\\\\Transfere\\0lieuArrivee\\\";s:8:\\\"Hammamet\\\";s:33:\\\"\\0App\\\\Entity\\\\Transfere\\0description\\\";N;s:32:\\\"\\0App\\\\Entity\\\\Transfere\\0prixNormal\\\";s:5:\\\"50.00\\\";s:37:\\\"\\0App\\\\Entity\\\\Transfere\\0prixSaisonForte\\\";s:6:\\\"100.00\\\";s:27:\\\"\\0App\\\\Entity\\\\Transfere\\0actif\\\";b:1;s:26:\\\"\\0App\\\\Entity\\\\Transfere\\0slug\\\";s:17:\\\"aireport-aireport\\\";s:27:\\\"\\0App\\\\Entity\\\\Transfere\\0image\\\";s:44:\\\"/uploads/transferts/images-6ac94de7122fa.jpg\\\";s:34:\\\"\\0App\\\\Entity\\\\Transfere\\0translations\\\";O:33:\\\"Doctrine\\\\ORM\\\\PersistentCollection\\\":2:{s:13:\\\"\\0*\\0collection\\\";O:43:\\\"Doctrine\\\\Common\\\\Collections\\\\ArrayCollection\\\":1:{s:53:\\\"\\0Doctrine\\\\Common\\\\Collections\\\\ArrayCollection\\0elements\\\";a:5:{i:0;O:31:\\\"App\\\\Entity\\\\TransfereTranslation\\\":6:{s:35:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0id\\\";i:4;s:42:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0transfere\\\";r:13;s:39:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0locale\\\";s:2:\\\"de\\\";s:43:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0lieuDepart\\\";N;s:44:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0lieuArrivee\\\";N;s:38:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0duree\\\";N;}i:1;O:31:\\\"App\\\\Entity\\\\TransfereTranslation\\\":6:{s:35:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0id\\\";i:2;s:42:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0transfere\\\";r:13;s:39:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0locale\\\";s:2:\\\"en\\\";s:43:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0lieuDepart\\\";s:22:\\\"Tunis Carthage Airport\\\";s:44:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0lieuArrivee\\\";s:8:\\\"Hammamet\\\";s:38:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0duree\\\";s:6:\\\"60 min\\\";}i:2;O:31:\\\"App\\\\Entity\\\\TransfereTranslation\\\":6:{s:35:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0id\\\";i:3;s:42:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0transfere\\\";r:13;s:39:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0locale\\\";s:2:\\\"es\\\";s:43:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0lieuDepart\\\";N;s:44:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0lieuArrivee\\\";N;s:38:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0duree\\\";N;}i:3;O:31:\\\"App\\\\Entity\\\\TransfereTranslation\\\":6:{s:35:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0id\\\";i:1;s:42:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0transfere\\\";r:13;s:39:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0locale\\\";s:2:\\\"fr\\\";s:43:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0lieuDepart\\\";s:23:\\\"aireport Tunis Carthage\\\";s:44:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0lieuArrivee\\\";s:8:\\\"Hammamet\\\";s:38:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0duree\\\";s:6:\\\"60 min\\\";}i:4;O:31:\\\"App\\\\Entity\\\\TransfereTranslation\\\":6:{s:35:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0id\\\";i:5;s:42:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0transfere\\\";r:13;s:39:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0locale\\\";s:2:\\\"it\\\";s:43:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0lieuDepart\\\";N;s:44:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0lieuArrivee\\\";N;s:38:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0duree\\\";N;}}}s:14:\\\"\\0*\\0initialized\\\";b:1;}s:34:\\\"\\0App\\\\Entity\\\\Transfere\\0reservations\\\";O:33:\\\"Doctrine\\\\ORM\\\\PersistentCollection\\\":2:{s:13:\\\"\\0*\\0collection\\\";O:43:\\\"Doctrine\\\\Common\\\\Collections\\\\ArrayCollection\\\":1:{s:53:\\\"\\0Doctrine\\\\Common\\\\Collections\\\\ArrayCollection\\0elements\\\";a:0:{}}s:14:\\\"\\0*\\0initialized\\\";b:0;}}s:36:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0nom\\\";s:8:\\\"testatds\\\";s:39:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0prenom\\\";s:8:\\\"testatds\\\";s:38:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0email\\\";s:26:\\\"ahmedksontini457@gmail.com\\\";s:42:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0telephone\\\";s:7:\\\"9313278\\\";s:46:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0transfereType\\\";E:41:\\\"App\\\\Enum\\\\ReservationTransfereType:ONE_WAY\\\";s:47:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0pickupLocation\\\";s:22:\\\"Tunis Carthage Airport\\\";s:48:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0dropoffLocation\\\";s:8:\\\"Hammamet\\\";s:43:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0pickupDate\\\";O:17:\\\"DateTimeImmutable\\\":3:{s:4:\\\"date\\\";s:26:\\\"2026-10-22 00:00:00.000000\\\";s:13:\\\"timezone_type\\\";i:3;s:8:\\\"timezone\\\";s:3:\\\"UTC\\\";}s:43:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0pickupTime\\\";O:17:\\\"DateTimeImmutable\\\":3:{s:4:\\\"date\\\";s:26:\\\"1970-01-01 22:02:00.000000\\\";s:13:\\\"timezone_type\\\";i:3;s:8:\\\"timezone\\\";s:3:\\\"UTC\\\";}s:53:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0returnPickupLocation\\\";N;s:54:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0returnDropoffLocation\\\";N;s:49:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0returnPickupDate\\\";N;s:49:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0returnPickupTime\\\";N;s:48:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0nombrePassagers\\\";i:1;s:46:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0nombreBagages\\\";i:0;s:40:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0message\\\";N;s:42:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0numeroVol\\\";N;s:45:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0compagnieVol\\\";N;s:42:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0prixTotal\\\";s:5:\\\"50.00\\\";s:39:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0devise\\\";s:3:\\\"TND\\\";s:39:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0status\\\";E:36:\\\"App\\\\Enum\\\\ReservationStatus:CANCELLED\\\";s:42:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0createdAt\\\";O:17:\\\"DateTimeImmutable\\\":3:{s:4:\\\"date\\\";s:26:\\\"2026-10-14 16:59:41.000000\\\";s:13:\\\"timezone_type\\\";i:3;s:8:\\\"timezone\\\";s:3:\\\"UTC\\\";}}s:10:\\\"clientName\\\";s:17:\\\"testatds testatds\\\";s:8:\\\"siteName\\\";s:16:\\\"Location Mahmoud\\\";}i:3;a:6:{i:0;N;i:1;N;i:2;N;i:3;N;i:4;a:0:{}i:5;a:2:{i:0;O:37:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\\":2:{s:46:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\0headers\\\";a:3:{s:4:\\\"from\\\";a:1:{i:0;O:47:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:4:\\\"From\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:58:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\0addresses\\\";a:1:{i:0;O:30:\\\"Symfony\\\\Component\\\\Mime\\\\Address\\\":2:{s:39:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0address\\\";s:26:\\\"ahmedksontini122@gmail.com\\\";s:36:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0name\\\";s:16:\\\"Location Mahmoud\\\";}}}}s:2:\\\"to\\\";a:1:{i:0;O:47:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:2:\\\"To\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:58:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\0addresses\\\";a:1:{i:0;O:30:\\\"Symfony\\\\Component\\\\Mime\\\\Address\\\":2:{s:39:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0address\\\";s:26:\\\"ahmedksontini457@gmail.com\\\";s:36:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0name\\\";s:17:\\\"testatds testatds\\\";}}}}s:7:\\\"subject\\\";a:1:{i:0;O:48:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\UnstructuredHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:7:\\\"Subject\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:55:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\UnstructuredHeader\\0value\\\";s:47:\\\"Mise à jour du statut de votre réservation #6\\\";}}}s:49:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\0lineLength\\\";i:76;}i:1;N;}}i:4;N;}s:61:\\\"\\0Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\0envelope\\\";N;}}', '[]', 'default', '2026-10-14 17:08:46', '2026-10-14 17:08:46', NULL),
(7, 'O:36:\\\"Symfony\\\\Component\\\\Messenger\\\\Envelope\\\":2:{s:44:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Envelope\\0stamps\\\";a:1:{s:46:\\\"Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\\";a:1:{i:0;O:46:\\\"Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\\":1:{s:55:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\0busName\\\";s:21:\\\"messenger.bus.default\\\";}}}s:45:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Envelope\\0message\\\";O:51:\\\"Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\\":2:{s:60:\\\"\\0Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\0message\\\";O:39:\\\"Symfony\\\\Bridge\\\\Twig\\\\Mime\\\\TemplatedEmail\\\":5:{i:0;s:48:\\\"emails/reservation_excursion_confirmed.html.twig\\\";i:1;N;i:2;a:4:{s:11:\\\"reservation\\\";O:31:\\\"App\\\\Entity\\\\ReservationExcursion\\\":13:{s:35:\\\"\\0App\\\\Entity\\\\ReservationExcursion\\0id\\\";i:4;s:42:\\\"\\0App\\\\Entity\\\\ReservationExcursion\\0excursion\\\";O:35:\\\"Proxies\\\\__CG__\\\\App\\\\Entity\\\\Excursion\\\":33:{s:24:\\\"\\0App\\\\Entity\\\\Excursion\\0id\\\";i:6;s:30:\\\"\\0App\\\\Entity\\\\Excursion\\0category\\\";O:34:\\\"Proxies\\\\__CG__\\\\App\\\\Entity\\\\Category\\\":1:{s:23:\\\"\\0App\\\\Entity\\\\Category\\0id\\\";i:1;}s:33:\\\"\\0App\\\\Entity\\\\Excursion\\0destination\\\";O:37:\\\"Proxies\\\\__CG__\\\\App\\\\Entity\\\\Destination\\\":1:{s:26:\\\"\\0App\\\\Entity\\\\Destination\\0id\\\";i:1;}s:26:\\\"\\0App\\\\Entity\\\\Excursion\\0type\\\";s:7:\\\"private\\\";s:28:\\\"\\0App\\\\Entity\\\\Excursion\\0status\\\";s:6:\\\"active\\\";s:34:\\\"\\0App\\\\Entity\\\\Excursion\\0isBestSeller\\\";b:1;s:32:\\\"\\0App\\\\Entity\\\\Excursion\\0isFeatured\\\";b:0;s:35:\\\"\\0App\\\\Entity\\\\Excursion\\0durationHours\\\";i:3;s:37:\\\"\\0App\\\\Entity\\\\Excursion\\0durationMinutes\\\";i:5;s:31:\\\"\\0App\\\\Entity\\\\Excursion\\0startTime\\\";N;s:29:\\\"\\0App\\\\Entity\\\\Excursion\\0endTime\\\";N;s:32:\\\"\\0App\\\\Entity\\\\Excursion\\0priceAdult\\\";s:6:\\\"100.00\\\";s:32:\\\"\\0App\\\\Entity\\\\Excursion\\0priceChild\\\";s:5:\\\"60.00\\\";s:36:\\\"\\0App\\\\Entity\\\\Excursion\\0priceConverted\\\";N;s:37:\\\"\\0App\\\\Entity\\\\Excursion\\0minParticipants\\\";i:1;s:37:\\\"\\0App\\\\Entity\\\\Excursion\\0maxParticipants\\\";i:10;s:39:\\\"\\0App\\\\Entity\\\\Excursion\\0cancellationHours\\\";i:24;s:35:\\\"\\0App\\\\Entity\\\\Excursion\\0ratingAverage\\\";N;s:33:\\\"\\0App\\\\Entity\\\\Excursion\\0ratingCount\\\";i:0;s:34:\\\"\\0App\\\\Entity\\\\Excursion\\0bookingCount\\\";i:0;s:26:\\\"\\0App\\\\Entity\\\\Excursion\\0slug\\\";s:1:\\\"z\\\";s:31:\\\"\\0App\\\\Entity\\\\Excursion\\0createdAt\\\";O:8:\\\"DateTime\\\":3:{s:4:\\\"date\\\";s:26:\\\"2026-10-05 18:14:52.000000\\\";s:13:\\\"timezone_type\\\";i:3;s:8:\\\"timezone\\\";s:3:\\\"UTC\\\";}s:31:\\\"\\0App\\\\Entity\\\\Excursion\\0updatedAt\\\";O:8:\\\"DateTime\\\":3:{s:4:\\\"date\\\";s:26:\\\"2026-10-05 18:14:52.000000\\\";s:13:\\\"timezone_type\\\";i:3;s:8:\\\"timezone\\\";s:3:\\\"UTC\\\";}s:34:\\\"\\0App\\\\Entity\\\\Excursion\\0translations\\\";O:33:\\\"Doctrine\\\\ORM\\\\PersistentCollection\\\":2:{s:13:\\\"\\0*\\0collection\\\";O:43:\\\"Doctrine\\\\Common\\\\Collections\\\\ArrayCollection\\\":1:{s:53:\\\"\\0Doctrine\\\\Common\\\\Collections\\\\ArrayCollection\\0elements\\\";a:1:{i:0;O:31:\\\"App\\\\Entity\\\\ExcursionTranslation\\\":11:{s:35:\\\"\\0App\\\\Entity\\\\ExcursionTranslation\\0id\\\";i:5;s:42:\\\"\\0App\\\\Entity\\\\ExcursionTranslation\\0excursion\\\";r:13;s:39:\\\"\\0App\\\\Entity\\\\ExcursionTranslation\\0locale\\\";s:2:\\\"fr\\\";s:38:\\\"\\0App\\\\Entity\\\\ExcursionTranslation\\0title\\\";s:5:\\\"ZERZF\\\";s:41:\\\"\\0App\\\\Entity\\\\ExcursionTranslation\\0subtitle\\\";s:5:\\\"ZEFZE\\\";s:44:\\\"\\0App\\\\Entity\\\\ExcursionTranslation\\0description\\\";s:9:\\\"F ZEF ZEF\\\";s:41:\\\"\\0App\\\\Entity\\\\ExcursionTranslation\\0location\\\";N;s:42:\\\"\\0App\\\\Entity\\\\ExcursionTranslation\\0metaTitle\\\";N;s:48:\\\"\\0App\\\\Entity\\\\ExcursionTranslation\\0metaDescription\\\";N;s:42:\\\"\\0App\\\\Entity\\\\ExcursionTranslation\\0createdAt\\\";O:8:\\\"DateTime\\\":3:{s:4:\\\"date\\\";s:26:\\\"2026-10-05 18:14:52.000000\\\";s:13:\\\"timezone_type\\\";i:3;s:8:\\\"timezone\\\";s:3:\\\"UTC\\\";}s:42:\\\"\\0App\\\\Entity\\\\ExcursionTranslation\\0updatedAt\\\";O:8:\\\"DateTime\\\":3:{s:4:\\\"date\\\";s:26:\\\"2026-10-05 18:14:52.000000\\\";s:13:\\\"timezone_type\\\";i:3;s:8:\\\"timezone\\\";s:3:\\\"UTC\\\";}}}}s:14:\\\"\\0*\\0initialized\\\";b:1;}s:28:\\\"\\0App\\\\Entity\\\\Excursion\\0images\\\";O:33:\\\"Doctrine\\\\ORM\\\\PersistentCollection\\\":2:{s:13:\\\"\\0*\\0collection\\\";O:43:\\\"Doctrine\\\\Common\\\\Collections\\\\ArrayCollection\\\":1:{s:53:\\\"\\0Doctrine\\\\Common\\\\Collections\\\\ArrayCollection\\0elements\\\";a:0:{}}s:14:\\\"\\0*\\0initialized\\\";b:0;}s:30:\\\"\\0App\\\\Entity\\\\Excursion\\0features\\\";O:33:\\\"Doctrine\\\\ORM\\\\PersistentCollection\\\":2:{s:13:\\\"\\0*\\0collection\\\";O:43:\\\"Doctrine\\\\Common\\\\Collections\\\\ArrayCollection\\\":1:{s:53:\\\"\\0Doctrine\\\\Common\\\\Collections\\\\ArrayCollection\\0elements\\\";a:0:{}}s:14:\\\"\\0*\\0initialized\\\";b:0;}s:33:\\\"\\0App\\\\Entity\\\\Excursion\\0itineraries\\\";O:33:\\\"Doctrine\\\\ORM\\\\PersistentCollection\\\":2:{s:13:\\\"\\0*\\0collection\\\";O:43:\\\"Doctrine\\\\Common\\\\Collections\\\\ArrayCollection\\\":1:{s:53:\\\"\\0Doctrine\\\\Common\\\\Collections\\\\ArrayCollection\\0elements\\\";a:0:{}}s:14:\\\"\\0*\\0initialized\\\";b:0;}s:32:\\\"\\0App\\\\Entity\\\\Excursion\\0inclusions\\\";O:33:\\\"Doctrine\\\\ORM\\\\PersistentCollection\\\":2:{s:13:\\\"\\0*\\0collection\\\";O:43:\\\"Doctrine\\\\Common\\\\Collections\\\\ArrayCollection\\\":1:{s:53:\\\"\\0Doctrine\\\\Common\\\\Collections\\\\ArrayCollection\\0elements\\\";a:0:{}}s:14:\\\"\\0*\\0initialized\\\";b:0;}s:26:\\\"\\0App\\\\Entity\\\\Excursion\\0faqs\\\";O:33:\\\"Doctrine\\\\ORM\\\\PersistentCollection\\\":2:{s:13:\\\"\\0*\\0collection\\\";O:43:\\\"Doctrine\\\\Common\\\\Collections\\\\ArrayCollection\\\":1:{s:53:\\\"\\0Doctrine\\\\Common\\\\Collections\\\\ArrayCollection\\0elements\\\";a:0:{}}s:14:\\\"\\0*\\0initialized\\\";b:0;}s:28:\\\"\\0App\\\\Entity\\\\Excursion\\0badges\\\";O:33:\\\"Doctrine\\\\ORM\\\\PersistentCollection\\\":2:{s:13:\\\"\\0*\\0collection\\\";O:43:\\\"Doctrine\\\\Common\\\\Collections\\\\ArrayCollection\\\":1:{s:53:\\\"\\0Doctrine\\\\Common\\\\Collections\\\\ArrayCollection\\0elements\\\";a:0:{}}s:14:\\\"\\0*\\0initialized\\\";b:0;}s:29:\\\"\\0App\\\\Entity\\\\Excursion\\0reviews\\\";O:33:\\\"Doctrine\\\\ORM\\\\PersistentCollection\\\":2:{s:13:\\\"\\0*\\0collection\\\";O:43:\\\"Doctrine\\\\Common\\\\Collections\\\\ArrayCollection\\\":1:{s:53:\\\"\\0Doctrine\\\\Common\\\\Collections\\\\ArrayCollection\\0elements\\\";a:0:{}}s:14:\\\"\\0*\\0initialized\\\";b:0;}s:34:\\\"\\0App\\\\Entity\\\\Excursion\\0reservations\\\";O:33:\\\"Doctrine\\\\ORM\\\\PersistentCollection\\\":2:{s:13:\\\"\\0*\\0collection\\\";O:43:\\\"Doctrine\\\\Common\\\\Collections\\\\ArrayCollection\\\":1:{s:53:\\\"\\0Doctrine\\\\Common\\\\Collections\\\\ArrayCollection\\0elements\\\";a:0:{}}s:14:\\\"\\0*\\0initialized\\\";b:0;}s:43:\\\"\\0App\\\\Entity\\\\Excursion\\0reservationExcursions\\\";O:33:\\\"Doctrine\\\\ORM\\\\PersistentCollection\\\":2:{s:13:\\\"\\0*\\0collection\\\";O:43:\\\"Doctrine\\\\Common\\\\Collections\\\\ArrayCollection\\\":1:{s:53:\\\"\\0Doctrine\\\\Common\\\\Collections\\\\ArrayCollection\\0elements\\\";a:0:{}}s:14:\\\"\\0*\\0initialized\\\";b:0;}}s:36:\\\"\\0App\\\\Entity\\\\ReservationExcursion\\0nom\\\";s:5:\\\"ahmed\\\";s:39:\\\"\\0App\\\\Entity\\\\ReservationExcursion\\0prenom\\\";s:8:\\\"ksontini\\\";s:38:\\\"\\0App\\\\Entity\\\\ReservationExcursion\\0email\\\";s:26:\\\"ahmedksontini560@gmail.com\\\";s:41:\\\"\\0App\\\\Entity\\\\ReservationExcursion\\0whatsapp\\\";s:8:\\\"93313278\\\";s:46:\\\"\\0App\\\\Entity\\\\ReservationExcursion\\0nombreAdultes\\\";i:3;s:46:\\\"\\0App\\\\Entity\\\\ReservationExcursion\\0nombreEnfants\\\";i:2;s:46:\\\"\\0App\\\\Entity\\\\ReservationExcursion\\0dateExcursion\\\";O:8:\\\"DateTime\\\":3:{s:4:\\\"date\\\";s:26:\\\"2026-10-22 00:00:00.000000\\\";s:13:\\\"timezone_type\\\";i:3;s:8:\\\"timezone\\\";s:3:\\\"UTC\\\";}s:44:\\\"\\0App\\\\Entity\\\\ReservationExcursion\\0heureDepart\\\";O:8:\\\"DateTime\\\":3:{s:4:\\\"date\\\";s:26:\\\"1970-01-01 14:08:00.000000\\\";s:13:\\\"timezone_type\\\";i:3;s:8:\\\"timezone\\\";s:3:\\\"UTC\\\";}s:50:\\\"\\0App\\\\Entity\\\\ReservationExcursion\\0lieuPriseEnCharge\\\";s:10:\\\"bita coffe\\\";s:39:\\\"\\0App\\\\Entity\\\\ReservationExcursion\\0statut\\\";s:9:\\\"confirmee\\\";s:42:\\\"\\0App\\\\Entity\\\\ReservationExcursion\\0createdAt\\\";O:8:\\\"DateTime\\\":3:{s:4:\\\"date\\\";s:26:\\\"2026-10-15 11:07:01.000000\\\";s:13:\\\"timezone_type\\\";i:3;s:8:\\\"timezone\\\";s:3:\\\"UTC\\\";}}s:14:\\\"excursionTitle\\\";s:5:\\\"ZERZF\\\";s:10:\\\"clientName\\\";s:14:\\\"ahmed ksontini\\\";s:8:\\\"siteName\\\";s:16:\\\"Location Mahmoud\\\";}i:3;a:6:{i:0;N;i:1;N;i:2;N;i:3;N;i:4;a:0:{}i:5;a:2:{i:0;O:37:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\\":2:{s:46:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\0headers\\\";a:3:{s:4:\\\"from\\\";a:1:{i:0;O:47:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:4:\\\"From\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:58:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\0addresses\\\";a:1:{i:0;O:30:\\\"Symfony\\\\Component\\\\Mime\\\\Address\\\":2:{s:39:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0address\\\";s:26:\\\"ahmedksontini122@gmail.com\\\";s:36:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0name\\\";s:16:\\\"Location Mahmoud\\\";}}}}s:2:\\\"to\\\";a:1:{i:0;O:47:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:2:\\\"To\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:58:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\0addresses\\\";a:1:{i:0;O:30:\\\"Symfony\\\\Component\\\\Mime\\\\Address\\\":2:{s:39:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0address\\\";s:26:\\\"ahmedksontini560@gmail.com\\\";s:36:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0name\\\";s:14:\\\"ahmed ksontini\\\";}}}}s:7:\\\"subject\\\";a:1:{i:0;O:48:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\UnstructuredHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:7:\\\"Subject\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:55:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\UnstructuredHeader\\0value\\\";s:51:\\\"Confirmation de votre réservation d’excursion #4\\\";}}}s:49:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\0lineLength\\\";i:76;}i:1;N;}}i:4;N;}s:61:\\\"\\0Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\0envelope\\\";N;}}', '[]', 'default', '2026-10-15 11:08:15', '2026-10-15 11:08:15', NULL),
(8, 'O:36:\\\"Symfony\\\\Component\\\\Messenger\\\\Envelope\\\":2:{s:44:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Envelope\\0stamps\\\";a:1:{s:46:\\\"Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\\";a:1:{i:0;O:46:\\\"Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\\":1:{s:55:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\0busName\\\";s:21:\\\"messenger.bus.default\\\";}}}s:45:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Envelope\\0message\\\";O:51:\\\"Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\\":2:{s:60:\\\"\\0Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\0message\\\";O:28:\\\"Symfony\\\\Component\\\\Mime\\\\Email\\\":6:{i:0;s:4:\\\"test\\\";i:1;s:5:\\\"utf-8\\\";i:2;N;i:3;N;i:4;a:0:{}i:5;a:2:{i:0;O:37:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\\":2:{s:46:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\0headers\\\";a:3:{s:4:\\\"from\\\";a:1:{i:0;O:47:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:4:\\\"From\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:58:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\0addresses\\\";a:1:{i:0;O:30:\\\"Symfony\\\\Component\\\\Mime\\\\Address\\\":2:{s:39:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0address\\\";s:26:\\\"ahmedksontini122@gmail.com\\\";s:36:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0name\\\";s:16:\\\"Location Mahmoud\\\";}}}}s:2:\\\"to\\\";a:1:{i:0;O:47:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:2:\\\"To\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:58:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\0addresses\\\";a:1:{i:0;O:30:\\\"Symfony\\\\Component\\\\Mime\\\\Address\\\":2:{s:39:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0address\\\";s:26:\\\"ahmedksontini560@gamil.com\\\";s:36:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0name\\\";s:14:\\\"ksontini ahmed\\\";}}}}s:7:\\\"subject\\\";a:1:{i:0;O:48:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\UnstructuredHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:7:\\\"Subject\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:55:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\UnstructuredHeader\\0value\\\";s:11:\\\"reservation\\\";}}}s:49:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\0lineLength\\\";i:76;}i:1;N;}}s:61:\\\"\\0Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\0envelope\\\";N;}}', '[]', 'default', '2026-10-17 15:19:38', '2026-10-17 15:19:38', NULL),
(9, 'O:36:\\\"Symfony\\\\Component\\\\Messenger\\\\Envelope\\\":2:{s:44:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Envelope\\0stamps\\\";a:1:{s:46:\\\"Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\\";a:1:{i:0;O:46:\\\"Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\\":1:{s:55:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\0busName\\\";s:21:\\\"messenger.bus.default\\\";}}}s:45:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Envelope\\0message\\\";O:51:\\\"Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\\":2:{s:60:\\\"\\0Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\0message\\\";O:28:\\\"Symfony\\\\Component\\\\Mime\\\\Email\\\":6:{i:0;s:77:\\\"Bonjour ahmed,\n\n---\nMessage original du 25/09/2026 20:07 :\ntest contact email\\\";i:1;s:5:\\\"utf-8\\\";i:2;N;i:3;N;i:4;a:0:{}i:5;a:2:{i:0;O:37:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\\":2:{s:46:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\0headers\\\";a:3:{s:4:\\\"from\\\";a:1:{i:0;O:47:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:4:\\\"From\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:58:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\0addresses\\\";a:1:{i:0;O:30:\\\"Symfony\\\\Component\\\\Mime\\\\Address\\\":2:{s:39:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0address\\\";s:26:\\\"ahmedksontini122@gmail.com\\\";s:36:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0name\\\";s:0:\\\"\\\";}}}}s:2:\\\"to\\\";a:1:{i:0;O:47:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:2:\\\"To\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:58:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\0addresses\\\";a:1:{i:0;O:30:\\\"Symfony\\\\Component\\\\Mime\\\\Address\\\":2:{s:39:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0address\\\";s:26:\\\"ahmedksontini560@gmail.com\\\";s:36:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0name\\\";s:0:\\\"\\\";}}}}s:7:\\\"subject\\\";a:1:{i:0;O:48:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\UnstructuredHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:7:\\\"Subject\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:55:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\UnstructuredHeader\\0value\\\";s:18:\\\"Re: teste de email\\\";}}}s:49:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\0lineLength\\\";i:76;}i:1;N;}}s:61:\\\"\\0Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\0envelope\\\";N;}}', '[]', 'default', '2026-10-17 15:20:51', '2026-10-17 15:20:51', NULL),
(10, 'O:36:\\\"Symfony\\\\Component\\\\Messenger\\\\Envelope\\\":2:{s:44:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Envelope\\0stamps\\\";a:1:{s:46:\\\"Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\\";a:1:{i:0;O:46:\\\"Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\\":1:{s:55:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\0busName\\\";s:21:\\\"messenger.bus.default\\\";}}}s:45:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Envelope\\0message\\\";O:51:\\\"Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\\":2:{s:60:\\\"\\0Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\0message\\\";O:28:\\\"Symfony\\\\Component\\\\Mime\\\\Email\\\":6:{i:0;s:8:\\\"tyesssts\\\";i:1;s:5:\\\"utf-8\\\";i:2;N;i:3;N;i:4;a:0:{}i:5;a:2:{i:0;O:37:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\\":2:{s:46:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\0headers\\\";a:3:{s:4:\\\"from\\\";a:1:{i:0;O:47:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:4:\\\"From\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:58:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\0addresses\\\";a:1:{i:0;O:30:\\\"Symfony\\\\Component\\\\Mime\\\\Address\\\":2:{s:39:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0address\\\";s:26:\\\"ahmedksontini122@gmail.com\\\";s:36:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0name\\\";s:14:\\\"Yassmine Guide\\\";}}}}s:2:\\\"to\\\";a:1:{i:0;O:47:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:2:\\\"To\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:58:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\0addresses\\\";a:1:{i:0;O:30:\\\"Symfony\\\\Component\\\\Mime\\\\Address\\\":2:{s:39:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0address\\\";s:26:\\\"ahmedksontini560@gmail.com\\\";s:36:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0name\\\";s:14:\\\"ahmed ksontini\\\";}}}}s:7:\\\"subject\\\";a:1:{i:0;O:48:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\UnstructuredHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:7:\\\"Subject\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:55:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\UnstructuredHeader\\0value\\\";s:12:\\\"reserfvation\\\";}}}s:49:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\0lineLength\\\";i:76;}i:1;N;}}s:61:\\\"\\0Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\0envelope\\\";N;}}', '[]', 'default', '2026-10-17 16:12:30', '2026-10-17 16:12:30', NULL),
(11, 'O:36:\\\"Symfony\\\\Component\\\\Messenger\\\\Envelope\\\":2:{s:44:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Envelope\\0stamps\\\";a:1:{s:46:\\\"Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\\";a:1:{i:0;O:46:\\\"Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\\":1:{s:55:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\0busName\\\";s:21:\\\"messenger.bus.default\\\";}}}s:45:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Envelope\\0message\\\";O:51:\\\"Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\\":2:{s:60:\\\"\\0Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\0message\\\";O:28:\\\"Symfony\\\\Component\\\\Mime\\\\Email\\\":6:{i:0;s:77:\\\"Bonjour ahmed,\n\n---\nMessage original du 25/09/2026 20:07 :\ntest contact email\\\";i:1;s:5:\\\"utf-8\\\";i:2;N;i:3;N;i:4;a:0:{}i:5;a:2:{i:0;O:37:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\\":2:{s:46:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\0headers\\\";a:3:{s:4:\\\"from\\\";a:1:{i:0;O:47:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:4:\\\"From\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:58:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\0addresses\\\";a:1:{i:0;O:30:\\\"Symfony\\\\Component\\\\Mime\\\\Address\\\":2:{s:39:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0address\\\";s:26:\\\"ahmedksontini122@gmail.com\\\";s:36:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0name\\\";s:14:\\\"Yassmine Guide\\\";}}}}s:2:\\\"to\\\";a:1:{i:0;O:47:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:2:\\\"To\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:58:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\0addresses\\\";a:1:{i:0;O:30:\\\"Symfony\\\\Component\\\\Mime\\\\Address\\\":2:{s:39:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0address\\\";s:26:\\\"ahmedksontini560@gmail.com\\\";s:36:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0name\\\";s:14:\\\"ahmed ksontini\\\";}}}}s:7:\\\"subject\\\";a:1:{i:0;O:48:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\UnstructuredHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:7:\\\"Subject\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:55:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\UnstructuredHeader\\0value\\\";s:18:\\\"Re: teste de email\\\";}}}s:49:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\0lineLength\\\";i:76;}i:1;N;}}s:61:\\\"\\0Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\0envelope\\\";N;}}', '[]', 'default', '2026-10-17 16:12:41', '2026-10-17 16:12:41', NULL),
(12, 'O:36:\\\"Symfony\\\\Component\\\\Messenger\\\\Envelope\\\":2:{s:44:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Envelope\\0stamps\\\";a:1:{s:46:\\\"Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\\";a:1:{i:0;O:46:\\\"Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\\":1:{s:55:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\0busName\\\";s:21:\\\"messenger.bus.default\\\";}}}s:45:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Envelope\\0message\\\";O:51:\\\"Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\\":2:{s:60:\\\"\\0Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\0message\\\";O:28:\\\"Symfony\\\\Component\\\\Mime\\\\Email\\\":6:{i:0;N;i:1;N;i:2;s:5347:\\\"<!DOCTYPE html>\n<html lang=\\\"fr\\\">\n<head>\n    <meta charset=\\\"UTF-8\\\">\n    <meta name=\\\"viewport\\\" content=\\\"width=device-width, initial-scale=1.0\\\">\n    <title>Nouveau message de contact</title>\n    <style>\n        body {\n            font-family: \\\'Helvetica Neue\\\', Arial, sans-serif;\n            line-height: 1.6;\n            color: #333333;\n            background-color: #f8fafc;\n            margin: 0;\n            padding: 0;\n        }\n        .container {\n            max-width: 600px;\n            margin: 40px auto;\n            background-color: #ffffff;\n            border-radius: 12px;\n            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);\n            overflow: hidden;\n        }\n        .header {\n            background-color: #063B73;\n            color: #ffffff;\n            padding: 30px 20px;\n            text-align: center;\n        }\n        .header h1 {\n            margin: 0;\n            font-size: 24px;\n            font-weight: 600;\n        }\n        .header p {\n            margin: 10px 0 0;\n            opacity: 0.9;\n        }\n        .content {\n            padding: 30px 40px;\n        }\n        .section-title {\n            color: #0B5ED7;\n            font-size: 18px;\n            font-weight: 600;\n            margin-bottom: 20px;\n            border-bottom: 2px solid #e2e8f0;\n            padding-bottom: 10px;\n        }\n        .info-table {\n            width: 100%;\n            border-collapse: collapse;\n            margin-bottom: 30px;\n        }\n        .info-table td {\n            padding: 12px 0;\n            border-bottom: 1px solid #f1f5f9;\n        }\n        .info-table td.label {\n            font-weight: 600;\n            color: #475569;\n            width: 35%;\n        }\n        .info-table td.value {\n            color: #1e293b;\n        }\n        .message-box {\n            background-color: #f8fafc;\n            border: 1px solid #e2e8f0;\n            border-radius: 8px;\n            padding: 20px;\n            margin-top: 10px;\n            white-space: pre-wrap;\n            color: #334155;\n        }\n        .footer {\n            background-color: #f1f5f9;\n            padding: 20px;\n            text-align: center;\n            font-size: 12px;\n            color: #64748b;\n            border-top: 1px solid #e2e8f0;\n        }\n        @media only screen and (max-width: 600px) {\n            .container {\n                margin: 20px 10px;\n            }\n            .content {\n                padding: 20px;\n            }\n            .info-table td {\n                display: block;\n                width: 100%;\n            }\n            .info-table td.label {\n                padding-bottom: 4px;\n                border-bottom: none;\n            }\n            .info-table td.value {\n                padding-top: 0;\n                padding-bottom: 16px;\n            }\n        }\n    </style>\n</head>\n<body>\n    <div class=\\\"container\\\">\n        <div class=\\\"header\\\">\n            <h1>Nouveau message de contact</h1>\n            <p>LocationMahmoud</p>\n        </div>\n        \n        <div class=\\\"content\\\">\n            <h2 class=\\\"section-title\\\">Informations du visiteur</h2>\n            <table class=\\\"info-table\\\">\n                <tr>\n                    <td class=\\\"label\\\">Prénom :</td>\n                    <td class=\\\"value\\\">ksontini</td>\n                </tr>\n                <tr>\n                    <td class=\\\"label\\\">Nom :</td>\n                    <td class=\\\"value\\\">amine</td>\n                </tr>\n                <tr>\n                    <td class=\\\"label\\\">Email :</td>\n                    <td class=\\\"value\\\"><a href=\\\"mailto:ahmedksontini560@gmail.com\\\" style=\\\"color: #0B5ED7; text-decoration: none;\\\">ahmedksontini560@gmail.com</a></td>\n                </tr>\n                                <tr>\n                    <td class=\\\"label\\\">Téléphone :</td>\n                    <td class=\\\"value\\\"><a href=\\\"tel:93313278\\\" style=\\\"color: #0B5ED7; text-decoration: none;\\\">93313278</a></td>\n                </tr>\n                                <tr>\n                    <td class=\\\"label\\\">Type de demande :</td>\n                    <td class=\\\"value\\\">Demande de renseignement</td>\n                </tr>\n                <tr>\n                    <td class=\\\"label\\\">Service concerné :</td>\n                    <td class=\\\"value\\\">Location de véhicules</td>\n                </tr>\n                                <tr>\n                    <td class=\\\"label\\\">Numéro de réservation :</td>\n                    <td class=\\\"value\\\">123</td>\n                </tr>\n                                <tr>\n                    <td class=\\\"label\\\">Date d&#039;envoi :</td>\n                    <td class=\\\"value\\\">17/10/2026 16:40</td>\n                </tr>\n            </table>\n\n            <h2 class=\\\"section-title\\\">Détails du message</h2>\n            <div style=\\\"margin-bottom: 10px;\\\">\n                <span style=\\\"font-weight: 600; color: #475569;\\\">Sujet :</span>\n                <span style=\\\"color: #1e293b; margin-left: 10px;\\\">TESTSTST</span>\n            </div>\n            \n            <div class=\\\"message-box\\\">TETSTSTETSTTE</div>\n        </div>\n        \n        <div class=\\\"footer\\\">\n            <p>Ce message a été envoyé automatiquement depuis le formulaire de contact du site LocationMahmoud.</p>\n            <p>&copy; 2026 LocationMahmoud. Tous droits réservés.</p>\n        </div>\n    </div>\n</body>\n</html>\n\\\";i:3;s:5:\\\"utf-8\\\";i:4;a:0:{}i:5;a:2:{i:0;O:37:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\\":2:{s:46:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\0headers\\\";a:4:{s:4:\\\"from\\\";a:1:{i:0;O:47:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:4:\\\"From\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:58:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\0addresses\\\";a:1:{i:0;O:30:\\\"Symfony\\\\Component\\\\Mime\\\\Address\\\":2:{s:39:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0address\\\";s:26:\\\"ahmedksontini122@gmail.com\\\";s:36:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0name\\\";s:14:\\\"Yassmine Guide\\\";}}}}s:2:\\\"to\\\";a:1:{i:0;O:47:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:2:\\\"To\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:58:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\0addresses\\\";a:1:{i:0;O:30:\\\"Symfony\\\\Component\\\\Mime\\\\Address\\\":2:{s:39:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0address\\\";s:26:\\\"ahmedksontini122@gmail.com\\\";s:36:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0name\\\";s:0:\\\"\\\";}}}}s:8:\\\"reply-to\\\";a:1:{i:0;O:47:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:8:\\\"Reply-To\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:58:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\0addresses\\\";a:1:{i:0;O:30:\\\"Symfony\\\\Component\\\\Mime\\\\Address\\\":2:{s:39:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0address\\\";s:26:\\\"ahmedksontini560@gmail.com\\\";s:36:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0name\\\";s:14:\\\"ksontini amine\\\";}}}}s:7:\\\"subject\\\";a:1:{i:0;O:48:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\UnstructuredHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:7:\\\"Subject\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:55:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\UnstructuredHeader\\0value\\\";s:39:\\\"Nouveau message de contact — TESTSTST\\\";}}}s:49:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\0lineLength\\\";i:76;}i:1;N;}}s:61:\\\"\\0Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\0envelope\\\";N;}}', '[]', 'default', '2026-10-17 16:40:57', '2026-10-17 16:40:57', NULL),
(13, 'O:36:\\\"Symfony\\\\Component\\\\Messenger\\\\Envelope\\\":2:{s:44:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Envelope\\0stamps\\\";a:1:{s:46:\\\"Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\\";a:1:{i:0;O:46:\\\"Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\\":1:{s:55:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\0busName\\\";s:21:\\\"messenger.bus.default\\\";}}}s:45:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Envelope\\0message\\\";O:51:\\\"Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\\":2:{s:60:\\\"\\0Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\0message\\\";O:28:\\\"Symfony\\\\Component\\\\Mime\\\\Email\\\":6:{i:0;s:89:\\\"Bonjour ksontini, TESTETS TET E\n\n---\nMessage original du 17/10/2026 16:40 :\nTETSTSTETSTTE\\\";i:1;s:5:\\\"utf-8\\\";i:2;N;i:3;N;i:4;a:0:{}i:5;a:2:{i:0;O:37:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\\":2:{s:46:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\0headers\\\";a:3:{s:4:\\\"from\\\";a:1:{i:0;O:47:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:4:\\\"From\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:58:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\0addresses\\\";a:1:{i:0;O:30:\\\"Symfony\\\\Component\\\\Mime\\\\Address\\\":2:{s:39:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0address\\\";s:26:\\\"ahmedksontini122@gmail.com\\\";s:36:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0name\\\";s:14:\\\"Yassmine Guide\\\";}}}}s:2:\\\"to\\\";a:1:{i:0;O:47:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:2:\\\"To\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:58:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\0addresses\\\";a:1:{i:0;O:30:\\\"Symfony\\\\Component\\\\Mime\\\\Address\\\":2:{s:39:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0address\\\";s:26:\\\"ahmedksontini560@gmail.com\\\";s:36:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0name\\\";s:14:\\\"ksontini amine\\\";}}}}s:7:\\\"subject\\\";a:1:{i:0;O:48:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\UnstructuredHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:7:\\\"Subject\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:55:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\UnstructuredHeader\\0value\\\";s:12:\\\"Re: TESTSTST\\\";}}}s:49:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\0lineLength\\\";i:76;}i:1;N;}}s:61:\\\"\\0Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\0envelope\\\";N;}}', '[]', 'default', '2026-10-17 16:41:29', '2026-10-17 16:41:29', NULL);
INSERT INTO `messenger_messages` (`id`, `body`, `headers`, `queue_name`, `created_at`, `available_at`, `delivered_at`) VALUES
(14, 'O:36:\\\"Symfony\\\\Component\\\\Messenger\\\\Envelope\\\":2:{s:44:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Envelope\\0stamps\\\";a:1:{s:46:\\\"Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\\";a:1:{i:0;O:46:\\\"Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\\":1:{s:55:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\0busName\\\";s:21:\\\"messenger.bus.default\\\";}}}s:45:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Envelope\\0message\\\";O:51:\\\"Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\\":2:{s:60:\\\"\\0Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\0message\\\";O:28:\\\"Symfony\\\\Component\\\\Mime\\\\Email\\\":6:{i:0;s:12:\\\"tzetze zf zf\\\";i:1;s:5:\\\"utf-8\\\";i:2;N;i:3;N;i:4;a:0:{}i:5;a:2:{i:0;O:37:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\\":2:{s:46:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\0headers\\\";a:3:{s:4:\\\"from\\\";a:1:{i:0;O:47:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:4:\\\"From\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:58:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\0addresses\\\";a:1:{i:0;O:30:\\\"Symfony\\\\Component\\\\Mime\\\\Address\\\":2:{s:39:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0address\\\";s:26:\\\"ahmedksontini122@gmail.com\\\";s:36:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0name\\\";s:14:\\\"Yassmine Guide\\\";}}}}s:2:\\\"to\\\";a:1:{i:0;O:47:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:2:\\\"To\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:58:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\0addresses\\\";a:1:{i:0;O:30:\\\"Symfony\\\\Component\\\\Mime\\\\Address\\\":2:{s:39:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0address\\\";s:26:\\\"ahmedksontini122@gmail.com\\\";s:36:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0name\\\";s:5:\\\"tetet\\\";}}}}s:7:\\\"subject\\\";a:1:{i:0;O:48:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\UnstructuredHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:7:\\\"Subject\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:55:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\UnstructuredHeader\\0value\\\";s:4:\\\"tete\\\";}}}s:49:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\0lineLength\\\";i:76;}i:1;N;}}s:61:\\\"\\0Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\0envelope\\\";N;}}', '[]', 'default', '2026-10-17 16:42:25', '2026-10-17 16:42:25', NULL),
(15, 'O:36:\\\"Symfony\\\\Component\\\\Messenger\\\\Envelope\\\":2:{s:44:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Envelope\\0stamps\\\";a:1:{s:46:\\\"Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\\";a:1:{i:0;O:46:\\\"Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\\":1:{s:55:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\0busName\\\";s:21:\\\"messenger.bus.default\\\";}}}s:45:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Envelope\\0message\\\";O:51:\\\"Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\\":2:{s:60:\\\"\\0Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\0message\\\";O:39:\\\"Symfony\\\\Bridge\\\\Twig\\\\Mime\\\\TemplatedEmail\\\":5:{i:0;s:38:\\\"emails/reservation_cancelled.html.twig\\\";i:1;N;i:2;a:3:{s:11:\\\"reservation\\\";O:31:\\\"App\\\\Entity\\\\ReservationTransfere\\\":24:{s:35:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0id\\\";i:7;s:42:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0transfere\\\";O:35:\\\"Proxies\\\\__CG__\\\\App\\\\Entity\\\\Transfere\\\":11:{s:24:\\\"\\0App\\\\Entity\\\\Transfere\\0id\\\";i:1;s:32:\\\"\\0App\\\\Entity\\\\Transfere\\0lieuDepart\\\";s:23:\\\"aireport Tunis Carthage\\\";s:33:\\\"\\0App\\\\Entity\\\\Transfere\\0lieuArrivee\\\";s:8:\\\"Hammamet\\\";s:33:\\\"\\0App\\\\Entity\\\\Transfere\\0description\\\";N;s:32:\\\"\\0App\\\\Entity\\\\Transfere\\0prixNormal\\\";s:5:\\\"50.00\\\";s:37:\\\"\\0App\\\\Entity\\\\Transfere\\0prixSaisonForte\\\";s:6:\\\"100.00\\\";s:27:\\\"\\0App\\\\Entity\\\\Transfere\\0actif\\\";b:1;s:26:\\\"\\0App\\\\Entity\\\\Transfere\\0slug\\\";s:17:\\\"aireport-aireport\\\";s:27:\\\"\\0App\\\\Entity\\\\Transfere\\0image\\\";s:44:\\\"/uploads/transferts/images-6ac94de7122fa.jpg\\\";s:34:\\\"\\0App\\\\Entity\\\\Transfere\\0translations\\\";O:33:\\\"Doctrine\\\\ORM\\\\PersistentCollection\\\":2:{s:13:\\\"\\0*\\0collection\\\";O:43:\\\"Doctrine\\\\Common\\\\Collections\\\\ArrayCollection\\\":1:{s:53:\\\"\\0Doctrine\\\\Common\\\\Collections\\\\ArrayCollection\\0elements\\\";a:5:{i:0;O:31:\\\"App\\\\Entity\\\\TransfereTranslation\\\":6:{s:35:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0id\\\";i:4;s:42:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0transfere\\\";r:13;s:39:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0locale\\\";s:2:\\\"de\\\";s:43:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0lieuDepart\\\";N;s:44:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0lieuArrivee\\\";N;s:38:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0duree\\\";N;}i:1;O:31:\\\"App\\\\Entity\\\\TransfereTranslation\\\":6:{s:35:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0id\\\";i:2;s:42:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0transfere\\\";r:13;s:39:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0locale\\\";s:2:\\\"en\\\";s:43:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0lieuDepart\\\";s:22:\\\"Tunis Carthage Airport\\\";s:44:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0lieuArrivee\\\";s:8:\\\"Hammamet\\\";s:38:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0duree\\\";s:6:\\\"60 min\\\";}i:2;O:31:\\\"App\\\\Entity\\\\TransfereTranslation\\\":6:{s:35:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0id\\\";i:3;s:42:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0transfere\\\";r:13;s:39:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0locale\\\";s:2:\\\"es\\\";s:43:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0lieuDepart\\\";N;s:44:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0lieuArrivee\\\";N;s:38:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0duree\\\";N;}i:3;O:31:\\\"App\\\\Entity\\\\TransfereTranslation\\\":6:{s:35:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0id\\\";i:1;s:42:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0transfere\\\";r:13;s:39:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0locale\\\";s:2:\\\"fr\\\";s:43:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0lieuDepart\\\";s:23:\\\"aireport Tunis Carthage\\\";s:44:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0lieuArrivee\\\";s:8:\\\"Hammamet\\\";s:38:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0duree\\\";s:6:\\\"60 min\\\";}i:4;O:31:\\\"App\\\\Entity\\\\TransfereTranslation\\\":6:{s:35:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0id\\\";i:5;s:42:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0transfere\\\";r:13;s:39:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0locale\\\";s:2:\\\"it\\\";s:43:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0lieuDepart\\\";N;s:44:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0lieuArrivee\\\";N;s:38:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0duree\\\";N;}}}s:14:\\\"\\0*\\0initialized\\\";b:1;}s:34:\\\"\\0App\\\\Entity\\\\Transfere\\0reservations\\\";O:33:\\\"Doctrine\\\\ORM\\\\PersistentCollection\\\":2:{s:13:\\\"\\0*\\0collection\\\";O:43:\\\"Doctrine\\\\Common\\\\Collections\\\\ArrayCollection\\\":1:{s:53:\\\"\\0Doctrine\\\\Common\\\\Collections\\\\ArrayCollection\\0elements\\\";a:0:{}}s:14:\\\"\\0*\\0initialized\\\";b:0;}}s:36:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0nom\\\";s:8:\\\"tettette\\\";s:39:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0prenom\\\";s:8:\\\"tettette\\\";s:38:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0email\\\";s:26:\\\"ahmedksontini122@gmail.com\\\";s:42:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0telephone\\\";s:8:\\\"93313278\\\";s:46:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0transfereType\\\";E:41:\\\"App\\\\Enum\\\\ReservationTransfereType:ONE_WAY\\\";s:47:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0pickupLocation\\\";s:23:\\\"aireport Tunis Carthage\\\";s:48:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0dropoffLocation\\\";s:8:\\\"Hammamet\\\";s:43:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0pickupDate\\\";O:17:\\\"DateTimeImmutable\\\":3:{s:4:\\\"date\\\";s:26:\\\"2026-10-29 00:00:00.000000\\\";s:13:\\\"timezone_type\\\";i:3;s:8:\\\"timezone\\\";s:3:\\\"UTC\\\";}s:43:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0pickupTime\\\";O:17:\\\"DateTimeImmutable\\\":3:{s:4:\\\"date\\\";s:26:\\\"1970-01-01 22:08:00.000000\\\";s:13:\\\"timezone_type\\\";i:3;s:8:\\\"timezone\\\";s:3:\\\"UTC\\\";}s:53:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0returnPickupLocation\\\";N;s:54:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0returnDropoffLocation\\\";N;s:49:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0returnPickupDate\\\";N;s:49:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0returnPickupTime\\\";N;s:48:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0nombrePassagers\\\";i:1;s:46:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0nombreBagages\\\";i:0;s:40:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0message\\\";N;s:42:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0numeroVol\\\";N;s:45:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0compagnieVol\\\";N;s:42:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0prixTotal\\\";s:5:\\\"50.00\\\";s:39:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0devise\\\";s:3:\\\"TND\\\";s:39:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0status\\\";E:36:\\\"App\\\\Enum\\\\ReservationStatus:CANCELLED\\\";s:42:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0createdAt\\\";O:17:\\\"DateTimeImmutable\\\":3:{s:4:\\\"date\\\";s:26:\\\"2026-10-14 17:05:08.000000\\\";s:13:\\\"timezone_type\\\";i:3;s:8:\\\"timezone\\\";s:3:\\\"UTC\\\";}}s:10:\\\"clientName\\\";s:17:\\\"tettette tettette\\\";s:8:\\\"siteName\\\";s:14:\\\"Yassmine Guide\\\";}i:3;a:6:{i:0;N;i:1;N;i:2;N;i:3;N;i:4;a:0:{}i:5;a:2:{i:0;O:37:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\\":2:{s:46:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\0headers\\\";a:3:{s:4:\\\"from\\\";a:1:{i:0;O:47:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:4:\\\"From\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:58:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\0addresses\\\";a:1:{i:0;O:30:\\\"Symfony\\\\Component\\\\Mime\\\\Address\\\":2:{s:39:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0address\\\";s:26:\\\"ahmedksontini122@gmail.com\\\";s:36:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0name\\\";s:14:\\\"Yassmine Guide\\\";}}}}s:2:\\\"to\\\";a:1:{i:0;O:47:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:2:\\\"To\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:58:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\0addresses\\\";a:1:{i:0;O:30:\\\"Symfony\\\\Component\\\\Mime\\\\Address\\\":2:{s:39:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0address\\\";s:26:\\\"ahmedksontini122@gmail.com\\\";s:36:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0name\\\";s:17:\\\"tettette tettette\\\";}}}}s:7:\\\"subject\\\";a:1:{i:0;O:48:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\UnstructuredHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:7:\\\"Subject\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:55:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\UnstructuredHeader\\0value\\\";s:47:\\\"Mise à jour du statut de votre réservation #7\\\";}}}s:49:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\0lineLength\\\";i:76;}i:1;N;}}i:4;N;}s:61:\\\"\\0Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\0envelope\\\";N;}}', '[]', 'default', '2026-10-17 17:07:26', '2026-10-17 17:07:26', NULL),
(16, 'O:36:\\\"Symfony\\\\Component\\\\Messenger\\\\Envelope\\\":2:{s:44:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Envelope\\0stamps\\\";a:1:{s:46:\\\"Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\\";a:1:{i:0;O:46:\\\"Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\\":1:{s:55:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Stamp\\\\BusNameStamp\\0busName\\\";s:21:\\\"messenger.bus.default\\\";}}}s:45:\\\"\\0Symfony\\\\Component\\\\Messenger\\\\Envelope\\0message\\\";O:51:\\\"Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\\":2:{s:60:\\\"\\0Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\0message\\\";O:39:\\\"Symfony\\\\Bridge\\\\Twig\\\\Mime\\\\TemplatedEmail\\\":5:{i:0;s:38:\\\"emails/reservation_confirmed.html.twig\\\";i:1;N;i:2;a:3:{s:11:\\\"reservation\\\";O:31:\\\"App\\\\Entity\\\\ReservationTransfere\\\":24:{s:35:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0id\\\";i:7;s:42:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0transfere\\\";O:35:\\\"Proxies\\\\__CG__\\\\App\\\\Entity\\\\Transfere\\\":11:{s:24:\\\"\\0App\\\\Entity\\\\Transfere\\0id\\\";i:1;s:32:\\\"\\0App\\\\Entity\\\\Transfere\\0lieuDepart\\\";s:23:\\\"aireport Tunis Carthage\\\";s:33:\\\"\\0App\\\\Entity\\\\Transfere\\0lieuArrivee\\\";s:8:\\\"Hammamet\\\";s:33:\\\"\\0App\\\\Entity\\\\Transfere\\0description\\\";N;s:32:\\\"\\0App\\\\Entity\\\\Transfere\\0prixNormal\\\";s:5:\\\"50.00\\\";s:37:\\\"\\0App\\\\Entity\\\\Transfere\\0prixSaisonForte\\\";s:6:\\\"100.00\\\";s:27:\\\"\\0App\\\\Entity\\\\Transfere\\0actif\\\";b:1;s:26:\\\"\\0App\\\\Entity\\\\Transfere\\0slug\\\";s:17:\\\"aireport-aireport\\\";s:27:\\\"\\0App\\\\Entity\\\\Transfere\\0image\\\";s:44:\\\"/uploads/transferts/images-6ac94de7122fa.jpg\\\";s:34:\\\"\\0App\\\\Entity\\\\Transfere\\0translations\\\";O:33:\\\"Doctrine\\\\ORM\\\\PersistentCollection\\\":2:{s:13:\\\"\\0*\\0collection\\\";O:43:\\\"Doctrine\\\\Common\\\\Collections\\\\ArrayCollection\\\":1:{s:53:\\\"\\0Doctrine\\\\Common\\\\Collections\\\\ArrayCollection\\0elements\\\";a:5:{i:0;O:31:\\\"App\\\\Entity\\\\TransfereTranslation\\\":6:{s:35:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0id\\\";i:4;s:42:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0transfere\\\";r:13;s:39:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0locale\\\";s:2:\\\"de\\\";s:43:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0lieuDepart\\\";N;s:44:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0lieuArrivee\\\";N;s:38:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0duree\\\";N;}i:1;O:31:\\\"App\\\\Entity\\\\TransfereTranslation\\\":6:{s:35:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0id\\\";i:2;s:42:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0transfere\\\";r:13;s:39:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0locale\\\";s:2:\\\"en\\\";s:43:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0lieuDepart\\\";s:22:\\\"Tunis Carthage Airport\\\";s:44:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0lieuArrivee\\\";s:8:\\\"Hammamet\\\";s:38:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0duree\\\";s:6:\\\"60 min\\\";}i:2;O:31:\\\"App\\\\Entity\\\\TransfereTranslation\\\":6:{s:35:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0id\\\";i:3;s:42:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0transfere\\\";r:13;s:39:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0locale\\\";s:2:\\\"es\\\";s:43:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0lieuDepart\\\";N;s:44:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0lieuArrivee\\\";N;s:38:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0duree\\\";N;}i:3;O:31:\\\"App\\\\Entity\\\\TransfereTranslation\\\":6:{s:35:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0id\\\";i:1;s:42:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0transfere\\\";r:13;s:39:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0locale\\\";s:2:\\\"fr\\\";s:43:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0lieuDepart\\\";s:23:\\\"aireport Tunis Carthage\\\";s:44:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0lieuArrivee\\\";s:8:\\\"Hammamet\\\";s:38:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0duree\\\";s:6:\\\"60 min\\\";}i:4;O:31:\\\"App\\\\Entity\\\\TransfereTranslation\\\":6:{s:35:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0id\\\";i:5;s:42:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0transfere\\\";r:13;s:39:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0locale\\\";s:2:\\\"it\\\";s:43:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0lieuDepart\\\";N;s:44:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0lieuArrivee\\\";N;s:38:\\\"\\0App\\\\Entity\\\\TransfereTranslation\\0duree\\\";N;}}}s:14:\\\"\\0*\\0initialized\\\";b:1;}s:34:\\\"\\0App\\\\Entity\\\\Transfere\\0reservations\\\";O:33:\\\"Doctrine\\\\ORM\\\\PersistentCollection\\\":2:{s:13:\\\"\\0*\\0collection\\\";O:43:\\\"Doctrine\\\\Common\\\\Collections\\\\ArrayCollection\\\":1:{s:53:\\\"\\0Doctrine\\\\Common\\\\Collections\\\\ArrayCollection\\0elements\\\";a:0:{}}s:14:\\\"\\0*\\0initialized\\\";b:0;}}s:36:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0nom\\\";s:8:\\\"tettette\\\";s:39:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0prenom\\\";s:8:\\\"tettette\\\";s:38:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0email\\\";s:26:\\\"ahmedksontini122@gmail.com\\\";s:42:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0telephone\\\";s:8:\\\"93313278\\\";s:46:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0transfereType\\\";E:41:\\\"App\\\\Enum\\\\ReservationTransfereType:ONE_WAY\\\";s:47:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0pickupLocation\\\";s:23:\\\"aireport Tunis Carthage\\\";s:48:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0dropoffLocation\\\";s:8:\\\"Hammamet\\\";s:43:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0pickupDate\\\";O:17:\\\"DateTimeImmutable\\\":3:{s:4:\\\"date\\\";s:26:\\\"2026-10-29 00:00:00.000000\\\";s:13:\\\"timezone_type\\\";i:3;s:8:\\\"timezone\\\";s:3:\\\"UTC\\\";}s:43:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0pickupTime\\\";O:17:\\\"DateTimeImmutable\\\":3:{s:4:\\\"date\\\";s:26:\\\"1970-01-01 22:08:00.000000\\\";s:13:\\\"timezone_type\\\";i:3;s:8:\\\"timezone\\\";s:3:\\\"UTC\\\";}s:53:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0returnPickupLocation\\\";N;s:54:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0returnDropoffLocation\\\";N;s:49:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0returnPickupDate\\\";N;s:49:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0returnPickupTime\\\";N;s:48:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0nombrePassagers\\\";i:1;s:46:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0nombreBagages\\\";i:0;s:40:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0message\\\";N;s:42:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0numeroVol\\\";N;s:45:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0compagnieVol\\\";N;s:42:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0prixTotal\\\";s:5:\\\"50.00\\\";s:39:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0devise\\\";s:3:\\\"TND\\\";s:39:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0status\\\";E:36:\\\"App\\\\Enum\\\\ReservationStatus:CONFIRMED\\\";s:42:\\\"\\0App\\\\Entity\\\\ReservationTransfere\\0createdAt\\\";O:17:\\\"DateTimeImmutable\\\":3:{s:4:\\\"date\\\";s:26:\\\"2026-10-14 17:05:08.000000\\\";s:13:\\\"timezone_type\\\";i:3;s:8:\\\"timezone\\\";s:3:\\\"UTC\\\";}}s:10:\\\"clientName\\\";s:17:\\\"tettette tettette\\\";s:8:\\\"siteName\\\";s:14:\\\"Yassmine Guide\\\";}i:3;a:6:{i:0;N;i:1;N;i:2;N;i:3;N;i:4;a:0:{}i:5;a:2:{i:0;O:37:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\\":2:{s:46:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\0headers\\\";a:3:{s:4:\\\"from\\\";a:1:{i:0;O:47:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:4:\\\"From\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:58:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\0addresses\\\";a:1:{i:0;O:30:\\\"Symfony\\\\Component\\\\Mime\\\\Address\\\":2:{s:39:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0address\\\";s:26:\\\"ahmedksontini122@gmail.com\\\";s:36:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0name\\\";s:14:\\\"Yassmine Guide\\\";}}}}s:2:\\\"to\\\";a:1:{i:0;O:47:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:2:\\\"To\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:58:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\MailboxListHeader\\0addresses\\\";a:1:{i:0;O:30:\\\"Symfony\\\\Component\\\\Mime\\\\Address\\\":2:{s:39:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0address\\\";s:26:\\\"ahmedksontini122@gmail.com\\\";s:36:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Address\\0name\\\";s:17:\\\"tettette tettette\\\";}}}}s:7:\\\"subject\\\";a:1:{i:0;O:48:\\\"Symfony\\\\Component\\\\Mime\\\\Header\\\\UnstructuredHeader\\\":5:{s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0name\\\";s:7:\\\"Subject\\\";s:56:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lineLength\\\";i:76;s:50:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0lang\\\";N;s:53:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\AbstractHeader\\0charset\\\";s:5:\\\"utf-8\\\";s:55:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\UnstructuredHeader\\0value\\\";s:37:\\\"Confirmation de votre réservation #7\\\";}}}s:49:\\\"\\0Symfony\\\\Component\\\\Mime\\\\Header\\\\Headers\\0lineLength\\\";i:76;}i:1;N;}}i:4;N;}s:61:\\\"\\0Symfony\\\\Component\\\\Mailer\\\\Messenger\\\\SendEmailMessage\\0envelope\\\";N;}}', '[]', 'default', '2026-10-17 17:07:31', '2026-10-17 17:07:31', NULL);

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
-- Structure de la table `reservation_excursion`
--

DROP TABLE IF EXISTS `reservation_excursion`;
CREATE TABLE IF NOT EXISTS `reservation_excursion` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nom` varchar(255) NOT NULL,
  `prenom` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `whatsapp` varchar(50) NOT NULL,
  `nombre_adultes` int NOT NULL,
  `nombre_enfants` int NOT NULL,
  `date_excursion` date NOT NULL,
  `heure_depart` time NOT NULL,
  `lieu_prise_en_charge` longtext NOT NULL,
  `statut` varchar(20) NOT NULL DEFAULT 'en_attente',
  `created_at` datetime NOT NULL,
  `excursion_id` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `IDX_814F86B04AB4296F` (`excursion_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb3;

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
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `reservation_transfere`
--

INSERT INTO `reservation_transfere` (`id`, `transfere_id`, `nom`, `prenom`, `email`, `telephone`, `transfere_type`, `pickup_location`, `dropoff_location`, `pickup_date`, `pickup_time`, `return_pickup_location`, `return_dropoff_location`, `return_pickup_date`, `return_pickup_time`, `nombre_passagers`, `nombre_bagages`, `message`, `prix_total`, `devise`, `status`, `created_at`, `numero_vol`, `compagnie_vol`) VALUES
(5, 1, 'Ahmedksontini', 'Ahmedksontini', 'ahmedksontini560@gmail.com', '93313278', 'one_way', 'aireport Tunis Carthage', 'Hammamet', '2026-10-19', '22:03:00', NULL, NULL, NULL, NULL, 1, 2, 'message de test', 50.00, 'TND', 'confirmed', '2026-10-11 20:00:37', 'TU123', 'Tunisaire'),
(6, 1, 'testatds', 'testatds', 'ahmedksontini457@gmail.com', '9313278', 'one_way', 'Tunis Carthage Airport', 'Hammamet', '2026-10-22', '22:02:00', NULL, NULL, NULL, NULL, 1, 0, NULL, 50.00, 'TND', 'cancelled', '2026-10-14 16:59:41', NULL, NULL),
(7, 1, 'tettette', 'tettette', 'ahmedksontini122@gmail.com', '93313278', 'one_way', 'aireport Tunis Carthage', 'Hammamet', '2026-10-29', '22:08:00', NULL, NULL, NULL, NULL, 1, 0, NULL, 50.00, 'TND', 'confirmed', '2026-10-14 17:05:08', NULL, NULL);

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
  `image` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `UNIQ_TRANSFERE_SLUG` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `transfere`
--

INSERT INTO `transfere` (`id`, `lieu_depart`, `lieu_arrivee`, `actif`, `slug`, `prix_normal`, `prix_saison_forte`, `description`, `image`) VALUES
(1, 'aireport Tunis Carthage', 'Hammamet', 1, 'aireport-aireport', 50.00, 100.00, NULL, '/uploads/transferts/images-6ac94de7122fa.jpg'),
(3, 'Aireport Enfidha', 'Sousse', 1, 'aireport-enfidha-sousse', 100.00, 200.00, NULL, '/uploads/transferts/sousse-quartiers-a-eviter-zones-a-privilegier-securite-conseils-6ad3a6e526a02.webp'),
(4, 'Aereport Tunis Carthage', 'Monastir', 1, 'aereport-tunis-carthage-monastir', 120.00, 180.00, 'TEST DE DESCRIPTION', '/uploads/transferts/img-2021-06-08-02-31-58-1-monastir-mausolee-de-bourguiba-C-paulfell-as-86532928-showfront-487-366-6ad3a7fe05fa2.jpg'),
(5, 'Aéroport Enfidha', 'Mahdia', 1, 'aeroport-enfidha-mahdia', 120.00, 190.00, NULL, '/uploads/transferts/Lamphitheatre-dEl-Jem-Mahdia-Tunisie-14-Blog-Etnafes-6ad3a93394b3e.jpg');

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
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `transfere_translation`
--

INSERT INTO `transfere_translation` (`id`, `transfere_id`, `locale`, `lieu_depart`, `lieu_arrivee`, `duree`) VALUES
(1, 1, 'fr', 'aireport Tunis Carthage', 'Hammamet', '60 min'),
(2, 1, 'en', 'Tunis Carthage Airport', 'Hammamet', '60 min'),
(3, 1, 'es', NULL, NULL, NULL),
(4, 1, 'de', NULL, NULL, NULL),
(5, 1, 'it', NULL, NULL, NULL),
(8, 3, 'fr', 'Aireport Enfidha', 'Sousse', '1 Heure'),
(9, 3, 'en', 'Aéroport d\'Enfidha', 'Sousse', '1 Hour'),
(10, 4, 'fr', 'Aereport Tunis Carthage', 'Monastir', '2:30 Heures'),
(11, 4, 'en', 'Aéroport Tunis Carthage', 'Monastir', '2:30 Hours'),
(12, 5, 'fr', 'Aéroport Enfidha', 'Mahdia', '2  Heures'),
(13, 5, 'en', 'Aéroport d\'Enfidha', 'Mahdia', '2 Hours');

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
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb3;

--
-- Déchargement des données de la table `user`
--

INSERT INTO `user` (`id`, `email`, `roles`, `password`) VALUES
(9, 'qa-excursion@test.local', '[\"ROLE_ADMIN\"]', '$2y$13$ZHxJcxLYNqPvNkqEgKTgUubBYrxVAyImoKFKpUo.17wGdUzv65E0e'),
(5, 'ahmed@gmail.com', '[\"ROLE_ADMIN\"]', '$2y$13$NTsSuCFvFACye9AgNhktJOqL99Ir3HJ4FvGKfHPk565bwePyr6JIq'),
(8, 'azdazdazd@gmail.com', '[]', '$2y$13$jocbkPuQotUyLkQQRIbTUu2NpgAaJ/Ah5LI1hygDqV46kVAePubHe');

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
-- Contraintes pour la table `reservation_excursion`
--
ALTER TABLE `reservation_excursion`
  ADD CONSTRAINT `FK_814F86B04AB4296F` FOREIGN KEY (`excursion_id`) REFERENCES `excursion` (`id`) ON DELETE RESTRICT;

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
