<?php

namespace App\Services\Dplus;

use Illuminate\Support\Facades\Storage;

class Dplus
{
    protected $appkey = NULL;
    protected $appMasterSecret = NULL;
    protected $timestamp = NULL;
    protected $validation_token = NULL;

    /**
     * Demo constructor.
     * @param string $type 1 安卓 2 ios
     */
    function __construct($type = 1)
    {

        $iosKey = '5dcd17923fc1952129000345';
        $iosSecret = '1a7bv7jutphozriadurmfcufwkadsfwd';
        $androidKey = '5dcd11fe3fc195e194000a16';
        $androidSecret = '6lwkmo024xoethse15sovdma9h78sibw';
        if ($type == 1) {
            $this->appkey = $androidKey;
            $this->appMasterSecret = $androidSecret;
        } else {
            $this->appkey = $iosKey;
            $this->appMasterSecret = $iosSecret;
        }
        $this->timestamp = strval(time());
    }

    /**
     * 广播
     * @param $ticker
     * @param $title
     * @param $text
     * @param $state
     * @param $inform_sign
     * @param $inform_url
     * @param $inform_data
     */
    function sendAndroidBroadcast($ticker, $title, $text, $state, $inform_sign, $inform_url, $inform_data)
    {
        try {
            $brocast = new \AndroidBroadcast();
            $brocast->setAppMasterSecret($this->appMasterSecret);
            $brocast->setPredefinedKeyValue("appkey", $this->appkey);
            $brocast->setPredefinedKeyValue("timestamp", $this->timestamp);
            $brocast->setPredefinedKeyValue("ticker", $ticker);
            $brocast->setPredefinedKeyValue("title", $title);
            $brocast->setPredefinedKeyValue("text", $text);
            $brocast->setPredefinedKeyValue("after_open", "go_app");
            // Set 'production_mode' to 'false' if it's a test device.
            // For how to register a test device, please see the developer doc.
            $brocast->setPredefinedKeyValue("production_mode", "true");
            // [optional]Set extra fields
            $brocast->setExtraField("type", $state);
            $brocast->setExtraField("all", 1);
            $brocast->setExtraField("inform_sign", $inform_sign);
            $brocast->setExtraField("inform_url", $inform_url);
            $brocast->setExtraField("inform_data", $inform_data);
//			print("Sending broadcast notification, please wait...\r\n");
            $res = $brocast->send();
            $res = json_decode($res, true);
            if ($res['ret'] != 'SUCCESS') {
                $this->androidLog(@$res['data']['error_code'] . '---' . @$res['data']['error_msg']);
            }
            return $res;
        } catch (\Throwable $e) {
            $this->androidLog('行数：' . $e->getLine() . ' msg:' . $e->getMessage());
        }
    }

//	function sendAndroidUnicast($ticker, $title, $text, $device) {
//		try {
//			$unicast = new AndroidUnicast();
//			$unicast->setAppMasterSecret($this->appMasterSecret);
//			$unicast->setPredefinedKeyValue("appkey",           $this->appkey);
//			$unicast->setPredefinedKeyValue("timestamp",        $this->timestamp);
//			// Set your device tokens here
//			$unicast->setPredefinedKeyValue("device_tokens",    $device);
//			$unicast->setPredefinedKeyValue("ticker",           $ticker);
//			$unicast->setPredefinedKeyValue("title",            $title);
//			$unicast->setPredefinedKeyValue("text",             $text);
//			$unicast->setPredefinedKeyValue("after_open",       "go_app");
//			// Set 'production_mode' to 'false' if it's a test device.
//			// For how to register a test device, please see the developer doc.
//			$unicast->setPredefinedKeyValue("production_mode", "true");
//			// Set extra fields
//			$unicast->setExtraField("test", "helloworld");
//			print("Sending unicast notification, please wait...\r\n");
//			$unicast->send();
//			print("Sent SUCCESS\r\n");
//		} catch (\Throwable $e) {
//			print("Caught \Throwable: " . $e->getMessage());
//		}
//	}

//	function sendAndroidFilecast() {
//		try {
//			$filecast = new AndroidFilecast();
//			$filecast->setAppMasterSecret($this->appMasterSecret);
//			$filecast->setPredefinedKeyValue("appkey",           $this->appkey);
//			$filecast->setPredefinedKeyValue("timestamp",        $this->timestamp);
//			$filecast->setPredefinedKeyValue("ticker",           "Android filecast ticker");
//			$filecast->setPredefinedKeyValue("title",            "Android filecast title");
//			$filecast->setPredefinedKeyValue("text",             "Android filecast text");
//			$filecast->setPredefinedKeyValue("after_open",       "go_app");  //go to app
//			print("Uploading file contents, please wait...\r\n");
//			// Upload your device tokens, and use '\n' to split them if there are multiple tokens
//			$filecast->uploadContents("aa"."\n"."bb");
//			print("Sending filecast notification, please wait...\r\n");
//			$filecast->send();
//			print("Sent SUCCESS\r\n");
//		} catch (\Throwable $e) {
//			print("Caught \Throwable: " . $e->getMessage());
//		}
//	}

//	function sendAndroidGroupcast() {
//		try {
//			/*
//		 	 *  Construct the filter condition:
//		 	 *  "where":
//		 	 *	{
//    	 	 *		"and":
//    	 	 *		[
//      	 	 *			{"tag":"test"},
//      	 	 *			{"tag":"Test"}
//    	 	 *		]
//		 	 *	}
//		 	 */
//			$filter = 	array(
//							"where" => 	array(
//								    		"and" 	=>  array(
//								    						array(
//							     								"tag" => "test"
//															),
//								     						array(
//							     								"tag" => "Test"
//								     						)
//								     		 			)
//								   		)
//					  	);
//
//			$groupcast = new AndroidGroupcast();
//			$groupcast->setAppMasterSecret($this->appMasterSecret);
//			$groupcast->setPredefinedKeyValue("appkey",           $this->appkey);
//			$groupcast->setPredefinedKeyValue("timestamp",        $this->timestamp);
//			// Set the filter condition
//			$groupcast->setPredefinedKeyValue("filter",           $filter);
//			$groupcast->setPredefinedKeyValue("ticker",           "Android groupcast ticker");
//			$groupcast->setPredefinedKeyValue("title",            "Android groupcast title");
//			$groupcast->setPredefinedKeyValue("text",             "Android groupcast text");
//			$groupcast->setPredefinedKeyValue("after_open",       "go_app");
//			// Set 'production_mode' to 'false' if it's a test device.
//			// For how to register a test device, please see the developer doc.
//			$groupcast->setPredefinedKeyValue("production_mode", "true");
//			print("Sending groupcast notification, please wait...\r\n");
//			$groupcast->send();
//			print("Sent SUCCESS\r\n");
//		} catch (\Throwable $e) {
//			print("Caught \Throwable: " . $e->getMessage());
//		}
//	}

