<?php	return array (
  'SHOW_PAGE_TRACE' => true,
  'URL_CASE_INSENSITIVE' => true,
  'DB_LIKE_FIELDS' => 'news_title|news_content|news_flag|news_open',
  'URL_MODEL' => '0',
  'URL_ROUTER_ON' => true,
  'URL_ROUTE_RULES' => 
  array (
    'con/:n_id' => 'Home/Index/news_content',
    'list/:c_id' => 'Home/Index/news_list',
  ),
  'TMPL_PARSE_STRING' => array(
	'__UPLOAD__' => __ROOT__ . '/data/upload/',
	'__PUBLIC__'=>__ROOT__ . '/public',
	'__DATA__' => __ROOT__ . '/data/',
	'__AVATAR__' => __ROOT__ . '/data/image/avatar/',
	'__STATIC_ROOT__' => __ROOT__,
    ),
  'UPLOAD_TEMP_DIR' => './data/runtime/Data/',
  'UPLOAD_DIR' => './data/upload/',
  'YFCMF_VERSION' => 'V1.0.0',
  'THINK_SDK_QQ' => 
  array (
    'APP_KEY' => '203564',
    'APP_SECRET' => 'bab1f3acc0c67b69a1f470ac5e8dc36a',
    'CALLBACK' => 'http://www.rainfer.cn/ace/index.php?m=Home&c=oauth&a=callback&type=qq',
  ),
  'THINK_SDK_SINA' => 
  array (
    'APP_KEY' => '602735229',
    'APP_SECRET' => '66781cbeab9fdb9b014a387ab6e943fe',
    'CALLBACK' => 'http://www.rainfer.cn/ace/index.php?m=Home&c=oauth&a=callback&type=sina',
  ),
);