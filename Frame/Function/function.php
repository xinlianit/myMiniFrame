<?php 
/*********************************************************
 * 		Time  	: 2015-01-20							 *
 * 		Author	: jirenyou							 	 *
 * 		Email	: 390066398@qq.com						 *
 * 		全局函数											 *
**********************************************************/
/*
 * 递归创建目录
 * @param string $dir   		目录路径名称
 * */
function createDir($dir)
{
	//目录已存在
	if(is_dir($dir))
	{
		return true;	
	}
	
	//目录不存在
	if(!is_dir(dirname($dir)))
	{
		createDir(dirname($dir));
	}
	
	return mkdir($dir);
}

/*
 * 目录操作
 * @param string $type					操作类型
 * @param string $sourcePath			源目录
 * @param string $targetPath			目标目录
 * */
function dirOperations($type='copy',$sourcePath=null,$targetPath=null)
{
	/*检查要移动的额目录是否存在*/
    if(!file_exists($sourcePath))return 2;  //源文件或目录不存在
    /*检查移动后的存储目录是否存在,不存在自动创建*/
    if(!empty($targetPath))
    {
    	if(!file_exists($targetPath))
    	{
    		if(!createDir($targetPath))
    		{
    			return 3;	//目标目录或文件打开失败
    		}
    	}
    }
    /*打开目录并获取文件*/
    if(!$dir        = @opendir($sourcePath))return 4;//打开源目录失败
    
	switch($type)
	{
		//拷贝文件
		case 'copy':
		    $files        =array();    //用来存储目录下的文件
		    $dirs        =array();    //用来存储目录下的子目录
		    if(false!=$dir){
		        while($item    =readdir($dir)){
		            $itemPath    =$sourcePath.'/'.$item;
		            if($item!='.'&&$item!='..'){
		                if(filetype($itemPath)=='file'){
		                    $files[]    =$item;
		                }elseif(filetype($itemPath)=='dir'){
		                    $dirs[]        =$item;
		                }
		            }
		        }
		        @closedir($dir);
		    }
		    /*复制文件到目标地址*/
		    foreach($files as $file){
		        @copy($sourcePath.'/'.$file,$targetPath.'/'.$file);    //拷贝文件到目标地址
		    }
		     
		    /*递归处理子目录*/
		    if(sizeof($dirs)>0){
		        foreach($dirs  as $childDir){
		            dirOperations($type,$sourcePath.'/'.$childDir,$targetPath.'/'.$childDir);
		        }
		    }
		    return true;
			break;
		//移动文件
		case 'move':
			$files        =array();    //用来存储目录下的文件
		    $dirs        =array();    //用来存储目录下的子目录
		    if(false!=$dir){
		        while($item    =readdir($dir)){
		            $itemPath    =$sourcePath.'/'.$item;
		            if($item!='.'&&$item!='..'){
		                if(filetype($itemPath)=='file'){
		                    $files[]    =$item;
		                }elseif(filetype($itemPath)=='dir'){
		                    $dirs[]        =$item;
		                }
		            }
		        }
		        @closedir($dir);
		    }
		    /*复制文件到目标地址*/
		    foreach($files as $file){
		        @copy($sourcePath.'/'.$file,$targetPath.'/'.$file);    //拷贝文件到目标地址
		        @unlink($sourcePath.'/'.$file);                    //删除原始文件
		    }
		    
		    /*递归处理子目录*/
		    if(sizeof($dirs)>0){
		        foreach($dirs  as $childDir){
		            dirOperations($type,$sourcePath.'/'.$childDir,$targetPath.'/'.$childDir);
		        }
		    }
		    //删除当前目录
		    @rmdir($sourcePath);
		    return true;
			break;
		//删除文件
		case 'delete':
			$files        =array();    //用来存储目录下的文件
		    $dirs        =array();    //用来存储目录下的子目录
			if(false!=$dir){
		        while($item    =readdir($dir)){
		            $itemPath    =$sourcePath.'/'.$item;
		            if($item!='.'&&$item!='..'){
		                if(filetype($itemPath)=='file'){
		                    $files[]    =$item;
		                }elseif(filetype($itemPath)=='dir'){
		                    $dirs[]        =$item;
		                }
		            }
		        }
		        @closedir($dir);
		    }
		    /*复制文件到目标地址*/
		    foreach($files as $file){
		        @unlink($sourcePath.'/'.$file);                    //删除原始文件
		    }
		    
		    /*递归处理子目录*/
		    if(sizeof($dirs)>0){
		        foreach($dirs  as $childDir){
		            dirOperations($type,$sourcePath.'/'.$childDir);
		        }
		    }
		    //删除当前目录
		    @rmdir($sourcePath);
		    return true;
			break;	
	}
}

/*
 * 保存数组文件(只支持四维以下数组含四维)
 * @param array $array  		数组元素
 * @param string $file   		保存路径
 * @param string $filename     文件名
 * return boole
 * */
function setArray($array=array(), $filepath=null, $filename=null)
{
	//检测目录是否存在
	createDir($filepath);
	
	$arrayContent = "<?php\r\n";
	$arrayContent.= "	return array(\r\n";
	
	foreach($array as $key=>$val)
	{
		//如果是数组
		if(is_array($val))
		{
			//分解数组
			$arrayContent .= "		'".$key."' => array(\r\n";
			foreach($val as $key1=>$val1)
			{
				//如果是数组
				if(is_array($val1))
				{
					//分解数组
					$arrayContent .= "				'".$key1."' => array(\r\n";
					foreach($val1 as $key2=>$val2)
					{
						//如果是数组
						if(is_array($val2))
						{
							//分解数组
							$arrayContent .= "						'".$key2."' => array(\r\n";
							foreach($val2 as $key3=>$val3)
							{
								$arrayContent .= "								'".$key3."'				=> '".$val3."',\r\n";
							}
							$arrayContent .= "						),\r\n";
						}
						else
						{
							$arrayContent .= "						'".$key2."'				=> '".$val2."',\r\n";
						}
					}
					$arrayContent .= "				),\r\n";
				}
				else
				{
					$arrayContent .= "				'".$key1."'				=> '".$val1."',\r\n";
				}
			}
			$arrayContent .= "		),\r\n";
		}
		else
		{
			$arrayContent.= "		'".$key."'				=> '".$val."',\r\n";
		}
	}
	
	$arrayContent.= "	);\r\n";
	$arrayContent.= "?>"; 
	$filepath = empty($filepath) ? ROOT.'/'.$filename : $filepath.$filename;
	if(@file_put_contents($filepath,$arrayContent))
	{
		return true;	
	}
	return false;
}

/*
 * 写入日志
 * @param string $content	 日志内容
 * */
