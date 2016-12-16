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

class Sys extends Base {
	//站点设置显示
	public function sys(){
		//主题
		$arr=list_file(APP_PATH.'home/view/');
		$tpls=array();
		foreach($arr as $v){
			if($v['isDir'] && strtolower($v['filename']!='public')){
				$tpls[]=$v['filename'];
			}
		}
		$this->assign('templates',$tpls);
		$arr=Db::name('options')->where('option_l',$this->lang)->where(array('option_name'=>'site_options'))->find();
		if(empty($arr)){
			$data['option_name']='site_options';
			$data['option_value']='';
			$data['autoload']=1;
			$data['option_l']=$this->lang;
			Db::name('options')->insert($data);
		}
		$sys=array(
			'map_lat'=>'',
			'map_lng'=>'',
			'site_name'=>'',
			'site_host'=>'',
			'site_tpl'=>'',
			'site_logo'=>'',
			'site_icp'=>'',
			'site_tongji'=>'',
			'site_copyright'=>'',
			'site_co_name'=>'',
			'site_address'=>'',
			'site_tel'=>'',
			'site_admin_email'=>'',
			'site_qq'=>'',
			'site_seo_title'=>'',
			'site_seo_keywords'=>'',
			'site_seo_description'=>'',
		);
		$sys=empty($arr['option_value'])?$sys:array_merge($sys,json_decode($arr['option_value'],true));
		$map_lat=empty($sys['map_lat'])?'':$sys['map_lat'];
		$map_lng=empty($sys['map_lng'])?'':$sys['map_lng'];
		if((empty($map_lat) || empty($map_lng)) && !empty($sys['site_co_name'])){
			$strUrl="http://api.map.baidu.com/place/v2/search?query=".$sys['site_co_name']."&region=全国&city_limit=false&output=json&ak=".config('baidumap_ak');//自己去申请ak
			//接受json数据  
			$jsonStr = file_get_contents($strUrl);
			//进行json字符串编码  
			$map = json_decode($jsonStr,TRUE);
			if(!empty($map['results']) && !empty($map['results'][0]['location'])){
				$map_lat=$map['results'][0]['location']['lat'];
				$map_lng=$map['results'][0]['location']['lng'];
			}
		}
		$this->assign('map_lat',$map_lat);
		$this->assign('map_lng',$map_lng);
		$this->assign('sys',$sys);
		return $this->fetch();
	}
	//保存站点设置
	public function runsys(){
		if (!request()->isAjax()){
			$this->error('提交方式不正确',url('sys'));
		}else{
			$update_check=input('update_check',0,'intval')?true:false;;
			sys_config_setbykey('update_check',$update_check);
			$checkpic=input('checkpic');
			$oldcheckpic=input('oldcheckpic');
			$options=input('post.options/a');
			$img_url='';
			if ($checkpic!=$oldcheckpic){
				$file = request()->file('file0');
				if(!empty($file)){
					if(config('storage.storage_open')){
						//七牛
						$upload = \Qiniu::instance();
						$info = $upload->upload();
						$error = $upload->getError();
						if ($info) {
							$img_url= config('storage.domain').$info[0]['key'];
						}else{
							$this->error($error,url('sys'));//否则就是上传错误，显示错误原因
						}
					}else{
						//本地
						$validate=config('upload_validate');
						$info = $file->validate($validate)->rule('uniqid')->move(ROOT_PATH . config('upload_path') . DS . date('Y-m-d'));
						if($info) {
							$img_url=config('upload_path'). '/' . date('Y-m-d') . '/' . $info->getFilename();
							//写入数据库
							$data['uptime']=time();
							$data['filesize']=$info->getSize();
							$data['path']=$img_url;
							Db::name('plug_files')->insert($data);
						}else{
							$this->error($file->getError(),url('sys'));//否则就是上传错误，显示错误原因
						}
					}
					$options['site_logo']=$img_url;
				}
			}else{
				//原有图片
				$options['site_logo']=input('oldcheckpicname');
			}
			$rst=Db::name('options')->where('option_l',$this->lang)->where(array('option_name'=>'site_options'))->setField('option_value',json_encode($options));
			if($rst!==false){
				cache("site_options", $options);
				$this->success('站点设置保存成功',url('sys'));
			}else{
				$this->error('提交参数不正确',url('sys'));
			}
		}
	}
	//url设置显示
	public function urlsys(){
		return $this->fetch();
	}
	/*
     * 路由规则设置
	 * @author rainfer <81818832@qq.com>
     */
	public function runurlsys(){
		$route_on=input('route_on',0,'intval')?true:false;
		$route_must=input('route_must',0,'intval')?true:false;;
		$complete_match=input('complete_match',0,'intval')?true:false;;
		$html_suffix=input('html_suffix','');
		sys_config_setbykey('url_route_on',$route_on);
		sys_config_setbykey('url_route_must',$route_must);
		sys_config_setbykey('url_complete_matcht',$complete_match);
		sys_config_setbykey('url_html_suffix',$html_suffix);
		cache::clear();
		$this->success('URL基本设置成功',url('urlsys'));
	}
	//发送邮件设置
	public function emailsys(){
		$arr=Db::name('options')->where('option_l',$this->lang)->where(array('option_name'=>'email_options'))->find();
		if(empty($arr)){
			$data['option_name']='email_options';
			$data['option_value']='';
			$data['autoload']=1;
			$data['option_l']=$this->lang;
			Db::name('options')->insert($data);
		}
		$sys=array(
			'email_open'=>0,
			'email_rename'=>'',
			'email_name'=>'',
			'email_smtpname'=>'',
			'smtpsecure'=>'',
			'smtp_port'=>'',
			'email_emname'=>'',
			'email_pwd'=>'',
		);
		$sys=empty($arr['option_value'])?$sys:array_merge($sys,json_decode($arr['option_value'],true));
		$this->assign('sys',$sys);
		return $this->fetch();
	}
	//保存邮箱设置
	public function runemail(){
		if (!request()->isAjax()){
			$this->error('提交方式不正确',url('emailsys'));
		}else{
			$rst=Db::name('options')->where('option_l',$this->lang)->where(array('option_name'=>'email_options'))->setField('option_value',json_encode(input('post.options/a')));
			if($rst!==false){
				cache("email_options",null);
				$this->success('邮箱设置保存成功',url('emailsys'));
			}else{
				$this->error('提交参数不正确',url('emailsys'));
			}
		}
	}
	//帐号激活设置
	public function activesys(){
		$arr=Db::name('options')->where('option_l',$this->lang)->where(array('option_name'=>'active_options'))->find();
		if(empty($arr)){
			$data['option_name']='active_options';
			$data['option_value']='';
			$data['autoload']=1;
			$data['option_l']=$this->lang;
			Db::name('options')->insert($data);
		}
		$sys=array(
			'email_active'=>0,
			'email_title'=>'',
			'email_tpl'=>'',
		);
		$sys=empty($arr['option_value'])?$sys:array_merge($sys,json_decode($arr['option_value'],true));
		$this->assign('sys',$sys);
		return $this->fetch();
	}
	//保存帐号激活设置
	public function runactive(){
		if (!request()->isAjax()){
			$this->error('提交方式不正确',url('activesys'));
		}else{
			$options=input('post.options/a');
			$options['email_tpl']=htmlspecialchars_decode($options['email_tpl']);
			$rst=Db::name('options')->where('option_l',$this->lang)->where(array('option_name'=>'active_options'))->setField('option_value',json_encode($options));
			if($rst!==false){
				cache("active_options",null);
				$this->success('帐号激活设置保存成功',url('activesys'));
			}else{
				$this->error('提交参数不正确',url('activesys'));
			}
		}
	}
	//第三方登录设置显示
	public function oauthsys(){
		$oauth_qq=sys_config_get('think_sdk_qq');
		$oauth_sina=sys_config_get('think_sdk_sina');
		$this->assign('oauth_qq',$oauth_qq);
		$this->assign('oauth_sina',$oauth_sina);
		return $this->fetch();
	}
	//保存第三方登录设置
	public function runoauthsys(){
		if (!request()->isAjax()){
			$this->error('提交方式不正确',url('oauthsys'));
		}else{
			$host=get_host();
			$data = array(
				'think_sdk_qq' => array(
					'app_key'    => input('qq_appid'),
					'app_secret' => input('qq_appkey'),
					'callback'   => $host.url('home/oauth/callback','type=qq'),
				),
				'think_sdk_sina' => array(
					'app_key'    => input('sina_appid'),
					'app_secret' => input('sina_appkey'),
					'callback'   => $host.url('home/oauth/callback','type=sina'),
				),
			);
			$rst=sys_config_setbyarr($data);
			if($rst){
				Cache::clear();
				$this->success('设置保存成功',url('oauthsys'));
			}else{
				$this->error('设置保存失败',url('oauthsys'));
			}
		}
	}
	//云存储设置
	public function storagesys(){
		$storage=config('storage');
		$this->assign('storage',$storage);
		return $this->fetch();
	}
	//保存云存储设置
	public function runstorage(){
		$storage=array(
			'storage_open'=>input('storage_open',0)?true:false,
			'accesskey'=>input('accesskey',''),
			'secretkey'=>input('secretkey',''),
			'bucket'=>input('bucket',''),
			'domain'=>input('domain','')
		);
		$rst=sys_config_setbyarr(array('storage'=>$storage));
		if($rst){
			Cache::clear();
			$this->success('设置保存成功',url('storagesys'));
		}else{
			$this->error('设置保存失败',url('storagesys'));
		}
	}
	/*
	 * 文章来源列表
	 * @author rainfer <81818832@qq.com>
	 */
	public function source_list(){
		$source=Db::name('source')->order('source_order,source_id desc')->paginate(config('paginate.list_rows'));
		$page = $source->render();
		$this->assign('source',$source);
		$this->assign('page',$page);
		return $this->fetch();
	}
	/*
	 * 添加来源操作
	 * @author rainfer <81818832@qq.com>
	 */
	public function source_runadd(){
		if (!request()->isAjax()){
			$this->error('提交方式不正确',url('source_list'));
		}else{
			$data=input('post.');
			Db::name('source')->insert($data);
			$this->success('来源添加成功',url('source_list'));
		}
	}
	/*
	 * 来源删除操作
	 * @author rainfer <81818832@qq.com>
	 */
	public function source_del(){
		$p=input('p');
		$rst=Db::name('source')->where(array('source_id'=>input('source_id')))->delete();
		if($rst!==false){
			$this->success('来源删除成功',url('source_list',array('p' => $p)));
		}else{
			$this->error('来源删除失败',url('source_list',array('p' => $p)));
		}
	}
	/*
	 * 来源修改返回值操作
	 * @author rainfer <81818832@qq.com>
	 */
	public function source_edit(){
		$source_id=input('source_id');
		$source=Db::name('source')->where(array('source_id'=>$source_id))->find();
		$sl_data['source_id']=$source['source_id'];
		$sl_data['source_name']=$source['source_name'];
		$sl_data['source_order']=$source['source_order'];
		$sl_data['code']=1;
		return json($sl_data);
	}
	/*
	 * 修改来源操作
	 * @author rainfer <81818832@qq.com>
	 */
	public function source_runedit(){
		if (!request()->isAjax()){
			$this->error('提交方式不正确',url('source_list'));
		}else{
			$sl_data=array(
				'source_id'=>input('source_id'),
				'source_name'=>input('source_name'),
				'source_order'=>input('source_order'),
			);
			$rst=Db::name('source')->update($sl_data);
			if($rst!==false){
				$this->success('来源修改成功',url('source_list'));
			}else{
				$this->error('来源修改失败',url('source_list'));
			}
		}
	}
	/*
	 * 来源排序
	 * @author rainfer <81818832@qq.com>
	 */
	public function source_order(){
		if (!request()->isAjax()){
			$this->error('提交方式不正确',url('source_list'));
		}else{
			foreach (input('post.') as $source_id => $source_order){
				Db::name('source')->where(array('source_id' => $source_id ))->setField('source_order' , $source_order);
			}
			$this->success('排序更新成功',url('source_list'));
		}
	}
	//管理员列表
	public function admin_list(){
		$val=input('val');
		$this->assign('testval',$val);
		$map=array();
		if($val){
			$map['admin_username']= array('like',"%".$val."%");
		}
		$admin_list=Db::name('admin')->where($map)->order('admin_id')->paginate(config('paginate.list_rows'),false,['query'=>get_query()]);
		$page = $admin_list->render();
		$this->assign('admin_list',$admin_list);
		$this->assign('page',$page);
		return $this->fetch();
	}
	//管理员添加
	public function admin_add(){
		$auth_group=Db::name('auth_group')->select();
		$this->assign('auth_group',$auth_group);
		return $this->fetch();
	}
	//管理员添加操作
	public function admin_runadd(){
		$check_user=Db::name('admin')->where(array('admin_username'=>input('admin_username')))->find();
		if ($check_user){
			$this->error('用户已存在，请重新输入用户名',url('admin_list'));
		}
		$admin_pwd_salt=random(10);
		$sldata=array(
			'admin_username'=>input('admin_username'),
			'admin_pwd_salt' => $admin_pwd_salt,
			'admin_pwd'=>encrypt_password(input('admin_pwd'),$admin_pwd_salt),
			'admin_email'=>input('admin_email',''),
			'admin_tel'=>input('admin_tel',''),
			'admin_open'=>input('admin_open',0),
			'admin_realname'=>input('admin_realname',''),
			'admin_ip'=>request()->ip(),
			'admin_addtime'=>time(),
			'admin_changepwd'=>time(),
		);
		$result=Db::name('admin')->insertGetId($sldata);
		if($result){
			$accdata=array(
				'uid'=>$result,
				'group_id'=>input('group_id'),
			);
			//添加管理组
			Db::name('auth_group_access')->insert($accdata);
			//添加会员
			$sldata=array(
				'member_list_username'=>input('admin_username'),
				'member_list_salt' => $admin_pwd_salt,
				'member_list_pwd'=>encrypt_password(input('admin_pwd'),$admin_pwd_salt),
				'member_list_groupid'=>1,
				'member_list_nickname'=>input('admin_realname',''),
				'member_list_email'=>input('admin_email',''),
				'member_list_tel'=>input('admin_tel',''),
				'member_list_open'=>1,
				'last_login_ip'=>request()->ip(),
				'member_list_addtime'=>time(),
				'last_login_time'=>time(),
				'user_status'=>1,
			);
			$member_id=Db::name('member_list')->insertGetId($sldata);
			if($member_id){
				Db::name('admin')->where('admin_id',$result)->setField('member_id', $member_id);
			}
			$this->success('管理员添加成功',url('admin_list'));
		}else{
			$this->error('管理员添加失败',url('admin_list'));
		}
	}
	//管理员修改
	public function admin_edit(){
		$auth_group=Db::name('auth_group')->select();
		$admin_list=Db::name('admin')->where(array('admin_id'=>input('admin_id')))->find();
		$auth_group_access=Db::name('auth_group_access')->where(array('uid'=>$admin_list['admin_id']))->value('group_id');
		$this->assign('admin_list',$admin_list);
		$this->assign('auth_group',$auth_group);
		$this->assign('auth_group_access',$auth_group_access);
		return $this->fetch();
	}
	//管理员修改操作
	public function admin_runedit(){
		$admin_pwd=input('admin_pwd');
		$group_id=input('group_id');
		$admindata['admin_id']=input('admin_id');
		if ($admin_pwd){
			$admin_pwd_salt=random(10);
			$admindata['admin_pwd_salt']=$admin_pwd_salt;
			$admindata['admin_pwd']=encrypt_password(input('admin_pwd'),$admin_pwd_salt);
			$admindata['admin_changepwd']=time();
		}
		$admindata['admin_email']=input('admin_email');
		$admindata['admin_tel']=input('admin_tel','');
		$admindata['admin_realname']=input('admin_realname');
		$admindata['admin_open']=input('admin_open',0,'intval');
		$rst=Db::name('admin')->update($admindata);
		if($group_id){
			$rst=Db::name('auth_group_access')->where(array('uid'=>input('admin_id')))->find();
			if($rst){
				//修改
				$rst=Db::name('auth_group_access')->where(array('uid'=>input('admin_id')))->setField('group_id',$group_id);
			}else{
				//增加
				$data['uid']=input('admin_id');
				$data['group_id']=$group_id;
				$rst=Db::name('auth_group_access')->insert($data);
			}
		}
		if($rst!==false){
			$this->success('管理员修改成功',url('admin_list'));
		}else{
			$this->error('管理员修改失败',url('admin_list'));
		}
	}
	//管理员删除
	public function admin_del(){
		$admin_id=input('admin_id');
		if (empty($admin_id)){
			$this->error('用户ID不存在',url('admin_list'));
		}
		//对应会员ID
		$member_id=Db::name('admin')->where(array('admin_id'=>input('admin_id')))->value('member_id');
		Db::name('admin')->where(array('admin_id'=>input('admin_id')))->delete();
		//删除对应会员
		if($member_id){
			Db::name('member_list')->where('member_list_id',$member_id)->delete();
		}
		$rst=Db::name('auth_group_access')->where(array('uid'=>input('admin_id')))->delete();
		if($rst!==false){
			$this->success('管理员删除成功',url('admin_list'));
		}else{
			$this->error('管理员删除失败',url('admin_list'));
		}
	}
	//管理员开启、禁止
	public function admin_state(){
		$id=input('x');
		if (empty($id)){
			$this->error('用户ID不存在',url('admin_list'));
		}
		$status=Db::name('admin')->where(array('admin_id'=>$id))->value('admin_open');//判断当前状态情况
		if($status==1){
			$statedata = array('admin_open'=>0);
			Db::name('admin')->where(array('admin_id'=>$id))->setField($statedata);
			$this->success('状态禁止');
		}else{
			$statedata = array('admin_open'=>1);
			Db::name('admin')->where(array('admin_id'=>$id))->setField($statedata);
			$this->success('状态开启');
		}
	}
	//用户组管理
	public function admin_group_list(){
		$auth_group=Db::name('auth_group')->select();
		$this->assign('auth_group',$auth_group);
		return $this->fetch();
	}
	//用户组增加
	public function admin_group_add(){
		return $this->fetch();
	}
	//用户组增加操作
	public function admin_group_runadd(){
		if (!request()->isAjax()){
			$this->error('提交方式不正确',url('admin_group_list'));
		}else{
			$sldata=array(
				'title'=>input('title'),
				'status'=>input('status',0),
				'addtime'=>time(),
			);
			$rst=Db::name('auth_group')->insert($sldata);
			if($rst!==false){
				$this->success('用户组添加成功',url('admin_group_list'));
			}else{
				$this->error('用户组添加失败',url('admin_group_list'));
			}
		}
	}
	//用户组删除操作
	public function admin_group_del(){
		$rst=Db::name('auth_group')->where(array('id'=>input('id')))->delete();
		if($rst!==false){
			$this->success('用户组删除成功',url('admin_group_list'));
		}else{
			$this->error('用户组删除失败',url('admin_group_list'));
		}
	}
	//用户组编辑
	public function admin_group_edit(){
		$group=Db::name('auth_group')->where(array('id'=>input('id')))->find();
		$this->assign('group',$group);
		return $this->fetch();
	}
	//用户组编辑操作
	public function admin_group_runedit(){
		if (!request()->isAjax()){
			$this->error('提交方式不正确',url('admin_group_list'));
		}else{
			$sldata=array(
				'id'=>input('id'),
				'title'=>input('title'),
				'status'=>input('status'),
			);
			Db::name('auth_group')->update($sldata);
			$this->success('用户组修改成功',url('admin_group_list'));
		}
	}
	//用户组开启禁用
	public function admin_group_state(){
		$id=input('x');
		$status=Db::name('auth_group')->where(array('id'=>$id))->value('status');//判断当前状态情况
		if($status==1){
			$statedata = array('status'=>0);
			Db::name('auth_group')->where(array('id'=>$id))->setField($statedata);
			$this->success('状态禁止');
		}else{
			$statedata = array('status'=>1);
			Db::name('auth_group')->where(array('id'=>$id))->setField($statedata);
			$this->success('状态开启');
		}
	}
	//四重权限配置
	public function admin_group_access(){
		$admin_group=Db::name('auth_group')->where(array('id'=>input('id')))->find();
		$data = Db::name('auth_rule')->field('id,name,title')->where('pid=0')->select();
		foreach ($data as $k=>$v){
			$data[$k]['sub'] = Db::name('auth_rule')->field('id,name,title')->where('pid='.$v['id'])->select();
			foreach ($data[$k]['sub'] as $kk=>$vv){
				$data[$k]['sub'][$kk]['sub'] = Db::name('auth_rule')->field('id,name,title')->where('pid='.$vv['id'])->select();
				foreach ($data[$k]['sub'][$kk]['sub'] as $kkk=>$vvv){
					$data[$k]['sub'][$kk]['sub'][$kkk]['sub'] = Db::name('auth_rule')->field('id,name,title')->where('pid='.$vvv['id'])->select();
				}
			}
		}
		$this->assign('admin_group',$admin_group);
		$this->assign('datab',$data);
		return $this->fetch();
	}
	//权限配置设置
	public function admin_group_runaccess(){
		$new_rules = input('new_rules/a');
		$imp_rules = implode(',', $new_rules).',';
		$sldata=array(
			'id'=>input('id'),
			'rules'=>$imp_rules,
		);
		if(Db::name('auth_group')->update($sldata)!==false){
			Cache::clear();
			$this->success('权限配置成功',url('admin_group_list'));
		}else{
			$this->error('权限配置失败',url('admin_group_list'));
		}
	}
	//权限规则列表
	public function admin_rule_list(){
		$pid=input('pid',0);
		$level=input('level',0);
		$id_str=input('id','pid');
		$admin_rule=Db::name('auth_rule')->where('pid',$pid)->order('sort')->select();
		$arr = \Leftnav::rule($admin_rule,'─',$pid,$level,$level*20);
		$this->assign('admin_rule',$arr);
		$this->assign('pid',$id_str);
		if(request()->isAjax()){
			return $this->fetch('ajax_admin_rule_list');
		}else{
			return $this->fetch();
		}
	}
	//权限规则添加
	public function admin_rule_runadd(){
		if(!request()->isAjax()){
			$this->error('提交方式不正确',url('admin_rule_list'));
		}else{
			$pid=Db::name('auth_rule')->where(array('id'=>input('pid')))->field('level')->find();
			$level=$pid['level']+1;
			//检测name是否有效
			if($level==1){
				$name=input('name');
				if (!has_controller(APP_PATH . 'admin'. DS .'controller',$name)) {
					$this->error('不存在 '.$name.' 的控制器',url('admin_rule_list'));
				}
			}elseif($level==2){
				//不检测
			}else{
				//是否存在控制器/方法
				$arr=explode('/',input('name'));
				if(count($arr)==2){
					$rst=has_action(APP_PATH . 'admin'. DS .'controller',$arr[0],$arr[1]);
					if($rst==0){
						$this->error('不存在 '.$arr[0].' 的控制器',url('admin_rule_list'));
					}elseif($rst==1){
						$this->error('控制器'.$arr[0].'不存在方法'.$arr[1],url('admin_rule_list'));
					}
				}else{
					$this->error('提交名称不规范',url('admin_rule_list'));
				}
			}
			$sldata=array(
				'name'=>input('name'),
				'title'=>input('title'),
				'status'=>input('status',0,'intval'),
				'sort'=>input('sort',50,'intval'),
				'addtime'=>time(),
				'pid'=>input('pid'),
				'css'=>input('css',''),
				'level'=>$level,
			);
			Db::name('auth_rule')->insert($sldata);
			Cache::clear();
			$this->success('权限添加成功',url('admin_rule_list'),1);
		}
	}
	//权限规则开启禁止
	public function admin_rule_state(){
		$id=input('x');
		$statusone=Db::name('auth_rule')->where(array('id'=>$id))->value('status');//判断当前状态情况
		if($statusone==1){
			$statedata = array('status'=>0);
			Db::name('auth_rule')->where(array('id'=>$id))->setField($statedata);
			Cache::clear();
			$this->success('状态禁止');
		}else{
			$statedata = array('status'=>1);
			Db::name('auth_rule')->where(array('id'=>$id))->setField($statedata);
			Cache::clear();
			$this->success('状态开启');
		}
	}
	//权限规则排序
	public function admin_rule_order(){
		if (!request()->isAjax()){
			$this->error('提交方式不正确',url('admin_rule_list'));
		}else{
			foreach ($_POST as $id => $sort){
				Db::name('auth_rule')->where(array('id' => $id ))->setField('sort' , $sort);
			}
			Cache::clear();
			$this->success('排序更新成功',url('admin_rule_list'));
		}
	}
	//权限规则编辑
	public function admin_rule_edit(){
		//全部规则
		$admin_rule_all=Db::name('auth_rule')->order('sort')->select();
		$arr = \Leftnav::rule($admin_rule_all);
		$this->assign('admin_rule',$arr);
		//待编辑规则
		$admin_rule=Db::name('auth_rule')->where(array('id'=>input('id')))->find();
		$this->assign('rule',$admin_rule);
		return $this->fetch();
	}
	//权限规则复制
	public function admin_rule_copy(){
		//全部规则
		$admin_rule_all=Db::name('auth_rule')->order('sort')->select();
		$arr = \Leftnav::rule($admin_rule_all);
		$this->assign('admin_rule',$arr);
		//待编辑规则
		$admin_rule=Db::name('auth_rule')->where(array('id'=>input('id')))->find();
		$this->assign('rule',$admin_rule);
		return $this->fetch();
	}
	//权限规则编辑操作
	public function admin_rule_runedit(){
		if(!request()->isAjax()){
			$this->error('提交方式不正确',url('admin_rule_list'));
		}else{
			$pid=Db::name('auth_rule')->where(array('id'=>input('pid')))->field('level')->find();
			$level=$pid['level']+1;
			$sldata=array(
				'id'=>input('id',1,'intval'),
				'name'=>input('name'),
				'title'=>input('title'),
				'status'=>input('status',0),
				'pid'=>input('pid',0,'intval'),
				'css'=>input('css'),
				'sort'=>input('sort'),
				'level'=>$level,
			);
			$rst=Db::name('auth_rule')->update($sldata);
			if($rst!==false){
				Cache::clear();
				$this->success('权限修改成功',url('admin_rule_list'));
			}else{
				$this->error('权限修改失败',url('admin_rule_list'));
			}
		}
	}
	//权限规则删除
	public function admin_rule_del(){
		//TODO 自动删除子权限
		$rst=Db::name('auth_rule')->where(array('id'=>input('id')))->delete();
		if($rst!==false){
			Cache::clear();
			$this->success('权限删除成功',url('admin_rule_list'));
		}else{
			$this->error('权限删除失败',url('admin_rule_list'));
		}
	}
	public function security_list()
	{
		$security_dir=ROOT_PATH.'data/security/';
		if (!file_exists($security_dir)) {
			@mkdir($security_dir);
		}
		$finger_files = list_file($security_dir, '*.finger');
		$this->assign('finger_files',$finger_files);
		return $this->fetch();
	}
	public function security_generate()
	{
		$security_dir=ROOT_PATH.'data/security/';
		if (!file_exists($security_dir)) {
			@mkdir($security_dir);
		}
		$filename = $security_dir . 'file_finger_' . date('YmdHi') . '_' . random(10) . '.finger';
		$f = fopen($filename, 'w');
		fwrite($f, "GENE: RCF V" . THINK_VERSION . "\n");
		fwrite($f, "TIME: " . date('Y-m-d H:i:s') . "\n");
		fwrite($f, "ROOT: \n");
		$files_md5 = array();
		foreach (array(
					//检测目录
					 'vendor',
					 'app',
					 'extend',
					 'public',
					 'thinkphp'
				 ) as $dir) {
			foreach ($this->security_filefingergenerate('./' . $dir . '/', $dir . '/') as $file_md5) {
				$files_md5 [] = $file_md5;
				fwrite($f, $file_md5 [1] . '|' . $file_md5 [0] . "\n");
			}
		}
		fclose($f);
		$this->success('成功生成安全文件',url('security_list'));
	}
	public function security_delete()
	{
		$security_dir=ROOT_PATH.'data/security/';
		if (!file_exists($security_dir)) {
			$this->error('文件不存在',url('security_list'));
		}
		$file=input('file');
		foreach (list_file($security_dir, '*.finger') as $f) {
			if (md5($f ['filename']) == $file) {
				@unlink($f ['pathname']);
			}
		}
		$this->success('成功删除',url('security_list'));
	}
	public function security_check()
	{
		$security_dir=ROOT_PATH.'data/security/';
		if (!file_exists($security_dir)) {
			$this->error('文件不存在',url('security_list'));
		}
		$md5_file = null;
		$file=input('file');
		foreach (list_file($security_dir, '*.finger') as $f) {
			if (md5($f ['filename']) == $file) {
				$md5_file = $f ['pathname'];
				break;
			}
		}
		if (null != $md5_file) {
			if (!file_exists($md5_file) || !is_file($md5_file)) {
				$this->error('文件不存在',url('security_list'));
			}
			$lines = explode("\n", file_get_contents($md5_file));
			if (count($lines) < 3) {
				$this->error('安全文件错误',url('security_list'));
			}
			if (!preg_match('/^GENE: RCF V.*?$/', $lines [0]) || !preg_match('/^TIME: \\d+\\-\\d+\\-\\d+ \\d+:\\d+:\\d+$/', $lines [1]) || !preg_match('/^ROOT: ([\\/\\.]*)/', $lines [2])) {
				$this->error('安全文件错误',url('security_list'));
			}
			$finger_file_root = trim(substr($lines [2], 5));
			$basedir = str_replace('\\', '/', rtrim(realpath($finger_file_root), '\\/')) . '/';
			unset ($lines [0], $lines [1], $lines [2]);
			$error_msgs = array();
			$file_should_exists = array();
			foreach ($lines as $line) {
				$line = trim($line);
				if ($line) {
					$l = explode('|', $line);
					if (count($l) == 2) {
						$file = trim($l [1]);
						$md5 = trim($l [0]);
						$file_should_exists [$file] = $md5;
						if (file_exists($filename = $basedir . $file)) {
							if ($md5 != md5_file($filename)) {
								$error_msgs [] = '文件被篡改 : ' . $file;
							}
						} else {
							$error_msgs [] = '缺少文件 : ' . $file;
						}
					} else {
						$error_msgs [] = '错误行 : ' . $line;
					}
				}
			}
			$this->assign('error_msgs',$error_msgs);
			return $this->fetch();
		}else{
			$this->error('文件不存在',url('security_list'));
		}
	}
	private function security_filefingergenerate($dir = '', $prefix = '')
	{
		static $allow_file_exts = array(
			'php' => true,
			'js' => true,
			'html' => true,
			'htm' => true
		);
		$file_arrs = array();
		foreach (list_file($dir) as $file) {
			if ($file ['isDir']) {
				$file_arrs = array_merge($file_arrs, $this->security_filefingergenerate($file ['pathname'] . '/', $prefix . $file ['filename'] . '/'));
			} else if ($file ['isFile']) {
				if (isset ($allow_file_exts [$file ['ext']])) {
					$file_saved = $prefix . str_replace('\\', '/', $file ['filename']);
					$file_arrs [] = array(
						$file_saved,
						md5_file($file ['pathname'])
					);
				}
			}
		}
		return $file_arrs;
	}
	//数据库备份
	public function database($type = null){
		if(empty($type)){
			$type='export';
		}
		$title='';
		$list=array();
		switch ($type) {
			/* 数据还原 */
			case 'import':
				//列出备份文件列表
				$path=config('db_path');
				if (!is_dir($path)) {
					mkdir($path, 0755, true);
				}
				$path = realpath($path);
				$flag = \FilesystemIterator::KEY_AS_FILENAME;
				$glob = new \FilesystemIterator($path,  $flag);

				$list = array();
				foreach ($glob as $name => $file) {
					if(preg_match('/^\d{8,8}-\d{6,6}-\d+\.sql(?:\.gz)?$/', $name)){
						$name = sscanf($name, '%4s%2s%2s-%2s%2s%2s-%d');

						$date = "{$name[0]}-{$name[1]}-{$name[2]}";
						$time = "{$name[3]}:{$name[4]}:{$name[5]}";
						$part = $name[6];

						if(isset($list["{$date} {$time}"])){
							$info = $list["{$date} {$time}"];
							$info['part'] = max($info['part'], $part);
							$info['size'] = $info['size'] + $file->getSize();
						} else {
							$info['part'] = $part;
							$info['size'] = $file->getSize();
						}
						$extension        = strtoupper(pathinfo($file->getFilename(), PATHINFO_EXTENSION));
						$info['compress'] = ($extension === 'SQL') ? '-' : $extension;
						$info['time']     = strtotime("{$date} {$time}");
						$list["{$date} {$time}"] = $info;
					}
				}
				$title = '数据还原';
				break;

			/* 数据备份 */
			case 'export':
				$list  = Db::query('SHOW TABLE STATUS FROM '.config('database.database'));
				$list  = array_map('array_change_key_case', $list);
				//过滤非本项目前缀的表
				foreach($list as $k=>$v){
					if(stripos($v['name'],strtolower(config('database.prefix')))!==0){
						unset($list[$k]);
					}
				}
				$title = '数据备份';
				break;

			default:
				$this->error('参数错误！');
		}

		//渲染模板
		$this->assign('meta_title', $title);
		$this->assign('data_list', $list);
		return $this->fetch($type);
	}
	//数据库还原
	public function import(){
		$path=config('db_path');
		if (!is_dir($path)) {
			mkdir($path, 0755, true);
		}
		$path = realpath($path);
		$flag = \FilesystemIterator::KEY_AS_FILENAME;
		$glob = new \FilesystemIterator($path,$flag);

		$list = array();
		foreach ($glob as $name => $file) {
			if(preg_match('/^\d{8,8}-\d{6,6}-\d+\.sql(?:\.gz)?$/', $name)){
				$name = sscanf($name, '%4s%2s%2s-%2s%2s%2s-%d');

				$date = "{$name[0]}-{$name[1]}-{$name[2]}";
				$time = "{$name[3]}:{$name[4]}:{$name[5]}";
				$part = $name[6];

				if(isset($list["{$date} {$time}"])){
					$info = $list["{$date} {$time}"];
					$info['part'] = max($info['part'], $part);
					$info['size'] = $info['size'] + $file->getSize();
				} else {
					$info['part'] = $part;
					$info['size'] = $file->getSize();
				}
				$extension        = strtoupper(pathinfo($file->getFilename(), PATHINFO_EXTENSION));
				$info['compress'] = ($extension === 'SQL') ? '-' : $extension;
				$info['time']     = strtotime("{$date} {$time}");

				$list["{$date} {$time}"] = $info;
			}
		}
		//渲染模板
		$this->assign('data_list', $list);
		return $this->fetch();
	}
	/**
	 * 优化表
	 * @param  String $tables 表名
	 * @author rainfer <81818832@qq.com>
	 */
	public function optimize($tables = null){
		if($tables) {
			if(is_array($tables)){
				$tables = implode('`,`', $tables);
				$list = Db::query("OPTIMIZE TABLE `{$tables}`");
				if($list){
					$this->success("数据表优化完成！");
				} else {
					$this->error("数据表优化出错请重试！");
				}
			} else {
				$list = Db::query("OPTIMIZE TABLE `{$tables}`");
				if($list){
					$this->success("数据表'{$tables}'优化完成！");
				} else {
					$this->error("数据表'{$tables}'优化出错请重试！");
				}
			}
		} else {
			$this->error("请指定要优化的表！");
		}
	}
	/**
	 * 修复表
	 * @param  String $tables 表名
	 * @author rainfer <81818832@qq.com>
	 */
	public function repair($tables = null){
		if($tables) {
			if(is_array($tables)){
				$tables = implode('`,`', $tables);
				$list = Db::query("REPAIR TABLE `{$tables}`");
				if($list){
					$this->success("数据表修复完成！");
				} else {
					$this->error("数据表修复出错请重试！");
				}
			} else {
				$list = Db::query("REPAIR TABLE `{$tables}`");
				if($list){
					$this->success("数据表'{$tables}'修复完成！");
				} else {
					$this->error("数据表'{$tables}'修复出错请重试！");
				}
			}
		} else {
			$this->error("请指定要修复的表！");
		}
	}
	/**
	 * 备份单表
	 * @param  String $table 不含前缀表名
	 * @author rainfer <81818832@qq.com>
	 */
	public function exportsql($table = null){
		if($table){
			if(stripos($table,config('database.prefix'))==0){
				//含前缀的表,去除表前缀
				$table=str_replace(config('database.prefix'),"",$table);
			}
			if (!db_is_valid_table_name($table)) {
				$this->error("不存在表" . ' ' . $table);
			}
			force_download_content(date('Ymd') . '_' . config('database.prefix') . $table . '.sql', db_get_insert_sqls($table));
		}else{
			$this->error('未指定需备份的表');
		}
	}
	/**
	 * 删除备份文件
	 * @param  Integer $time 备份时间
	 * @author rainfer <81818832@qq.com>
	 */
	public function del($time = 0){
		if($time){
			$name  = date('Ymd-His', $time) . '-*.sql*';
			$path  = realpath(config('db_path')) . DS . $name;
			array_map("unlink", glob($path));
			if(count(glob($path))){
				$this->error('备份文件删除失败，请检查权限！',url('import'));
			} else {
				$this->success('备份文件删除成功！',url('import'));
			}
		} else {
			$this->error('参数错误！',url('import'));
		}
	}
	public function restore($time = 0, $part = null, $start = null){
		//读取备份配置
		$config = array(
			'path'     => realpath(config('db_path')) . DS,
			'part'     => config('db_part'),
			'compress' => config('db_compress'),
			'level'    => config('db_level'),
		);
		if(is_numeric($time) && is_null($part) && is_null($start)){ //初始化
			//获取备份文件信息
			$name  = date('Ymd-His', $time) . '-*.sql*';
			$path  = realpath(config('db_path')) . DS . $name;
			$files = glob($path);
			$list  = array();
			foreach($files as $name){
				$basename = basename($name);
				$match    = sscanf($basename, '%4s%2s%2s-%2s%2s%2s-%d');
				$gz       = preg_match('/^\d{8,8}-\d{6,6}-\d+\.sql.gz$/', $basename);
				$list[$match[6]] = array($match[6], $name, $gz);
			}
			ksort($list);
			//检测文件正确性
			$last = end($list);
			if(count($list) === $last[0]){
				session('backup_list', $list); //缓存备份列表
				$this->restore(0,1,0);
			} else {
				$this->error('备份文件可能已经损坏，请检查！');
			}
		} elseif(is_numeric($part) && is_numeric($start)) {
			$list  = session('backup_list');
			$db = new \Database($list[$part],$config);
			$start = $db->import($start);
			if(false === $start){
				$this->error('还原数据出错！');
			} elseif(0 === $start) { //下一卷
				if(isset($list[++$part])){
					//$data = array('part' => $part, 'start' => 0);
					$this->restore(0,$part,0);
				} else {
					session('backup_list', null);
					$this->success('还原完成！',url('Sys/import'));
				}
			} else {
				$data = array('part' => $part, 'start' => $start[0]);
				if($start[1]){
					$this->restore(0,$part, $start[0]);
				} else {
					$data['gz'] = 1;
					$this->restore(0,$part, $start[0]);
				}
			}
		} else {
			$this->error('参数错误！');
		}
	}
	public function export($tables = null, $id = null, $start = null){
		if(request()->isPost() && !empty($tables) && is_array($tables)){ //初始化
			//读取备份配置
			$config = array(
				'path'     => realpath(config('db_path')) . DS,
				'part'     => config('db_part'),
				'compress' => config('db_compress'),
				'level'    => config('db_level'),
			);
			//检查是否有正在执行的任务
			$lock = "{$config['path']}backup.lock";
			if(is_file($lock)){
				$this->error('检测到有一个备份任务正在执行，请稍后再试！');
			} else {
				//创建锁文件
				file_put_contents($lock, time());
			}
			//检查备份目录是否可写
			is_writeable($config['path']) || $this->error('备份目录不存在或不可写，请检查后重试！');
			session('backup_config', $config);
			//生成备份文件信息
			$file = array(
				'name' => date('Ymd-His', time()),
				'part' => 1,
			);
			session('backup_file', $file);
			//缓存要备份的表
			session('backup_tables', $tables);
			//创建备份文件
			$Database = new \Database($file, $config);
			if(false !== $Database->create()){
				$tab = array('id' => 0, 'start' => 0);
				return json(array('code'=>1,'tab' => $tab,'tables' => $tables,'msg'=>'初始化成功！'));
			} else {
				$this->error('初始化失败，备份文件创建失败！');
			}
		} elseif (request()->isGet() && is_numeric($id) && is_numeric($start)) { //备份数据
			$tables = session('backup_tables');
			//备份指定表
			$Database = new \Database(session('backup_file'), session('backup_config'));
			$start  = $Database->backup($tables[$id], $start);
			if(false === $start){ //出错
				$this->error('备份出错！');
			} elseif (0 === $start) { //下一表
				if(isset($tables[++$id])){
					$tab = array('id' => $id, 'start' => 0);
					return json(array('code'=>1,'tab' => $tab,'msg'=>'备份完成！'));
				} else { //备份完成，清空缓存
					unlink(session('backup_config.path') . 'backup.lock');
					session('backup_tables', null);
					session('backup_file', null);
					session('backup_config', null);
					return json(array('code'=>1,'msg'=>'备份完成！'));
				}
			} else {
				$tab  = array('id' => $id, 'start' => $start[0]);
				$rate = floor(100 * ($start[0] / $start[1]));
				return json(array('code'=>1,'tab' => $tab,'msg'=>"正在备份...({$rate}%)"));
			}
		} else { //出错
			$this->error('参数错误！');
		}
	}
	//Excel导入
	public function excel_import(){
		return $this->fetch();
	}
	//Excel导出
	public function excel_export(){
		$list  = Db::query('SHOW TABLE STATUS FROM '.config('database.database'));
		$list  = array_map('array_change_key_case', $list);
		//过滤非本项目前缀的表
		foreach($list as $k=>$v){
			if(stripos($v['name'],strtolower(config('database.prefix')))!==0){
				unset($list[$k]);
			}
		}
		$this->assign('data_list', $list);
		return $this->fetch();
	}
	/*
	 * 表格导入
	 * @author rainfer <81818832@qq.com>
	 */
	public function excel_runimport(){
		if (! empty ( $_FILES ['file_stu'] ['name'] )){
			$tmp_file = $_FILES ['file_stu'] ['tmp_name'];
			$file_types = explode ( ".", $_FILES ['file_stu'] ['name'] );
			$file_type = $file_types [count ( $file_types ) - 1];
			/*判别是不是.xls文件，判别是不是excel文件*/
			if (strtolower ( $file_type ) != "xls"){
				$this->error ( '不是Excel文件，重新上传',url('excel_import'));
			}
			/*设置上传路径*/
			$savePath =ROOT_PATH. 'public/excel/';
			/*以时间来命名上传的文件*/
			$str = time ();
			$file_name = $str . "." . $file_type;
			if (! copy ( $tmp_file, $savePath . $file_name )){
				$this->error ('上传失败',url('excel_import'));
			}
			$res = read ( $savePath . $file_name );
			if (!$res){
				$this->error ('数据处理失败',url('excel_import'));
			}
			$titles=array();
			foreach ( $res as $k => $v ){
				if ($k != 1){
					$data=array();
					foreach($titles as $ColumnIndex=>$title){
						//排除主键
						if($title!='n_id'){
							$data[$title]=$v[$ColumnIndex];
						}
					}
					$result = Db::name ('news')->insert($data);
					if (!$result){
						$this->error ('导入数据库失败',url('excel_import'));
					}
				}else{
					$titles=$v;
				}
			}
			$this->success ('导入数据库成功',url('excel_import'));
		}
	}
	/*
	 * 数据导出功能
	 * @author rainfer <81818832@qq.com>
	 */
	public function excel_runexport($table){
		export2excel($table);
	}
	//清除缓存
	public function clear(){
		Cache::clear();
		$this->success ('清理缓存成功');
	}
	//日常维护
	public function maintain()
	{
		$action=input('action');
		switch ($action) {
			case 'download_log' :
			case 'view_log':
				$logs = array();
				foreach (list_file(LOG_PATH) as $f) {
					if ($f ['isDir']) {
						foreach (list_file($f ['pathname'] . '/', '*.log') as $ff) {
							if ($ff ['isFile']) {
								$spliter = '==========================';
								$logs [] = $spliter . '  ' . $f ['filename'] . '/' . $ff ['filename'] . '  ' . $spliter . "\n\n" . file_get_contents($ff ['pathname']);
							}
						}
					}
				}
				if ('download_log' == $action) {
					force_download_content('log_' . date('Ymd_His') . '.log', join("\n\n\n\n", $logs));
				} else {
					echo '<pre>' . htmlspecialchars(join("\n\n\n\n", $logs)) . '</pre>';
				}
				break;
			case 'clear_log' :
				remove_dir(LOG_PATH);
				$this->success ('清除日志成功',url('Index/index'));
				break;
			case 'debug_on' :
				$data = array('app_debug'=>true);
				$res=sys_config_setbyarr($data);
				if($res === false){
					$this->error('打开调试失败',url('Index/index'));
				}else{
					Cache::clear();
					$this->success('已打开调试',url('Index/index'));
				}
				break;
			case 'debug_off' :
				$data = array('app_debug'=>false);
				$res=sys_config_setbyarr($data);
				if($res === false){
					$this->error('关闭调试失败',url('Index/index'));
				}else{
					Cache::clear();
					$this->success('已关闭调试',url('Index/index'));
				}
				break;
			case 'trace_on' :
				$data = array('app_trace'=>true);
				$res=sys_config_setbyarr($data);
				if($res === false){
					$this->error('打开Trace失败',url('Index/index'));
				}else{
					Cache::clear();
					$this->success('已打开Trace',url('Index/index'));
				}
				break;
			case 'trace_off' :
				$data = array('app_trace'=>false);
				$res=sys_config_setbyarr($data);
				if($res === false){
					$this->error('关闭Trace失败',url('Index/index'));
				}else{
					Cache::clear();
					$this->success('已关闭Trace',url('Index/index'));
				}
				break;
		}
	}
	//管理员信息
	public function profile(){
		$admin=array();
		if(session('aid')){
			$admin=Db::name('admin')->alias("a")->join(config('database.prefix').'auth_group_access b','a.admin_id =b.uid')->join(config('database.prefix').'auth_group c','b.group_id = c.id')->where(array('a.admin_id'=>session('aid')))->find();
			$news_count=Db::name('News')->where(array('news_auto'=>session('member_id')))->count();
			$admin['news_count']=$news_count;
		}
		$this->assign('admin', $admin);
		return $this->fetch();
	}
	//头像
	public function avatar(){
		$imgurl=input('imgurl');
		//去'/'
		$imgurl=str_replace('/','',$imgurl);
		$url='/data/upload/avatar/'.$imgurl;
		$state=false;
		if(config('storage.storage_open')){
			//七牛
			$upload = \Qiniu::instance();
			$info = $upload->uploadOne('.'.$url,"image/");
			if ($info) {
				$state=true;
				$imgurl= config('storage.domain').$info['key'];
				@unlink('.'.$url);
			}
		}
		if($state !=true){
			//本地
			//写入数据库
			$data['uptime']=time();
			$data['filesize']=filesize('.'.$url);
			$data['path']=$url;
			Db::name('plug_files')->insert($data);
		}
		$admin=Db::name('admin')->where(array('admin_id'=>session('aid')))->find();
		$admin['admin_avatar']=$imgurl;
		$rst=Db::name('admin')->where(array('admin_id'=>session('aid')))->update($admin);
		if($rst!==false){
			session('admin_avatar',$imgurl);
			$this->success ('头像更新成功',url('profile'));
		}else{
			$this->error ('头像更新失败',url('profile'));
		}
	}
}