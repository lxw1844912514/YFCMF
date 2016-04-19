<?php
namespace Admin\Model;
use Think\Model\RelationModel;

class MemberListModel extends RelationModel{
	protected $_link=array(
		'Member_group' => array(
			'mapping_type'  => self::BELONGS_TO,
			'class_name'    => 'Member_group',
			'foreign_key'   => 'member_list_groupid',
			'as_fields'  => 'member_group_name',
		),
	);

}
?>