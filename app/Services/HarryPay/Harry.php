<?php

namespace App\Services\HarryPay;


use App\Services\Tools\WebApiRsa;
use GuzzleHttp\Client;

class Harry
{

//--------------------------------------测试使用
//    public $root_url = 'http://39.107.15.64:8095/';
//    public $private_key = 'MIICXAIBAAKBgQDGJOxxQhysRUpRUbwMns75dsJXYMYSQb+CG2gB/Ssz01oQ8gCyC6lchIIqlzMnsQmmDsOIL0K+spFs/oYAqCTryfU5xZXzctYFEHNaDV9xqcyGjuo8R5J1DSHmNkydAwr8Lp2JyMEsWt652dv5nNXxw7FE/6G8pVSz1cZ+wYd6OwIDAQABAoGBAL/ckwJcNO1urratQTyrLdkK8MWxFDorZefy57Q9U+778VNFMf01I4pNWMkq3ULKv4AG/bjJooSK3hw/HLxYFF50MkQTW/Vg9uIxeD6qPuN8cqBVKvuFQO4xZBZUePUdeYI7qKmhMJJ6a336ppH6a4oYCq7cHPTS5er3BhaYyZsZAkEA6DHjM3SXx6SlrjTfdWQIGCX9gLqBLlp1t/m6CVqwQjI9WAlTvoM9eZOBsoFIueqRhzNB85kbt/vEg8ldyzIKhwJBANp1WpcdxuARF1Extd1f28j7V1PwVhJSmLEUsnG+1agFtvql9Lhu/0hhoI5A/sfotyL1EAmiWCDbGH0Of0F8+60CQArEXXPCYVNpqCEm5IHODK4J/PJeM6VRnonUc7MBWJEJQVz2ucJo1Y3wsB/17MhqPytUziccn3NtolQ2HzpP7LsCQGYmm+6vwNADjeismwLiEQ7A4IvihQzaTIX5TJu9hYCk83Pu6CjZ1ktNQ1thbwGhgwk4mIA4xobOjHvlrIG95J0CQAQx5F8OY/1syCIqSm6bG+DH3q8YnyFyyimvIh1U6YnZZ75kXghshDB1YwW8a4TGLpo7Xbb1uyRaETQ3Qgvqee4=';
//    public $public_key = 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDGJOxxQhysRUpRUbwMns75dsJXYMYSQb+CG2gB/Ssz01oQ8gCyC6lchIIqlzMnsQmmDsOIL0K+spFs/oYAqCTryfU5xZXzctYFEHNaDV9xqcyGjuo8R5J1DSHmNkydAwr8Lp2JyMEsWt652dv5nNXxw7FE/6G8pVSz1cZ+wYd6OwIDAQAB';

    ///

//    public $outMemberNo = '1237264584692940801';
//    public $contractNo = '1163708131004383232';
//    public $notifyUrl = 'http://api.36qq.com/api/harry_t_call';


//    public $t_root_url = 'http://39.107.15.64:8090/';
//    public $notifyUrl_get = 'http://baidu.com';
//
//    public $md5_key = 'df1ec76efb7fb76ffdd42e036045a046';
//--------------------------------------测试使用

    public $root_url = 'https://contract.lingxinpay.com/contract-api/';
    public $private_key = 'MIICXgIBAAKBgQCSL8v6j6zXom7kaSaP43zrziJ5X2thOY6sQoYqqd0Al2qBXVARpikVg/AD8Nvh80mU6wu0WYanUvwtmp1Rt++ZNF2cXNPju/kvpYYtOA09kirA7K4trDLvd6o7XfvM0cxNQromUzK6JMt5krGnHb818+dl5uwWMXhKlmKMz4Dw/QIDAQABAoGBAIw3tlJuLx5iGjWSOj+3tyHDBcQfZzLJb3UBFgmkBmxD0A+nfl5/X1bYx4YwJ+gxYDmrvf1OBd9GtMXVUOKKKBD2mP1Wp/JtijQzoaj8+EzsON6UVSbTB3Lm5q5OjXa/Dx5zOFI1bA0ANllrV2w/8BKZjTmS0DTxO+TRHdotxy9BAkEA4t7offt+ZWOaagrP8RKNSmVMlKgI0yubtqOrPk1fHapjLA1/ihJ9LwzFe1cGlNfcQLXbE+wcFBYoXRELUae5DQJBAKT024/yshDXLvcIQeEPbRQKGuMRT14mDeW6fxRZPT48HE8ZeoPJcI+szWgxfh7x1sJW++/nNpjwW0qenv10O7ECQCdt0j5C/T6lxupzIpylOsUZQev8IDyDMbbWTyauz78aI84+MlJO0E7jC1daUpx/v5nHgWG/AUpEZ5N1KOByI+kCQQCY9yrnuJHhRfoaQAD/WBO5goleST4FO1qlzqRrVTmSjaFexGy06sbDpOWxmjuvLGoPOyRTWmBpwHGXp7IdrHxxAkEAxtXngGIpRHJb4ceXhTZ7KjiUw7IYfQjziCA8C6K51wLkI9jorpyM6px6GWXAzhPAGpoMJOTXHNFcXwwiHeq5AA==';
    public $public_key = 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDOm0xUSfrBO52mjq6fvRgRVGfK5Et+j4mYdkloAOmw83kG0Bnj3IbDbhI0G0XVeRpWOWVPdFWNaoFDOb2OTSErn4z02QF0MhOnYyTok9LxcUPZH329737269hwpFyoieI4s7j59NQWmJZyZkRxOpLaPhdpVt9CNcO7OmUX1n7RlwIDAQAB';

