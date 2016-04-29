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
}