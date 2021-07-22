<?php

namespace App\Http\Controllers\Admin;

use App\Entitys\Ad\ExchangeGrapeOrder;
use App\Entitys\Ad\RechargeOrder;
use App\Entitys\Ad\UserCreditLog;
use App\Entitys\Ad\VoipMoneyOrder;
use App\Entitys\App\ApplyCash;
use App\Entitys\App\ArticleCheckInfo;
use App\Entitys\App\ChangeReason;
use App\Entitys\App\CircleMaid;
use App\Entitys\App\CircleOrder;
use App\Entitys\App\CircleRed;
use App\Entitys\App\ShopOrders;
use App\Entitys\App\TodayMoneyChange;
use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class IndexController extends Controller
{
    /**
     * 每日财务报表
     * @param Request $request
     * @param TodayMoneyChange $todayMoneyChange
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function getListAdmin(Request $request, ArticleCheckInfo $articleCheckInfo, ExchangeGrapeOrder $exchangeGrapeOrder, ApplyCash $applyCash, ChangeReason $changeReason, UserCreditLog $creditLog, TodayMoneyChange $todayMoneyChange, VoipMoneyOrder $voipMoneyOrder, ShopOrders $shopOrders, RechargeOrder $rechargeOrder, CircleOrder $circleOrder, CircleRed $circleRed)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'today_time' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $ip_config = [
                '39.106.6.68',
                '39.105.154.214',
                '47.244.104.78',
                '121.204.113.103',
                '123.57.80.156',
                '110.87.32.168',
            ];
            if (!in_array($request->ip(), $ip_config)) {
                return $this->getInfoResponse('4004', '您的环境异常，请使用正常环境访问！');
            }

            $data_maid = $todayMoneyChange->getByTime($arrRequest['today_time']);
            $now_alipay_get_in = 0;
            $now_wechat_get_in = 0;
            $now_pyb_get_in = 0;
            $now_all_voip_get_in = 0;
            $now_all_shop_get_in = 0;
            $now_all_vip_get_in = 0;
            $now_all_ad_get_in = 0;
            $now_all_circle_get_in = 0;
            $now_all_red_get_in = 0;
            $time_start_int = $arrRequest['today_time'];
            $time_end_int = $arrRequest['today_time'] + 86400;
            $time_start = date('Y-m-d H:i:s', $time_start_int);
            $time_end = date('Y-m-d H:i:s', $time_end_int);
            $now_all_voip_get_in += $voipMoneyOrder->where('created_at', '>', $time_start)->where('created_at', '<', $time_end)->where(['status' => 1, 'buy_type' => 1])->sum('real_price');
            $now_all_voip_get_in += $voipMoneyOrder->where('created_at', '>', $time_start)->where('created_at', '<', $time_end)->where(['status' => 1, 'buy_type' => 3])->sum('real_price');
            $now_all_voip_get_in += $voipMoneyOrder->where('created_at', '>', $time_start)->where('created_at', '<', $time_end)->where(['status' => 1, 'buy_type' => 2])->sum('real_price');
            $now_alipay_get_in += $voipMoneyOrder->where('created_at', '>', $time_start)->where('created_at', '<', $time_end)->where(['status' => 1, 'buy_type' => 1])->sum('real_price');
            $now_wechat_get_in += $voipMoneyOrder->where('created_at', '>', $time_start)->where('created_at', '<', $time_end)->where(['status' => 1, 'buy_type' => 3])->sum('real_price');
            $now_pyb_get_in += $voipMoneyOrder->where('created_at', '>', $time_start)->where('created_at', '<', $time_end)->where(['status' => 1, 'buy_type' => 2])->sum('real_price');
            $now_all_shop_get_in += $shopOrders->where('created_at', '>', $time_start)->where('created_at', '<', $time_end)->where('price', '<=', '650')->where('status', '>', '0')->sum('real_price');
            $now_all_shop_get_in += $shopOrders->where('created_at', '>', $time_start)->where('created_at', '<', $time_end)->where('price', '<=', '650')->where('status', '>', '0')->sum('ptb_number') / 10;
            $now_all_vip_get_in += $shopOrders->where('created_at', '>', $time_start)->where('created_at', '<', $time_end)->where('price', '>', '650')->where('status', '>', '0')->sum('real_price');
            $now_all_vip_get_in += $shopOrders->where('created_at', '>', $time_start)->where('created_at', '<', $time_end)->where('price', '>', '650')->where('status', '>', '0')->sum('ptb_number') / 10;
            $now_alipay_get_in += $shopOrders->where('created_at', '>', $time_start)->where('created_at', '<', $time_end)->where('status', '>', '0')->sum('real_price');
            $now_pyb_get_in += $shopOrders->where('created_at', '>', $time_start)->where('created_at', '<', $time_end)->where('status', '>', '0')->sum('ptb_number') / 10;
            $now_all_ad_get_in += $rechargeOrder->where('submitdate', '>', $time_start_int)->where('submitdate', '<', $time_end_int)->where('price', '<', '100')->where('status', '=', '2')->sum('price');
            $now_alipay_get_in += $rechargeOrder->where('submitdate', '>', $time_start_int)->where('submitdate', '<', $time_end_int)->where('price', '<', '100')->where('status', '=', '2')->sum('price');
            $now_all_circle_get_in += $circleOrder->where('created_at', '>', $time_start)->where('created_at', '<', $time_end)->where(['buy_type' => '1', 'status' => '1'])->sum('money');
            $now_all_circle_get_in += $circleOrder->where('created_at', '>', $time_start)->where('created_at', '<', $time_end)->where(['buy_type' => '2', 'status' => '1'])->sum('money');
            $now_alipay_get_in += $circleOrder->where('created_at', '>', $time_start)->where('created_at', '<', $time_end)->where(['buy_type' => '1', 'status' => '1'])->sum('money');
            $now_pyb_get_in += $circleOrder->where('created_at', '>', $time_start)->where('created_at', '<', $time_end)->where(['buy_type' => '2', 'status' => '1'])->sum('money');
            $now_all_red_get_in += $circleRed->where('created_at', '>', $time_start)->where('created_at', '<', $time_end)->where('order_id', '<>', '0')->where(['status' => '1'])->sum('price');
            $now_all_red_get_in += $circleRed->where('created_at', '>', $time_start)->where('created_at', '<', $time_end)->where('order_id', '=', '0')->where(['status' => '1'])->sum('price');
            $now_alipay_get_in += $circleRed->where('created_at', '>', $time_start)->where('created_at', '<', $time_end)->where('order_id', '<>', '0')->where(['status' => '1'])->sum('price');
            $now_pyb_get_in += $circleRed->where('created_at', '>', $time_start)->where('created_at', '<', $time_end)->where('order_id', '=', '0')->where(['status' => '1'])->sum('price');

            $now_get_in = $now_alipay_get_in + $now_wechat_get_in + $now_pyb_get_in;

            $credit_logs = $creditLog->where('dateline', '>', $time_start_int)->where('dateline', '<', $time_end_int)->where(['operation' => 'RPR'])->get();
            foreach ($credit_logs as $k => $log) {
                $reason = $changeReason->where(['change_id' => $log->logid])->first();
                if (!empty($reason)) {
                    $credit_logs[$k]->reason = $reason->info;
                } else {
                    $credit_logs[$k]->reason = '旧数据，未填写理由';
                }
            }
//

            $circleMaid = new CircleMaid();
            $commission_circle_bidding = $circleMaid->where('created_at', '>', $time_start)->where('created_at', '<', $time_end)
                    ->whereIn('type', [1, 5])
                    ->sum('money') / 10;
            $commission_circle_buy = $circleMaid->where('created_at', '>', $time_start)->where('created_at', '<', $time_end)
                    ->whereIn('type', [2, 4, 6])
                    ->sum('money') / 10;


            $now_all_circle_bidding_get_in = $circleOrder->where('created_at', '>', $time_start)->where('created_at', '<', $time_end)
                ->where('money', '<>', '600')
                ->where(['status' => '1'])
                ->sum('money');
            $now_all_circle_buy_get_in = $circleOrder->where('created_at', '>', $time_start)->where('created_at', '<', $time_end)
                ->where(['money' => 600, 'status' => '1'])
                ->sum('money');

            $get_article_commission = $articleCheckInfo->where('created_at', '>', $time_start)->where('created_at', '<', $time_end)
                ->count();

            return $this->getResponse([
                'now_date' => date('Y-m-d', $arrRequest['today_time']),
                'now_get_in' => $now_get_in,
                'now_alipay_get_in' => $now_alipay_get_in,
                'now_wechat_get_in' => $now_wechat_get_in,
                'now_ptb_get_in' => $now_pyb_get_in,
                'now_today_change' => ($credit_logs->sum('extcredits4') / 10),
                'now_today_change_list' => $credit_logs,

                'now_all_voip_get_in' => $now_all_voip_get_in,
                'now_all_shop_get_in' => $now_all_shop_get_in,
                'now_all_vip_get_in' => $now_all_vip_get_in,
                'now_all_ad_get_in' => $now_all_ad_get_in,
                'now_all_circle_get_in' => $now_all_circle_get_in,
                'now_all_circle_bidding_get_in' => $now_all_circle_bidding_get_in,
                'now_all_circle_buy_get_in' => $now_all_circle_buy_get_in,
                'now_all_red_get_in' => $now_all_red_get_in,

                'now_today_app_cash' => empty($data_maid->now_today_app_cash) ? '脚本计算中...请耐心等待' : $data_maid->now_today_app_cash,
                'now_today_app_cash_all' => empty($data_maid->now_today_app_cash_all) ? '脚本计算中...请耐心等待' : $data_maid->now_today_app_cash_all,
                'now_today_app_cash_dividend' => empty($data_maid->now_today_app_cash_dividend) ? '脚本计算中...请耐心等待' : $data_maid->now_today_app_cash_dividend,
                'now_today_app_cash_submit' => empty($data_maid->now_today_app_cash_submit) ? '脚本计算中...请耐心等待' : $data_maid->now_today_app_cash_submit,
                'now_today_app_cash_success' => empty($data_maid->now_today_app_cash_success) ? '脚本计算中...请耐心等待' : $data_maid->now_today_app_cash_success,
                'now_today_app_cash_fail' => empty($data_maid->now_today_app_cash_fail) ? '脚本计算中...请耐心等待' : $data_maid->now_today_app_cash_fail,
                'now_today_ad_cash' => empty($data_maid->now_today_ad_cash) ? '脚本计算中...请耐心等待' : $data_maid->now_today_ad_cash,
                'now_today_ad_cash_success' => empty($data_maid->now_today_ad_cash_success) ? '脚本计算中...请耐心等待' : $data_maid->now_today_ad_cash_success,
                'now_today_ad_cash_fail' => empty($data_maid->now_today_ad_cash_fail) ? '脚本计算中...请耐心等待' : $data_maid->now_today_ad_cash_fail,
                'all_ptb_number' => empty($data_maid->all_ptb_number) ? '脚本计算中...请耐心等待' : $data_maid->all_ptb_number,
                'submit_new' => empty($data_maid->submit_new) ? '脚本计算中...请耐心等待' : $data_maid->submit_new,
                'to_month_ok_submit_new' => empty($data_maid->to_month_ok_submit_new) ? '脚本计算中...请耐心等待' : $data_maid->to_month_ok_submit_new,
                'to_month_submit_new' => empty($data_maid->to_month_submit_new) ? '脚本计算中...请耐心等待' : $data_maid->to_month_submit_new,
                'share_new' => empty($data_maid->share_new) ? '脚本计算中...请耐心等待' : $data_maid->share_new,
                'commission_all' => empty($data_maid->commission_all) ? '脚本计算中...请耐心等待' : $data_maid->commission_all,
                'commission_phone' => empty($data_maid->commission_phone) ? '脚本计算中...请耐心等待' : $data_maid->commission_phone,
                'commission_shop' => empty($data_maid->commission_shop) ? '脚本计算中...请耐心等待' : $data_maid->commission_shop,
                'commission_vip' => empty($data_maid->commission_vip) ? '脚本计算中...请耐心等待' : $data_maid->commission_vip,
                'commission_ad' => empty($data_maid->commission_ad) ? '脚本计算中...请耐心等待' : $data_maid->commission_ad,
                'commission_circle' => empty($data_maid->commission_circle) ? '脚本计算中...请耐心等待' : $data_maid->commission_circle,
                'commission_circle_bidding' => $commission_circle_bidding,
                'commission_circle_buy' => $commission_circle_buy,
                'commission_red' => empty($data_maid->commission_red) ? '脚本计算中...请耐心等待' : $data_maid->commission_red,

                'get_article_commission' => $get_article_commission / 10,
            ]);
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }
}
