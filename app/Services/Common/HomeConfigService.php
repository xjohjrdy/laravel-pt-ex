<?php


namespace App\Services\Common;


use App\Entitys\App\HomeTopBanner;
use App\Entitys\App\HomeTopCategory;
use App\Entitys\App\HomeTopCategoryChild;
use Illuminate\Support\Facades\Cache;

class HomeConfigService
{
    private $homeCategoryModel;
    private $homeIconsModel;
    private $homeBannersModel;
    private $device;
    private $appversion;
    private $ios_hide_version;
    const HOME_CATEGORY_BANNER = 'app_home_category_banner_config';

    public function __construct($device = null, $appversion = null, $ios_hide_version = null)
    {
        $this->homeCategoryModel = new HomeTopCategory();
        $this->homeIconsModel = new HomeTopCategoryChild();
        $this->homeBannersModel = new HomeTopBanner();
        $this->device = $device;
        $this->appversion = $appversion;
        $this->ios_hide_version = $ios_hide_version;
    }

    /**
     * 获取首页配置
     * @return array
     */
    public function getHomeConfigData()
    {

        $category_list = $this->homeCategoryModel->orderByDesc('sort')->get(['title', 'id', 'sort']);
        $category_list = $category_list->toArray();
        foreach ($category_list as $index => $category) {
            $gets = ['icon', 'icon_type', 'text', 'redirect_type', 'redirect_url', 'extra_params', 'grade', 'hide_flag', 'min_ios_version', 'min_android_version', 'sort', 'index', 'login_flag', 'page_params'];
            $icons = $this->homeIconsModel->where(['category_id' => $category['id'], 'show_flag' => 1])->orderByDesc('sort')->get($gets);
            $category_list[$index]['sub_categorys'] = @$icons->toArray();
        }
        $gets = ['image_url', 'title', 'redirect_type', 'redirect_url', 'extra_params', 'grade', 'hide_flag', 'min_ios_version', 'min_android_version', 'sort', 'index', 'login_flag', 'page_params'];
        $banners = $this->homeBannersModel->where(['show_flag' => 1])->orderByDesc('sort')->get($gets);
        $data = [
            'category_list' => $category_list,
            'banners' => @$banners->toArray()
        ];
        return $data;
    }

    /**
     * 获取首页配置缓存
     * @return array
     */
    public function getHomeConfigCache()
    {
        $data = Cache::get(static::HOME_CATEGORY_BANNER);
        if (empty($data)) {
            $data = $this->getHomeConfigData();
            Cache::put(static::HOME_CATEGORY_BANNER, $data);
        }
        return $data;
    }


    /**
     * 处理单个item
     * @param $sub_category
     * @return bool
     */
    public function collateItem(&$sub_category)
    {


        if ($this->device == 'ios') {

            //低于此版本不可见 ,或者 特定版本号不可见
            if (version_compare($this->appversion, $sub_category['min_ios_version'], '<')) {
                return false;
            }

            //特殊图标获取需要隐藏的版本
            if ($sub_category['hide_flag'] == 1 && $this->appversion == $this->ios_hide_version) {
                return false;
            }

        } elseif ($this->device == 'android') {

            //低于此版本不可见
            if ($this->appversion < $sub_category['min_android_version']) {
                return false;
            }

        }

        if (empty($sub_category['extra_params'])) {
            unset($sub_category['extra_params']);
        }

        if (empty($sub_category['grade'])) {
            unset($sub_category['grade']);
        }

        if (!empty($sub_category['page_params'])) {
            $sub_category['page_params'] = json_decode($sub_category['page_params']);
        }

        unset($sub_category['hide_flag']);
        unset($sub_category['min_ios_version']);
        unset($sub_category['min_android_version']);

        return true;

    }
}