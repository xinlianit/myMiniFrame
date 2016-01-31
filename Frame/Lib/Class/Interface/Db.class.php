<?php 
/*********************************************************
 * 		Time  	: 2015-01-11							 *
 * 		Author	: jirenyou							 	 *
 * 		Email	: 390066398@qq.com						 *
**********************************************************/
/*
 * 数据库接口
 * */
namespace Frame\Db;
interface Db
{
	/*
	 * 连接数据库
	 * return boole
	 * */
	public function connect();
	
	/*
	 * 选择数据库
	 * @Access public
	 * @param string $dbname			数据库名称
	 * */
	public function select_db($dbname=null);
	
	/*
	 * 设置数据库编码
	 * @Access public 
	 * @param string $char				编码
	 * return boole
	 * */
	public function setChar($char=null);
	
	/*
	 * 发送数据库命令
	 * @Access public
	 * @param string $sql				数据库命令语句
	 * return array or boole
	 * */
	public function query($sql=null);
	
	/*
	 * 关闭数据库连接
	 * @Access public 
	 * */
	public function close_connect();
	
	/*
	 * 插入单条数据
	 * @Access public
	 * @param string $table				插入表
	 * @param array $data       		要添加的数据
	 * return int						插入成功后的数据id
	 * */
	public function add($table=array(),$data = array());
	
	/*
	 * 插入批量多条数据
	 * @Access public
	 * @param string $table				插入表
	 * @param array $data       		要添加的数据
	 * return int						插入成功后的数据id
	 * */
	public function addAll($table=array(),$data = array());
	
	/*
	 * 删除数据
	 * @Access public 
	 * @param array $condition			删除条件
	 * return boole
	 * */
	public function delete($condition = array());
	
	/*
	 * 修改数据
	 * @Access public
	 * @param string or array $table				表
	 * @param string or array $condition			修改条件
	 * @param string or array $data					修改数据
	 * return boole
	 * */
	public function update($table = null,$condition = null,$data = null);
	
	/*
	 * 查询单条数据
	 * @Access public
	 * @param array $checkData						查询数据数组条件
	 * return array
	 * */
	public function find($checkData=array());
	
	/*
	 * 查询多条数据
	 * @Access public
	 * @param array $checkData						查询数据数组条件
	 * return array
	 * */
	public function select($checkData=array());
}
?>