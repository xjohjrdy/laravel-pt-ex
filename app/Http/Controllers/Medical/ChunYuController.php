<?php

namespace App\Http\Controllers\Medical;

use App\Entitys\Ad\AdUserInfo;
use App\Entitys\Ad\UserAccount;
use App\Entitys\App\JsonConfig;
use App\Entitys\App\MedicalHospitalErrorOrders;
use App\Entitys\App\MedicalHospitalTestOrders;
use App\Entitys\App\MedicalSpringRainChat;
use App\Entitys\App\MedicalSpringRainOrders;
use App\Exceptions\ApiException;
use App\Services\ChunYuDoctor\ChunYuDoctor;
use App\Services\ZhongKang\ZhongKangServices;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Yansongda\Pay\Pay;

class ChunYuController extends Controller
{

    /*
     * Send Msg
     */
    public function sendMsg(Request $request, ChunYuDoctor $chunYuDoctor)
    {
        $post_data = json_decode($request->data, true);
        $rules = [
            'app_id' => 'required',
            'service_id' => 'required',
            'content_list' => 'required',
        ];
        $validator = Validator::make($post_data, $rules);
        if ($validator->fails()) {
            throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
        }

        $app_id = $post_data['app_id'];
        $service_id = $post_data['service_id'];
        $content_list = $post_data['content_list'];

        $json_resq = $chunYuDoctor->addContent($app_id, $service_id, $content_list);

        $arr_resq = json_decode($json_resq, true);

        if (empty($arr_resq)) {
            return $this->getInfoResponse('4004', '网络开小差请稍后再试！4004');
        }

        if (@$arr_resq['error'] != 0) {
            return $this->getInfoResponse('1001', @$arr_resq['error_msg']);
        }

        $type_value = [
            'text' => 0,
            'audio' => 1,
            'image' => 2,
        ];
        $type_context = [
            'text' => 'text',
            'audio' => 'file',
            'image' => 'file',
        ];
        $medicalSpringRainChat = new MedicalSpringRainChat();
        foreach ($content_list as $v) {
            @$arr = [
                'problem_id' => $service_id,
                'app_id' => $app_id,
                'doctor_id' => 0,
                'context' => $v[$type_context[$v['type']]],
                'type' => $type_value[$v['type']],
                'from' => 0,
            ];
            $medicalSpringRainChat->doctorReply($arr);
        }


        return $this->getResponse('发送成功！');
    }


    public function getDialogues(Request $request, ChunYuDoctor $chunYuDoctor)
    {
        $post_data = json_decode($request->data, true);
        $rules = [
            'app_id' => 'required',
            'service_id' => 'required',
        ];
        $validator = Validator::make($post_data, $rules);
        if ($validator->fails()) {
            throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
        }

        $app_id = $post_data['app_id'];
        $service_id = $post_data['service_id'];


        $json_resq = $chunYuDoctor->detail($app_id, $service_id);

        $arr_resq = json_decode($json_resq, true);

        if (empty($arr_resq)) {
            return $this->getInfoResponse('4004', '网络开小差请稍后再试！4004');
        }

        if (@$arr_resq['error'] != 0) {
            return $this->getInfoResponse('1001', @$arr_resq['error_msg']);
        }

        foreach ($arr_resq['content'] as &$item) {
            $item['content'] = json_decode($item['content']);
        }

        return $this->getResponse($arr_resq);
    }

