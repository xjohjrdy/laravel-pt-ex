<?php


namespace App\Services\Common;


use App\Entitys\App\AppBanner;

class AppBannerService
{
    const MY_CENTER = 1; // 我的-个人中心页面banner
    private $page;
    private $bannerModel;
    public function __construct($page)
    {
        $this->page = $page;
        $this->bannerModel = new AppBanner();
    }

    public function getBanners(){
        $gets = ['image_url', 'title', 'redirect_type', 'redirect_url', 'extra_params', 'hide_flag', 'min_ios_version', 'min_android_version', 'index', 'login_flag', 'page_params'];
        $banners = $this->bannerModel->where(['page' => $this->page, 'show_flag' => 1])->orderByDesc('sort')->get($gets);
        $banners = $banners->toArray();
        foreach ($banners as $key=> $item){
            $banners[$key]['page_params'] = json_decode($item['page_params']);
        }
        return $banners;
    }
}