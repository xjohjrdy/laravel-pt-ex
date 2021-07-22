<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/8/19
 * Time: 09:40
 */

namespace App\Services\TbkCashCreate;


class TbkCashCreateServices
{
    protected $app_key = '25626319';
    protected $app_secret = '05668c4eefc404c0cd175fb300b2723d';
    protected $url = 'https://eco.taobao.com/router/rest';

    private $new_can_change_auth_info = [
        //woxiaoli
        '109375250125' => [
            'app_secret' => '05668c4eefc404c0cd175fb300b2723d',
            'app_key' => '25626319',
            'pid' => 'mm_122930784_46170255_109375250125',
        ],
        //卢树青13799401629
        '109469450037' => [
            'app_secret' => 'db9604b2acf693b95c7da990ad07b4f7',
            'app_key' => '25842871',
            'pid' => 'mm_123640184_378350331_109469450037',
        ],
        //xxh 徐丽华15959875125
        '109467850460' => [
            'app_secret' => 'b12d3463ad8c0609c648202aad946ddb',
            'app_key' => '25620531',
            'pid' => 'mm_123348922_46184097_109467850460',
        ],
        //黄雪慧
        '109467900491' => [
            'app_secret' => '0676acbd3d38d1ceac4b476a25556eef',
            'app_key' => '25821858',
            'pid' => 'mm_105946111_379150017_109467900491',
        ],
        //林剑锋
        '109551050001' => [
            'app_secret' => '7387f5ae77d61f8c6d2261f386d0c6d0',
            'app_key' => '25811684',
            'pid' => 'mm_123184147_379150041_109551050001',
        ],
    ];

    /*
     * 淘宝客 推广者淘礼金创建
     */
    public function cashCreate($adzone_id, $item_id, $total_num, $name, $user_total_win_num_limit, $security_switch, $per_face, $send_start_time, $campaign_type = '', $send_end_time = '', $use_end_time = '', $use_end_time_mode = 1, $use_start_time = '')
    {
        $app_key = '25811684';
        $app_secret = '7387f5ae77d61f8c6d2261f386d0c6d0';
        $c = new \TopClient();
        $c->appkey = $app_key;
        $c->secretKey = $app_secret;
        $c->format = 'json';
        $c->gatewayUrl = $this->url;
        $req = new \TbkDgVegasTljCreateRequest();
        if (!empty($campaign_type)) $req->setCampaignType($campaign_type);  #可空 CPS佣金计划类型 如：定向：DX；鹊桥：LINK_EVENT；营销：MKT
        $req->setAdzoneId($adzone_id);                                      #妈妈广告位Id
        $req->setItemId($item_id);                                          #宝贝id
        $req->setTotalNum($total_num);                                      #淘礼金总个数
        $req->setName($name);                                               #淘礼金名称，最大10个字符
        $req->setUserTotalWinNumLimit($user_total_win_num_limit);           #单用户累计中奖次数上限
        $req->setSecuritySwitch($security_switch);                          #安全开关 启动安全：true；不启用安全：false
        $req->setPerFace($per_face);                                        #单个淘礼金面额，支持两位小数，单位元
        $req->setSendStartTime($send_start_time);                           #发放开始时间
        if (!empty($send_end_time)) $req->setSendEndTime($send_end_time);   #可空 发放截止时间
        //可空 使用结束日期。如果是结束时间模式为相对时间，时间格式为1-7直接的整数, 例如，1（相对领取时间1天）
        //如果是绝对时间，格式为yyyy-MM-dd，例如，2019-01-29，表示到2019-01-29 23:59:59结
        if (!empty($use_end_time)) $req->setUseEndTime($use_end_time);
        if (!empty($use_end_time_mode)) $req->setUseEndTimeMode($use_end_time_mode);  #可空 结束日期的模式,1:相对时间，2:绝对时间
        //可空 使用开始日期。相对时间，无需填写，以用户领取时间作为使用开始时间。
        //绝对时间，格式 yyyy-MM-dd，例如，2019-01-29，表示从2019-01-29 00:00:00 开始
        if (!empty($use_start_time)) $req->setUseStartTime($use_start_time);
        $resp = $c->execute($req);
        return $resp;
    }

