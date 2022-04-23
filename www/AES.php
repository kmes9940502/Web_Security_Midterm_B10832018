<?php
    class AES {

        private $key;
        private $iv = '00000000000000000000000000000000';//undefine?

        function __construct($keyAES) {
            $this->key = hash('sha256', $keyAES, true);
            $this->iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        }

        public function encrypt($payload)
        {
            $encrypted = openssl_encrypt($payload, 'aes-256-cbc', $this->key, 0, $this->iv);
            return base64_encode($encrypted);
        }
        public function decrypt($input)
        {
            $decrypted = openssl_decrypt(base64_decode($input), 'aes-256-cbc', $this->key, 0, $this->iv);
            return $decrypted;
        }
    }
?>