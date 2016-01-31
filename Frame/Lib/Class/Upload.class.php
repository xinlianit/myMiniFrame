<?php 
/*********************************************************
 * 		Time  	: 2015-05-17							 *
 * 		Author	: jirenyou							 	 *
 * 		Email	: 390066398@qq.com						 *
**********************************************************/
namespace Frame;
class Upload
{
	//允许上传的文件类型
	public $AllowUpload = array('image/jpg','image/jpeg','image/png','image/pjpeg','image/gif','image/bmp','image/x-png');
	//同名覆盖
	public $ReplaceName = true;
	//命名规则   unique、time、unchanged
	public $NameRule = 'unique';
	//最大上传限制 2M（单位：BYTE）
	public $MaxUpSize = 2097152;
	//文件上传存放的路径
	public $SavePath = '/Uploads/';
	//是否要加水印
	public $Watermark = false;
	//水印类型(1为文字,2为图片)
	public $WatermarkType = 1;
	//水印位置(1为左下角,2为右下角,3为左上角,4为右上角,5为居中)
	public $WatermarkPosition = 4;
	//水印字符串
	public $WaterString = 'MyFrame';
	//水印图片
	public $WaterImage = '';
	//是否生成缩略图
	public $ThumImage = false;
	//缩略图宽、高
	public $ThumImageSize = array(array(100,100));
	
	//上传
	public function upload()
	{
		//判断是否获得上传的文件
	 	if(!is_uploaded_file($_FILES['Filedata']['tmp_name']))
	 	{
	 		$result['status'] = -1;
	 		$result['msg'] = "请选择要上传的文件";
	 		return $result;		//没有获取到 
	 	}
	 	
	 	//获取到文件
	 	$file = $_FILES['Filedata'];            //定义一个文件变量，把上传的文件赋给文件变量
	 	
	 	//判断上传的文件 	   
	 	if(!in_array($file['type'],$this->AllowUpload))
	 	{    //判断文件类型  ，检查数组中是否存在某个值
	 		  $result['status'] = -2;
		 	  $result['msg'] = "该文件类型不允许上传";
		 	  return $result;		
	 	}
	 	
	 	if($file['size'] > $this->MaxUpSize)
	 	{       
	 		//判断文件的大小,如果上传文件的大小大于规定的大小
	 		$result['status'] = -3;
		 	$result['msg'] = "文件太大不能上传";
		 	return $result;	
	 	}
	 	
	 	//检查上传的文件或目录是否存在
	 	if(!file_exists($this->SavePath))
	 	{
	 		mkdir($this->SavePath,0777,true);               //如果不存在，则创建路径,可创建多级的路径
	 	}
	 	
	 	//获取上传的文件信息
	 	$image_name = $file['tmp_name'];
	 	$image_size = getimagesize($image_name);
	 	$path_info = pathinfo($file['name']);
	 	$file_type = $path_info['extension'];
	 	$path = $this->SavePath;
	 	switch($this->NameRule)
	 	{
	 		case 'unique':
	 			$filename = substr(md5(mt_rand(1000,9999).time().mt_rand(100000,999999).mt_rand(1000,9999)),0,16);
	 			$path .= $filename;
	 			break;
	 		case 'time':
	 			$filename = time();
	 			$path .= $filename;
	 			break;
	 		case 'unchanged':
	 			$filename .= $file['name'];
	 			$path .= $filename;
	 			break;
	 	}
	 	$path .= ".".$file_type;
	 	
	 	//检测上传的文件是否存在
	 	if(file_exists($path))
	 	{
	 		if(!$this->ReplaceName)
	 		{
	 			$result['status'] = -4;
			 	$result['msg'] = "同名文件已经存在";
			 	return $result;	
	 		}
	 	}
	 	
	 	//移动上传文件
	 	if(!move_uploaded_file($image_name,$path))
	 	{
	 		$result['status'] = -5;
		 	$result['msg'] = "移动文件失败";
		 	return $result;	
	 	}
	 	
	 	//文件上传成功
	 	$result['status'] = 1;
	 	$result['msg'] = "文件上传成功";
	 	$result['info'] = pathinfo($path);
	 	//水印
	 	if($this->Watermark)
	 	{
	 		$this->createWatermark($path,$image_size);
	 	}
	 	//缩略图
	 	if($this->ThumImage)
	 	{
	 		foreach($this->ThumImageSize as $val)
	 		{
	 			$thumName = $this->SavePath.$filename.'_'.$val[0].'_'.$val[1].'.png';
	 			if($this->thumbnail($path,$val[0],$val[1],$thumName))
	 			{
	 				$result['thumImage']['thum_'.$val[0].'_'.$val[1]] = $thumName;
	 			}
	 		}
	 	}
	 	return $result;	
	}
	
	/*
	 * 添加水印
	 * @parame string $path			被添加水印图片
	 * */
	public function createWatermark($path = null,$image_size=null)
	{
		$iinfo=getimagesize($path,$iinfo);
        $nimage=imagecreatetruecolor($image_size[0],$image_size[1]);
        $white=imagecolorallocate($nimage,255,255,255);
        $black=imagecolorallocate($nimage,0,0,0);
        $red=imagecolorallocate($nimage,255,0,0);
        imagefill($nimage,0,0,$white);
        switch ($iinfo[2])
        {
            case 1:
            $simage =imagecreatefromgif($path);
            break;
            case 2:
            $simage =imagecreatefromjpeg($path);
            break;
            case 3:
            $simage =imagecreatefrompng($path);
            break;
            case 6:
            $simage =imagecreatefromwbmp($path);
            break;
            default:
            die("不支持的文件类型");
            exit;
        }

        imagecopy($nimage,$simage,0,0,0,0,$image_size[0],$image_size[1]);
        imagefilledrectangle($nimage,1,$image_size[1]-15,80,$image_size[1],$white);

        switch($this->WatermarkType)
        {
        	//加水印字符串
            case 1:   
            imagestring($nimage,2,3,$image_size[1]-15,$this->WaterString,$black);
            break;
            //加水印图片
            case 2:   
            $simage1 =imagecreatefromgif($this->WaterImage);
            imagecopy($nimage,$simage1,0,0,0,0,85,15);
            imagedestroy($simage1);
            break;
        }

        switch ($iinfo[2])
        {
            case 1:
            //imagegif($nimage, $destination);
            imagejpeg($nimage, $path);
            break;
            case 2:
            imagejpeg($nimage, $path);
            break;
            case 3:
            imagepng($nimage, $path);
            break;
            case 6:
            imagewbmp($nimage, $path);
            //imagejpeg($nimage, $destination);
            break;
        }

        //覆盖原上传文件
        imagedestroy($nimage);
        imagedestroy($simage);
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
	public function thumbnail($srcFile = null, $toW = 100, $toH = 100, $toFile = null)
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
}
?>