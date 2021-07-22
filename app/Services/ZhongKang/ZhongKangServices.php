<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2019/7/12
 * Time: 16:13
 */

namespace App\Services\ZhongKang;

use App\Entitys\Ad\AdUserInfo;
use App\Entitys\Ad\UserAboutLog;
use App\Entitys\Ad\UserAccount;
use App\Entitys\Ad\UserCreditLog;
use App\Exceptions\ApiException;
use GuzzleHttp\Client;

class ZhongKangServices
{
    protected $app_key = 'putao';
    protected $app_secret = '2iMpsSlC6TzK';
    protected $url = 'https://www.viptijian.com/api';

    /*
    * 中康 预定体检套餐
    */
    public function startBook($arr)
    {
        $time = date("Y-m-d H:i:s");
        @$arr_parameter = [
            /*公共参数*/
            'app_key' => $this->app_key, #唯一appid标识
            'timestamp' => $time,        #时间 格式为yyyy-MM-dd HH:mm:ss
            'format' => "json",          #响应格式 默认json
            'v' => 2,                    #API协议版本 默认2
            'sign_method' => "MD5",      #签名摘要算法 默认MD5
            /*业务参数*/
            'zk_unit_id' => $arr['zk_unit_id'],      #中康平台的机构编号
            'zk_combo_id' => $arr['zk_combo_id'],    #中康平台的套餐编号
            'zk_combo_name' => $arr['zk_combo_name'],#中康平台的套餐名 可空
            'out_order_no' => $arr['out_order_no'],  #合作伙伴的唯一订单编号
            'tj_time' => $arr['tj_time'],            #用户预约的体检时间，格式如2018-07-01,数据精确到天
            'tj_name' => $arr['tj_name'],            #用户姓名
            'mobile' => $arr['mobile'],              #用户手机
            'amount' => $arr['amount'],              #订单总价 可空
            'quantity' => $arr['quantity'],          #预订人数，默认1人，一人一单
        ];
        if (empty($arr['amount'])) {
            unset($arr_parameter['amount']);
        }
        if (empty($arr['zk_combo_name'])) {
            unset($arr_parameter['zk_combo_name']);
        }
        $p = ksort($arr_parameter);
        if ($p) {
            $str = '';
            foreach ($arr_parameter as $k => $val) {
                $str .= $k . $val;
            }
        }
        $sign = md5($this->app_secret . $str . $this->app_secret);
        $client = new Client();
        $url = $this->url . '/book/startbook';
        @$arr_api_data = [
            /*公共参数*/
            'sign' => $sign,                                  #参数签名结果
            /*业务参数*/
            'tj_gender' => $arr['tj_gender'],                 #用户性别 1-男 2-女
            'tj_married' => $arr['tj_married'],               #用户婚否 1-已婚 2-未婚
            'tj_age' => $arr['tj_age'],                       #用户年龄
            'tj_ident' => $arr['tj_ident'],                   #用户身份证号
            'promo_amount' => $arr['promo_amount'],           #优惠金额 可空
            'promo_amount_desc' => $arr['promo_amount_desc'], #优惠金额说明 可空
            'comment' => $arr['comment'],                     #用户备注信息 可空
        ];
        if (empty($arr['promo_amount'])) {
            unset($arr_parameter['promo_amount']);
        }
        if (empty($arr['promo_amount_desc'])) {
            unset($arr_parameter['promo_amount_desc']);
        }
        if (empty($arr['comment'])) {
            unset($arr_parameter['comment']);
        }
        $post_api_data = array_merge($arr_parameter, $arr_api_data);

        $header_data = [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'form_params' => $post_api_data,
            'verify' => false
        ];
        $res_login_data = $client->request('POST', $url, $header_data);
        return (string)$res_login_data->getBody();
    }

