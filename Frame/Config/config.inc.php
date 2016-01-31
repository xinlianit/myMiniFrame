<?php 
/*********************************************************
 * 		Time  	: 2015-01-11							 *
 * 		Author	: jirenyou							 	 *
 * 		Email	: 390066398@qq.com						 *
**********************************************************/
/*
 * 框架的相关相关配置
 * */
//项目配置文件
@$dbconfig = include(ROOT_PATH.'/Public/mysql.config.php');	//数据库配置
$objectconfig = include(PORJECT_ROOT.'/Config/config.php');	//项目配置
$config = array();
//数据库配置
$config['db'] = array(
	array(
		'type'			=> @$dbconfig['DB_TYPE'] ? $dbconfig['DB_TYPE'] : 'mysql',
		'host'			=> @$dbconfig['DB_HOST'] ? $dbconfig['DB_HOST'] : 'localhost',
		'port'			=> @$dbconfig['DB_PORT'] ? $dbconfig['DB_PORT'] : '3306',
		'username'		=> @$dbconfig['DB_USER'] ? $dbconfig['DB_USER'] : 'root',
		'password'		=> @$dbconfig['DB_PWD'] ? $dbconfig['DB_PWD'] : 'root',
		'dbname'		=> @$dbconfig['DB_NAME'] ? $dbconfig['DB_NAME'] : '',
		'prefix'		=> @$dbconfig['DB_PREFIX'] ? $dbconfig['DB_PREFIX'] : '',
		'char'			=> @$dbconfig['DB_CHAR'] ? $dbconfig['DB_CHAR'] : 'UTF8',
	),
);
return array_merge($config,$objectconfig);
?>
