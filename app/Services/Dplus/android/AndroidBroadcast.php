<?php
namespace App\Services\Dplus\Android;
use App\Services\Dplus\AndroidNotification;

class AndroidBroadcast extends AndroidNotification {
	function  __construct() {
		parent::__construct();
		$this->data["type"] = "broadcast";
	}
}