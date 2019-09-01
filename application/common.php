<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
/**
 * 记录测试数据(不区分大小写)
 * @param string|array $var 要输入的字符串或者数组
 * @param bool $append 是否追加
 * @param string $file 写入的文件名称
 * @author Sorry <1150699887@qq.com>
 */

function myputs($var, $append = true, $file = 'debug.log') {
    //$file = RUNTIME_PATH . '/' . $file;
    $file = str_replace('\\','/',RUNTIME_PATH.'log' . '/' . $file);
    if ($append) {
        file_put_contents($file, var_export($var, true) . "\n\n", FILE_APPEND);
    } else {
        file_put_contents($file, var_export($var, true) . "\n\n");
    }
    \chmod($file, 0777);
}
/**
 * 记录输出结果(不区分大小写)
 * @author Sorry <1150699887@qq.com>
 */
function mydebug($var, $append = false, $file = 'debug.log') {
    myputs($var, $append, $file);
}

