<?php
return [
    // +----------------------------------------------------------------------
    // | 静态资源目录设置
    // +----------------------------------------------------------------------
    //后台css目录
    'admin_css'    =>  '/static/admin/css/',
    //后台js目录
    'admin_js'    =>  '/static/admin/js/',
    //后台image目录
    'admin_images'   =>  '/static/admin/images/',
    //layui目录
    'admin_layui'   =>  '/static/admin/lib/layui/',
    //fonts目录
    'admin_fonts'   =>  '/static/admin/fonts/',
   //图片上传目录
    'pic_uploads'   =>  '/static/uploads/',
   //ueditor目录
    'admin_ueditor'   =>  '/static/admin/lib/ueditor/',

    // +----------------------------------------------------------------------
    // | 模版布局配置
    // +----------------------------------------------------------------------
   'template'  =>  [
        'layout_on'     =>  true,
        'layout_name'   =>  'layout',
        'layout_item'   =>  '{__CONTENT__}'
   ],

   // +----------------------------------------------------------------------
   // | 会话设置
   // +----------------------------------------------------------------------

    'session'                => [
        'id'             => '',
       // SESSION_ID的提交变量,解决flash上传跨域
        'var_session_id' => '',
       // SESSION 前缀
        'prefix'         => 'think',
       // 驱动方式 支持redis memcache memcached
        'type'           => 'redis',
       // 是否自动开启 SESSION
        'auto_start'     => true,
        'host'           => '127.0.0.1',
        'port'          => 6379, // redis端口
        'password'      => '123456', // 密码
        'select'        => 1, // 操作库
        'expire'        => 3600,
    ],

   // +----------------------------------------------------------------------
   // | Cookie设置
   // +----------------------------------------------------------------------
    'cookie'                 => [
   // cookie 名称前缀
    'prefix'    => '',
   // cookie 保存时间
    'expire'    => 3600,
   // cookie 保存路径
    'path'      => '/',
   // cookie 有效域名
    'domain'    => '',
   //  cookie 启用安全传输
    'secure'    => false,
   // httponly设置
    'httponly'  => '',
   // 是否使用 setcookie
    'setcookie' => true,
],

];