<?php

namespace App\Http\Controllers\Admin;

use App\Entitys\App\ShopGoods;
use App\Entitys\App\ShopSupplierGoods;
use App\Entitys\App\ShopSupplierGoodsArea;
use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

/**
 * 供应商后台接口信息
 * Class GoodsController
 * @package App\Http\Controllers\Admin
 */
class GoodsController extends Controller
{
    /**
     * 分页
     * 获取供应商上线的商品贩卖信息
     */
    public function getNeedShow(Request $request, ShopGoods $shopGoods)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'supplier_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $goods = $shopGoods->getBySupplier($arrRequest['supplier_id']);

            return $this->getResponse($goods);
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络连接错误，错误信息：' . $e->getLine() . '/' . $e->getMessage(), '500');
        }
    }

    /**
     * 分页
     * 获取供应商的商品
     */
    public function getSupplierGoods(Request $request, ShopSupplierGoods $shopSupplierGoods)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'supplier_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $goods = $shopSupplierGoods->getAllByAppId($arrRequest['supplier_id']);

            return $this->getResponse($goods);
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络连接错误，错误信息：' . $e->getLine() . '/' . $e->getMessage(), '500');
        }
    }

    /**
     * 分页<可不分页>
     * 获取对应商品的订单信息
     */
    public function getSupplierGoodsOrders(Request $request, ShopGoods $shopGoods)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'supplier_id' => 'required',
                'goods_id' => 'required',
                'is_page' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            if (empty($arrRequest['status'])) {
                $status = 1;
            } else {
                $status = $arrRequest['status'];
            }

            $goods = $shopGoods->getSupplierGoods($arrRequest['goods_id'], $arrRequest['supplier_id']);
            if (empty($goods)) {
                return $this->getInfoResponse('4004', '该商品不存在！请核对商品是否归属该供应商！');
            }

            return $this->getResponse($goods);
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络连接错误，错误信息：' . $e->getLine() . '/' . $e->getMessage(), '500');
        }
    }


    /**
     * 上传供应商提供的商品
     * (发起审核)/(审核失败修改审核)/(审核成功修改参数再次发起审核)
     */
    public function pushSupplierGoods(Request $request, ShopSupplierGoods $shopSupplierGoods, ShopSupplierGoodsArea $shopSupplierGoodsArea)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
                'shop_id' => 'required',
                'title' => 'required',
                'price' => 'required',
                'tao_jd_price' => 'required',
                'vip_price' => 'required',
                'real_weight' => 'required',
                'real_sale_volume' => 'required',
                'click_number' => 'required',
                'sale_volume' => 'required',
                'cost_price' => 'required',
                'volume' => 'required',
                'express' => 'required',
                'custom' => 'required',
                'is_push' => 'required',
                'area' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $area = $arrRequest['area'];
            if (empty($arrRequest['id'])) {
                $res_push = $shopSupplierGoods->pushGoods($arrRequest);
                $shopSupplierGoodsArea->addArea([
                    'supplier_good_id' => $res_push->id,
                    'area' => $area,
                ]);
                return $this->getResponse('创建成功！');
            }
            $shopSupplierGoodsArea->updateArea($arrRequest['id'], [
                'area' => $area,
            ]);
            $arrRequest['review_status'] = 0;
            $shopSupplierGoods->updateGoods($arrRequest);
            return $this->getResponse('重新发起成功！');

        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络连接错误，错误信息：' . $e->getLine() . '/' . $e->getMessage(), '500');
        }
    }

    /**
     * 审核对应的供应商信息
     * <审核成功-补信息><审核失败-理由>
     */
    public function checkSupplierGoods(Request $request, ShopSupplierGoods $shopSupplierGoods, ShopGoods $shopGoods, ShopSupplierGoodsArea $shopSupplierGoodsArea)
    {
        if ($request->header('data')) {
            $request->data = $request->header('data');
        }
        $arrRequest = json_decode($request->data, true);
        $rules = [
            'type' => 'required',
            'id' => 'required',
        ];
        $validator = Validator::make($arrRequest, $rules);
        if ($validator->fails()) {
            throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
        }

        if ($arrRequest['type'] == 1) {
            $rules = [
                'open_time' => 'required',
                'profit_value' => 'required',
                'cost_price' => 'required',
                'price' => 'required',
                'tao_jd_price' => 'required',
                'vip_price' => 'required',
                'detail_desc' => 'required',
                'sort' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('审核通过缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $area = $shopSupplierGoodsArea->getAreaBySupplier($arrRequest['id']);
            $shopSupplierGoods->pass($arrRequest['id'], $arrRequest['cost_price'], $arrRequest['price'], $arrRequest['tao_jd_price'], $arrRequest['vip_price'], $arrRequest['detail_desc'], $arrRequest['sort']);
            $supplierGoods = $shopSupplierGoods->getById($arrRequest['id']);
            $goods = $shopGoods->newGoods([
                'app_id' => $supplierGoods->app_id,
                'title' => $supplierGoods->title,
                'profit_value' => ($arrRequest['profit_value'] / 0.41),
                'price' => $arrRequest['price'],
                'vip_price' => $arrRequest['vip_price'],
                'cost_price' => $arrRequest['cost_price'],
                'real_weight' => $supplierGoods->real_weight,
                'zone' => $supplierGoods->zone,
                'real_sale_volume' => $supplierGoods->real_sale_volume,
                'click_number' => $supplierGoods->click_number,
                'sale_volume' => $supplierGoods->sale_volume,
                'detail_desc' => $arrRequest['detail_desc'],
                'sort' => $arrRequest['sort'],
                'volume' => $supplierGoods->volume,
                'express' => $supplierGoods->express,
                'custom' => $supplierGoods->custom,
                'parameter' => $supplierGoods->parameter,
                'sidle_img' => $supplierGoods->sidle_img,
                'header_img' => $supplierGoods->header_img,
                'video_url' => $supplierGoods->video_url,
                'detail_share_img' => $supplierGoods->detail_share_img,
                'detail_img' => $supplierGoods->detail_img,
                'is_push' => $supplierGoods->is_push,
                'status' => '1',
                'shop_id' => $supplierGoods->shop_id,
                'open_time' => strtotime($arrRequest['open_time']),
            ], $area->good_id);
            $shopSupplierGoodsArea->updateArea($arrRequest['id'], [
                'good_id' => $goods->id,
            ]);
            return $this->getResponse('修改成功！');
        }

        if ($arrRequest['type'] == 2) {
            $rules = [
                'review_fail_reason' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('审核失败缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $shopSupplierGoods->fail($arrRequest['id'], $arrRequest['review_fail_reason']);
            return $this->getResponse('修改成功！');
        }

        return $this->getResponse('此路不通！');
    }
}
