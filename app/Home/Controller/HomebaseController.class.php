<?php
// +----------------------------------------------------------------------
// | YFCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2015-2016 http://www.rainfer.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: rainfer <81818832@qq.com>
// +----------------------------------------------------------------------
namespace Home\Controller;
use Common\Controller\CommonController;
class HomebaseController extends CommonController{
	protected $user_model;
	protected $user;
	protected $userid;
	protected function _initialize(){
		parent::_initialize();
		$site_options=get_site_options();
		C('DEFAULT_THEME', $site_options['site_tpl']);
		$this->assign($site_options);
		$this->theme(C('DEFAULT_THEME'));
		$this->userid=0;
		$this->user=array();
		$this->user_model=M('member_list');
		$address='';
		if(session('hid')){
			$this->userid=session('hid');
			$this->user=$this->user_model->find(session('hid'));
			if(!empty($this->user['member_list_province'])){
				$rst=M('region')->field('name')->find($this->user['member_list_province']);
				$address.=$rst?$rst['name'].'省':'';
			}
			if(!empty($this->user['member_list_city'])){
				$rst=M('region')->field('name')->find($this->user['member_list_city']);
				$address.=$rst?$rst['name'].'市(地区)':'';
			}
			if(!empty($this->user['member_list_town'])){
				$rst=M('region')->field('name')->find($this->user['member_list_town']);
				$address.=$rst?$rst['name']:'';
			}
		}
		$this->user['address']=$address;
		$this->assign("user",$this->user);
	}
	/**
	 * 检查用户登录
	 */
	protected function check_login(){
		if(!session('hid')){
			$this->error('您还没有登录！',__ROOT__."/");
		}
	}
}