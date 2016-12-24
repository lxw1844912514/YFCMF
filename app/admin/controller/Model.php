<?php
// +----------------------------------------------------------------------
// | YFCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2015-2016 http://www.rainfer.cn All rights reserved.
// +----------------------------------------------------------------------
// | Author: rainfer <81818832@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;
use think\Db;
use think\Cache;
class Model extends Base
{
    public function model_list()
    {
		$models=Db::name('model')->order('create_time desc')->select();
		$this->assign('models',$models);
		return $this->fetch();
	} 
	public function model_state(){
		$id=input('x');
		$status=Db::name('model')->where(array('model_id'=>$id))->value('model_status');//判断当前状态情况
		if($status==1){
			$statedata = array('model_status'=>0);
			Db::name('model')->where(array('model_id'=>$id))->setField($statedata);
			$this->success('状态禁止');
		}else{
			$statedata = array('model_status'=>1);
			Db::name('model')->where(array('model_id'=>$id))->setField($statedata);
			$this->success('状态开启');
		}
	}

    //模型添加到后台menu
    public function model_addmenu(){
        $model_id=input('model_id');
        $model=Db::name('model')->where('model_id',$model_id)->find();
        if (empty($model)){
            $this->error('参数错误',url('model_list'));
        }else{
            //添加顶级菜单
            $rst=Db::name('auth_rule')->where('name','Model/cmslist?id='.$model_id)->find();
            if(empty($rst)){
                //不存在
                $sldata=array(
                    'name'=>'Model',
                    'title'=>input('menu_name'),
                    'css'=>input('css'),
                    'pid'=>0,
                    'level'=>1,
                    'sort'=>input('sort',50,'intval'),
                    'addtime'=>time()
                );
                $pid1=Db::name('auth_rule')->insertGetId($sldata);
                if($pid1){
                    //增加模型数据
                    $sldata=array(
                        'name'=>'Model/cmsadd?id='.$model_id,
                        'title'=>'增加'.$model['model_title'],
                        'pid'=>$pid1,
                        'level'=>2,
                        'sort'=>20,
                        'addtime'=>time()
                    );
                    $pid2=Db::name('auth_rule')->insertGetId($sldata);
                    if($pid2){
                        $sldata=array(
                            'name'=>'cmsrunadd',
                            'title'=>'增加操作',
                            'pid'=>$pid2,
                            'level'=>3,
                            'sort'=>10,
                            'status'=>0,
                            'addtime'=>time()
                        );
                        Db::name('auth_rule')->insert($sldata);
                    }else{
                        $this -> error("添加失败，请稍后重试",url('model_list'));
                    }
                    //添加列表
                    $sldata=array(
                        'name'=>'Model/cmslist?id='.$model_id,
                        'title'=>$model['model_title'].'列表',
                        'pid'=>$pid1,
                        'level'=>2,
                        'sort'=>10,
                        'addtime'=>time()
                    );
                    $pid2=Db::name('auth_rule')->insertGetId($sldata);
                    if($pid2){
                        //删除、状态、编辑显示、编辑操作、排序、全部删除
                        $sldata=[
                            ['name'=>'Model/cmsdel','title'=>'删除操作','pid'=>$pid2,'level'=>3,'status'=>0,'addtime'=>time()],
                            ['name'=>'Model/cmsstate','title'=>'状态操作','pid'=>$pid2,'level'=>3,'status'=>0,'addtime'=>time()],
                            ['name'=>'Model/cmsorder','title'=>'排序操作','pid'=>$pid2,'level'=>3,'status'=>0,'addtime'=>time()],
                            ['name'=>'Model/cmsalldel','title'=>'全部删除','pid'=>$pid2,'level'=>3,'status'=>0,'addtime'=>time()],
                            ['name'=>'Model/cmsedit','title'=>'编辑显示','pid'=>$pid2,'level'=>3,'status'=>0,'addtime'=>time()],
                            ['name'=>'Model/cmsrunedit','title'=>'编辑操作','pid'=>$pid2,'level'=>3,'status'=>0,'addtime'=>time()],
                        ];
                        Db::name('auth_rule')->insertAll($sldata);
                        Cache::clear();
                        $this->success('菜单添加成功',url('model_list'));
                    }else{
                        $this -> error("添加失败，请稍后重试",url('model_list'));
                    }
                }else{
                    $this -> error("添加失败，请稍后重试",url('model_list'));
                }
            }else{
                $this -> error("已存在，请确认！",url('model_list'));
            }
        }
    }
    //模型删除
    public function model_del(){
        $model_id=input('model_id');
        $model=Db::name('model')->where('model_id',$model_id)->find();
        if (empty($model)){
            $this->error('参数错误',url('model_list'));
        }else{
            $rst=Db::name('model')->where('model_id',$model_id)->delete();
            if($rst!==false){
                $rule=Db::name('auth_rule')->where('name','Model/cmslist?id='.$model_id)->find();
                if($rule){
                    $pid=$rule['pid'];//顶级菜单
                    $arr=Db::name('auth_rule')->select();
                    $ids=array();
                    $arrTree=getMenuTree($arr, $pid,'pid',$ids);
                    if(!empty($arrTree)){
                        Db::name('auth_rule')->where('id','in',$ids)->delete();
                        Cache::clear();
                    }
                }
                $this->success('模型删除成功',url('model_list'));
            }else{
                $this -> error("模型删除失败！",url('model_list'));
            }
        }
    }
	protected function build_table_exists($table)
    {
        static $tables = null;
        static $db_prefix = null;

        $db_prefix = config('database.prefix');
        if (null === $tables) {
            $tables = db_get_tables(true);
        }

        if (in_array($db_prefix . $table, $tables)) {
            return true;
        }
        return false;
    }
    public function model_add()
    {
		return $this->fetch();
	} 
    public function model_runadd()
    {
        
		static $db = null;
        static $db_prefix = null;
        $db_prefix = config('database.prefix');
        if (null === $db) {
            $db = Db::connect([], true);
        }
        //表名
        $model_name = input('model_name', '');
        if (empty ($model_name)) {
            $this->error('请设置模型名');
        }
		$rst=$db->name('model')->where('model_name',$model_name)->find();
        if($rst){
            $this->error('已存在'.$model_name.'标识的模型');
        }		
        //主键
        $model_pk = input('model_pk', '');
        if (empty ($model_pk)) {
            $this->error('请设置主键');
        }
        //引擎
        $model_engine = input('model_engine', 'MyISAM');
        //字段数组,2维[0][字段名,字段标题,字段类型,字段数据,字段说明,字段长度,字段规则,默认值]
		$fields=input('fields');
		$model_fields=$fields?$this->fields(json_decode($fields,true)):array();
        if (empty ($model_fields)) {
            $this->error('请设置模型字段');
        }
		$fields=json_encode($model_fields);
        //保存到model数据表中
        $sl_data=array(
            'model_name'=>$model_name,
            'model_title'=>input('model_title',''),
            'model_pk'=>$model_pk,
            'model_cid'=>input('model_cid',$model_name.'_cid'),
            'model_order'=>input('model_order',$model_name.'_order'),
            'model_sort'=>input('model_sort',$model_name.'_order'),
            'model_fields'=>$fields,
			'model_list'=>input('model_list',''),
			'model_edit'=>input('model_edit',''),
            'model_indexes'=>input('model_indexes',''),
            'search_list'=>input('search_list',''),
            'create_time'=>time(),
            'update_time'=>time(),
            'model_status'=>1,
            'model_engine'=>$model_engine

        );
        $rst=$db->name('model')->insert($sl_data);
        if($rst===false){
            $this->error('创建模型失败');
        }
        //加上cid order字段
        $model_fields[input('model_cid',$model_name.'_cid')]=array(
            'name'=>input('model_cid',$model_name.'_cid'),
            'title'=>'前台栏目',
            'type'=>'selecttext',
            'data'=>'menu|id|menu_name|id',
            'description'=>'前台栏目',
            'length'=>100,
            'rules'=>'required',
            'default'=>''
        );
        $model_fields[input('model_order',$model_name.'_order')]=array(
            'name'=>input('model_cid',$model_name.'_order'),
            'title'=>'排序',
            'type'=>'number',
            'data'=>'',
            'description'=>'排序,越小越靠前',
            'length'=>3,
            'rules'=>'',
            'default'=>50
        );
        switch (config('database.type')) {
            case 'mysql' :
                //不存在则创建
                if (!$this->build_table_exists($model_name)) {
                    $sql = ("CREATE TABLE `$db_prefix$model_name` (
                        `$model_pk` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                        %FIELDS_SQL%
                        %PRIMARY_KEY_SQL%
                        %UNIQUE_KEY_SQL%
                        %KEY_SQL%
                        ) ENGINE=$model_engine AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;");

                    $sql_fields = array();
                    $sql_primary_key = "PRIMARY KEY (`$model_pk`)";
                    $sql_unique_key = array();
                    $sql_key = array();

                    foreach ($model_fields as $f=>$fi) {
                        $rules = explode(',', str_replace(' ', '', $fi ['rules']));
                        switch ($fi ['type']) {
                            //百度地图字段，双精度型
                            case 'baidu_map':
                                $defaults = explode(',', $fi['default']);
                                $sql_fields [] = "`${f}_lng` DOUBLE NOT NULL DEFAULT ".($defaults[0]?"$defaults[0]":"0")." COMMENT '$fi[title]'";
                                $sql_fields [] = "`${f}_lat` DOUBLE NOT NULL DEFAULT ".($defaults[1]?"$defaults[1]":"0")." COMMENT '$fi[title]'";
                                break;
                            //变长或固定字符串型
                            case 'text' :
                            case 'imagefile' :
                            case 'selecttext' :
                            case 'checkbox' :
                                if (empty ($fi ['length'])) {
                                    $fi ['length'] = 200;
                                }
                                $ftype = 'VARCHAR';
                                //固定长度
                                if (in_array('lengthfixed', $rules)) {
                                    $ftype = 'CHAR';
                                }
                                $fnull = '';
                                //非空
                                if (in_array('required', $rules)) {
                                    $fnull = 'NOT NULL';
									$fi['default']=$fi['default']?:'未填写';
                                }
                                $sql_fields [] = "`$f` $ftype($fi[length]) $fnull DEFAULT '$fi[default]' COMMENT '$fi[title]'";
                                break;
                            //bigint型
                            case 'currency':
                            case 'large_number':
                                $funsigned = '';
                                //非负数
                                if (in_array('unsigned', $rules)) {
                                    $funsigned = 'UNSIGNED';
                                }
                                $fnull = '';
                                if (in_array('required', $rules)) {
                                    $fnull = 'NOT NULL';
									$fi['default']=isset($fi['default'])?$fi['default']:'0';
                                }
                                $sql_fields [] = "`$f` BIGINT $funsigned $fnull ".($fi['default']?"DEFAULT $fi[default]":"")." COMMENT '$fi[title]'";
                                break;
                            //整数型
                            case 'number' :
                            case 'datetime' :
                            case 'date' :
                            case 'selectnumber' :
                                $funsigned = '';
                                if (in_array($fi ['type'], array(
                                        'date',
                                        'datetime'
                                    )) || in_array('unsigned', $rules)
                                ) {
                                    $funsigned = 'UNSIGNED';
                                }
                                $fnull = '';
                                if (in_array('required', $rules)) {
                                    $fnull = 'NOT NULL';
									$fi['default']=isset($fi['default'])?$fi['default']:'0';
                                }
                                $sql_fields [] = "`$f` INT $funsigned $fnull ".($fi['default']?"DEFAULT $fi[default]":"")." COMMENT '$fi[title]'";
                                break;
                            //text型
                            case 'richtext' :
                            case 'bigtext' :
                            case 'images':
                                $sql_fields [] = "`$f` TEXT COMMENT '$fi[title]'";
                                break;
                            //TINYINT型
                            case 'switch' :
                                $sql_fields [] = "`$f` TINYINT UNSIGNED NOT NULL DEFAULT ".($fi['default']?"1":"0")." COMMENT '$fi[title]'";
                                break;
                            default :
                                $this->error('不能识别字段类型');
                        }
                        if (in_array($fi ['type'], array(
                            'switch',
                            'text',
                            'number',
                            'datetime',
                            'date',
                            'selecttext',
                            'selectnumber',
                            'checkbox'
                        ))) {
                            //不重复
                            if (in_array('unique', $rules)) {
                                $sql_unique_key [] = "UNIQUE KEY $f ($f)";
                            }
                        }
                    }
                    //索引数组
					$model_indexes=input('model_indexes','')?explode(',',input('model_indexes','')):array();
                    if (!empty($model_indexes)) {
                        foreach ($model_indexes as $indexes) {
                            $sql_key[] = "INDEX IX_" . join('_', $indexes) . "(" . join(',', $indexes) . ")";
                        }
                    }
                    //替换sql语句
                    $sql = str_replace(array(
                        '%FIELDS_SQL%',
                        '%PRIMARY_KEY_SQL%',
                        '%UNIQUE_KEY_SQL%',
                        '%KEY_SQL%'
                    ), array(
                        join(",\n", $sql_fields) . ((empty ($sql_primary_key) && empty ($sql_unique_key) && empty ($sql_key)) ? '' : ",\n"),
                        $sql_primary_key . ((empty ($sql_primary_key) || (empty ($sql_unique_key) && empty ($sql_key))) ? '' : ",\n"),
                        join(",\n", $sql_unique_key) . ((empty ($sql_unique_key) || empty ($sql_key)) ? '' : ",\n"),
                        join(",\n", $sql_key)
                    ), $sql);
                    $rst=$db->execute($sql);//创建模型数据表
                    if($rst===false){
                        $this->success('创建模型失败');
                    }
                }
                break;
            //TODO mysql以外数据类型
            default :
                $this->error('不支持的数据库类型');
        }
        $this->success('创建模型成功',url('model_list'));
    }
    public function model_edit()
    {
		$model_id = input('model_id');
		$model=Db::name('model')->where('model_id',$model_id)->find();
		$fields=json_decode($model['model_fields'],true);
		$this->assign('model',$model);
		$this->assign('fields',$fields);
		return $this->fetch();
	}
    public function model_copy()
    {
        $model_id = input('model_id');
        $model=Db::name('model')->where('model_id',$model_id)->find();
        $fields=json_decode($model['model_fields'],true);
        $this->assign('model',$model);
        $this->assign('fields',$fields);
        return $this->fetch();
    }
    public function model_runedit()
    {
		static $db = null;
        static $db_prefix = null;
        $db_prefix = config('database.prefix');
        if (null === $db) {
            $db = Db::connect([], true);
        }
        $model_id = input('model_id', 0);
		$old_model=$db->name('model')->where('model_id',$model_id)->find();
		if(empty($old_model)){
			$this->error('模型不存在');
		}
		//表名
        $model_name = input('model_name', '');
        if (empty ($model_name)) {
            $this->error('请设置模型名');
        }
        //主键
        $model_pk = input('model_pk', '');
        if (empty ($model_pk)) {
            $this->error('请设置主键');
        }
        //引擎
        $model_engine = input('model_engine', 'MyISAM');
        //字段数组,2维[0][字段名,字段标题,字段类型,字段数据,字段说明,字段长度,字段规则,默认值]
		$fields=input('fields');
		$model_fields=$fields?$this->fields(json_decode($fields,true)):array();
        if (empty ($model_fields)) {
            $this->error('请设置模型字段');
        }
		$fields=json_encode($model_fields);
        //保存到model数据表中
        $sl_data=array(
            'model_name'=>$model_name,
            'model_title'=>input('model_title',''),
            'model_pk'=>$model_pk,
            'model_cid'=>input('model_cid',$model_name.'_cid'),
            'model_order'=>input('model_order',$model_name.'_order'),
            'model_sort'=>input('model_sort',$model_name.'_order'),
            'model_fields'=>$fields,
			'model_list'=>input('model_list',''),
			'model_edit'=>input('model_edit',''),
            'model_indexes'=>input('model_indexes',''),
            'search_list'=>input('search_list',''),
            'update_time'=>time(),
            'model_status'=>1,
            'model_engine'=>$model_engine

        );
        $rst=$db->name('model')->where('model_id',$model_id)->update($sl_data);
        if($rst===false){
            $this->error('编辑模型失败');
        }else{
			$old_table=$old_model['model_name'];
			if($this->build_table_exists($old_table)){
				//备份
				$path=ROOT_PATH.'data/backup/';
				if (!file_exists($path)) {
					@mkdir($path,0777,true);
				}
				$content=db_get_insert_sqls($old_table);
				file_put_contents($path.$db_prefix.$old_table.'.sql', $content);
				//删除
				$sql="DROP TABLE `$db_prefix$old_table`;";
                $db->execute($sql);
			}
        }
        //加上cid order字段
        $model_fields[input('model_cid',$model_name.'_cid')]=array(
            'name'=>input('model_cid',$model_name.'_cid'),
            'title'=>'前台栏目',
            'type'=>'selecttext',
            'data'=>'menu|id|menu_name|id',
            'description'=>'前台栏目',
            'length'=>100,
            'rules'=>'required',
            'default'=>''
        );
        $model_fields[input('model_order',$model_name.'_order')]=array(
            'name'=>input('model_cid',$model_name.'_order'),
            'title'=>'排序',
            'type'=>'number',
            'data'=>'',
            'description'=>'排序,越小越靠前',
            'length'=>3,
            'rules'=>'',
            'default'=>50
        );
        switch (config('database.type')) {
            case 'mysql' :
                if (!$this->build_table_exists($model_name)) {
                    $sql = ("CREATE TABLE `$db_prefix$model_name` (
                        `$model_pk` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                        %FIELDS_SQL%
                        %PRIMARY_KEY_SQL%
                        %UNIQUE_KEY_SQL%
                        %KEY_SQL%
                        ) ENGINE=$model_engine AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;");
                    $sql_fields = array();
                    $sql_primary_key = "PRIMARY KEY (`$model_pk`)";
                    $sql_unique_key = array();
                    $sql_key = array();

                    foreach ($model_fields as $f=>$fi) {
                        $rules = explode(',', str_replace(' ', '', $fi ['rules']));
                        switch ($fi ['type']) {
                            //百度地图字段，双精度型
                            case 'baidu_map':
                                $defaults = explode(',', $fi['default']);
                                $sql_fields [] = "`${f}_lng` DOUBLE NOT NULL DEFAULT ".($defaults[0]?"$defaults[0]":"0")." COMMENT '$fi[title]'";
                                $sql_fields [] = "`${f}_lat` DOUBLE NOT NULL DEFAULT ".($defaults[1]?"$defaults[1]":"0")." COMMENT '$fi[title]'";
                                break;
                            //变长或固定字符串型
                            case 'text' :
                            case 'imagefile' :
                            case 'selecttext' :
                            case 'checkbox' :
                                if (empty ($fi ['length'])) {
                                    $fi ['length'] = 200;
                                }
                                $ftype = 'VARCHAR';
                                //固定长度
                                if (in_array('lengthfixed', $rules)) {
                                    $ftype = 'CHAR';
                                }
                                $fnull = '';
                                //非空
                                if (in_array('required', $rules)) {
                                    $fnull = 'NOT NULL';
									$fi['default']=$fi['default']?:'未填写';
                                }
                                $sql_fields [] = "`$f` $ftype($fi[length]) $fnull DEFAULT '$fi[default]' COMMENT '$fi[title]'";
                                break;
                            //bigint型
                            case 'currency':
                            case 'large_number':
                                $funsigned = '';
                                //非负数
                                if (in_array('unsigned', $rules)) {
                                    $funsigned = 'UNSIGNED';
                                }
                                $fnull = '';
                                if (in_array('required', $rules)) {
                                    $fnull = 'NOT NULL';
									$fi['default']=isset($fi['default'])?$fi['default']:'0';
                                }
                                $sql_fields [] = "`$f` BIGINT $funsigned $fnull ".($fi['default']?"DEFAULT $fi[default]":"")." COMMENT '$fi[title]'";
                                break;
                            //整数型
                            case 'number' :
                            case 'datetime' :
                            case 'date' :
                            case 'selectnumber' :
                                $funsigned = '';
                                if (in_array($fi ['type'], array(
                                        'date',
                                        'datetime'
                                    )) || in_array('unsigned', $rules)
                                ) {
                                    $funsigned = 'UNSIGNED';
                                }
                                $fnull = '';
                                if (in_array('required', $rules)) {
                                    $fnull = 'NOT NULL';
									$fi['default']=isset($fi['default'])?$fi['default']:'0';
                                }
                                $sql_fields [] = "`$f` INT $funsigned $fnull ".($fi['default']?"DEFAULT $fi[default]":"")." COMMENT '$fi[title]'";
                                break;
                            //text型
                            case 'richtext' :
                            case 'bigtext' :
                            case 'images':
                                $sql_fields [] = "`$f` TEXT COMMENT '$fi[title]'";
                                break;
                            //TINYINT型
                            case 'switch' :
                                $sql_fields [] = "`$f` TINYINT UNSIGNED NOT NULL DEFAULT ".($fi['default']?"1":"0")." COMMENT '$fi[title]'";
                                break;
                            default :
                                $this->error('不能识别字段类型');
                        }
                        if (in_array($fi ['type'], array(
                            'switch',
                            'text',
                            'number',
                            'datetime',
                            'date',
                            'selecttext',
                            'selectnumber',
                            'checkbox'
                        ))) {
                            //不重复
                            if (in_array('unique', $rules)) {
                                $sql_unique_key [] = "UNIQUE KEY $f ($f)";
                            }
                        }
                    }
                    //索引数组
					$model_indexes=input('model_indexes','')?explode(',',input('model_indexes','')):array();
                    if (!empty($model_indexes)) {
                        foreach ($model_indexes as $indexes) {
                            $sql_key[] = "INDEX IX_" . join('_', $indexes) . "(" . join(',', $indexes) . ")";
                        }
                    }
                    //替换sql语句
                    $sql = str_replace(array(
                        '%FIELDS_SQL%',
                        '%PRIMARY_KEY_SQL%',
                        '%UNIQUE_KEY_SQL%',
                        '%KEY_SQL%'
                    ), array(
                        join(",\n", $sql_fields) . ((empty ($sql_primary_key) && empty ($sql_unique_key) && empty ($sql_key)) ? '' : ",\n"),
                        $sql_primary_key . ((empty ($sql_primary_key) || (empty ($sql_unique_key) && empty ($sql_key))) ? '' : ",\n"),
                        join(",\n", $sql_unique_key) . ((empty ($sql_unique_key) || empty ($sql_key)) ? '' : ",\n"),
                        join(",\n", $sql_key)
                    ), $sql);
                    $rst=$db->execute($sql);//创建模型数据表
                    if($rst===false){
                        $this->success('创建模型失败');
                    }
                }
                break;
            //TODO mysql以外数据类型
            default :
                $this->error('不支持的数据库类型');
        }
        $this->success('编辑模型成功，原数据已备份',url('model_list'));
    }
    public function cmslist()
    {
        $model_id=input('id',0);
        $key=input('key','');
		$model=Db::name('model')->where('model_id',$model_id)->find();
		if(empty($model)){
			$this->error('不存在的模型');
		}
		//除主键、cid、order外字段
		$model_fields=$model['model_fields']?json_decode($model['model_fields'],true):array();
		//加上主键、cid、order
		$model_fields[$model['model_pk']]=array(
			'name'=>$model['model_pk'],
			'title'=>'ID',
			'type'=>'number',
			'data'=>'',
			'description'=>'',
			'length'=>'',
			'rules'=>'',
			'default'=>''
		);
        $model_fields[$model['model_cid']]=array(
            'name'=>$model['model_cid'],
            'title'=>'前台栏目',
            'type'=>'selecttext',
            'data'=>'menu|id|menu_name|id',
            'description'=>'前台栏目',
            'length'=>100,
            'rules'=>'required',
            'default'=>''
        );
        $model_fields[$model['model_order']]=array(
            'name'=>$model['model_order'],
            'title'=>'排序',
            'type'=>'number',
            'data'=>'',
            'description'=>'排序,越小越靠前',
            'length'=>3,
            'rules'=>'',
            'default'=>50
        );
		//栏目过滤
        $map=array();
        $model_cid=input($model['model_cid'],'');
        if ($model_cid!=''){
            $ids=get_menu_byid($model_cid,1,2);
            $map[$model['model_cid']]= array('in',implode(",", $ids));
        }
        //处理搜索字段
        $where='';
		if(!empty($key)){
            $model_search=$model['search_list']?explode(',',$model['search_list']):array();
            $fields_search = array();
            if(empty($model_search)){
                $fields_search=$model_fields;
            }else{
                foreach ($model_search as &$v) {
                    if (isset ($model_fields[$v])) {
                        $fields_search[$v] = $model_fields[$v];
                    }
                }
            }
            $field_search=array();
            foreach($fields_search as $k=>$v){
                if($v['type']=='baidu_map'){
                    $field_search[]=$k.'_lng';
                    $field_search[]=$k.'_lat';
                }else{
                    $field_search[]=$k;
                }
            }
            $where=join('|',$field_search);
        }
		//列表显示字段
		$model_list=$model['model_list']?explode(',',$model['model_list']):array();
		//字段处理,排除不在表内字段
		$fields = array();
		if(empty($model_list)){
			$fields=$model_fields;
		}else{
			foreach ($model_list as &$v) {
				if (isset ($model_fields[$v])) {
					$fields[$v] = $model_fields[$v];
				}
			}
		}
		$field_list=array();
		foreach($fields as $k=>$v){
			if($v['type']=='baidu_map'){
				$field_list[]=$k.'_lng';
				$field_list[]=$k.'_lat';
			}else{
				$field_list[]=$k;
			}
		}
		//判断cid order是否在显示字段里，不在则加入
        if(!in_array($model['model_cid'],$field_list)){
            array_unshift($field_list,$model['model_cid']);
        }
        if(!in_array($model['model_order'],$field_list)){
            array_unshift($field_list,$model['model_order']);
        }
        $order=$model['model_sort']?:$model['model_order'];
		if($where){
            $data=Db::name($model['model_name'])->where($map)->where($where,'like',"%".$key."%")->field(join(',',$field_list))->order($order)->paginate(config('paginate.list_rows'),false,['query'=>get_query()]);
        }else{
            $data=Db::name($model['model_name'])->where($map)->field(join(',',$field_list))->order($order)->paginate(config('paginate.list_rows'),false,['query'=>get_query()]);
        }
		//处理分页
		$show = $data->render();
		$show=preg_replace("(<a[^>]*page[=|/](\d+).+?>(.+?)<\/a>)","<a href='javascript:ajax_page($1);'>$2</a>",$show);
		//处理数据
		$data_list = array();
		foreach($data as &$v){
			$item=array();
			foreach($v as $kk=>$vv){
				if ($kk == $model['model_pk']) {
					$item [$kk] = $vv;
					continue;
				}
				if (!isset($model_fields [$kk])) {
					$kk = substr($kk, 0, strrpos($kk, '_'));
				}				
				switch ($model_fields [$kk] ['type']) {
					case 'images':
						$images = array();
						if ($vv) {
							foreach (explode(',', $vv) as $vvv) {
								$images[] = '<img src="' . get_imgurl($vvv) . '" style="max-width:40px;max-height:40px;" /></a>';
							}
							$item [$kk] = join(' ', $images);
						} else {
							$item [$kk] = '';
						}
						break;
					case 'text' :
					case 'number' :
					case 'large_number':
					    $item [$kk] = htmlspecialchars($vv);
                        break;
					case 'currency':
						$item[$kk] = long_currency($vv);
						break;
					case 'datetime' :
						$item [$kk] = date('Y-m-d H:i:s', $vv);
						break;	
					case 'date' :
						$item [$kk] = date('Y-m-d', $vv);
						break;	
					case 'imagefile' :
						if ($vv) {
							$item [$kk] = '<img src="' . get_imgurl($vv) . '" style="max-width:40px;max-height:40px;" /></a>';
						} else {
							$item [$kk] = '';
						}
						break;	
					case 'switch' :
						if ($vv) {
							$item [$kk] = '<a class="red open-btn" href="'.url('cmsstate',['key'=>$kk,'id'=>$model_id]).'" data-id="'.$v[$model['model_pk']].'" title="开启"><div><button class="btn btn-minier btn-yellow">开启</button></div></a>';
						} else {
							$item [$kk] = '<a class="red open-btn" href="'.url('cmsstate',['key'=>$kk,'id'=>$model_id]).'" data-id="'.$v[$model['model_pk']].'" title="关闭"><div><button class="btn btn-minier btn-danger">关闭</button></div></a>';
						}
						break;
					case 'bigtext' :
					case 'richtext' :
						$item [$kk] = htmlspecialchars(html_trim($vv,20));
						break;	
					case 'selecttext' :
					case 'selectnumber' :
					case 'checkbox' :
						$item [$kk] = $this->cms_field_option_get_titles($model_fields [$kk] ['data'], explode(',', $vv));
						$item [$kk] = htmlspecialchars(join(',', $item [$kk]));
						break;
					case 'baidu_map':
						$item[$kk] = '( ' . $v[$kk . '_lng'] . ', ' . $v[$kk . '_lat'] . ' )';
						break;
				}
			}
			$data_list[]= $item;
		}
        //栏目数据
        $nav = new \Leftnav;
        $menu_next=Db::name('menu')->where('menu_type <> 4 and menu_type <> 2')-> order('menu_l desc,listorder') -> select();
        $arr = $nav::menu_n($menu_next);
        $this->assign('menu',$arr);
        $this->assign('model_cid',$model_cid);
		$this->assign('page',$show);
		$this->assign('data',$data_list);
		$this->assign('model_id', $model_id);
		$this->assign('model', $model);
        $this->assign('keyy', $key);
		$this->assign('fields', $fields);
		if(request()->isAjax()){
			return $this->fetch('ajax_cmslist');
		}else{
			return $this->fetch();
		}
    }
    //模型增加
    public function cmsadd()
    {
        $model_id=input('id',0);
        $model=Db::name('model')->where('model_id',$model_id)->find();
        if(empty($model)){
            $this->error('不存在的模型');
        }
        $model_cid=input($model['model_cid'],'');
        //cid、order
        $model_fields[$model['model_cid']]=array(
            'name'=>$model['model_cid'],
            'title'=>'前台栏目',
            'type'=>'selecttext',
            'data'=>'menu|id|menu_name|id',
            'description'=>'前台栏目',
            'length'=>100,
            'rules'=>'required',
            'default'=>''
        );
        $model_fields[$model['model_order']]=array(
            'name'=>$model['model_order'],
            'title'=>'排序',
            'type'=>'number',
            'data'=>'',
            'description'=>'排序,越小越靠前',
            'length'=>3,
            'rules'=>'',
            'default'=>50
        );
        //除主键外字段
        $model_fields=array_merge($model_fields,$model['model_fields']?json_decode($model['model_fields'],true):array());
		//处理默认值
		$fields_data = array();
        foreach ($model_fields as $k=>$v) {
            $fields_data [$k] = $model_fields [$k];
            $fields_data [$k] ['rules'] = explode('|', $fields_data [$k] ['rules']);
            //未设置则为''
            if (!isset ($fields_data [$k] ['default'])) {
                $fields_data [$k] ['default'] = '';
            }
            switch ($fields_data [$k] ['type']) {
                case 'images':
                    $fields_data [$k] ['images']=array_filter(explode(",", $fields_data [$k] ['default']));
					$fields_data [$k] ['value'] = join(',', $fields_data [$k] ['images']);
                    break;
                case 'baidu_map':
                    $fields_data [$k] ['default'] = explode(',', $fields_data [$k] ['default']);
                    $fields_data [$k] ['value']['lng'] = $fields_data [$k] ['default'][0];
                    $fields_data [$k] ['value']['lat'] = $fields_data [$k] ['default'][1];
                    break;
                case 'text' :
                case 'number' :
                case 'switch' :
                case 'bigtext' :
                case 'large_number':
                    $fields_data [$k] ['value'] = $fields_data [$k] ['default'];
                    break;
                case 'currency':
                    $fields_data [$k] ['value'] = long_currency($fields_data [$k] ['default']);
                    break;
                case 'datetime' :
                    $fields_data [$k] ['value'] = $fields_data [$k] ['default'];
                    if (empty ($fields_data [$k] ['value'])) {
                        $fields_data [$k] ['value'] = time();
                    }
                    break;
                case 'date' :
                    $fields_data [$k] ['value'] = $fields_data [$k] ['default'];
                    if (empty ($fields_data [$k] ['value'])) {
                        $fields_data [$k] ['value'] = time();
                    }
                    break;
                case 'selectnumber' :
                case 'selecttext' :
                    $fields_data [$k] ['value'] = $fields_data [$k] ['default'];
                    if($k!=$model['model_cid']){
                        $fields_data [$k] ['option'] = $this->cms_field_option_conv($fields_data [$k] ['data']);
                    }else{
                        $nav = new \Leftnav;
                        $arr=Db::name('menu')->where(['menu_modelid'=>$model_id,'menu_type'=>3])->select();
                        $fields_data [$k] ['option']=$nav::menu_n($arr);
                    }
                    break;
                case 'checkbox' :
                    $fields_data [$k] ['value'] = explode(',', $fields_data [$k] ['default']);;
                    $fields_data [$k] ['option'] = $this->cms_field_option_conv($fields_data [$k] ['data']);
                    break;
                case 'richtext' :
                    $fields_data [$k] ['value'] = $fields_data [$k] ['default'];
                    break;
                case 'imagefile' :
                    $fields_data [$k] ['value'] = $fields_data [$k] ['default'];
                    break;
                default :
                    $this->error('未知字段 ' . $fields_data [$k] ['type']);
                    break;
            }
        }//处理默认值
		$this->assign('model_id',$model_id);
		$this->assign('model',$model);
        $this->assign('model_cid',$model_cid);
		$this->assign('fields_data', $fields_data);
		return $this->fetch();
    }
    //模型添加
    public function cmsrunadd()
    {
        $model_id=input('id',0);
        $model=Db::name('model')->where('model_id',$model_id)->find();
        if(empty($model)){
            $this->error('不存在的模型');
        }
        //cid、order
        $model_fields[$model['model_cid']]=array(
            'name'=>$model['model_cid'],
            'title'=>'前台栏目',
            'type'=>'selecttext',
            'data'=>'menu|id|menu_name|id',
            'description'=>'前台栏目',
            'length'=>100,
            'rules'=>'required',
            'default'=>''
        );
        $model_fields[$model['model_order']]=array(
            'name'=>$model['model_order'],
            'title'=>'排序',
            'type'=>'number',
            'data'=>'',
            'description'=>'排序,越小越靠前',
            'length'=>3,
            'rules'=>'',
            'default'=>50
        );
        //除主键外字段
        $model_fields=array_merge($model_fields,$model['model_fields']?json_decode($model['model_fields'],true):array());
        //处理postdata
        $postdata=array();
        foreach ($model_fields as $k=>$v) {
            $f=$model_fields[$k];
            $rules = explode('|', $f ['rules']);
            //处理input数据
			switch ($f ['type']) {
				case 'images':
					//判断是否有传多图
					$files = request()->file('pic_all');
					$picall_url='';
					if(!empty($files)){
						if(config('storage.storage_open')){
							//七牛
							$upload = \Qiniu::instance();
							$info = $upload->upload();
							$error = $upload->getError();
							if ($info) {
								if(!empty($info['pic_all'])) {
									foreach ($info['pic_all'] as $file) {
										$img_url=config('storage.domain').$file['key'];
										$picall_url = $img_url . ',' . $picall_url;
									}
								}else{
									foreach ($info as $file) {
										$img_url=config('storage.domain').$file['key'];
										$picall_url = $img_url . ',' . $picall_url;
									}
								}
							}else{
								$this->error($error);//否则就是上传错误，显示错误原因
							}
						}else{
							$validate = config('upload_validate');
							//多图
							foreach ($files as $file) {
								$info = $file->validate($validate)->rule('uniqid')->move(ROOT_PATH . config('upload_path') . DS . date('Y-m-d'));
								if ($info) {
									$img_url = config('upload_path'). '/' . date('Y-m-d') . '/' . $info->getFilename();
									//写入数据库
									$data['uptime'] = time();
									$data['filesize'] = $info->getSize();
									$data['path'] = $img_url;
									Db::name('plug_files')->insert($data);
									$picall_url = $img_url . ',' . $picall_url;
								} else {
									$this->error($file->getError());//否则就是上传错误，显示错误原因
								}
							}
						}
					}
					$postdata[$k]=$picall_url;
					break;
				case 'imagefile':
					$file = request()->file('pic_one');
					$img_one='';
					if(!empty($file)){
						if(config('storage.storage_open')){
							//七牛
							$upload = \Qiniu::instance();
							$info = $upload->upload();
							$error = $upload->getError();
							if ($info) {
								if(!empty($info['pic_one'])){
									$img_one= config('storage.domain').$info['pic_one'][0]['key'];
								}else{
									$img_one= config('storage.domain').$info[0]['key'];
								}
							}else{
								$this->error($error);//否则就是上传错误，显示错误原因
							}
						}else{
							$validate = config('upload_validate');
							//单图
							$info = $file[0]->validate($validate)->rule('uniqid')->move(ROOT_PATH . config('upload_path') . DS . date('Y-m-d'));
							if ($info) {
								$img_url = config('upload_path'). '/' . date('Y-m-d') . '/' . $info->getFilename();
								//写入数据库
								$data['uptime'] = time();
								$data['filesize'] = $info->getSize();
								$data['path'] = $img_url;
								Db::name('plug_files')->insert($data);
								$img_one = $img_url;
							} else {
								$this->error($file->getError());//否则就是上传错误，显示错误原因
							}
						}
					}
					$postdata[$k]=$img_one;
					break;
				case 'baidu_map':
					$postdata [$k . '_lng'] = input("${k}_lng", 0, 'floatval');
					$postdata [$k . '_lat'] = input("${k}_lat", 0, 'floatval');
					break;
				case 'text' :
				case 'bigtext' :
					$postdata [$k] = input("$k", '', 'trim');
					break;
				case 'number' :
				case 'switch' :
					$postdata [$k] = input("$k", 0, 'intval');
					break;
				case 'large_number' :
					$postdata [$k] = input("$k", 0);
					break;
				case 'currency':
					$postdata [$k] = currency_long(input("$k", '', 'trim'));
					break;
				case 'datetime' :
				case 'date' :
					$postdata [$k] = input("$k", '', 'strtotime');
					break;
				case 'selectnumber' :
					$postdata [$k] = input("$k", 0, 'intval');
					if (!$this->cms_field_option_valid($f ['data'], $postdata [$k])) {
						$this->error($f ['title'] . ' 无效');
					}
					break;
				case 'selecttext' :
					$postdata [$k] = input("$k", '', 'trim');
					if (!$this->cms_field_option_valid($f ['data'], $postdata [$k])) {
						$this->error($f ['title'] . ' 无效');
					}
					break;
				case 'checkbox' :
					$postdata [$k] = input("{$k}".'/a', array());
					if (!$this->cms_field_option_valid($f ['data'], $postdata [$k])) {
						$this->error($f ['title'] . ' 无效');
					}
					$postdata [$k] = join(',', $postdata [$k]);
					break;
				case 'richtext' :
					$postdata [$k] = htmlspecialchars_decode(input("$k"));
					break;
				default :
					$this->error('未知字段 ' . $f ['title'] . ':' . $f ['type']);
					break;
			}
            //处理特殊规则-必须
            if (in_array('required', $rules)) {
                switch ($model_fields[$k]['type']) {
                    case 'baidu_map':
                        if (!isset ($postdata [$k . '_lng']) || '' === $postdata [$k . '_lng']) {
                            $this->error($f ['title'] . ' 不能为空');
                        }
                        if (!isset ($postdata [$k . '_lat']) || '' === $postdata [$k . '_lat']) {
                            $this->error($f ['title'] . ' 不能为空');
                        }
                        break;
                    default:
                        if (!isset ($postdata [$k]) || '' === $postdata [$k]) {
                            $this->error($f ['title'] . ' 不能为空');
                        }
                        break;
                }
            }
            //处理特殊规则-唯一
            if (in_array('unique', $rules)) {
                $one = Db::name($model['model_name'])->where(array(
                    $k => $postdata [$k]
                ))->find();
                if ($one) {
                    $this->error($f ['title'] . ' 不能重复');
                }
            }
        }
        $rst=Db::name($model['model_name'])->insert($postdata);
        if($rst!==false){
            $this->success('增加成功',url('cmslist',['id'=>$model_id]));
        }else{
            $this->error('增加失败');
        }
    }	
	//模型编辑
    public function cmsedit()
    {
        $model_id=input('id',0);
		$model=Db::name('model')->where('model_id',$model_id)->find();
		if(empty($model)){
			$this->error('不存在的模型');
		}
		//主键id
		$model_pkid=input($model['model_pk'],0);
		//前台栏目id
        $model_cid=Db::name($model['model_name'])->where($model['model_pk'],$model_pkid)->value($model['model_cid']);
        //cid、order
        $model_fields[$model['model_cid']]=array(
            'name'=>$model['model_cid'],
            'title'=>'前台栏目',
            'type'=>'selecttext',
            'data'=>'menu|id|menu_name|id',
            'description'=>'前台栏目',
            'length'=>100,
            'rules'=>'required',
            'default'=>''
        );
        $model_fields[$model['model_order']]=array(
            'name'=>$model['model_order'],
            'title'=>'排序',
            'type'=>'number',
            'data'=>'',
            'description'=>'排序,越小越靠前',
            'length'=>3,
            'rules'=>'',
            'default'=>50
        );
        //除主键外字段
        $model_fields=array_merge($model_fields,$model['model_fields']?json_decode($model['model_fields'],true):array());
		//可编辑字段
		$model_edit=$model['model_edit']?explode(',',$model['model_edit']):array();
		//字段处理,排除不在表内字段
		$fields = array();
		if(empty($model_edit)){
			$fields=$model_fields;
		}else{
			foreach ($model_edit as &$v) {
				if (isset ($model_fields[$v])) {
					$fields[$v] = $model_fields[$v];
				}
			}
		}
		$field_list=array();
		foreach($fields as $k=>$v){
			if($v['type']=='baidu_map'){
				$field_list[]=$k.'_lng';
				$field_list[]=$k.'_lat';
			}else{
				$field_list[]=$k;
			}
		}
		$data=Db::name($model['model_name'])->field(join(',',$field_list))->where($model['model_pk'],$model_pkid)->find();
		//处理数据
		$fields_data = array();
		foreach ($field_list as $k) {
			if (!isset($model_fields [$k])) {
				$k = substr($k, 0, strrpos($k, '_'));
			}
		    $fields_data [$k] = $model_fields [$k];
            $fields_data [$k] ['rules'] = explode(',', $fields_data [$k] ['rules']);
			switch ($fields_data [$k] ['type']) {
				case 'images':
					$fields_data [$k] ['images'] = array_filter(explode(",", $data [$k]));
                    $fields_data [$k] ['value'] = join(',',$fields_data [$k] ['images']);
					break;
				case 'baidu_map':
					$fields_data [$k] ['default'] = explode(',', $fields_data [$k] ['default']);
					$fields_data [$k] ['value']['lng'] = $data [$k . '_lng'];
					$fields_data [$k] ['value']['lat'] = $data [$k . '_lat'];
					break;
				case 'text' :
                case 'number' :
                case 'switch' :
                case 'date' :
				case 'datetime' :
                case 'bigtext' :
				case 'richtext' :
                case 'large_number' :
				case 'imagefile' :
                    $fields_data [$k] ['value'] = $data [$k];
                    break;
				case 'currency':
                    $fields_data [$k] ['value'] = long_currency($data [$k]);
                    break;
				case 'selectnumber' :
                case 'selecttext' :
                    $fields_data [$k] ['value'] = $data [$k];
                    if($k!=$model['model_cid']){
                        $fields_data [$k] ['option'] = $this->cms_field_option_conv($fields_data [$k] ['data']);
                    }else{
                        $nav = new \Leftnav;
                        $arr=Db::name('menu')->where(['menu_modelid'=>$model_id,'menu_type'=>3])->select();
                        $fields_data [$k] ['option']=$nav::menu_n($arr);
                    }
                    break;
				case 'checkbox' :
                    $fields_data [$k] ['value'] = explode(',', $data [$k]);
                    $fields_data [$k] ['option'] = $this->cms_field_option_conv($fields_data [$k] ['data']);
                    break;
				default :
                    $this->error('未知字段 ' . $fields_data [$k] ['type']);
                    break;
			}
		}
		$this->assign('model_pkid',$model_pkid);
		$this->assign('model_id',$model_id);
		$this->assign('model',$model);
        $this->assign('model_cid',$model_cid);
		$this->assign('fields_data', $fields_data);
		return $this->fetch();
	}
    //模型编辑
    public function cmsrunedit()
    {
        $model_id=input('id',0);
        $model=Db::name('model')->where('model_id',$model_id)->find();
        if(empty($model)){
            $this->error('不存在的模型');
        }
        //主键id
        $model_pkid=input($model['model_pk'],0);
        //除主键外字段
        $model_fields=$model['model_fields']?json_decode($model['model_fields'],true):array();
        //可编辑字段
        $model_edit=$model['model_edit']?explode(',',$model['model_edit']):array();
        //字段处理,排除不在表内字段
        $fields = array();
        if(empty($model_edit)){
            $fields=$model_fields;
        }else{
            foreach ($model_edit as &$v) {
                if (isset ($model_fields[$v])) {
                    $fields[$v] = $model_fields[$v];
                }
            }
        }
        //得到可编辑字段
        $field_list=array_keys($fields);
        //处理postdata
        $postdata=array();
        foreach ($field_list as $k) {
            $f=$model_fields[$k];
            $rules = explode('|', $f ['rules']);
            //非只读时处理input数据
            if(!in_array('readonly', $rules)){
                switch ($f ['type']) {
                    case 'images':
                        //判断是否有传多图
                        $files = request()->file('pic_all');
                        $picall_url='';
                        if(!empty($files)){
                            if(config('storage.storage_open')){
                                //七牛
                                $upload = \Qiniu::instance();
                                $info = $upload->upload();
                                $error = $upload->getError();
                                if ($info) {
									if(!empty($info['pic_all'])) {
										foreach ($info['pic_all'] as $file) {
											$img_url=config('storage.domain').$file['key'];
											$picall_url = $img_url . ',' . $picall_url;
										}
									}else{
										foreach ($info as $file) {
											$img_url=config('storage.domain').$file['key'];
											$picall_url = $img_url . ',' . $picall_url;
										}
									}
                                }else{
                                    $this->error($error);//否则就是上传错误，显示错误原因
                                }
                            }else{
                                $validate = config('upload_validate');
                                //多图
                                foreach ($files as $file) {
                                    $info = $file->validate($validate)->rule('uniqid')->move(ROOT_PATH . config('upload_path') . DS . date('Y-m-d'));
                                    if ($info) {
                                        $img_url = config('upload_path'). '/' . date('Y-m-d') . '/' . $info->getFilename();
                                        //写入数据库
                                        $data['uptime'] = time();
                                        $data['filesize'] = $info->getSize();
                                        $data['path'] = $img_url;
                                        Db::name('plug_files')->insert($data);
                                        $picall_url = $img_url . ',' . $picall_url;
                                    } else {
                                        $this->error($file->getError());//否则就是上传错误，显示错误原因
                                    }
                                }
                            }
                        }
                        $postdata[$k]=input('pic_oldlist','').$picall_url;
                        break;
                    case 'imagefile':
                        $file = request()->file('pic_one');
                        $img_one='';
                        if(!empty($file)){
                            if(config('storage.storage_open')){
                                //七牛
                                $upload = \Qiniu::instance();
                                $info = $upload->upload();
                                $error = $upload->getError();
                                if ($info) {
									if(!empty($info['pic_one'])){
										$img_one= config('storage.domain').$info['pic_one'][0]['key'];
									}else{
										$img_one= config('storage.domain').$info[0]['key'];
									}
                                }else{
                                    $this->error($error);//否则就是上传错误，显示错误原因
                                }
                            }else{
                                $validate = config('upload_validate');
                                //单图
                                $info = $file[0]->validate($validate)->rule('uniqid')->move(ROOT_PATH . config('upload_path') . DS . date('Y-m-d'));
                                if ($info) {
                                    $img_url = config('upload_path'). '/' . date('Y-m-d') . '/' . $info->getFilename();
                                    //写入数据库
                                    $data['uptime'] = time();
                                    $data['filesize'] = $info->getSize();
                                    $data['path'] = $img_url;
                                    Db::name('plug_files')->insert($data);
                                    $img_one = $img_url;
                                } else {
                                    $this->error($file->getError());//否则就是上传错误，显示错误原因
                                }
                            }
                        }
                        if(!empty($img_one)){
                            $postdata[$k]=$img_one;
                        }
                        break;
                    case 'baidu_map':
                        $postdata [$k . '_lng'] = input("${k}_lng", 0, 'floatval');
                        $postdata [$k . '_lat'] = input("${k}_lat", 0, 'floatval');
                        break;
                    case 'text' :
                    case 'bigtext' :
                        $postdata [$k] = input("$k", '', 'trim');
                        break;
                    case 'number' :
                    case 'switch' :
                        $postdata [$k] = input("$k", 0, 'intval');
                        break;
                    case 'large_number' :
                        $postdata [$k] = input("$k", 0);
                        break;
                    case 'currency':
                        $postdata [$k] = currency_long(input("$k", '', 'trim'));
                        break;
                    case 'datetime' :
                    case 'date' :
                        $postdata [$k] = input("$k", '', 'strtotime');
                        break;
                    case 'selectnumber' :
                        $postdata [$k] = input("$k", 0, 'intval');
                        if (!$this->cms_field_option_valid($f ['data'], $postdata [$k])) {
                            $this->error($f ['title'] . ' 无效');
                        }
                        break;
                    case 'selecttext' :
                        $postdata [$k] = input("$k", '', 'trim');
                        if (!$this->cms_field_option_valid($f ['data'], $postdata [$k])) {
                            $this->error($f ['title'] . ' 无效');
                        }
                        break;
                    case 'checkbox' :
                        $postdata [$k] = input("{$k}".'/a', array());
                        if (!$this->cms_field_option_valid($f ['data'], $postdata [$k])) {
                            $this->error($f ['title'] . ' 无效');
                        }
                        $postdata [$k] = join(',', $postdata [$k]);
                        break;
                    case 'richtext' :
                        $postdata [$k] = htmlspecialchars_decode(input("$k"));
                        break;
                    default :
                        $this->error('未知字段 ' . $f ['title'] . ':' . $f ['type']);
                        break;
                }
            }
            //处理特殊规则-必须
            if (in_array('required', $rules)) {
                switch ($model_fields[$k]['type']) {
                    case 'baidu_map':
                        if (!isset ($postdata [$k . '_lng']) || '' === $postdata [$k . '_lng']) {
                            $this->error($f ['title'] . ' 不能为空');
                        }
                        if (!isset ($postdata [$k . '_lat']) || '' === $postdata [$k . '_lat']) {
                            $this->error($f ['title'] . ' 不能为空');
                        }
                        break;
                    default:
                        if (!isset ($postdata [$k]) || '' === $postdata [$k]) {
                            $this->error($f ['title'] . ' 不能为空');
                        }
                        break;
                }
            }
            //处理特殊规则-唯一
            if (in_array('unique', $rules)) {
                $one = Db::name($model['model_name'])->where(array(
                    $k => $postdata [$k]
                ))->find();
                if ($one && $one [$model['model_pk']] != $model_pkid) {
                    $this->error($f ['title'] . ' 不能重复');
                }
            }
        }
        $rst=Db::name($model['model_name'])->where($model['model_pk'],$model_pkid)->update($postdata);
        if($rst!==false){
            $this->success('修改成功',url('cmslist',['id'=>$model_id]));
        }else{
            $this->error('修改失败');
        }
    }
    //模型删除
    public function cmsdel()
    {
        $model_id=input('id',0);
        $model=Db::name('model')->where('model_id',$model_id)->find();
        if(empty($model)){
            $this->error('不存在的模型');
        }
        //主键id
        $model_pkid=input($model['model_pk'],0);
        $rst=Db::name($model['model_name'])->where($model['model_pk'],$model_pkid)->delete();
        if($rst!==false){
            $this->success('删除成功',url('cmslist',['id'=>$model_id]));
        }else{
            $this -> error("删除失败！",url('cmslist',['id'=>$model_id]));
        }
    }
    //全选删除
    public function cmsalldel(){
        $model_id=input('id',0);
        $model=Db::name('model')->where('model_id',$model_id)->find();
        if(empty($model)){
            $this->error('不存在的模型');
        }
        $ids = input($model['model_pk'].'/a');
        if(empty($ids)){
            $this -> error("请选择要删除的ID",url('cmslist',['id'=>$model_id]));
        }
        $ids=is_array($ids)?$ids:(array)$ids;
        $rst=Db::name($model['model_name'])->where($model['model_pk'],'in',$ids)->delete();
        if($rst!==false){
            $this->success("全部删除成功",url('cmslist',['id'=>$model_id]));
        }else{
            $this -> error("删除失败！",url('cmslist',['id'=>$model_id]));
        }
    }
    public function cmsstate(){
        $model_id=input('id',0);
        $model=Db::name('model')->where('model_id',$model_id)->find();
        if(empty($model)){
            $this->error('不存在的模型');
        }
        $model_pkid=input('x');
        $key=input('key');
        $status=Db::name($model['model_name'])->where($model['model_pk'],$model_pkid)->value($key);//判断当前状态情况
        if($status==1){
            $statedata = array($key=>0);
            Db::name($model['model_name'])->where($model['model_pk'],$model_pkid)->setField($statedata);
            $this->success('状态禁止');
        }else{
            $statedata = array($key=>1);
            Db::name($model['model_name'])->where($model['model_pk'],$model_pkid)->setField($statedata);
            $this->success('状态开启');
        }
    }
    //model_fields处理
    private function fields($fields)
    {
        $rst=array();
		foreach($fields as $v){
			//[字段名,字段标题,字段类型,字段数据,字段说明,字段长度,字段规则,默认值]
			$rst[$v[0]]=array(
			    'name'=>$v[0],
                'title'=>$v[1],
                'type'=>$v[2],
                'data'=>$v[3],
                'description'=>$v[4],
                'length'=>$v[5],
                'rules'=>$v[6],
                'default'=>$v[7]
            );
		}
		return $rst;
    }
	//字段选项取标题
	private function cms_field_option_get_titles($data, $value)
	{
		if (!is_array($value)) {
			$value = array(
				$value
			);
		}
		$value = array_unique($value);
		$rets = array();
		if (!empty ($value)) {
			if(stripos($data,'|') != false){
				@list ($model, $vfield, $vtitle,$sort)=explode('|', $data);
				$fields=Db::name($model)->field($vtitle)->where($vfield,'in',$value)->order($sort)->select();
				foreach ($fields as $v){
					$rets [] = $v [$vtitle];
				}
			}else{
				$arr=explode(',', $data);
				$data=array();
				foreach ($arr as $v) {
					@list($kk,$vv)=explode(':', $v);
					$data[$kk]=$vv;
				}
				foreach ($value as $v) {
					$rets [] = isset ($data [$v]) ? $data [$v] : $v;
				}
			}
		}
		return $rets;
	}
	//字段选项转换成数组
	private function cms_field_option_conv($data)
	{
        $rets = array();
	    if(stripos($data,'|') != false){
            @list ($model, $vfield, $vtitle,$sort)=explode('|', $data);
            $fields=Db::name($model)->field($vfield . ',' . $vtitle)->order($sort)->select();
            foreach ($fields as $v){
                $rets [$v [$vfield]] = $v [$vtitle];
            }
        }else{
            $arr=explode(',', $data);
            foreach ($arr as $v) {
                @list($kk,$vv)=explode(':', $v);
                $rets[$kk]=$vv;
            }
        }
        return $rets;
	}
    //检测字段选项是否有效
    private function cms_field_option_valid($data, $value)
    {
        $data=$this->cms_field_option_conv($data);
        if (!is_array($value)) {
            $value = array($value);
        }
        $value = array_unique($value);//去重复
        if (!empty ($value)) {
            foreach ($value as $v) {
                if (!isset ($data [$v])) {
                    return false;
                }
            }
        }
        return true;
    }
}