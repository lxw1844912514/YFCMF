<?php
/*
 * @thinkphp3.2.2  auth认证   php5.3以上
 * @Created on 2015/08/18
 * @Author  slackck   876902658@qq.com
 * @如果需要公共控制器，就不要继承AuthController，直接继承Controller
 */
namespace Common\Controller;
use Think\Auth;
//权限认证
class AuthController extends CommonController {
	//初始化
	protected function _initialize(){

		//未登陆，不允许直接访问
		if(!$_SESSION['aid']){
			$this->error('还没有登录，正在跳转到登录页',U('Admin/Login/login'));
		}
		//已登录，不需要验证的权限
		$not_check = array('Sys/clear');//不需要检测的控制器/方法

		//当前操作的请求                 模块名/方法名
		//在不需要验证权限时
		if(in_array(CONTROLLER_NAME.'/'.ACTION_NAME, $not_check)){
			return true;
		}
		//下面代码动态判断权限
		$auth = new Auth();
		if(!$auth->check(CONTROLLER_NAME.'/'.ACTION_NAME,$_SESSION['aid']) && $_SESSION['aid']!= 1){
			$this->error('没有权限',0,0);
		}
	}
}