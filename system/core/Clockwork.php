<?php if(!defined('ACCESS')) exit('Access denied');
    /**
     * Clockwork
     * @author Jelle van der Coelen
     * @package Clockwork/Core
     */
    class Clockwork extends Singleton
    {
        /**
         * Error level: fatal = 0
         * @const int
         */
        const ERROR_FATAL = 0;

        /**
         * Error level: warning = 1
         * @const int
         */
        const ERROR_WARNING = 1;

        /**
         *
         *
         */
        private $actions = [];

        /**
         * Loaded libraries
         * @var array
         */
        private $loadedLibraries = array();

        /**
         * Loaded modules
         * @var array
         */
        private $loadedModules = array();
        
        /**
         * Loaded plugins
         * @var array
         */
        public $loadedPlugins = array();
        
        /**
         * Stylesheets and script loaded for plugins
         * @var array
         */
        public $pluginLoadables = array();

        /**
         * Constructor
         *
         * @return void
         */
        protected function __construct(){}

        /**
         * Initalize framework
         *
         * @return void
         */
        public function init()
        {
            $this->loadSingleton('Config', 'load');
            
            $this->load('library');
            $this->load('module');

            //data
            if(self::isModuleLoaded('Data/Database'))
                $this->loadSingleton('Database', 'connect');

            //cache
            if(self::isModuleLoaded('Cache'))
                $this->loadSingleton('Cache');
            
            $this->load('plugin');
        }

        /**
         * Check whether a library is loaded or not
         *
         * @param string $library
         *
         * @return boolean
         */
        public static function isLibraryLoaded($library)
        {
            $clockwork = Clockwork::getInstance();
            
            return (array_search($library, $clockwork->loadedLibraries) !== false ? true : false);
        }

        /**
         * Check whether a module is loaded or not
         *
         * @param string $module
         *
         * @return boolean
         */
        public static function isModuleLoaded($module)
        {
            $clockwork = Clockwork::getInstance();

            return (array_search($module, $clockwork->loadedModules) !== false ? true : false);
        }
        
        /**
         * Check whether a plugin is loaded or not
         *
         * @param string $name
         *
         * @return boolean
         */
        public static function isPluginLoaded($name)
        {
            $clockwork = Clockwork::getInstance();
            
            return (!empty($clockwork->loadedPlugins[strtolower($name)]) !== false ? true : false);
        }

        /**
         * Load
         *
         * @param string $type
         *
         * @return boolean
         */
        private function load($type)
        {
            $data = Config::getData($type);

            foreach($data as $values)
            {
                foreach($values as $key => $value)
                {
                    if($value == 1)
                    {
                        if($type == 'plugin')
                            $key = $key.'/'.$key;

                        $file = constant(strtoupper($type).'_DIR').$key.'.php';

                        if(!file_exists($file))
                        {
                            $file = APP_DIR.$type.'/'.$key.'.php';
                            
                            if(strpos($key, '/') !== false && !file_exists($file))
                                $file = PLUGIN_DIR.str_replace('/', '/'.$type.'/', $key).'.php';
                        }
                        
                        if(file_exists($file))
                        {
                            include_once $file;

                            if($type == 'module')  $this->loadedModules[]   = $key;
                            if($type == 'library') $this->loadedLibraries[] = $key;
                        }
                        else
                            Clockwork::throwError('Could not load '.$type.' ('.$key.')');
                    }
                }
            }
        }

        /**
         * Load a class which is a singleton and optionally run a method
         *
         * @param string $classname
         * @param string $method    (optional, default = null)
         * 
         * @return object
         */
        private function loadSingleton($classname, $method = null)
        {
            $class = Singleton::getInstance($classname);
            
            if($method && method_exists($class, $method))
                return $class->{$method}();

            return $class;
        }
        
        /**
         * Load all plugin scripts and stylesheets
         * 
         * @return void
         */
        public function pluginLoader()
        {
            foreach($this->pluginLoadables as $type => $files)
            {
                foreach($files as $file)
                {
                    if($type == 'css')
                        echo '<link rel="stylesheet" href="'.$file.'" type="text/css" />'.PHP_EOL;
                    else if($type == 'js')
                        echo '<script src="'.$file.'"></script>'.PHP_EOL;
                }
            }
        }
        
        /**
         * Register a plugin
         *
         * @param string $name
         *
         * @return void
         */
        public static function registerPlugin($name, $plugin)
        {
            Clockwork::getInstance()->loadedPlugins[strtolower($name)] = $plugin;
        }

        /**
         * Throw an error and exit if needed
         *
         * @param string $error
         * @param int    $level      (optional, default = self::ERROR_FATAL)
         * @param int    $traceLevel (optional, default = 0)
         *
         * @return void
         */
        public static function throwError($error, $level = self::ERROR_FATAL, $traceLevel = 0)
        {
            if(!Config::getSetting('debug'))
                return false;

            $errors = array(0 => 'FATAL CLOCKWORK ERROR',
                            1 => 'CLOCKWORK WARNING');

            $trace = debug_backtrace();
            $trace = $trace[$traceLevel];

            if(isset($error[$level]))
                echo '<br /><b>'.$errors[$level].':</b> '.$error.' in <b>'.$trace['file'].'</b> on line <b>'.$trace['line'].'</b><br />';
            else
                self::throwError('Invalid error level', self::ERROR_WARNING, 1);
            
            if($level == self::ERROR_FATAL)
                exit;
        }
    }
?>