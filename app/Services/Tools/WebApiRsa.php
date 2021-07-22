<?php
/**
 * User: Administrator
 * Date: 2019/9/20/020
 * Time: 17:20
 */

namespace App\Services\Tools;


class WebApiRsa
{
    private $public_key_resource = 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCEPRnL/rMAM45SQ4StNhUU81rsMZ13S2rZgh7d9/++UWzXjWM3IhaIa8GSDDfrXAnEjCUeRBMG5zu1yMfQauwAyGzO8dYJKQn8Y3DDRqiFQU7auJTiO9KLZbabyYnpNunGIUoTB7RDerZvcJ5+8HLHbruxGwC/ZLrWVN84TTribQIDAQAB';
    private $private_key_resource = 'MIICXAIBAAKBgQCEPRnL/rMAM45SQ4StNhUU81rsMZ13S2rZgh7d9/++UWzXjWM3IhaIa8GSDDfrXAnEjCUeRBMG5zu1yMfQauwAyGzO8dYJKQn8Y3DDRqiFQU7auJTiO9KLZbabyYnpNunGIUoTB7RDerZvcJ5+8HLHbruxGwC/ZLrWVN84TTribQIDAQABAoGAHScbOM29yJ2VEq2v4j+6BhRgRxHpFRObSrhU9bpmtx5cUGjrJoxtS0X2Nqxa55gyzxHWXQXE7BCuKTVKV7g6ruAjWein6fL2bHvfNWyD0++/zyZd5dIABgGr1rf/JuywNl7rJ10ifkU3bMat85v5ytHOGYZNnfUSg+KNKArsQAECQQDGqeN3bqiK8BSQ6/AyVaO90MQj0R5NYN3CiMdm7SuJF4TQaQz4lOo3gEYYbOpmWoJ4E5zIh8UuWdy9+KACOhkBAkEAqmd0eqevhW4dXPFhwWDDrs00PTc3mduQsMEZxkzQLh6+6+0aUvzX2aYbxSaf/Kyz48mddTdp3RFvuA0/IYk9bQJAGSK9qQrTQGEH+R4hEf1L7mRPrMh0sQ2kgUyVDizL6ViUcVoZTgppaARO2iBNuA2TnGW+3JBxHmA4UcD3XdDEAQJAKWzVCCxToyBNyxZzKUuYxpnkJS2TOrgByLZoyahKw6t9xmTxjVMiNisHfToSkp55bNrKiIBcH/3pJtkxi7mNUQJBAJFw+3sdovH4TPHeHTIewGfv9oV+/k+qVdaE4IT7W8tjyNZWMWbmUWTvwxMPGqqqAKGbijodj03Cysyf7b2PRiY=';

    /**
     * 构造函数
     * @param [string] $public_key  [公钥数据字符串]
     * @param [string] $private_key [私钥数据字符串]
     */
    public function __construct($public_key = null, $private_key = null)
    {
        $this->public_key_resource = "-----BEGIN PUBLIC KEY-----\n" .
            wordwrap($this->public_key_resource, 64, "\n", true) .
            "\n-----END PUBLIC KEY-----";
        $this->private_key_resource = "-----BEGIN RSA PRIVATE KEY-----\n" .
            wordwrap($this->private_key_resource, 64, "\n", true) .
            "\n-----END RSA PRIVATE KEY-----";
    }

    /**
     * 用私钥加密
     */
    public function private_encrypt($input)
    {
        $crypto = '';
        foreach (str_split($input, 117) as $chunk) {
            openssl_private_encrypt($chunk, $encryptData, $this->private_key_resource);
            $crypto .= $encryptData;
        }
        return base64_encode($crypto);
    }

    /**
     * 解密 私钥加密后的密文
     */
    public function public_decrypt($input)
    {
        $crypto = '';
        foreach (str_split(base64_decode($input), 128) as $chunk) {

            openssl_public_decrypt($chunk, $decryptData, $this->public_key_resource);

            $crypto .= $decryptData;
        }
        return $crypto;

    }

    /**
     * 用公钥加密
     */
    public function public_encrypt($input)
    {
        $crypto = '';
        foreach (str_split($input, 117) as $chunk) {
            openssl_public_encrypt($chunk, $encryptData, $this->public_key_resource);
            $crypto .= $encryptData;
        }
        return base64_encode($crypto);
    }

    /**
     * 解密 公钥加密后的密文
     */
    public function private_decrypt($input)
    {
        $crypto = '';
        foreach (str_split(base64_decode($input), 128) as $chunk) {
            openssl_private_decrypt($chunk, $decryptData, $this->private_key_resource);
            $crypto .= $decryptData;
        }
        return $crypto;
    }

    /*
     *
     */
    public function response_encrypt($input)
    {
        if (is_array($input)) $input = json_encode($input);
        return $this->private_encrypt($input);
    }
}