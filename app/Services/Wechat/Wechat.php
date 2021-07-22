<?php

namespace App\Services\Wechat;


use App\Entitys\App\AppUserInfo;
use App\Services\Crypt\RsaUtils;
use App\Services\JPush\JPush;
use GuzzleHttp\Client;

class Wechat
{
    protected $app_id = 'wxd2d9077a3072b5db';
    protected $app_secret = '4046e6b6d9f262902c2a10caef4c6f9f';

    protected $request_pubKey = '-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDKQaCXSby62RDZl3KpSoFSo+ic
tQS8oC1g1aTushv9HmgNUxRZdRfXAdicZGgGMPjeJepaGLGUYSmUwFKLNcbirpTn
oRrxtt/pFoZrBkEROAXUWqmxRns+j16Uu+5HFuDCeDBsAJiBANM3nQAOZWsDuOII
g1BYPIOOtXSiK6AOQQIDAQAB
-----END PUBLIC KEY-----';

    /**
     * 创建AccessToken
     * 需要给第一步的code
     *
     *
     * https://api.weixin.qq.com/sns/oauth2/access_token?appid=APPID&secret=SECRET&code=CODE&grant_type=authorization_code
     * @param Client $client
     * @return mixed
     */
    public function getOne($code, $client)
    {
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=" . $this->app_id . "&secret=" . $this->app_secret . "&code=" . $code . "&grant_type=authorization_code";
        $res = $client->request('get', $url, ['verify' => false]);
        $data = json_decode((string)$res->getBody(), true);
        return $data;
    }

    /**
     * i need get user name and ico_img
     */
    public function getUserNameIcoImg($access_token, $open_id, $client)
    {
        $url = "https://api.weixin.qq.com/sns/userinfo?openid=" . $open_id . "&access_token=" . $access_token;
        $res = $client->request('get', $url, ['verify' => false]);
        $data = json_decode((string)$res->getBody(), true);
        return $data;
    }

    public function loginApp($phone, $password, $is_wechat_login_wuhang, $client, $rsaUtils, $device_id)
    {

        $appUserInfo = new  AppUserInfo();
        $app_user = $appUserInfo->getUserByPhone($phone);
        //10000000
        $new_app_id = $app_user->id;
        if ($app_user->id >= 10000000) {
            $new_app_id = base_convert($app_user->id, 10, 33); // 10 转 33
            $new_app_id = 'x' . $new_app_id;
        }

        $data = [
            'id' => $app_user->id,
            'show_id' => $new_app_id,
            'user_name' => $app_user->user_name,
            'real_name' => $app_user->real_name,
            'avatar' => $app_user->avatar,
            'phone' => $app_user->phone,
            'alipay' => $app_user->alipay,
            'parent_id' => $app_user->parent_id,
        ];

        $appUserInfo->updateUserLogin($app_user->id, $device_id);

        return $data;
    }

    /**
     * 传入一个手机号，注册用户
     * @param $phone
     * @param $client
     * @param string $parent_id
     * @return bool|mixed
     */
    public function doRegisterBK($phone, $client, $parent_id = '')
    {
        $analog_sms_code = (string)mt_rand(100000, 999999);
        $analog_password = time() . $analog_sms_code;
        $data = [
            'phone' => $phone,
            'sms_code' => $analog_sms_code,
            'password' => $analog_password,
            'confirm_password' => $analog_password,
            'parent_id' => $parent_id
        ];
        $post_data = $data;
        sort($data);
        $token = md5('pt' . serialize($data) . 'llq');
        $groupData = [
            'headers' => ['token' => $token],
            'form_params' => $post_data
        ];
        $res = $client->request('POST', 'http://xin.36qq.com/Mobile/auth/doregister', $groupData);

        $jsonRes = (string)$res->getBody();
        $arrRes = json_decode($jsonRes, true);

        if (is_array($arrRes)) {
            @file_put_contents("../storage/logs/account/" . md5('pt') . "_log.txt", $phone . '-' . $analog_password . '-' . $jsonRes . PHP_EOL, FILE_APPEND);
            return $arrRes;
        }

        return false;

    }

    /*
     * H5签名验证获取的tokrn
     */
    public function getAccessToken($client)
    {
        $app_id = 'wx105af0df08828840';
        $app_secret = 'ec73c13435f7e23bcaaea2422f03e81f';
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $app_id . "&secret=" . $app_secret;
        $res = $client->request('get', $url, ['verify' => false]);
        $data = json_decode((string)$res->getBody(), true);
        return $data;
    }

    /*
     * H5签名验证用 access_token 得到 jsapi_ticket
     */
    public function getJsapiTicket($access_token, $client)
    {
        $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=" . $access_token . "&type=jsapi";
        $res = $client->request('get', $url, ['verify' => false]);
        $data = json_decode((string)$res->getBody(), true);
        return $data;
    }

    /*
     *
     */
    public function doRegister($phone, $client = '', $parent_id = '')
    {

        try {//该手机号已被注册
            if (AppUserInfo::where(['phone' => $phone])->exists()) {
                return false;
            }
            $analog_sms_code = (string)mt_rand(100000, 999999);
            $analog_password = time() . $analog_sms_code;
            $register_params = [
                "user_name" => $phone,
                "real_name" => "",
                "avatar" => "",
                "phone" => $phone,
                "password" => bcrypt($analog_password),
                "alipay" => "",
                "level" => 1,
                "parent_id" => empty($parent_id) ? 0 : $parent_id,
                "up_three_floor" => "",
                "up_four_floor" => "",
                "status" => 1,
                "create_time" => time(),
                "active_value" => 0,
                "append_active_value" => 0,
                "sign_active_value" => 0,
                "order_num_active_value" => 0,
                "history_active_value" => 0,
                "bonus_amount" => 0,
                "order_amount" => 0,
                "apply_cash_amount" => 0,
                "next_month_cash_amount" => 0,
                "current_month_passed_order" => 0,
                "order_can_apply_amount" => 0,
                "sign_number" => 0,
                "level_modify_time" => time(),
                "apply_status" => 2,
            ];//查询是否有上级用户
            if (!empty($parent_id)) {
                //['id' => $user_id, 'status' => 1]
                //查询上级用户是否在
                $parent_id = AppUserInfo::where(['id' => $parent_id, 'status' => 1])->value('id');
                if (empty($parent_id)) {
                    //上级ID错误
                    return false;
                }

                //如果有则 order_can_apply_amount 添加100报销奖励

                $register_params['order_can_apply_amount'] = 100;
            }

            //创建一个lc_user的账户
            AppUserInfo::insert($register_params);
            if (!empty($parent_id)) {
                JPush::push_user('您新增了一个粉丝！', $parent_id, 1, 1, 3);
            }
            return true;
        } catch (\Exception $e) {
            return false;
        }

    }
}
