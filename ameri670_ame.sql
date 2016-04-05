-- phpMyAdmin SQL Dump
-- version 4.1.14
-- http://www.phpmyadmin.net
--
-- Host: 127.0.0.1
-- Generation Time: Apr 05, 2016 at 05:24 PM
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
-- Table structure for table `ame_goods_record`
--

CREATE TABLE IF NOT EXISTS `ame_goods_record` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` int(11) NOT NULL COMMENT '产品编号（UPC之类）',
  `brand` varchar(100) NOT NULL,
  `name_cn` varchar(250) NOT NULL COMMENT '中文名',
  `name_en` varchar(250) NOT NULL COMMENT '英文名',
  `name_other` varchar(250) NOT NULL COMMENT '其它名',
  `part_no` varchar(30) NOT NULL COMMENT '型号',
  `spec` varchar(30) NOT NULL COMMENT '规格',
  `made_in` varchar(50) NOT NULL COMMENT '产地',
  `func` varchar(200) NOT NULL COMMENT '功能',
  `use` varchar(200) NOT NULL COMMENT '用途',
  `ingredients` varchar(1000) NOT NULL COMMENT '成分',
  `made_date` int(11) NOT NULL COMMENT '出厂日期',
  `comment` varchar(500) NOT NULL COMMENT '备注',
  `img` varchar(100) NOT NULL COMMENT '图片路径（100X100）',
  `tax_unit` varchar(15) NOT NULL COMMENT '计税单位',
  `quantity` int(11) NOT NULL COMMENT '数量',
  `unit_value` float NOT NULL COMMENT '单价（RMB）',
  `tos` varchar(50) NOT NULL COMMENT '业务类型',
  `district` varchar(25) NOT NULL COMMENT '关区',
  `art_no` varchar(30) NOT NULL COMMENT '货号',
  `sequence` varchar(30) NOT NULL COMMENT '物资序号',
  `declare_unit` varchar(15) NOT NULL COMMENT '申报单位',
  `declare_num` int(11) NOT NULL COMMENT '申报数量',
  `gross_weight` float NOT NULL COMMENT '毛重（kg）',
  `net_weight` float NOT NULL COMMENT '净重（kg）',
  `rule_no` varchar(30) NOT NULL COMMENT '规则型号',
  `owner_code` varchar(30) NOT NULL COMMENT '货主编码',
  `hs_code` varchar(30) NOT NULL COMMENT '税则号',
  `tax` float NOT NULL COMMENT '税率',
  `cif_price` float NOT NULL COMMENT '完税价格（RMB）',
  `estimated_tariff` float NOT NULL COMMENT '预估关税（RMB）',
  `total_price` float NOT NULL COMMENT '商品含税价（RMB）',
  `state` enum('pending','cancel','submited','done') NOT NULL COMMENT '备案状态',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `ame_goods_record`
--

INSERT INTO `ame_goods_record` (`id`, `code`, `brand`, `name_cn`, `name_en`, `name_other`, `part_no`, `spec`, `made_in`, `func`, `use`, `ingredients`, `made_date`, `comment`, `img`, `tax_unit`, `quantity`, `unit_value`, `tos`, `district`, `art_no`, `sequence`, `declare_unit`, `declare_num`, `gross_weight`, `net_weight`, `rule_no`, `owner_code`, `hs_code`, `tax`, `cif_price`, `estimated_tariff`, `total_price`, `state`) VALUES
(1, 1, '不知名', '不知道', 'Unknow', '', '', '', '', '', '', '', 0, '', '', '', 1, 34.99, '', '', '', '', '', 0, 0, 0, '', '', '', 0, 0, 0, 0, 'pending'),
(2, 2, '超有名', '你懂的', 'Well known', '', '', '', '', '', '', '', 0, '', '', '', 1, 12.59, '', '', '', '', '', 0, 0, 0, '', '', '', 0, 0, 0, 0, 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `ame_order`
--

CREATE TABLE IF NOT EXISTS `ame_order` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ame_no` varchar(30) NOT NULL COMMENT 'AME快递单号',
  `date` int(11) NOT NULL,
  `s_id` varchar(20) NOT NULL COMMENT '寄件人会员ID',
  `s_name` varchar(50) NOT NULL,
  `s_company` varchar(50) NOT NULL,
  `s_country` varchar(50) NOT NULL,
  `s_province` varchar(50) NOT NULL,
  `s_city` varchar(50) NOT NULL,
  `s_address` varchar(500) NOT NULL,
  `s_zip` varchar(15) NOT NULL,
  `s_phone` varchar(25) NOT NULL,
  `s_email` varchar(200) NOT NULL,
  `r_name` varchar(50) NOT NULL COMMENT '收件人姓名',
  `r_company` varchar(50) NOT NULL,
  `r_country` varchar(50) NOT NULL,
  `r_province` varchar(50) NOT NULL,
  `r_city` varchar(50) NOT NULL,
  `r_address` varchar(500) NOT NULL,
  `r_zip` varchar(15) NOT NULL,
  `r_phone` varchar(25) NOT NULL,
  `r_email` varchar(200) NOT NULL,
  `r_id` varchar(25) NOT NULL COMMENT '收件人身份证',
  `state` enum('pending','cancel','done') NOT NULL COMMENT '包裹状态',
  `weight` float NOT NULL COMMENT '总重量（kg）',
  `trank_no` varchar(30) NOT NULL COMMENT '别的快递单号',
  `track_company` enum('','WS') NOT NULL COMMENT '别的快递公司',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Dumping data for table `ame_order`
--

INSERT INTO `ame_order` (`id`, `ame_no`, `date`, `s_id`, `s_name`, `s_company`, `s_country`, `s_province`, `s_city`, `s_address`, `s_zip`, `s_phone`, `s_email`, `r_name`, `r_company`, `r_country`, `r_province`, `r_city`, `r_address`, `r_zip`, `r_phone`, `r_email`, `r_id`, `state`, `weight`, `trank_no`, `track_company`) VALUES
(1, 'AME1604050001', 1459878758, '', '123', '', 'CA', 'ON', 'Markham', '123 ame street', 'A1B 2C3', '123-123-1234', '123@pbcc.ca', '321', '', 'CN', '浙江', '杭州', '没人路 321号， 321单元A棟321室', '321321', '321-3210-3210', '321@qq.com', '330205321032323210', 'pending', 0, '', '');

-- --------------------------------------------------------

--
-- Table structure for table `ame_order_goods`
--

CREATE TABLE IF NOT EXISTS `ame_order_goods` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) NOT NULL,
  `good_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=3 ;

--
-- Dumping data for table `ame_order_goods`
--

INSERT INTO `ame_order_goods` (`id`, `order_id`, `good_id`, `quantity`) VALUES
(1, 1, 1, 2),
(2, 1, 2, 3);

-- --------------------------------------------------------

--
-- Table structure for table `ame_user`
--

CREATE TABLE IF NOT EXISTS `ame_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL COMMENT '会员号',
  `name` varchar(50) NOT NULL,
  `company` varchar(100) NOT NULL,
  `country` varchar(50) NOT NULL,
  `province` varchar(50) NOT NULL,
  `city` varchar(50) NOT NULL,
  `address` varchar(500) NOT NULL,
  `zip` varchar(15) NOT NULL,
  `phone` varchar(25) NOT NULL,
  `email` varchar(150) NOT NULL,
  `id_card` varchar(40) NOT NULL COMMENT '中国身份证',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
