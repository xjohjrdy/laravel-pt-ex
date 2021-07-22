<?php

namespace App\Http\Controllers\WebShop;

use App\Entitys\Ad\AdUserInfo;
use App\Entitys\App\AppUserInfo;
use App\Entitys\App\ShopGoods;
use App\Entitys\App\WechatInfo;
use App\Exceptions\ApiException;
use App\Services\Common\CommonFunction;
use App\Services\Common\NewSms;
use App\Services\Common\Sms;
use App\Services\Crypt\RsaUtils;
use App\Services\Wechat\Wechat;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Input;

class GoodsController extends Controller
{
    //
    /**
     * 第一步
     * @param $good_id
     * @param Request $request
     * @param ShopGoods $shopGoods
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getGoodsDetail($good_id, $invite_app_id, Request $request, ShopGoods $shopGoods)
    {
        $app_id = $invite_app_id;
        if (!$good_id) {
            return view('web_shop.error', ['data' => '您的商品信息不存在！']);
        }

        $shop_good = $shopGoods->getOneGood($good_id);
        if (!empty(json_decode($shop_good->header_img, true)[0])) {
            $shop_good->header_img = json_decode($shop_good->header_img, true)[0];
        }

        if (!empty(json_decode($shop_good->detail_img, true)[0])) {
            $shop_good->detail_img = json_decode($shop_good->detail_img, true)[0] . '/yasuo-123';
        }

        if (!empty(json_decode($shop_good->custom, true))) {
            $shop_good->custom = json_decode($shop_good->custom, true);
        }
        $shop_good->profit_value = number_format($shop_good->profit_value * 0.41 * 0.14, 2);
        if (!$shop_good) {
            return view('web_shop.error', ['data' => '您的商品信息已下架！']);
        }

        if (!$app_id) {
            return view('web_shop.error', ['data' => '您不存在邀请人！请使用邀请人链接！']);
        }

        return view('web_shop.good_detail', ['good' => $shop_good, 'invite_app_id' => $app_id]);
    }

    /**
     * 生成订单页面
     * @param $good_id
     * @param $invite_app_id
     * @param Request $request
     * @param CommonFunction $commonFunction
     * @param RsaUtils $rsaUtils
     * @param Client $client
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function generateOrder($good_id, $invite_app_id, Request $request, CommonFunction $commonFunction, RsaUtils $rsaUtils, Client $client)
    {
        $data = $request->all();
        //假设已经登录了
//        session(['users' => ['uid' => '1499531', 'app_id' => '1569840', 'phone' => '13194089498', 'username' => '13194089498']]);
        //假设已经登录了
        if (!$invite_app_id) {
            return view('web_shop.error', ['data' => '您不存在邀请人！请使用邀请人链接！']);
        }
        //登录校验
        if (!$request->session()->has('users')) {
            return view('web_shop.register', ['good_id' => $good_id, 'invite_app_id' => $invite_app_id]);
        }

        $app_id = $request->session()->get('users.app_id');
        $desc = '';
        foreach ($data as $k => $item) {
            $desc .= $k . '：' . $item . '，';
        }
        $push_data = '{"app_id":"' . $app_id . '","good_id":"' . $good_id . '","shop_id":"0","desc":"' . $desc . '","number":"1"}';
        $encode_data = $commonFunction->encodeForApi($push_data, '/api/orders/create', $rsaUtils);
        $res = $client->request('get', $encode_data['url'] . '?type=2', [
            'headers' => $encode_data
        ]);
        $jsonRes = (string)$res->getBody();
        $arrRes = json_decode($jsonRes, true);

        if (is_array($arrRes)) {
            if ($arrRes['code'] <> 200) {
                return view('web_shop.error', ['data' => $arrRes['msg']]);
            }
        } else {
            return view('web_shop.error', ['data' => '网络连接异常，请稍后重试']);
        }

        return view('web_shop.order', ['order_detail' => $arrRes['data'], 'good_id' => $good_id, 'invite_app_id' => $invite_app_id]);
    }

    /**
     * 兼容付款与重新填写地址以后的情况
     * @param $good_id
     * @param $invite_app_id
     * @param $order_id
     * @param Request $request
     * @param CommonFunction $commonFunction
     * @param RsaUtils $rsaUtils
     * @param Client $client
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function getOrderInfo($good_id, $invite_app_id, $order_id, Request $request, CommonFunction $commonFunction, RsaUtils $rsaUtils, Client $client)
    {
        //登录校验
        if (!$request->session()->has('users')) {
            return view('web_shop.register', ['good_id' => $good_id, 'invite_app_id' => $invite_app_id]);
        }

        $app_id = $request->session()->get('users.app_id');
        $push_data = '{"app_id":"' . $app_id . '"}';
        $encode_data = $commonFunction->encodeForApi($push_data, '/api/orders/' . $order_id, $rsaUtils);
        $res = $client->request('get', $encode_data['url'], [
            'headers' => $encode_data
        ]);
        $jsonRes = (string)$res->getBody();
        $arrRes = json_decode($jsonRes, true);

        if (is_array($arrRes)) {
            if ($arrRes['code'] <> 200) {
                return view('web_shop.error', ['data' => $arrRes['msg']]);
            }
        } else {
            return view('web_shop.error', ['data' => '网络连接异常，请稍后重试']);
        }

        return view('web_shop.order', ['order_detail' => $arrRes['data'], 'good_id' => $good_id, 'invite_app_id' => $invite_app_id]);

    }

    /**
     * 网页版不加密发送验证码
     * @param Request $request
     * @param CommonFunction $commonFunction
     * @param Sms $sms
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function sendSms(Request $request, CommonFunction $commonFunction, Sms $sms)
    {
        try {
            $arrRequest = $request->all();
            if ($request->header('data')) {
                $request->data = $request->header('data');
            }
            $arrRequest = json_decode($request->data, true) ?? $arrRequest;
            $arrRequest['area_code'] = empty($arrRequest['area_code']) ? 86 : $arrRequest['area_code'];
            if (!$arrRequest || !array_key_exists('phone', $arrRequest)) {
                throw new ApiException('传入参数错误', '3001');
            }
            if (!preg_match("/^\d{4,20}$/i", $arrRequest['phone'])) {
                return $this->getInfoResponse('4004', '不是一个正常的手机号码');
            }

            if (empty($arrRequest['no_check'])) {
                //校验数据库中是否存在该手机号
                $user_info = AppUserInfo::where(['phone' => $arrRequest['phone']])->first();
                if (!empty($user_info)) {
                    return $this->getInfoResponse('4005', '该手机号已被注册');
                }
            }
            if (Cache::has($arrRequest['phone'])) {
                return $this->getInfoResponse('3002', '验证码已发送');
            }
            //return $this->getResponse('验证码下发成功！');
            //旧短信发送备份
//            $res = $commonFunction->sendSms($arrRequest['phone'], $sms);
            //新短信发送
            $res = $commonFunction->randomKeys(5);
            $new_sms = new NewSms();
            $res_sms = $new_sms->SendSms($arrRequest['phone'], $res, $arrRequest['area_code']);
            if ($res_sms) {
                Cache::put($arrRequest['phone'], $res, 6);
                return $this->getResponse('验证码下发成功！');
            }
            return $this->getInfoResponse('1001', '验证码发送失败！');
        } catch (\Exception $e) {
            //判断是否正常抛出异常
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络出现异常，请稍后再试', '500');
        }
    }

    /**
     * 登录+注册功能
     */
    public function checkUserLogin($good_id, $invite_app_id, Request $request, AppUserInfo $appUserInfo, AdUserInfo $adUserInfo, Wechat $wechat, Client $client)
    {
        $data = $request->all();
        if (empty($data['code']) || empty($data['phone'])) {
            return view('web_shop.error', ['data' => '您没有填写手机号或者验证码']);
        } else {
            $data['phone'] = (int)$data['phone'];
            $data['code'] = (int)$data['code'];
        }
        if (!$invite_app_id) {
            return view('web_shop.error', ['data' => '您不存在邀请人！请使用邀请人链接！']);
        }
        if (Cache::has($data['phone'])) {
            $code = Cache::get($data['phone']);
            if ($code <> $data['code']) {
                return view('web_shop.error', ['data' => '手机验证码错误!']);
            }
        } else {
            return view('web_shop.error', ['data' => '手机不存在验证码!']);
        }

        $app_user = $appUserInfo->getUserByPhone($data['phone']);

        if (!$app_user) {
            $res = $wechat->doRegister($data['phone'], $client, $invite_app_id);
            if (!$res) {
                return view('web_shop.error', ['data' => '异常情况，请联系客服!']);
            }
            if ($res['code'] == 400) {
                return view('web_shop.error', ['data' => $res['message']]);
            }
            $app_user = $appUserInfo->getUserByPhone($data['phone']);
        }

        $ad_user = $adUserInfo->appToAdUserId($app_user->id);
        //设置session
        session(['users' => ['uid' => $ad_user->uid, 'app_id' => $app_user->id, 'phone' => $app_user->phone, 'username' => $ad_user->username]]);

        return redirect('/get_web_shop_detail/' . $good_id . '/' . $invite_app_id);
    }

