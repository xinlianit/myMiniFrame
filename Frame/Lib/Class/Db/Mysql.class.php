<?php 
/*********************************************************
 * 		Time  	: 2015-01-11							 *
 * 		Author	: jirenyou							 	 *
 * 		Email	: 390066398@qq.com						 *
**********************************************************/
/*
 * MySql数据库类
 * */
namespace Frame\Db;
require_once(FRAME_CORE_CLASS.'/Interface/Db.class.php');
require_once(FRAME_CORE_CLASS.'/Db/Config.class.php');
class Mysql implements Db
{
	//自身对象变量
	private static $Ins;
	//数据库连接
	private $conn;
	//数据库配置
	private $config;
	//执行的SQL语句
	private $sql;
	//数据表
	public static $table = null;
	//表前缀
	private $tablePrefix;
	//数据库名称
	private $dbName;
	
	//构造函数
	final protected function __construct()
	{
		//连接数据库
		self::connect();
	}
	
	/*
	 * 连接数据库
	 * */
	public function connect()
	{
		//数据库配置
		$ConfigIns = Config::getIns();
		$this->config = $ConfigIns->db;
		//表前缀
		$this->tablePrefix = $this->config[0]['prefix'];
		//数据库名称
		$this->dbName = $this->config[0]['dbname'];
		
		foreach($this->config as $val)
		{
			switch(ucwords($val['type']))
			{
				//Mysql
				case 'Mysql':
					//链接Mysql数据库服务器
					$this->conn= mysql_connect($val['host'] . ':' .$val['port'],$val['username'],$val['password']) or die("Mysql数据库连接失败");
					//选择数据库
					self::select_db($val['dbname']);
					//设置编码
					self::setChar($val['char']);
					break;
			}
		}
	}
	
	/*
	 * 选择数据库
	 * @Access public 
	 * @param string $dbname		数据名
	 * */
	public function select_db($dbname=null)
	{
		mysql_select_db($dbname,$this->conn);
	}
	
	/*
	 * 设置编码
	 * @Access public
	 * @param string $char			编码
	 * */
	public function setChar($char=null)
	{
		$sql = 'SET NAMES '.$char;
		self::query($sql);
	}
	
	/*
	 * 发送数据库命令
	 * @Access public
	 * @param string $sql			数据库命令语句
	 * */
	public function query($sql=null)
	{
		$this->sql = $sql;
		return mysql_query($this->sql,$this->conn);
	}
	
	/*
	 * 关闭数据库连接
	 * */
	public function close_connect()
	{
		if(!empty($this->conn))
		{
			mysql_close($this->conn);
		}
	}
	
	/*
	 * 创建表单字段数据
	 * @param string or array $table			数据表
	 * return array
	 * */
	public function createFieldData($table=null)
	{
		$table = $table ? $table :self::$table;
		//表前缀
		$prefix 	= is_array($table) && !empty($table['prefix']) ? $table['prefix'] : $this->tablePrefix;
		//表名
		$tablename  = is_array($table) && !empty($table['table']) ? $table['table'] : $table;
		//表字段
		$fields = self::getTableField(array('prefix'=>$prefix,'table'=>$tablename));
		//POST、GET接收的数据
		$data = Finput();
		
		//赋值到字段
		$fieldsData = array();
		foreach($data as $key => $val)
		{
			//数字赋值
			if(in_array($key,$fields['fields']))
			{
				$fieldsData[$key] = $val;
			}
		}
		
		//返回数据
		return $fieldsData;
	}
	
