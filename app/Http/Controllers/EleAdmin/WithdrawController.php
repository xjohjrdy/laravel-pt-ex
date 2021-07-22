<?php

namespace App\Http\Controllers\EleAdmin;

use App\Entitys\Ad\AdUserInfo;
use App\Entitys\App\AppUserInfo;
use App\Entitys\App\BonusLog;
use App\Entitys\App\HarryAgreement;
use App\Entitys\App\ShopOrdersMaid;
use App\Entitys\App\TaobaoChangeUserLog;
use App\Entitys\App\TaobaoMaid;
use App\Entitys\App\TaobaoUser;
use App\Entitys\App\TaobaoUserGet;
use App\Entitys\App\UserOrderNew;
use App\Exceptions\ApiException;
use App\Extend\Random;
use App\Services\Common\UserMoney;
use App\Services\HarryPay\Harry;
use App\Services\HarryPay\RsaHarry;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class WithdrawController extends Controller
{
    public function getUserWithdrawList(Request $request)
    {
        try {
            $params = $request->input();
            $limit = $params['limit'];
            $search_keys = ['app_id', 'type'];
            $wheres = [];
            foreach ($search_keys as $key => $item) {
                if (!is_null($params[$item])) {
                    $wheres[$item] = $params[$item];
                }
            }

            $model = new TaobaoUserGet();
            $list = $model->where($wheres);
            if (!empty($params['sort'])) { // 添加排序
                foreach ($params['sort'] as $key => $value) {
                    $item = json_decode($value, true);
                    foreach ($item as $column => $direction) {
                        $list = $list->orderBy($column, $direction);
                    }
                }
            }
            if (!empty($params['date_range'])) {
                $list = $list->whereBetween('created_at', $params['date_range']);
            }
            $list = $list->orderBy('id', 'desc')->paginate($limit);
            return $this->getResponse($list);
        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                return $this->getInfoResponse($e->getCode(), $e->getMessage());
            }
            return $this->getInfoResponse(500, $e->getMessage());
        }

    }

    public function agreeLog(Request $request)
    {
        try {
            $params = $request->input();
            $rules = [
                'id' => 'required',
            ];
            $validator = Validator::make($params, $rules);
            if ($validator->fails()) {
                return $this->getInfoResponse(3002, '缺少必要参数,错误信息：' . $validator->errors());
            }
            $userMoneyService = new UserMoney();
            $model = new TaobaoUserGet();
            $apply_cash = $model->where([
                'id' => $params['id'],
                'type' => 0,
            ])->first();
            if (empty($apply_cash)) {
                return $this->getInfoResponse(2000, '为查找到该记录');
            } else {
                $model->where([
                    'id' => $params['id'],
                    'type' => 0,
                ])->update([
                    'type' => 1,
                ]);
            }
            return $this->getResponse('');
        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                return $this->getInfoResponse($e->getCode(), $e->getMessage());
            }
            return $this->getInfoResponse(500, $e->getMessage());
        }

    }

    public function withdrawReject(Request $request)
    {
        try {
            DB::connection('app38')->beginTransaction();
            $params = $request->input();
            $rules = [
                'id' => 'required',
                'reason' => 'required',
            ];
            $validator = Validator::make($params, $rules);
            if ($validator->fails()) {
                return $this->getInfoResponse(3002, '缺少必要参数,错误信息：' . $validator->errors());
            }
            $userMoneyService = new UserMoney();
            $model = new TaobaoUserGet();
            $apply_cash = $model->where([
                'id' => $params['id'],
                'type' => 0,
            ])->first();
            if (empty($apply_cash)) {
                return $this->getInfoResponse(2000, '为查找到该记录');
            } else {
                $model->where([
                    'id' => $params['id'],
                    'type' => 0,
                ])->update([
                    'type' => 2,
                    'reason' => $params['reason']
                ]);
                $fee = $apply_cash['fee'];
                $cash_money = $apply_cash['money'];
                $money = 0; //最终退回用户的金额
                $from_type = 447;
                if ($apply_cash['from_type'] == 3) { // 支付宝
                    $from_type = 447;
                }
                if ($apply_cash['from_type'] == 2) { // 银行卡
                    $from_type = 446;
                }
                if ($fee > 0) { // 新逻辑
                    $money = $cash_money + $fee;
                } else { // 旧逻辑
                    if ($cash_money > 99) {
                        $money = round($cash_money / 0.99, 2);
                    } else {
                        $money = $cash_money + 1;
                    }
                }
                $userMoneyService->plusCnyAndLogNoTrans($apply_cash['app_id'], $money, $from_type, '');
            }

            DB::connection('app38')->commit();
            return $this->getResponse('');
        } catch (\Exception $e) {
            DB::connection('app38')->rollBack();
            if (!empty($e->getCode())) {
                return $this->getInfoResponse($e->getCode(), $e->getMessage());
            }
            return $this->getInfoResponse(500, $e->getMessage());
        }

    }

    /**
     * @param Request $request
     * 分页导出
     * @return \Illuminate\Http\JsonResponse
     */
    public function exportForPage(Request $request)
    {
        try {
            $params = $request->input();
            $wheres = [];
            $userModel = new AppUserInfo();
            $taobaoGetModel = new TaobaoUserGet();
            $userOrderNewModel = new UserOrderNew();
            $adUserModel = new AdUserInfo();
            $bonusLogModel = new BonusLog();
            $taobaoChangeUserLog = new TaobaoChangeUserLog();
            $taobaoMaidModel = new TaobaoMaid();
            $shopOrderMaidModel = new ShopOrdersMaid();
            $wheres['type'] = 0;
            $limit = $params['limit'];
            $endTime = date('Y-m-d H:i:s', $params['endTime']);
            $list = $taobaoGetModel->where($wheres)->where('created_at', '<=', $endTime)->whereIn('from_type', [2, 3]);
            $list = $list->orderBy('id', 'asc')->paginate($limit);
            $list = $list->toArray();
            foreach ($list['data'] as $key => $v) {
                //申请提现的金额
                if ($v['fee'] > 0) {
                    $apply_cash = $v['money'] + $v['fee'];
                } else {
                    $apply_cash = $v['money'] > 99 ? $v['money'] / 0.99 : $v['money'] + 1;
                }

                //订单返现金额
                $order_amount = $taobaoChangeUserLog->where(['app_id' => $v['app_id'], 'from_type' => 0])->sum('before_next_money');


                //待报销订单笔数
//            $verify_order_total_number = $taobaoMaidOld->where(['app_id' => $v['app_id'], 'real' => 0])->count();
                $res = DB::connection('app38')->select("select count(id) as res from lc_taobao_maid_old where `app_id`={$v['app_id']} AND `real`=0");

                $verify_order_total_number = (int)$res[0]->res;
                //获取用户信息
                $obj_user_info = $userModel->find($v['app_id']);
                //跳过异常用户
                if (empty($obj_user_info)) {
                    continue;
                }
                //上月活跃值
                $history_active_value = $obj_user_info->history_active_value;

                //提现历史总金额
                $cash_amount = $taobaoGetModel->where(['app_id' => $v['app_id'], 'type' => 1])->sum('money');

                //报销历史总金额,统计user_order_new表
                $cashback_amount = $userOrderNewModel->where(['user_id' => $v['app_id'], 'status' => 9])->sum('cashback_amount');

                //上月报销总金额
                $begin_time_stamp = strtotime(date('Y-m-01 00:00:00', strtotime('-1 month')));
                $end_time_stamp = strtotime(date("Y-m-d 23:59:59", strtotime(-date('d') . 'day')));


                $last_cashback_amount = $userOrderNewModel->where('user_id', $v['app_id'])
                    ->where(['status' => ['in', [3, 4, 9]]])
                    ->whereBetween('create_time', [$begin_time_stamp, $end_time_stamp])
                    ->sum('cashback_amount');
                //历史分红总金额
                $bonus_log_amount = $taobaoChangeUserLog->where('app_id', $v['app_id'])->where(['from_type' => ['in', [2, 4]]])->sum('before_next_money');


                //分红金额
                $bonus_amount = $bonusLogModel->where('user_id', $v['app_id'])->orderBy('id', 'desc')->limit(1)->value('bonus_amount');

                //团队直属下级人数
                $children_count = $userModel->getRangeChildrenCount($v['app_id']);


                //什么级别2：普通用户/vip/优质转正
                $obj_user = $adUserModel->appToAdUserId($v['app_id']);

                //直推vip数
                $vip_count = DB::connection('a1191125678')
                    ->select("select count(pt_id) as cnt from pre_common_member p1 where p1.pt_pid = {$v['app_id']} and p1.groupid in (23,24)");
                $direct_vip = empty($vip_count[0]->cnt) ? 0 : $vip_count[0]->cnt;
                // 淘宝报销综合
                $taobao_maid_2_sum = $taobaoMaidModel->where(['app_id' => $v['app_id']])->sum('maid_money');
                $taobao_maid_own = $taobaoMaidModel->where(['app_id' => $v['app_id'], 'type' => 2])->sum('maid_money');
                // 商城佣金综合
                $shop_order_maid_sum = $shopOrderMaidModel->where(['app_id' => $v['app_id']])->sum('money');
                $shop_order_maid_sum = round($shop_order_maid_sum / 10, 2);
                $user_groupid = [
                    '10' => '普通用户',
                    '23' => '超级用户',
                    '24' => '优质转正',
                ];
                $user_level = [
                    '1' => '无',
                    '2' => '实习',
                    '3' => '转正',
                    '4' => '经理',
                    '5' => '董事',
                ];
                $list['data'][$key]['bonus_amount'] = $bonus_amount;
                $list['data'][$key]['order_amount'] = $order_amount;
                $list['data'][$key]['apply_cash'] = round($apply_cash, 2);
                $list['data'][$key]['verify_order_total_number'] = $verify_order_total_number;
                $list['data'][$key]['history_active_value'] = $history_active_value;
                $list['data'][$key]['cash_amount'] = $cash_amount;
                $list['data'][$key]['cashback_amount'] = $cashback_amount;
                $list['data'][$key]['last_cashback_amount'] = $last_cashback_amount;
                $list['data'][$key]['bonus_log_amount'] = $bonus_log_amount;
                $list['data'][$key]['children_count'] = @$children_count[$v['app_id']];
                $level = empty($obj_user_info->level) ? '0' : $obj_user_info->level;
                $list['data'][$key]['level'] = $user_level[$level];
                $list['data'][$key]['user_groupid'] = @$user_groupid[$obj_user->groupid];
                $list['data'][$key]['direct_vip'] = $direct_vip;
                $list['data'][$key]['taobao_maid_2_sum'] = $taobao_maid_2_sum;
                $list['data'][$key]['taobao_maid_own'] = $taobao_maid_own;
                $list['data'][$key]['shop_order_maid_sum'] = $shop_order_maid_sum;

            }
            return $this->getResponse($list);
        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                return $this->getInfoResponse($e->getCode(), $e->getMessage());
            }
            return $this->getInfoResponse(500, $e->getMessage());
        }


    }

    public function exportWithdrawList(Request $request)
    {
        try {
            $params = $request->input();
            $wheres = [];
            $userModel = new AppUserInfo();
            $taobaoGetModel = new TaobaoUserGet();
            $userOrderNewModel = new UserOrderNew();
            $adUserModel = new AdUserInfo();
            $bonusLogModel = new BonusLog();
            $taobaoChangeUserLog = new TaobaoChangeUserLog();
            $wheres['type'] = 0;
            $list = $taobaoGetModel->where($wheres)->whereIn('from_type', [2, 3]);
            if (!empty($params['date_range'])) {
                $list = $list->whereBetween('created_at', $params['date_range']);
            }
            $list = $list->get();
            foreach ($list as $key => $v) {
                //申请提现的金额
                if ($v['fee'] > 0) {
                    $apply_cash = $v['money'] + $v['fee'];
                } else {
                    $apply_cash = $v['money'] > 99 ? $v['money'] / 0.99 : $v['money'] + 1;
                }

                //订单返现金额
                $order_amount = $taobaoChangeUserLog->where(['app_id' => $v['app_id'], 'from_type' => 0])->sum('before_next_money');


                //待报销订单笔数
//            $verify_order_total_number = $taobaoMaidOld->where(['app_id' => $v['app_id'], 'real' => 0])->count();
                $res = DB::connection('app38')->select("select count(id) as res from lc_taobao_maid_old where `app_id`={$v['app_id']} AND `real`=0");

                $verify_order_total_number = (int)$res[0]->res;
                //获取用户信息
                $obj_user_info = $userModel->find($v['app_id']);
                //跳过异常用户
                if (empty($obj_user_info)) {
                    continue;
                }
                //上月活跃值
                $history_active_value = $obj_user_info->history_active_value;

                //提现历史总金额
                $cash_amount = $taobaoGetModel->where('app_id', $v['app_id'])->where('type', 1)->sum('money');

                //报销历史总金额,统计user_order_new表
                $cashback_amount = $userOrderNewModel->where(['user_id' => $v['app_id'], 'status' => 9])->sum('cashback_amount');

                //上月报销总金额
                $begin_time_stamp = strtotime(date('Y-m-01 00:00:00', strtotime('-1 month')));
                $end_time_stamp = strtotime(date("Y-m-d 23:59:59", strtotime(-date('d') . 'day')));


                $last_cashback_amount = $userOrderNewModel->where('user_id', $v['app_id'])
                    ->where(['status' => ['in', [3, 4, 9]]])
                    ->whereBetween('create_time', [$begin_time_stamp, $end_time_stamp])
                    ->sum('cashback_amount');
                //历史分红总金额
                $bonus_log_amount = $taobaoChangeUserLog->where('app_id', $v['app_id'])->where(['from_type' => ['in', [2, 4]]])->sum('before_next_money');


                //分红金额
                $bonus_amount = $bonusLogModel->where('user_id', $v['app_id'])->orderBy('id', 'desc')->limit(1)->value('bonus_amount');

                //团队直属下级人数
                $children_count = $userModel->getRangeChildrenCount($v['app_id']);


                //什么级别2：普通用户/vip/优质转正
                $obj_user = $adUserModel->appToAdUserId($v['app_id']);
                $user_groupid = [
                    '10' => '普通用户',
                    '23' => '超级用户',
                    '24' => '优质转正',
                ];
                $list[$key]['bonus_amount'] = $bonus_amount;
                $list[$key]['order_amount'] = $order_amount;
                $list[$key]['apply_cash'] = round($apply_cash, 2);
                $list[$key]['verify_order_total_number'] = $verify_order_total_number;
                $list[$key]['history_active_value'] = $history_active_value;
                $list[$key]['cash_amount'] = $cash_amount;
                $list[$key]['cashback_amount'] = $cashback_amount;
                $list[$key]['last_cashback_amount'] = $last_cashback_amount;
                $list[$key]['bonus_log_amount'] = $bonus_log_amount;
                $list[$key]['children_count'] = @$children_count[$v['app_id']];
                $list[$key]['level'] = $obj_user_info->level;
                $list[$key]['user_groupid'] = @$user_groupid[$obj_user->groupid];

            }
            return $this->getResponse($list);
        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                return $this->getInfoResponse($e->getCode(), $e->getMessage());
            }
            return $this->getInfoResponse(500, $e->getMessage());
        }

    }


    /**
     * harry 导入提现
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function importDataAndWithdraw2Harry(Request $request)
    {
        try {
            $cache_key = 'vue_other_import_do_withdraw';
            $params = $request->input();
            $list = $params['list'];
            $threeUserGet = new TaobaoUserGet();
            $harryService = new Harry();
            $harryEntityModel = new HarryAgreement();
            $need = [
                'total' => count($list),     #总共导入的数据量
                'no_apply_num' => 0,          #导入的数据中未对应到提现申请的数量
                'success' => 0,               #操作正常的数量
                'fail' => 0,                  #操作失败的数量
            ];
            foreach ($list as $item) {
                $type = 0;
                $msg = '';
                $err_info = '';
                $apply_cash = $threeUserGet->where([
                    'app_id' => $item['app_id'],
                    'phone' => $item['phone'],
                    'real_name' => $item['real_name'],
                    'money' => $item['money'],
                    'type' => 0,
                ])->first();
                if (!empty($apply_cash)) {
                    try {
                        $harry_info = $harryEntityModel->where(['app_id' => $item['app_id']])->first();
                        $citizenship = $harry_info['citizenship'];

                        $name = $harry_info['name'];
                        $mobile = $harry_info['phone'];
                        $certificate = $harry_info['identityId'];
                        $money = $apply_cash['money'] * 100; // 单位分
                        if ($citizenship == 4) { // 外籍用户
                            $threeUserGet->where(['id' => $apply_cash['id']])->update([
                                'error_info' => '外籍身份' . $certificate
                            ]);
                            $need['no_apply_num']++;
                            continue;
                        }
//                        $money = 1;
                        $account = $apply_cash['phone'];
                        $certificateTypes = [
                            0 => 'ID_CARD', // 大陆 --- 身份证
                            1 => 'HMP', // 香港 --- 港澳通行证
                            2 => 'HMP', // 澳门 --- 港澳通行证
                            3 => 'TMP', // 台湾通行证
                        ];
//                        $out_order_id = 'PTC2TX' . $apply_cash['id'];
                        $out_order_id = 'PTC2TX20200711' . $apply_cash['id'];
                        if ($apply_cash['from_type'] == 3) { // 提现至支付宝
                            $res = $harryService->push($name, $mobile, $certificate, $out_order_id, $money, $account, $certificateTypes[$citizenship]);
                        } else if ($apply_cash['from_type'] == 2) {
                            $res = $harryService->cardPush($name, $mobile, $certificate, $out_order_id, $money, $account, $certificateTypes[$citizenship]);
                        } else {
                            continue; // 跳过非众薪打款
                        }
                        DB::connection('app38')->beginTransaction();
                        if ($res['return_code'] == 'T') {
                            $need['success'] = $need['success'] + 1;
                            $threeUserGet->where(['id' => $apply_cash['id']])->update([
                                'type' => 4
                            ]);
                        } else {
                            $message = empty($res['return_message']) ? '未返回错误消息' : $res['return_message'];
                            $content = empty($res['content']) ? '' : $res['content'];
                            $update_body = [
                                'error_info' => $message . '||' . $content
                            ];
                            if ($content == '验签失败，收款方年龄不合规') {
                                $userMoneyService = new UserMoney();
                                $update_body['type'] = 2;
                                $update_body['reason'] = '提现失败，收款方年龄需大于18周岁，请修改后重新申请提现！';
                                $fee = $apply_cash['fee'];
                                $cash_money = $apply_cash['money'];
                                $money = 0; //最终退回用户的金额
                                if ($fee > 0) { // 新逻辑
                                    $money = $cash_money + $fee;
                                } else { // 旧逻辑
                                    if ($cash_money > 99) {
                                        $money = round($cash_money / 0.99, 2);
                                    } else {
                                        $money = $cash_money + 1;
                                    }
                                }
                                $from_type = 447;
                                if ($apply_cash['from_type'] == 3) { // 支付宝
                                    $from_type = 447;
                                }
                                if ($apply_cash['from_type'] == 2) { // 银行卡
                                    $from_type = 446;
                                }
                                $userMoneyService->plusCnyAndLogNoTrans($apply_cash['app_id'], $money, $from_type, '');
                            }
                            $threeUserGet->where(['id' => $apply_cash['id']])->update($update_body);
                            $need['fail'] = $need['fail'] + 1;
//                            return $this->getInfoResponse(1001, '提交众薪打款异常，终止打款，请联系第三方解决！' . '错误信息：' . $res['return_message']);
                        }
                        DB::connection('app38')->commit();
                    } catch (\Exception $exception) {
                        DB::connection('app38')->rollBack();
                        return $this->getInfoResponse(1001,
                            '共：' . $need['total'] .
                            ' 提交众薪打款中:' . $need['success'] .
                            ' 提交失败:' . $need['fail'] .
                            '系统异常结束：' . $exception->getMessage()
                        );
                    }

                } else {
                    $need['no_apply_num']++;
                }
            }
            return $this->getResponse($need);
        } catch (\Exception $e) {
//            Cache::forget($cache_key);
            if (!empty($e->getCode())) {
                return $this->getInfoResponse($e->getCode(), $e->getMessage());
            }
            return $this->getInfoResponse(500, $e->getMessage());
        }

    }


    /**
     * 众薪，提现结果回调
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function harryWithdrawCallback(Request $request)
    {
        try {
            $params = Input::all();
            $threeUserGet = new TaobaoUserGet();
            $web_rsa = new RsaHarry();
            $res = $web_rsa->private_decrypt($params['sign']);
            $res = json_decode($res, true);
            $this->harryLog($res);
            /**
             * {
             * "actualAmount": 1,
             * "additionalCharge": 0,
             * "cardAttribute": "C",
             * "cardType": "DC",
             * "certificateNo": "352227198704131818",
             * "charset": "UTF-8",
             * "companyCharge": 0,
             * "companyServiceFee": 0,
             * "companyTax": 0,
             * "createTime": "2020-03-19 15:15:17",
             * "endTime": "2020-03-19 15:20:11.0",
             * "mobile": "18695790413",
             * "name": "卞贤铃",
             * "noticeNum": "0",
             * "notifyUrl": "http://pt.qmshidai.com/api/harry_withdraw_callback",
             * "orderNo": "20200319151516995308178083840",
             * "outMemberNo": "1239451943400300544",
             * "outerOrderNo": "harry_1584602116",
             * "payAccount": "6217001820028709537",
             * "payType": "1",
             * "personServiceFee": 0,
             * "predictAmount": 1,
             * "projectName": "余额提现（银行卡）",
             * "salaryType": "0",
             * "service": "bpotop.zx.pay.order",
             * "serviceCharge": 0,
             * "signType": "RSA",
             * "status": "1",
             * "taxFee": 0,
             * "version": "1.1"
             * }
             *
             *
             * {
             * "actualAmount": 1,
             * "additionalCharge": 0,
             * "cardAttribute": "C",
             * "cardType": "DC",
             * "certificateNo": "352227198704131818",
             * "charset": "UTF-8",
             * "companyCharge": 0,
             * "companyServiceFee": 0,
             * "companyTax": 0,
             * "createTime": "2020-03-19 15:03:40",
             * "endTime": "2020-03-19 15:07:01.0",
             * "errorCode": "F9999",
             * "errorMsg": "收款行联行号不正确",
             * "mobile": "18695790413",
             * "name": "卞贤铃",
             * "noticeNum": "0",
             * "notifyUrl": "http://pt.qmshidai.com/api/harry_withdraw_callback",
             * "orderNo": "20200319150340004384785940481",
             * "outMemberNo": "1239451943400300544",
             * "outerOrderNo": "harry_1584601419",
             * "payAccount": "352227198704131818",
             * "payType": "1",
             * "personServiceFee": 0,
             * "predictAmount": 1,
             * "projectName": "余额提现（银行卡）",
             * "salaryType": "0",
             * "service": "bpotop.zx.pay.order",
             * "serviceCharge": 0,
             * "signType": "RSA",
             * "status": "2",
             * "taxFee": 0,
             * "version": "1.1"
             * }
             */
            $userMoneyService = new UserMoney();
            if (!empty($res['status'])) {
                $msg = '';
                $status = $res['status'];
                $id = $res['outerOrderNo'];
//                $id = mb_substr($id, 6, mb_strlen($id));
                $id = mb_substr($id, 14, mb_strlen($id));
                $apply_cash = $threeUserGet->where([
                    'id' => $id
                ])->whereIn('type', [0, 4])->first();
                if (empty($apply_cash)) {
                    return 'success';
                }
                DB::connection('app38')->beginTransaction();
                #todo 交易成功
                if ($status == 1) {
                    $apply_cash->where(['id' => $id])->update([
                        'type' => 1,
                        'error_info' => '',
                        'reason' => ''
                    ]);
                }
                #todo 交易失败
                if ($status == 2) {
                    $errorMsg = $res['errorMsg'];
                    $from_type = 447;
                    $reason = '';
                    if ($apply_cash['from_type'] == 3) { // 支付宝
                        $from_type = 447;
                        $reason = '请核实支付宝账号与收款人姓名是否一致，如果确认一致，可能是因为收款的支付宝账户号不是注册支付宝账号时绑定的手机号或者邮箱号，建议尝试更换一下收款支付宝账号（更换为手机号或者邮箱号）！或重新申请银行卡提现。';
                    }
                    if ($apply_cash['from_type'] == 2) { // 银行卡
                        $from_type = 446;
                        $reason = '请核实收款银行卡号是否正确，收款银行卡号是否是本人的银行卡号，或尝试使用支付宝提现。';
                    }
                    if ($errorMsg == '验签失败，余额不足') {
                        $reason = '提现升级，请重新申请提现，审核通过后3个工作日内到账';
                    }
                    $apply_cash->where(['id' => $id])->update([
                        'type' => 2,
                        'error_info' => $errorMsg,
                        'reason' => $reason
                    ]);
                    $fee = $apply_cash['fee'];
                    $cash_money = $apply_cash['money'];
                    $money = 0; //最终退回用户的金额
                    if ($fee > 0) { // 新逻辑
                        $money = $cash_money + $fee;
                    } else { // 旧逻辑
                        if ($cash_money > 99) {
                            $money = round($cash_money / 0.99, 2);
                        } else {
                            $money = $cash_money + 1;
                        }
                    }
                    $userMoneyService->plusCnyAndLogNoTrans($apply_cash['app_id'], $money, $from_type, '');
                }
            }

            DB::connection('app38')->commit();
        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            DB::connection('app38')->rollBack();
            $this->harryLog($e->getMessage());
        }
        return 'success';
    }

    /**
     * 批量审核
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function batchSuccess(Request $request)
    {
        try {
            $params = $request->getContent();
            $rules = [
                'ids' => 'required'
            ];
            $model = new TaobaoUserGet();
            $params = json_decode($params, true);
            $validator = Validator::make($params, $rules);
            if ($validator->fails()) {
                return $this->getInfoResponse(3002, '缺少必要参数,错误信息：' . $validator->errors());
            }
            $list = $params['ids'];
            $results = [
                'success' => 0,
                'total' => count($list),
                'unSearch' => 0,
                'fail' => 0
            ];
            foreach ($list as $item){
                try{
                    $audit_info = $model->where(['id' => $item, 'type' => 0])->first();
                    if(empty($audit_info)){
                        $results['unSearch'] += 1;
                    } else {
                        $model->where(['id' => $item])->update([
                            'type' => 1
                        ]);
                        $results['success'] += 1;
                    }
                } catch (\Exception $e){
                    $results['fail'] += 1;
                }
            }
            return $this->getResponse($results);
        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                return $this->getInfoResponse($e->getCode(), $e->getMessage());
            }
            return $this->getInfoResponse(500, $e->getMessage());
        }

    }

    /**
     * 支付记录日志
     */
    private function harryLog($msg)
    {
        $date = date('Ymd');
        Storage::disk('local')->append('callback_document/harry_app/' . $date . '.txt', date('H:i:s') . '#### ' . var_export($msg, true) . ' ####');
    }
}
