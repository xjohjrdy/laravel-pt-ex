<?php

    namespace App\Console\Commands\User;

use App\Entitys\App\TaobaoUserGet;
use App\Services\Common\UserMoney;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class WithdrawReject extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:WithdrawReject';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info("hello");
        $userMoneyService = new UserMoney();
        $model = new TaobaoUserGet();
        $reason = '因银行卡通道升级维护，本次提现失败，金额已退回，建议使用支付宝通道进行提现。';
        while (1) {
            try {
                $page_size = 10000;
                $apply_cash_list = $model->where([
                    'type' => 0,
                    'from_type' => 2
                ])->forPage(1, $page_size)->get();
                foreach ($apply_cash_list as $apply_cash) {
                    if (empty($apply_cash)) {
                    } else {
                        $fee = $apply_cash['fee'];
                        $cash_money = $apply_cash['money'];
                        $money = 0; //最终退回用户的金额
                        $from_type = 447;
                        if ($apply_cash['from_type'] == 3) { // 支付宝
                            $from_type = 447;
                        }
                        if ($apply_cash['from_type'] == 2) { // 银行卡
                            $from_type = 446;
                        }
                        if ($fee > 0) { // 新逻辑
                            $money = $cash_money + $fee;
                        } else { // 旧逻辑
                            if ($cash_money > 99) {
                                $money = round($cash_money / 0.99, 2);
                            } else {
                                $money = $cash_money + 1;
                            }
                        }
                        try {
                            DB::connection('app38')->beginTransaction();
                            $model->where([
                                'id' => $apply_cash['id'],
                                'type' => 0,
                            ])->update([
                                'type' => 2,
                                'reason' => $reason
                            ]);
                            $userMoneyService->plusCnyAndLogNoTrans($apply_cash['app_id'], $money, $from_type, '');
                            DB::connection('app38')->commit();
                            $this->info('id:' . $apply_cash['id'] . '姓名:' . $apply_cash['real_name'] . 'money:' . $apply_cash['money'] . ' ok');
                        } catch (\Exception $e) {
                            $this->info($e->getMessage());
                            DB::connection('app38')->rollBack();
                            continue;
                        }
                    }

                }
                $count = count($apply_cash_list);
                $this->info($page_size);
                if ($count < $page_size) {
                    break;
                }
            } catch (\Exception $e) {
                $this->info($e->getMessage());
                break;
            }
        }
        $this->info('结束！');
    }
}
