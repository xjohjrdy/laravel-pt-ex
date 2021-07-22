<?php

namespace App\Services\Advertising;



use App\Entitys\Ad\AdUserInfo;
use App\Entitys\Ad\UserAboutLog;
use App\Entitys\Ad\UserAccount;
use App\Entitys\Ad\UserCreditLog;
use App\Entitys\App\AppUserInfo;
use App\Exceptions\ApiException;

class Withdrawalsawals
{
    protected $appUserInfo;
    protected $adUserInfo;
    protected $userAccount;
    protected $userCreditLog;
	protected $aboutLog;

    public function __construct(AppUserInfo $appUserInfo,AdUserInfo $adUserInfo,UserAccount $userAccount,UserCreditLog $userCreditLog,UserAboutLog $aboutLog)
    {
        $this->appUserInfo = $appUserInfo;
        $this->adUserInfo = $adUserInfo;
        $this->userAccount = $userAccount;
        $this->userCreditLog = $userCreditLog;
		$this->aboutLog = $aboutLog;
    }

    /**
     * 传入用户id 查询是否存在对应的广告联盟账号
     * 以及查询广告联盟钱包账号是否存在
     * @param $arrParam
     * @return array
     * @throws ApiException
     */
    public function verify($arrParam){
        $adUserId = $this->adUserInfo->where('pt_id',$arrParam['user_id'])->first();
        if (empty($adUserId))
            throw new ApiException('没有注册广告联盟', '4001');
        $adUserAccountInfo = $this->userAccount->where('uid',$adUserId->uid)->first();

        if (empty($adUserAccountInfo))
            throw new ApiException('没有注册广告联盟', '4001');

        $appUserId = $this->appUserInfo->where('id',$arrParam['user_id'])->first();
        if ($arrParam['bonus_amount']>$appUserId->bonus_amount)
            throw new ApiException('分红提现申请金额异常', '4002');
        if ($arrParam['order_amount']>$appUserId->order_amount)
            throw new ApiException('订单提现申请金额异常', '4002');

        return array(
            'adUid' => $adUserId->uid,
            'adUserName' => $adUserId->username,
            'adPtId' => $adUserId->pt_id,
            'extcredits4' => $adUserAccountInfo->extcredits4,
        );

    }

    public function deduction($arrParam){

        $appUserId = $this->appUserInfo->where('id',$arrParam['user_id'])->first();

        $this->appUserInfo
            ->where('id',$arrParam['user_id'])
            ->update([
                'bonus_amount'=>$appUserId->bonus_amount-$arrParam['bonus_amount'],
                'order_amount'=>$appUserId->order_amount-$arrParam['order_amount'],
                'apply_cash_amount'=>$appUserId->apply_cash_amount-$arrParam['bonus_amount']-$arrParam['order_amount'],
                'order_can_apply_amount'=>$appUserId->order_can_apply_amount-$arrParam['order_amount']
                ]);
    }

    public function addPtGold($arrParam){
        try {
            $this->userAccount
                ->where('uid', $arrParam['adUid'])
                ->update(['extcredits4' => $arrParam['extcredits4'] + $arrParam['ptGold']]);
        } catch (\Exception $e) {
            throw new ApiException('添加我的币失败', '5001');
        }
        try {
            $insert_id = $this->userCreditLog->addLog(intval($arrParam['adUid']), "APT", ['extcredits4' => intval($arrParam['ptGold'])]);
            $this->aboutLog->addLog($insert_id, $arrParam['adUid'],$arrParam['adUserName'], $arrParam['adPtId'], ["extcredits4" => $arrParam['extcredits4']], ["extcredits4" => $arrParam['extcredits4'] + $arrParam['ptGold']]);

        } catch (\Exception $e) {
            throw new ApiException('积分记录添加失败', '5002');
        }

    }
    
}
