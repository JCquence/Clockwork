<?php
    /**
     * Login
     * @author Jelle van der Coelen
     * @package Clockwork/Module
     */
    class Login
    {
        /**
         * User class
         * @var string
         */
        protected static $className = 'User';
        
        /**
         * Session key
         * @var string
         */
        protected static $sessionKey = 'isLoggedIn';
        
        /**
         * Constructor
         *
         */
        public function __construct()
        {
            if(!Clockwork::getInstance()->isModuleLoaded('Cache'))
                Clockwork::throwError('Login module depends on Cache module, which is not loaded', Clockwork::ERROR_FATAL, 1);
            if(!Clockwork::getInstance()->isModuleLoaded('Hash'))
                Clockwork::throwError('Login module depends on Hash module, which is not loaded', Clockwork::ERROR_FATAL, 1);
        }

        /**
         * Check login
         *
         * @param string $username
         * @param string $password
         *
         * @return boolean
         */
        public static function checkLogin($username, $password, $ufield = 'emailaddress', $pfield = 'password')
        {
            $user = new static::$className($username, $ufield);
        
            if(!$user->getError(404) && $user->get('status') != -1)
            {
                if((new Hash())->verify($password, $user->get($pfield)))
                {
                    // --- update password hash
                    $user->set('password', (new Hash())->create($password))->save();

                    // --- session
                    $id = sha1(md5(Config::getSetting('salt').$user->get('id')));
                    $_SESSION[static::$sessionKey] = $id;

                    return true;
                }
            }

            return false;
        }

        /**
         * Check whether an user is logged in or not
         *
         * @return boolean
         */
        public static function isLoggedIn()
        {
            if(isset($_SESSION[static::$sessionKey]))
                return (self::getUser(false) ? true : false);
            
            return false;
        }

        /**
         * Return logged in user
         *
         * @return object|boolean
         */
        public static function getUser($check = true)
        {
            if(($check && self::isLoggedIn()) || !$check)
            {
                if(($user = Cache::loadData(static::$sessionKey)) === null)
                {
                    $class = static::$className;
                    
                    if(($user = $class::create(sanitize($_SESSION[static::$sessionKey]), "*SHA1(MD5(CONCAT('".Config::getSetting('salt')."',  id)))")) !== false)
                        Cache::saveData(static::$sessionKey, $user);
                    else
                    {
                        unset($_SESSION[static::$sessionKey]);
                        redirect();
                    }
                }

                return $user;
            }
            else
                return false;
        }
    }
?>