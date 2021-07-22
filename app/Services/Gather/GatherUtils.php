<?php

namespace App\Services\Gather;

use App\Entitys\Article\Article;

class GatherUtils
{
    protected $appArticle;

    public function __construct(Article $appArticle)
    {
        $this->appArticle = $appArticle;
    }
    public function gatherArticle($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $tmpInfo = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        if ($httpCode != 200) {
            return false;
        }
        return $tmpInfo;
    }
    public function gatherArticleContent($url)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_FAILONERROR, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        $tmpInfo = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        if ($httpCode != 200) {
            return false;
        }
        return $tmpInfo;
    }
    public function addArticleList($arrSingleArticle)
    {
        try {
            $this->appArticle->insert([
                'article_id' => $arrSingleArticle['regAid'],
                'article_url' => $arrSingleArticle['regUrl'],
                'article_sg_url' => $arrSingleArticle['regSgUrl'],
                'list_img' => $arrSingleArticle['regImg'],
                'title' => $arrSingleArticle['regTitle'],
                'abstract' => $arrSingleArticle['regAbstract'],
                'vipcn_url' => $arrSingleArticle['regVipcnUrl'],
                'vipcn_name' => $arrSingleArticle['regVipcnName'],
                'sort' => $arrSingleArticle['sort'],
                'addtime' => time()
            ]);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    public function addArticleWyList($arrSingleArticle)
    {
        try {
            $this->appArticle->insert($arrSingleArticle);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
    public function getArticleContent()
    {
        $singleInfo = $this->appArticle
            ->where('static', 0)
            ->orderBy('id')
            ->first(['id', 'title', 'abstract', 'article_url', 'addtime', 'static', 'article_id', 'list_img', 'article_sg_url', 'sort']);

        if (empty($singleInfo)) {
            return false;
        }

        return ['id' => $singleInfo->id, 'url' => $singleInfo->article_sg_url];
    }
    public function getArticleWyContent()
    {
        $singleInfo = $this->appArticle
            ->where('static', 10)
            ->orderBy('id')
            ->first(['id', 'title', 'abstract', 'article_url', 'addtime', 'static', 'article_id', 'list_img', 'article_sg_url', 'sort']);

        if (empty($singleInfo)) {
            return false;
        }

        return ['id' => $singleInfo->id, 'url' => $singleInfo->vipcn_url];
    }
    public function setArticleContent($arrParam)
    {
        $arrParam['content'] = preg_replace_callback(
            '/./u',
            function (array $match) {
                return strlen($match[0]) >= 4 ? '' : $match[0];
            },
            $arrParam['content']);

        try {
            $this->appArticle
                ->where('id', $arrParam['id'])
                ->update(['content' => $arrParam['content'],
                    'static' => $arrParam['static'],
                    'addtime' => time(),
                ]);
        } catch (\Exception $e) {
            return false;
        }
    }

}
