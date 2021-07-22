<?php

namespace App\Services\Advertising;

use App\Entitys\Ad\AdUserInfo;
use App\Entitys\Ad\ExchangeGrapeCard;
use App\Entitys\Ad\ExchangeGrapeOrder;
use App\Entitys\Ad\UserAboutLog;
use App\Entitys\Ad\UserAccount;
use App\Entitys\Ad\UserCreditLog;
use App\Entitys\App\AppUserInfo;
use App\Exceptions\ApiException;

class ExchangeGrape
{

    protected $exchangeGrapeOrder;
    protected $exchangeGrapeCard;
    protected $userAccount;
    protected $userCreditLog;
    protected $aboutLog;
    protected $adUserInfo;

    public function __construct(
        ExchangeGrapeOrder $exchangeGrapeOrder,
        ExchangeGrapeCard $exchangeGrapeCard,
        UserAccount $userAccount,
        UserCreditLog $userCreditLog,
        UserAboutLog $aboutLog,
        AdUserInfo $adUserInfo
    )
    {
        $this->exchangeGrapeOrder = $exchangeGrapeOrder;
        $this->exchangeGrapeCard = $exchangeGrapeCard;
        $this->userAccount = $userAccount;
        $this->userCreditLog = $userCreditLog;
        $this->aboutLog = $aboutLog;
        $this->adUserInfo = $adUserInfo;
    }


    /**
     * 根据uid 查询提现订单
     * @param $uid
     * @return array|bool
     */
    public function getExchangeOrder($uid)
    {
        $listOrder = $this->exchangeGrapeOrder->where('uid', $uid)->orderByDesc('crts')->get();
        if (empty($listOrder)) {
            return false;
        }
        return $listOrder->toArray();
    }

    /**
     * 得到用户的我的币金额
     * @param $uid
     * @return bool|mixed
     */
    public function getUserPTB($uid)
    {
        $userAccount = $this->userAccount->getUserAccount($uid);
        if (empty($userAccount)) {
            return false;
        }
        return $userAccount->extcredits4;

    }

    /**
     * 得到支付宝账号
     * @param $uid
     * @param int $isdefault 如果为空 则取默认支付宝账号  如果为0 则取时间最近的一个支付宝账号
     * @return array|bool
     */
    public function getAliCard($uid, $isdefault = 1)
    {
        if ($isdefault == 1) {
            $queryParams = ['uid' => $uid, 'isdefault' => 1];
        } else {
            $queryParams = ['uid' => $uid];
        }
        $aliPay = $this->exchangeGrapeCard->where($queryParams)->orderByDesc('crts')->first();
        if (empty($aliPay)) {
            return false;
        }

        return $aliPay->toArray();
    }

    /**
     * 扣除我的币，并记录日志
     * @param $uid
     * @param $userPTB
     * @param $ptb
     * @return bool
     * @throws ApiException
     */
    public function deductPTB($uid, $userPTB, $ptb)
    {
        try {
            $userInfo = $this->adUserInfo->getUserById($uid);
            $startPTB = $userPTB;
            $finalPTB = $userPTB - $ptb;
            $this->userAccount->addPTBMoney($finalPTB, $uid);
            $insert_id = $this->userCreditLog->addLog($uid, "TFR", ['extcredits4' => -$ptb]);
            $this->aboutLog->addLog($insert_id, $uid, $userInfo->username, $userInfo->pt_id, ["extcredits4" => $startPTB], ["extcredits4" => $finalPTB]);
        } catch (\Exception $e) {
            throw new ApiException('扣除我的币失败', 5003);
        }
        return true;
    }

