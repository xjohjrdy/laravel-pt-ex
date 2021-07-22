<?php

namespace App\Http\Controllers\Article;

use App\Exceptions\ApiException;
use App\Services\Gather\GatherUtils;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use QL\QueryList;

class GatherSingleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(GatherUtils $gatherUtils)
    {
        $getNewInfo = $gatherUtils->getArticleContent();
        if (empty($getNewInfo)){
            return $this->getResponse('已经没有需获取的文章');
        }
        $getHtmlContent = $gatherUtils->gatherArticleContent($getNewInfo['url']);

        if (preg_match('/(?<=warn\"\>)\s+(\S+)/', $getHtmlContent,$regMsg)){
                $getNewInfo['content'] = $regMsg[1];
                $getNewInfo['static'] = 8;

        }else{
            $arrTwo = explode('<meta',$getHtmlContent,2);

            if (count($arrTwo)!=2){
                return $this->getResponse('头部处理错误',5001);
            }

            $resArticle = "{$arrTwo[0]}<meta name=\"referrer\" content=\"never\">\r\n<meta{$arrTwo[1]}";
            $contentDiv = "<div class=\"rich_media_content\" lang==\"en\" id=\"js_content\">";
            $getNewInfo['content'] = preg_replace('/(?<=img-content"\>)[\s\S]+?\<div.+js_content">/i',$contentDiv,$resArticle);
            $getNewInfo['static'] = 1;
        }
		
        $gatherUtils->setArticleContent($getNewInfo);

        return $this->getResponse('获取文章内容成功');

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
    public function show($id)
    {
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
