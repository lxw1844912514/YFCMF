-- phpMyAdmin SQL Dump
-- version 3.4.10.1
-- http://www.phpmyadmin.net
--
-- 主机: localhost
-- 生成日期: 2016 年 04 月 25 日 08:58
-- 服务器版本: 5.5.20
-- PHP 版本: 5.4.45

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- 数据库: `yfcmf`
--

-- --------------------------------------------------------

--
-- 表的结构 `yf_plug_files`
--

CREATE TABLE IF NOT EXISTS `yf_plug_files` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `uptime` int(10) unsigned NOT NULL DEFAULT '0',
  `filesize` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '文件大小',
  `path` varchar(255) DEFAULT '' COMMENT '路径',
  PRIMARY KEY (`id`),
  KEY `uptime` (`uptime`),
  KEY `path` (`path`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
