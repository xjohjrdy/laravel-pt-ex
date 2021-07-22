<?php

namespace App\Http\Controllers\Circle;

use App\Entitys\Ad\AdUserInfo;
use App\Entitys\Ad\RechargeOrder;
use App\Entitys\Ad\UserAccount;
use App\Entitys\App\AppUserInfo;
use App\Entitys\App\CircleCityKing;
use App\Entitys\App\CircleCityKingAdd;
use App\Entitys\App\CircleRingAdd;
use App\Entitys\App\TaobaoUser;
use App\Exceptions\ApiException;
use App\Services\Circle\LuckyMoney;
use App\Services\HeMengTong\HeMeToServices;
use App\Services\TencentCloud\CaptchaService;
use App\Services\Verify\Captcha;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Yansongda\Pay\Pay;

class LuckyMoneyController extends Controller
{
    public function getSum(Request $request)
    {

        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'circle_id' => 'integer',
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $circle_id = $arrRequest['circle_id'];
            $obj_ring_add = new CircleRingAdd();
            return $this->getResponse($obj_ring_add->getSum($circle_id));

        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }

    }

    public function sendRed(Request $request, LuckyMoney $luckyMoney)
    {

        //return $this->getInfoResponse(3001, '系统升级期间圈子暂停付款，预计1月1号升级完成，敬请期待！');
        DB::beginTransaction();
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'circle_id' => 'integer',
                'buy_type' => Rule::in([1, 2, 3]),
                'app_id' => 'integer',
                'price' => 'numeric',
                'number' => 'integer',
                'area_land' => 'required',
            ];

            $err_msg = [
                'price.integer' => '红包金额必须为整数。',
            ];

            $validator = Validator::make($arrRequest, $rules, $err_msg);

            if ($validator->fails()) {

                foreach ($validator->errors()->toArray() as $key => $item) {
                    if ($key == 'price') {
                        return $this->getInfoResponse('3002', @$item[0]);
                    }
                }

                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $circle_id = $arrRequest['circle_id'];
            $buy_type = $arrRequest['buy_type'];
            $app_id = $arrRequest['app_id'];
            $price = (int)$arrRequest['price'];
            $price_ptb = $price * 10;
            $number = $arrRequest['number'];
            $comment = isset($arrRequest['comment']) ? $arrRequest['comment'] : '';
            $area_land = $arrRequest['area_land'];
            $comment_img = isset($arrRequest['comment_img']) ? $arrRequest['comment_img'] : '';

            //拦截版本
            $request_device = $request->header('Accept-Device'); //设备类型
            $request_appversion = $request->header('Accept-Appversion'); //版本号
            if ($request_device != 'android' || $request_appversion < 191) {
                if ($app_id != 3675700) {
                    return $this->getInfoResponse('3003', '系统升级期间圈子暂停付款，预计1月1号升级完成，敬请期待！');
                }
            }


            if (Cache::has('lucky_money_send_red_' . $app_id)) {
                return $this->getInfoResponse('2005', '发红包频率过快.');
            }
            Cache::put('lucky_money_send_red_' . $app_id, 1, 0.5);

            if ($buy_type == 3) {
            }
            if ($buy_type == 2 && $app_id != 1694511) {
                return $this->getInfoResponse('3001', '支付宝正在升级中，请先用微信或者我的币支付！');
            }

            if ($price > 200) {
                return $this->getInfoResponse(3001, '单个红包金额不可超过200元');
            }
            $circle_info = $luckyMoney->getCircleInfo($circle_id);
            $king_info = $luckyMoney->getKingInfo($circle_info->king_id);

            if (empty($circle_info->app_id) && empty($king_info->app_id)) {
                $if_rate = 0;
                $if_population = 0;
                $red_value['remain_price'] = $price_ptb / 10;
                $red_value['red_have'] = $number;
            } elseif (!empty($circle_info->app_id) && empty($king_info->app_id)) {
                $if_rate = 0.1;
                $if_population = 1;
                $red_value['remain_price'] = ($price_ptb - round($price_ptb * 0.1)) / 10;
                $red_value['red_have'] = $number - 1;
            } elseif (empty($circle_info->app_id) && !empty($king_info->app_id)) {
                $if_rate = 0.01;
                $if_population = 1;
                $red_value['remain_price'] = ($price_ptb - round($price_ptb * 0.01)) / 10;
                $red_value['red_have'] = $number - 1;
            } elseif (!empty($circle_info->app_id) && !empty($king_info->app_id)) {
                $if_rate = 0.11;
                $if_population = 2;
                $red_value['remain_price'] = ($price_ptb - round($price_ptb * 0.11)) / 10;
                $red_value['red_have'] = $number - 2;
            }
            $ring_host_ptb = round($price_ptb * $if_rate);
            if (($number - $if_population) > ($price_ptb - $ring_host_ptb)) {
                return $this->getInfoResponse('3001', '红包个数过多,' . $price_ptb . '我的币最多发' . ($price_ptb - $ring_host_ptb) . '个红包');
            }

            if ($number < $if_population + 1) {
                return $this->getInfoResponse('3005', '红包至少发' . ($if_population + 1) . '个');
            }

            if ($price_ptb < 100) {
                return $this->getInfoResponse('3003', '红包最少发10元');
            }

            $count_times = 1;
            if ($buy_type == 1) {

                $obj_ad_user = new AdUserInfo();
                $obj_ad_info = $obj_ad_user->appToAdUserId($app_id);
                if (empty($obj_ad_info)) {
                    return false;
                }

//                $obj_account = new UserAccount();
//                $user_ptb = $obj_account->getUserAccount($obj_ad_info->uid)->extcredits4;

                $taobao_user = new TaobaoUser();//用户真实分佣表
                $int_taobao_user = $taobao_user->where('app_id', $app_id)->value('money');
                $int_taobao_user = empty($int_taobao_user) ? 0 : $int_taobao_user;

                if ($price > $int_taobao_user) {
                    return $this->getInfoResponse('3004', '余额不足');
                }
                $red_value['app_id'] = $app_id;
                $red_value['circle_id'] = $circle_id;
                $red_value['price'] = $price;
                $red_value['number'] = $number;
                $red_value['comment'] = mb_substr($comment, 0, 100);
                $red_value['status'] = 1;
                $red_value['order_id'] = 0;
                $red_value['area_land'] = $area_land;
                $red_value['comment_img'] = substr($comment_img, 0, 1500);

                $red_id = $luckyMoney->sendRed($red_value);

                $luckyMoney->takePtb($app_id, $price_ptb);

                if (!empty($circle_info->app_id)) {
                    $red_user_info = AppUserInfo::find($circle_info->app_id);
                    $red_time_value['red_id'] = $red_id;
                    $red_time_value['from_app_id'] = $app_id;
                    $red_time_value['to_app_id'] = $circle_info->app_id;
                    $red_time_value['to_app_username'] = $red_user_info->user_name;
                    $red_time_value['type'] = 2;
                    $red_time_value['to_app_img'] = $red_user_info->avatar;
                    $red_time_value['have'] = $ring_host_ptb;
                    $red_time_value['time'] = $count_times;

                    $luckyMoney->getRed($red_time_value);
                    $red_time_log = [
                        'app_id' => $circle_info->app_id,
                        'from_user_name' => $red_user_info->real_name,
                        'from_user_phone' => $red_user_info->phone,
                        'from_user_img' => $red_user_info->avatar,
                        'from_circle_name' => $circle_info->ico_title,
                        'from_circle_img' => $circle_info->ico_img,
                        'order_id' => $red_id,
                        'order_money' => $price_ptb,
                        'money' => $ring_host_ptb,
                        'type' => 3,
                    ];
                    $luckyMoney->addRedLog($red_time_log);
                    $count_times += 1;
                }
                if (!empty($king_info->app_id)) {

                    $obj_king_add = new  CircleCityKingAdd();
                    $king_add_info = $obj_king_add->where(['king_id' => $circle_info->king_id, 'app_id' => $king_info->app_id])->first();
                    if ((time() - strtotime($king_add_info->created_at)) > 60 * 60 * 24 * 365) {
                        $obj_king = new CircleCityKing();
                        $obj_king->where('id', $circle_info->king_id)->update(['app_id' => 0]);
                    } else {
                        $red_user_info = AppUserInfo::find($king_info->app_id);

                        $red_time_value['red_id'] = $red_id;
                        $red_time_value['from_app_id'] = $app_id;
                        $red_time_value['to_app_id'] = $king_info->app_id;
                        $red_time_value['to_app_username'] = $red_user_info->user_name;
                        $red_time_value['type'] = 3;
                        $red_time_value['to_app_img'] = $red_user_info->avatar;
                        $red_time_value['have'] = $ring_host_ptb * 0.1;
                        $red_time_value['time'] = $count_times;

                        $luckyMoney->getRed($red_time_value);
                        $red_time_log = [
                            'app_id' => $king_info->app_id,
                            'from_user_name' => $red_user_info->real_name,
                            'from_user_phone' => $red_user_info->phone,
                            'from_user_img' => $red_user_info->avatar,
                            'from_circle_name' => $circle_info->ico_title,
                            'from_circle_img' => $circle_info->ico_img,
                            'order_id' => $red_id,
                            'order_money' => $price_ptb,
                            'money' => $ring_host_ptb * 0.1,
                            'type' => 3,
                        ];
                        $luckyMoney->addRedLog($red_time_log);
                    }

                }
                DB::commit();
                return $this->getResponse('成功发红包');
            } elseif ($buy_type == 2) {
                $red_value['app_id'] = $app_id;
                $red_value['circle_id'] = $circle_id;
                $red_value['price'] = $price;
                $red_value['remain_price'] = ($price_ptb - $ring_host_ptb) / 10;
                $red_value['number'] = $number;
                $red_value['red_have'] = $number - 1;
                $red_value['comment'] = $comment;
                $red_value['status'] = 0;
                $red_value['order_id'] = $luckyMoney->getOrderId();
                $red_value['area_land'] = $area_land;
                $red_value['comment_img'] = $comment_img;

                $red_id = $luckyMoney->sendRed($red_value);

                if ($app_id == 1694511) $price = 0.01;

                //改为禾孟通支付
//                $ali_value['out_trade_no'] = $red_value['order_id'];
//                $ali_value['total_amount'] = $price;
//                $ali_value['subject'] = '商城购物 - ' . $price . '元';
//                $ali_value['body'] = '发圈子红包';
                $heMeToServices = new HeMeToServices();
                $data = $heMeToServices->appPay($price, $price, $red_value['order_id']);
                $res = json_decode($data, true);

                DB::commit();
                if (@$res['fcode'] != 10000) {
                    return $this->getResponse('购买失败！请联系客服');
                }
                return $this->getResponse(@$res['fcode_url']);
//                return Pay::alipay(config('ring.ali_red_config'))->app($ali_value);
            } elseif ($buy_type == 3) {

                if ($arrRequest['app_id'] == 1569840 || $arrRequest['app_id'] == 1694511) {
                    $price = 0.01;
                }
                $red_value['app_id'] = $app_id;
                $red_value['circle_id'] = $circle_id;
                $red_value['price'] = $price;
                $red_value['remain_price'] = ($price_ptb - $ring_host_ptb) / 10;
                $red_value['number'] = $number;
                $red_value['red_have'] = $number - 1;
                $red_value['comment'] = $comment;
                $red_value['status'] = 0;
                $red_value['order_id'] = $luckyMoney->getOrderId();
                $red_value['area_land'] = $area_land;
                $red_value['comment_img'] = $comment_img;

                $red_id = $luckyMoney->sendRed($red_value);

                //改为禾盟通支付
//                $order = [
//                    'out_trade_no' => $red_value['order_id'],
//                    'total_fee' => ($price * 100),
//                    'body' => '商城购物 - ' . $price . '元',
//                ];
//                $this->wechat_config['notify_url'] = config('ring.we_red_notify_url');
//                $pay = Pay::wechat(config('ring.we_red_config'))->app($order);
                $heMeToServices = new HeMeToServices();
                $data = $heMeToServices->appWxPay($price, $red_value['order_id'], $app_id);

                DB::commit();
                return $this->getResponse($data);
//                return $pay;
            }

        } catch (\Throwable $e) {
            Storage::disk('local')->append('callback_document/circle_red_error.txt', var_export('出现异常情况，文件' . $e->getFile() . ',行' . $e->getLine() . ',错误信息：' . $e->getMessage(), true));
            DB::rollBack();
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('【发红包】网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
     * 圈子发红包
     */
    public function sendRedV1(Request $request, LuckyMoney $luckyMoney)
    {
        DB::beginTransaction();
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'circle_id' => 'integer',
                'buy_type' => Rule::in([2, 3, 5]),
                'app_id' => 'integer',
                'price' => 'numeric',
                'number' => 'integer',
                'area_land' => 'required',
            ];

            $err_msg = [
                'price.integer' => '红包金额必须为整数。',
            ];

            $validator = Validator::make($arrRequest, $rules, $err_msg);

            if ($validator->fails()) {

                foreach ($validator->errors()->toArray() as $key => $item) {
                    if ($key == 'price') {
                        return $this->getInfoResponse('3002', @$item[0]);
                    }
                }

                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $circle_id = $arrRequest['circle_id'];
            $buy_type = $arrRequest['buy_type'];
            $app_id = $arrRequest['app_id'];
            $price = (int)$arrRequest['price'];
            $price_ptb = $price * 10;
            $number = $arrRequest['number'];
            $comment = isset($arrRequest['comment']) ? $arrRequest['comment'] : '';
            $area_land = $arrRequest['area_land'];
            $comment_img = isset($arrRequest['comment_img']) ? $arrRequest['comment_img'] : '';

            //拦截版本
            $request_device = $request->header('Accept-Device'); //设备类型
            $request_appversion = $request->header('Accept-Appversion'); //版本号
            if (($request_device == 'android' && $request_appversion < 191) || ($request_device == 'ios' && version_compare($request_appversion,'4.6.3','<'))) {
                if ($app_id != 3675700) {
                    return $this->getInfoResponse('3003', '系统升级期间圈子暂停付款，预计1月1号升级完成，敬请期待！');
                }
            }

            if (Cache::has('lucky_money_send_red_' . $app_id)) {
                return $this->getInfoResponse('2005', '发红包频率过快.');
            }
            Cache::put('lucky_money_send_red_' . $app_id, 1, 0.5);

            if ($buy_type == 3) {
            }
//            if ($buy_type == 2 && $app_id != 1694511) {
//                return $this->getInfoResponse('3001', '支付宝正在升级中，请先用微信或者余额支付！');
//            }

            if ($price > 200) {
                return $this->getInfoResponse(3001, '单个红包金额不可超过200元');
            }
            $circle_info = $luckyMoney->getCircleInfo($circle_id);
            $king_info = $luckyMoney->getKingInfo($circle_info->king_id);

            if (empty($circle_info->app_id) && empty($king_info->app_id)) {
                $if_rate = 0;
                $if_population = 0;
                $red_value['remain_price'] = $price_ptb / 10;
                $red_value['red_have'] = $number;
            } elseif (!empty($circle_info->app_id) && empty($king_info->app_id)) {
                $if_rate = 0.1;
                $if_population = 1;
                $red_value['remain_price'] = ($price_ptb - round($price_ptb * 0.1)) / 10;
                $red_value['red_have'] = $number - 1;
            } elseif (empty($circle_info->app_id) && !empty($king_info->app_id)) {
                $if_rate = 0.01;
                $if_population = 1;
                $red_value['remain_price'] = ($price_ptb - round($price_ptb * 0.01)) / 10;
                $red_value['red_have'] = $number - 1;
            } elseif (!empty($circle_info->app_id) && !empty($king_info->app_id)) {
                $if_rate = 0.11;
                $if_population = 2;
                $red_value['remain_price'] = ($price_ptb - round($price_ptb * 0.11)) / 10;
                $red_value['red_have'] = $number - 2;
            }
            $ring_host_ptb = round($price_ptb * $if_rate);
            if (($number - $if_population) > ($price_ptb - $ring_host_ptb)) {
                return $this->getInfoResponse('3001', '红包个数过多,' . $price_ptb / 10 . '元最多发' . ($price_ptb - $ring_host_ptb) . '个红包');
            }

            if ($number < $if_population + 1) {
                return $this->getInfoResponse('3005', '红包至少发' . ($if_population + 1) . '个');
            }

            if ($price_ptb < 100) {
                return $this->getInfoResponse('3003', '红包最少发10元');
            }

            $count_times = 1;
            if ($buy_type == 5) {
                $obj_ad_user = new AdUserInfo();
                $obj_ad_info = $obj_ad_user->appToAdUserId($app_id);
                if (empty($obj_ad_info)) {
                    return false;
                }

                $taobao_user = new TaobaoUser();//用户真实分佣表
                $int_taobao_user = $taobao_user->where('app_id', $app_id)->value('money');
                $int_taobao_user = empty($int_taobao_user) ? 0 : $int_taobao_user;

                if ($price > $int_taobao_user) {
                    return $this->getInfoResponse('3004', '余额不足');
                }
                $red_value['app_id'] = $app_id;
                $red_value['circle_id'] = $circle_id;
                $red_value['price'] = $price;
                $red_value['number'] = $number;
                $red_value['comment'] = mb_substr($comment, 0, 100);
                $red_value['status'] = 1;
                $red_value['order_id'] = 0;
                $red_value['area_land'] = $area_land;
                $red_value['comment_img'] = substr($comment_img, 0, 1500);

                $red_id = $luckyMoney->sendRed($red_value);

                //扣除余额
                $luckyMoney->takeMoney($app_id, $price);

                if (!empty($circle_info->app_id)) {
                    $red_user_info = AppUserInfo::find($circle_info->app_id);
                    $red_time_value['red_id'] = $red_id;
                    $red_time_value['from_app_id'] = $app_id;
                    $red_time_value['to_app_id'] = $circle_info->app_id;
                    $red_time_value['to_app_username'] = $red_user_info->user_name;
                    $red_time_value['type'] = 2;
                    $red_time_value['to_app_img'] = $red_user_info->avatar;
                    $red_time_value['have'] = $ring_host_ptb;
                    $red_time_value['time'] = $count_times;

                    $luckyMoney->getRed($red_time_value);
                    $red_time_log = [
                        'app_id' => $circle_info->app_id,
                        'from_user_name' => $red_user_info->real_name,
                        'from_user_phone' => $red_user_info->phone,
                        'from_user_img' => $red_user_info->avatar,
                        'from_circle_name' => $circle_info->ico_title,
                        'from_circle_img' => $circle_info->ico_img,
                        'order_id' => $red_id,
                        'order_money' => $price_ptb,
                        'money' => $ring_host_ptb,
                        'type' => 3,
                    ];
                    $luckyMoney->addRedLog($red_time_log);
                    $count_times += 1;
                }
                if (!empty($king_info->app_id)) {

                    $obj_king_add = new  CircleCityKingAdd();
                    $king_add_info = $obj_king_add->where(['king_id' => $circle_info->king_id, 'app_id' => $king_info->app_id])->first();
                    if ((time() - strtotime($king_add_info->created_at)) > 60 * 60 * 24 * 365) {
                        $obj_king = new CircleCityKing();
                        $obj_king->where('id', $circle_info->king_id)->update(['app_id' => 0]);
                    } else {
                        $red_user_info = AppUserInfo::find($king_info->app_id);

                        $red_time_value['red_id'] = $red_id;
                        $red_time_value['from_app_id'] = $app_id;
                        $red_time_value['to_app_id'] = $king_info->app_id;
                        $red_time_value['to_app_username'] = $red_user_info->user_name;
                        $red_time_value['type'] = 3;
                        $red_time_value['to_app_img'] = $red_user_info->avatar;
                        $red_time_value['have'] = $ring_host_ptb * 0.1;
                        $red_time_value['time'] = $count_times;

                        $luckyMoney->getRed($red_time_value);
                        $red_time_log = [
                            'app_id' => $king_info->app_id,
                            'from_user_name' => $red_user_info->real_name,
                            'from_user_phone' => $red_user_info->phone,
                            'from_user_img' => $red_user_info->avatar,
                            'from_circle_name' => $circle_info->ico_title,
                            'from_circle_img' => $circle_info->ico_img,
                            'order_id' => $red_id,
                            'order_money' => $price_ptb,
                            'money' => $ring_host_ptb * 0.1,
                            'type' => 3,
                        ];
                        $luckyMoney->addRedLog($red_time_log);
                    }

                }
                DB::commit();
                return $this->getResponse('成功发红包');
            } elseif ($buy_type == 2) {
                $red_value['app_id'] = $app_id;
                $red_value['circle_id'] = $circle_id;
                $red_value['price'] = $price;
                $red_value['remain_price'] = ($price_ptb - $ring_host_ptb) / 10;
                $red_value['number'] = $number;
                $red_value['red_have'] = $number - 1;
                $red_value['comment'] = $comment;
                $red_value['status'] = 0;
                $red_value['order_id'] = $luckyMoney->getOrderId();
                $red_value['area_land'] = $area_land;
                $red_value['comment_img'] = $comment_img;
                $red_id = $luckyMoney->sendRed($red_value);

                if ($app_id == 1694511) $price = 0.01;

                //改为禾孟通支付
//                $ali_value['out_trade_no'] = $red_value['order_id'];
//                $ali_value['total_amount'] = $price;
//                $ali_value['subject'] = '商城购物 - ' . $price . '元';
//                $ali_value['body'] = '发圈子红包';

                $heMeToServices = new HeMeToServices();
                $data = $heMeToServices->appPayCircleSend($price, $price, $red_value['order_id']);
                $res = json_decode($data, true);
                DB::commit();
                if (@$res['fcode'] != 10000) {
                    return $this->getResponse('购买失败！请联系客服');
                }
                return $this->getResponse(@$res['fcode_url']);
//                return Pay::alipay(config('ring.ali_red_config'))->app($ali_value);
//                $ali_secret = Pay::alipay(config('ring.ali_red_config'))->app($ali_value);
//                return $this->getResponse($ali_secret->getContent());
            } elseif ($buy_type == 3) {

                if ($arrRequest['app_id'] == 1569840 || $arrRequest['app_id'] == 1694511) {
                    $price = 0.01;
                }
                $red_value['app_id'] = $app_id;
                $red_value['circle_id'] = $circle_id;
                $red_value['price'] = $price;
                $red_value['remain_price'] = ($price_ptb - $ring_host_ptb) / 10;
                $red_value['number'] = $number;
                $red_value['red_have'] = $number - 1;
                $red_value['comment'] = $comment;
                $red_value['status'] = 0;
                $red_value['order_id'] = $luckyMoney->getOrderId();
                $red_value['area_land'] = $area_land;
                $red_value['comment_img'] = $comment_img;

                $red_id = $luckyMoney->sendRed($red_value);

                //改为禾孟通支付
//                $order = [
//                    'out_trade_no' => $red_value['order_id'],
//                    'total_fee' => ($price * 100),
//                    'body' => '商城购物 - ' . $price . '元',
//                ];
//                $this->wechat_config['notify_url'] = config('ring.we_red_notify_url');
//                $pay = Pay::wechat(config('ring.we_red_config'))->app($order);
//                $we_secret = Pay::wechat(config('ring.we_red_config'))->app($order);

                $heMeToServices = new HeMeToServices();
                $data = $heMeToServices->appWxPayCircleSend($price, $red_value['order_id'], $app_id);
                DB::commit();
                return $this->getResponse($data);
//                return $pay;
//                return $this->getResponse($we_secret->getContent());
            }

        } catch (\Throwable $e) {
            Storage::disk('local')->append('callback_document/circle_red_error.txt', var_export('出现异常情况，文件' . $e->getFile() . ',行' . $e->getLine() . ',错误信息：' . $e->getMessage(), true));
            DB::rollBack();
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('【发红包】网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
     * 支付宝回调 post 形式
     */
    public function aliNotify(Request $request, LuckyMoney $luckyMoney)
    {
        try {
            $this->log('---------------start-test----------');
            $this->log($request->input());
            $this->log($request->getUri());
            $obj_ali_pay = Pay::alipay(config('ring.ali_red_config'));
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
            $order_info = $luckyMoney->getInfoByOrderId($order_id);

            if (empty($order_info)) {
                $this->log('不存在该订单：' . $order_id);
                $this->log('---------------end_error----------');
                return 'error';
            }
            if ($order_info->price != $actual) {
                $this->log('该用户实际支付金额有误：实付' . $actual . '元');
                $this->log('---------------end_error----------');
            }
            if ($order_info->status == 1) {
                $this->log('该笔订单已经支付过：' . $order_id);
                $this->log('---------------end_error----------');
                return 'error';
            }
            $this->log('开始处理发红包，将订单处理成支付状态');
            $luckyMoney->updateRed($order_id);

            $red_id = $order_info->id;
            $circle_id = $order_info->circle_id;
            $app_id = $order_info->app_id;
            $red_price = $order_info->price;
            $remain_price = $order_info->remain_price;
            $circle_info = $luckyMoney->getCircleInfo($circle_id);
            $king_info = $luckyMoney->getKingInfo($circle_info->king_id);

            $count_times = 1;
            $ring_host_ptb = round($red_price * 0.1 * 10);

            if (!empty($circle_info->app_id)) {
                $this->log('开始添加红包记录，并给圈主分我的币');
                $red_user_info = AppUserInfo::find($circle_info->app_id);
                $red_time_value['red_id'] = $red_id;
                $red_time_value['from_app_id'] = $app_id;
                $red_time_value['to_app_id'] = $circle_info->app_id;
                $red_time_value['to_app_username'] = $red_user_info->user_name;
                $red_time_value['type'] = 2;
                $red_time_value['to_app_img'] = $red_user_info->avatar;
                $red_time_value['have'] = $ring_host_ptb;
                $red_time_value['time'] = $count_times;

                $luckyMoney->getRed($red_time_value);

                $this->log('添加圈主分佣记录');
                $red_time_log = [
                    'app_id' => $circle_info->app_id,
                    'from_user_name' => $red_user_info->real_name,
                    'from_user_phone' => $red_user_info->phone,
                    'from_user_img' => $red_user_info->avatar,
                    'from_circle_name' => $circle_info->ico_title,
                    'from_circle_img' => $circle_info->ico_img,
                    'order_id' => $red_id,
                    'order_money' => $red_price * 10,
                    'money' => $ring_host_ptb,
                    'type' => 3,
                ];
                $luckyMoney->addRedLog($red_time_log);
                $count_times += 1;
            }

            if (!empty($king_info->app_id)) {
                $red_user_info = AppUserInfo::find($king_info->app_id);

                $red_time_value['red_id'] = $red_id;
                $red_time_value['from_app_id'] = $app_id;
                $red_time_value['to_app_id'] = $king_info->app_id;
                $red_time_value['to_app_username'] = $red_user_info->user_name;
                $red_time_value['type'] = 3;
                $red_time_value['to_app_img'] = $red_user_info->avatar;
                $red_time_value['have'] = $ring_host_ptb * 0.1;
                $red_time_value['time'] = $count_times;

                $luckyMoney->getRed($red_time_value);
                $red_time_log = [
                    'app_id' => $king_info->app_id,
                    'from_user_name' => $red_user_info->real_name,
                    'from_user_phone' => $red_user_info->phone,
                    'from_user_img' => $red_user_info->avatar,
                    'from_circle_name' => $circle_info->ico_title,
                    'from_circle_img' => $circle_info->ico_img,
                    'order_id' => $red_id,
                    'order_money' => $red_price * 10,
                    'money' => $ring_host_ptb * 0.1,
                    'type' => 3,
                ];
                $luckyMoney->addRedLog($red_time_log);
            }
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
    public function weNotify(Request $request, LuckyMoney $luckyMoney)
    {
        $this->weLog('---------------start----------');

        $pay = Pay::wechat(config('ring.we_red_config'));
        try {
            $obj_data = $pay->verify();
            if ($obj_data->return_code != "SUCCESS") {
                $this->weLog('错误信息：' . $obj_data->return_msg);
                $this->weLog('---------------end----------');
                return 'error';
            }

            $order_id = $obj_data->out_trade_no;
            $actual = $obj_data->total_fee;

            $this->weLog('开始查询订单：' . $order_id);
            $order_info = $luckyMoney->getInfoByOrderId($order_id);

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
            $this->weLog('开始处理发红包，将订单处理成支付状态');
            $luckyMoney->updateRed($order_id);

            $red_id = $order_info->id;
            $circle_id = $order_info->circle_id;
            $app_id = $order_info->app_id;
            $red_price = $order_info->price;
            $remain_price = $order_info->remain_price;
            $circle_info = $luckyMoney->getCircleInfo($circle_id);
            $king_info = $luckyMoney->getKingInfo($circle_info->king_id);

            $count_times = 1;
            $ring_host_ptb = round($red_price * 0.1 * 10);

            if (!empty($circle_info->app_id)) {
                $this->weLog('开始添加红包记录，并给圈主分我的币');
                $red_user_info = AppUserInfo::find($circle_info->app_id);
                $red_time_value['red_id'] = $red_id;
                $red_time_value['from_app_id'] = $app_id;
                $red_time_value['to_app_id'] = $circle_info->app_id;
                $red_time_value['to_app_username'] = $red_user_info->user_name;
                $red_time_value['type'] = 2;
                $red_time_value['to_app_img'] = $red_user_info->avatar;
                $red_time_value['have'] = $ring_host_ptb;
                $red_time_value['time'] = $count_times;

                $luckyMoney->getRed($red_time_value);

                $this->weLog('添加圈主分佣记录');
                $red_time_log = [
                    'app_id' => $circle_info->app_id,
                    'from_user_name' => $red_user_info->real_name,
                    'from_user_phone' => $red_user_info->phone,
                    'from_user_img' => $red_user_info->avatar,
                    'from_circle_name' => $circle_info->ico_title,
                    'from_circle_img' => $circle_info->ico_img,
                    'order_id' => $red_id,
                    'order_money' => $red_price * 10,
                    'money' => $ring_host_ptb,
                    'type' => 3,
                ];
                $luckyMoney->addRedLog($red_time_log);
                $count_times += 1;
            }

            if (!empty($king_info->app_id)) {
                $red_user_info = AppUserInfo::find($king_info->app_id);

                $red_time_value['red_id'] = $red_id;
                $red_time_value['from_app_id'] = $app_id;
                $red_time_value['to_app_id'] = $king_info->app_id;
                $red_time_value['to_app_username'] = $red_user_info->user_name;
                $red_time_value['type'] = 3;
                $red_time_value['to_app_img'] = $red_user_info->avatar;
                $red_time_value['have'] = $ring_host_ptb * 0.1;
                $red_time_value['time'] = $count_times;

                $luckyMoney->getRed($red_time_value);
                $red_time_log = [
                    'app_id' => $king_info->app_id,
                    'from_user_name' => $red_user_info->real_name,
                    'from_user_phone' => $red_user_info->phone,
                    'from_user_img' => $red_user_info->avatar,
                    'from_circle_name' => $circle_info->ico_title,
                    'from_circle_img' => $circle_info->ico_img,
                    'order_id' => $red_id,
                    'order_money' => $red_price * 10,
                    'money' => $ring_host_ptb * 0.1,
                    'type' => 3,
                ];
                $luckyMoney->addRedLog($red_time_log);
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
        Storage::disk('local')->append('callback_document/circle_red_alipay_notify.txt', var_export($msg, true));
    }

    /*
     * 记录日志
     */
    private function weLog($msg)
    {
        Storage::disk('local')->append('callback_document/circle_red_wechat_notify.txt', var_export($msg, true));
    }

    /**
     * 新版领取红包
     * 增加验证码功能
     * 1.当一个用户当天领取超过3次,每次领取红包的时候，都需要弹回js代码
     * 判断条件
     * $arrRequest['token']存在的话，不弹js代码，走验证流程
     *
     * 验证失败，则弹回验证失败
     * 验证通过，则继续流程
     * @param Request $request
     * @param LuckyMoney $luckyMoney
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */

    public function getRedForWuHang(Request $request, LuckyMoney $luckyMoney, Captcha $captcha)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $rate_val = 1;
            if ($request->header('Accept-Version')) {
                if (version_compare($request->header('Accept-Version'), '1.0.0', '>=')) {
                    $rate_val = 10;
                }
            }
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'id' => 'integer',
                'app_id' => 'integer',
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }


            $red_id = $arrRequest['id'];
            $app_id = $arrRequest['app_id'];
            $count_red = $luckyMoney->getRedCount($app_id);

            $ad_user_info = AdUserInfo::where(['pt_id' => $app_id])->first();

            $groupid = $ad_user_info->groupid;
            if ($groupid != 23 && $groupid != 24) {
                if (RechargeOrder::where(['uid' => $ad_user_info->uid, 'price' => 10, 'status' => 2])->exists()) {
                    if ($count_red >= 10) {
                        return $this->getInfoResponse(3007, '对不起，广告包用户每天仅限领取10个红包！赶快去升级吧！');
                    }
                } else {
                    if ($count_red >= 1) {
                        return $this->getInfoResponse(3007, '对不起，普通用户每天仅限领取1个红包！赶快去升级吧！');
                    }
                }
            }
            /*
             * 增加验证码功能
             * 1.当一个用户当天领取超过3次,每次领取红包的时候，都需要弹回js代码
             * 判断条件
             * $arrRequest['token']存在的话，不弹js代码，走验证流程
             *
             * 验证失败，则弹回验证失败
             * 验证通过，则继续流程
             */
            if ($count_red >= 3 && empty($arrRequest['ticket'])) {
//                $res = $captcha->getJsUrl($request->ip());
                return $this->getInfoResponse('5657', '');
            }
            // ticket,randstr,ip
            if (!empty($arrRequest['ticket'])) {

                if (empty($arrRequest['randstr'])) { //如果为空则走旧版红包校验逻辑

                    $res = $captcha->check($arrRequest['ticket'], $request->ip());
                    if ($res['code'] <> 0) {
                        return $this->getInfoResponse('4421', '验证失败！');
                    }

                } else {
                    @$validate_params = [
                        'Ticket' => $arrRequest['ticket'], //验证码返回给用户的票据
                        'UserIp' => $arrRequest['ip'],//用户操作来源的外网 IP
                        'Randstr' => $arrRequest['randstr'], //验证票据需要的随机字符串
                        'app_id' => $arrRequest['app_id']
                    ];

                    $service = new CaptchaService();
                    $res = $service->validateCaptcha($validate_params);
                    if ($res->CaptchaCode == 1) {
//                    return $this->getResponse('验证成功！');
                    } else {
                        return $this->getInfoResponse($res->CaptchaCode, $res->CaptchaMsg);
//                        return $this->getInfoResponse('4421', '验证失败！');
                    }
                }

            }


            $red_where['id'] = $red_id;
            $red_where['status'] = 1;
            $red_info = $luckyMoney->getRedInfo($red_where);
            unset($red_where);
            if (empty($red_info)) {
                throw new ApiException('该红包不存在', 4001);
            }


            $circle_id = $red_info->circle_id;
            if (!$luckyMoney->belongToCircle($app_id, $circle_id)) {
                return $this->getInfoResponse('3006', $circle_id);
            }

            $residue_degree = $red_info->red_have;
            $residue_money = $red_info->remain_price;
            if ($residue_degree == 0) {
                return $this->getInfoResponse('3001', '红包已被领取完毕');
            }
            if ($luckyMoney->isGetRed($app_id, $red_id)) {
                return $this->getInfoResponse('3002', '您已领取过该红包');
            }
            if ($residue_degree == 1) {
                $get_red_ptb = $residue_money * 10;
                $red_where['remain_price'] = 0;
            } else {
                $red_max = round(($residue_money / $residue_degree) * 20) - 1;
                $red_min = 1;
                $get_red_ptb = mt_rand($red_min, $red_max);
                $red_where['remain_price'] = $residue_money - ($get_red_ptb / 10);
            }

            $luckyMoney->takeRed($red_id, $red_where);
            $red_user_info = AppUserInfo::find($app_id);
            $red_time_value['red_id'] = $red_id;
            $red_time_value['to_app_username'] = $red_user_info->user_name;
            $red_time_value['type'] = 1;
            $red_time_value['to_app_img'] = $red_user_info->avatar;
            $red_time_value['from_app_id'] = $red_info->app_id;
            $red_time_value['to_app_id'] = $app_id;
            $red_time_value['have'] = $get_red_ptb;
            $red_time_value['time'] = $red_info->number - $residue_degree + 1;
            $red_time_value['ip'] = sprintf("%u", ip2long($request->ip()));

            $luckyMoney->getRed($red_time_value);
            return $this->getResponse($get_red_ptb / $rate_val);

        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
     * 用户领红包
     */
    public function getRed(Request $request, LuckyMoney $luckyMoney)
    {
        return $this->getInfoResponse('4004', '新版已更新！请使用新版领取红包！');
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'id' => 'integer',
                'app_id' => 'integer',
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }


            $red_id = $arrRequest['id'];
            $app_id = $arrRequest['app_id'];
            $count_red = $luckyMoney->getRedCount($app_id);

            $ad_user_info = AdUserInfo::where(['pt_id' => $app_id])->first();

            $groupid = $ad_user_info->groupid;
            if ($groupid != 23 && $groupid != 24) {
                if (RechargeOrder::where(['uid' => $ad_user_info->uid, 'price' => 10, 'status' => 2])->exists()) {
                    if ($count_red >= 10) {
                        return $this->getInfoResponse(3007, '对不起，广告包用户每天仅限领取10个红包！赶快去升级吧！');
                    }
                } else {
                    if ($count_red >= 1) {
                        return $this->getInfoResponse(3007, '对不起，普通用户每天仅限领取1个红包！赶快去升级吧！');
                    }
                }
            }
            $red_where['id'] = $red_id;
            $red_where['status'] = 1;
            $red_info = $luckyMoney->getRedInfo($red_where);
            unset($red_where);
            if (empty($red_info)) {
                throw new ApiException('该红包不存在', 4001);
            }


            $circle_id = $red_info->circle_id;
            if (!$luckyMoney->belongToCircle($app_id, $circle_id)) {
                return $this->getInfoResponse('3006', $circle_id);
            }

            $residue_degree = $red_info->red_have;
            $residue_money = $red_info->remain_price;
            if ($residue_degree == 0) {
                return $this->getInfoResponse('3001', '红包已被领取完毕');
            }
            if ($luckyMoney->isGetRed($app_id, $red_id)) {
                return $this->getInfoResponse('3002', '您已领取过该红包');
            }
            if ($residue_degree == 1) {
                $get_red_ptb = $residue_money * 10;
                $red_where['remain_price'] = 0;
            } else {
                $red_max = round(($residue_money / $residue_degree) * 20) - 1;
                $red_min = 1;
                $get_red_ptb = mt_rand($red_min, $red_max);
                $red_where['remain_price'] = $residue_money - ($get_red_ptb / 10);
            }

            $luckyMoney->takeRed($red_id, $red_where);
            $red_user_info = AppUserInfo::find($app_id);
            $red_time_value['red_id'] = $red_id;
            $red_time_value['to_app_username'] = $red_user_info->user_name;
            $red_time_value['type'] = 1;
            $red_time_value['to_app_img'] = $red_user_info->avatar;
            $red_time_value['from_app_id'] = $red_info->app_id;
            $red_time_value['to_app_id'] = $app_id;
            $red_time_value['have'] = $get_red_ptb;
            $red_time_value['time'] = $red_info->number - $residue_degree + 1;
            $red_time_value['ip'] = sprintf("%u", ip2long($request->ip()));

            $luckyMoney->getRed($red_time_value);
            return $this->getResponse($get_red_ptb);

        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
     * 获取当前圈子可用红包
     */
    public function getList(Request $request, LuckyMoney $luckyMoney)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'circle_id' => 'integer',
                'app_id' => 'integer',
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $circle_id = $arrRequest['circle_id'];
            $app_id = $arrRequest['app_id'];

            $circle_list = $luckyMoney->getCircleList($circle_id);
            $red_add_list = $luckyMoney->getAddList($app_id);

            foreach ($circle_list as &$value) {
                $value->get = 1;
                foreach ($red_add_list as $item) {
                    if ($value->id == $item) {
                        $value->get = 2;
                    }
                }
            }
            $red_count = count($circle_list);

            if ($red_count == 0) {
                return $this->getResponse([
                    ['get' => 4],
                    ['get' => 4],
                    ['get' => 4],
                    ['get' => 4],
                    ['get' => 4]
                ]);
            }

            if ($red_count < 5) {
                for ($i = 0; $i < (5 - $red_count); $i++) {
                    $circle_list->push(['get' => 3]);
                }
            }
            foreach ($circle_list as $key => $row) {
                $distance[$key] = $row['get'];
            }
            $circle_list = $circle_list->toArray();
            array_multisort($distance, SORT_ASC, $circle_list);

            return $this->getResponse($circle_list);

        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * 首页拉取首页的红包，注意领取过，已经抢完了的红包
     * 地图上也要，多坐标字段
     * @param Request $request
     * @param LuckyMoney $luckyMoney
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function getAllList(Request $request, LuckyMoney $luckyMoney)
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

            $red_list = $luckyMoney->getAllList();
            $red_add_list = $luckyMoney->getAddList($app_id);

            foreach ($red_list as &$value) {

                $value->get = 1;
                foreach ($red_add_list as $item) {
                    if ($value->id == $item) {
                        $value->get = 2;
                        break;
                    }
                }
                $circle_info = $luckyMoney->getCircleInfo($value->circle_id);
                if ($circle_info->add_price > 0) {
                    $value->get = 5;
                }
            }
            $view_data = [];
            foreach ($red_list as $item) {
                if ($item->get != 5) {
                    $view_data[] = $item;
                }
            }
            $red_count = count($view_data);

            if ($red_count == 0) {
                return $this->getResponse([
                    ['get' => 4],
                    ['get' => 4],
                    ['get' => 4],
                    ['get' => 4],
                    ['get' => 4]
                ]);
            }

            if ($red_count < 5) {
                for ($i = 0; $i < (5 - $red_count); $i++) {
                    array_push($view_data, ['get' => 3]);
                }
            }
            foreach ($view_data as $key => $row) {
                $distance[$key] = $row['get'];
            }

            array_multisort($distance, SORT_ASC, $view_data);

            return $this->getResponse($view_data);

        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * 红包详情页拉列表
     * @param Request $request
     * @param LuckyMoney $luckyMoney
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function getInfoList(Request $request, LuckyMoney $luckyMoney, AppUserInfo $appUserInfo)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $rate_val = 1;
            if ($request->header('Accept-Version')) {
                if (version_compare($request->header('Accept-Version'), '1.0.0', '>=')) {
                    $rate_val = 10;
                }
            }
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'red_id' => 'integer',
                'app_id' => 'integer',
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $red_id = $arrRequest['red_id'];
            $app_id = $arrRequest['app_id'];

            $red_where['id'] = $red_id;
            $red_where['status'] = 1;
            $red_info = $luckyMoney->getRedInfo($red_where);
            if (empty($red_info)) {
                throw new ApiException('不存在该红包：' . $validator->errors(), 3003);
            }

            $circle_id = $red_info->circle_id;
            if (!$luckyMoney->belongToCircle($app_id, $circle_id)) {
                return $this->getInfoResponse('3006', (string)$circle_id);
            }
            $red_user_info = $appUserInfo->where(['id' => $red_info->app_id])->first();
            $red_view['username'] = $red_user_info->user_name;
            $red_view['avatar'] = $red_user_info->avatar;

            $red_view['price_ptb'] = $red_info->price * 10 / $rate_val;
            $red_view['comment'] = $red_info->comment;
            $red_view['comment_img'] = $red_info->comment_img;
            $red_view['ratio_number'] = ($red_info->number - $red_info->red_have) . '/' . $red_info->number;
            $red_view['ratio_money'] = (($red_info->price - $red_info->remain_price) * 10 / $rate_val) . '/' . ($red_info->price * 10 / $rate_val);
            $list_record = $luckyMoney->getRecord($red_id);

            foreach ($list_record as &$item) {
                $item->ptb /= $rate_val;
            }

            $red_view['list'] = $list_record;

            return $this->getResponse($red_view);

        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }
}
