<?php if(!defined('ACCESS')) exit('Access denied');
    /**
     * Crypt
     * @author Jelle van der Coelen
     * @package Clockwork/Module
     */
    class Crypt
    {
        /**
         * Key.
         * @var string
         */
        private $key;

        /**
         * Constructor.
         *
         * @return void
         */
        public function __construct()
        {
            $this->setKey();
        }

        /**
         * Decrypt a string.
         *
         * @param string $str String to decrypt.
         *
         * @return string
         */
        public function decrypt($str)
        {
            $str    = base64_decode($str);
            $ivSize = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
            $ivDec  = substr($str, 0, $ivSize);
            $str    = substr($str, $ivSize);

            return mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $this->key, $str, MCRYPT_MODE_CBC, $ivDec);
        }

        /**
         * Encrypt a string.
         *
         * @param string $str String to encrypt.
         *
         * @return string
         */
        public function encrypt($str)
        {
            $iv  = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC), MCRYPT_RAND);
            $str = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $this->key, $str, MCRYPT_MODE_CBC, $iv);
            $str = $iv.$str;

            return base64_encode($str);
        }

        /**
         * Create cipher key.
         *
         * @return string
         */
        public function setKey()
        {
            if(!$this->key)
                $this->key = md5(sha1(Config::getSetting('cipher_key')));

            return $this->key;
        }
    }
?>