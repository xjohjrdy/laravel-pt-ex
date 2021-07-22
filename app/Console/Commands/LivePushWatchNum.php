<?php

namespace App\Console\Commands;

use App\Entitys\App\LiveInfo;
use App\Services\Live\LiveServices;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class LivePushWatchNum extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:LivePushWatchNum';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '每一分钟推送观看直播人数';

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
        $this->info('start');
        //得到需要推送人数的直播group_id
        $group_id = Cache::get('watch_live_number_l_i_v_e', '');

        //设置了推送id才获取
        $push_fak_live_num = 0;
        if ($group_id) {
            //得到对应直播的观看人数
            $liveServices = new LiveServices();
            $informInfo = $liveServices->getLiveGroupInfo([$group_id]);
            $arr_informInfo = json_decode($informInfo, true);
            if ($arr_informInfo['GroupInfo'][0]['ErrorCode'] != 0) {
                $this->info('推送失败:' . $arr_informInfo['GroupInfo'][0]['ErrorInfo']);
            } else {
                $live_num = $arr_informInfo['GroupInfo'][0]['MemberNum'];
                $this->info('当前直播实际人数为:' . $live_num);

                //取直播数据
                $liveInfo = new LiveInfo();
                $time = time();
                $live_ing_data = $liveInfo->where('group_id', $group_id)
                    ->where('start_time', '<=', $time)
                    ->where('end_time', 0)
                    ->orderByDesc('id')
                    ->first();

                //真实人数 + 播放秒数 + 随机数 = 推送的观看人数
                $push_fak_live_num = $live_num + ($time - $live_ing_data->start_time) * 16 + rand(0, 100) + $live_ing_data->see;

                //更新直播观看人数
                $live_ing_data->see = $push_fak_live_num;
                $live_ing_data->save();

                //发起通知
                $liveServices = new LiveServices();
                //推送观看人数的自定义消息体
                $data_msg = [
                    'cmd' => 'CustomCmdMsg',
                    'data' => [
                        'cmd' => 'AudienceNum',
                        'msg' => $push_fak_live_num,
                        'userAvatar' => '',
                        'userName' => '',
                    ]
                ];
                $informInfo = $liveServices->sendGroupInform($group_id, json_encode($data_msg));
                $arr_informInfo = json_decode($informInfo, true);

                if ($arr_informInfo['ErrorCode'] != 0) {
                    $this->info('推送失败', $arr_informInfo['ErrorInfo']);#推送失败
                } else {
                    $this->info('推送成功');#推送成功
                }
            }
        }
        $this->info('推送的人数为:' . $push_fak_live_num);
        $this->info('end');
    }
}
