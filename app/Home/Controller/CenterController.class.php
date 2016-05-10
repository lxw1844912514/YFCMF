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
    //编辑用户资料
	public function edit() {
		$province = M('Region')->where ( array('pid'=>1) )->select ();
		$this->assign('province',$province);
		$this->assign($this->user);
    	$this->display("User:edit");
    }
    public function runedit() {
    	if(IS_POST){
    		if ($this->users_model->field('member_list_nickname,member_list_sex,member_list_tel,user_url,signature,member_list_province,member_list_city，member_list_town')->create()) {
				if ($this->users_model->where(array('member_list_id'=>$this->userid))->save()!==false) {
					$this->user=$this->users_model->find($this->userid);
					session('user',$this->user);
					$this->success("保存成功！",U("Center/edit"));
				} else {
					$this->error("保存失败！");
				}
			} else {
				$this->error($this->users_model->getError());
			}
    	}
    }
}
