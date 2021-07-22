<?php

namespace App\Services\Voip;


use App\Entitys\Ad\AdUserInfo;
use App\Entitys\Ad\VoipAccount;
use App\Entitys\Ad\VoipOrders;
use App\Entitys\App\AppUserInfo;
use App\Entitys\App\TaobaoChangeUserLog;
use App\Exceptions\ApiException;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class Call
{
    protected $app_id = 'c52d36a7263449f8b491fc528982f256';
    protected $app_key = '533f020516eb45c9ad5a81b48a6d11c8';
    protected $send_url = 'http://phone.voiper.cn:8585/api/comm/call';
    protected $return_url = 'http://api.36qq.com/voip_callback';
    protected $max_time = '60';
    protected $cost = 0.25;
    public $user_id;
    public $user_phone;

    /**
     * 判断用户余额是否够用
     * 并且设置用户最大通话分钟
     * @return bool
     */
    public function verifyMoney()
    {
        $obj_voip_account = new VoipAccount();
        $user_money = $obj_voip_account->where('app_id', $this->user_id)->value('money');
        if ($user_money > 0) {
            $max_time = intval($user_money / $this->cost);
            $this->max_time = $max_time < 120 ? $max_time : 120;
            return true;
        }
        return false;
    }

    /**
     * 打电话给某人（发送回拨呼叫）
     * @param $my_phone
     * @param $to_phone
     * @return bool
     * @throws ApiException
     */
    public function callUp($my_phone, $to_phone)
    {


        $params = array();
        $params['appid'] = '008143';
        $params['caller'] = $my_phone;
        $params['callees'] = $to_phone;
        $params['return_url'] = 'http://api.36qq.com/voip_callback';
        ksort($params);
        $requestString = http_build_query($params);
        $sign = md5('&' . URLdecode(http_build_query($params)) . 'g3m10gp06w8ge8fy532ujwyq3yeedomv');
        $requestString .= "&" . http_build_query(array('sign' => $sign, 'maxtime' => $this->max_time));
        $url = "http://call.tenzhao.com/apicall.do?command=call&" . $requestString;

        $client = new Client();
        $obj_res = $client->request('GET', $url);
        if ($obj_res->getStatusCode() != 200) {
            throw new ApiException('线路不通畅', '3001');
        }

        $json_res = json_decode((string)$obj_res->getBody(), true);

        if (empty($json_res)) {
            throw new ApiException('线路不通畅', '3002');
        }
        /*
         Array
        (
            [msg] => call success
            [code] => 1
            [data] => Array
                (
                    [orderid] => XT1809211687532382915980269597
                    [showno] => 15980269597
                )

        )
         */

        $res_code = @$json_res['code'];
        $res_orderid = @$json_res['data']['orderid'];
        if (!$res_orderid) {
            throw new ApiException('线路繁忙:' . $my_phone . ':' . $to_phone . '，请稍后再试！' . $res_code, '3003');
        }
        $obj_voip_order = new VoipOrders();
        $obj_voip_order->create([
            'app_id' => $this->user_id,
            'order_id' => $res_orderid,
            'caller' => $my_phone,
            'to_caller' => $to_phone,
            'return_url' => $this->return_url,
            'max_time' => $this->max_time,
            'code' => $res_code,
        ]);

        /*
            -1 appid错误     -2  IP受限         -3  签名不正确 
            -4  超过并发数  -5 不在呼叫时间段  -6 已呼叫  -7 余额不足
            -8  参数不正确  -11.线路繁忙
         */

        if ($res_code != 1) {

            $this->callUpNew($my_phone, $to_phone);
        }

        return true;

    }

    /**
     * 新打电话给某人（发送回拨呼叫）
     * @param $my_phone
     * @param $to_phone
     * @return bool
     * @throws ApiException
     */
    public function callUpNew($my_phone, $to_phone)
    {
        $params = array();
        $params['appid'] = '58939870';
        $params['caller'] = $my_phone;
        $params['callees'] = $to_phone;
        $params['return_url'] = 'http://api.36qq.com/api/voip_callback_new';
        ksort($params);
        $requestString = http_build_query($params);
        $sign = md5('&' . URLdecode(http_build_query($params)) . '5671158f37973d4fc7647c381374cf82');
        $requestString .= "&" . http_build_query(array('sign' => $sign, 'maxtime' => $this->max_time));
        $url = "http://api.cqfenlan.com:8085/webapi.aspx?command=call&" . $requestString;
        $client = new Client();
        $obj_res = $client->request('GET', $url);
        if ($obj_res->getStatusCode() != 200) {
            throw new ApiException('线路不通畅', '3001');
        }

        $json_res = json_decode((string)$obj_res->getBody(), true);

        if (empty($json_res)) {
            throw new ApiException('线路不通畅', '3002');
        }
        $res_code = @$json_res['code'];
        $res_orderid = @$json_res['data']['orderid'];
        if (!$res_orderid) {
            throw new ApiException('线路繁忙:' . $my_phone . ':' . $to_phone . '，请稍后再试！' . $res_code, '3003');
        }
        $obj_voip_order = new VoipOrders();
        $obj_voip_order->create([
            'app_id' => $this->user_id,
            'order_id' => $res_orderid,
            'caller' => $my_phone,
            'to_caller' => $to_phone,
            'return_url' => $this->return_url,
            'max_time' => $this->max_time,
            'code' => $res_code,
        ]);

        if ($res_code != 1) {
            throw new ApiException('线路繁忙，等待码' . @$json_res['code'], '3003');
        }

        return true;

    }

    /*
     * 久话通讯
     */
    public function callUpJiuHua($my_phone, $to_phone)
    {
        $client = new Client();
        if (!Cache::has('token_value_verification')) {
            $token_url = 'http://api.02110000.com:8088/api/login';
            $post_api_data = [
                "Username" => "20554",
                "Password" => "sqanAJ4CqPvPBRLOo2HvzpLGZjCTANKI"
            ];
            $token_url_data = [
                'headers' => ['Content-Type' => 'application/json'],
                'json' => $post_api_data,
            ];
            $res_create_oriented_problem_data = $client->request('POST', $token_url, $token_url_data);
            $tokem = json_decode((string)$res_create_oriented_problem_data->getBody(), true)['token'];
            if (empty($tokem)) {
                return $this->getInfoResponse('1001', '网络异常请稍后再试！');
            }
            Cache::put('token_value_verification', $tokem, 1400);
        }
        $token = Cache::get('token_value_verification');
        $call_url = 'http://api.02110000.com:8088/api/CallRequest';
        $post_api_data = [
            "Callid" => uniqid(),                                         #唯一id
            "App_id" => "20554",
            "Caller" => $my_phone,                                        #主叫
            "Callee" => $to_phone,                                        #被叫
            "Call_minutes" => (string)$this->max_time,                    #最大呼叫时间
            "Cdr_url" => 'http://api.36qq.com/api/voip_callback_jiu_hua', #接收推送话单的url
        ];
        $call_url_data = [
            'headers' => ['Content-Type' => 'application/json', 'Authorization' => 'JH ' . $token],
            'json' => $post_api_data,
        ];
        $res_jiu_you_m_data = $client->request('POST', $call_url, $call_url_data);
        if ($res_jiu_you_m_data->getStatusCode() != 200) {
            throw new ApiException('线路不通畅', '3001');
        }
        $arr_res = json_decode((string)$res_jiu_you_m_data->getBody(), true);
        $code_val = [
            '0' => '拨打成功，请稍候',
            '101' => '系统繁忙，请稍后再试',
            '102' => '系统繁忙，请稍后再试',
            '103' => '系统繁忙，请稍后再试',
            '104' => '您的余额不足，请先前往充值',
            '105' => '线路正忙，请您稍后再拨',
            '106' => '',
            '107' => '',
            '108' => '',
            '109' => '未获得应用权限',
            '110' => '操作频繁，请24小时后再试。',
            '112' => '',
            '113' => '号码异常，请确认后再呼叫',
            '114' => '您已达到呼叫上线',
            '116' => '本地区的线路还在升级中，预计10月份完成，请耐心等待',
            '117' => '呼叫的用户不存在',
            '501' => '身份验证不通过',
            '999' => ''
        ];

        if ($arr_res['result'] != 0) {
            throw new ApiException('呼叫失败,' . @$code_val[$arr_res['result']], '3002');
        }

        $res_result = @$arr_res['result'];
        $res_taskid = @$arr_res['taskid'];
        $obj_voip_order = new VoipOrders();
        $obj_voip_order->create([
            'app_id' => $this->user_id,
            'order_id' => $res_taskid,
            'caller' => $my_phone,
            'to_caller' => $to_phone,
            'return_url' => 'http://api.36qq.com/api/voip_callback_jiu_hua',
            'max_time' => $this->max_time,
            'code' => $res_result,
        ]);
        return true;
    }

    /**
     * 通过订单号检验该笔订单处理情况
     * @param $orderid
     * @return bool
     */
    public function verifyOrder($orderid)
    {
        $obj_voip_order = new VoipOrders();
        $arr_params = $obj_voip_order->where('order_id', $orderid)->first(['app_id', 'hold_time']);
        if (empty($arr_params)) {
            return false;
        }
        if ($arr_params->hold_time > 0) {
            return false;
        }

        $this->user_id = $arr_params->app_id;
        return true;

    }

    /**
     * 通过回调过来的参数 处理订单以及扣款
     * @param $get_param
     * @return bool
     * @throws \Exception
     * @throws \Throwable
     */
    public function updateOrder($get_param)
    {
        DB::transaction(function () use ($get_param) {
            $all_cost = $this->cost * $get_param['fee_time'];
            $obj_voip_account = new VoipAccount();
            $obj_voip_account->where(['app_id' => $this->user_id])->update(['money' => DB::raw("money - " . $all_cost)]);
            $obj_voip_order = new VoipOrders();
            $obj_voip_order
                ->where('order_id', $get_param['orderid'])
                ->update([
                    'state' => $get_param['state'],
                    'feed_time' => $get_param['fee_time'],
                    'hold_time' => $get_param['hold_time'],
                    'start_time' => isset($get_param['start']) ? $get_param['start'] : '',
                    'end_time' => isset($get_param['end']) ? $get_param['end'] : '',
                    'use_money' => $all_cost,
                ]);
        });

        return true;
    }

    /*
     * 久话通讯通话账单处理
     */
    public function updateOrderJiuHua($get_param)
    {
        DB::transaction(function () use ($get_param) {
            $all_cost = $this->cost * ($get_param['Call_duration'] / 60);
            $obj_voip_account = new VoipAccount();
            $obj_voip_account->where(['app_id' => $this->user_id])->update(['money' => DB::raw("money - " . $all_cost)]);
            $obj_voip_order = new VoipOrders();
            $obj_voip_order
                ->where('order_id', $get_param['Call_id'])
                ->update([
                    'state' => $get_param['Call_duration'] ? 1 : 0,                                     #接听状态
                    'feed_time' => ceil($get_param['Call_duration'] / 60),                       #通话时长（分钟）
                    'hold_time' => $get_param['Call_duration'],                                         #通话时长（秒）
                    'start_time' => $get_param['Call_starttime'] ? $get_param['Call_starttime'] : '',   #开始时间
                    'end_time' => $get_param['Call_endtime'] ? $get_param['Call_endtime'] : '',         #结束时间
                    'use_money' => $all_cost,                                                           #通话费用
                ]);
        });

        return true;
    }

    /**
     * 获取账户信息(使用前需定义user_id,user_phone
     * 如果找不到新用户，则重新创建一个新的账户
     */
    public function getAccountInfo()
    {
        $obj_voip_account = new VoipAccount();
        $account_info = $obj_voip_account->where('app_id', $this->user_id)->first();
        $account_info_phone = $obj_voip_account->where('phone', $this->user_phone)->first();
        if (empty($account_info) && empty($account_info_phone)) {
            $arr_info = [
                'app_id' => $this->user_id,
                'phone' => $this->user_phone,
                'money' => 0,
                'delete_time' => time() + 0 * 60 * 60 * 24,
                'is_new' => 1,
            ];
            $obj_voip_account->insert($arr_info);
            return $arr_info;
        }

        if (empty($account_info) && $account_info_phone) {
            $obj_voip_account->where(['phone' => $this->user_phone])->update(['app_id' => $this->user_id]);
            $arr_info = [
                'app_id' => $this->user_id,
                'phone' => $this->user_phone,
                'money' => $account_info_phone->money,
                'delete_time' => $account_info_phone->delete_time,
                'is_new' => $account_info_phone->is_new,
            ];
            return $arr_info;
        }

        return $account_info->toArray();
    }

    /**
     * 通过user_id 检测用户的话费是否过期
     * 如果话费过期了，则扣除所有的话费
     */
    public function checkFailure()
    {
        $obj_voip_account = new VoipAccount();
        $dele_time = $obj_voip_account->where('app_id', $this->user_id)->value('delete_time');

        if (time() > $dele_time) {
            $obj_voip_account->where('app_id', $this->user_id)->update(['money' => 0]);
        }

        return true;

    }

    /**
     * 查询指定日期的订单
     * @param $start_time
     * @param $end_time
     * @return array|bool
     */
    public function getOrderInfo($start_time, $end_time)
    {

        $obj_voip_order = new VoipOrders();

        $order_info = $obj_voip_order
            ->where('app_id', $this->user_id)
            ->whereBetween('created_at', [$start_time, $end_time])
            ->get(['created_at', 'to_caller', 'use_money']);

        if (empty($order_info)) {
            return [];
        }

        return $order_info->toArray();

    }

    /*
     * 久话通讯检测是否被拉黑
     */
    public function jiuHuaIsBlacklist($user_id, $my_phone)
    {
        $obj_voip_order = new VoipOrders();
        if (Cache::has('jiu_hua_is_blacklist_' . $my_phone)) {
            return true;
        }
        $start_time = (string)date("Y-m-d", time()) . " 00:00:00";
        $end_time = (string)date("Y-m-d", time()) . " 23:59:59";
        $present_start_time = (string)date("Y-m-d H:i:s", time());
        $present_end_time = (string)date("Y-m-d H:i:s", time() - 900);

        $int_order_info = $obj_voip_order
            ->where('app_id', (string)$user_id)
            ->whereBetween('created_at', [$start_time, $end_time])
            ->count("id");

        $int_info = $obj_voip_order
            ->where('app_id', (string)$user_id)
            ->whereBetween('created_at', [$present_end_time, $present_start_time])
            ->count("id");

        if ($int_order_info > 50 || $int_info > 7) {
            Cache::put('jiu_hua_is_blacklist_' . $my_phone, 1, 1440);
            return true;
        }
        return false;
    }

    /*
     * 判断被叫号码是否被封
     */
    public function isCall($phone_number)
    {
        if (Cache::has('to_call_' . $phone_number)) {
            return false;
        }

        $time_m_10 = date("Y-m-d H:i:s", strtotime("-10 minute"));
        $time_h_12 = date("Y-m-d H:i:s", strtotime("-12 hours"));

        $obj_orders = new VoipOrders();
        $count_m_10 = $obj_orders->whereRaw("to_caller='{$phone_number}' and created_at>'{$time_m_10}'")->count();
        $count_h_12 = $obj_orders->whereRaw("to_caller='{$phone_number}' and created_at>'{$time_h_12}'")->count();

        if ($count_m_10 > 10) {
            Cache::put('to_call_' . $phone_number, '1', 60 * 24);
            return false;
        }

        if ($count_h_12 > 15) {
            Cache::put('to_call_' . $phone_number, '1', 60 * 12);
            return false;
        }
        return true;
    }

    /**
     * this is can jump check
     */
    public function whiteList($app_id)
    {
        $appUserInfo = new AppUserInfo();
        $taobaoChange = new TaobaoChangeUserLog();
        $adUserInfo = new AdUserInfo();

        $user = $appUserInfo->getUserById($app_id);
        $ad_user = $adUserInfo->appToAdUserId($app_id);
        $taobao_zero_log = $taobaoChange->where([
            'app_id' => $app_id,
            'from_type' => 0,
        ])->count();
        $taobao_get_log = $taobaoChange->where([
            'app_id' => $app_id,
            'from_type' => 2,
        ])->count();

        $point = 0;
        if ((time() - $user->create_time) > 31536000) {
            $point++;
        }

        if ($user->level >= 2) {
            $point++;
        }

        if ($ad_user->groupid >= 23) {
            $point++;
        }

        if (!empty($taobao_zero_log)) {
            $point++;
        }

        if (!empty($taobao_get_log)) {
            $point++;
        }
        return $point;
    }

    /**
     * 新增校验
     * @return bool
     */
    public function checkSCheck($user_id, $to_phone)
    {
        $obj_voip_order = new VoipOrders();
        if (Cache::has('s_jiu_hua_is_blacklist_' . $user_id)) {
            return true;
        }
        $time_m_10 = date("Y-m-d H:i:s", strtotime("-10 minute"));

        $obj_orders = new VoipOrders();
        $int_info = $obj_orders
            ->where('app_id', (string)$user_id)
            ->whereRaw("to_caller='{$to_phone}' and created_at>'{$time_m_10}'")->count();

        if ($int_info > 5) {
            Cache::put('s_jiu_hua_is_blacklist_' . $user_id, 1, 10);
            return true;
        }
        return false;
    }
}
