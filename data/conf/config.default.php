<?php
return array(
	'SHOW_PAGE_TRACE'=>false,
	'URL_CASE_INSENSITIVE'=>true,
	'DB_LIKE_FIELDS'=>'news_title|news_content|news_flag|news_open',//自动模糊查询字段
	'URL_ROUTER_ON'   => true,
	'URL_ROUTE_RULES'=>array(
		'con/:n_id'=> 'Home/Index/news_content',//路由文章页
		'list/:c_id'=> 'Home/Index/news_list',//路由列表页
	),
);