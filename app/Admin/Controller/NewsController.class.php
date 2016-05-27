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

class NewsController extends AuthController {
    protected function _initialize(){
		parent::_initialize();
        $sys=M('options')->where(array('option_name'=>'site_options'))->getField("option_value");
        $sys=json_decode($sys,true);
		$arr=list_file(APP_PATH.'Home/View/'.$sys['site_tpl'],'*.html');
		$tpls=array();
		foreach($arr as $v){
			$tpls[]=basename($v['filename'],'.html');
		}
		$this->tpls=$tpls;
    }
	//文章列表
	public function news_list(){
		$keytype=I('keytype','news_title');
		$key=I('key');
		$opentype_check=I('opentype_check','');
		$diyflag=I('diyflag','');
		//查询：时间格式过滤
		$sldate=I('reservation','');//获取格式 2015-11-12 - 2015-11-18
		$arr = explode(" - ",$sldate);//转换成数组
        if(count($arr)==2){
            $arrdateone=strtotime($arr[0]);
            $arrdatetwo=strtotime($arr[1].' 23:55:55');
            $map['news_time'] = array(array('egt',$arrdateone),array('elt',$arrdatetwo),'AND');
        }
		//map架构查询条件数组
		$map['news_back']= 0;
        if($keytype=='news_title'){
            $map[$keytype]= array('like',"%".$key."%");
        }elseif($keytype=='news_author'){
            $map['admin_realname|admin_username']= array('like',"%".$key."%");
        }else{
            $map[$keytype]= $key;
        }
		if ($opentype_check!=''){
			$map['news_open']= array('eq',$opentype_check);
		}
		if ($diyflag){
			$map[] ="FIND_IN_SET('$diyflag',news_flag)";
		}
		//p($map);die;
        $rs=D('News');
        $join1 = "".C('DB_PREFIX').'admin as b on a.news_auto =b.admin_id';
		$count= $rs->alias("a")->join($join1)->where($map)->count();// 查询满足要求的总记录数
		$Page= new \Think\Page($count,C('DB_PAGENUM'));// 实例化分页类 传入总记录数和每页显示的记录数
		$show= $Page->show();// 分页显示输出
		$this->assign('page',$show);
		$listRows=(intval(C('DB_PAGENUM'))>0)?C('DB_PAGENUM'):20;
		if($count>$listRows){
			$Page->setConfig('theme','<div class=pagination><ul> %upPage% %downPage% %first%  %prePage%  %linkPage%  %nextPage% %end%</ul></div>');
		}
		$show= $Page->show();// 分页显示输出
		$this->assign('page_min',$show);
		$news=$rs->alias("a")->join($join1)->where($map)->limit($Page->firstRow.','.$Page->listRows)->order('news_time desc')->relation(true)->select();
		$diyflag_list=M('diyflag')->select();//文章属性数据
		$this->assign('opentype_check',$opentype_check);
		$this->assign('keytype',$keytype);
		$this->assign('keyy',$key);
		$this->assign('sldate',$sldate);
		$this->assign('diyflag_check',$diyflag);
		$this->assign('diyflag',$diyflag_list);
		$this->assign('news',$news);
		$this->display();
	}

