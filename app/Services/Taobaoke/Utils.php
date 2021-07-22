<?php

namespace App\Services\Taobaoke;

use App\Exceptions\ApiException;

class Utils
{
    protected $url = 'https://eco.taobao.com/router/rest';
    protected $method;
    protected $appkey = '25626319';
    protected $secretKey = '05668c4eefc404c0cd175fb300b2723d';
    protected $timestamp;
    protected $signMethod = "md5";
    protected $apiVersion = "2.0";
    protected $format = "json";
    protected $simplify = 'true';
    public $arrPublic;

    public function __construct($method = ""){
        $this->method = $method;
    }


    /**
     * 得到appKey
     * @return string
     */
    public function getAppkey()
    {
        return $this->appkey;
    }

    /**
     * 与公共数组合
     * @param $strParams
     */
    public function arrange(&$strParams){
        $strParams['method'] = $this->method;
        $strParams['app_key'] = $this->appkey;
        $strParams['timestamp'] = date("Y-m-d H:i:s");
        $strParams['sign_method'] = $this->signMethod;
        $strParams['format'] = $this->format;
        $strParams['v'] = $this->apiVersion;
        $strParams['simplify'] = $this->simplify;
    }

    /**
     * 对传入的参数进行签名
     * @param $params
     * @return string
     */
    public function generateSign(&$params)
    {
        ksort($params);
        $stringToBeSigned = $this->secretKey;
        foreach ($params as $k => $v)
        {
            if(!is_array($v) && "@" != substr($v, 0, 1))
            {
                $stringToBeSigned .= "$k$v";
            }
        }
        unset($k, $v);
        $stringToBeSigned .= $this->secretKey;

        return strtoupper(md5($stringToBeSigned));
    }

    /**
     * @param $strParms
     * @return mixed
     * @throws ApiException
     */
    public function curl($strParms)
    {
        $url = $this->url.$strParms;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FAILONERROR, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if(strlen($url) > 5 && strtolower(substr($url,0,5)) == "https" ) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }

        $reponse = curl_exec($ch);

        if (curl_errno($ch))
        {
            throw new ApiException(curl_error($ch),0);
        }
        else
        {
            $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            if (200 !== $httpStatusCode)
            {
                throw new ApiException($reponse,$httpStatusCode);
            }
        }
        curl_close($ch);
        return $reponse;
    }
}
