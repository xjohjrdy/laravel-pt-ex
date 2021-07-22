<?php

namespace App\Http\Controllers\Circle;

use App\Entitys\Ad\AdUserInfo;
use App\Entitys\Ad\UserCreditLog;
use App\Entitys\App\AppUserInfo;
use App\Entitys\App\CircleCityKing;
use App\Entitys\App\CircleCommonNotify;
use App\Entitys\App\CircleHighNumber;
use App\Entitys\App\CircleNoSay;
use App\Entitys\App\CircleOrder;
use App\Entitys\App\CircleRing;
use App\Entitys\App\TaobaoUser;
use App\Entitys\App\UserHigh;
use App\Exceptions\ApiException;
use App\Services\Circle\BecomeHost;
use App\Services\HeMengTong\HeMeToServices;
use App\Services\Other\CircleCommissionService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Yansongda\Pay\Pay;

class BecomeHostController extends Controller
{
    public function getHotTitle()
    {
        return $this->getResponse(config('ring.hot_title'));
    }

    public function getPtb(Request $request, BecomeHost $host)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'integer',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $app_id = $arrRequest['app_id'];

            $user_ptb = $host->getPtb($app_id);

            if (empty($user_ptb)) {
                return $this->getInfoResponse(3001, '不存在该用户');
            }

        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }

        return $this->getResponse($user_ptb);
    }

    public function searchTitle(Request $request, CircleRing $ring, CircleCityKing $king, CircleHighNumber $circleHighNumber)
    {

        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'search' => 'required',
                'city' => 'required',
                'coordinate' => 'required',
                'app_id' => 'integer',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $search = $arrRequest['search'];
            $app_id = $arrRequest['app_id'];
            $city = $arrRequest['city'];
            $coordinate = $arrRequest['coordinate'];
            $pattern = '/^[\x{00}-\x{ff}\x{4e00}-\x{9fa5}\x{3010}\x{3011}\x{ff08}\x{ff09}\x{201c}\x{201d}\x{2018}\x{2019}\x{ff0c}\x{ff01}\x{ff0b}\x{3002}\x{ff1f}\x{3001}\x{ff1b}\x{ff1a}\x{300a}\x{300b}]{1,8}$/u';

            $obj_ad_user = new AdUserInfo();
            $obj_ad_info = $obj_ad_user->appToAdUserId($app_id);
            if (empty($obj_ad_info)) {
                return $this->getInfoResponse('3003', '账户异常请联系客服！');
            }


            if (!preg_match($pattern, $search)) {
                return $this->getInfoResponse('3003', '请勿输入特殊字符，或长度不要超过八位。');
            }
            $search = preg_replace('/[\s]/', '', $search);
            $obj_no_say = new CircleNoSay();
            $arr_no_say = $obj_no_say->getNoSay();

            foreach ($arr_no_say as $item) {
                if (strstr($search, $item)) {
                    return $this->getInfoResponse('3003', '禁止输入非法关键词');
                }
            }
            $where['ico_title'] = $search;

            $circle_info = $ring->getInfo($where);
            $res = [];
            if (empty($circle_info)) {

                $king_info = $king->createOrAdd($city);

                $ring_params['ico_title'] = $search;
                $ring_params['king_id'] = $king_info->id;
                $ring_params['area'] = $city;
                $ring_params['area_land'] = $coordinate;
                $ring_params['price'] = config('ring.price');
                $ring_info = $ring->createRing($ring_params);


                $res['id'] = $ring_info->id;
                $res['title'] = $search;
                $res['static'] = 1;
                $res['price'] = config('ring.price');
                $res['free'] = false;
                $res['caption']['a'] = '进入圈子';
                $res['caption']['b'] = '成为圈主';
                $tmp_count_number = 0;
                $groupid = AdUserInfo::where(['pt_id' => $app_id])->value('groupid');


                $high_number = $circleHighNumber->getCanGetNumber($arrRequest['app_id']);
                $tmp_count_number += $high_number;
                if ($groupid == 24) {
                    $tmp_count_number += 1;
                }
                $ring_is_count = CircleOrder::where(['app_id' => $app_id, 'money' => 0, 'status' => 1])->count();
                if ($tmp_count_number > $ring_is_count) {
                    $res['free'] = true;
                }
                return $this->getResponse($res);
            }
            if ($circle_info->app_id == $app_id) {
                $res['static'] = 2;
                $res['id'] = $circle_info->id;
                $res['number_person'] = $circle_info->number_person;
                $res['number_zone'] = $circle_info->number_zone;
                $res['number_anima'] = $circle_info->number_anima;
                $res['ico_img'] = $circle_info->ico_img;
                $res['title'] = $circle_info->ico_title;
                $res['app_id'] = $circle_info->app_id;
                $res['close'] = $circle_info->close;
                $res['price'] = $circle_info->price;

                $res['caption']['a'] = '进入';

                return $this->getResponse($res);
            }

            $res['static'] = 2;

            $res['id'] = $circle_info->id;
            $res['number_person'] = $circle_info->number_person;
            $res['number_zone'] = $circle_info->number_zone;
            $res['number_anima'] = $circle_info->number_anima;
            $res['ico_img'] = $circle_info->ico_img;
            $res['title'] = $circle_info->ico_title;
            $res['app_id'] = $circle_info->app_id;
            $res['close'] = $circle_info->close;
            $res['price'] = $circle_info->price;

            $res['caption']['a'] = '进入';
            $res['caption']['b'] = '竞价';
            $res['caption']['c'] = '联系圈主';


        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }

        return $this->getResponse($res);
    }

    public function free(Request $request, BecomeHost $host, CircleHighNumber $circleHighNumber)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'integer',
                'ring_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $app_id = $arrRequest['app_id'];
            $ring_id = $arrRequest['ring_id'];
            $area = isset($arrRequest['area']) ? $arrRequest['area'] : '';

            $obj_circle = CircleRing::find($ring_id);

            if (empty($obj_circle)) {
                throw new ApiException('非法请求，不存在该圈子', 5004);
            }
            if ($host->countCircle($app_id) >= 10) {
                return $this->getInfoResponse('3001', '您已拥有10个圈子，不允许再竞价其他圈子。');
            }
            if ($obj_circle->app_id > 0) {
                Storage::disk('local')->append('callback_document/circle_ring_info.txt', var_export('操作异常，该圈子已有圈主', true));
                return $this->getInfoResponse('3000', '操作异常，该圈子已有圈主');
            }

            /*
            $groupid = AdUserInfo::where(['pt_id' => $app_id])->value('groupid');
            if ($groupid != 23 && $groupid != 24) {
                throw new ApiException('非法请求，非代理商优质转正！', 5001);
            }
            $ring_is = CircleOrder::where(['app_id' => $app_id, 'money' => 0, 'status' => 1])->exists();
            if ($ring_is) {
                throw new ApiException('非法请求，您已经免费领取过了！', 5002);
            }

            */
            $tmp_count_number = 0;
            $groupid = AdUserInfo::where(['pt_id' => $app_id])->value('groupid');


            $high_number = $circleHighNumber->getCanGetNumber($arrRequest['app_id']);
            $tmp_count_number += $high_number;
            if ($groupid == 24) {
                $tmp_count_number += 1;
            }
            $ring_is_count = CircleOrder::where(['app_id' => $app_id, 'money' => 0, 'status' => 1])->count();
            if ($tmp_count_number <= $ring_is_count) {
                throw new ApiException('非法请求，您的免费次数已用光！', 5002);
            }
            $order_value['money'] = 0;
            $order_value['status'] = 1;
            $order_value['app_id'] = $app_id;
            $order_value['circle_id'] = $ring_id;
            $order_id = $host->createOrderNot($order_value);
            if (empty($order_id)) {
                throw new ApiException('网络异常，不存在order_id,请联系客服！', 5003);
            }
            $circle_params['app_id'] = $app_id;
            $circle_params['price'] = 600 * 1.2;
            if (!empty($area)) {
                $circle_params['area'] = $area;
            }
            $host->updateCircleNotNumber($order_id, $ring_id, $circle_params);
            $host->addCircle($app_id, $ring_id, $area);

        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
        return $this->getResponse('领取成功');

    }

    /*public function buy(Request $request, BecomeHost $host)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'buy_type' => Rule::in([1, 2, 3]),
                'app_id' => 'integer',
                'ring_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $buy_type = $arrRequest['buy_type'];
            $app_id = $arrRequest['app_id'];
            $ring_id = $arrRequest['ring_id'];
            $area = isset($arrRequest['area']) ? $arrRequest['area'] : '';
            $circle_price = config('ring.price');

            $circle_info = $host->getCircleInfo($ring_id);
            if (empty($circle_info)) {
                throw new ApiException('网络异常，不存在该圈子,请联系客服！', 5004);
            }

            if ($buy_type == 1) {

                $user_uid = $host->getUid($app_id);

                $tmp_pass = $host->verifyPtb($user_uid, $circle_price * 10);

                if ($tmp_pass == false) {
                    return $this->getInfoResponse('3001', '葡萄币不足');
                }
                $host->takePtb($app_id, $circle_price * 10);
                $order_value['money'] = $circle_price;
                $order_value['buy_type'] = 2;
                $order_value['status'] = 1;
                $order_value['app_id'] = $app_id;
                $order_value['circle_id'] = $ring_id;
                $order_id = $host->createOrderNot($order_value);
                if (empty($order_id)) {
                    throw new ApiException('网络异常，不存在order_id,请联系客服！', 5003);
                }
                $host->newBonus($order_id);
                $king_id = $circle_info->king_id;
                $host->kingBonus($king_id, $order_id);
                $host->addCircle($app_id, $ring_id, $area);


                return $this->getResponse('低价认购成功！');

            } elseif ($buy_type == 2) {
                $order_value['money'] = $circle_price;
                $order_value['buy_type'] = 1;
                $order_value['status'] = 0;
                $order_value['app_id'] = $app_id;
                $order_value['circle_id'] = $ring_id;
                $order_id = $host->createOrderNot($order_value);
                if (empty($order_id)) {
                    throw new ApiException('网络异常，不存在order_id,请联系客服！', 5003);
                }
                $ali_value['out_trade_no'] = $order_id;
                $ali_value['total_amount'] = $circle_price;
                $ali_value['subject'] = '商城购物 - ' . $circle_price . '元';
                $ali_value['body'] = $area;

                return Pay::alipay(config('ring.ali_config'))->app($ali_value);
            } elseif ($buy_type == 3) {

                if ($arrRequest['app_id'] == 1569840 || $arrRequest['app_id'] == 1694511) {
                    $circle_price = 0.01;
                }
                $order_value['money'] = $circle_price;
                $order_value['buy_type'] = 1;
                $order_value['status'] = 0;
                $order_value['app_id'] = $app_id;
                $order_value['circle_id'] = $ring_id;
                $order_id = $host->createOrderNot($order_value);
                if (empty($order_id)) {
                    throw new ApiException('网络异常，不存在order_id,请联系客服！', 5003);
                }
                $order = [
                    'out_trade_no' => $order_id,
                    'total_fee' => ($circle_price * 100),
                    'body' => '商城购物 - ' . $circle_price . '元',
                    'attach' => $area,
                ];
                $this->wechat_config['notify_url'] = config('ring.we_host_notify_url');
                $pay = Pay::wechat(config('ring.we_host_config'))->app($order);
                return $pay;

            }

        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }*/
    public function bid(Request $request, BecomeHost $host)
    {


        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'buy_type' => Rule::in([1, 2, 3]),
                'app_id' => 'integer',
                'ring_id' => 'integer',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $buy_type = $arrRequest['buy_type'];
            $app_id = $arrRequest['app_id'];
            $ring_id = $arrRequest['ring_id'];
            $area = isset($arrRequest['area']) ? $arrRequest['area'] : '';
            if ($app_id != 3675700) {
                return $this->getInfoResponse('3003', '系统升级期间圈子暂停付款，预计1月1号升级完成，敬请期待！');
            }
            $obj_ad_user = new AdUserInfo();
            $obj_ad_info = $obj_ad_user->appToAdUserId($app_id);
            if (empty($obj_ad_info)) {
                return $this->getInfoResponse('3003', '账户异常请联系客服！');
            }

            if ($buy_type == 3) {
            }
            if ($buy_type == 2 && $app_id != 1694511) {
                return $this->getInfoResponse('3001', '支付宝正在升级中，请先用微信或者葡萄币支付！');
            }
            if (Cache::has('become_host_bid_' . $app_id)) {
                return $this->getInfoResponse('2005', '频率过快请稍后再试.');
            }
            Cache::put('become_host_bid_' . $app_id, 1, 0.5);
            if ($host->countCircle($app_id) >= 10) {
                return $this->getInfoResponse('3001', '您已拥有10个圈子，不允许再竞价其他圈子。');
            }
            $circle_info = $host->getCircleInfo($ring_id);

            $circle_price = $circle_info->price;
            $quondam_app_id = $circle_info->app_id;
            $circle_close = $circle_info->close;
            $ring_title = $circle_info->ico_title;
            if ($circle_close == 1) {
                return $this->getInfoResponse('4004', '圈子已锁定，无法竞价');
            }

            if ($quondam_app_id == $app_id) {
                return $this->getInfoResponse('4005', '已经是自己的圈子，无法竞价');
            }

            if ($buy_type == 1) {
                $user_uid = $host->getUid($app_id);

                $tmp_pass = $host->verifyPtb($user_uid, $circle_price * 10);

                if ($tmp_pass == false) {
                    return $this->getInfoResponse('3001', '葡萄币不足');
                }
                $host->takePtb($app_id, $circle_price * 10);
                $order_value['money'] = $circle_price;
                $order_value['buy_type'] = 2;
                $order_value['status'] = 1;
                $order_value['app_id'] = $app_id;
                $order_value['circle_id'] = $ring_id;
                $order_id = $host->createOrderNot($order_value);
                if (empty($order_id)) {
                    throw new ApiException('网络异常，不存在order_id,请联系客服！', 5003);
                }
                $circle_params['app_id'] = $app_id;
                $circle_params['price'] = $circle_price * 1.2;
                if (!empty($area)) {
                    $circle_params['area'] = $area;
                }
                if ($host->isLock($ring_id)) {
                    $circle_params['close'] = 1;
                }
                $host->updateCircle($order_id, $ring_id, $circle_params);
                $host->addCircle($app_id, $ring_id);

                $re_obj_user = AppUserInfo::find($app_id);
                if ($circle_price != 600) {
                    $host->demotion($quondam_app_id, $ring_id);
                    $return_ptb = round($circle_price * 10 * config('ring.return_money'));
                    $host->addPtb($quondam_app_id, $return_ptb);
                    $host->addBoundsLog($quondam_app_id, $app_id, $order_id, $return_ptb);
                    $obj_notify = new CircleCommonNotify();
                    $n_data = [];
                    $n_data['app_id'] = $quondam_app_id;
                    $n_data['ico'] = 'http://putaoliulanqi1.oss-cn-zhangjiakou.aliyuncs.com/logo/logo.png';
                    $n_data['username'] = '系统通知';
                    if (empty($re_obj_user->user_name)) {
                        $re_obj_user->user_name = 'ID：' . $re_obj_user->id;
                    }
                    $n_data['notify'] = "{$re_obj_user->user_name} 花费 " . ($circle_price * 10) . "葡萄币，抢购了您的“{$ring_title}”圈子！";
                    $n_data['to_id'] = $ring_id;
                    $n_data['type'] = 3;
                    $obj_notify->addNotify($n_data);

                    //剥离多级分
                    $obj_circle_commission_service = new CircleCommissionService();
                    $host->bidBonus($order_id);//团队会员竞价圈子获得津贴 直属分
                    $obj_circle_commission_service->biddingCircleCommission($order_id);//团队会员竞价圈子获得津贴 第三方分

                    return $this->getResponse('竞价成功！');
                } else {
                    //剥离多级分
                    $obj_circle_commission_service = new CircleCommissionService();
                    $host->newBonus($order_id);//团队会员购买圈子津贴 直属分
                    $obj_circle_commission_service->buyCircleCommission($order_id);//团队会员购买圈子津贴 第三方分

                    return $this->getResponse('600购买成功！');
                }


            } elseif ($buy_type == 2) {
                $order_value['money'] = $circle_price;
                $order_value['buy_type'] = 1;
                $order_value['status'] = 0;
                $order_value['app_id'] = $app_id;
                $order_value['circle_id'] = $ring_id;
                $order_id = $host->createOrderNot($order_value);
                if (empty($order_id)) {
                    throw new ApiException('网络异常，不存在order_id,请联系客服！', 5003);
                }
                if ($app_id == 1694511) $circle_price = 0.01;
                $ali_value['out_trade_no'] = $order_id;
                $ali_value['total_amount'] = $circle_price;
                $ali_value['subject'] = '商城购物 - ' . $circle_price . '元';
                $ali_value['body'] = $area;
                return Pay::alipay(config('ring.ali_config'))->app($ali_value);

            } elseif ($buy_type == 3) {

                if ($arrRequest['app_id'] == 1569840 || $arrRequest['app_id'] == 1694511) {
                    $circle_price = 0.01;
                }
                $order_value['money'] = $circle_price;
                $order_value['buy_type'] = 1;
                $order_value['status'] = 0;
                $order_value['app_id'] = $app_id;
                $order_value['circle_id'] = $ring_id;
                $order_id = $host->createOrderNot($order_value);
                if (empty($order_id)) {
                    throw new ApiException('网络异常，不存在order_id,请联系客服！', 5003);
                }
                $order = [
                    'out_trade_no' => $order_id,
                    'total_fee' => ($circle_price * 100),
                    'body' => '商城购物 - ' . $circle_price . '元',
                    'attach' => $area,
                ];
                $this->wechat_config['notify_url'] = config('ring.we_host_notify_url');
                $pay = Pay::wechat(config('ring.we_host_config'))->app($order);
                return $pay;

            }

        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
     * 购买圈子
     */
    public function bidV1(Request $request, BecomeHost $host)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'buy_type' => Rule::in([2, 3, 5]),
                'app_id' => 'integer',
                'ring_id' => 'integer',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $buy_type = $arrRequest['buy_type'];
            $app_id = $arrRequest['app_id'];
            $ring_id = $arrRequest['ring_id'];
            $area = isset($arrRequest['area']) ? $arrRequest['area'] : '';

            //拦截版本
            $request_device = $request->header('Accept-Device'); //设备类型
            $request_appversion = $request->header('Accept-Appversion'); //版本号
            if (($request_device == 'android' && $request_appversion < 191) || ($request_device == 'ios' && version_compare($request_appversion, '4.6.3', '<'))) {
                if ($app_id != 3675700) {
                    return $this->getInfoResponse('3003', '系统升级期间圈子暂停付款，预计1月1号升级完成，敬请期待！');
                }
            }

            $obj_ad_user = new AdUserInfo();
            $obj_ad_info = $obj_ad_user->appToAdUserId($app_id);
            if (empty($obj_ad_info)) {
                return $this->getInfoResponse('3003', '账户异常请联系客服！');
            }

            if ($buy_type == 3) {
            }
//            if ($buy_type == 2 && $app_id != 1694511) {
//                return $this->getInfoResponse('3001', '支付宝正在升级中，请先用微信或者余额支付！');
//            }
            if (Cache::has('become_host_bid_' . $app_id)) {
                return $this->getInfoResponse('2005', '频率过快请稍后再试.');
            }
            Cache::put('become_host_bid_' . $app_id, 1, 0.5);
            if ($host->countCircle($app_id) >= 10) {
                return $this->getInfoResponse('3001', '您已拥有10个圈子，不允许再竞价其他圈子。');
            }
            $circle_info = $host->getCircleInfo($ring_id);

            $circle_price = $circle_info->price;
            $quondam_app_id = $circle_info->app_id;
            $circle_close = $circle_info->close;
            $ring_title = $circle_info->ico_title;
            if ($circle_price == 600) {
                return $this->getInfoResponse('4004', '圈子模块升级中，敬请期待~');
            }


            if ($circle_close == 1) {
                return $this->getInfoResponse('4004', '圈子已锁定，无法竞价');
            }

            if ($quondam_app_id == $app_id) {
                return $this->getInfoResponse('4005', '已经是自己的圈子，无法竞价');
            }

            if ($buy_type == 5) {
                $taobao_user = new TaobaoUser();//用户真实分佣表
                $int_taobao_user = $taobao_user->where('app_id', $app_id)->value('money');
                $int_taobao_user = empty($int_taobao_user) ? 0 : $int_taobao_user;

                if ($circle_price > $int_taobao_user) {
                    return $this->getInfoResponse('3004', '余额不足');
                }

                //余额支扣除
                $host->takeMoney($app_id, $circle_price);

                $order_value['money'] = $circle_price;
                $order_value['buy_type'] = 2;
                $order_value['status'] = 1;
                $order_value['app_id'] = $app_id;
                $order_value['circle_id'] = $ring_id;
                $order_id = $host->createOrderNot($order_value);
                if (empty($order_id)) {
                    throw new ApiException('网络异常，不存在order_id,请联系客服！', 5003);
                }
                $circle_params['app_id'] = $app_id;
                $circle_params['price'] = $circle_price * 1.2;
                if (!empty($area)) {
                    $circle_params['area'] = $area;
                }
                if ($host->isLock($ring_id)) {
                    $circle_params['close'] = 1;
                }
                $host->updateCircle($order_id, $ring_id, $circle_params);
                $host->addCircle($app_id, $ring_id);

                $re_obj_user = AppUserInfo::find($app_id);
                if ($circle_price != 600) {
                    $host->demotion($quondam_app_id, $ring_id);
                    $return_ptb = round($circle_price * 10 * config('ring.return_money'));
                    $host->addPtb($quondam_app_id, $return_ptb);
                    $host->addBoundsLog($quondam_app_id, $app_id, $order_id, $return_ptb);
                    $obj_notify = new CircleCommonNotify();
                    $n_data = [];
                    $n_data['app_id'] = $quondam_app_id;
                    $n_data['ico'] = 'http://putaoliulanqi1.oss-cn-zhangjiakou.aliyuncs.com/logo/logo.png';
                    $n_data['username'] = '系统通知';
                    if (empty($re_obj_user->user_name)) {
                        $re_obj_user->user_name = 'ID：' . $re_obj_user->id;
                    }
                    $n_data['notify'] = "{$re_obj_user->user_name} 花费 " . ($circle_price) . "元，抢购了您的“{$ring_title}”圈子！";
                    $n_data['to_id'] = $ring_id;
                    $n_data['type'] = 3;
                    $obj_notify->addNotify($n_data);

                    //剥离多级分
                    $obj_circle_commission_service = new CircleCommissionService();
                    $host->bidBonus($order_id);//团队会员竞价圈子获得津贴 直属分
                    $obj_circle_commission_service->biddingCircleCommission($order_id);//团队会员竞价圈子获得津贴 第三方分

                    return $this->getResponse('竞价成功！');
                } else {
                    //剥离多级分
                    $obj_circle_commission_service = new CircleCommissionService();
                    $host->newBonus($order_id);//团队会员购买圈子津贴 直属分
                    $obj_circle_commission_service->buyCircleCommission($order_id);//团队会员购买圈子津贴 第三方分

                    return $this->getResponse('600购买成功！');
                }


            } elseif ($buy_type == 2) {
                $order_value['money'] = $circle_price;
                $order_value['buy_type'] = 1;
                $order_value['status'] = 0;
                $order_value['app_id'] = $app_id;
                $order_value['circle_id'] = $ring_id;
                $order_id = $host->createOrderNot($order_value);
                if (empty($order_id)) {
                    throw new ApiException('网络异常，不存在order_id,请联系客服！', 5003);
                }
                if ($app_id == 1694511 || $app_id == 3675700 || $app_id == 9873668 || $app_id == 8343202) $circle_price = 0.01;

                //改为禾盟通支付
//                $ali_value['out_trade_no'] = $order_id;
//                $ali_value['total_amount'] = $circle_price;
//                $ali_value['subject'] = '商城购物 - ' . $circle_price . '元';
//                $ali_value['body'] = $area;
//                return Pay::alipay(config('ring.ali_config'))->app($ali_value);
//                $ali_secret = Pay::alipay(config('ring.ali_config'))->app($ali_value);;
//                return $this->getResponse($ali_secret->getContent());

                $heMeToServices = new HeMeToServices();
                $data = $heMeToServices->appPayCircleBuy($circle_price, $circle_price, $order_id, $area);
                $res = json_decode($data, true);
                if (@$res['fcode'] != 10000) {
                    return $this->getResponse('购买失败！请联系客服');
                }
                return $this->getResponse(@$res['fcode_url']);

            } elseif ($buy_type == 3) {

                if ($arrRequest['app_id'] == 1569840 || $arrRequest['app_id'] == 1694511 || $arrRequest['app_id'] == 3675700 || $arrRequest['app_id'] == 9873668 || $arrRequest['app_id'] == 8343202) {
                    $circle_price = 0.01;
                }
                $order_value['money'] = $circle_price;
                $order_value['buy_type'] = 1;
                $order_value['status'] = 0;
                $order_value['app_id'] = $app_id;
                $order_value['circle_id'] = $ring_id;
                $order_id = $host->createOrderNot($order_value);
                if (empty($order_id)) {
                    throw new ApiException('网络异常，不存在order_id,请联系客服！', 5003);
                }

                //改为何盟通支付
//                $order = [
//                    'out_trade_no' => $order_id,
//                    'total_fee' => ($circle_price * 100),
//                    'body' => '商城购物 - ' . $circle_price . '元',
//                    'attach' => $area,
//                ];
//                $this->wechat_config['notify_url'] = config('ring.we_host_notify_url');
//                $pay = Pay::wechat(config('ring.we_host_config'))->app($order);
//                return $pay;
//                $we_secret = Pay::wechat(config('ring.we_host_config'))->app($order);
//                return $this->getResponse($we_secret->getContent());

                $heMeToServices = new HeMeToServices();
                $data = $heMeToServices->appWxPayCircleBuy($circle_price, $order_id, $app_id, $area);
                return $this->getResponse($data);
            }
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
     * 支付宝回调 post 形式
     */
    public function aliNotify(Request $request, BecomeHost $host)
    {
        try {
            $obj_ali_pay = Pay::alipay(config('ring.ali_config'));
            $obj_data = $obj_ali_pay->verify();

            $this->log('---------------start----------');
            $this->log($obj_data->toArray());
            if ($obj_data->trade_status != 'TRADE_SUCCESS' && $obj_data->trade_status != 'TRADE_FINISHED') {
                $this->log('---------------end----------');
                return 'error';
            }
            if ($obj_data->seller_id != config('ring.ali_pid')) {
                $this->log('---------------end_error----------');
                return 'error';
            }

            $order_id = $obj_data->out_trade_no;
            $actual = $obj_data->total_amount;
            $area = $obj_data->body;
            $order_info = $host->getInfoByOrderId($order_id);
            $this->log('处理订单：' . $order_id);
            if (empty($order_info)) {
                $this->log('不存在该订单：' . $order_id);
                $this->log('---------------end_error----------');
                return 'error';
            }
            if ($order_info->status == 1) {
                $this->log('该笔订单已经支付过：' . $order_id);
                $this->log('---------------end_error----------');
                return 'error';
            }

            /*
             * 校验当前金额是否低于圈子现价，
             * 如果支付金额低于圈子当前价格，则判定圈子已经被被人捷足先登
             * 记录日志，返还该用户充值金额
             */

            $circle_id = $order_info->circle_id;
            $circle_info = $host->getCircleInfo($circle_id);
            if ($circle_info->price != $actual) {
                $tmp_msg = "【订单失效】订单id：{$order_id}，用户交易金额：{$actual}，圈子现价：{$circle_info->price}。";

                Storage::disk('local')->append('callback_document/circle_alipay_notify_overdue.txt', var_export($tmp_msg, true));

                return $obj_ali_pay->success();
            }
            $circle_price = config('ring.price');
            $log_app_id = 0;
            if ($actual > $circle_price) {
                $this->log('竞价');
                $circle_id = $order_info->circle_id;
                $circle_info = $host->getCircleInfo($circle_id);
                $quondam_app_id = $circle_info->app_id;
                $app_id = $order_info->app_id;
                $log_app_id = $app_id;
                $circle_params['app_id'] = $app_id;
                $circle_params['price'] = $actual * 1.2;
                if ($host->isLock($circle_id)) {
                    $circle_params['close'] = 1;
                }
                if (!empty($area)) {
                    $circle_params['area'] = $area;
                }

                $host->updateCircle($order_id, $circle_id, $circle_params);
                $this->log('创建者加入圈子：' . $order_info->app_id . '，圈子id：' . $circle_id);
                $host->addCircle($order_info->app_id, $circle_id);
                $this->log('原圈主降级：' . $quondam_app_id . '，圈子id：' . $circle_id);
                $host->demotion($quondam_app_id, $circle_id);
                $return_ptb = round($actual * 10 * config('ring.return_money'));

                $host->addPtb($quondam_app_id, $return_ptb);
                $host->addBoundsLog($quondam_app_id, $app_id, $order_id, $return_ptb);

                $re_obj_user = AppUserInfo::find($app_id);
                $obj_notify = new CircleCommonNotify();
                $n_data = [];
                $n_data['app_id'] = $quondam_app_id;
                $n_data['ico'] = 'http://putaoliulanqi1.oss-cn-zhangjiakou.aliyuncs.com/logo/logo.png';
                $n_data['username'] = '系统通知';
                if (empty($re_obj_user->user_name)) {
                    $re_obj_user->user_name = 'ID：' . $re_obj_user->id;
                }
                $n_data['notify'] = "{$re_obj_user->user_name} 花费 " . ($actual * 10) . "葡萄币，抢购了您的“{$circle_info->ico_title}”圈子！";
                $n_data['to_id'] = $circle_id;
                $n_data['type'] = 3;
                $obj_notify->addNotify($n_data);

                //剥离多级分
                $obj_circle_commission_service = new CircleCommissionService();
                $host->bidBonus($order_id);//团队会员竞价圈子获得津贴支付宝回调 直属分
                $obj_circle_commission_service->biddingCircleCommission($order_id);//团队会员竞价圈子获得津贴支付宝回调 第三方分


            } else {
                $circle_id = $order_info->circle_id;
                $circle_info = $host->getCircleInfo($circle_id);
                $this->log('低价认购');
                $circle_value['app_id'] = $order_info->app_id;
                $log_app_id = $order_info->app_id;
                $circle_value['price'] = $actual * 1.2;
                if (!empty($area)) {
                    $circle_value['area'] = $area;
                }
                $host->updateCircleNotNumber($order_id, $circle_id, $circle_value);
                $this->log('开始分佣订单：' . $order_id);

                //剥离多级分
                $obj_circle_commission_service = new CircleCommissionService();
                $host->newBonus($order_id);//团队会员购买圈子津贴支付宝回调 直属分
                $obj_circle_commission_service->buyCircleCommission($order_id);//团队会员购买圈子津贴支付宝回调 第三方分

                $king_id = $circle_info->king_id;
                $host->kingBonus($king_id, $order_id);
                $this->log('创建者加入圈子：' . $order_info->app_id . '，圈子id：' . $circle_id);
                $host->addCircle($order_info->app_id, $circle_id, $area);
            }

            $ad_user_info = AdUserInfo::where(['pt_id' => $log_app_id])->first();
            $obj_log = new UserCreditLog();

            $obj_log->addLog($ad_user_info->uid, "APG", ['extcredits1' => $actual]);
        } catch (\Throwable $e) {
            $this->log('出现异常情况，文件' . $e->getFile() . ',行' . $e->getLine() . ',错误信息：' . $e->getMessage());
            $this->log('---------------end_error----------');
            return 'error';
        }
        $this->log('---------------end----------');
        return $obj_ali_pay->success();
    }

    /*
     * 微信回调
     */
    public function weNotify(Request $request, BecomeHost $host)
    {
        $this->weLog('---------------start----------');
        $pay = Pay::wechat(config('ring.we_host_config'));

        try {
            $obj_data = $pay->verify();
            if ($obj_data->return_code != "SUCCESS") {
                $this->weLog('错误信息：' . $obj_data->return_msg);
                $this->weLog('---------------end----------');
                return 'error';
            }

            $order_id = $obj_data->out_trade_no;
            $actual = $obj_data->total_fee;
            $area = $obj_data->attach;

            $this->weLog('开始查询订单：' . $order_id);
            $order_info = $host->getInfoByOrderId($order_id);

            if (empty($order_info)) {
                $this->weLog('不存在该订单：' . $order_id);
                $this->weLog('---------------end_error----------');
                return 'error';
            }

            $this->weLog('开始校验金额：' . ($actual / 100));
            if ($order_info->status == 1) {
                $this->weLog('该笔订单已经支付过：' . $order_id);
                $this->weLog('---------------end_error----------');
                return 'error';
            }

            $actual /= 100;


            /*
             * 校验当前金额是否低于圈子现价，
             * 如果支付金额低于圈子当前价格，则判定圈子已经被被人捷足先登
             * 记录日志，返还该用户充值金额
             */

            $circle_id = $order_info->circle_id;
            $circle_info = $host->getCircleInfo($circle_id);
            if ($circle_info->price != $actual) {
                $tmp_msg = "【订单失效】订单id：{$order_id}，用户交易金额：{$actual}，圈子现价：{$circle_info->price}。";
                Storage::disk('local')->append('callback_document/circle_wechat_notify_overdue.txt', var_export($tmp_msg, true));
                return $pay->success();
            }
            $circle_price = config('ring.price');
            if ($actual > $circle_price) {
                $this->weLog('竞价');
                $circle_id = $order_info->circle_id;
                $circle_info = $host->getCircleInfo($circle_id);
                $quondam_app_id = $circle_info->app_id;
                $app_id = $order_info->app_id;
                $circle_params['app_id'] = $app_id;
                $circle_params['price'] = $actual * 1.2;
                if ($host->isLock($circle_id)) {
                    $circle_params['close'] = 1;
                }
                if (!empty($area)) {
                    $circle_params['area'] = $area;
                }

                $host->updateCircle($order_id, $circle_id, $circle_params);
                $this->weLog('创建者加入圈子：' . $order_info->app_id . '，圈子id：' . $circle_id);
                $host->addCircle($order_info->app_id, $circle_id);
                $this->weLog('原圈主降级：' . $quondam_app_id . '，圈子id：' . $circle_id);
                $host->demotion($quondam_app_id, $circle_id);
                $return_ptb = round($actual * 10 * config('ring.return_money'));
                $host->addPtb($quondam_app_id, $return_ptb);
                $host->addBoundsLog($quondam_app_id, $app_id, $order_id, $return_ptb);

                $re_obj_user = AppUserInfo::find($app_id);
                $obj_notify = new CircleCommonNotify();
                $n_data = [];
                $n_data['app_id'] = $quondam_app_id;
                $n_data['ico'] = 'http://putaoliulanqi1.oss-cn-zhangjiakou.aliyuncs.com/logo/logo.png';
                $n_data['username'] = '系统通知';
                if (empty($re_obj_user->user_name)) {
                    $re_obj_user->user_name = 'ID：' . $re_obj_user->id;
                }
                $n_data['notify'] = "{$re_obj_user->user_name} 花费 " . ($actual * 10) . "葡萄币，抢购了您的“{$circle_info->ico_title}”圈子！";
                $n_data['to_id'] = $circle_id;
                $n_data['type'] = 3;
                $obj_notify->addNotify($n_data);

                //剥离多级分
                $obj_circle_commission_service = new CircleCommissionService();
                $host->bidBonus($order_id);//团队会员竞价圈子获得津贴微信回调 直属分
                $obj_circle_commission_service->biddingCircleCommission($order_id);//团队会员竞价圈子获得津贴微信回调 第三方分

            } else {
                $circle_id = $order_info->circle_id;
                $circle_info = $host->getCircleInfo($circle_id);
                $this->weLog('低价认购');
                $circle_value['app_id'] = $order_info->app_id;
                $circle_value['price'] = $actual * 1.2;
                if (!empty($area)) {
                    $circle_value['area'] = $area;
                }
                $host->updateCircleNotNumber($order_id, $circle_id, $circle_value);
                $this->weLog('开始分佣订单：' . $order_id);

                //剥离多级分
                $obj_circle_commission_service = new CircleCommissionService();
                $host->newBonus($order_id);//团队会员购买圈子津贴微信回调 直属分
                $obj_circle_commission_service->buyCircleCommission($order_id);//团队会员购买圈子津贴微信回调 第三方分

                $king_id = $circle_info->king_id;
                $host->kingBonus($king_id, $order_id);
                $this->weLog('创建者加入圈子：' . $order_info->app_id . '，圈子id：' . $circle_id);
                $host->addCircle($order_info->app_id, $circle_id, $area);
            }

            $this->weLog('---------------end----------');

        } catch (\Throwable $e) {
            $this->weLog('出现异常情况，文件' . $e->getFile() . ',行' . $e->getLine() . ',错误信息：' . $e->getMessage());
            $this->weLog('---------------end_error----------');
            return 'error';
        }

        return $pay->success();

    }


    /*
     * 记录日志
     */
    private function log($msg)
    {

        Storage::disk('local')->append('callback_document/circle_alipay_notify.txt', var_export($msg, true));
    }

    /*
     * 记录日志（微信）
     */
    private function weLog($msg)
    {

        Storage::disk('local')->append('callback_document/circle_wechat_notify.txt', var_export($msg, true));
    }

    /*
     * 竞价历史
     */
    public function bidHistory(Request $request, BecomeHost $host)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'ring_id' => 'integer',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $ring_id = $arrRequest['ring_id'];

            $circle_info = $host->getCircleInfo($ring_id);
            if (empty($circle_info)) {
                return $this->getInfoResponse('3003', '不存在该圈子');
            }

            $info_list = $host->getBidHistory($ring_id);
            $view_list = [];
            $circle_name = $circle_info->ico_title;

            foreach ($info_list as $item) {
                $user_info = AppUserInfo::find($item->app_id);

                $view_list[] = [
                    'circle_name' => $circle_name,
                    'user_avatar' => $user_info->avatar,
                    'user_name' => $user_info->user_name,
                    'money' => $item->money,
                    'time' => $item->created_at->format('Y-m-d H:i:s'),
                ];
            }

            return $this->getResponse($view_list);


        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }


    /*
     * 得到对该圈子的竞价次数
     * 历史竞价次数
     */
    public function countHistory(Request $request, BecomeHost $host)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'ring_id' => 'integer',
                'app_id' => 'integer',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $ring_id = $arrRequest['ring_id'];
            $app_id = $arrRequest['app_id'];
            $count_number = $host->countHistory($app_id, $ring_id);

            return $this->getResponse($count_number + 1);

        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }

    }

}