	/*
	 * 插入单条数据
	 * @param string or array $table		表
	 * @param string $table['name']			表名
	 * @param string $table['prefix']		表前缀
	 * @param array $data     				要添加的数据
	 * return int
	 * */
	public function add($data=array(),$table=null)
	{
		$table = $table ? $table :self::$table;
		//表前缀
		$prefix 	= is_array($table) && !empty($table['prefix']) ? $table['prefix'] : $this->tablePrefix;
		//表名
		$tablename  = is_array($table) && !empty($table['table']) ? $table['table'] : self::$table;
		//表字段
		$fields = array();
		if(file_exists(ROOT_PATH.'/Runtime/Data/Field/'.$prefix.$tablename.'.php') && DEBUG == false)
		{
			//生成字段
			$fields = include(ROOT_PATH.'/Runtime/Data/Field/'.$prefix.$tablename.'.php');
		}
		else
		{
			//生成字段
			$fields = self::getTableField(array('prefix'=>$prefix,'table'=>$tablename));
			//缓存到文件
			setArray($fields,ROOT_PATH.'/Runtime/Data/Field/',$prefix.$tablename.'.php');
		}
		
		//过滤字段
		if(!empty($fields))
		{
			foreach($data as $key=>$val)
			{
				if(!in_array($key,$fields['fields']))
				{
					unset($data[$key]);
				}
			}
		}
		
		$keys = '';
		$vals = '';
		foreach($data as $key=>$val)
		{
			$keys .= '`' . $key . '`,';
			$vals .= '"' . $val . '",';
		}
		$keys = substr($keys,0,-1);
		$vals = substr($vals,0,-1);
		$sql = 'INSERT INTO '.$prefix.$tablename.' ('.$keys.') VALUES ('.$vals.');';
		
		if(self::query($sql))
		{
			return self::getInsertId();
		}
		return false;
	}
	
	/*
	 * 插入批量数据
	 * @Access public
	 * @param array or string $table		表
	 * @param string $table['prefix']		表前缀
	 * @param string $table['table']		表名
	 * @param array $data					插入数据
	 * return boole or int
	 * */
	public function addAll($data=array(),$table=null)
	{
		$table 		= $table ? $table : self::$table;
		//表前缀
		$prefix 	= is_array($table) && !empty($table['prefix']) ? $table['prefix'] : $this->tablePrefix;
		//表名
		$tablename  = is_array($table) && !empty($table['table']) ? $table['table'] : self::$table;
		
		//表字段
		$fields = array();
		if(file_exists(ROOT_PATH.'/Runtime/Data/Field/'.$prefix.$tablename.'.php') && DEBUG == false)
		{
			//生成字段
			$fields = include(ROOT_PATH.'/Runtime/Data/Field/'.$prefix.$tablename.'.php');
		}
		else
		{
			//生成字段
			$fields = self::getTableField(array('prefix'=>$prefix,'table'=>$tablename));
			//缓存到文件
			setArray($fields,ROOT_PATH.'/Runtime/Data/Field/',$prefix.$tablename.'.php');
		}
		
		//过滤字段
		if(!empty($fields))
		{
			foreach($data as $key=>$val)
			{
				foreach($val as $key1=>$val1)
				{
					if(!in_array($key1,$fields['fields']))
					{
						unset($data[$key][$key1]);
					}
				}
			}
		}
		
		//插入数据组合
		foreach($data as $val)
		{
			static $datas;
			$keys = '';
			$vals = '';
			if(is_array($val) && !empty($val))
			{
				foreach($val as $key1=>$val1)
				{
					
					//字段组合
					$keys .= $key1 . ',';
					//数据组合
					$vals .= '"' . $val1 . '",';
					//语句组合
				}
				$keys = substr($keys,0,-1);
				$vals = substr($vals,0,-1);
			}
			$datas .= '('.$vals.'),';
		}
		//截取掉最后一个连接符，追加上语句结束符
		$datas = substr($datas,0,-1).';';
		$sql = 'INSERT INTO '.$prefix.$tablename.' ('.$keys.') VALUES '.$datas;
		
		if(self::query($sql))
		{
			return self::getInsertId();
		}
		return false;
	}
	
