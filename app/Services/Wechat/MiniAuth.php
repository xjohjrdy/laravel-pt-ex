<?php


namespace App\Services\Wechat;


use App\Entitys\App\MiniWechatInfo;
use GuzzleHttp\Client;

class MiniAuth
{
    //我的优选
//    private $app_id = 'wx8e681805fc9c3e02';
//    private $app_secret = '2b4af62085ce03a6ea7f3029a50dd0b8';

    private $app_id = 'wx34989a331407111a';
    private $app_secret = 'c6a7c0feb7113d3112481490bfe7cbac';

    private $auth_url = 'https://api.weixin.qq.com/sns/jscode2session';
    public function __construct()
    {

    }

    public function authCode2Session($code = '')
    {
        $client = new Client();
        $get_prams = [
            'appid' => $this->app_id,
            'secret' => $this->app_secret,
            'grant_type' => 'authorization_code',
            'js_code' => $code
        ];
        $str = '';
        foreach ($get_prams as $key => $item){
            $str = $str . '&' . $key . '=' .$item;
        }
        $url = $this->auth_url . '?' . $str;
        //合并所需全部参数
        $header_data = [
            'verify' => false
        ];
        $group_data = [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
        ];
        $res = $client->request('GET', $url, $header_data);

        $json_res = (string)$res->getBody();
        $arr_res = json_decode($json_res, true);
        return $arr_res;
    }

    /**
     * 关联小程序openId和客户端appId
     * @param $openId
     * @param $appId
     */
    public function relateOpenIdAndAppId($openId, $appId){

    }

    /**
     * 根据openId检查用户是否已经关联appId
     * @param string $openId
     * @param Array $model
     * @return MiniWechatInfo[]|\Illuminate\Database\Eloquent\Collection
     */
    public function checkAppIdAuth($model = []){
        $miniInfoModel = new MiniWechatInfo();
        $exit = $miniInfoModel->where(['openid' => $model['openid']])->exists();
        if(!$exit){
            $miniInfoModel->create($model);
        } else {
            $info = $miniInfoModel->where(['openid' => $model['openid']])->first();
            $model['app_id'] = $info['app_id'];
        }
        return $model;
    }


}