	//添加文章
	public function news_add(){
		$menu=M('menu');
		$diyflag=M('diyflag');
		$nav = new \Org\Util\Leftnav;
		$menu_next=$menu->where('menu_type <> 4 and menu_type <> 2')-> order('listorder') -> select();
		$diyflag=$diyflag->select();
		$arr = $nav::menu_n($menu_next);
		$source=M('source')->select();
		$this->assign('source',$source);
		$this->assign('menu',$arr);
		$this->assign('diyflag',$diyflag);
		$this->display();
	}
	//添加操作
	public function news_runadd(){
		if (!IS_AJAX){
			$this->error('提交方式不正确',U('news_list'),0);
		}
		$news=M('news');
		//单图上传控制

		if($pop=$_FILES['pic_one']['name'][0] || $popp=$_FILES['pic_all']['name'][0]){ //images 是你上传的名称
			//获取图片上传后路径
			$upload = new \Think\Upload();// 实例化上传类
			$upload->maxSize   =     3145728 ;// 设置附件上传大小
			$upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
			$upload->rootPath  =     C('UPLOAD_DIR'); // 设置附件上传根目录
			$upload->savePath  =     ''; // 设置附件上传（子）目录
			$upload->saveRule  =     'time';
			$info   =   $upload->upload();
			$picall_url="";
			if($info) {
				foreach($info as $file){
					if ($file['key']=='pic_one'){//单图路径数组
						$img_url=substr(C('UPLOAD_DIR'),1).$file[savepath].$file[savename];//如果上传成功则完成路径拼接
					}else{
						$picall=substr(C('UPLOAD_DIR'),1).$file[savepath].$file[savename];//如果上传成功则完成路径拼接
						$picall_url=$picall.','.$picall_url;
					}
				}


			}else{
				$this->error($upload->getError(),U('news_list'),0);//否则就是上传错误，显示错误原因
			}
		}

		//获取文章属性
		$news_flag=I('news_flag');
		$flag=array();
		foreach ($news_flag as $v){
			$flag[]=$v;
		}
		$flagdata=implode(',',$flag);



		$sl_data=array(
			'news_title'=>I('news_title'),
			'news_titleshort'=>I('news_titleshort'),
			'news_columnid'=>I('news_columnid'),
			'news_flag'=>$flagdata,
			'news_zaddress'=>I('news_zaddress'),
			'news_key'=>I('news_key'),
			'news_tag'=>I('news_key'),
			'news_source'=>I('news_source'),
			'news_pic_type'=>I('news_pic_type'),
			'news_pic_content'=>I('news_pic_content'),
			'news_pic_allurl'=>$picall_url,//多图路径

			'news_img'=>$img_url,

			'news_open'=>I('news_open'),
			'news_scontent'=>I('news_scontent'),
			'news_content'=>htmlspecialchars_decode(I('news_content')),
			'news_auto'=>session('aid'),
			'news_time'=>time(),
			'news_hits'=>200,
		);

		$news->add($sl_data);
		$this->success('文章添加成功,返回列表页',U('news_list'),1);
	}

