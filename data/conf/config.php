<?php
return array(
	'SHOW_PAGE_TRACE'=>true,
	'URL_CASE_INSENSITIVE'=>true,
	'DB_LIKE_FIELDS'=>'news_title|news_content|news_flag|news_open',//自动模糊查询字段
	'URL_MODEL' => '0',
	'URL_ROUTER_ON'   => true,
	'URL_ROUTE_RULES'=>array(
		'con/:n_id'=> 'Home/Index/news_content',//路由文章页
		'list/:c_id'=> 'Home/Index/news_list',//路由列表页
	),
	//模板相关
    'TMPL_PARSE_STRING' => array(
		'__UPLOAD__' => __ROOT__ . '/data/upload/',
		'__PUBLIC__'=>__ROOT__ . '/public'
        '__RES__' => __ROOT__ . '/res/',
        '__DATA__' => __ROOT__ . '/data/',
		'__AVATAR__' => __ROOT__ . '/data/image/avatar/',
        '__STATIC_ROOT__' => __ROOT__,
        '__JS_SUFFIX__' => (APP_DEBUG ? '.src.js' : '.js'),
        '__CSS_SUFFIX__' => (APP_DEBUG ? '.src.css' : '.css')
    ),
	//上传相关
	'UPLOAD_TEMP_DIR' => DATA_PATH,
	'UPLOAD_DIR' => './data/upload/',
	'YFCMF_VERSION'=>'V1.0.0',
);