    /*
     * 淘宝客 获取Access Token
     */
    public function getAccessToken($code, $classify = 'wxl')
    {
        $c = new \TopClient();
        switch ($classify) {
            case 'xlh':
                $c->appkey = '25620531';
                $c->secretKey = 'b12d3463ad8c0609c648202aad946ddb';
                break;
            case 'hxh':
                $c->appkey = '25821858';
                $c->secretKey = '0676acbd3d38d1ceac4b476a25556eef';
                break;
            case 'lsq':
                $c->appkey = '25842871';
                $c->secretKey = 'db9604b2acf693b95c7da990ad07b4f7';
                break;
            case 'pay0': # 零元购
                $c->appkey = '25811684';
                $c->secretKey = '7387f5ae77d61f8c6d2261f386d0c6d0';
                break;
            case 'agql':
                $c->appkey = '25811684';
                $c->secretKey = '7387f5ae77d61f8c6d2261f386d0c6d0';
                break;
            default :
                $c->appkey = $this->app_key;
                $c->secretKey = $this->app_secret;
                break;
        }
        $c->format = 'json';
        $c->gatewayUrl = $this->url;
        $req = new \TopAuthTokenCreateRequest();
        $req->setCode($code);                       #授权code，grantType==authorization_code 时需要
        $resp = $c->execute($req);
        return $resp;
    }

    /*
     * 淘宝客 刷新Access Token
     */
    public function getTokenRefresh($refresh_token, $classify = 'wxl')
    {
        $c = new \TopClient();
        switch ($classify) {
            case 'xlh':
                $c->appkey = '25620531';
                $c->secretKey = 'b12d3463ad8c0609c648202aad946ddb';
                break;
            case 'hxh':
                $c->appkey = '25821858';
                $c->secretKey = '0676acbd3d38d1ceac4b476a25556eef';
                break;
            case 'lsq':
                $c->appkey = '25842871';
                $c->secretKey = 'db9604b2acf693b95c7da990ad07b4f7';
                break;
            case 'pay0':
                $c->appkey = '25811684';
                $c->secretKey = '7387f5ae77d61f8c6d2261f386d0c6d0';
                break;
            case 'agql':
                $c->appkey = '25811684';
                $c->secretKey = '7387f5ae77d61f8c6d2261f386d0c6d0';
                break;
            default :
                $c->appkey = $this->app_key;
                $c->secretKey = $this->app_secret;
                break;
        }
        $c->format = 'json';
        $c->gatewayUrl = $this->url;
        $req = new \TopAuthTokenRefreshRequest();
        $req->setRefreshToken($refresh_token);      #grantType==refresh_token 时需要
        $resp = $c->execute($req);
        return $resp;
    }

