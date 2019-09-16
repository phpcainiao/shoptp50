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
        $nickname = $params['nickname'];
        $gender = $params['gender'];
        $city = $params['city'];
        $province = $params['province'];
        $country = $params['country'];
        $avatar = $params['avatarUrl'];

        $output = $this->httpUrl($code,$appid,$secret);
        $sessionKey = $output['session_key'];
        $openid = $output['openid'];

        $session3rd = $this->randomFromDev();
        $info = [$openid,$sessionKey];
        //写入redis
        Session::set($session3rd,$info);

        $insert = [$openid,$session3rd,$nickname,$gender,$country,$province,$city,$avatar];
        $res = Db::execute("update sp_member set session3rd='$session3rd' where openid='$openid'");
        if(!$res){
            //首次登录
            Db::execute("INSERT INTO sp_member(-openid,session3rd,nickname,gender,country,province,city,avatarUrl) VALUES(?,?,?,?,?,?,?,?)",$insert);
        }

        return json_encode([$session3rd,session_id()]);
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
     * 删除登录态
     * @return string
     */
    public function destroy(){
        $skey = Request::instance()->post('skey');
        Session::delete($skey);
        return json_encode(['code'=>1]);
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
        //检查当前用户是否已经设置过默认地址
        if($isflag == true){
            $info = Db::table('sp_member_address')->where(['member_id'=>$memberid,'isflag'=>1])->find();
            if($info){
                //如果当前用户之前设置过默认地址，则新设置的默认地址会替代旧的默认地址，旧的默认地址变为普通地址
                Db::startTrans();//开启事物
                try {
                    Db::table('sp_member_address')->where('member_id',$memberid)->update(['isflag'=>0]);
                    Db::table('sp_member_address')->insert($data);
                    Db::commit();//提交事物
                    $res = 1;
                } catch (\Exception $e) {
                    $res = 0;
                    Db::rollback();//回滚
                }
            }else{
                $res = Db::table('sp_member_address')->insert($data);
            }

        }
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
    public function getDefaultAddress(){
        $skey = Request::instance()->post('skey');
        $memberInfo = Db::table('sp_member')
            ->alias('m')
            ->join('sp_member_address a','a.member_id=m.id')
            ->where('m.session3rd',$skey)
            ->where('a.isflag',1)
            ->field('a.receiver,a.phone,a.area,a.address')
            ->select();
        if(empty($memberInfo)){
            $arr = ['code'=>0];
        }else{
            $arr = ['code'=>1,'info'=>$memberInfo[0]];
        }
        return json_encode($arr);
    }
    /**
     * 添加微信地址
     */
    public function addWxAddress()
    {
        $skey = Request::instance()->post('skey');
        $cityname = Request::instance()->post('cityname');
        $countyname = Request::instance()->post('countyname');
        $detailinfo = Request::instance()->post('detailinfo');
        $provincename = Request::instance()->post('provincename');
        $telnumber = Request::instance()->post('telnumber');
        $username = Request::instance()->post('username');
        $info = Db::table('sp_member')->where('session3rd',$skey)->field('id')->find();
        $memberId = $info['id'];
        $area = $provincename.$countyname.$cityname.$detailinfo;
        $addressInfo = ['member_id'=>$memberId,'receiver'=>$username,'phone'=>$telnumber,'area'=>$area,'isflag'=>1];
        $res = Db::table('sp_member_address')->insert($addressInfo);
        if($res){
            $arr = ['code'=>1,'address'=>$addressInfo];
        }else{
            $arr = ['code'=>0];
        }
        return json_encode($arr);

    }
}