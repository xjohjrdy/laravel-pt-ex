<?php

/**
 * 第三方分润后台路由
 */
Route::post('login', 'OtherAdmin\IndexController@login');
Route::group(['middleware' => ['web_other']], function () {
    Route::get('info', 'OtherAdmin\IndexController@info');
    Route::get('log_out', 'OtherAdmin\IndexController@logOut');
    Route::get('withdraw', 'OtherAdmin\WithdrawController@getUserWithdrawList'); // 获取提现列表
    Route::post('import_withdraw', 'OtherAdmin\WithdrawController@importDataAndWithdraw'); // 导入并打款
    Route::post('import_withdraw_harry', 'OtherAdmin\WithdrawController@importDataAndWithdraw2Harry'); // 导入并打款
    Route::get('user_list', 'OtherAdmin\UserController@getList'); // 获取提现列表
    Route::post('user_add', 'OtherAdmin\UserController@add');
    Route::post('user_edit', 'OtherAdmin\UserController@edit');
    Route::post('withdraw_reject', 'OtherAdmin\WithdrawController@withdrawReject'); // 获取提现列表
    Route::get('export_apply_for_page', 'OtherAdmin\WithdrawController@exportForPage'); // 导出

    //拉出查询列表
    Route::post('work_done_by_hand', 'OtherAdmin\CensusController@getList');
});