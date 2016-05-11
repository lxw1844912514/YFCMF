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
use Think\Verify;
use Org\Util\String;
class RegisterController extends HomebaseController {
	
	function index(){
	    if(session('hid')){ //已经登录时直接跳到首页
	        redirect(__ROOT__."/");
	    }else{
	        $this->display("User:register");
	    }
	}
	//验证码
	public function verify(){
        if (session('hid')) {
            redirect(__ROOT__."/");
        }
        $verify = new Verify (array(
            'fontSize' => 20,
            'imageH' => 42,
            'imageW' => 250,
            'length' => 4,
            'useCurve' => false,
        ));
        $verify->entry('reg');
    }

	function runregister(){
		if(IS_POST){
			$member_list_username=I('member_list_username');
			$member_list_email=I('member_list_email');
			$password=I('password');
			$repassword=I('repassword');
			$verify=I('verify');
			$verify_obj =new Verify ();
			if (!$verify_obj->check($verify, 'reg')) {
				$this->error('验证码错误',0,0);
			}
			$rules = array(
            //array(验证字段,验证规则,错误提示,验证条件,附加规则,验证时间)
            array('member_list_email', 'require', '邮箱不能为空！', 1 ),
            array('password','require','密码不能为空！',1),
			array('member_list_username','require','密码不能为空！',1),
			array('password','5,20','密码长度最小5位,最大20位！',1,'length'),
            array('repassword', 'require', '重复密码不能为空！', 1 ),
            array('repassword','password','确认密码不正确',0,'confirm'),
            array('member_list_email','email','邮箱格式不正确！',1), // 验证email字段格式是否正确
			);
			$users_model=M("member_list");
			if($users_model->validate($rules)->create()===false){
				$this->error($users_model->getError(),0,0);
			}
			//用户名需过滤的字符的正则
			$stripChar = '?<*.>\'"';
			if(preg_match('/['.$stripChar.']/is', $member_list_username)==1){
				$this->error('用户名中包含'.$stripChar.'等非法字符！',0,0);
			}
			//判断是否存在
			$where['member_list_username']=$member_list_username;
			$where['member_list_email']=$member_list_email;
			$where['_logic'] = 'OR';
			$result = $users_model->where($where)->count();
			if($result){
				$this->error("用户名或者该邮箱已经存在！",0,0);
			}else{
				$member_list_salt=String::randString(10);
				$sl_data=array(
					'member_list_groupid'=>I('member_list_groupid'),
					'member_list_username'=>$member_list_username,
					'member_list_salt' => $member_list_salt,
					'member_list_pwd'=>encrypt_password($password,$member_list_salt),
					'member_list_email'=>$member_list_email,
					'member_list_open'=>1,
					'member_list_addtime'=>time(),
					'user_status'=>1,
				);
				$rst=$users_model->add($sl_data);
				if($rst!==false){
					$this->success('会员注册成功',U('Login/index'),1);
				}else{
					$this->error("会员注册失败",0,0);
				}
			}
		}
	}
}