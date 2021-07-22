<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Entitys\Article\ArticleInfo;
use Illuminate\Support\Facades\Cache;
use App\Services\Common\CommonFunction;

/**
 * 首页-头条-缓存更新脚本
 * @author putao
 */
class IndexArticleUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:IndexArticleUpdate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '每分钟更新一次首页头条内容（前20页）';

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
        $this->info("command:IndexArticleUpdate begin...");
        
        $articleInfo = new ArticleInfo();
        $pages = 20;
        $cache_engine = 'redis';
        for ( $page=1; $page<=$pages; $page++ ) {
            $cache_key = CommonFunction::getIndexArticlePageCacheKey($page);
            $ArticleInfo = $articleInfo
                ->orderBy('id', 'desc')
                ->paginate(20, ['id', 'addtime', 'infoid', 'title', 'userid', 'title', 'wximg', 'wxlink'],'',$page);
            Cache::store($cache_engine)->forever($cache_key, $ArticleInfo );
        }
        
        $this->info('command:IndexArticleUpdate end...');
    }

}
