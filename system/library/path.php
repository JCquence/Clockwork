<?php if(!defined('ACCESS')) exit('Access denied');
    if(!function_exists('appdir'))
    {
        /**
         * Return app_dir.
         * @author Jelle van der Coelen
         * @package Clockwork/Library/Path
         *
         * @param string $location Location to append to dir
         *
         * @return string
         */
        function appdir($location = ''){ return path($location, 'app', 'dir'); }
    }

    if(!function_exists('assetdir'))
    {
        /**
         * Return asset_dir.
         * @author Jelle van der Coelen
         * @package Clockwork/Library/Path
         *
         * @param string $location Location to append to dir.
         *
         * @return string
         */
        function assetdir($location = ''){ return path($location, 'asset', 'dir'); }
    }
    
    if(!function_exists('plugindir'))
    {
        /**
         * Return plugin_dir.
         * @author Jelle van der Coelen
         * @package Clockwork/Library/Path
         *
         * @param string $location Location to append to dir.
         *
         * @return string
         */
        function plugindir($location = ''){ return path($location, 'plugin', 'dir'); }
    }

    if(!function_exists('sysdir'))
    {
        /**
         * Return sys_dir.
         * @author Jelle van der Coelen
         * @package Clockwork/Library/Path
         *
         * @param string $location Location to append to dir.
         *
         * @return string
         */
        function sysdir($location = ''){ return path($location, 'sys', 'dir'); }
    }

    if(!function_exists('root'))
    {
        /**
         * Return [$const]_dir.
         * @author Jelle van der Coelen
         * @package Clockwork/Library/Path
         *
         * @param string $location Location to append to dir.
         * @param string $const    Constant to use.
         *
         * @return string
         */
        function root($location = '', $const = 'root'){ return path($location, $const, 'dir'); }
    }

    if(!function_exists('path'))
    {
        /**
         * Return [const]_[path|dir].
         * @author Jelle van der Coelen
         * @package Clockwork/Library/Path
         *
         * @param string $location Location to append to dir.
         * @param string $const    Constant to use.
         * @param string $type     Type of constant to use (path|dir).
         *
         * @return string
         */
        function path($location = '', $const = 'root', $type = 'path'){ return Config::getSetting($const.'_'.$type).$location; }
    }

    if(!function_exists('apppath'))
    {
        /**
         * Return app_path.
         * @author Jelle van der Coelen
         * @package Clockwork/Library/Path
         *
         * @param string $location Location to append to path.
         *
         * @return string
         */
        function apppath($location = ''){ return path($location, 'app'); }
    }

    if(!function_exists('assetpath'))
    {
        /**
         * Return asset_path.
         * @author Jelle van der Coelen
         * @package Clockwork/Library/Path
         *
         * @param string $location Location to append to path.
         *
         * @return string
         */
        function assetpath($location = ''){ return path($location, 'asset'); }
    }
    
    if(!function_exists('pluginpath'))
    {
        /**
         * Return plugin_path.
         * @author Jelle van der Coelen
         * @package Clockwork/Library/Path
         *
         * @param string $location Location to append to path.
         *
         * @return string
         */
        function pluginpath($location = ''){ return path($location, 'plugin'); }
    }

    if(!function_exists('syspath'))
    {
        /**
         * Return sys_path.
         * @author Jelle van der Coelen
         * @package Clockwork/Library/Path
         *
         * @param string $location Location to append to path.
         *
         * @return string
         */
        function syspath($location = ''){ return path($location, 'sys'); }
    }
?>