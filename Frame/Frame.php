<?php 
header('Content-type: text/html;charset=utf-8;');
@session_start();
//路径初始化
//项目根路径
define('ROOT_PATH',substr(str_replace("\\","/",dirname(__FILE__)),0,-6));
//框架根路径
define('FRAME_PATH',str_replace("\\","/",dirname(__FILE__)));
//框架配置文件路径
define('FRAME_CONFIG',FRAME_PATH.'/Config');
//函数库目录
define('FRAME_FUNCTION',FRAME_PATH.'/Function');
//框架核心程序路径
define('FRAME_CORE',FRAME_PATH.'/Lib');
//框架核心类库路径
define('FRAME_CORE_CLASS',FRAME_CORE.'/Class');
//项目运行时路径
define('FRAME_RUNTIME',ROOT_PATH.'/Runtime');
//项目工程名称目录路径
define('PROJECT_NAME','Project');

//挂载全局函数
require_once(FRAME_FUNCTION.'/global.function.php');
//挂载数据库类
require_once(FRAME_CORE_CLASS.'/Db/Mysql.class.php');
//挂载控制器基类
require_once(FRAME_CORE_CLASS.'/Controller.class.php');
//挂载模型基类
require_once(FRAME_CORE_CLASS.'/Model.class.php');
//挂载核心类
require_once(FRAME_CORE_CLASS.'/Frame.class.php');
//挂载模块别名
$models_alias = include(FRAME_RUNTIME.'/Data/models_alias.php');
//挂载使用别名的模块
$use_alias_models = include(FRAME_RUNTIME.'/Data/use_alias_models.php');

//应用，控制器，方法名称
//URL类型
$urlType = config('URL_TYPE');
//URL后缀
$urlSuffix = config('URL_SUFFIX');
$url_param = array();
switch($urlType)
{
	//普通模式
	case 0:
		$show_index = strrpos($_SERVER['REQUEST_URI'],'?');
		$urlstr = substr($_SERVER['REQUEST_URI'],$show_index,strlen($_SERVER['REQUEST_URI']));
		$urlInfo = str_replace(array('?application=','&controller=','&action='),'/',$urlstr);
		$urlInfo = str_replace(strstr($urlInfo,'&'),'',$urlInfo);
		break;
	//pathinfo模式
	case 1:
		$urlInfo = $_SERVER['REQUEST_URI'];
		break;	
	//rewrite重写模式
	case 2:
		$rewriteFile = ROOT.'/Config/url_rewrite.config.php';
		if(!file_exists($rewriteFile))
		{
			die("URL重写配置不存在！");
		}
		//url规则
		$rewriteRule = include($rewriteFile);
		$urlInfo = str_replace(array('/',$urlSuffix),'',$_SERVER['REQUEST_URI']);
		//存在重写情况下
		if($rewriteRule[$urlInfo])
		{
			$url_infos = $rewriteRule[$urlInfo];
			$show_index = strrpos($url_infos,'?');
			$urlstr = substr($url_infos,$show_index,strlen($url_infos));
			$urlInfo = str_replace(array('?application=','&controller=','&action='),'/',$urlstr);
			$urlInfo = str_replace(strstr($urlInfo,'&'),'',$urlInfo);
		}
		else
		{
			//不存在重写的情况下，分析url
			$url_rewrite_info = explode('-',$urlInfo);
			@list($action,$paramstr) = explode('?',$url_rewrite_info[2]);
			$url_rewrite_info[2] = $action;
			if($paramstr)
			{
				$arr = explode('&',$paramstr);	
				foreach($arr as $key=>$val)
				{
					$val = str_replace('=','_',$val);
					array_push($url_rewrite_info,$val);
				}
			}
			foreach($url_rewrite_info as $key=>$val)
			{
				if($key<=2)
				{
					$urlInfo .= '/'.ucfirst($val);
				}
				else
				{
					@list($param_key,$param_val) = explode('_',$val);
					//模拟GET值
					$_REQUEST[$param_key] = $param_val;
					$_GET[$param_key] = $param_val;
				}
			}
		}
		break;
	//兼容模式
	case 3:
		$urlInfo = $_SERVER['REQUEST_URI'];
		break;
	default:
		$urlInfo = $_SERVER['REQUEST_URI'];
		break;
}

//到URL地址为普通参数地址是处理
$urlInfo = str_replace(array('index.php?application=','/index.php?application=','&controller=','&action='),'/',strtolower($urlInfo));

//echo $urlInfo;exit;
//$url_info = explode('/',substr($urlInfo,1));
$url_info = explode('/',substr($urlInfo,0));
//$url_info = explode('/',substr($urlInfo,1));

$urlInfo_arr = explode('&',$url_info[3]);
if(is_array($urlInfo_arr))
{
	$url_info[3] = $urlInfo_arr[0];
}
$url_info[0] = $url_info[0] ? $url_info[0] : $url_info[1].'-'.$url_info[2].'-'.$url_info[3];
$url__info = array($url_info[0],$url_info[1],$url_info[2],$url_info[3]);
//Fprint($url__info);exit;
//url模式
$url_type = config('URL_TYPE');
if(count($url__info) >= 4)
{
	//@list($projectName, $applicationName, $controllerName, $actionName) = explode('/',substr($urlInfo,1));
	$projectName	 	= $url__info[0];
	$applicationName 	= $url__info[1];
	$controllerName 	= $url__info[2];
	$actionName 		= $url__info[3];
}
else
{
	//@list($applicationName, $controllerName, $actionName) = explode('/',substr($urlInfo,1));
	$applicationName 	= $url__info[0];
	$controllerName 	= $url__info[1];
	$actionName 		= $url__info[2];
}

//检测使用别名的模块，加以绕道隔墙
if(in_array(ucfirst($applicationName),$use_alias_models)) die('Access denied!');
//工程入口
define('PROJECT_INDEX',$projectName ? $projectName : 'index.php');
//当前模块名称
@define('APPLICATION_NAME',$applicationName ? $models_alias[ucfirst($applicationName)] ? $models_alias[ucfirst($applicationName)] : ucfirst($applicationName) : DEFAULT_MODULE);
//当前控制器名称
define('CONTROLLER_NAME',$controllerName ? ucfirst($controllerName) : 'Index');
//当前方法名称
define('ACTION_NAME',$actionName ? $actionName : 'index');
//当前请求的URL
define('REQUEST_URL',url(APPLICATION_NAME.'/'.CONTROLLER_NAME.'/'.ACTION_NAME,$_GET));
//POST请求
define('POST_REQUEST',$_SERVER['REQUEST_METHOD'] == 'POST' ? true : false);
//GET请求
define('GET_REQUEST',$_SERVER['REQUEST_METHOD'] == 'GET' ? true : false);

/*
//当前模块名称
@define('APPLICATION_NAME',ucfirst($applicationName));
//当前控制器名称
define('CONTROLLER_NAME',ucfirst($controllerName));
//当前方法名称
define('ACTION_NAME',$actionName);
*/
?>