<?php

defined('FOF_INCLUDED') or die;
class FOFEncryptAes
{
    protected $key = '';
    protected $adapter;
    public function __construct($key, $strength = 128, $mode = 'cbc', FOFUtilsPhpfunc $phpfunc = null, $priority = 'openssl')
    {
        if ($priority == 'openssl') {
            $this->adapter = new FOFEncryptAesOpenssl();
            if (!$this->adapter->isSupported($phpfunc)) {
                $this->adapter = new FOFEncryptAesMcrypt();
            }
        } else {
            $this->adapter = new FOFEncryptAesMcrypt();
            if (!$this->adapter->isSupported($phpfunc)) {
                $this->adapter = new FOFEncryptAesOpenssl();
            }
        }
        $this->adapter->setEncryptionMode($mode, $strength);
        $this->setPassword($key, true);
    }
    public function setPassword($password, $legacyMode = false)
    {
        $this->key = $password;
        $passLength = strlen($password);
        if (function_exists('mb_strlen')) {
            $passLength = mb_strlen($password, 'ASCII');
        }
        if ($legacyMode && $passLength != 32) {
            $this->key = hash('sha256', $password, true);
            $this->key = $this->adapter->resizeKey($this->key, $this->adapter->getBlockSize());
        }
    }
    public function encryptString($stringToEncrypt, $base64encoded = true)
    {
        $blockSize = $this->adapter->getBlockSize();
        $randVal = new FOFEncryptRandval();
        $iv = $randVal->generate($blockSize);
        $key = $this->getExpandedKey($blockSize, $iv);
        $cipherText = $this->adapter->encrypt($stringToEncrypt, $key, $iv);
        if ($base64encoded) {
            $cipherText = base64_encode($cipherText);
        }
        return $cipherText;
    }
    public function decryptString($stringToDecrypt, $base64encoded = true)
    {
        if ($base64encoded) {
            $stringToDecrypt = base64_decode($stringToDecrypt);
        }
        $iv_size = $this->adapter->getBlockSize();
        $iv = substr($stringToDecrypt, 0, $iv_size);
        $key = $this->getExpandedKey($iv_size, $iv);
        $plainText = $this->adapter->decrypt($stringToDecrypt, $key);
        return $plainText;
    }
    public static function isSupported(FOFUtilsPhpfunc $phpfunc = null)
    {
        if (!is_object($phpfunc) || !$phpfunc instanceof $phpfunc) {
            $phpfunc = new FOFUtilsPhpfunc();
        }
        $adapter = new FOFEncryptAesMcrypt();
        if (!$adapter->isSupported($phpfunc)) {
            $adapter = new FOFEncryptAesOpenssl();
        }
        if (!$adapter->isSupported($phpfunc)) {
            return false;
        }
        if (!$phpfunc->function_exists('base64_encode')) {
            return false;
        }
        if (!$phpfunc->function_exists('base64_decode')) {
            return false;
        }
        if (!$phpfunc->function_exists('hash_algos')) {
            return false;
        }
        $algorightms = $phpfunc->hash_algos();
        if (!in_array('sha256', $algorightms)) {
            return false;
        }
        return true;
    }
    public function getExpandedKey($blockSize, $iv)
    {
        $key = $this->key;
        $passLength = strlen($key);
        if (function_exists('mb_strlen')) {
            $passLength = mb_strlen($key, 'ASCII');
        }
        if ($passLength != $blockSize) {
            $iterations = 1000;
            $salt = $this->adapter->resizeKey($iv, 16);
            $key = hash_pbkdf2('sha256', $this->key, $salt, $iterations, $blockSize, true);
        }
        return $key;
    }
}
if (!function_exists('hash_pbkdf2')) {
    function hash_pbkdf2($algo, $password, $salt, $count, $length = 0, $raw_output = false)
    {
        if (!in_array(strtolower($algo), hash_algos())) {
            trigger_error(__FUNCTION__ . '(): Unknown hashing algorithm: ' . $algo, E_USER_WARNING);
        }
        if (!is_numeric($count)) {
            trigger_error(__FUNCTION__ . '(): expects parameter 4 to be long, ' . gettype($count) . ' given', E_USER_WARNING);
        }
        if (!is_numeric($length)) {
            trigger_error(__FUNCTION__ . '(): expects parameter 5 to be long, ' . gettype($length) . ' given', E_USER_WARNING);
        }
        if ($count <= 0) {
            trigger_error(__FUNCTION__ . '(): Iterations must be a positive integer: ' . $count, E_USER_WARNING);
        }
        if ($length < 0) {
            trigger_error(__FUNCTION__ . '(): Length must be greater than or equal to 0: ' . $length, E_USER_WARNING);
        }
        $output = '';
        $block_count = $length ? ceil($length / strlen(hash($algo, '', $raw_output))) : 1;
        for ($i = 1; $i <= $block_count; $i++) {
            $last = $xorsum = hash_hmac($algo, $salt . pack('N', $i), $password, true);
            for ($j = 1; $j < $count; $j++) {
                $xorsum ^= $last = hash_hmac($algo, $last, $password, true);
            }
            $output .= $xorsum;
        }
        if (!$raw_output) {
            $output = bin2hex($output);
        }
        return $length ? substr($output, 0, $length) : $output;
    }
}
