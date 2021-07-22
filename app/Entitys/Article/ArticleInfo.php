<?php

namespace App\Entitys\Article;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ArticleInfo extends Model
{
    protected $connection = 'a1191125678';
    protected $table = 'tbl_info';
    public $timestamps = false;

    /**
     * 找到当前用户的文章
     * @param $username
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getByUsername($username)
    {
        $models = $this->where(['userid' => $username])->orderBy('addtime', 'desc')->get(['id', 'touch_number', 'infoid', 'wcount', 'zan', 'title', 'addtime']);

        $models->map(function ($model) {
            $row = $model->title;
            $checked = preg_match('/^if.+else.+/isu', urldecode($row));
            if ($checked > 0) {
                preg_match('/(?<=write\(\").+?(?=\"\))/', urldecode($row), $strTitleRes);
                $title_checked = @$strTitleRes[0];
            } else {
                $title_checked = urldecode($row);
            }
            $model->title = $title_checked;
            return $model;
        });

        return $models;

    }

    /**
     * 新增新的文章
     * `title`, 标题
     * `content`, 内容
     * `adpic`, 广告图片
     * `adlink`, 广告链接
     * `userid`, 用户username
     * `wcount`, 阅读量
     * `addtime`,添加时间
     * `gongzhonghao`, 我的浏览器
     * `infoid`,文章唯一标示， time().rand(10,1000)
     * `daili`, 默认：'system'
     * `share_pic`, image_proxy.php?1=1&siteid=1&url=  "img的链接"
     * `share_desc`, 分享小标题
     * `adid`, 广告id
     * `wxlink`, 微信标题
     * `wximg` 微信图片
     * @param $article
     * @param $ad
     * @param $username
     * @param $type
     * @return bool
     */
    public function addNewArticle($article, $ad, $username, $type = 2)
    {
        $res = $this->insert([
            'title' => $article->title,
            'content' => '1',
            'adpic' => $ad->ad_img,
            'adlink' => $ad->ad_link,
            'userid' => $username,
            'addtime' => date('Y-m-d H:i:s', time()),
            'gongzhonghao' => '我的浏览器',
            'infoid' => time() . rand(10, 1000),
            'daili' => 'system',
            'share_pic' => 'image_proxy.php?1=1&siteid=1&url=' . $article->list_img,
            'share_desc' => $article->title,
            'adid' => $ad->id,
            'ifweizhi' => $type,
            'autoplay' => 0,
            'wxlink' => $article->article_sg_url,
            'wximg' => $article->list_img,
        ]);

        return $res;
    }

    /**
     * 修复拿取文章
     * @param $id
     * @return Model|null|static
     */
    public function getByInfoId($id)
    {
        $res = $this->where(['infoid' => $id])->first(['adpic', 'adlink', 'wxlink', 'title', 'userid', 'touch_number']);
        return $res;
    }


    /**
     * 增加我的币金额
     * @param int $value
     * @return int
     */
    public function addUserPTBMoney($value, $info_id)
    {
        return $this->where(['infoid' => $info_id])->update(['touch_number' => DB::raw("touch_number + " . $value)]);
    }
}
