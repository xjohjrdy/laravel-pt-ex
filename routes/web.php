<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return 'Hello World';
});

Route::get('huipengtokencheck', function () {
    return 'dys7iweuasjduyw83628jcuasd';
});

Route::get('medical_show_regular', 'Medical\CanController@getRegular');
Route::get('medical_show_index_regular', 'Medical\CanController@getIndexRegular');
Route::get('medical_show_can_know', 'Medical\CanController@getCanKnow');
Route::get('medical_show_booking_know', 'Medical\CanController@getBookingKnow');

Route::post('share_web_for_article', 'App\ShareController@getUserShareInfo');


Route::get('article_show_rule', 'News\ShowController@getRule');
Route::get('article_one_new_show_rule', 'News\ShowController@getTeamRule');

Route::get('jd_show_rule', 'Jd\ShowController@getJdRule');
Route::get('jd_team_show_rule', 'Jd\ShowController@getJdTeamRule');


Route::get('pdd_show_rule', 'Pdd\ShowController@getJdRule');
Route::get('pdd_team_show_rule', 'Pdd\ShowController@getJdTeamRule');

Route::get('alimama_show_regular', 'Alimama\ShowController@getRegular');
Route::get('alimama_show_order', 'Alimama\ShowController@getOrderRegular');
Route::get('alimama_show_rule', 'Alimama\ShowController@getRule');
Route::get('alimama_show_video', 'Alimama\ShowController@getVideoUrl');
Route::get('test_redis_websocket', 'Test\TestController@testRedisWebsocket');
Route::get('getReward/{id}', 'Article\AdvertisementController@webUpdate');
Route::any('encrypt_code', 'Common\IndexController@encryptCode');
Route::get('get_time', function () {
    return time();
});


Route::get('ttcc', function () {
});


Route::get('suning_one_go_go', 'Web\SuningController@getIndex');

Route::get('suning_go_by_wu', 'Common\DisplayController@getSuNingUrlByWu');

Route::get('getRechargeNotice', 'Recharge\DisplayController@getNotice');

Route::get('getRecharge', 'Recharge\DisplayController@getRecharge');

Route::get('getArticleType', 'Article\DisplayController@getType');
Route::get('show_shop_display', 'Shop\DisplayController@getIndexDisplay');

Route::get('getRegulation', 'Index\ActiveController@getRegulation');
Route::get('getSign', 'Pay\AliPayController@getSign');
Route::get('notify_url', 'Pay\RechargeAliPayController@callBackForAli');
Route::post('notify_url', 'Pay\RechargeAliPayController@callBackForAli');
Route::get('notify_url_v1', 'Pay\RechargeAliPayController@callBackForAliV1'); // 阿里支付回调
Route::post('notify_url_v1', 'Pay\RechargeAliPayController@callBackForAliV1'); // 阿里支付回调
Route::get('need_buy_back', 'Back\BuyBackController@showBuyBack');
Route::post('buy_back', 'Back\BuyBackController@buyBack');
Route::get('need_buy_back_assets_function', function () {
    return '即将上线我的资产功能，敬请期待';
});


Route::get('function_article_show', function () {
    return '<h1>春节期间升级葡萄头条功能，节后再使用头条哦！</h1>';
});
Route::get('get_count_test_ip', 'Test\TestController@TestCount');
Route::get('lock_rule', 'Back\DisplayController@getRuleForLock');
Route::get('money_rule', 'Back\DisplayController@getRuleForMoney');
Route::get('rollback_rule', 'Back\DisplayController@getRuleForRollback');
Route::get('get_all_five_real', 'Test\TestController@getAllFive');
Route::get('phpinfo_test_wuhang_wenwanbin', 'Test\TestController@testFunction');
Route::any('get_sign_image', 'Common\DisplayController@getSignImage');
Route::any('get_index_image', 'Common\DisplayController@getIndexImage');
Route::any('get_today_shop', 'Common\DisplayController@getTodayShop');
Route::any('get_ali_code', 'Common\DisplayController@getAliCode');
Route::any('get_recharge_info_answer', 'Common\DisplayController@getRechargeInfoAnswer');
Route::get('refresh_shop_express', 'Shop\DisplayController@refreshExpress');
Route::get('show_return_info', 'Shop\DisplayController@showReturnInfo');
Route::get('get_common_time', 'Common\DisplayController@getCommonTime');
Route::post('new_tao_get_index', 'Alimama\NewController@getIndex');
Route::post('new_tao_get_buying', 'Alimama\NewController@getBuying');
Route::post('new_tao_big_search', 'Alimama\NewController@getBigSearch');
Route::any('get_web_shop_detail/{good_id}/{invite_app_id}', 'WebShop\GoodsController@getGoodsDetail');
Route::any('generate_order/{good_id}/{invite_app_id}', 'WebShop\GoodsController@generateOrder');
Route::any('check_user_login/{good_id}/{invite_app_id}', 'WebShop\GoodsController@checkUserLogin');
Route::any('get_order_info/{good_id}/{invite_app_id}/{order_id}', 'WebShop\GoodsController@getOrderInfo');
Route::any('add_address/{good_id}/{invite_app_id}/{order_id}', 'WebShop\GoodsController@addAddress');
Route::any('use_money/{order_id}', 'WebShop\GoodsController@useMoney');
Route::any('send_code', 'WebShop\GoodsController@sendSms');
Route::any('article_resolve/{id}', 'Article\DisplayController@resolveArticle');
Route::any('notify_url_voip_buy', 'Voip\RechargeController@aliCallBack');
Route::get('voip_callback', 'Voip\IndexController@callback');
Route::any('is_need_movie/{app_id}', 'Voip\RechargeController@IsNeedMovie');
Route::any('get_show_buy_page/{group_id}', 'Common\DisplayController@getShowBuyPage');
Route::any('display_news/{id}', 'Common\DisplayController@displayNews');
Route::get('getConfirmGoodsInfo', 'Shop\GoodsController@getInfoMessage');
Route::any('jd_shop', 'Jd\GetController@jdShopWeb');


