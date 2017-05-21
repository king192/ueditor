<?php
class File {
	public function __construct(){
		$this->base64Up();
	}
	protected function fileUp(){
		file_put_contents('cache/log1.txt', json_encode($_POST));
		// file_put_contents('log2.txt', json_encode(new SplFileObject($_POST['tmp_name'])));
		file_put_contents('cache/is_uploaded_file.txt', !is_uploaded_file($_POST['tmp_name']));
		move_uploaded_file($_POST['tmp_name'], 'Uploads/Adminn/thumb/20170314/test.png');
		exit;
	}
	protected function count(){
		$file = 'cache/count.txt';
		if(file_exists($file)){
			$count = file_get_contents($file);
			// $count = intval($count);
			$res = file_put_contents($file, ++$count);
		}else{
			$res = file_put_contents($file, 0);
		}
		if(!$res){
			exit(json_encode(['code' => -5,'msg' => 'count.txt 不可写']));
		}
	}

	protected function base64Up(){
		// session_start(oid)
		// $cookie = session_get_cookie_params();
		// file_put_contents('cookie', json_encode($cookie));
		// file_put_contents('cookies', json_encode($_COOKIE));
		require 'config/config.php';
		if(config::COOKIE != $_COOKIE['flag']){
			$this->count();
			exit(json_encode(['code' => -100,'msg' => 'ccccccccc'.$_COOKIE['flag']]));
		}
		$base_info = $_POST['file'];
        //上传路径
        $path = './..'.str_replace('\\', '/', $_POST['save_path']);
        $pathinfo = pathinfo($path);
        $dirname = $pathinfo['dirname'];
        $basename = $pathinfo['basename'];
        $extension = $pathinfo['extension'];
        if (!is_dir($dirname)) {
            if(!mkdir($dirname, 0777, true)){
            	exit(json_encode(['code' => -2,'msg' => '文件夹创建失败']));
            }
        }
        //接收base64编码数据
        // $base_info = $_POST['base_img'];
        $base64Len = $_POST['base64Len'];//用于校验数据完整
        // file_put_contents('log.txt', $_POST);
        // $base_info = file_get_contents('log.txt');

        if (preg_match('/^(data:image;base64,)/', $base_info, $result)) {
        	// var_dump($result);
            if (!in_array($extension, array('gif', 'jpg', 'png', 'jpeg','bmp'))) {
                exit(json_encode(['code' => -1,'msg' => '不支持' . $extension . '的图片格式']));
            }
            if(file_exists($path)){
            	exit(json_encode(['code' => -2,'msg' => '图片已存在']));
            }
            if(file_put_contents($path, base64_decode(str_replace('data:image;base64,', '', $base_info)))){
            	exit(json_encode(['code' => 1,'msg' => '图片保存成功','path' => $path])) ;
            }else{
            	exit(json_encode(['code' => -3,'msg' => '图片保存失败','path' => $path])) ;
            }
        } 
        exit(json_encode(['code' => 0,'msg' => '图片保存失败'])) ;
    }
}
$file = new File();