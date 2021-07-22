<?php

namespace App\Http\Controllers\Alimama;

use App\Entitys\App\ApplyCash;
use App\Entitys\App\AppUserInfo;
use App\Entitys\App\JdMaidOld;
use App\Entitys\App\PddMaidOld;
use App\Entitys\App\TaobaoChangeUserLog;
use App\Entitys\App\TaobaoMaidOld;
use App\Entitys\App\TaobaoUser;
use App\Entitys\App\TaobaoUserGet;
use App\Entitys\App\WechatInfo;
use App\Exceptions\ApiException;
use App\Services\Commands\CountEverydayService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class GetController extends Controller
{
    /**
     * 获取全部首页
     */
    public function getAllIndex(Request $request, TaobaoUser $taobaoUser, TaobaoMaidOld $taobaoMaidOld, PddMaidOld $pddMaidOld, JdMaidOld $jdMaidOld)
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

            $taobao_user = $taobaoUser->getUser($arrRequest['app_id']);
            $two_prediction_now_2 = $taobaoMaidOld->getTime($arrRequest['app_id'], 2);
            $two_prediction_now_1 = $taobaoMaidOld->getTime($arrRequest['app_id'], 1);


            $pdd_two_prediction_now_2 = $pddMaidOld->getTime($arrRequest['app_id'], 2);
            $pdd_two_prediction_now_1 = $pddMaidOld->getTime($arrRequest['app_id'], 1);

            $jd_two_prediction_now_2 = $jdMaidOld->getTime($arrRequest['app_id'], 2);
            $jd_two_prediction_now_1 = $jdMaidOld->getTime($arrRequest['app_id'], 1);

            return $this->getResponse([
                'one_get' => $taobao_user->money,
                'two_prediction_now' => round(($two_prediction_now_2 + $pdd_two_prediction_now_2 + $jd_two_prediction_now_2), 2),
                'three_prediction_forward' => round(($two_prediction_now_1 + $pdd_two_prediction_now_1 + $jd_two_prediction_now_1), 2),
            ]);
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * 获取首页
     */
    public function getIndex(Request $request, TaobaoUser $taobaoUser, AppUserInfo $appUserInfo)
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

            $taobao_user = $taobaoUser->getUser($arrRequest['app_id']);
            $user = $appUserInfo->getUserById($arrRequest['app_id']);
            return $this->getResponse([
                'money' => $taobao_user->money,
                'phone' => $user->alipay,
                'real_name' => $user->real_name,
                'one_value' => '34',
                'two_value' => '1',
                'three_value' => '0.03',
            ]);
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * 获取日志
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function getLog(Request $request, TaobaoUserGet $taobaoUserGet, ApplyCash $applyCash)
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

            $data = [];

            $all_sum = $taobaoUserGet->getSum($arrRequest['app_id'], 1);
            $log = $taobaoUserGet->getByAppId($arrRequest['app_id']);

            $total_cash_amount = $applyCash->getTotalCashAmount($arrRequest['app_id']);
            $list = $applyCash->getApplyCashList($arrRequest['app_id']);

            if (!empty($log)) {
                $log = $log->toArray();
                foreach ($log['data'] as $value) {
                    $from_type_info = '微信提现';//0：微信提现，1：支付宝提现, 2众薪银行卡 ， 3众薪支付宝
                    if ($value['from_type'] == 1) {
                        $from_type_info = '支付宝提现';
                    }
                    if ($value['from_type'] == 2) {
                        $from_type_info = '银行卡提现';
                    }
                    if ($value['from_type'] == 3) {
                        $from_type_info = '支付宝提现';
                    }
                    $data['list'][] = [
                        'app_id' => $value['app_id'],
                        'phone' => $value['phone'],
                        'real_name' => $value['real_name'],
                        'money' => $value['money'],
                        'type' => $value['type'],
                        'reason' => $value['reason'],
                        'created_at' => date('Y-m-d', strtotime($value['created_at'])),
                        'from_type' => $value['from_type'],
                        'from_type_info' => $from_type_info,
                    ];
                }
                $current_page = $log['current_page'];
                $last_page = $log['last_page'];
                $total = $log['total'];
            }
            if (!empty($list)) {
                $list = $list->toArray();
                foreach ($list['data'] as $item) {
                    $data['list'][] = [
                        'app_id' => $arrRequest['app_id'],
                        'phone' => $item['alipay'],
                        'real_name' => $item['real_name'],
                        'money' => $item['cash_amount'],
                        'type' => $item['status'],
                        'reason' => $item['reason'],
                        'created_at' => date('Y-m-d', $item['create_time']),
                        'from_type' => 1,
                        'from_type_info' => '支付宝提现',
                    ];
                }
                if ($total < $list['total']) {
                    $current_page = $list['current_page'];
                    $last_page = $list['last_page'];
                    $total = $list['total'];
                }
            }

            if (empty($data['list'])) {
                $data['list'] = [];
            }

            $data['current_page'] = $current_page;
            $data['last_page'] = $last_page;
            $data['total'] = $total;

            return $this->getResponse([
                'all_sum' => round($all_sum + $total_cash_amount, 2),
                'log' => $data,
            ]);
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * 增加日志
     */
    public function addLog(Request $request, WechatInfo $wechatInfo, TaobaoUserGet $taobaoUserGet, TaobaoUser $taobaoUser, AppUserInfo $appUserInfo)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'money' => 'required',
                'app_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            if (!empty($arrRequest['from_to'])) {
                if ($arrRequest['from_to'] == 1) {
                    return $this->getInfoResponse('5001', '暂不支持支付宝提现！');
                }
            }

            return $this->getInfoResponse('5001', '苹果版提现升级中，建议暂时使用安卓手机进行提现。');

            $wechat_user_info = $wechatInfo->getAppId($arrRequest['app_id']);
            if (empty($wechat_user_info)) {
                return $this->getInfoResponse('4004', '即日起提现需要绑定微信，以微信入账的形式支付，请前往绑定微信');
            }
            if ($arrRequest['money'] < 5) {
                return $this->getInfoResponse('5001', '金额不可小于5元');
            }
            if ($arrRequest['money'] > 5000) {
                return $this->getInfoResponse('5001', '金额不可大于5000元');
            }
            $app_id = $arrRequest['app_id'];
            $taobao_user = $taobaoUser->getUser($arrRequest['app_id']);
            $user = $appUserInfo->getUserById($arrRequest['app_id']);
            if ($taobao_user->money < $arrRequest['money']) {
                return $this->getInfoResponse('5002', '提现金额已超出可提现金额');
            }
            if (empty($user->alipay) || empty($user->real_name)) {
                return $this->getInfoResponse('5003', '没有输入支付宝账号和支付宝姓名，请先去填写');
            }
            $int_dispose_apply_cash = $taobaoUserGet->getDisposeApplyCash($app_id);
            if (!empty($int_dispose_apply_cash)) {
                return $this->getInfoResponse('1002', '当前有正在处理中的提现申请,请勿重复申请');
            }
            if (Cache::has('alimama_get_cash_' . $app_id)) {
                return $this->getInfoResponse('1005', '申请频繁，请稍后再试！');
            }
            Cache::put('alimama_get_cash_' . $app_id, 1, 0.25);
            $taobaoUser->subMoney($arrRequest['app_id'], $arrRequest['money']);
            if ($arrRequest['money'] > 100) {
                $money = $arrRequest['money'] * 0.99;
            } else {
                $money = $arrRequest['money'] - 1;
            }
            $taobaoUserGet->addLog([
                'app_id' => $arrRequest['app_id'],
                'phone' => $user->alipay,
                'real_name' => $user->real_name,
                'money' => $money,
                'type' => 0,
            ]);

            $changeUserLog = new TaobaoChangeUserLog();
            $changeUserLog->create([
                'app_id' => $app_id,
                'before_money' => $taobao_user->money,
                'before_next_money' => -$arrRequest['money'],
                'before_last_money' => 0,
                'after_money' => ($taobao_user->money - $arrRequest['money']),
                'after_next_money' => 0,
                'after_last_money' => 0,
                'from_type' => 1,
            ]);

            return $this->getResponse('提现成功');
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * 某个时间的各种信息
     */
    public function getPredictionLog(Request $request, TaobaoUser $taobaoUser, TaobaoMaidOld $taobaoMaidOld, CountEverydayService $countEverydayService, JdMaidOld $jdMaidOld, PddMaidOld $pddMaidOld)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
                'need_time' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            if (date('m', $arrRequest['need_time']) == '2') {
                $date_time = (int)((strtotime(date('Y-m', time())) - strtotime(date('Y-m', $arrRequest['need_time']))) / 2419200);
            } else {
                $date_time = (int)((strtotime(date('Y-m', time())) - strtotime(date('Y-m', $arrRequest['need_time']))) / 2592000);
            }

            $taobao_user = $taobaoUser->getUser($arrRequest['app_id']);
            $prediction_now_2 = $taobaoMaidOld->getTime($arrRequest['app_id'], 2, $date_time);
            $prediction_now_1 = $taobaoMaidOld->getTime($arrRequest['app_id'], 1, $date_time);


            $jd_prediction_now_2 = $jdMaidOld->getTime($arrRequest['app_id'], 2, $date_time);
            $jd_prediction_now_1 = $jdMaidOld->getTime($arrRequest['app_id'], 1, $date_time);


            $pdd_prediction_now_2 = $pddMaidOld->getTime($arrRequest['app_id'], 2, $date_time);
            $pdd_prediction_now_1 = $pddMaidOld->getTime($arrRequest['app_id'], 1, $date_time);

            $my_special = $taobaoMaidOld->getTimeMySpecial($arrRequest['app_id'], 1, $date_time);


            $begin_time_stamp = mktime(0, 0, 0, date('m', $arrRequest['need_time']), 1, date('Y', $arrRequest['need_time']));
            $end_time_stamp = mktime(23, 59, 59, date('m', $arrRequest['need_time']), date('t', $begin_time_stamp), date('Y', $arrRequest['need_time']));

            $five_active = $countEverydayService->getGroupOrderAccount($arrRequest['app_id'], $begin_time_stamp, $end_time_stamp);
            $group_order_account_number_new = $countEverydayService->getGroupOrderAccountNew($arrRequest['app_id'], $begin_time_stamp, $end_time_stamp);
            //拼多多活跃度统计
            $pdd_time_begin = date('Y-m-d h:i:s', $begin_time_stamp);
            $pdd_time_end = date('Y-m-d h:i:s', $end_time_stamp);
            $pdd_group_order_account_number = $countEverydayService->getGroupOrderAccountPdd($arrRequest['app_id'], $pdd_time_begin, $pdd_time_end);
            $pdd_active_value = round($pdd_group_order_account_number / 100, 2);
            //京东活跃度统计
            $jd_group_order_account_number = $countEverydayService->getGroupOrderAccountJd($arrRequest['app_id'], $begin_time_stamp . '000', $end_time_stamp . '000');//得到团队全部京东报销金额，时间区间为时间戳

            $active_value_new = round(($five_active + $group_order_account_number_new + $pdd_active_value + $jd_group_order_account_number), 2);
            $show_active = round(($active_value_new - $prediction_now_2 - $jd_prediction_now_2 - $pdd_prediction_now_2), 2);
            if ($show_active < 0) {
                $show_active = 0;
            }
            return $this->getResponse([
                'one_all' => round(($prediction_now_1 + $prediction_now_2 + $jd_prediction_now_2 + $jd_prediction_now_1 + $pdd_prediction_now_2 + $pdd_prediction_now_1), 2),
                'two_person' => round($prediction_now_2, 2),
                'three_prediction' => empty($my_special) ? 0 : round($my_special, 2),
                'four_no_push' => round(($prediction_now_1 - $my_special), 2),
                'five_active' => empty($active_value_new) ? 0 : $show_active,
            ]);
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }
}