function writeLog($content=null)
{
	//日志目录
	$logDir 	= ROOT.'/Runtime/Logs/';
	//日志名称
	$logName 	= date('Y-m-d').'.log';
	//日志内容 
	$content 	= date('Y-m-d H:i:s',time())." ".$content."\r\n";
	//确保创建目录
	createDir($logDir);
	//写入日志
	return file_put_contents($logDir.$logName,$content,FILE_APPEND);
}

/*
 * 配置读取
 * @param string $paramName				配置变量名称
 * return string or array
 * */
function config($paramName=null)
{
	//读取配置文件
	$config = include(FRAME_CONFIG.'/config.inc.php');
	return @$config[$paramName];
}

/*
 * 地址路由
 * @param string $url		url地址
 * @param array $param		地址参数
 * return string
 * */
function url($url=null,$param=array())
{
	//路径模式
	$url_type = config('URL_TYPE');
	//解析重写路径(应用名称，控制器名称，方法名称)
	$urlInfo = explode('/',$url);
	switch($url_type)
		{
			//普通模式
			case 0:
				//地址识别
				if(count($urlInfo) == 1)
				{
					//当前控制器方法
					$url = './index.php?application='.APPLICATION_NAME.'&controller='.CONTROLLER_NAME.'&action='.$urlInfo[0];
				}
				else if(count($urlInfo) == 2)
				{
					//当前应该下控制器和方法
					$url = './index.php?application='.APPLICATION_NAME.'&controller='.$urlInfo[0].'&action='.$urlInfo[1];
				}
				else if(count($urlInfo) == 3)
				{
					//重定向应用
					$url = './index.php?application='.$urlInfo[0].'&controller='.$urlInfo[1].'&action='.$urlInfo[2];
				}
				
				//url参数连接
				if(!empty($param))
				{
					//url参数是否编码
					$urlcode = config('URL_ENCODE');
					/* 
					foreach($param as $key=>$val)
					{
						if($urlcode)
						{
							$val = urlencode($val);
						}
						$url .= '&'.$key.'='.$val;
					}
					*/
					foreach($param as $val)
					{
						@list($key, $value) = explode('=',$val);
						if($urlcode)
						{
							$value = urlencode($value);
						}
						$url .= '&'.$key.'='.$value;
					}
				}
				break;
			//pathinfo模式
			case 1:
				//地址识别
				if(count($urlInfo) == 1)
				{
					//当前控制器方法
					$url = './index.php/'.APPLICATION_NAME.'/'.CONTROLLER_NAME.'/'.$urlInfo[0];
				}
				else if(count($urlInfo) == 2)
				{
					//当前应该下控制器和方法
					$url = './index.php/'.APPLICATION_NAME.'/'.$urlInfo[0].'/'.$urlInfo[1];
				}
				else if(count($urlInfo) == 3)
				{
					//重定向应用
					$url = './index.php/'.$urlInfo[0].'/'.$urlInfo[1].'/'.$urlInfo[2];
				}
				
				//url参数连接
				if(!empty($param))
				{
					//url参数是否编码
					$urlcode = config('URL_ENCODE'); 
					/*
					foreach($param as $key=>$val)
					{
						if($urlcode)
						{
							$val = urlencode($val);
						}
						$url .= '/'.$key.'/'.$val;
					}
					*/
					foreach($param as $val)
					{
						@list($key, $value) = explode('=',$val);
						if($urlcode)
						{
							$value = urlencode($value);
						}
						$url .= '/'.$key.'/'.$value;
					}
				}
				break;
			//重写模式
			case 2:
				$url = str_replace('/','-',$url);
				if(!empty($param))
				{
					foreach($param as $val)
					{
						@list($key,$val) = explode('=',$val);
						$url .= '-'.($key).'_'.$val;
					}
				}
				$url .= config('URL_SUFFIX');
				break;
			//兼容模式
			case 3:
				break;
			default:
				exit("adsf");
				//header('Location: '.$url);
				break;
		}
		return $url;
}

/*
 * 当前url地址增加删除参数
 * @param arrty $data		参数
 * return string
 * */
function urlMarge($url=null,$data=array())
{
	$url = $url ? $url : str_replace(config('URL_SUFFIX'),'',$_SERVER['REQUEST_URI']);
	switch(config('URL_TYPE'))
	{
		//0：普通模式
		case 0:
			$new_url = $_SERVER['PHP_SELF'].'?';
			foreach($data as $k=>$v)
			{
				unset($data[$k]);
				@list($k,$v) = explode('=',$v);	
				$data[$k] = 	$v;		
			}
			$param = array_merge($_GET,$data);
			foreach($param as $k=>$v)
			{
				if(!empty($v))
				{
					$new_url .= $k.'='.$v.'&';
				}
			}
			$new_url = substr($new_url,0,-1);
			break;
		//1：pathinfo模式
		case 1:
			//http://www.11.com/index.php/mall/action/index
			$new_url = $_SERVER['REQUEST_URI'];
			foreach($data as $v)
			{
				unset($data[$k]);
				@list($k,$v) = explode('=',$v);	
				$data[$k] = 	$v;	
			}
			foreach($data as $k=>$v)
			{
				if(!empty($v))
				{
					$new_url .= '/'.$k.'/'.$v;
				}
			}
			$new_url = substr($new_url,0,-1);
			break;
		//2：rewrite重写模式
		case 2:
			//新请求参数
			foreach($data as $key=>$val)
			{
				@list($k,$v) = explode('=',$val);
				$data[$k] = $v;
				unset($data[$key]);
			}
			
			//原请求参数
			if(@$ld_param = explode('-',$url))
			{
				foreach(@$ld_param as $key=>$val)
				{
					if($key<=2)
					{
						//请求地址
						$old_url .= $val.'-';
					}
					else
					{
						@list($k,$v) = explode('_',$val);
						$old_data[$k] = $v;
						unset($ld_param[$key]);
					}
				}
				$old_url = substr($old_url,0,-1);
			}
			
			//合并新旧参数
			$old_data = $old_data ? $old_data : array();
			@$url_param = array_merge($old_data,$data);
			
			if(!empty($url_param))
			{
				foreach($url_param as $key=>$val)
				{
					$old_url .=  '-'.$key.'_'.$val;
				}
			}
			
			$old_url = str_replace(config('URL_SUFFIX'),'',$old_url);
			//$new_url = $old_url.config('URL_SUFFIX');
			$new_url = $old_url;
			break;
		//3：兼容模式
		case 3:
				break;
	}
	return $new_url;
}

/*
 * 实例化模型
 * @param string $modelName			模型名称
 * @param boole $mod				是否用户定义表模型
 * return boject
 * */
function model($modelName=null,$mod=false)
{
	if($mod)
	{
		//用户定义数据表模型
		//$model_class = '\\'.APPLICATION_NAME.'\\Model\\'.$modelName.'Model';
		$model_class = '\\'.APPLICATION_NAME.'\\Model\\'.$modelName.'Model';
		$model = new $model_class;
	}
	else
	{
		//无需数据表模型
		$model = \Frame\Db\Mysql::getIns($modelName);
	}
	
	return $model;
}

