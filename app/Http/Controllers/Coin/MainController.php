<?php

namespace App\Http\Controllers\Coin;

use App\Entitys\App\CoinChangeLog;
use App\Exceptions\ApiException;
use App\Services\CoinPlate\CoinCommonService;
use App\Services\CoinPlate\CoinConst;
use App\Services\CoinPlate\MainService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class MainController extends Controller
{
    public function mainInfo(Request $request)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $app_id = $arrRequest['app_id'];
            $time = time();
            $mainService = new MainService($app_id, $time);
            return $this->getResponse($mainService->getCoinMainInfo());
        } catch (\Exception $e) {
            if (!empty($e->getMessage())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('服务器异常', '500');
        }
    }

    public function successTask(Request $request)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
                'task_id' => 'required'
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $app_id = $arrRequest['app_id'];
            $task_id = $arrRequest['task_id'];
            $time = time();

            if (!config('test_appid.debug') || in_array($app_id, config('test_appid.app_ids'))) {
                try {
                    $commonService = new CoinCommonService($app_id);
                    $task_coin = $commonService->successTask($task_id, $time);
                    return $this->getResponse($task_coin);//正常返回数据
                } catch (\Exception $e) {

                }
            }
            return $this->getInfoResponse('1001', '无效的任务或任务已被完成!');//错误返回数据
        } catch (\Exception $e) {
            if (!empty($e->getMessage())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('服务器异常', '500');
        }
    }

    /**
     * 获取金币明细
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function getCoinChangeHistory(Request $request)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }//仅用于测试兼容旧版-----------------线上可删除
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
//                'type' => Rule::in([1, 2])
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $app_id = $arrRequest['app_id'];
//            $type = $arrRequest['type'];
            $taskModel = new CoinChangeLog();
//            $operate_code = '<';
//            if($type == 2){
//                $operate_code = '>';
//            }
            // ->where('change_coin', $operate_code, 0)
            $list = $taskModel->where(['app_id' => $app_id])->orderByDesc('id')->paginate(20, ['remark', 'change_coin', 'created_at', DB::raw("DATE_FORMAT(created_at,'%Y-%m') as ym")]);
            $list = $list->toArray();
            $arr = [];
            $index = 0;
            $cur_month = '';
            foreach ($list['data'] as $key => $item) {
                $item['change_coin'] = $item['change_coin'] > 0 ? '+' . $item['change_coin'] : $item['change_coin'];
                if ($cur_month != $item['ym']) {
                    $cur_month = $item['ym'];
                    $arr[$index]['month'] = $item['ym'];
                    $arr[$index]['data'][] = $item;
                    $index += 1;
                } else {
                    $arr[$index - 1]['data'][] = $item;
                }
            }

            $list['data'] = $arr;
            return $this->getResponse($list);
        } catch (\Exception $e) {
            if (!empty($e->getMessage())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('服务器异常', '500');
        }
    }
}
