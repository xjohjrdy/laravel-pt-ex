<?php

namespace App\Console\Commands;

use App\Entitys\Article\Article;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

class GetArticleByWu extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:GetArticleByWu';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '抓取文章';

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
        for ($wuhang = 1; $wuhang <= 32; $wuhang++) {
            $client = new Client();
            $article = new Article();
            $list_url = 'http://m.yangtse.com/news/'.$wuhang;
            $res_list = $client->request('get', $list_url);

            $jsonRes = (string)$res_list->getBody();
            $arr_res_list = json_decode($jsonRes, true);

            foreach ($arr_res_list as $k => $v) {
                $detail_url = 'http://m.yangtse.com/content/app/' . $v['id'] . '.html';
                $res_body = $client->request('get', $detail_url);
                $res_body_html = (string)$res_body->getBody();
                $res_body_html = preg_replace('/<div class=\"am-btn-group am-btn-group-justify\"(.|\n)+?<\/div>/', ' ', $res_body_html);
                $res_body_html = preg_replace('/<div class=\"am-header-left am-header-nav\">(.|\n)+?<\/div>/', ' ', $res_body_html);
                $res_body_html = preg_replace('/<div class=\"\" data-backend-compiled=\"\">(.|\n)+?<\/div>/', ' ', $res_body_html);
                $res_body_html = preg_replace('/<h2 class=\"am-text-primary\">(.|\n)+?<\/h2>/', ' ', $res_body_html);
                $res_body_html = preg_replace('/<h1 class=\"am-header-title\">(.|\n)+?<\/h1>/', ' ', $res_body_html);
                $res_body_html = preg_replace('/<p class=\"am-article-meta\">(.|\n)+?<\/p>/', '<p class="am-article-meta">' . date('Y-m-d H:i:s', time()) . ' &nbsp;&nbsp;我的浏览器</p>', $res_body_html);
                $res_body_html = preg_replace('/<div class=\"am-footer-switch\">(.|\n)+<\/div>/', ' ', $res_body_html);
                $is_need_jump = $article->getUseArticle('yzwb_wuhang' . $v['id']);
                if ($is_need_jump) {
                    continue;
                }
                $article->insertNewTitle($v['title'], 'http://api.36qq.com/display_news/yzwb_wuhang' . $v['id'], 1, 'yzwb_wuhang' . $v['id'], '0', $v['titlepic']);
            }
            var_dump(1);
        }
    }
}