    /*
     * 中康 取消订单接口通知
     */
    public function cancelBook($zk_unit_id, $zk_combo_id, $out_order_no, $reaspm = '')
    {
        $time = date("Y-m-d H:i:s");
        $arr_parameter = [
            /*公共参数*/
            'app_key' => $this->app_key, #唯一appid标识
            'timestamp' => $time,        #时间 格式为yyyy-MM-dd HH:mm:ss
            'format' => "json",          #响应格式 默认json
            'v' => 2,                    #API协议版本 默认2
            'sign_method' => "MD5",      #签名摘要算法 默认MD5
            /*业务参数*/
            'zk_unit_id' => $zk_unit_id,    #中康平台的机构编号
            'zk_combo_id' => $zk_combo_id,  #中康平台的套餐编号
            'out_order_no' => $out_order_no,#合作伙伴的唯一订单编号
            'reaspm' => $reaspm,            #取消原因 可空
        ];
        if (empty($reaspm)) {
            unset($arr_parameter['reaspm']);
        }
        $p = ksort($arr_parameter);
        if ($p) {
            $str = '';
            foreach ($arr_parameter as $k => $val) {
                $str .= $k . $val;
            }
        }
        $sign = md5($this->app_secret . $str . $this->app_secret);
        $client = new Client();
        $url = $this->url . '/book/cancelbook';
        $arr_api_data = [
            /*公共参数*/
            'sign' => $sign,             #参数签名结果
            /*业务参数*/

        ];
        $post_api_data = array_merge($arr_parameter, $arr_api_data);

        $header_data = [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'form_params' => $post_api_data,
            'verify' => false
        ];
        $res_login_data = $client->request('POST', $url, $header_data);
        return (string)$res_login_data->getBody();
    }

    /*
     * 中康 核销状态查询
     */
    public function consumeStatus($out_order_no)
    {
        $time = date("Y-m-d H:i:s");
        $arr_parameter = [
            /*公共参数*/
            'app_key' => $this->app_key,    #唯一appid标识
            'timestamp' => $time,           #时间 格式为yyyy-MM-dd HH:mm:ss
            'format' => "json",             #响应格式 默认json
            'v' => 2,                       #API协议版本 默认2
            'sign_method' => "MD5",         #签名摘要算法 默认MD5
            /*业务参数*/
            'out_order_no' => $out_order_no,#合作伙伴的唯一订单编号
        ];
        $p = ksort($arr_parameter);
        if ($p) {
            $str = '';
            foreach ($arr_parameter as $k => $val) {
                $str .= $k . $val;
            }
        }
        $sign = md5($this->app_secret . $str . $this->app_secret);
        $client = new Client();
        $url = $this->url . '/query/consumestatus';
        $arr_api_data = [
            /*公共参数*/
            'sign' => $sign,             #参数签名结果
            /*业务参数*/

        ];
        $post_api_data = array_merge($arr_parameter, $arr_api_data);

        $header_data = [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'form_params' => $post_api_data,
            'verify' => false
        ];
        $res_login_data = $client->request('POST', $url, $header_data);
        return (string)$res_login_data->getBody();
    }

    /*
    * 中康 修改体检预约时间
    */
    public function modifyBookTime($out_order_no, $zk_unit_id, $tj_time)
    {
        $time = date("Y-m-d H:i:s");
        $arr_parameter = [
            /*公共参数*/
            'app_key' => $this->app_key,    #唯一appid标识
            'timestamp' => $time,           #时间 格式为yyyy-MM-dd HH:mm:ss
            'format' => "json",             #响应格式 默认json
            'v' => 2,                       #API协议版本 默认2
            'sign_method' => "MD5",         #签名摘要算法 默认MD5
            /*业务参数*/
            'out_order_no' => $out_order_no,#中康平台的机构编号
            'zk_unit_id' => $zk_unit_id,    #中康平台的机构编号
            'tj_time' => $tj_time,          #用户改期后的预约的体检时间，格式如2018-07-01,数据精确到天
        ];
        $p = ksort($arr_parameter);
        if ($p) {
            $str = '';
            foreach ($arr_parameter as $k => $val) {
                $str .= $k . $val;
            }
        }
        $sign = md5($this->app_secret . $str . $this->app_secret);
        $client = new Client();
        $url = $this->url . '/book/modifybooktime';
        $arr_api_data = [
            /*公共参数*/
            'sign' => $sign,             #参数签名结果
            /*业务参数*/

        ];
        $post_api_data = array_merge($arr_parameter, $arr_api_data);

        $header_data = [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'form_params' => $post_api_data,
            'verify' => false
        ];
        $res_login_data = $client->request('POST', $url, $header_data);
        return (string)$res_login_data->getBody();
    }

