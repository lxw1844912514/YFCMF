<?php
// +----------------------------------------------------------------------
// | YFCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2015-2016 http://www.rainfer.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: rainfer <81818832@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;
use think\Db;
use think\Cache;
class We extends Base {
	//微信设置显示
	public function wesys(){
		$arr=Db::name('options')->where('option_l',$this->lang)->where(array('option_name'=>'weixin_options'))->find();
		if(empty($arr)){
			$data['option_name']='weixin_options';
			$data['option_value']='';
			$data['autoload']=1;
			$data['option_l']=$this->lang;
			Db::name('options')->insert($data);
		}
		$sys=array(
			'wesys_name'=>'',
			'wesys_id'=>'',
			'wesys_number'=>'',
			'wesys_appid'=>'',
			'wesys_appsecret'=>'',
			'wesys_type'=>1,
		);
		$sys=empty($arr['option_value'])?$sys:array_merge($sys,json_decode($arr['option_value'],true));
		$this->assign('sys',$sys);
		return view();
	}

	//保存微信设置
	public function runwesys(){
		if (!request()->isAjax()){
			$this->error('提交方式不正确',url('wesys'));
		}else{
			$rst=Db::name('options')->where('option_l',$this->lang)->where(array('option_name'=>'weixin_options'))->setField('option_value',json_encode(input('post.options/a')));
			if($rst!==false){
				$this->success('微信设置保存成功',url('wesys'));
			}else{
				$this->error('提交参数不正确',url('wesys'));
			}
		}
	}
}