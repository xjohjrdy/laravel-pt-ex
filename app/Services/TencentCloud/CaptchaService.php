<?php


namespace App\Services\TencentCloud;


use App\Services\TencentCloud\Captcha\V20190722\CaptchaClient;
use App\Services\TencentCloud\Captcha\V20190722\Models\DescribeCaptchaResultRequest;
use App\Services\TencentCloud\Common\Credential;
use App\Services\TencentCloud\Common\Profile\HttpProfile;

class CaptchaService
{
    private $SECRET_ID = 'AKIDIUfwJFnteLr4ywc853w7KSUcghXCLw2x';
    private $SECRET_KEY = 'iEKUoBmEn8cVE35wbQ8qSxJkKEwpLk5G';

    /**
     * @param integer $CaptchaType 验证码类型，9：滑块验证码
     * @param string $Ticket 验证码返回给用户的票据
     * @param string $UserIp 用户操作来源的外网 IP
     * @param string $Randstr 验证票据需要的随机字符串
     * @param integer $CaptchaAppId 验证码应用ID
     * @param string $AppSecretKey 用于服务器端校验验证码票据的验证密钥，请妥善保密，请勿泄露给第三方
     * @param integer $BusinessId 业务 ID，网站或应用在多个业务中使用此服务，通过此 ID 区分统计数据
     * @param integer $SceneId 场景 ID，网站或应用的业务下有多个场景使用此服务，通过此 ID 区分统计数据
     * @param string $MacAddress mac 地址或设备唯一标识
     * @param string $Imei 手机设备号
     */
    private $default_params = [
        'CaptchaType' => 9,
        'CaptchaAppId' => 2074165296,
        'AppSecretKey' => '0G820LFr9BsMijYAQQ8Zeuw**',
    ];
    public function validateCaptcha($params = [])
    {
        $cred = new Credential($this->SECRET_ID, $this->SECRET_KEY);

        $client = new CaptchaClient($cred, '');

        $req = new DescribeCaptchaResultRequest();


        $params = array_merge($params, $this->default_params);
        $req->deserialize($params);
        $resp = $client->DescribeCaptchaResult($req);
//        print_r($resp->toJsonString());
        return $resp;


    }
}