<?php

namespace App\Http\Controllers\Newbies;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class IndexController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {

        $arrNewbieList = array(
            array(
                'title' => '如何发布头条',
//                'imgUrl' => 'https://a119112.oss-cn-beijing.aliyuncs.com/%E8%A7%86%E9%A2%91/%E7%BB%8413%402x.png',
                'imgUrl' => 'http://cdn01.36qq.com/CDN/%E5%A4%B4%E6%9D%A1@2x.png',
                'contentUrl' => 'http://a119112.oss-cn-beijing.aliyuncs.com/UI-%E5%A7%9C%E9%AB%98%E5%B0%9A/%E5%9B%BE%E7%89%87%E9%A6%96%E9%A1%B5/1/%E8%A7%86%E9%A2%91/%E8%A7%86%E9%A2%91%E6%95%99%E7%A8%8B1114_v2/%E8%A7%86%E9%A2%91%E6%95%99%E7%A8%8B1114_v2/2%20-%20%E8%91%A1%E8%90%84%E5%A4%B4%E6%9D%A1_1.mp4'
            ),
//            array(
//                'title' => '如何充值话费',
////                'imgUrl' => 'https://a119112.oss-cn-beijing.aliyuncs.com/%E8%A7%86%E9%A2%91/%E7%BB%8419%402x.png',
//                'imgUrl' => 'http://cdn01.36qq.com/CDN/%E9%80%9A%E8%AE%AF@2x.png',
//                'contentUrl' => 'http://a119112.oss-cn-beijing.aliyuncs.com/web_shop/te/%E8%A7%86%E9%A2%91/%E8%91%A1%E8%90%84%E9%80%9A%E8%AE%AF%E6%9C%80%E6%96%B0%E6%95%99%E7%A8%8B%E8%A7%86%E9%A2%91.mp4'
//            ),
//            array(
//                'title' => '如何成为超级用户',
//                'imgUrl' => 'http://a119112.oss-cn-beijing.aliyuncs.com/UI-%E5%A7%9C%E9%AB%98%E5%B0%9A/%E5%9B%BE%E7%89%87%E9%A6%96%E9%A1%B5/1/%E8%A7%86%E9%A2%91/%E8%A7%86%E9%A2%91%E6%95%99%E7%A8%8B1114_v2/%E8%A7%86%E9%A2%91%E6%95%99%E7%A8%8B1114_v2/%E6%96%B0%E6%89%8B%E6%94%BB%E7%95%A5banner%E5%9B%BE-1114/%E5%A6%82%E4%BD%95%E6%88%90%E4%B8%BAVIP.png',
//                'contentUrl' => 'http://a119112.oss-cn-beijing.aliyuncs.com/web_shop/te/%E8%A7%86%E9%A2%91/VIP.mp4'
//            ),
            array(
                'title' => '正确购物报销视频',
//                'imgUrl' => 'https://a119112.oss-cn-beijing.aliyuncs.com/%E8%A7%86%E9%A2%91/bg1%402x.png',
                'imgUrl' => 'http://cdn01.36qq.com/CDN/%E6%B7%98%E5%AE%9D%E6%8A%A5%E9%94%80@2x.png',
                'contentUrl' => 'http://a119112.oss-cn-beijing.aliyuncs.com/UI-%E5%A7%9C%E9%AB%98%E5%B0%9A/%E5%9B%BE%E7%89%87%E9%A6%96%E9%A1%B5/1/%E8%A7%86%E9%A2%91/%E8%A7%86%E9%A2%91%E6%95%99%E7%A8%8B1114_v2/%E8%A7%86%E9%A2%91%E6%95%99%E7%A8%8B1114_v2/1%20-%20%E8%B4%AD%E7%89%A9%E6%8A%A5%E9%94%80.mp4'
            ),
//            array(
//
//                'title' => '如何抢圈子红包',
////                'imgUrl' => 'http://a119112.oss-cn-beijing.aliyuncs.com/UI-%E5%A7%9C%E9%AB%98%E5%B0%9A/%E5%9B%BE%E7%89%87%E9%A6%96%E9%A1%B5/1/%E8%A7%86%E9%A2%91/%E8%A7%86%E9%A2%91%E6%95%99%E7%A8%8B1114_v2/%E8%A7%86%E9%A2%91%E6%95%99%E7%A8%8B1114_v2/%E6%96%B0%E6%89%8B%E6%94%BB%E7%95%A5banner%E5%9B%BE-1114/%E5%A6%82%E4%BD%95%E6%8A%A2%E5%9C%88%E5%AD%90%E7%BA%A2%E5%8C%85.png',
//                'imgUrl' => 'http://cdn01.36qq.com/CDN/%E5%9C%88%E5%AD%90%E6%8A%A2%E7%BA%A2%E5%8C%85@2x.png',
//                'contentUrl' => 'http://a119112.oss-cn-beijing.aliyuncs.com/UI-%E5%A7%9C%E9%AB%98%E5%B0%9A/%E5%9B%BE%E7%89%87%E9%A6%96%E9%A1%B5/1/%E8%A7%86%E9%A2%91/%E8%A7%86%E9%A2%91%E6%95%99%E7%A8%8B1114_v2/%E8%A7%86%E9%A2%91%E6%95%99%E7%A8%8B1114_v2/4%20-%20%E5%9C%88%E5%AD%90%E6%8A%A2%E7%BA%A2%E5%8C%85.mp4'
//            ),
//            array(
//                'title' => '如何购买葡萄圈子',
////                'imgUrl' => 'http://a119112.oss-cn-beijing.aliyuncs.com/UI-%E5%A7%9C%E9%AB%98%E5%B0%9A/%E5%9B%BE%E7%89%87%E9%A6%96%E9%A1%B5/1/%E8%A7%86%E9%A2%91/%E8%A7%86%E9%A2%91%E6%95%99%E7%A8%8B1114_v2/%E8%A7%86%E9%A2%91%E6%95%99%E7%A8%8B1114_v2/%E6%96%B0%E6%89%8B%E6%94%BB%E7%95%A5banner%E5%9B%BE-1114/%E5%A6%82%E4%BD%95%E8%B4%AD%E4%B9%B0%E8%91%A1%E8%90%84%E5%9C%88%E5%AD%90.png',
//                'imgUrl' => 'http://cdn01.36qq.com/CDN/%E8%B4%AD%E4%B9%B0%E5%9C%88%E5%AD%90@2x.png',
//                'contentUrl' => 'http://a119112.oss-cn-beijing.aliyuncs.com/UI-%E5%A7%9C%E9%AB%98%E5%B0%9A/%E5%9B%BE%E7%89%87%E9%A6%96%E9%A1%B5/1/%E8%A7%86%E9%A2%91/%E8%A7%86%E9%A2%91%E6%95%99%E7%A8%8B1114_v2/%E8%A7%86%E9%A2%91%E6%95%99%E7%A8%8B1114_v2/6%20-%20%E8%B4%AD%E4%B9%B0%E5%9C%88%E5%AD%90.mp4'
//            ),
//            array(
//                'title' => '如何退出葡萄圈子',
////                'imgUrl' => 'http://a119112.oss-cn-beijing.aliyuncs.com/UI-%E5%A7%9C%E9%AB%98%E5%B0%9A/%E5%9B%BE%E7%89%87%E9%A6%96%E9%A1%B5/1/%E8%A7%86%E9%A2%91/%E8%A7%86%E9%A2%91%E6%95%99%E7%A8%8B1114_v2/%E8%A7%86%E9%A2%91%E6%95%99%E7%A8%8B1114_v2/%E6%96%B0%E6%89%8B%E6%94%BB%E7%95%A5banner%E5%9B%BE-1114/%E5%A6%82%E4%BD%95%E9%80%80%E5%87%BA%E8%91%A1%E8%90%84%E5%9C%88%E5%AD%90.png',
//                'imgUrl' => 'http://cdn01.36qq.com/CDN/%E9%80%80%E5%87%BA%E8%91%A1%E8%90%84%E5%9C%88%E5%AD%90@2x.png',
//                'contentUrl' => 'http://a119112.oss-cn-beijing.aliyuncs.com/UI-%E5%A7%9C%E9%AB%98%E5%B0%9A/%E5%9B%BE%E7%89%87%E9%A6%96%E9%A1%B5/1/%E8%A7%86%E9%A2%91/%E8%A7%86%E9%A2%91%E6%95%99%E7%A8%8B1114_v2/%E8%A7%86%E9%A2%91%E6%95%99%E7%A8%8B1114_v2/5%20-%20%E9%80%80%E5%87%BA%E5%9C%88%E5%AD%90.mp4'
//            )
        );
        return $this->getResponse($arrNewbieList);
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
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
    }
}
