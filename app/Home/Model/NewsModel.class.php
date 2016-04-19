<?php
namespace Home\Model;
use Think\Model\RelationModel;

class NewsModel extends RelationModel{
	protected $_link=array(
		'column' => array(
			'mapping_type'  => self::BELONGS_TO,
			'class_name'    => 'column',
			'foreign_key'   => 'news_columnid',
			'as_fields'  => 'column_name',
		),
	);

}




?>