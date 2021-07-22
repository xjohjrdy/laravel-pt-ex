<?php

namespace App\Entitys\App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class ArticleInfo extends Model
{
    protected $connection = 'app38';
    protected $table = 'lc_article_info';
    use SoftDeletes;
    protected $get_columns = ['article_id', 'app_id', 'user_name', 'touch_number', 'touch_get_number', 'style_type', 'item_type', 'url', 'my_url', 'publish_time',
        'cmt_cnt', 'source_name', 'from_time', 'title', 'subhead', 'img_url', 'ad_where', 'ad_url', 'ad_jump_url', 'created_at'];
    /**
     * 需要被转换成日期的属性。
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * 不可被批量赋值的属性。
     *
     * @var array
     */
    protected $guarded = [];


    public function getArticles($conditions = [])
    {
        return $this->where($conditions)->get($this->get_columns);
    }

    public function getOneArticles($conditions = [])
    {
        return $this->where($conditions)->first($this->get_columns);
    }

    public function getArticlesByPage($conditions = [])
    {
        return $this->where($conditions)->orderBy('id', 'desc')->paginate(15, $this->get_columns);
    }

    /**
     * 增加我的币金额
     * @param int $value
     * @return int
     */
    public function addUserPTBMoney($value, $info_id)
    {
        return $this->where(['article_id' => $info_id])->update(['touch_get_number' => DB::raw("touch_get_number + " . $value)]);
    }

    /**
     * 增加我的币金额
     * @param int $value
     * @return int
     */
    public function addUserCheck($value, $info_id)
    {
        return $this->where(['article_id' => $info_id])->update(['touch_number' => DB::raw("touch_number + " . $value)]);
    }

    /**
     * 更新某个数据
     * @param int $value
     * @return int
     */
    public function updateData($article_id, $key, $value)
    {
        return $this->where(['article_id' => $article_id])->update([$key => $value]);
    }
}
