<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('put_new_moneys', 'CzhTest\TestController@getRangeMoney');//升级配置
Route::get('put_new_ranks', 'CzhTest\TestController@getInviteGift');//首页弹窗配置

Route::get('get_shop_video_url', 'Xin\PutaoHomeController@getShopVideoUrl');//得到购物报销教程视频url
Route::get('advertising_id_issue', 'Xin\PutaoHomeController@advertisingIdIssue');//广告id随机下发
Route::post('app_alert', 'App\AlertController@getUserShareInfo');//首页弹窗配置

Route::post('app_alert_info', 'App\AlertController@isUpgrade');//升级配置
Route::post('app_alert_ip', 'App\AlertController@getIp');//首页弹窗配置

Route::post('order_refund_minus', 'Common\PlusUserMoney@minusMoneyByRefundOrder'); // 确认收货退款订单分佣扣除
Route::post('harry_do_withdraw_bank', 'Other\GongMallController@harryWithdraw2Bank'); // 众薪提现银行卡
Route::post('harry_do_withdraw', 'Other\GongMallController@harryWithdraw'); // 众薪提现
Route::post('harry_withdraw_callback', 'Other\GongMallController@harryWithdrawCallback'); //众薪提现回调
Route::post('harry_withdraw_callback_ele', 'EleAdmin\WithdrawController@harryWithdrawCallback'); //众薪提现回调

Route::post('he_meng_tong_pay_call_back', 'HeMengTong\HeMeToController@heMeToPayCallBack');//禾盟通支付回调
Route::post('he_meng_tong_order_refund', 'HeMengTong\HeMeToController@orderRefund');//禾盟通订单退款
Route::post('he_meng_tong_circle_send_call_back', 'HeMengTong\HeMeToController@heMeToCircleSendCallBack');//禾盟通支付 圈子发红包回调
Route::post('he_meng_tong_circle_buy_call_back', 'HeMengTong\HeMeToController@heMeToCircleBuyCallBack');//禾盟通支付 圈子购买回调
Route::post('he_meng_tong_circle_join_call_back', 'HeMengTong\HeMeToController@heMeToCircleJoinCallBack');//禾盟通支付 圈子加入回调

Route::post('for_user_plus_money', 'Common\PlusUserMoney@plusMoney'); //
Route::post('for_user_minus_money', 'Common\PlusUserMoney@minusMoney'); //
Route::post('for_user_minus_money_list', 'Common\PlusUserMoney@minusMoneyList'); //
Route::post('ev_identify_validate_test', 'Other\GongMallController@validateIdentify'); // 电签验证
Route::post('ev_identify_callback', 'Other\GongMallController@callBack'); // 电签回调
Route::post('ev_do_withdraw', 'Other\GongMallController@doWithdraw'); // 提现
Route::post('ev_get_withdraw', 'Other\GongMallController@getWithDrawList'); //
Route::post('ev_get_company_balance', 'Other\GongMallController@getCompanyBalance'); //
Route::post('ev_get_taxInfo', 'Other\GongMallController@getTaxInfo'); //
Route::post('ev_withdraw_callback', 'Other\GongMallController@withdrawCallback'); //


Route::get('mini_hjk_goods_list', 'Jd\GetController@getHaoJinKeGoodsList'); //haojingke商品列表
Route::any('wechat_pay_for_test', 'Pay\WechatController@index');
Route::any('wechat_pay_for_notify', 'Pay\WechatController@notify');
Route::any('voip_wechat_pay_now_wuhang', 'Voip\RechargeController@wechatCallBack');
Route::any('shop_wechat_pay_now_wuhang', 'Pay\RechargeAliPayController@callBackWechatPay');
Route::any('shop_wechat_pay_now_wuhang_v1', 'Pay\RechargeAliPayController@callBackWechatPayV1');
Route::resource('article', 'Index\ArticleController');
Route::resource('newbies', 'Newbies\IndexController');
Route::resource('gathersingle', 'Article\GatherSingleController');
Route::resource('gatherarticle', 'Article\GatherController');
Route::resource('tbkgetitem', 'Taobaoke\GetItemController');
Route::resource('aligetdgitem', 'Alimama\GetDgItemController');
Route::resource('special_good_taobaoke', 'Taobaoke\SpecialGoodController');
Route::get('circle_ring_number_need_app_id', 'Circle\CommonController@getCommonNumber');
Route::get('circle_ring_number_need_circle_id', 'Circle\CommonController@getCircleNumber');
Route::post('admin_today_money', 'Admin\IndexController@getListAdmin');
Route::post('put_check_verify_test', 'Index\VerifyController@put');
Route::post('new_jd_get_type', 'Jd\NewController@getType');
Route::post('new_jd_get_list', 'Jd\NewController@getList');
Route::post('new_jd_get_change_url', 'Jd\NewController@changeUrl');
Route::post('new_jd_get_check_orders', 'Jd\NewController@checkOrders');
Route::post('new_jd_get_orders', 'Jd\NewController@getOrders');
Route::post('new_jd_get_old_orders', 'Jd\NewController@getOldOrders');
Route::post('alimama_my_rank', 'Alimama\IndexController@getRank');
Route::post('new_zero_buy_alimama_index', 'Alimama\ManyController@zeroIndex');
Route::post('new_zero_buy_alimama_change', 'Alimama\ManyController@zeroChange');
Route::any('error_any_write_for_test', 'Test\TestController@getArr');

Route::post('one_go_category', 'OneGo\OnGoController@getCategoryList');
Route::post('one_go_goods_list', 'OneGo\OnGoController@getGoodsListFromCid');
Route::post('one_go_union_uri', 'OneGo\OnGoController@getUnionUrlApi');

Route::get('ali_big_push_smart_url_change', 'Alimama\ManyController@getRushChangeUrl');
Route::get('ali_my_push_smart_url_change', 'Alimama\ManyController@getMyWuChangeUrl');
Route::get('ali_my_push_new_url_change', 'Alimama\ManyController@getMyWuChangeUrlNew');

Route::get('voip_index_img', 'Voip\ShowController@getVoipIndexShow');

Route::post('new_many_alimama_index', 'Alimama\ManyController@getIndex');
Route::get('taobao_authorisation_zero', 'Alimama\VegasOneGoController@authorisation');

Route::post('uc_token_notify', 'News\UCNewsController@NotifyUCTokenUpdate'); //UC token回调获取接口不加密

Route::post('one_go_orders', 'OneGo\OnGoController@getOrders');
Route::post('one_go_check_order', 'OneGo\OnGoController@checkOrders');
Route::post('ali_dtk_search', 'Alimama\ManyController@getDtkSearch');//超级报销
Route::post('vip_shop_count', 'Other\ShopCommissionController@vipShopCommission');
Route::post('general_shop_count', 'Other\ShopCommissionController@generalShopCommission');
Route::get('ali_get_activity_link', 'Alimama\ManyController@getActivityLink');//淘宝客 推广者 官方活动转链
Route::post('ali_ele_more_count', 'Other\ShopCommissionController@eleMoreCommission');//饿了么统计
Route::post('ali_ele_more_count_add_money', 'Other\ShopCommissionController@eleMoreCommissionAddMoney');//饿了么假变真

Route::post('card_order_count', 'Other\CardCommissionController@otherCardCommission');
Route::post('tb_un_count', 'Other\TaoBaoCommissionController@delCountOrder');
Route::post('tb_order_count', 'Other\TaoBaoCommissionController@vipShopCommission');
Route::post('jd_un_count', 'Other\JdCommissionController@delCountOrder');
Route::post('j_order_count', 'Other\JdCommissionController@vipShopCommission');
Route::post('p_order_count', 'Other\PddCommissionController@vipShopCommission');
Route::post('pdd_un_count', 'Other\PddCommissionController@delCountOrder');
Route::post('get_info_circle_count', 'Other\CircleCommissionController@getInfoCircleCommission');//加入圈子多级报销
Route::post('buy_circle_count', 'Other\CircleCommissionController@buyCircleCommission');//团队会员购买圈子多级
Route::post('bidding_circle_count', 'Other\CircleCommissionController@biddingCircleCommission');//团队会员竞价圈子多级

