<?php
/**
 * User: Administrator
 * Date: 2019/9/4/004
 * Time: 14:25
 */


return [

    //支付宝 PID
//    'ali_pid' => '2088531728490041',  //配对 'app_id' => '2019080266097538'   2019版
    'ali_pid' => '2088021889126973',  //配对 'app_id' => '2017080508049766'  2017版本
//    'ali_pid' => '2088102179133096', //沙箱


//    'test' => array_replace(
//        config('ring.ali_config'),
//        [
//            'notify_url' => '111', //程序内部设定
//            'return_url' => '222', //程序内部设定
//        ]),

    //支付宝配置 2019 账号
    'ali_config_2019' => [
        'app_id' => '2019080266097538',
        'notify_url' => '', //程序内部设定
        'return_url' => '', //程序内部设定
        'ali_public_key' => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAqZp6YpOomxg7Jdoj8xr+nCn0WNwm06Oqq8GXYZTM0cIAQIi0DXxdrLuhf33dEMSZuBYyB3rN9Ecu0dmZw1XrRnx/3RRVYxxuNacmdnnO48wQzBVp5t1K2CEo0D3F7rZrUtLFn9dNgRytW2F4zjO5ZTAtfAgTifAAzynS2XTTJGKAEq6rifkGo82QYy+r1Doe16MJs0bkRDBtLr1vjmDyj6kHlsIuwBrrxwYKVRMPq/NyPD5ILYNULsPGSqcJZNHTfbTwjZ/V8drOoZ0OtmVmkwzL+AQpWjwU7k3ujTrjbEPfnIRs+OlRqx9DjtFnhpjxiQTGi/Fh+sjOVxbrB4Fz4wIDAQAB',
        'private_key' => 'MIIEpAIBAAKCAQEArZxIQv8RgMZh10XEYepdj4HFOkP+i2FGtOiKBfRnZMDECrsRPmmVndtiwFJDY3r/q+TgwF7xuEc0bNmsyxGZsWg2mduCd8zNkoVdUDJY62LjHZoMjfTcF/Js8Q/I7hDxtP9Z/HW8AjgXCsFRZe7jYgxVUI5B8JdAGVHHrHdAYxMkVAkRNbNg/0x1Ms1E2wTfX8rMuDi+X7Aav+ewC0tk0eBQa+JiIHmyWP+o16T8hF6G+KmvCYIM0EF0+j6uQAxPRDIqMLQ92otX9fXc5M8L53ZYlG/oIsjX+mgqNJKDc3QlQyukoIeyaLhss3kVWyXBSASwaDNROPPPFfPJc+XX4wIDAQABAoIBAQClyFUg1N1r8QTBQvgS4HBTd4JU71UE1/FjE6OpqAeLqKAL9zKyPLUIttSH/oYnWu8GwFr2mDOO2z/uqbZdfAMd6/wn0/u0Vrf/sKa4zDspG5bxT4epjycVHwR09bPT0g5d3nGZqPvNyq4GMTB/fC1aFZjqgc3p5yToiLV5ta6ga7S4TvMCguAg5vVvHyt3ij9RjKhccILGMa3gmyDl7zwDVXDcLARZlu33VXJzkgaGIgSEJfo7MrjQS5YWeoBstzu7qjQFgB30TKPiGKNvpxhh3ANcmWwiWQ9n59QKbEUO+VwvYfrOYtBZbiprea937k+uhOGxdOY1xZ6foZ1CzdrRAoGBAOb0pm4oAuEhWjwuflo+DAB8Sr2Oia1+a3kQgVV3QifGiEX92YyzXHcY/AuS2VFE4cMGa/B1kVmvunj+0keNdg6bqQX5LLYHSRabBlWDwn1BEOYe/clgLZS6xPOH/rbjAVmQEFO6HP3FrgE5taro8MFo78Er1CiqBQg/DSI1rDgVAoGBAMBvuYImj5uxP1q0+vEIVgkQXcvkWBPndZ7nAPcloR2eJIGTP1wVUu9iVCjnBRv17Wkd/hnPgQ7gNFwxjFyWuRH47NLD87z8QslM4MvYz/ulJJbWnqGlH9+0AtHv6oOyYfIbjmavbpz99P5jWruOiA6q/UX/LGUhMrZbdgjV7xYXAoGBAMf/M+BQZa3u4+UZnfEnqd0BxPdBZ5gF0auUz2rjSzaGhZuWp71f1MKNsDWVhPsLWzU3amFgbe3sbt44TIAJ0CH2SfgSPtWimXgp5uJGpzUwEyIz1DF+R/pzgfoh2kElcxXL21el41Ueyf/lqZvG/DWAWZ4+BgrwsErjzsvBDtHBAoGAB8Z74F5efxPyU1/so8CKeWNH5u0bAfLgNcNvroy+rcut/e5NKRNTfoiijeSHuFF/fjfQBIr10/wLIY5+9V7Bq5A7QWjZeFFZAXkxvArOnIRXriCN2Eort4Y2dvUSrK+QdY/XeR6tgXphY8Xv7JQduzn9cc6VpKBqxCN96W17c5ECgYAcRswDyl6YdBdYgxF5KbBikDXgEkgwF7AJUBBpaOcdaVeJ5amui8noJZd0XGAiqW+MAMeqSf6SruHpFjnIPmCjPmZvBFrJ44A0BuxZ64lfq8sLr/vb0uptdQ6lem/YgGl5/6kBpHr5IgD/79EX4k9Tby7B79NAza5xQU9ZMt25RQ==',
        'log' => [ // optional
            'file' => './logs/alipay.log',
            //'level' => 'debug'
        ],
        //'mode' => 'dev', // optional,设置此参数，将进入沙箱模式
    ],

    //支付宝配置 - 沙箱环境
    'ali_config_s' => [
        'app_id' => '2016101100663476',
        'notify_url' => '', //程序内部设定
        'return_url' => '', //程序内部设定
        'ali_public_key' => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAprLcDDUN/4CauNu61gPIspk9Ee1V9StjSoRjLE6pzX09O7QZR43JtYOe5d60Ggh+oMpACK7PFpFms15ToB3FOPUq6ItKoFqLJhGYTNApis5aLyAh4BVQAGmN/qD05XGBcyM63M+yQSt3VUUdcbFJ+lFHL2hFk1wacFXZ1+NuK5dlg1T49Ip+y4YQkxtkZIKHjxtJp9gWHMFVMTJBN4WnrMHY5d8WnGK12r71l2DXQ8n23z+RjQDMN7FPykwvSPBc2BF+nY7tzGcgb0Ebq7QRV+bS+uqx1jvRylxKrGZX2suDihPMHym64vyCy7QAxf6cdGTsHuZTtrgjWhBqWhSTNwIDAQAB',
        'private_key' => 'MIIEowIBAAKCAQEAiZzbwKRG9gapMMpN/zT20iMWimnjKd183DQJQuLw6kaf3D2ZP7Gq5BOtQZs1yBrMqfWhSEkW/DOpO2rFDZyi7OeFLDzCVfbpQHE7lewDivkkIL2KxYxavoYejIGortmr9WoaKNTPubKf3x20K9FlhbMA2xfOdafY5SUEUWanl2OTylER6CUkMIgmVmFa6/O1F1wsL0WxvQmLfPb1lEGCtaN/HASmrQOu/uq47IQ9kOG3S+km1619aHJ7CFy4K8fuBnH7DajrIwpUg36fJN/W5po0IBBbNTy1Km5qX4kkMuLk8FosIQUH3VdsVf4ZmCUqI4bMWi6JeFEAOlTGz6j7mQIDAQABAoIBAGBcUVjk77epVGIQ12sEaG0fGtKTcPlI2fzMB0wp4675A0nSbHQ4ccTBromJKLDVa0j78hLO82RovHEZw6BTdZbUS+F9LkvJ2O1CHQpPOO/go5a1gu3v6T8OZWfYBwlg7NOtl2HOGyMaXG58u0QPnbpwbQH9+plSjkNul0Z0zpfnn7A/4oSibKOcGBmX7YGnoMRlxK5JKX0aCovk0+1A3PZUj+ZHUMhYAwhggW2nWcfyk67WSt+7mX9rLombK38d7yRWsO4iWvuJIBWmxlmMUfctcyZxwWfUguNW2ULlhhVp81cDRyqMXa4F1SJ5UcxUloJfZgnSTPDWay3rsdvTcHECgYEAzEqnri0tipOurW+fleMK44srkVCA9R6kVBUqXfeIH0GxNhWHA5CtNFGjyli0S35yDrI3Rch7JpIeHDh2NwVylfE9ZKwJpAWpD2sW47/M1QUFZl6ui4oRKKLbWY/D47qWSF/zmUJDgsfL4mCJx5pykcNtpGyyo+YWpoN13f7CSO0CgYEArHGoF1HRz2kzQ69S5z0PvhWVcLmM0HFp2qRbSiTd+H2sG3S8d3vQWtJjLTjrIek4ftlbKIwAFl383gSzsg3lvY2VWuTUus56W7YomGNp6TML4oCPqUTcsCvJ0fusi4igjTqpiUginHvIpliDgkNewf8OakmauAzmvDSPJnMCQ90CgYBwlKObZOI3//K/OUhkvV3+z28PAaJPcrd0bsWOSx8EupsCBxMEb/JDZsowdHnHMMOCPcwf9L+JHfTh8GuVHrdq0irXE8esUEI+cPGISOaEbePv6jWeSFP0ZOskjAmTsfunntkLmGzD4X8GHdVP3llM4rnFZvF7SQ0qbj/COuaucQKBgD8YOuOFaVoVaM9sF85J1je7l/ktuusNhCiemFRRlvHzsYQ+OzMQl1STw/vo5od62NP43VTBnMqSSxJE8CbG1Pd9FVezgG51W+mwhbv0K+1KYx/V4DqT2peO/gIZrrFDJVNnUl7LiPZuiCTtH/kKg2JB21liYvzSo53bMUFlNT/1AoGBAIdTKQ901xrLJ32SSw9dV5za9vXtCcRkDOsrUHxy5wed+Ubcwd4od8dhI/hds9kf5gue/7CeBo6mlt/VcdSC4MXxtsmbjTAbrvYHq6gAC77QT8qkVge4QlMW/a5AuoxQYEa1zujQpyNTIjOw7I9D8OoczqDwKZNT+T6DSHEv/Ag8',
        'log' => [ // optional
            'file' => './logs/alipay.log',
            //'level' => 'debug'
        ],
        'mode' => 'dev', // optional,设置此参数，将进入沙箱模式
    ],


    //支付宝配置文件'app_id' => '2017080508049766',
    'ali_config' => [
        'app_id' => '2017080508049766',
        'notify_url' => '', //程序内部设定
        'return_url' => '', //程序内部设定
        'ali_public_key' => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAq2lFYWgiBr0mwZqmJQ7AXlz44SYNXD+zc7HxqMgaWJiBSgU2XrJMvb281kt6I6+XlyEVCVLD2C6L4Ria/jKaq1MEi4XpOUbch+rnBvIX2vYqea4hFCvf7LGdViW9UPgBaemjziEfoEdfcCK8Gd61pxmp/98mxf5Pj72twCmA2I06/hAqCiyAsxb/uUuwwe5OLZkdGqrZXc6d0/BwiomE5uLr8+WgEQygNBX7HOW6P/gFj+VngslstqS9tM/LejNVZWol7DiekuNUkza+IXYZTNF67zUyzx4EVPtGrH6+ZOACpnTzQSgo3Y+b+TLMllZMO/E0gDtg7ct1BZR5zx9buwIDAQAB',
        'private_key' => 'MIIEogIBAAKCAQEAq2lFYWgiBr0mwZqmJQ7AXlz44SYNXD+zc7HxqMgaWJiBSgU2XrJMvb281kt6I6+XlyEVCVLD2C6L4Ria/jKaq1MEi4XpOUbch+rnBvIX2vYqea4hFCvf7LGdViW9UPgBaemjziEfoEdfcCK8Gd61pxmp/98mxf5Pj72twCmA2I06/hAqCiyAsxb/uUuwwe5OLZkdGqrZXc6d0/BwiomE5uLr8+WgEQygNBX7HOW6P/gFj+VngslstqS9tM/LejNVZWol7DiekuNUkza+IXYZTNF67zUyzx4EVPtGrH6+ZOACpnTzQSgo3Y+b+TLMllZMO/E0gDtg7ct1BZR5zx9buwIDAQABAoIBAFzAG8C3XiITW9NK4TgQIVtuJ2V896HA/lNOKBtSG59w9hSdeWwIQqow7utt7+s7amVmi0F+PWbO2PW2ILgHndWymDT/AtHxXAdXvibt/KaIqTcqV8/YZdRnz2D8DtKvP+SVfgNdvQU3WZcN1IaTEewXYPuJPivIjjiTRF9ElemWr01TF217RKGdbX9LpOO75hEqTXHFFGOFq4VTVLuRRnT8+VdxxbnbMWITg95ZQJULk5NBDZs4WclpFUmTLgVFnEGT//mnmWMhmYbIXt9+bT0Jqg82B/VtvFXwE81fruWrtJemdNgCkfznfXC3ntWyKJqjD90JU4v0FC3tsAVNEYECgYEA2NIoZh5PmiEfgK8x8opxmHSn54TKQUUk6OmHXyzBEroQfTYw0lYzEuXM38jwmhnKw+tHrybB3jli5EWKOrfAuq39KWifoJDqGcbKZHiF+lboocbUt2jokh0SiZfIbfuyekEh+36JckYns144/4aZtJctQjictXnkGZgg3UIVUKECgYEAymKFewFe132UdUJ8sx5Q6AurUzYxZwRroVhSGLklg1q5d4ffluH0qFbIdvPSQEkzw3Lx4y47hq4YO+lTvxos8ciP0GTgbILEimvZKEwiuIbjq+rMVv2UMKEDxLVH4D8sY+pn6ezeqLwYumqv/0vSB9XiyEdouaaGqjrkBq1uItsCgYAgpaRHnBDIWGxx5+9RYd8w4X/WsHvz0AF6wI8NmaOIulN5Rwua4DfYhJmQqKTxzyhkz6x/hwpx95oYXRAvPzPZ6BDSh1phxvA46WHYNv2VcDifA11MMJO0TIRaC2y2gsug9OW1BJyVhFK+A1X5w76pHopGClguSyg5Ylqwdh7XgQKBgB8fM2OM/Gaq2w+FH+Pzf4GPedMb/FDZLyvqhwViQ1CFvQuSi2GKcbMJVV5ldCmKmDkwDd5fl6vITdW4tbYnck3kB6mGObS2Dz8yAnzd6GDz7ULconumFwm7WPcA4YKsdEkRHNWuJTdRZYQVL+dUY4Hs9CjUQlh6MS8wSyxzswINAoGAYpkCxPPesjvcYTtNVOLn41vx9nJ2jL4YLHDB/dREBbsk05oMnBJ06AePnc4DWrzlDkU9OQdLMXy4/55vQN7u2weOhmt/UXsrgrkJ/bqhGIMB3q1kgbU5Sl99yEMLH8+i/kyGLiYs7aGMm5BuFFMtnAXcNUflbofhmC9GTNt8qp8=',
        'log' => [ // optional
            'file' => './logs/alipay.log',
            //'level' => 'debug'
        ],
        //'mode' => 'dev', // optional,设置此参数，将进入沙箱模式
    ],
    //微信_支付配置
    'we_config' => [
        'appid' => 'wxd2d9077a3072b5db', // APP APPID
//        'app_id' => 'wxb3fxxxxxxxxxxx', // 公众号 APPID
//        'miniapp_id' => 'wxb3fxxxxxxxxxxx', // 小程序 APPID
        'mch_id' => '1521224461',
        'key' => 'wuhang1231wuhang7890wuhang886655',
        'notify_url' => '',
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