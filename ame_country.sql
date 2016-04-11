-- phpMyAdmin SQL Dump
-- version 4.1.14
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Apr 11, 2016 at 12:53 PM
-- Server version: 5.6.17
-- PHP Version: 5.3.15

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `ameri670_ame`
--

-- --------------------------------------------------------

--
-- Table structure for table `ame_country`
--

CREATE TABLE IF NOT EXISTS `ame_country` (
  `code` varchar(10) NOT NULL COMMENT '国家代码',
  `name_zh` varchar(20) NOT NULL,
  `name_en` varchar(20) NOT NULL,
  `name_cn` varchar(20) NOT NULL,
  `is_open` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否能寄',
  PRIMARY KEY (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `ame_country`
--

INSERT INTO `ame_country` (`code`, `name_zh`, `name_en`, `name_cn`, `is_open`) VALUES
('CA', '加拿大', 'Canada', '加拿大', 1),
('CN', '中國', 'China', '中国', 1),
('TW', '台灣', 'Taiwan', '台湾', 1),
('US', '美國', 'America', '美国', 1);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
