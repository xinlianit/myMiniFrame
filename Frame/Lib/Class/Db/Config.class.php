<?php
/*********************************************************
 * 		Time  	: 2015-01-11							 *
 * 		Author	: jirenyou							 	 *
 * 		Email	: 390066398@qq.com						 *
**********************************************************/
/*
 * 读写数据库配置类
 * */
namespace Frame\Db;
class Config
{
	protected static $Ins = null;			//申明一个静态变量用来存放该类的实例对象
	protected $config = array();			//申明一个数组变量用来存放配置数据
	
	//加载配置信息
	final protected function __construct()
	{
		//读取配置信息
		$this->config = include(FRAME_PATH.'/Config/config.inc.php');
	}
	
	//克隆对象
	final protected function __clone()
	{
		//克隆成新对象需要执行的程序
	}
	
	//get魔术方法，获取索引
	public function __get($key)
	{
		//所传的键名是否存在该配置数组变量中
		if(array_key_exists($key,$this->config))
		{
			return $this->config[$key];
		}
		else
		{
			return  null;
		}
	}
	
	//set魔术方法，设置索引
	public function __set($key,$val)
	{
		return $this->config[$key] = $val;
	}
	
	//获取类的实例对象
	public static function getIns()
	{
		//判断变量是否类的实例对象
		if(self::$Ins instanceof self)
		{
			//返回自身对象
			return self::$Ins;
		}
		else
		{
			//实例化自己对象赋值给变量
			self::$Ins = new self();
			return self::$Ins;
		}
	}
}
?>