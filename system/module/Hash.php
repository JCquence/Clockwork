<?php if(!defined('ACCESS')) exit('Access denied');
    /**
     * Hash
     * @author Jelle van der Coelen
     * @package Clockwork/Module
     */
    class Hash
    {
        /**
         * Hash.
         * @var string
         */
        private $hash = null;

        /**
         * Hash options.
         * @var array
         */
        private $opts = ['cost' => 11];

        /**
         * Constructor.
         *
         * @return void
         */
        public function __construct($pass = null)
        {
            if($pass)
                $this->hash = ($this->isHash($pass) ? $pass : $this->create($pass));
        }

        /**
         * Create a hash.
         *
         * @param string $pass String to create a hash from.
         *
         * @return void
         */
        public function create($pass)
        {
            $this->hash = password_hash($pass, PASSWORD_DEFAULT, $this->opts);

            return $this->hash;
        }

        /**
         * Check wheter the given string is a hash or a string.
         *
         * @param string $hash Hash to check.
         *
         * @return boolean
         */
        public function isHash($hash)
        {
            $info = password_get_info($hash);

            return ($info['algo'] != 0 && $info['algoName'] != 'unknown' ? true : false);
        }

        /**
         * Update a hash, if needed.
         *
         * @param string $pass String to create hash from.
         * @param string $hash Hash to check.
         *
         * @return string
         */
        public function update($pass, $hash = null)
        {
            if(!$hash)
                $hash = $this->hash;

            if(password_needs_rehash($hash, PASSWORD_DEFAULT, $this->opts))
                $this->hash = $this->create($pass, PASSWORD_DEFAULT, $this->opts);

            return $this->hash;
        }

        /**
         * Verify a hash.
         *
         * @param string $pass Original string.
         * @param string $hash Hash to check.
         *
         * @return boolean
         */
        public function verify($pass, $hash = null)
        {
            if(!$hash)
                $hash = $this->hash;

            return password_verify($pass, $hash);
        }
    }
?>