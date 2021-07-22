<?php

namespace App\Http\Controllers\Medical;

use App\Entitys\Ad\AdUserInfo;
use App\Entitys\Ad\UserAccount;
use App\Entitys\App\JsonConfig;
use App\Entitys\App\MedicalHospitalErrorOrders;
use App\Entitys\App\MedicalHospitalTestOrders;
use App\Exceptions\ApiException;
use App\Services\HeMengTong\HeMeToServices;
use App\Services\ZhongKang\ZhongKangServices;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Yansongda\Pay\Pay;

class CheckupController extends Controller
{

    /*
     * Appointment Time
     */
    public function getAppointmentTime(Request $request, ZhongKangServices $zhongKangServices)
    {
        $post_data = json_decode($request->data, true);
        $rules = [
            'app_id' => 'required',
            'zk_unit_id' => 'required',
            'bookdate' => 'required',
            'days' => 'required',
        ];
        $validator = Validator::make($post_data, $rules);
        if ($validator->fails()) {
            throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
        }

        $zk_unit_id = $post_data['zk_unit_id'];
        $bookdate = $post_data['bookdate'];
        $days = $post_data['days'];

        $json_resq = $zhongKangServices->remainCountByTime($zk_unit_id, $bookdate, $days);

        $arr_resq = json_decode($json_resq, true);

        if (empty($arr_resq)) {
            return $this->getInfoResponse('4004', '网络开小差请稍后再试！4004');
        }

        if (@$arr_resq['code'] != 0) {
            return $this->getInfoResponse('1001', @$arr_resq['msg']);
        }

        return $this->getResponse(@$arr_resq['data']);
    }

    /*
     * get packager
     */
    public function getPackager(Request $request, ZhongKangServices $zhongKangServices)
    {
        $post_data = json_decode($request->data, true);
        $rules = [
            'app_id' => 'required',
            'zk_unit_id' => 'required',
        ];
        $validator = Validator::make($post_data, $rules);
        if ($validator->fails()) {
            throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
        }

        $zk_unit_id = $post_data['zk_unit_id'];

        if (Cache::has('zk_packager_id_' . $zk_unit_id)) {
            return $this->getResponse(Cache::get('zk_packager_id_' . $zk_unit_id));
        }

        $json_resq = $zhongKangServices->getIsvUnitCombos($zk_unit_id);

        $arr_resq = json_decode($json_resq, true);

        if (empty($arr_resq)) {
            return $this->getInfoResponse('4004', '网络开小差请稍后再试！4004');
        }

        if (@$arr_resq['code'] != 0) {
            return $this->getInfoResponse('1001', @$arr_resq['msg']);
        }

        $obj_config = new JsonConfig();
        $arr_config_data = $obj_config->getValue('medical_config');
        $money_ratio = @$arr_config_data['packager_value'];
        if (empty($money_ratio)) $money_ratio = 1;
        $packager_list = @$arr_resq['data'];
        foreach ($packager_list as &$item) {
            $item['cb_title'] = preg_replace('/.+体检中心/', '', $item['cb_title']);
            $item['price_normal'] = $item['cb_oriprice'] * 2;
            $item['cb_oriprice'] = round($item['cb_price'] * $money_ratio, 2);
        }
        Cache::put('zk_packager_id_' . $zk_unit_id, $packager_list, 60 * 3);

        return $this->getResponse($packager_list);

    }

