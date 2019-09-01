<?php
namespace app\api\controller;

use cls\wxBizDataCrypt;
use think\Request;
use think\Session;
use think\Db;

class Auth{
    public function wxLogin(){
        $params = Request::instance()->post();
        $appid = 'wx8e1059d36d261422';
        $secret = 'a93634994a473d9e249423cbb7cc5fb1';
        $code = $params['code'];
        $encryptedData = $params['encryptedData'];
        $iv = $params['iv'];
        $signature = $params['signature'];
        $skey = isset($params['skey']) ? $params['skey'] : '';
        if(empty($code) || empty($encryptedData) || empty($iv) || empty($signature)){
            echo '传递的信息不全！';
            die;
        }
        $output = $this->httpUrl($code,$appid,$secret);

        $sessionKey = $output['session_key'];
        //解密用户信息
        $pc = new wxBizDataCrypt($appid, $sessionKey);
        $errCode = $pc->decryptData($encryptedData, $iv, $data);

        if($errCode != 0){
            echo '解密数据失败';
            die;
        }else{
            $data = json_decode($data,true);
            $session3rd = $this->randomFromDev();
            $data['session3rd'] = $session3rd;
            $info = [$data['openId'],$sessionKey];
            //写入redis
            Session::set($session3rd,$info);
            $openid = $data['openId'];
            $nickname = $data['nickName'];
            $gender = $data['gender'];
            $country = $data['country'];
            $province = $data['province'];
            $city = $data['city'];
            $avatar = $data['avatarUrl'];

            $insert = [$openid,$session3rd,$nickname,$gender,$country,$province,$city,$avatar];
            if($skey){
                //非首次登录
                Db::execute("update sp_member set session3rd='$session3rd' where openid='$openid'");
            }else{
                Db::execute("INSERT INTO sp_member(-openid,session3rd,nickname,gender,country,province,city,avatarUrl) VALUES(?,?,?,?,?,?,?,?)",$insert);
            }

            return json_encode([$session3rd,session_id()]);
        }

    }

    protected function httpUrl($code,$appid,$secret){
        $url = 'https://api.weixin.qq.com/sns/jscode2session?appid='.$appid.'&secret='.$secret.'&js_code='.$code.'&grant_type=authorization_code';
        //初始化
        $ch = curl_init();
        //设置选项
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_HEADER,0);
        // 关闭SSL验证
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        // 3. 执行并获取HTML文档内容
        $output = curl_exec($ch);
        if($output === FALSE ){
            return "CURL Error:".curl_error($ch);
        }
        curl_close($ch);
        return json_decode($output,true);  //加true将json字符串转成json数组，否则转成json对象
    }

    /**
     * 7.生成第三方3rd_session，用于第三方服务器和小程序之间做登录态校验。为了保证安全性，3rd_session应该满足：
     * a.长度足够长。建议有2^128种组合，即长度为16B
     * b.避免使用srand（当前时间）然后rand()的方法，而是采用操作系统提供的真正随机数机制，比如Linux下面读取/dev/urandom设备
     * c.设置一定有效时间，对于过期的3rd_session视为不合法
     *
     * 以 $session3rd 为key，sessionKey+openId为value，写入memcached
     */
    protected function randomFromDev()
    {
        $session3rd = '';
        $string = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
        $max = strlen($string)-1;
        for($i=0;$i<16;$i++)
        {
            $session3rd .= $string[rand(0,$max)];
        }
        return $session3rd;
    }

    /**
     * 检查登录态是否过期
     */
    public function check()
    {
        $skey = Request::instance()->post('skey');
        $value = Session::get($skey);
        if($value){
            $res = ['code'=>1,'skey'=>$value];
        }else{
            $res = ['code'=>0];
        }
        return json_encode($res);
    }

    /**
     * 添加用户信息
     */
    public function addAddress()
    {
        $params = Request::instance()->post();
        $skey = $params['skey'];
        $memberid = Db::table('sp_member')->where('session3rd',$skey)->column('id');
        $memberid = $memberid[0];
        $receiver = $params['username'];
        $phone = $params['tel'];
        $area = $params['address'][0];
        $address = $params['detailAddress'];
        $isflag = $params['switch1'] == true ? 1:0;
        $data = ['member_id'=>$memberid,'receiver'=>$receiver,'phone'=>$phone,'area'=>$area,'address'=>$address,'isflag'=>$isflag];
        $res = Db::table('sp_member_address')->insert($data);
        if($res){
            $arr = ['code'=>1,'msg'=>'保存成功'];
        }else{
            $arr = ['code'=>0,'msg'=>'保存失败'];
        }
        return json_encode($arr);
    }

    /**
     * 地址列表
     */
    public function getAddressList(){
        $skey = Request::instance()->post('skey');
        $memberid = Db::table('sp_member')->where('session3rd',$skey)->column('id');
        $memberid = $memberid[0];
        $list = Db::table('sp_member_address')->where('member_id',$memberid)->select();
        return json_encode($list);
    }
}