	public function news_edit(){
		$n_id = I('n_id');
		if (empty($n_id)){
			$this->error('参数错误',U('news_list'),0);
		}else{
			$news_list=M('news')->where(array('n_id'=>I('n_id')))->find();
			/*
			 * 多图字符串转换成数组
			 */
			$text = $news_list['news_pic_allurl'];
			$pic_list = array_filter(explode(",", $text));
			$this->assign('pic_list',$pic_list);

			$diyflag=M('diyflag');
			$nav = new \Org\Util\Leftnav;
			$menu_next=M('menu')->where('menu_type <> 4 and menu_type <> 2')-> order('listorder') -> select();
			$diyflag=$diyflag->select();
			$arr = $nav::menu_n($menu_next);
			$source=M('source')->select();//来源
			$this->assign('source',$source);
			$this->assign('menu',$arr);
			$this->assign('diyflag',$diyflag);
			$this->assign('news_list',$news_list);
			$this->display();
		}
	}
	public function news_runedit(){
		if (!IS_AJAX){
			$this->error('提交方式不正确',U('news_list'),0);
		}
		$news=M('news');
		//获取图片上传后路径
		$checkpic=I('checkpic');
		$oldcheckpic=I('oldcheckpic');

		$pic_oldlist=I('pic_oldlist');//老多图字符串

		if($pop=$_FILES['pic_one']['name'][0] || $popp=$_FILES['pic_all']['name'][0]){ //images 是你上传的名称
			$upload = new \Think\Upload();// 实例化上传类
			$upload->maxSize   =     3145728 ;// 设置附件上传大小
			$upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
			$upload->rootPath  =     C('UPLOAD_DIR'); // 设置附件上传根目录
			$upload->savePath  =     ''; // 设置附件上传（子）目录
			$upload->saveRule  =     'time';
			$info   =   $upload->upload();
			$picall_url="";
			if($info) {
				foreach($info as $file){//获取全部的上传数据
					if ($file['key']=='pic_one'){//单图路径数组，通过key来判断是单图还是多图
						$img_url=substr(C('UPLOAD_DIR'),1).$file[savepath].$file[savename];//如果上传成功则完成路径拼接
					}else{//多图上传路径
						$picall=substr(C('UPLOAD_DIR'),1).$file[savepath].$file[savename];//如果上传成功则完成路径拼接
						$picall_url=$picall.','.$picall_url;//循环拼凑成字符串
					}
				}
			}else{
				$this->error($upload->getError(),U('news_list'),0);//否则就是上传错误，显示错误原因
			}
			$picall_list=$pic_oldlist.$picall_url;//整合新的多图字符串以及老的字符串
		}else{
			$picall_list=$pic_oldlist;//整合新的多图字符串以及老的字符串
		}
		$sll_data=array(
			'n_id'=>I('n_id'),
		);

		//获取文章属性
		$news_flag=I('news_flag');
		$flag=array();
		foreach ($news_flag as $v){
			$flag[]=$v;
		}
		$flagdata=implode(',',$flag);

		$sl_data=array(
			'n_id'=>I('n_id'),
			'news_title'=>I('news_title'),
			'news_titleshort'=>I('news_titleshort'),
			'news_columnid'=>I('news_columnid'),
			'news_flag'=>$flagdata,
			'news_zaddress'=>I('news_zaddress'),
			'news_key'=>I('news_key'),
			'news_tag'=>I('news_key'),
			'news_source'=>I('news_source'),
			'news_pic_type'=>I('news_pic_type'),
			'news_pic_content'=>I('news_pic_content'),
			'news_open'=>I('news_open'),
			'news_scontent'=>I('news_scontent'),
			'news_content'=>htmlspecialchars_decode(I('news_content')),
		);
		if ($checkpic!=$oldcheckpic){
			$sl_data['news_img']=$img_url;
		}
		$sl_data['news_pic_allurl']=$picall_list;
		$rst=$news->save($sl_data);
		if($rst!==false){
			$this->success('文章修改成功,返回列表页',U('news_list'),1);
		}else{
			$this->error('文章修改失败',U('news_list'),0);
		}
	}

	public function news_del(){
		$p=I('p');
		$rst=M('news')->where(array('n_id'=>I('n_id')))->setField('news_back',1);//转入回收站
		if($rst){
			$this->success('文章已转入回收站',U('news_list',array('p' => $p)),1);
		}else{
			$this -> error("删除文章失败！",U('news_list',array('p'=>$p)),0);
		}
	}
	public function news_alldel(){
		$p = I('p');
		$ids = I('n_id');
		if(empty($ids)){
			$this -> error("请选择删除文章",U('news_list',array('p'=>$p)),0);//判断是否选择了文章ID
		}
		if(is_array($ids)){//判断获取文章ID的形式是否数组
			$where = 'n_id in('.implode(',',$ids).')';
		}else{
			$where = 'n_id='.$ids;
		}
		M('news')->where($where)->setField('news_back',1);//转入回收站
		$this->success("成功把文章移至回收站！",U('news_list',array('p'=>$p)),1);
	}