    /*
     * submit
     */
    public function submitPackager(Request $request, MedicalHospitalTestOrders $hospitalTestOrders, ZhongKangServices $zhongKangServices, UserAccount $userAccount)
    {
        $post_data = json_decode($request->data, true);
        $rules = [
            'app_id' => 'required',
            'type' => 'required',
            'zk_unit_id' => 'required',
            'zk_combo_id' => 'required',
            'tj_time' => 'required',
            'tj_name' => 'required',
            'mobile' => 'required',
            'tj_gender' => 'required',
            'tj_married' => 'required',
            'tj_age' => 'required',
            'tj_ident' => 'required',
            'hospital_name' => 'required',
            'hospital_address' => 'required',
        ];
        $validator = Validator::make($post_data, $rules);
        if ($validator->fails()) {
            throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
        }
        $app_id = $post_data['app_id'];
        $pay_type = $post_data['type'];
        $tj_gender = $post_data['tj_gender'];
        $tj_married = $post_data['tj_married'];
        if (Cache::has('zk_submit_packager_' . $app_id)) {
            return $this->getInfoResponse('2005', '请求频率过快，请稍后再试！');
        }

        Cache::put('zk_submit_packager_' . $app_id, 1, 0.1);
        $ad_user_info = AdUserInfo::where(['pt_id' => $app_id])->first();

        $pt_pid = $ad_user_info->pt_pid;

        $groupid = $ad_user_info->groupid;

        if (empty($pt_pid) && $groupid < 23) {
            return $this->getInfoResponse('5555', '无上级普通用户！5555');
        }


        if ($groupid < 23) {
            return $this->getInfoResponse('4444', '非超级用户会员！4444');
        }

        $zk_unit_id = $post_data['zk_unit_id'];
        $zk_combo_id = $post_data['zk_combo_id'];

        if (!Cache::has('zk_packager_id_' . $zk_unit_id)) {
            return $this->getInfoResponse('3005', '页面超时，请刷新后再试！3005');
        }

        $zk_packager_data = Cache::get('zk_packager_id_' . $zk_unit_id);
        $found_key = array_search($zk_combo_id, array_column($zk_packager_data, 'cb_id'));

        if ($found_key === false) {
            return $this->getInfoResponse('3005', '该套餐不存在！3005');
        }

        $item_packager = @$zk_packager_data[$found_key];

        $cb_oriprice = @$item_packager['cb_oriprice'];
        $zk_combo_name = @$item_packager['cb_title'];
        $cb_featrue = @$item_packager['cb_featrue'];

        $cb_sex = @$item_packager['cb_sex'];
        $cb_sexover = @$item_packager['cb_sexover'];

        if ($cb_sex != 0 && $tj_gender != $cb_sex) {
            return $this->getInfoResponse('3005', '请核对该套餐性别！3005');
        }

        if ($cb_sexover != 0 && $cb_sexover != $tj_married) {
            return $this->getInfoResponse('3005', '请核对该套餐婚否情况！3005');
        }


        if (empty($cb_oriprice)) {
            return $this->getInfoResponse('5001', '套餐价格异常！5001');
        }

        $mobile = $post_data['mobile'];
        $tj_name = $post_data['tj_name'];
        $tj_gender = $post_data['tj_gender'];
        $tj_married = $post_data['tj_married'];
        $tj_age = $post_data['tj_age'];
        $tj_ident = $post_data['tj_ident'];
        $tj_time = $post_data['tj_time'];
        $hospital_name = $post_data['hospital_name'];
        $hospital_address = $post_data['hospital_address'];
        $out_order_no = uniqid(mt_rand(10000, 99999));

        switch ($pay_type) {
            case 0:
                $user_account = $userAccount->getUserAccount($ad_user_info->uid);

                return $this->getInfoResponse('3001', '不支持我的币');
                if ($user_account->extcredits4 < $cb_oriprice * 10) {
                    return $this->getInfoResponse('3001', '我的币余额不足！');
                }
                $params = [
                    'app_id' => $app_id,
                    'my_order' => $out_order_no,
                    'zk_order_no' => '',
                    'mobile' => $mobile,
                    'name' => $tj_name,
                    'in_time' => $tj_time,
                    'zk_unit_id' => $zk_unit_id,
                    'hospital_name' => $hospital_name,
                    'hospital_address' => $hospital_address,
                    'zk_combo_id' => $zk_combo_id,
                    'zk_combo_name' => $zk_combo_name,
                    'desc' => $cb_featrue,
                    'use_money' => 0,
                    'use_ptb' => $cb_oriprice * 10,
                    'type' => $pay_type,
                    'status' => 0,
                    'real_sex' => $tj_gender,
                    'is_married' => $tj_married,
                    'age' => $tj_age,
                    'id_card' => $tj_ident,
                ];

                $hospitalTestOrders->addOrder($params);
                $params = [
                    'zk_unit_id' => $zk_unit_id,      #中康平台的机构编号
                    'zk_combo_id' => $zk_combo_id,    #中康平台的套餐编号
                    'zk_combo_name' => $zk_combo_name,#中康平台的套餐名 可空
                    'out_order_no' => $out_order_no,  #合作伙伴的唯一订单编号,#中康平台的套餐名 可空
                    'tj_time' => $tj_time,            #用户预约的体检时间，格式如2018-07-01,数据精确到天
                    'tj_name' => $tj_name,            #用户姓名
                    'mobile' => $mobile,              #用户手机
                    'amount' => $cb_oriprice,         #订单总价 可空
                    'quantity' => 1,                  #预订人数，默认1人，一人一单
                    'tj_gender' => $tj_gender,        #用户性别 1-男 2-女
                    'tj_married' => $tj_married,      #用户婚否 1-已婚 2-未婚
                    'tj_age' => $tj_age,              #用户年龄
                    'tj_ident' => $tj_ident,          #用户身份证号
                    'promo_amount' => '',             #优惠金额 可空
                    'promo_amount_desc' => '',        #优惠金额说明 可空
                    'comment' => '',                  #用户备注信息 可空
                ];

                $zk_resq = $zhongKangServices->startBook($params);
                $arr_zk_resq = json_decode($zk_resq, true);
                if (empty($arr_zk_resq) || @$arr_zk_resq['code'] != 0) {
                    $obj_error_order = new MedicalHospitalErrorOrders();
                    $obj_error_order->addErrorOrder([
                        'app_id' => $app_id,
                        'pay_type' => $pay_type,
                        'pay_ptb' => $cb_oriprice * 10,
                        'pay_zfb' => 0,
                        'pt_order' => $out_order_no,
                        'tj_time' => $tj_time,
                        'tj_name' => $tj_name,
                        'tj_ident' => $tj_ident,
                        'error_reason' => @$arr_zk_resq['code'] . ':' . @$arr_zk_resq['msg'],
                    ]);

                    $params = [
                        'status' => 7,
                    ];
                    $hospitalTestOrders->upOrder($out_order_no, $params);
                    return $this->getInfoResponse('3001', '订单出现异常，请联系客服！');
                }

                $params = [
                    'status' => 1,
                    'zk_order_no' => $arr_zk_resq['data']['zk_order_no']
                ];
                $hospitalTestOrders->upOrder($out_order_no, $params);
                $zhongKangServices->takePtb($app_id, $cb_oriprice * 10);

                break;
            case 1:
                $user_account = $userAccount->getUserAccount($ad_user_info->uid);
                return $this->getInfoResponse('3001', '不支持我的币');
                if ($user_account->extcredits4 > $cb_oriprice * 10) {
                    return $this->getInfoResponse('3001', '我的币充足，不可使用混合支付方式！');
                }

                $remaining = $cb_oriprice - ($user_account->extcredits4 / 10);
                $params = [
                    'app_id' => $app_id,
                    'my_order' => $out_order_no,
                    'zk_order_no' => '',
                    'mobile' => $mobile,
                    'name' => $tj_name,
                    'in_time' => $tj_time,
                    'zk_unit_id' => $zk_unit_id,
                    'hospital_name' => $hospital_name,
                    'hospital_address' => $hospital_address,
                    'zk_combo_id' => $zk_combo_id,
                    'zk_combo_name' => $zk_combo_name,
                    'desc' => $cb_featrue,
                    'use_money' => $remaining,
                    'use_ptb' => $user_account->extcredits4,
                    'type' => $pay_type,
                    'status' => 0,
                    'real_sex' => $tj_gender,
                    'is_married' => $tj_married,
                    'age' => $tj_age,
                    'id_card' => $tj_ident,
                ];

                $hospitalTestOrders->addOrder($params);
                $ali_value['out_trade_no'] = $out_order_no;
                $ali_value['total_amount'] = $cb_oriprice;
                $ali_value['subject'] = '我的医疗 - 剩余支付 - ' . $cb_oriprice . '元';
                $ali_secret = Pay::alipay(config('medical.ali_config'))->app($ali_value);
                return $this->getResponse($ali_secret->getContent());
                break;
            case 2:
                $params = [
                    'app_id' => $app_id,
                    'my_order' => $out_order_no,
                    'zk_order_no' => '',
                    'mobile' => $mobile,
                    'name' => $tj_name,
                    'in_time' => $tj_time,
                    'zk_unit_id' => $zk_unit_id,
                    'hospital_name' => $hospital_name,
                    'hospital_address' => $hospital_address,
                    'zk_combo_id' => $zk_combo_id,
                    'zk_combo_name' => $zk_combo_name,
                    'desc' => $cb_featrue,
                    'use_money' => $cb_oriprice,
                    'use_ptb' => 0,
                    'type' => $pay_type,
                    'status' => 0,
                    'real_sex' => $tj_gender,
                    'is_married' => $tj_married,
                    'age' => $tj_age,
                    'id_card' => $tj_ident,
                ];

                $hospitalTestOrders->addOrder($params);
                $ali_value['out_trade_no'] = $out_order_no;
                $ali_value['total_amount'] = $cb_oriprice;
                $ali_value['subject'] = '我的医疗 - ' . $cb_oriprice . '元';
                $ali_secret = Pay::alipay(config('medical.ali_config'))->app($ali_value);
                return $this->getResponse($ali_secret->getContent());
                break;
            case 3:
                $user_account = $userAccount->getUserAccount($ad_user_info->uid);
                return $this->getInfoResponse('3001', '不支持我的币');
                if ($user_account->extcredits4 > $cb_oriprice * 10) {
                    return $this->getInfoResponse('3001', '我的币充足，不可使用混合支付方式！');
                }

                $remaining = $cb_oriprice - ($user_account->extcredits4 / 10);
                $params = [
                    'app_id' => $app_id,
                    'my_order' => $out_order_no,
                    'zk_order_no' => '',
                    'mobile' => $mobile,
                    'name' => $tj_name,
                    'in_time' => $tj_time,
                    'zk_unit_id' => $zk_unit_id,
                    'hospital_name' => $hospital_name,
                    'hospital_address' => $hospital_address,
                    'zk_combo_id' => $zk_combo_id,
                    'zk_combo_name' => $zk_combo_name,
                    'desc' => $cb_featrue,
                    'use_money' => $remaining,
                    'use_ptb' => $user_account->extcredits4,
                    'type' => $pay_type,
                    'status' => 0,
                    'real_sex' => $tj_gender,
                    'is_married' => $tj_married,
                    'age' => $tj_age,
                    'id_card' => $tj_ident,
                ];

                $hospitalTestOrders->addOrder($params);
                $order = [
                    'out_trade_no' => $out_order_no,
                    'total_fee' => ($cb_oriprice * 100),
                    'body' => '我的医疗 - ' . $cb_oriprice . '元',
                ];

                $this->wechat_config['notify_url'] = config('medical.we_config.notify_url');

                $we_secret = Pay::wechat(config('medical.we_config'))->app($order);
                return $this->getResponse($we_secret->getContent());
                break;
            case 4:
                $params = [
                    'app_id' => $app_id,
                    'my_order' => $out_order_no,
                    'zk_order_no' => '',
                    'mobile' => $mobile,
                    'name' => $tj_name,
                    'in_time' => $tj_time,
                    'zk_unit_id' => $zk_unit_id,
                    'hospital_name' => $hospital_name,
                    'hospital_address' => $hospital_address,
                    'zk_combo_id' => $zk_combo_id,
                    'zk_combo_name' => $zk_combo_name,
                    'desc' => $cb_featrue,
                    'use_money' => $cb_oriprice,
                    'use_ptb' => 0,
                    'type' => $pay_type,
                    'status' => 0,
                    'real_sex' => $tj_gender,
                    'is_married' => $tj_married,
                    'age' => $tj_age,
                    'id_card' => $tj_ident,
                ];

                $hospitalTestOrders->addOrder($params);
                $order = [
                    'out_trade_no' => $out_order_no,
                    'total_fee' => ($cb_oriprice * 100),
                    'body' => '我的医疗 - ' . $cb_oriprice . '元',
                ];

                $this->wechat_config['notify_url'] = config('medical.we_config.notify_url');

                $we_secret = Pay::wechat(config('medical.we_config'))->app($order);

                return $this->getResponse($we_secret->getContent());
                break;
            default:
                return $this->getInfoResponse('3001', '请求错误！3001');
        }

        return $this->getResponse('申请成功');
    }

