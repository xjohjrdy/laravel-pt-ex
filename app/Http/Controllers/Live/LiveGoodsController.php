<?php

namespace App\Http\Controllers\Live;

use App\Entitys\App\LiveInfo;
use App\Entitys\App\LiveShopGoods;
use App\Entitys\App\ShopGoods;
use App\Entitys\Other\ThreeUser;
use App\Exceptions\ApiException;
use App\Services\Live\LiveServices;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class LiveGoodsController extends Controller
{
    //


    public function liveGoodsList(Request $request)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'live_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $live_id = $arrRequest['live_id'];

            //直播数据
            $liveInfo = new LiveInfo();
            $live_ing = $liveInfo->where('id', $live_id)
                ->where('start_time', '<=', time())
                ->where('end_time', 0)
                ->first();

            $good_status = empty($live_ing) ? 0 : 1;

            $goodsModel = new ShopGoods();
            $liveGoodsModel = new LiveShopGoods();
            $goods_list = $liveGoodsModel->leftJoin($goodsModel->getTable(), $liveGoodsModel->getTable() . '.good_id', "=", $goodsModel->getTable() . '.id')
                ->where([
                    $goodsModel->getTable() . '.status' => 1,
                    $liveGoodsModel->getTable() . '.live_id' => $live_id
                ])->get(['good_id as goods_id', 'sale_volume', 'cost_price', 'volume', 'price', 'vip_price', 'title', 'header_img', 'real_sale_volume']);
            foreach ($goods_list as $key => $item) {
                $image = empty(json_decode($item['header_img'], true)[0]) ? '' : json_decode($item['header_img'], true)[0];
                $goods_list[$key]['header_img'] = $image;
                $goods_list[$key]['good_status'] = $good_status;
            }
            return $this->getResponse($goods_list);
        } catch (\Exception $e) {
            if (!empty($e->getMessage())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('服务器异常', '500');
        }
    }

    public function liveGoodsCount(Request $request)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'live_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $live_id = $arrRequest['live_id'];
            $goodsModel = new ShopGoods();
            $liveGoodsModel = new LiveShopGoods();
            $count = $liveGoodsModel->leftJoin($goodsModel->getTable(), $liveGoodsModel->getTable() . '.good_id', "=", $goodsModel->getTable() . '.id')
                ->where([
                    $goodsModel->getTable() . '.status' => 1,
                    $liveGoodsModel->getTable() . '.live_id' => $live_id
                ])->count($liveGoodsModel->getTable() . '.id');
            return $this->getResponse($count);
        } catch (\Exception $e) {
            if (!empty($e->getMessage())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('服务器异常', '500');
        }
    }

    public function explainGoodsInfo(Request $request)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'live_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $live_id = $arrRequest['live_id'];
            $goodsModel = new ShopGoods();
            $liveGoodsModel = new LiveShopGoods();
            $count = $liveGoodsModel->leftJoin($goodsModel->getTable(), $liveGoodsModel->getTable() . '.good_id', "=", $goodsModel->getTable() . '.id')
                ->where([
                    $goodsModel->getTable() . '.status' => 1,
                    $liveGoodsModel->getTable() . '.live_id' => $live_id,
                    $liveGoodsModel->getTable() . '.read_is' => 1,
                ])->first(['good_id as goods_id', 'sale_volume', 'cost_price', 'volume', 'price', 'vip_price', 'title', 'header_img', 'real_sale_volume']);
            $image = empty(json_decode($count['header_img'], true)[0]) ? '' : json_decode($count['header_img'], true)[0];
            $count['header_img'] = $image;
            return $this->getResponse($count);
        } catch (\Exception $e) {
            if (!empty($e->getMessage())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('服务器异常', '500');
        }
    }

    public function setExplainGoods(Request $request)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'live_id' => 'required',
                'goods_id' => 'required',
                'group_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $live_id = $arrRequest['live_id'];
            $goods_id = $arrRequest['goods_id'];
            $group_id = $arrRequest['group_id'];

            //发起通知
            $liveServices = new LiveServices();
            //推送关联商品的自定义消息体
            $data_msg = [
                'cmd' => 'CustomCmdMsg',
                'data' => [
                    'cmd' => 'sendGoods',
                    'msg' => '1',
                    'userAvatar' => '',
                    'userName' => '',
                ]
            ];
            $informInfo = $liveServices->sendGroupInform($group_id, json_encode($data_msg));
            $arr_informInfo = json_decode($informInfo, true);
            if ($arr_informInfo['ErrorCode'] != 0) {
                return $this->getInfoResponse($arr_informInfo['ErrorCode'], $arr_informInfo['ErrorInfo']);//错误返回数据
            }

            $liveGoodsModel = new LiveShopGoods();
            $liveGoodsModel->where([
                'live_id' => $live_id,
                'read_is' => 1,
            ])->update(['read_is' => 0]);
            $liveGoodsModel->where([
                'live_id' => $live_id,
                'good_id' => $goods_id,
            ])->update(['read_is' => 1]);

            return $this->getResponse('设置成功');
        } catch (\Exception $e) {
            if (!empty($e->getMessage())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('服务器异常', '500');
        }
    }
}
