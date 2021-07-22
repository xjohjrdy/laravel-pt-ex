<?php

namespace App\Http\Controllers\Jd;

use App\Entitys\App\AppUserInfo;
use App\Entitys\App\JdTestWh;
use App\Exceptions\ApiException;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class GetController extends Controller
{
    /**
     * 下单行为
     */
    public function order(Request $request, AppUserInfo $appUserInfo, Client $client)
    {
        try {
            $all = $request->all();
            $rules = [
                'app_id' => 'required',
                'type' => 'required',
            ];
            $validator = Validator::make($all, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            if ($all['type'] == 2) {
                $user = $appUserInfo->getUserById($all['app_id']);
                $all['app_id'] = $user->parent_id;
            }

            $url = "apimd.haojingke.com/api/index/getunionurlzdy?positionId=584021***" . $all['app_id'];
            $obj_res = $client->request('POST', $url, ['verify' => false]);
            $json_res = json_decode((string)$obj_res->getBody(), true);
            $res_url = @$json_res['data'];

            return $this->getResponse($res_url);
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * 获取haojingke商品列表
     */
    public function getHaoJinKeGoodsList(Request $request, Client $client)
    {
        try {
            //仅用于测试兼容旧版-----------------线上可删除
            $url = "https://api-gw.haojingke.com/index.php/v1/api/jd/getjingfen?apikey=584831d33eaca654&eliteId=22";
            $obj_res = $client->request('POST', $url, ['verify' => false]);
            $json_res = json_decode((string)$obj_res->getBody(), true);
            $res_url = @$json_res['data'];
            foreach ($res_url['data'] as $key=>$item){
                $res_url['data'][$key]['tkmoney_general'] = round($item['commission'] * 0.2, 2);
                $res_url['data'][$key]['tkmoney_vip'] = round($item['commission'] * 0.325,  2);
                unset($res_url['data'][$key]['commission']);
            }
            return $this->getResponse($res_url);
        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }
    /**
     * 生成专属链接
     */
    public function getUrl(Request $request)
    {
        try {
            $all = $request->all();
            $rules = [
                'app_id' => 'required',
            ];
            $validator = Validator::make($all, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $url = "http://api.36qq.com/jd_shop?app_id=" . $all['app_id'];

            return $this->getResponse($url);

        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * 我的推广订单
     */
    public function myOrders(Request $request, JdTestWh $jdTestWh)
    {
        try {
            $all = $request->all();
            $rules = [
                'app_id' => 'required',
            ];
            $validator = Validator::make($all, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $res = $jdTestWh->getMyOrders($all['app_id']);
            $count = $jdTestWh->where(['positionId' => $all['app_id']])->count();
            foreach ($res as $re) {
                if ($count > 50) {
                    $re->money = 5;
                } else {
                    $re->money = 3;
                }

            }

            return $this->getResponse($res);

        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * 活动规则
     */
    public function regular(Request $request)
    {
        try {
            $all = $request->all();
            $rules = [
                'app_id' => 'required',
            ];
            $validator = Validator::make($all, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $url = "http://xin_new.36qq.com/mobile/notice/details?id=63&from=singlemessage&isappinstalled=0";

            return $this->getResponse($url);
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
     * 京东推广界面
     */
    public function jdShopWeb(Request $request)
    {
        $idol = $request->get('app_id');

        return view('jd.jd_shop', [
            'parent_id' => $idol,
        ]);
    }

}
