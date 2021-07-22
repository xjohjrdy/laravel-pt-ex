<?php

namespace App\Services\Crypt;

class RsaUtils
{
    private $path_request_public_key;
    private $path_request_private_key;
    private $path_response_public_key;
    private $path_response_private_key;
    private $path_outline_public_key;
    private $path_outline_private_key;
    public function __construct()
    {
        $this->path_request_public_key = file_get_contents(base_path('resources/keys/request/rsa_public_key_client.pem'));
        $this->path_request_private_key = file_get_contents(base_path('resources/keys/request/rsa_private_key.pem'));
        $this->path_response_private_key = file_get_contents(base_path('resources/keys/response/rsa_private_key.pem'));
        $this->path_response_public_key = file_get_contents(base_path('resources/keys/response/rsa_public_key_client.pem'));
        $this->path_outline_private_key = file_get_contents(base_path('resources/keys/game/rsa_private_key.pem'));
        $this->path_outline_public_key = file_get_contents(base_path('resources/keys/game/rsa_public_key.pem'));
    }


    private static function getPrivateKey($privateKey)
    {
        return openssl_pkey_get_private($privateKey);
    }

    private static function getPublicKey($privateKey)
    {
        return openssl_pkey_get_public($privateKey);
    }


    /**
     * 公钥加密--测试通过（客户端）
     * @param $data 需要加密的值
     * @return string
     */
    public function rsaOutlinePublicEncode($data)
    {
        return self::publicEncrypt($data, $this->path_outline_public_key);
    }

    /**
     * 私钥解密---测试通过（服务端）
     * @param $data 需要解密的值
     * @return string
     */
    public function rsaOutlineDecode($data)
    {
        return urldecode(self::privateDecrypt($data, $this->path_outline_private_key));
    }


    /**
     * 公钥加密--测试通过（客户端）
     * @param $data 需要加密的值
     * @return string
     */
    public function rsaPublicEncode($data)
    {
        return self::publicEncrypt($data, $this->path_request_public_key);
    }

    /**
     * 私钥解密---测试通过（服务端）
     * @param $data 需要解密的值
     * @return string
     */
    public function rsaDecode($data)
    {
        return urldecode(self::privateDecrypt($data, $this->path_request_private_key));
    }

    /**
     * 私钥加密（服务端）
     * @param $data 需要加密的值
     * @return string
     */
    public function rsaEncode($data)
    {
        return self::privateEncrypt($data, $this->path_response_private_key);
    }

    /**
     * 公钥解密（客户端）
     * @param $data 需要解密的值
     * @return string
     */
    public function rsaPublicDecode($data)
    {
        return urldecode(self::publicDecrypt($data, $this->path_response_public_key));
    }

    /**
     * 私钥加密
     */
    public static function privateEncrypt($data, $privateKey)
    {
        if (!is_string($data)) {
            return null;
        }
        return openssl_private_encrypt($data, $encrypted, self::getPrivateKey($privateKey)) ? base64_encode($encrypted) : null;
    }

    /**
     * 公钥解密
     */
    public static function publicDecrypt($encrypted, $publicKey)
    {
        if (!is_string($encrypted)) {
            return null;
        }
        return (openssl_public_decrypt(base64_decode($encrypted), $decrypted, self::getPublicKey($publicKey))) ? $decrypted : null;
    }

    /**
     * 公钥加密
     */
    public static function publicEncrypt($data, $publicKey)
    {
        if (!is_string($data)) {
            return null;
        }
        $crypto = '';
        foreach (str_split($data, 117) as $chunk) {
            openssl_public_encrypt($chunk, $encryptData, self::getPublicKey($publicKey));
            $crypto .= $encryptData;
        }
        $encrypted = base64_encode($crypto);
        return $encrypted;
    }

    /**
     * 公钥加密
     */
    public function publicEncryptOutline($data, $publicKey)
    {
        if (!is_string($data)) {
            return null;
        }
        $crypto = '';
        foreach (str_split($data, 117) as $chunk) {
            openssl_public_encrypt($chunk, $encryptData, self::getPublicKey($publicKey));
            $crypto .= $encryptData;
        }
        $encrypted = base64_encode($crypto);
        return $encrypted;
    }

    /**
     * 私钥解密
     */
    public static function privateDecrypt($encrypted, $privateKey)
    {
        if (!is_string($encrypted)) {
            return null;
        }
        $crypto = '';
        foreach (str_split(base64_decode($encrypted), 128) as $chunk) {
            openssl_private_decrypt($chunk, $decryptData, self::getPrivateKey($privateKey));
            $crypto .= $decryptData;
        }

        return $crypto;
    }


}
