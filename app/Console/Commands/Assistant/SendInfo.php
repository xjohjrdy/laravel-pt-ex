<?php

namespace App\Console\Commands\Assistant;

use App\Entitys\App\AlimamaInfo;
use App\Entitys\App\AlimamaInfoNew;
use App\Services\Alimama\BigWashUser;
use App\Services\Itaoke\WechatServices;
use App\Services\TbkCashCreate\TbkCashCreateServices;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SendInfo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:assistant-send_info';

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

        $md_posts = DB::connection('app38')
            ->table("lc_wechat_assistant_user")
            ->select("id", "app_id", "wx_id", "robot_id", "group_flag", "circle_flag")
            ->where("expiry_time", ">", time())
            ->where("user_flag", "2")
            ->where("group_flag", 1)
            ->whereNull('deleted_at');

        $md_user_info = DB::connection('app38')
            ->table("lc_wechat_assistant_user")
            ->select("id", "app_id", "wx_id", "robot_id", "group_flag", "circle_flag")
            ->where("expiry_time", ">", time())
            ->where("user_flag", "2")
            ->where("circle_flag", 1)
            ->whereNull('deleted_at')
            ->union($md_posts);


//        $this->getSql();

        $md_user_info->orderBy("id")->chunk(200, function ($users) {

            foreach ($users as $user) {

                $this->info("APP_ID:" . $user->app_id . "\tgroup_flag：" . $user->group_flag . "\tcircle_flag:" . $user->circle_flag);

                $sv_wechat = new WechatServices($user->robot_id);
                $cashCreateServices = new TbkCashCreateServices();
                $service_dataoke = new BigWashUser();
                try {
                    if (empty($sv_wechat->robotCheckOnline())) {
                        $this->info("机器人ID：" . $sv_wechat->getRobotId() . '离线状态。');
                        continue;
                    }
                } catch (\Throwable $e) {
                    $this->info("error--查询机器人状态失败");
                    continue;
                }
                $send_data = $this->getHdk();

                if (empty($send_data)) {
                    $this->info("APP_ID:" . $user->app_id . "--error--拉取好单库信息失败");
                    continue;
                }
                $datum = $send_data;
                $params = [
                    'goodsId' => $datum['itemid'],
                ];
                $rid = AlimamaInfo::where('app_id', $user->app_id)->value('relation_id');
                if (empty($rid)) {
                    $rid = AlimamaInfoNew::where('app_id', $user->app_id)->value('relation_id');
                }
                if ($rid) {
                    $share_url_change = $service_dataoke->newUrlChange($params);
                    $joint_share_url_change = $share_url_change . '&relationId=' . $rid;
                    $tbk_command = $cashCreateServices->getTpwdCreate($datum['itemtitle'], $joint_share_url_change);
                    $tbk_command = @$tbk_command['data']['model'];
                    $datum['copy_comment'] = $tbk_command;
                } else {
                    $datum['copy_comment'] = '[无]';
                }
                $this->info("APP_ID:" . $user->app_id . "\t淘口令：" . $datum['copy_comment']);

                $send_content = preg_replace("/\&lt;br\&gt;/i", "\r\n", $datum['copy_content']);

                $reg_tao = "/\x{ffe5}([a-zA-Z0-9]{11})\x{ffe5}/isu";

                if (preg_match($reg_tao, $datum['copy_comment'], $m_tao) !== false) {
                    $datum['copy_comment'] = "(" . @$m_tao[1] . ")";
                }
//                dd($datum['copy_comment']);


//                $send_comment = "\r\n复制这条淘口令，进入【Tao宝】即可抢购，\$淘口令\$" . $datum['copy_comment'];
                $send_comment = "\r\n复制这条淘口令，进入【Tao宝】即可抢购，" . $datum['copy_comment'];

                if ($user->circle_flag) {
                    # fa圈
                    $send_circle = [];
                    $send_circle["content"] = $send_content;
                    $send_circle["comment"] = $send_comment;

                    if (empty($datum['itempic'])) {
                        $send_circle["pic_url"] = null;
                    } else {
                        $datum['itempic'] = array_slice($datum['itempic'], 0, 2);
                        $send_circle["pic_url"] = implode("_310x310.jpg;", $datum['itempic']) . '_310x310.jpg';
                    }
                    $this->sendCircle($sv_wechat, $send_circle);
//                    dd($send_circle);
                }
                //
//                dd($user);

                if ($user->group_flag) {

                    $tmp_flag = DB::connection('app38')
                        ->table("lc_wechat_assistant_send_group")
                        ->where("app_id", $user->app_id)
                        ->where("wx_id", $user->wx_id)
                        ->where("tb_flag", 1)
                        ->where("send_flag", 0)
                        ->exists();

                    if (!$tmp_flag) {
                        $tmp_flag = DB::connection('app38')
                            ->table("lc_wechat_assistant_send_group")
                            ->where("app_id", $user->app_id)
                            ->where("wx_id", $user->wx_id)
                            ->where("tb_flag", 1)
                            ->exists();
                        if ($tmp_flag) {
                            DB::connection('app38')
                                ->table("lc_wechat_assistant_send_group")
                                ->where("app_id", $user->app_id)
                                ->where("wx_id", $user->wx_id)
                                ->where("tb_flag", 1)
                                ->update(["send_flag" => 0]);
                        } else {
                            continue; //无数据值
                        }

                    }

                    $md_group = DB::connection('app38')
                        ->table("lc_wechat_assistant_send_group")
                        ->where("app_id", $user->app_id)
                        ->where("wx_id", $user->wx_id)
                        ->where("tb_flag", 1)
                        ->where("send_flag", 0)
                        ->select("id", "user_name")
                        ->orderBy("id")
                        ->first();

                    # fa消息
                    $send_group = [];
                    $send_group["content"] = $send_content . $send_comment;
                    $send_group["pic_url"] = $datum['sola_image'] . '_310x310.jpg';

                    $this->sendGroup($sv_wechat, $md_group->user_name, $send_group);


                    DB::connection('app38')
                        ->table("lc_wechat_assistant_send_group")
                        ->where("id", $md_group->id)
                        ->update(["send_flag" => 1]);
                }


            }
        });