Route::any('app_service', 'Market\WebController@serviceByWeb');

Route::any('jd_active', 'Web\JdController@index');
Route::any('jd_activity', 'Web\JdController@jdActivity');
Route::any('jd_invite', 'Web\JdController@jdInvite');
Route::post('xin_jd_submit_phone', 'Web\JdController@submitPhone');

Route::get('activity_count', 'Web\JdController@activityCount');

Route::get('taobao_authorisation', 'Alimama\VegasController@authorisation');
Route::get('taobao_authorisation_all', 'Alimama\VegasControllerAll@authorisation');
Route::get('taobao_authorisation_all_rand', 'Alimama\VegasControllerAllRand@authorisation');
Route::get('taobao_authorisation_vip', 'UpgradeVip\VegasVipController@authorisation');

Route::group(['middleware' => ['admin']], function () {
    Route::post('admin_goods_supplier', 'Admin\GoodsController@getNeedShow');
    Route::post('admin_goods_supplier_goods', 'Admin\GoodsController@getSupplierGoods');
    Route::post('admin_push_supplier_goods', 'Admin\GoodsController@pushSupplierGoods');
    Route::post('admin_check_supplier_goods', 'Admin\GoodsController@checkSupplierGoods');
    Route::post('jd_push_user_phone', 'Jd\PhoneController@pushPhone');
    Route::post('jd_get_user_phone', 'Jd\PhoneController@getPhone');


    Route::post('admin_by_wuhang_in_wechat_get', 'App\InController@getWechatIn');
});
Route::get('agent_login', 'AgentWeb\LoginController@loginWeb');

Route::get('agent_logout', 'AgentWeb\LoginController@logout');

Route::post('agent_login', 'AgentWeb\LoginController@login');

Route::post('agent_send_sms', 'AgentWeb\LoginController@sendCode');
Route::group(['middleware' => ['agent_admin']], function () {
    Route::get('agent_admin', 'AgentWeb\AdminController@index');
});
Route::group(['middleware' => ['agent_lose']], function () {

    Route::get('agent_main', 'AgentWeb\AdminController@main');

    Route::get('agent_center_online', 'AgentWeb\AdminController@centerOnline');

    Route::get('agent_center_check', 'AgentWeb\AdminController@centerCheck');

    Route::any('agent_center_check_add_html', 'AgentWeb\AdminController@centerCheckAddHtml');

    Route::any('agent_center_check_ed_html', 'AgentWeb\AdminController@centerCheckEdHtml');

    Route::any('agent_center_check_de_html', 'AgentWeb\AdminController@centerCheckDeHtml');

    Route::get('agent_system_params', 'AgentWeb\JsonController@systemParameter');

    Route::get('agent_json_online_goods', 'AgentWeb\JsonController@onlineGoods');

    Route::get('agent_json_check_goods', 'AgentWeb\JsonController@checkGoods');
	
	Route::get('agent_center_educe', 'AgentWeb\AdminController@centerEduce');

	Route::get('agent_export_excel_goods', 'AgentWeb\AdminController@exportExcelGoods');
});

Route::get('agent_404', function () {
    return view('agent.404');
});



















