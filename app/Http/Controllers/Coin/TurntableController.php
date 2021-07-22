<?php

namespace App\Http\Controllers\Coin;

use App\Entitys\App\AppUserInfo;
use App\Entitys\App\CoinShopGoods;
use App\Entitys\App\CoinTurntable;
use App\Entitys\App\CoinTurntableGetLog;
use App\Entitys\App\CoinTurntableOrders;
use App\Entitys\App\CoinTurntablePrize;
use App\Entitys\App\CoinUser;
use App\Entitys\App\CoinVirtualGoods;
use App\Entitys\App\ShopAddress;
use App\Entitys\App\TaobaoChangeUserLog;
use App\Entitys\App\TaobaoUser;
use App\Exceptions\ApiException;
use App\Services\CoinPlate\CoinCommonService;
use App\Services\Common\CommonFunction;
use App\Services\Common\UserMoney;
use App\Services\CoinPlate\TurntableService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TurntableController extends Controller
{
    /*
     * 转盘首页
     */
    public function turntableIndex(Request $request, AppUserInfo $appUserInfo)
    {
        try {//仅用于测试兼容旧版-start
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'app_id' => 'required',         #必须整数
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            //取用户app_id
            $app_id = $arrRequest['app_id'];
            $turntable_id = empty($arrRequest['turntable_id']) ? 1 : $arrRequest['turntable_id'];

            /***********************************/
            $coinUser = new CoinUser();
            $coinTurntable = new CoinTurntable();
            $coinTurntablePrize = new CoinTurntablePrize();
            $turntableService = new TurntableService();

            //开始处理逻辑问题
            $obj_user_info = $appUserInfo->getUserInfo($app_id);
            if (empty($obj_user_info)) {
                return $this->getInfoResponse('1001', '用户数据不存在！');//错误返回数据
            }

            //得到转盘信息
            $obj_turntable_info = $coinTurntable->getTurntableInfo($turntable_id);
            if (empty($obj_turntable_info)) {
                return $this->getInfoResponse('1002', '该转盘不存在或已被禁用！');//错误返回数据
            }

            //得到抽奖次数
            $turntable_count = $coinUser->where('app_id', $app_id)->value('turntable_count');
            $turntable_count = empty($turntable_count) ? 0 : $turntable_count;

            //根据转盘id得到所有奖品信息
            $obj_turntable_prize_info = $coinTurntablePrize->getTurntablePrizeInfo($turntable_id);

            //随机生成滚动中奖记录
            $roll_win_prize_info = $turntableService->rollWinPrizeInfo(10, $turntable_id);

            $arr_data = [
                'turntable_info' => $obj_turntable_info,
                'prize_info' => $obj_turntable_prize_info,
                'roll_win_prize_info' => $roll_win_prize_info,
                'turntable_count' => $turntable_count
            ];
            return $this->getResponse($arr_data);//正常返回数据
            /***********************************/
        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
     * 开始抽奖
     */
    public function startTurntableLuckyDraw(Request $request, AppUserInfo $appUserInfo)
    {
        try {//仅用于测试兼容旧版-start
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'app_id' => 'required',         #必须有值
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            //取用户app_id
            $app_id = $arrRequest['app_id'];
            $turntable_id = empty($arrRequest['turntable_id']) ? 1 : $arrRequest['turntable_id'];

            //缓存拦截
//            if (Cache::has('turntable_lucky_draw_' . $app_id)) {
//                return $this->getInfoResponse('1004', '抽奖频率过快，请稍后再试！');
//            }
//            Cache::put('turntable_lucky_draw_' . $app_id, 1, 0.05);

            /***********************************/
            //开始处理逻辑问题
            $obj_user_info = $appUserInfo->getUserInfo($app_id);
            if (empty($obj_user_info)) {
                return $this->getInfoResponse('1001', '用户数据不存在！');//错误返回数据
            }

            //验证抽奖次数
            $coinUser = new CoinUser();
            $turntable_count = $coinUser->where('app_id', $app_id)->value('turntable_count');
            $turntable_count = empty($turntable_count) ? 0 : $turntable_count;
            if ($turntable_count <= 0) {
                return $this->getInfoResponse('1002', '抽奖次数不够!！');//错误返回数据
            }

            //验证转盘信息
            $coinTurntable = new CoinTurntable();
            $obj_turntable_info = $coinTurntable->getTurntableInfo($turntable_id);
            if (empty($obj_turntable_info)) {
                return $this->getInfoResponse('1003', '该转盘不存在或已被禁用！');//错误返回数据
            }

            //根据转盘id得到 可抽的奖品信息
            $coinTurntablePrize = new CoinTurntablePrize();
            $obj_turntable_prize_info = $coinTurntablePrize->getTurntableValidPrizeInfo($turntable_id);

            //开始抽奖
            $turntableService = new TurntableService();
            $data = [];
            foreach ($obj_turntable_prize_info as $v) {
                if ($v->number < ($v->already_get + 1)) continue;//跳过已被抽完的奖品

                $data[] = [
                    'id' => $v->id,
                    'name' => $v->title,
                    'type' => $v->type,
                    'img' => $v->img,
                    'luck_draw_get' => $v->luck_draw_get,
                    'probability' => $v->win_probability,
                ];
            }

            $win_result = $turntableService->startLuckyDraw($data);

            if (empty($win_result)) {
                return $this->getInfoResponse('1004', '奖品异常,请联系客服处理！');//错误返回数据
            }

            //添加中奖记录
            $coinTurntableGetLog = new CoinTurntableGetLog();
            $coinCommonService = new CoinCommonService($app_id);

            $log_info = [
                'app_id' => $app_id,
                'award_id' => $win_result['id'],#奖品 配置id
                'turntable_id' => $turntable_id,
                'get_time' => time(),
            ];

            DB::beginTransaction();
            try {
                $obj_log = $coinTurntableGetLog->create($log_info); #添加中奖纪录
                $coinCommonService->incrementTurntableNum(-1); #扣除抽奖次数
                if ($win_result['type'] == 1) { #如果是实物增加待领取订单
                    $coinTurntableOrders = new CoinTurntableOrders();
                    $commonFunction = new  CommonFunction();

                    $order_no = 'TURNTABLE' . date('YmdHis') . $commonFunction->random(5);
                    $order = [
                        'app_id' => $app_id,
                        'get_log_id' => $obj_log->id,#中奖记录id
                        'order_no' => $order_no,#订单编号
                        'status' => 0,
                    ];
                    $coinTurntableOrders->create($order);
                } else { #如果是非实物直接发放奖品
                    $turntableService->virtualGoodGive($app_id, $win_result);
                }

                $coinTurntablePrize->where('id', $win_result['id'])->increment('already_get');#商品抽中次数+1
//                $coinTurntablePrize->where('id', $win_result['id'])->decrement('number', $win_result['luck_draw_get']);#扣除商品库存
                // 提交事务
                DB::commit();
                return $this->getResponse(['id' => $win_result['id'], 'name' => $win_result['name'], 'type' => $win_result['type'], 'img' => $win_result['img']]);//正常返回数据
            } catch (\Exception $e) {
                // 回滚事务
                DB::rollback();
                return $this->getInfoResponse('1005', '异常抽奖!');//错误返回数据
            }
            /***********************************/
//            return $this->getInfoResponse('3001', '错误返回数据!');//错误返回数据
//            return $this->getResponse('正常返回数据!');//正常返回数据
        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
     * 我的中奖记录
     */
    public function turntableGetLog(Request $request, AppUserInfo $appUserInfo)
    {
        try {//仅用于测试兼容旧版-start
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'app_id' => 'required',         #必须整数
                'type' => 'required',         #必须有数据
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            //取用户app_id
            $app_id = $arrRequest['app_id'];
            $type = $arrRequest['type']; #1=实物, 2=虚拟商品

            /***********************************/
            //开始处理逻辑问题
            $obj_user_info = $appUserInfo->getUserInfo($app_id);
            if (empty($obj_user_info)) {
                return $this->getInfoResponse('1001', '用户数据不存在！');//错误返回数据
            }

            //得到中奖记录
            $coinTurntableGetLog = new CoinTurntableGetLog();
            if ($type == 1) {//实物
                $obj_turntable_log = $coinTurntableGetLog->getRealityGoodInfo($app_id, $type);
            } elseif ($type == 2) {//虚拟
                $obj_turntable_log = $coinTurntableGetLog->getTurntablePrizeLog($app_id);
            }

            return $this->getResponse($obj_turntable_log);//正常返回数据
            /***********************************/
        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }


    /*
     * 购买抽奖页面
     */
    public function buyLuckyDrawIndex(Request $request, AppUserInfo $appUserInfo)
    {
        try {//仅用于测试兼容旧版-start
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'app_id' => 'required',         #必须整数
                'buy_type' => Rule::in([1, 2]), #1=金币 2=转盘次数
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            //取用户app_id
            $app_id = $arrRequest['app_id'];
            $buy_type = $arrRequest['buy_type'];

            /***********************************/
            //开始处理逻辑问题
            $obj_user_info = $appUserInfo->getUserInfo($app_id);
            if (empty($obj_user_info)) {
                return $this->getInfoResponse('1001', '用户数据不存在！');//错误返回数据
            }

            //得到购买抽奖次数配置
            $coinVirtualGoods = new CoinVirtualGoods();
            $obj_virtual_goods_info = $coinVirtualGoods->getConfigByType($buy_type);

            //得到用户余额数
            $taobao_user = new TaobaoUser();//用户真实分佣表
            $int_taobao_user = $taobao_user->where('app_id', $app_id)->value('money');
            $int_taobao_user = empty($int_taobao_user) ? 0 : $int_taobao_user;

            //10进制id转换33进制
            $app_id = CommonFunction::userAppIdCompatibility($app_id);

            $data = [
                'config' => $obj_virtual_goods_info,
                'app_id' => $app_id,
                'user_name' => $obj_user_info->user_name,
                'avatar' => $obj_user_info->avatar,
                'money' => $int_taobao_user,
            ];

            return $this->getResponse($data);//正常返回数据
            /***********************************/
        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
     * 购买抽奖支付
     */
    public function payLuckyDrawCount(Request $request, AppUserInfo $appUserInfo)
    {
        try {//仅用于测试兼容旧版-start
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'app_id' => 'required',         #必须有值
                'virtual_goods_id' => 'integer',#必须整数
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            //取用户app_id
            $app_id = $arrRequest['app_id'];
            $virtual_goods_id = $arrRequest['virtual_goods_id'];

            /***********************************/
            //开始处理逻辑问题
            $obj_user_info = $appUserInfo->getUserInfo($app_id);
            if (empty($obj_user_info)) {
                return $this->getInfoResponse('1001', '用户数据不存在！');//错误返回数据
            }

            //得到充值的次数与所需金额
            $coinVirtualGoods = new CoinVirtualGoods();
            $pay_info = $coinVirtualGoods->where(['id' => $virtual_goods_id, 'type' => 2])->first(['coin_number', 'real_price']);
            if (empty($pay_info)) {
                return $this->getInfoResponse('1002', '购买异常');//错误返回数据
            }

            //得到用户余额数
            $taobao_user = new TaobaoUser();//用户真实分佣表
            $taobaoChangeUserLog = new TaobaoChangeUserLog();//购买日志表
            $int_taobao_user = $taobao_user->where('app_id', $app_id)->value('money');
            $int_taobao_user = empty($int_taobao_user) ? 0 : $int_taobao_user;

            if ($pay_info->real_price > $int_taobao_user) {
                return $this->getInfoResponse('1003', '余额不足');
            }

            //得到今日已购买的转盘次数
            $str_date = date('Y-m-d');
            $money_num = $taobaoChangeUserLog->where('app_id', $app_id)
                ->where('from_type', 20006)
                ->where('created_at', '>=', $str_date)
                ->pluck('before_next_money')
                ->toArray();
            $all_coin_number = 0;
            foreach ($money_num as $v) {
                $coin_number = $coinVirtualGoods->where(['real_price' => abs($v), 'type' => 2])->value('coin_number');
                $all_coin_number += $coin_number;
            }

            if ($all_coin_number + $pay_info->coin_number > 50) {
                return $this->getInfoResponse('1004', '当日兑换次数已达上限,请明日再来!');
            }

            $obj_user_money = new UserMoney();
            $coinCommonService = new CoinCommonService($app_id);


            DB::beginTransaction();
            try {
                $obj_user_money->minusCnyAndLog($app_id, $pay_info->real_price, 20006);
                $coinCommonService->incrementTurntableNum($pay_info->coin_number);
                // 提交事务
                DB::commit();
                return $this->getResponse('购买成功!');//正常返回数据
            } catch (\Exception $e) {
                // 回滚事务
                DB::rollback();
                return $this->getInfoResponse('1004', '购买失败!');//错误返回数据
            }
            /***********************************/
//            return $this->getInfoResponse('3001', '错误返回数据!');//错误返回数据
//            return $this->getResponse('正常返回数据!');//正常返回数据
        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
     * 得到实物中奖订单信息
     */
    public function getTurntableOrderInfo(Request $request, AppUserInfo $appUserInfo)
    {
        try {//仅用于测试兼容旧版-start
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'app_id' => 'required',         #必须有值
                'win_prize_id' => 'integer',#必须整数
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            //取用户app_id
            $app_id = $arrRequest['app_id'];
            $win_prize_id = $arrRequest['win_prize_id'];

            /***********************************/
            //开始处理逻辑问题
            $obj_user_info = $appUserInfo->getUserInfo($app_id);
            if (empty($obj_user_info)) {
                return $this->getInfoResponse('1001', '用户数据不存在！');//错误返回数据
            }

            //根据中奖记录id得到订单信息
            $coinTurntableGetLog = new CoinTurntableGetLog();
            $obj_order_info = $coinTurntableGetLog->getOrderInfoById($app_id, $win_prize_id);
            if (empty($obj_order_info)) {
                return $this->getInfoResponse('1002', '异常操作,没有该中奖记录!');//错误返回数据
            }

            //根据状态调整订单数据
            $address = [];
            if ($obj_order_info->status == 0) {
                //取用户默认收货地址
                $shopAddress = new ShopAddress();
                $address = $shopAddress->getUserDefaultAddress($arrRequest['app_id']);
                $address = collect($address)->only(['collection', 'phone', 'zone', 'detail']);
                $obj_order_info = collect($obj_order_info)->only(['id', 'app_id', 'title', 'img', 'luck_draw_get', 'status', 'desc']);
            } elseif ($obj_order_info->status == 1 || $obj_order_info->status == 3) {
                $obj_order_info = collect($obj_order_info)->only(['id', 'app_id', 'title', 'img', 'luck_draw_get', 'status', 'receiving_address', 'address_phone', 'address_user', 'order_no', 'created_at', 'desc']);
            } elseif ($obj_order_info->status == 2) {
                $obj_order_info = collect($obj_order_info)->only(['id', 'app_id', 'title', 'img', 'luck_draw_get', 'status', 'receiving_address', 'address_phone', 'address_user', 'track_no', 'order_no', 'created_at', 'desc']);
            } else {
                return $this->getInfoResponse('1007', '订单状态异常!');//错误返回数据
            }

            $data = [
                'address' => $address,
                'good_info' => $obj_order_info
            ];

            return $this->getResponse($data);//正常返回数据
            /***********************************/
//            return $this->getInfoResponse('3001', '错误返回数据!');//错误返回数据
//            return $this->getResponse('正常返回数据!');//正常返回数据
        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
     * 提交领取实物订单
     */
    public function submitTurntableOrder(Request $request, AppUserInfo $appUserInfo)
    {
        try {//仅用于测试兼容旧版-start
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'app_id' => 'required',         #必须有值
                'order_id' => 'required',        #必须有值
                'receiving_address' => 'required',        #必须有值
                'address_phone' => 'required',        #必须有值
                'address_user' => 'required',        #必须有值
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            //取用户app_id
            $app_id = $arrRequest['app_id'];
            $order_id = $arrRequest['order_id'];
            $receiving_address = $arrRequest['receiving_address'];
            $address_phone = $arrRequest['address_phone'];
            $address_user = $arrRequest['address_user'];

            //缓存拦截
            if (Cache::has($order_id . 'submit_turntable_order_' . $app_id)) {
                return $this->getInfoResponse('1006', '操作太频繁!请稍后再试...');
            }
            Cache::put($order_id . 'submit_turntable_order_' . $app_id, 1, 0.1);
            /***********************************/
            //开始处理逻辑问题
            $obj_user_info = $appUserInfo->getUserInfo($app_id);
            if (empty($obj_user_info)) {
                return $this->getInfoResponse('1001', '用户数据不存在！');//错误返回数据
            }

            //临时处理 北京地区拦截
            if (stristr($receiving_address, '北京市')) {
                return $this->getInfoResponse('1006', '北京由于疫情原因和快递政策暂不能发货，开放时间另行通知，敬请谅解！');
            }

            //根据订单id得到订单信息
            $coinTurntableOrders = new CoinTurntableOrders();
            $obj_order_info = $coinTurntableOrders->where('id', $order_id)->first();

            if (empty($obj_order_info)) {
                return $this->getInfoResponse('1002', '订单异常！');//错误返回数据
            }

            if ($obj_order_info->app_id != $app_id) {
                return $this->getInfoResponse('1003', '订单异常！');//错误返回数据
            }

            if ($obj_order_info->status != 0) {
                return $this->getInfoResponse('1004', '该订单已经领取！');//错误返回数据
            }

            //更新订单信息
            $order_data = [
                'receiving_address' => $receiving_address,
                'address_phone' => $address_phone,
                'address_user' => $address_user,
                'status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
            ];
            $res = $coinTurntableOrders->updateOrderInfo($app_id, $order_id, $order_data);

            if (!$res) {
                return $this->getInfoResponse('1005', '领取失败！');//错误返回数据
            }
            return $this->getResponse('领取成功!');//正常返回数据
            /***********************************/
//            return $this->getInfoResponse('3001', '错误返回数据!');//错误返回数据
//            return $this->getResponse('正常返回数据!');//正常返回数据
        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
     * 确认实物订单到货
     */
    public function affirmTurntableOrder(Request $request, AppUserInfo $appUserInfo)
    {
        try {//仅用于测试兼容旧版-start
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'app_id' => 'required',         #必须有值
                'order_no' => 'required',        #必须有值
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            //取用户app_id
            $app_id = $arrRequest['app_id'];
            $order_no = $arrRequest['order_no'];

            /***********************************/
            //开始处理逻辑问题
            $obj_user_info = $appUserInfo->getUserInfo($app_id);
            if (empty($obj_user_info)) {
                return $this->getInfoResponse('1001', '用户数据不存在！');//错误返回数据
            }

            //根据订单id得到订单信息
            $coinTurntableOrders = new CoinTurntableOrders();
            $obj_order_info = $coinTurntableOrders->where('order_no', $order_no)->first();

            if (empty($obj_order_info)) {
                return $this->getInfoResponse('1002', '订单不存在！');//错误返回数据
            }

            if ($obj_order_info->app_id != $app_id) {
                return $this->getInfoResponse('1003', '不是你的订单！');//错误返回数据
            }

            if ($obj_order_info->status != 2) {
                return $this->getInfoResponse('1004', '订单状态异常！');//错误返回数据
            }

            //更新订单状态
            $coinTurntableOrders->where(['app_id' => $app_id, 'order_no' => $order_no])->update(['status' => 3]);

            return $this->getResponse('签收成功!');//正常返回数据
            /***********************************/
//            return $this->getInfoResponse('3001', '错误返回数据!');//错误返回数据
//            return $this->getResponse('正常返回数据!');//正常返回数据
        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }
}
