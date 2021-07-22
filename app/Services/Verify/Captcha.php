<?php

namespace App\Services\Verify;

class Captcha
{
    /**
     *
     * /* 密钥,请进行替换,密钥申请地址 https://console.qcloud.com/capi
     */
    public $SECRET_ID = 'AKIDIUfwJFnteLr4ywc853w7KSUcghXCLw2x';
    public $SECRET_KEY = 'iEKUoBmEn8cVE35wbQ8qSxJkKEwpLk5G';
    public $CAPTACHA_TYPE = '9';
    public $DISTURB_LEVEL = '1';
    public $IS_HTTPS = '1';
    public $CLIENT_TYPE = '1';
    public $BUSINESS_ID = '99';
    public $VERIFY_TYPE = '0';
    public $URL = 'csec.api.qcloud.com/v2/index.php';

    /* Basic request URL */

    public function sendRequest($url, $method = 'POST')
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if (false !== strpos($url, "https")) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }
        $resultStr = curl_exec($ch);
        $result = json_decode($resultStr, true);

        return $result;
    }

    /* Generates an available URL */
    public function makeURL($method, $action, $region, $secretId, $secretKey, $args)
    {
        /* Add common parameters */
        $args['Nonce'] = (string)rand(0, 0x7fffffff);
        $args['Action'] = $action;
        $args['Region'] = $region;
        $args['SecretId'] = $secretId;
        $args['Timestamp'] = (string)time();

        /* Sort by key (ASCII order, ascending), then calculate signature using HMAC-SHA1 algorithm */
        ksort($args);
        $args['Signature'] = base64_encode(
            hash_hmac(
                'sha1', $method . $this->URL . '?' . $this->makeQueryString($args, false),
                $secretKey, true
            )
        );

        /* Assemble final request URL */

        return 'https://' . $this->URL . '?' . $this->makeQueryString($args, true);
    }

    /* Construct query string from array */
    public function makeQueryString($args, $isURLEncoded)
    {
        $arr = array();
        foreach ($args as $key => $value) {
            if (!$isURLEncoded) {
                $arr[] = "$key=$value";
            } else {
                $arr[] = $key . '=' . urlencode($value);
            }
        }
        return implode('&', $arr);
    }

    /* Demo section */
    public function getJsUrl($ip)
    {
        /*
        * 补充用户、行为信息数据,方便我们做更准确的数据模型
        * 协议参考 https://www.qcloud.com/doc/api/254/2897
        */
        $url = $this->makeURL(
            'GET',
            'CaptchaIframeQuery',
            'sz',
            $this->SECRET_ID,
            $this->SECRET_KEY,

            array(
                /* 行为信息参数 */
                'userIp' => $ip,

                /* 验证码信息参数 */
                'captchaType' => $this->CAPTACHA_TYPE,
                'disturbLevel' => $this->DISTURB_LEVEL,
                'isHttps' => $this->IS_HTTPS,
                'clientType' => $this->CLIENT_TYPE,

                /* 其他信息参数 */
                'businessId' => $this->BUSINESS_ID
            )
        );
        $result = $this->sendRequest($url);
        $jsUrl = $result['url'];

        return $jsUrl;
    }

    public function check($ticket, $ip)
    {
        /*
        * 补充用户、行为信息数据,方便我们做更准确的数据模型
        * 协议参考 https://www.qcloud.com/doc/api/254/2898
        */
        $url = $this->makeURL(
            'GET',
            'CaptchaCheck',
            'sz',
            $this->SECRET_ID,
            $this->SECRET_KEY,

            array(
                /* 行为信息参数 */
                'userIp' => $ip,

                /* 验证码信息参数 */
                'captchaType' => $this->CAPTACHA_TYPE,
                'ticket' => $ticket,
                'verifyType' => $this->VERIFY_TYPE,
                /* 其他信息参数 */
                'businessId' => $this->BUSINESS_ID
            )
        );
        $result = $this->sendRequest($url);

        return $result;
    }
}
