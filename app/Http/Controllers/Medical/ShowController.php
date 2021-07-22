<?php

namespace App\Http\Controllers\Medical;

use App\Entitys\Ad\AdUserInfo;
use App\Entitys\App\MedicalCity;
use App\Entitys\App\MedicalHospital;
use App\Entitys\App\MedicalHospitalTestOrders;
use App\Entitys\App\MedicalShopGoods;
use App\Entitys\App\ShopGoods;
use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class ShowController extends Controller
{
    /**
     * 首页接口
     */
    public function getIndex(Request $request, MedicalShopGoods $medicalShopGoods, ShopGoods $shopGoods)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $shop_good = [];

            $all = $medicalShopGoods->getIndex();

            if (empty($all)) {
                return $this->getInfoResponse('4004', '未配置数据！');
            }

            $all = $all->toArray();

            foreach ($all as $k => $one) {
                $good_info = $shopGoods->getOneGood($one['good_id']);
                if (empty($good_info)) {
                    unset($all[$k]);
                    continue;
                }

                $shop_good[] = [
                    'id' => $good_info->id,
                    'cost_price' => $good_info->cost_price,
                    'header_img' => json_decode($good_info->header_img, true),
                    'vip_price' => $good_info->vip_price,
                    'price' => $good_info->price,
                    'title' => $good_info->title,
                ];
            }

            return $this->getResponse([
                'shop_good' => $shop_good
            ]);
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * all shop good
     */
    public function getAllShopIndex(Request $request, MedicalShopGoods $medicalShopGoods, ShopGoods $shopGoods)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $shop_good = [];

            $all = $medicalShopGoods->getAll();

            if (empty($all)) {
                return $this->getInfoResponse('4004', '未配置数据！');
            }

            $all = $all->toArray();

            foreach ($all['data'] as $k => $one) {

                $good_info = $shopGoods->getOneGood($one['good_id']);
                if (empty($good_info)) {
                    unset($all[$k]);
                    continue;
                }

                $shop_good[] = [
                    'id' => $good_info->id,
                    'cost_price' => $good_info->cost_price,
                    'header_img' => json_decode($good_info->header_img, true),
                    'vip_price' => $good_info->vip_price,
                    'price' => $good_info->price,
                    'title' => $good_info->title,
                ];
            }

            return $this->getResponse([
                'current_page' => $all['current_page'],
                'last_page' => $all['last_page'],
                'shop_goods' => $shop_good,
            ]);
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * 拉取医院
     */
    public function getHospital(Request $request, MedicalCity $medicalCity, MedicalHospital $medicalHospital, AdUserInfo $adUserInfo)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
                'area_name' => 'required',
                'lat' => 'required',
                'lon' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $city = $medicalCity->getOne($arrRequest['area_name']);
            if (empty($city)) {
                return $this->getInfoResponse('4044', '当前城市没有开通！');
            }

            $all_hospital = $medicalHospital->getInfo($city->area_code);

            if (empty($all_hospital)) {
                return $this->getInfoResponse('4044', '当前城市没有开通！');
            }

            $user = $adUserInfo->appToAdUserId($arrRequest['app_id']);

            $all_hospital = $all_hospital->toArray();
            foreach ($all_hospital['data'] as $k => $hospital) {
                $all_hospital['data'][$k]['distance_mi'] = $medicalHospital->getDistance($arrRequest['lon'], $arrRequest['lat'], $all_hospital['data'][$k]['lon'], $all_hospital['data'][$k]['lat']);
            }

            return $this->getResponse($all_hospital);
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * 获取订单
     */
    public function getOrders(Request $request, MedicalHospitalTestOrders $medicalHospitalTestOrders)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
                'status' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $redis_key = 'one_' . $arrRequest['app_id'] . '_this_for_medical';

            if (!(Cache::has($redis_key))) {
                $medicalHospitalTestOrders->updateOrder($arrRequest['app_id']);
                Cache::put($redis_key, 1, 1000);
            }
            $test_orders = $medicalHospitalTestOrders->getOrders($arrRequest['app_id'], $arrRequest['status']);

            if (empty($test_orders)) {
                return $this->getInfoResponse('4004', '不存在订单！');
            }

            $test_orders = $test_orders->toArray();
            $orders = [];
            foreach ($test_orders['data'] as $order) {
                $orders[] =
                    [
                        'id' => $order['id'],
                        'zk_combo_name' => $order['zk_combo_name'],
                        'all_money' => $order['use_money'] + ($order['use_ptb'] / 10),
                        'status' => $order['status'],
                        'my_order' => $order['my_order'],
                        'name' => $order['name'],
                        'in_time' => $order['in_time'],
                        'hospital_name' => $order['hospital_name'],
                        'created_at' => $order['created_at'],
                        'desc' => $order['desc'],
                        'mobile' => $order['mobile'],
                        'no_in_reason' => $order['no_in_reason'],
                        'response_reason' => $order['response_reason'],
                    ];
            }


            return $this->getResponse([
                'current_page' => $test_orders['current_page'],
                'last_page' => $test_orders['last_page'],
                'orders' => $orders,
            ]);
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * refund start
     */
    public function startRefund(Request $request, MedicalHospitalTestOrders $medicalHospitalTestOrders)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
                'refund_id' => 'required',
                'reason' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $orders = $medicalHospitalTestOrders->getCanRefundOrder($arrRequest['app_id'], $arrRequest['refund_id']);
            if (empty($orders)) {
                return $this->getInfoResponse('4004', '订单不存在！');
            }

            if (empty($arrRequest['reason'])) {
                return $this->getInfoResponse('4004', '理由不能为空！');
            }

            $medicalHospitalTestOrders->refundOrder($arrRequest['app_id'], $arrRequest['refund_id'], $arrRequest['reason']);

            return $this->getResponse('退款成功！');
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

}
