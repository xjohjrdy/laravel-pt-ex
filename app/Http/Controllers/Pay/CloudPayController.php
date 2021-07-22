<?php

namespace App\Http\Controllers\Pay;

use App\Exceptions\ApiException;
use App\Services\Pay\CloudPay;
use com\unionpay\acp\sdk\AcpService;
use com\unionpay\acp\sdk\LogUtil;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Input;
use PhpParser\Node\Scalar\String_;

class CloudPayController extends Controller
{
    /**
     * 云闪付支付接口
     */
    public function cloudPay(Request $request, CloudPay $cloudPay)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $orderNo = @$arrRequest['orderNo'];
            $money = @$arrRequest['money'];
            $subject = @$arrRequest['subject'];
            $time = date('YmdHis');
            $result = $cloudPay->pay($orderNo, $money, $time, $subject);
            return $this->getResponse($result);
        } catch (\Throwable $e) {
            if (!empty($e->getMessage())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }


    /**
     * 云闪付app支付成功回调接口
     */
    public function cloudNotify(Request $request, CloudPay $cloudPay)
    {
        $logger = LogUtil::getLogger();
        $flag = [
            'result' => false
        ];
        $post = Input::all();
        $logger->LogInfo('获取金额' . @$post['txnAmt']);
        $logger->LogInfo('接口回调成功处理订单信息：' . var_export($post, true));
        if (isset($post['signature'])) {
            $res = AcpService::validate($post);
            $orderId = $post['orderId'];
            $respCode = $post['respCode'];
            $flag = $cloudPay->singleQuery($orderId);
        }
        if ($flag['result']) {

        }
        return $flag;


    }
}
