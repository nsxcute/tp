<?php

namespace app\index\controller;
use think\Controller;
use think\Db;
//require ROOT_PATH.'/vendor/php-ffmpeg/php-ffmpeg/src/FFMpeg/FFMpeg.php';
use FFMpeg\FFMpeg;
use FFMpeg\FFProbe;
use FFMpeg\Coordinate\Dimension;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\Format\Video\X264 as X264;
use FFMpeg\Format\Video\WMV as WMV;
use FFMpeg\Format\Video\WebM as WebM;
use FFMpeg\Filters\Video\ExtractMultipleFramesFilter;
use think\request;
class Uploadfile extends Controller
{
	public function index()
	{
		return $this->fetch('');
	}
	public function uploadfile()
	{
		$uploaddir =  ROOT_PATH . 'public/download/';
		$uploadfile = $uploaddir . basename($_FILES['userfile']['name']);

		echo '<pre>';
		if (move_uploaded_file($_FILES['userfile']['tmp_name'], $uploadfile)) {
			//var_dump($_FILES);
			//echo 111;
			//var_dump(ROOT_PATH . 'public/download/'.$_FILES['userfile']['name']);die;
			//$image = \think\Image::open(ROOT_PATH . 'public/download/'.$_FILES['userfile']['name']);
			//var_dump($image);die;
			//$width = $image->width(); 
			//$height = $image->height(); 
			//$a = $image->thumb($width,$height,\think\Image::THUMB_CENTER)->save($_FILES['userfile']['name']);
			//var_dump($a);die;
		    echo "File is valid, and was successfully uploaded.\n";
		} else {
			echo 222;
		    echo "Possible file upload attack!\n";
		}

		echo 'Here is some more debugging info:';
		print_r($_FILES);

		print "</pre>";

	}
	//使用框架对图像的处理
	public function picture()
	{
		$image = \think\Image::open(ROOT_PATH.'./image.png');
		//var_dump($image);die;
		$a = $image->thumb(150,150,\think\Image::THUMB_CENTER)->save('./thumb.png');
		var_dump($a);die;
	} 

	//video
	public function video()
	{
		require ROOT_PATH.'/vendor/autoload.php';
		require ROOT_PATH.'/vendor/php-ffmpeg/php-ffmpeg/src/FFMpeg/FFMpeg.php';
		//$ffmpeg = FFMpeg::create();
		///dump($ffmpeg);die;
		// $format = new WebM();
		// dump($format);
		// die;
		$ffmpeg = \FFMpeg\FFMpeg::create([
            'ffmpeg.binaries'  => '/usr/bin/ffmpeg.exe',
            'ffprobe.binaries' => '/usr/bin/ffprobe.exe',
            'timeout'          => 0, // The timeout for the underlying process
            'ffmpeg.threads'   => 12,   // The number of threads that FFMpeg should use

        ]);
		die;
        $video = $ffmpeg->open(ROOT_PATH . 'public/test.mp4');
		$video = $ffmpeg->open('video.mpg');
		$video
			->filters()
			->resize(new FFMpeg\Coordinate\Dimension(320, 240))
			->synchronize();
		$video
			->frame(FFMpeg\Coordinate\TimeCode::fromSeconds(10))
			->save('frame.jpg');
		$video
			->save(new X264(), 'export-x264.mp4')
			->save(new WMV(), 'export-wmv.wmv')
			->save(new WebM(), 'export-webm.webm');
	}

	public function video1()
    {
		//$uploaddir =  ROOT_PATH . 'public/download/';
		//dump($uploaddir);die;
        //$uploadfile = $uploaddir . basename($_FILES['userfile']['name']);
		//dump($uploadfile);die;
		$video = request()->file('video');//接收video文件
		//dump($video);
        if(!empty($video)){
            $info = $video->move(ROOT_PATH . 'public' . DS . 'uploads');
            if($info) {
                $videoName = $info->getSaveName();
                $source = '/uploads/' .$videoName;//原视频名称

                $dir = ROOT_PATH . 'public' . DS . 'uploads/';//上传图片路径
                $fileName = str_replace('.mp4','.jpg',$videoName);//图片名称
                $video_filePath = $dir.$videoName;//视频路径
                //用ffmpeg截取视频第一帧，并上传保存在和视频一样的路径下
                //使用-ss参数,可以从指定的时间开始处理转换任务后面是1代表从第一秒开始
                //-s参数，后面是图片的宽高，这个可以不用写，因为有横屏的或者竖屏的视频，不能固定
                $str = "ffmpeg -i ".$video_filePath." -y -f mjpeg -ss 1 -t 0.001 -s 1348*1470 ".$dir.$fileName;
                exec($str,$out,$status);
                $datas['video'] = $source;
                $datas['picture'] = '/uploads/' . $fileName;
				//dump($datas);
				$image = \think\Image::open('.'.$datas['picture']);
				//dump($image);die;
                //调用tp5的图片处理类，获取图片宽高，并给封面图片添加播放按钮
                $width = $image->width();
                // 返回图片的高度
                $height = $image->height();
                $datas['width'] = $width;
                $datas['height'] = $height;
				$water = $dir.'water.png';//播放按钮
				//dump($water);
				//die;
                $image->water($water,\think\Image::WATER_NORTHWEST,50)->save('.'.$datas['picture']);
            }
        }
        
    }
}
