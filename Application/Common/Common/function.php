<?php
function request_post($url = '', $param = '')
{
    if (empty($url) || empty($param)) {
        return false;
    }

    $postUrl = $url;
    $curlPost = $param;
    // 初始化curl
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $postUrl);
    curl_setopt($curl, CURLOPT_HEADER, 0);
    // 要求结果为字符串且输出到屏幕上
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    // post提交方式
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $curlPost);
    // 运行curl
    $data = curl_exec($curl);
    curl_close($curl);

    return $data;
}

function access_token( $type = 'qiandao')
{
    $key = 'robot_'.$type.'_access_token';
    $token = S( $key );
    if(empty($token)){
        $post_data['grant_type']    = 'client_credentials';
        $post_data['client_id']     = C('baidu.'.$type)['API_Key'];
        $post_data['client_secret'] = C('baidu.'.$type)['Secret_Key'];
        $o = "";
        foreach ( $post_data as $k => $v ) 
        {
            $o.= "$k=" . urlencode( $v ). "&" ;
        }
        $post_data = substr($o,0,-1);
        $response = request_post(C('baidu.token_url'), $post_data);
        $response = json_decode($response,true);
        if(!$response || isset($response['error']) ){
            echo  $res['error_description']."\n";
            exit(0);
        }

        if (!isset($response['access_token'])){
            echo "ERROR TO OBTAIN TOKEN\n";
            exit(1);
        }
        if (!isset($response['scope'])){
            echo "ERROR TO OBTAIN scopes\n";
            exit(2);
        }
        $token = $response['access_token'];
        S( $key , $token, 3600*24*30);
    }
    return $token;
}

/**
 * 获取图片的Base64编码(不支持url)
 * @param $img_file 传入本地图片地址
 *
 * @return string
 */
function imgToBase64($img_file) {
    // var_dump($img_file);

    $img_base64 = '';
    if (file_exists('.'.$img_file)) {
        $app_img_file = '.'.$img_file; // 图片路径
        $fp = fopen($app_img_file, "r"); // 图片是否可读权限
        if ($fp) {
            $filesize = filesize($app_img_file);
            $content = fread($fp, $filesize);
            $file_content = chunk_split(base64_encode($content)); // base64编码
            $img_base64 = $file_content; //本项目中不要头信息
        }
        fclose($fp);
    }
    return $img_base64; //返回图片的base64
}
        
//得到uid
function getUid()
{
    $my = session('user_info');
    $my = json_decode($my, true);
    if(empty($my) || !$my['id']) return 0;
    return $my['id'];
}

//得到用户信息
function getMyInfo()
{
   $uid = getUid();
   return M('user')->where(['id' => $uid])->find();
}