<?php

namespace App\Http\Controllers\Index;

use App\Entitys\App\PtMoneyChangeLog;
use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class HasReadController extends Controller
{
    public function index(Request $request)
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
            $changeModel = new PtMoneyChangeLog();
            $log = $changeModel->getLogByAppId($app_id);
            if (empty($log)) {
//                return $this->getInfoResponse(500, '无效得app_id');
                return $this->getResponse('无记录的app_id');
            }
            if ($log['status'] == 0) {
                $changeModel->where(['app_id' => $app_id])->update([
                    'status' => 1,
                ]);
//                return $this->getInfoResponse(600, '葡萄币合并为余额，账号剩余的' . $log['pt'] . '个葡萄币已准入到余额中，详情请查【余额变更明细】！');
                return $this->getInfoResponse(600, '
                你好，为了提升用户体验，我们已将葡萄币合并到余额，您账号是' . $log['pt'] . '个葡萄币已按' . $log['money'] . '元转入到余额中；之后所有收益都将累计到余额中；
                ');
            } else {
                return $this->getResponse('用户已读');
            }


        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }

    }

    public function changeLog(Request $request)
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
            $changeModel = new PtMoneyChangeLog();
            $log = $changeModel->getLogByAppId($app_id);
            if (empty($log)) {
                return $this->getInfoResponse(500, '无效得app_id');
            }
            return $this->getResponse($log);
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }

    }
}