	public function news_state(){
		$id=I('x');
		$status=M('news')->where(array('n_id'=>$id))->getField('news_open');//判断当前状态情况
		if($status==1){
			$statedata = array('news_open'=>0);
			$auth_group=M('news')->where(array('n_id'=>$id))->setField($statedata);
			$this->success('未审',1,1);
		}else{
			$statedata = array('news_open'=>1);
			$auth_group=M('news')->where(array('n_id'=>$id))->setField($statedata);
			$this->success('已审',1,1);
		}
	}
	//回收站
	public function news_back_open(){
		$p=I('p');
		$rst=M('news')->where(array('n_id'=>I('n_id')))->setField('news_back',0);//转入正常
		if($rst!==false){
			$this->success('文章还原成功',U('news_back',array('p' => $p)),1);
		}else{
			$this -> error("文章还原失败！",U('news_back',array('p' => $p)),0);
		}
	}

	public function news_back(){
		$keytype=I('keytype','news_title');
		$key=I('key');
		$opentype_check=I('opentype_check','');
		$diyflag=I('diyflag','');
		//查询：时间格式过滤
		$sldate=I('reservation','');//获取格式 2015-11-12 - 2015-11-18
		$arr = explode(" - ",$sldate);//转换成数组
        if(count($arr)==2){
            $arrdateone=strtotime($arr[0]);
            $arrdatetwo=strtotime($arr[1].' 23:55:55');
            $map['news_time'] = array(array('egt',$arrdateone),array('elt',$arrdatetwo),'AND');
        }
		//map架构查询条件数组
		$map['news_back']= 1;
		$map[$keytype]= array('like',"%".$key."%");
		if ($opentype_check!=''){
			$map['news_open']= array('eq',$opentype_check);
		}
		if ($diyflag){
			$map[] ="FIND_IN_SET('$diyflag',news_flag)";
		}
		//p($map);die;
		$join1 = "".C('DB_PREFIX').'admin as b on a.news_auto =b.admin_id';
		$count= M('news')->alias("a")->join($join1)->where($map)->count();// 查询满足要求的总记录数
		$Page= new \Think\Page($count,C('DB_PAGENUM'));// 实例化分页类 传入总记录数和每页显示的记录数
		$show= $Page->show();// 分页显示输出
		$this->assign('page',$show);
		$listRows=(intval(C('DB_PAGENUM'))>0)?C('DB_PAGENUM'):20;
		if($count>$listRows){
			$Page->setConfig('theme','<div class=pagination><ul> %upPage% %downPage% %first%  %prePage%  %linkPage%  %nextPage% %end%</ul></div>');
		}
		$show= $Page->show();// 分页显示输出
		$this->assign('page_min',$show);
		$news=D('News')->alias("a")->join($join1)->where($map)->limit($Page->firstRow.','.$Page->listRows)->order('news_time desc')->relation(true)->select();
		$diyflag_list=M('diyflag')->select();//文章属性数据
		$this->assign('opentype_check',$opentype_check);
		$this->assign('keytype',$keytype);
		$this->assign('keyy',$key);
		$this->assign('sldate',$sldate);
		$this->assign('diyflag_check',$diyflag);
		$this->assign('diyflag',$diyflag_list);
		$this->assign('news',$news);
		$this->display();
	}

	public function news_back_del(){
		$n_id=I('n_id');
		$p = I('p');
		if (empty($n_id)){
			$this->error('参数错误',U('news_back'),0);
		}else{
			$rst=M('news')->where(array('n_id'=>I('n_id')))->delete();
			if($rst){
				$this->success('文章彻底删除成功',U('news_back',array('p' => $p)),1);
			}else{
				$this -> error("文章彻底删除失败！",U('news_back',array('p' => $p)),0);
			}
		}
	}

	public function news_back_alldel(){
		$p = I('p');
		$ids = I('n_id','',htmlspecialchars);
		if(empty($ids)){
			$this -> error("请选择删除文章",U('news_back',array('p'=>$p)),0);//判断是否选择了文章ID
		}
		$model = D('news');
		if(is_array($ids)){//判断获取文章ID的形式是否数组
			$where = 'n_id in('.implode(',',$ids).')';
		}else{
			$where = 'n_id='.$ids;
		}
		M('news')->where($where)->delete();
		$this->success("成功把文章删除，不可还原！",U('news_back',array('p'=>$p)),1);
	}

