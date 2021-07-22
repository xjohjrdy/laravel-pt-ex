<?php

namespace App\Services\HarryPay;


class RsaHarry
{
    //1024 PKCS1
//    private $public_key_resource = 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQC53ds9NcvkR2MmOLe79px6Trx+kwhNYwOH9RKreb8hGEFRcaRa/CB+rh0V8C/1b1/VrrC/HNkJMK3WTbMEXTD8+46VLKDtK0rY+reeCbaDU0YyLvV/0IyFzWRzLw/gS4W4C900m1nPFiR5dWajf3SWCYd9kgMdq4wNnsXnBDswiwIDAQAB';
//    private $private_key_resource = 'MIICXAIBAAKBgQDGJOxxQhysRUpRUbwMns75dsJXYMYSQb+CG2gB/Ssz01oQ8gCyC6lchIIqlzMnsQmmDsOIL0K+spFs/oYAqCTryfU5xZXzctYFEHNaDV9xqcyGjuo8R5J1DSHmNkydAwr8Lp2JyMEsWt652dv5nNXxw7FE/6G8pVSz1cZ+wYd6OwIDAQABAoGBAL/ckwJcNO1urratQTyrLdkK8MWxFDorZefy57Q9U+778VNFMf01I4pNWMkq3ULKv4AG/bjJooSK3hw/HLxYFF50MkQTW/Vg9uIxeD6qPuN8cqBVKvuFQO4xZBZUePUdeYI7qKmhMJJ6a336ppH6a4oYCq7cHPTS5er3BhaYyZsZAkEA6DHjM3SXx6SlrjTfdWQIGCX9gLqBLlp1t/m6CVqwQjI9WAlTvoM9eZOBsoFIueqRhzNB85kbt/vEg8ldyzIKhwJBANp1WpcdxuARF1Extd1f28j7V1PwVhJSmLEUsnG+1agFtvql9Lhu/0hhoI5A/sfotyL1EAmiWCDbGH0Of0F8+60CQArEXXPCYVNpqCEm5IHODK4J/PJeM6VRnonUc7MBWJEJQVz2ucJo1Y3wsB/17MhqPytUziccn3NtolQ2HzpP7LsCQGYmm+6vwNADjeismwLiEQ7A4IvihQzaTIX5TJu9hYCk83Pu6CjZ1ktNQ1thbwGhgwk4mIA4xobOjHvlrIG95J0CQAQx5F8OY/1syCIqSm6bG+DH3q8YnyFyyimvIh1U6YnZZ75kXghshDB1YwW8a4TGLpo7Xbb1uyRaETQ3Qgvqee4=';


    public $private_key_resource = 'MIICXgIBAAKBgQCSL8v6j6zXom7kaSaP43zrziJ5X2thOY6sQoYqqd0Al2qBXVARpikVg/AD8Nvh80mU6wu0WYanUvwtmp1Rt++ZNF2cXNPju/kvpYYtOA09kirA7K4trDLvd6o7XfvM0cxNQromUzK6JMt5krGnHb818+dl5uwWMXhKlmKMz4Dw/QIDAQABAoGBAIw3tlJuLx5iGjWSOj+3tyHDBcQfZzLJb3UBFgmkBmxD0A+nfl5/X1bYx4YwJ+gxYDmrvf1OBd9GtMXVUOKKKBD2mP1Wp/JtijQzoaj8+EzsON6UVSbTB3Lm5q5OjXa/Dx5zOFI1bA0ANllrV2w/8BKZjTmS0DTxO+TRHdotxy9BAkEA4t7offt+ZWOaagrP8RKNSmVMlKgI0yubtqOrPk1fHapjLA1/ihJ9LwzFe1cGlNfcQLXbE+wcFBYoXRELUae5DQJBAKT024/yshDXLvcIQeEPbRQKGuMRT14mDeW6fxRZPT48HE8ZeoPJcI+szWgxfh7x1sJW++/nNpjwW0qenv10O7ECQCdt0j5C/T6lxupzIpylOsUZQev8IDyDMbbWTyauz78aI84+MlJO0E7jC1daUpx/v5nHgWG/AUpEZ5N1KOByI+kCQQCY9yrnuJHhRfoaQAD/WBO5goleST4FO1qlzqRrVTmSjaFexGy06sbDpOWxmjuvLGoPOyRTWmBpwHGXp7IdrHxxAkEAxtXngGIpRHJb4ceXhTZ7KjiUw7IYfQjziCA8C6K51wLkI9jorpyM6px6GWXAzhPAGpoMJOTXHNFcXwwiHeq5AA==';
    public $public_key_resource = 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQDOm0xUSfrBO52mjq6fvRgRVGfK5Et+j4mYdkloAOmw83kG0Bnj3IbDbhI0G0XVeRpWOWVPdFWNaoFDOb2OTSErn4z02QF0MhOnYyTok9LxcUPZH329737269hwpFyoieI4s7j59NQWmJZyZkRxOpLaPhdpVt9CNcO7OmUX1n7RlwIDAQAB';

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
