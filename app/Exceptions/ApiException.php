<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/5/24 0024
 * Time: 下午 15:52
 */
namespace App\Exceptions;

class ApiException extends \Exception
{
    function __construct($msg='',$code='500')
    {
        parent::__construct($msg,$code);
    }
}