<?php

namespace App\Http\Controllers\Index;

use App\Entitys\App\NewAppVersion;
use App\Exceptions\ApiException;
use App\Services\Verify\Captcha;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class VerifyController extends Controller
{
    /**
     * 验证码发起校验行为
     */
    public function put(Request $request, Captcha $captcha)
    {
        $res = $captcha->getJsUrl($request->ip());

        return $this->getResponse($res);
    }

    /**
     * 加密验证码校验行为
     * @param Request $request
     * @param Captcha $captcha
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function check(Request $request, Captcha $captcha)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'ticket' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $res = $captcha->check($arrRequest['ticket'], $request->ip());
            if ($res['code'] == 0) {
                return $this->getResponse('请求成功！校验通过');
            }

            return $this->getInfoResponse($res['code'], $res['message']);

        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * 版本校验
     */
    public function version(Request $request, NewAppVersion $newAppVersion)
    {

        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'tag' => 'required',
                'version' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }
//            if (Cache::has('verify_version_' . $request->ip())) {
//                return $this->getInfoResponse('4004', '无更新版本！');
//            }
//            Cache::put('verify_version_' . $request->ip(), 1, 0.5);

            $app_version = $newAppVersion->getAppVersion($arrRequest['tag']);

            if ($arrRequest['version'] <> $app_version->version) {
                $app_version->info = unserialize($app_version->info);
                if ($arrRequest['tag'] == 1) {
                    $app_version->download_url = 'http://a.app.qq.com/o/simple.jsp?pkgname=com.qwh.grapebrowser';
                } else {
                    $app_version->download_url = 'https://itunes.apple.com/cn/app/%E8%91%A1%E8%90%84%E6%B5%8F%E8%A7%88%E5%99%A8/id1158782306?mt=8';
                }
                return $this->getResponse($app_version);
            }

            return $this->getInfoResponse('4004', '无更新版本！');

        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }
}
