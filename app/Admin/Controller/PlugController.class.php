<?php
namespace Admin\Controller;
use Common\Controller\AuthController;
use Think\Storage;
class PlugController extends AuthController {
		private $files_res_exists;
		private $files_res_used;
		private $files_unused;
	/*
     * 友情链接列表
     */
	public function plug_link_list(){
		$type=I('type');
		$val=I('val');
		$map=array();
		if (!empty($type)){
			$map['plug_link_typeid']=  array('eq',I('type'));
		}
		if (!empty($val)){
			$map['plug_link_name|plug_link_url'] = array('like',"%".$val."%");
		}
		$link_type=M('plug_linktype')->select();
		$plug_link=D('Plug_link')->where($map)->order('plug_link_addtime desc')->relation(true)->select();
		$this->assign('plug_link',$plug_link);
		$this->assign('link_type',$link_type);
		$this->assign('val',$val);
		$this->display();
	}


	/*
     * 友情链接添加操作
     */
	public function plug_link_runadd(){
		if (!IS_AJAX){
			$this->error('提交方式不正确',0,0);
		}else{
			$sl_data=array(
				'plug_link_name'=>I('plug_link_name'),
				'plug_link_url'=>I('plug_link_url'),
				'plug_link_typeid'=>I('plug_link_typeid'),
				'plug_link_qq'=>I('plug_link_qq'),
				'plug_link_order'=>I('plug_link_order'),
				'plug_link_addtime'=>time(),
				'plug_link_open'=>I('plug_link_open'),
			);
			M('plug_link')->add($sl_data);
			$this->success('友情链接添加成功',U('plug_link_list'),1);
		}
	}

	/*
     * 友情链接删除操作
     */
	public function plug_link_del(){
		$p=I('p');
		M('plug_link')->where(array('plug_link_id'=>I('plug_link_id')))->delete();
		$this->redirect('plug_link_list', array('p' => $p));
	}

	/*
     * 友情链接状态操作
     */
	public function plug_link_state(){
		$id=I('x');
		$status=M('plug_link')->where(array('plug_link_id'=>$id))->getField('plug_link_open');//判断当前状态情况
		if($status==1){
			$statedata = array('plug_link_open'=>0);
			$auth_group=M('plug_link')->where(array('plug_link_id'=>$id))->setField($statedata);
			$this->success('状态禁止',1,1);
		}else{
			$statedata = array('plug_link_open'=>1);
			$auth_group=M('plug_link')->where(array('plug_link_id'=>$id))->setField($statedata);
			$this->success('状态开启',1,1);
		}
	}

	/*
     * 友情链接修改返回值操作
     */
	public function plug_link_edit(){
		$plug_link_id=I('plug_link_id');
		$plug_link=M('plug_link')->where(array('plug_link_id'=>$plug_link_id))->find();
		$sl_data['plug_link_id']=$plug_link['plug_link_id'];
		$sl_data['plug_link_name']=$plug_link['plug_link_name'];
		$sl_data['plug_link_url']=$plug_link['plug_link_url'];
		$sl_data['plug_link_qq']=$plug_link['plug_link_qq'];
		$sl_data['plug_link_order']=$plug_link['plug_link_order'];
		$sl_data['plug_link_open']=$plug_link['plug_link_open'];
		$sl_data['plug_link_typeid']=$plug_link['plug_link_typeid'];
		$sl_data['status']=1;
		$this->ajaxReturn($sl_data,'json');
	}

	/*
     * 友情 链接修改操作
     */
	public function plug_link_runedit(){
		if (!IS_AJAX){
			$this->error('提交方式不正确',0,0);
		}else{
			$sl_data=array(
				'plug_link_id'=>I('plug_link_id'),
				'plug_link_name'=>I('plug_link_name'),
				'plug_link_url'=>I('plug_link_url'),
				'plug_link_typeid'=>I('plug_link_typeid'),
				'plug_link_qq'=>I('plug_link_qq'),
				'plug_link_order'=>I('plug_link_order'),

			);
			M('plug_link')->save($sl_data);
			$this->success('友情链接修改成功',U('plug_link_list'),1);
		}
	}

