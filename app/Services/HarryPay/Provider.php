<?php

namespace App\Services\HarryPay;

class Provider
{
    private $_config;

    //1024 PKCS1
    private $public_key_resource = 'MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQC53ds9NcvkR2MmOLe79px6Trx+kwhNYwOH9RKreb8hGEFRcaRa/CB+rh0V8C/1b1/VrrC/HNkJMK3WTbMEXTD8+46VLKDtK0rY+reeCbaDU0YyLvV/0IyFzWRzLw/gS4W4C900m1nPFiR5dWajf3SWCYd9kgMdq4wNnsXnBDswiwIDAQAB';
    private $private_key_resource = 'MIICXAIBAAKBgQDGJOxxQhysRUpRUbwMns75dsJXYMYSQb+CG2gB/Ssz01oQ8gCyC6lchIIqlzMnsQmmDsOIL0K+spFs/oYAqCTryfU5xZXzctYFEHNaDV9xqcyGjuo8R5J1DSHmNkydAwr8Lp2JyMEsWt652dv5nNXxw7FE/6G8pVSz1cZ+wYd6OwIDAQABAoGBAL/ckwJcNO1urratQTyrLdkK8MWxFDorZefy57Q9U+778VNFMf01I4pNWMkq3ULKv4AG/bjJooSK3hw/HLxYFF50MkQTW/Vg9uIxeD6qPuN8cqBVKvuFQO4xZBZUePUdeYI7qKmhMJJ6a336ppH6a4oYCq7cHPTS5er3BhaYyZsZAkEA6DHjM3SXx6SlrjTfdWQIGCX9gLqBLlp1t/m6CVqwQjI9WAlTvoM9eZOBsoFIueqRhzNB85kbt/vEg8ldyzIKhwJBANp1WpcdxuARF1Extd1f28j7V1PwVhJSmLEUsnG+1agFtvql9Lhu/0hhoI5A/sfotyL1EAmiWCDbGH0Of0F8+60CQArEXXPCYVNpqCEm5IHODK4J/PJeM6VRnonUc7MBWJEJQVz2ucJo1Y3wsB/17MhqPytUziccn3NtolQ2HzpP7LsCQGYmm+6vwNADjeismwLiEQ7A4IvihQzaTIX5TJu9hYCk83Pu6CjZ1ktNQ1thbwGhgwk4mIA4xobOjHvlrIG95J0CQAQx5F8OY/1syCIqSm6bG+DH3q8YnyFyyimvIh1U6YnZZ75kXghshDB1YwW8a4TGLpo7Xbb1uyRaETQ3Qgvqee4=';


    public function __construct()
    {
        $this->public_key_resource = "-----BEGIN PUBLIC KEY-----\n" .
            wordwrap($this->public_key_resource, 64, "\n", true) .
            "\n-----END PUBLIC KEY-----";
        $this->private_key_resource = "-----BEGIN RSA PRIVATE KEY-----\n" .
            wordwrap($this->private_key_resource, 64, "\n", true) .
            "\n-----END RSA PRIVATE KEY-----";


        $rsa_config['private_key'] = $this->private_key_resource;
        $rsa_config['public_key'] = $this->public_key_resource;


        if (empty($rsa_config['private_key']) && empty($rsa_config['public_key'])) {
            return false;
        }
        $this->_config = $rsa_config;
    }

    /**
     * 私钥加密
     * @param string $data 要加密的数据
     * @return string 加密后的字符串
     */
    public function privateKeyEncode($data)
    {
        $encrypted = '';
        $this->_needKey(2);
        $private_key = openssl_pkey_get_private($this->_config['private_key']);
        $fstr = array();
        $array_data = $this->_splitEncode($data); //把要加密的信息 base64 encode后 等长放入数组
        foreach ($array_data as $value) {//理论上是可以只加密数组中的第一个元素 其他的不加密 因为只要一个解密不出来 整体也就解密不出来 这里先全部加密
            openssl_private_encrypt($value, $encrypted, $private_key); //私钥加密
            $fstr[] = $encrypted; //对数组中每个加密
        }
        return base64_encode(serialize($fstr)); //序列化后base64_encode
    }

    /**
     * 公钥加密
     * @param string $data 要加密的数据
     * @return string 加密后的字符串
     */
    public function publicKeyEncode($data)
    {
        $encrypted = '';
        $this->_needKey(1);
        $public_key = openssl_pkey_get_public($this->_config['public_key']);
        $fstr = array();
        $array_data = $this->_splitEncode($data);
        foreach ($array_data as $value) {
            openssl_public_encrypt($value, $encrypted, $public_key); //私钥加密
            $fstr[] = $encrypted;
        }
        return base64_encode(serialize($fstr));
    }

    /**
     * 用公钥解密私钥加密内容
     * @param string $data 要解密的数据
     * @return string 解密后的字符串
     */
    public function decodePrivateEncode($data)
    {
        $decrypted = '';
        $this->_needKey(1);
        $public_key = openssl_pkey_get_public($this->_config['public_key']);
        $array_data = $this->_toArray($data); //数据base64_decode 后 反序列化成数组
        $str = '';
        foreach ($array_data as $value) {
            openssl_public_decrypt($value, $decrypted, $public_key); //私钥加密的内容通过公钥可用解密出来
            $str .= $decrypted; //对数组中的每个元素解密 并拼接
        }
        return base64_decode($str); //把拼接的数据base64_decode 解密还原
    }

    /**
     * 用私钥解密公钥加密内容
     * @param string $data 要解密的数据
     * @return string 解密后的字符串
     */
    public function decodePublicEncode($data)
    {
        $decrypted = '';
        $this->_needKey(2);
        $private_key = openssl_pkey_get_private($this->_config['private_key']);
        $array_data = $this->_toArray($data);
        $str = '';
        foreach ($array_data as $value) {
            openssl_private_decrypt($value, $decrypted, $private_key); //私钥解密
            $str .= $decrypted;
        }
        return base64_decode($str);
    }

    /**
     * 检查是否 含有所需配置文件
     * @param int 1 公钥 2 私钥
     * @return int 1
     * @throws Exception
     */
    private function _needKey($type)
    {
        switch ($type) {
            case 1:
                if (empty($this->_config['public_key'])) {
                    throw new \Exception('请配置公钥');
                }
                break;
            case 2:
                if (empty($this->_config['private_key'])) {
                    throw new \Exception('请配置私钥');
                }
                break;
        }
        return 1;
    }

    /**
     *
     * @param type $data
     * @return type
     */
    private function _splitEncode($data)
    {
        $data = base64_encode($data); //加上base_64 encode  便于用于 分组
        $total_lenth = strlen($data);
        $per = 96; // 能整除2 和 3 RSA每次加密不能超过100个
        $dy = $total_lenth % $per;
        $total_block = $dy ? ($total_lenth / $per) : ($total_lenth / $per - 1);
        $total_block = intval($total_block + 1);
        for ($i = 0; $i < $total_block; $i++) {
            $return[] = substr($data, $i * $per, $per); //把要加密的信息base64 后 按64长分组
        }
        return $return;
    }

    /**
     * 公钥加密并用 base64 serialize 过的 data
     * @param type $data base64 serialize 过的 data
     */
    private function _toArray($data)
    {
        $data = base64_decode($data);
        $array_data = unserialize($data);
        if (!is_array($array_data)) {
            throw new \Exception('数据加密不符');
        }
        return $array_data;
    }
}