	/*
	 * 删除数据
	 * @param array $condition							要删除的数据条件
	 * @param string $condition['prefix']				表前缀
	 * @param string $condition['table']				要删除数据的表名
	 * @param string or array $condition['condition']	要删除数据的条件
	 * return boole
	 * */
	public function delete($condition = array())
	{
		//表前缀
		@$prefix = is_array($condition) && isset($condition['prefix']) ? $condition['prefix'] : $this->tablePrefix;
		
		//表
		@$table = is_array($condition) && isset($condition['table']) ? $condition['table'] : self::$table;
		
		//删除数据
		$sql = 'DELETE FROM '.$prefix;
		
		if(is_array($condition) && !empty($condition))
		{
			$sql .= $table.' WHERE';
			//判断条件是数组还是字符串
			if(is_array($condition['condition']) && !empty($condition['condition']))
			{
				//遍历条件
				foreach($condition['condition'] as $key=>$val)
				{
					//如果字段值为数组，那么条件运算符由值条件决定
					if(is_array($val) && !empty($val))
					{
						switch($val[0])
						{
							case 'in':
								$sql .= ' '.$key.' in (';
								//如果值为数组
								if(is_array($val[1]))
								{
									foreach($val[1] as $val1)
									{
										$sql .= $val1.',';
									}
									//截去掉最后一个逗号
									$sql = substr($sql,0,-1);
								}
								else
								{
									$sql .= $val[1];
								}
								$sql .= ') AND';
								break;
							case 'notin':
								$sql .= ' '.$key.' NOT IN (';
								//如果值为数组
								if(is_array($val[1]))
								{
									foreach($val[1] as $val1)
									{
										$sql .= $val1.',';
									}
									//截去掉最后一个逗号
									$sql = substr($sql,0,-1);
								}
								else
								{
									$sql .= $val[1];
								}
								$sql .= ') AND';
								break;
							default :
								$sql .= ' '.$key.' '.$val[0].' '.$val[1].' AND';
								break;
						}
					}
					else
					{
						$sql .= ' '.$key.' = '.$val.' AND';
					}	
				}
				
				//截去掉组后一个连接关系运算符
				$sql = substr($sql,0,-4);
			}
			else
			{
				//字符串条件
				$sql .= ' '.$condition['condition'];
			}
		}
		else
		{
			return null;
		}
		//执行SQL
		return self::query($sql);
	}
	
	/*
	 * 修改数据
	 * @param string or array $table				表
	 * @param string or array $condition			更新条件
	 * @param string or array $data					更新数据
	 * return boole
	 * */
	public function update($condition=null,$data=null,$table=null)
	{
		$table = $table ? $table : self::$table;
		//表前缀
		$prefix = is_array($table) && isset($table['prefix']) ? $table['prefix'] : $this->tablePrefix;
		//表名
		$table = is_array($table) && isset($table['table']) ? $table['table'] : self::$table;
		
		//更新数据
		$datas = '';
		if(!empty($data) && is_array($data))
		{
			foreach($data as $key=>$val)
			{
				$datas .= $key . ' = "' . $val . '",';
			}
		}
		$datas = substr($datas,0,-1);
		
		//更新条件
		$conditions = '';
		if(is_array($condition) && !empty($condition))
		{
			//遍历条件
			foreach($condition as $key=>$val)
			{
				//值为数组时，条件运算符由$val决定
				if(is_array($val) && !empty($val))
				{
					switch($val[0])
					{
						case 'in':
							$conditions .= ' ' .$key . ' IN (';
							if(is_array($val[1]) && !empty($val[1]))
							{
								foreach($val[1] as $val1)
								{
									$conditions .= '"' . $val1 . '",';
								}
								$conditions = substr($conditions,0,-1);
							}
							else
							{
								$conditions .= $val[1];
							}
							$conditions .= ')';
							break;
						case 'notin':
							$conditions .= ' ' . $key . ' NOT IN (';
							if(is_array($val[1]) && !empty($val[1]))
							{
								foreach($val[1] as $val1)
								{
									$conditions .= '"' . $val1 . '",';
								}
								$conditions = substr($conditions,0,-1);
							}
							else
							{
								$conditions .= $val[1];
							}
							$conditions .= ')';
							break;
						default:
							$conditions .= ' ' . $key . ' ' .$val[0] .' "'. $val[1] . '" AND';
							break;
					}
				}	
				else
				{
					$conditions .= ' ' .$key . ' = "' . $val . '" AND';
				}
			}
			//截去掉最后一个运算连接符
			$conditions = substr($conditions,0,-4);
		}
		else
		{
			$conditions .= $condition;
		}
		
		$sql = 'UPDATE '.$prefix.$table.' SET '.$datas.' WHERE '.$conditions;
		return self::query($sql);
	}
	
