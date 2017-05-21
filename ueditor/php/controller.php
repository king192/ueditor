<?php
// if(file_exists('config.php')){
    $myConfig = require 'config.php';
// }
$mm = parse_url($_SERVER['HTTP_REFERER']);
// var_dump($mm);
$access_origin = $mm['scheme'].'://'.$mm['host'];
// $origin = isset($_SERVER['HTTP_ORIGIN'])? $_SERVER['HTTP_ORIGIN'] : 'oo';
// echo $origin;exit;
if(in_array($access_origin, $myConfig['cross_domain'])){
    header('Access-Control-Allow-Origin: '.$access_origin); //设置http://www.baidu.com允许跨域访问
    header('Access-Control-Allow-Headers: X-Requested-With,X_Requested_With'); //设置允许的跨域header
}
date_default_timezone_set("Asia/chongqing");
error_reporting(E_ERROR);
header("Content-Type: text/html; charset=utf-8");

$CONFIG = json_decode(preg_replace("/\/\*[\s\S]+?\*\//", "", file_get_contents("config.json")), true);
$action = $_GET['action'];

switch ($action) {
    case 'config':
        $result =  json_encode($CONFIG);
        break;

    /* 上传图片 */
    case 'uploadimage':
    /* 上传涂鸦 */
    case 'uploadscrawl':
    /* 上传视频 */
    case 'uploadvideo':
    /* 上传文件 */
    case 'uploadfile':
        $result = include("action_upload.php");

        $res = json_decode($result,true);
        if('SUCCESS' == $res['state']){
            $cross_domain = str_replace(['://','.'], ['_','_'], $access_origin);
            $dir = 'cache/'.$cross_domain.'/'.date('Y/m/d');
            if(!file_exists($dir)){
                mkdir($dir,0777,true);
            }
            $fileName = $dir.'/limit_size.txt';
            if(file_exists($fileName)){
                $size = file_get_contents($fileName);
            }else{
                $size = 0;
            }
            $size = $size + $res['size'];
            if($size> $myConfig['daly_size']){
                $filePath = $_SERVER['DOCUMENT_ROOT'].$res['url'];
                unlink($filePath);
                $result = json_encode([
                    "state" => '日限:'.($myConfig['daly_size']/1024).'k',
                    ]);
            }else{
                if(!file_put_contents($fileName, $size)){
                    exit($fileName);
                }
            }
        }
        break;

    /* 列出图片 */
    case 'listimage':
        $result = include("action_list.php");
        break;
    /* 列出文件 */
    case 'listfile':
        $result = include("action_list.php");
        break;

    /* 抓取远程文件 */
    case 'catchimage':
        $result = include("action_crawler.php");
        $res = json_decode($result,true);
        if('SUCCESS' == $res['state']){
                $cross_domain = str_replace(['://','.'], ['_','_'], $access_origin);
                $dir = 'cache/'.$cross_domain.'/'.date('Y/m/d');
                if(!file_exists($dir)){
                    mkdir($dir,0777,true);
                }
                $fileName = $dir.'/limit_size.txt';
            foreach ($res['list'] as $k => $v) {
                if(file_exists($fileName)){
                    $size = file_get_contents($fileName);
                }else{
                    $size = 0;
                }
                $size = $size + $v['size'];
                if($size> $myConfig['daly_size']){
                    $filePath = $_SERVER['DOCUMENT_ROOT'].$v['url'];
                    unlink($filePath);
                    $result = json_encode([
                        "state" => '日限:'.($myConfig['daly_size']/1024).'k',
                        ]);
                }else{
                    if(!file_put_contents($fileName, $size)){
                        exit($fileName);
                    }
                }
            }
        }
        break;

    default:
        $result = json_encode(array(
            'state'=> '请求地址出错'
        ));
        break;
}
/* 输出结果 */
if (isset($_GET["callback"])) {
    if (preg_match("/^[\w_]+$/", $_GET["callback"])) {
        echo htmlspecialchars($_GET["callback"]) . '(' . $result . ')';
    } else {
        echo json_encode(array(
            'state'=> 'callback参数不合法'
        ));
    }
} else {
    echo $result;
}
