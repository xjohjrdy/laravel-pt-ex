<?php

return [
    // 我为圈主热搜关键词
    'hot_title' => [
        '女装外贸',
        '女装外贸',
        '女装外贸',
        '女装外贸',
        '女装外贸',
        '女装外贸',
    ],

    //关键词过滤
    'keywords' => [
        '黄',
        '赌',
        '毒',
    ],

    // 圈子竞价默认价格
    'price' => 600,

    //返还给前圈主葡萄币
    'return_money' => 0.91666,

    //圈子模块支付宝配置
    'ali_config' => [
        'app_id' => '2019080266097538',
//        'notify_url' => 'http://vv.uub.me/api/circle_host_XxX_ali_notify',
//        'return_url' => 'http://vv.uub.me/api/circle_host_XxX_ali_notify',
        'notify_url' => 'http://api_new.36qq.com/api/circle_host_XxX_ali_notify',
        'return_url' => 'http://api_new.36qq.com/api/circle_host_XxX_ali_notify',
        'ali_public_key' => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAqZp6YpOomxg7Jdoj8xr+nCn0WNwm06Oqq8GXYZTM0cIAQIi0DXxdrLuhf33dEMSZuBYyB3rN9Ecu0dmZw1XrRnx/3RRVYxxuNacmdnnO48wQzBVp5t1K2CEo0D3F7rZrUtLFn9dNgRytW2F4zjO5ZTAtfAgTifAAzynS2XTTJGKAEq6rifkGo82QYy+r1Doe16MJs0bkRDBtLr1vjmDyj6kHlsIuwBrrxwYKVRMPq/NyPD5ILYNULsPGSqcJZNHTfbTwjZ/V8drOoZ0OtmVmkwzL+AQpWjwU7k3ujTrjbEPfnIRs+OlRqx9DjtFnhpjxiQTGi/Fh+sjOVxbrB4Fz4wIDAQAB',
        'private_key' => 'MIIEpAIBAAKCAQEArZxIQv8RgMZh10XEYepdj4HFOkP+i2FGtOiKBfRnZMDECrsRPmmVndtiwFJDY3r/q+TgwF7xuEc0bNmsyxGZsWg2mduCd8zNkoVdUDJY62LjHZoMjfTcF/Js8Q/I7hDxtP9Z/HW8AjgXCsFRZe7jYgxVUI5B8JdAGVHHrHdAYxMkVAkRNbNg/0x1Ms1E2wTfX8rMuDi+X7Aav+ewC0tk0eBQa+JiIHmyWP+o16T8hF6G+KmvCYIM0EF0+j6uQAxPRDIqMLQ92otX9fXc5M8L53ZYlG/oIsjX+mgqNJKDc3QlQyukoIeyaLhss3kVWyXBSASwaDNROPPPFfPJc+XX4wIDAQABAoIBAQClyFUg1N1r8QTBQvgS4HBTd4JU71UE1/FjE6OpqAeLqKAL9zKyPLUIttSH/oYnWu8GwFr2mDOO2z/uqbZdfAMd6/wn0/u0Vrf/sKa4zDspG5bxT4epjycVHwR09bPT0g5d3nGZqPvNyq4GMTB/fC1aFZjqgc3p5yToiLV5ta6ga7S4TvMCguAg5vVvHyt3ij9RjKhccILGMa3gmyDl7zwDVXDcLARZlu33VXJzkgaGIgSEJfo7MrjQS5YWeoBstzu7qjQFgB30TKPiGKNvpxhh3ANcmWwiWQ9n59QKbEUO+VwvYfrOYtBZbiprea937k+uhOGxdOY1xZ6foZ1CzdrRAoGBAOb0pm4oAuEhWjwuflo+DAB8Sr2Oia1+a3kQgVV3QifGiEX92YyzXHcY/AuS2VFE4cMGa/B1kVmvunj+0keNdg6bqQX5LLYHSRabBlWDwn1BEOYe/clgLZS6xPOH/rbjAVmQEFO6HP3FrgE5taro8MFo78Er1CiqBQg/DSI1rDgVAoGBAMBvuYImj5uxP1q0+vEIVgkQXcvkWBPndZ7nAPcloR2eJIGTP1wVUu9iVCjnBRv17Wkd/hnPgQ7gNFwxjFyWuRH47NLD87z8QslM4MvYz/ulJJbWnqGlH9+0AtHv6oOyYfIbjmavbpz99P5jWruOiA6q/UX/LGUhMrZbdgjV7xYXAoGBAMf/M+BQZa3u4+UZnfEnqd0BxPdBZ5gF0auUz2rjSzaGhZuWp71f1MKNsDWVhPsLWzU3amFgbe3sbt44TIAJ0CH2SfgSPtWimXgp5uJGpzUwEyIz1DF+R/pzgfoh2kElcxXL21el41Ueyf/lqZvG/DWAWZ4+BgrwsErjzsvBDtHBAoGAB8Z74F5efxPyU1/so8CKeWNH5u0bAfLgNcNvroy+rcut/e5NKRNTfoiijeSHuFF/fjfQBIr10/wLIY5+9V7Bq5A7QWjZeFFZAXkxvArOnIRXriCN2Eort4Y2dvUSrK+QdY/XeR6tgXphY8Xv7JQduzn9cc6VpKBqxCN96W17c5ECgYAcRswDyl6YdBdYgxF5KbBikDXgEkgwF7AJUBBpaOcdaVeJ5amui8noJZd0XGAiqW+MAMeqSf6SruHpFjnIPmCjPmZvBFrJ44A0BuxZ64lfq8sLr/vb0uptdQ6lem/YgGl5/6kBpHr5IgD/79EX4k9Tby7B79NAza5xQU9ZMt25RQ==',
        'log' => [ // optional
            'file' => './logs/alipay.log',
            //'level' => 'debug'
        ],
        //'mode' => 'dev', // optional,设置此参数，将进入沙箱模式
    ],

    //圈子发红包模块支付宝配置
    'ali_red_config' => [
        'app_id' => '2019080266097538',
        'notify_url' => 'http://api_new.36qq.com/api/circle_red_XxX_ali_notify',
        'return_url' => 'http://api_new.36qq.com/api/circle_red_XxX_ali_notify',
        'ali_public_key' => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAqZp6YpOomxg7Jdoj8xr+nCn0WNwm06Oqq8GXYZTM0cIAQIi0DXxdrLuhf33dEMSZuBYyB3rN9Ecu0dmZw1XrRnx/3RRVYxxuNacmdnnO48wQzBVp5t1K2CEo0D3F7rZrUtLFn9dNgRytW2F4zjO5ZTAtfAgTifAAzynS2XTTJGKAEq6rifkGo82QYy+r1Doe16MJs0bkRDBtLr1vjmDyj6kHlsIuwBrrxwYKVRMPq/NyPD5ILYNULsPGSqcJZNHTfbTwjZ/V8drOoZ0OtmVmkwzL+AQpWjwU7k3ujTrjbEPfnIRs+OlRqx9DjtFnhpjxiQTGi/Fh+sjOVxbrB4Fz4wIDAQAB',
        'private_key' => 'MIIEpAIBAAKCAQEArZxIQv8RgMZh10XEYepdj4HFOkP+i2FGtOiKBfRnZMDECrsRPmmVndtiwFJDY3r/q+TgwF7xuEc0bNmsyxGZsWg2mduCd8zNkoVdUDJY62LjHZoMjfTcF/Js8Q/I7hDxtP9Z/HW8AjgXCsFRZe7jYgxVUI5B8JdAGVHHrHdAYxMkVAkRNbNg/0x1Ms1E2wTfX8rMuDi+X7Aav+ewC0tk0eBQa+JiIHmyWP+o16T8hF6G+KmvCYIM0EF0+j6uQAxPRDIqMLQ92otX9fXc5M8L53ZYlG/oIsjX+mgqNJKDc3QlQyukoIeyaLhss3kVWyXBSASwaDNROPPPFfPJc+XX4wIDAQABAoIBAQClyFUg1N1r8QTBQvgS4HBTd4JU71UE1/FjE6OpqAeLqKAL9zKyPLUIttSH/oYnWu8GwFr2mDOO2z/uqbZdfAMd6/wn0/u0Vrf/sKa4zDspG5bxT4epjycVHwR09bPT0g5d3nGZqPvNyq4GMTB/fC1aFZjqgc3p5yToiLV5ta6ga7S4TvMCguAg5vVvHyt3ij9RjKhccILGMa3gmyDl7zwDVXDcLARZlu33VXJzkgaGIgSEJfo7MrjQS5YWeoBstzu7qjQFgB30TKPiGKNvpxhh3ANcmWwiWQ9n59QKbEUO+VwvYfrOYtBZbiprea937k+uhOGxdOY1xZ6foZ1CzdrRAoGBAOb0pm4oAuEhWjwuflo+DAB8Sr2Oia1+a3kQgVV3QifGiEX92YyzXHcY/AuS2VFE4cMGa/B1kVmvunj+0keNdg6bqQX5LLYHSRabBlWDwn1BEOYe/clgLZS6xPOH/rbjAVmQEFO6HP3FrgE5taro8MFo78Er1CiqBQg/DSI1rDgVAoGBAMBvuYImj5uxP1q0+vEIVgkQXcvkWBPndZ7nAPcloR2eJIGTP1wVUu9iVCjnBRv17Wkd/hnPgQ7gNFwxjFyWuRH47NLD87z8QslM4MvYz/ulJJbWnqGlH9+0AtHv6oOyYfIbjmavbpz99P5jWruOiA6q/UX/LGUhMrZbdgjV7xYXAoGBAMf/M+BQZa3u4+UZnfEnqd0BxPdBZ5gF0auUz2rjSzaGhZuWp71f1MKNsDWVhPsLWzU3amFgbe3sbt44TIAJ0CH2SfgSPtWimXgp5uJGpzUwEyIz1DF+R/pzgfoh2kElcxXL21el41Ueyf/lqZvG/DWAWZ4+BgrwsErjzsvBDtHBAoGAB8Z74F5efxPyU1/so8CKeWNH5u0bAfLgNcNvroy+rcut/e5NKRNTfoiijeSHuFF/fjfQBIr10/wLIY5+9V7Bq5A7QWjZeFFZAXkxvArOnIRXriCN2Eort4Y2dvUSrK+QdY/XeR6tgXphY8Xv7JQduzn9cc6VpKBqxCN96W17c5ECgYAcRswDyl6YdBdYgxF5KbBikDXgEkgwF7AJUBBpaOcdaVeJ5amui8noJZd0XGAiqW+MAMeqSf6SruHpFjnIPmCjPmZvBFrJ44A0BuxZ64lfq8sLr/vb0uptdQ6lem/YgGl5/6kBpHr5IgD/79EX4k9Tby7B79NAza5xQU9ZMt25RQ==',
        'log' => [ // optional
            'file' => './logs/alipay.log',
            //'level' => 'debug'
        ],
        //'mode' => 'dev', // optional,设置此参数，将进入沙箱模式
    ],

    //进入圈子支付宝配置
    'ali_add_config' => [
        'app_id' => '2019080266097538',
        'notify_url' => 'http://api_new.36qq.com/api/notify_url_circle_wuhang_add',
        'return_url' => 'http://api_new.36qq.com/api/notify_url_circle_wuhang_add',
        'ali_public_key' => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAqZp6YpOomxg7Jdoj8xr+nCn0WNwm06Oqq8GXYZTM0cIAQIi0DXxdrLuhf33dEMSZuBYyB3rN9Ecu0dmZw1XrRnx/3RRVYxxuNacmdnnO48wQzBVp5t1K2CEo0D3F7rZrUtLFn9dNgRytW2F4zjO5ZTAtfAgTifAAzynS2XTTJGKAEq6rifkGo82QYy+r1Doe16MJs0bkRDBtLr1vjmDyj6kHlsIuwBrrxwYKVRMPq/NyPD5ILYNULsPGSqcJZNHTfbTwjZ/V8drOoZ0OtmVmkwzL+AQpWjwU7k3ujTrjbEPfnIRs+OlRqx9DjtFnhpjxiQTGi/Fh+sjOVxbrB4Fz4wIDAQAB',
        'private_key' => 'MIIEpAIBAAKCAQEArZxIQv8RgMZh10XEYepdj4HFOkP+i2FGtOiKBfRnZMDECrsRPmmVndtiwFJDY3r/q+TgwF7xuEc0bNmsyxGZsWg2mduCd8zNkoVdUDJY62LjHZoMjfTcF/Js8Q/I7hDxtP9Z/HW8AjgXCsFRZe7jYgxVUI5B8JdAGVHHrHdAYxMkVAkRNbNg/0x1Ms1E2wTfX8rMuDi+X7Aav+ewC0tk0eBQa+JiIHmyWP+o16T8hF6G+KmvCYIM0EF0+j6uQAxPRDIqMLQ92otX9fXc5M8L53ZYlG/oIsjX+mgqNJKDc3QlQyukoIeyaLhss3kVWyXBSASwaDNROPPPFfPJc+XX4wIDAQABAoIBAQClyFUg1N1r8QTBQvgS4HBTd4JU71UE1/FjE6OpqAeLqKAL9zKyPLUIttSH/oYnWu8GwFr2mDOO2z/uqbZdfAMd6/wn0/u0Vrf/sKa4zDspG5bxT4epjycVHwR09bPT0g5d3nGZqPvNyq4GMTB/fC1aFZjqgc3p5yToiLV5ta6ga7S4TvMCguAg5vVvHyt3ij9RjKhccILGMa3gmyDl7zwDVXDcLARZlu33VXJzkgaGIgSEJfo7MrjQS5YWeoBstzu7qjQFgB30TKPiGKNvpxhh3ANcmWwiWQ9n59QKbEUO+VwvYfrOYtBZbiprea937k+uhOGxdOY1xZ6foZ1CzdrRAoGBAOb0pm4oAuEhWjwuflo+DAB8Sr2Oia1+a3kQgVV3QifGiEX92YyzXHcY/AuS2VFE4cMGa/B1kVmvunj+0keNdg6bqQX5LLYHSRabBlWDwn1BEOYe/clgLZS6xPOH/rbjAVmQEFO6HP3FrgE5taro8MFo78Er1CiqBQg/DSI1rDgVAoGBAMBvuYImj5uxP1q0+vEIVgkQXcvkWBPndZ7nAPcloR2eJIGTP1wVUu9iVCjnBRv17Wkd/hnPgQ7gNFwxjFyWuRH47NLD87z8QslM4MvYz/ulJJbWnqGlH9+0AtHv6oOyYfIbjmavbpz99P5jWruOiA6q/UX/LGUhMrZbdgjV7xYXAoGBAMf/M+BQZa3u4+UZnfEnqd0BxPdBZ5gF0auUz2rjSzaGhZuWp71f1MKNsDWVhPsLWzU3amFgbe3sbt44TIAJ0CH2SfgSPtWimXgp5uJGpzUwEyIz1DF+R/pzgfoh2kElcxXL21el41Ueyf/lqZvG/DWAWZ4+BgrwsErjzsvBDtHBAoGAB8Z74F5efxPyU1/so8CKeWNH5u0bAfLgNcNvroy+rcut/e5NKRNTfoiijeSHuFF/fjfQBIr10/wLIY5+9V7Bq5A7QWjZeFFZAXkxvArOnIRXriCN2Eort4Y2dvUSrK+QdY/XeR6tgXphY8Xv7JQduzn9cc6VpKBqxCN96W17c5ECgYAcRswDyl6YdBdYgxF5KbBikDXgEkgwF7AJUBBpaOcdaVeJ5amui8noJZd0XGAiqW+MAMeqSf6SruHpFjnIPmCjPmZvBFrJ44A0BuxZ64lfq8sLr/vb0uptdQ6lem/YgGl5/6kBpHr5IgD/79EX4k9Tby7B79NAza5xQU9ZMt25RQ==',
        'log' => [ // optional
            'file' => './logs/alipay.log',
            //'level' => 'debug'
        ],
        //'mode' => 'dev', // optional,设置此参数，将进入沙箱模式
    ],

    //圈子模块支付宝 PID
    'ali_pid' => '2088531728490041',

    //微信_圈子红包回调地址
    'we_red_notify_url' => 'http://api.36qq.com/api/circle_red_XxX_we_notify',

    //微信_圈子红包回调配置
    'we_red_config' => [
        'appid' => 'wxd2d9077a3072b5db', // APP APPID
//        'app_id' => 'wxb3fxxxxxxxxxxx', // 公众号 APPID
//        'miniapp_id' => 'wxb3fxxxxxxxxxxx', // 小程序 APPID
        'mch_id' => '1521224461',
        'key' => 'wuhang1231wuhang7890wuhang886655',
        'notify_url' => 'http://api.36qq.com/api/circle_red_XxX_we_notify',
//        'cert_client' => './cert/apiclient_cert.pem', // optional，退款等情况时用到
//        'cert_key' => './cert/apiclient_key.pem',// optional，退款等情况时用到
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

    //微信_圈子支付回调地址
    'we_host_notify_url' => 'http://api.36qq.com/api/circle_host_XxX_we_notify',

    //微信_圈子红包回调配置
    'we_host_config' => [
        'appid' => 'wxd2d9077a3072b5db', // APP APPID
//        'app_id' => 'wxb3fxxxxxxxxxxx', // 公众号 APPID
//        'miniapp_id' => 'wxb3fxxxxxxxxxxx', // 小程序 APPID
        'mch_id' => '1521224461',
        'key' => 'wuhang1231wuhang7890wuhang886655',
        'notify_url' => 'http://api.36qq.com/api/circle_host_XxX_we_notify',
//        'cert_client' => './cert/apiclient_cert.pem', // optional，退款等情况时用到
//        'cert_key' => './cert/apiclient_key.pem',// optional，退款等情况时用到
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