<?php
// +----------------------------------------------------------------------
// | YFCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2015-2016 http://www.rainfer.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: rainfer <81818832@qq.com>
// +----------------------------------------------------------------------
namespace Admin\Controller;
use Common\Controller\AuthController;
use Com\Wechat;
use Com\WechatAuth;
class WeController extends AuthController {
	/*
     * 自定义菜单列表
     */
	public function we_menu_list(){

		$nav = new \Org\Util\Leftnav;
		$we_menu=M('we_menu')->order('we_menu_order')->select();
		$arr = $nav::menu($we_menu);
		$menu_top=M('we_menu')->where(array('we_menu_leftid'=>0))->order('we_menu_order')->select();
		$this->assign('menu_top',$menu_top);
		$this->assign('we_menu',$arr);
		$this->display();
	}
	/*
     * 添加自定义菜单方法
     */
	public function we_menu_runadd(){
		if(!IS_AJAX){
			$this->error('提交方式不正确',0,0);
		}else{
			$we_menu=M('we_menu');
			$sldata=array(
				'we_menu_leftid'=>I('we_menu_leftid'),
				'we_menu_name'=>I('we_menu_name'),
				'we_menu_type'=>I('we_menu_type'),
				'we_menu_typeval'=>I('we_menu_typeval'),
				'we_menu_order'=>I('we_menu_order'),
				'we_menu_open'=>I('we_menu_open'),
			);
			$we_menu->add($sldata);
			$this->success('自定义菜单添加成功',U('we_menu_list'),1);
		}
	}

	/*
     * 自定义菜单状态修改
     */
	public function we_menu_state(){
		$id=I('x');
		$statusone=M('we_menu')->where(array('we_menu_id'=>$id))->getField('we_menu_open');//判断当前状态情况
		if($statusone==1){
			$statedata = array('we_menu_open'=>0);
			M('we_menu')->where(array('we_menu_id'=>$id))->setField($statedata);
			$this->success('状态禁止',1,1);
		}else{
			$statedata = array('we_menu_open'=>1);
			M('we_menu')->where(array('we_menu_id'=>$id))->setField($statedata);
			$this->success('状态开启',1,1);
		}

	}

	/*
     * 自定义菜单排序
     */
	public function we_menu_order(){
		if (!IS_AJAX){
			$this->error('提交方式不正确',0,0);
		}else{
			$we_menu=M('we_menu');
			foreach ($_POST as $id => $sort){
				$we_menu->where(array('we_menu_id' => $id ))->setField('we_menu_order' , $sort);
			}
			$this->success('排序更新成功',U('we_menu_list'),1);
		}
	}

	/*
     * 修改自定义菜单显示
     */
	public function we_menu_edit(){
		$we_menu_id=I('we_menu_id');
		$we_menu=M('we_menu')->where(array('we_menu_id'=>$we_menu_id))->find();
		$sl_data['we_menu_id']=$we_menu['we_menu_id'];
		$sl_data['we_menu_name']=$we_menu['we_menu_name'];
		$sl_data['we_menu_leftid']=$we_menu['we_menu_leftid'];
		$sl_data['we_menu_type']=$we_menu['we_menu_type'];
		$sl_data['we_menu_typeval']=$we_menu['we_menu_typeval'];
		$sl_data['we_menu_order']=$we_menu['we_menu_order'];
		$sl_data['status']=1;
		$this->ajaxReturn($sl_data,'json');
	}

	/*
     * 修改自定义菜单方法
     */
	public function we_menu_runedit(){
		if (!IS_AJAX){
			$this->error('提交方式不正确',0,0);
		}else{
			$sl_data=array(
				'we_menu_id'=>I('we_menu_id'),
				'we_menu_name'=>I('we_menu_name'),
				'we_menu_leftid'=>I('we_menu_leftid'),
				'we_menu_type'=>I('we_menu_type'),
				'we_menu_typeval'=>I('we_menu_typeval'),
				'we_menu_order'=>I('we_menu_order'),

			);
			M('we_menu')->save($sl_data);
			$this->success('自定义菜单修改成功',U('we_menu_list'),1);
		}
	}

	/*
     * 删除自定义菜单
     */
	public function we_menu_del(){
		$rst=M('we_menu')->where(array('we_menu_id'=>I('we_menu_id')))->delete();
		if($rst!==false){
            $this->success('自定义菜单删除成功',U('we_menu_list'),1);
        }else{
            $this->error('自定义菜单删除失败',0,0);
        }
	}

	/*
     * 生成自定义菜单
     */

	public function we_menu_make(){
		$we=M('sys')->where(array('sys_id'=>1))->find();//读取微信配置参数
		$url="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$we["wesys_appid"]."&secret=".$we["wesys_appsecret"]."";
		$ch=curl_init();//初始化
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
		curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
		$output=curl_exec($ch);
		curl_close($ch);
		$jsoninfo=json_decode($output,true);
		$access_token=$jsoninfo['access_token'];
		/*
         * 菜单处理开始
         * 只取3条leftid=0的数据
         */
		$we_menu=M('we_menu')->where(array('we_menu_leftid'=>0,'we_menu_open'=>1))->order('we_menu_order')->limit(3)->select();
		/*
         * 菜单数据重组
         * 重组结构参考微信公共平台开发文档
         * name 菜单名称
         * type 菜单类型
         * url 链接地址：针对viewleix
         */
		$data = '{"button":[';//菜单头
		foreach($we_menu as $v){
			$data.='{"name":"'.$v['we_menu_name'].'",';//菜单名称

			$count=M('we_menu')->where(array('we_menu_leftid'=>$v['we_menu_id'],'we_menu_open'=>1))->limit(5)->order('we_menu_order')->count();//判断是否有子栏目
			if($count){//二级栏目
				$data.='"sub_button":[';
				$we_twomenu=M('we_menu')->where(array('we_menu_leftid'=>$v['we_menu_id'],'we_menu_open'=>1))->order('we_menu_order')->limit(5)->select();
				$k=0;
				foreach($we_twomenu as $t){
					$k=$k+1;
					$data.='{"name":"'.$t['we_menu_name'].'",';
					$data.='"type":"view",';
					$data.='"url":"http://www.baidu.com"';
					if ($k==$count){
						$data.= '}';
					}else{
						$data.= '},';
					}
				}
				$data.= ']},';
			}else{
				$data.='"type":"view",';
				$data.='"url":"http://www.baidu.com"';
			}
		}
		$data.= '},]';
		$data.= '}';

		$url="https://api.weixin.qq.com/cgi-bin/menu/create?access_token=$access_token";
		$ch=curl_init();//初始化
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_POST,1);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,FALSE);
		curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,FALSE);
		$this->success('菜单生成成功',U('we_menu_list'),1);
		curl_exec($ch);
		curl_close($ch);
		return $access_token;
	}
}