	/*
	 * 查询单条数据
	 * @Access public
	 * @param array $checkData								查询数据集合
	 * @param string $checkData['prefix']					表前缀
	 * @param string $checkData['table']					表名
	 * @param string or array $checkData['field']			字段
	 * @param string or array $checkData['condition']		查询条件
	 * return array
	 * */
	public function find($checkData=array())
	{
		//表前缀
		@$prefix = empty($checkData['prefix']) ? $this->tablePrefix : $checkData['prefix'];
		//表名
		@$table = empty($checkData['table']) ? self::$table : $checkData['table'];
		
		//字段
		$field = empty($checkData['field']) ? '*' : $checkData['field'];
		if(is_array($field))
		{
			$fields = '';
			foreach($field as $key=>$val)
			{
				//如果键名不为整型数字，那么字段命名别名
				if(!is_int($key))
				{
					$fields .= $key.' AS '.$val.',';
				}
				else
				{
					$fields .= $val.',';
				}
			}
			
			//截去掉最后一个连接逗号,
			$field = substr($fields,0,-1);
		}
		//条件
		@$condition = $checkData['condition'];
		
		//数据库查询命令语句
		$sql = 'SELECT '.$field.' FROM '.$prefix.$table;
		
		//数组
		if(is_array($condition) && !empty($condition))
		{
			$sql .= ' WHERE ';
			
			//判断数组是否为多个条件,如果为多个查询条件则
			if(count($condition) > 1)
			{
				//遍历条件组合查询条件
				foreach($condition as $key=>$val)
				{
					//如果值为数组，那么运算关系符由$key决定
					if(is_array($val))
					{
						$valstr = $val[0].' "'.$val[1].'"';
						$sql .= $key.' '.$valstr.' AND ';
					}
					else
					{
						$sql .= $key.' = "'.$val.'" AND ';
					}
				}
				
				//截去掉最后一个AND连接
				$sql = substr($sql,0,-5);
			}
			//单个查询条件
			else
			{
				//遍历检测查询值是否为数组
				foreach($condition as $key=>$val)
				{
					//查询值为数组时，运算关系符由$key决定
					if(is_array($val))
					{
						$valstr = $val[0].' "'.$val[1].'"';
						$sql .= $key.' '.$valstr;
					}
					else
					{
						$sql .= $key.' = "'.$val.'"';
					}
				}
			}
		}
		//字符串
		else
		{
			if(!empty($condition))
			{
				$sql .= ' WHERE '.$condition;
			}
		}
		
		//查询到的结果集
		$result = self::query($sql);
		if($result)
		{
			if(!empty($result))
			{
				$array = array();
				while(@$row = mysql_fetch_assoc($result))
				{
					$array = $row;
				}
				
				//释放资源
     			mysql_free_result($result);
				return $array;
			}
			else
			{
				return null;
			}
		}
		else
		{
			return false;
		}
	}
	
	/*
	 * 查询数据
	 * @param array $checkData							查询数据数组条件
	 * @param string $checkData['prefix']				表前缀
	 * @param string $checkData['table']				表名
	 * @param string or array $checkData['field']		字段
	 * @param string or array $checkData['condition']	查询条件
	 * @param string or array $checkData['group']		分组
	 * @param string or array $checkData['order']		排序
	 * @param string or array $checkData['limit']		查询数目	
	 * return array									
	 * */
	public function select($checkData=array())
	{
		//表前缀
		@$prefix = empty($checkData['prefix']) ? $this->tablePrefix : $checkData['prefix'];
		//表名
		@$table = empty($checkData['table']) ? self::$table : $checkData['table'];
		//字段
		$field = empty($checkData['field']) ? '*' : $checkData['field'];
		if(is_array($field))
		{
			$fields = '';
			foreach($field as $key=>$val)
			{
				//如果键名不为整型数字，那么字段命名别名
				if(!is_int($key))
				{
					$fields .= $key.' AS '.$val.',';
				}
				else
				{
					$fields .= $val.',';
				}
			}
			
			//截去掉最后一个连接逗号,
			$field = substr($fields,0,-1);
		}
		//条件
		@$condition = $checkData['condition'];
		
		//分组
		@$group = $checkData['group'];
		if(!empty($group))
		{
			if(is_array($group))
			{
				$groups = '';
				foreach($group as $val)
				{
					$groups .= $val.',';
				}
				$group = substr($groups,0,-1);
			}
		}
		
		//排序
		@$order = $checkData['order'];
		if(!empty($order))
		{
			if(is_array($order))
			{
				$orders = '';
				foreach($order as $key=>$val)
				{
					$orders .= $key.' '.$val.',';	
				}
				$order = substr($orders,0,-1);
			}
		}
		
		//获取数目
		@$limit = $checkData['limit'];
		
		//数据库查询命令语句
		$sql = 'SELECT '.$field.' FROM '.$prefix.$table;
		
		//数组
		if(is_array($condition) && !empty($condition))
		{
			$sql .= ' WHERE ';
			
			//判断数组是否为多个条件,如果为多个查询条件则
			if(count($condition) > 1)
			{
				//遍历条件组合查询条件
				foreach($condition as $key=>$val)
				{
					//如果值为数组，那么运算关系符由$key决定
					if(is_array($val))
					{
						$valstr = $val[0].' "'.$val[1].'"';
						$sql .= $key.' '.$valstr.' AND ';
					}
					else
					{
						$sql .= $key.' = "'.$val.'" AND ';
					}
				}
				
				//截去掉最后一个AND连接
				$sql = substr($sql,0,-5);
			}
			//单个查询条件
			else
			{
				//遍历检测查询值是否为数组
				foreach($condition as $key=>$val)
				{
					//查询值为数组时，运算关系符由$key决定
					if(is_array($val))
					{
						$valstr = $val[0].' "'.$val[1].'"';
						$sql .= $key.' '.$valstr;
					}
					else
					{
						$sql .= $key.' = "'.$val.'"';
					}
				}
			}
		}
		//字符串
		else
		{
			if(!empty($condition))
			{
				$sql .= ' WHERE '.$condition;
			}
		}
		
		//分组
		if(!empty($group))
		{
			$sql .= ' GROUP BY '.$group;	
		}

		//排序
		if(!empty($order))
		{
			$sql .= ' ORDER BY '.$order;
		}
		
		//获取数目
		if(!empty($limit))
		{
			if(is_array($limit))
			{
				$limit = $limit[0].','.$limit[1];		
			}
			$sql .= ' LIMIT '.$limit;
		}
		
		//查询到的结果集
		$result = self::query($sql);
		if($result)
		{
			if(!empty($result))
			{
				$array = array();
				while(@$row = mysql_fetch_assoc($result))
				{
					$array[] = $row;
				}
				//释放资源
     			mysql_free_result($result);
				return $array;
			}
			else
			{
				return null;
			}
		}
		else
		{
			return false;
		}
	}
	