   /*
    * 医疗接he盟通支付
    */
    public function submitPackagerV2(Request $request, MedicalHospitalTestOrders $hospitalTestOrders, ZhongKangServices $zhongKangServices, UserAccount $userAccount)
    {
        return $this->getInfoResponse('1001', '医疗支付升级中,请耐心等待!');

        $post_data = json_decode($request->data, true);
        $rules = [
            'app_id' => 'required',
            'type' => 'required',
            'zk_unit_id' => 'required',
            'zk_combo_id' => 'required',
            'tj_time' => 'required',
            'tj_name' => 'required',
            'mobile' => 'required',
            'tj_gender' => 'required',
            'tj_married' => 'required',
            'tj_age' => 'required',
            'tj_ident' => 'required',
            'hospital_name' => 'required',
            'hospital_address' => 'required',
        ];
        $validator = Validator::make($post_data, $rules);
        if ($validator->fails()) {
            throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
        }
        $app_id = $post_data['app_id'];
        $pay_type = $post_data['type'];
        $tj_gender = $post_data['tj_gender'];
        $tj_married = $post_data['tj_married'];
        if (Cache::has('zk_submit_packager_' . $app_id)) {
            return $this->getInfoResponse('2005', '请求频率过快，请稍后再试！');
        }

        Cache::put('zk_submit_packager_' . $app_id, 1, 0.1);
        $ad_user_info = AdUserInfo::where(['pt_id' => $app_id])->first();

        $pt_pid = $ad_user_info->pt_pid;

        $groupid = $ad_user_info->groupid;

        if (empty($pt_pid) && $groupid < 23) {
            return $this->getInfoResponse('5555', '无上级普通用户！5555');
        }


        if ($groupid < 23) {
            return $this->getInfoResponse('4444', '非超级用户会员！4444');
        }

        $zk_unit_id = $post_data['zk_unit_id'];
        $zk_combo_id = $post_data['zk_combo_id'];

        if (!Cache::has('zk_packager_id_' . $zk_unit_id)) {
            return $this->getInfoResponse('3005', '页面超时，请刷新后再试！3005');
        }

        $zk_packager_data = Cache::get('zk_packager_id_' . $zk_unit_id);
        $found_key = array_search($zk_combo_id, array_column($zk_packager_data, 'cb_id'));

        if ($found_key === false) {
            return $this->getInfoResponse('3005', '该套餐不存在！3005');
        }

        $item_packager = @$zk_packager_data[$found_key];

        $cb_oriprice = @$item_packager['cb_oriprice'];
        $zk_combo_name = @$item_packager['cb_title'];
        $cb_featrue = @$item_packager['cb_featrue'];

        $cb_sex = @$item_packager['cb_sex'];
        $cb_sexover = @$item_packager['cb_sexover'];

        if ($cb_sex != 0 && $tj_gender != $cb_sex) {
            return $this->getInfoResponse('3005', '请核对该套餐性别！3005');
        }

        if ($cb_sexover != 0 && $cb_sexover != $tj_married) {
            return $this->getInfoResponse('3005', '请核对该套餐婚否情况！3005');
        }


        if (empty($cb_oriprice)) {
            return $this->getInfoResponse('5001', '套餐价格异常！5001');
        }

        $mobile = $post_data['mobile'];
        $tj_name = $post_data['tj_name'];
        $tj_gender = $post_data['tj_gender'];
        $tj_married = $post_data['tj_married'];
        $tj_age = $post_data['tj_age'];
        $tj_ident = $post_data['tj_ident'];
        $tj_time = $post_data['tj_time'];
        $hospital_name = $post_data['hospital_name'];
        $hospital_address = $post_data['hospital_address'];
        $out_order_no = uniqid(mt_rand(10000, 99999));

        switch ($pay_type) {
            case 0:
                $user_account = $userAccount->getUserAccount($ad_user_info->uid);

                return $this->getInfoResponse('3001', '不支持我的币');
                if ($user_account->extcredits4 < $cb_oriprice * 10) {
                    return $this->getInfoResponse('3001', '我的币余额不足！');
                }
                $params = [
                    'app_id' => $app_id,
                    'my_order' => $out_order_no,
                    'zk_order_no' => '',
                    'mobile' => $mobile,
                    'name' => $tj_name,
                    'in_time' => $tj_time,
                    'zk_unit_id' => $zk_unit_id,
                    'hospital_name' => $hospital_name,
                    'hospital_address' => $hospital_address,
                    'zk_combo_id' => $zk_combo_id,
                    'zk_combo_name' => $zk_combo_name,
                    'desc' => $cb_featrue,
                    'use_money' => 0,
                    'use_ptb' => $cb_oriprice * 10,
                    'type' => $pay_type,
                    'status' => 0,
                    'real_sex' => $tj_gender,
                    'is_married' => $tj_married,
                    'age' => $tj_age,
                    'id_card' => $tj_ident,
                ];

                $hospitalTestOrders->addOrder($params);
                $params = [
                    'zk_unit_id' => $zk_unit_id,      #中康平台的机构编号
                    'zk_combo_id' => $zk_combo_id,    #中康平台的套餐编号
                    'zk_combo_name' => $zk_combo_name,#中康平台的套餐名 可空
                    'out_order_no' => $out_order_no,  #合作伙伴的唯一订单编号,#中康平台的套餐名 可空
                    'tj_time' => $tj_time,            #用户预约的体检时间，格式如2018-07-01,数据精确到天
                    'tj_name' => $tj_name,            #用户姓名
                    'mobile' => $mobile,              #用户手机
                    'amount' => $cb_oriprice,         #订单总价 可空
                    'quantity' => 1,                  #预订人数，默认1人，一人一单
                    'tj_gender' => $tj_gender,        #用户性别 1-男 2-女
                    'tj_married' => $tj_married,      #用户婚否 1-已婚 2-未婚
                    'tj_age' => $tj_age,              #用户年龄
                    'tj_ident' => $tj_ident,          #用户身份证号
                    'promo_amount' => '',             #优惠金额 可空
                    'promo_amount_desc' => '',        #优惠金额说明 可空
                    'comment' => '',                  #用户备注信息 可空
                ];

                $zk_resq = $zhongKangServices->startBook($params);
                $arr_zk_resq = json_decode($zk_resq, true);
                if (empty($arr_zk_resq) || @$arr_zk_resq['code'] != 0) {
                    $obj_error_order = new MedicalHospitalErrorOrders();
                    $obj_error_order->addErrorOrder([
                        'app_id' => $app_id,
                        'pay_type' => $pay_type,
                        'pay_ptb' => $cb_oriprice * 10,
                        'pay_zfb' => 0,
                        'pt_order' => $out_order_no,
                        'tj_time' => $tj_time,
                        'tj_name' => $tj_name,
                        'tj_ident' => $tj_ident,
                        'error_reason' => @$arr_zk_resq['code'] . ':' . @$arr_zk_resq['msg'],
                    ]);

                    $params = [
                        'status' => 7,
                    ];
                    $hospitalTestOrders->upOrder($out_order_no, $params);
                    return $this->getInfoResponse('3001', '订单出现异常，请联系客服！');
                }

                $params = [
                    'status' => 1,
                    'zk_order_no' => $arr_zk_resq['data']['zk_order_no']
                ];
                $hospitalTestOrders->upOrder($out_order_no, $params);
                $zhongKangServices->takePtb($app_id, $cb_oriprice * 10);

                break;
            case 1:
                $user_account = $userAccount->getUserAccount($ad_user_info->uid);
                return $this->getInfoResponse('3001', '不支持我的币');
                if ($user_account->extcredits4 > $cb_oriprice * 10) {
                    return $this->getInfoResponse('3001', '我的币充足，不可使用混合支付方式！');
                }

                $remaining = $cb_oriprice - ($user_account->extcredits4 / 10);
                $params = [
                    'app_id' => $app_id,
                    'my_order' => $out_order_no,
                    'zk_order_no' => '',
                    'mobile' => $mobile,
                    'name' => $tj_name,
                    'in_time' => $tj_time,
                    'zk_unit_id' => $zk_unit_id,
                    'hospital_name' => $hospital_name,
                    'hospital_address' => $hospital_address,
                    'zk_combo_id' => $zk_combo_id,
                    'zk_combo_name' => $zk_combo_name,
                    'desc' => $cb_featrue,
                    'use_money' => $remaining,
                    'use_ptb' => $user_account->extcredits4,
                    'type' => $pay_type,
                    'status' => 0,
                    'real_sex' => $tj_gender,
                    'is_married' => $tj_married,
                    'age' => $tj_age,
                    'id_card' => $tj_ident,
                ];

                $hospitalTestOrders->addOrder($params);
                $ali_value['out_trade_no'] = $out_order_no;
                $ali_value['total_amount'] = $cb_oriprice;
                $ali_value['subject'] = '我的医疗 - 剩余支付 - ' . $cb_oriprice . '元';
                $ali_secret = Pay::alipay(config('medical.ali_config'))->app($ali_value);
                return $this->getResponse($ali_secret->getContent());
                break;
            case 2:
                $params = [
                    'app_id' => $app_id,
                    'my_order' => $out_order_no,
                    'zk_order_no' => '',
                    'mobile' => $mobile,
                    'name' => $tj_name,
                    'in_time' => $tj_time,
                    'zk_unit_id' => $zk_unit_id,
                    'hospital_name' => $hospital_name,
                    'hospital_address' => $hospital_address,
                    'zk_combo_id' => $zk_combo_id,
                    'zk_combo_name' => $zk_combo_name,
                    'desc' => $cb_featrue,
                    'use_money' => $cb_oriprice,
                    'use_ptb' => 0,
                    'type' => $pay_type,
                    'status' => 0,
                    'real_sex' => $tj_gender,
                    'is_married' => $tj_married,
                    'age' => $tj_age,
                    'id_card' => $tj_ident,
                ];

                $hospitalTestOrders->addOrder($params);

                //改为禾盟通支付
//                $ali_value['out_trade_no'] = $out_order_no;
//                $ali_value['total_amount'] = $cb_oriprice;
//                $ali_value['subject'] = '我的医疗 - ' . $cb_oriprice . '元';
//                $ali_secret = Pay::alipay(config('medical.ali_config'))->app($ali_value);
//                return $this->getResponse($ali_secret->getContent());

                $heMeToServices = new HeMeToServices();
                $data = $heMeToServices->appPayMedical($cb_oriprice, $cb_oriprice, $out_order_no);
                $res = json_decode($data, true);
                if (@$res['fcode'] != 10000) {
                    return $this->getResponse('购买失败！请联系客服');
                }
                return $this->getResponse(@$res['fcode_url']);
                break;
            case 3:
                $user_account = $userAccount->getUserAccount($ad_user_info->uid);
                return $this->getInfoResponse('3001', '不支持我的币');
                if ($user_account->extcredits4 > $cb_oriprice * 10) {
                    return $this->getInfoResponse('3001', '我的币充足，不可使用混合支付方式！');
                }

                $remaining = $cb_oriprice - ($user_account->extcredits4 / 10);
                $params = [
                    'app_id' => $app_id,
                    'my_order' => $out_order_no,
                    'zk_order_no' => '',
                    'mobile' => $mobile,
                    'name' => $tj_name,
                    'in_time' => $tj_time,
                    'zk_unit_id' => $zk_unit_id,
                    'hospital_name' => $hospital_name,
                    'hospital_address' => $hospital_address,
                    'zk_combo_id' => $zk_combo_id,
                    'zk_combo_name' => $zk_combo_name,
                    'desc' => $cb_featrue,
                    'use_money' => $remaining,
                    'use_ptb' => $user_account->extcredits4,
                    'type' => $pay_type,
                    'status' => 0,
                    'real_sex' => $tj_gender,
                    'is_married' => $tj_married,
                    'age' => $tj_age,
                    'id_card' => $tj_ident,
                ];

                $hospitalTestOrders->addOrder($params);
                $order = [
                    'out_trade_no' => $out_order_no,
                    'total_fee' => ($cb_oriprice * 100),
                    'body' => '我的医疗 - ' . $cb_oriprice . '元',
                ];

                $this->wechat_config['notify_url'] = config('medical.we_config.notify_url');

                $we_secret = Pay::wechat(config('medical.we_config'))->app($order);
                return $this->getResponse($we_secret->getContent());
                break;
            case 4:
                $params = [
                    'app_id' => $app_id,
                    'my_order' => $out_order_no,
                    'zk_order_no' => '',
                    'mobile' => $mobile,
                    'name' => $tj_name,
                    'in_time' => $tj_time,
                    'zk_unit_id' => $zk_unit_id,
                    'hospital_name' => $hospital_name,
                    'hospital_address' => $hospital_address,
                    'zk_combo_id' => $zk_combo_id,
                    'zk_combo_name' => $zk_combo_name,
                    'desc' => $cb_featrue,
                    'use_money' => $cb_oriprice,
                    'use_ptb' => 0,
                    'type' => $pay_type,
                    'status' => 0,
                    'real_sex' => $tj_gender,
                    'is_married' => $tj_married,
                    'age' => $tj_age,
                    'id_card' => $tj_ident,
                ];

                $hospitalTestOrders->addOrder($params);

                //改he盟通支付
//                $order = [
//                    'out_trade_no' => $out_order_no,
//                    'total_fee' => ($cb_oriprice * 100),
//                    'body' => '我的医疗 - ' . $cb_oriprice . '元',
//                ];
//                $this->wechat_config['notify_url'] = config('medical.we_config.notify_url');
//                $we_secret = Pay::wechat(config('medical.we_config'))->app($order);
//                return $this->getResponse($we_secret->getContent());

                $heMeToServices = new HeMeToServices();
                $data = $heMeToServices->appWxPayMedical($cb_oriprice, $out_order_no, $app_id);
                return $this->getResponse($data);
                break;
            default:
                return $this->getInfoResponse('3001', '请求错误！3001');
        }

        return $this->getResponse('申请成功');
    }

