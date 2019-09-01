<?php
namespace app\admin\controller;

use think\Controller;
use think\Lang;
use think\Request;
use think\Db;
use think\Session;

class Manager extends Controller{
    /**
     * @return \think\response\View
     */
    public function login()
    {
        $userinfo = Session::get('userinfo');
        if(!empty($userinfo)){
            $this->redirect(url('index/index'));
        }else{
            if(Request::instance()->isPost()){
                $username = Request::instance()->post('username');
                $pwd = Request::instance()->post('password');
                $pwd = md5($pwd);
                //有验证码先验证

                //判断是否有该用户，有则判断密码是否一致
                $info = Db::table('sp_manager')->where(['name'=>$username,'password'=>$pwd])->field('id,name')->find();
                if($info){
                    //用户名密码正确
                    Session::set('userinfo',$info);
                    Session::set('session_start_time',time());
                    //写入登录日志

                    $arr = ['code'=>1,'url'=>url('admin/index/index')];
                }else{
                    $arr = ['code'=>0,'msg'=>lang('NAME_OR_PWD_FAILED')];
                }
                echo json_encode($arr);
                die;
            }

            return view();
        }
    }

    public function logout()
    {
        Session::clear();
        $this->redirect(url('admin/manager/login'));
        return;
    }

}