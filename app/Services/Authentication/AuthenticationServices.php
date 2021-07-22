<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/8/13
 * Time: 10:03
 */

namespace App\Services\Authentication;

class AuthenticationServices
{
    protected $accessKeyId = 'LTAIWmKBrzM9gTOG';
    protected $accessKeySecret = 'aNyB00EbO9PR7dgAKxcT7GEgMTMpzy';
    protected $url = 'cloudauth.aliyuncs.com';
    public $connectTimeout = 3000;
    public $readTimeout = 80000;

    /*
     * 发起认证请求
     */
    public function getVerifyToken($my_order, $Binding = '', $UserData = '')
    {
        date_default_timezone_set("GMT");
        $time = date('Y-m-d\TH:i:s\Z');
        /*公共参数*/
        $arr_parameter['Format'] = 'JSON';                  #可空 返回值的类型，支持 JSON 与 XML，默认为 XML
        $arr_parameter['Version'] = '2018-09-16';           #API 版本号，为日期形式：YYYY-MM-DD，具体请参考https://help.aliyun.com/document_detail/65922.html?spm=a2c4g.11186623.2.15.437f1619iP5Nr9
        $arr_parameter['AccessKeyId'] = $this->accessKeyId; #阿里云颁发给用户的访问服务所用的密钥 ID
        $arr_parameter['SignatureMethod'] = 'HMAC-SHA1';    #签名方式，目前支持 HMAC-SHA1
        $arr_parameter['Timestamp'] = $time;                #请求的时间戳。日期格式按照 ISO8601 标准表示，并需要使用 UTC 时间 0 时区的值。格式为 YYYY-MM-DDThh:mm:ssZ
        $arr_parameter['SignatureVersion'] = '1.0';         #签名算法版本，目前版本是1.0
        $arr_parameter['SignatureNonce'] = uniqid();        #唯一随机数，用于防止网络重放攻击。用户在不同请求间要使用不同的随机数值
        /*接口参数*/
        $arr_parameter['Action'] = 'GetVerifyToken';        #要执行的操作。取值：GetVerifyToken
        $arr_parameter['RegionId'] = 'cn-hangzhou';         #服务所在地域。取值：cn-hangzhou
        $arr_parameter['Biz'] = 'forTongxunCall';           #使用实人认证服务的业务场景
        $arr_parameter['TicketId'] = $my_order;             #标识一次认证任务的唯一ID 发起不同的认证任务时需要更换不同的认证 ID
        $arr_parameter['Binding'] = $Binding;               #可空 认证扩展材料（JSON 格式）
        $arr_parameter['UserData'] = $UserData;             #可空 业务数据（JSON 格式），通常用来传递业务上下文内容
        if (empty($arr_parameter['Format'])) {
            unset($arr_parameter['Format']);
        }
        if (empty($arr_parameter['Binding'])) {
            unset($arr_parameter['Binding']);
        }
        if (empty($arr_parameter['UserData'])) {
            unset($arr_parameter['UserData']);
        }
        $sign = $this->computeSignature($arr_parameter, $this->accessKeySecret);
        $arr_parameter['Signature'] = $sign;
        $requestUrl = rtrim($this->url, "/") . "/?";
        foreach ($arr_parameter as $apiParamKey => $apiParamValue) {
            $requestUrl .= "$apiParamKey=" . urlencode($apiParamValue) . "&";
        }
        $requestUrl = substr($requestUrl, 0, -1);
        $requestUrl = 'https://' . $requestUrl;
        try {
            $resp = $this->curl($requestUrl, null);
        } catch (\Exception $e) {
            if ("JSON" == $arr_parameter['Format']) {
                return json_decode($e->getMessage());
            } else if ("XML" == $arr_parameter['Format']) {
                return @simplexml_load_string($e->getMessage());
            }
        }
        return $resp;
    }