	//菜单管理
	public function news_menu_list(){
		$nav = new \Org\Util\Leftnav;
		$menus=M('menu')->order('listorder')->select();
		$arr = $nav::menu_n($menus);
		$this->assign('arr',$arr);
		$this->display();
	}

	//添加菜单
	public function news_menu_add(){
		$parentid=I('id',0);
		$this->assign('parentid',$parentid);
        $this->assign('tpls',$this->tpls);
		$this->display();
	}

	public function news_menu_runadd(){
		if (!IS_AJAX){
			$this->error('提交方式不正确',U('news_menu_list'),0);
		}else{
			//处理图片
			$img_url='';
			$file=I('file0');//获取图片路径
			//获取图片上传后路径
			$upload = new \Think\Upload();// 实例化上传类
			$upload->maxSize   =     3145728 ;// 设置附件上传大小
			$upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
			$upload->rootPath  =     C('UPLOAD_DIR'); // 设置附件上传根目录
			$upload->savePath  =     ''; // 设置附件上传（子）目录
			$upload->saveRule  =     'time';
			$info   =   $upload->upload();
			if($info) {
				$img_url=substr(C('UPLOAD_DIR'),1).$info[file0][savepath].$info[file0][savename];//如果上传成功则完成路径拼接
			}elseif(!$file){
				$img_url='';//否则如果字段为空，表示没有上传任何文件，赋值空
			}else{
				$this->error($upload->getError(),U('news_menu_list'),0);//否则就是上传错误，显示错误原因
			}
			//构建数组
			
			$data=array(
				'menu_name'=>I('menu_name'),
				'menu_enname'=>I('menu_enname'),
				'menu_type'=>I('menu_type'),
				'parentid'=>I('parentid'),
				'menu_listtpl'=>I('menu_listtpl'),
				'menu_newstpl'=>I('menu_newstpl'),
				'menu_address'=>I('menu_address'),
				'menu_open'=>I('menu_open',0),
				'listorder'=>I('listorder'),
				'menu_seo_title'=>I('menu_seo_title'),
				'menu_seo_key'=>I('menu_seo_key'),
				'menu_seo_des'=>I('menu_seo_des'),
				'menu_content'=>htmlspecialchars_decode(I('menu_content')),
				'menu_img'=>$img_url,
			);
			$rst=M('menu')->add($data);
            if($rst!==false){
                $arr=M('menu')->find(I('parentid'));
                if(I('menu_type')==3 && $arr['menu_type']==3){
                    M('menu')->where(array('id'=>I('parentid')))->setField('menu_type' , 1);
                }
                $this->success('菜单添加成功',U('news_menu_list'),1);
            }else{
                $this->error('菜单添加失败',U('news_menu_list'),0);
            }

		}
	}

	//删除菜单
	public function news_menu_del(){
		$arr=M('menu')->find(I('id'));
        $parentid=$arr['parentid'];
        $arr=M('menu')->find($parentid);
		$rst=M('menu')->where(array('parentid'=>I('id')))->select();
		if($rst){
			$rst=M('menu')->where(array('parentid'=>I('id')))->delete();//删除子菜单
			if($rst!==false){
				$rst=M('menu')->where(array('id'=>I('id')))->delete();//删除自身菜单
				if($rst!==false){
                    //判断其父菜单是否还存在子菜单，如无子菜单，且父菜单类型为1
                    if($parentid && $arr['menu_type']==1){
                        $child=M('menu')->where(array('parentid'=>$parentid))->select();
                        if(empty($child)){
                            M('menu')->where(array('id'=>$parentid))->setField('menu_type' , 3);
                        }
                    }
                    $this->success('菜单删除成功',U('news_menu_list'),1);
				}else{
					$this -> error("菜单删除失败！",U('news_menu_list'),0);
				}
			}else{
				$this -> error("菜单删除失败！",U('news_menu_list'),0);
			}
		}else{
			$rst=M('menu')->where(array('id'=>I('id')))->delete();//无子菜单，删除自身
			if($rst!==false){
                //判断其父菜单是否还存在子菜单，如无子菜单，且父菜单类型为1
                if($parentid && $arr['menu_type']==1){
                    $child=M('menu')->where(array('parentid'=>$parentid))->select();
                    if(empty($child)){
                        M('menu')->where(array('id'=>$parentid))->setField('menu_type' , 3);
                    }
                }
                $this->success('菜单删除成功',U('news_menu_list'),1);
			}else{
				$this -> error("菜单删除失败！",U('news_menu_list'),0);
			}
		}
	}



