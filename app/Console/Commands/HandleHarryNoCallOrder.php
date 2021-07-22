<?php

namespace App\Console\Commands;

use App\Entitys\App\JdEnterOrders;
use App\Entitys\App\JdEnterOrdersFirst;
use App\Entitys\App\TaobaoUserGet;
use App\Services\Common\UserMoney;
use App\Services\HarryPay\Harry;
use App\Services\JdCommodity\JdCommodityServices;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class HandleHarryNoCallOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:HandleHarryNoCallOrder';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '处理众薪打款未回调的订单';

    private $getModel = null;
    private $harryService = null;
    private $threeUserGet = null;
    private $userMoneyService = null;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->getModel = new TaobaoUserGet();
        $this->harryService = new Harry();
        $this->userMoneyService = new UserMoney();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //
        $get_list = $this->getModel->where(['type' => 4])->get();
        $bar = $this->output->createProgressBar(count($get_list));//设置进度条开始状态
        foreach ($get_list as $key => $item) {
            try {
                $res = $this->harryService->getPushResult($item['id']);
                $code = @$res['return_code'];
                if ($code != 'T') {
                    $this->info('error:' . @$res['content'] . ' id:' . $item['id']);
                    continue;
                }
                DB::connection('app38')->beginTransaction();
                $res = @$res['data'];
                if ($res['status'] == 1 || $res['status'] == 2) {
                    $msg = '';
                    $status = $res['status'];
                    $id = $res['outerOrderNo'];
                    $apply_cash = $this->getModel->where([
                        'id' => $id
                    ])->first();
                    #todo 交易成功
                    if ($status == 1) {
                        $this->getModel->where(['id' => $id])->update([
                            'type' => 1,
                            'reason' => ''
                        ]);
                        $this->info('chenggong!');
                    }
                    #todo 交易失败
                    if ($status == 2) {
                        $errorMsg = $res['errorMsg'];
                        $from_type = 447;
                        $reason = '';
                        if ($apply_cash['from_type'] == 3) { // 支付宝
                            $from_type = 447;
                            $reason = '请核实支付宝账号与收款人姓名是否一致，如果确认一致，可能是因为收款的支付宝账户号不是注册支付宝账号时绑定的手机号或者邮箱号，建议尝试更换一下收款支付宝账号（更换为手机号或者邮箱号）！或重新申请银行卡提现。';
                        }
                        if ($apply_cash['from_type'] == 2) { // 银行卡
                            $from_type = 446;
                            $reason = '请核实收款银行卡号是否正确，收款银行卡号是否是本人的银行卡号，或尝试使用支付宝提现。';
                        }
                        if($errorMsg == '验签失败，余额不足'){
                            $reason = '提现升级，请重新申请提现，审核通过后3个工作日内到账';
                        }
                        $apply_cash->where(['id' => $id])->update([
                            'type' => 2,
                            'error_info' => $errorMsg,
                            'reason' => $reason
                        ]);
                        $fee = $apply_cash['fee'];
                        $cash_money = $apply_cash['money'];
                        $money = 0; //最终退回用户的金额
                        if ($fee > 0) { // 新逻辑
                            $money = $cash_money + $fee;
                        } else { // 旧逻辑
                            if ($cash_money > 99) {
                                $money = round($cash_money / 0.99, 2);
                            } else {
                                $money = $cash_money + 1;
                            }
                        }
                        $this->userMoneyService->plusCnyAndLogNoTrans($apply_cash['app_id'], $money, $from_type, '');
                    }
                } else {
                    $this->info('data is null');
                }
                DB::connection('app38')->commit();
            } catch (\Throwable $e) {
                DB::connection('app38')->rollBack();
                $this->info($e->getMessage());
            }
            $bar->advance();
        }
        $bar->finish();
    }

    private function updateUserGet($res)
    {

    }
}