    /**
     * 添加地址
     * @param $good_id
     * @param $invite_app_id
     * @param $order_id
     * @param Request $request
     * @param CommonFunction $commonFunction
     * @param RsaUtils $rsaUtils
     * @param Client $client
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View|string
     */
    public function addAddress($good_id, $invite_app_id, $order_id, Request $request, CommonFunction $commonFunction, RsaUtils $rsaUtils, Client $client)
    {
        $post_data = $request->all();
        if (empty($post_data)) {
            return view('web_shop.add_address', ['good_id' => $good_id, 'invite_app_id' => $invite_app_id, 'order_id' => $order_id]);
        }
        //登录校验
        if (!$request->session()->has('users')) {
            return view('web_shop.register', ['good_id' => $good_id, 'invite_app_id' => $invite_app_id]);
        }

        $app_id = $request->session()->get('users.app_id');

        $post_data['app_id'] = $app_id;
        $post_data['is_default'] = 1;

        try {//将需要发送的结果转换成json
            $push_data = json_encode($post_data);
            $encode_data = $commonFunction->encodeForApi($push_data, '/api/address/create', $rsaUtils);
            $res = $client->request('get', $encode_data['url'], [
                'headers' => $encode_data
            ]);
            $jsonRes = (string)$res->getBody();
            $arrRes = json_decode($jsonRes, true);
        } catch (\Exception $e) {
            return json_encode(['code' => 400, 'message' => '添加地址失败']);
        }

        return json_encode(['code' => $arrRes['code'], 'message' => $arrRes['msg'], 'good_id' => $good_id, 'invite_app_id' => $invite_app_id, 'order_id' => $order_id]);

    }

    /**
     * 付款
     */
    public function useMoney(Request $request)
    {
        $data = $request->all();
        dd($data);
    }
}