	public function news_menu_order(){
		if (!IS_AJAX){
			$this->error('提交方式不正确',U('news_menu_list'),0);
		}else{
			$menu=M('menu');
			foreach ($_POST as $id => $sort){
				$menu->where(array('id' => $id ))->setField('listorder' , $sort);
			}
			$this->success('排序更新成功',U('news_menu_list'),1);
		}
	}


	public function news_menu_state(){
		$id=I('x');
		$status=M('menu')->where(array('id'=>$id))->getField('menu_open');//判断当前状态情况
		if($status==1){
			$statedata = array('menu_open'=>0);
			$auth_group=M('menu')->where(array('id'=>$id))->setField($statedata);
			$this->success('状态禁止',1,1);
		}else{
			$statedata = array('menu_open'=>1);
			$auth_group=M('menu')->where(array('id'=>$id))->setField($statedata);
			$this->success('状态开启',1,1);
		}
	}

	public function news_menu_edit(){
		$menu=M('menu')->where(array('id'=>I('id')))->find();
		$this->assign('menu',$menu);
        $this->assign('tpls',$this->tpls);
		$this->display();
	}


	public function news_menu_runedit(){
		if (!IS_AJAX){
			$this->error('提交方式不正确',U('news_menu_list'),0);
		}else{
			$file=I('file1');//获取图片路径
			$checkpic=I('checkpic');
			$oldcheckpic=I('oldcheckpic');
			$img_url='';
			if ($checkpic!=$oldcheckpic){
				//获取图片上传后路径
				$upload = new \Think\Upload();// 实例化上传类
				$upload->maxSize   =     3145728 ;// 设置附件上传大小
				$upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
				$upload->rootPath  =     C('UPLOAD_DIR'); // 设置附件上传根目录
				$upload->savePath  =     ''; // 设置附件上传（子）目录
				$upload->saveRule  =     'time';
				$info   =   $upload->upload();

				if($info) {
					$img_url=substr(C('UPLOAD_DIR'),1).$info[file0][savepath].$info[file0][savename];//如果上传成功则完成路径拼接
				}else{
					$this->error($upload->getError(),U('news_menu_list'),0);//否则就是上传错误，显示错误原因
				}
			}
			$data=array(
				'id'=>I('id'),
				'menu_name'=>I('menu_name'),
				'menu_enname'=>I('menu_enname'),
				'menu_type'=>I('menu_type'),
				'parentid'=>I('parentid'),
				'menu_listtpl'=>I('menu_listtpl'),
				'menu_newstpl'=>I('menu_newstpl'),
				'menu_address'=>I('menu_address'),
				'menu_open'=>I('menu_open',0),
				'listorder'=>I('listorder'),
				'menu_seo_title'=>I('menu_seo_title'),
				'menu_seo_key'=>I('menu_seo_key'),
				'menu_seo_des'=>I('menu_seo_des'),
				'menu_content'=>htmlspecialchars_decode(I('menu_content')),
			);
			if ($checkpic!=$oldcheckpic){
				$data['menu_img']=$img_url;
			}
			$rst=M('menu')->save($data);
			if($rst!==false){
				$this->success('菜单修改成功',U('news_menu_list'),1);
			}else{
				$this->error('菜单修改失败',U('news_menu_list'),0);
			}
		}
	}

}