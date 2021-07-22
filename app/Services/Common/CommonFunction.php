<?php

namespace App\Services\Common;

use Illuminate\Support\Facades\Storage;

class CommonFunction
{
    public function randomKeys($length)
    {
        $output = '';
        for ($a = 0; $a < $length; $a++) {
            $output .= rand(0, 9);
        }
        return $output;
    }

    public function random($length, $numeric = 0)
    {
        PHP_VERSION < '4.2.0' && mt_srand((double)microtime() * 1000000);
        if ($numeric) {
            $hash = sprintf('%0' . $length . 'd', mt_rand(0, pow(10, $length) - 1));
        } else {
            $hash = '';
            $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
            $max = strlen($chars) - 1;
            for ($i = 0; $i < $length; $i++) {
                $hash .= $chars[mt_rand(0, $max)];
            }
        }
        return $hash;
    }

    /**
     * 发送验证码
     */
    public function sendSms($phone, $sms)
    {
        $code = $this->randomKeys(5);

        $params = array();

//        $accessKeyId = "LTAIWmKBrzM9gTOG";
//        $accessKeySecret = "aNyB00EbO9PR7dgAKxcT7GEgMTMpzy";
        //大权限
        $accessKeyId = "LTAI4G5VuTorjZZdFuupQoDN";
        $accessKeySecret = "n4t3BDMx3ckyz8bT2rRunO62vH23RQ";

        $params["PhoneNumbers"] = $phone;
        $params["SignName"] = "葡萄浏览器";
        $params["TemplateCode"] = "SMS_99560021";
        $params['TemplateParam'] = Array(
            "code" => $code
        );
        $params['OutId'] = "12345";
        $params['SmsUpExtendCode'] = "1234567";
        if (!empty($params["TemplateParam"]) && is_array($params["TemplateParam"])) {
            $params["TemplateParam"] = json_encode($params["TemplateParam"], JSON_UNESCAPED_UNICODE);
        }
        $content = $sms->request(
            $accessKeyId,
            $accessKeySecret,
            "dysmsapi.aliyuncs.com",
            array_merge($params, array(
                "RegionId" => "cn-hangzhou",
                "Action" => "SendSms",
                "Version" => "2017-05-25",
            ))
        );

        return $code;
    }

    /**
     * 发送通知码
     */
    public function sendWarnSms($phone, $sms, $msg, $code)
    {
        $params = array();

//        $accessKeyId = "LTAIWmKBrzM9gTOG";
//        $accessKeySecret = "aNyB00EbO9PR7dgAKxcT7GEgMTMpzy";
        //大权限
        $accessKeyId = "LTAI4G5VuTorjZZdFuupQoDN";
        $accessKeySecret = "n4t3BDMx3ckyz8bT2rRunO62vH23RQ";

        $params["PhoneNumbers"] = $phone;
        $params["SignName"] = "葡萄浏览器";
        $params["TemplateCode"] = "SMS_140730505";
        $params['TemplateParam'] = Array(
            "code" => $code,
            "msg" => $msg
        );
        $params['OutId'] = "12345";
        $params['SmsUpExtendCode'] = "1234567";
        if (!empty($params["TemplateParam"]) && is_array($params["TemplateParam"])) {
            $params["TemplateParam"] = json_encode($params["TemplateParam"], JSON_UNESCAPED_UNICODE);
        }
        $content = $sms->request(
            $accessKeyId,
            $accessKeySecret,
            "dysmsapi.aliyuncs.com",
            array_merge($params, array(
                "RegionId" => "cn-hangzhou",
                "Action" => "SendSms",
                "Version" => "2017-05-25",
            ))
        );

        return $code;
    }

    /**
     * 用于混淆id
     * @param $msg
     * @return bool|string
     */
    public function easyEncode($msg)
    {
        if (!preg_match("/^\d+$/", $msg)) {
            return false;
        }
        $msg = 58 . $msg . 58;
        $msg_md5 = md5('ptKey' . strval($msg) . 'ptKey');

        $key = '';
        for ($i = 0; $i < strlen($msg); $i++) {
            $key .= $msg[$i] . $msg_md5[$i];
        }
        return $key;
    }

    /**
     * 用于解密混淆
     * @param $msg
     * @return bool|string
     */
    public function easyDecode($msg)
    {
        if (strlen($msg) % 2) {
            return false;
        }
        $de_msg = '';
        $de_key = '';
        for ($i = 0; $i < strlen($msg); $i = $i + 2) {
            $de_msg .= $msg[$i];
            $de_key .= $msg[$i + 1];

        }
        $id = substr($de_msg, 2, -2);
        $msg_md5 = md5('ptKey' . strval($de_msg) . 'ptKey');
        $key_md5 = substr($msg_md5, 0, strlen($de_key));

        if (strcmp($de_key, $key_md5)) {
            return false;
        }
        return $id;
    }


