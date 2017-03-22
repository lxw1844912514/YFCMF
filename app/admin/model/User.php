<?php
// +----------------------------------------------------------------------
// | YFCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2015-2016 http://www.rainfer.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: rainfer <81818832@qq.com>
// +----------------------------------------------------------------------

namespace app\admin\model;

use think\Model;

/**
 * 用户模型
 * @package app\admin\model
 */
class User extends Model
{
	public function roles()
	{
		return $this->belongsToMany('Role','__ROLE_ACCESS__','roles_id','users_id');
	}
}