	/**********************************************友情链接所属栏目***********************************************************/
	/*
     * 友情链接类型列表
     */
	public function plug_linktype_list(){
		$link_type=M('plug_linktype')->select();
		$this->assign('link_type',$link_type);
		$this->display();
	}
	/*
     * 友情链接类型删除
     */
	public function plug_linktype_del(){
		$link_type=M('plug_linktype')->where(array('plug_linktype_id'=>I('plug_linktype_id')))->delete();
		$this->redirect('plug_linktype_list');
	}

	/*
     * 友情链接类型添加
     */
	public function plug_linktype_runadd(){
		$plug_linktype=M('plug_linktype');
		$plug_linktype->add($_POST);
		$this->success('栏目添加成功',U('plug_linktype_list'),1);
	}

	/*
     * 友情链接类型修改
     */
	public function plug_linktype_runedit(){
		$plug_linktype=M('plug_linktype');
		$sl_data=array(
			'plug_linktype_id'=>I('plug_linktype_id'),
			'plug_linktype_name'=>I('plug_linktype_name'),
			'plug_linktype_order'=>I('plug_linktype_order'),
		);
		$plug_linktype->save($sl_data);
		$this->success('友情链接栏目修改成功',U('plug_linktype_list'),1);
	}

	/*
     * 友情链接排序
     */
	public function plug_linktype_order(){
		if (!IS_AJAX){
			$this->error('提交方式不正确',0,0);
		}else{
			$plug_linktype=M('plug_linktype');
			foreach ($_POST as $plug_linktype_id => $plug_linktype_order){
				$plug_linktype->where(array('plug_linktype_id' => $plug_linktype_id ))->setField('plug_linktype_order' , $plug_linktype_order);
			}
			$this->success('排序更新成功',U('plug_linktype_list'),1);
		}
	}
	/**********************************************广告设置***********************************************************/

	/*
     * 广告管理
     */
	public function plug_ad_list(){
		$key=I('key');
		$map['plug_ad_name'] = array('like',"%".$key."%");

		$count= M('plug_ad')->where($map)->count();// 查询满足要求的总记录数
		$Page= new \Think\Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数
		$show= $Page->show();// 分页显示输出

		$plug_adtype_list=M('plug_adtype')->order('plug_adtype_order')->select();//获取所有广告位
		$plug_ad_list=M('plug_ad')->where($map)->limit($Page->firstRow.','.$Page->listRows)->order('plug_ad_order')->select();
		$this->assign('plug_ad_list',$plug_ad_list);
		$this->assign('plug_adtype_list',$plug_adtype_list);
		$this->assign('page',$show);
		$this->assign('val',$key);
		$this->display();
	}

