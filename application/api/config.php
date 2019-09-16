<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/9/11 0011
 * Time: 12:23
 */
return [
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
        'expire'        => 3600*24*365,
    ],
    'cookie'                 => [
        // cookie 名称前缀
        'prefix'    => '',
        // cookie 保存时间
        'expire'    => 3600*24*365,  //一年
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