    /**
     * 创建问题
     * @param Request $request
     * @param ChunYuDoctor $chunYuDoctor
     * @param UserAccount $userAccount
     * @param MedicalSpringRainOrders $medicalSpringRainOrders
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function createQuestion(Request $request, ChunYuDoctor $chunYuDoctor, UserAccount $userAccount, MedicalSpringRainOrders $medicalSpringRainOrders)
    {
        $post_data = json_decode($request->data, true);
        $rules = [
            'app_id' => 'required',
            'type' => 'required',
            'price' => 'required',
            'doctor_id' => 'required',
            'content_list' => 'required',
            'clinic_name' => 'required',
            'good_at' => 'required',
            'hospital_name' => 'required',
            'image' => 'required',
            'name' => 'required',
            'title' => 'required',
            'is_famous_doctor' => 'required',

        ];
        $validator = Validator::make($post_data, $rules);
        if ($validator->fails()) {
            throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
        }
        $app_id = $post_data['app_id'];
        $pay_type = $post_data['type'];
        $price = $post_data['price'];
        $doctor_id = $post_data['doctor_id'];
        $content_list = $post_data['content_list'];
        $clinic_name = $post_data['clinic_name'];
        $good_at = $post_data['good_at'];
        $hospital_name = $post_data['hospital_name'];
        $image = $post_data['image'];
        $name = $post_data['name'];
        $title = $post_data['title'];
        $is_famous_doctor = $post_data['is_famous_doctor'];
        if (Cache::has('cy_create_question_' . $app_id)) {
            return $this->getInfoResponse('2005', '请求频率过快，请稍后再试！');
        }

        Cache::put('cy_create_question_' . $app_id, 1, 0.1);

        $out_order_no = uniqid(mt_rand(10000, 99999));

        switch ($pay_type) {
            case 0:
                $ad_user_info = AdUserInfo::where(['pt_id' => $app_id])->first();
                $user_account = $userAccount->getUserAccount($ad_user_info->uid);

                return $this->getInfoResponse('3001', '不支持我的币！');
                if ($user_account->extcredits4 < $price * 10) {
                    return $this->getInfoResponse('3001', '我的币余额不足！');
                }
                $params = [
                    'app_id' => $app_id,
                    'problem_id' => 0,
                    'doctor_id' => $doctor_id,
                    'clinic_name' => $clinic_name,
                    'good_at' => $good_at,
                    'hospital_name' => $hospital_name,
                    'image' => $image,
                    'name' => $name,
                    'title' => $title,
                    'is_famous_doctor' => $is_famous_doctor,
                    'use_money' => 0,
                    'use_ptb' => $price * 10,
                    'all_money' => $price,
                    'type' => $pay_type,
                    'status' => 0,
                    'no_in_reason' => '',
                    'response_reason' => '',
                    'context' => json_encode($content_list),
                    'my_order' => $out_order_no,
                    'first_context' => serialize($content_list),
                ];

                $medicalSpringRainOrders->addOrder($params);


                $cy_resq = $chunYuDoctor->createOrientedProblem($app_id, $doctor_id, $content_list, $price);


                $arr_cy_resq = json_decode($cy_resq, true);
                if (empty($arr_cy_resq) || @$arr_cy_resq['error'] != 0) {


                    $params = [
                        'status' => 7,
                    ];
                    $medicalSpringRainOrders->upOrder($out_order_no, $params);
                    return $this->getInfoResponse('3001', '订单出现异常，请联系客服！');
                }

                @$params = [
                    'status' => 1,
                    'problem_id' => $arr_cy_resq['problems'][0]['problem_id']
                ];
                $medicalSpringRainOrders->upOrder($out_order_no, $params);
                $chunYuDoctor->takePtb($app_id, $price * 10);

                break;
            case 2:
                $params = [
                    'app_id' => $app_id,
                    'problem_id' => 0,
                    'doctor_id' => $doctor_id,
                    'clinic_name' => $clinic_name,
                    'good_at' => $good_at,
                    'hospital_name' => $hospital_name,
                    'image' => $image,
                    'name' => $name,
                    'title' => $title,
                    'is_famous_doctor' => $is_famous_doctor,
                    'use_money' => $price,
                    'use_ptb' => 0,
                    'all_money' => $price,
                    'type' => $pay_type,
                    'status' => 0,
                    'no_in_reason' => '',
                    'response_reason' => '',
                    'context' => json_encode($content_list),
                    'my_order' => $out_order_no,
                    'first_context' => serialize($content_list),
                ];

                $medicalSpringRainOrders->addOrder($params);
                $ali_value['out_trade_no'] = $out_order_no;
                $ali_value['total_amount'] = $price;
                $ali_value['subject'] = '我的医疗问答 - ' . $price . '元';
                $ali_secret = Pay::alipay(config('medical.ali_question_config'))->app($ali_value);
                return $this->getResponse($ali_secret->getContent());
                break;
            case 4:
                $params = [
                    'app_id' => $app_id,
                    'problem_id' => 0,
                    'doctor_id' => $doctor_id,
                    'clinic_name' => $clinic_name,
                    'good_at' => $good_at,
                    'hospital_name' => $hospital_name,
                    'image' => $image,
                    'name' => $name,
                    'title' => $title,
                    'is_famous_doctor' => $is_famous_doctor,
                    'use_money' => $price,
                    'use_ptb' => 0,
                    'all_money' => $price,
                    'type' => $pay_type,
                    'status' => 0,
                    'no_in_reason' => '',
                    'response_reason' => '',
                    'context' => json_encode($content_list),
                    'my_order' => $out_order_no,
                    'first_context' => serialize($content_list),
                ];

                $medicalSpringRainOrders->addOrder($params);
                $order = [
                    'out_trade_no' => $out_order_no,
                    'total_fee' => ($price * 100),
                    'body' => '我的医疗问答 - ' . $price . '元',
                ];

                $this->wechat_config['notify_url'] = config('medical.we_question_config.notify_url');

                $we_secret = Pay::wechat(config('medical.we_question_config'))->app($order);

                return $this->getResponse($we_secret->getContent());
                break;
            default:
                return $this->getInfoResponse('3001', '请求错误！3001');
        }

        return $this->getResponse('创建成功');

    }

    public function aliNotify(Request $request)
    {
        try {
            $obj_ali_pay = Pay::alipay(config('medical.ali_question_config'));
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
            $md_medical_order = new MedicalSpringRainOrders();
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

            $chunYuDoctor = new ChunYuDoctor();
            $app_id = $order_info->app_id;
            $doctor_id = $order_info->doctor_id;
            $content_list = unserialize($order_info->first_context);
            $price = $order_info->use_money;
            $out_order_no = $order_id;

            switch ($order_info->type) {
                case 2:
                    $this->log('开始创建远程问题');
                    $cy_resq = $chunYuDoctor->createOrientedProblem($app_id, $doctor_id, $content_list, $price);

                    $arr_cy_resq = json_decode($cy_resq, true);
                    if (empty($arr_cy_resq) || @$arr_cy_resq['error'] != 0) {

                        $this->log('远程创建问题状态异常：' . @$arr_cy_resq['code'] . ':' . @$arr_cy_resq['msg']);
                        $this->log('---------------end_error----------');

                        $params = [
                            'status' => 7,
                        ];
                        $md_medical_order->upOrder($out_order_no, $params);
                        return $this->getInfoResponse('3001', '订单出现异常，请联系客服！');
                    }
                    $this->log('开始更新订单状态');
                    @$params = [
                        'status' => 1,
                        'problem_id' => $arr_cy_resq['problems'][0]['problem_id']
                    ];
                    $md_medical_order->upOrder($out_order_no, $params);
                    $this->log('更新完成');
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

            $md_medical_order = new MedicalSpringRainOrders();
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

            $chunYuDoctor = new ChunYuDoctor();
            $app_id = $order_info->app_id;
            $doctor_id = $order_info->doctor_id;
            $content_list = unserialize($order_info->first_context);
            $price = $order_info->use_money;
            $out_order_no = $order_id;

            switch ($order_info->type) {
                case 4:
                    $this->weLog('开始创建远程问题');
                    $cy_resq = $chunYuDoctor->createOrientedProblem($app_id, $doctor_id, $content_list, $price);

                    $arr_cy_resq = json_decode($cy_resq, true);
                    if (empty($arr_cy_resq) || @$arr_cy_resq['error'] != 0) {

                        $this->weLog('远程创建问题状态异常：' . @$arr_cy_resq['code'] . ':' . @$arr_cy_resq['msg']);
                        $this->weLog('---------------end_error----------');

                        $params = [
                            'status' => 7,
                        ];
                        $md_medical_order->upOrder($out_order_no, $params);
                        return $this->getInfoResponse('3001', '订单出现异常，请联系客服！');
                    }
                    $this->weLog('开始更新订单状态');
                    @$params = [
                        'status' => 1,
                        'problem_id' => $arr_cy_resq['problems'][0]['problem_id']
                    ];
                    $md_medical_order->upOrder($out_order_no, $params);
                    $this->weLog('更新完成');
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
        Storage::disk('local')->append('callback_document/medical_question_alipay_notify.txt', var_export($msg, true));
    }

    /*
     * 记录日志
     */
    private function weLog($msg)
    {
        Storage::disk('local')->append('callback_document/medical_question_wechat_notify.txt', var_export($msg, true));
    }

}
