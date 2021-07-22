<?php

namespace App\Services\Pay;

use Illuminate\Database\Eloquent\Model;
use Yansongda\Pay\Pay;

class RefundService extends Model
{


    /**
     * @param $order_id 本地订单号
     * @param $amount 退款金额
     * @param string $desc 退款描述
     * @return bool
     */
    public function aliRefund($order_id, $amount, $desc = '葡萄浏览器退款')
    {

        $order = [
            'out_trade_no' => $order_id,
            'refund_amount' => $amount,
            'refund_reason' => $desc
        ];
        /*
         array:10 [▼
          "code" => "10000"
          "msg" => "Success"
          "buyer_logon_id" => "177******20"
          "buyer_user_id" => "2088502770158108"
          "fund_change" => "N"
          "gmt_refund_pay" => "2019-08-21 17:43:39"
          "out_trade_no" => "CC20190821174230414916"
          "refund_fee" => "0.01"
          "send_back_fee" => "0.00"
          "trade_no" => "2019082122001458100517896520"
        ]

        array:10 [▼
          "code" => "10000"
          "msg" => "Success"
          "buyer_logon_id" => "177******20"
          "buyer_user_id" => "2088502770158108"
          "fund_change" => "Y"
          "gmt_refund_pay" => "2019-08-21 17:56:38"
          "out_trade_no" => "CC20190802172403929810"
          "refund_fee" => "0.01"
          "send_back_fee" => "0.00"
          "trade_no" => "2019080222001458100559850857"
        ]

         */
        try {
            $alipay = Pay::alipay(config('pay_refund.ali_config'));
            $result = $alipay->refund($order);
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        return true;
    }

    /**
     * @param $order_id 本地订单号
     * @param $amount 退款金额
     * @param string $desc 退款描述
     * @return bool
     */
    public function weRefund($order_id, $amount, $desc = '葡萄浏览器退款')
    {
        $order = [
            'out_trade_no' => $order_id,
            'out_refund_no' => uniqid(date('YmdHis')),
            'total_fee' => $amount * 100,
            'refund_fee' => $amount * 100,
            'refund_desc' => $desc,
        ];
        /*
         array:18 [▼
              "return_code" => "SUCCESS"
              "return_msg" => "OK"
              "appid" => "wxd2d9077a3072b5db"
              "mch_id" => "1521224461"
              "nonce_str" => "5SEKjaIdKsSoFGV1"
              "sign" => "448F34DA3151F446290884103349F709"
              "result_code" => "SUCCESS"
              "transaction_id" => "4200000378201908210855344911"
              "out_trade_no" => "20190821183907EmJHC"
              "out_refund_no" => "1566384734"
              "refund_id" => "50000701552019082111657779096"
              "refund_channel" => []
              "refund_fee" => "1"
              "coupon_refund_fee" => "0"
              "total_fee" => "1"
              "cash_fee" => "1"
              "coupon_refund_count" => "0"
              "cash_refund_fee" => "1"
            ]
         */
        try {
            $wechat = Pay::wechat(config('pay_refund.we_config'));
            $result = $wechat->refund($order);
        } catch (\Exception $e) {
            return $e->getMessage();
        }

        return true;
    }

}