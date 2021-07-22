<?php


namespace App\Services\Qmshida;


use App\Entitys\Other\ThreeChangeUserLog;
use App\Entitys\Other\ThreeUser;
use Illuminate\Support\Facades\DB;

class OtherUserMoneyService
{
    private $otherUserModel;
    private $otherUserChangeModel;
    
    public function __construct()
    {
        $this->otherUserModel = new ThreeUser();
        $this->otherUserChangeModel = new ThreeChangeUserLog();
    }

    public function plusThreeUserMoney($app_id, $commissionRMB, $from_type, $from_info){
        $account = $this->otherUserModel->where(['app_id' => $app_id])->first();
        if (!$account) {
            $this->otherUserModel->create([
                'app_id' => $app_id,
                'money' => 0,
            ]);
            $account = $this->otherUserModel->where(['app_id' => $app_id])->first();
        }
        $user_money = $account->money;
        $this->otherUserModel->where('app_id', $app_id)->update(['money' => DB::raw("money + " . $commissionRMB)]);

        $later_money = $user_money + $commissionRMB;
        $this->otherUserChangeModel->addLog($app_id, $user_money, $commissionRMB, $later_money, $from_type, $from_info);
    }

    public function minusThreeUserMoney($app_id, $commissionRMB, $from_type, $from_info){
        $account = $this->otherUserModel->where(['app_id' => $app_id])->first();
        if (!$account) {
            $this->otherUserModel->create([
                'app_id' => $app_id,
                'money' => 0,
            ]);
            $account = $this->otherUserModel->where(['app_id' => $app_id])->first();
        }
        $user_money = $account->money;
        $this->otherUserModel->where('app_id', $app_id)->update(['money' => DB::raw("money - " . $commissionRMB)]);

        //记录可提余额变化记录值与变化说明
        $this->otherUserChangeModel = new ThreeChangeUserLog();
        $later_money = $user_money - $commissionRMB;
        $this->otherUserChangeModel->addLog($app_id, $user_money, -$commissionRMB, $later_money, $from_type, $from_info);
    }
}