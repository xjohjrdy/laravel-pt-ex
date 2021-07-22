<?php

namespace App\Tools;

class ObjectDataHandle
{
    public static function handle($query)
    {
        if ($query) {
            return $query->toArray();
        }

        return null;
    }
}