	/*
     * 添加广告操作
     */
	public function plug_ad_runadd(){
		if (!IS_AJAX){
			$this->error('提交方式不正确',0,0);
		}else{
			//处理图片
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
				$img_url=C('UPLOAD_DIR').$info[file0][savepath].$info[file0][savename];//如果上传成功则完成路径拼接

			}elseif(!$file){
				$img_url='';//否则如果字段为空，表示没有上传任何文件，赋值空
			}else{
				$this->error($upload->getError());//否则就是上传错误，显示错误原因
			}
			//构建数组

			$sl_data=array(
				'plug_ad_adtypeid'=>I('plug_ad_adtypeid'),
				'plug_ad_name'=>I('plug_ad_name'),
				'plug_ad_pic'=>$img_url,
				'plug_ad_url'=>I('plug_ad_url'),
				'plug_ad_checkid'=>I('plug_ad_checkid'),
				'plug_ad_js'=>I('plug_ad_js'),
				'plug_ad_open'=>I('plug_ad_open'),
				'plug_ad_order'=>I('plug_ad_order'),
				'plug_ad_addtime'=>time(),
				//plug_ad_depid
				//plug_ad_butt 数据库中为预留字段
			);

			M('plug_ad')->add($sl_data);
			$this->success('广告添加成功',U('plug_ad_list'),1);
		}
	}

	/*
     * 广告删除
     */
	public function plug_ad_del(){
		$plug_ad_id=I('plug_ad_id');
		M('plug_ad')->where(array('plug_ad_id'=>$plug_ad_id))->delete();
		$this->redirect('plug_ad_list', array('p' => $p));
	}

	/*
     * 批量排序
     */
	public function plug_ad_order(){
		if (!IS_AJAX){
			$this->error('提交方式不正确',0,0);
		}else{
			$plug_ad=M('plug_ad');
			foreach ($_POST as $id => $sort){
				$plug_ad->where(array('plug_ad_id' => $id ))->setField('plug_ad_order' , $sort);
			}
			$this->success('广告排序更新成功',U('plug_ad_list'),1);
		}
	}

	/*
     * 广告状态
     */
	public function plug_ad_state(){
		$id=I('x');
		$status=M('plug_ad')->where(array('plug_ad_id'=>$id))->getField('plug_ad_open');//判断当前状态情况
		if($status==1){
			$statedata = array('plug_ad_open'=>0);
			M('plug_ad')->where(array('plug_ad_id'=>$id))->setField($statedata);
			$this->success('状态禁止',1,1);
		}else{
			$statedata = array('plug_ad_open'=>1);
			M('plug_ad')->where(array('plug_ad_id'=>$id))->setField($statedata);
			$this->success('状态开启',1,1);
		}
	}

	/*
     * 广告位修改操作
     */
	public function plug_ad_edit(){
		$plug_adtype_list=M('plug_adtype')->select();
		$plug_ad_id=I('plug_ad_id');
		$plug_ad=M('plug_ad')->where(array('plug_ad_id'=>$plug_ad_id))->find();
		$this->assign('plug_adtype_list',$plug_adtype_list);
		$this->assign('plug_ad',$plug_ad);
		$this->display();

	}

	/*
     * 修改广告操作
     */
	public function plug_ad_runedit(){
		if (!IS_AJAX){
			$this->error('提交方式不正确',0,0);
		}else{

			$file=I('file1');//获取图片路径
			$checkpic=I('checkpic');
			$oldcheckpic=I('oldcheckpic');

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
					$img_url=C('UPLOAD_DIR').$info[file0][savepath].$info[file0][savename];//如果上传成功则完成路径拼接
				}else{
					$this->error($upload->getError());//否则就是上传错误，显示错误原因
				}
			}

			$sl_data=array(
				'plug_ad_id'=>I('plug_ad_id'),
				'plug_ad_adtypeid'=>I('plug_ad_adtypeid'),
				'plug_ad_name'=>I('plug_ad_name'),
				'plug_ad_url'=>I('plug_ad_url'),
				'plug_ad_order'=>I('plug_ad_order'),
				'plug_ad_checkid'=>I('plug_ad_checkid'),
				'plug_ad_js'=>I('plug_ad_js'),
			);
			if ($checkpic!=$oldcheckpic){
				$sl_data['plug_ad_pic']=$img_url;
			}
			M('plug_ad')->save($sl_data);
			$this->success('广告设置修改成功',U('plug_ad_list'),1);
		}
	}

	/**********************************************广告位设置***********************************************************/

	/*
     * 广告位列表
     */
	public function plug_adtype_list(){

		$key=I('key');
		$map['plug_adtype_name '] = array('like',"%".$key."%");

		$count= M('plug_adtype')->where($map)->count();// 查询满足要求的总记录数
		$Page= new \Think\Page($count,10);// 实例化分页类 传入总记录数和每页显示的记录数
		$show= $Page->show();// 分页显示输出

		$plug_adtype_list=M('plug_adtype')->where($map)->limit($Page->firstRow.','.$Page->listRows)->order('plug_adtype_order')->select();
		$this->assign('plug_adtype_list',$plug_adtype_list);
		$this->assign('page',$show);
		$this->assign('val',$key);
		$this->display();
	}

	/*
     * 广告位添加操作
     */
	public function plug_adtype_runadd(){
		if (!IS_AJAX){
			$this->error('提交方式不正确',U('plug_adtype_list'),0);
		}else{
			M('plug_adtype')->add($_POST);
			$this->success('广告位保存成功',U('plug_adtype_list'),1);
		}
	}

	/*
     * 广告位修改操作
     */
	public function plug_adtype_edit(){
		if (!IS_AJAX){
			$this->error('提交方式不正确',U('plug_adtype_list'),0);
		}else{
			$plug_adtype_id=I('plug_adtype_id');
			$plug_adtype=M('plug_adtype')->where(array('plug_adtype_id'=>$plug_adtype_id))->find();

			$sl_data['plug_adtype_id']=$plug_adtype['plug_adtype_id'];
			$sl_data['plug_adtype_name']=$plug_adtype['plug_adtype_name'];
			$sl_data['plug_adtype_order']=$plug_adtype['plug_adtype_order'];
			$sl_data['status']=1;
			$this->ajaxReturn($sl_data,'json');
		}
	}

	/*
     * 广告位修改操作
     */
	public function plug_adtype_runedit(){
		if (!IS_AJAX){
			$this->error('提交方式不正确',0,0);
		}else{
			M('plug_adtype')->save($_POST);
			$this->success('广告位修改成功',U('plug_adtype_list'),1);
		}
	}

	/*
     * 广告位删除
     */
	public function plug_adtype_del(){
		M('plug_adtype')->where(array('plug_adtype_id'=>I('plug_ad_adtypeid')))->delete();//删除广告位
		M('plug_ad')->where(array('plug_ad_adtypeid'=>I('plug_ad_adtypeid')))->delete();//删除该广告位所有广告
		$this->redirect('plug_linktype_list', array('p' => $p));
	}

	/*
     * 广告位排序
     */
	public function plug_adtype_order(){
		if (!IS_AJAX){
			$this->error('提交方式不正确',0,0);
		}else{
			$plug_adtype=M('plug_adtype');
			foreach ($_POST as $id => $sort){
				$plug_adtype->where(array('plug_adtype_id' => $id ))->setField('plug_adtype_order' , $sort);
			}
			$this->success('广告位排序更新成功',U('plug_adtype_list'),1);
		}
	}


	/**********************************************留言设置***********************************************************/

	/*
     * 留言列表
     */
	public function plug_sug_list(){
		$plug_sug=M('plug_sug')->select();
		$this->assign('plug_sug',$plug_sug);
		$this->display();
	}

	public function plug_sug_edit(){
		$plug_sug_id=I('plug_sug_id');
		$plug_link=M('plug_sug')->where(array('plug_sug_id'=>plug_sug_id))->find();
		$sl_data['plug_sug_id']=$plug_link['plug_sug_id'];
		$sl_data['plug_sug_id']=$plug_link['plug_sug_title'];
		$sl_data['plug_sug_email']=$plug_link['plug_sug_email'];
		$sl_data['plug_sug_addtime']=$plug_link['plug_sug_addtime'];
		$sl_data['plug_sug_content']=$plug_link['plug_sug_content'];
		$sl_data['status']=1;
		$this->ajaxReturn($sl_data,'json');
	}

	/**********************************************文件设置***********************************************************/
	public function plug_file_list(){
        $map=array();
        //查询：时间格式过滤
        $sldate=I('reservation','');//获取格式 2015-11-12 - 2015-11-18
        $arr = explode(" - ",$sldate);//转换成数组
        if(count($arr)==2){
            $arrdateone=strtotime($arr[0]);
            $arrdatetwo=strtotime($arr[1].' 23:55:55');
            $map['uptime'] = array(array('egt',$arrdateone),array('elt',$arrdatetwo),'AND');
        }
        //查询文件路径
        $val=I('val');
        if(!empty($val)){
            $map['path']= array('like',"%".$val."%");
        }
		$count=M('plug_files')->where($map)->count();
		$Page= new \Think\Page($count,8);// 实例化分页类 传入总记录数和每页显示的记录数
		$show= $Page->show();// 分页显示输出
		$plug_files=M('plug_files')->where($map)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->assign('plug_files',$plug_files);
		$this->assign('page',$show);
		$this->display();
	}
	public function plug_file_filter(){
		//获取本地文件数组，'./data/upload/2016-01-21/56a03ff96b6ff.jpg' => int 224138
		$file_list=list_file('data/upload');
		$path="/data/upload/";
		$this->files_res_exists=array();
		foreach ($file_list as $a){
			if ($a ['isDir']) {
				foreach (list_file($a ['pathname'] . '/') as $d) {
					if (!$d ['isDir']) {
						//文件
						if($d['ext']!='html' && $d['ext']!='lock'){
							$this->files_res_exists ['.'.$path . $a ['filename'] . '/' . $d ['filename']] = $d ['size'];
						}
					}
				}
			}
		}
		//获取数据表datafile已存记录，并删除资源数组里的成员，完毕后得到未存入数据表datafile的资源文件
		$datas = M('plug_files')->select();
		if (is_array($datas)) {
			foreach ($datas as &$d) {
				$f = $d ['path'];
				if (isset ($this->files_res_exists [$f])) {
					unset ($this->files_res_exists [$f]);
				}
			}
		}
		//未存入数据表的数据写入数据表
		$time=time();
		foreach ($this->files_res_exists as $d => $v) {
			M('plug_files')->add(array(
				'path' => $d,
				'uptime' => $time,
				'filesize' => $v
			));
		}
		//获取利用到的资源文件
		$this->files_res_used=array();
		//avatar,涉及表admin里字段admin_avatar，member_list里member_list_headpic,头像只保存头像图片名
		$datas = M('admin')->select();
		if (is_array($datas)) {
			foreach ($datas as &$d) {
				if($d['admin_avatar']){
					if(stripos($d['admin_avatar'],'http')===false){
						//本地头像
						$this->files_res_used['./data/upload/avatar/' . $d['admin_avatar']]=true;
					}
				}
			}
		}
		$datas = M('member_list')->select();
		if (is_array($datas)) {
			foreach ($datas as &$d) {
				if($d['member_list_headpic']){
					if(stripos($d['member_list_headpic'],'http')===false){
						//本地头像
						$this->files_res_used['./data/upload/avatar/' . $d['member_list_headpic']]=true;
					}
				}
			}
		}
		//news里的news_img,news_pic_allurl,news_content
		$datas = M('news')->select();
		if (is_array($datas)) {
			foreach ($datas as &$d) {
				//字段保存'./data/....'
				if($d['news_img']){
					if(stripos($d['news_img'],'http')===false){
						$this->files_res_used[$d['news_img']]=true;
					}
				}
				//字段保存'./data/....'
				if($d['news_pic_allurl']){
					$imgs=explode(",",$d['news_pic_allurl']);
					foreach ($imgs as &$f) {
						if(stripos($f,'http')===false && !empty($f)){
							$this->files_res_used[$f]=true;
						}
					}
				}
				if($d['news_content']){
					//匹配'/网站目录/data/....'
					preg_match_all(__ROOT__.'\/data\/upload\/([0-9]{4}[-][0-9]{2}[-][0-9]{2}\/[a-z0-9]{13}\.[a-z0-9]+)/i', $d['news_content'], $mat);
                    foreach ($mat [1] as &$f) {
                        $this->files_res_used['./data/upload/'.$f]=true;
                    }
					//匹配'./data/....'
					preg_match_all('/\.\/data\/upload\/([0-9]{4}[-][0-9]{2}[-][0-9]{2}\/[a-z0-9]{13}\.[a-z0-9]+)/i', $d['news_content'], $mat);
                    foreach ($mat [1] as &$f) {
                        $this->files_res_used['./data/upload/'.$f]=true;
                    }
				}
			}
		}
		//options里'option_name'=>'site_options'的site_logo、site_qr,字段保存'./data/....'
		$datas = M('options')->where(array('option_name'=>'site_options'))->select();
		if (is_array($datas)) {
			foreach ($datas as &$d) {
				if($d['option_value']){
					$smeta=json_decode($d['option_value'],true);
					if($smeta['site_logo'] && stripos($smeta['site_logo'],'http')===false){
						$this->files_res_used[$smeta['site_logo']]=true;
					}
					if($smeta['site_qr'] && stripos($smeta['site_qr'],'http')===false){
						$this->files_res_used[$smeta['site_qr']]=true;
					}
				}
			}
		}		
		//plug_ad里plug_ad_pic,字段保存'./data/....'
		$datas = M('plug_ad')->select();
		if (is_array($datas)) {
			foreach ($datas as &$d) {
				if($d['plug_ad_pic']){
					if(stripos($d['plug_ad_pic'],'http')===false){
						//本地图片
						$this->files_res_used[$d['plug_ad_pic']]=true;
					}
				}
			}
		}
        //dump($this->files_res_used);
        //dump($this->files_res_exists);
        //找出未使用的资源文件
        $this->files_unused=array();
        $ids=array();
        $datas = M('plug_files')->select();
        if (is_array($datas)) {
            foreach ($datas as &$d) {
                $f = $d ['path'];
                if (isset ($this->files_res_used[$f])) {
                    unset ($this->files_res_used[$f]);
                } else {
                    $ids[]=$d ['id'];
                    $this->files_unused [] = array(
                        'id' => $d ['id'],
                        'filesize' =>$d['filesize'],
                        'path' => $f,
                        'uptime' => $d ['uptime']
                    );
                }
            }
        }
        //数据库
		$where=array();
		$plug_files=array();
		if(!empty($ids)){
			$where['id']=array('in',$ids);
			$count=M('plug_files')->where($where)->count();
			$Page= new \Think\Page($count,8);// 实例化分页类 传入总记录数和每页显示的记录数
			$show= $Page->show();// 分页显示输出
			$plug_files=M('plug_files')->where($where)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
			$this->assign('plug_files',$plug_files);
			$this->assign('page',$show);
		}
        $this->display();
	}
	public function plug_file_alldel(){
		$p = I('p');
		$ids = I('id');
		if(empty($ids)){
			$this -> error("请选择要删除的文件");
		}
		if(is_array($ids)){
			$where = 'id in('.implode(',',$ids).')';
			foreach (M('plug_files')->field('path')->where($where)->select() as $r) {
				$file = $r ['path'];
				if(stripos($file, "/")===0){
					$file=substr($file,1);
				}
				if (Storage::has($file)) {
					Storage::unlink($file);
				}
			}
			if (M('plug_files')->where($where)->delete()!==false) {
				$this->success("删除文件成功！",U('plug_file_filter',array('p'=>$p)),1);
			} else {
				$this->error("删除文件失败！");
			}
		}else{
			$r=M('plug_files')->find($ids);
			if($r){
				$file=$r['path'];
				if(stripos($file, "/")===0){
					$file=substr($file,1);
				}
				if (Storage::has($file)) {
					Storage::unlink($file);
				}
				if (M('plug_files')->delete($ids)!==false) {
					$this->success("删除文件成功！",U('plug_file_filter',array('p'=>$p)),1);
				}else{
					$this->error("删除文件失败！");
				}
			}else{
				$this->error("删除文件失败！");
			}
		}
	}
	public function plug_file_del(){
		$id=I('id');
		$p = I('p');
		if (empty($id)){
			$this->error('参数错误',U('plug_file_filter'),0);
		}else{
			$r=M('plug_files')->find($id);
			if($r){
				$file=$r['path'];
				if(stripos($file, "/")===0){
					$file=substr($file,1);
				}
				if (Storage::has($file)) {
					Storage::unlink($file);
				}
				if (M('plug_files')->delete($id)!==false) {
					$this->success("删除文件成功！",U('plug_file_filter',array('p'=>$p)),1);
				}else{
					$this->error("删除文件失败！");
				}
			}else{
				$this -> error("文件删除失败！");
			}
		}
	}
}