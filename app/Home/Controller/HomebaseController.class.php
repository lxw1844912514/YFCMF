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
	protected $yf_theme_path;
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
		$this->yf_theme_path=__ROOT__."/app/".MODULE_NAME.'/'.C('DEFAULT_V_LAYER').'/'.C('DEFAULT_THEME').'/';
		$this->user['address']=$address;
		$this->assign("yf_theme_path",$this->yf_theme_path);
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
    /**
     * 检查操作频率
     * @param int $t_check 距离最后一次操作的时长
     */
    protected function check_last_action($t_check){
    	$action=MODULE_NAME."-".CONTROLLER_NAME."-".ACTION_NAME;
    	$time=time();
    	if(!empty($_SESSION['last_action']['action']) && $action==$_SESSION['last_action']['action']){
    		$t=$time-$_SESSION['last_action']['time'];
    		if($t_check>$t){
    			$this->error("操作太频繁，请喝杯咖啡后再试!",0,0);
    		}else{
    			$_SESSION['last_action']['time']=$time;
    		}
    	}else{
    		$_SESSION['last_action']['action']=$action;
    		$_SESSION['last_action']['time']=$time;
    	}
    }
}