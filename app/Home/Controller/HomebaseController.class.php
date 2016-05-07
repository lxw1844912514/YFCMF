<?php
namespace Home\Controller;
use Common\Controller\CommonController;
class HomebaseController extends CommonController{
	protected function _initialize(){
		parent::_initialize();
		$site_options=get_site_options();
		C('DEFAULT_THEME', $site_options['site_tpl']);
		$this->assign($site_options);
		$this->theme(C('DEFAULT_THEME'));
	}
	/**
	 * 检查用户登录
	 */
	protected function check_login(){
		if(!session('uid')){
			$this->error('您还没有登录！',__ROOT__."/");
		}
	}
}