    /**
     * 单播
     * @param $ticker 提示文字
     * @param $title 提示标题
     * @param $text 提示描述
     * @param $app_id
     */
    function sendAndroidCustomizedcast($ticker, $title, $text, $app_id, $state, $inform_sign, $inform_url, $inform_data)
    {
        try {
            $customizedcast = new \AndroidCustomizedcast();
            $customizedcast->setAppMasterSecret($this->appMasterSecret);
            $customizedcast->setPredefinedKeyValue("appkey", $this->appkey);
            $customizedcast->setPredefinedKeyValue("timestamp", $this->timestamp);
            // Set your alias here, and use comma to split them if there are multiple alias.
            // And if you have many alias, you can also upload a file containing these alias, then
            // use file_id to send customized notification.
            $customizedcast->setPredefinedKeyValue("alias", $app_id);
            // Set your alias_type here
            $customizedcast->setPredefinedKeyValue("alias_type", "putaoliulanqi");
            $customizedcast->setPredefinedKeyValue("ticker", $ticker);
            $customizedcast->setPredefinedKeyValue("title", $title);
            $customizedcast->setPredefinedKeyValue("text", $text);
            $customizedcast->setPredefinedKeyValue("after_open", "go_app");
            $customizedcast->setExtraField("type", $state);
            $customizedcast->setExtraField("all", 0);
            $customizedcast->setExtraField("inform_sign", $inform_sign);
            $customizedcast->setExtraField("inform_url", $inform_url);
            $customizedcast->setExtraField("inform_data", $inform_data);
            $res = $customizedcast->send();
            $res = json_decode($res, true);
            if ($res['ret'] != 'SUCCESS') {
                $this->androidLog(@$res['data']['error_code'] . '---' . @$res['data']['error_msg']);
            }
            return $res;
        } catch (\Throwable $e) {
            $this->androidLog('行数：' . $e->getLine() . ' msg:' . $e->getMessage());
        }
    }

//	function sendAndroidCustomizedcastFileId() {
//		try {
//			$customizedcast = new AndroidCustomizedcast();
//			$customizedcast->setAppMasterSecret($this->appMasterSecret);
//			$customizedcast->setPredefinedKeyValue("appkey",           $this->appkey);
//			$customizedcast->setPredefinedKeyValue("timestamp",        $this->timestamp);
//			// if you have many alias, you can also upload a file containing these alias, then
//			// use file_id to send customized notification.
//			$customizedcast->uploadContents("aa"."\n"."bb");
//			// Set your alias_type here
//			$customizedcast->setPredefinedKeyValue("alias_type",       "putaoliulanqi");
//			$customizedcast->setPredefinedKeyValue("ticker",           "Android customizedcast ticker");
//			$customizedcast->setPredefinedKeyValue("title",            "Android customizedcast title");
//			$customizedcast->setPredefinedKeyValue("text",             "Android customizedcast text");
//			$customizedcast->setPredefinedKeyValue("after_open",       "go_app");
//			print("Sending customizedcast notification, please wait...\r\n");
//			$customizedcast->send();
//			print("Sent SUCCESS\r\n");
//		} catch (\Throwable $e) {
//			print("Caught \Throwable: " . $e->getMessage());
//		}
//	}

