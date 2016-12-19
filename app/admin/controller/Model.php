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
        //主键
        $model_pk = input('model_pk', '');
        if (empty ($model_pk)) {
            $this->error('请设置主键');
        }
        //引擎
        $model_engine = input('model_engine', 'MyISAM');
        //字段数组
        $model_fields = input('fields/a') ?: array();
        if (empty ($model_fields)) {
            $this->error('请设置模型字段');
        }
        //保存到model数据表中
        $sl_data=array(
            'model_name'=>$model_name,
            'model_title'=>input('model_title',''),
            'model_pk'=>$model_pk,
            'model_sort'=>input('model_sort',''),
            'model_fields'=>json_encode($model_fields),
            'model_indexes'=>input('model_indexes',''),
            'template_list'=>input('template_list',''),
            'template_add'=>input('template_add',''),
            'template_edit'=>input('template_edit',''),
            'search_key'=>input('search_key',''),
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

                    foreach ($model_fields as $f => $fi) {
                        $rules = explode('|', $fi ['rules']);
                        switch ($fi ['type']) {
                            //百度地图字段，双精度型
                            case 'baidu_map':
                                $defaults = explode(',', $fi['default']);
                                $sql_fields [] = "`${f}_lng` DOUBLE NOT NULL DEFAULT '$defaults[0]' COMMENT '$fi[title]'";
                                $sql_fields [] = "`${f}_lat` DOUBLE NOT NULL DEFAULT '$defaults[1]' COMMENT '$fi[title]'";
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
                                }
                                $sql_fields [] = "`$f` BIGINT $funsigned $fnull DEFAULT '$fi[default]' COMMENT '$fi[title]'";
                                break;
                            //整数型
                            case 'number' :
                            case 'datetime' :
                            case 'date' :
                            case 'selectnumber' :
                            case 'treeparent':
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
                                }
                                $sql_fields [] = "`$f` INT $funsigned $fnull DEFAULT '$fi[default]' COMMENT '$fi[title]'";
                                break;
                            //text型
                            case 'richtext' :
                            case 'bigtext' :
                            case 'images':
                                $sql_fields [] = "`$f` TEXT COMMENT '$fi[title]'";
                                break;
                            //TINYINT型
                            case 'switch' :
                                $sql_fields [] = "`$f` TINYINT UNSIGNED NOT NULL DEFAULT '$fi[default]' COMMENT '$fi[title]'";
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
                            'checkbox',
                            'treeparent',
                        ))) {
                            //不重复
                            if (in_array('unique', $rules)) {
                                $sql_unique_key [] = "UNIQUE KEY $f ($f)";
                            }
                        }
                    }
                    //索引数组
                    $model_indexes = input('fields_indexes/a') ?: array();
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
        $this->success('创建模型成功');
    }
}