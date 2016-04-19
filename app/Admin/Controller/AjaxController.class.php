<?php
namespace Admin\Controller;
use Think\Controller;
class AjaxController extends Controller{
	//返回行政区域json字符串
	public function getRegion(){
		$Region=M("region");
		$map['pid']=I('pid');
		$map['type']=I('type');
		$list=$Region->where($map)->select();
		echo json_encode($list);
	}
}
