<?php

namespace App\Services\Common;

use App\Entitys\App\LiveInfo;

class LiveGroupConfig
{
    public static function produceGroupId()
    {
        $groupId = '';
        $beforeLen = 3;
        $rearLen = 9;
        $rearChars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

        while (true) {
            $before = '@';
            $rear = '#';
            for ($num = 0;$num < $beforeLen;$num++) {
                $before .= chr(rand(65, 90));
            }
            for ($num = 0;$num < $rearLen;$num++) {
                $rear .= $rearChars[mt_rand(0, strlen($rearChars) - 1)];
            }

            $groupId = $before . $rear;
            $result = LiveInfo::where('group_id', $groupId)->value('id');
            if (!$result) {
                break;
            }
        }

        return $groupId;
    }
}