	/*
	 * 视图
	 * @ Access Public
	 * @ param string $viewName							视图名称
	 * @ param array $tables							视图真实表
	 * @ param array $query								查询视图条件
	 * @ param string $queryType						视图操作类型 ( find | select | update | add | delete )
	 * return array or boole
	 * */
	public function view($viewName = null, $tables = array(), $query = array(), $queryType = 'select')
	{
		//标识视图表
		$viewName = 'view_'.$viewName;
		//视图表名称
		$query['table'] = $viewName;
		//判断要创建的视图是否已存在
		$view_exists_sql = 'SELECT COUNT(information_schema.VIEWS.TABLE_SCHEMA) AS counts FROM information_schema.VIEWS WHERE information_schema.VIEWS.TABLE_NAME="'.$this->tablePrefix.$viewName.'" AND (information_schema.VIEWS.TABLE_SCHEMA="'.$this->dbName.'");';
		//查询视图是否存在，存在返回：1，不存在返回：0
		$view_exists = self::query($view_exists_sql);
		$view_row = mysql_fetch_assoc($view_exists);
		if(!$view_row['counts']){
			/*视图未存在*/
			if(!empty($tables)){
				//创建视图
				$create_view_sql = 'CREATE ALGORITHM=MERGE VIEW '.$this->tablePrefix.$viewName.' (';
				//要查询的真实表
				$true_tables = array();
				//视图表字段
				$viewField = array();
				//表查询语句
				$tablesField = array();
				//查询条件
				$wheres = array();
				
				foreach($tables as $table=>$fields){
					foreach($fields as $fk=>$fv){
						//语句定义字段识别
						switch($fk){
							case '0':
								//组合视图字段
								if(!in_array($fv,$viewField)){
									array_push($viewField,$fv);
								}
								//组合表字段
								array_push($tablesField,$table . '.' . $fv);
								break;
							//语句连接类型( LEFT JOIN | RIGHT JOIN | INNER JOIN )
							case '_TYPE':
								break;
							//语句间联合条件
							case '_ON':
								array_push($wheres,$fv);
								break;
							//表名称
							case '_TABLE':
								//表设置别名
								$tableName = $this->tablePrefix.$fv;
								if(is_array($fv)){
									$tableName = $fv['prefix'].$fv['name'];
								}
								array_push($true_tables,$tableName . ' AS ' . $table);
								break;
							//表字段
							default:
								//组合视图字段
								if(!in_array($fv,$viewField)){
									array_push($viewField,$fv);
								}
								//组合表字段
								if(is_numeric($fk)){
									$fk = $fv;
								}
								array_push($tablesField,$table . '.' . $fk);
								break;
						}
					}
					//将视图字段转字符串(分解一个数组为字符串)
					$viewField_str = implode(',',$viewField);
					//将表字段转字符串
					$tablesField_str = implode(',',$tablesField);
					//将表转字符串
					$table_str = implode(',',$true_tables);
					//将查询转字符串
					$wheres_str = implode(' AND ',$wheres);
				}
				//连接视图字段
				$create_view_sql .= $viewField_str . ') AS SELECT ';
				//连接表字字段
				$create_view_sql .= $tablesField_str . ' FROM ';
				//连接查询表
				$create_view_sql .= $table_str;
				//连接查询条件
				$create_view_sql .= ' WHERE '.$wheres_str;
				
				//创建视图
				if(false === self::query($create_view_sql)){
					//创建视图失败
					$result['errorCode'] = 2;
					$result['create_view_sql'] = $create_view_sql;
					$result['errorMsg'] = "create view error!(创建视图失败)";
					return $result;
				}
			}
		}
		
		//查询视图
		$query['table'] = $viewName;
		switch($queryType)
		{
			//查询单个
			case 'find':
				return self::find($query);
				break;
			//查询多个
			case 'select':
				return self::select($query);
				break;
			//更新
			case 'update':
				break;
			//删除
			case 'delete':
				break;
			//添加
			case 'add':
				break;
		}
	}
	
