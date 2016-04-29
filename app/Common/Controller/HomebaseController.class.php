<?php
namespace Common\Controller;
use Common\Controller\CommonController;
class HomebaseController extends CommonController{
	protected function _initialize(){
		parent::_initialize();
		$site_options=get_site_options();
		$this->assign($site_options);
	}
}