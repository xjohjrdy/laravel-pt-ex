<?php

/**
 * 回调专用路由 callback/get_ip_test
 */
Route::post('harry_withdraw_callback', 'Other\GongMallController@harryWithdrawCallback'); //众薪提现回调
Route::post('harry_withdraw_callback_ele', 'EleAdmin\WithdrawController@harryWithdrawCallback'); //众薪提现回调
Route::post('he_meng_tong_pay_call_back', 'HeMengTong\HeMeToController@heMeToPayCallBack');//禾盟通支付回调
Route::post('fulu_pay_call_back', 'HeMengTong\HeMeToController@fuluPayCallback');//福禄订单支付回调
Route::post('fulu_order_call_back', 'FuLu\OrderController@enterOrderCallback');//福禄订单下单回调
Route::post('he_meng_tong_coin_shop_call_back', 'HeMengTong\HeMeToController@heMeToCoinShopBuyCallBack');//禾盟通支付 金币商城回调

Route::post('get_harry_result', 'CzhTest\TestController@getHarryResult'); //众薪提现回调
Route::post('get_harry_result2', 'CzhTest\TestController@getHarryResult2'); //众薪提现回调

Route::post('card_recover', 'CzhTest\TestController@cardRecover'); //众薪提现回调



Route::post('wx_robot_pay', 'HeMengTong\HeMeToController@robotPayCallback');//微信机器人支付回调

Route::post('general_shop_count_maid_manager', 'Other\ManagerController@maid');