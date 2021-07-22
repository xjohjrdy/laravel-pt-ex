<?php

namespace App\Services\UpgradeVip;


class VegasVipService
{
    private $list_vip_info = [
        'vip' => [
            'appkey' => '25919216',
            'secretKey' => '9aac454e0b538ee6c0504d48446be023',
        ],
    ];

    protected $url = 'https://eco.taobao.com/router/rest';


    /*
     * 淘宝客 获取Access Token
     */
    public function getAccessToken($code, $classify = 'vip')
    {

        try {
            $c = new \TopClient();
            $single_info = $this->list_vip_info[$classify];
            $c->appkey = $single_info['appkey'];
            $c->secretKey = $single_info['secretKey'];
            $c->format = 'json';
            $c->gatewayUrl = $this->url;
            $req = new \TopAuthTokenCreateRequest();
            $req->setCode($code);// 授权code，grantType==authorization_code 时需要
            $resp = $c->execute($req);
            return $resp;
        } catch (\Exception $e) {
            return false;
        }
    }

    /*
     * 淘宝客 公用 私域用户备案
     */
    public function publisherInfoSave($inviter_code, $sessionKey, $classify = 'vip', $info_type = 1, $note = '', $relation_from = '', $offline_scene = '', $online_scene = '', $register_info = '{}')
    {
        try {
            $c = new \TopClient();
            $single_info = $this->list_vip_info[$classify];
            $c->appkey = $single_info['appkey'];
            $c->secretKey = $single_info['secretKey'];
            $c->format = 'json';
            $c->gatewayUrl = $this->url;
            $req = new \TbkScPublisherInfoSaveRequest();
            if (!empty($relation_from)) $req->setRelationFrom($relation_from);#可空 渠道备案 - 来源，取链接的来源
            if (!empty($offline_scene)) $req->setOfflineScene($offline_scene);#可空 渠道备案 - 线下场景信息，1 - 门店，2- 学校，3 - 工厂，4 - 其他
            if (!empty($online_scene)) $req->setOnlineScene($online_scene);#可空 渠道备案 - 线上场景信息，1 - 微信群，2- QQ群，3 - 其他
            $req->setInviterCode($inviter_code);#渠道备案 - 淘宝客邀请渠道的邀请码
            $req->setInfoType($info_type);#类型，必选 默认为1:
            if (!empty($note)) $req->setNote($note);#可空 媒体侧渠道备注
            if (!empty($register_info)) $req->setRegisterInfo($register_info);
            $resp = $c->execute($req, $sessionKey);
            return $resp;
        } catch (\Exception $e) {
            return false;
        }
    }

}
