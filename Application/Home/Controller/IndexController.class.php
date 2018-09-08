<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends Controller {

    //1. 手动创建会议
	public function create_meeting()
	{
		//先在本地创建会议
		$data = array(
			'name' => '黑客松编程大赛宣讲',
			'desc' => '黑客松编程大赛宣讲黑客松编程大赛宣讲黑客松编程大赛宣讲',
			'startTime' => '1536249600',
			'endTime' => '1538236800',
			'bdImage' => '',
			'signinSuccessTip' => '签到成功',
			'signinFailTip' => '签到失败',
			'gId' => '0ad6123666a9f71b1816220a1912e9df' // md5('The One')
		);
		$uProjectId = M('meeting')->add($data); 
		if($uProjectId === false){
			die('创建会议失败');
		}
		//调用接口
		header("Content-Type:application/json");
		$url = 'https://aip.baidubce.com/api/v1/solution/direct/meeting/project?access_token='.access_token('qiandao');
		$bodys = array(
			"uProjectId"       => $uProjectId, 
			"gId"              => $gId,  //不要随意换这个值，会换人脸库的
			"name"             => $data['name'],
			"desc"             => $data['desc'],
			"startTime"        => $data['startTime']*1000,
			"endTime"          => $data['endTime']*1000,
			"headImage"        => "", //不支持自定义
			"bgImage"          => $data['bgImage'], //背景图，可自定义
			"signinSuccessTip" => $data['signinSuccessTip'],
			"signinFailTip"    => $data['signinFailTip'],
			"interactType"     => 0, //自动拉取签到成功信息
		);
		$bodys = json_encode($bodys);
		$res = request_post($url, $bodys); 
		var_dump($res);
		
		$res = json_decode($res,true);
		
		


		// {
		//     "log_id": 151255384259103
		//     "result": {
		//         "token": "z9FYdEeDRF0d8Diq",
		//         "qrcodeUrl": "http://bj.bcebos.com/v1/aip-web/17BD5BFCC11744429B5310A5ABDC42E2?authorization=bce-auth-v1%2Ff86a2044998643b5abc89b59158bad6d%2F2017-12-06T09%3A50%3A44Z%2F-1%2F%2Fdea8046dcf6581ab2ddd4c2add83ec134ccc31328fae2044bb33013c59d1d661"
		//     }
		// }

		//存储创建会议的token 和 登录二维码
		if( !$res || isset($res['error_code']) ){
			//删除已有的数据
			M('meeting')->where(['id' => $uProjectId])->delete();
			$this->error( $res['error_msg'] );
		}else{
			$res['result'] = json_decode($res['result'],true);

			if( M('meeting')->where(['id' => $uProjectId])->update([ 'token' => $res['result']['token'] ,'qrcodeUrl' => $res['result']['qrcodeUrl'] ]) !== false){

				S('robot_meeting_token', $res['result']['token'], 3600*24*30) ;

				echo '创建会议成功';
			}else{
				echo '创建会议失败';
			}
		}

	}

}