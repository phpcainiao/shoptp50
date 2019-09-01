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
        $memberid = Db::table('sp_member')->where('session3rd',$skey)->column('id');
        $memberid = $memberid[0];
        $res = Db::table('sp_cart')->insert(['user_id'=>$memberid,'good_id'=>$goodId,'cartnum'=>$cartNum]);
        if($res){
            $arr = ['code'=>1,'msg'=>'加入成功~'];
        }else{
            $arr = ['code'=>0,'msg'=>'系统错误'];
        }
        echo json_encode($arr);
    }

    public function getConfirmOrder()
    {
        $params = Request::instance()->post();
        $skey = $params['skey'];
        $goodId = $params['goodsId'];
        $num = $params['num']; //商品数量
        $memberInfo = Db::table('sp_member')
            ->alias('m')
            ->join('sp_member_address a','a.member_id=m.id')
            ->where('m.session3rd',$skey)
            ->where('a.isflag',1)
            ->field('a.receiver,a.phone,a.area,a.address')
            ->select();
        $goodInfo = Db::table('sp_goods')->where('id',$goodId)->field('title,price,good_image')->select();mydebug($goodInfo,1);
        $goodInfo['num'] = $num;
        $newInfo = ['address'=>$memberInfo[0],'goodsInfo'=>$goodInfo];
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