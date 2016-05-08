<?php
// +----------------------------------------------------------------------
// | YFCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2015-2016 http://www.rainfer.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: rainfer <81818832@qq.com>
// +----------------------------------------------------------------------
namespace Admin\Controller;
use Common\Controller\CommonController;
class UeditorController extends CommonController {
	protected $config;
	function _initialize() {
		parent::_initialize();
		$adminid=session('aid');
		$userid=session('hid');
		if(empty($adminid) && empty($userid)){
			exit("非法上传！");
		}
	}
	function upload(){
		date_default_timezone_set("Asia/chongqing");
		error_reporting(E_ERROR);
		header("Content-Type: text/html; charset=utf-8");		
		$CONFIG = json_decode(preg_replace("/\/\*[\s\S]+?\*\//", "", file_get_contents("./public/ueditor/config.json")), true);
		$this->config=$CONFIG;
		$action = $_GET['action'];
		switch ($action) {
			case 'config':
				$result =  json_encode($CONFIG);
				break;
		
				/* 上传图片 */
			case 'uploadimage':
				$result = $this->_ueditor_upload();
				break;
				/* 上传涂鸦 */
			case 'uploadscrawl':
				$result = $this->_ueditor_upload_scrawl();
				break;
				/* 上传视频 */
			case 'uploadvideo':
				$result = $this->_ueditor_upload(array('maxSize' => 1073741824,/*1G*/'exts'=>array('mp4', 'avi', 'wmv','rm','rmvb','mkv')));
				break;
				/* 上传文件 */
			case 'uploadfile':
				$result = $this->_ueditor_upload(array('exts'=>array('jpg', 'gif', 'png', 'jpeg','txt','pdf','doc','docx','xls','xlsx','zip','rar','ppt','pptx',)));
				break;
		
				/* 列出图片 */
			case 'listimage':
				/* 列出文件 */
			case 'listfile':
				$result = $this->_ueditor_list($action);
				break;		
				/* 抓取远程文件 */
			case 'catchimage':
				$result = $this->_ueditor_upload_catch();
				break;
		
			default:
				$result = json_encode(array('state'=> '请求地址出错'));
				break;
		}
		
		/* 输出结果 */
		if (isset($_GET["callback"]) && false ) {
			//TODO 跨域上传
			if (preg_match("/^[\w_]+$/", $_GET["callback"])) {
				echo htmlspecialchars($_GET["callback"]) . '(' . $result . ')';
			} else {
				echo json_encode(array(
						'state'=> 'callback参数不合法'
				));
			}
		} else {
			exit($result) ;
		}
	}
	private function _ueditor_list($action){
		/* 判断类型 */
		switch ($action) {
			/* 列出文件 */
			case 'listfile':
				$allowFiles = $this->config['fileManagerAllowFiles'];
				$listSize = $this->config['fileManagerListSize'];
				break;
			/* 列出图片 */
			case 'listimage':
			default:
				$allowFiles = $this->config['imageManagerAllowFiles'];
				$listSize = $this->config['imageManagerListSize'];
		}
		$allowFiles = substr(str_replace(".", "|", join("", $allowFiles)), 1);
		/* 获取参数 */
		$size = isset($_GET['size']) ? htmlspecialchars($_GET['size']) : $listSize;
		$start = isset($_GET['start']) ? htmlspecialchars($_GET['start']) : 0;
		$end = $start + $size;
		/* 获取文件列表 */
		$path = $_SERVER['DOCUMENT_ROOT'] . C("TMPL_PARSE_STRING.__UPLOAD__");
		$files = $this->getfiles($path, $allowFiles);
		if (!count($files)) {
			return json_encode(array(
				"state" => "no match file",
				"list" => array(),
				"start" => $start,
				"total" => count($files)
			));
		}
		/* 获取指定范围的列表 */
		$len = count($files);
		for ($i = min($end, $len) - 1, $list = array(); $i < $len && $i >= 0 && $i >= $start; $i--){
			$list[] = $files[$i];
		}
		/* 返回数据 */
		$result = json_encode(array(
			"state" => "SUCCESS",
			"list" => $list,
			"start" => $start,
			"total" => count($files)
		));
		return $result;
	}
	/**
	 * 遍历获取目录下的指定类型的文件
	 * @author rainfer <81818832@qq.com>
	 * @param $path
	 * @param array $files
	 * @return array
	 */
	private function getfiles($path, $allowFiles, &$files = array())
	{
		if (!is_dir($path)) return null;
		if(substr($path, strlen($path) - 1) != '/') $path .= '/';
		$handle = opendir($path);
		while (false !== ($file = readdir($handle))) {
			if ($file != '.' && $file != '..') {
				$path2 = $path . $file;
				if (is_dir($path2)) {
					$this->getfiles($path2, $allowFiles, $files);
				} else {
					if (preg_match("/\.(".$allowFiles.")$/i", $file)) {
						$files[] = array(
							'url'=> substr($path2, strlen($_SERVER['DOCUMENT_ROOT'])),
							'mtime'=> filemtime($path2)
						);
					}
				}
			}
		}
		return $files;
	}	
	private function _ueditor_upload($config=array()){
		$date=date("Y-m-d");
		//上传处理类
		$mconfig=array(
				'rootPath' => C('UPLOAD_DIR'),
				'savePath' => "$date/",
				'maxSize' => 10485760,//10M
				'saveName'   =>    array('uniqid',''),
				'exts'       =>    array('jpg', 'gif', 'png', 'jpeg'),
				'autoSub'    =>    false,
		);
		
		if(is_array($config)){
			$config=array_merge($mconfig,$config);
		}else{
			$config=$mconfig;
		}
		$upload = new \Think\Upload($config);//
		
		$file = $title = $oriName = $state ='0';
		
		$info=$upload->upload();
		//开始上传
		if ($info) {
			//上传成功
			$title = $oriName = $_FILES['upfile']['name'];
			$size=$info['upfile']['size'];
		
			$state = 'SUCCESS';
			
			if(!empty($info['upfile']['url'])){
				$url=$info['upfile']['url'];
			}else{
				$url = C("TMPL_PARSE_STRING.__UPLOAD__")."$date/".$info['upfile']['savename'];
			}
			// if(strpos($url, "https")===0 || strpos($url, "http")===0){		
			// }else{//local
				// $host=(is_ssl() ? 'https' : 'http')."://".$_SERVER['HTTP_HOST'];
				// $url=$host.$url;
			// }
		} else {
			$state = $upload->getError();
            $url='';
		}
		
		$response=array(
				"state" => $state,
				"url" => $url,
				"title" => $title,
				"original" =>$oriName,
		);
		return json_encode($response);
	}
	private function _ueditor_upload_scrawl(){		
		$data = I('post.' . $this->config ['scrawlFieldName']);
        $url='';
        $title = '';
        $oriName = '';
		if (empty ($data)) {
			$state= 'Scrawl Data Empty!';
		} else {
			$img = base64_decode($data);
			$savepath = save_storage_content('png', $img);
			if ($savepath) {
				$state = 'SUCCESS';
				//$url = (is_ssl() ? 'https' : 'http')."://".$_SERVER['HTTP_HOST'].__ROOT__.'/'.$savepath;
				$url = __ROOT__.'/'.$savepath;
			} else {
				$state = 'Save scrawl file error!';
			}
		}
		$response=array(
		"state" => $state,
		"url" => $url,
		"title" => $title,
		"original" =>$oriName,
		);
		return json_encode($response);
	}
	private function _ueditor_upload_catch(){
		set_time_limit(0);
		$sret = array(
			'state' => '',
			'list' => null
		);
		$savelist = array();
		$flist = I('request.' . $this->config ['catcherFieldName']);
		if (empty ($flist)) {
			$sret ['state'] = 'ERROR';
		} else {
			$sret ['state'] = 'SUCCESS';
			foreach ($flist as $f) {
				if (preg_match('/^(http|ftp|https):\\/\\//i', $f)) {
					$ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
					if (in_array('.' . $ext, $this->config ['catcherAllowFiles'])) {
						if ($img = file_get_contents($f)) {
							$savepath = save_storage_content($ext, $img);
							if ($savepath) {
								$savelist [] = array(
									'state' => 'SUCCESS',
									//'url' => (is_ssl() ? 'https' : 'http')."://".$_SERVER['HTTP_HOST'].__ROOT__.'/'.$savepath,
									'url' => __ROOT__.'/'.$savepath,
									'size' => strlen($img),
									'title' => '',
									'original' => '',
									'source' => htmlspecialchars($f)
								);
							} else {
								$savelist [] = array(
								'state' => 'Save remote file error!',
								'url' => '',
								'size' => '',
								'title' => '',
								'original' => '',
								'source' => htmlspecialchars($f),
								);
							}
						} else {
							$savelist [] = array(
							'state' => 'Get remote file error',
							'url' => '',
							'size' => '',
							'title' => '',
							'original' => '',
							'source' => htmlspecialchars($f),
							);
						}
					} else {
						$sret ['state'] = 'File ext not allowed';
					}
				} else {
					$savelist [] = array(
						'state' => 'not remote image',
						'url' => '',
						'size' => '',
						'title' => '',
						'original' => '',
						'source' => htmlspecialchars($f),
					);
				}
			}
			$sret ['list'] = $savelist;
		}
		return json_encode($sret);
	}
}