/*
 * 变量数据打印输出
 * @param string or array or object .... $data   	需要打印输出的数据
 * return string
 * */
function Fprint($data=null)
{
	echo "<pre>";
	print_r($data);
	echo "</pre>";
}

/*
 * 变量数据和类型打印输出
 * @param string or array or object .... $data   	需要打印输出的数据
 * return string
 * */
function Fdump($data=null)
{
	echo "<pre>";
	var_dump($data);
	echo "</pre>";
}

/*
 * 获取及设置session值
 * @param string $name 					session名称
 * @param string or array $value		session值
 * return string or array or boole
 * */
function session_get_set($name=null,$value=null)
{
	@session_start();
	if(!empty($value))
	{
		$_SESSION[$name] = $value;
		return Finput('session',$name);
	}
	return Finput('session',$name);
}

/*
 * 设置cookie
 * @param string $name 					cookie名称
 * @param string or array $value		cookie值
 * @param string $time					cookie有效期
 * */
function set_cookies($name = null, $value = null, $time = 0)
{
	$time = $time > 0 ? $time : (empty($value) ? time() - 3600 : 0);
	$port = $_SERVER['SERVER_PORT'] == '443' ? 1 : 0;
	$value = is_array($value) ? serialize($value) : $value;
	if(setcookie($name, $value, $time, '/', '', $port))
	{
		return $_COOKIE[$name];
	}
	return false;
}

/*
 * 获取及设置cookie
 * @param string $name 					cookie名称
 * @param string or array $value		cookie值
 * return string or array or boole
 * */
function cookie_get_set($name=null,$value=null,$timeout=null)
{
	if(!empty($value))
	{
		$cookie = Finput('cookie',$name,$value,$timeout);
	}
	$cookie = Finput('cookie',$name);
	$cookie_array = unserialize($cookie);
	return is_array($cookie_array) ? $cookie_array : $cookie;
}

/*
 * 创建Smarty实例对象
 * return object
 * */
function CreateSmarty()
{
	//引入Smarty类库
	require_once(FRAME_CORE_CLASS.'/Smarty-3.1.21/libs/Smarty.class.php');
	
	//实例化Smarty
	$Smarty = new Smarty();
	
	//配置Smarty
	$smrty_config = config('SMARTY');
	if($smrty_config){
		foreach($smrty_config as $key=>$val)
		{
			//变量配置设置每一项配置
			$Smarty->$key = $val;
		}
	}
	return $Smarty;
}

/*
 * 提交数据过滤检测
 * @author jirenyou
 * @param array $checkArr 		二维数组数据验证规则
 * @param string checkType		数据验证类型：非空、数字、字母 等...
 * @param string field			验证字段
 * @param string field2			验证字段2
 * @param string table			查询表名
 * @param string value			查询条件值
 * @param int minlength			字符最短
 * @param int maxlength 		字符最长
 * @param int length			字符长度
 * @param string errorMsg		错误信息
 * @return json			
 * */
function submitDataCheck($checkArr = array())
{
	foreach($checkArr as $val)
	{
		//验证类型
		switch($val['checkType'])
		{
			//非空
			case 'notnull':
				if(empty($val['field']))
				{
					exit(ajax_return($val['errorMsg'],300));
				}
				break;
			//只能是数字
			case 'number':
				//数字字符串验证
				if(!is_numeric($val['field']))
				{
					exit(ajax_return($val['errorMsg'],300));
				}
				
				//数字长度验证
				if(is_numeric($val['length']))
				{
					if(strlen($val['field']) != $val['length'])
					{
						exit(ajax_return($val['errorMsg'],300));
					}
				}
				else
				{
					@list($minlength,$maxlength) = explode('-',$val['length']);
					if(strlen($val['field']) < $minlength or strlen($val['field']) > $maxlength)
					{
						exit(ajax_return($val['errorMsg'],300));
					}
				}
				break;
			//只能是字符串
			case 'string':
				break;
			//只能是数字+字符串
			case 'numberstring':
				break;
			//非0
			case 'not0':
				if($val['field'] == 0)
				{
					exit(ajax_return($val['errorMsg'],300));
				}
				break;
			//记录存在
			case 'unique':
				$table = ucwords($val['table']);
				if(M($table)->where(array($val['field']=>trim($val['value'])))->find())
				{
					exit(ajax_return($val['errorMsg'],300));
				}
				break;
			//邮箱真实
			case 'email':
				if(!emailCheck($val['field']))
				{
					exit(ajax_return($val['errorMsg'],300));
				}
				break;
			//字符长度
			case 'length':
				if(strlen($val['field']) < $val['minlength'] or strlen($val['field']) > $val['maxlength'])
				{
					exit(ajax_return($val['errorMsg'],300));
				}
				break;
			//字段相等
			case 'equal':
				if($val['field'] != $val['field2'])
				{
					exit(ajax_return($val['errorMsg'],300));
				}
				break;
			//等于指定值
			case 'eqvalue':
				if($val['field'] == $val['value'])
				{
					exit(ajax_return($val['errorMsg'],300));
				}
				break;
			//条件验证记录存在
			case 'exists':
				if(M($val['table'])->where($val['condition'])->find())
				{
					exit(ajax_return($val['errorMsg'],300));
				}
				break;
		}
	}
}

/*
 * 提交数据过滤检测
 * @author jirenyou
 * @param array $checkArr 		二维数组数据验证规则
 * @param string checkType		数据验证类型：非空、数字、字母 等...
 * @param string field			验证字段
 * @param string field2			验证字段2
 * @param string table			查询表名
 * @param string value			查询条件值
 * @param int minlength			字符最短
 * @param int maxlength 		字符最长
 * @param int length			字符长度
 * @param string errorMsg		错误信息
 * @return json			
 * */
