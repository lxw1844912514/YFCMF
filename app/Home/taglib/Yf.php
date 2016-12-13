<?php
// +----------------------------------------------------------------------
// | YFCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2015-2016 http://www.rainfer.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: rainfer <81818832@qq.com>
// +----------------------------------------------------------------------

//------------------------
// 自定义标签库
//-------------------------

namespace app\home\taglib;

use think\template\TagLib;

class Yf extends Taglib
{

    // 标签定义
    protected $tags = [
        // 标签定义： attr 属性列表 close 是否闭合（0 或者1 默认1） alias 标签别名 level 嵌套层次
		//'attr'=>返回的数组变量、文章ids、查询字段、limit、order、字符串条件、是否分页、单页数、搜索类型、搜索的值;具体参考get_news函数
        'news' => ['attr' => 'name,ids,cid,field,limit,order,where,ispage,pagesize,type,key', 'close' => 0],
		'ads' => ['attr' => 'name,cid,limit,order', 'close' => 0],
		'singlepage'=> ['attr' => 'name,menuid', 'close' => 0],
		'comments'=> ['attr' => 'name,field,limit,order', 'close' => 0],
		'menu'=>['attr' => 'top_ul_id,top_ul_class,child_ul_class,child_li_class,firstchild_dropdown_class,haschild_a_class,haschild_span_class,nochild_a_class,showlevel', 'close' => 0],
    ];

    /**
     * 返回news
     * @param $tag
     * @return string
     */
    public function tagNews($tag)
    {
        $name   = $tag['name'];
		$ids    = isset($tag['ids']) ? $tag['ids'] : '';
        $cid    = isset($tag['cid']) ? $tag['cid'] : '';
        $field  = isset($tag['field']) ? $tag['field'] : '';
        $limit  = isset($tag['limit']) ? $tag['limit'] : '';
        $order    = isset($tag['order']) ? $tag['order'] : '';
		$where    = isset($tag['where']) ? $tag['where'] : '';
        $ispage    = isset($tag['ispage']) ? $tag['ispage'] : 'false'; 
		$pagesize    = !empty($tag['ispage']) && !empty($tag['pagesize']) && is_numeric($tag['pagesize']) ? intval($tag['pagesize']) : 10; 
        $type    = isset($tag['type']) ? $tag['type'] : 'null';
		$key    = isset($tag['key']) ? $tag['key'] : '';
		$tag_str='';
		$tag_str .=!empty($ids)?'ids:'.$ids:'';
		$tag_str .=!empty($cid)?';cid:'.$cid:'';
		$tag_str .=!empty($field)?';field:'.$field:'';
		$tag_str .=!empty($limit)?';limit:'.$limit:'';
		$tag_str .=!empty($order)?';order:'.$order:'';
		$tag_str .=!empty($where)?';where:'.$where:'';
		if(substr($tag_str, 0, 1)==';') $tag_str=substr($tag_str,1);
		$parseStr = '<?php ';
		$parseStr .='$'.$name .'=get_news('.'"'.$tag_str.'",'.$ispage.','.$pagesize.',"'.$type.'","'.$key.'");';
		$parseStr .="?>";
        if (!empty($parseStr)) {
            return $parseStr;
        }
        return;
    }
    /**
     * 返回ads
     * @param $tag
     * @return string
     */
    public function tagAds($tag)
    {
        $name   = $tag['name'];
        $cid    = isset($tag['cid']) ? $tag['cid'] : '';
        $limit  = isset($tag['limit']) ? $tag['limit'] : '';
        $order    = isset($tag['order']) ? $tag['order'] : '';
		$parseStr = '<?php ';
		$parseStr .='$'.$name .'=get_ads('.'"'.$cid.'","'.$limit.'","'.$order.'");';
		$parseStr .="?>";
        if (!empty($parseStr)) {
            return $parseStr;
        }
        return;
    }
    /**
     * 返回singlepage的menu
     * @param $tag
     * @return string
     */
    public function tagSinglepage($tag)
    {
        $name   = $tag['name'];
        $menuid    = isset($tag['menuid']) ? $tag['menuid'] : '';
		$parseStr = '<?php ';
		$parseStr .='$'.$name .'=get_menu_one('.'"'.$menuid.'");';
		$parseStr .="?>";
        if (!empty($parseStr)) {
            return $parseStr;
        }
        return;
    }
    /**
     * 返回comments
     * @param $tag
     * @return string
     */
    public function tagComments($tag)
    {
        $name   = $tag['name'];
        $field  = isset($tag['field']) ? $tag['field'] : '';
        $limit  = isset($tag['limit']) ? $tag['limit'] : '';
        $order    = isset($tag['order']) ? $tag['order'] : '';
		$where    = isset($tag['where']) ? $tag['where'] : '';
		$tag_str='';
		$tag_str .=!empty($field)?'field:'.$field:'';
		$tag_str .=!empty($limit)?';limit:'.$limit:'';
		$tag_str .=!empty($order)?';order:'.$order:'';
		if(substr($tag_str, 0, 1)==';') $tag_str=substr($tag_str,1);
		$parseStr = '<?php ';
		$parseStr .='$'.$name .'=get_comments('.'"'.$tag_str.'");';
		$parseStr .="?>";
        if (!empty($parseStr)) {
            return $parseStr;
        }
        return;
    }
    /**
     * 返回前台menu
     * @param $tag
     * @return string
     */
    public function tagMenu($tag)
    {
		$top_ul_id=isset($tag['top_ul_id']) ? $tag['top_ul_id'] : '';
		$top_ul_class=isset($tag['top_ul_class']) ? $tag['top_ul_class'] : '';
		$child_ul_class=isset($tag['child_ul_class']) ? $tag['child_ul_class'] : '';
		$child_li_class=isset($tag['child_li_class']) ? $tag['child_li_class'] : '';
		$firstchild_dropdown_class=isset($tag['firstchild_dropdown_class']) ? $tag['firstchild_dropdown_class'] : '';
		$haschild_a_class=isset($tag['haschild_a_class']) ? $tag['haschild_a_class'] : '';
		$haschild_span_class=isset($tag['haschild_span_class']) ? $tag['haschild_span_class'] : '';
		$nochild_a_class=isset($tag['nochild_a_class']) ? $tag['nochild_a_class'] : '';
		$showlevel=!empty($tag['showlevel']) ? intval($tag['showlevel']) : 6;
		
		$childtpl='<a href=\'\$href\' class=\''.$nochild_a_class.'\'>\$menu_name</a>';
		$parenttpl='<a href=\'#\' class=\''.$haschild_a_class.'\'>\$menu_name<span class=\''.$haschild_span_class.'\'>&nbsp;<i class=\'fa fa-angle-down\'></i></span></a>';
		$parseStr = '<?php ';
		$parseStr .='echo get_menu("main","'.$top_ul_id.'","'.$childtpl.'","'.$parenttpl.'","'.$child_ul_class.'","'.$child_li_class.'","'.$top_ul_class.'","'.$showlevel.'","'.$firstchild_dropdown_class.'");';
		$parseStr .="?>";
        if (!empty($parseStr)) {
            return $parseStr;
        }
        return;
    }
}