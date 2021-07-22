<?php

namespace App\Http\Controllers\Shop;

use App\Entitys\App\ShopGoods;
use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ShowController extends Controller
{
    /**
     * i can pay anything
     * @param Request $request
     * @throws ApiException
     */
    public function getPayShow(Request $request, ShopGoods $shopGoods)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'good_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $res = $shopGoods->getOneGoodById($arrRequest['good_id']);

            if (empty($res)) {
                return $this->getInfoResponse('4004', '商品已下架！');
            }
            $pay_show = [
                [
                    'id' => 1,
                    'title' => '支付宝支付',
                    'img' => 'http://a119112.oss-cn-beijing.aliyuncs.com/%E5%95%86%E5%9F%8E%E9%A6%96%E9%A1%B5/%E6%94%AF%E4%BB%98%E5%9B%BE%E6%A0%87/%E6%94%AF%E4%BB%98%E5%9B%BE%E6%A0%87/%E6%94%AF%E4%BB%98%E5%AE%9D@2x.png',
                ],
                [
                    'id' => 2,
                    'title' => '微信支付',
                    'img' => 'http://a119112.oss-cn-beijing.aliyuncs.com/%E5%95%86%E5%9F%8E%E9%A6%96%E9%A1%B5/%E6%94%AF%E4%BB%98%E5%9B%BE%E6%A0%87/%E6%94%AF%E4%BB%98%E5%9B%BE%E6%A0%87/%E5%BE%AE%E4%BF%A1@2x.png',
                ],
            ];

            if ($res->can_pay == 1) {
                $pay_show[] = [
                    'id' => 3,
                    'title' => '云闪付支付',
                    'img' => 'http://a119112.oss-cn-beijing.aliyuncs.com/%E5%95%86%E5%9F%8E%E9%A6%96%E9%A1%B5/%E6%94%AF%E4%BB%98%E5%9B%BE%E6%A0%87/%E6%94%AF%E4%BB%98%E5%9B%BE%E6%A0%87/%E4%BA%91%E9%97%AA%E4%BB%98@2x.png',
                ];
            }

            return $this->getResponse($pay_show);
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }
}