function dataCheck($checkArr = array())
{
	//验证类型
	switch($checkArr['checkType'])
	{
		//非空
		case 'notnull':
			if(empty($checkArr['field']))
			{
				return false;
			}
			break;
		//只能是数字
		case 'number':
			//数字字符串验证
			if(!is_numeric($checkArr['field']))
			{
				return false;
			}
			
			//数字长度验证
			if(is_numeric($checkArr['length']))
			{
				if(strlen($checkArr['field']) != $checkArr['length'])
				{
					return false;
				}
			}
			else
			{
				@list($minlength,$maxlength) = explode('-',$checkArr['length']);
				if(strlen($checkArr['field']) < $minlength or strlen($checkArr['field']) > $maxlength)
				{
					return false;
				}
			}
			break;
		//只能是字符串
		case 'string':
			break;
		//只能是数字+字符串
		case 'numberstring':
			break;
		//非0
		case 'not0':
			if($checkArr['field'] == 0)
			{
				return false;
			}
			break;
		//记录存在
		case 'unique':
			$table = ucwords($checkArr['table']);
			$model = model($checkArr['table']);
			$checkData = array(
				'condition' => array(
					$checkArr['field']	=> trim($checkArr['value'])
				),
			);
			if($model->find($checkData))
			{
				return false;
			}
			break;
		//邮箱真实
		case 'email':
			if(!emailCheck($checkArr['field']))
			{
				return false;
			}
			break;
		//手机号
		case 'phone':
			if(!phoneCheck($checkArr['field']))
			{
				return false;
			}
			break;
		//字符长度
		case 'length':
			if(strlen($checkArr['field']) < $checkArr['minlength'] or strlen($checkArr['field']) > $checkArr['maxlength'])
			{
				return false;
			}
			break;
		//字段相等
		case 'equal':
			if($checkArr['field'] != $checkArr['field2'])
			{
				return false;
			}
			break;
		//等于指定值
		case 'eqvalue':
			if($checkArr['field'] == $checkArr['value'])
			{
				return false;
			}
			break;
		//条件验证记录存在
		case 'exists':
			$model = model($checkArr['table']);
			if($model->find($checkArr['condition']))
			{
				return false;
			}
			break;
		default:
			return false;
			break;
	}
	return true;
}

/*
 * 模拟数据库查询，通过字段查询单条记录
 * author jirenyou
 * @param array $array				要检测的二维数组
 * @param string $field				查询的字段
 * @param string $value 			查询值
 * @param string $getField			获取的字段
 * return array						返回数组
 * */
function getArrayRow($array=array(),$field='id',$value='',$getField='*')
{
	//获取的字段
	if($getField != '*')
	{
		$fieldArray = explode(',',$getField);
	}
	else
	{
		$fieldArray = $getField;
	}
	
	//新数组
	$new_array = array();
	
	//遍历要检索的数组
	foreach($array as $key=>$val)
	{
		//查询条件判断
		if($val[$field] == $value)
		{
			//过滤字段
			if(is_array($fieldArray))
			{
				//卸载不需要的字段
				foreach($val as $vkey=>$vval)
				{
					if(!in_array($vkey,$fieldArray))
					{
						unset($val[$vkey]);
					}
					
					$new_array = $val;
				}
			}
			else
			{
				$new_array = $val;
			}
		}
	}
	
	return $new_array;
}

/*
 * 手机格式验证
 * @param string $phone			电子邮箱
 * return boole 				返回boole
 * */
//function phoneCheck($mobilephone=null)
//{
//	if(preg_match("/^13[0-9]{1}[0-9]{8}$|15[0189]{1}[0-9]{8}$|18[0-9]{1}[0-9]{8}$/",$mobilephone)){    
//	    //验证通过    
//	    return true;   
//	}else{    
//	    //手机号码格式不对    
//	    return false;
//	}
//}

/*
 * 邮箱格式验证
 * author jirenyou
 * @param string $email			电子邮箱
 * @param boole $test_mx		是否返回邮箱域名
 * return boole or array		返回boole或邮箱域名数组
 * */
function emailCheck($email, $test_mx = false)
{
    if(@eregi("^([_a-z0-9-]+)(\.[_a-z0-9-]+)*@([a-z0-9-]+)(\.[a-z0-9-]+)*(\.[a-z]{2,4})$", $email))
    {
        if($test_mx)
        {
            return list($username, $domain) = split("@", $email);
        }
        else
        {
        	return true;
        }
    }
    else
    {
    	return false;
    }
}

/*
 * 手机号验证码
 * */
function phoneCheck($phone)
{
	if(@eregi("^1[3|4|5|8][0-9]\d{8}$",$phone))
	{
		return true;
	}
	return false;
}

/*
 *数组转换JSON格式数据输出 
 *author:jirenyou
 *@param array				数组数据
 *return json				返回json数据 		
 **/
function outputJson($result = array())
{
	return die(json_encode($result));
}

/*
 * 删除文件
 * author:jirenyou
 * @param string $filename		文件名
 * return int
 * */
function removeFile($filename)
{
	if(file_exists($filename))
	{
		if(unlink($filename))
		{
			return 1;
		}
		return 0;
	}
	return 2;
}

/*
 * 文件下载
 * author:jirenyou
 * @param string $file_name				下载文件名
 * @param string $file_dir				下载文件所在目录
 * return int boole
 * */
function download($file_name,$file_dir){
	$file_name = iconv("UTF-8","GB2312",$file_name);  //对中文文件名进行转码
	$file_path = $file_dir.$file_name;  //下载的路径
	if(!file_exists($file_path)){  //如果文件不存在，返回错误信息
            //$this->error("您要下载的文件不存在或已被管理员删除！");
            return 0;  //返回假 终止代码向下执行
        }
        //文件存在 则：打开这个文件
        $fp=fopen($file_path,"r");
        $file_size=filesize($file_path); //获取文件的大小
        //打开文件后需要下载则需要一个http协议响应的一个下载的头数据文件 代码一下：
        header("Content-type: application/octet-stream");
        //这句是返回文件网络流  stream 流
        header("Accept-Ranges: bytes");
        //这句是按照字节大小返回
        header("Accept-Length: $file_size");
        //这句是返回文件的大小
        header("Content-Disposition: attachment; filename=".$file_name);
        //这句是在客户端弹出的下载对话框中的文件名称
        $buffer=1024;  //定义缓存大小 为 1024 字节
        $file_count=0;  //定义一个计数变量
        while(!feof($fp) && ($file_size-$file_count>0)){ 
       	   //当这个文件的指针没有移动到文件尾并且所读取的文件大小不大于文件本身的大小   开始读取文件
           $file_data=fread($fp,$buffer);  //分多次读取文件
		   $file_count+=$buffer;  //每读一次，累加一次缓存
		   echo $file_data;    //分多次返回给浏览器
        }
        fclose();   //关闭文件
        return 1;
       // exit;
}

/*
 * 发送邮件
 * author jirenyou
 * @param array $mailArr							邮箱参数数组
 * @param string $mailArr['charset']				邮件编码
 * @param string $mailArr['receiveemal']			邮件接收人
 * @param string $mailArr['postoffice']				企业邮局
 * @param string $mailArr['smtp']					启用SMTP验证功能
 * @param string $mailArr['senduser']				发送账号
 * @param string $mailArr['sendpassword']			发送账号密码
 * @param string $mailArr['from']					邮局发送者
 * @param string $mailArr['sendnick']				发送人名称
 * @param string $mailArr['receivenick']			收件人
 * @param string $mailArr['emailtitle']				邮件标题
 * @param string $mailArr['emailcontent']			邮件内容
 * @param string $mailArr['appendinfo']				附加信息
 * */