    /**
     * 创建提现订单
     * @param $aliPay
     * @param $ptb
     * @param $rmb
     * @return string
     * @throws ApiException
     */
    public function addAliPayOrder($aliPay, $ptb, $rmb)
    {
        try {
            $tixianid = date('YmdHis') . mt_rand(1000000, 9999999);
            $arrParams = array(
                'tixianid' => $tixianid,
                'amount' => $rmb,
                'crts' => time(),
                'return_msg' => '',
                'err_code_des' => '',
                'return_code' => '',
                'openid' => $aliPay['openid'],
                'uid' => $aliPay['uid'],
                'account' => trim($aliPay['account']),
                'type' => $aliPay['type'],
                'bank_name' => $aliPay['bank_name'],
                'account_name' => $aliPay['account_name'],
                'ctype' => 4,
                'bilv' => 10,
                'sxf' => 1,
                'addfundamount' => $ptb,
                'status' => 0,
                'upts' => 0,
                'note' => ''
            );
            $this->exchangeGrapeOrder->insert($arrParams);
        } catch (\Exception $e) {
            throw new ApiException('创建订单失败', 5004);
        }
        return $tixianid;
    }

    /**
     * 根据真实姓名和支付宝账号判定该账号是否被绑定过
     * @param $name
     * @param $account
     * @return bool
     */
    public function isBoundPay($name, $account)
    {
        $isBoundPay = $this->exchangeGrapeCard->where(['account_name' => $name, 'account' => $account])->exists();
        return $isBoundPay;
    }

    /**
     * 添加默认支付宝账号
     * @param $uid
     * @param $name
     * @param $account
     * @return bool
     * @throws ApiException
     */
    public function setAliPayAccount($uid, $name, $account)
    {
        try {
            $this->clearDefaultPay($uid);
            $this->exchangeGrapeCard->insert([
                'uid' => $uid,
                'type' => 'ali',
                'account' => $account,
                'account_name' => $name,
                'bank_name' => '支付宝',
                'crts' => time(),
                'upts' => 0,
                'isdefault' => 1,
                'openid' => ''
            ]);
        } catch (\Exception $e) {
            throw new ApiException('添加默认支付宝错误', 5002);
        }
        return true;
    }


    /**
     * 得到用户首页资料
     * @param $uid
     * @return array
     * @throws ApiException
     */
    public function getUserPayInfo($uid)
    {
        $userPTB = $this->getUserPTB($uid);
        $userName = $this->adUserInfo->getUserById($uid)->username;
        $pt_id = $this->adUserInfo->getUserById($uid)->pt_id;
        $AppUserInfo = new AppUserInfo();
        $phone = $AppUserInfo->getUserById($pt_id)->phone;
        $aliPayAccount = $this->getAliCard($uid);
        if (empty($aliPayAccount)) {
            $aliPayAccount = $this->getAliCard($uid, 0);
            if (empty($aliPayAccount)) {
                return [
                    'ptb' => $userPTB,
                    'userName' => $userName,
                ];
            } else {
                $this->setDefaultPay($aliPayAccount['id']);
            }
        }
        $aliPayList = $this->exchangeGrapeCard->where('uid', $uid)->orderByDesc('crts')->get(['id', 'bank_name', 'account_name', 'account', 'isdefault']);

        return [
            'ptb' => $userPTB,
            'userName' => $phone,
            'payInfo' => [
                'payId' => $aliPayAccount['id'],
                'bankName' => $aliPayAccount['bank_name'],
                'accountName' => $aliPayAccount['account_name'],
                'account' => $aliPayAccount['account'],
            ],
            'payListInfo' => $aliPayList
        ];
    }

    /**
     * 根据记录ID设置为默认支付宝
     * @param $payId
     * @return bool
     * @throws ApiException
     */
    public function setDefaultPay($payId)
    {

        try {
            $this->exchangeGrapeCard->where('id', $payId)->update(['isdefault' => 1]);
        } catch (\Exception $e) {
            throw new ApiException('设置默认支付宝错误', 5001);
        }
        return true;

    }


    /**
     * 将用户的支付宝默认账户全部重置为0
     * @param $uid
     * @return bool
     * @throws ApiException
     */
    public function clearDefaultPay($uid)
    {

        try {
            $this->exchangeGrapeCard->where('uid', $uid)->update(['isdefault' => 0]);
        } catch (\Exception $e) {
            throw new ApiException('重置默认账户失败', 5002);
        }
        return true;

    }


}
