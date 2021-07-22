<?php

namespace App\Http\Controllers\Morning;

use App\Entitys\App\MorningSchemes;
use App\Entitys\App\MorningUser;
use App\Entitys\App\MorningUserRecords;
use App\Entitys\App\TaobaoUser;
use App\Exceptions\ApiException;
use App\Services\CoinPlate\CoinCommonService;
use App\Services\Common\UserMoney;
use App\Services\Morning\MorningService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class IndexController extends Controller
{
    /**
     * 早餐打卡首页拉取总数据接口
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function indexData(Request $request)
    {
        try {
            $time = time();
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
            ];
            $moneyService = new MorningService($arrRequest['app_id'], $time);


            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            #TODO 下面为接口获取值
            $res = [
                'ranking_list' => $moneyService->getRankList(), // 排行榜
            ];
            $res = array_merge($res, $moneyService->getMidBtnStatus());
            $res = array_merge($res, $moneyService->getMoneyAndUserCount());
            return $this->getResponse($res);
        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            if (!empty($e->getMessage())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * 早餐打卡方案列表
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function schemesList(Request $request)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
            ];
            $app_id = $arrRequest['app_id'];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $taobaoUserModel = new TaobaoUser();
            $user = $taobaoUserModel->where(['app_id' => $app_id])->first();
            if (empty($user)) {
                return $this->getInfoResponse(1001, '用户不存在！');
            }
            $schemesModel = new MorningSchemes();
            $list = $schemesModel->where(['status' => 1])->get(['id', 'title', 'money']);

            return $this->getResponse([
                'lift_money' => $user['money'],
                'list' => $list
            ]);
        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            if (!empty($e->getMessage())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }


    /**
     * 用户报名接口
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function apply(Request $request)
    {
        try {
            $time = time();
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
                'schemes_id' => 'required',
                'ip' => 'required',
                'device' => 'required',
            ];
            $app_id = $arrRequest['app_id'];
            $schemes_id = $arrRequest['schemes_id'];
            $ip = $arrRequest['ip'];
            $device = $arrRequest['device'];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $taobaoUserModel = new TaobaoUser();
            $schemesModel = new MorningSchemes();
            $schemes = $schemesModel->where(['status' => 1, 'id' => $schemes_id])->first();
            if (empty($schemes)) {
                return $this->getInfoResponse(1002, '您报名的方案不存在，请重新报名参与！');
            }
            $money = $taobaoUserModel->getUserMoney($app_id);
            if ($money < $schemes['money']) {
                return $this->getInfoResponse(1003, '您的余额不足！');
            }
            DB::connection('app38')->beginTransaction();
            $userMoneyService = new UserMoney(); //
            $userMoneyService->minusCnyAndLogNoTrans($app_id, $schemes['money'], '20003', '早安打卡,' . $schemes_id);
            $morningService = new MorningService($app_id, $time);
            $morningService->userApply($schemes_id, $schemes['money'], $ip, $device);
            DB::connection('app38')->commit();

            //早安打卡完成任务
            if (!config('test_appid.debug') || in_array($app_id, config('test_appid.app_ids'))) {
                try {
                    $coinCommonService = new CoinCommonService($app_id);
                    $task_id = 11;#首次早安打卡
                    $task_time = time();
                    $coinCommonService->successTask($task_id, $task_time);
                } catch (\Exception $e) {

                }
            }

            return $this->getResponse('报名成功');
        } catch (\Throwable $e) {
            DB::connection('app38')->rollBack();
            //判断是否正常抛出异常
            if (!empty($e->getCode())) {
                return $this->getInfoResponse($e->getCode(), $e->getMessage());
//                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }


    /**
     * 用户打卡接口
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sign(Request $request)
    {
        try {
            $time = time();
            DB::connection('app38')->beginTransaction();
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
            ];
            $app_id = $arrRequest['app_id'];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $taobaoUserModel = new TaobaoUser();
            $user = $taobaoUserModel->where(['app_id' => $app_id])->first();
            if (empty($user)) {
                return $this->getInfoResponse(1001, '用户不存在！');
            }
            $morningService = new MorningService($app_id, $time);
            $morningService->userSign();
            DB::connection('app38')->commit();
            return $this->getResponse('打卡成功');
        } catch (\Throwable $e) {
            DB::connection('app38')->rollBack();
            //判断是否正常抛出异常
            if (!empty($e->getCode())) {
                return $this->getInfoResponse($e->getCode(), $e->getMessage());
//                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * 用户历史参与记录
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function records(Request $request)
    {
        try {
            $time = time();
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
            ];
            $app_id = $arrRequest['app_id'];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $morningService = new MorningService($app_id, $time);
            $res = $morningService->userRecords();
            return $this->getResponse($res);
        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            if (!empty($e->getMessage())) {
                return $this->getInfoResponse($e->getCode(), $e->getMessage());
//                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * 用户总参与金额信息
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function userMainInfo(Request $request)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
            ];
            $app_id = $arrRequest['app_id'];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $morningUserModel = new MorningUser();
            $res = $morningUserModel->where(['app_id' => $app_id])->first(['apply_days', 'apply_total_money', 'success_days', 'success_total_money', 'continuous_days']);
            if (empty($res)) {
                $res = [
                    'app_id' => $app_id,
                    'apply_days' => 0,
                    'apply_total_money' => 0,
                    'success_days' => 0,
                    'success_total_money' => 0,
                    'continuous_days' => 0
                ];
                $morningUserModel->create($res);
                $res = $morningUserModel->where(['app_id' => $app_id])->first(['apply_days', 'apply_total_money', 'success_days', 'success_total_money', 'continuous_days']);
            }
            return $this->getResponse($res);
        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            if (!empty($e->getCode())) {
                return $this->getInfoResponse($e->getCode(), $e->getMessage());
//                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }
}