function sendEmail($mailArr = array(),$SuccessMsg='')
{
	require(ROOT."/Application/Common/Class/mail/sendmail.php");
	$result = sendEmail_fun($mailArr,$SuccessMsg);
	return $result;
}

/*
 * 发生手机短信
 * @param to 手机号码集合,用英文逗号分开
 * @param datas 内容数据 格式为数组 例如：array('Marry','Alon')，如不需替换请填 null
 * @param $tempId 模板Id
 * */
function sendPhoneMsg($to=null,$datas=null,$tempId=0)
{
	require_once(ROOT.'/Plus/yuntongxun/SDK/CCPRestSDK.php');
	$config = config('YTSMS');
	//主帐号
	$accountSid			= $config['accountSid'];
	//主帐号Token
	$accountToken		= $config['accountToken'];
	//应用Id
	$appId				= $config['appId'];
	//请求地址，格式如下，不需要写https://
	$serverIP			= $config['serverIP'];
	//请求端口 
	$serverPort			= $config['serverPort'];
	//REST版本号
	$softVersion		= $config['softVersion'];
	// 初始化REST SDK
     global $accountSid,$accountToken,$appId,$serverIP,$serverPort,$softVersion;
     $rest = new REST($serverIP,$serverPort,$softVersion);
     $rest->setAccount($accountSid,$accountToken);
     $rest->setAppId($appId);
    
     // 发送模板短信
     $result = $rest->sendTemplateSMS($to,$datas,$tempId);
     if($result == NULL ) 
     {
     	$res['status'] = 0;
     	$res['msg'] = "获取结果错误";
        return $res;
     }
     if($result->statusCode!=0) 
     {
     	$res['status'] 		= 0;
     	$res['code'] 		= $result->statusCode;
     	$res['msg'] 		= $result->statusMsg;
     	return $res;
     }
     else
     {
        // 获取返回信息
        $smsmessage = $result->TemplateSMS;
        //TODO 添加成功处理逻辑
        $res['status'] 				= 1;
     	$res['dateCreated'] 		= $smsmessage->dateCreated;
     	$res['smsMessageSid'] 		= $smsmessage->smsMessageSid;
     	$res['msg'] 				= "短信发生成功";
     	return $res;
     }
}

/*
 *无限极分类子孙树递归查找
 *author jirenyou
 *@param $array		 要查找的子孙数据数组
 *@param $pid 		 要查找的父id
 *@paran $level		 查询到的节点等级
 **/
function getTree($array=array(),$pid=0,$level=0)
{
	//申明一个静态变量用于存放递归后的数组
	static $new_array = array();
	//遍历数组
	foreach($array as $val)
	{
		//判断pid父id是否为要查找的id
		if($val['pid'] == $pid)
		{
			//节点层数
			$val['level'] = $level;
			$new_array[] = $val;
			//递归再次查询子孙树
			getTree($array,$val['id'],$level+1);
		}
	}
	return $new_array;
}

/*
 * 判断一个数在两数范围内
 * author jirenyou
 * @param int $number			要判断的数值
 * @param array $data			判断和要返回的数据
 * @param array $data[0]		最小值
 * @param array $data[1]		最大值
 * @param array $data[2]		返回的数据
 * */
function zone($number,$data=array())
{
	$status = false;
	foreach($data as $val)
	{
		if($number>=$val[0] && $number<=$val[1])
		{
			$status = true;
			return $val[2];
		}
	}
	return $status;
}

/*
 * 写入文件
 * author jirenyou
 * @param string $path			保存路径
 * @param string $filename		生成文件名	
 * @param string $contents		写入的内容		
 * */
function writeFile($path,$filename,$contents)
{
	if(createDir($path))
	{
		return file_put_contents($path.'/'.$filename,$contents);
	}
	
	return false;
}

/*
 * 生成zip打包文件
 * @param string $sourcePath		源目录
 * @param string $targetFile 		目标文件
 * @param boole $download			是否下载
 * return boole 
 * */
function packZip($sourcePath=null,$targetFile=null,$download=false)
{
	//引入Zip类库
	$zip = new \Frame\Zip();
	if($download)
	{
		//打包下载	
		$zip->downloadZip($sourcePath, $targetFile . ".zip"); 
	}
	else
	{
		//只打包
		$zip->createZip($sourcePath,$targetFile . ".zip",true);
	}
}

/**
    * 导出数据为excel表格
    * author jirenyou
    *@param $data    一个二维数组,结构如同从数据库查出来的数组
    *@param $title   excel的第一行标题,一个数组,如果为空则没有标题
    *@param $filename 下载的文件名
    *@examlpe 
    $stu = M ('User');
    $arr = $stu -> select();
    exportexcel($arr,array('id','账户','密码','昵称'),'文件名!');
*/
function exportexcel($data=array(),$title=array(),$filename='report'){
    header("Content-type:application/octet-stream");
    header("Accept-Ranges:bytes");
    header("Content-type:application/vnd.ms-excel");  
    header("Content-Disposition:attachment;filename=".$filename.".xls");
    header("Pragma: no-cache");
    header("Expires: 0");
    //导出xls 开始
    if (!empty($title)){
        foreach ($title as $k => $v) {
            $title[$k]=iconv("UTF-8", "GB2312",$v);
        }
        $title= implode("\t", $title);
        echo "$title\n";
    }
    if (!empty($data)){
        foreach($data as $key=>$val){
            foreach ($val as $ck => $cv) {
                $data[$key][$ck]=iconv("UTF-8", "GB2312", $cv);
            }
            $data[$key]=implode("\t", $data[$key]);
            
        }
        echo implode("\n",$data);
    }
 }

/*
 * 二维数组排序
 * @param $arr    要排序的二维数组
 * @param $keys   要排序的二维数组键
 * @param $type   排序类型
 * */
function array_sort($arr,$keys,$type='asc'){
    $keysvalue = $new_array = array();
    foreach ($arr as $k=>$v)
    {
        $keysvalue[$k] = $v[$keys];
    }
    if($type == 'asc')
    {
        asort($keysvalue);
    }
    else
    {
        arsort($keysvalue);
    }
    reset($keysvalue);
    foreach ($keysvalue as $k=>$v)
    {
        $new_array[$k] = $arr[$k];
    }
    return $new_array;
}

/*
 * 参数递归转义
 * @param string or array $data				要转义的数组数据
 * return array or string
 * */
function _addslashes($data=null)
{
	if(is_string($data))
	{
		$data = addslashes($data);
	}
	else if(is_array($data))
	{
		//转义后的数组数据
		foreach($data as $key=>$val)
		{
			if(is_string($val))
			{
				$data[$key] = addslashes($val);
			}	
			else if(is_array($val))
			{
				$data[$key] = _addslashes($val);
			}
		}
	}
	return $data;
}

