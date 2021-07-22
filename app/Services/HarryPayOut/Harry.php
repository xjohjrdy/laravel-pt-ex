<?php

namespace App\Services\HarryPayOut;

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
    public $private_key = 'MIICWwIBAAKBgQCgzOPHghx8uAImOhySJb0ZmqJXiWeA1TsaK8MV7/b3Q7InsNdmsT1aC5XZh0sLoJsNRA+cnQXVUp6q8cDOaf5KUBWsWUihlb/0BpXnuY6o3Li7gPeyidEfFAXQgKKyLkovv714GP0CbV9Wzg4PnAYkaIgZFunGzHvMxqCcfj9/PQIDAQABAoGAChizWKyXw1D+eY3+i0KpW/k0plBvWkyJOHx09GSr2hy7C/jznXQViRjfINh44tMDyVJztH67hgh5A/zIAW3wVHqbMV5RgminPEpWWVl15LSKRa2XBWznJ7lJsOkti6d2O4Du/S/vPQmCOnrMJWknvZkOPfiWx0uUwgiV/LLB4dUCQQDeNNF0sKS7SA9r/d85HnqnQnKNJOSXX/Af3qndKyaoE9z1GSpaLThqTpbVWSmm4reiAruXh11HBIjCCZ4Pq0CXAkEAuUFYtwAVmnmTgbtTmk2UpOJVJ4oz6pE+2i9ZuObKG8aIGK++OnYk6yjjfhx3o1IpOf7M3dZBzt8+tWS1mShlSwJAI7IYc8ZssClDUPXXhjV/Pp9OB56FmkuvJ299min0a8vFExqX0ySwi2NUl7FbH5QMK9qEiDMWqPHxhjpFSf8YwQJAa1HN4QXtffXcXBV3UzaKXBK6HhPUC5lk/eTcZ19bykdy5Eo7O4bh0FF5qL85F6YrN+vCJulOalet7kuPYFCkjQJAA8j9/AiirnloXSdM1bHf/BpENB8kpfYxmsX6A7Wd9APtodMVlGpEvtkqk8JlWPPSyOKXpX5zHQVBuKDJECNWDQ==';
    public $public_key = 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCBB4QGIEQ73gAh1b0+VJvam1AbG2ilm75yqA1M/9bZVnjTxZOVHof6ZgtYM/pv+zgrRby5EWiYcjXQ6TRTuMdPella/Dp9vpzAn/wFHrwIiYkjfpkUkMD8B28LOpYd5y+aTjeiI9iWhlkMFQIQYeFguyTfe/H+tvzgfO/WMpubQQIDAQAB';

    public $outMemberNo = '1239451943400300544';
    public $contractNo = '1204677629409529856';
    public $notifyUrl = 'http://pt.qmshidai.com/api/out_harry_t_call';

    public $t_root_url = 'https://api.lingxinpay.com/';
//    public $notifyUrl_get = 'http://chenzhenghang.imwork.net/api/harry_withdraw_callback';
    public $notifyUrl_get = 'http://pt.qmshidai.com/callback/harry_withdraw_callback';

    public $md5_key = '0a05f7101843fa057aa49c817518f040';

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
        $json_arr['certificateType'] = $certificateType;
        $json_arr['payAccount'] = $payAccount;

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
     *
     * 银行卡打款逻辑
     * @param $name
     * @param $mobile
     * @param $certificateNo
     * @param $outerOrderNo
     * @param $predictAmount 打款金额，单位分，小心
     * @param $payAccount 打款账户（银行卡）
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
