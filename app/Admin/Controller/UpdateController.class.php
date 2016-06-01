<?php
// +----------------------------------------------------------------------
// | YFCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2015-2016 http://www.rainfer.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: rainfer <81818832@qq.com>
// +----------------------------------------------------------------------
namespace Admin\Controller;
use Common\Controller\CommonController;
use OT\File;
class UpdateController extends CommonController {
	/**
	 * 初始化页面
	 */
	public function index(){
		if(IS_POST){
			$this->display();
			//再确认版本
			$version=S('ver_last');
			if(empty($version)){
				$version = checkVersion();
				S('ver_last',$version);
			}
			$ver_curr=substr(C('YFCMF_VERSION'),1);
			$ver_last=substr($version,1);
			if(version_compare($ver_curr,$ver_last)===-1){
			//需在线更新
			$backup=I('backupfile',0,'intval');
			update(C('YFCMF_VERSION'),$backup);
			}
		}else{
			$this->display();
		}
	}
}