    /**
     * 公钥加密 获取加密数据
     * @param $data
     * @param $url_last
     * @param $rsaUtils 来源是service RSA
     * @return array
     */
    public function encodeForApi($data, $url_last, $rsaUtils)
    {
        $encrypted = $rsaUtils->rsaPublicEncode($data);
        $time = time();
        $url = "http://";
        $url .= "api.36qq.com/" . $url_last;
        $key = "62h3svBYRsPUaZPXNRU9";
        $key_sub = mb_substr($key, 0, 5);
        $key_sub2 = mb_substr($key, 5, 5);
        $key_sub3 = mb_substr($key, 10, 5);
        $key_sub4 = mb_substr($key, 15, 5);
        $need_sign = $key_sub . $encrypted . $key_sub2 . $time . $key_sub3 . $url . $key_sub4;
        $sign = hash("sha512", $need_sign);

        return [
            'token' => $encrypted,
            'time' => $time,
            'url' => $url,
            'sign' => $sign
        ];
    }

    /**
     * 获取首页头条列表页缓存key
     * @param int $page
     */
    public static function getIndexArticlePageCacheKey($page = 1)
    {
        return 'putao.index.article.list.page.' . $page;
    }

    /**
     * 获取用户粉丝数缓存key
     * @param int $app_id
     */
    public static function getUserFansCountCacheKey($app_id)
    {
        return 'putao.user.fanscount.' . $app_id;
    }

    /**
     * 仿照MYSQL内置函数第几天
     * @param string $day
     * @return number
     */
    public static function toDays($day)
    {
        $time1 = strtotime('2019-01-01');
        $time2 = strtotime($day);
        $_day = ($time2 - $time1) / 86400;
        return 737425 + $_day;
    }

    /**
     * 内存转换
     * @param int $bytes
     * @param number $precision
     * @return string
     */
    public static function formatBytes($bytes, $precision = 2)
    {
        $units = array("b", "kb", "mb", "gb", "tb");

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . " " . $units[$pow];
    }

    /**
     * 获取用户月活跃值缓存key
     * @param int $user_id
     * @param string $date
     * @return string
     */
    public static function getUserMonthActiveCacheKey($user_id, $date = '0000-00')
    {
        return 'putao.user.month.active.' . $user_id . '.' . $date;
    }

    /**
     * 仅用户客户端头部参数传输 Accept-Sign-Id
     * app_id 简单转换成 code
     * @param $app_id
     * @return string
     */
    public function idToCode($app_id)
    {
        $oct = ($app_id + 92) * 3;

        $convert = base_convert($oct, 10, 35);

        return $convert;
    }

    /**
     * 仅用户客户端头部参数传输 Accept-Sign-Id
     * 将heade获取的code转换成app_id值
     * @param $code
     * @return float|int
     */
    public function codeToId($code)
    {
        $oct = base_convert($code, 35, 10);

        $app_id = $oct / 3 - 92;

        return $app_id;
    }


    /**
     * AES 加密
     * @param $data
     * @param $secret_key
     * @param string $iv
     * @return string
     */
    public function aesEncode($data, $secret_key, $iv = '1234567891234567')
    {
        return base64_encode(openssl_encrypt($data, 'AES-256-CBC', $secret_key, OPENSSL_RAW_DATA, $iv));
    }

    /**
     * AES解密
     * @param $data
     * @param $secret_key
     * @param string $iv
     * @return string
     */
    public function aesDecode($data, $secret_key, $iv = '1234567891234567')
    {
        return openssl_decrypt(base64_decode($data), 'AES-256-CBC', $secret_key, OPENSSL_RAW_DATA, $iv);
    }

    /*
     * app_id兼容 转33进制
     */
    static public function userAppIdCompatibility($app_id)
    {
        if ($app_id >= 10000000) {
            $app_id = base_convert($app_id, 10, 33); // 10 转 33
            $app_id = 'x' . $app_id;
        }
        return $app_id;
    }

    /**
     * 记录回调日志
     */
    static public function  callbackLog($msg, $dir = '', $fn_end = '')
    {
        $date = date('Ymd');
        $pre_dir = 'callback_document/';
        if(!empty($dir)){
            $pre_dir = $pre_dir . $dir;
        }
        Storage::disk('local')->append($pre_dir. '/' . $date . $fn_end . '.txt', date('H:i:s') . '#### ' . var_export($msg, true) . ' ####');
    }

    /**
     * 记录回调日志
     */
    static public function  log($msg, $dir = '', $fn_end = '')
    {
        $date = date('Ymd');
        Storage::disk('local')->append($dir. '/' . $date . $fn_end . '.txt', date('H:i:s') . '#### ' . var_export($msg, true) . ' ####');
    }
}
