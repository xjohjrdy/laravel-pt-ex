<?php

namespace App\Http\Controllers;

use App\Services\Crypt\RsaUtils;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
	use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

	protected $config_v1 = [
		'app_id' => '2019080266097538',
		'notify_url' => 'http://api.36qq.com/notify_url_v1',
		'return_url' => 'http://api.36qq.com/notify_url_v1',
		'ali_public_key' => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAqZp6YpOomxg7Jdoj8xr+nCn0WNwm06Oqq8GXYZTM0cIAQIi0DXxdrLuhf33dEMSZuBYyB3rN9Ecu0dmZw1XrRnx/3RRVYxxuNacmdnnO48wQzBVp5t1K2CEo0D3F7rZrUtLFn9dNgRytW2F4zjO5ZTAtfAgTifAAzynS2XTTJGKAEq6rifkGo82QYy+r1Doe16MJs0bkRDBtLr1vjmDyj6kHlsIuwBrrxwYKVRMPq/NyPD5ILYNULsPGSqcJZNHTfbTwjZ/V8drOoZ0OtmVmkwzL+AQpWjwU7k3ujTrjbEPfnIRs+OlRqx9DjtFnhpjxiQTGi/Fh+sjOVxbrB4Fz4wIDAQAB',
		'private_key' => 'MIIEpAIBAAKCAQEArZxIQv8RgMZh10XEYepdj4HFOkP+i2FGtOiKBfRnZMDECrsRPmmVndtiwFJDY3r/q+TgwF7xuEc0bNmsyxGZsWg2mduCd8zNkoVdUDJY62LjHZoMjfTcF/Js8Q/I7hDxtP9Z/HW8AjgXCsFRZe7jYgxVUI5B8JdAGVHHrHdAYxMkVAkRNbNg/0x1Ms1E2wTfX8rMuDi+X7Aav+ewC0tk0eBQa+JiIHmyWP+o16T8hF6G+KmvCYIM0EF0+j6uQAxPRDIqMLQ92otX9fXc5M8L53ZYlG/oIsjX+mgqNJKDc3QlQyukoIeyaLhss3kVWyXBSASwaDNROPPPFfPJc+XX4wIDAQABAoIBAQClyFUg1N1r8QTBQvgS4HBTd4JU71UE1/FjE6OpqAeLqKAL9zKyPLUIttSH/oYnWu8GwFr2mDOO2z/uqbZdfAMd6/wn0/u0Vrf/sKa4zDspG5bxT4epjycVHwR09bPT0g5d3nGZqPvNyq4GMTB/fC1aFZjqgc3p5yToiLV5ta6ga7S4TvMCguAg5vVvHyt3ij9RjKhccILGMa3gmyDl7zwDVXDcLARZlu33VXJzkgaGIgSEJfo7MrjQS5YWeoBstzu7qjQFgB30TKPiGKNvpxhh3ANcmWwiWQ9n59QKbEUO+VwvYfrOYtBZbiprea937k+uhOGxdOY1xZ6foZ1CzdrRAoGBAOb0pm4oAuEhWjwuflo+DAB8Sr2Oia1+a3kQgVV3QifGiEX92YyzXHcY/AuS2VFE4cMGa/B1kVmvunj+0keNdg6bqQX5LLYHSRabBlWDwn1BEOYe/clgLZS6xPOH/rbjAVmQEFO6HP3FrgE5taro8MFo78Er1CiqBQg/DSI1rDgVAoGBAMBvuYImj5uxP1q0+vEIVgkQXcvkWBPndZ7nAPcloR2eJIGTP1wVUu9iVCjnBRv17Wkd/hnPgQ7gNFwxjFyWuRH47NLD87z8QslM4MvYz/ulJJbWnqGlH9+0AtHv6oOyYfIbjmavbpz99P5jWruOiA6q/UX/LGUhMrZbdgjV7xYXAoGBAMf/M+BQZa3u4+UZnfEnqd0BxPdBZ5gF0auUz2rjSzaGhZuWp71f1MKNsDWVhPsLWzU3amFgbe3sbt44TIAJ0CH2SfgSPtWimXgp5uJGpzUwEyIz1DF+R/pzgfoh2kElcxXL21el41Ueyf/lqZvG/DWAWZ4+BgrwsErjzsvBDtHBAoGAB8Z74F5efxPyU1/so8CKeWNH5u0bAfLgNcNvroy+rcut/e5NKRNTfoiijeSHuFF/fjfQBIr10/wLIY5+9V7Bq5A7QWjZeFFZAXkxvArOnIRXriCN2Eort4Y2dvUSrK+QdY/XeR6tgXphY8Xv7JQduzn9cc6VpKBqxCN96W17c5ECgYAcRswDyl6YdBdYgxF5KbBikDXgEkgwF7AJUBBpaOcdaVeJ5amui8noJZd0XGAiqW+MAMeqSf6SruHpFjnIPmCjPmZvBFrJ44A0BuxZ64lfq8sLr/vb0uptdQ6lem/YgGl5/6kBpHr5IgD/79EX4k9Tby7B79NAza5xQU9ZMt25RQ==',
		'log' => [
			'file' => './logs/alipay.log',
		],
	];

	protected $config = [
		'app_id' => '2019080266097538',
		'notify_url' => 'http://api.36qq.com/notify_url',
		'return_url' => 'http://api.36qq.com/notify_url',
		'ali_public_key' => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAqZp6YpOomxg7Jdoj8xr+nCn0WNwm06Oqq8GXYZTM0cIAQIi0DXxdrLuhf33dEMSZuBYyB3rN9Ecu0dmZw1XrRnx/3RRVYxxuNacmdnnO48wQzBVp5t1K2CEo0D3F7rZrUtLFn9dNgRytW2F4zjO5ZTAtfAgTifAAzynS2XTTJGKAEq6rifkGo82QYy+r1Doe16MJs0bkRDBtLr1vjmDyj6kHlsIuwBrrxwYKVRMPq/NyPD5ILYNULsPGSqcJZNHTfbTwjZ/V8drOoZ0OtmVmkwzL+AQpWjwU7k3ujTrjbEPfnIRs+OlRqx9DjtFnhpjxiQTGi/Fh+sjOVxbrB4Fz4wIDAQAB',
		'private_key' => 'MIIEpAIBAAKCAQEArZxIQv8RgMZh10XEYepdj4HFOkP+i2FGtOiKBfRnZMDECrsRPmmVndtiwFJDY3r/q+TgwF7xuEc0bNmsyxGZsWg2mduCd8zNkoVdUDJY62LjHZoMjfTcF/Js8Q/I7hDxtP9Z/HW8AjgXCsFRZe7jYgxVUI5B8JdAGVHHrHdAYxMkVAkRNbNg/0x1Ms1E2wTfX8rMuDi+X7Aav+ewC0tk0eBQa+JiIHmyWP+o16T8hF6G+KmvCYIM0EF0+j6uQAxPRDIqMLQ92otX9fXc5M8L53ZYlG/oIsjX+mgqNJKDc3QlQyukoIeyaLhss3kVWyXBSASwaDNROPPPFfPJc+XX4wIDAQABAoIBAQClyFUg1N1r8QTBQvgS4HBTd4JU71UE1/FjE6OpqAeLqKAL9zKyPLUIttSH/oYnWu8GwFr2mDOO2z/uqbZdfAMd6/wn0/u0Vrf/sKa4zDspG5bxT4epjycVHwR09bPT0g5d3nGZqPvNyq4GMTB/fC1aFZjqgc3p5yToiLV5ta6ga7S4TvMCguAg5vVvHyt3ij9RjKhccILGMa3gmyDl7zwDVXDcLARZlu33VXJzkgaGIgSEJfo7MrjQS5YWeoBstzu7qjQFgB30TKPiGKNvpxhh3ANcmWwiWQ9n59QKbEUO+VwvYfrOYtBZbiprea937k+uhOGxdOY1xZ6foZ1CzdrRAoGBAOb0pm4oAuEhWjwuflo+DAB8Sr2Oia1+a3kQgVV3QifGiEX92YyzXHcY/AuS2VFE4cMGa/B1kVmvunj+0keNdg6bqQX5LLYHSRabBlWDwn1BEOYe/clgLZS6xPOH/rbjAVmQEFO6HP3FrgE5taro8MFo78Er1CiqBQg/DSI1rDgVAoGBAMBvuYImj5uxP1q0+vEIVgkQXcvkWBPndZ7nAPcloR2eJIGTP1wVUu9iVCjnBRv17Wkd/hnPgQ7gNFwxjFyWuRH47NLD87z8QslM4MvYz/ulJJbWnqGlH9+0AtHv6oOyYfIbjmavbpz99P5jWruOiA6q/UX/LGUhMrZbdgjV7xYXAoGBAMf/M+BQZa3u4+UZnfEnqd0BxPdBZ5gF0auUz2rjSzaGhZuWp71f1MKNsDWVhPsLWzU3amFgbe3sbt44TIAJ0CH2SfgSPtWimXgp5uJGpzUwEyIz1DF+R/pzgfoh2kElcxXL21el41Ueyf/lqZvG/DWAWZ4+BgrwsErjzsvBDtHBAoGAB8Z74F5efxPyU1/so8CKeWNH5u0bAfLgNcNvroy+rcut/e5NKRNTfoiijeSHuFF/fjfQBIr10/wLIY5+9V7Bq5A7QWjZeFFZAXkxvArOnIRXriCN2Eort4Y2dvUSrK+QdY/XeR6tgXphY8Xv7JQduzn9cc6VpKBqxCN96W17c5ECgYAcRswDyl6YdBdYgxF5KbBikDXgEkgwF7AJUBBpaOcdaVeJ5amui8noJZd0XGAiqW+MAMeqSf6SruHpFjnIPmCjPmZvBFrJ44A0BuxZ64lfq8sLr/vb0uptdQ6lem/YgGl5/6kBpHr5IgD/79EX4k9Tby7B79NAza5xQU9ZMt25RQ==',
		'log' => [
			'file' => './logs/alipay.log',
		],
	];
