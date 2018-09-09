<?php
namespace Index\Controller;

use Think\Controller;
class IndexController extends Controller{

	//首页
	public function index()
	{
		$date = date('Y-m-d');
		$time = date('H:i');
		$this->assign('date',$date);
		$this->assign('time',$time);
		$my = getMyInfo();
		$url  = !empty($my) ? U('my') : U('register');

		$this->assign('url',$url);
		$this->display();

	}
	//注册页面
	public function register()
	{
		if(IS_POST){
			$data['name'] = I('post.name','','strip_tags'); if(!$data['name']) $this->error('请填写用户名');

			$data['telephone'] = I('post.telephone','','strip_tags'); 
			if( !$data['telephone'] || !preg_match("/^1[345678]{1}\d{9}$/",$data['telephone']) )  $this->error('请填写正确的手机号');
				
			$data['faceImage'] = I('post.faceImage','','strip_tags');  if(!$data['faceImage']) $this->error('请上传人脸照片');

			$data['code'] = I('post.code','','strip_tags');
			$uUserId = M('user')->add($data);
		 	if(!$uUserId) $this->error('制作失败');

		 	//测试
		 	$data['id'] = $uUserId;
		 	session("user_info" , json_encode($data));
	 		$this->success('制作成功',U('my'));
	 		exit;
		 	
			//百度签到平台的token
			$meeting_token = S('robot_meeting_token');
			if(empty($meeting_token)){
				$meeting_token = M('meeting')->order('id desc')->getField('token');
			}
			if(empty($meeting_token)) $this->error( 'meeting token is invalid' );
			
			//获取图片的base64编码字符串
			$img_base64 = imgToBase64( $faceImage );

			//调用接口注册
			header("Content-Type:application/json");
			$url   = 'https://aip.baidubce.com/api/v1/solution/direct/meeting/apply?access_token='.access_token('qiandao');
			$bodys = array(
				"uUserId"   => $uUserId,
			    "name"      => $data['name'],
			    "meeting_token"     => $meeting_token, 
			    "telephone" => $data['telephone'],
			    "faceImage" => $img_base64
			);
			$bodys = json_encode($bodys);
			$res = request_post($url, $bodys);
			$res = json_decode($res,true);
			unset($bodys,$img_base64);

			//把得到的数据保存进数据库，同时然后给前端 
			if( !$res || isset($res['error_code']) ){
				//删除已有的数据
				M('user')->where(['id' => $uUserId])->delete();
				$this->error( $res['error_msg'] );
			}else{
				$res['result'] = json_decode($res['result'],true);

				if( M('user')->where(['id' => $uUserId])->update(['meeting_token' => $meeting_token,'applyId' => $res['result']['applyId'] ,'qrcode' => $res['result']['qrcode'] , 'qrcodeUrl' => $res['result']['qrcodeUrl']]) !== false){

					//更新信息
					$data['id'] = $uUserId;
					$data['qrcode']  =  $res['result']['qrcode'];
					$data['qrcodeUrl'] =  $res['result']['qrcodeUrl'];
					session('user_info' , json_encode($data) );
					unset($data);

					$this->success('制作成功',U('my'));
				}else{
					$this->error('制作失败');
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

    //显示自己的名片页
    public function my()
    {
    	//获取我的信息
    	$my = getMyInfo();
    	if( isset($my['qrcodeUrl']) && $my['qrcodeUrl']){
    		$my['pic'] = $my['qrcodeUrl'];
    	}else if( isset($my['code']) && $my['code']){
    		$my['pic'] = $my['code'];
    	}
    	// var_dump($my);
    	// exit;
    	$this->assign('my', $my);
  		$this->display();
    }

    //隐私保护开启
    public function safe()
    {
    	$status  = I('post.status' , 0, 'intval');
    	$my = getMyInfo();
    	if( M('user') -> where(['id' => $my['id'] ]) -> save( ['safety_on' => $status]) !== false){
    		if($status == 1){
    			$data = array('name' => $my['name']);
    		}else{
    			$data = array('name' => $my['name'], 'telephone' => $my['telephone'],'qrcodeUrl' => $my['qrcodeUrl']);
    		}
    		$this->ajaxReturn(['status' => 1,'info' =>'开启成功', 'data' => $data]);
    	}else{
    		$this->error('开启失败');
    	}
    }

    //修改名片
    public function edit()
    {
    	if(IS_POST){
    		$my  = getMyInfo();

			$uid = I('post.uid','','strip_tags'); 
			$name = I('post.name','','strip_tags'); 
			if( $name ){
				$data['name'] = $name;
				$my['name'] = $name;
			}
			$telephone= I('post.telephone','','strip_tags'); 
			if( $telephone && !preg_match("/^1[345678]{1}\d{9}$/",$telephone) )  $this->error('请填写正确的手机号');

			if( $telephone ) {
				$data['telephone'] = $telephone;
				$my['telephone'] = $telephone;
			}
			if(!empty($data) && $uid){
				if( M('user')->where(['id' => $uid])->save($data) !== false ){
					// session('user_info' , json_encode($my));
					$this->success('成功',U('my'));
				}else{
					$this->error('失败');
				}
			}else{
				$this->error('参数错误');
			}
		}else{
			$my  = getMyInfo();
			$this->assign('my',$my);
			$this->display();
		}
    
    }
}