Route::post('coin_shop_wh_area_no', 'Coin\ShopController@area');//运费接口
Route::post('material_friends_hidden', 'Material\FriendController@getPublic');

Route::resource('no_a_shop_member_cny', 'Shop\MemberCnyController');

Route::post('new_tao_get_prediction_log_no_c', 'Alimama\GetController@getPredictionLog');

Route::group(['middleware' => ['data']], function () {
	
	Route::post('task_browse_good', 'Coin\TaskTriggerController@TaskBrowseGood'); //浏览商品触发任务
	Route::post('task_watch_video', 'Coin\TaskTriggerController@TaskWatchVideo'); //观看福利视频触发任务
	Route::post('task_new_watch_strategy', 'Coin\TaskTriggerController@TaskNewWatchStrategy'); //观看新手攻略触发任务
	Route::post('task_new_share_hair_ring', 'Coin\TaskTriggerController@TaskNewShareHairRing'); //分享发圈触发任务

//获取首页信息
    Route::post('pull_index_get_index', 'Pull\IndexController@getIndex');
//获取首页弹窗校验接口
    Route::post('pull_index_get_jump', 'Pull\IndexController@getJump');
//瓜分点击
    Route::post('pull_index_carve_up', 'Pull\IndexController@carveUp');
//我的奖品
    Route::post('pull_index_get_reward', 'Pull\IndexController@getReward');
//奖品详情
    Route::post('pull_index_reward_detail', 'Pull\IndexController@rewardDetail');
//立即兑奖
    Route::post('pull_index_push_reward', 'Pull\IndexController@pushReward');
//我的邀请
    Route::post('pull_index_my_invitation', 'Pull\IndexController@myInvitation');


    Route::post('user_get_end_live', 'Live\PlayOverController@userGetEndLive'); //用户读取结束直播数据
	Route::post('open_live', 'Live\PlayOverController@openLive'); //开启直播
    Route::post('get_sign_or_sdkappid', 'Live\PlayOverController@getSignOrSdkappid'); //获取签名&sdkAppID
    Route::post('add_live_number', 'Live\PlayOverController@addLiveNumber'); //增加直播人数
    Route::post('end_live', 'Live\PlayOverController@endLive'); //结束直播
    Route::post('get_push_url', 'Live\PlayOverController@getPushUrl'); //得到推流地址
	Route::post('get_anchor_live_data', 'Live\PlayOverController@getAnchorLiveData'); //用户拉取直播数据
	Route::post('get_plan_live_data', 'Live\PlayOverController@getPlanLiveData'); //用户拉取预告直播数据
	Route::post('subscribe_plan_live', 'Live\PlayOverController@subscribePlanLive'); //用户订阅直播

	Route::get('live_goods_list', 'Live\LiveGoodsController@liveGoodsList'); //
	Route::get('live_goods_count', 'Live\LiveGoodsController@liveGoodsCount'); //
	Route::get('live_explain_goods', 'Live\LiveGoodsController@explainGoodsInfo'); //
	Route::post('live_set_explain', 'Live\LiveGoodsController@setExplainGoods'); //

    Route::post('comment_msg_all', 'Comment\CommentController@msgAll');//消息总量
    Route::post('comment_send_msg', 'Comment\CommentController@sendMsg');//发送意见(尽量兼容小程序)
    Route::post('comment_msg_list', 'Comment\CommentController@msgList');//消息总量
    Route::post('comment_msg_reply', 'Comment\CommentController@msgReply');//拉出反馈的具体信息
    Route::post('comment_send_reply', 'Comment\CommentController@sendReply');//发送回复
    Route::post('comment_end', 'Comment\CommentController@end');//已解决

	Route::post('xin_get_my_idol_info', 'Xin\IdolController@getMyIdolInfo'); // 我的偶像

	Route::get('get_page_banner', 'App\BannerController@getBannerByPage');// banner获取
//金币中心商城系列

    Route::post('coin_shop_wh_log', 'Coin\ShopController@getLog');//列表日志兑换
    Route::post('coin_shop_wh_is_buy', 'Coin\ShopController@isBuy');//是否可以购买

    Route::post('coin_shop_wh_goods', 'Coin\ShopController@goods');//商品列表
    Route::post('coin_shop_wh_details', 'Coin\ShopController@details');//商品详情
    Route::post('coin_shop_wh_area', 'Coin\ShopController@area');//运费接口
    Route::post('coin_shop_wh_pay', 'Coin\ShopController@pay');//支付接口
    Route::post('coin_shop_wh_address', 'Coin\ShopController@getDefaultAddress');//支付接口


	Route::get('coin_main_info', 'Coin\MainController@mainInfo');//金币首页接口
	Route::get('coin_change_list', 'Coin\MainController@getCoinChangeHistory');// 金币变动明细
	Route::post('coin_success_task', 'Coin\MainController@successTask');// 完成任务
	Route::post('get_haiwei_index_url', 'HaiWei\HaiWeiController@index');//得到海威首页url
	Route::post('get_haiwei_my_order_url', 'HaiWei\HaiWeiController@myOrder');//得到海威我的订单url
	
	Route::post('get_kfc_login_v3_url', 'Kfc\KfcController@getLoginV3');//得到肯德基有手机号码登陆url
	Route::post('get_kfc_login_v2_url', 'Kfc\KfcController@getLoginV2');//得到肯德基没有手机号码登陆url

    //提现用户相关类
    Route::post('cash_user_default', 'Cash\UserController@getUserDefault');
    Route::post('cash_user_all', 'Cash\UserController@getUserAll');
    Route::post('cash_user_add_or_change', 'Cash\UserController@addOrChange');
    Route::post('cash_user_del', 'Cash\UserController@delUser');
    Route::post('new_cash_push_add', 'Cash\JumpController@addLog');

    //客户端版
    Route::post('local_harry_agreement_put', 'Harry\agreeMentController@push');//签约页面接口
    Route::post('local_harry_t_is_call', 'Harry\agreeMentController@checkIsCall');//是否签约接口

    // 早安打卡
    Route::post('morning_index_data', 'Morning\IndexController@indexData'); // 早安打卡首页初始数据
    Route::get('morning_schemes_list', 'Morning\IndexController@schemesList'); // 报名方案列表
    Route::post('morning_user_apply', 'Morning\IndexController@apply'); // 用户报名参与
    Route::post('morning_user_sign', 'Morning\IndexController@sign'); // 用户打卡
    Route::get('morning_user_records', 'Morning\IndexController@records'); // 用户打卡历史记录
    Route::get('morning_user_info', 'Morning\IndexController@userMainInfo'); // 用户总记录

    Route::post('mt_get_maid_order', 'MeiTuan\IndexController@getMtMaidOrder');//得到美团报销订单
    Route::post('mt_get_directly_maid_order', 'MeiTuan\IndexController@getDirectlyMtMaidOrder');//得到美团直属下级报销订单
    Route::post('mt_get_yes_rebate_url', 'MeiTuan\IndexController@getYesRebateUrl'); //得到美团有二级分佣的投放链接

    Route::post('ev_identify_validate', 'Other\GongMallController@validateIdentify'); // 电签验证
    Route::post('ev_check_validate', 'Other\GongMallController@checkValidate'); // 查询是否已验证
    Route::post('material_friends', 'Material\FriendController@getList');


    //新增
    Route::get('ptb_change_log', 'Index\HasReadController@changeLog');
    Route::get('ptb_change_status', 'Index\HasReadController@index');

    Route::get('shop_show_display_cny', 'Shop\DisplayCnyController@showRsa');
    Route::resource('shop_member_cny', 'Shop\MemberCnyController');
    Route::resource('circle_ring_maid_cny', 'Circle\MaidCnyController');

    //注销
    Route::post('app_sign_delete_user', 'App\SignController@deleteUser');

    //广告包
    Route::get('ad_package_list', 'Ad\PackageController@getList');
    Route::post('buy_advertising_package_XxX_we_notify_2', 'Ad\PackageController@weNotify');//购买广告包微信支付回调新
    Route::post('buy_advertising_package_new', 'Ad\PackageController@buyAdvertisingPackage');//广告包购买套餐

    //首页
    Route::get('start_page_config', 'StartPage\indexController@config');
    Route::get('start_page_down', 'StartPage\indexController@down');
    Route::get('check_has_ring', 'Circle\MatchRingController@matchByUser');

    //成长值
    Route::post('growth_user_get_list', 'Growth\IndexController@getList');
    Route::post('growth_user_growth_value', 'Growth\IndexController@getValueChange');
    Route::post('growth_user_income_list', 'Growth\UserInComeController@userMaidDetail');
    Route::post('growth_user_v1_childs', 'Growth\IndexController@getUserNext');

    // 我的头条用户模块
    Route::post('release_news_article', 'News\PuTaoNewsController@releaseArticle');
    Route::get('get_news_all_articles', 'News\PuTaoNewsController@getAllUserArticles');
    Route::get('get_news_one_articles', 'News\PuTaoNewsController@getUserArticles');
    Route::get('get_news_release_info', 'News\PuTaoNewsController@getReleaseArticlesInfo');
    Route::get('uc_channels', 'News\UCNewsController@getChannels');
    Route::get('uc_articles', 'News\UCNewsController@getChannelDetails');


    Route::post('taobao_status_verify_zero', 'Alimama\VegasOneGoController@statusVerify');

    Route::post('shop_show_good_can_pay', 'Shop\ShowController@getPayShow');

    Route::post('pdd_orders', 'Pdd\MyController@getOrders');// 拼多多我的订单查询接口
    Route::post('pdd_team_orders', 'Pdd\MyController@getTeamOrders');// 拼多多团队订单查询接口
    Route::post('pdd_predict_log_orders', 'Pdd\MyController@getPddPredictionLog');// 拼多多团队订单查询接口

    Route::get('index_banner_sort', 'Jd\IndexController@indexBannerAndSorts');//京东首页滑动图及分类接口

    Route::post('jd_orders_team', 'Jd\PredictionController@getTeamOrders');
    Route::post('jd_orders_my', 'Jd\PredictionController@getOrders');
    Route::post('jd_orders_my_get_money', 'Jd\PredictionController@getPredictionLog');

    Route::post('app_sign_user_info_wh', 'App\SignController@checkSign');

    Route::post('app_get_ptb_list_new', 'Withdrawals\ExchangeController@getShow');

    Route::post('app_wechat_user_info_wh', 'App\InController@resolution');

    Route::post('alimama_log_index_get', 'Alimama\LogController@getLog');
    Route::post('alimama_change_user_name', 'Alimama\LogController@changeUserName');

    Route::post('medical_spring_get_apartment', 'Medical\SpringController@getChat');
    Route::post('medical_spring_get_apartment_by_apartment', 'Medical\SpringController@getDoctorByApartment');
    Route::post('medical_spring_get_apartment_by_search', 'Medical\SpringController@getDoctorBySearch');
    Route::post('medical_spring_get_apartment_by_word', 'Medical\SpringController@getDoctorByWord');
    Route::post('medical_spring_index', 'Medical\SpringController@login');

    Route::post('real_active_main', 'Active\IndexController@main');
    Route::post('real_active_show', 'Active\IndexController@show');
    Route::post('real_active_show_detail', 'Active\IndexController@showDetail');

    Route::post('medical_show_index_shop_good', 'Medical\ShowController@getIndex');
    Route::post('medical_show_index_shop_good_all', 'Medical\ShowController@getAllShopIndex');
    Route::post('medical_show_index_hospital_all', 'Medical\ShowController@getHospital');

    Route::post('medical_show_index_orders', 'Medical\ShowController@getOrders');
    Route::post('medical_show_index_refund_orders', 'Medical\ShowController@startRefund');

    Route::post('alimama_music_list', 'Alimama\MusicController@getList');
    Route::post('alimama_music_index', 'Alimama\MusicController@getIndex');

    Route::post('material_teacher_index', 'Material\TeacherController@index');
    Route::post('material_teacher_down', 'Material\TeacherController@getDown');
    Route::post('material_teacher_type', 'Material\TeacherController@getTypeInfo');
    Route::post('material_teacher_search', 'Material\TeacherController@getSearch');
    Route::post('material_teacher_topic', 'Material\TeacherController@topicInfo');
    Route::post('material_teacher_detail', 'Material\TeacherController@libraryInfo');
    Route::post('material_teacher_good', 'Material\TeacherController@good');

    Route::resource('new_new_foot_taobaoke', 'Alimama\FootController');
    Route::resource('new_new_collection_taobaoke', 'Alimama\NewCollectionController');
    Route::post('new_tao_foot_add', 'Alimama\GoController@addFoot');
    Route::post('new_tao_foot_get', 'Alimama\GoController@getFoot');
    Route::post('new_tao_foot_del', 'Alimama\GoController@delFoot');
    Route::post('new_tao_add_collection', 'Alimama\GoController@addCollection');
    Route::post('new_tao_get_collection', 'Alimama\GoController@getCollection');
    Route::post('new_tao_del_collection', 'Alimama\GoController@delCollection');
    Route::post('new_tao_get_all_index', 'Alimama\GetController@getAllIndex');
    Route::post('new_tao_get_index', 'Alimama\GetController@getIndex');
    Route::post('new_tao_get_log', 'Alimama\GetController@getLog');
    Route::post('new_tao_get_add_log', 'Alimama\GetController@addLog');
    Route::post('new_tao_get_prediction_log', 'Alimama\GetController@getPredictionLog');
    Route::resource('new_tao_order_get', 'Alimama\OrdersController');
    Route::post('alimama_my_index', 'Alimama\IndexController@getIndexInfo');
    Route::post('alimama_my_type', 'Alimama\IndexController@getIndexType');
    Route::post('alimama_my_info', 'Alimama\IndexController@getIndexMy');
    Route::post('alimama_my_type_list', 'Alimama\IndexController@getTypeList');
    Route::post('alimama_my_detail_shop', 'Alimama\IndexController@getInfoShop');
    Route::post('alimama_my_search_list', 'Alimama\IndexController@getSearchUrl');
    Route::resource('alimama_collection', 'Alimama\CollectionController');
    Route::post('check_version_verify', 'Index\VerifyController@version');
    Route::post('get_check_verify', 'Index\VerifyController@check');
    Route::resource('login_index', 'Index\IndexController');
    Route::resource('article_no', 'Index\ArticleController');
    Route::resource('test', 'Test\TestApiController');
    Route::resource('withdrawals', 'Withdrawals\IndexController');
    Route::resource('online', 'Index\OnlineController');
    Route::resource('user', 'Index\UserController');
    Route::resource('ad', 'Article\AdvertisementController');
    Route::resource('agent', 'Article\UserController');
    Route::resource('news', 'Article\ArticleController');
    Route::resource('active', 'Index\ActiveController');
    Route::resource('menu', 'Ad\IndexController');
    Route::resource('member', 'Recharge\MemberController');
    Route::resource('brokerage', 'Recharge\BrokerageController');
    Route::resource('team', 'Recharge\TeamController');
    Route::resource('profit', 'Ad\ProfitController');
    Route::resource('certificate', 'Certificate\IndexController');
    Route::resource('exchange', 'Withdrawals\ExchangeController');
    Route::resource('sms', 'Sms\AliSmsController');
    Route::resource('back_member', 'Back\MemberController');
    Route::resource('rollback', 'Back\RollbackController');
    Route::resource('address', 'Shop\AddressController');
    Route::resource('goods', 'Shop\GoodsController');
    Route::resource('carts', 'Shop\CartsController');
    Route::resource('orders', 'Shop\OrdersController');
    Route::post('wechat_orders_pay', 'Shop\OrdersController@wechatPay');
    Route::post('wechat_orders_pay_v1', 'Shop\OrdersController@wechatPayV1'); // 商城订单微信支付（余额支付）
    Route::post('ali_orders_pay_v1', 'Shop\OrdersController@storeV1'); // 商城订单支付宝支付（余额支付）
	Route::post('wechat_orders_pay_v2', 'Shop\OrdersController@wechatPayV2'); // 商城订单微信支付（余额支付）禾盟通
	Route::post('ali_orders_pay_v2', 'Shop\OrdersController@storeV2'); // 商城订单支付宝支付（余额支付）禾盟通
    Route::resource('shop_member', 'Shop\MemberController');
    Route::resource('rejected', 'Shop\RejectedController');
    Route::resource('index_goods', 'Shop\IndexGoodsController');
    Route::get('index_goods_new_add', 'Shop\IndexGoodsController@getNewPage');
    Route::get('index_goods_new_type_index', 'Shop\IndexGoodsController@getTypeInfo');
    Route::get('index_goods_new_search_index', 'Shop\IndexGoodsController@getSearchInfo');
    Route::resource('wechat_resource', 'Wechat\IndexController');
    Route::resource('wechat_user_info', 'Xin\WechatController');
    Route::get('shop_show_display', 'Shop\DisplayController@showRsa');
    Route::resource('voip_recharge', 'Voip\RechargeController');
    Route::resource('voip_send', 'Voip\IndexController');
    Route::resource('voip_recharge_maid', 'Voip\RechargeMaidController');
    Route::get('voip_index', 'Voip\IndexController@indexInfo');
    Route::get('voip_order', 'Voip\IndexController@getOrderList');
    Route::resource('circle_apply', 'Circle\ApplyController');
    Route::resource('circle_friend', 'Circle\FriendController');
    Route::post('circle_msg_list', 'Circle\MessageController@index');
    Route::post('circle_msg_item', 'Circle\MessageController@getItem');
    Route::post('circle_msg_send', 'Circle\MessageController@sendMsg');
    Route::post('circle_msg_read', 'Circle\MessageController@read');
    Route::resource('circle_ring', 'Circle\RingController');
    Route::get('circle_ring_my_circle', 'Circle\RingController@getMyCircle');
    Route::resource('circle_ring_add', 'Circle\RingAddController');
    Route::resource('circle_card', 'Circle\CardcaseController');
    Route::resource('circle_ring_complaint', 'Circle\RingComplaintController');
    Route::resource('circle_ring_active', 'Circle\RingActiveController');
    Route::get('circle_ring_active_one', 'Circle\RingActiveController@getOne');
    Route::resource('circle_ring_comment', 'Circle\RingCommentController');
    Route::resource('circle_ring_talk', 'Circle\TalkController');
    Route::resource('circle_ring_maid', 'Circle\MaidController');
    Route::resource('circle_ring_common_notify', 'Circle\NotifyController');
    Route::get('circle_ring_active_index_up', 'Circle\RingActiveController@indexUp');
    Route::post('circle_host_search', 'Circle\BecomeHostController@searchTitle');
    Route::post('circle_host_free', 'Circle\BecomeHostController@free');
    Route::post('circle_host_bid', 'Circle\BecomeHostController@bid');
    Route::post('circle_host_ptb', 'Circle\BecomeHostController@getPtb');
    Route::post('circle_host_bid_history', 'Circle\BecomeHostController@bidHistory');
    Route::post('circle_host_bid_history_count', 'Circle\BecomeHostController@countHistory');
    Route::post('circle_red_sum', 'Circle\LuckyMoneyController@getSum');
    Route::post('circle_red_send', 'Circle\LuckyMoneyController@sendRed');
    Route::post('circle_red_get', 'Circle\LuckyMoneyController@getRed');
    Route::post('circle_red_get_three', 'Circle\LuckyMoneyController@getRedForWuHang');
    Route::post('circle_red_list', 'Circle\LuckyMoneyController@getList');
    Route::post('circle_red_all_list', 'Circle\LuckyMoneyController@getAllList');
    Route::post('circle_red_info_list', 'Circle\LuckyMoneyController@getInfoList');
    Route::resource('circle_city_king', 'Circle\CityKingController');
    Route::resource('circle_city_king_add', 'Circle\CityKingAddController');
    Route::post('circle_ring_show_message', 'Circle\RingController@getShowMessage');
    Route::post('circle_talk_many_index', 'Circle\TalkController@getManyIndex');
    Route::post('circle_friend_all_index', 'Circle\FriendController@getAllIndex');
    Route::post('circle_find_all_index', 'Circle\FriendController@findAllIndex');
    Route::resource('app_password', 'Ad\PasswordController');
    Route::post('reset_phone', 'Ad\ResetPhoneController@resetPhone');
    Route::post('app_sign', 'App\UserController@sign');
    Route::post('record_err', 'Test\AliOrderController@record');
    Route::post('app_mine_my_order', 'AppMine\MyOrderController@getMyOrder');
    Route::post('xin_get_init_data', 'Xin\UserCenterController@getInitData');
    Route::post('xin_get_poster_images', 'Xin\ShareGatherController@getPosterImages');
    Route::post('xin_submit_suggestion', 'Xin\ShareGatherController@submitSuggestion');
    Route::post('xin_create_parent_user', 'Xin\IdolController@createParentUser');
    Route::post('xin_get_target_user_name', 'Xin\IdolController@getTargetUserName');
    Route::post('xin_get_parent_user_info', 'Xin\IdolController@getParentUserInfo');
    Route::post('xin_get_init', 'Xin\QuestionController@init');
    Route::post('xin_get_list_by_type', 'Xin\QuestionController@getListByType');
    Route::post('xin_my_work_order_list', 'Xin\QuestionController@myWorkOrderList');
    Route::post('xin_check_apply_cash', 'Xin\ApplyCashController@checkApplyCash');
    Route::post('xin_apply_cash', 'Xin\ApplyCashController@applyCash');
    Route::post('xin_get_apply_bonus_list', 'Xin\ApplyCashController@getApplyBonusList');
    Route::post('xin_get_apply_cash_list', 'Xin\ApplyCashController@getApplyCashList');
    Route::post('xin_get_apply_order_list', 'Xin\ApplyCashController@getApplyOrderList');
    Route::post('xin_get_user_info', 'Xin\GroupManageController@getUserInfo');
    Route::post('xin_check_apply_upgrade', 'Xin\GroupManageController@checkApplyUpgrade');
    Route::post('xin_get_bonus_log', 'Xin\GroupManageController@getBonusLog');
    Route::post('xin_update_user_name', 'Xin\UserInfoController@updateUserName');
    Route::post('xin_update_avatar', 'Xin\UserInfoController@updateAvatar');
    Route::post('xin_update_real_name', 'Xin\UserInfoController@updateRealName');
    Route::post('xin_update_alipay', 'Xin\UserInfoController@updateAlipay');
    Route::post('xin_submit_order_number', 'Xin\OrderController@submitOrderNumber');
    Route::post('xin_submit_list_order_number', 'Xin\OrderController@submitListOrderNumber');
    Route::post('xin_check_hide_code_version', 'Xin\IosController@checkHideCodeVersion');
    Route::post('xin_get_announcement_total', 'Xin\PutaoHomeController@getAnnouncementTotal');
    Route::post('xin_get_search_url_data', 'Xin\PutaoHomeController@getSearchUrlData');
    Route::post('xin_get_home_url_data', 'Xin\PutaoHomeController@getHomeUrlData');
    Route::post('xin_get_list', 'Xin\PutaoHomeController@getList');
    Route::post('xin_submit_work_order', 'Xin\QuestionController@submitWorkOrder');
    Route::post('xin_apply_upgrade', 'Xin\GroupManageController@ApplyUpgrade');
    Route::post('xin_get_work_order_detail', 'Xin\QuestionController@getWorkOrderDetail');
    Route::post('xin_register', 'Xin\AuthController@register');
    Route::post('xin_active_user', 'Xin\AuthController@activeUser');
    Route::post('ali_get_key_word', 'Alimama\NewController@getKeyWord');
    Route::post('ali_get_search', 'Alimama\NewController@getSearch');
    Route::post('ali_get_classify', 'Alimama\NewController@getClassify');
    Route::post('ali_get_subject', 'Alimama\NewController@getSubject');
    Route::post('alimama_commodity_details', 'Alimama\NewController@commodityDetails');
    Route::post('xin_new_submit_order_number', 'Xin\OrderController@newSubmitOrderNumber');
    Route::post('xin_submit_complaint', 'Xin\QuestionController@submitComplaint');
    Route::post('ali_smart_super_search', 'Alimama\ManyController@getBigSearch');
    Route::post('ali_smart_ali_search', 'Alimama\ManyController@getAliSearch');
    Route::post('ali_smart_goods_details', 'Alimama\ManyController@getDetail');
    Route::resource('page_user', 'Index\PageUserController');
    Route::post('business_school_selectness', 'Material\TeacherController@selectness');
    Route::post('business_school_friends', 'Material\TeacherController@friends');
    Route::post('business_school_counter', 'Material\TeacherController@counter');
    Route::post('alimama_video_commodity_details', 'Alimama\NewController@videoCommodityDetails');
    Route::post('medical_appointment_time', 'Medical\CheckupController@getAppointmentTime');
    Route::post('medical_get_packager', 'Medical\CheckupController@getPackager');
    Route::post('medical_submit_packager', 'Medical\CheckupController@submitPackager');
	Route::post('medical_submit_packager_v2', 'Medical\CheckupController@submitPackagerV2');//医疗接he盟通支付
    Route::post('medical_add_file', 'Medical\IndexController@addFile');
    Route::post('medical_updata_file', 'Medical\IndexController@updataFile');
    Route::post('medical_get_file', 'Medical\IndexController@getFile');
    Route::post('medical_delete_file', 'Medical\IndexController@deleteFile');
    Route::post('medical_consulting_send', 'Medical\ChunYuController@sendMsg');
    Route::post('medical_dialogues_get', 'Medical\ChunYuController@getDialogues');
    Route::post('medical_get_issue_history', 'Medical\IndexController@getIssueHistory');
    Route::post('medical_health_bzgh', 'Medical\IndexController@healthBzgh');
    Route::post('medical_health_jkgjqdb', 'Medical\IndexController@healthJkgjqdb');//健康之路 我的首页
    Route::post('medical_health_bzgh_T', 'Medical\IndexController@healthBzghT');//健康之路H5 临时增加
    Route::post('medical_create_question', 'Medical\ChunYuController@createQuestion');
    Route::post('ali_get_verify_token', 'Prove\LivingProveController@getVerifyToken');//ali 发起活体认证
    Route::post('ali_get_status', 'Prove\LivingProveController@getStatus');//ali 获取认证状态
    Route::post('taobao_status_verify', 'Alimama\VegasController@statusVerify');
    Route::post('taobao_status_verify_all', 'Alimama\VegasControllerAll@statusVerify');
    Route::post('taobao_status_verify_all_rand', 'Alimama\VegasControllerAllRand@statusVerify');
    Route::post('jd_commodity_list', 'Shop\JdPddCommodityController@jdCommodityList');//京东商品列表
    Route::post('jd_goods_detail', 'Shop\JdPddCommodityController@jdGoodsDetail');//京东商品详情
    Route::post('jd_union_url', 'Shop\JdPddCommodityController@jdUnionUrl');//京东商品转链
    Route::post('pdd_commodity_list', 'Shop\JdPddCommodityController@pddCommodityList');//拼多多商品列表
    Route::post('pdd_goods_detail', 'Shop\JdPddCommodityController@pddGoodsDetail');//拼多多商品详情
    Route::post('pdd_union_url', 'Shop\JdPddCommodityController@pddUnionUrl');//拼多多商品转链
    Route::post('pdd_get_order', 'Shop\JdPddCommodityController@pddGetOrder');//拼多多订单查询
    Route::post('cloud_pay', 'Pay\CloudPayController@cloudPay');// 云闪付支付接口
    Route::post('credit_card_show_info', 'CreditCard\ShowController@showDetail');//信用卡展示消息
    Route::post('credit_card_login', 'CreditCard\ShowController@cardLogin');//信用卡登陆
    Route::post('zero_buy_show', 'Web\ZeroBuyController@showInfo');//一分购数据统计
    Route::post('buy_advertising_package', 'Recharge\MemberController@buyAdvertisingPackage');//广告包购买
    Route::post('shop_vip_commodity_assign', 'Shop\GoodsController@vipCommodityAssign');//vip商品指定
    Route::post('voip_save_gps_info', 'Voip\ShowController@voipSaveGpsInfo');//存入gps信息
    Route::post('taobao_status_verify_vip', 'UpgradeVip\VegasVipController@statusVerify');
    Route::post('growth_user_past_value', 'Growth\IndexController@getPastValue');//获取用户预估待结算成长值
    Route::post('upgrade_change_active', 'UpgradeVip\ChangeVipController@activeVip');//通过活跃度升级
    Route::post('upgrade_change_growth', 'UpgradeVip\ChangeVipController@growthVip');//通过成长值升级
    Route::post('ali_get_tao_index_data', 'Alimama\ManyController@getTaoIndexData');//淘首页 整改补充
    Route::post('ali_get_tao_index_recommend', 'Alimama\ManyController@getTaoIndexRecommend');//淘首页 为你推荐剥离
    Route::post('ali_get_tao_index_goods', 'Alimama\ManyController@getTaoIndexGoods');//淘首页 抢购商品剥离
    Route::post('ali_loophole_list', 'Alimama\ManyController@getLoopholeData');
    Route::post('ali_get_ele_maid_order', 'Alimama\EleOrderController@getEleMaidOrder');//得到饿了么报销订单
    Route::post('ali_get_directly_ele_maid_order', 'Alimama\EleOrderController@getDirectlyEleMaidOrder');//得到饿了么直属下级报销订单
    Route::post('medical_health_get_url', 'Medical\HealthShowController@getUrl');//健康管家获取url
    Route::post('circle_red_send_v1', 'Circle\LuckyMoneyController@sendRedV1');//圈子发红包扣除余额
    Route::post('circle_host_bid_v1', 'Circle\BecomeHostController@bidV1');//购买圈子
    Route::post('circle_ring_add_v1', 'Circle\RingAddController@addCircleV1');//加入圈子扣除余额
    Route::post('buy_advertising_package_v1', 'Recharge\MemberController@buyAdvertisingPackageV1');//广告包购买支出
	Route::post('get_ejy_url', 'EJiaYou\EJiaYouController@getUrl');//生成加油链接
	Route::post('wechat_assistant_audit', 'Wechat\AssistantController@index');//群助手
    Route::post('wechat_assistant_audit_v2', 'Wechat\AssistantController@getV2');//群助手v2
	Route::get('home_info', 'Index\HomeController@index');//得到首页home
});
Route::get('credit_card_show_rule_web', function () {
//    return "信用卡规则展示页面-暂定";
    return view('activity.credit_active');
});//信用卡展示消息

