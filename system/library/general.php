<?php if(!defined('ACCESS')) exit('Access denied');
     if(!function_exists('cfile_get_contents'))
     {
        /**
         * cURL replacement for file_get_contents.
         * @author Jelle van der Coelen
         * @package Clockwork/Library/General
         *
         * @param string  $url  URL to load.
         * @param boolean $json Decides whether to return a json decoded string or not.
         * @param array   $opts Optional options to be set with curl_setop().
         *
         * @return string
         */
        function cfile_get_contents($url, $json = false, $opts = array())
        {
        	$ch = curl_init($url);

        	curl_setopt($ch, CURLOPT_HEADER, 0);
        	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        	
        	foreach($opts as $opt => $value)
        	    curl_setopt($ch, constant('CURLOPT_'.$opt), $value);
        	
        	$response = curl_exec($ch);
        	curl_close($ch);
        	
        	return ($json ? json_decode($response) : $response);
        }
    }
    
    if(!function_exists('rfile_get_contents'))
    {
        /**
         * Read contents of a file.
         * @author Jelle van der Coelen
         * @package Clockwork/Library/General
         *
         * @param string $file Path to file to read.
         *
         * @return string
         */
        function rfile_get_contents($file)
        {
            $handle   = fopen($file, 'r');
            $contents = fread($handle, filesize($file));
            fclose($handle);
            
            return $contents;
        }
    }

    if(!function_exists('dump'))
    {
        /**
         * Print readable array(s).
         * @author Jelle van der Coelen
         * @package Clockwork/Library/General
         *
         * @param array $array
         * @param array $array, ...
         *
         * @return void
         */
        function dump()
        {
            echo '<pre>';

            $args = func_get_args();
            foreach($args as $arg)
                print_r($arg);

            echo '</pre>';
        }
    }

    if(!function_exists('hashStr'))
    {
        /**
         * Return a hashed string.
         * @author Jelle van der Coelen
         * @package Clockwork/Library/General
         *
         * @param string  $str  String to hash.
         * @param boolean $salt Include salt.
         *
         * @return string
         */
        function hashStr($str, $salt = false){ return hash(Config::getSetting('hash_algo', false, 'sha512'), (($salt ? Config::getSetting('salt') : '')).$str); }
    }

    if(!function_exists('isEmailAddress'))
    {
        /**
         * Check whether the given emailaddress is valid or not.
         * @author Jelle van der Coelen
         * @package Clockwork/Library/General
         *
         * @param string $emailaddress Email address to check.
         *
         * @return boolean
         */
        function isEmailAddress($emailaddress = ''){ return filter_var($emailaddress, FILTER_VALIDATE_EMAIL); }
    }

    if(!function_exists('objectToArray'))
    {
        /**
         * Convert objects to simple array.
         * @author Jelle van der Coelen
         * @package Clockwork/Library/General
         * @since 2.1.5
         *
         * @param array        $objects Objects from which to extract the data.
         * @param string|array $key     Provide an array of keys to return multidimensional array. Set null to return all values.
         * @param string       $index   Set an index to use for the output array
         *
         * @return array
         */
        function objectToArray($objects, $key = null, $index = null)
        {
            $array = array();
            
            if(!empty($objects))
            {
                if(!is_array($objects))
                {
                    $single = true;
                    $objects = [$objects];
                }
                
                foreach($objects as $obj)
                {
                    if($key === null)
                        $arr = $obj->getValues();
                    else if(is_string($key) && preg_match('/^(\-)/', $key))
                    {
                        $minus = explode(' ', $key);
                        $a = $obj->getValues();
                        
                        foreach($minus as $min)
                            unset($a[substr($min, 1)]);
                        
                        $arr = $a;
                    }
                    else if(!is_array($key))
                        $arr = $obj->get($key);
                    else
                    {
                        $arr = array();
                        foreach($key as $k => $v)
                            $arr[$v] = $obj->get($v);
                    }
                    
                    if($index)
                        $array[$obj->get($index)] = $arr;
                    else
                        $array[] = $arr;
                }
            }

            return (isset($single) ? current($array) : $array);
        }
    }

    if(!function_exists('randStr'))
    {
        /**
         * Generate a random string of $length characters.
         * @author Jelle van der Coelen
         * @package Clockwork/Library/General
         *
         * @param int     $length  Length of string to return.
         * @param regexp  $exclude Regular expression for characters to exclude from $str.
         * @param boolean $special Include special characters (like ? @ # etc.).
         *
         * @return string
         */
        function randStr($length = 8, $exclude = '(1|l|i|0|o|O)', $special = true)
        {
            $str        = '';
            $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'.($special ? '~!@#^&*();?.,' : '');
            $characters = preg_replace('/'.preg_quote($exclude, '/').'/', '', $characters);

            for($i = 0; $i < $length; $i++)
                $str .= substr($characters, rand(0, strlen($characters)), 1);
            
            return $str;
        }
    }

    if(!function_exists('redirect'))
    {
        /**
         * Redirect to a specified url.
         * @author Jelle van der Coelen
         * @package Clockwork/Library/General
         *
         * @param string $location Omit to redirect to ROOT_PATH.
         * @param int    $header   HTTP Header to send. Possible values are 200, 301, 404.
         *
         * @return void
         */
        function redirect($location = '', $header = null)
        {
            if(function_exists('overrideRedirect'))
                $location = overrideRedirect($location);

            if($header == 200) header('HTTP/1.1 200 Ok');
            if($header == 301) header('HTTP/1.1 301 Moved Permanently');
            if($header == 404) header('HTTP/1.1 404 Page not found');
            
            header('Location: '.(!preg_match('/^https?:\/\//', $location) ? ROOT_PATH : '').$location);
            
            exit;
        }
    }

    if(!function_exists('str_lreplace'))
    {
        /**
         * Replace last occurance of $search in $subject.
         * @author Jelle van der Coelen
         * @package Clockwork/Library/General
         *
         * @param string $search  Search for this string.
         * @param string $replace Replace it with this string.
         * @param string $subject Replace this string.
         *
         * @return string
         */
        function str_lreplace($search, $replace, $subject)
        {
            $pos = strrpos($subject, $search);

            if($pos !== false)
                $subject = substr_replace($subject, $replace, $pos, strlen($search));

            return $subject;
        }
    }
?>