//        dd($md_user_info->limit(10)->get("deleted_at"));
        $this->info("end");
    }

    public function sendGroup(WechatServices $services, $group_id, $send_group)
    {

        try {
            $this->info(date("Y-m-d H:i:s") . "\t开始发群");
            if ($services->robotSendText($group_id, @$send_group['content'])) {
                $this->info("发群文字成功");
            }
            if ($services->robotSendUrlImg($group_id, @$send_group['pic_url'])) {
                $this->info("发群图片成功");
            }
        } catch (\Throwable  $e) {
            $this->info("error--发群错误");
        }

    }

    public function sendCircle(WechatServices $services, $send_circle)
    {

        try {
            $this->info(date("Y-m-d H:i:s") . "\t开始发圈");//        dd($send_circle);
            $resq = $services->robotSendCircle($send_circle['content'], $send_circle['pic_url']);
            $msg_id = @$resq['object']['id'];
            $wx_id = @$resq['object']['userName'];
            if (empty($msg_id || $wx_id)) {
                return false;
            }
            $this->info("发圈文案成功");
            if ($services->robotSendCircleComment($wx_id, $msg_id, $send_circle['comment'])) {
                $this->info("发圈评论成功");
            }
        } catch (\Throwable  $e) {
            $this->info("error--发圈错误");
        }
    }

    public function getHdk()
    {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => "v2.api.haodanku.com/selected_item/apikey/Licieuh/min_id/1",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",

        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $arr_data = json_decode($response, true);

        if (empty($arr_data)) {
            return false;
        }
        $resq = $arr_data['data']['0'];

//        $key = mt_rand(0, 4);
//        $this->info($key);
//        $resq = $arr_data['data'][$key];
        if (empty($resq)) {
            return false;
        }


        return $resq;
    }
}
