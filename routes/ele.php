<?php

/**
 * ele后台路由
 */

//Route::post('login', 'EleAdmin\LoginController@login');
Route::post('captcha', 'EleAdmin\LoginController@captcha');
Route::get('export-to-csv-test', 'EleAdmin\ExcelTestController@testExportExcelToCsv');

//上传文件
Route::post('upload-file', 'Common\UploadController@upload');

Route::group(['middleware' => 'ele_admin_check'], function () {
    //权限模块
    Route::post('role', 'EleAdmin\Authority\AdminController@getRoles');
    Route::get('info', 'EleAdmin\Authority\AdminController@getInfo');
    Route::get('log-out', 'EleAdmin\Authority\AdminController@logOut');

    Route::get('admin-list', 'EleAdmin\Authority\AdminController@lists');
    Route::post('admin-add', 'EleAdmin\Authority\AdminController@add');
    Route::post('admin-edit', 'EleAdmin\Authority\AdminController@edit');

    Route::get('get-roles', 'EleAdmin\Authority\RoleController@getRoles');
    Route::get('role-list', 'EleAdmin\Authority\RoleController@lists');
    Route::post('role-add', 'EleAdmin\Authority\RoleController@add');
    Route::post('role-edit', 'EleAdmin\Authority\RoleController@edit');
    Route::post('get-role-menus', 'EleAdmin\Authority\RoleController@getMenuIds');
    Route::post('set-role-menus', 'EleAdmin\Authority\RoleController@setMenus');

    Route::get('menu-list', 'EleAdmin\Authority\MenuController@lists');
    Route::post('menu-add', 'EleAdmin\Authority\MenuController@add');
    Route::post('menu-edit', 'EleAdmin\Authority\MenuController@edit');
    Route::get('get-menu-tree', 'EleAdmin\Authority\MenuController@getMenuTree');
    Route::get('get-menus', 'EleAdmin\Authority\MenuController@getMenus');

    //直播模块
    Route::get('live-list', 'EleAdmin\Live\LiveInfoController@lists');
    Route::post('create-notice', 'EleAdmin\Live\LiveInfoController@createNotice');
    Route::post('live-start', 'EleAdmin\Live\LiveInfoController@liveStart');
    Route::post('live-end', 'EleAdmin\Live\LiveInfoController@liveEnd');
    Route::post('live-member-list', 'EleAdmin\Live\LiveInfoController@getMembers');
    Route::post('live-member-forbid', 'EleAdmin\Live\LiveInfoController@forbidMember');

    Route::post('live-shop-good-list', 'EleAdmin\Live\LiveShopGoodController@lists');
    Route::get('get-shop-goods', 'EleAdmin\Live\ShopGoodController@getList');

    //拉新模块
    //拉新排行榜
    Route::get('put-new-rank-list', 'EleAdmin\PutNew\RankController@lists');
    Route::post('put-new-rank-add', 'EleAdmin\PutNew\RankController@add');
    Route::post('put-new-rank-edit', 'EleAdmin\PutNew\RankController@edit');
    //拉新配置
    Route::get('put-new-background-config', 'EleAdmin\PutNew\BackgroundConfigController@get');
    Route::post('put-new-background-config-edit', 'EleAdmin\PutNew\BackgroundConfigController@edit');
    //邀请播报栏
    Route::get('put-new-faker-list', 'EleAdmin\PutNew\FakerController@lists');
    Route::post('put-new-faker-add', 'EleAdmin\PutNew\FakerController@add');
    Route::post('put-new-faker-edit', 'EleAdmin\PutNew\FakerController@edit');
    //奖品配置
    Route::get('put-new-reward-list', 'EleAdmin\PutNew\RewardController@lists');
    Route::post('put-new-reward-edit', 'EleAdmin\PutNew\RewardController@edit');

    //工具模块
    //图片上传
    Route::post('tool-img-upload', 'EleAdmin\Tool\ImgUploadController@upload');
    Route::get('tool-cmp-types', 'EleAdmin\Tool\ImgUploadController@getCmpTypeTextList');
    Route::get('tool-img-upload-log-list', 'EleAdmin\Tool\ImgUploadController@logs');
});

