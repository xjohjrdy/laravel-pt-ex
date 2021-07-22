<?php

namespace App\Http\Controllers\Article;

use App\Entitys\Article\ArticleInfo;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DisplayController extends Controller
{
    /**
     * 获取分类
     */
    public function getType()
    {
        $data = [
            ['title' => '搞笑', "type" => "0"],
            ['title' => '热门', "type" => "1"],
            ['title' => '养生', "type" => "2"],
            ['title' => '私房', "type" => "3"],
            ['title' => '八卦', "type" => "4"],
            ['title' => '科技', "type" => "5"],
            ['title' => '财经', "type" => "6"],
            ['title' => '汽车', "type" => "7"],
            ['title' => '生活', "type" => "8"],
            ['title' => '时尚', "type" => "9"],
            ['title' => '育儿', "type" => "10"],
            ['title' => '旅游', "type" => "11"],
            ['title' => '职场', "type" => "12"],
            ['title' => '美食', "type" => "13"],
            ['title' => '历史', "type" => "14"],
            ['title' => '教育', "type" => "15"],
            ['title' => '星座', "type" => "16"],
            ['title' => '体育', "type" => "17"],
            ['title' => '军事', "type" => "18"],
            ['title' => '游戏', "type" => "19"],
            ['title' => '萌宠', "type" => "20"],
        ];
        return $this->getResponse($data);
    }

    /**
     * 获取文章信息
     * @param $id
     * @param ArticleInfo $articleInfo
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function resolveArticle($id, ArticleInfo $articleInfo)
    {
        $res = $articleInfo->getByInfoId($id);
        if (empty($res)) {
            return view('common.alert', ['msg' => '当前文章已过期！']);
        }
        return view('article.index', ['article_id' => $id,'title' => $res->title, 'ad_img' => $res->adpic, 'ad_link' => $res->adlink, 'article_link' => $res->wxlink]);
    }
}