    public $outMemberNo = '1239437135082823681';
    public $contractNo = '1204677629409529856';
    public $notifyUrl = 'http://api.36qq.com/api/harry_t_call';

    public $t_root_url = 'https://api.lingxinpay.com/';
    public $notifyUrl_get = 'http://api.36qq.com/callback/harry_withdraw_callback_ele';

    public $md5_key = '0a4f736820de213cb9ec37371d8e2787';

    /**
     * 加密逻辑
     */
    public function check()
    {
        return 1;
    }

    /**
     * 合同逻辑
     * @param $name
     * @param $phone
     * @param $citizenship
     * @param $identityId
     * @param string $openBank
     * @param string $bankCode
     * @param string $ice
     * @param string $icePhone
     * @param string $iceRep
     * @param string $startTime
     * @param string $endTime
     * @return mixed
     */
    public function put($serialNo, $name, $phone, $citizenship, $identityId, $openBank = '', $bankCode = '', $ice = '', $icePhone = '', $iceRep = '', $startTime = '', $endTime = '')
    {
        $son_url = 'api/signContract';
        $real_url = $this->root_url . $son_url;
//        $serialNo = '';//订单号-----------------------需要生产

        $now_time = date('Y-m-d', time());

        //json内的参数
        /**
         *   jsonObject2.put("name", "何亮");
         * jsonObject2.put("phone", "17600220933");
         * jsonObject2.put("citizenship", "0");
         * jsonObject2.put("identityId", "130423199206192818");
         * jsonObject2.put("signTime", "2019-11-05");
         */
        $json_arr = [
            'name' => $name,
            'phone' => $phone,
            'citizenship' => $citizenship,
            'identityId' => $identityId,
            'signTime' => $now_time,
            //-------------------
            'openBank' => $openBank,
            'bankCode' => $bankCode,
            'ice' => $ice,
            'icePhone' => $icePhone,
            'iceRep' => $iceRep,
            'startTime' => $startTime,
            'endTime' => $endTime,
        ];
        $post_api_data_sign = [
            'outMemberNo' => $this->outMemberNo,
            'serialNo' => $serialNo,
            'contractNo' => $this->contractNo,
            'notifyUrl' => $this->notifyUrl,
            'contractSignInfo' => $json_arr,
        ];
        $post_api_data_sign_en = json_encode($post_api_data_sign);

//        $web_en = new Provider();
//        $sign = $web_en->publicKeyEncode($post_api_data_sign_en);

        $web_rsa = new RsaHarry();
        $sign = $web_rsa->public_encrypt($post_api_data_sign_en);

//        var_dump($sign);
//        $sign = 'dhwRpfW8gjqUEoVqn0+KeVp4fONm4pdwNj+lWw0lTr1t1K/r3KTFPPv0EqDlwUVCXc8T+sSarqvjk1KSeJUBrmnsiSoHYSCxtm8QDWqNsYjPhO0HboM3WKeEQUWpnCgbj3Byxa8nLMxMinT22UdoWOwKpA2YvFfk54sxnO8AqpSpx0b03iQ5ktuCWR/E+EELvRZky53OcGoIsGo/2oGsAlwMArPvlWY51sugzDsvEEWUlOWSEi/Be5PiwCFXQ52ctzzGoY6VqtLbvyP1AmIB7SBLNGtn/bq+PI/xb6uuyJgkXABDvkgobsG7hBQro53gGb5cBxTWEBHQFbJCUv1d/Xq9LDcR+IhPDifUyonQR/wjMK5dYRtFs+jCzbsa1jbIlvQZHCI2bhpCtM4AYDkmnY7cwA19ziAF8u+GoJ1gAR+nNOSdBHSnuVoC+5jm5FL9aXv+XR/q4f/tWWA0RnAzl0CF/xtZJCA15hEnYOsvChoJx1oRc1gOn/0VmFc7ixNZ';
        //所需参数
        /**
         *  jsonObject.put("outMemberNo", "1212668098747514881");
         * jsonObject.put("serialNo", "11");
         * jsonObject.put("contractNo", "1163708131004383232");
         * jsonObject.put("notifyUrl", "http://www.baidu.com");
         */
        $post_api_data = [
            'signType' => 'RSA',
            'service' => 'bpotop.zx.contract',
            'charset' => 'UTF-8',
            'version' => '1.0',
            'createTime' => $now_time,
            //---------------------------
            'outMemberNo' => $this->outMemberNo,
            'serialNo' => $serialNo,
            'contractNo' => $this->contractNo,
            'notifyUrl' => $this->notifyUrl,
            'contractSignInfo' => $json_arr,
            //---------------------------
            'sign' => $sign,
        ];


        $group_data = [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'json' => $post_api_data,
            'verify' => false
        ];
        $client = new Client();
        //发送post请求
        $res = $client->request('POST', $real_url, $group_data);
        $json_res = (string)$res->getBody();
        //dd($json_res);
        $arr_res = json_decode($json_res, true);
        return $arr_res;

    }

