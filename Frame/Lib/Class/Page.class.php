<?php
/*********************************************************
 * 		Time  	: 2015-05-03							 *
 * 		Author	: jirenyou							 	 *
 * 		Email	: 390066398@qq.com						 *
**********************************************************/
namespace Frame;
/*
 *   设计思路：  当 当前页为 1 时，没有 上一页  和  首页 
 *             当 当前页为 2-5 时 ，有上一页，没有首页，因为此时 第一页 还没有冲掉
 *             当 当前页为 6 以上时，有上一页 和 首页
 *  
 *  页码
 *    设计思路：定义一个 i, 记录下当前页数，赋给 j
 *             边界考虑，设置边界不大于总页数
 *             当       1=<i<6                 时，循环出        1-10 页码
 *             当       6=<i<总页数 -5          时 ，循环出       2-11、3-12、4-13、5-14、6-15.....
 *             当      总页数-5=<i<总页数                 时 ，循环出      总页数-10——总页数
 *
 *               循环 1-10 页码           循环 2-11、3-12、4-13、页码            循环 总页-10—— 总页
 *             |<-------------|-------------------------------|------------------->|
 *             |______________|_______________________________|____________________|
 *             1              6                             总页-6                总页
 *  
 *  下一页、尾页
 *    设计思路：边界考虑，当 当前页 小于 总页数
 *             当 当前页  小于  总页-5，有上一页  和   尾页
 *             当当前页大于  总页-5 时   ，只有下一页  ，以为此事尾页码 已经出来
 *             当用户点击到最后一页时   提示是最后一页了  可以提示      
 *            
 */
class Page
{
    //分页变量赋初值
    //字段
    public $Field = '*';
    //当前对象
    public $Model;
    //条件
    public $Condition;
    //总记录数
    public $rowCount=0;
    //每页显示记录数
    public $PageSize=10;
    //总页数
    public $PageCount=0;
    //当前页数
    public	$NowPage=1;
    //排序
    public $Order;
    //分页导航
    public $PageHtml=null;
    
    public function __construct($table)
    {
    	$this->Model = model($table);
    }
    
	//获取分页
     public function getPage()
     {
     	//总共记录数	
        $page['rowCount'] 			= $this->Model->getRowsCount($this->Condition,$this->table);
        //总页数
        $page['pageCount'] 			= $this->getPageCount();
     	//当前页数
     	$page['nowPage']			= $this->NowPage;
     	//一页记录数
     	$page['pageSize']			= $this->PageSize;
     	//分页导航
     	$page['pageHtml'] 			= $this->getPageHtml();
     	//分页Sql
     	$page['sql'] 				= $this->Model->getSql();
     	//分页Sql
     	$page['url'] 				= substr(urlMarge('',array('page=1')),0,-1);
     	//$page['url'] 				= urlMarge('',array());
     	//记录列表
     	$page['list'] 				= $this->getPageList();
     	//返回分页集合数据
     	return $page;
     }
    
	//1.1、获得总页数
    public function getPageCount()
    {
       	//获取表总记录数
        $this->rowCount = $this->Model->getRowsCount($this->Condition,$this->table) ? $this->Model->getRowsCount($this->Condition,$this->table) : 1;
        //1.5、判断结果
        if($this->rowCount)
        {
	        //1.6、计算出总页数
	        $this->PageCount=ceil($this->rowCount/$this->PageSize);
        }
       
        //4、把结果返回给调用页面
         return $this->PageCount;
     }
       
     //1.2、构造页显示函数
     public function getPageList()
     {
	     //操作语句
		 $check = array(
		 	'condition'=>$this->Condition,
		   	'field'=>$this->Field,
		   	'limit'=>array(($this->NowPage-1)*$this->PageSize,$this->PageSize),
		 	'order'=>$this->Order,
		 );
		 return $this->Model->select($check);
     }
     
