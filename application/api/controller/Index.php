<?php
namespace app\api\controller;

use think\Request;
use cls\Tree;
use think\Db;

class Index{
    public function getBanner()
    {
        $bannerList = Db::table('sp_banner')->field('id,image')->select();
        if($bannerList){
            $res = ['code'=>1,'bannerlist'=>$bannerList];
        }else{
            $res = ['code'=>0];
        }

        return json_encode($res);
    }

    public function getHotGoods()
    {
        $hotList = Db::table('sp_goods')->where(['is_sale'=>1,'is_hot'=>1])->field('id,good_image,title,price')->paginate(10);
        if($hotList){
            $res = ['code'=>1,'hotList'=>$hotList];
        }else{
            $res = ['code'=>0];
        }
        return json_encode($res);
    }

    public function getTopCategory()
    {
        $topCategoryList = Db::table('sp_category')->where(['is_show'=>1,'data_flag'=>1,'level'=>1])->select();
        return json_encode($topCategoryList);
    }

    public function getSubCategory()
    {
        $id = Request::instance()->post('id');
        $list = Db::table('sp_category')->where(['is_show'=>1,'data_flag'=>1])->select();

        $tree = Tree::instance();
        $tree->init($list);
        $childList = $tree->getTreeArr($id);
        return json_encode($childList);
    }

    public function getGoodsDetail()
    {
        $id = Request::instance()->get('id');
        $goodinfo = Db::table('sp_goods')->where('id',$id)->field('id,title,price,goods_stock,good_image,images')->find();
        $attrinfo = Db::table('sp_goods_attributes')->where('good_id',$id)->field('attrname,attrval')->select();
        $images = $goodinfo['images'];
        preg_match_all('/(\/\w{3,8}){5}\/\d{16}\.\w{3}/',$images,$matches);
        $goodinfo['images'] = $matches[0];
        $data = ['goodinfo'=>$goodinfo,'attrinfo'=>$attrinfo];
        mydebug($data,1);
        return json_encode($data);
    }

    public function getGoodsList()
    {
       $cat_id = Request::instance()->get('id');
       $index = Request::instance()->get('index');//0:综合排序 1：价格升高 2：价格降低
       $order = $index == 1 ? 'price asc' : ($index == 2 ? 'price desc' : 'sale_num desc');
       $goodsList = Db::table('sp_goods')->where('cat_id',$cat_id)->order($order)->field('id,title,price,good_image,sale_num')->paginate(10);
        return json_encode($goodsList);
    }

}