/*
 * 包含数据配置文件
 * @param string $filename				文件名
 * return boole or string or array		
 * */
function includeDataFile($filename=null)
{
	if(!empty($filename))
	{
		$data = include($filename);
		return _addslashes($data);
	}
	return false;
}

/*
 * 获取并过滤客户端提交数据
 * 过滤项：REQUEST、POST、GET、PUT、COOKIE、SESSION、SERVER 默认接收REQUEST
 * @param string $type						请求数据类型
 * @param string $field						获取数据字段
 * @param string or array $default			字段数据为空时默认设置和返回
 * @param string $time						有效时间
 * return string or array
 * */
function Finput($type='request',$field=null,$default=null,$time=null)
{
	switch($type)
	{
		//_REQUEST数据
		case 'request':
			//GET方式请求
			if($_SERVER['REQUEST_METHOD'] == 'GET')
			{
				//方式
				$urlencode = config('URL_ENCODE') ? config('URL_ENCODE') : false;
				if($urlencode)
				{
					//GET方式判断是否URL编码
					foreach($_REQUEST as $key=>$val)
					{
						//解码url参数
						$_REQUEST[$key] = urldecode($val);
					}
				}
			}
		
			if(!empty($field))
			{
				@$data = $_REQUEST[$field] ? $_REQUEST[$field] : $default;
			}
			else 
			{
				@$data = $_REQUEST;
			}
			break;
		//_POST数据
		case 'post':
			if(!empty($field))
			{
				@$data = $_POST[$field] ? $_POST[$field] : $default;
			}
			else 
			{
				@$data = $_POST;
			}
			break;
		//_GET数据
		case 'get':
			//GET方式请求
			$urlencode = config('URL_ENCODE') ? config('URL_ENCODE') : false;
			if($urlencode)
			{
				//GET方式判断是否URL编码
				foreach($_GET as $key=>$val)
				{
					//解码url参数
					$_GET[$key] = urldecode($val);
				}
			}
			
			if(!empty($field))
			{
				@$data = $_GET[$field] ? $_GET[$field] : $default;
			}
			else 
			{
				@$data = $_GET;
			}
			break;
		//_PUT数据
		case 'put':
			if(!empty($field))
			{
				@$data = $_PUT[$field] ? $_PUT[$field] : $default;
			}
			else 
			{
				@$data = $_PUT;
			}
			break;
		//COOKIE数据
		case 'cookie':
			if(!empty($field))
			{
				@$data = $_COOKIE[$field] ? $_COOKIE[$field] : set_cookies($field,$default,$time);
				
			}
			else 
			{
				@$data = $_COOKIE;
			}
			break;
		//SESSION数据
		case 'session':
			@session_start();
			if(!empty($field))
			{
				@$data = $_SESSION[$field] ? $_SESSION[$field] : $default;
			}
			else 
			{
				@$data = $_SESSION;
			}
			break;
		//SERVER数据
		case 'server':
			if(!empty($field))
			{
				@$data = $_SERVER[$field] ? $_SERVER[$field] : $default;
			}
			else 
			{
				@$data = $_SERVER;
			}
			break;
	}
	//递归转义数据
	return _addslashes($data);
}

/*
 * 实例化控制器类
 * @param string $className			类名包括命名空间
 * return object
 * */
function creteClass($className=null)
{
	return new $className;
}

/*
 * Ajax返回数据
 * @param array $data 					要编码的数据
 * @param string $returnType			输出数据格式
 * return array or object or string		
 * */
function ajaxReturn($data=array(),$outputType='JSON')
{
	switch(strtoupper($outputType))
	{
		//JSON
		case 'JSON':
			$result = json_encode($data);
			break;
		//XML
		case 'XML':
			break;
		//HTML
		case 'HTML':
			$result = $data;
			break;
	}
	return die($result);
	exit;
}

/*
 * 发送邮件管理员
 * @param string $title 		邮件标题
 * @param string $content		邮件内容
 * @param string $appendInfo	附件信息
 * return boole or array
 * */
function sendEmail_admin($title=null,$content=null,$appendInfo=null)
{
	//网站设置
	$siteInfo = includeDataFile(ROOT . '/Public/siteInfo.config.php');
	//邮件服务器配置
    $emailConf = includeDataFile(ROOT . '/Public/smtp.config.php');
    				
	//邮件参数
	$adminemailparam = array(
		'charset' => 'UTF-8',   //编码
		'receiveemal' => $siteInfo['siteemail'],    //接收人邮件
		'postoffice' => $emailConf['host'],  //企业邮局 邮件服务器
		'smtp' =>  true ,      //启用SMTP验证功能
		'senduser' => $emailConf['account'],    //邮件发送人
		'sendpassword' => $emailConf['pwd'],   //邮件发送人密码
		'from' => $emailConf['account'],   //邮件发送人
		'sendnick' => '',   //邮件发送人昵称
		'receivenick' => '',   //接收人名称
		'emailtitle' => $title,     //邮件标题
		'emailcontent' => $content,    //邮件内容
		'appendinfo' => $appendInfo,  //附加信息
	);
	
	//发送
	return sendEmail($adminemailparam);
}

/*
 * 获取广告位
 * author jirenyou
 * @param string $advId			广告位id
 * */
function getAdv($advId='')
{
	$advcateModel			= model('advcate');
	
	//广告位查询
	$advcondition = array(
		'condition'=>array('advid'=>$advId,'status'=>1)
	);
	$adv			= $advcateModel->find($advcondition);
	//广告列表
	$advModel				= model('adv');
	$advlistcon = array(
		'condition'=>array('cid'=>$adv['id'],'status'=>1),
		'order'=>array('sort'=>'asc'),
	);
	$adv['list'] 	= $advModel->select($advlistcon);
	return $adv;
}

/*
 * 获取分页
 * @param string $table							表
 * @param array $param 							分页参数
 * @param int $param['PageSize']				每页大小
 * @param string or array $param['Field']		获取字段
 * @param string or array $param['Condition']	条件
 * @param int $param['NowPage']					当前页数
 * return array
 * */
function getPage($table=null,$param = array())
{
	$Page = new \Frame\Page($table);
	//设置每页数量
	if(!empty($param['pagesize']))
	{
		$Page->PageSize = $param['pagesize'];
	}
	//获取字段
	if(!empty($param['field']))
	{
		$Page->Field = $param['field'];
	}
	//获取条件
	$Page->Condition = $param['condition'];
	//排序
	$Page->Order 	 = $param['order'];
	
	//当前页数
	$Page->NowPage = Finput('request','page') ? Finput('request','page') : 1;
	
	return $Page->getPage();
}

