SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

CREATE TABLE IF NOT EXISTS `yf_route` (
  `id` int(11) NOT NULL AUTO_INCREMENT COMMENT '路由id',
  `full_url` varchar(255) DEFAULT NULL COMMENT '完整url， 如：portal/list/index?id=1',
  `url` varchar(255) DEFAULT NULL COMMENT '实际显示的url',
  `listorder` int(5) DEFAULT '0' COMMENT '排序，优先级，越小优先级越高',
  `status` tinyint(1) DEFAULT '1' COMMENT '状态，1：启用 ;0：不启用',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='url路由表' AUTO_INCREMENT=6 ;

INSERT INTO `yf_route` (`id`, `full_url`, `url`, `listorder`, `status`) VALUES
(1, 'home/list/index?id=1', 'about', 1, 1),
(2, 'home/list/index?id=10', 'contacts', 2, 1),
(3, 'home/list/index', 'list/:id\\d', 3, 1),
(4, 'home/news/index', 'news/:id\\d', 4, 1),
(5, 'home/index/index', 'index', 5, 1);

UPDATE `yf_news` SET `news_img`=REPLACE (`news_img`,'./data/','/data/');
UPDATE `yf_news` SET `news_pic_allurl`=REPLACE (`news_pic_allurl`,'./data/','/data/');
UPDATE `yf_options` SET `option_value`=REPLACE (`option_value`,'.\/data\/','\/data\/') WHERE `option_name`='site_options';
UPDATE `yf_plug_ad` SET `plug_ad_pic`=REPLACE (`plug_ad_pic`,'./data/','/data/');
UPDATE `yf_plug_files` SET `path`=REPLACE (`path`,'./data/','/data/');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
