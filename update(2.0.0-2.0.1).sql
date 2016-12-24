SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;


ALTER TABLE `yf_menu` ADD `menu_modelid` INT(3) NOT NULL DEFAULT '0' COMMENT '模型id' AFTER `menu_type`;
INSERT INTO `yfcmf`.`yf_auth_rule` (`id`, `name`, `title`, `type`, `status`, `css`, `condition`, `pid`, `level`, `sort`, `addtime`) VALUES (NULL, 'Model', '模型管理', '1', '1', 'fa fa-list', '', '0', '1', '22', '1482139134');
CREATE TABLE IF NOT EXISTS `yf_model` (
  `model_id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '模型ID',
  `model_name` char(30) NOT NULL DEFAULT '' COMMENT '模型标识',
  `model_title` char(30) NOT NULL DEFAULT '' COMMENT '模型名称',
  `model_pk` char(30) NOT NULL DEFAULT '' COMMENT '主键字段',
  `model_cid` char(30) NOT NULL DEFAULT '' COMMENT '栏目字段',
  `model_order` char(30) NOT NULL DEFAULT '' COMMENT '默认排序字段',
  `model_sort` varchar(255) DEFAULT NULL COMMENT '表单字段排序',
  `model_fields` text NOT NULL COMMENT '字段列表',
  `model_list` varchar(255) DEFAULT NULL COMMENT '列表显示字段，为空显示全部',
  `model_edit` varchar(255) DEFAULT '' COMMENT '可编辑字段，为空则除主键外均可以编辑',
  `model_indexes` varchar(255) DEFAULT NULL COMMENT '索引字段',
  `search_list` varchar(255) DEFAULT '' COMMENT '高级搜索的字段',
  `create_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  `update_time` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '更新时间',
  `model_status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '状态',
  `model_engine` varchar(25) NOT NULL DEFAULT 'MyISAM' COMMENT '数据库引擎',
  PRIMARY KEY (`model_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='文档模型表' AUTO_INCREMENT=2 ;

--
-- 转存表中的数据 `yf_model`
--

INSERT INTO `yf_model` (`model_id`, `model_name`, `model_title`, `model_pk`, `model_cid`, `model_order`, `model_sort`, `model_fields`, `model_list`, `model_edit`, `model_indexes`, `search_list`, `create_time`, `update_time`, `model_status`, `model_engine`) VALUES
(1, 'test', '测试模型', 'test_id', 'test_cid', 'test_order', 'test_order', '{"m_text":{"name":"m_text","title":"\\u6587\\u672c\\u5b57\\u6bb5","type":"text","data":"","description":"\\u6587\\u672c\\u5b57\\u6bb5\\u8bf4\\u660e","length":"","rules":"readonly","default":""},"m_map":{"name":"m_map","title":"\\u5730\\u56fe\\u5b57\\u6bb5","type":"baidu_map","data":"","description":"\\u5730\\u56fe\\u5b57\\u6bb5\\u8bf4\\u660e","length":"","rules":"","default":"22,22"},"m_imagefile":{"name":"m_imagefile","title":"\\u5355\\u56fe\\u7247\\u5b57\\u6bb5","type":"imagefile","data":"","description":"\\u5355\\u56fe\\u7247\\u5b57\\u6bb5\\u8bf4\\u660e","length":"","rules":"","default":""},"m_images":{"name":"m_images","title":"\\u591a\\u56fe\\u7247\\u5b57\\u6bb5","type":"images","data":"","description":"\\u591a\\u56fe\\u7247\\u5b57\\u6bb5\\u8bf4\\u660e","length":"","rules":"","default":""},"m_selecttext":{"name":"m_selecttext","title":"\\u9009\\u62e9\\u6587\\u672c","type":"selecttext","data":"auth_group|id|title|id","description":"\\u9009\\u62e9\\u6587\\u672c\\u8bf4\\u660e","length":"","rules":"required","default":""},"m_cur":{"name":"m_cur","title":"\\u8d27\\u5e01\\u5b57\\u6bb5","type":"currency","data":"","description":"\\u8d27\\u5e01\\u5b57\\u6bb5\\u8bf4\\u660e","length":"","rules":"unsigned","default":"22"},"m_long":{"name":"m_long","title":"\\u957f\\u6574\\u6570\\u5b57\\u6bb5","type":"large_number","data":"","description":"\\u957f\\u6574\\u6570\\u5b57\\u6bb5\\u8bf4\\u660e","length":"","rules":"","default":"0"},"m_int":{"name":"m_int","title":"\\u6574\\u6570\\u5b57\\u6bb5","type":"number","data":"","description":"\\u6574\\u6570\\u5b57\\u6bb5\\u8bf4\\u660e","length":"","rules":"required","default":"11"},"m_datatime":{"name":"m_datatime","title":"\\u65e5\\u671f\\u65f6\\u95f4\\u5b57\\u6bb5","type":"datetime","data":"","description":"\\u65e5\\u671f\\u65f6\\u95f4\\u5b57\\u6bb5","length":"","rules":"","default":""},"m_date":{"name":"m_date","title":"\\u65e5\\u671f\\u5b57\\u6bb5","type":"date","data":"","description":"\\u65e5\\u671f\\u5b57\\u6bb5\\u8bf4\\u660e","length":"","rules":"","default":""},"m_selectnumber":{"name":"m_selectnumber","title":"\\u9009\\u62e9\\u6570\\u5b57\\u5b57\\u6bb5","type":"selectnumber","data":"1:a,2:b,3:c","description":"\\u9009\\u62e9\\u6570\\u5b57\\u5b57\\u6bb5\\u8bf4\\u660e","length":"","rules":"readonly","default":""},"m_richtext":{"name":"m_richtext","title":"\\u5bcc\\u6587\\u672c\\u5b57\\u6bb5","type":"richtext","data":"","description":"\\u5bcc\\u6587\\u672c\\u5b57\\u6bb5\\u8bf4\\u660e","length":"","rules":"","default":""},"m_bigtext":{"name":"m_bigtext","title":"\\u6587\\u672c\\u57df\\u5b57\\u6bb5","type":"bigtext","data":"","description":"\\u6587\\u672c\\u57df\\u5b57\\u6bb5\\u8bf4\\u660e","length":"","rules":"","default":""},"m_switch":{"name":"m_switch","title":"\\u5f00\\u5173\\u5b57\\u6bb5","type":"switch","data":"","description":"\\u5f00\\u5173\\u5b57\\u6bb5\\u8bf4\\u660e","length":"","rules":"","default":"0"},"m_check":{"name":"m_check","title":"\\u591a\\u9009\\u6846\\u5b57\\u6bb5","type":"checkbox","data":"diyflag|diyflag_id|diyflag_name|diyflag_order","description":"\\u591a\\u9009\\u6846\\u5b57\\u6bb5\\u8bf4\\u660e","length":"","rules":"","default":""}}', 'test_id,m_selecttext,m_date,m_switch,m_imagefile', '', '', '', 1482231462, 1482402443, 1, 'MyISAM');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