    /*
     * 中康 获取机构可预约时间
     */
    public function remainCountByTime($zk_unit_id, $bookdate, $days)
    {
        $time = date("Y-m-d H:i:s");
        $arr_parameter = [
            /*公共参数*/
            'app_key' => $this->app_key, #唯一appid标识
            'timestamp' => $time,        #时间 格式为yyyy-MM-dd HH:mm:ss
            'format' => "json",          #响应格式 默认json
            'v' => 2,                    #API协议版本 默认2
            'sign_method' => "MD5",      #签名摘要算法 默认MD5
            /*业务参数*/
            'zk_unit_id' => $zk_unit_id, #中康平台的机构编号
            'bookdate' => $bookdate,     #用户选择查询开始的预约时间，格式如2018-07-01,数据精确到天
            'days' => $days,             #几天的查询时间组成，days=2，包含booktime的2天的体检时间,最大不超过90

        ];
        $p = ksort($arr_parameter);
        if ($p) {
            $str = '';
            foreach ($arr_parameter as $k => $val) {
                $str .= $k . $val;
            }
        }
        $sign = md5($this->app_secret . $str . $this->app_secret);
        $client = new Client();
        $url = $this->url . '/query/remaincountbytime';
        $arr_api_data = [
            /*公共参数*/
            'sign' => $sign,             #参数签名结果
            /*业务参数*/

        ];
        $post_api_data = array_merge($arr_parameter, $arr_api_data);

        $header_data = [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'form_params' => $post_api_data,
            'verify' => false
        ];
        $res_login_data = $client->request('POST', $url, $header_data);
        return (string)$res_login_data->getBody();
    }

    /*
     * 中康 获取机构列表4
     */
    public function getIsvUnits()
    {
        $time = date("Y-m-d H:i:s");
        $arr_parameter = [
            /*公共参数*/
            'app_key' => $this->app_key, #唯一appid标识
            'timestamp' => $time,        #时间 格式为yyyy-MM-dd HH:mm:ss
            'format' => "json",          #响应格式 默认json
            'v' => 2,                    #API协议版本 默认2
            'sign_method' => "MD5",      #签名摘要算法 默认MD5
            /*业务参数*/

        ];
        $p = ksort($arr_parameter);
        if ($p) {
            $str = '';
            foreach ($arr_parameter as $k => $val) {
                $str .= $k . $val;
            }
        }
        $sign = md5($this->app_secret . $str . $this->app_secret);
        $client = new Client();
        $url = $this->url . '/query/getisvunits';
        $arr_api_data = [
            /*公共参数*/
            'sign' => $sign,             #参数签名结果
            'sign' => $sign,             #参数签名结果
            /*业务参数*/

        ];
        $post_api_data = array_merge($arr_parameter, $arr_api_data);

        $header_data = [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'form_params' => $post_api_data,
            'verify' => false
        ];
        $res_login_data = $client->request('POST', $url, $header_data);
        return (string)$res_login_data->getBody();
    }

    /*
     * 中康 获取机构套餐集合35491 6-1普及型体检套餐（女已婚）
     */
    public function getIsvUnitCombos($zk_unit_id)
    {
        $time = date("Y-m-d H:i:s");
        $arr_parameter = [
            /*公共参数*/
            'app_key' => $this->app_key, #唯一appid标识
            'timestamp' => $time,        #时间 格式为yyyy-MM-dd HH:mm:ss
            'format' => "json",          #响应格式 默认json
            'v' => 2,                    #API协议版本 默认2
            'sign_method' => "MD5",      #签名摘要算法 默认MD5
            /*业务参数*/
            'zk_unit_id' => $zk_unit_id  #中康平台的机构编号
        ];
        $p = ksort($arr_parameter);
        if ($p) {
            $str = '';
            foreach ($arr_parameter as $k => $val) {
                $str .= $k . $val;
            }
        }
        $sign = md5($this->app_secret . $str . $this->app_secret);
        $client = new Client();
        $url = $this->url . '/query/getisvunitcombos';
        $arr_api_data = [
            /*公共参数*/
            'sign' => $sign,             #参数签名结果
            /*业务参数*/

        ];
        $post_api_data = array_merge($arr_parameter, $arr_api_data);

        $header_data = [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'form_params' => $post_api_data,
            'verify' => false
        ];
        $res_login_data = $client->request('POST', $url, $header_data);
        return (string)$res_login_data->getBody();
    }

