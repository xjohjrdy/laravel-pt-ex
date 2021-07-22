<?php

namespace App\Http\Controllers\Xin;

use App\Entitys\Xin\Config;
use App\Exceptions\ApiException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class IosController extends Controller
{
    /*
     * 隐藏二维码
     */
    public function checkHideCodeVersion(Request $request, Config $config)
    {
        try {
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'version' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
            $version = $arrRequest['version'];
            /***********************************/
            $str_ios_hide_code_version = $config->getHideConfigValue('ios_hide_code_version');
            if (!($version==$str_ios_hide_code_version)) {
                return $this->getInfoResponse('1001', '版本号不同');
            }
            return $this->getResponse("版本号相同");
            /***********************************/
        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }
}
