<?php

namespace App\Http\Controllers\Article;

use App\Exceptions\ApiException;
use App\Services\Gather\GatherUtils;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class GatherController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($sort,GatherUtils $gatherUtil)
    {
        $listUrl = "http://weixin.sogou.com/pcindex/pc/pc_{$sort}/pc_{$sort}.html";
        $getHtmlList = $gatherUtil->gatherArticle($listUrl);
        if (empty($gatherUtil))
            throw new ApiException('抓取数据为空',4001);

        if (!preg_match_all('/(?<=\bd\=\").+?(?=\")/i', $getHtmlList, $regAid))
            throw new ApiException('抓取文章ID为空',4002);

        if (!preg_match_all('/(?<=img\"\shref\=\").+?(?=\")/i', $getHtmlList, $regUrl))
            throw new ApiException('抓取文章链接为空',4004);

        if (!preg_match_all('/(?<=share\=\").+?(?=\")/i', $getHtmlList, $regSgUrl))
            throw new ApiException('抓取文章链接为空',4004);

        if (!preg_match_all('/(?<=img\ssrc\=\").+?(?=\")/i', $getHtmlList, $regImg))
            throw new ApiException('抓取文章列表图片为空',4004);

        if (!preg_match_all('/(?<=\"\>).+?(?=\<\/a\>\<\/h3)/i', $getHtmlList, $regTitle))
            throw new ApiException('抓取文章标题为空',4005);

        if (!preg_match_all('/(?<=ank\"\>).+?(?=\<\/p)/i', $getHtmlList, $regAbstract))
            throw new ApiException('抓取文章摘要为空',4006);

        if (!preg_match_all('/href\=\"(http\S+)\"\s\S+\"\sdata-isV=\"\d\"\>(\S+)\</i', $getHtmlList, $regVipcn))
            throw new ApiException('抓取公众号为空',4007);

        $articleList = array(array());
        for ($i=0;$i<count($regAid[0]);$i++){
            $articleList[$i]['regAid'] = @$regAid[0][$i];
            $articleList[$i]['regUrl'] = @$regUrl[0][$i];
            $articleList[$i]['regSgUrl'] = @$regSgUrl[0][$i];
            $articleList[$i]['regImg'] = @$regImg[0][$i];
            $articleList[$i]['regTitle'] = @$regTitle[0][$i];
            $articleList[$i]['regAbstract'] = @$regAbstract[0][$i];
            $articleList[$i]['regVipcnUrl'] = @$regVipcn[1][$i];
            $articleList[$i]['regVipcnName'] = @$regVipcn[2][$i];
            $articleList[$i]['sort'] = $sort;

            if (!$gatherUtil->addArticleList($articleList[$i]))
                break;
        }

        return $this->getResponse("成功添加".$i."条记录");
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
    }
}
