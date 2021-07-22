<?php

namespace App\Http\Controllers\OtherAdmin;

use App\Entitys\App\TaobaoChangeUserLog;
use App\Entitys\Other\HarryAgreementOut;
use App\Entitys\Other\MallUserInfoPassOther;
use App\Entitys\OtherOut\AdUserInfoOut;
use App\Entitys\OtherOut\AppUserInfoOut;
use App\Entitys\Other\ThreeChangeUserLog;
use App\Entitys\Other\ThreeUser;
use App\Entitys\Other\ThreeUserGet;
use App\Entitys\OtherOut\BonusLogOut;
use App\Entitys\OtherOut\TaobaoChangeUserLogOut;
use App\Entitys\OtherOut\TaobaoUserGetOther;
use App\Entitys\OtherOut\UserOrderNewOut;
use App\Extend\Random;
use App\Services\EVisa\EVisaServices;
use App\Services\HarryPayOut\Harry;
use App\Services\Qmshida\GongMallService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
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
            $model = new ThreeUserGet();
            $list = $model->where($wheres);
            if (!empty($params['date_range'])) {
                $list = $list->whereBetween('created_at', $params['date_range']);
            }
            if (!empty($params['sort'])) { // 添加排序
                foreach ($params['sort'] as $key=>$value){
                    $item = json_decode($value, true);
                    foreach ($item as $column=>$direction) {
                        $list = $list->orderBy($column, $direction);
                    }
                }
            }
            $list = $list->paginate($limit);
            return $this->getResponse($list);
        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                return $this->getInfoResponse($e->getCode(), $e->getMessage());
            }
            return $this->getInfoResponse(500, $e->getMessage());
        }

    }

    public function importDataAndWithdraw(Request $request)
    {
        try {
            $params = $request->input();
            $list = $params['list'];
            $threeUserGet = new ThreeUserGet();
            $evService = new GongMallService();
            $mallModel = new MallUserInfoPassOther();
            $threeUserModel = new ThreeUser();
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
                $commit_time = '';
                $apply_cash = $threeUserGet->where([
                    'app_id' => $item['app_id'],
                    'phone' => $item['phone'],
                    'real_name' => $item['real_name'],
                    'money' => $item['money'],
                    'type' => 0,
                ])->first();
                if (!empty($apply_cash)) {
                    $three_user = $threeUserModel->getUser($item['app_id']);
                    $apply_cash['reason'] = empty($data['reason']) ? '' : $data['reason'];
                    $mall = $mallModel->where(['app_id' => $item['app_id']])->first();
                    try {
                        $res = $evService->doWithdraw([
                            "mobile" => $mall['mobile'],
                            "name" => $mall['name'],
                            "identity" => $mall['identity'],
                            "amount" => $apply_cash['money'],
                            "bankAccount" => $mall['salaryAccount'],
                            "remark" => $item['app_id'] . '提现',
                            "requestId" => $apply_cash['id'], // 取记录的唯一ID
                        ]);
                        $res = json_decode($res, true);
                        DB::connection('db001')->beginTransaction();
                        if ($res['success']) {
                            if ($res['data']['opFlag'] == 1) {
                                $type = 4; // 提现申请成功，具体提现结果，需在提现回调中处理
                                $commit_time = $res['data']['appmentTime'];
//                                $msg = '系统已审核成功，等待银行打款！';
                                $msg = '';
                                $need['success'] = $need['success'] + 1;
                            } else {
                                $type = 0;
                                $msg = '';
                                $need['fail'] = $need['fail'] + 1;
                                $err_info = '工猫接口申请失败：openFlag: 0';
                            }
                        } else {
                            $type = 0;
                            $msg = '';
                            $err_info = $res['errorMsg'];
                            $need['fail'] = $need['fail'] + 1;
                        }
                        $threeUserGet->where(['id' => $apply_cash['id']])->update([
                            'type' => $type,
                            'update_time' => $commit_time,
                            'error_info' => $err_info,
                            'reason' => $msg
                        ]);
                        if ($type == 2) { // 如果是提现失败 则回退用户提现金额
                            $item['money'] = $money = $apply_cash['money'] + $apply_cash['manage_fee'];;
                            $changeUserLog = new ThreeChangeUserLog();
                            $changeUserLog->create([
                                'app_id' => $item['app_id'],
                                'before_money' => $three_user->money,
                                'before_next_money' => $item['money'],
                                'after_money' => ($three_user->money + $item['money']),
                                'from_type' => 6,
                                'from_info' => 'FUWD',
                            ]);
                            $threeUserModel->addMoney($item['app_id'], $item['money']);
                        }
                        DB::connection('db001')->commit();
                    } catch (\Exception $exception) {
                        DB::connection('db001')->rollBack();
                        return $this->getInfoResponse(1001,
                            '共：' . $need['total'] .
                            ' 提交工猫审核中:' . $need['success'] .
                            ' 审核失败:' . $need['fail'] .
                            '系统异常结束：' . $exception->getMessage()
                        );
                    }

                } else {
                    $need['no_apply_num']++;
                }
            }

            return $this->getResponse($need);
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
//            if(Cache::has($cache_key)){
//                return $this->getInfoResponse(1001, '当前有正在处理的提现文档，请稍等处理完毕后重新审核！');
//            }
//            Cache::put($cache_key, 1, 60 * 24);
            $params = $request->input();
            $list = $params['list'];
            $threeUserGet = new ThreeUserGet();
            $harryService = new Harry();
            $harryEntityModel = new HarryAgreementOut();
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
                        $apply_cash['reason'] = empty($data['reason']) ? '' : $data['reason'];
                        $harry_info = $harryEntityModel->where(['app_id' => $item['app_id']])->first();
                        $name = $harry_info['name'];
                        $mobile = $harry_info['phone'];
                        $citizenship = $harry_info['citizenship'];
                        $certificate = $harry_info['identityId'];
                        if($citizenship == 4){ // 外籍用户
                            $threeUserGet->where(['id' => $apply_cash['id']])->update([
                                'error_info' => '外籍身份' . $certificate
                            ]);
                            $need['no_apply_num']++;
                            continue;
                        }
                        $certificateTypes = [
                            0 => 'ID_CARD', // 大陆 --- 身份证
                            1 => 'HMP', // 香港 --- 港澳通行证
                            2 => 'HMP', // 澳门 --- 港澳通行证
                            3 => 'TMP' // 台湾通行证
                        ];
                        $money = $apply_cash['money'] * 100;
//                        $money = 1;
                        $account = $apply_cash['phone'];
//                        $out_order_id = $apply_cash['id'];
//                        $out_order_id = 'DSF2TX' . $apply_cash['id'];
                        $out_order_id = date('ymdHis') . 'OT' . Random::numeric(6);

                        if ($apply_cash['from_type'] == 1) { // 提现至支付宝
                            $res = $harryService->push($name, $mobile, $certificate, $out_order_id, (int)$money, $account, $certificateTypes[$citizenship]);
                        } else {
                            $res = $harryService->cardPush($name, $mobile, $certificate, $out_order_id, (int)$money, $account, $certificateTypes[$citizenship]);
                        }
                        DB::connection('db001')->beginTransaction();
                        if ($res['return_code'] == 'T') {
                            $need['success'] = $need['success'] + 1;
                            $threeUserGet->where(['id' => $apply_cash['id']])->update([
                                'type' => 4,
                                'order_id' => $out_order_id
                            ]);
                        } else {
                            $message =  empty($res['return_message']) ? '未返回错误消息' : $res['return_message'];
                            $content = empty($res['content']) ? '' : $res['content'];
                            $update_body = [
                                'order_id' => $out_order_id,
                                'error_info' => $message . '||' . $content
                            ];
                            if($content == '验签失败，收款方年龄不合规') {
                                $threeUserModel = new ThreeUser();
                                $changeUserLog = new ThreeChangeUserLog();
                                $update_body['type'] = 2;
                                $update_body['reason'] = '提现失败，收款方年龄需大于18周岁，请修改后重新申请提现！';
                                $three_user = $threeUserModel->getUser($apply_cash['app_id']);
                                $money = $apply_cash['money'] + $apply_cash['manage_fee'];
                                $changeUserLog->create([
                                    'app_id' => $apply_cash['app_id'],
                                    'before_money' => $three_user->money,
                                    'before_next_money' => $money,
                                    'after_money' => ($three_user->money + $money),
                                    'from_type' => 6,
                                    'from_info' => 'FUWD',
                                ]);
                                $threeUserModel->addMoney($apply_cash['app_id'], $money);
                            }
                            $threeUserGet->where(['id' => $apply_cash['id']])->update($update_body);
                            $need['fail'] = $need['fail'] + 1;
//                            return $this->getInfoResponse(1001, '提交众薪打款异常，终止打款，请联系第三方解决！' . '错误信息：' . $res['return_message']);
                        }
                        DB::connection('db001')->commit();
                    } catch (\Exception $exception) {
                        DB::connection('db001')->rollBack();
                        return $this->getInfoResponse(1001,
                            '共：' . $need['total'] .
                            ' 提交银行打款中:' . $need['success'] .
                            ' 提交失败:' . $need['fail'] .
                            '系统异常结束：' . $exception->getMessage()
                        );
                    }

                } else {
                    $need['no_apply_num']++;
                }
            }
            Cache::forget($cache_key);
            return $this->getResponse($need);
        } catch (\Exception $e) {
//            Cache::forget($cache_key);
            if (!empty($e->getCode())) {
                return $this->getInfoResponse($e->getCode(), $e->getMessage());
            }
            return $this->getInfoResponse(500, $e->getMessage());
        }

    }


    public function withdrawReject(Request $request)
    {
        try {
            DB::connection('db001')->beginTransaction();
            $params = $request->input();
            $rules = [
                'id' => 'required',
                'reason' => 'required',
            ];
            $validator = Validator::make($params, $rules);
            if ($validator->fails()) {
                return $this->getInfoResponse(3002, '缺少必要参数,错误信息：' . $validator->errors());
            }
            $model = new ThreeUserGet();
            $threeUserModel = new ThreeUser();
            $changeUserLog = new ThreeChangeUserLog();
            $apply_cash = $model->where([
                'id' => $params['id'],
                'type' => 0,
            ])->first();
            if(empty($apply_cash)){
                return $this->getInfoResponse(2000, '为查找到该记录');
            } else {
                $model->where([
                    'id' => $params['id'],
                    'type' => 0,
                ])->update([
                    'type' => 2,
                    'reason' => $params['reason']
                ]);
                $three_user = $threeUserModel->getUser($apply_cash['app_id']);
                $money = $apply_cash['money'] + $apply_cash['manage_fee'];
                $changeUserLog->create([
                    'app_id' => $apply_cash['app_id'],
                    'before_money' => $three_user->money,
                    'before_next_money' => $money,
                    'after_money' => ($three_user->money + $money),
                    'from_type' => 6,
                    'from_info' => 'FUWD',
                ]);
                $threeUserModel->addMoney($apply_cash['app_id'], $money);
            }

            DB::connection('db001')->commit();
            return $this->getResponse('');
        } catch (\Exception $e) {
            DB::connection('db001')->rollBack();
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
            $userModel = new AppUserInfoOut();
            $taobaoGetModel = new TaobaoUserGetOther();
            $threeGetModel = new ThreeUserGet();
            $adUserModel = new AdUserInfoOut();
            $bonusLogModel = new BonusLogOut();

            $taobaoChangeUserLog = new TaobaoChangeUserLogOut();
//            $otherChangeLogModel = new ThreeChangeUserLog();
            $wheres['type'] = 0;
            $limit = $params['limit'];
            $endTime = date('Y-m-d H:i:s', $params['endTime']);
            $list = $threeGetModel->where($wheres)->where('created_at', '<=', $endTime);
            $list = $list->orderBy('id', 'asc')->paginate($limit);
            $list = $list->toArray();
            foreach ($list['data'] as $key => $v) {
                //申请提现的金额
                $apply_cash = $v['money'] + $v['manage_fee'];
                $list['data'][$key]['apply_cash'] = round($apply_cash, 2);
//                //订单返现金额
//                $order_amount = $taobaoChangeUserLog->where(['app_id' => $v['app_id'], 'from_type' => 0])->sum('before_next_money');
//
//
//                //待报销订单笔数
////            $verify_order_total_number = $taobaoMaidOld->where(['app_id' => $v['app_id'], 'real' => 0])->count();
//                $res = DB::connection('app38')->select("select count(id) as res from lc_taobao_maid_old where `app_id`={$v['app_id']} AND `real`=0");
//
//                $verify_order_total_number = (int)$res[0]->res;
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
                $cash_amount_other = $threeGetModel->where(['app_id' => $v['app_id'], 'type' => 1])->sum('money');
//                //报销历史总金额,统计user_order_new表
//                $cashback_amount = $userOrderNewModel->where(['user_id' => $v['app_id'], 'status' => 9])->sum('cashback_amount');

                //上月报销总金额
//                $begin_time_stamp = strtotime(date('Y-m-01 00:00:00', strtotime('-1 month')));
//                $end_time_stamp = strtotime(date("Y-m-d 23:59:59", strtotime(-date('d') . 'day')));
//
//
//                $last_cashback_amount = $userOrderNewModel->where('user_id', $v['app_id'])
//                    ->where(['status' => ['in', [3, 4, 9]]])
//                    ->whereBetween('create_time', [$begin_time_stamp, $end_time_stamp])
//                    ->sum('cashback_amount');
                //历史分红总金额
                $bonus_log_amount = $taobaoChangeUserLog->where('app_id', $v['app_id'])->where(['from_type' => ['in', [2, 4]]])->sum('before_next_money');


                //分红金额
                $bonus_amount = $bonusLogModel->where('user_id', $v['app_id'])->orderBy('id', 'desc')->limit(1)->value('bonus_amount');

                //团队直属下级人数
                $children_count = $this->getRangeChildrenCount($v['app_id']);


                //什么级别2：普通用户/vip/优质转正
                $obj_user = $adUserModel->appToAdUserId($v['app_id']);
                $user_groupid = [
                    '10' => '普通用户',
                    '23' => 'VIP',
                    '24' => '优质转正',
                ];
                $user_level = [
                    '1'=>'无',
                    '2'=>'实习',
                    '3'=>'转正',
                    '4'=>'经理',
                    '5'=>'董事',
                ];
                $list['data'][$key]['bonus_amount'] = $bonus_amount;
//                $list['data'][$key]['order_amount'] = $order_amount;
//                $list['data'][$key]['verify_order_total_number'] = $verify_order_total_number;
                $list['data'][$key]['history_active_value'] = $history_active_value;
                $list['data'][$key]['cash_amount'] = $cash_amount;
                $list['data'][$key]['cash_amount_other'] = $cash_amount_other;
//                $list['data'][$key]['cashback_amount'] = $cashback_amount;
//                $list['data'][$key]['last_cashback_amount'] = $last_cashback_amount;
                $list['data'][$key]['bonus_log_amount'] = $bonus_log_amount;
                $list['data'][$key]['children_count'] = @$children_count[$v['app_id']];
                $level = empty($obj_user_info->level) ? '0' : $obj_user_info->level;
                $list['data'][$key]['level'] = $user_level[$level];
                $list['data'][$key]['user_groupid'] = @$user_groupid[$obj_user->groupid];

            }
            return $this->getResponse($list);
        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                return $this->getInfoResponse($e->getCode(), $e->getMessage());
            }
            return $this->getInfoResponse(500, $e->getMessage());
        }


    }

    //获取直属下级人数
    function getRangeChildrenCount($user_ids)
    {
        $user_ids = is_array($user_ids) ? implode(',', $user_ids) : $user_ids;
        if (empty($user_ids)) {
            return false;
        }
        $sql = "
        select count(u.id) as count,a.id
        from `lc_user` as u
        inner join (select id from lc_user where id in ({$user_ids})) as a
        on u.parent_id in (a.id)
        where u.status = 1 
        group by a.id";
        $res = DB::connection('app38_out')->select($sql);
        $res = array_column($res, 'count', 'id');
        return $res;
    }
}
