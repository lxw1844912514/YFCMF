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
    public function menu_list(){
        $menu=Db::name('we_menu')->order('we_menu_order')->select();
        $menu=menu_left($menu,'we_menu_id','we_menu_leftid');
        $this->assign('menu',$menu);
        if(request()->isAjax()){
            return $this->fetch('ajax_menu_list');
        }else{
            return $this->fetch();
        }
    }
    public function menu_runadd(){
        if(!request()->isAjax()){
            $this->error('提交方式不正确');
        }else{
            $we_menu=Db::name('we_menu');
            $we_menu_leftid=input('we_menu_leftid',0,'intval');
            if($we_menu_leftid==0){
                $top_menu=$we_menu->where(['we_menu_leftid'=>0,'we_menu_open'=>1])->count();
                if ($top_menu>2){
                    $this->error('顶级菜单不能超过3个',url('menu_list'));
                }
            }else{
                $child_menu=$we_menu->where(['we_menu_leftid'=>$we_menu_leftid,'we_menu_open'=>1])->count();
                if ($child_menu>4){
                    $this->error('子菜单不能超过5个',url('menu_list'));
                }
            }
            $sldata=array(
                'we_menu_leftid'=>$we_menu_leftid,
                'we_menu_name'=>input('we_menu_name'),
                'we_menu_type'=>input('we_menu_type',1),
                'we_menu_typeval'=>input('we_menu_typeval',''),
                'we_menu_order'=>input('we_menu_order',50),
                'we_menu_open'=>input('we_menu_open',0),
            );
            $rst=$we_menu->insert($sldata);
            if($rst!==false){
                $this->success('菜单添加成功',url('menu_list'));
            }else{
                $this->error('菜单添加失败',url('menu_list'));
            }
        }
    }
    public function menu_state(){
        $id=input('x');
        $we_menu=Db::name('we_menu');
        $statusone=$we_menu->where('we_menu_id',$id)->value('we_menu_open');
        if($statusone==1){
            $statedata = array('we_menu_open'=>0);
            $we_menu->where('we_menu_id',$id)->setField($statedata);
            $this->success('状态禁止');
        }else{
            $statedata = array('we_menu_open'=>1);
            $we_menu->where('we_menu_id',$id)->setField($statedata);
            $this->success('状态开启');
        }
    }
    public function menu_order(){
        if (!request()->isAjax()){
            $this->error('提交方式不正确');
        }else{
            $we_menu=Db::name('we_menu');
            foreach (input('post.') as $id => $sort){
                $we_menu->where('we_menu_id',$id)->setField('we_menu_order' , $sort);
            }
            $this->success('排序更新成功',url('menu_list'));
        }
    }
    public function menu_del(){
        $menu=Db::name('we_menu')->select();
        $ids=array();
        getMenuTree($menu, input('we_menu_id'),'we_menu_leftid','we_menu_id',$ids,true);
        $rst=Db::name('we_menu')->where('we_menu_id','in',$ids)->delete();
        if($rst!==false){
            $this->success('菜单删除成功',url('menu_list'));
        }else{
            $this->error('菜单删除失败',url('menu_list'));
        }
    }
    public function menu_edit(){
        $menus=Db::name('we_menu')->select();
        $we_menu_id=input('we_menu_id');
        $menu=Db::name('we_menu')->where('we_menu_id',$we_menu_id)->find();
        $menu['code']=1;
        return json($menu);
    }
    public function menu_runedit(){
        if(!request()->isAjax()){
            $this->error('提交方式不正确');
        }else{
            $we_menu=Db::name('we_menu');
            $sldata=array(
                'we_menu_leftid'=>input('we_menu_leftid',0,'intval'),
                'we_menu_name'=>input('we_menu_name'),
                'we_menu_type'=>input('we_menu_type',1),
                'we_menu_typeval'=>input('we_menu_typeval',''),
                'we_menu_order'=>input('we_menu_order',50),
                'we_menu_open'=>input('we_menu_open',0),
            );
            $rst=$we_menu->where('we_menu_id',input('we_menu_id'))->update($sldata);
            if($rst!==false){
                $this->success('菜单编辑成功',url('menu_list'));
            }else{
                $this->error('菜单编辑失败',url('menu_list'));
            }
        }
    }
    public function menu_make(){
        $arr=Db::name('options')->where('option_l',$this->lang)->where(array('option_name'=>'weixin_options'))->find();
        if(empty($arr) || !isset($arr['option_value']) || empty($arr['option_value'])){
            $this->error('微信配置不正确',url('menu_list'));
        }
        $we=json_decode($arr['option_value'],true);
        //组装数据
        $we_menu=Db::name('we_menu')->where(array('we_menu_leftid'=>0,'we_menu_open'=>1))->order('we_menu_id')->limit(3)->select();
        if(empty($we_menu)){
            $this->error('没有菜单需要生成',url('menu_list'));
        }
        $new_menu = array();
        $menu_count = 0;
        foreach ($we_menu as $v){
            $new_menu[$menu_count]['name'] = $v['we_menu_name'];
            $c_menus = Db::name('we_menu')->where(array('we_menu_leftid'=>$v['we_menu_id'],'we_menu_open'=>1))->limit(5)->select();
            if($c_menus){
                foreach($c_menus as $vv){
                    $c_menu = array();
                    $c_menu['name'] = $vv['we_menu_name'];
                    $c_menu['type'] = ($vv['we_menu_type']==1)?'view':(($vv['we_menu_type']==2)?'click':$vv['we_menu_type']);
                    if($c_menu['type'] == 'view'){
                        $c_menu['url'] = $vv['we_menu_typeval'];
                    }else{
                        $c_menu['key'] = $vv['we_menu_typeval'];
                    }
                    $c_menu['sub_button'] = array();
                    if($c_menu['name']){
                        $new_menu[$menu_count]['sub_button'][] = $c_menu;
                    }
                }
            }else{
                $new_menu[$menu_count]['type'] = ($v['we_menu_type']==1)?'view':(($v['we_menu_type']==2)?'click':$v['we_menu_type']);
                if($new_menu[$menu_count]['type'] == 'view'){
                    $new_menu[$menu_count]['url'] = $v['we_menu_typeval'];
                }else{
                    $new_menu[$menu_count]['key'] = $v['we_menu_typeval'];
                }
            }
            $menu_count++;
        }
        $data=json_encode(array('button'=>$new_menu),JSON_UNESCAPED_UNICODE);
        //获取access_token
        $url="https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$we['wesys_appid']."&secret=".$we['wesys_appsecret'];
        $return=go_curl($url,'GET');
        $return=json_decode($return,true);
        if(!isset($return['access_token']) || empty($return['access_token'])){
            $this->error('获取access_token失败',url('menu_list'));
        }
        $access_token=$return['access_token'];
        //发送菜单数据
        $url="https://api.weixin.qq.com/cgi-bin/menu/create?access_token=$access_token";
        $return = go_curl($url,'POST',$data);
        $return=json_decode($return,true);
        if($return['errcode'] == 0){
            $this->success('菜单已成功生成',url('menu_list'));
        }else{
            $this->error('生成失败,错误:'.$return['errcode'],url('menu_list'));
        }
    }
}