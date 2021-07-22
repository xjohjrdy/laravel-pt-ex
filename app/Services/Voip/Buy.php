<?php
namespace App\Services\Voip;
use App\Entitys\Ad\AdUserInfo;
use App\Entitys\Ad\UserAboutLog;
use App\Entitys\Ad\UserAccount;
use App\Entitys\Ad\UserCreditLog;
use App\Entitys\Ad\VoipAccount;
use App\Entitys\Ad\VoipMoneyOrder;
use App\Entitys\Ad\VoipMoneyOrderMaid;
use App\Entitys\App\AppUserInfo;
use App\Entitys\App\ShopVoipOrders;
use App\Exceptions\ApiException;
use App\Services\Common\UserMoney;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
class Buy
{
    protected $voipAccount;
    protected $voipMoneyOrder;
    protected $voipMoneyOrderMaid;
    protected $adUserInfo;
    protected $userCreditLog;
    protected $userAboutLog;
    protected $userAccount;
    protected $call;
    protected $appUserInfo;
    protected $shopVoipOrders;
    public function __construct(VoipAccount $voipAccount, ShopVoipOrders $shopVoipOrders, Call $call, AppUserInfo $appUserInfo, VoipMoneyOrder $voipMoneyOrder, VoipMoneyOrderMaid $voipMoneyOrderMaid, AdUserInfo $adUserInfo, UserCreditLog $userCreditLog, UserAboutLog $userAboutLog, UserAccount $userAccount)
    {
        $this->voipAccount = $voipAccount;
        $this->voipMoneyOrder = $voipMoneyOrder;
        $this->voipMoneyOrderMaid = $voipMoneyOrderMaid;
        $this->adUserInfo = $adUserInfo;
        $this->userCreditLog = $userCreditLog;
        $this->userAboutLog = $userAboutLog;
        $this->userAccount = $userAccount;
        $this->call = $call;
        $this->appUserInfo = $appUserInfo;
        $this->shopVoipOrders = $shopVoipOrders;
    }
    /**
     * （一定要小心，给别人充值的时候，如果用户还没有在我们系统里有账户，则充值会不成功）
     * 只有订单支付成功才能调用此方法，否则后果自负
     * @param $order_id 真*id
     * @return int
     */
    public function overOrder($order_id)
    {
        $order = $this->voipMoneyOrder->getById($order_id);
        $is_maid = $this->voipMoneyOrderMaid->getById($order_id);
        if ($is_maid) {
            return 0;
        }
        $voip_account_is = $this->voipAccount->where(['phone' => $order->phone])->first();
        if (!$voip_account_is) {
            $app_user_info = $this->appUserInfo->getUserByPhone($order->phone);
            if ($app_user_info) {
                $call = new Call();
                $call->user_id = $app_user_info->id;
                $call->user_phone = $order->phone;
                $call->getAccountInfo();
            } else {
                $arr_info = [
                    'phone' => $order->phone,
                    'money' => 5,
                    'delete_time' => time() + 3 * 60 * 60 * 24,
                    'is_new' => 1,
                ];
                $this->voipAccount->insert($arr_info);
            }
        }
        $this->voipMoneyOrder->updateOrderStatus($order_id);
        $this->voipAccount->addMoney($order->phone, $order->price);
        $this->voipAccount->addTime($order->phone, $order->time);
        $this->voipAccount->addMovieTime($order->phone);
        $this->maidOrderNew($order_id, $order->real_price, $order->app_id);
        $user_info = $this->adUserInfo->where('pt_id', $order->app_id)->first(['uid', 'groupid', 'pt_pid', 'username', 'pt_id']);
        if (!empty($user_info) && $order->buy_type <> 2) {
            $this->userCreditLog->addLog($user_info->uid, "APP", ['extcredits1' => $order->real_price]);
        }
        return 1;
    }
    /**
     * 处理结束掉充值卡订单
     * @param $phone
     * @param $card_name
     * @param $card_pass
     * @return int
     * @throws ApiException
     */
    public function overOrderCard($phone, $card_name, $card_pass)
    {
        $voip_account_is = $this->voipAccount->where(['phone' => $phone])->first();
        if (!$voip_account_is) {
            $app_user_info = $this->appUserInfo->getUserByPhone($phone);
            if ($app_user_info) {
                $call = new Call();
                $call->user_id = $app_user_info->id;
                $call->user_phone = $phone;
                $call->getAccountInfo();
            } else {
                $arr_info = [
                    'phone' => $phone,
                    'money' => 5,
                    'delete_time' => time() + 3 * 60 * 60 * 24,
                    'is_new' => 1,
                ];
                $this->voipAccount->insert($arr_info);
            }
        }
        $card_info = $this->shopVoipOrders->getByCardName($card_name);
        if (!$card_info) {
            throw new ApiException('充值卡不存在！', '4004');
        }
        if ($card_info->card_pass <> $card_pass) {
            throw new ApiException('充值卡密码错误！', '5000');
        }
        $this->voipAccount->addMoney($phone, $card_info->price);
        $this->voipAccount->addTime($phone, $card_info->time);
        $this->voipAccount->addMovieTime($phone);
        $this->shopVoipOrders->useCard($card_info->id, $phone);
        return 1;
    }
    /**
     * 新的通讯分佣方法
     * @param $order_id
     * @param $commission
     * @param $app_id
     */
    public function newMaidOrder($order_id, $commission, $app_id)
    {
        $user_info = $this->adUserInfo->where('pt_id', $app_id)->first(['uid', 'groupid', 'pt_pid', 'username', 'pt_id']);
        if ($user_info) {
            $ptPid = $user_info->pt_pid;
        } else {
            $ptPid = null;
        }
        if (empty($ptPid)) {
            Storage::disk('local')->append('callback_document/voip_no_maid_reason.txt', var_export('---------------start--------', true));
            Storage::disk('local')->append('callback_document/voip_no_maid_reason.txt', var_export('---------------' . $order_id . '--------', true));
            Storage::disk('local')->append('callback_document/voip_no_maid_reason.txt', var_export('---------------' . $app_id . '--------', true));
            Storage::disk('local')->append('callback_document/voip_no_maid_reason.txt', var_export('---------------end--------', true));
            return 1;
        }
        $parentInfo = $this->adUserInfo->where('pt_id', $ptPid)->first(['uid', 'groupid', 'pt_pid', 'username', 'pt_id']);
        if (empty($parentInfo)) {
            Storage::disk('local')->append('callback_document/voip_no_maid_reason.txt', var_export('---------------start--------', true));
            Storage::disk('local')->append('callback_document/voip_no_maid_reason.txt', var_export('---------------查不到当前用户信息--------', true));
            Storage::disk('local')->append('callback_document/voip_no_maid_reason.txt', var_export('---------------' . $order_id . '--------', true));
            Storage::disk('local')->append('callback_document/voip_no_maid_reason.txt', var_export('---------------' . $ptPid . '--------', true));
            Storage::disk('local')->append('callback_document/voip_no_maid_reason.txt', var_export('---------------end--------', true));
            return 1;
        }
        $parentInfo = $parentInfo->toArray();
        $commission_percent = 0.05;
        if ($parentInfo['groupid'] == 24) {
            $commission_percent = 0.2;
        }
        if ($parentInfo['groupid'] == 23) {
            $commission_percent = 0.15;
        }
        if ($this->voipMoneyOrderMaid->where(['app_id' => $parentInfo['pt_id'], 'order_id' => $order_id])->exists()) {
            Storage::disk('local')->append('callback_document/voip_no_maid_reason.txt', var_export('---------------start--------', true));
            Storage::disk('local')->append('callback_document/voip_no_maid_reason.txt', var_export('---------------记录重复分佣的pt_id--------', true));
            Storage::disk('local')->append('callback_document/voip_no_maid_reason.txt', var_export('---------------' . $parentInfo['pt_id'] . '--------', true));
            Storage::disk('local')->append('callback_document/voip_no_maid_reason.txt', var_export('---------------end--------', true));
            return 1;
        }
        $commission_result = $commission * $commission_percent;
        $this->voipMoneyOrderMaid->addMaidLog($parentInfo['pt_id'], $order_id, $commission_result, $commission);
        $userMoneyService = new UserMoney();
        $userMoneyService->plusCnyAndLog($parentInfo['pt_id'], $commission_result, '59');
//        $commission_result = $commission_result * 10;
//        $perentAcount = $this->userAccount->getUserAccount($parentInfo['uid'])->extcredits4;
//        $this->userAccount->where('uid', $parentInfo['uid'])->update(['extcredits4' => DB::raw("extcredits4 + " . $commission_result)]);
//        $insert_id = $this->userCreditLog->addLog($parentInfo['uid'], "TXG", ['extcredits4' => $commission_result]);
//        $this->userAboutLog->addLog($insert_id, $parentInfo['uid'], $parentInfo['username'], $parentInfo['pt_id'], ["extcredits4" => $perentAcount], ["extcredits4" => $perentAcount + $commission_result]);
        return true;
    }
    /**
     * 分佣方法
     */
    public function maidOrder($order_id, $commission, $app_id)
    {
        $user_info = $this->adUserInfo->where('pt_id', $app_id)->first(['uid', 'groupid', 'pt_pid', 'username', 'pt_id']);
        if ($user_info) {
            $ptPid = $user_info->pt_pid;
        } else {
            $ptPid = null;
        }
        $signOk = false;
        for ($i = 0; $i < 50; $i++) {
            if (empty($ptPid)) {
                break;
            }
            $parentInfo = $this->adUserInfo->where('pt_id', $ptPid)->first(['uid', 'groupid', 'pt_pid', 'username', 'pt_id']);
            if (empty($parentInfo)) {
                break;
            }
            $parentInfo = $parentInfo->toArray();
            $ptPid = $parentInfo['pt_pid'];
            $commission_percent = 0.05;
            if ($i > 2) {
                break;
            } else {
                if ($parentInfo['groupid'] == 24) {
                    $commission_percent = 0.15;
                }
                if ($parentInfo['groupid'] == 23) {
                    $commission_percent = 0.1;
                }
            }
            if ($this->voipMoneyOrderMaid->where(['app_id' => $parentInfo['pt_id'], 'order_id' => $order_id])->exists()) {
                Log::warning('真实重复分佣情况！', [$parentInfo['pt_id'] => $order_id]);
                continue;
            }
            $commission_result = $commission * $commission_percent;
            $this->voipMoneyOrderMaid->addMaidLog($parentInfo['pt_id'], $order_id, $commission_result, $commission);
            $userMoneyService = new UserMoney();
            $userMoneyService->plusCnyAndLog($parentInfo['pt_id'], $commission_result, '59');
//            $commission_result = $commission_result * 10;
//            $this->userAccount->where('uid', $parentInfo['uid'])->update(['extcredits4' => DB::raw("extcredits4 + " . $commission_result)]);
//            $perentAcount = $this->userAccount->getUserAccount($parentInfo['uid'])->extcredits4;
//            $insert_id = $this->userCreditLog->addLog($parentInfo['uid'], "TXG", ['extcredits4' => $commission_result]);
//            $this->userAboutLog->addLog($insert_id, $parentInfo['uid'], $parentInfo['username'], $parentInfo['pt_id'], ["extcredits4" => $perentAcount], ["extcredits4" => $perentAcount + $commission_result]);
            if ($signOk) {
                break;
            }
        }
        return true;
    }
    /**
     * 分佣方法(新)
     */
    public function maidOrderNew($order_id, $commission, $app_id)
    {
        $user_info = $this->adUserInfo->where('pt_id', $app_id)->first(['uid', 'groupid', 'pt_pid', 'username', 'pt_id']);
        if ($user_info) {
            $ptPid = $user_info->pt_pid;
        } else {
            $ptPid = null;
        }
        $signOk = false;
        $num_percent = 0;
        for ($i = 0; $i < 50; $i++) {
            if ($num_percent >= 2) {
                break;
            }
            if (empty($ptPid)) {
                break;
            }
            $parentInfo = $this->adUserInfo->where('pt_id', $ptPid)->first(['uid', 'groupid', 'pt_pid', 'username', 'pt_id']);
            if (empty($parentInfo)) {
                break;
            }
            $parentInfo = $parentInfo->toArray();
            $ptPid = $parentInfo['pt_pid'];
            $commission_percent = 0.05;
            if ($i > 0) {
                if ($parentInfo['groupid'] == 24) {
                    $num_percent++;
                    if ($num_percent == 1) {
                        $commission_percent = 0.05;
                    } elseif ($num_percent == 2) {
                        $commission_percent = 0.025;
                    } else {
                        break;
                    }
                }
            } else {
                if ($parentInfo['groupid'] == 24) {
                    $num_percent++;
                    $commission_percent = 0.2;
                }
                if ($parentInfo['groupid'] == 23) {
                    $commission_percent = 0.15;
                }
            }
            if ($this->voipMoneyOrderMaid->where(['app_id' => $parentInfo['pt_id'], 'order_id' => $order_id])->exists()) {
                Log::warning('真实重复分佣情况！', [$parentInfo['pt_id'] => $order_id]);
                continue;
            }
            $commission_result = $commission * $commission_percent;
            $this->voipMoneyOrderMaid->addMaidLog($parentInfo['pt_id'], $order_id, $commission_result, $commission);
            $userMoneyService = new UserMoney();
            $userMoneyService->plusCnyAndLog($parentInfo['pt_id'], $commission_result, '59');
//            $commission_result = $commission_result * 10;
//            $this->userAccount->where('uid', $parentInfo['uid'])->update(['extcredits4' => DB::raw("extcredits4 + " . $commission_result)]);
//            $perentAcount = $this->userAccount->getUserAccount($parentInfo['uid'])->extcredits4;
//            $insert_id = $this->userCreditLog->addLog($parentInfo['uid'], "TXG", ['extcredits4' => $commission_result]);
//            $this->userAboutLog->addLog($insert_id, $parentInfo['uid'], $parentInfo['username'], $parentInfo['pt_id'], ["extcredits4" => $perentAcount], ["extcredits4" => $perentAcount + $commission_result]);
            if ($signOk) {
                break;
            }
        }
        return true;
    }
    /**
     * 分佣方法
     */
    public function maidNoOrder($order_id, $commission, $app_id, $need_time)
    {
        $user_info = $this->adUserInfo->where('pt_id', $app_id)->first(['uid', 'groupid', 'pt_pid', 'username', 'pt_id']);
        if ($user_info) {
            $ptPid = $user_info->pt_pid;
        } else {
            $ptPid = null;
        }
        $signOk = false;
        for ($i = 0; $i < 50; $i++) {
            if (empty($ptPid)) {
                break;
            }
            $parentInfo = $this->adUserInfo->where('pt_id', $ptPid)->first(['uid', 'groupid', 'pt_pid', 'username', 'pt_id']);
            if (empty($parentInfo)) {
                break;
            }
            $parentInfo = $parentInfo->toArray();
            $ptPid = $parentInfo['pt_pid'];
            $commission_percent = 0.05;
            if ($i > 2) {
                break;
            } else {
                if ($parentInfo['groupid'] == 24) {
                    $commission_percent = 0.15;
                }
                if ($parentInfo['groupid'] == 23) {
                    $commission_percent = 0.1;
                }
            }
            if ($this->voipMoneyOrderMaid->where(['app_id' => $parentInfo['pt_id'], 'order_id' => $order_id])->exists()) {
                continue;
            }
            $commission_result = $commission * $commission_percent;
            $this->voipMoneyOrderMaid->addMaidLog($parentInfo['pt_id'], $order_id, $commission_result, $commission);
            $userMoneyService = new UserMoney();
            $userMoneyService->plusCnyAndLog($parentInfo['pt_id'], $commission_result, '59');
//            $commission_result = $commission_result * 10;
//            $this->userAccount->where('uid', $parentInfo['uid'])->update(['extcredits4' => DB::raw("extcredits4 + " . $commission_result)]);
//            $perentAcount = $this->userAccount->getUserAccount($parentInfo['uid'])->extcredits4;
//            $userCreditLog = new UserCreditLog();
//            $change_arr = [
//                'uid' => intval($parentInfo['uid']),
//                'relatedid' => intval($parentInfo['uid']),
//                'operation' => "TXG",
//                'extcredits1' => 0,
//                'extcredits2' => 0,
//                'extcredits3' => 0,
//                'extcredits4' => 0,
//                'extcredits5' => 0,
//                'extcredits6' => 0,
//                'extcredits7' => 0,
//                'extcredits8' => 0,
//                'dateline' => $need_time,
//            ];
//            $change_arr = array_merge($change_arr, ['extcredits4' => $commission_result]);
//            $userCreditLog->insertGetId($change_arr);
            if ($signOk) {
                break;
            }
        }
        return true;
    }
}
