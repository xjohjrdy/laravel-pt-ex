<?php

namespace App\Http\Controllers\Xin;

use App\Entitys\Xin\Complaint;
use App\Entitys\Xin\FrequentlyQuestion;
use App\Entitys\Xin\QuestionType;
use App\Entitys\Xin\WorkOrder;
use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class QuestionController extends Controller
{
    /*
     * 帮助中心主页数据初始化
     */
    public function init()
    {
        try {
            /***********************************/
            $obj_frequently_question = new FrequentlyQuestion();
            $obj_question_type = new QuestionType();
            $data['guess'] = $obj_frequently_question->getRandomFour();
            $data['question_type'] = $obj_question_type->getTypeList();
            if (!($data['guess'] && $data['question_type'])) {
                return $this->getInfoResponse('1001', '首页数据获取失败！');
            }
            return $this->getResponse($data);
            /***********************************/
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
     * 问题详情列
     */
    function getListByType(Request $request, FrequentlyQuestion $frequentlyQuestion)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'type' => 'required',
                'page' => 'integer',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $type = $arrRequest['type'];
            /***********************************/
            $obj_list_by_type = $frequentlyQuestion->getListByType($type);
            if (empty($obj_list_by_type->items())) {
                return $this->getInfoResponse('1001', '数据获取失败！');
            }
            return $this->getResponse($obj_list_by_type);
            /***********************************/
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
     * 问题详情页
     */
    public function details(FrequentlyQuestion $frequentlyQuestion)
    {
        $id = \request()->input('id');
        $data = $frequentlyQuestion->getFrequentlyQuestion($id);
        if (empty($data)) {
            return $this->getInfoResponse('1001', '没有找到此数据！');
        }
        return view('xin.details', [
            'data' => $data
        ]);
    }

    /*
     * 我的提问列表
     */
    public function myWorkOrderList(Request $request, WorkOrder $workOrder)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'integer',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $app_id = $arrRequest['app_id'];
            /***********************************/
            $workOrder->setReplyWorkOrderReadStatus($app_id);
            $obj_api_page_list = $workOrder->getApiPageList($app_id);
            if (!$obj_api_page_list->items()) {
                return $this->getInfoResponse('1001', '没有找到数据');
            }
            return $this->getResponse($obj_api_page_list);
            /***********************************/
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
     * 提交问题
     */
    public function submitWorkOrder(Request $request, WorkOrder $workOrder)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'integer',
                'title' => 'required',
                'content' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            /***********************************/
            $obj_work_order = $workOrder->saveWorkOrder($arrRequest);
            if (!$obj_work_order) {
                return $this->getInfoResponse('1001', '提交失败');
            }
            return $this->getResponse("提交成功");
            /***********************************/
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
     * 详情工单
     */
    public function getWorkOrderDetail(Request $request, WorkOrder $workOrder)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'work_order_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $work_order_id = $arrRequest['work_order_id'];
            /***********************************/
            $data = $workOrder->getWorkOrderDetails($work_order_id);
            if (!$data) {
                return $this->getInfoResponse('1001', '数据获取失败');
            }
            return $this->getResponse($data);
            /***********************************/
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
     * 评论客服
     */
    public function submitComplaint(Request $request, Complaint $complaint)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'wechat' => 'required',
                'image' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            /***********************************/
            $arrRequest['phone'] = empty($arrRequest['phone'])?"":$arrRequest['phone'];
            if ($arrRequest['phone']){
                $pattern_account = '/^\d{4,20}$/i';
                if (!preg_match($pattern_account, $arrRequest['phone'])) {
                    return $this->getInfoResponse('1001', '您的手机号输入错误！');
                }
            }
            $res = $complaint->create($arrRequest);
            if (empty($res)){
                return $this->getInfoResponse('1002', '提交失败！');
            }
            return $this->getResponse("提交成功");
            /***********************************/
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }
}
