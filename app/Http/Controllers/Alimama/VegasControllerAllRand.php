<?php

namespace App\Http\Controllers\Alimama;

use App\Entitys\App\AlimamaInfo;
use App\Entitys\App\AlimamaInfoNew;
use App\Exceptions\ApiException;
use App\Services\TbkCashCreate\TbkCashCreateServices;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class VegasControllerAllRand extends Controller
{

    /*
     * 阿里妈妈 - 状态校验
     */
    public function statusVerify(Request $request)
    {
        try {
            //仅用于测试兼容旧版
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            //仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);
            if (!$arrRequest || empty($arrRequest['app_id'])) {
                throw new ApiException('传入参数错误', '3001');
            }

            $app_id = $arrRequest['app_id'];

            $obj_alimama_info = new AlimamaInfoNew();

            $info_exists = $obj_alimama_info->where('app_id', $app_id)->exists();

            if ($info_exists) { //不允许用户重复绑定
                return $this->getInfoResponse(1001, '已绑定淘宝账号');
            }
            //
            $list_client_id = [
//                    25626319,   //wxl
                25620531,   //xlh 0
                25821858,   //hxh 1
                25842871,   //lsq 2
                25811684,   //agql 3
            ];
            $account = array_rand($list_client_id);
            $client_id = $list_client_id[$account];

            $url = 'https://oauth.m.taobao.com/authorize?response_type=code&client_id=' . $client_id . '&redirect_uri=http://api.36qq.com/taobao_authorisation_all_rand?account=' . $account . '&view=wap&state=' . $app_id;

            return $this->getResponse($url);

        } catch (\Throwable $e) {
            //判断是否正常抛出异常
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
            //仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = $request->all();


            $rules = [
                'state' => 'required',
                'code' => 'required',
                'account' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                return 101;
            }


            $app_id = $arrRequest['state'];
            $taobao_code = $arrRequest['code'];
            $num_bound = $arrRequest['account'];
            $redirect_uri = $request->fullUrl();

            $obj_alimama_info = new AlimamaInfoNew();

            $alimama_list = [
                [ //徐丽花
                    'valid_account' => 'xlh',
                    'client_id' => '25620531',
                    'inviter_code' => 'WU4BE8',
                    'taobao_adzone' => '109467850460',
                ],
                [ //hxh 黄雪惠
                    'valid_account' => 'hxh',
                    'client_id' => '25821858',
                    'inviter_code' => 'GD3PHJ',
                    'taobao_adzone' => '109467900491',
                ],
                [ // 卢淑清
                    'valid_account' => 'lsq',
                    'client_id' => '25842871',
                    'inviter_code' => '9EJ5CI',
                    'taobao_adzone' => '109469450037',
                ],
                [ //爱隔千里
                    'valid_account' => 'agql',
                    'client_id' => '25811684',
                    'inviter_code' => 'TAPI28',
                    'taobao_adzone' => '109551050001',
                ],
            ];

            $alimama_info = @$alimama_list[$num_bound];

            if (empty($alimama_info)) {
                return redirect('https://a119112.oss-cn-beijing.aliyuncs.com/静态网页/kaifazhong/app-h5/pages/error/index.html#/?msg=恶意篡改数据，账号已被标记！！');
            }


            //兑换淘宝token
            $resq_taobao = $tbkCashCreateServices->getAccessToken($taobao_code, $alimama_info['valid_account']);


//        dd($resq_taobao);

            if (empty(@$resq_taobao['token_result'])) {

                if (in_array($app_id, [3675700, 4024688, 4693063, 8343202])) {
                    dd($resq_taobao, $num_bound);
                }


//                return 103;
                return redirect('https://a119112.oss-cn-beijing.aliyuncs.com/静态网页/kaifazhong/app-h5/pages/error/index.html#/?msg=淘宝不允许生成Token');
            }

            $arr_token_result = json_decode($resq_taobao['token_result'], true);


            $taobao_user_id = $arr_token_result['taobao_user_id'];

            $num_now_bound = $obj_alimama_info->where('taobao_user_id', $taobao_user_id)->count();

            if ($num_now_bound >= 4) {
                return redirect('https://a119112.oss-cn-beijing.aliyuncs.com/静态网页/kaifazhong/app-h5/pages/error/index.html#/?msg=该淘宝账号绑定我的浏览器id数已达上限，请更换其他淘宝账号重试。错误码:' . $taobao_user_id);
            }


            $resq_auth = @$tbkCashCreateServices->publisherInfoSave($alimama_info['inviter_code'], $arr_token_result['access_token'], $alimama_info['valid_account']);

            if (empty(@$resq_auth['data'])) {
//                return empty(@$resq_auth['sub_msg']) ? '绑定渠道失败' : $resq_auth['sub_msg'];

                $error_msg = empty(@$resq_auth['sub_msg']) ? '绑定渠道失败' : $resq_auth['sub_msg'];

                return redirect('https://a119112.oss-cn-beijing.aliyuncs.com/静态网页/kaifazhong/app-h5/pages/error/index.html#/?msg=' . $error_msg);
            }


            $relation_id = @$resq_auth['data']['relation_id'];
            $taobao_adzone = $alimama_info['taobao_adzone'];

            $relation_id_exists = $obj_alimama_info->where(['relation_id' => $relation_id, 'adzone_id' => $taobao_adzone])->exists();

            if ($relation_id_exists) { //有值，则说明被其他账号绑定 再随机一个账号给用户
                //todo 临时处理
                $list_client_id = [
//                    25626319,   //wxl
                    25620531,   //xlh 0
                    25821858,   //hxh 1
                    25842871,   //lsq 2
                    25811684,   //agql 3
                ];
                $account = array_rand($list_client_id);
                $client_id = $list_client_id[$account];

                //回炉重造
                $remake_url = 'https://oauth.m.taobao.com/authorize?response_type=code&client_id=' . $client_id . '&redirect_uri=http://api.36qq.com/taobao_authorisation_all_rand?account=' . $account . '&view=wap&state=' . $app_id;

                return redirect($remake_url);
            }


            //创建私域用户
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
                'adzone_id' => $taobao_adzone
            ];

            $obj_alimama_info->create($params);

//            return 200;
            return redirect('http://a119112.oss-cn-beijing.aliyuncs.com/%E9%9D%99%E6%80%81%E7%BD%91%E9%A1%B5/kaifazhong/app-h5/pages/authorize.html', 301);
            //http://a119112.oss-cn-beijing.aliyuncs.com/%E9%9D%99%E6%80%81%E7%BD%91%E9%A1%B5/kaifazhong/app-h5/pages/authorize.html
        } catch (\Throwable $e) {

//            return '500:' . $e->getMessage();
            return redirect('https://a119112.oss-cn-beijing.aliyuncs.com/静态网页/kaifazhong/app-h5/pages/error/index.html#/?msg=500:' . $e->getMessage());
        }
    }

}
