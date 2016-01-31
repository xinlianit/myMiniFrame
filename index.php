<?php
/************************************
 * 				入口文件					*
 ************************************/
header('Content-type:text/html;charset=utf-8;');
/******************************** 环境配置初始化 ********************************/
// 检测PHP环境
if(version_compare(PHP_VERSION,'5.3.0','<'))  die('require PHP > 5.3.0 !');

//php.ini 设置
@ini_set('default_charset','utf-8');								//编码设置
@ini_set('memory_limit', '128M');									//php使用内存
@ini_set('max_execution_time',0);  									//最大执行时间

/******************************** 环境配置初始化 ********************************/


/******************************** 系统运行初始化 ********************************/

//定义跟路径
define('ROOT',str_replace('\\','/',getcwd()));
//当前项目路径
define('PORJECT_ROOT',str_replace('\\','/',getcwd()));

//框架路径
define('FRAME_DIR','./Frame');

//开发调试，开发时请打开DEBUG，生产环境请关闭DEBUG
define('DEBUG',true);

//设置报错级别,开发时默认为E_ALL，生产环境请设置为0 关闭报错
error_reporting(E_ERROR | E_WARNING | E_PARSE);

//应用项目列表
define('DEFAULT_MODULE','Home');   //默认访问模块
//需新增应用时在此次添加应用名称（如：Home|Admin）,支持别名：Admin:administrtor
//define('APPLICATIONS','Common|Home|Member');
//Skin皮肤路径
define('SKIN_PATH', '/Skin');
//Public路径
define('PUBLIC_PATH',ROOT . '/Public');
//Runtime路径
define('RUNTIME_PATH',ROOT . '/Runtime');

/******************************** 系统运行初始化 ********************************/

//ini_set('memory_limit', '1280M');

//加载框架初始化文件
require_once(FRAME_DIR.'/Frame.php');

//当前模板路径
define('TEMPLATE_PATH','');

//启动运行
Frame\Frame::run();

?>