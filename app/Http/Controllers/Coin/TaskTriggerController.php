<?php

namespace App\Http\Controllers\Coin;

use App\Exceptions\ApiException;
use App\Services\CoinPlate\CoinCommonService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class TaskTriggerController extends Controller
{
    /*
     * 浏览商品完成任务
     */
    public function TaskBrowseGood(Request $request)
    {
        try {//仅用于测试兼容旧版-start
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'app_id' => 'integer',  //必须整数
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            //取用户app_id
            $app_id = $arrRequest['app_id'];
            /***********************************/
            //开始处理逻辑问题
            //浏览商品1分钟完成任务
            if (!config('test_appid.debug') || in_array($app_id, config('test_appid.app_ids'))) {
                try {
                    $coinCommonService = new CoinCommonService($app_id);
                    $task_id = 7;#浏览商品
                    $task_time = time();
                    $task_coin = $coinCommonService->successTask($task_id, $task_time);
                    return $this->getResponse($task_coin);//正常返回数据
                } catch (\Exception $e) {

                }
            }
            return $this->getInfoResponse('1001', '无效的任务或任务已被完成!');//错误返回数据
            /***********************************/
//            return $this->getInfoResponse('3001', '错误返回数据!');//错误返回数据
//            return $this->getResponse('正常返回数据!');//正常返回数据
        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
     * 观看福利视频触发任务
     */
    public function TaskWatchVideo(Request $request)
    {
        try {//仅用于测试兼容旧版-start
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'app_id' => 'integer',  //必须整数
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            //取用户app_id
            $app_id = $arrRequest['app_id'];
            /***********************************/
            //开始处理逻辑问题
            //观看福利视频完成任务
            if (!config('test_appid.debug') || in_array($app_id, config('test_appid.app_ids'))) {
                try {
                    $coinCommonService = new CoinCommonService($app_id);
                    $task_id = 8;#看福利视频赚金币
                    $task_time = time();
                    $task_coin = $coinCommonService->successTask($task_id, $task_time);
                    return $this->getResponse($task_coin);//正常返回数据
                } catch (\Exception $e) {

                }
            }
            return $this->getInfoResponse('1001', '无效的任务或任务已被完成!');//错误返回数据
            /***********************************/
//            return $this->getInfoResponse('3001', '错误返回数据!');//错误返回数据
//            return $this->getResponse('正常返回数据!');//正常返回数据
        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
     * 观看新手攻略触发任务
     */
    public function TaskNewWatchStrategy(Request $request)
    {
        try {//仅用于测试兼容旧版-start
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'app_id' => 'integer',  //必须整数
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            //取用户app_id
            $app_id = $arrRequest['app_id'];
            /***********************************/
            //开始处理逻辑问题
            //观看新手攻略完成任务
            if (!config('test_appid.debug') || in_array($app_id, config('test_appid.app_ids'))) {
                try {
                    $coinCommonService = new CoinCommonService($app_id);
                    $task_id = 1;#观看新手攻略
                    $task_time = time();
                    $task_coin = $coinCommonService->successTask($task_id, $task_time);
                    return $this->getResponse($task_coin);//正常返回数据
                } catch (\Exception $e) {

                }
            }
            return $this->getInfoResponse('1001', '无效的任务或任务已被完成!');//错误返回数据
            /***********************************/
//            return $this->getInfoResponse('3001', '错误返回数据!');//错误返回数据
//            return $this->getResponse('正常返回数据!');//正常返回数据
        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /*
     * 分享发圈触发任务
     */
    public function TaskNewShareHairRing(Request $request)
    {
        try {//仅用于测试兼容旧版-start
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'app_id' => 'integer',  //必须整数
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            //取用户app_id
            $app_id = $arrRequest['app_id'];
            /***********************************/
            //开始处理逻辑问题
            //分享发圈完成任务
            if (!config('test_appid.debug') || in_array($app_id, config('test_appid.app_ids'))) {
                try {
                    $coinCommonService = new CoinCommonService($app_id);
                    $task_id = 5;#首次分享发圈
                    $task_time = time();
                    $task_coin = $coinCommonService->successTask($task_id, $task_time);
                    return $this->getResponse($task_coin);//正常返回数据
                } catch (\Exception $e) {

                }
            }
            return $this->getInfoResponse('1001', '无效的任务或任务已被完成!');//错误返回数据
            /***********************************/
//            return $this->getInfoResponse('3001', '错误返回数据!');//错误返回数据
//            return $this->getResponse('正常返回数据!');//正常返回数据
        } catch (\Throwable $e) {
            //判断是否正常抛出异常
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }
}
