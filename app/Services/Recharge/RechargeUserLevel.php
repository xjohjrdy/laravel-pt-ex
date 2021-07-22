<?php

namespace App\Services\Recharge;


use App\Exceptions\ApiException;

class RechargeUserLevel
{

    protected $purchaseUserGroup;


    private $groupid;
    private $gid;
    private $uid;
    private $groupPriceRMB;
    private $userAccountRMB;
    private $listIdGroupId;
    private $listGroupPrice;
    private $listGroupDays;
    private $listGroupDesc;
    private $orderid;

    public function __construct(PurchaseUserGroup $purchaseUserGroup)
    {
        $this->purchaseUserGroup = $purchaseUserGroup;
    }

    /*
     * 兼容其他方法
     * $arrParams['uid'] =
     * $arrParams['money'] =
     * $arrParams['orderid'] =
     *
     */
    public function initOrder($arrParams)
    {

        $this->uid = $arrParams['uid'];

        $timestamp = $this->purchaseUserGroup->timestamp;
        $listIdRechargeSetting = $this->purchaseUserGroup->getRechargeSetting();
        $userInfo = $this->purchaseUserGroup->getUserCommonMember($this->uid);

        $this->listIdGroupId = array_column($listIdRechargeSetting, 'groupid', 'id');
        $this->listGroupPrice = array_column($listIdRechargeSetting, 'price', 'id');
        $this->listGroupDays = array_column($listIdRechargeSetting, 'days', 'id');
        $this->listGroupDesc = array_column($listIdRechargeSetting, 'desc', 'id');

        if (empty($arrParams['gid'])) {
            $this->gid = array_search($arrParams['money'], $this->listGroupPrice);
            $this->orderid = $arrParams['orderid'];
        } else {
            $this->gid = $arrParams['gid'];
        }
        $this->groupid = $this->listIdGroupId[$this->gid];
        $this->groupPriceRMB = $this->listGroupPrice[$this->gid];

    }
    public function isAccount()
    {
        $this->userAccountRMB = $this->purchaseUserGroup->getUserAccountRMB();

        if ($this->userAccountRMB < $this->groupPriceRMB) {
            throw new ApiException('用户余额不足', 2001);
        }
        return true;
    }
    public function installOrder($type)
    {
        $this->orderid = date('YmdHis') . $this->purchaseUserGroup->random(18);
        if ($this->purchaseUserGroup->whetherOrder($this->orderid)) {
            $this->orderid = date('YmdHis') . $this->purchaseUserGroup->random(18);
        }
        $arrOrderParam = array(
            'orderid' => $this->orderid,
            'status' => $type == 1 ? '2' : '1',
            'uid' => $this->purchaseUserGroup->tempUserInfo['uid'],
            'groupid' => $this->groupid,
            'amount' => $this->listGroupDays[$this->gid],
            'price' => $this->listGroupPrice[$this->gid],
            'desc' => $this->listGroupDesc[$this->gid],
            'submitdate' => $this->purchaseUserGroup->timestamp,
            'confirmdate' => $this->purchaseUserGroup->timestamp,
            'a' => '',
            'b' => '',
            'c' => '',
            'd' => 0,
            'e' => 0,
        );
        $this->purchaseUserGroup->addOrder($arrOrderParam);

        return [$this->orderid, $this->listGroupPrice[$this->gid], $this->listGroupDesc[$this->gid]];
    }
    public function updateExt()
    {
        $userInfo = $this->purchaseUserGroup->tempUserInfo;
        $extgroupids = $userInfo['extgroupids'] ? explode("\t", $userInfo['extgroupids']) : array();
        $extgroupidsarray = array($userInfo['groupid']);
        foreach (array_unique(array_merge($extgroupids, array($this->groupid))) as $extgroupid) {
            if ($extgroupid) {
                $extgroupidsarray[] = $extgroupid;
            }
        }
        $extgroupidsnew = implode("\t", $extgroupidsarray);
        $this->purchaseUserGroup->updateExtgroupids($extgroupidsnew);

        $groupterms = $this->purchaseUserGroup->getGroupterms();

        $groupterms['ext'][$this->groupid] = (@$groupterms['ext'][$this->groupid] > $this->purchaseUserGroup->timestamp ? $groupterms['ext'][$this->groupid] : $this->purchaseUserGroup->timestamp) + $this->listGroupDays[$this->gid] * 86400;
        $this->purchaseUserGroup->updateGroupterms($groupterms);
        if (in_array($this->groupid, array(23, 24))) {
            $groupexpirynew = $groupterms['ext'][$this->groupid];
            $this->purchaseUserGroup->updateCommonMemberGroup($this->groupid, $groupexpirynew);
        }
    }
    public function updateUserAccountRMB()
    {
        $this->purchaseUserGroup->updateUserAccountRMB($this->userAccountRMB - $this->groupPriceRMB);
    }
    public function returnCommission()
    {
        $this->purchaseUserGroup->returnCommissionV2($this->orderid);
    }
    public function returnCommissionV12()
    {
        $this->purchaseUserGroup->returnCommissionV12($this->orderid, $this->groupPriceRMB);
    }
    public function handleArticle()
    {
        /**************************= 开始处理文章 =***************************/
        $addArticleNumber = 10;
        if ($this->groupPriceRMB >= 300) {
            $is_forever = 1;
            if ($this->purchaseUserGroup->isTimeOk()) {
                $addArticleNumber = 0;
            }
        } else {
            $is_forever = 0;
        }
        $arrAgentInfo = $this->purchaseUserGroup->getAgentInfo();
        if ($arrAgentInfo) {
            if ($arrAgentInfo['forever'] && $is_forever) {
                $articleNumber = $arrAgentInfo['number'];
            } else {
                $articleNumber = $arrAgentInfo['number'] + $addArticleNumber;
            }
            $is_forever = $arrAgentInfo['forever'] ? 1 : $is_forever;

            $this->purchaseUserGroup->updateAgentInfo($this->purchaseUserGroup->timestamp, $articleNumber, $is_forever);
        } else {
            $this->purchaseUserGroup->addAgentInfo($this->purchaseUserGroup->timestamp, $addArticleNumber, $is_forever);
        }
    }


    /**
     * 1.订单生成方法（状态为未支付）
     * @param $uid
     * @return array
     */
    public function generatingOrder($uid)
    {
        $price = 0.01;
        $desc = '测试商品';
        $group_id = 11;
        /**
         * @param $groupid
         * @param $price
         * @param $desc
         * @param int $amount 建议全部采用 9999
         */
        $order_id = time();

        return [$order_id, $price, $group_id, $desc];
    }

    /**
     * 2.此处仅用于用户充值金额扣除，需要制定价格和用户
     * @param $uid
     * @param $price
     * @return int
     */
    public function buy($uid, $price)
    {

        return 1;
    }

    /**
     * 3.此处用户用户扣钱成功以后，进行后续的操作
     * @param $uid
     * @param $order_id (如果是支付宝支付，此处反馈的是阿里反馈的订单号)
     * @param $price (如果是支付宝支付，此处反馈的是阿里实际支付的金额)
     * @return int
     */
    public function levelUp($uid, $order_id, $price)
    {

        return 1;
    }


}
