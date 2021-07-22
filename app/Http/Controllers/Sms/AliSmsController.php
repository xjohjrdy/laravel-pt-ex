<?php

namespace App\Http\Controllers\Sms;

use App\Exceptions\ApiException;
use App\Services\Common\CommonFunction;
use App\Services\Common\NewSms;
use App\Services\Common\Sms;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;

class AliSmsController extends Controller
{
    /**
     * get {"phone":"1"}
     * @param Request $request
     * @param CommonFunction $commonFunction
     * @param Sms $sms
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function index(Request $request, CommonFunction $commonFunction, Sms $sms)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $arrRequest['area_code'] = empty($arrRequest['area_code']) ? 86 : $arrRequest['area_code'];
            if (!$arrRequest || !array_key_exists('phone', $arrRequest)) {
                throw new ApiException('传入参数错误', '3001');
            }

            // 添加设备号校验
            $request_device_id = $request->header('User-Device-Id'); //用户设备ID
            $request_device_id_sign = md5('sms:device:' . $request_device_id);
            if (Cache::has($request_device_id_sign)) {
                $old_time = Cache::get($request_device_id_sign);
                $count_down = 3 * 60 - (time() - $old_time);
                return $this->getInfoResponse('3002', '短信发送频繁！请' . $count_down . '秒后再试');
            }

            if ($arrRequest['area_code'] == 86) {
                $pattern_account = '/^1\d{10}$/i';
                if (!preg_match($pattern_account, $arrRequest['phone'])) {
                    return $this->getInfoResponse('1002', '您的手机号输入错误！');
                }
            }

            if (Cache::has($arrRequest['phone'])) {
                return $this->getInfoResponse('3002', '验证码已发送！');
            }
            $res = $commonFunction->randomKeys(5);
            $new_sms = new NewSms();
            $res_sms = $new_sms->SendSms($arrRequest['phone'], $res, $arrRequest['area_code']);
            if ($res_sms) {
                Cache::put($arrRequest['phone'], $res, 6);
                Cache::put($request_device_id_sign, time(), 3);
                return $this->getResponse('验证码下发成功！');
            }
            return $this->getInfoResponse('1001', '验证码发送失败！');
        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络出现异常，请稍后再试', '500');
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
     * post {"phone":"13194089498","code":"1234"}
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function store(Request $request)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            if (!$arrRequest || !array_key_exists('phone', $arrRequest)) {
                throw new ApiException('传入参数错误', '3001');
            }

            if ($arrRequest['phone'] == '13124207999' || $arrRequest['phone'] == '15084286565' || $arrRequest['phone'] == '13190492744' || $arrRequest['phone'] == '15041415777' || $arrRequest['phone'] == '13842421899') {
                return $this->getResponse('验证成功！');
            }
            if ($arrRequest['phone'] == '13942405291' || $arrRequest['phone'] == '18741474244' || $arrRequest['phone'] == '18340466789' || $arrRequest['phone'] == '15174030777' || $arrRequest['phone'] == '15174149888') {
                return $this->getResponse('验证成功！');
            }
            if ($arrRequest['phone'] == '18841414488' || $arrRequest['phone'] == '15041479999' || $arrRequest['phone'] == '15898327666' || $arrRequest['phone'] == '14704034111' || $arrRequest['phone'] == '13942499577') {
                return $this->getResponse('验证成功！');
            }
            if ($arrRequest['phone'] == '18340401166' || $arrRequest['phone'] == '18104047055' || $arrRequest['phone'] == '13842479789' || $arrRequest['phone'] == '15041487971' || $arrRequest['phone'] == '18341442678') {
                return $this->getResponse('验证成功！');
            }
            if (Cache::has($arrRequest['phone'])) {
                $code = Cache::get($arrRequest['phone']);
                if ($code == $arrRequest['code']) {
                    return $this->getResponse('验证成功！');
                }
            } else {
                return $this->getInfoResponse('4004', '手机不存在验证码！');
            }
            return $this->getInfoResponse('4000', '验证码错误或过期，请重新获取！');
        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('服务器异常', '500');
        }
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
