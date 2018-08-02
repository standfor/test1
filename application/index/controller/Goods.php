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
use app\index\model\Catagory;

class Goods extends Controller
{
    public function goodslist()
    {
        return $this->fetch();	
    }

    /**
     * [catagory  商品分类页面]
     * @Author   Mr.fang
     * @DateTime 2018-08-02
     * @version  V1.0.0
     * @return   ary     返回页面数据
     */
    public function catagory(){
    	return $this->fetch();
    }

    /**
     * [catagoryZtree 获取分类数据]
     * @Author   Mr.fang
     * @DateTime 2018-08-02
     * @version  V1.0.0
     * @return   ary     [返回ztree数组]
     */
    public function catagoryZtree(){
    	$catagory = new Catagory;
    	$res = $catagory ->field('cat_id,parent_id,cname') ->where('is_active',1) ->where('is_delete',0) ->order('orderby desc') ->select();
    	if($res){
	    	// 构造返回数组
	    	// $catagoryArr = array();
	    	foreach ($res as $key => $value) {
	    		$temp['id'] = $value['cat_id']; 
	    		$temp['pId'] = $value['parent_id']; 
	    		$temp['name'] = $value['cname']; 
	    		$temp['open'] = 'true'; 
	    		$temp['checked'] = 'false';
	    		$catagoryArr[] = $temp;
	    	}
        	return objReturn(0,'数据获取成功！',$catagoryArr);
    	}
        return objReturn(400,'数据获取失败！');
    }







}
