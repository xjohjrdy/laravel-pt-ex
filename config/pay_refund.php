<?php
/**
 * User: Administrator
 * Date: 2019/8/21/021
 * Time: 16:06
 * pay_refund
 */

return [
    //支付宝 PID
    'ali_pid' => '2088531728490041',

    //支付宝配置-退款用
    'ali_config' => [
        'app_id' => '2019080266097538',
        'notify_url' => '', //退款等操作用不到
        'return_url' => '', //退款等操作用不到
        'ali_public_key' => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAqZp6YpOomxg7Jdoj8xr+nCn0WNwm06Oqq8GXYZTM0cIAQIi0DXxdrLuhf33dEMSZuBYyB3rN9Ecu0dmZw1XrRnx/3RRVYxxuNacmdnnO48wQzBVp5t1K2CEo0D3F7rZrUtLFn9dNgRytW2F4zjO5ZTAtfAgTifAAzynS2XTTJGKAEq6rifkGo82QYy+r1Doe16MJs0bkRDBtLr1vjmDyj6kHlsIuwBrrxwYKVRMPq/NyPD5ILYNULsPGSqcJZNHTfbTwjZ/V8drOoZ0OtmVmkwzL+AQpWjwU7k3ujTrjbEPfnIRs+OlRqx9DjtFnhpjxiQTGi/Fh+sjOVxbrB4Fz4wIDAQAB',
        'private_key' => 'MIIEpAIBAAKCAQEArZxIQv8RgMZh10XEYepdj4HFOkP+i2FGtOiKBfRnZMDECrsRPmmVndtiwFJDY3r/q+TgwF7xuEc0bNmsyxGZsWg2mduCd8zNkoVdUDJY62LjHZoMjfTcF/Js8Q/I7hDxtP9Z/HW8AjgXCsFRZe7jYgxVUI5B8JdAGVHHrHdAYxMkVAkRNbNg/0x1Ms1E2wTfX8rMuDi+X7Aav+ewC0tk0eBQa+JiIHmyWP+o16T8hF6G+KmvCYIM0EF0+j6uQAxPRDIqMLQ92otX9fXc5M8L53ZYlG/oIsjX+mgqNJKDc3QlQyukoIeyaLhss3kVWyXBSASwaDNROPPPFfPJc+XX4wIDAQABAoIBAQClyFUg1N1r8QTBQvgS4HBTd4JU71UE1/FjE6OpqAeLqKAL9zKyPLUIttSH/oYnWu8GwFr2mDOO2z/uqbZdfAMd6/wn0/u0Vrf/sKa4zDspG5bxT4epjycVHwR09bPT0g5d3nGZqPvNyq4GMTB/fC1aFZjqgc3p5yToiLV5ta6ga7S4TvMCguAg5vVvHyt3ij9RjKhccILGMa3gmyDl7zwDVXDcLARZlu33VXJzkgaGIgSEJfo7MrjQS5YWeoBstzu7qjQFgB30TKPiGKNvpxhh3ANcmWwiWQ9n59QKbEUO+VwvYfrOYtBZbiprea937k+uhOGxdOY1xZ6foZ1CzdrRAoGBAOb0pm4oAuEhWjwuflo+DAB8Sr2Oia1+a3kQgVV3QifGiEX92YyzXHcY/AuS2VFE4cMGa/B1kVmvunj+0keNdg6bqQX5LLYHSRabBlWDwn1BEOYe/clgLZS6xPOH/rbjAVmQEFO6HP3FrgE5taro8MFo78Er1CiqBQg/DSI1rDgVAoGBAMBvuYImj5uxP1q0+vEIVgkQXcvkWBPndZ7nAPcloR2eJIGTP1wVUu9iVCjnBRv17Wkd/hnPgQ7gNFwxjFyWuRH47NLD87z8QslM4MvYz/ulJJbWnqGlH9+0AtHv6oOyYfIbjmavbpz99P5jWruOiA6q/UX/LGUhMrZbdgjV7xYXAoGBAMf/M+BQZa3u4+UZnfEnqd0BxPdBZ5gF0auUz2rjSzaGhZuWp71f1MKNsDWVhPsLWzU3amFgbe3sbt44TIAJ0CH2SfgSPtWimXgp5uJGpzUwEyIz1DF+R/pzgfoh2kElcxXL21el41Ueyf/lqZvG/DWAWZ4+BgrwsErjzsvBDtHBAoGAB8Z74F5efxPyU1/so8CKeWNH5u0bAfLgNcNvroy+rcut/e5NKRNTfoiijeSHuFF/fjfQBIr10/wLIY5+9V7Bq5A7QWjZeFFZAXkxvArOnIRXriCN2Eort4Y2dvUSrK+QdY/XeR6tgXphY8Xv7JQduzn9cc6VpKBqxCN96W17c5ECgYAcRswDyl6YdBdYgxF5KbBikDXgEkgwF7AJUBBpaOcdaVeJ5amui8noJZd0XGAiqW+MAMeqSf6SruHpFjnIPmCjPmZvBFrJ44A0BuxZ64lfq8sLr/vb0uptdQ6lem/YgGl5/6kBpHr5IgD/79EX4k9Tby7B79NAza5xQU9ZMt25RQ==',
        'log' => [ // optional
            'file' => './logs/alipay.log',
            //'level' => 'debug'
        ],
//        'mode' => 'dev', // optional,设置此参数，将进入沙箱模式
    ],


    //微信配置项-退款用 旧版
    'we_config' => [
        'appid' => 'wxd2d9077a3072b5db', // APP APPID
        'app_id' => 'wxd2d9077a3072b5db', // 公众号 APPID
//        'miniapp_id' => 'wxb3fxxxxxxxxxxx', // 小程序 APPID
        'mch_id' => '1521224461',
        'key' => 'wuhang1231wuhang7890wuhang886655',
        'notify_url' => '', //退款等操作用不到
        'cert_client' => '/data/Website/wechat/ssl/cert.pem', // optional，退款等情况时用到  线上
        'cert_key' => '/data/Website/wechat/ssl/key.pem',// optional，退款等情况时用到
//        'cert_client' => 'F:/11/cert.pem', // optional，退款等情况时用到  本地
//        'cert_key' => 'F:/11/key.pem',// optional，退款等情况时用到
        'log' => [ // optional
            'file' => './logs/wechat.log',
            'level' => 'info', // 建议生产环境等级调整为 info，开发环境为 debug
            'type' => 'single', // optional, 可选 daily.
            'max_file' => 30, // optional, 当 type 为 daily 时有效，默认 30 天
        ],
        'http' => [ // optional
            'timeout' => 5.0,
            'connect_timeout' => 5.0,
            // 更多配置项请参考 [Guzzle](https://guzzle-cn.readthedocs.io/zh_CN/latest/request-options.html)
        ],
        'mode' => 'normal', // optional, dev/hk;当为 `hk` 时，为香港 gateway。
    ],

    //微信配置项-退款用 新版
    'we_config_new' => [
        'appid' => 'wx59f17f4458534e73', // APP APPID
        'app_id' => 'wx59f17f4458534e73', // 公众号 APPID
//        'miniapp_id' => 'wxb3fxxxxxxxxxxx', // 小程序 APPID
        'mch_id' => '1550735471',
        'key' => 'wuhang1231wuhang7890wuhang886655',
        'notify_url' => '', //退款等操作用不到
        'cert_client' => '/data/Website/wechat/ssl/cert.pem', // optional，退款等情况时用到  线上
        'cert_key' => '/data/Website/wechat/ssl/key.pem',// optional，退款等情况时用到
//        'cert_client' => 'F:/11/cert.pem', // optional，退款等情况时用到  本地
//        'cert_key' => 'F:/11/key.pem',// optional，退款等情况时用到
        'log' => [ // optional
            'file' => './logs/wechat.log',
            'level' => 'info', // 建议生产环境等级调整为 info，开发环境为 debug
            'type' => 'single', // optional, 可选 daily.
            'max_file' => 30, // optional, 当 type 为 daily 时有效，默认 30 天
        ],
        'http' => [ // optional
            'timeout' => 5.0,
            'connect_timeout' => 5.0,
            // 更多配置项请参考 [Guzzle](https://guzzle-cn.readthedocs.io/zh_CN/latest/request-options.html)
        ],
        'mode' => 'normal', // optional, dev/hk;当为 `hk` 时，为香港 gateway。
    ],

];