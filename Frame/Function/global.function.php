<?php 
include('function.php');
if(file_exists(ROOT.'/Lib/Function/function.php')) include(ROOT.'/Lib/Function/function.php');
//注册自动加载
spl_autoload_register('autoload');
//自动加载类（当调用未定义类）
function autoload($ClassName)
{
	//判断类是，控制器、模型、基类
	if(strtolower(substr($ClassName,-5)) == 'model' && strtolower($ClassName) !== 'model')
	{
		//加载应用模型
		if(file_exists(PORJECT_ROOT.'/'.PROJECT_NAME.'/'.$ClassName.'.class.php'))
		{
			require_once(PORJECT_ROOT.'/'.PROJECT_NAME.'/'.$ClassName.'.class.php');
		}
	}
	else if(strtolower(substr($ClassName,-10)) == 'controller' && strtolower($ClassName) !== 'controller')
	{
		//加载应用控制器
		if(file_exists(PORJECT_ROOT.'/'.PROJECT_NAME.'/'.$ClassName.'.class.php'))
		{
			require_once(PORJECT_ROOT.'/'.PROJECT_NAME.'/'.$ClassName.'.class.php');
		}
	}
	else
	{
		$namespace = explode('\\',$ClassName);
		$count = count($namespace);
		$lib_path = "";
		for($i=0;$i<$count-1;$i++)
		{
			if($namespace[$i] != 'Frame')
			{
				$lib_path .= $namespace[$i] . '/';
			}
		}
		$className = $namespace[$count-1];
		//加载基类
		if(in_array('Frame',$namespace))
		{
			require_once(ROOT_PATH.'/Frame/Lib/Class/'.$lib_path.$className.'.class.php');
		}
	}
}
?>