Route::post('check_version_verify_no', 'Index\VerifyController@version');//no加密

Route::post('buy_advertising_package_XxX_we_notify', 'Recharge\MemberController@weNotify');//购买广告包微信支付回调
Route::get('voip_is_gps_word', 'Voip\ShowController@voipIsGpsWord');//通讯是否开启gps文案
Route::get('can_pay_type_chase', 'Pay\AliPayController@getOpenPayData');//开启的支付方式
Route::post('cloud_pay_cta_oOoOo', 'Pay\CloudPayController@cloudPay');// 云闪付支付接口
Route::post('cloud_notify', 'Pay\CloudPayController@cloudNotify'); // 云闪付支付回调接口
Route::post('ali_gift_mirror', 'Alimama\IndexController@giftMirror');//淘礼金镜像
Route::post('ali_one_shopping_gift', 'Alimama\IndexController@oneShoppingGift');//一元购淘礼金
Route::post('share_commodity_part', 'Alimama\ManyController@shareCommodity');//分享商品
Route::post('wechat_right_verify', 'Wechat\IndexController@rightVerify');//H5权限配置验证
Route::post('ali_we_oOo0olI1_refund', 'Pay\RefundController@refund');
Route::post('mini_refund_callback', 'Pay\PayPaiController@refundCallback'); // 小程序退款回调
Route::post('mini_order_refund', 'Pay\PayPaiController@refund'); // 小程序订单退款
Route::post('medical_question_XxX_ali_notify', 'Medical\ChunYuController@aliNotify');
Route::post('medical_question_XxX_we_notify', 'Medical\ChunYuController@weNotify');
Route::post('medical_make_XxX_we_notify', 'Medical\CheckupController@weNotify');
Route::post('medical_make_XxX_ali_notify', 'Medical\CheckupController@aliNotify');
Route::get('medical_health_bzgh_back', 'Medical\IndexController@healthBzghBack');
Route::post('voip_callback_jiu_hua', 'Voip\IndexController@callbackJiuHua');
Route::get('ali_video_list_msg', 'Alimama\HDKVideoController@getListMsg');
Route::get('ali_video_change_url', 'Alimama\HDKVideoController@getRatesUrl');
Route::get('ali_video_new_push_change_url', 'Alimama\HDKVideoController@getNewRatesUrl');
Route::get('ali_video_new_push_change_url_new', 'Alimama\HDKVideoController@getNewRatesUrlNew');
Route::get('business_school_top_banner', 'Material\TeacherController@topBanner');   // banner
Route::get('ali_smart_url_change', 'Alimama\ManyController@changeUrl');
Route::post('ali_sync_gather_order', 'Alimama\AlimamaSyncController@gatherOrder');
Route::get('voip_callback_new', 'Voip\IndexController@callbackNew');
Route::get('ali_get_ratesurl', 'Alimama\NewController@getRatesUrl');
Route::post('xin_do_register', 'Xin\AuthController@doRegister');
Route::post('web_send_sms', 'WebShop\GoodsController@SendSMS');
Route::get('xin_share_register', 'Xin\AuthController@shareRegister');
Route::get('xin_share_register_new', 'Xin\AuthController@shareRegisterNew');
Route::get('xin_user_agreementr', 'Xin\AuthController@userAgreement');
Route::get('xin_about_us', 'Xin\ShareGatherController@aboutUs');
Route::get('xin_get_details', 'Xin\PutaoHomeController@getDetails');
Route::get('xin_details', 'Xin\QuestionController@details');
Route::get('xin_get_advertising', 'Xin\PutaoHomeController@getAdvertising');
Route::post('jd_test_wu_order', 'Jd\GetController@order');
Route::post('jd_test_wu_url', 'Jd\GetController@getUrl');
Route::post('jd_test_wu_my', 'Jd\GetController@myOrders');
Route::post('jd_test_wu_regular', 'Jd\GetController@regular');
Route::post('circle_host_bid_one', 'Circle\BecomeHostController@bid');
Route::post('circle_red_get_one', 'Circle\LuckyMoneyController@getRed');
Route::get('circle_ring_common', 'Circle\CommonController@getUrlForShare');
Route::get('circle_ring_url_common', 'Circle\CommonController@getUrlForCircle');
Route::get('circle_host_hot', 'Circle\BecomeHostController@getHotTitle');
Route::post('circle_host_XxX_ali_notify', 'Circle\BecomeHostController@aliNotify');
Route::post('circle_red_XxX_ali_notify', 'Circle\LuckyMoneyController@aliNotify');
Route::post('circle_host_XxX_we_notify', 'Circle\BecomeHostController@weNotify');
Route::post('circle_red_XxX_we_notify', 'Circle\LuckyMoneyController@weNotify');
Route::resource('voip_recharge_type', 'Voip\RechargeTypeController');
Route::get('voip_phone_list', 'Voip\IndexController@getPhoneList');
Route::get('voip_question_list', 'Voip\RechargeController@getQuestionList');
Route::resource('shop_display', 'Shop\DisplayController');
Route::resource('shop_special', 'Shop\SpecialController');
Route::resource('bank_money', 'Money\IndexController');
Route::group(['middleware' => ['game']], function () {
    Route::resource('test', 'Test\TestApiController');
    Route::resource('game_recharge', 'Game\IndexController');
});
Route::get('get_163_article', 'Article\NewsController@getNewsArticle');
Route::get('get_163_list', 'Article\NewsController@getNewsList');
Route::get('jd_new_url_this_wu', 'Common\DisplayController@getNewJdUrlThisWu');
Route::get('jd_new_url_this_wan', 'Common\DisplayController@getNewJdUrlThisWan');
Route::get('jd_new_orders_this_wu', 'Common\DisplayController@getNewJdAllOrdersThisWu');
Route::get('jd_new_orders_this_wan', 'Common\DisplayController@getNewJdAllOrdersThisWan');
Route::get('get_ip', 'Tools\CommonController@getIp');
Route::get('get_ip_test', 'Tools\CommonController@getIpTest');

