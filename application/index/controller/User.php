<?php
namespace app\index\controller;

use \think\Controller;
use \think\Request;
use \think\Cache;

class User extends Controller
{
	/**
	 * [index description]
	 * @Author   Cungson
	 * @DateTime 2018-08-02
	 * @version  V1.0.0
	 * @return   [type]     [description]
	 */
	public function userlist()
	{
		$user = Db('user') -> select();

		return $this -> fetch();
	}

}