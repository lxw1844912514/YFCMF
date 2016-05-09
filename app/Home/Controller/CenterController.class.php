<?php
// +----------------------------------------------------------------------
// | YFCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2015-2016 http://www.rainfer.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: rainfer <81818832@qq.com>
// +----------------------------------------------------------------------
namespace Home\Controller;
use Home\Controller\HomebaseController;
class CenterController extends HomebaseController {
	protected function _initialize(){
		parent::_initialize();
		$this->check_login();
	}
	public function index() {
		$this->assign($this->user);
    	$this->display("User:center");
    }
}
