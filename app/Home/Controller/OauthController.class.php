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
use LT\ThinkSDK\ThinkOauth;
use Event\TypeEvent;
class OauthController extends HomebaseController {
	
	public function login($type = null){
		empty($type) && $this->error('参数错误');
		$_SESSION['login_http_referer']=$_SERVER["HTTP_REFERER"];
		$sns  = ThinkOauth::getInstance($type);
		redirect($sns->getRequestCodeURL());
	}

	public function callback($type = null, $code = null){
		(empty($type)) && $this->error('参数错误');
		if(empty($code)){
			redirect(__ROOT__."/");
		}	
		$sns  = ThinkOauth::getInstance($type);
		$extend = null;
		if($type == 'tencent'){
			$extend = array('openid' => I("get.openid"), 'openkey' => I("get.openkey"));
		}
		$token = $sns->getAccessToken($code , $extend);
		//获取当前登录用户信息
		if(is_array($token)){
			$user_info = A('Type', 'Event')->$type($token);
			if(!empty($_SESSION['oauth_bang'])){
				$this->_bang_handle($user_info, $type, $token);
			}else{
				$this->_login_handle($user_info, $type, $token);
			}
		}else{
			
			$this->success('登录失败！',$this->_get_login_redirect());
		}
	}
	
	function bang($type=""){
		if(session('hid')){
			empty($type) && $this->error('参数错误',0,0);
			$sns  = ThinkOauth::getInstance($type);
			$_SESSION['oauth_bang']=1;
			redirect($sns->getRequestCodeURL());
		}else{
			$this->error("您还没有登录！",0,0);
		}
		
		
	}
	
	private function _get_login_redirect(){
		return empty($_SESSION['login_http_referer'])?__ROOT__."/":$_SESSION['login_http_referer'];
	}
	
	//绑定第三方账号
	private function _bang_handle($user_info, $type, $token){
		$current_uid=session('hid');
		$oauth_user_model = M('OauthUser');
		$type=strtolower($type);
		$find_oauth_user = $oauth_user_model->where(array("oauth_from"=>$type,"openid"=>$token['openid']))->find();
		$need_bang=true;
		if($find_oauth_user){
			if($find_oauth_user['uid']==$current_uid){
				$this->error("您之前已经绑定过此账号！",U('Center/bang'),0);exit;
			}else{
				$this->error("该帐号已被本站其他账号绑定！",U('Center/bang'),0);exit;
			}
		}
		
		if($need_bang){
			if($current_uid){
				//第三方用户表中创建数据
				$new_oauth_user_data = array(
						'oauth_from' => $type,
						'name' => $user_info['name'],
						'head_img' => $user_info['head'],
						'create_time' =>date("Y-m-d H:i:s"),
						'uid' => $current_uid,
						'last_login_time' => date("Y-m-d H:i:s"),
						'last_login_ip' => get_client_ip(0,true),
						'login_times' => 1,
						'status' => 1,
						'access_token' => $token['access_token'],
						'expires_date' => (int)(time()+$token['expires_in']),
						'openid' => $token['openid'],
				);
				$new_oauth_user_id=$oauth_user_model->add($new_oauth_user_data);
				if($new_oauth_user_id){
					$this->success("绑定成功！",U('Center/bang'),1);
				}else{
					$users_model->where(array("member_list_id"=>$new_user_id))->delete();
					$this->error("绑定失败！",U('Center/bang'),0);
				}
			}else{
				$this->error("绑定失败！",U('Center/bang'),0);
			}
		}
	}
	
	//登陆
	private function _login_handle($user_info, $type, $token){
		$oauth_user_model = M('OauthUser');
		$type=strtolower($type);
		$find_oauth_user = $oauth_user_model->where(array("oauth_from"=>$type,"openid"=>$token['openid']))->find();
		$return = array();
		$local_username="";
		$need_register=true;
		if($find_oauth_user){
			$find_user = M('Member_list')->where(array("member_list_id"=>$find_oauth_user['uid']))->find();
			if($find_user){
				$need_register=false;
				//更新字段
				$data = array(
					'last_login_time' => time(),
					'last_login_ip' => get_client_ip(0,true),
				);
				M('Member_list')->where(array('member_list_id'=>$find_user["member_list_id"]))->save($data);
				session('hid',$find_user['member_list_id']);
				session('user',$find_user);
				redirect($this->_get_login_redirect());
			}else{
				$need_register=true;
			}
		}
		
		if($need_register){
			//本地用户中创建对应一条数据
			$new_user_data = array(
					'member_list_username' => $user_info['name'],
					'member_list_nickname' => $user_info['name'],
					'member_list_headpic' => $user_info['head'],
					'member_list_addtime' => time(),
					'member_list_groupid'=>1,
					'member_list_sex'=>3,
					'member_list_open'=>1,
					'member_list_from'=>$type,
					'last_login_time' => time(),
					'last_login_ip' => get_client_ip(0,true),
			);
			$users_model=M("member_list");
			$new_user_id = $users_model->add($new_user_data);
			$new_user_data=$users_model->find($new_user_id);
			if($new_user_id){
				//第三方用户表中创建数据
				$new_oauth_user_data = array(
					'oauth_from' => $type,
					'name' => $user_info['name'],
					'head_img' => $user_info['head'],
					'create_time' =>time(),
					'uid' => $new_user_id,
					'last_login_time' => time(),
					'last_login_ip' => get_client_ip(0,true),
					'login_times' => 1,
					'status' => 1,
					'access_token' => $token['access_token'],
					'expires_date' => (int)(time()+$token['expires_in']),
					'openid' => $token['openid'],
				);
				$new_oauth_user_id=$oauth_user_model->add($new_oauth_user_data);
				if($new_oauth_user_id){
					session('hid',$new_user_id);
					session('user',$new_user_data);
					redirect($this->_get_login_redirect());
				}else{
					$users_model->where(array("member_list_id"=>$new_user_id))->delete();
					$this->error("登陆失败",$this->_get_login_redirect());
				}
			}else{
				$this->error("登陆失败",$this->_get_login_redirect());
			}
			
		}
	}
}