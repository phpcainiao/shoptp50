<?php
namespace app\admin\common;

use think\Controller;
use think\Session;

class AdminCommon extends Controller{
    public function __construct()
    {
        parent::__construct();
        if(!session('userinfo')){
            $this->redirect(url('admin/manager/login'));
            return;
        }

        if(time()-session('session_start_time') > config('session')['expire']){
            Session::clear();
            $this->redirect(url('admin/manager/login'));
            return;
        }
    }
}