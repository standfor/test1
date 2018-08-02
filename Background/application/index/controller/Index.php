<?php
namespace app\index\controller;

use \think\Controller;
use \think\Request;
use \think\Cache;
use \think\Db;
use \think\Session;
use \think\File;
use app\index\model\User_profile;
use app\index\model\Admin;
use app\index\model\Power;

class Index extends Controller
{
    public function index()
    {
        return $this->fetch();	
    }











}
