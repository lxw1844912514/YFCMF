<?php
namespace Admin\Model;
use Think\Model\RelationModel;

class NewsModel extends RelationModel{
	protected $_link=array(
		'menu' => array(
			'mapping_type'  => self::BELONGS_TO,
			'class_name'    => 'menu',
			'foreign_key'   => 'news_columnid',
			'as_fields'  => 'menu_name',
		),
	);

}
?>