<?php if(!defined('ACCESS')) exit('Access denied');
    /**
     * Singleton
     * @author Jelle van der Coelen
     * @package Clockwork/Core
     */
    class Singleton
    {
        /**
         * Holds all singleton instances.
         * @var array
         */
        protected static $instance = array();
        
        /**
         * Get singleton instance and save it, prevent multiple instances.
         * 
         * @param string $class Class to retunr instance for.
         *
         * @return object
         */
        public static function getInstance($class = null)
        {
            if(!$class)
                $class = get_called_class();

            if(!isset(self::$instance[$class]))
                self::$instance[$class] = new $class;
            
            return self::$instance[$class];
        }
    }
?>