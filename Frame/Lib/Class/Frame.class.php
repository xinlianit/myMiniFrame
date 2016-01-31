<?php 
/*********************************************************
 * 		Time  	: 2015-01-11							 *
 * 		Author	: jirenyou							 	 *
 * 		Email	: 390066398@qq.com						 *
**********************************************************/
namespace Frame;
/*
 * 框架核心类
 * */
class Frame
{
	/*
	 * 启动运行
	 * */
	public static function run()
	{
		//生成工程套装(模块包、类库包)
		//$applications = explode('|',APPLICATIONS);
		//加载模块列表
		@$applications = include(PORJECT_ROOT.'/Config/modules.config.php');
		//如果未发现模块列表，则默认生成Home模块应用
		if(!$applications)
		{
			$applications = array('Home');
		}
		
		//应用生成路径
		$application_path = PORJECT_ROOT.'/Project/';
		//模块
		$models = array();
		//模块别名包
		$models_alias = array();
		//使用别名的模块
		$use_alias_models = array();
		
		//模块生成状态集
		$status = array();
		foreach($applications as $k=>$v)
		{
			//别名处理
			@list($k,$v) = explode(':',$v);
			$models[$k] = $v;
			//该模块设置了别名
			if(isset($v))
			{
				$models_alias[ucfirst($v)] = $k;
				array_push($use_alias_models,$k);
			}
			
			//模块包路径
			$dir = array($application_path.ucfirst($k),$application_path.($k).'/Model',$application_path.ucfirst($k).'/Controller');
			
			//生成模块
			foreach($dir as $val)
			{
				if(!createDir($val))
				{
					//生成错误时，抛出错误状态
					array_push($status,0);
					//写入日志
					writeLog($val.' 目录创建失败 ×');
				}
			}
		}
		
		//生成类库包
		$libs = array(
			//类库路径
			PORJECT_ROOT.'/Lib/Class',
			//函数库路径
			PORJECT_ROOT.'/Lib/Function',
			//配置文件路径
			PORJECT_ROOT.'/Config',
			//smarty配置路径
			PORJECT_ROOT.'/Config/SmartyConfig',
			//皮肤路径
			PORJECT_ROOT.'/Skin',
			//css路径
			PORJECT_ROOT.'/Skin/css',
			//js路径
			PORJECT_ROOT.'/Skin/js',
			//图片路径
			PORJECT_ROOT.'/Skin/images',
			//Smarty模板路径
			PORJECT_ROOT.'/Skin/Templates',
			//默认皮肤模板
			PORJECT_ROOT.'/Skin/Templates/Default',
			//Smarty模板编译路径
			PORJECT_ROOT.'/Skin/Templates_c/Default',
			//运行时目录
			PORJECT_ROOT.'/Runtime',
			//运行时目录缓存目录
			PORJECT_ROOT.'/Runtime/Cache',
			//运行时目录数据目录
			PORJECT_ROOT.'/Runtime/Data',
			//运行时目录日志目录
			PORJECT_ROOT.'/Runtime/Logs',
			//运行时目录临时目录
			PORJECT_ROOT.'/Runtime/Temp',
		);
		foreach($libs as $v)
		{
			if(!createDir($v))
			{
				//生成错误时，抛出错误状态
				array_push($status,0);
				//写入日志	
				writeLog($v.' 目录创建失败 ×');
			}
		}
		
		//生成模块别名包
		setArray($models_alias,PORJECT_ROOT.'/Runtime/Data/','models_alias.php');
		//生成使用别名的模块集合
		setArray($use_alias_models,PORJECT_ROOT.'/Runtime/Data/','use_alias_models.php');
		
		//创建目录出现错误时
		if(!empty($status))
		{
			echo "至少有".count($status)."个目录创建失败，请查看日志详情！";
		}
		
		//生成默认配置文件
		$defaultConfigName = 'config.php';
		$configPath = PORJECT_ROOT.'/Config/';
		if(!file_exists($configPath.$defaultConfigName))
		{
			//默认配置文件不存在时生成默认配置文件
			setArray(array(),$configPath,$defaultConfigName);
		}
		
		//生成模块列表配置
		$modulesConfigName = 'modules.config.php';
		if(!file_exists($configPath.$modulesConfigName))
		{
			//默认配置文件不存在时生成默认配置文件
			setArray($applications,$configPath,$modulesConfigName);
		}
		
		//在默认及每个模块下面生成默认欢迎Index控制器
		foreach($applications as $k=>$v)
		{
			//别名处理
			@list($k,$v) = explode(':',$v);
			
			//控制器代码
			$index_controller = "<?php\r\nnamespace ".ucfirst($k)."\Controller;\r\nuse Frame\Controller;\r\nclass IndexController extends Controller\r\n{\r\n	public function index()\r\n	{\r\n		echo 'Congratulations! Create project success, welcome to Frame<br/>您当前访问的是：<span style=\"font-size:14px;font-weight:bold;\">".ucfirst($k)."</span> 模块下的 <span style=\"font-size:14px;font-weight:bold;\">Index</span> 控制器下的 <span style=\"font-size:14px;font-weight:bold;\">index</span> 方法！';\r\n	}\r\n}\r\n?>";
			//控制器名称
			$controller_name = PORJECT_ROOT . '/Project/'.ucfirst($k).'/Controller/IndexController.class.php';
			if(!file_exists($controller_name))
			{
				file_put_contents($controller_name,$index_controller);
			}
		}
		
		//派生分发到控制器
		$controller_class = '\\'.APPLICATION_NAME.'\\Controller\\'.CONTROLLER_NAME.'Controller';
		$controller_action = ACTION_NAME;
		//实例化对象
		$Object = new $controller_class;
		//执行当前控制函数
		$Object->$controller_action();
	}
	
}
?>