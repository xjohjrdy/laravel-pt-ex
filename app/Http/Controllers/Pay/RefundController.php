<?php

namespace App\Http\Controllers\Pay;

use App\Exceptions\ApiException;
use App\Services\Pay\RefundService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Yansongda\Pay\Pay;


class RefundController extends Controller
{
    /*
     * 对订单进行退款操作
     */
    public function refund(Request $request)
    {

        try {

            $arrRequest = json_decode($request->data, true);
            $rules = [
                'order_id' => 'required',
                'amount' => 'required',
                'sign' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $desc = empty($arrRequest['desc']) ? '葡萄浏览器商城退款' : $arrRequest['desc'];
            $order_id = $arrRequest['order_id'];
            $amount = $arrRequest['amount'];
            $you_sign = $arrRequest['sign'];

            unset($arrRequest['sign']);

            $my_sign = md5(implode("/*1pt23*/", $arrRequest));

            if ($my_sign != $you_sign) {
                return $this->getInfoResponse('1001', '签名错误已记录IP！' . $request->ip());
            }

            $s_pay = new RefundService();

            $resq_ali = $s_pay->aliRefund($order_id, $amount, $desc);

            if ($resq_ali === true) {
                return $this->getResponse('退款成功');
            }

            $resq_we = $s_pay->weRefund($order_id, $amount, $desc);


            if ($resq_we === true) {
                return $this->getResponse('退款成功');
            }

            return $this->getInfoResponse('1000', $resq_ali . '---' . $resq_we);


        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }


    }


}