    /*
     * 淘宝客 公用 私域用户备案
     */
    public function publisherInfoSave($inviter_code, $sessionKey, $classify = 'wxl', $info_type = 1, $note = '', $relation_from = '', $offline_scene = '', $online_scene = '', $register_info = '{}')
    {
        $c = new \TopClient();
        switch ($classify) {
            case 'xlh':
                $c->appkey = '25620531';
                $c->secretKey = 'b12d3463ad8c0609c648202aad946ddb';
                break;
            case 'hxh':
                $c->appkey = '25821858';
                $c->secretKey = '0676acbd3d38d1ceac4b476a25556eef';
                break;
            case 'lsq':
                $c->appkey = '25842871';
                $c->secretKey = 'db9604b2acf693b95c7da990ad07b4f7';
                break;
            case 'pay0':
                $c->appkey = '25811684';
                $c->secretKey = '7387f5ae77d61f8c6d2261f386d0c6d0';
                break;
            case 'agql':
                $c->appkey = '25811684';
                $c->secretKey = '7387f5ae77d61f8c6d2261f386d0c6d0';
                break;
            default :
                $c->appkey = $this->app_key;
                $c->secretKey = $this->app_secret;
                break;
        }
        $c->format = 'json';
        $c->gatewayUrl = $this->url;
        $req = new \TbkScPublisherInfoSaveRequest();
        if (!empty($relation_from)) $req->setRelationFrom($relation_from);  #可空 渠道备案 - 来源，取链接的来源
        if (!empty($offline_scene)) $req->setOfflineScene($offline_scene);  #可空 渠道备案 - 线下场景信息，1 - 门店，2- 学校，3 - 工厂，4 - 其他
        if (!empty($online_scene)) $req->setOnlineScene($online_scene);     #可空 渠道备案 - 线上场景信息，1 - 微信群，2- QQ群，3 - 其他
        $req->setInviterCode($inviter_code);                                #渠道备案 - 淘宝客邀请渠道的邀请码
        $req->setInfoType($info_type);                                      #类型，必选 默认为1:
        if (!empty($note)) $req->setNote($note);                            #可空 媒体侧渠道备注
        //可空 线下备案注册信息,字段包含:
        //电话号码(phoneNumber，必填),
        //省(province,必填),市(city,必填),
        //区县街道(location,必填),详细地址(detailAddress,必填),
        //经营类型(career,线下个人必填),
        //店铺类型(shopType,线下店铺必填),
        //店铺名称(shopName,线下店铺必填),
        //店铺证书类型(shopCertifyType,线下店铺选填),
        //店铺证书编号(certifyNumber,线下店铺选填)
        //示例：
        //'{
        //"phoneNumber":"18801088599",
        //"city":"江苏省",
        //"province":"南京市",
        //"location":"玄武区花园小区",
        //"detailAddress":"5号楼3单元101室",
        //"shopType":"社区店",
        //"shopName":"全家便利店",
        //"shopCertifyType":"营业执照",
        //"certifyNumber":"111100299001"
        //}'
        if (!empty($register_info)) $req->setRegisterInfo($register_info);
        $resp = $c->execute($req, $sessionKey);
        return $resp;
    }

    /*
     * 淘宝客 公用 私域用户邀请码生成
     */
    public function getInvitecode($relation_app, $code_type, $sessionKey, $relation_id = '')
    {
        $c = new \TopClient();
        $c->appkey = $this->app_key;
        $c->secretKey = $this->app_secret;
        $c->format = 'json';
        $c->gatewayUrl = $this->url;
        $req = new \TbkScInvitecodeGetRequest();
        if (!empty($relation_id)) $req->setRelationId($relation_id);  #可空 渠道关系ID
        $req->setRelationApp($relation_app);                          #渠道推广的物料类型
        $req->setCodeType($code_type);                                #邀请码类型，1 - 渠道邀请，2 - 渠道裂变，3 -会员邀请
        $resp = $c->execute($req, $sessionKey);
        return $resp;
    }

    /*
     * 淘宝客 公用 淘口令生成
     */
    public function getTpwdCreate($text, $url, $user_id = '', $logo = '', $ext = '{}')
    {
        $c = new \TopClient();
        $c->appkey = $this->app_key;
        $c->secretKey = $this->app_secret;
        $c->format = 'json';
        $c->gatewayUrl = $this->url;
        $req = new \TbkTpwdCreateRequest();
        if (!empty($user_id)) $req->setUserId($user_id);  #可空 生成口令的淘宝用户ID
        $req->setText($text);                             #口令弹框内容
        $req->setUrl($url);                               #口令跳转目标页
        if (!empty($logo)) $req->setLogo($logo);          #可空 口令弹框logoURL
        if (!empty($ext)) $req->setExt($ext);             #可空 扩展字段JSON格式
        $resp = $c->execute($req);
        return $resp;
    }

    /*
     * 淘宝客-推广者-官方活动转链
     */
    function getActivityLink($adzone_id, $relation_id)
    {
        $key_info = $this->new_can_change_auth_info[$adzone_id];

        /**
         * @var $app_key string
         * @var $app_secret string
         * @var $pid string
         */
        extract($key_info);

        $c = new \TopClient;
        $c->appkey = $app_key;
        $c->secretKey = $app_secret;
        $c->format = 'json';
        $req = new \TbkActivitylinkGetRequest;
        $req->setAdzoneId($adzone_id);
        $req->setPromotionSceneId("1571715733668");
        $req->setRelationId($relation_id);
        $resp = $c->execute($req);
        return $resp;
    }
}