<?php

namespace App\Services\Pay;

use Illuminate\Database\Eloquent\Model;

class EscrowPay extends Model
{
    private $key = '492E85A421514D0EB0D9DB99E7A3C32C';
    private $pay_api = "http://putao.api.fuzmh.com/channel/pay";
    private $notify_api = "http://jiaoyi.grachain.net/api/notify_escrow_pay";


    /**
     * 生成支付链接
     * @param $order_id
     * @param $amount
     * @param string $remark
     * @return bool
     */
    public function aliPay($order_id, $amount, $remark = '')
    {
        $data = [
            'tenantOrderNo' => $order_id,
            'notifyUrl' => $this->notify_api,
            'amount' => $amount,
            'payType' => 'alipay',
            'remark' => $remark,
        ];
        $data['sign'] = md5($this->serialization($data) . '&key=' . $this->key);
        $res = $this->postData($this->pay_api, $data);

        $arr_res = json_decode($res, true);

        if (empty($arr_res)) {
            return false;
        }

        if ($arr_res['status'] != 200) {
            return false;
        }

        return $arr_res['url'];
    }

    /**
     * 校验接收到的完整数据是否被篡改
     * @param $array
     * @return bool
     */
    public function verifyReceive($array)
    {
        if (!isset($array['sign'])) {
            return false;
        }
        $sign = strtolower($array['sign']);
        unset($array['sign']);
        
        $sign_maker = md5($this->serialization($array) . '&key=' . $this->key);
        if ($sign != $sign_maker) {
            return false;
        }

        return true;
    }


    /**
     * 发起post操作
     * @param $url
     * @param $data_string
     * @return mixed
     */
    private function postData($url, $data_string)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }

    /**
     * 对数组进行拼接排序
     * @param $array
     * @return string
     */
    private function serialization($array)
    {
        $arr = [];
        foreach ($array as $key => $value) {
            $arr[$key] = $key;
        }
        sort($arr);
        $str = "";
        foreach ($arr as $k => $v) {
            if ($k == 0) {
                $str = $v . '=' . $array[$v];
            } else {
                $str = $str . '&' . $v . '=' . $array[$v];
            }
        }
        return $str;
    }
}