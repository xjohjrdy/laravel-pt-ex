<?php

namespace App\Services\CoinPlate;

use App\Entitys\App\CoinUser;
use App\Exceptions\ApiException;
use Illuminate\Support\Facades\DB;

class CoinConst
{
    // 获得金币类别
    const COIN_PLUS_TASK_DAILY =        101; // 101 日常任务获得
    const COIN_PLUS_TASK_NEW =          102; // 102新手任务获得
    const COIN_PLUS_TASK_HIGH =         103; // 103进阶任务获得
    const COIN_PLUS_TASK_TURNTABLE =    104; // 104大转盘抽奖获得
    const COIN_PLUS_TASK_SIGN =         105; // 签到获得
    const COIN_PLUS_TASK_REFUND =       106; // 退款获得

    //扣除金币类别
    const COIN_MINUS_PRIZE_CHANGE =     201; // 大转盘次数兑换
    const COIN_MINUS_ARTICLE_CHANGE =   202; // 文章次数兑换
    const COIN_MINUS_GOODS_DEDUCT =     203; // 实物商品购买抵扣
    const COIN_MINUS_COUPON_CHANGE =    204; // 爆款商城优惠券兑换
    const COIN_MINUS_ARTICLE_READ =     205; // 文章阅读扣除
    const COIN_MINUS_ANSWER =           206; // 金币答题
    const COIN_MINUS_XXL =              207; // 消消乐
    const COIN_MINUS_LOTTERY_GOODS =    208; // 金币实物抽奖


    // 首页任务类型
    const TASK_NEW = 1; // 新手任务
    const TASK_DAILY = 2; // 日常任务
    const TASK_HIGH = 3; //进阶任务

    // 相关任务hash缓存key值
    const TASK_HASH_KEY = 'pt_coin_task_list_';
    const TASK_LAST_FINISH_TIME = 'pt_coin_task_finish_time'; // 最后任务的完成时间

    static function plusLogDesc($key){
        $arr = [
            static::COIN_PLUS_TASK_DAILY =>         '完成日常任务获得',
            static::COIN_PLUS_TASK_NEW =>           '完成新手任务获得',
            static::COIN_PLUS_TASK_HIGH =>          '完成进阶任务获得',
            static::COIN_PLUS_TASK_TURNTABLE =>     '大转盘抽奖获得',
            static::COIN_PLUS_TASK_SIGN =>          '签到获得',
            static::COIN_PLUS_TASK_REFUND =>        '商品退款获得',
        ];
        return $arr[$key];
    }

    static function minusLogDesc($key){
        $arr = [
            static::COIN_MINUS_PRIZE_CHANGE =>      '大转盘次数兑换',
            static::COIN_MINUS_ARTICLE_CHANGE =>    '头条文章次数兑换',
            static::COIN_MINUS_GOODS_DEDUCT =>      '实物商品购买抵扣',
            static::COIN_MINUS_COUPON_CHANGE =>     '商城优惠券兑换',
            static::COIN_MINUS_ARTICLE_READ =>      '文章阅读扣除',
            static::COIN_MINUS_ANSWER =>            '金币答题',
            static::COIN_MINUS_XXL =>               '参加消消乐',
            static::COIN_MINUS_LOTTERY_GOODS =>     '参与实物抽奖',
        ];
        return $arr[$key];
    }

}
