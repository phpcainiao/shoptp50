<?php
namespace app\api\controller;
use think\Request;
use think\Db;

class Order{
    /**
     * 加入购物车
     */
    public function addCart()
    {
        $params = Request::instance()->post();
        $skey = $params['skey'];
        $goodId = $params['goodsId'];
        $cartNum = $params['num'];
        $cateval = $params['cateval'];
        $memberid = Db::table('sp_member')->where('session3rd',$skey)->column('id');
        $memberid = $memberid[0];
        $res = Db::table('sp_cart')->insert(['user_id'=>$memberid,'good_id'=>$goodId,'cartnum'=>$cartNum,'cateval'=>$cateval]);
        if($res){
            $arr = ['code'=>1,'msg'=>'加入成功~'];
        }else{
            $arr = ['code'=>0,'msg'=>'系统错误'];
        }
        echo json_encode($arr);
    }

    public function getConfirmOrder()
    {
        $goodId = Request::instance()->post('goodsId');
        $num = Request::instance()->post('num');//商品数量
        $goodInfo = Db::table('sp_goods')->where('id',$goodId)->field('title,price,image')->select();
        $goodInfo[0]['num'] = $num;
        $newInfo = ['goodsInfo'=>$goodInfo];
        return json_encode($newInfo);
    }

    public function getCartList()
    {
        $skey = Request::instance()->post();
        $memberid = Db::table('sp_member')->where('session3rd',$skey)->column('id');
        $memberid = $memberid[0];

        $list = Db::table('sp_cart')
                ->alias('c')
                ->join('sp_goods g','c.good_id = g.id')
                ->where('c.user_id',$memberid)
                ->field('c.cartnum,g.title,g.price');
    }
}