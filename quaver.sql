-- phpMyAdmin SQL Dump
-- version 4.1.12
-- http://www.phpmyadmin.net
--
-- Servidor: localhost:8889
-- Tiempo de generación: 24-06-2014 a las 16:20:50
-- Versión del servidor: 5.5.34
-- Versión de PHP: 5.5.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de datos: `quaver`
--
CREATE DATABASE IF NOT EXISTS `quaver` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `quaver`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `lang`
--

DROP TABLE IF EXISTS `lang`;
CREATE TABLE `lang` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL DEFAULT '',
  `slug` varchar(3) NOT NULL DEFAULT '',
  `tld` varchar(8) DEFAULT NULL,
  `locale` varchar(5) NOT NULL DEFAULT '',
  `customerLanguage` varchar(3) NOT NULL DEFAULT '',
  `active` varchar(1) NOT NULL DEFAULT 'y',
  `priority` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=7 ;

--
-- Volcado de datos para la tabla `lang`
--

INSERT INTO `lang` (`id`, `name`, `slug`, `tld`, `locale`, `customerLanguage`, `active`, `priority`) VALUES
(1, 'English', 'eng', '.co.uk', 'en_US', '001', 'y', 1),
(2, 'Español', 'esp', '.es', 'es_ES', '002', 'y', 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `lang_strings`
--

DROP TABLE IF EXISTS `lang_strings`;
CREATE TABLE `lang_strings` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `language` int(11) NOT NULL,
  `label` varchar(64) NOT NULL DEFAULT '',
  `text` text NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `url`
--

DROP TABLE IF EXISTS `url`;
CREATE TABLE `url` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `url` varchar(255) NOT NULL DEFAULT '',
  `controller` varchar(64) DEFAULT '',
  `enabled` varchar(1) NOT NULL DEFAULT 'y',
  PRIMARY KEY (`id`),
  UNIQUE KEY `url` (`url`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=118 ;

--
-- Volcado de datos para la tabla `url`
--

INSERT INTO `url` (`id`, `url`, `controller`, `enabled`) VALUES
(1, '/', 'home', 'y'),
(2, '/404/', '404', 'y'),
(4, '/login/', 'login', 'y'),
(5, '/logout/', 'logout', 'y'),
(6, '/register/', 'register', 'y');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user`
--

DROP TABLE IF EXISTS `user`;
CREATE TABLE `user` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `active` varchar(1) NOT NULL DEFAULT 'y',
  `level` varchar(8) NOT NULL DEFAULT 'user',
  `email` varchar(255) NOT NULL DEFAULT '',
  `password` varchar(40) NOT NULL DEFAULT '',
  `salt` varchar(8) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `surname` varchar(255) NOT NULL DEFAULT '',
  `phone` varchar(16) DEFAULT '',
  `id_number` varchar(16) DEFAULT '',
  `avatar` varchar(255) DEFAULT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(32) DEFAULT NULL,
  `timezone` varchar(32) NOT NULL DEFAULT '',
  `biography` text,
  `registered` int(11) NOT NULL,
  `last_login` int(11) NOT NULL,
  `last_activity` int(11) NOT NULL,
  `language` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
