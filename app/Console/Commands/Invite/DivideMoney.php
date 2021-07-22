<?php

namespace App\Console\Commands\Invite;

use App\Entitys\App\InviteRealUser;
use App\Entitys\App\PutNewBackgroundAll;
use App\Entitys\App\PutNewGetMoney;
use App\Entitys\App\PutNewRankList;
use App\Entitys\App\PutNewReward;
use App\Entitys\App\PutNewRewardUser;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DivideMoney extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:InviteDivideMoney';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '拉新活动瓜分金额';

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

        $money = 1500; // 可瓜分总金额
        $start_time = strtotime('2020-07-01');
        $end_time = strtotime('2020-08-01');
        $end_time2 = strtotime('2020-08-02');
        $time = time();
//        $time = strtotime('2020-08-01');
        if($time < $end_time || $time > $end_time2){
            $this->info('活动尚未结束或已超过发放日期！');
            return;
        }
        $page = 1;
        $page_size = 20000;
        $this->info('开始' .date('Y-m-d H:i:s', $time));
        $this->info('清空用户临时表');
        DB::connection('_app38')->table('lc_pull_real_user_copy')->truncate();
        while (1) {
            $this->info('第' . $page . '页。每页：' . $page_size . '条');
            $limit_page = ($page - 1) * $page_size;
            $sql = "select u1.id as id, u1.parent_id as pid
                from lc_user u1
                LEFT JOIN lc_user_order_new uon on u1.id = uon.user_id
                LEFT JOIN lc_pdd_enter_orders pdd on u1.id = pdd.app_id
                LEFT JOIN lc_jd_enter_orders jd on u1.id = jd.app_id
                LEFT JOIN lc_shop_orders_one shop on u1.id = shop.app_id
                LEFT JOIN lc_alimama_info_new alimama on u1.id = alimama.app_id
                LEFT JOIN lc_taobao_enter_order taobao on alimama.relation_id = taobao.relation_id
                where u1.create_time >= {$start_time} and u1.create_time < {$end_time} and u1.parent_id > 0
                and (pdd.order_status in (1,2,3,5)
                or (jd.validCode >= 17 and jd.frozenSkuNum >= 0 and jd.skuReturnNum >= 0)
                or shop.`status` in (1,2,3)
                or taobao.`tk_status` in (3, 12, 14)
                or uon.`status` in (3,4,9))
                GROUP BY id limit {$limit_page}, {$page_size}";
            $list = DB::connection('_app38')->select($sql);
            $count = count($list);
            try {
                DB::connection('_app38')->table('lc_pull_real_user_copy')->insert($this->objToArr($list));
            } catch (\Throwable $e) {
                $this->info($e->getMessage() . ':' . $e->getLine());
            }
            if ($count < $page_size) {
                break;
            } else {
                $page++;
            }
        }
        $delete =  DB::connection('_app38')->delete('DELETE l1 FROM lc_pull_real_user l1 left join lc_pull_real_user_copy l2 on l1.id = l2.id where l2.id is null');
        $this->info('删除失效用户' . $delete . '个');
        $insert =  DB::connection('_app38')->insert("INSERT INTO lc_pull_real_user (id, pid)
                                            select l1.id as id,l1.pid as pid  from lc_pull_real_user_copy l1 left join lc_pull_real_user l2 on l1.id = l2.id
                                            where l2.id is null
                                            on duplicate key UPDATE id=VALUES(id),pid=VALUES(pid)");
        $this->info('新增新用户'. $insert . '个');
        #todo 统计排行榜
        $this->info('开始统计排行榜');
        $sql2 = "select count(1) as invite,real_name,avatar,phone, pid,user_name from lc_pull_real_user u2 left join lc_user u1 on u1.id = u2.pid GROUP BY pid order by invite desc";
        $putRankModel = new PutNewRankList();
        $page2 = 1;
        $page_size2 = 10000;
        while (1){
            $this->info('第' . $page2 . '页。每页：' . $page_size2 . '条');
            $list = DB::connection('_app38')->table(DB::raw("($sql2) cc"))->forPage($page2, $page_size2)->get();
            $count = count($list);
            $this->info($count);
            foreach ($list as $key => $item) {
                try {
                    $avatar = empty($item->avatar) ? '' : $item->avatar;
                    $phone = empty($item->phone) ? '0' : $item->phone;
                    $show_info = empty($item->user_name) ? $phone : $item->user_name;
                    $success_add = $item->invite;
                    $app_id = $item->pid;
                    if ($putRankModel->where(['app_id' => $app_id, 'change' => 0])->exists()) {
                        $putRankModel->where(['app_id' => $app_id, 'change' => 0])->update([
                            'avatar' => $avatar,
                            'show_info' => $show_info,
                            'success_add' => $success_add,
                            'change' => 0]);
                    } else {
                        $putRankModel->create([
                            'avatar' => $avatar,
                            'show_info' => $show_info,
                            'success_add' => $success_add,
                            'change' => 0,
                            'app_id' => $app_id
                        ]);
                    }

                } catch(\Throwable $e) {
                    $this->info($e->getMessage() . ':' . $e->getLine());
                    continue;
                }
            }
            if ($count < $page_size2) {
                break;
            } else {
                $page2++;
            }
        }

        $this->info("排行榜更新完毕, 开始瓜分金额");
        #todo 瓜分金额
        /**
         *  现金奖励瓜分规则
         * X（平均数）=瓜分总金额÷总人数，
         * 0.01≤瓜分金额≤2X
         */

        $putMoneyModel = new PutNewGetMoney();
        if ($putMoneyModel->count() == 0) {
            $gift_sql = "select count(1) as invite, pid from lc_pull_real_user_copy GROUP BY pid having invite >= 10 order by invite desc";
            $list = DB::connection('_app38')->table(DB::raw("($gift_sql) cc"))->get();
            $count = count($list);
            $insertData = [];
            $logData = [];
            $time = date('Y-m-d H:i:s');
            foreach ($list as $key => $item) {
                $range_key = $count - $key;
                $max = 0;
                if ($range_key == 1) {
                    $range = round($money, 2);
                } else {
                    $max = round($money / $range_key) * 2;
                    $range = $this->rand($max);
                }
                $money -= $range;
                $app_id = $item->pid;
                $insertData[] = [
                    'app_id' => $app_id,
                    'money' => $range,
                    'updated_at' => $time,
                    'created_at' => $time,
                ];
                $logData[] = [
                    'app_id' => $app_id,
                    'money' => $range,
                ];
            }
            $this->info(json_encode($logData));

            $putMoneyModel->insert($insertData);
            $this->info("金额瓜分完毕");
        } else {
            $this->info('金额已瓜分过');
        }

        $this->info("开始根据排行榜1-50 名发放礼物");
        $rankModel = new PutNewRankList();
        $putNewRewardModel = new PutNewReward();
        $rewardUserModel = new PutNewRewardUser();
        $rank_list = $rankModel->orderByDesc('success_add')->take(50)->get(['app_id', 'success_add', 'change'])->toArray();

        foreach ($rank_list as $key => $item) {
            $search_key = 999;
            switch ($key) {
                case 0:
                    $search_key = 1;
                    break;
                case 1:
                    $search_key = 2;
                    break;
                case 2:
                    $search_key = 3;
                    break;
                default:
                    $search_key = 4;
                    break;
            }
            $app_id = $item['app_id'];
            if($item['change'] == 0){ // 真实排行榜
                $reward = $putNewRewardModel->where(['for_one' => $search_key])->first(['img', 'title', 'money', 'for_one']);
                $info = [
                    'app_id' => $app_id,
                    'img' => $reward['img'],
                    'title' => $reward['title'],
                    'money' => $reward['money'],
                    'for_one' => $reward['for_one']
                ];
                if(!$rewardUserModel->where(['app_id' => $app_id, 'for_one' => $reward['for_one']])->exists()){
                    $rewardUserModel->create($info);
                }
            }
        }
        $this->info('奖品发放完毕！');
    }

    public function rand($max)
    {
        $i = 0;
        $res = 0;
        while ($i < 3) {
            $res += mt_rand(1, $max * 100);
            $i++;
        }
        return round($res / 100 / $i, 2);
    }

    public function objToArr($object)
    {
        //先编码成json字符串，再解码成数组
        return json_decode(json_encode($object), true);
    }
}