//    protected $wechat_config = [
//        'appid' => 'wxd2d9077a3072b5db',
//        'mch_id' => '1521224461',
//        'key' => 'wuhang1231wuhang7890wuhang886655',
//        'notify_url' => 'http://api.36qq.com/api/wechat_pay_for_notify',
//        'log' => [
//            'file' => './logs/wechat.log',
//            'level' => 'info',
//            'type' => 'single',
//            'max_file' => 30,
//        ],
//        'http' => [
//            'timeout' => 5.0,
//            'connect_timeout' => 5.0,
//        ],
//        'mode' => 'normal',
//    ];

	protected $wechat_config = [
		'appid' => 'wxd2d9077a3072b5db',
//        'appid' => 'wx59f17f4458534e73',
		'mch_id' => '1550735471',
		'key' => 'wuhang1231wuhang7890wuhang886655',
		'notify_url' => 'http://api.36qq.com/api/wechat_pay_for_notify',
		'log' => [
			'file' => './logs/wechat.log',
			'level' => 'info',
			'type' => 'single',
			'max_file' => 30,
		],
		'http' => [
			'timeout' => 5.0,
			'connect_timeout' => 5.0,
		],
		'mode' => 'normal',
	];

	/**
	 * 默认不加密
	 * @param $data
	 * @param int $is_encrypt
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function getResponse($data, $is_encrypt = 0)
	{
		$rsa = new RsaUtils();
		if ($is_encrypt) {
			$data = $rsa->rsaEncode($data);
		}
		return response()->json([
			'code' => 200,
			'msg' => '请求成功',
			'data' => $data
		]);
	}

	/**
	 * 默认不加密
	 * @param $code
	 * @param $msg
	 * @return \Illuminate\Http\JsonResponse
	 */
	public function getInfoResponse($code, $msg)
	{
		return response()->json([
			'code' => $code,
			'msg' => $msg
		]);
	}
}
