<?php
namespace Index\Controller;

use Think\Controller;
class ApiController extends Controller{

	//人脸验证接口
	public function verify($params = array())
	{
		// params is invalid
		$params = json_decode($params,true);
		if( isset($params['token']) &&  ( isset($params['faceImage']) || isset($params['qrcode']) ) ){

			header("Content-Type:application/json");
			$url  = 'https://aip.baidubce.com/api/v1/solution/direct/meeting/verify?access_token='. access_token( 'qiandao' );

			$bodys['token'] = $params['token'];
			$bodys['source'] = 0;
 			if( !empty($params['faceImage']) ){
 				$bodys['faceImage'] = $params['faceImage'];
			}
			if( !empty($params['qrcode']) ){
 				$bodys['qrcode'] = $params['qrcode'];
			}
			$bodys = json_encode($bodys);
			$res = request_post($url, $bodys);
			
			// 把结果直接返回免费的签到app返回json
			return $res;

		} 
	}

}