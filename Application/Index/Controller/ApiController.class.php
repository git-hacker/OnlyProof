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
			var_dump($res);

			// {
			//     "log_id": 150087028516001,
			//     "result": {
			//         "applyId": 1,
			//         "uprojectId":"1234567890", //自定义项目id
			//         "uUserId":"1234567890", //自定义用户id
			//         "userName":"张三", 
			//         "data":"{'email':'zhangsan@163.com','username':'张三'}",
			//         "bgImage":"http://xxxx.baidu.com", //背景图
			//         "userImage":"http://xxxx.baidu.com" //用户人脸
			//     }
			// }

			// 把结果直接返回免费的签到app返回json
			return $res;

		} 
	}

}