Route::post('withdraw_batch', 'EleAdmin\WithdrawController@batchSuccess'); // 获取提现列表
Route::post('login', 'EleAdmin\IndexController@login');
Route::post('login_no_code', 'EleAdmin\IndexController@loginNoCode');
Route::post('send_sms', 'EleAdmin\IndexController@sendLoginSMS');
Route::group(['middleware' => ['web_other']], function () {
//    Route::get('info', 'EleAdmin\IndexController@info');
//    Route::get('log_out', 'EleAdmin\IndexController@logOut');
    Route::get('withdraw', 'EleAdmin\WithdrawController@getUserWithdrawList'); // 获取提现列表
    Route::post('withdraw_reject', 'EleAdmin\WithdrawController@withdrawReject'); // 获取提现列表
    Route::post('withdraw_agree', 'EleAdmin\WithdrawController@agreeLog'); // 获取提现列表
    Route::post('import_withdraw_harry', 'EleAdmin\WithdrawController@importDataAndWithdraw2Harry'); // 导入并打款
    Route::get('export_withdraw', 'EleAdmin\WithdrawController@exportWithdrawList'); // 导出
    Route::get('export_apply_for_page', 'EleAdmin\WithdrawController@exportForPage'); // 导出

    Route::get('user_list', 'EleAdmin\UserController@getList'); // 获取提现列表
    Route::post('user_add', 'EleAdmin\UserController@add');
    Route::post('user_edit', 'EleAdmin\UserController@edit');
    //微信群助手
    Route::get('customer/assistant_list', 'EleAdmin\Audit\WechatAssistant@getList');
    Route::post('customer/assistant_audit', 'EleAdmin\Audit\WechatAssistant@audit');
    Route::post('customer/assistant_batch_audit', 'EleAdmin\Audit\WechatAssistant@batchAudit');
    // APP首页配置
    Route::get('app_config/home_category_list', 'EleAdmin\AppConfig\HomeController@getCategoryList');
    Route::get('app_config/home_category_list', 'EleAdmin\AppConfig\HomeController@getCategoryList');
    Route::get('app_config/home_sub_category_list', 'EleAdmin\AppConfig\HomeController@getSubCategoryList');
    Route::get('app_config/home_banner_list', 'EleAdmin\AppConfig\HomeController@getBannerList');
    Route::post('app_config/operate_sub_category', 'EleAdmin\AppConfig\HomeController@operateSubCategory');
    Route::post('app_config/operate_banner', 'EleAdmin\AppConfig\HomeController@operateBanners');
    Route::post('app_config/update_category', 'EleAdmin\AppConfig\HomeController@updateCategory');
    Route::post('app_config/push_home_config', 'EleAdmin\AppConfig\HomeController@pushConfig');
    Route::post('app_config/del_icon', 'EleAdmin\AppConfig\HomeController@delIcon');
    Route::post('app_config/del_banner', 'EleAdmin\AppConfig\HomeController@delBanner');

    Route::get('config/banner/list', 'EleAdmin\AppConfig\AppBannerController@getList');
    Route::post('config/banner/operate', 'EleAdmin\AppConfig\AppBannerController@operate');
    Route::post('config/banner/del', 'EleAdmin\AppConfig\AppBannerController@del');

    Route::get('config/page/list', 'EleAdmin\AppConfig\AppPageController@getList');
    Route::post('config/page/operate', 'EleAdmin\AppConfig\AppPageController@operate');
    Route::post('config/page/del', 'EleAdmin\AppConfig\AppPageController@del');

    Route::get('config/alert/get', 'EleAdmin\AppConfig\AppAlertController@getFirst');
    Route::post('config/alert/operate', 'EleAdmin\AppConfig\AppAlertController@operate');
    Route::post('config/alert/del', 'EleAdmin\AppConfig\AppAlertController@del');

    Route::post('money/import/send', 'EleAdmin\SendMoneyController@importSendMoney'); // 导入并打款
    Route::get('money/change/list', 'EleAdmin\SendMoneyController@getList');
    // 财务报表
    Route::post('work_done_by_hand', 'EleAdmin\CensusController@getList');
    // 金币中心
    Route::get('coin/task/list', 'EleAdmin\CoinPlate\TaskController@getList');
    Route::post('coin/task/operate', 'EleAdmin\CoinPlate\TaskController@operate');
    Route::post('coin/task/del', 'EleAdmin\CoinPlate\TaskController@del');
    Route::post('coin/task/del_cache', 'EleAdmin\CoinPlate\TaskController@delCache');

    Route::get('coin/menu/list', 'EleAdmin\CoinPlate\MenuController@getList');
    Route::post('coin/menu/operate', 'EleAdmin\CoinPlate\MenuController@operate');
    Route::post('coin/menu/del', 'EleAdmin\CoinPlate\MenuController@del');

    Route::get('coin/prize/list', 'EleAdmin\CoinPlate\PrizeController@getList');
    Route::post('coin/prize/operate', 'EleAdmin\CoinPlate\PrizeController@operate');
    Route::post('coin/prize/del', 'EleAdmin\CoinPlate\PrizeController@del');
    Route::get('coin/prize/log/get', 'EleAdmin\CoinPlate\PrizeController@getUserLogList');
    Route::get('coin/prize/log/category', 'EleAdmin\CoinPlate\PrizeController@getPriceCategory');

    Route::get('coin/turntable/list', 'EleAdmin\CoinPlate\TurntableController@getList');
    Route::post('coin/turntable/operate', 'EleAdmin\CoinPlate\TurntableController@operate');
    Route::post('coin/turntable/del', 'EleAdmin\CoinPlate\TurntableController@del');

    Route::get('coin/shop/list', 'EleAdmin\CoinPlate\ShopController@getList');
    Route::post('coin/shop/operate', 'EleAdmin\CoinPlate\ShopController@operate');
    Route::post('coin/shop/del', 'EleAdmin\CoinPlate\ShopController@del');
    Route::post('coin/shop/upload', 'EleAdmin\CoinPlate\ShopController@upload');
    Route::get('coin/shop/get', 'EleAdmin\CoinPlate\ShopController@getById');

    Route::get('coin/prize/order/list', 'EleAdmin\CoinPlate\PrizeOrderController@getList');
    Route::post('coin/prize/order/operate', 'EleAdmin\CoinPlate\PrizeOrderController@operate');
    Route::post('coin/prize/order/del', 'EleAdmin\CoinPlate\PrizeOrderController@del');
    Route::get('coin/prize/order/get', 'EleAdmin\CoinPlate\PrizeOrderController@getById');
    Route::get('export-prize-order-for-page', 'EleAdmin\CoinPlate\PrizeOrderController@exportForPage');
    //金币订单
    Route::get('coin/orders/get', 'EleAdmin\CoinPlate\OrdersController@getList');
    Route::get('coin/orders/push', 'EleAdmin\CoinPlate\OrdersController@push');
    Route::get('coin/orders/refund', 'EleAdmin\CoinPlate\OrdersController@refund');
    Route::post('coin/orders/operate', 'EleAdmin\CoinPlate\OrdersController@operate');
    Route::get('export-coin-order-for-page', 'EleAdmin\CoinPlate\OrdersController@exportForPage');

    //意见反馈
    Route::get('audit/feedback/list', 'EleAdmin\Audit\FeedbackController@getFeedbackList');
    Route::post('audit/feedback/send', 'EleAdmin\Audit\FeedbackController@sendReply');
});
Route::post('work_done_by_hand', 'EleAdmin\CensusController@getList');