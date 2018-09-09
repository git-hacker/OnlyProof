<?php
namespace Index\Controller;

use Think\Controller;
class TestController extends Controller{

	public function ts($value='')
	{
		session('user_info' , false);
		exit;
	}
}