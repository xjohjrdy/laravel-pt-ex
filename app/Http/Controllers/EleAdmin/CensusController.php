<?php

namespace App\Http\Controllers\EleAdmin;

use App\Entitys\App\TodayMoneyChangeNew;
use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class CensusController extends Controller
{
    //
    /**
     *
     */
    public function getList(Request $request, TodayMoneyChangeNew $todayMoneyChangeNew)
    {
        try {
            $arrRequest = $request->input();
            $rules = [
                'today_time' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            //接收当日0点的时间戳
            $data_maid = $todayMoneyChangeNew->getByTime($arrRequest['today_time']);


            return $this->getResponse([
                'today' => $data_maid,
                'work_done_by_hand' => null//预留记录，可以展示手动变更
            ]);
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }
}