    function sendIOSBroadcast($title, $state, $inform_sign, $inform_url, $inform_data)
    {
        try {
            $brocast = new \IOSBroadcast();
            $brocast->setAppMasterSecret($this->appMasterSecret);
            $brocast->setPredefinedKeyValue("appkey", $this->appkey);
            $brocast->setPredefinedKeyValue("timestamp", $this->timestamp);

            $brocast->setPredefinedKeyValue("alert", $title);
            $brocast->setPredefinedKeyValue("badge", 0);
            $brocast->setPredefinedKeyValue("sound", "chime");
            // Set 'production_mode' to 'true' if your app is under production mode
            $brocast->setPredefinedKeyValue("production_mode", "false");
            // Set customized fields。
            $brocast->setCustomizedField("type", "$state");
            $brocast->setCustomizedField("all", 1);
            $brocast->setCustomizedField("inform_sign", $inform_sign);
            $brocast->setCustomizedField("inform_url", $inform_url);
            $brocast->setCustomizedField("inform_data", $inform_data);
            $res = $brocast->send();
            $res = json_decode($res, true);
            if ($res['ret'] != 'SUCCESS') {
                $this->iosLog(@$res['data']['error_code'] . '---' . @$res['data']['error_msg']);
            }
            return $res;
        } catch (\Throwable $e) {
            $this->iosLog('行数：' . $e->getLine() . ' msg:' . $e->getMessage());
        }
    }

//	function sendIOSUnicast() {
//		try {
//			$unicast = new IOSUnicast();
//			$unicast->setAppMasterSecret($this->appMasterSecret);
//			$unicast->setPredefinedKeyValue("appkey",           $this->appkey);
//			$unicast->setPredefinedKeyValue("timestamp",        $this->timestamp);
//			// Set your device tokens here
//			$unicast->setPredefinedKeyValue("device_tokens",    "xx");
//			$unicast->setPredefinedKeyValue("alert", "IOS 单播测试");
//			$unicast->setPredefinedKeyValue("badge", 0);
//			$unicast->setPredefinedKeyValue("sound", "chime");
//			// Set 'production_mode' to 'true' if your app is under production mode
//			$unicast->setPredefinedKeyValue("production_mode", "false");
//			// Set customized fields
//			$unicast->setCustomizedField("test", "helloworld");
//			print("Sending unicast notification, please wait...\r\n");
//			$unicast->send();
//			print("Sent SUCCESS\r\n");
//		} catch (\Throwable $e) {
//			print("Caught \Throwable: " . $e->getMessage());
//		}
//	}

//	function sendIOSFilecast() {
//		try {
//			$filecast = new IOSFilecast();
//			$filecast->setAppMasterSecret($this->appMasterSecret);
//			$filecast->setPredefinedKeyValue("appkey",           $this->appkey);
//			$filecast->setPredefinedKeyValue("timestamp",        $this->timestamp);
//
//			$filecast->setPredefinedKeyValue("alert", "IOS 文件播测试");
//			$filecast->setPredefinedKeyValue("badge", 0);
//			$filecast->setPredefinedKeyValue("sound", "chime");
//			// Set 'production_mode' to 'true' if your app is under production mode
//			$filecast->setPredefinedKeyValue("production_mode", "false");
////			print("Uploading file contents, please wait...\r\n");
//			// Upload your device tokens, and use '\n' to split them if there are multiple tokens
//			$filecast->uploadContents("aa"."\n"."bb");
////			print("Sending filecast notification, please wait...\r\n");
//			$filecast->send();
//			print("Sent SUCCESS\r\n");
//		} catch (\Throwable $e) {
//			print("Caught \Throwable: " . $e->getMessage());
//		}
//	}

//	function sendIOSGroupcast() {
//		try {
//			/*
//		 	 *  Construct the filter condition:
//		 	 *  "where":
//		 	 *	{
//    	 	 *		"and":
//    	 	 *		[
//      	 	 *			{"tag":"iostest"}
//    	 	 *		]
//		 	 *	}
//		 	 */
//			$filter = 	array(
//							"where" => 	array(
//								    		"and" 	=>  array(
//								    						array(
//							     								"tag" => "iostest"
//															)
//								     		 			)
//								   		)
//					  	);
//
//			$groupcast = new IOSGroupcast();
//			$groupcast->setAppMasterSecret($this->appMasterSecret);
//			$groupcast->setPredefinedKeyValue("appkey",           $this->appkey);
//			$groupcast->setPredefinedKeyValue("timestamp",        $this->timestamp);
//			// Set the filter condition
//			$groupcast->setPredefinedKeyValue("filter",           $filter);
//			$groupcast->setPredefinedKeyValue("alert", "IOS 组播测试");
//			$groupcast->setPredefinedKeyValue("badge", 0);
//			$groupcast->setPredefinedKeyValue("sound", "chime");
//			// Set 'production_mode' to 'true' if your app is under production mode
//			$groupcast->setPredefinedKeyValue("production_mode", "false");
//			print("Sending groupcast notification, please wait...\r\n");
//			$groupcast->send();
//			print("Sent SUCCESS\r\n");
//		} catch (\Throwable $e) {
//			print("Caught \Throwable: " . $e->getMessage());
//		}
//	}