    /**
     * 获取合同结果逻辑
     */
    public function getPutResult($serialNo)
    {
        $son_url = 'api/queryContractInfo';
        $real_url = $this->root_url . $son_url;
        $now_time = date('Y-m-d', time());

        /**
         *  JSONObject jsonObject = new JSONObject();
         * jsonObject.put("outMemberNo", "");
         * jsonObject.put("serialNo", "");
         * jsonObject.put("contractNo", "");
         * try {
         * String encryptStr = RSA.encryptPub(JSONObject.toJSONString(jsonObject),publicKey);
         * jsonObject.put("sign", encryptStr);
         * } catch (Exception e) {
         * System.out.println("加密/发送失败：" + e);
         * }
         * jsonObject.put("signType", "RSA");
         * jsonObject.put("service", "bpotop.zx.contract");
         * jsonObject.put("charset", "UTF-8");
         * jsonObject.put("version", "1.0");
         * jsonObject.put("createTime", "2019-08-09");
         * String s = HttpUtils.doPost("http://39.107.15.64:8095/api/queryContractInfo",jsonObject.toJSONString());
         * logger.info("请求合同查询响应参数：{}",s);
         */
        $json_arr = [
            'outMemberNo' => $this->outMemberNo,
            'serialNo' => $serialNo,
            'contractNo' => $this->contractNo,
        ];

        $post_api_data_sign_en = json_encode($json_arr);
        $web_rsa = new RsaHarry();
        $sign = $web_rsa->public_encrypt($post_api_data_sign_en);

        $json_arr = [
            'outMemberNo' => $this->outMemberNo,
            'serialNo' => $serialNo,
            'contractNo' => $this->contractNo,
            //---------------------------------------
            'sign' => $sign,
            'signType' => 'RSA',
            'service' => 'bpotop.zx.contract',
            'charset' => 'UTF-8',
            'version' => '1.0',
            'createTime' => $now_time,
        ];

        $group_data = [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'json' => $json_arr,
            'verify' => false
        ];
        $client = new Client();
        //发送post请求
        $res = $client->request('POST', $real_url, $group_data);
        $json_res = (string)$res->getBody();
        //dd($json_res);
        $arr_res = json_decode($json_res, true);
        return $arr_res;

    }

