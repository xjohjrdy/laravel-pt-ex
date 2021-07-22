<?php

namespace App\Http\Controllers\OneGo;

use App\Entitys\App\JdNewActiveOrders;
use App\Exceptions\ApiException;
use App\Services\OneGo\OneGoService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class OnGoController extends Controller
{
    //
    public function getCategoryList(Request $request, OneGoService $oneGoService)
    {
        try {
//            $data_arr = json_decode($request->data, true);
//            $rules = [
//                'app_id' => 'required',
//            ];
//
//            $validator = Validator::make($data_arr, $rules);
//            if ($validator->fails()) {
//                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
//            }
            $cache_key = 'ONE_GO_CATEGORY_LIST';
            $result = [];
            if (Cache::has($cache_key)) {
                $data = Cache::get($cache_key);
                return $this->getResponse($data);
            } else {
                $result = $oneGoService->getCategoryList();
                if ((int)$result['status_code'] == 200) {
                    Cache::put($cache_key, $result['data'], 10);
                    return $this->getResponse($result['data']);
                } else {
                    return $this->getInfoResponse($result['status_code'], $result['message']);
                }
            }


        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            if (!empty($e->getMessage())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    public function getGoodsListFromCid(Request $request, OneGoService $oneGoService)
    {
        try {
//            if ($request->header('data')) {
//                $request->data = $request->header('data');
//            }
            $data_arr = json_decode($request->data, true);
            $rules = [
                'pagesize' => 'integer',
                'pageindex' => 'integer',
                'cid' => 'required', // 分类ID 测试：4726
            ];
            $validator = Validator::make($data_arr, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $cache_key = 'ONE_GO_GOODS_LIST_' . $data_arr['pageindex'] . '_' . $data_arr['pagesize'] . '_' . $data_arr['cid'];
            if (Cache::has($cache_key)) {
                $data = Cache::get($cache_key);
                return $this->getResponse($data);
            } else {
                $result = $oneGoService->getGoodsListFromCid($data_arr);
                if ((int)$result['status_code'] == 200) {
                    Cache::put($cache_key, $result['data'], 1);
                    return $this->getResponse($result['data']);
                } else {
                    return $this->getInfoResponse($result['status_code'], $result['message']);
                }
            }

        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            if (!empty($e->getMessage())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    public function getUnionUrlApi(Request $request, OneGoService $oneGoService)
    {
        try {
//            if ($request->header('data')) {
//                $request->data = $request->header('data');
//            }
            $data_arr = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required|numeric',
                'goods_id' => 'required',
            ];
            $validator = Validator::make($data_arr, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $data_arr['subunionid'] = $data_arr['app_id']; //
            $result = $oneGoService->getUnionUrlApi($data_arr);
            if ((int)$result['status_code'] == 200) {
                return $this->getResponse($result['data']);
            } else {
                return $this->getInfoResponse($result['status_code'], $result['message']);
            }
        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            if (!empty($e->getMessage())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * 获取所有订单
     */
    public function getOrders(Request $request, OneGoService $oneGoService)
    {
        //仅用于测试兼容旧版-----------------线上可删除
//        if ($request->header('data')) {
//            $request->data = $request->header('data');
//        }
        $arrRequest = json_decode($request->data, true);
        $rules = [
            'app_id' => 'required|numeric',
        ];
        $validator = Validator::make($arrRequest, $rules);
        if ($validator->fails()) {
            throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
        }
        //{ "data": {
        // "data":
        // [
        // {
        // "orderid": "94868558030",[订单id]
        // "ordertime": 1557219530,[下单时间]
        // "finishtime": 0,[完成时间]
        // "pid": 1,[福利公会会员id]
        // "goods_id": "42795596254",[商品id]
        // "goods_name": "运动轴承跳绳健身器材男女学生训练中考负重跳神 ",[商品名称]
        // "goods_num": "1",[数量]
        // "goods_frozennum": "0",[售后数量]
        // "goods_returnnum": "0",[退货数量]
        // "cosprice": "1.00",[计佣金额]
        // "subunionid": "12345",[自定义推广位]
        // "yn": 1,[状态码 0无效1有效]
        // "ynstatus": "已付款" [订单状态描述]
        // }
        // ],
        // "total": 1
        // },
        // "message": "success", "status_code": 200
        // }
        $params = [
            'subunionid' => $arrRequest['app_id']
        ];
        $json_res = $oneGoService->getHdOrders($params);
        $params['yn'] = 2;
        $json_res_h = $oneGoService->getHdOrders($params);
        if ($arrRequest['app_id'] == 'undefined' || empty($arrRequest['app_id'])) {
            $json_res['data']['data'] = [];
            $json_res['data']['total'] = 0;
            $json_res_h['data']['total'] = 0;
        }
//        foreach ($json_res['data']['data'] as &$iteam){
//            $iteam['ordertime'] = @$iteam['ordertime'] * 1000;
//        }
        return $this->getResponse(
            [
                'can' => $json_res['data']['total'],
                'can_use' => $json_res_h['data']['total'],
                'money' => $json_res_h['data']['total'] * 9.89,
                'get_money' => 0,
                'orders' => $json_res['data']['data']
            ]
        );
    }

    /**
     * 校验订单
     */
    public function checkOrders(Request $request, OneGoService $oneGoService, JdNewActiveOrders $jdNewActiveOrders)
    {
        //仅用于测试兼容旧版-----------------线上可删除
//        if ($request->header('data')) {
//            $request->data = $request->header('data');
//        }
        $arrRequest = json_decode($request->data, true);
        $rules = [
            'app_id' => 'required|numeric',
            'orderid' => 'required',
        ];
        $validator = Validator::make($arrRequest, $rules);
        if ($validator->fails()) {
            throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
        }
        $json_res = $oneGoService->getHdOrdersByOrdersId($arrRequest['orderid'], $arrRequest['app_id']);
        if (!empty($json_res['data']['total'])) {
            if ($json_res['data']['total'] > 0) {
                $jdNewActiveOrders->addInfo([
                    'app_id' => $arrRequest['app_id'],
                    'orders' => $arrRequest['orderid'],
                ]);
                return $this->getResponse("提交成功");
            }
        }

        return $this->getResponse("订单未找到，需要等一段时间再提交哦！");
    }
}
