<?php


namespace App\Services\Qmshida;


use GuzzleHttp\Client;

class GongMallService
{
    //正式环境 江苏展朔科技咨询有限公司
        protected $appKey = 'd19fb6fa0fee41b3b79afa0c29c1bb9e';
        protected $appSecret = 'f81b7dcafcfc3b4ed35fa8f2a17b5a6d';
        protected $contractUrl = 'https://contract.gongmall.com/url_contract.html?companyId=PxY6wM&positionId=kPxgNV&channel=WVnYzZ';
        protected $url = 'https://openapi.gongmall.com';

    //测试环境 江苏展朔科技咨询有限公司
//    protected $appKey = '1fc3427ebe384d06b94890611c1735fa';
//    protected $appSecret = 'beee19364c733ce7c9964b8bc1ab1af1';
//    protected $contractUrl = 'https://contract-qa.gongmall.com/url_contract.html?companyId=ePqkoz&positionId=9zwXkV&channel=WVnYzZ';
//    protected $url = 'https://openapi-qa.gongmall.com';

    //正式环境 福建搜索互动网络科技有限公司
    //    protected $appKey = 'ba99a67fb9ee44e19501af12cc4f9657';
    //    protected $appSecret = '7ca4be398745119d0bab9802bff40d3c';
    //    protected $contractUrl = 'https://contract.gongmall.com/url_contract.html?companyId=M2qQDP&positionId=4PpBRz&channel=WVnYzZ';
    //    protected $url = 'https://openapi.gongmall.com';

//    //测试环境 福建搜索互动网络科技有限公司
//    protected $appKey = 'c311382bd9ff462b9c22a07dae19cb88';
//    protected $appSecret = 'f37459724723af929aa83ba5ced8e8b6';
//    protected $contractUrl = 'https://contract-qa.gongmall.com/url_contract.html?companyId=4Ppbmz&positionId=lzDYqV&channel=WVnYzZ';
//    protected $url = 'https://openapi-qa.gongmall.com';

    /*
     * 接入 得到加密url
     */
    public function getEncryptionUrl($workNumber, $name, $mobile, $certificateType, $idNumber, $bankNum)
    {
        $data = [
            'name' => $name,                        #姓名
            'mobile' => $mobile,                    #手机号
            'certificateType' => $certificateType,  # 1:身份证
            'idNumber' => $idNumber,                #证件号
            'bankNum' => $bankNum,                  #银行卡号/ 支付宝账户
            'workNumber' => $workNumber
        ];
        //data为AES加密数据
        $plaintext = urldecode(http_build_query($data));

        //加密key由配置的appKey与appSecret生成
        $key = strtoupper(md5($this->appKey . $this->appSecret));
        //偏移量
        $size = 16;
        $iv = str_repeat("\0", $size);
        // 添加Padding，使用//PKCS5Padding
        $padding = $size - strlen($plaintext) % $size;
        $plaintext .= str_repeat(chr($padding), $padding);
        //使用AES-192-CBC进行加密
        $encrypted = openssl_encrypt($plaintext, 'AES-192-CBC', base64_decode($key), OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv);
        //加密结果
        $contractUrl = $this->contractUrl . "&data=" . base64_encode($encrypted);
        return $contractUrl;
    }

    /*
     * 查询电签结果
     */
    public function getContractStatus($workNumber)
    {
        //查询电签结果api
        $client = new Client();
        $full_url = $this->url . '/api/employee/getContractStatus';
        //所需参数
        $post_api_data = [
            'appKey' => $this->appKey,                           #开发者唯一标识
            'nonce' => uniqid('dq', true), #随机数 不长于32位
            'timestamp' => time() . '000',                      #当前毫秒时间戳
            'workNumber' => $workNumber
        ];
//        if (!empty($isunion)) $post_api_data['isunion'] = $isunion;

        //生成签名
        $post_api_data['sign'] = $this->sign($post_api_data);

        $data = [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'form_params' => $post_api_data,
            'verify' => false
        ];
        $res_login_data = $client->request('POST', $full_url, $data);
        return (string)$res_login_data->getBody();
    }


    /*
        * 提现
        */
    public function doWithdraw($params)
    {
        //查询电签结果api
        $client = new Client();
        $full_url = $this->url . '/api/withdraw/doWithdraw';
        //所需参数

        $post_api_data = [
            'appKey' => $this->appKey,                           #开发者唯一标识
            'nonce' => uniqid('dq', true), #随机数 不长于32位
            'timestamp' => time() . '000',                      #当前毫秒时间戳
            "dateTime" => date("YmdHms", time()),
        ];
        $post_api_data = array_merge($post_api_data, $params);
//        if (!empty($isunion)) $post_api_data['isunion'] = $isunion;
        //生成签名
        $post_api_data['sign'] = $this->sign($post_api_data);

        $data = [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'form_params' => $post_api_data,
            'verify' => false
        ];
        $res_login_data = $client->request('POST', $full_url, $data);
        return (string)$res_login_data->getBody();
    }

