<?php

namespace App\Services\HarryPayOut;

class RsaHarry
{

    //1024 PKCS1
//    private $public_key_resource = 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQC53ds9NcvkR2MmOLe79px6Trx+kwhNYwOH9RKreb8hGEFRcaRa/CB+rh0V8C/1b1/VrrC/HNkJMK3WTbMEXTD8+46VLKDtK0rY+reeCbaDU0YyLvV/0IyFzWRzLw/gS4W4C900m1nPFiR5dWajf3SWCYd9kgMdq4wNnsXnBDswiwIDAQAB';
//    private $private_key_resource = 'MIICXAIBAAKBgQDGJOxxQhysRUpRUbwMns75dsJXYMYSQb+CG2gB/Ssz01oQ8gCyC6lchIIqlzMnsQmmDsOIL0K+spFs/oYAqCTryfU5xZXzctYFEHNaDV9xqcyGjuo8R5J1DSHmNkydAwr8Lp2JyMEsWt652dv5nNXxw7FE/6G8pVSz1cZ+wYd6OwIDAQABAoGBAL/ckwJcNO1urratQTyrLdkK8MWxFDorZefy57Q9U+778VNFMf01I4pNWMkq3ULKv4AG/bjJooSK3hw/HLxYFF50MkQTW/Vg9uIxeD6qPuN8cqBVKvuFQO4xZBZUePUdeYI7qKmhMJJ6a336ppH6a4oYCq7cHPTS5er3BhaYyZsZAkEA6DHjM3SXx6SlrjTfdWQIGCX9gLqBLlp1t/m6CVqwQjI9WAlTvoM9eZOBsoFIueqRhzNB85kbt/vEg8ldyzIKhwJBANp1WpcdxuARF1Extd1f28j7V1PwVhJSmLEUsnG+1agFtvql9Lhu/0hhoI5A/sfotyL1EAmiWCDbGH0Of0F8+60CQArEXXPCYVNpqCEm5IHODK4J/PJeM6VRnonUc7MBWJEJQVz2ucJo1Y3wsB/17MhqPytUziccn3NtolQ2HzpP7LsCQGYmm+6vwNADjeismwLiEQ7A4IvihQzaTIX5TJu9hYCk83Pu6CjZ1ktNQ1thbwGhgwk4mIA4xobOjHvlrIG95J0CQAQx5F8OY/1syCIqSm6bG+DH3q8YnyFyyimvIh1U6YnZZ75kXghshDB1YwW8a4TGLpo7Xbb1uyRaETQ3Qgvqee4=';


    public $private_key_resource = 'MIICWwIBAAKBgQCgzOPHghx8uAImOhySJb0ZmqJXiWeA1TsaK8MV7/b3Q7InsNdmsT1aC5XZh0sLoJsNRA+cnQXVUp6q8cDOaf5KUBWsWUihlb/0BpXnuY6o3Li7gPeyidEfFAXQgKKyLkovv714GP0CbV9Wzg4PnAYkaIgZFunGzHvMxqCcfj9/PQIDAQABAoGAChizWKyXw1D+eY3+i0KpW/k0plBvWkyJOHx09GSr2hy7C/jznXQViRjfINh44tMDyVJztH67hgh5A/zIAW3wVHqbMV5RgminPEpWWVl15LSKRa2XBWznJ7lJsOkti6d2O4Du/S/vPQmCOnrMJWknvZkOPfiWx0uUwgiV/LLB4dUCQQDeNNF0sKS7SA9r/d85HnqnQnKNJOSXX/Af3qndKyaoE9z1GSpaLThqTpbVWSmm4reiAruXh11HBIjCCZ4Pq0CXAkEAuUFYtwAVmnmTgbtTmk2UpOJVJ4oz6pE+2i9ZuObKG8aIGK++OnYk6yjjfhx3o1IpOf7M3dZBzt8+tWS1mShlSwJAI7IYc8ZssClDUPXXhjV/Pp9OB56FmkuvJ299min0a8vFExqX0ySwi2NUl7FbH5QMK9qEiDMWqPHxhjpFSf8YwQJAa1HN4QXtffXcXBV3UzaKXBK6HhPUC5lk/eTcZ19bykdy5Eo7O4bh0FF5qL85F6YrN+vCJulOalet7kuPYFCkjQJAA8j9/AiirnloXSdM1bHf/BpENB8kpfYxmsX6A7Wd9APtodMVlGpEvtkqk8JlWPPSyOKXpX5zHQVBuKDJECNWDQ==';
    public $public_key_resource = 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCBB4QGIEQ73gAh1b0+VJvam1AbG2ilm75yqA1M/9bZVnjTxZOVHof6ZgtYM/pv+zgrRby5EWiYcjXQ6TRTuMdPella/Dp9vpzAn/wFHrwIiYkjfpkUkMD8B28LOpYd5y+aTjeiI9iWhlkMFQIQYeFguyTfe/H+tvzgfO/WMpubQQIDAQAB';

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
        openssl_private_encrypt($input, $output, $this->private_key_resource);
        return base64_encode($output);
    }

    /**
     * 解密 私钥加密后的密文
     */
    public function public_decrypt($input)
    {
        openssl_public_decrypt(base64_decode($input), $output, $this->public_key_resource);
        return $output;
    }

    private static function getPublicKey($privateKey)
    {
        return openssl_pkey_get_public($privateKey);
    }

    /**
     * 用公钥加密
     */
    public function public_encrypt($input)
    {
//        openssl_public_encrypt($input, $output, $this->public_key_resource);
//        return base64_encode($output);

        if (!is_string($input)) {
            return null;
        }
        $crypto = '';
        foreach (str_split($input, 117) as $chunk) {
            openssl_public_encrypt($chunk, $encryptData, self::getPublicKey($this->public_key_resource));
            $crypto .= $encryptData;
        }
        $encrypted = base64_encode($crypto);
        return $encrypted;
    }

    private static function getPrivateKey($privateKey)
    {
        return openssl_pkey_get_private($privateKey);
    }

    /**
     * 解密 公钥加密后的密文
     */
    public function private_decrypt($encrypted)
    {
        if (!is_string($encrypted)) {
            return null;
        }
        $crypto = '';
        foreach (str_split(base64_decode($encrypted), 128) as $chunk) {
            openssl_private_decrypt($chunk, $decryptData, self::getPrivateKey($this->private_key_resource));
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
