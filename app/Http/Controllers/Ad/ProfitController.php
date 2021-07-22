<?php

namespace App\Http\Controllers\Ad;

use App\Entitys\Ad\UserCreditLog;
use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

class ProfitController extends Controller
{
    /**
     *
     * 收益详情-其他记录（积分变更记录）
     * {"uid":"1569840","limit":"20"}
     * @param Request $request
     * @param UserCreditLog $userCreditLog
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function index(Request $request, UserCreditLog $userCreditLog)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            if (!$arrRequest || !array_key_exists('uid', $arrRequest)) {
                throw new ApiException('传入参数错误', '3001');
            }
            if (!array_key_exists('limit', $arrRequest)) {
                $limit = 20;
            } else {
                $limit = $arrRequest['limit'];
            }

            $redis_key = 'profit_' . $arrRequest['uid'] . '_' . $request->get('page');
            if (Cache::has($redis_key)) {
                return $this->getResponse(Cache::get($redis_key));
            }

            $log = $userCreditLog->getAllCreditLog($arrRequest['uid'], $limit);
            $log->map(function ($model) {
                $model->dispaly_title = $model->change['logs_credit_update_' . $model->operation];
                if ($model->extcredits3 <> 0) {
                    $model->dispaly_info = "充值金额：" . $model->extcredits3;
                    if ($model->extcredits3 > 0) {
                        $model->dispaly_info = "充值金额：+" . $model->extcredits3;
                    }
                }
                if ($model->extcredits4 <> 0) {
                    $model->dispaly_info = "葡萄币：" . $model->extcredits4;
                    if ($model->extcredits4 > 0) {
                        $model->dispaly_info = "葡萄币：+" . $model->extcredits4;
                    }
                }
                if ($model->extcredits5 <> 0) {
                    $model->dispaly_info = "GRA：" . $model->extcredits5;
                    if ($model->extcredits5 > 0) {
                        $model->dispaly_info = "GRA：+" . $model->extcredits5;
                    }
                }
                if ($model->extcredits3 == 0 && $model->extcredits4 == 0 && $model->extcredits5 == 0) {
                    $model->dispaly_info = "免费";
                }
            });
            Cache::put($redis_key, $log, 10);

            return $this->getResponse($log);

        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络连接错误，错误信息：' . $e->getLine() . '/' . $e->getMessage(), '500');
        }

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
    }
}