    /*
     * 支付宝回调 post 形式
     */
    /**
     * @param Request $request
     * @return string|\Symfony\Component\HttpFoundation\Response
     */
    public function aliNotify(Request $request)
    {
        try {
            $obj_ali_pay = Pay::alipay(config('medical.ali_config'));
            $obj_data = $obj_ali_pay->verify();

            $this->log('---------------start----------');
            $this->log($obj_data->toArray());
            if ($obj_data->trade_status != 'TRADE_SUCCESS' && $obj_data->trade_status != 'TRADE_FINISHED') {
                $this->log('---------------end----------');
                return 'error';
            }
            if ($obj_data->seller_id != config('medical.ali_pid')) {
                $this->log('---------------end_error----------');
                return 'error';
            }

            $order_id = $obj_data->out_trade_no;
            $actual = $obj_data->total_amount;
            $md_medical_order = new MedicalHospitalTestOrders();
            $order_info = $md_medical_order->getUnpaidByOrderId($order_id);

            if (empty($order_info)) {
                $this->log('不存或已处理该订单：' . $order_id);
                $this->log('---------------end_error----------');
                return 'error';
            }
            if ($order_info->use_money != $actual) {
                $this->log('该用户实际支付金额有误：实付' . $actual . '元');
                $this->log('---------------end_error----------');
                return 'error';
            }
            $this->log('开始判断订单类型');

            $zhongKangServices = new ZhongKangServices();


            switch ($order_info->type) {
                case 2:
                    $this->log('开始发送远程订单');
                    $params = [
                        'zk_unit_id' => $order_info->zk_unit_id,      #中康平台的机构编号
                        'zk_combo_id' => $order_info->zk_combo_id,    #中康平台的套餐编号
                        'zk_combo_name' => $order_info->zk_combo_name,#中康平台的套餐名 可空
                        'out_order_no' => $order_id,  #合作伙伴的唯一订单编号,#中康平台的套餐名 可空
                        'tj_time' => $order_info->in_time,            #用户预约的体检时间，格式如2018-07-01,数据精确到天
                        'tj_name' => $order_info->name,            #用户姓名
                        'mobile' => $order_info->mobile,              #用户手机
                        'quantity' => 1,                  #预订人数，默认1人，一人一单
                        'tj_gender' => $order_info->real_sex,        #用户性别 1-男 2-女
                        'tj_married' => $order_info->is_married,      #用户婚否 1-已婚 2-未婚
                        'tj_age' => $order_info->age,              #用户年龄
                        'tj_ident' => $order_info->id_card,          #用户身份证号
                        'promo_amount' => '',             #优惠金额 可空
                        'promo_amount_desc' => '',        #优惠金额说明 可空
                        'comment' => '',                  #用户备注信息 可空
                    ];

                    $zk_resq = $zhongKangServices->startBook($params);
                    $arr_zk_resq = json_decode($zk_resq, true);
                    if (empty($arr_zk_resq) || @$arr_zk_resq['code'] != 0) {
                        $obj_error_order = new MedicalHospitalErrorOrders();
                        $obj_error_order->addErrorOrder([
                            'app_id' => $order_info->app_id,
                            'pay_type' => $order_info->type,
                            'pay_ptb' => $order_info->use_ptb,
                            'pay_zfb' => $order_info->use_money,
                            'pt_order' => $order_id,
                            'tj_time' => $order_info->in_time,
                            'tj_name' => $order_info->name,
                            'tj_ident' => $order_info->id_card,
                            'error_reason' => @$arr_zk_resq['code'] . ':' . @$arr_zk_resq['msg'],
                        ]);
                        $this->log('远程下单订单状态异常：' . @$arr_zk_resq['code'] . ':' . @$arr_zk_resq['msg']);
                        $this->log('---------------end_error----------');
                        $params = [
                            'status' => 7,
                        ];
                        $md_medical_order->upOrder($order_id, $params);
                        return 'error';
                    }

                    $params = [
                        'status' => 1,
                        'zk_order_no' => $arr_zk_resq['data']['zk_order_no']
                    ];
                    $this->log('更新订单为成功状态');
                    $md_medical_order->upOrder($order_id, $params);
                    break;
                case 1:
                    $ad_user_info = AdUserInfo::where(['pt_id' => $order_info->app_id])->first();
                    $userAccount = new UserAccount();
                    $user_account = $userAccount->getUserAccount($ad_user_info->uid);

                    if ($user_account->extcredits4 < $order_info->use_ptb) {
                        $obj_error_order = new MedicalHospitalErrorOrders();
                        $obj_error_order->addErrorOrder([
                            'app_id' => $order_info->app_id,
                            'pay_type' => $order_info->type,
                            'pay_ptb' => $order_info->use_ptb,
                            'pay_ptb' => $order_info->use_ptb,
                            'pay_zfb' => $order_info->use_money,
                            'pt_order' => $order_id,
                            'tj_time' => $order_info->in_time,
                            'tj_name' => $order_info->name,
                            'tj_ident' => $order_info->id_card,
                            'error_reason' => '用户恶意消耗我的币，账户余额：' . $user_account->extcredits4 . '，订单需要我的币：' . $order_info->use_ptb
                        ]);
                        $this->log('用户恶意消耗我的币，账户余额：' . $user_account->extcredits4 . '，订单需要我的币：' . $order_info->use_ptb);
                        $this->log('---------------end_error----------');
                        $params = [
                            'status' => 7,
                        ];
                        $md_medical_order->upOrder($order_id, $params);
                        return 'error';
                    }
                    $params = [
                        'zk_unit_id' => $order_info->zk_unit_id,      #中康平台的机构编号
                        'zk_combo_id' => $order_info->zk_combo_id,    #中康平台的套餐编号
                        'zk_combo_name' => $order_info->zk_combo_name,#中康平台的套餐名 可空
                        'out_order_no' => $order_id,  #合作伙伴的唯一订单编号,#中康平台的套餐名 可空
                        'tj_time' => $order_info->in_time,            #用户预约的体检时间，格式如2018-07-01,数据精确到天
                        'tj_name' => $order_info->name,            #用户姓名
                        'mobile' => $order_info->mobile,              #用户手机
                        'quantity' => 1,                  #预订人数，默认1人，一人一单
                        'tj_gender' => $order_info->real_sex,        #用户性别 1-男 2-女
                        'tj_married' => $order_info->is_married,      #用户婚否 1-已婚 2-未婚
                        'tj_age' => $order_info->age,              #用户年龄
                        'tj_ident' => $order_info->id_card,          #用户身份证号
                        'promo_amount' => '',             #优惠金额 可空
                        'promo_amount_desc' => '',        #优惠金额说明 可空
                        'comment' => '',                  #用户备注信息 可空
                    ];
                    $this->log('开始发送远程订单');
                    $zk_resq = $zhongKangServices->startBook($params);
                    $arr_zk_resq = json_decode($zk_resq, true);
                    if (empty($arr_zk_resq) || @$arr_zk_resq['code'] != 0) {
                        $obj_error_order = new MedicalHospitalErrorOrders();
                        $obj_error_order->addErrorOrder([
                            'app_id' => $order_info->app_id,
                            'pay_type' => $order_info->type,
                            'pay_ptb' => $order_info->use_ptb,
                            'pay_zfb' => $order_info->use_money,
                            'pt_order' => $order_id,
                            'tj_time' => $order_info->in_time,
                            'tj_name' => $order_info->name,
                            'tj_ident' => $order_info->id_card,
                            'error_reason' => @$arr_zk_resq['code'] . ':' . @$arr_zk_resq['msg'],
                        ]);
                        $this->log('远程下单订单状态异常：' . @$arr_zk_resq['code'] . ':' . @$arr_zk_resq['msg']);
                        $this->log('---------------end_error----------');
                        $params = [
                            'status' => 7,
                        ];
                        $md_medical_order->upOrder($order_id, $params);
                        return 'error';
                    }
                    $this->log('开始更新订单状态');
                    $params = [
                        'status' => 1,
                        'zk_order_no' => $arr_zk_resq['data']['zk_order_no']
                    ];
                    $this->log('更新订单为成功状态');
                    $md_medical_order->upOrder($order_id, $params);
                    $this->log('开始扣除用户我的币');
                    $zhongKangServices->takePtb($order_info->app_id, $order_info->use_ptb);
                    break;
                default:
                    $this->log('订单类型异常：' . $order_info->type);
                    $this->log('---------------end_error----------');
                    return 'error';
            }
        } catch (\Throwable $e) {
            $this->log('出现异常情况，文件' . $e->getFile() . ',行' . $e->getLine() . ',错误信息：' . $e->getMessage());
            $this->log('---------------end_error----------');
            return 'error';
        }

        $this->log('---------------end----------');

        return $obj_ali_pay->success();
    }

