<?php 
/*********************************************************
 * 		Time  	: 2015-02-12							 *
 * 		Author	: jirenyou							 	 *
 * 		Email	: 390066398@qq.com						 *
**********************************************************/
namespace Frame;
/*
 * 控制器基类
 * */
class Controller
{
	public $Smarty;
	/*
	 * 控制器初始化
	 * */
	public function __construct()
	{
		//创建Smarty实例对象
		$this->Smarty = CreateSmarty();
		//初始化函数
		$this->_init();
	}
	
	/*
	 * 初始化控制器
	 * */
	public function _init()
	{

	}
	
	/*
	 * 页面重定向
	 * */
	public function redirect($url=null,$param=array())
	{
		$url = url($url,$param);
		header('Location: '.$url);
	}
	
	/*
	 * 获取模板内容
	 * @param string $template_name		模板名称
	 * return string
	 * */
	public function fetch($template_name=null)
	{
		return $this->Smarty->fetch($template_name);
	}
	
	/*
	 * 变量赋值
	 * @param string $name				变量名称
	 * @param string or array $value	变量值
	 * */
	public function assign($name=null,$value=null)
	{
		$this->Smarty->assign($name,$value);
	}
	
	/*
	 * 模板显示
	 * @param string $template_name		 模板名称
	 * */
	public function display($template_name=null)
	{
		$templates_dir = $this->siteInfo['admintemplate'] ? $this->siteInfo['admintemplate'] : 'Default';
		//默认模板
		$templates = $templates_dir.'/'.APPLICATION_NAME.'/'.CONTROLLER_NAME.'/'.ACTION_NAME.'.tpl';
		if(!empty($template_name))
		{
			$templates = $template_name;
		}
		$this->Smarty->display($templates);
	}
}
?>