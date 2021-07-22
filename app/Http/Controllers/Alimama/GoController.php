<?php

namespace App\Http\Controllers\Alimama;

use App\Entitys\App\Collection;
use App\Entitys\App\Foot;
use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class GoController extends Controller
{
    /**
     * 足迹增加
     */
    public function addFoot(Request $request, Foot $foot)
    {
        $arrRequest = json_decode($request->data, true);
        $rules = [
            'app_id' => 'required',
            'itemid' => 'required',
            'itemshorttitle' => 'required',
            'itemprice' => 'required',
            'itemendprice' => 'required',
            'itemsale' => 'required',
            'couponurl' => 'required',
            'tkmoney_general' => 'required',
            'tkmoney_vip' => 'required',
            'itempic' => 'required',
            'couponmoney' => 'required',
            'sellernick' => 'required',
            'shoptype' => 'required',
        ];
        $validator = Validator::make($arrRequest, $rules);
        if ($validator->fails()) {
            throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
        }
        $no_foot = $foot->one($arrRequest['itemid'], $arrRequest['app_id']);

        if (!empty($no_foot)) {
            return $this->getInfoResponse('4004', '已经添加过足迹');
        }

        $res = $foot->addFoot($arrRequest);
        return $this->getResponse($res);
    }

    /**
     * 获取足迹
     */
    public function getFoot(Request $request, Foot $foot)
    {
        $arrRequest = json_decode($request->data, true);
        $rules = [
            'app_id' => 'required',
        ];
        $validator = Validator::make($arrRequest, $rules);
        if ($validator->fails()) {
            throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
        }

        $f = $foot->getById($arrRequest['app_id']);

        return $this->getResponse($f);
    }

    /**
     * 删除
     */
    public function delFoot(Request $request, Foot $foot)
    {
        $arrRequest = json_decode($request->data, true);
        $rules = [
            'foot_id' => 'required',
            'app_id' => 'required',
        ];
        $validator = Validator::make($arrRequest, $rules);
        if ($validator->fails()) {
            throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
        }
        $foot->delAll($arrRequest['foot_id']);

        return $this->getResponse('删除成功！');
    }

    /**
     * 删除
     */
    public function delCollection(Request $request, Collection $collection)
    {
        $arrRequest = json_decode($request->data, true);
        $rules = [
            'col_id' => 'required',
            'app_id' => 'required',
        ];
        $validator = Validator::make($arrRequest, $rules);
        if ($validator->fails()) {
            throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
        }

        $collection->delAll($arrRequest['col_id']);

        return $this->getResponse('取消收藏成功！');
    }


    /**
     * 足迹增加
     */
    public function addCollection(Request $request, Collection $collection)
    {
        $arrRequest = json_decode($request->data, true);
        $rules = [
            'app_id' => 'required',
            'itemid' => 'required',
            'itemshorttitle' => 'required',
            'itemprice' => 'required',
            'itemendprice' => 'required',
            'itemsale' => 'required',
            'couponurl' => 'required',
            'tkmoney_general' => 'required',
            'tkmoney_vip' => 'required',
            'itempic' => 'required',
            'couponmoney' => 'required',
            'sellernick' => 'required',
            'shoptype' => 'required',
        ];
        $validator = Validator::make($arrRequest, $rules);
        if ($validator->fails()) {
            throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
        }
        $no_foot = $collection->one($arrRequest['itemid'], $arrRequest['app_id']);

        if (!empty($no_foot)) {
            return $this->getInfoResponse('4004', '已经添加过收藏');
        }

        $res = $collection->addFoot($arrRequest);
        return $this->getResponse($res);
    }

    /**
     * 获取足迹
     */
    public function getCollection(Request $request, Collection $foot)
    {
        $arrRequest = json_decode($request->data, true);
        $rules = [
            'app_id' => 'required',
        ];
        $validator = Validator::make($arrRequest, $rules);
        if ($validator->fails()) {
            throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
        }

        $f = $foot->getById($arrRequest['app_id']);

        return $this->getResponse($f);
    }
}
