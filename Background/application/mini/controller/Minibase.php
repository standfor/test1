<?php

namespace app\mini\controller;

use think\Controller;
use think\Request;
use think\Cache;
use think\Db;

class Minibase extends Controller
{

    /**
     * 获取用户信息
     * @param array userInfo
     * @param string openid
     * @return json 是否插入成功成功
     */
    public function setUserInfo(Request $request)
    {
        $uid = intval($request->param('uid'));
        if (!isset($uid)) {
            return objReturn(401, "Invaild Param");
        }
        // 有一个Openid 的缓存array，如果已经将该用户数据插入过，在缓存中就会体现
    	// 判断缓存库中是否有该openid
    	// 获取用户信息并入库
        $user_profile = new User_profile;

        $userProFileExist = $user_profile->where('uid', $uid)->count();
        if ($userProFileExist == 1) {
            return objReturn(0, 'User Already Auth');
        }

        $userInfo = $request->param('userInfo/a');
        $userInfo['created_at'] = time();
        $userInfo['uid'] = $uid;
        $userInfo['nickname'] = $userInfo['nickName'];
        $userInfo['avatar_url'] = $userInfo['avatarUrl'];
        unset($userInfo['nickName']);
        unset($userInfo['avatarUrl']);
        $insert = $user_profile->insert($userInfo);
        if (!$insert) {
            return objReturn(402, 'failed', $insert);
        }
        // 更新user表
        $user = new User;
        $user->update(['uid' => $uid, 'is_auth' => 1, 'auth_at' => time()]);
        // 更新用户信息到缓存
        $userAccountInfo = Cache::get('userAccountInfo');
        foreach ($userAccountInfo as $k => $v) {
            if ($v['uid'] == $uid) {
                $userAccountInfo[$k]['userInfo'] = $userInfo;
                $userAccountInfo[$k]['isAuth'] = true;
                break 1;
            }
        }
        Cache::set('userAccountInfo', $userAccountInfo, 0);
        return objReturn(0, 'success', $userAccountInfo);
    }

    /**
     * 获取用户openID
     * 
     * @param string code 登陆时wx.login返回的code
     * @return json 用户openid
     */
    public function getUserOpenid($code)
    {
        $url = "https://api.weixin.qq.com/sns/jscode2session?appid=" . config('APPID') . "&secret=" . config('APPSECRET') . "&js_code=" . $code . "&grant_type=authorization_code";
        $info = file_get_contents($url);
        $info = json_decode($info);
        $info = get_object_vars($info);
        return $info['openid'];
    }

    /**
     * 将用户登陆信息插入数据库中
     * 将用户点击小程序的信息插入到数据库中
     * 
     * @param Request $request
     * @return void
     */
    public function setUserLog(Request $request)
    {
        $logs = $request->param('logs/a');
        $miniLogs = $request->param('miniLogs/a');
        $columnLogs = $request->param('columnLogs/a');
        $catLogs = $request->param('catLogs/a');
        $articleLogs = $request->param('articleLogs/a');
        $openid = $request->param('openid');
        $logArr = array();
        arsort($logs);
        foreach ($logs as $k => $v) {
            $array['open_time'] = date('Y-m-d H:i:s', $v);
            $array['openid'] = $openid;
            $logArr[] = $array;
        }
        $usercount = new Usercount;
        $usercount->saveAll($logArr);
        // 如果有小程序点击的log就存入对应数据库
        if ($miniLogs && count($miniLogs) > 0) {
            foreach ($miniLogs as $k => $v) {
                $miniLogs[$k]['create_time'] = time();
                $miniLogs[$k]['openid'] = $openid;
            }
            $mini_click_count = new Mini_click_count;
            $mini_click_count->saveAll($miniLogs);
        }
        // 如果有专栏点击的log就存入对应的数据库
        if ($columnLogs && count($columnLogs) > 0) {
            foreach ($columnLogs as $k => $v) {
                $columnLogs[$k]['create_time'] = time();
                $columnLogs[$k]['openid'] = $openid;
            }
            $column_click_count = new Column_click_count;
            $column_click_count->saveAll($columnLogs);
        }
        // 如果有分类点击的log就存入对应的数据库
        if ($catLogs && count($catLogs) > 0) {
            foreach ($catLogs as $k => $v) {
                $catLogs[$k]['create_time'] = time();
                $catLogs[$k]['openid'] = $openid;
            }
            $cat_click_count = new Cat_click_count;
            $cat_click_count->saveAll($catLogs);
        }
        // 如果有文章点击的log就存入对应的数据库
        if ($articleLogs && count($articleLogs) > 0) {
            foreach ($articleLogs as $k => $v) {
                $articleLogs[$k]['create_time'] = time();
                $articleLogs[$k]['openid'] = $openid;
            }
            $article_click_count = new Article_click_count;
            $article_click_count->saveAll($articleLogs);
        }

    }

    /**
     * 获取用户信息
     *
     * @param Request $request
     * @return void
     */
    public function getUserAccount(Request $request)
    {
        $token = $request->param('token');
        $code = $request->param('code');

        if (!empty($token)) {
            // 用户信息缓存
            $userAccount = Cache::get('userAccount');
            if ($userAccount) {
                foreach ($userAccount as $k => $v) {
                    if ($v['token'] == $token) {
                        return objReturn(0, 'Get UserInfo Success', $v);
                    }
                }
            }
        } else {
            $openid = $this -> getUserOpenid($code);
            $token = $this -> genToken($openid);
        }

        return objReturn(400, 'No User Exist');
    }

    /**
     * 生成用户token
     *
     * @param string $openid 用户openid
     * @return string token
     */
    public function genToken($openid){
        return md5($openid);
    }

    /**
     * 获取当前系统的用户协议
     *
     * @return void
     */
    public function getCaluse()
    {
        $clause = new Clause;
        $clauseInfo = $clause->where('idx', 1)->field('clause')->select();
        $clauseInfo = collection($clauseInfo)->toArray();
        return objReturn(0, 'success', $clauseInfo[0]);
    }

    /**
     * 获取小程序首页详情
     *
     * @param Request $request
     * @return void
     */
    public function getShopInfo(Request $request)
    {
        $pageNum = intval($request->param('pageNum'));
        $uid = intval($request->param('uid'));

        $courseList = getAllCourse(null, false, $pageNum);
        $isHaveMore = true;

        if (!$courseList || count($courseList) == 0) {
            $isHaveMore = false;
            $courseList = [];
        } else {
            if (count($courseList) < 10) {
                $isHaveMore = false;
            }
            // $curTime = time();
            // $temp = [];
            // // 简单的课程筛选 筛选在展示中的课程
            // foreach ($courseList as $k => $v) {
            //     if ($v['show_start_at'] > $curTime && $v['show_end_at'] > $curTime) {
            //         $temp []= $v;
            //     }
            // }
            // $courseList = $temp;
        }



        $res['isHaveMore'] = $isHaveMore;
        $res['list'] = $courseList;

        $banner = new Banner;
        $bannerField = "img";
        $bannerList = getBanner($bannerField, false);
        if (!$bannerList || count($bannerList) == 0) {
            $temp['img'] = config('SITEROOT') . '/static/img/banner/default.png';
            $bannerList[] = $temp;
        }
        $res['banner'] = $bannerList;

        return objReturn(0, 'success', $res);
    }

}