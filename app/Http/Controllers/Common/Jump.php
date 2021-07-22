<?php

namespace App\Http\Controllers\Common;

class Jump
{
    static function win($msg, $url, $time = 2333, $note = "页面跳转中")
    {
        header('Content-Type:text/html;charset=utf-8');
        echo "<link rel='stylesheet'href='http://a119112.oss-cn-beijing.aliyuncs.com/web_shop/web_GRA/sweet-alert.css'/><script src='http://a119112.oss-cn-beijing.aliyuncs.com/web_shop/web_GRA/sweet-alert.min.js'></script><script>window.onload=myfun;countDown();function myfun(){swal('$msg','$note','success');delayURL(\"$url\",$time);document.querySelector('.confirm').onclick=function(){location.href='$url'}};function delayURL(url,time){setTimeout(\"top.location.href='\" + url + \"'\",time)}</script>";
        exit();
    }

    static function back($msg, $url, $time = 2333, $note = "页面跳转中")
    {
        header('Content-Type:text/html;charset=utf-8');
        echo "<link rel='stylesheet'href='http://a119112.oss-cn-beijing.aliyuncs.com/web_shop/web_GRA/sweet-alert.css'/>
<script src='http://a119112.oss-cn-beijing.aliyuncs.com/web_shop/web_GRA/sweet-alert.min.js'></script>
<script>window.onload=myfun;countDown();function myfun(){swal('$msg','$note','success');
document.querySelector('.confirm').onclick=function(){location.href='$url'}};
function delayURL(url,time){setTimeout(\"top.location.href='\" + url + \"'\",time)}</script>";
        exit();
    }


    static function fail($msg, $time = 2333, $note = "页面跳转中")
    {
        header('Content-Type:text/html;charset=utf-8');
        echo "<link rel='stylesheet'href='http://a119112.oss-cn-beijing.aliyuncs.com/web_shop/web_GRA/sweet-alert.css'/><script src='http://a119112.oss-cn-beijing.aliyuncs.com/web_shop/web_GRA/sweet-alert.min.js'></script><script>window.onload=myfun;function myfun(){swal('$msg','$note','error');delayURL(3000);document.querySelector('.confirm').onclick=function(){window.history.back()}};function delayURL(time){setTimeout(\"window.history.back();\",$time)};</script>";
        exit();
    }

    static function success($msg, $back = -1, $time = 2333, $note = "页面跳转中")
    {
        header('Content-Type:text/html;charset=utf-8');
        echo "<link rel='stylesheet'href='http://a119112.oss-cn-beijing.aliyuncs.com/web_shop/web_GRA/sweet-alert.css'/><script src='http://a119112.oss-cn-beijing.aliyuncs.com/web_shop/web_GRA/sweet-alert.min.js'></script><script>window.onload=myfun;function myfun(){swal('$msg','$note','success');delayURL(3000);document.querySelector('.confirm').onclick=function(){window.history.go($back)}};function delayURL(time){setTimeout(\"window.history.go($back);\",$time)};</script>";
        exit();
    }
}

