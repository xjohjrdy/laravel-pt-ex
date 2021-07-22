<?php

namespace App\Http\Controllers\Pay;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Yansongda\Pay\Pay;

class WechatController extends Controller
{
    protected $wechat_config = [
        'appid' => 'wxd2d9077a3072b5db',
        'mch_id' => '1521224461',
        'key' => 'wuhang1231wuhang7890wuhang886655',
        'notify_url' => 'http://api.36qq.com/api/wechat_pay_for_notify',
        'log' => [
            'file' => './logs/wechat.log',
            'level' => 'info',
            'type' => 'single',
            'max_file' => 30,
        ],
        'http' => [
            'timeout' => 5.0,
            'connect_timeout' => 5.0,
        ],
        'mode' => 'normal',
    ];

    public function index()
    {
        $order = [
            'out_trade_no' => time(),
            'total_fee' => '1',
            'body' => 'test body - 测试',
        ];
        $this->config['notify_url'] = 'http://api.36qq.com/api/';
        $pay = Pay::wechat($this->wechat_config)->app($order);

        return $pay;
    }

    public function notify()
    {

        Storage::disk('local')->append('callback_document/wechat_pay_notify.txt', var_export('step-1', true));
        $pay = Pay::wechat($this->wechat_config);

        try {
            $data = $pay->verify();

            Storage::disk('local')->append('callback_document/wechat_pay_notify.txt', var_export($data->out_trade_no, true));
            if ($data->return_code <> "SUCCESS") {

                Storage::disk('local')->append('callback_document/wechat_pay_notify.txt', var_export($data->out_trade_no, true));

                Storage::disk('local')->append('callback_document/wechat_pay_notify.txt', var_export($data->return_msg, true));
            }
            if ($data->total_fee <> "1") {

                Storage::disk('local')->append('callback_document/wechat_pay_notify.txt', var_export('金额不对等', true));

                Storage::disk('local')->append('callback_document/wechat_pay_notify.txt', var_export($data->total_fee, true));

                Storage::disk('local')->append('callback_document/wechat_pay_notify.txt', var_export("订单金额：1", true));
            }

            Storage::disk('local')->append('callback_document/wechat_pay_notify.txt', var_export('step-2', true));
        } catch (\Throwable $e) {

            Storage::disk('local')->append('callback_document/wechat_pay_notify.txt', var_export('error!need change is!', true));
        }
        return $pay->success();
    }
}
