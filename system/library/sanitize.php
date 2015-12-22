<?php if(!defined('ACCESS')) exit('Access denied');
    if(!function_exists('escape'))
    {
        /**
         * Escape a string.
         * @author Jelle van der Coelen
         * @package Clockwork/Library/Sanitize
         * @since 2.1.7
         *
         * @param string $str String to escape.
         *
         * @return $str
         */
        function escape($str){ return htmlspecialchars($str, ENT_QUOTES, 'UTF-8', false); }
    }
    
    if(!function_exists('filterInput'))
    {
        /**
         * Return fully sanitized string.
         * @author Jelle van der Coelen
         * @package Clockwork/Library/Sanitize
         *
         * @param string|array $str String to filter.
         *
         * @return string
         */
        function filterInput($str){ return sanitize($str, array('full' => true)); }
    }

    if(!function_exists('sanitize'))
    {
        /**
         * Return sanitized string.
         * @author Jelle van der Coelen
         * @package Clockwork/Library/Sanitize
         *
         * @param string|array $str
         * @param array        $options
         *
         * @return string
         */
        function sanitize($str, $options = array())
        {
            $defaults = array('singleQuote' => true,
                              'doubleQuote' => false,
                              'lessThan'    => false,
                              'greaterThan' => false,
                              'full'        => false);

            $options  = array_merge($defaults, $options);

            foreach($options as $function => $opt)
            {
                $function = 'sanitize'.ucfirst($function);

                if($function != 'sanitizeFull' && ($opt === true || $options['full'] === true))
                {
                    if(is_array($str))
                    {
                        foreach($str as $key => $value)
                            $str[$key] = $function($value);
                    }
                    else
                        $str = $function($str);
                }
            }

            return $str;
        }
    }

    if(!function_exists('sanitizeDoubleQuote'))
    {
        /**
         * Replace double quotes.
         * @author Jelle van der Coelen
         * @package Clockwork/Library/Sanitize
         *
         * @param string $str String to replace double quotes for.
         *
         * @return string
         */
        function sanitizeDoubleQuote($str){ return str_replace('"', '&quot;', $str); }
    }

    if(!function_exists('sanitizeGreaterThan'))
    {
        /**
         * Replace >.
         * @author Jelle van der Coelen
         * @package Clockwork/Library/Sanitize
         *
         * @param string $str String to replace > for.
         *
         * @return string
         */
        function sanitizeGreaterThan($str){ return str_replace('>', '&gt;', $str); }
    }

    if(!function_exists('sanitizeLessThan'))
    {
        /**
         * Replace <.
         * @author Jelle van der Coelen
         * @package Clockwork/Library/Sanitize
         *
         * @param string $str String to replace < for.
         *
         * @return string
         */
        function sanitizeLessThan($str){ return str_replace('<', '&lt;', $str); }
    }

    if(!function_exists('sanitizeSingleQuote'))
    {
        /**
         * Replace single quotes.
         * @author Jelle van der Coelen
         * @package Clockwork/Library/Sanitize
         *
         * @param string $str String to replace single quotes for.
         *
         * @return string
         */
        function sanitizeSingleQuote($str){ return str_replace('\'', '&#0039;', $str); }
    }
?>