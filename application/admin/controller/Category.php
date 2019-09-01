<?php
namespace app\admin\controller;

use think\Controller;
use think\Db;
use cls\Tree;
use think\Request;

class Category extends Controller
{
    //前置操作，执行add,edit方法时先执行cateList方法
    protected $beforeActionList = [
       'cateList' => ['only'=>'add,edit']
    ];

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return \think\response\View
     */
    public function index()
    {
        $list = Db::table('sp_category')->where(['data_flag'=>1,'is_show'=>1])->select();

        $tree = Tree::instance();
        $tree->init($list);
        $cateList = $tree->getTree();

        $this->assign('cateList',$cateList);
        return view();
    }

    /**
     * 添加
     * @return \think\response\View
     */
    public function add()
    {
        $id = Request::instance()->param('id');
        $info = Db::table('sp_category')->where('id',$id)->field('id,level')->find();

        $this->assign('info',$info);
        return view();
    }

    /**
     * 编辑
     * @return \think\response\View
     */
    public function edit()
    {
        $id = Request::instance()->param('id');
        $info = Db::table('sp_category')->where('id',$id)->find();

        $this->assign('info',$info);
        return view();
    }

    /**
     * 删除
     * json
     */
    public function delete(){
        $id = Request::instance()->post('id');
        $result = model('category')->save(['data_flag'=>'-1'],['id'=>$id]);

        if($result != false){
            $arr = ['code'=>'1','info'=>lang('DELETE_SUCCESS')];
        }else{
            $arr = ['code'=>'0','info'=>lang('DELETE_FAILED')];
        }
        echo json_encode($arr);
    }

    /**
     * ajax
     *根据选择分类得出当前添加的是第几层分类
     * @return json
     */
    public function postLevel(){
        $id = Request::instance()->post('id');
        if(!empty($id) && is_numeric($id)){
            $level = Db::table('sp_category')->where('id',$id)->column('level');
            $arr = ['code'=>1,'level'=>$level[0]];
        }else{//失败或者id等于0
            $arr = ['code'=>0,'level'=>''];
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
            'pid'   =>  $form['top_cate'],
            'name'  =>  $form['name'],
            'level' =>  $form['level'],
            'image' =>  $form['image'],
            'createtime'    =>  time()
        ];

        if(isset($form['id']) && $id = $form['id']){ //更新
            $result = model('category')->save($field,['id'=>$id]);
        }else{ //插入
            $result = model('category')->save($field);
        }
        if($result != false){
            $arr = ['code'=>'1','info'=>lang('SUBMIT_SUCCESS')];
        }else{
            $arr = ['code'=>'0','info'=>lang('SUBMIT_FAILED')];
        }
        echo json_encode($arr);
    }

    /**
     * 图片上传
     */
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

    protected function cateList(){
        //一二级分类列表
        $list = Db::table('sp_category')
            ->where(['data_flag'=>1,'is_show'=>1])
            ->where('level','in',[1,2])
            ->select();

        $tree = Tree::instance();
        $tree->init($list);
        $topList = $tree->getTree();

        $this->assign('topList',$topList);
    }
}

