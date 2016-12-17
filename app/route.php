<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

return [
'/' => 'Index/index',
'login' => 'Login/index',
//单页
'about'=>'Listn/index?id=1',
'about_en'=>'Listn/index?id=8',
'contacts'=>'Listn/index?id=4',
'contacts_en'=>'Listn/index?id=11',
//频道页
'list/:id'=>'Listn/index',
//新闻
'news/:id'=>'News/index',
];
