<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CountArticleNumber extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:countArticleNumber';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '统计用户文章数';

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
        $countUser = DB::connection("a1191125678")->table('pre_common_member')->count();
        $limit = 1000;
        $page =  ceil($countUser/$limit);
        print_r($page."\r\n".$countUser."\r\n");

        for ($i=1265;$i<$page;$i++){
            echo ($i*$limit."-".$limit."\r\n");
            $listUserInfo = DB::connection("a1191125678")
                ->select("SELECT uid,username,pt_id,groupid FROM pre_common_member ORDER BY uid LIMIT ?,?",[$i*$limit,$limit]);
            foreach ($listUserInfo as $singleUser){

                file_put_contents("count_article.txt",var_export($singleUser->uid,true));

                $listAgent = DB::connection("a1191125678")
                    ->select("SELECT * FROM tbl_agent  WHERE uid = ?",[$singleUser->uid]);

                $countArticle = DB::connection('wenzhang')
                    ->select("
                        SELECT
                            c1 - c2 as cc
                        FROM
                            ( SELECT SUM( wenzhangshu ) AS c1 FROM tbl_discuz_user WHERE uid = ? ) t1,
                            ( SELECT count( * ) AS c2 FROM tbl_info WHERE userid = ? ) t2
                    ",[$singleUser->uid,$singleUser->username]);
                $articleNumber = $countArticle[0]->cc;

                if (!empty($articleNumber)){
                    echo "count:".$countArticle[0]->cc."\r\n";
                    $forever = 0;
                    if ($singleUser->groupid==23||$singleUser->groupid==24){
                        $forever = 1;
                    } 

                    if (empty($listAgent)){
                        DB::connection('a1191125678')
                            ->insert("INSERT INTO tbl_agent (username, pt_id, uid,update_time,number,forever) VALUES (?,?,?, UNIX_TIMESTAMP(NOW()),?,?)",
                                [$singleUser->username,$singleUser->pt_id,$singleUser->uid,$articleNumber,$forever]);
                    }else{
                        DB::connection('a1191125678')
                            ->update('UPDATE tbl_agent SET update_time=UNIX_TIMESTAMP(NOW()), number=?,forever=? WHERE uid=?',[$articleNumber,$forever,$singleUser->uid]);
                    }

                }
            }
        }

    }
}
