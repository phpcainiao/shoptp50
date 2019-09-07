<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/13 0013
 * Time: 13:57
 */

namespace app\admin\controller;

use think\Controller;
use think\Db;
use cls\Tree;
use think\Request;

class Goods extends Controller
{
    //前置操作，执行add,edit方法时先执行cateList方法
    protected $beforeActionList = [
        'cateList' => ['only'=>'add,edit']
    ];

    public function __construct()
    {
        parent::__construct();
    }

    public function index(){
        $list = Db::table('sp_goods')->order('createtime desc')->paginate(10);
        $this->assign('list',$list);
        return view();
    }
    public function add(){
        return view();
    }

    /**
     * 编辑
     * @return \think\response\View
     */
    public function edit()
    {
        $id = Request::instance()->param('id');
        $info = Db::table('sp_goods')->where('id',$id)->find();
        $info['good_image'] = trim(str_replace('\\','/',$info['good_image']));

        $this->assign('info',$info);
        return view();
    }

    /**
     * 删除
     * json
     */
    public function delete(){
        $id = Request::instance()->post('id');
        $result = Db::table('sp_goods')->delete($id);

        if($result){
            $arr = ['code'=>'1','info'=>lang('DELETE_SUCCESS')];
        }else{
            $arr = ['code'=>'0','info'=>lang('DELETE_FAILED')];
        }
        echo json_encode($arr);
    }

    public function upload(){
        $file = request()->file('file');
        $info = $file->move(ROOT_PATH.'public'.DS.'static'.DS.'uploads');
        if($info){
            $arr = ['code'=>1,'info'=>lang('UPLOAD_SUCCESS'),'src'=>str_replace('\\','/',$info->getSaveName())];
        }else{
            $arr = ['code'=>0,'info'=>lang('UPLOAD_FAILED')];
        }
        echo json_encode($arr);
    }

    /**
     * 提交表单
     * @return json
     */
    public function postSubmit(){
        $form = Request::instance()->post();
        $field = [
            'cat_id'        =>  $form['top_cate'],
            'good_sn'       =>  $form['good_sn'],
            'title'         =>  $form['title'],
            'price'         =>  $form['price'],
            'good_image'    =>  isset($form['good_image']) ? implode(',',$form['good_image']) : '',
            'images'        =>  isset($form['content']) ? $form['content'] : '',
            'createtime'    => time()
        ];
        $goods = new \app\admin\model\Goods();
        if(isset($form['id']) && $id = $form['id']){ //更新
            $result = $goods->save($field,['id'=>$id]);
        }else{ //插入
            $result = $goods->save($field);
        }
        if($result != false){
            $arr = ['code'=>'1','info'=>lang('SUBMIT_SUCCESS')];
        }else{
            $arr = ['code'=>'0','info'=>lang('SUBMIT_FAILED')];
        }
        echo json_encode($arr);
    }

    protected function cateList(){
        //一二级分类列表
        $list = Db::table('sp_category')
            ->where(['data_flag'=>1,'is_show'=>1])
            ->select();

        $tree = Tree::instance();
        $tree->init($list);
        $topList = $tree->getTree();

        $this->assign('topList',$topList);
    }
}