<?php

namespace App\Http\Controllers\Article;

use App\Services\Gather\GatherUtils;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class NewsController extends Controller
{
    public function getNewsList(Client $client, GatherUtils $gatherUtil)
    {
        $json_list_url = 'https://3g.163.com/touch/api/pagedata/index_yaowen';
		
		try {
			$obj_res = $client->request('GET', $json_list_url, ['verify' => false]);
		} catch (ClientException $e) {
			return [
                'code' => 5001,
                'msg' => '状态码错误'
            ];
		}
		
        if ($obj_res->getStatusCode() != 200) {
            return [
                'code' => 5001,
                'msg' => '状态码错误'
            ];
        }

        $json_res = (string)$obj_res->getBody();

        if (empty($json_res)) {
            return [
                'code' => 5002,
                'msg' => '解析错误'
            ];
        }

        $arr_res = json_decode($json_res, true);

        if (empty($arr_res['data'])) {
            return [
                'code' => 5003,
                'msg' => 'Json没有data数据'
            ];
        }

        $arr_list = [];
        foreach ($arr_res['data'] as $class_single) {

            foreach ($class_single as $info_single) {
                if (empty($info_single['picInfo'])) {
                    continue;
                }
                if (empty($info_single['title'])) {
                    continue;
                }
                if (empty($info_single['digest'])) {
                    continue;
                }

                $info_url = 'http://api.36qq.com/display_news/' . $info_single['docid'] . '#' . $info_single['link'];

                $news_info['title'] = $info_single['title'];
                $news_info['abstract'] = $info_single['digest'];
                $news_info['article_url'] = $info_url;
                $news_info['vipcn_name'] = '葡萄浏览器';
                $news_info['vipcn_url'] = $info_single['link'];
                $news_info['addtime'] = time();
                $news_info['static'] = 10;
                $news_info['article_id'] = $info_single['docid'];
                $news_info['list_img'] = $info_single['picInfo'][0]['url'];
                $news_info['article_sg_url'] = $info_url;
                $news_info['sort'] = 0;

                if (!$gatherUtil->addArticleWyList($news_info))
                    continue;
                $arr_list[] = $news_info;
            }
        }

        return $this->getResponse("成功添加" . count($arr_list) . "条记录");

    }
    public function getNewsArticle(Request $request, Client $client, GatherUtils $gatherUtils)
    {
        $getNewInfo = $gatherUtils->getArticleWyContent();
		
		
		
        if (empty($getNewInfo)) {
            return $this->getResponse('已经没有需获取的文章');
        }
        $url = $getNewInfo['url'];

        if (empty($url)) {
            return [
                'code' => 400,
                'msg' => 'url输入错误'
            ];
        }
		
		try {
			$obj_res = $client->request('GET', $url, ['verify' => false]);
		} catch (ClientException $e) {
			$getNewInfo['content'] = '';
            $getNewInfo['static'] = 18;
			$gatherUtils->setArticleContent($getNewInfo);
			return $this->getResponse('该文章不可用' . $getNewInfo['id']);
		}
		
        if ($obj_res->getStatusCode() != 200) {
            return [
                'code' => 5001,
                'msg' => '状态码错误'
            ];
        }

        $html_res = (string)$obj_res->getBody();

        if (empty($html_res)) {
            return [
                'code' => 5002,
                'msg' => '解析错误'
            ];
        }
		
		
		

        $content = <<< EOT
       
		</main>
            <script src="//static.ws.126.net/163/wap/article-ssr-2018/article_ssr_beta-wapCommonLib.js"></script>
            
            <script src="//static.ws.126.net/163/wap/article-ssr-2018/article_ssr_beta-wapCommon.js"></script>
            
            <script src="//static.ws.126.net/163/wap/article-ssr-2018/manifest.4635cfd.js"></script>
            
            <script src="//static.ws.126.net/163/wap/article-ssr-2018/vendor.09a85bb.js"></script>
            
            <script src="//static.ws.126.net/163/wap/article-ssr-2018/run.89c3292.js"></script>
           
            <script type="text/javascript">
                $(document).ready(function(){
                    $("[class='page js-page']").attr("class","page js-page on");
                    
                    $(".head").hide();
                    
                });
            </script>
			</body>
		</html>
EOT;
        $html_res = preg_replace("/<header(.|\n)+?<\/header>/i", "", $html_res);
        $html_res = preg_replace("/(?<=<\/article>)(.|\n)+?(?=<\/main>)/i", '', $html_res);
        $html_res = preg_replace('/<div class="footer">(.|\n)+?(?=<\/article>)/i', '', $html_res);

        $content_css = <<< EOT
        <style type="text/css">
            .doc-footer-wrapper{
                display: none;
            }
        </style>
        </head>
EOT;
        $html_res = preg_replace('/<\/head>/i', $content_css, $html_res);
        $html_res = preg_replace("/<\/main>.+/is", $content, $html_res);

        if (empty($html_res)){
            $getNewInfo['content'] = '';
            $getNewInfo['static'] = 18;
        }else{
            $getNewInfo['content'] = $html_res;
            $getNewInfo['static'] = 11;
        }

        $gatherUtils->setArticleContent($getNewInfo);

        return $this->getResponse('获取文章内容成功' . $getNewInfo['id']);


    }
}