Route::get('get_news_from_id', 'News\PuTaoNewsController@getArticlesById');

Route::get('get_reward_new_my_url', 'News\AdController@getMyUrl');

Route::post('harry_t', 'Harry\HarryController@index');//调试使用
Route::post('harry_t_push_call', 'Harry\HarryController@pushCallBack');//调试使用
Route::post('harry_t_call', 'Harry\agreeMentController@callBack');//回调装置1号签约
Route::post('harry_agreement_put_out', 'Harry\agreeMentController@push');//签约页面接口
Route::post('out_harry_agreement_put_out', 'Harry\agreeMentOutController@push');//签约页面接口（无限）

Route::post('out_harry_t_call', 'Harry\agreeMentOutController@callBack');//回调英菲尼迪

Route::post('jump_user_one', 'Growth\IndexController@jump');
Route::post('mini_user_groupid-t', 'Mini\UserController@getGroupId');

Route::post('mini_order_pay_callback', 'Pay\PayPaiController@h5PayCallBack'); // 小程序支付回调

Route::any('tool_send_qywx', 'Tools\ToolController@send'); //消息推送通知
Route::any('tool_send_dding', 'Tools\ToolController@dingSend');

Route::post('mini_upload', 'Common\MiniOssController@upload');//小程序图片接口 控制器加密