	/*
	 * 开始事务
	 * @ Access Public
	 * @ param array $sqls						执行的数据命令
	 * @ param string $sqls['type']				操作类型[添加 add | 更新 update | 删除 delete]
	 * @ param int $sqls['alls']				一条命令中是否同时插入多条数据	
	 * @ param int $sqls['table']				操作的表名
	 * @ param int $sqls['condition']			操作条件
	 * @ param int $sqls['data']				操作参数数据	
	 * return array
	 * */
	public function affair($sqls = array())
	{
		//关闭事务自动提交
		self::query("SET AUTOCOMMIT=0");
		//开始事务
		self::query("BEGIN");
		//存放每一条命令执行的结果，成功：1  失败：0
		$result = array();
		$sqls_res = array();
		foreach($sqls as $key=>$val){
			//事务过程
			switch($val['type']){
				//插入
				case 'add':
					//是否同时插入多条数据
					if($val['alls'])
					{
						if(self::addAll($val['data'],$val['table']))
						{
							array_push($result,1);
						}else{
							array_push($result,0);
						}
					}else{
						if(self::add($val['data'],$val['table']))
						{
							array_push($result,1);
						}else{
							array_push($result,0);
						}
					}
					break;
				//更新
				case 'update':
					if(false !== self::update($val['condition'],$val['data'],$val['table'])){
						array_push($result,1);
					}else{
						array_push($result,0);
					}
					break;
				case 'delete':
					if(self::delete($val['condition'])){
						array_push($result,1);
					}else{
						array_push($result,0);
					}
					break;
				default:
					array_push($result,0);
					break;
			}
			array_push($sqls_res,self::getSql());
		}
		//事务提交/回滚
		$res['status'] 		= 0;
		if(in_array(0,$result)){
			$res['result'] 		= $result;
			$res['sql_res'] 	= $sqls_res;
			
			//存在执行失败命令，事务回滚
			self::query("ROLLBACK");
			
		}else{
			$res['status'] 		= 1;
			$res['result'] 		= $result;
			$res['sql_res'] 	= $sqls_res;
			
			//命令全部执行成功，事务提交
			self::query("COMMIT");
		}
		//结束事务
		self::query("END");
		//处理完事务后，开启自动提交
		self::query("SET AUTOCOMMIT=1");
		return $res;
	}
	
	/*
	 * 获取最后一条插入数据的id
	 * @Access public
	 * return int
	 * */
	public function getInsertId()
	{
		return mysql_insert_id();
	}
	
	/*
	 * 获取表字段
	 * @Access public
	 * @param string or array $table			数据表
	 * return array
	 * */
	public function getTableField($table=null)
	{
		$table 		= $table ? $table : self::$table;
		//表前缀
		$prefix 	= is_array($table) && !empty($table['prefix']) ? $table['prefix'] : $this->tablePrefix;
		//表名
		$tablename  = is_array($table) && !empty($table['table']) ? $table['table'] : $table;
		
		$sql = 'DESC '.$prefix.$tablename;
		$result = self::query($sql);
		
		if(!$result)
		{
			return false;	
		}
		
		$field = array();
		while(@$row = mysql_fetch_assoc($result))
		{
			//主键
			if($row['Key'] == 'PRI')
			{
				$field['prikey'] = $row['Field'];
			}
			//字段
			$field['fields'][] = $row['Field'];
		}
		return $field;
	}
	
