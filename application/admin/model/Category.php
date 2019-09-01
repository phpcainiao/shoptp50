<?php
namespace app\admin\model;

use think\Model;

class Category extends Model
{
    /**
     * 查询所有分类
     * @return false|\PDOStatement|string|\think\Collection
     */
    public function index(){
        $list = Category::where(['data_flag'=>1,'is_show'=>1])->select();
        return $list;
    }
}