Route::get('wx_robot_info_o0o_ing', 'Wechat\Assistant\UserController@info'); //


Route::post('mini_wu_harry_list_no', 'Other\WuController@getOut');//得到列表
Route::post('mini_wu_out_list_no', 'Other\WuController@getOutList');//得到列表
Route::post('mini_wu_out_list_detail_no', 'Other\WuController@getDetail');//得到列表


Route::group(['middleware' => ['web_api']], function () {
	
	Route::post('xin_do_register_rs', 'Xin\AuthController@doRegister'); //注册接口
	Route::post('web_send_sms_rs', 'WebShop\GoodsController@SendSMS'); //短信接口
	
	Route::resource('mini_wechat_user_info', 'Xin\WechatController');
	Route::post('mini_xin_get_my_idol_info', 'Xin\IdolController@getMyIdolInfo'); // 我的偶像
	
	Route::post('mini_get_all_team_orders_data', 'Other\MiNiTeamDataController@getAllTeamOrdersData');//得到全部类型订单简要数据
	
	Route::post('wechat_assistant_audit_h5', 'Wechat\AssistantController@index');//群助手
	Route::post('wx_robot_order_pay', 'Wechat\Assistant\OrderController@generatePayOrder'); // 机器人下单支付
	Route::get('wx_robot_order_detail', 'Wechat\Assistant\OrderController@showOrderDetail'); // 机器人订单详情
	Route::get('wx_robot_orders', 'Wechat\Assistant\OrderController@orderList'); // 机器人支付订单列表
	Route::get('wx_robot_info', 'Wechat\Assistant\UserController@info'); // 机器人支付订单列表
	Route::post('wx_robot_get_way', 'Wechat\Assistant\UserController@getWay'); // 机器人公用入口

    Route::post('mini_comment_msg_all', 'Comment\CommentController@msgAll');//消息总量
    Route::post('mini_comment_send_msg', 'Comment\CommentController@sendMsg');//发送意见(尽量兼容小程序)
    Route::post('mini_comment_msg_list', 'Comment\CommentController@msgList');//消息总量
    Route::post('mini_comment_msg_reply', 'Comment\CommentController@msgReply');//拉出反馈的具体信息
    Route::post('mini_comment_send_reply', 'Comment\CommentController@sendReply');//发送回复
    Route::post('mini_comment_end', 'Comment\CommentController@end');//已解决


	Route::post('mini_get_turntable_index', 'Coin\TurntableController@turntableIndex');//转盘首页
	Route::post('mini_get_turntable_get_log', 'Coin\TurntableController@turntableGetLog');//我的中奖记录
	Route::post('mini_get_buy_lucky_draw_index', 'Coin\TurntableController@buyLuckyDrawIndex');//购买抽奖次数页面
	Route::post('mini_pay_lucky_draw_count', 'Coin\TurntableController@payLuckyDrawCount');//购买抽奖支付接口
	Route::post('mini_start_turntable_lucky_draw', 'Coin\TurntableController@startTurntableLuckyDraw');//开始抽奖接口
	Route::post('mini_get_turntable_order_info', 'Coin\TurntableController@getTurntableOrderInfo');//领取实物or查看订单状态
	Route::post('mini_submit_turntable_order', 'Coin\TurntableController@submitTurntableOrder');//提交领取实物订单
	Route::post('mini_affirm_turntable_order', 'Coin\TurntableController@affirmTurntableOrder');//确认实物订单到货
	
	Route::post('mini_get_fulu_goods_one_classify', 'FuLu\FuLuController@getGoodsOneClassify');//得到福禄商品一级分类
	Route::post('mini_get_fulu_goods_two_classify', 'FuLu\FuLuController@getGoodsTwoClassify');//得到福禄商品二级分类
	Route::post('mini_get_fulu_goods_three_classify', 'FuLu\FuLuController@getGoodsThreeClassify');//得到福禄商品三级分类
	Route::post('fulu_pre_pay_info', 'FuLu\OrderController@generatePrePayInfo');//福禄订单预支付信息展示
	Route::post('fulu_order_pay_info', 'FuLu\OrderController@generatePayOrder');//福禄订单生成支付订单并弹窗支付信息。
	Route::get('fulu_card_info', 'FuLu\OrderController@orderCardInfo');//卡密信息
	Route::post('fulu_order_to_pay', 'FuLu\OrderController@toPay');//福禄订单生成支付订单并弹窗支付信息。
	Route::get('fulu_order_detail', 'FuLu\OrderController@showOrderDetail');//福禄订单详情
	Route::get('fulu_order_list', 'FuLu\OrderController@orderList');//福禄订单列表
	Route::post('mini_get_fulu_goods_list', 'FuLu\FuLuController@getGoodsList');//得到福禄商品列表
	Route::post('mini_get_fulu_goods_info', 'FuLu\FuLuController@getGoodsInfo');//得到福禄商品信息
	Route::post('mini_get_fulu_goods_template', 'FuLu\FuLuController@getGoodsTemplate');//得到福禄商品模板信息
	
	Route::post('mini_xin_get_init_data', 'Xin\UserCenterController@getInitData');//我的首页个人中心数据
	Route::post('mini_xin_submit_work_order', 'Xin\QuestionController@submitWorkOrder');//小程序发起提问
	Route::post('mini_xin_my_work_order_list', 'Xin\QuestionController@myWorkOrderList');//小程序我的提问记录
	Route::post('mini_xin_get_work_order_detail', 'Xin\QuestionController@getWorkOrderDetail');//小程序问题详情

    Route::post('xin_submit_suggestion_mini', 'Xin\ShareGatherController@submitSuggestionMini');//投诉建议

	Route::post('xin_share_register_new_info', 'Xin\AuthController@shareRegisterNewInfo'); //获取用户资料



    Route::post('mini_wu_get_in', 'Other\WuController@getIn');//得到3行列
    Route::post('mini_wu_get_list', 'Other\WuController@getList');//得到列表
    Route::post('mini_wu_harry_list', 'Other\WuController@getOut');//得到列表
    Route::post('mini_wu_out_list', 'Other\WuController@getOutList');//得到列表
    Route::post('mini_wu_out_list_detail', 'Other\WuController@getDetail');//得到列表

	Route::resource('mini_shop_member_team_cny', 'Shop\MemberTeamCnyController'); //团队已到账商城佣金明细
	Route::post('mini_get_hot_shop_estimated_income', 'Other\MiNiTeamDataController@hotShopEstimatedIncome');//得到爆款商城管理费预估收入
	Route::post('mini_get_tb_team_orders_data', 'Other\MiNiTeamDataController@getTbTeamOrdersData');//得到淘宝团队订单数据
	Route::post('mini_get_jd_team_orders_data', 'Other\MiNiTeamDataController@getJdTeamOrdersData');//得到京东团队订单数据
	Route::post('mini_get_pdd_team_orders_data', 'Other\MiNiTeamDataController@getPddTeamOrdersData');//得到拼多多团队订单数据
	Route::post('mini_get_ele_team_orders_data', 'Other\MiNiTeamDataController@getEleTeamOrdersData');//得到饿了么团队订单数据
	Route::post('mini_get_mt_team_orders_data', 'Other\MiNiTeamDataController@getMtTeamOrdersData');//得到美团团队订单数据

    Route::post('out_harry_agreement_put', 'Harry\agreeMentOutController@push');//签约页面接口（无限）
    Route::post('out_harry_t_is_call', 'Harry\agreeMentOutController@checkIsCall');//是否签约接口（无限）
    Route::post('harry_agreement_put', 'Harry\agreeMentController@push');//签约页面接口
    Route::post('harry_t_is_call', 'Harry\agreeMentController@checkIsCall');//是否签约接口

    Route::post('mini_mt_get_maid_order', 'MeiTuan\IndexController@getMtMaidOrder');//得到美团报销订单
    Route::post('mini_mt_get_directly_maid_order', 'MeiTuan\IndexController@getDirectlyMtMaidOrder');//得到美团直属下级报销订单

    Route::post('ev_identify_validate_web', 'Other\GongMallController@validateIdentify'); // 电签验证
    Route::post('ev_check_validate_web', 'Other\GongMallController@checkValidate'); // 查询是否已验证

    Route::post('other_ev_get_user_info', 'Other\GongMallController@getIdentifyInfo'); // 获取工猫用户信息

    Route::resource('mini_shop_member_cny', 'Shop\MemberCnyController'); //今日已到账商城佣金

    Route::post('mini_qr_code', 'Wechat\MiniController@qrCode');

    Route::resource('mini_new_tao_order_get', 'Alimama\OrdersController');//淘宝报销-团队订单
    Route::post('mini_ali_get_directly_ele_maid_order', 'Alimama\EleOrderController@getDirectlyEleMaidOrder');//得到饿了么直属下级报销订单

    Route::post('mini_xin_get_parent_user_info', 'Xin\IdolController@getParentUserInfo');
    Route::post('mini_growth_user_income_list', 'Growth\UserInComeController@userMaidDetail');

    Route::post('mini_xin_create_parent_user', 'Xin\IdolController@createParentUser'); // 添加偶像
    Route::post('mini_xin_get_target_user_name', 'Xin\IdolController@getTargetUserName'); // 查询偶像
    Route::resource('mini_create_orders', 'Shop\OrdersWXAppletController'); // 小程序生下单
    Route::post('mini_orders_pay', 'Pay\PayPaiController@generatePayInfo'); // 小程序付款
    Route::post('mini_orders_pay_v1', 'Pay\PayPaiController@generatePayInfoV1'); // 小程序付款（余额支付）
    Route::post('mini_orders_pay_v2', 'Pay\PayPaiController@generatePayInfoV2'); // 禾盟通小程序付款（余额支付）
    Route::post('mini_order_h5pay', 'Pay\PayPaiController@h5Pay'); // 小程序h5订单支付接口

    Route::post('mini_order_pay', 'Pay\PayPaiController@pay'); // 小程序订单支付接口

    Route::post('mini_one_go_union_uri', 'OneGo\OnGoController@getUnionUrlApi');
    Route::post('mini_new_tao_get_prediction_log', 'Alimama\GetController@getPredictionLog');
    Route::resource('mini_rejected', 'Shop\RejectedController');
    Route::get('mini_show_return_info', 'Shop\DisplayController@showReturnInfo');
    Route::get('mini_refresh_shop_express', 'Shop\DisplayController@refreshExpress');
    Route::get('mini_index_goods_new_search_index', 'Shop\IndexGoodsController@getSearchInfo');
    Route::resource('mini_address', 'Shop\AddressController');
    Route::resource('mini_orders', 'Shop\OrdersController');
    Route::post('mini_new_tao_get_all_index', 'Alimama\GetController@getAllIndex');
    Route::resource('mini_goods', 'Shop\GoodsController');
    Route::resource('mini_carts', 'Shop\CartsController');
    Route::get('mini_shop_show_display', 'Shop\DisplayController@showRsa');
    Route::get('mini_index_goods_new_type_index', 'Shop\IndexGoodsController@getTypeInfo');
    Route::resource('mini_menu', 'Ad\IndexController');
    Route::resource('mini_index_goods', 'Shop\IndexGoodsController');
    Route::get('mini_index_goods_new_add', 'Shop\IndexGoodsController@getNewPage');
    Route::resource('mini_sms', 'Sms\AliSmsController');
    Route::post('mini_auth', 'Wechat\MiniController@code2UserInfo');
    Route::post('mini_register', 'Wechat\MiniController@miniRegister');
    Route::post('mini_login', 'Wechat\MiniController@login');

    Route::resource('mini_login_index', 'Index\IndexController');
    Route::get('mini_index_banner_sort', 'Jd\IndexController@indexBannerAndSorts');//京东首页滑动图及分类接口
    Route::post('mini_jd_commodity_list', 'Shop\JdPddCommodityController@jdCommodityList');//京东商品列表
    Route::post('mini_jd_goods_detail', 'Shop\JdPddCommodityController@jdGoodsDetail');//京东商品详情
    Route::post('mini_jd_union_url', 'Shop\JdPddCommodityController@jdUnionUrlMini');//京东商品转链
    Route::post('mini_jd_orders_my', 'Jd\PredictionController@getOrdersMini');//全部订单
    Route::post('mini_jd_orders_team', 'Jd\PredictionController@getTeamOrders');//京东团队订单
    Route::post('mini_jd_orders_my_get_money', 'Jd\PredictionController@getPredictionLog');

    Route::post('mini_pdd_commodity_list', 'Shop\JdPddCommodityController@pddCommodityList');//拼多多商品列表
    Route::post('mini_pdd_goods_detail', 'Shop\JdPddCommodityController@pddGoodsDetail');//拼多多商品详情
    Route::post('mini_pdd_union_url', 'Shop\JdPddCommodityController@pddUnionUrlMini');//拼多多商品转链
    Route::post('mini_pdd_get_order', 'Shop\JdPddCommodityController@pddGetOrder');//拼多多订单查询
    Route::post('mini_pdd_orders', 'Pdd\MyController@getOrders');// 拼多多我的订单查询接口
    Route::post('mini_pdd_team_orders', 'Pdd\MyController@getTeamOrders');// 拼多多团队订单查询接口
    Route::post('mini_pdd_predict_log_orders', 'Pdd\MyController@getPddPredictionLog');// 拼多多预估报销

    Route::post('mini_user_groupid', 'Mini\UserController@getGroupId');

    //web api 加密路由
    //提现模块
	Route::get('other_user_level', 'Other\IndexController@getUserLevel');
	Route::get('other_manage_income', 'Other\IndexController@getOtherMaidMoney');
    Route::get('other_user_money', 'Other\IndexController@getUserMoney');
    Route::get('other_user_withdraws', 'Other\IndexController@getUserWithdrawList');
    Route::post('other_add_log', 'Other\IndexController@addLog');
    Route::get('other_account_logs', 'Other\IndexController@getUserLogList');
    Route::get('other_withdraw_total', 'Other\IndexController@getWithDrawSuccess');
    Route::post('other_apply_withdraw_ali', 'Other\IndexController@applyWithdrawAlipay'); //
    Route::post('other_bind_account', 'Other\IndexController@bindWithdrawInfo'); // 用户绑定银行卡或支付宝
	Route::post('other_manager_money_list', 'Other\IndexController@getManagerMoney'); // 经理佣金
		
    Route::post('get_reward_new/{id}', 'News\AdController@webUpdate');
    Route::post('ali_subsidy_zero_shop', 'Web\ZeroBuyController@subsidyZeroShop');//补贴0元购

    Route::post('ali_zero_buy_change_url', 'Alimama\ManyController@zeroChangeUrl');

    Route::post('rsa_test', function (Request $request) {
        return [
            'code' => 200,
            'msg' => '请求成功',
            'time' => time(),
            'data' => $request->data,
        ];
    });

});


Route::get('/command_order', function (Request $request) {

    if (!$request->has(['order', 'start'])) {
        return 'error';
    }

    $order = $request->get('order');
    $start = $request->get('start');

    $dm = 'cc.k5.hk';
    $host_ip = gethostbyname($dm);
    $request_ip = $request->ip();
    if ($request_ip != '127.0.0.1' && $host_ip != $request_ip) {
        return 'error ips :' . $request_ip;
    }

    $exitCode = Artisan::call('command:taobaoMissingOrder', [
        'order' => $order, 'start' => $start
    ]);

    return $exitCode;
    //
});

Route::get('get_time', function () {
    return time();
});

Route::get('get_client_ip', function (Request $request) {
    return [
        'code' => 200,
        'msg' => '请求成功',
        'data' => $request->ip(),
    ];
});


