<?php
namespace Index\Controller;

use Think\Controller;
class IndexController extends Controller{

	public function index()
	{
		// echo '开始你的代码之旅吧！';
		echo md5('The One'); //0ad6123666a9f71b1816220a1912e9df
	}


	//注册页面
	public function register()
	{
		if(IS_POST){

			//将得到的name ,phone, faceImage 存入数据库
			$name = I('post.name','','strip_tags'); if(!$name) $this->error('请填写用户名');

			$telephone = I('post.telephone','','strip_tags'); 
			if( !$telephone || !preg_match("/^1[345678]{1}\d{9}$/",$telephone) )  $this->error('请填写正确的手机号');
				
			$faceImage = I('post.faceImage','','strip_tags');  if(!$faceImage) $this->error('请上传人脸照片');
			$uUserId = M('user')->add(['name' => $name , 'telephone' => $telephone, 'faceImage' => $faceImage ]);
	
			//百度签到平台的token
			$meeting_token = S('robot_meeting_token');
			if(empty($meeting_token)){
				$meeting_token = M('meeting')->order('id desc')->getField('token');
			}

			//获取图片的base64编码字符串
			$img_base64 = imgToBase64( dirname(__FILE__) . $faceImage );

			//调用接口注册
			header("Content-Type:application/json");
			$url   = 'https://aip.baidubce.com/api/v1/solution/direct/meeting/apply?access_token='.access_token('qiandao');
			$bodys = array(
				"uUserId"   => $uUserId,
			    "name"      => $name,
			    "token"     => $meeting_token, 
			    "telephone" => $telephone,
			    "faceImage" => $img_base64
			);
			$bodys = json_encode($bodys);
			$res = request_post($url, $bodys);
			var_dump($res);
			exit;
			// {
			//     "log_id": 150087028516001,
			//     "result": {
			//         "applyId": 1,
			//         "qrcode":"MnsfaaDb",
			//         "qrcodeUrl":"http://xxxx.baidu.com"
			//     }
			// }

			//把得到的数据保存进数据库，同时然后给前端 
			if( !$res || isset($res['error_code']) ){
				//删除已有的数据
				M('user')->where(['id' => $uUserId])->delete();
				$this->error( $res['error_msg'] );
			}else{
				$res['result'] = json_decode($res['result'],true);

				if( M('user')->where(['id' => $uUserId])->update(['applyId' => $res['result']['applyId'] ,'qrcode' => $res['result']['qrcode'] , 'qrcodeUrl' => $res['result']['qrcodeUrl']]) !== false){

					$result = array('status' => 1, 'info' => '注册成功' , 
						'qrcode' => $res['result']['qrcode'] , 'qrcodeUrl' => $res['result']['qrcodeUrl'] , 'faceImage' => $faceImage
					);
					$this->ajaxReturn($result);
				}else{
					$this->error('注册失败');
				}
			}
		}else{
			$this->display();
		}
	}

	//接受上传的图片并保存
	public function upload(){
        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize   =     3145728 ;// 设置附件上传大小
        $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
        $upload->rootPath  =     './Uploads/'; // 设置附件上传根目录
        $upload->savePath  =     ''; // 设置附件上传（子）目录
        $upload->subName   =    array('date','Ymd');
        $upload->saveName  =    array('uniqid','');
        // 上传文件 
        $info   =   $upload->upload();
        // var_dump($info);exit;

        if(!$info) {// 上传错误提示错误信息
            $this->error($upload->getError());
        }else{ 
      	 	$info = current($info);
        	//返回地址
        	$result = array(
        		'status' => 1,
        		'info' => '上传成功',
        		'url' => '/Uploads/'.$info['savepath'].$info['savename']
        	);
            $this->ajaxReturn($result);
        }
    }
}

