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
 * 管理群组模型
 * @package app\admin\model
 */
class AuthGroup extends Model
{
	protected $autoWriteTimestamp = true;
	protected $createTime = 'addtime';
	protected $updateTime = false;

	public function groups()
    {
		return $this->belongsToMany('Admin','__AUTH_GROUP_ACCESS__','uid','group_id');
    }
}