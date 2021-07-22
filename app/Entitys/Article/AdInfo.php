<?php

namespace App\Entitys\Article;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdInfo extends Model
{
    protected $connection = 'a1191125678';
    protected $table = 'tbl_ad';
    public $timestamps = false;

    /**
     * 获取当前用户所有广告
     * @param $user_id
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function getAdList($user_id)
    {
        $res = $this->where(['userid' => $user_id])->get(['ad_title', 'ad_img', 'id']);
        return $res;
    }

    /**
     *
     * 获得当前用户指定广告
     * @param $user_id
     * @param $ad_id
     * @return Model|null|static
     */
    public function getUserAd($user_id, $ad_id)
    {
        if ($ad_id == -1) {
            $res = $this->where(['userid' => $user_id])->orderBy('id', 'asc')->first(['ad_title', 'ad_link', 'ad_img', 'pmd', 'id', 'ad_img']);
        } else {
            $res = $this->where(['userid' => $user_id, 'id' => $ad_id])->orderBy('id', 'desc')->first(['ad_title', 'ad_link', 'ad_img', 'pmd', 'id', 'ad_img']);
        }

        return $res;
    }

    /**
     *
     * 可用于更新，也可用于新增
     * {"user_id": "3","article_id":"1","type":"2","ad_link":"#","ad_img":"https://a119112.oss-cn-beijing.aliyuncs.com/app_question/haibao/haibao.jpg","ad_id":"1103","ad_context":"跑马灯内容"}
     * @param $user_id
     * @param $arrRequest
     * @param $username
     * @return AdInfo|Model|null
     */
    public function updateOrInsertUserAd($user_id, $arrRequest, $username)
    {
        $ad_title = array_key_exists('ad_title', $arrRequest) ? $arrRequest['ad_title'] : "葡萄浏览器";
        $ad_link = $arrRequest['ad_link'] ? $arrRequest['ad_link'] : "http://api.36qq.com/getReward/" . $username;
        $ad_img = $arrRequest['ad_img'] ? $arrRequest['ad_img'] : "https://a119112.oss-cn-beijing.aliyuncs.com/app_question/haibao/haibao.jpg";
        $pmd = $arrRequest['ad_context'] ? $arrRequest['ad_context'] : "跑马灯内容";

        if ($arrRequest['ad_id'] == -1) {
            $arrRequest['ad_id'] = $this->insertGetId([
                'userid' => $user_id,
                'ad_title' => $ad_title,
                'ad_link' => $ad_link,
                'ad_img' => $ad_img,
                'pmd' => $pmd
            ]);
        } else {
            $this->where([
                'id' => $arrRequest['ad_id'],
                'userid' => $user_id
            ])->update([
                'userid' => $user_id,
                'ad_title' => $ad_title,
                'ad_link' => $ad_link,
                'ad_img' => $ad_img,
                'pmd' => $pmd
            ]);
        }

        $res = $this->getUserAd($user_id, $arrRequest['ad_id']);

        return $res;
    }

    /**
     * 可用于更新，也可用于新增
     * 标准参数：{"user_id": "3","ad_title":1,"ad_link":"#","ad_img":"https://a119112.oss-cn-beijing.aliyuncs.com/app_question/haibao/haibao.jpg"}
     * @param $arrRequest
     * @return bool
     */
    public function addUserAd($user_id, $arrRequest)
    {
        $this->userid = $user_id;
        $this->ad_title = array_key_exists('ad_title', $arrRequest) ? $arrRequest['ad_title'] : "葡萄浏览器";
        $this->ad_link = array_key_exists('ad_link', $arrRequest) ? $arrRequest['ad_link'] : "#";
        $this->ad_img = array_key_exists('ad_img', $arrRequest) ? $arrRequest['ad_img'] : "https://a119112.oss-cn-beijing.aliyuncs.com/app_question/haibao/haibao.jpg";
        $res = $this->save();
        return $res;
    }
}
