<?php

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Register The Composer Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader
| for our application. We just need to utilize it! We'll require it
| into the script here so we do not have to manually load any of
| our application's PHP classes. It just feels great to relax.
|
*/

require __DIR__ . '/../vendor/autoload.php';
include __DIR__ . '/../app/Library/ali-taobaoke/TopSdk.php';
include __DIR__ . '/../app/Library/cloud-pay-sdk/acp_service.php';
require_once __DIR__ . '/../app/Library/aliyun-oss-php-sdk-2.3.0.phar';
include __DIR__ . '/../app/Library/umen-dplus/Index.php';
