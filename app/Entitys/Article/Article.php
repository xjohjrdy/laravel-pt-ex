<?php

namespace App\Entitys\Article;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $connection = 'a1191125678';
    protected $table = 'tbl_article_x';
    public $timestamps = false;

    /**
     *
     * 校验文章,如果可以使用则直接获取
     * @param $id
     * @return Model|null|static
     */
    public function getCanUseArticle($id)
    {
        $article = $this->where(['id' => $id, 'static' => 1])->first(['id', 'title', 'abstract', 'article_url', 'addtime', 'static', 'article_id', 'list_img', 'article_sg_url', 'sort']);

        return $article;
    }

    /**
     * 校验文章,如果可以使用则直接获取
     * @param $article_id
     * @return Model|null|static
     */
    public function getUseArticle($article_id)
    {
        $article = $this->where(['article_id' => $article_id])->first(['id', 'title', 'abstract', 'article_url', 'addtime', 'static', 'article_id', 'list_img', 'article_sg_url', 'sort']);

        return $article;
    }

    /**
     * 调用此方法标记已经被使用，必须使用文章对象
     * @return bool
     */
    public function usingArticle()
    {
        $this->static = 2;
        return $this->save();
    }

    /**
     * 插入文章
     * @param $title 文章标题
     * @param $url 最好不要填写外带链接，使用自己系统的解析链接
     * @param $static 0代表没有内容，1代表可以直接使用
     * @param $article_id 文章标志，有唯一性
     * @param $content 文章内容
     * @param $list_img 展示头图片
     * @param int 分类
     * @return bool
     */
    public function insertNewTitle($title, $url, $static, $article_id, $content, $list_img, $sort = 1)
    {
        $res = $this->insert([
            'title' => $title,
            'abstract' => $title,
            'article_url' => $url,
            'vipcn_name' => '葡萄浏览器',
            'vipcn_url' => $url,
            'addtime' => time(),
            'static' => $static,
            'article_id' => $article_id,
            'content' => $content,
            'list_img' => $list_img,
            'article_sg_url' => $url,
            'sort' => $sort,
        ]);
        return $res;
    }
}
