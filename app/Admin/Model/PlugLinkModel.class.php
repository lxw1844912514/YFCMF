<?php
namespace Admin\Model;
use Think\Model\RelationModel;

class PlugLinkModel extends RelationModel{
	protected $_link=array(
		'Plug_linktype' => array(
			'mapping_type'  => self::BELONGS_TO,
			'class_name'    => 'Plug_linktype',
			'foreign_key'   => 'plug_link_typeid',
			'as_fields'  => 'plug_linktype_name',
		),
	);

}
?>