	/*
	 * 获取总行数
	 * @Access public
	 * @param string or array $table			数据表
	 * @param string or array $condition		查询条件
	 * return string
	 * */
	public function getRowsCount($condition=null,$table=null)
	{
		$table 		= $table ? $table : self::$table;
		//表前缀
		$prefix 	= is_array($table) && !empty($table['prefix']) ? $table['prefix'] : $this->tablePrefix;
		//表名
		$tablename  = is_array($table) && !empty($table['table']) ? $table['table'] : $table;
		
		//查询SQL
		$sql = 'SELECT COUNT(*) AS counts FROM '.$prefix.$tablename;
		//数组
		if(is_array($condition) && !empty($condition))
		{
			$sql .= ' WHERE ';
			
			//判断数组是否为多个条件,如果为多个查询条件则
			if(count($condition) > 1)
			{
				//遍历条件组合查询条件
				foreach($condition as $key=>$val)
				{
					//如果值为数组，那么运算关系符由$key决定
					if(is_array($val))
					{
						$valstr = $val[0].' "'.$val[1].'"';
						$sql .= $key.' '.$valstr.' AND ';
					}
					else
					{
						$sql .= $key.' = "'.$val.'" AND ';
					}
				}
				
				//截去掉最后一个AND连接
				$sql = substr($sql,0,-5);
			}
			//单个查询条件
			else
			{
				//遍历检测查询值是否为数组
				foreach($condition as $key=>$val)
				{
					//查询值为数组时，运算关系符由$key决定
					if(is_array($val))
					{
						$valstr = $val[0].' "'.$val[1].'"';
						$sql .= $key.' '.$valstr;
					}
					else
					{
						$sql .= $key.' = "'.$val.'"';
					}
				}
			}
		}
		//字符串
		else
		{
			if(!empty($condition))
			{
				$sql .= ' WHERE '.$condition;
			}
		}
		$resource = self::query($sql);
		@$rows = mysql_fetch_row($resource);
		return $rows[0];
	}
	
	/*
	 * 字段累加
	 * @param string or array $condition		条件
	 * @param array $data 						累加字段
	 * return boole or array
	 * */
	public function fieldSum($condition=null,$data=array())
	{
		$fields = array();
		foreach($data as $key=>$val)
		{
			$fields[] = $key;
		}
		
		$findData = array(
			'condition'		=> $condition,
			'field'			=> $fields,
		);
		$row = self::find($findData);
		foreach($row as $k=>$v)
		{
			$row[$k] = $v + $data[$k];
		}
		if(false !== self::update($condition,$row))
		{
			return $row;
		}
		else
		{
			return false;
		}
	}
	
	/*
	 * 字段递减
	 * @param string or array $condition		条件
	 * @param array $data 						递减字段
	 * return boole or array
	 * */
	public function fieldDec($condition=null,$data=array())
	{
		$fields = array();
		foreach($data as $key=>$val)
		{
			$fields[] = $key;
		}
		
		$findData = array(
			'condition'		=> $condition,
			'field'			=> $fields,
		);
		$row = self::find($findData);
		foreach($row as $k=>$v)
		{
			$row[$k] = $v - $data[$k];
		}
		if(false !== self::update($condition,$row))
		{
			return $row;
		}
		else
		{
			return false;
		}
	}
	
	/*
	 * 获取执行的SQL语句
	 * @Access public
	 * return string
	 * */
	public function getSql()
	{
		return $this->sql;
	}
	
	/*
	 * 获取自身对象
	 * @Access public static
	 * return object
	 * */
	public static function getIns($table=null)
	{
		if(!empty($table))
		{
			self::$table = $table;	
		}
		
		if(self::$Ins instanceof self)
		{
			return self::$Ins;	
		}
		else
		{
			return self::$Ins = new self();
		}
	}
	
	/*
	 * 析构函数
	 * */
	public function __destruct()
	{
		//关闭数据库连接
		self::close_connect();
	}
}
?>