/*
 * 生成缩略图
 *
 * 生成保持原图纵横比的缩略图，支持.png .jpg .gif
 * 缩略图类型统一为.png格式
 * $srcFile     原图像文件名称
 * $toW         缩略图宽
 * $toH         缩略图高
 * $toFile      缩略图文件名称，为空覆盖原图像文件
 * @return bool    
 * */
function thumbnail($srcFile = null, $toW = 100, $toH = 100, $toFile = null)
{
	if (empty($toFile))
         { 
                $toFile = $srcFile; 
         }
         $info = "";
         //返回含有4个单元的数组，0-宽，1-高，2-图像类型，3-宽高的文本描述。
         //失败返回false并产生警告。
         $data = getimagesize($srcFile, $info);
         if (!$data)
             return false;
         
         //将文件载入到资源变量im中
         switch ($data[2]) //1-GIF，2-JPG，3-PNG
         {
         case 1:
             if(!function_exists("imagecreatefromgif"))
             {
             	$result['status'] = 2;
             	$result['msg'] = "图片格式不支持！";
             	return $result;
             }
             $im = imagecreatefromgif($srcFile);
             break;
             
         case 2:
             if(!function_exists("imagecreatefromjpeg"))
             {
             	$result['status'] = 2;
             	$result['msg'] = "图片格式不支持！";
             	return $result;
             }
             $im = imagecreatefromjpeg($srcFile);
             break;
               
         case 3:
             $im = imagecreatefrompng($srcFile);    
             break;
         }
         
         //计算缩略图的宽高
         $srcW = imagesx($im);
         $srcH = imagesy($im);
         $toWH = $toW / $toH;
         $srcWH = $srcW / $srcH;
         if ($toWH <= $srcWH) 
         {
             $ftoW = $toW;
             $ftoH = (int)($ftoW * ($srcH / $srcW));
         }
         else 
         {
             $ftoH = $toH;
             $ftoW = (int)($ftoH * ($srcW / $srcH));
         }
         
         if (function_exists("imagecreatetruecolor")) 
         {
             $ni = imagecreatetruecolor($ftoW, $ftoH); //新建一个真彩色图像
             if ($ni) 
             {
                 //重采样拷贝部分图像并调整大小 可保持较好的清晰度
                 imagecopyresampled($ni, $im, 0, 0, 0, 0, $ftoW, $ftoH, $srcW, $srcH);
             } 
             else 
             {
                 //拷贝部分图像并调整大小
                 $ni = imagecreate($ftoW, $ftoH);
                 imagecopyresized($ni, $im, 0, 0, 0, 0, $ftoW, $ftoH, $srcW, $srcH);
             }
         }
         else 
         {
             $ni = imagecreate($ftoW, $ftoH);
             imagecopyresized($ni, $im, 0, 0, 0, 0, $ftoW, $ftoH, $srcW, $srcH);
         }
 
         //保存到文件 统一为.png格式
         imagepng($ni, $toFile); //以 PNG 格式将图像输出到浏览器或文件
         ImageDestroy($ni);
         ImageDestroy($im);
         return true;
}

/*
 * 获取淘宝客商品详情
 * */
function tbkGoodsInfo($goodiid='')
{
	require_once(ROOT.'/plus/taobaoke/TopSdk.php');
	$c = new TopClient;
	$c->appkey = '23188977';
	$c->secretKey = '0591664547cf8ee5e241c1a8e76efa2d';
	//淘宝客商品详情
	$req = new TbkItemsDetailGetRequest;
	$req->setFields("num_iid,seller_id,nick,title,price,volume,pic_url,item_url,shop_url,click_url,discount_price");
	$req->setNumIids($goodiid);
	$resp = $c->execute($req);
	return $resp;
}

/*
 * 根据年月获取当月的天数
 * @param int $month		月份
 * @param int $year			年月
 * return int
 * */
function getDays($month, $year)
{
	return $month == 2 ? ($year % 4 ? 28 : ($year % 100 ? 29 : ($year % 400 ? 28 : 29))) : (($month - 1) % 7 % 2 ? 30 : 31);
}

/*
 * 生成二维码
 * @param string $value						二维码值
 * @param string $qrcidename				二维码图片路径名称
 * @param string $errorCorrectionLevel		容错级别
 * @param int  $matrixPointSize				生成图片大小
 * @param string $logo						LOGO图片路径
 * return string							返回二维码路径
 * */
function createQrcode($value=null,$qrcidename=null,$errorCorrectionLevel='L',$matrixPointSize=6,$logo=null,$is_logo = false)
{
	//引入二维码
	include_once(ROOT.'/plus/phpqrcode/phpqrcode.php');
	//生成二维码图片 
	QRcode::png($value, $qrcidename, $errorCorrectionLevel, $matrixPointSize, 2);
	//已经生成的原始二维码图 
	$QR = $qrcidename;

	if ($logo !== FALSE) 
	{ 
		 $QR = imagecreatefromstring(file_get_contents($QR)); 
		 $logo = imagecreatefromstring(file_get_contents($logo)); 
		 $QR_width = imagesx($QR);//二维码图片宽度 
		 $QR_height = imagesy($QR);//二维码图片高度 
		 $logo_width = imagesx($logo);//logo图片宽度 
		 $logo_height = imagesy($logo);//logo图片高度 
		 
		 $logo_qr_width = $QR_width / 5; 
		 $scale = $logo_width/$logo_qr_width; 
		 $logo_qr_height = $logo_height/$scale; 
		 $from_width = ($QR_width - $logo_qr_width) / 2;

		 //echo $from_width.'-'.$logo_qr_width.'-'.$logo_qr_height.'-'.$logo_width.'-'.$logo_height;
		 
		 //重新组合图片并调整大小 
		 imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width, 
		 $logo_qr_height, $logo_width, $logo_height); 
		 
		 if($is_logo)
		 {
		 	// 圆角处理  
		     $radius  = 10.5;  
		     // lt(左上角)  
		     $lt_corner  = get_lt_rounder_corner($radius,225,255,255);  
		     imagecopymerge($QR, $lt_corner, $from_width, $from_width, 0, 0, $radius, $radius, 100);  
		     // lb(左下角)  
		     $lb_corner  = imagerotate($lt_corner, 90, 0);  
		     imagecopymerge($QR, $lb_corner, $from_width, $from_width+$logo_qr_height-$radius, 0, 0, $radius, $radius, 100);  
		     // rb(右上角)  
		     $rb_corner  = imagerotate($lt_corner, 180, 0);  
		     imagecopymerge($QR, $rb_corner, $from_width+$logo_qr_width-$radius, $from_width+$logo_qr_height-$radius, 0, 0, $radius, $radius, 100);  
		     // rt(右下角)  
		     $rt_corner  = imagerotate($lt_corner, 270, 0);  
		     imagecopymerge($QR, $rt_corner, $from_width+$logo_qr_width-$radius, $from_width, 0, 0, $radius, $radius, 100); 
		 }
	} 
	//输出图片 
	imagepng($QR, $qrcidename); 
	return substr($qrcidename,1,strlen($qrcidename));
}