    /*
    * 中康 体检机构对接城市列表查询
    */
    public function getIsvCitys()
    {
        $time = date("Y-m-d H:i:s");
        $arr_parameter = [
            /*公共参数*/
            'app_key' => $this->app_key, #唯一appid标识
            'timestamp' => $time,        #时间 格式为yyyy-MM-dd HH:mm:ss
            'format' => "json",          #响应格式 默认json
            'v' => 2,                    #API协议版本 默认2
            'sign_method' => "MD5",      #签名摘要算法 默认MD5
            /*业务参数*/

        ];
        $p = ksort($arr_parameter);
        if ($p) {
            $str = '';
            foreach ($arr_parameter as $k => $val) {
                $str .= $k . $val;
            }
        }
        $sign = md5($this->app_secret . $str . $this->app_secret);
        $client = new Client();
        $url = $this->url . '/query/getisvcitys';
        $arr_api_data = [
            /*公共参数*/
            'sign' => $sign,             #参数签名结果
            /*业务参数*/

        ];
        $post_api_data = array_merge($arr_parameter, $arr_api_data);

        $header_data = [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'form_params' => $post_api_data,
            'verify' => false
        ];
        $res_login_data = $client->request('POST', $url, $header_data);
        return (string)$res_login_data->getBody();
    }

    /*
    * 中康 订单列表查询
    */
    public function queryOrders($pa = '', $begin = '', $end = '')
    {
        $time = date("Y-m-d H:i:s");
        $arr_parameter = [
            /*公共参数*/
            'app_key' => $this->app_key, #唯一appid标识
            'timestamp' => $time,        #时间 格式为yyyy-MM-dd HH:mm:ss
            'format' => "json",          #响应格式 默认json
            'v' => 2,                    #API协议版本 默认2
            'sign_method' => "MD5",      #签名摘要算法 默认MD5
            /*业务参数*/
            'p' => $pa,                  #当前请求分页,如果不传就默认是1 可空
            'begin' => $begin,           #查询订单开始日期,例如:2018-12-01 精确到日期 可空
            'end' => $end                #查询订单结束日期,例如:2018-12-01 精确到日期 可空
        ];
        if (empty($p)) {
            unset($arr_parameter['p']);
        }
        if (empty($begin)) {
            unset($arr_parameter['begin']);
        }
        if (empty($end)) {
            unset($arr_parameter['end']);
        }
        $p = ksort($arr_parameter);
        if ($p) {
            $str = '';
            foreach ($arr_parameter as $k => $val) {
                $str .= $k . $val;
            }
        }
        $sign = md5($this->app_secret . $str . $this->app_secret);
        $client = new Client();
        $url = $this->url . '/query/queryorders';
        $arr_api_data = [
            /*公共参数*/
            'sign' => $sign,             #参数签名结果
            /*业务参数*/

        ];
        $post_api_data = array_merge($arr_parameter, $arr_api_data);

        $header_data = [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'form_params' => $post_api_data,
            'verify' => false
        ];
        $res_login_data = $client->request('POST', $url, $header_data);
        return (string)$res_login_data->getBody();
    }

    /*
    * 中康 订单详情查询
    */
    public function queryOrderDetails($orderno)
    {
        $time = date("Y-m-d H:i:s");
        $arr_parameter = [
            /*公共参数*/
            'app_key' => $this->app_key, #唯一appid标识
            'timestamp' => $time,        #时间 格式为yyyy-MM-dd HH:mm:ss
            'format' => "json",          #响应格式 默认json
            'v' => 2,                    #API协议版本 默认2
            'sign_method' => "MD5",      #签名摘要算法 默认MD5
            /*业务参数*/
            'orderno' => $orderno,       #在中康体检网的唯一的订单编号
        ];
        $p = ksort($arr_parameter);
        if ($p) {
            $str = '';
            foreach ($arr_parameter as $k => $val) {
                $str .= $k . $val;
            }
        }
        $sign = md5($this->app_secret . $str . $this->app_secret);
        $client = new Client();
        $url = $this->url . '/query/queryorderdetails';
        $arr_api_data = [
            /*公共参数*/
            'sign' => $sign,             #参数签名结果
            /*业务参数*/

        ];
        $post_api_data = array_merge($arr_parameter, $arr_api_data);

        $header_data = [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'form_params' => $post_api_data,
            'verify' => false
        ];
        $res_login_data = $client->request('POST', $url, $header_data);
        return (string)$res_login_data->getBody();
    }

