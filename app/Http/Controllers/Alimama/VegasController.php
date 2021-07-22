<?php

namespace App\Http\Controllers\Alimama;

use App\Entitys\App\AlimamaInfo;
use App\Exceptions\ApiException;
use App\Services\TbkCashCreate\TbkCashCreateServices;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class VegasController extends Controller
{

    /*
     * 阿里妈妈 - 状态校验
     */
    public function statusVerify(Request $request)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);
            if (!$arrRequest || empty($arrRequest['app_id'])) {
                throw new ApiException('传入参数错误', '3001');
            }

            $app_id = $arrRequest['app_id'];

            $obj_alimama_info = new AlimamaInfo();

            $info_exists = $obj_alimama_info->where('app_id', $app_id)->exists();

            if ($info_exists) {
                return $this->getInfoResponse(1001, '已绑定淘宝账号');
            }

            $url = 'https://oauth.m.taobao.com/authorize?response_type=code&client_id=25626319&redirect_uri=http://api.36qq.com/taobao_authorisation&view=wap&state=' . $app_id;

            return $this->getResponse($url);

        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }

    }


    /*
     * 阿里妈妈 - 用户信息存储
     */
    public function authorisation(Request $request, TbkCashCreateServices $tbkCashCreateServices)
    {
        try {
            $arrRequest = $request->all();


            $rules = [
                'state' => 'required',
                'code' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                return 101;
            }
            /*
             array:5 [▼
              "code" => 15
              "msg" => "Remote service error"
              "sub_code" => "1"
              "sub_msg" => "授权用户未入驻！"
              "request_id" => "8dq4hgskf7vq"
            ]


             array:2 [▼
              "data" => array:1 [▼
                "inviter_code" => "N9BT8V"
              ]
              "request_id" => "4qqtnd0twgru"
            ]

            array:5 [▼
              "code" => 27
              "msg" => "Invalid session"
              "sub_code" => "invalid-sessionkey"
              "sub_msg" => "SessionKey非法"
              "request_id" => "saelco6piee9"
            ]
            6200308af638bff3ZZd946c39118a63edbf9c2b70636eaf324822964
            6201e2495c7e5a973c15ee272de362f2ZZ1e81678b7d5b22006684494

            62027108043fa19610ZZ01d21306f3f5d5bc703b5cd513b3495566616

            620252495ef0e1ce72e9bb8946d2ded7ZZac2391f4656bf2698835154

             */

            /*
             array:5 [▼
              "code" => 15
              "msg" => "Remote service error"
              "sub_code" => "1"
              "sub_msg" => "很抱歉，您已经是合作方，无法成为其他合作方的渠道，快去邀请自己的渠道吧"
              "request_id" => "amay487yn8bd"
            ]

            array:5 [▼
              "code" => 27
              "msg" => "Invalid session"
              "sub_code" => "invalid-sessionkey"
              "sub_msg" => "SessionKey非法"
              "request_id" => "3qxgnn3uj6mg"
            ]


            array:2 [▼
              "data" => array:3 [▼
                "account_name" => "暴**六"
                "desc" => "绑定成功"
                "relation_id" => "2240018988"
              ]
              "request_id" => "7rpzo5suea3z"
            ]

            array:2 [▼
              "data" => array:3 [▼
                "account_name" => "暴**六"
                "desc" => "重复绑定渠道"
                "relation_id" => "2240018988"
              ]
              "request_id" => "6bwl5ts731gq"
            ]

            array:2 [▼
              "data" => array:3 [▼
                "account_name" => "嘎**们"
                "desc" => "绑定成功"
                "relation_id" => "2240096333"
              ]
              "request_id" => "5zc834hhibfs"
            ]

             */

            $app_id = $arrRequest['state'];
            $taobao_code = $arrRequest['code'];
            $redirect_uri = $request->fullUrl();

            $obj_alimama_info = new AlimamaInfo();

            $info_exists = $obj_alimama_info->where('app_id', $app_id)->exists();

            if ($info_exists) {
                return redirect('https://a119112.oss-cn-beijing.aliyuncs.com/静态网页/kaifazhong/app-h5/pages/error/index.html#/?msg=不允许重复绑定');
            }

            $resq_taobao = $tbkCashCreateServices->getAccessToken($taobao_code);

            if (empty(@$resq_taobao['token_result'])) {
                return redirect('https://a119112.oss-cn-beijing.aliyuncs.com/静态网页/kaifazhong/app-h5/pages/error/index.html#/?msg=淘宝不允许生成Token');
            }

            $arr_token_result = json_decode($resq_taobao['token_result'], true);

            /*
             array:2 [▼
                  "token_result" => "{"w1_expires_in":2592000,"refresh_token_valid_time":1569488815957,"taobao_user_nick":"woxiaoli675015017","re_expires_in":2592000,"expire_time":1569488815957,"token_type":"Bearer","access_token":"6200308af638bff3ZZd946c39118a63edbf9c2b70636eaf324822964","taobao_open_uid":"AAE139MmAHpuSQaL2iqPLiL2","w1_valid":1569488815957,"refresh_token":"6202708b6dde3fc2ZZ6996e594015f51fb656a5715848c0324822964","w2_expires_in":300,"w2_valid":1566897115957,"r1_expires_in":2592000,"r2_expires_in":86400,"r2_valid":1566983215957,"r1_valid":1569488815957,"taobao_user_id":"324822964","expires_in":2592000} ◀"
                  "request_id" => "148oi7aajlmto"
                ]
             *//*
         {
            "w1_expires_in": 2592000,
            "refresh_token_valid_time": 1569488815957,
            "taobao_user_nick": "woxiaoli675015017",
            "re_expires_in": 2592000,
            "expire_time": 1569488815957,
            "token_type": "Bearer",
            "access_token": "6200308af638bff3ZZd946c39118a63edbf9c2b70636eaf324822964",
            "taobao_open_uid": "AAE139MmAHpuSQaL2iqPLiL2",
            "w1_valid": 1569488815957,
            "refresh_token": "6202708b6dde3fc2ZZ6996e594015f51fb656a5715848c0324822964",
            "w2_expires_in": 300,
            "w2_valid": 1566897115957,
            "r1_expires_in": 2592000,
            "r2_expires_in": 86400,
            "r2_valid": 1566983215957,
            "r1_valid": 1569488815957,
            "taobao_user_id": "324822964",
            "expires_in": 2592000
        }
         */;

            $resq_auth = @$tbkCashCreateServices->publisherInfoSave('N9BT8V', $arr_token_result['access_token']);

            if (empty(@$resq_auth['data'])) {

                $error_msg = empty(@$resq_auth['sub_msg']) ? '绑定渠道失败' : $resq_auth['sub_msg'];

                return redirect('https://a119112.oss-cn-beijing.aliyuncs.com/静态网页/kaifazhong/app-h5/pages/error/index.html#/?msg=' . $error_msg);
            }

            $relation_id = @$resq_auth['data']['relation_id'];
            $relation_id_exists = $obj_alimama_info->where('relation_id', $relation_id)->exists();

            if ($relation_id_exists) {
                return redirect('https://a119112.oss-cn-beijing.aliyuncs.com/静态网页/kaifazhong/app-h5/pages/error/index.html#/?msg=淘宝账号已被其他浏览器账户绑定,请勿多账号绑定！！错误码:' . $relation_id);
            }
            @$params = [
                'app_id' => $app_id,
                'grant_type' => 'authorization_code',
                'code' => $taobao_code,
                'redirect_uri' => $redirect_uri,
                'access_token' => $arr_token_result['access_token'],
                'token_type' => $arr_token_result['token_type'],
                'expires_in' => $arr_token_result['expires_in'],
                'refresh_token' => $arr_token_result['refresh_token'],
                're_expires_in' => $arr_token_result['re_expires_in'],
                'r1_expires_in' => $arr_token_result['r1_expires_in'],
                'r2_expires_in' => $arr_token_result['r2_expires_in'],
                'w1_expires_in' => $arr_token_result['w1_expires_in'],
                'w2_expires_in' => $arr_token_result['w2_expires_in'],
                'taobao_user_nick' => $arr_token_result['taobao_user_nick'],
                'taobao_user_id' => $arr_token_result['taobao_user_id'],
                'relation_id' => $resq_auth['data']['relation_id'],
                'account_name' => $resq_auth['data']['account_name'],
            ];

            $obj_alimama_info->create($params);
            return redirect('http://a119112.oss-cn-beijing.aliyuncs.com/%E9%9D%99%E6%80%81%E7%BD%91%E9%A1%B5/kaifazhong/app-h5/pages/authorize.html', 301);
        } catch (\Throwable $e) {
            return redirect('https://a119112.oss-cn-beijing.aliyuncs.com/静态网页/kaifazhong/app-h5/pages/error/index.html#/?msg=500:' . $e->getMessage());
        }
    }

}
