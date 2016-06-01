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

class UpgradeController extends HomebaseController {

	public function check() {
		$domain=I('domain');
		$version=I('version');
		$domain_model=M('domain');
		$rst=$domain_model->where(array('domain_name'=>$domain))->find();
		if($rst){
			$domain_model->where(array('domain_name'=>$domain))->setField('ver_curr',$version);
		}else{
			$data['domain_name']=$domain;
			$data['ver_curr']=$version;
			$domain_model->add($data);
		}
		$rst=M('update')->where(array('ver_next'=>0))->find();
		if($rst){
			echo $rst['ver_curr'];
		}else{
			echo '';			
		}
	}
	public function get_updates() {
		$version=I('version');
		$rst=M('update')->where(array('ver_curr'=>$version))->find();
		if($rst){
			$id=$rst['id'];
			$where['id']=array('EGT',$id);
			$updates=M('update')->where($where)->order('id asc')->select();
		}else{
			$updates=array();		
		}
		echo json_encode($updates);
	}
}
