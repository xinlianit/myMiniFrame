<?php 
/*
 * 测试类
 * */
class Text
{
	private $params=null;
	public function __constructe($param)
	{
		echo $param;
		$this->params = $param;
	}
	public function test()
	{
		echo "new class success!";
		if($this->params){
			echo "<br/>param: ".$this->params;
		}
	}
}
?>