    /*
      * 查询企业剩余金额
      */
    public function checkCompanyMoney($params)
    {
        //查询电签结果api
        $client = new Client();
        $full_url = $this->url . '/api/company/getBalance';
        //所需参数

        $post_api_data = [
            'appKey' => $this->appKey,                           #开发者唯一标识
            'nonce' => uniqid('dq', true), #随机数 不长于32位
            'timestamp' => time() . '000',                      #当前毫秒时间戳
            "dateTime" => date("YmdHms", time()),
        ];
        $post_api_data = array_merge($post_api_data, $params);
//        if (!empty($isunion)) $post_api_data['isunion'] = $isunion;
        //生成签名
        $post_api_data['sign'] = $this->sign($post_api_data);

        $data = [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'form_params' => $post_api_data,
            'verify' => false
        ];
        $res_login_data = $client->request('POST', $full_url, $data);
        return (string)$res_login_data->getBody();
    }

    /*
    * 获取税额
    */
    public function getTaxInfo($params)
    {
        //查询电签结果api
        $client = new Client();
        $full_url = $this->url . '/api/withdraw/getTaxInfo';
        //所需参数

        $post_api_data = [
            'appKey' => $this->appKey,                           #开发者唯一标识
            'nonce' => uniqid('dq', true), #随机数 不长于32位
            'timestamp' => time() . '000',                      #当前毫秒时间戳
            "dateTime" => date("YmdHms", time()),
        ];
        $post_api_data = array_merge($post_api_data, $params);
//        if (!empty($isunion)) $post_api_data['isunion'] = $isunion;
        //生成签名
        $post_api_data['sign'] = $this->sign($post_api_data);

        $data = [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'form_params' => $post_api_data,
            'verify' => false
        ];
        $res_login_data = $client->request('POST', $full_url, $data);
        return (string)$res_login_data->getBody();
    }

    /*
       * 查询单笔提现结果
       */
    public function getWithdrawResult($params)
    {
        //查询电签结果api
        $client = new Client();
        $full_url = $this->url . '/api/withdraw/getWithdrawResult';
        //所需参数

        $post_api_data = [
            'appKey' => $this->appKey,                           #开发者唯一标识
            'nonce' => uniqid('dq', true), #随机数 不长于32位
            'timestamp' => time() . '000',                      #当前毫秒时间戳
            "dateTime" => date("YmdHms", time()),
        ];
        $post_api_data = array_merge($post_api_data, $params);
//        if (!empty($isunion)) $post_api_data['isunion'] = $isunion;
        //生成签名
        $post_api_data['sign'] = $this->sign($post_api_data);

        $data = [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'form_params' => $post_api_data,
            'verify' => false
        ];
        $res_login_data = $client->request('POST', $full_url, $data);
        return (string)$res_login_data->getBody();
    }

    /*
        * 提现
        */
    public function getWithdrawList($params)
    {
        //查询电签结果api
        $client = new Client();
        $full_url = $this->url . '/api/withdraw/getWithdrawList';
        //所需参数

        $post_api_data = [
            'appKey' => $this->appKey,                           #开发者唯一标识
            'nonce' => uniqid('dq', true), #随机数 不长于32位
            'timestamp' => time() . '000',                      #当前毫秒时间戳
            "dateTime" => date("YmdHms", time()),
        ];
        $post_api_data = array_merge($post_api_data, $params);
//        if (!empty($isunion)) $post_api_data['isunion'] = $isunion;
        //生成签名
        $post_api_data['sign'] = $this->sign($post_api_data);

        $data = [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'form_params' => $post_api_data,
            'verify' => false
        ];
        $res_login_data = $client->request('POST', $full_url, $data);
        return (string)$res_login_data->getBody();
    }

    /*
     * 生成签名
     */
    function sign($params)
    {
        ksort($params);
        $stringA = '';
        foreach ($params as $key => $val) $stringA .= $key . '=' . $val . '&';
        $stringA = trim($stringA, '&');
        $stringSignTemp = $stringA . "&appSecret=" . $this->appSecret;
        $sign = strtoupper(md5($stringSignTemp));
        return $sign;
    }
}