     //获取分页导航
     public function getPageHtml()
     {
     	
	    //2.18、首页、上一页
	    /*if($NowPage>1){
	    	//echo "<a href='MembersList.php?NowPage=1'>首页</a>&nbsp;";
	     	$PreviousPage=$NowPage-1;
	     	echo "<a href='MembersList.php?NowPage=$PreviousPage'>上一页</a>&nbsp;";
	    }*/
     	$pageHtml = '';
	    if($this->NowPage != 1)
	    {
	    	$PreviousPage = intval($this->NowPage) - 1;
	    	$pageHtml .= '<a class="pre_page" href="'.urlMarge('',array('page='.$PreviousPage)).'"><<上一页</a>';
	    	
	    	/*
	        if($this->NowPage >= 2 && $this->NowPage <= 5){
	        	  $PreviousPage = intval($this->NowPage) - 1;
	        	  $pageHtml .= '<a class="pre_page" href="MembersList.php?NowPage=$PreviousPage"><<上一页</a>';
	        }else{
	     	      $pageHtml .= '<a href="MembersList.php?NowPage=1">首页</a>';
	     	      $PreviousPage = $this->NowPage-1;
	     	      $pageHtml .= '<a class="pre_page" href="MembersList.php?NowPage=$PreviousPage"><<上一页</a>';
	        }
	        */
	    }  
     
     
     //2.19、页码
     if($this->NowPage <= $this->PageCount){
          $i = $this->NowPage;
          $j = $i;
          $numcount = ($this->PageCount > 8) ? 8 : $this->PageCount;
          if($i<6)
          {
	           for($i=1;$i<=$numcount;$i++)
	           {
		        	if($i == $this->NowPage)
		        	{
		        	 	$pageHtml .= '<span class="now_page">'.$i.'</span>';
		          	}else{
		        	    $pageHtml .= '<a href="'.urlMarge('',array('page='.$i)).'">'.$i.'</a>';
		        	}      			
	     	   }
          }else{
          	
          if($i <= $this->PageCount - 5){

     	    for($i=$this->NowPage-4;$i<$j+4;$i++){
     	      if($i==$this->NowPage){
     			 $pageHtml .= '<span class="now_page">'.$i.'</span>';
     		  }else{
     		  	 $pageHtml .= '<a href="'.urlMarge('',array('page='.$i)).'">'.$i.'</a>';
     		  }    		
     	      }
            } else{
            	
            	if($i>$this->PageCount-5 && $i<=$this->PageCount){
            		
            		for($i=$this->PageCount-9;$i<=$this->PageCount;$i++){
            			if($i==$this->NowPage){
            				$pageHtml .= '<span class="now_page">'.$i.'</span>';
            			}else{
            				$pageHtml .= '<a href="'.urlMarge('',array('page='.$i)).'">'.$i.'</a>';           				
            			}
            		}

            	}

            }  


          }   
          //总页数大于导航页数并且页数大于最后导航页数
          if($this->PageCount > $numcount && $this->NowPage < $this->PageCount - 4)
          {
          	  $pageHtml .= '<span class="morepage">...</span><a href="'.urlMarge('',array('page='.$this->PageCount)).'">'.$this->PageCount.'</a>';
          }     
     }
     	     
     //2.20、下一页、尾页
     /*if($NowPage<$PageCount){
     	$NextPage=$NowPage+1;
     	echo "<a href='MembersList.php?NowPage=$NextPage'>下一页</a>&nbsp;";
     	echo "<a href='MembersList.php?NowPage=$PageCount'>尾页</a>";
     }*/
     if($this->NowPage < $this->PageCount){
     	$NextPage = $this->NowPage + 1;
     	$pageHtml .= '<a class="next_page" href="'.urlMarge('',array('page='.$NextPage)).'">下一页>></a>';
     	/*
      	if($this->NowPage < $this->PageCount-5){
	     	$pageHtml .= '<a class="next_page" href="MembersList.php?NowPage=$NextPage">下一页>></a>';
	     	$pageHtml .= '<a href="MembersList.php?NowPage=$PageCount">尾页</a>';     	
      	}else{
     		$pageHtml .= '<a class="next_page" href="MembersList.php?NowPage=$NextPage">下一页>></a>';     	
      	}
      	*/
     }
     
      if($this->NowPage == $this->PageCount)
      {
      	//$pageHtml .= "没有下一页了";
      }
      
      return $pageHtml;
     }
}
?>