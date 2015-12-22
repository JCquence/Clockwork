<?php
    /**
     * CSRF
     * @author Jelle van der Coelen
     * @package Clockwork/Module
     */
    class CSRF
    {
        /**
         * Check CSRF token.
         *
         * @param string $key     Key to check.
         * @param string $origina Hash to check against, defaults to $_POST.
         * 
         * @throws Exception
         * @return boolean
         */
        public static function check($key, $origin = null)
        {
            // --- does a generated token exist?
            if(empty($_SESSION['CSRF_'.$key]))
                throw new Exception('Missing CSRF token');
            
            // --- release token, one use only
            $hash = $_SESSION['CSRF_'.$key];
            unset($_SESSION['CSRF_'.$key]);
            
            // --- actual check
            $origin = ($origin ? $origin : $_POST['CSRF_'.$key]);
            if($origin === $hash)
                return true;
            else
                throw new Exception('Invalid CSRF token');
        }

        /**
         * Create new token for AJAX calls.
         *
         * @param $mixed $data Data to return in JSON response.
         *
         * @return string
         */
        public static function formatOutput($data)
        {
            $csrf = new CSRF();

            return json_encode(array('token' => $csrf->generate('ajax'), 'result' => $data));
        }

        /**
         * Generate and return a token, save it to session.
         *
         * @param string $key Name of the key.
         *
         * @return string
         */
        public function generate($key = '')
        {
            $token = base64_encode(time().sha1(md5($_SERVER['REMOTE_ADDR'].$_SERVER['HTTP_USER_AGENT'])).randStr(255));

            if(empty($_SESSION['CSRF_'.$key]))
            {
                $_SESSION['CSRF_'.$key] = $token;
                return $token;
            }
            else
                return $_SESSION['CSRF_'.$key];
        }

        /**
         * Generate form inputs to send token.
         *
         * @param string $key Name of the key.
         *
         * @return string
         */
        public function generateFormData($key = '')
        {
            // --- smarty args
            if(is_array($key))
                $key = current($key);

            $str  = '<input type="hidden" name="CSRF_'.$key.'" value="'.$this->generate($key).'" />';
            $str .= '<input type="hidden" name="CSRF-key" value="'.$key.'" />';
            
            return $str;
        }
    }
?>