<?php

use \think\Cache;

use app\index\model\User;
use app\index\model\User_profile;
use app\index\model\Msg;
use app\index\model\Banner;
use app\index\model\Admin;
use app\index\model\Goods;

/**
 * 构造返回数据
 *
 * @param int $code 返回码
 * @param string $msg 返回信息
 * @param array $data 返回的数据
 * @return json $data
 */
function objReturn($code, $msg, $data = null)
{
    if (!is_int($code)) {
        return 'Invaild Code';
    }
    if (!is_string($msg)) {
        return 'Invaild Msg';
    }
    $res['code'] = $code;
    $res['msg'] = $msg;
    if ($data) {
        $res['data'] = $data;
    }
    return json($res);
}

/**
 * 更细数据库相关信息
 *
 * @param int $table 需要更新的表名
 * @param array $where 更新的字段
 * @param int $isUpdate 是更新还是新增
 * @return int $isSuccess 是否更新成功
 */
function saveData($table, $where, $isUpdate = true)
{
    if (!$table || !is_string($table)) {
        return 'Invaild Table';
    }
    if (!$where || !is_array($where)) {
        return 'Invaild Field';
    }
    if ($isUpdate && !is_bool($isUpdate)) {
        return 'Invaild State';
    }
    // 表名
    $tableName = null;
    switch ($table) {
        case 'profile':
            $tableName = new User_profile;
            break;
        case 'user':
            $tableName = new User;
            break;
        case 'msg':
            $tableName = new Msg;
            break;
        case 'banner':
            $tableName = new Banner;
            break;
        case 'admin':
            $tableName = new Admin;
            break;
    }
    // 判断数据长度
    $isSuccess = $tableName->isUpdate($isUpdate)->save($where);
    // 结果返回
    return $isSuccess;
}

/**
 * 通过商品Id获取商品详情
 *
 * @param int $goodsId 商品ID
 * @param boolean $isInUse 商品上架状态
 * @return obj 能 array 商品信息 否 null
 */
function getGoodsById($goodsId, $isInUse = true)
{
    if (empty($goodsId)) {
        return "Invaild Param";
    }
    $baseUrl = "https://store.up.maikoo.cn/static";
    $isInUse = $isInUse ? [1] : [0, 1];
    $goods = new Goods;
    $goodsInfo = $goods->alias('g')->join('sm_catagory c', 'g.cat_id = c.cat_id', 'LEFT')->where('g.goods_id', $goodsId)->where('g.is_on_sale', 'in', $isInUse)->where('is_delete', 0)->where('is_verify', 1)->field('g.goods_id, g.goods_sn, g.goods_img, g.goods_name, g.market_price, g.shop_price, g.member_price, g.stock, g.sales_num, g.is_new, g.is_hot, g.points, g.unit, c.cat_id, c.cname, g.created_at')->find();
    if (!$goodsInfo) {
        return null;
    }
    $goodsInfo = collection($goodsInfo)->toArray();
    // 部分变量调整
    $goodsInfo['goods_img'] = $baseUrl . $goodsInfo['goods_img'];
    $goodsInfo['goods_name'] = htmlspecialchars_decode($goodsInfo['goods_img']);
    $goodsInfo['cname'] = htmlspecialchars_decode($goodsInfo['cname']);
    $goodsInfo['market_price'] = number_format($goodsInfo['market_price'], 2);
    $goodsInfo['shop_price'] = number_format($goodsInfo['shop_price'], 2);
    $goodsInfo['member_price'] = number_format($goodsInfo['member_price'], 2);
    return $goodsInfo;
}
