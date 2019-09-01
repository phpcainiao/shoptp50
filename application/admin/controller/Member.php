<?php
namespace app\admin\controller;
use think\Db;
use think\Controller;

class Member extends Controller{

    public function __construct()
    {
        parent::__construct();
    }

    public function index(){
        $member = Db::table('sp_member')->paginate(10);
        $this->assign('member',$member);
        return view();
    }
    public function add(){
        return view();
    }
}