    /*
     * 微信回调
     */
    public function weNotify(Request $request)
    {
        $this->weLog('---------------start----------');

        $pay = Pay::wechat(config('medical.we_config'));
        try {
            $obj_data = $pay->verify();
            if ($obj_data->return_code != "SUCCESS") {
                $this->weLog('错误信息：' . $obj_data->return_msg);
                $this->weLog('---------------end----------');
                return 'error';
            }

            $order_id = $obj_data->out_trade_no;
            $actual = $obj_data->total_fee;

            $this->weLog('开始查询订单：' . $order_id);
            $md_medical_order = new MedicalHospitalTestOrders();
            $order_info = $md_medical_order->getUnpaidByOrderId($order_id);
            if (empty($order_info)) {
                $this->weLog('不存或已处理该订单：' . $order_id);
                $this->weLog('---------------end_error----------');
                return 'error';
            }

            $this->weLog('用户支付金额：' . ($actual / 100));
            if ($order_info->use_money != ($actual / 100)) {
                $this->weLog('该用户实际支付金额有误：实付' . ($actual / 100) . '元');
                $this->weLog('---------------end_error----------');
                return 'error';
            }
            $this->weLog('开始判断订单类型');

            $zhongKangServices = new ZhongKangServices();

            switch ($order_info->type) {
                case 4:
                    $this->weLog('开始发送远程订单');
                    $params = [
                        'zk_unit_id' => $order_info->zk_unit_id,      #中康平台的机构编号
                        'zk_combo_id' => $order_info->zk_combo_id,    #中康平台的套餐编号
                        'zk_combo_name' => $order_info->zk_combo_name,#中康平台的套餐名 可空
                        'out_order_no' => $order_id,  #合作伙伴的唯一订单编号,#中康平台的套餐名 可空
                        'tj_time' => $order_info->in_time,            #用户预约的体检时间，格式如2018-07-01,数据精确到天
                        'tj_name' => $order_info->name,            #用户姓名
                        'mobile' => $order_info->mobile,              #用户手机
                        'quantity' => 1,                  #预订人数，默认1人，一人一单
                        'tj_gender' => $order_info->real_sex,        #用户性别 1-男 2-女
                        'tj_married' => $order_info->is_married,      #用户婚否 1-已婚 2-未婚
                        'tj_age' => $order_info->age,              #用户年龄
                        'tj_ident' => $order_info->id_card,          #用户身份证号
                        'promo_amount' => '',             #优惠金额 可空
                        'promo_amount_desc' => '',        #优惠金额说明 可空
                        'comment' => '',                  #用户备注信息 可空
                    ];

                    $zk_resq = $zhongKangServices->startBook($params);
                    $arr_zk_resq = json_decode($zk_resq, true);
                    if (empty($arr_zk_resq) || @$arr_zk_resq['code'] != 0) {
                        $obj_error_order = new MedicalHospitalErrorOrders();
                        $obj_error_order->addErrorOrder([
                            'app_id' => $order_info->app_id,
                            'pay_type' => $order_info->type,
                            'pay_ptb' => $order_info->use_ptb,
                            'pay_zfb' => $order_info->use_money,
                            'pt_order' => $order_id,
                            'tj_time' => $order_info->in_time,
                            'tj_name' => $order_info->name,
                            'tj_ident' => $order_info->id_card,
                            'error_reason' => @$arr_zk_resq['code'] . ':' . @$arr_zk_resq['msg'],
                        ]);
                        $this->weLog('远程下单订单状态异常：' . @$arr_zk_resq['code'] . ':' . @$arr_zk_resq['msg']);
                        $this->weLog('---------------end_error----------');
                        $params = [
                            'status' => 7,
                        ];
                        $md_medical_order->upOrder($order_id, $params);
                        return 'error';
                    }

                    $params = [
                        'status' => 1,
                        'zk_order_no' => $arr_zk_resq['data']['zk_order_no']
                    ];
                    $this->weLog('更新订单为成功状态');
                    $md_medical_order->upOrder($order_id, $params);
                    break;
                case 3:
                    $ad_user_info = AdUserInfo::where(['pt_id' => $order_info->app_id])->first();
                    $userAccount = new UserAccount();
                    $user_account = $userAccount->getUserAccount($ad_user_info->uid);

                    if ($user_account->extcredits4 < $order_info->use_ptb) {
                        $obj_error_order = new MedicalHospitalErrorOrders();
                        $obj_error_order->addErrorOrder([
                            'app_id' => $order_info->app_id,
                            'pay_type' => $order_info->type,
                            'pay_ptb' => $order_info->use_ptb,
                            'pay_zfb' => $order_info->use_money,
                            'pt_order' => $order_id,
                            'tj_time' => $order_info->in_time,
                            'tj_name' => $order_info->name,
                            'tj_ident' => $order_info->id_card,
                            'error_reason' => '用户恶意消耗我的币，账户余额：' . $user_account->extcredits4 . '，订单需要我的币：' . $order_info->use_ptb
                        ]);
                        $this->weLog('用户恶意消耗我的币，账户余额：' . $user_account->extcredits4 . '，订单需要我的币：' . $order_info->use_ptb);
                        $this->weLog('---------------end_error----------');
                        $params = [
                            'status' => 7,
                        ];
                        $md_medical_order->upOrder($order_id, $params);
                        return 'error';
                    }
                    $params = [
                        'zk_unit_id' => $order_info->zk_unit_id,      #中康平台的机构编号
                        'zk_combo_id' => $order_info->zk_combo_id,    #中康平台的套餐编号
                        'zk_combo_name' => $order_info->zk_combo_name,#中康平台的套餐名 可空
                        'out_order_no' => $order_id,  #合作伙伴的唯一订单编号,#中康平台的套餐名 可空
                        'tj_time' => $order_info->in_time,            #用户预约的体检时间，格式如2018-07-01,数据精确到天
                        'tj_name' => $order_info->name,            #用户姓名
                        'mobile' => $order_info->mobile,              #用户手机
                        'quantity' => 1,                  #预订人数，默认1人，一人一单
                        'tj_gender' => $order_info->real_sex,        #用户性别 1-男 2-女
                        'tj_married' => $order_info->is_married,      #用户婚否 1-已婚 2-未婚
                        'tj_age' => $order_info->age,              #用户年龄
                        'tj_ident' => $order_info->id_card,          #用户身份证号
                        'promo_amount' => '',             #优惠金额 可空
                        'promo_amount_desc' => '',        #优惠金额说明 可空
                        'comment' => '',                  #用户备注信息 可空
                    ];
                    $this->weLog('开始发送远程订单');
                    $zk_resq = $zhongKangServices->startBook($params);
                    $arr_zk_resq = json_decode($zk_resq, true);
                    if (empty($arr_zk_resq) || @$arr_zk_resq['code'] != 0) {
                        $obj_error_order = new MedicalHospitalErrorOrders();
                        $obj_error_order->addErrorOrder([
                            'app_id' => $order_info->app_id,
                            'pay_type' => $order_info->type,
                            'pay_ptb' => $order_info->use_ptb,
                            'pay_zfb' => $order_info->use_money,
                            'pt_order' => $order_id,
                            'tj_time' => $order_info->in_time,
                            'tj_name' => $order_info->name,
                            'tj_ident' => $order_info->id_card,
                            'error_reason' => @$arr_zk_resq['code'] . ':' . @$arr_zk_resq['msg'],
                        ]);
                        $this->weLog('远程下单订单状态异常：' . @$arr_zk_resq['code'] . ':' . @$arr_zk_resq['msg']);
                        $this->weLog('---------------end_error----------');
                        $params = [
                            'status' => 7,
                        ];
                        $md_medical_order->upOrder($order_id, $params);
                        return 'error';
                    }
                    $this->weLog('开始更新订单状态');
                    $params = [
                        'status' => 1,
                        'zk_order_no' => $arr_zk_resq['data']['zk_order_no']
                    ];
                    $this->weLog('更新订单为成功状态');
                    $md_medical_order->upOrder($order_id, $params);
                    $this->weLog('开始扣除用户我的币');
                    $zhongKangServices->takePtb($order_info->app_id, $order_info->use_ptb);
                    break;
                default:
                    $this->weLog('订单类型异常：' . $order_info->type);
                    $this->weLog('---------------end_error----------');
                    return 'error';
            }


            $this->weLog('---------------end----------');

        } catch (\Throwable $e) {
            $this->weLog('出现异常情况，文件' . $e->getFile() . ',行' . $e->getLine() . ',错误信息：' . $e->getMessage());
            $this->weLog('---------------end_error----------');
            return 'error';
        }

        return $pay->success();

    }

    /*
     * 记录日志
     */
    private function log($msg)
    {
        Storage::disk('local')->append('callback_document/medical_host_alipay_notify.txt', var_export($msg, true));
    }

    /*
     * 记录日志
     */
    private function weLog($msg)
    {
        Storage::disk('local')->append('callback_document/medical_host_wechat_notify.txt', var_export($msg, true));
    }
}