    /**
     * 单笔提现逻辑
     * @param $name
     * @param $mobile
     * @param $certificateNo
     * @param $outerOrderNo
     * @param $predictAmount 打款金额，单位分，小心
     * @param $payAccount 打款账户
     * @return mixed
     */
    public function push($name, $mobile, $certificateNo, $outerOrderNo, $predictAmount, $payAccount, $certificateType = 'ID_CARD')
    {
        $son_url = 'bpotop_trade/single';
        $real_url = $this->t_root_url . $son_url;


//        $outerOrderNo = '';//订单号
        $projectName = '余额提现';//项目名称
        $cardType = 'DC';
        $salaryType = 0;
        $cardAttribute = 'C';
        $payType = 2;

        $json_arr = [];
        $json_arr['outMemberNo'] = $this->outMemberNo;
        $json_arr['outerOrderNo'] = $outerOrderNo;
        $json_arr['name'] = $name;
        $json_arr['certificateNo'] = $certificateNo;
        $json_arr['predictAmount'] = $predictAmount;
        //-----------------------------------------
        //升序字典
//        print_r($json_arr);
        ksort($json_arr);

//        print_r($json_arr);
        //组装器
        $md5_arr = urldecode(http_build_query($json_arr));
        $md5_k = $md5_arr . '&key=' . $this->md5_key;
        $md5_key = md5($md5_k);


//        $test = md5('certificateNo=130423199206192818&name=何亮&outMemberNo=1237264584692940801&outerOrderNo=0000000000&predictAmount=1&key=df1ec76efb7fb76ffdd42e036045a046');
//        $test_1 = md5('certificateNo=130423199206192818&name=何亮&outMemberNo=1237264584692940801&outerOrderNo=0000000000&predictAmount=1&key=df1ec76efb7fb76ffdd42e036045a046');
//        print_r($md5_key);
        //---------------------------------------------
        $json_arr['charset'] = 'UTF-8';
        $json_arr['mobile'] = $mobile;
        $json_arr['version'] = '1.1';
        $json_arr['service'] = 'bpotop.zx.pay.order';
        $json_arr['Md5Key'] = $md5_key;
        $json_arr['notifyUrl'] = $this->notifyUrl_get;
        $json_arr['cardType'] = $cardType;
        $json_arr['salaryType'] = $salaryType;
        $json_arr['projectName'] = $projectName;
        $json_arr['payType'] = $payType;
        $json_arr['cardAttribute'] = $cardAttribute;
        $json_arr['payAccount'] = $payAccount;
        $json_arr['certificateType'] = $certificateType;
        $json_arr['remark'] = '葡萄浏览器';

        $post_api_data_sign_en = json_encode($json_arr);
        $web_rsa = new RsaHarry();
//        print_r($post_api_data_sign_en);
        $sign = $web_rsa->public_encrypt($post_api_data_sign_en);

//        print_r($sign);
        $post_arr = [];
        $post_arr['outMemberNo'] = $this->outMemberNo;
        $post_arr['signType'] = 'RSA';
        $post_arr['sign'] = $sign;

//        print_r(json_encode($post_arr));
//
//        exit();
        $group_data = [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'json' => $post_arr,
            'verify' => false
        ];
        $client = new Client();
        //发送post请求
        $res = $client->request('POST', $real_url, $group_data);
        $json_res = (string)$res->getBody();
        //dd($json_res);
        $arr_res = json_decode($json_res, true);
        return $arr_res;

    }

    /**
     * 查询单笔提现
     */
    public function getPushResult($outerOrderNo)
    {
        $son_url = '/bpotop_trade/order_query';
        $real_url = $this->t_root_url . $son_url;
        /**
         *   Map<String, Object> requestMap = new HashMap<>();
         * //商户号
         * requestMap.put("outMemberNo", "");
         * //订单号
         * requestMap.put("outerOrderNo", "");
         * requestMap.put("service", "bpotop.zx.pay.order");
         * requestMap.put("version", "1.0");
         * requestMap.put("signType", "RSA");
         * requestMap.put("charset", "UTF-8");
         * String jsonStr = JSONObject.toJSONString(requestMap);
         * String encryptStr = RSA.encryptPub(jsonStr,publicKey);
         * Map<String, Object> requestMap2 = new HashMap<>();
         * requestMap2.put("outMemberNo", "");
         * requestMap2.put("sign", encryptStr);
         * logger.info("单笔查询请求参数：{}",JSONObject.toJSONString(requestMap2));
         * String resultJsonStr = HttpUtils.doPost("http://39.107.15.64:8090/bpotop_trade/order_query", JSONObject.toJSONString(requestMap2));
         * logger.info("单笔查询响应参数：{}",resultJsonStr);
         */


        $request_arr = [];
        $request_arr['outMemberNo'] = $this->outMemberNo;
        $request_arr['outerOrderNo'] = $outerOrderNo;
        $request_arr['service'] = 'bpotop.zx.pay.order';
        $request_arr['version'] = '1.0';
        $request_arr['signType'] = 'RSA';
        $request_arr['charset'] = 'UTF-8';
        //---------------------------------------
        $json_str = json_encode($request_arr);
        $web_rsa = new RsaHarry();
//        print_r($post_api_data_sign_en);
        $sign = $web_rsa->public_encrypt($json_str);

        $post_arr = [];
        $post_arr['outMemberNo'] = $this->outMemberNo;
        $post_arr['sign'] = $sign;

        $group_data = [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'json' => $post_arr,
            'verify' => false
        ];
        $client = new Client();
        //发送post请求
        $res = $client->request('POST', $real_url, $group_data);
        $json_res = (string)$res->getBody();
        //dd($json_res);
        $arr_res = json_decode($json_res, true);

        if ($arr_res['return_code'] == 'T') {
            $content = json_decode($arr_res['content'], true);
            $arr_res['data_json'] = $web_rsa->private_decrypt($content['sign']);
            $arr_res['data'] = json_decode($arr_res['data_json'], true);
        }

        return $arr_res;
    }


