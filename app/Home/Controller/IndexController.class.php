<?php
namespace Home\Controller;
use Home\Controller\HomebaseController;
class IndexController extends HomebaseController {
	Public function _initialize(){
		parent::_initialize();
	}
	public function index(){
		$this->display(':index');
	}
}