    /*
     * 查询认证状态
     */
    public function getStatus($TicketId)
    {
        date_default_timezone_set("GMT");
        $time = date('Y-m-d\TH:i:s\Z');
        /*公共参数*/
        $arr_parameter['Format'] = 'JSON';                  #可空 返回值的类型，支持 JSON 与 XML，默认为 XML
        $arr_parameter['Version'] = '2018-09-16';           #API 版本号，为日期形式：YYYY-MM-DD，具体请参考https://help.aliyun.com/document_detail/65922.html?spm=a2c4g.11186623.2.15.437f1619iP5Nr9
        $arr_parameter['AccessKeyId'] = $this->accessKeyId; #阿里云颁发给用户的访问服务所用的密钥 ID
        $arr_parameter['SignatureMethod'] = 'HMAC-SHA1';    #签名方式，目前支持 HMAC-SHA1
        $arr_parameter['Timestamp'] = $time;                #请求的时间戳。日期格式按照 ISO8601 标准表示，并需要使用 UTC 时间 0 时区的值。格式为 YYYY-MM-DDThh:mm:ssZ
        $arr_parameter['SignatureVersion'] = '1.0';         #签名算法版本，目前版本是1.0
        $arr_parameter['SignatureNonce'] = uniqid();        #唯一随机数，用于防止网络重放攻击。用户在不同请求间要使用不同的随机数值
        /*接口参数*/
        $arr_parameter['Action'] = 'GetStatus';             #要执行的操作。取值：GetStatus
        $arr_parameter['RegionId'] = 'cn-hangzhou';         #服务所在地域。取值：cn-hangzhou
        $arr_parameter['Biz'] = 'forTongxunCall';           #使用实人认证服务的业务场景
        $arr_parameter['TicketId'] = $TicketId;             #要查询的认证任务ID。通常由业务使用方指定，方便关联业务场景的其他内容 需要与当前认证任务在GetVerifyToken时的认证ID保持一致。
        if (empty($arr_parameter['Format'])) {
            unset($arr_parameter['Format']);
        }
        $sign = $this->computeSignature($arr_parameter, $this->accessKeySecret);
        $arr_parameter['Signature'] = $sign;
        $requestUrl = rtrim($this->url, "/") . "/?";
        foreach ($arr_parameter as $apiParamKey => $apiParamValue) {
            $requestUrl .= "$apiParamKey=" . urlencode($apiParamValue) . "&";
        }
        $requestUrl = substr($requestUrl, 0, -1);
        $requestUrl = 'https://' . $requestUrl;
        try {
            $resp = $this->curl($requestUrl, null);
        } catch (\Exception $e) {
            if ("JSON" == $arr_parameter['Format']) {
                return json_decode($e->getMessage());
            } else if ("XML" == $arr_parameter['Format']) {
                return @simplexml_load_string($e->getMessage());
            }
        }
        return $resp;
    }

    /*
     * 生成唯一认证id
     */
    public function guid()
    {
        if (function_exists('com_create_guid')) {
            return com_create_guid();
        } else {
            mt_srand((double)microtime() * 10000);
            $charid = strtoupper(md5(uniqid(rand(), true)));
            $hyphen = chr(45);
            $uuid = substr($charid, 0, 8) . $hyphen
                . substr($charid, 8, 4) . $hyphen
                . substr($charid, 12, 4) . $hyphen
                . substr($charid, 16, 4) . $hyphen
                . substr($charid, 20, 12);
            return $uuid;
        }
    }

    /*
     * 生成签名
     */
    protected function computeSignature($parameters, $accessKeySecret)
    {
        ksort($parameters);
        $canonicalizedQueryString = '';
        foreach ($parameters as $key => $value) {
            $canonicalizedQueryString .= '&' . $this->percentEncode($key)
                . '=' . $this->percentEncode($value);
        }
        $stringToSign = 'GET&%2F&' . $this->percentencode(substr($canonicalizedQueryString, 1));
        $signature = base64_encode(hash_hmac('sha1', $stringToSign, $accessKeySecret . '&', true));
        return $signature;
    }

    /*
     * 对字符编码处理
     */
    protected function percentEncode($str)
    {
        $res = urlencode($str);
        $res = preg_replace('/\+/', '%20', $res);
        $res = preg_replace('/\*/', '%2A', $res);
        $res = preg_replace('/%7E/', '~', $res);
        return $res;
    }

    /*
     * 发起请求
     */
    public function curl($url, $postFields = null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FAILONERROR, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($this->readTimeout) {
            curl_setopt($ch, CURLOPT_TIMEOUT, $this->readTimeout);
        }
        if ($this->connectTimeout) {
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->connectTimeout);
        }
        if (strlen($url) > 5 && strtolower(substr($url, 0, 5)) == "https") {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }

        if (is_array($postFields) && 0 < count($postFields)) {
            $postBodyString = "";
            $postMultipart = false;
            foreach ($postFields as $k => $v) {
                if ("@" != substr($v, 0, 1))
                {
                    $postBodyString .= "$k=" . urlencode($v) . "&";
                } else//文件上传用multipart/form-data，否则用www-form-urlencoded
                {
                    $postMultipart = true;
                }
            }
            unset($k, $v);
            curl_setopt($ch, CURLOPT_POST, true);
            if ($postMultipart) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
            } else {
                curl_setopt($ch, CURLOPT_POSTFIELDS, substr($postBodyString, 0, -1));
            }
        }
        $reponse = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new Exception(curl_error($ch), 0);
        }
        curl_close($ch);
        return $reponse;
    }
}