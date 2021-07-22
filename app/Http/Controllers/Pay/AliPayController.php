<?php

namespace App\Http\Controllers\Pay;

use App\Entitys\App\CanPayType;
use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Yansongda\LaravelPay\Facades\Pay;


class AliPayController extends Controller
{
    /**
     * wap:手机网站支付
     * web:电脑网站支付
     * app:原生支付
     * @return mixed
     */
    public function getSign()
    {
        $order = [
            'out_trade_no' => time(),
            'total_amount' => '0.01',
            'subject' => 'test subject - 测试',
        ];

        $alipay = Pay::alipay($this->config)->app($order);
        return $alipay;
    }

    /*
     * 得到开启的支付方式
     */
    public function getOpenPayData(CanPayType $canPayType, Request $request)
    {
        try {
            if (empty($request->from_this)) {
                $from_this = 0;
            } else {
                $from_this = $request->from_this;
            }

            $key = '1_get_open_pay_data_' . $from_this;

            if (Cache::has($key)) {
                $res = Cache::get($key);
                return $this->getResponse($res);
            }
            $where = [
                'status' => 1,
                'from_this' => $from_this
            ];
            $res = $canPayType->where($where)->get();
            $res = $res->toArray();
            if (!empty($request->from_this)) {
                if ($request->from_this == 1) {
                    foreach ($res as $k => $i) {
                        if ($i['id'] == 4) {
                            unset($res[$k]);
                        }
                    }
                }
            }

            Cache::put($key, $res, 5);
            return $this->getResponse($res);
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

}
