<?php
//默认配置
$configs= array(
	'SHOW_PAGE_TRACE'=>true,
	'URL_CASE_INSENSITIVE'=>true,
	'TMPL_ACTION_ERROR'=>'Public:dispatch_jump',//error错误提示页面
	'URL_ROUTER_ON'   => true,
	'URL_ROUTE_RULES'=>array(
	),
	'DB_FIELD_CACHE'=>false,
	'HTML_CACHE_ON'=>false,
);
//DB设置
if(!file_exists($file="Config/db.php")){
	$file= "Config/db.default.php";
}
$configs=array_merge($configs,include ($file));
//动态设置
if(!file_exists($file="Config/config.php")){
	$file= "Config/config.default.php";
}
$configs=array_merge($configs,include ($file));
return  $configs;
