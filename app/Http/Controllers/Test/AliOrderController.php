<?php

namespace App\Http\Controllers\Test;

use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AliOrderController extends Controller
{

    /*
     * 记录异常信息
     */
    public function record(Request $request)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);

            $rules = [
                'err_info' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);

            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $err_ali_pay_base = $arrRequest['err_info'];
            $err_ali_pay = base64_decode($err_ali_pay_base);
            $err_ali_pay = empty($err_ali_pay) ? 'base:' . $err_ali_pay_base : $err_ali_pay;
            Storage::disk('local')->append('callback_document/ali_pay_error_oOo0oO0OoO0Oo.txt', var_export($err_ali_pay, true));

            return $this->getResponse('Ok');

        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }
}