    /**
     * 单笔提现逻辑
     * @param $name
     * @param $mobile
     * @param $certificateNo
     * @param $outerOrderNo
     * @param $predictAmount 打款金额，单位分，小心
     * @param $payAccount 打款账户
     * @return mixed
     */
    public function cardPush($name, $mobile, $certificateNo, $outerOrderNo, $predictAmount, $payAccount)
    {
        $son_url = 'bpotop_trade/single';
        $real_url = $this->t_root_url . $son_url;


//        $outerOrderNo = '';//订单号
        $projectName = '余额提现（银行卡）';//项目名称
        $cardType = 'DC';
        $salaryType = 0;
        $cardAttribute = 'C';
        $payType = 1;

        $json_arr = [];
        $json_arr['outMemberNo'] = $this->outMemberNo;
        $json_arr['outerOrderNo'] = $outerOrderNo;
        $json_arr['name'] = $name;
        $json_arr['certificateNo'] = $certificateNo;
        $json_arr['predictAmount'] = $predictAmount;
        //-----------------------------------------
        //升序字典
//        print_r($json_arr);
        ksort($json_arr);

//        print_r($json_arr);
        //组装器
        $md5_arr = urldecode(http_build_query($json_arr));
        $md5_k = $md5_arr . '&key=' . $this->md5_key;
        $md5_key = md5($md5_k);


//        $test = md5('certificateNo=130423199206192818&name=何亮&outMemberNo=1237264584692940801&outerOrderNo=0000000000&predictAmount=1&key=df1ec76efb7fb76ffdd42e036045a046');
//        $test_1 = md5('certificateNo=130423199206192818&name=何亮&outMemberNo=1237264584692940801&outerOrderNo=0000000000&predictAmount=1&key=df1ec76efb7fb76ffdd42e036045a046');
//        print_r($md5_key);
        //---------------------------------------------
        $json_arr['charset'] = 'UTF-8';
        $json_arr['mobile'] = $mobile;
        $json_arr['version'] = '1.1';
        $json_arr['service'] = 'bpotop.zx.pay.order';
        $json_arr['Md5Key'] = $md5_key;
        $json_arr['notifyUrl'] = $this->notifyUrl_get;
        $json_arr['cardType'] = $cardType;
        $json_arr['salaryType'] = $salaryType;
        $json_arr['projectName'] = $projectName;
        $json_arr['payType'] = $payType;
        $json_arr['cardAttribute'] = $cardAttribute;
        $json_arr['payAccount'] = $payAccount;
        $json_arr['remark'] = '葡萄浏览器';

        $post_api_data_sign_en = json_encode($json_arr);
        $web_rsa = new RsaHarry();
//        print_r($post_api_data_sign_en);
        $sign = $web_rsa->public_encrypt($post_api_data_sign_en);

//        print_r($sign);
        $post_arr = [];
        $post_arr['outMemberNo'] = $this->outMemberNo;
        $post_arr['signType'] = 'RSA';
        $post_arr['sign'] = $sign;

//        print_r(json_encode($post_arr));
//
//        exit();
        $group_data = [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'json' => $post_arr,
            'verify' => false
        ];
        $client = new Client();
        //发送post请求
        $res = $client->request('POST', $real_url, $group_data);
        $json_res = (string)$res->getBody();
        //dd($json_res);
        $arr_res = json_decode($json_res, true);
        return $arr_res;

    }
}
