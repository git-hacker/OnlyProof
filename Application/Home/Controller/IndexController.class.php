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
			"gId"              => '0ad6123666a9f71b1816220a1912e9df',  
			"name"             => $data['name'],
			"desc"             => $data['desc'],
			"startTime"        => $data['startTime']*1000,
			"endTime"          => $data['endTime']*1000,
			"headImage"        => "", //不支持自定义
			"bgImage"          => $data['bgImage'] ? $data['bgImage'] :'', //背景图，可自定义
			"signinSuccessTip" => $data['signinSuccessTip'],
			"signinFailTip"    => $data['signinFailTip'],
			"interactType"     => 0, //自动拉取签到成功信息
		);
		$bodys = json_encode($bodys);
		$res = request_post($url, $bodys); 
		$res = json_decode($res,true);
		
		//存储创建会议的token 和 登录二维码
		if( !$res || isset($res['error_code']) ){
			//删除已有的数据
			M('meeting')->where(['id' => $uProjectId])->delete();
			echo  $res['error_msg'] ;
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