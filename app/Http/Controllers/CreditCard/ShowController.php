<?php

namespace App\Http\Controllers\CreditCard;

use App\Entitys\Ad\AdUserInfo;
use App\Exceptions\ApiException;
use App\Services\KaDuoFen\KaDuoFenServices;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class ShowController extends Controller
{
    /*
     * 展示信用卡明细内容
     * @param Request $request
     */
    public function showDetail(Request $request, KaDuoFenServices $kaDuoFenServices)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'app_id' => 'integer',
                'tm' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $app_id = $arrRequest['app_id'];
            $tm = $arrRequest['tm'];

            if (Cache::has('credit_card_' . $app_id . $tm)) {
                $resq = Cache::get('credit_card_' . $app_id . $tm);
                return $this->getResponse($resq);
            }

            $year = substr($tm, 0, 4);
            $month = substr($tm, 4);

            $start_ts = mktime(0, 0, 0, $month, 1, $year);

            if (empty($start_ts)) {
                throw new ApiException('时间参数错误！！', 3002);
            }

            $start_time = date("Y-m-01 00:00:00", $start_ts);
            $end_time = date("Y-m-t 23:59:59", $start_ts);

            /*
             * 通过app_id
             * 查询 lc_card_maid 表 app_id = 1694511 sum（maid_ptb）/10
             */
            $num_all_earnings = $kaDuoFenServices->cardAllEstimateEarnings($app_id);
            $total_revenue = $num_all_earnings / 10;

            /*
             * 通过app_id
             * 查询 lc_card_maid 表
             * app_id = 1694511 sum（maid_ptb）/10
             * created_at $start_time  $end_time 范围内
             */
            $num_all_earnings = $kaDuoFenServices->cardEstimateEarnings($app_id, [$start_time, $end_time]);
            $this_month = $num_all_earnings / 10;

            /*
             * 直属预估佣金
             *
             * lc_card_maid
             *
             * 查出app_id=自己  type = 2 的全部数据 假设为 $orders
             *
             * foreach($orders as $item){
             *      $class_app_id = $item->from_app_id;
             *      if
             *        该用户的parent_id ！= 自己 ，则continue；
             *
             *      累加maid_ptb/10
             * }
             *
             */
            $num_directly_earnings = $kaDuoFenServices->cardDirectlyEstimateEarnings($app_id);
            $directly_money = $num_directly_earnings / 10;

            /*
             * 团队预估佣金
             * lc_card_maid
             * 查出app_id=自己  type = 2 的全部数据 sum(maid_ptb)/10
             */
            $num_team_earnings = $kaDuoFenServices->cardTeamEstimateEarnings($app_id);
            $team_money = $num_team_earnings / 10;

            /*
             * 团核卡成功人数
             * lc_card_maid
             * count订单数
             * app_id=自己
             * type = 2
             */
            $num_succeed_number = $kaDuoFenServices->cardSucceedNumber($app_id);

            $resq = [
                'total_revenue' => $total_revenue,
                'this_month' => $this_month,
                'directly_money' => $directly_money,
                'team_money' => $team_money,
                'succeed_num' => $num_succeed_number,
            ];

            Cache::put('credit_card_' . $app_id . $tm, $resq, 10);

            return $this->getResponse($resq);

        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
     * 信用卡登陆用
     */
    public function cardLogin(Request $request, KaDuoFenServices $kaDuoFenServices, AdUserInfo $adUserInfo)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'app_id' => 'required',
                'phone' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $app_id = $arrRequest['app_id'];
            $phone = $arrRequest['phone'];
            list($msec, $sec) = explode(' ', microtime());
            $time_ms = (string)sprintf('%.0f', (floatval($msec) + floatval($sec)) * 1000);
            $bonusRate = '20';
            $shareRate = '20';
            $ad_user_info = $adUserInfo->where(['pt_id' => $app_id])->first();
            $group_id = $ad_user_info->groupid;
            if (in_array($group_id, [23, 24])) {
                $bonusRate = '40';
                $shareRate = '40';
            }

            $data_sign = [
                'mobile' => $phone,
                'user_id' => $app_id,
                'timestamp' => $time_ms,
                'bonusRate' => $bonusRate,
                'shareRate' => $shareRate,
            ];

            $str_rsa_sign = $kaDuoFenServices->rsaSign($data_sign);

            $resq = [
                'encrypt' => $str_rsa_sign,     #encrypt生成
                'user_ratio' => $bonusRate,     #用户分佣比例
                'share_ratio' => $shareRate,    #分享佣金比例
            ];
            return $this->getResponse($resq);
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }
}