/*
 * 生成圆角
 * */
function get_lt_rounder_corner($radius=10,$bgcolorR=255,$bgcolorG=255,$gbcolorB=255) 
{  
        $img     = imagecreatetruecolor($radius, $radius);  // 创建一个正方形的图像  
        $bgcolor    = imagecolorallocate($img, $bgcolorR, $bgcolorG, $gbcolorB);   // 图像的背景  
        $fgcolor    = imagecolorallocate($img, 0, 0, 0);  
        imagefill($img, 0, 0, $bgcolor);  
        // $radius,$radius：以图像的右下角开始画弧  
        // $radius*2, $radius*2：已宽度、高度画弧  
        // 180, 270：指定了角度的起始和结束点  
        // fgcolor：指定颜色  
        imagefilledarc($img, $radius, $radius, $radius*2, $radius*2, 180, 270, $fgcolor, IMG_ARC_PIE);  
        // 将弧角图片的颜色设置为透明  
        imagecolortransparent($img, $fgcolor);  
        // 变换角度  
        // $img = imagerotate($img, 90, 0);  
        // $img = imagerotate($img, 180, 0);  
        // $img = imagerotate($img, 270, 0);  
        // header('Content-Type: image/png');  
        // imagepng($img);  
        return $img;  
}  

/*
 * 第三方QQ接口
 * */
function QQAPI()
{
	//引入QQ接口文件
	if($_SERVER['HTTP_HOST'] == 'www.huipinwang.com' || $_SERVER['HTTP_HOST'] == 'huipinwang.com')
	{
		require_once(ROOT."/plus/Connect2.1/API/RequestAPI.php");
	}
	else if($_SERVER['HTTP_HOST'] == 'm.huipinwang.com' || $_SERVER['HTTP_HOST'] == 'm.taodianzhu.com')
	{
		require_once(ROOT."/Mobile/plus/Connect2.1/API/RequestAPI.php");
	}
	return new QC();
}

/*
 * 截取内容长度
 * $str:要截取的字符串
 * $start=0：开始位置，默认从0开始
 * $length：截取长度
 * $charset=”utf-8″：字符编码，默认UTF－8
 * $suffix=true：是否在截取后的字符后面显示省略号，默认true显示，false为不显示
 * */
function msubstr($str, $start=0, $length, $charset="utf-8", $suffix=true)  
{  
    if(function_exists("mb_substr")){  
        if($suffix)  
             return mb_substr($str, $start, $length, $charset)."...";  
        else 
             return mb_substr($str, $start, $length, $charset);  
    }  
    elseif(function_exists('iconv_substr')) {  
        if($suffix)  
             return iconv_substr($str,$start,$length,$charset)."...";  
        else 
             return iconv_substr($str,$start,$length,$charset);  
    }  
    $re['utf-8']   = "/[x01-x7f]|[xc2-xdf][x80-xbf]|[xe0-xef][x80-xbf]{2}|[xf0-xff][x80-xbf]{3}/";  
    $re['gb2312'] = "/[x01-x7f]|[xb0-xf7][xa0-xfe]/";  
    $re['gbk']    = "/[x01-x7f]|[x81-xfe][x40-xfe]/";  
    $re['big5']   = "/[x01-x7f]|[x81-xfe]([x40-x7e]|xa1-xfe])/";  
    preg_match_all($re[$charset], $str, $match);  
    $slice = join("",array_slice($match[0], $start, $length));  
    if($suffix) return $slice."…";  
    return $slice;
}

/*
 * 电脑、手机、移动端访问判断
 * */
function isMobile() {
    // 如果有HTTP_X_WAP_PROFILE则一定是移动设备
    if (isset($_SERVER['HTTP_X_WAP_PROFILE'])) {
        return true;
    }
    //如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
    if (isset($_SERVER['HTTP_VIA'])) {
        //找不到为flase,否则为true
        if(stristr($_SERVER['HTTP_VIA'], "wap"))
        {
            return true;
        }
    }
    //脑残法，判断手机发送的客户端标志,兼容性有待提高
    if (isset($_SERVER['HTTP_USER_AGENT'])) {
        $clientkeywords = array (
            'nokia',
            'sony',
            'ericsson',
            'mot',
            'samsung',
            'htc',
            'sgh',
            'lg',
            'sharp',
            'sie-',
            'philips',
            'panasonic',
            'alcatel',
            'lenovo',
            'iphone',
            'ipod',
            'blackberry',
            'meizu',
            'android',
            'netfront',
            'symbian',
            'ucweb',
            'windowsce',
            'palm',
            'operamini',
            'operamobi',
            'openwave',
            'nexusone',
            'cldc',
            'midp',
            'wap',
            'mobile',
            'phone',
        );
        // 从HTTP_USER_AGENT中查找手机浏览器的关键字
        if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
            return true;
        }
    }
    //协议法，因为有可能不准确，放到最后判断
    if (isset($_SERVER['HTTP_ACCEPT'])) {
        // 如果只支持wml并且不支持html那一定是移动设备
        // 如果支持wml和html但是wml在html之前则是移动设备
        if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
            return true;
        }
    }
    return false;
}

/*
 * 创建类
 * */
function newClass($className=null,$parma=null)
{
	//引入类文件
	require_once(ROOT_PATH.'/Lib/Class/'.$className.'.class.php');
	//实例化类	
	return new $className($parma);
}

/* 调用百度短链接API生成短链接
 * @param string $url			原网址
 * @param string $type			true 长链接转短链接，false 短链接转长链接
 * return string 
 */
/*
function bdUrlAPI($url=null,$type=true)
{
    if($type)
    {
    	$baseurl = 'http://dwz.cn/create.php';
    }
    else
    {
    	$baseurl = 'http://dwz.cn/query.php';
    }
    $ch=curl_init();
    curl_setopt($ch,CURLOPT_URL,$baseurl);
    curl_setopt($ch,CURLOPT_POST,true);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    if($type)
    {
    	$data=array('url'=>$url);
    }
    else
    {
    	$data=array('tinyurl'=>$url);
    }
    
    curl_setopt($ch,CURLOPT_POSTFIELDS,$data);
    $strRes=curl_exec($ch);
    curl_close($ch);
    $arrResponse=json_decode($strRes,true);
    
    if($arrResponse['status']!=0)
    {
	    echo 'ErrorCode: ['.$arrResponse['status'].'] ErrorMsg: ['.iconv('UTF-8','GBK',$arrResponse['err_msg'])."]<br/>";
	    return 0;
    }
    if($type)
    {
    	return $arrResponse['tinyurl'];
    }
    else
    {
    	return $arrResponse['longurl'];
    }
}
*/
?>