    /*
     * 中康 获取定制套餐列表
     */
    public function getCustomCombos()
    {
        $time = date("Y-m-d H:i:s");
        $arr_parameter = [
            /*公共参数*/
            'app_key' => $this->app_key, #唯一appid标识
            'timestamp' => $time,        #时间 格式为yyyy-MM-dd HH:mm:ss
            'format' => "json",          #响应格式 默认json
            'v' => 2,                    #API协议版本 默认2
            'sign_method' => "MD5",      #签名摘要算法 默认MD5
            /*业务参数*/

        ];
        $p = ksort($arr_parameter);
        if ($p) {
            $str = '';
            foreach ($arr_parameter as $k => $val) {
                $str .= $k . $val;
            }
        }
        $sign = md5($this->app_secret . $str . $this->app_secret);
        $client = new Client();
        $url = $this->url . '/custom/getcustomcombos';
        $arr_api_data = [
            /*公共参数*/
            'sign' => $sign,             #参数签名结果
            /*业务参数*/

        ];
        $post_api_data = array_merge($arr_parameter, $arr_api_data);

        $header_data = [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'form_params' => $post_api_data,
            'verify' => false
        ];
        $res_login_data = $client->request('POST', $url, $header_data);
        return (string)$res_login_data->getBody();
    }

    /*
     * 中康 获取定制套餐绑定的体检机构列表
     */
    public function getCustomComboBindUnits($cb_id)
    {
        $time = date("Y-m-d H:i:s");
        $arr_parameter = [
            /*公共参数*/
            'app_key' => $this->app_key, #唯一appid标识
            'timestamp' => $time,        #时间 格式为yyyy-MM-dd HH:mm:ss
            'format' => "json",          #响应格式 默认json
            'v' => 2,                    #API协议版本 默认2
            'sign_method' => "MD5",      #签名摘要算法 默认MD5
            /*业务参数*/
            'cb_id' => $cb_id            #套餐编号

        ];
        $p = ksort($arr_parameter);
        if ($p) {
            $str = '';
            foreach ($arr_parameter as $k => $val) {
                $str .= $k . $val;
            }
        }
        $sign = md5($this->app_secret . $str . $this->app_secret);
        $client = new Client();
        $url = $this->url . '/custom/getcustomcombobindunits';
        $arr_api_data = [
            /*公共参数*/
            'sign' => $sign,             #参数签名结果
            /*业务参数*/

        ];
        $post_api_data = array_merge($arr_parameter, $arr_api_data);

        $header_data = [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'form_params' => $post_api_data,
            'verify' => false
        ];
        $res_login_data = $client->request('POST', $url, $header_data);
        return (string)$res_login_data->getBody();
    }

    /*
     * 城市拉取
     */
    public function getCity()
    {
        $time = date("Y-m-d H:i:s");
        $arr_parameter = [
            /*公共参数*/
            'app_key' => $this->app_key, #唯一appid标识
            'timestamp' => $time,        #时间 格式为yyyy-MM-dd HH:mm:ss
            'format' => "json",          #响应格式 默认json
            'v' => 2,                    #API协议版本 默认2
            'sign_method' => "MD5",      #签名摘要算法 默认MD5
            /*业务参数*/

        ];
        $p = ksort($arr_parameter);
        if ($p) {
            $str = '';
            foreach ($arr_parameter as $k => $val) {
                $str .= $k . $val;
            }
        }
        $sign = md5($this->app_secret . $str . $this->app_secret);
        $client = new Client();
        $url = $this->url . '/query/getisvcitys';
        $arr_api_data = [
            /*公共参数*/
            'sign' => $sign,             #参数签名结果
            /*业务参数*/

        ];
        $post_api_data = array_merge($arr_parameter, $arr_api_data);

        $header_data = [
            'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
            'form_params' => $post_api_data,
            'verify' => false
        ];
        $res_login_data = $client->request('POST', $url, $header_data);
        return (string)$res_login_data->getBody();

    }


    /*
     * 通过用户 app_id 扣除相应葡萄币，并记录日志
     * $value 为葡萄币值
     * （独立方法，可直接调用）
     */
    public function takePtb($app_id, $value)
    {
        try {
            $obj_user = new AdUserInfo();
            $obj_info = $obj_user->appToAdUserId($app_id);
            $user_uid = $obj_info->uid;
            $username = $obj_info->username;
            $obj_account = new UserAccount();
            $user_ptb = $obj_account->getUserAccount($user_uid)->extcredits4;
            $obj_account->subtractPTBMoney($value, $user_uid);
            $obj_credit_log = new UserCreditLog();
            $obj_about_log = new UserAboutLog();
            $insert_id = $obj_credit_log->addLog($user_uid, "ZKP", ['extcredits4' => -$value]);
            $obj_about_log->addLog($insert_id, $user_uid, $username, $app_id, ["extcredits4" => $user_ptb], ["extcredits4" => $user_ptb - $value]);
        } catch (\Exception $e) {
            throw new ApiException('网络异常，扣费失败，请联系客服！', 5004);
        }

        return true;
    }
}