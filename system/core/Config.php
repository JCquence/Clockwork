<?php if(!defined('ACCESS')) exit('Access denied');
    /**
     * Config
     * @author Jelle van der Coelen
     * @package Clockwork/Core
     */
    class Config extends Singleton
    {
        /**
         * Holds loaded .ini file.
         * @var array
         */
        private $data = array();

        /**
         * Holds all settings.
         * @var array
         */
        private $settings = array();

        /**
         * Constructor.
         * 
         * @return void
         */
        public function __construct(){}

        /**
         * Return raw data from .ini file.
         *
         * @param string $section Section to return values of.
         *  
         * @return array
         */
        public static function getData($section = null)
        {
            $config = Config::getInstance();

            if(!$section)
                return $config->data;
            else
            {
                $values = array();

                foreach($config->data as $key => $value)
                    if(strtolower($section) == strtolower($key))
                        $values[] = $value;

                return $values;
            }
        }

        /**
         * Return a setting.
         *
         * @param string  $key     Setting to return.
         * @param boolean $error   Throw an error if setting does not exists.
         * @param boolean $default Default value to return if setting does not exists.
         *
         * @return mixed
         */
        public static function getSetting($key, $error = true, $default = null)
        {
            $config = self::getInstance();

            if(!isset($config->settings[$key]))
            {
                if(defined(strtoupper($key)))
                    return constant(strtoupper($key));

                if($error)
                    Clockwork::getInstance()->throwError('Config ('.$key.') not found', Clockwork::ERROR_WARNING, 1);

                return $default;
            }
            else
                return $config->settings[$key];
        }

        /**
         * Load .ini file and parse contents.
         *
         * @return void
         */
        public function load()
        {
            $ini = APP_DIR.'config/'.ENVIRONMENT.'.ini';

            if(!file_exists($ini))
                Clockwork::throwError('Could not load config ('.$ini.')');

            $this->data = parse_ini_file($ini, true);
            
            $this->setValues();
        }

        /**
         * Set values in the correct section.
         *
         * @return void
         */
        private function setValues()
        {
            foreach($this->data as $type => $values)
                if(!preg_match('/(library)/i', $type))
                    foreach($values as $key => $value)
                        $this->{strtolower($type)}[$key] = $value;
        }
    }
?>