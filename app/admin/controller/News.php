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

class News extends Base {
	protected $tpls;
    protected function _initialize(){
		parent::_initialize();
        $sys=Db::name('options')->where('option_l',$this->lang)->where(array('option_name'=>'site_options'))->value("option_value");
		if(empty($sys)) $sys=Db::name('options')->where('option_l','zh-cn')->where(array('option_name'=>'site_options'))->value("option_value");
        $sys=json_decode($sys,true);
		$arr=list_file(APP_PATH.'home/view/'.$sys['site_tpl'],'*.html');
		$tpls=array();
		foreach($arr as $v){
			$tpls[]=basename($v['filename'],'.html');
		}
		$this->tpls=$tpls;
    }
	//文章列表
	public function news_list(){
		$keytype=input('keytype','news_title');
		$key=input('key');
		$news_l=input('news_l');
		$opentype_check=input('opentype_check','');
		$news_columnid=input('news_columnid','');
		$diyflag=input('diyflag','');
		//查询：时间格式过滤
		$sldate=input('reservation','');//获取格式 2015-11-12 - 2015-11-18
		$arr = explode(" - ",$sldate);//转换成数组
        if(count($arr)==2){
            $arrdateone=strtotime($arr[0]);
            $arrdatetwo=strtotime($arr[1].' 23:55:55');
            $map['news_time'] = array(array('egt',$arrdateone),array('elt',$arrdatetwo),'AND');
        }
		//map架构查询条件数组
		$map['news_back']= 0;
		if(!empty($key)){
			if($keytype=='news_title'){
				$map[$keytype]= array('like',"%".$key."%");
			}elseif($keytype=='news_author'){
				$map['member_list_username']= array('like',"%".$key."%");
			}else{
				$map[$keytype]= $key;
			}
		}
		if ($opentype_check!=''){
			$map['news_open']= array('eq',$opentype_check);
		}
		if (!empty($news_l)){
			$map['news_l']= array('eq',$news_l);
		}
		if ($news_columnid!=''){
			$ids=get_menu_byid($news_columnid,1,2);
			$map['news_columnid']= array('in',implode(",", $ids));
		}
		$where=$diyflag?"FIND_IN_SET('$diyflag',news_flag)":'';
		$news=Db::name('News')->alias("a")->field('a.*,b.*,c.menu_name')->join(config('database.prefix').'member_list b','a.news_auto =b.member_list_id')->join(config('database.prefix').'menu c','a.news_columnid =c.id')->where($map)->where($where)->order('news_time desc')->paginate(config('paginate.list_rows'),false,['query'=>get_query()]);
		$show = $news->render();
		$show=preg_replace("(<a[^>]*page[=|/](\d+).+?>(.+?)<\/a>)","<a href='javascript:ajax_page($1);'>$2</a>",$show);
		$this->assign('page',$show);
		$diyflag_list=Db::name('diyflag')->select();//文章属性数据
		//栏目数据
		$nav = new \Leftnav;
		$menu_next=Db::name('menu')->where('menu_type <> 4 and menu_type <> 2')-> order('menu_l desc,listorder') -> select();
		$arr = $nav::menu_n($menu_next);
		$this->assign('menu',$arr);
		$this->assign('opentype_check',$opentype_check);
		$this->assign('news_columnid',$news_columnid);
		$this->assign('keytype',$keytype);
		$this->assign('keyy',$key);
		$this->assign('news_l',$news_l);
		$this->assign('sldate',$sldate);
		$this->assign('diyflag_check',$diyflag);
		$this->assign('diyflag',$diyflag_list);
		$this->assign('news',$news);
		if(request()->isAjax()){
			return $this->fetch('ajax_news_list');
		}else{
			return $this->fetch();
		}		
	}
	//添加文章
	public function news_add(){
		$nav = new \Leftnav;
		$menu_next=Db::name('menu')->where('menu_type <> 4 and menu_type <> 2')-> order('menu_l Desc,listorder') -> select();
		$diyflag=Db::name('diyflag')->select();
		$arr = $nav::menu_n($menu_next);
		$source=Db::name('source')->select();
		$this->assign('source',$source);
		$this->assign('menu',$arr);
		$this->assign('diyflag',$diyflag);
		return $this->fetch();
	}
	//添加操作
	public function news_runadd(){
		if (!request()->isAjax()){
			$this->error('提交方式不正确',url('news_list'));
		}
		$img_one='';
		$picall_url='';
		//上传图片部分
		if(!empty($_FILES['pic_one']['name'][0]) || !empty($_FILES['pic_all']['name'][0])) { //images 是你上传的名称
			$file = request()->file('pic_one');
			$files = request()->file('pic_all');
			if(config('storage.storage_open')){
				//七牛
				$upload = \Qiniu::instance();
				$info = $upload->upload();
				$error = $upload->getError();
				if ($info) {
					if($file && $files){
						//有单图、多图
						if(!empty($info['pic_one'])) $img_one= config('storage.domain').$info['pic_one'][0]['key'];
						if(!empty($info['pic_all'])) {
							foreach ($info['pic_all'] as $file) {
								$img_url=config('storage.domain').$file['key'];
								$picall_url = $img_url . ',' . $picall_url;
							}
						}
					}elseif($file){
						//单图
						$img_one= config('storage.domain').$info[0]['key'];
					}else{
						//多图
						foreach ($info as $file) {
							$img_url=config('storage.domain').$file['key'];
							$picall_url = $img_url . ',' . $picall_url;
						}
					}
				}else{
					$this->error($error,url('news_list'));//否则就是上传错误，显示错误原因
				}
			}else{
				$validate = config('upload_validate');
				//单图
				if (!empty($file)) {
					$info = $file[0]->validate($validate)->rule('uniqid')->move(ROOT_PATH . config('upload_path') . DS . date('Y-m-d'));
					if ($info) {
						$img_url = config('upload_path'). '/' . date('Y-m-d') . '/' . $info->getFilename();
						//写入数据库
						$data['uptime'] = time();
						$data['filesize'] = $info->getSize();
						$data['path'] = $img_url;
						Db::name('plug_files')->insert($data);
						$img_one = $img_url;
					} else {
						$this->error($file->getError(), url('news_list'));//否则就是上传错误，显示错误原因
					}
				}
				//多图
				if (!empty($files)) {
					foreach ($files as $file) {
						$info = $file->validate($validate)->rule('uniqid')->move(ROOT_PATH . config('upload_path') . DS . date('Y-m-d'));
						if ($info) {
							$img_url = config('upload_path'). '/' . date('Y-m-d') . '/' . $info->getFilename();
							//写入数据库
							$data['uptime'] = time();
							$data['filesize'] = $info->getSize();
							$data['path'] = $img_url;
							Db::name('plug_files')->insert($data);
							$picall_url = $img_url . ',' . $picall_url;
						} else {
							$this->error($file->getError(), url('news_list'));//否则就是上传错误，显示错误原因
						}
					}
				}
			}
		}
		//获取文章属性
		$news_flag=input('post.news_flag/a');
		$flag=array();
		if(!empty($news_flag)){
			foreach ($news_flag as $v){
				$flag[]=$v;
			}
		}
		$flagdata=implode(',',$flag);
		$sl_data=array(
			'news_title'=>input('news_title'),
			'news_titleshort'=>input('news_titleshort',''),
			'news_columnid'=>input('news_columnid'),
			'news_flag'=>$flagdata,
			'news_zaddress'=>input('news_zaddress',''),
			'news_key'=>input('news_key',''),
			'news_tag'=>input('news_tag',''),
			'news_source'=>input('news_source',''),
			'news_pic_type'=>input('news_pic_type'),
			'news_pic_content'=>input('news_pic_content',''),
			'news_pic_allurl'=>$picall_url,//多图路径
			'news_img'=>$img_one,//封面图片路径
			'news_open'=>input('news_open',0),
			'news_scontent'=>input('news_scontent',''),
			'news_content'=>htmlspecialchars_decode(input('news_content')),
			'news_auto'=>session('member_id'),
			'news_time'=>time(),
			'news_hits'=>200,
			'listorder'=>input('listorder',50,'intval'),
		);
		//根据栏目id,获取语言
		$news_l=Db::name('menu')->where('id',input('news_columnid'))->value('menu_l');
		$sl_data['news_l']=$news_l;
		//附加字段
		$showtime=input('showdate','');
		$news_extra['showdate']=($showtime=='')?time():strtotime($showtime);
		$sl_data['news_extra']=json_encode($news_extra);
		Db::name('news')->insert($sl_data);
		$this->success('文章添加成功,返回列表页',url('news_list'));
	}
	//文章编辑
	public function news_edit(){
		$n_id = input('n_id');
		if (empty($n_id)){
			$this->error('参数错误',url('news_list'));
		}
		$news_list=Db::name('news')->where(array('n_id'=>input('n_id')))->find();
		$news_extra=json_decode($news_list['news_extra'],true);
		$news_extra['showdate']=($news_extra['showdate']=='')?$news_list['news_time']:$news_extra['showdate'];
		/*
		 * 多图字符串转换成数组
		 */
		$text = $news_list['news_pic_allurl'];
		$pic_list = array_filter(explode(",", $text));
		$this->assign('pic_list',$pic_list);
		$nav = new \Leftnav;
		$menu_next=Db::name('menu')->where('menu_type <> 4 and menu_type <> 2')-> order('menu_l Desc,listorder') -> select();
		$diyflag=Db::name('diyflag')->select();
		$arr = $nav::menu_n($menu_next);
		$source=Db::name('source')->select();//来源
		$this->assign('source',$source);
		$this->assign('news_extra',$news_extra);
		$this->assign('menu',$arr);
		$this->assign('diyflag',$diyflag);
		$this->assign('news_list',$news_list);
		return $this->fetch();
	}
	public function news_runedit(){
		if (!request()->isAjax()){
			$this->error('提交方式不正确',url('news_list'));
		}
		//获取图片上传后路径
		$pic_oldlist=input('pic_oldlist');//老多图字符串
		$img_one='';
		$picall_url='';
		//上传处理
		if(!empty($_FILES['pic_one']['name'][0]) || !empty($_FILES['pic_all']['name'][0])) {
			$file = request()->file('pic_one');
			$files = request()->file('pic_all');
			if(config('storage.storage_open')){
				//七牛
				$upload = \Qiniu::instance();
				$info = $upload->upload();
				$error = $upload->getError();
				if ($info) {
					if($file && $files){
						//有单图、多图
						if(!empty($info['pic_one'])) $img_one= config('storage.domain').$info['pic_one'][0]['key'];
						if(!empty($info['pic_all'])) {
							foreach ($info['pic_all'] as $file) {
								$img_url=config('storage.domain').$file['key'];
								$picall_url = $img_url . ',' . $picall_url;
							}
						}
					}elseif($file){
						//单图
						$img_one= config('storage.domain').$info[0]['key'];
					}else{
						//多图
						foreach ($info as $file) {
							$img_url=config('storage.domain').$file['key'];
							$picall_url = $img_url . ',' . $picall_url;
						}
					}
				}else{
					$this->error($error,url('news_list'));//否则就是上传错误，显示错误原因
				}
			}else{
				$validate = config('upload_validate');
				//单图
				if (!empty($file)) {
					$info = $file[0]->validate($validate)->rule('uniqid')->move(ROOT_PATH . config('upload_path') . DS . date('Y-m-d'));
					if ($info) {
						$img_url = config('upload_path'). '/' . date('Y-m-d') . '/' . $info->getFilename();
						//写入数据库
						$data['uptime'] = time();
						$data['filesize'] = $info->getSize();
						$data['path'] = $img_url;
						Db::name('plug_files')->insert($data);
						$img_one = $img_url;
					} else {
						$this->error($file->getError(), url('news_list'));//否则就是上传错误，显示错误原因
					}
				}
				//多图
				if (!empty($files)) {
					foreach ($files as $file) {
						$info = $file->validate($validate)->rule('uniqid')->move(ROOT_PATH . config('upload_path') . DS . date('Y-m-d'));
						if ($info) {
							$img_url = config('upload_path'). '/' . date('Y-m-d') . '/' . $info->getFilename();
							//写入数据库
							$data['uptime'] = time();
							$data['filesize'] = $info->getSize();
							$data['path'] = $img_url;
							Db::name('plug_files')->insert($data);
							$picall_url = $img_url . ',' . $picall_url;
						} else {
							$this->error($file->getError(), url('news_list'));//否则就是上传错误，显示错误原因
						}
					}
				}
			}
		}
		//获取文章属性
		$news_flag=input('post.news_flag/a');
		$flag=array();
		if(!empty($news_flag)){
			foreach ($news_flag as $v){
				$flag[]=$v;
			}
		}
		$flagdata=implode(',',$flag);
		$sl_data=array(
			'n_id'=>input('n_id'),
			'news_title'=>input('news_title'),
			'news_titleshort'=>input('news_titleshort',''),
			'news_columnid'=>input('news_columnid'),
			'news_flag'=>$flagdata,
			'news_zaddress'=>input('news_zaddress',''),
			'news_key'=>input('news_key',''),
			'news_tag'=>input('news_tag',''),
			'news_source'=>input('news_source',''),
			'news_pic_type'=>input('news_pic_type'),
			'news_pic_content'=>input('news_pic_content',''),
			'news_open'=>input('news_open',0),
			'news_scontent'=>input('news_scontent',''),
			'news_content'=>htmlspecialchars_decode(input('news_content')),
			'listorder'=>input('listorder',50,'intval'),
		);
		//图片字段处理
		if(!empty($img_one)){
			$sl_data['news_img']=$img_one;
		}
		$sl_data['news_pic_allurl']=$pic_oldlist.$picall_url;
		//根据栏目id,获取语言
		$news_l=Db::name('menu')->where('id',input('news_columnid'))->value('menu_l');
		$sl_data['news_l']=$news_l;
		//附加字段
		$showtime=input('showdate','');
		$news_extra['showdate']=($showtime=='')?time():strtotime($showtime);
		$sl_data['news_extra']=json_encode($news_extra);
		$rst=Db::name('news')->update($sl_data);
		if($rst!==false){
			$this->success('文章修改成功,返回列表页',url('news_list'));
		}else{
			$this->error('文章修改失败',url('news_list'));
		}
	}
	//文章排序
	public function news_order(){
		if (!request()->isAjax()){
			$this->error('提交方式不正确',url('news_list'));
		}else{
			foreach (input('post.') as $n_id => $news_order){
				Db::name('news')->where(array('n_id' => $n_id ))->setField('listorder' , $news_order);
			}
			$this->success('排序更新成功',url('news_list'));
		}
	}
	//文章删除到回收站
	public function news_del(){
		$p=input('p');
		$rst=Db::name('news')->where(array('n_id'=>input('n_id')))->setField('news_back',1);//转入回收站
		if($rst!==false){
			$this->success('文章已转入回收站',url('news_list',array('p' => $p)));
		}else{
			$this -> error("删除文章失败！",url('news_list',array('p'=>$p)));
		}
	}
	//全选删除
	public function news_alldel(){
		$p = input('p');
		$ids = input('n_id/a');
		if(empty($ids)){
			$this -> error("请选择删除文章",url('news_list',array('p'=>$p)));//判断是否选择了文章ID
		}
		if(is_array($ids)){//判断获取文章ID的形式是否数组
			$where = 'n_id in('.implode(',',$ids).')';
		}else{
			$where = 'n_id='.$ids;
		}
		$rst=Db::name('news')->where($where)->setField('news_back',1);//转入回收站
		if($rst!==false){
			$this->success("成功把文章移至回收站！",url('news_list',array('p'=>$p)));
		}else{
			$this -> error("删除文章失败！",url('news_list',array('p'=>$p)));
		}
	}
	//文章审核
	public function news_state(){
		$id=input('x');
		$status=Db::name('news')->where(array('n_id'=>$id))->value('news_open');//判断当前状态情况
		if($status==1){
			$statedata = array('news_open'=>0);
			Db::name('news')->where(array('n_id'=>$id))->setField($statedata);
			$this->success('未审');
		}else{
			$statedata = array('news_open'=>1);
			Db::name('news')->where(array('n_id'=>$id))->setField($statedata);
			$this->success('已审');
		}
	}
	//回收站
	public function news_back(){
		$keytype=input('keytype','news_title');
		$key=input('key');
		$news_l=input('news_l');
		$opentype_check=input('opentype_check','');
		$diyflag=input('diyflag','');
		//查询：时间格式过滤
		$sldate=input('reservation','');//获取格式 2015-11-12 - 2015-11-18
		$arr = explode(" - ",$sldate);//转换成数组
		if(count($arr)==2){
			$arrdateone=strtotime($arr[0]);
			$arrdatetwo=strtotime($arr[1].' 23:55:55');
			$map['news_time'] = array(array('egt',$arrdateone),array('elt',$arrdatetwo),'AND');
		}
		//map架构查询条件数组
		$map['news_back']= 1;
		if(!empty($key)){
			$map[$keytype]= array('like',"%".$key."%");
		}
		if ($opentype_check!=''){
			$map['news_open']= array('eq',$opentype_check);
		}
		if (!empty($news_l)){
			$map['news_l']= array('eq',$news_l);
		}
		$where=$diyflag?"FIND_IN_SET('$diyflag',news_flag)":'';
		$news=Db::name('News')->alias("a")->field('a.*,b.*,c.menu_name')->join(config('database.prefix').'member_list b','a.news_auto =b.member_list_id')->join(config('database.prefix').'menu c','a.news_columnid =c.id')->where($map)->where($where)->order('news_time desc')->paginate(config('paginate.list_rows'),false,['query'=>get_query()]);
		$show = $news->render();
		$show=preg_replace("(<a[^>]*page[=|/](\d+).+?>(.+?)<\/a>)","<a href='javascript:ajax_page($1);'>$2</a>",$show);
		$this->assign('page',$show);
		$diyflag_list=Db::name('diyflag')->select();//文章属性数据
		$this->assign('opentype_check',$opentype_check);
		$this->assign('keytype',$keytype);
		$this->assign('keyy',$key);
		$this->assign('news_l',$news_l);
		$this->assign('sldate',$sldate);
		$this->assign('diyflag_check',$diyflag);
		$this->assign('diyflag',$diyflag_list);
		$this->assign('news',$news);
		if(request()->isAjax()){
			return $this->fetch('ajax_news_back');
		}else{
			return $this->fetch();
		}	
	}
	//回收站还原文章
	public function news_back_open(){
		$p=input('p');
		$rst=Db::name('news')->where(array('n_id'=>input('n_id')))->setField('news_back',0);//转入正常
		if($rst!==false){
			$this->success('文章还原成功',url('news_back',array('p' => $p)));
		}else{
			$this -> error("文章还原失败！",url('news_back',array('p' => $p)));
		}
	}
	//文章彻底删除
	public function news_back_del(){
		$n_id=input('n_id');
		$p = input('p');
		if (empty($n_id)){
			$this->error('参数错误',url('news_back'));
		}else{
			$rst=Db::name('news')->where(array('n_id'=>input('n_id')))->delete();
			if($rst!==false){
				$this->success('文章彻底删除成功',url('news_back',array('p' => $p)));
			}else{
				$this -> error("文章彻底删除失败！",url('news_back',array('p' => $p)));
			}
		}
	}
	//彻底删除
	public function news_back_alldel(){
		$p = input('p');
		$ids = input('n_id/a');
		if(empty($ids)){
			$this -> error("请选择删除文章",url('news_back',array('p'=>$p)));//判断是否选择了文章ID
		}
		if(is_array($ids)){//判断获取文章ID的形式是否数组
			$where = 'n_id in('.implode(',',$ids).')';
		}else{
			$where = 'n_id='.$ids;
		}
		$rst=Db::name('news')->where($where)->delete();
		if($rst!==false){
			$this->success("成功把文章删除，不可还原！",url('news_back',array('p'=>$p)));
		}else{
			$this -> error("文章彻底删除失败！",url('news_back',array('p' => $p)));
		}
	}
	//菜单管理
	public function news_menu_list(){
		//TODO 分页
		$menu_l=input('menu_l');
		$nav = new \Leftnav;
		$where=array();
		if(!empty($menu_l)){
			$where['menu_l']=array('eq',$menu_l);
		}
		$menus=Db::name('menu')->where($where)->order('menu_l Desc,listorder')->select();
        $menus=get_menu_model($menus);
		$show='';
		$arr = $nav::menu_n($menus);
		$this->assign('arr',$arr);
		$this->assign('menu_l',$menu_l);
		$this->assign('page',$show);
		if(request()->isAjax()){
			return $this->fetch('ajax_news_menu_list');
		}else{
			return $this->fetch();
		}
	}
	//添加菜单
	public function news_menu_add(){
		$parentid=input('id',0);
		//id不为0,取lang
		$menu_l='';
		if(!empty($parentid)){
			$menu_l=Db::name('menu')->where('id',$parentid)->value('menu_l');
		}
		$model=Db::name('model')->select();
        $this->assign('model',$model);
		$this->assign('parentid',$parentid);
		$this->assign('menu_l',$menu_l);
		$this->assign('tpls',$this->tpls);
		return $this->fetch();
	}
	//添加菜单操作
	public function news_menu_runadd(){
		$lang_list=input('lang_list');
		if(empty($lang_list)) $lang_list=input('menu_l','zh-cn');
		if (!request()->isAjax()){
			$this->error('提交方式不正确',url('news_menu_list',array('menu_l'=>$lang_list)));
		}else{
			//处理图片
			$img_url='';
			$file = request()->file('file0');
			if($file){
				if(config('storage.storage_open')){
					//七牛
					$upload = \Qiniu::instance();
					$info = $upload->upload();
					$error = $upload->getError();
					if ($info) {
						$img_url= config('storage.domain').$info[0]['key'];
					}else{
						$this->error($error,url('news_menu_list',array('menu_l'=>$lang_list)));//否则就是上传错误，显示错误原因
					}
				}else{
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
						$this->error($file->getError(),url('news_menu_list',array('menu_l'=>$lang_list)));//否则就是上传错误，显示错误原因
					}
				}
			}
			//构建数组
			$data=array(
				'menu_name'=>input('menu_name'),
				'menu_l'=>empty($lang_list)?input('menu_l','zh-cn'):$lang_list,
				'menu_enname'=>input('menu_enname'),
				'menu_type'=>input('menu_type'),
                'menu_modelid'=>input('menu_modelid',0,'intval'),
				'parentid'=>input('parentid'),
				'menu_listtpl'=>input('menu_listtpl'),
				'menu_newstpl'=>input('menu_newstpl'),
				'menu_address'=>input('menu_address'),
				'menu_open'=>input('menu_open',0),
				'listorder'=>input('listorder'),
				'menu_seo_title'=>input('menu_seo_title'),
				'menu_seo_key'=>input('menu_seo_key'),
				'menu_seo_des'=>input('menu_seo_des'),
				'menu_content'=>htmlspecialchars_decode(input('menu_content')),
				'menu_img'=>$img_url,
			);
			$rst=Db::name('menu')->insert($data);
			if($rst!==false){
				$arr=Db::name('menu')->find(input('parentid'));
				if(input('menu_type')==3 && $arr['menu_type']==3){
					Db::name('menu')->where(array('id'=>input('parentid')))->setField('menu_type',1);
				}
				cache('site_nav_main',null);
				$this->success('菜单添加成功',url('news_menu_list',array('menu_l'=>$lang_list)));
			}else{
				$this->error('菜单添加失败',url('news_menu_list',array('menu_l'=>$lang_list)));
			}
		}
	}
	//编辑菜单
	public function news_menu_edit(){
		$menu=Db::name('menu')->where(array('id'=>input('id')))->find();
        $model=Db::name('model')->select();
        $this->assign('model',$model);
		$this->assign('menu',$menu);
		$this->assign('tpls',$this->tpls);
		return $this->fetch();
	}
	public function news_menu_runedit(){
		$lang_list=input('lang_list');
		if (!request()->isAjax()){
			$this->error('提交方式不正确',url('news_menu_list',array('menu_l'=>$lang_list)));
		}else{
			$checkpic=input('checkpic');
			$oldcheckpic=input('oldcheckpic');
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
							$this->error($error,url('news_menu_list',array('menu_l'=>$lang_list)));//否则就是上传错误，显示错误原因
						}
					}else{
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
							$this->error($file->getError(),url('news_menu_list',array('menu_l'=>$lang_list)));//否则就是上传错误，显示错误原因
						}
					}
				}
			}
			$data=array(
				'id'=>input('id'),
				'menu_name'=>input('menu_name'),
				'menu_enname'=>input('menu_enname'),
				'menu_type'=>input('menu_type'),
                'menu_modelid'=>input('menu_modelid',0,'intval'),
				'parentid'=>input('parentid'),
				'menu_listtpl'=>input('menu_listtpl'),
				'menu_newstpl'=>input('menu_newstpl'),
				'menu_address'=>input('menu_address'),
				'menu_open'=>input('menu_open',0),
				'listorder'=>input('listorder'),
				'menu_seo_title'=>input('menu_seo_title'),
				'menu_seo_key'=>input('menu_seo_key'),
				'menu_seo_des'=>input('menu_seo_des'),
				'menu_content'=>htmlspecialchars_decode(input('menu_content')),
			);
			if ($checkpic!=$oldcheckpic){
				$data['menu_img']=$img_url;
			}
			$rst=Db::name('menu')->update($data);
			if($rst!==false){
				cache('site_nav_main',null);
				$this->success('菜单修改成功',url('news_menu_list',array('menu_l'=>$lang_list)));
			}else{
				$this->error('菜单修改失败',url('news_menu_list',array('menu_l'=>$lang_list)));
			}
		}
	}
	//删除菜单
	public function news_menu_del(){
		$lang_list=input('lang_list');
		$arr=Db::name('menu')->find(input('id'));
		$model_id=$arr['menu_modelid'];
		$parentid=$arr['parentid'];
		$arr=Db::name('menu')->find($parentid);
		$rst=Db::name('menu')->where(array('parentid'=>input('id')))->select();
		if($rst){
			$rst=Db::name('menu')->where(array('parentid'=>input('id')))->delete();//删除子菜单
			if($rst!==false){
				$rst=Db::name('menu')->where(array('id'=>input('id')))->delete();//删除自身菜单
				if($rst!==false){
					//判断其父菜单是否还存在子菜单，如无子菜单，且父菜单类型为1
					if($parentid && $arr['menu_type']==1){
						$child=Db::name('menu')->where(array('parentid'=>$parentid))->select();
						if(empty($child)){
                            Db::name('menu')->where(array('id'=>$parentid))->update(['menu_type'=>3,'menu_modelid'=>$model_id]);
						}
					}
					cache('site_nav_main',null);
					$this->success('菜单删除成功',url('news_menu_list',['menu_l'=>$lang_list]));
				}else{
					$this -> error("菜单删除失败！",url('news_menu_list',['menu_l'=>$lang_list]));
				}
			}else{
				$this -> error("菜单删除失败！",url('news_menu_list',['menu_l'=>$lang_list]));
			}
		}else{
			$rst=Db::name('menu')->where(array('id'=>input('id')))->delete();//无子菜单，删除自身
			if($rst!==false){
				//判断其父菜单是否还存在子菜单，如无子菜单，且父菜单类型为1
				if($parentid && $arr['menu_type']==1){
					$child=Db::name('menu')->where(array('parentid'=>$parentid))->select();
					if(empty($child)){
						Db::name('menu')->where(array('id'=>$parentid))->update(['menu_type'=>3,'menu_modelid'=>$model_id]);
					}
				}
				cache('site_nav_main',null);
				$this->success('菜单删除成功',url('news_menu_list',['menu_l'=>$lang_list]));
			}else{
				$this -> error("菜单删除失败！",url('news_menu_list',['menu_l'=>$lang_list]));
			}
		}
	}
	//菜单排序
	public function news_menu_order(){
		$lang_list=input('lang_list');
		if (!request()->isAjax()){
			$this->error('提交方式不正确',url('news_menu_list',['menu_l'=>$lang_list]));
		}else{
			foreach ($_POST as $id => $sort){
				Db::name('menu')->where(array('id' => $id ))->setField('listorder' , $sort);
			}
			cache('site_nav_main',null);
			$this->success('排序更新成功',url('news_menu_list',['menu_l'=>$lang_list]));
		}
	}
	//菜单状态
	public function news_menu_state(){
		$id=input('x');
		$status=Db::name('menu')->where(array('id'=>$id))->value('menu_open');//判断当前状态情况
		if($status==1){
			$statedata = array('menu_open'=>0);
			Db::name('menu')->where(array('id'=>$id))->setField($statedata);
			cache('site_nav_main',null);
			$this->success('状态禁止');
		}else{
			$statedata = array('menu_open'=>1);
			Db::name('menu')->where(array('id'=>$id))->setField($statedata);
			cache('site_nav_main',null);
			$this->success('状态开启');
		}
	}
}