    function sendIOSCustomizedcast($title, $app_id, $state, $inform_sign, $inform_url, $inform_data)
    {
        try {
            $customizedcast = new \IOSCustomizedcast();
            $customizedcast->setAppMasterSecret($this->appMasterSecret);
            $customizedcast->setPredefinedKeyValue("appkey", $this->appkey);
            $customizedcast->setPredefinedKeyValue("timestamp", $this->timestamp);

            // Set your alias here, and use comma to split them if there are multiple alias.
            // And if you have many alias, you can also upload a file containing these alias, then
            // use file_id to send customized notification.
            $customizedcast->setPredefinedKeyValue("alias", $app_id);
            // Set your alias_type here
            $customizedcast->setPredefinedKeyValue("alias_type", "putaoliulanqi");
            $customizedcast->setPredefinedKeyValue("alert", $title);
            $customizedcast->setPredefinedKeyValue("badge", 0);
            $customizedcast->setPredefinedKeyValue("sound", "chime");
            // Set 'production_mode' to 'true' if your app is under production mode
            $customizedcast->setPredefinedKeyValue("production_mode", "true");
            $customizedcast->setCustomizedField("type", "$state");
            $customizedcast->setCustomizedField("all", 0);
            $customizedcast->setCustomizedField("inform_sign", $inform_sign);
            $customizedcast->setCustomizedField("inform_url", $inform_url);
            $customizedcast->setCustomizedField("inform_data", $inform_data);
//			print("Sending customizedcast notification, please wait...\r\n");
            $res = $customizedcast->send();
            $res = json_decode($res, true);
            if ($res['ret'] != 'SUCCESS') {
                $this->iosLog(@$res['data']['error_code'] . '---' . @$res['data']['error_msg']);
            }
            return $res;
        } catch (\Throwable $e) {
            $this->iosLog('行数：' . $e->getLine() . ' msg:' . $e->getMessage());
        }
    }

    private function androidLog($msg)
    {
        Storage::disk('local')->append('DPlus/Android.txt', var_export(date('Y-m-d H:i:s', time()) . '  ' . $msg, true));
    }

    private function iosLog($msg)
    {
        Storage::disk('local')->append('DPlus/Ios.txt', var_export(date('Y-m-d H:i:s', time()) . '  ' . $msg, true));
    }
}

// Set your appkey and master secret here
//$demo = new Dplus(1); // 1 安卓  2 ios
//$demo->sendAndroidBroadcast(); // 安卓发送全部
//$demo->sendAndroidCustomizedcast(); // 安卓单个用户发送
//$demo->sendIOSBroadcast(); // ios 发送全部
//$demo->sendIOSCustomizedcast(); // ios 发送单个用户
/* these methods are all available, just fill in some fields and do the test
 * $demo->sendAndroidBroadcast();
 * $demo->sendAndroidFilecast();
 * $demo->sendAndroidGroupcast();
 * $demo->sendAndroidCustomizedcast();
 * $demo->sendAndroidCustomizedcastFileId();
 *
 * $demo->sendIOSBroadcast();
 * $demo->sendIOSUnicast();
 * $demo->sendIOSFilecast();
 * $demo->sendIOSGroupcast();
 * $demo->sendIOSCustomizedcast();
 */

