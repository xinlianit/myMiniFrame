<?php
namespace Home\Controller;
use Frame\Controller;
class IndexController extends Controller
{
	public function index()
	{
		$textclass = newClass('Text','hello');
		$textclass->test();	
	}
}
?>