<?php

namespace App\Console\Commands\Invite;

use App\Entitys\App\InviteRealUser;
use App\Entitys\App\PutNewRankList;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class InviteRank extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:InviteRank';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command 拉新活动当月统计';

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

        /**
         * -- 爆款的有效：lc_shop_orders_one的status=1,2,3
         * -- 京东的有效：lc_jd_enter_orders的['validCode' => 17, 'frozenSkuNum' => 0, 'skuReturnNum' => 0]
         * -- 拼多多的有效：lc_pdd_enter_orders的'order_status'属于[1, 2, 3, 5]
         * -- 淘宝的有效：lc_taobao_enter_order的'tk_status'属于[3, 12, 14]
         * -- 关联查出pdd,jd,爆款商城的商品
         */


        $page = 1;
        $page_size = 20000;
        $start_time = strtotime('2020-07-01');
        $end_time = strtotime('2020-08-01');
        if(time() > $end_time){
            $this->info('活动已经结束！');
            return;
        }
        $this->info('开始' .date('Y-m-d H:i:s', $start_time) . '至'. date('Y-m-d H:i:s', $end_time) );
        $this->info('清空拉新用户copy表');
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
            } catch (\Exception $e) {
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
//                    $user_name = $item->user_name;
//                    $phone = $item->phone;
//                    $real_name = $item->real_name;
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

        $this->info("排行榜更新完毕");
    }


    public function objToArr($object) {
        //先编码成json字符串，再解码成数组
        return json_decode(json_encode($object), true);
    }
}
