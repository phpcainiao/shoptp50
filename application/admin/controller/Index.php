<?php
namespace app\admin\controller;

use app\admin\common\AdminCommon;

class Index extends AdminCommon
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        return view();
    }
    public function welcome()
    {
        return view();
    }
}
