<?php
namespace App\Services\Dplus\Ios;
use App\Services\Dplus\IOSNotification;

class IOSBroadcast extends IOSNotification {
	function  __construct() {
		parent::__construct();
		$this->data["type"] = "broadcast";
	}
}