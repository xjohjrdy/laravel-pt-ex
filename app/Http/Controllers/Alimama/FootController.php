<?php

namespace App\Http\Controllers\Alimama;

use App\Entitys\App\GrowthUserValueConfig;
use App\Entitys\App\NewNewFoot;
use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class FootController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, NewNewFoot $foot)
    {
        if ($request->header('data')) {
            $request->data = $request->header('data');
        }
        $arrRequest = json_decode($request->data, true);
        $rules = [
            'app_id' => 'required',
        ];
        $validator = Validator::make($arrRequest, $rules);
        if ($validator->fails()) {
            throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
        }

        $f = $foot->getById($arrRequest['app_id']);

        $common_percent = 0.45;
        $share_vip_percent = 0.1;
        $share_common_percent = 0.05;

        //获取成长值比例 计算次月最大送的成长值
        $obj_growth_user_value_Config = new GrowthUserValueConfig();
        $num_growth_value = $obj_growth_user_value_Config->value('growth_config_value');

        if (!empty($f)) {
            $f = $f->toArray();
            foreach ($f['data'] as $k => $i) {
                $f['data'][$k]['share_tkmoney_general'] = round(($i['tkmoney_general'] / $common_percent) * $share_common_percent, 2);
                $f['data'][$k]['share_tkmoney_vip'] = round(($i['tkmoney_general'] / $common_percent) * $share_vip_percent, 2);
                $f['data'][$k]['growth_value_new_vip'] = (string)round($i['tkmoney_vip'] / $num_growth_value, 2);
                $f['data'][$k]['growth_value_new_normal'] = (string)round($i['tkmoney_general'] / $num_growth_value, 2);
                if (empty($i['shop_name'])) {
                    $f['data'][$k]['shop_name'] = '';
                }
            }
        }

        return $this->getResponse($f);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, NewNewFoot $foot)
    {

        if ($request->header('data')) {
            $request->data = $request->header('data');
        }
        $arrRequest = json_decode($request->data, true);
        $rules = [
            'app_id' => 'required',
            'good_id' => 'required',
            'title' => 'required',
            'price' => 'required',
            'coupon_price' => 'required',
            'sale_number' => 'required',
            'tkmoney_general' => 'required',
            'tkmoney_vip' => 'required',
            'img' => 'required',
            'coupon' => 'required',
        ];
        $validator = Validator::make($arrRequest, $rules);
        if ($validator->fails()) {
            throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
        }
        $no_foot = $foot->one($arrRequest['good_id'], $arrRequest['app_id']);

        if (!empty($no_foot)) {
            return $this->getInfoResponse('4004', '已经添加过足迹');
        }

        $res = $foot->addFoot([
            'app_id' => $arrRequest['app_id'],
            'good_id' => $arrRequest['good_id'],
            'title' => $arrRequest['title'],
            'price' => $arrRequest['price'],
            'coupon_price' => $arrRequest['coupon_price'],
            'sale_number' => $arrRequest['sale_number'],
            'tkmoney_general' => $arrRequest['tkmoney_general'],
            'tkmoney_vip' => $arrRequest['tkmoney_vip'],
            'img' => $arrRequest['img'],
            'coupon' => $arrRequest['coupon'],
            'from' => 0,
            'shop_name' => empty($arrRequest['shop_name']) ? 0 : $arrRequest['shop_name'],
        ]);
        return $this->getResponse($res);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Request $request, NewNewFoot $foot)
    {
        if ($request->header('data')) {
            $request->data = $request->header('data');
        }
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
}
