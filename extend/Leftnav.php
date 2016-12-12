<?php
class Leftnav{
	static public function cznav($cate , $lefthtml = '─' , $pid=0 , $lvl=0, $leftpin=0 ){
		$arr=array();
		foreach ($cate as $v){
			if($v['adminnav_leftid']==$pid){
				$v['lvl']=$lvl + 1;
				$v['leftpin']=$leftpin + 0;//左边距
				$v['lefthtml']='├'.str_repeat($lefthtml,$lvl);
				$arr[]=$v;
				$arr= array_merge($arr,self::cznav($cate,$lefthtml,$v['adminnav_id'],$lvl+1 , $leftpin+20));
			}
		}
		return $arr;
	}


	static public function rule($cate , $lefthtml = '─' , $pid=0 , $lvl=0, $leftpin=0 ){
		$arr=array();
		foreach ($cate as $v){
			if($v['pid']==$pid){
				$v['lvl']=$lvl + 1;
				$v['leftpin']=$leftpin + 0;//左边距
				$v['lefthtml']='├'.str_repeat($lefthtml,$lvl);
				$arr[]=$v;
				$arr= array_merge($arr,self::rule($cate,$lefthtml,$v['id'],$lvl+1 , $leftpin+20));
			}
		}
		return $arr;
	}

/*
 * 自定义菜单排列
 */
	static public function menu($cate , $lefthtml = '─' , $pid=0 , $lvl=0, $leftpin=0 ){
		$arr=array();
		foreach ($cate as $v){
			if($v['we_menu_pid']==$pid){
				$v['lvl']=$lvl + 1;
				$v['leftpin']=$leftpin + 0;
				$v['lefthtml']='├'.str_repeat($lefthtml,$lvl);
				$arr[]=$v;
				$arr= array_merge($arr,self::menu($cate,$lefthtml,$v['we_menu_id'], $lvl+1 ,$leftpin+20));
			}
		}

		return $arr;
	}
	
	


	
	static public function menu_n($cate , $lefthtml = '─' , $pid=0 , $lvl=0, $leftpin=0 ){
		$arr=array();
		foreach ($cate as $v){
			if($v['parentid']==$pid){
				$v['lvl']=$lvl + 1;
				$v['leftpin']=$leftpin + 0;//左边距
				$v['lefthtml']='├'.str_repeat($lefthtml,$lvl);
				$arr[]=$v;
				$arr= array_merge($arr,self::menu_n($cate,$lefthtml,$v['id'],$lvl+1 , $leftpin+20));
			}
		}
		return $arr;
	}

	
	

/*
 * $column_one 顶级栏目
 * $column_two 所有栏目
 * 用法匹配column_leftid 进行数组组合
 */
	static public function index_top($column_one , $column_two){
		$arr=array();
		foreach ($column_one as $v){
			$v['sub']= self::index_toptwo($column_two,$v['c_id']);
			$arr[]=$v;
		}
		return $arr;
	}
	
	static public function index_toptwo($column_two , $c_id){
		$arry=array();
		foreach ($column_two as $v){
			if ($v['column_leftid']==$c_id){
				$arry[]=$v;
			}
		}
		return $arry;
	}
}