<?php
namespace Home\Controller;
use Think\Controller;
class IndexController extends Controller {
    //1. 会议操作接口【后台管理系统功能】
	public function create_meeting()
	{
		// header("Content-Type:application/json");
		$type = 'qiandao';
		$token = access_token($type);

		$url = 'https://aip.baidubce.com/api/v1/solution/direct/meeting/project?access_token='.$token;

		$bodys = array(
			"uProjectId"       => 1001, //自己的主键
			"gId"              => C('baidu.'.$type)['API_Id'],
			"name"             => "会议名称",
			"desc"             => "会议描述",
			"startTime"        => "1536249600000",
			"endTime"          => "1538236800000",
			"headImage"        => "", //不支持自定义
			"bgImage"          => "", //背景图，可自定义
			"signinSuccessTip" => "签到成功",
			"signinFailTip"    => "签到失败",
			"interactType"     => 0
		);
		// $bodys = json_encode($bodys);
		$res = request_post($url, $bodys);
		var_dump($res);
	}

}