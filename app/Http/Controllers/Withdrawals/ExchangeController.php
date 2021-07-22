<?php

namespace App\Http\Controllers\Withdrawals;

use App\Entitys\Ad\AdUserInfo;
use App\Entitys\Ad\ExchangeGrapeCard;
use App\Entitys\Ad\ExchangeGrapeOrder;
use App\Entitys\App\AppUserInfo;
use App\Entitys\App\WechatInfo;
use App\Exceptions\ApiException;
use App\Services\Advertising\ExchangeGrape;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ExchangeController extends Controller
{
    /**
     * 提现首页
     * @param Request $request
     * @param ExchangeGrape $exchangeGrape
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function index(Request $request, ExchangeGrape $exchangeGrape)
    {
        try {
            $jsonParams = $request->data;
            $arrParams = json_decode($jsonParams, true);
            $rules = [
                'uid' => 'required',
            ];
            $validator = Validator::make($arrParams, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数', 3002);
            }
            $uid = $arrParams['uid'];
            $userPayInfo = $exchangeGrape->getUserPayInfo($uid);
        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }

            throw new ApiException('拉取用户信息失败', 500);
        }

        return $this->getResponse($userPayInfo);
    }

    /**
     * 添加绑定新的支付宝账号
     * @param Request $request
     * @param ExchangeGrape $exchangeGrape
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function create(Request $request, ExchangeGrape $exchangeGrape)
    {
        try {
            $jsonParams = $request->data;
            $arrParams = json_decode($jsonParams, true);
            $rules = [
                'uid' => 'required',
                'name' => 'required',
                'account' => 'required',
            ];
            $validator = Validator::make($arrParams, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数', 3002);
            }
            $uid = $arrParams['uid'];
            $name = $arrParams['name'];

            $pattern = '/^[\x{4e00}-\x{9fa5}·]+$/u';
            if (!preg_match($pattern, $name)) {
                return $this->getInfoResponse('3003', '您的名字请不要输入特殊符号！');
            }
            $account = $arrParams['account'];
            $pattern_account = '/^(\d{2,3}-\d{8,9}|\d{11}|\w[-\w.+]*@([A-Za-z0-9][-A-Za-z0-9]+\.)+[A-Za-z]{2,14})$/u';
            if (!preg_match($pattern_account, $account)) {
                return $this->getInfoResponse('3004', '您的提现支付宝账号有误！');
            }
            $isBoundPay = $exchangeGrape->isBoundPay($name, $account);
            if (!empty($isBoundPay)) {
                return $this->getInfoResponse('3003', '该支付宝账号已经被绑定过！！！！');
            }
            $exchangeGrape->setAliPayAccount($uid, $name, $account);


            return $this->getResponse('添加支付宝账号成功');
        } catch (\Exception $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }

            throw new ApiException('添加支付宝账号失败', 500);
        }

    }

    /**
     * 提交提现订单
     * @param Request $request
     * @param ExchangeGrape $exchangeGrape
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function store(Request $request, ExchangeGrape $exchangeGrape, AdUserInfo $adUserInfo, WechatInfo $wechatInfo)
    {
        //我的币即将合并到余额中，升级中暂时关闭。

        return $this->getInfoResponse('4121', '我的币即将合并到余额中，升级中暂时关闭。');
        try {
            DB::beginTransaction();
            $jsonParams = $request->data;
            $arrParams = json_decode($jsonParams, true);
            $rules = [
                'uid' => 'required',
                'ptb' => 'required',
            ];
            $validator = Validator::make($arrParams, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数', 3002);
            }
            $uid = $arrParams['uid'];
            $ptb = $arrParams['ptb'];

            if (!empty($arrParams['from_to'])) {
                if ($arrParams['from_to'] == 1) {
                    return $this->getInfoResponse('5001', '暂不支持支付宝提现！');
                }
            }

            if (Cache::has('exchange_' . $uid)) {
                return $this->getInfoResponse('2005', '请等待上一笔提现订单完成...');
            }
            Cache::put('exchange_' . $uid, 1, 0.5);

            $ad_user_info = $adUserInfo->getUserById($uid);
            if (empty($ad_user_info)) {
                return $this->getInfoResponse('2005', '不存在广告联盟用户...');
            }

            $wechat_info_user = $wechatInfo->getAppId($ad_user_info->pt_id);
            if (empty($wechat_info_user)) {
                return $this->getInfoResponse('2005', '即日起提现需要绑定微信，以微信入账的形式支付，请前往绑定微信');
            }

            $rmb = round(($ptb * 0.99) / 10, 2);
            if ($ptb < 100) {
                return $this->getInfoResponse('2001', '提现我的币最少100！！！！');
            }
            if ($ptb > 50000) {
                return $this->getInfoResponse('2001', '提现我的币最大50000！！！！');
            }
            $userPTB = $exchangeGrape->getUserPTB($uid);
            if (empty($userPTB)) {
                return $this->getInfoResponse('2002', '钱包账号不存在！！！！');
            } elseif ($ptb > $userPTB) {
                return $this->getInfoResponse('2003', '提现金额超过账号余额！！！！');
            }
            if (!empty($arrParams['from_to'])) {
                if ($arrParams['from_to'] == 2) {
                    $aliPay_wechat = $exchangeGrape->getAliCard($uid);
                    if (empty($aliPay_wechat)) {
                        $AppUserInfo = new AppUserInfo();
                        $user_all = $AppUserInfo->getUserInfo($ad_user_info->pt_id);
                        if (empty($user_all->real_name)) {
                            return $this->getInfoResponse('3004', '真实姓名需要填写！');
                        }
                        if (empty($user_all->alipay)) {
                            return $this->getInfoResponse('3004', '账号与真实姓名需要填写！');
                        }
                        $ExchangeGraCard = new ExchangeGrapeCard();
                        if ($ExchangeGraCard->where(['account' => $user_all->alipay, 'account_name' => $user_all->real_name])->exists()) {
                            return $this->getInfoResponse('3004', '该真实姓名已经被其他账号绑定，请更换！');
                        }
                        $exchangeGrape->setAliPayAccount($uid, $user_all->real_name, $user_all->alipay);
                    } else {
                        $AppUserInfo = new AppUserInfo();
                        $user_all = $AppUserInfo->getUserInfo($ad_user_info->pt_id);
                        if (empty($user_all->real_name)) {
                            return $this->getInfoResponse('3004', '真实姓名需要填写！');
                        }
                        if (empty($user_all->alipay)) {
                            return $this->getInfoResponse('3004', '账号与真实姓名需要填写！');
                        }
                        if ($aliPay_wechat['account_name'] <> $user_all->real_name) {
                            $ExchangeGraCard = new ExchangeGrapeCard();
                            if ($ExchangeGraCard->where(['account' => $user_all->alipay, 'account_name' => $user_all->real_name])->exists()) {
                                return $this->getInfoResponse('3004', '该真实姓名已经被其他账号绑定，请更换！');
                            }
                            $exchangeGrape->setAliPayAccount($uid, $user_all->real_name, $user_all->alipay);
                        }
                    }
                }
            }
            $aliPay = $exchangeGrape->getAliCard($uid);
            $account = @$aliPay['account'];
            $pattern_account = '/^(\d{2,3}-\d{8,9}|\d{11}|\w[-\w.+]*@([A-Za-z0-9][-A-Za-z0-9]+\.)+[A-Za-z]{2,14})$/u';
            if (!preg_match($pattern_account, $account)) {
                return $this->getInfoResponse('3004', '您的提现支付宝账号有误！');
            }
            if (empty($aliPay)) {
                return $this->getInfoResponse('2004', '未设置默认支付宝账号！！！！');
            }
            if (ExchangeGrapeOrder::where(['uid' => $uid, 'status' => 0])->exists()) {
                return $this->getInfoResponse('2005', '请等待上一笔提现订单完成');
            }
            /*************= 开始执行扣款加币操作 =*************/
            $exchangeGrape->deductPTB($uid, $userPTB, $ptb);
            $tixianid = $exchangeGrape->addAliPayOrder($aliPay, $ptb, $rmb);
            /********/
            /********/

            DB::commit();


            $m_msg = '用户：' . $uid;
            $m_msg .= '，提现金额：' . $ptb;
            $m_msg .= '，用户ip：' . $request->ip();


            return $this->getResponse("您提交的我的币提现请求已提交，请等待审核！");
        } catch (\Exception  $e) {
            DB::rollBack();
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }

            throw new ApiException('提交申请失败', 500);
        }


    }

    /**
     * 查询订单
     * @param $uid
     * @param ExchangeGrape $exchangeGrape
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($uid, ExchangeGrape $exchangeGrape)
    {
        $listOrder = $exchangeGrape->getExchangeOrder($uid);
        if (empty($listOrder)) {
            return $this->getInfoResponse('2001', '暂无提现记录！');
        }

        return $this->getResponse($listOrder);
    }

    /**
     * 查询
     */
    public function getShow(Request $request, AdUserInfo $adUserInfo, ExchangeGrapeOrder $exchangeGrapeOrder)
    {
        try {
            $arrRequest = json_decode($request->data, true);
            $rules = [
                'app_id' => 'required',
            ];
            $validator = Validator::make($arrRequest, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数,错误信息：' . $validator->errors(), 3002);
            }

            $user = $adUserInfo->appToAdUserId($arrRequest['app_id']);
            if (empty($user)) {
                return $this->getInfoResponse('3002', '联盟账户不存在，请确认您的版本是否是最新版！');
            }
            $uid = $user->uid;

            $x = $exchangeGrapeOrder->getList($uid);
            $c = $exchangeGrapeOrder->getCount($uid);

            return $this->getResponse([
                'list' => $x,
                'sum' => $c,
            ]);

        } catch (\Throwable $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('网络开小差了！请稍后再试,错误信息：' . $e->getLine(), '500');
        }
    }

    /**
     * 修改默认支付宝账号
     * @param $uid
     * @param Request $request
     * @param ExchangeGrape $exchangeGrape
     * @return \Illuminate\Http\JsonResponse
     * @throws ApiException
     */
    public function edit($uid, Request $request, ExchangeGrape $exchangeGrape)
    {
        try {
            $jsonParams = $request->data;
            $arrParams = json_decode($jsonParams, true);
            $rules = [
                'payId' => 'required',
            ];
            $validator = Validator::make($arrParams, $rules);
            if ($validator->fails()) {
                throw new ApiException('缺少必要参数', 3002);
            }
            $payId = $arrParams['payId'];
            $exchangeGrape->clearDefaultPay($uid);
            $exchangeGrape->setDefaultPay($payId);
        } catch (\Exception  $e) {
            if (!empty($e->getCode())) {
                throw new ApiException($e->getMessage(), $e->getCode());
            }
            throw new ApiException('更改默认支付宝失败', 500);
        }

        return $this->getResponse('更改默认支付宝成功');

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
