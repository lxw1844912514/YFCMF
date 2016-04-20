<?php
namespace Admin\Controller;
use Common\Controller\AuthController;

class NewsController extends AuthController {

	/************************************************文章管理**************************************************/
	//文章列表
	public function news_list(){
		$keytype=I('keytype',news_title);
		$key=I('key');
		$opentype_check=I('opentype_check','');
		$diyflag=I('diyflag','');
		//查询：时间格式过滤
		$sldate=I('reservation','');//获取格式 2015-11-12 - 2015-11-18
		$arr = explode(" - ",$sldate);//转换成数组
		$arrdateone=strtotime($arr[0]);
		$arrdatetwo=strtotime($arr[1].' 23:55:55');
		//map架构查询条件数组
		$map['news_back']= 0;
		$map[$keytype]= array('like',"%".$key."%");
		if ($opentype_check!=''){
			$map['news_open']= array('eq',$opentype_check);
		}
		$map['news_time'] = array(array('egt',$arrdateone),array('elt',$arrdatetwo),'AND');
		if ($diyflag){
			$map[] ="FIND_IN_SET('$diyflag',news_flag)";
		}
		//p($map);die;
		$count= M('news')->where($map)->count();// 查询满足要求的总记录数
		$Page= new \Think\Page($count,C('DB_PAGENUM'));// 实例化分页类 传入总记录数和每页显示的记录数
		$show= $Page->show();// 分页显示输出
		$this->assign('page',$show);
				$listRows=(intval(C('DB_PAGENUM'))>0)?C('DB_PAGENUM'):20;
		if($count>$listRows){
			$Page->setConfig('theme','<div class=pagination><ul> %upPage% %downPage% %first%  %prePage%  %linkPage%  %nextPage% %end%</ul></div>');
		}
		$show= $Page->show();// 分页显示输出
		$this->assign('page_min',$show);
		$news=D('News')->where($map)->limit($Page->firstRow.','.$Page->listRows)->order('news_time desc')->relation(true)->select();
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
		$column=M('column');
		$diyflag=M('diyflag');
		$nav = new \Org\Util\Leftnav;
		$column_next=$column->where('column_type <> 5 and column_type <> 2')-> order('column_order') -> select();
		$diyflag=$diyflag->select();
		$arr = $nav::column($column_next);
		$source=M('source')->select();
		$this->assign('source',$source);
		$this->assign('column',$arr);
		$this->assign('diyflag',$diyflag);
		$this->display();
	}

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
						$img_url=$file[savepath].$file[savename];//如果上传成功则完成路径拼接
					}else{
						$picall=$file[savepath].$file[savename];//如果上传成功则完成路径拼接
						$picall_url=$picall.','.$picall_url;
					}
				}


			}else{
				$this->error($upload->getError());//否则就是上传错误，显示错误原因
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
			'news_content'=>I('news_content'),
			'news_auto'=>session('admin_realname'),
			'news_time'=>time(),
			'news_hits'=>200,
		);

		$news->add($sl_data);
		$this->success('文章添加成功,返回列表页',U('news_list'),1);
	}

	public function news_edit(){
		$n_id = I('n_id','',htmlspecialchars);
		if (empty($n_id)){
			$this->error('参数错误',U('news_list'),0);
		}else{
			$news_list=M('news')->where(array('n_id'=>I('n_id')))->find();
			/*
			 * 多图字符串转换成数组
			 */
			$text = $news_list['news_pic_allurl'];
			$newstr = substr($text,0,strlen($text)-1);
			$pic_list = explode(",", $newstr);
			$this->assign('pic_list',$pic_list);

			$column=M('column');
			$diyflag=M('diyflag');
			$nav = new \Org\Util\Leftnav;
			$column_next=$column->where('column_type <> 5 and column_type <> 2')-> order('column_order') -> select();
			$diyflag=$diyflag->select();
			$arr = $nav::column($column_next);
			$source=M('source')->select();//来源
			$this->assign('source',$source);
			$this->assign('column',$arr);
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
						$img_url=$file[savepath].$file[savename];//如果上传成功则完成路径拼接
					}else{//多图上传路径
						$picall=$file[savepath].$file[savename];//如果上传成功则完成路径拼接
						$picall_url=$picall.','.$picall_url;//循环拼凑成字符串
					}
				}
			}else{
				$this->error($upload->getError());//否则就是上传错误，显示错误原因
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
			'news_content'=>I('news_content'),
		);
		if ($checkpic!=$oldcheckpic){
			$sl_data['news_img']=$img_url;
		}
		$sl_data['news_pic_allurl']=$picall_list;
		$news->save($sl_data);
		$this->success('文章修改成功,返回列表页',U('news_list'),1);
	}

	public function news_del(){
		$p=I('p');
		$rst=M('news')->where(array('n_id'=>I('n_id')))->setField('news_back',1);//转入回收站
		if($rst){
			$this->success('文章已转入回收站',U('news_list',array('p' => $p)),1);
		}else{
			$this -> error("删除文章失败！");
		}
	}
	public function news_alldel(){
		$p = I('p');
		$ids = I('n_id','',htmlspecialchars);
		if(empty($ids)){
			$this -> error("请选择删除文章");//判断是否选择了文章ID
		}
		$model = D('news');
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
		if($rst){
			$this->success('文章还原成功',U('news_back',array('p' => $p)),1);
		}else{
			$this -> error("文章还原失败！");
		}
	}

	public function news_back(){
		$keytype=I('keytype',news_title);
		$key=I('key');
		$opentype_check=I('opentype_check','');
		$diyflag=I('diyflag','');
		//查询：时间格式过滤
		$sldate=I('reservation','');//获取格式 2015-11-12 - 2015-11-18
		$arr = explode(" - ",$sldate);//转换成数组
		$arrdateone=strtotime($arr[0]);
		$arrdatetwo=strtotime($arr[1].' 23:55:55');
		//map架构查询条件数组
		$map['news_back']= 1;
		$map[$keytype]= array('like',"%".$key."%");
		if ($opentype_check!=''){
			$map['news_open']= array('eq',$opentype_check);
		}
		$map['news_time'] = array(array('egt',$arrdateone),array('elt',$arrdatetwo),'AND');
		if ($diyflag){
			$map[] ="FIND_IN_SET('$diyflag',news_flag)";
		}
		//p($map);die;
		$count= M('news')->where($map)->count();// 查询满足要求的总记录数
		$Page= new \Think\Page($count,C('DB_PAGENUM'));// 实例化分页类 传入总记录数和每页显示的记录数
		$show= $Page->show();// 分页显示输出
		$this->assign('page',$show);
		$listRows=(intval(C('DB_PAGENUM'))>0)?C('DB_PAGENUM'):20;
		if($count>$listRows){
			$Page->setConfig('theme','<div class=pagination><ul> %upPage% %downPage% %first%  %prePage%  %linkPage%  %nextPage% %end%</ul></div>');
		}
		$show= $Page->show();// 分页显示输出
		$this->assign('page_min',$show);
		$news=D('News')->where($map)->limit($Page->firstRow.','.$Page->listRows)->order('news_time desc')->relation(true)->select();
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
		if (empty($n_id)){
			$this->error('参数错误',U('news_back'),0);
		}else{
			$rst=M('news')->where(array('n_id'=>I('n_id')))->delete();
			if($rst){
				$this->success('文章彻底删除成功',U('news_back',array('p' => $p)),1);
			}else{
				$this -> error("文章彻底删除失败！");
			}
		}
	}

	public function news_back_alldel(){
		$p = I('p');
		$ids = I('n_id','',htmlspecialchars);
		if(empty($ids)){
			$this -> error("请选择删除文章");//判断是否选择了文章ID
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



	/************************************************栏目管理**************************************************/
	//栏目管理
	public function news_column_list(){
		$column=M('column');
		$nav = new \Org\Util\Leftnav;
		$column=$column->order('column_order')->select();
		$arr = $nav::column($column);
		$this->assign('arr',$arr);
		$this->display();
	}

	//添加栏目
	public function news_column_add(){
		$column_leftid=I('c_id',0);
		$this->assign('column_leftid',$column_leftid);
		$this->display();
	}

	public function news_column_runadd(){
		if (!IS_AJAX){
			$this->error('提交方式不正确',U('news_column_list'),0);
		}else{
			$data=array(
				'column_name'=>I('column_name'),
				'column_enname'=>I('column_enname'),
				'column_type'=>I('column_type'),
				'column_leftid'=>I('column_leftid'),
				'column_address'=>I('column_address'),
				'column_open'=>I('column_open',0),
				'column_order'=>I('column_order'),
				'column_title'=>I('column_title'),
				'column_key'=>I('column_key'),
				'column_des'=>I('column_des'),
				'column_content'=>I('column_content'),
			);
			M('column')->add($data);
			$this->success('栏目保存成功',U('news_column_list'),1);
		}
	}

	//删除栏目
	public function news_column_del(){
		M('column')->where(array('c_id'=>I('c_id')))->delete();
		$rst=M('column')->where(array('column_leftid'=>I('c_id')))->delete();
		if($rst){
			$this->success('栏目删除成功',U('news_column_list'),1);
		}else{
			$this -> error("栏目删除失败！");
		}
	}



	public function news_column_order(){
		if (!IS_AJAX){
			$this->error('提交方式不正确',U('news_column_list'),0);
		}else{
			$column=M('column');
			foreach ($_POST as $id => $sort){
				$column->where(array('c_id' => $id ))->setField('column_order' , $sort);
			}
			$this->success('排序更新成功',U('news_column_list'),1);
		}
	}


	public function news_column_state(){
		$id=I('x');
		$status=M('column')->where(array('c_id'=>$id))->getField('column_open');//判断当前状态情况
		if($status==1){
			$statedata = array('column_open'=>0);
			$auth_group=M('column')->where(array('c_id'=>$id))->setField($statedata);
			$this->success('状态禁止',1,1);
		}else{
			$statedata = array('column_open'=>1);
			$auth_group=M('column')->where(array('c_id'=>$id))->setField($statedata);
			$this->success('状态开启',1,1);
		}
	}

	public function news_column_edit(){
		$column=M('column')->where(array('c_id'=>I('c_id')))->find();
		$this->assign('column',$column);
		$this->display();
	}


	public function news_column_runedit(){
		if (!IS_AJAX){
			$this->error('提交方式不正确',U('news_column_list'),0);
		}else{
			$data=array(
				'c_id'=>I('c_id'),
				'column_name'=>I('column_name'),
				'column_enname'=>I('column_enname'),
				'column_type'=>I('column_type'),
				'column_leftid'=>I('column_leftid'),
				'column_address'=>I('column_address'),
				'column_open'=>I('column_open',0),
				'column_order'=>I('column_order'),
				'column_title'=>I('column_title'),
				'column_key'=>I('column_key'),
				'column_des'=>I('column_des'),
				'column_content'=>I('column_content'),
			);
			M('column')->save($data);
			$this->success('栏目保存成功',U('news_column_list'),1);
		}
	}

}