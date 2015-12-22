<?php
    /**
     * Plugin
     * @author Jelle van der Coelen
     * @package Clockwork/Core
     */
    class Plugin
    {
        /**
         * Directory name
         * @var string
         */
        private $dir = '';
        
        /**
         * Constructor, set directory name
         *
         */
        public function __construct($dir = null){ $this->dir = ($dir ? preg_replace('/\/$/', '', $dir).'/' : get_called_class().'/'); }
        
        /**
         * Return plugin dir
         *
         * @return string
         */ 
        public function dir(){ return plugindir($this->dir); }
        
        /**
         * Add files to be loaded within the <head>
         *
         * @param string $file
         *
         * @return void
         */
        public function addToLoader($file, $type = 'css'){ Clockwork::getInstance()->pluginLoadables[$type][] = $this->assetpath($file); }
        
        /**
         * Swap assetpath for plugin asset path
         *
         * @param string $file
         *
         * @return void
         */
        public function assetpath($file)
        {
            $path = explode('/', $file);
            $file = explode('.', end($path));
            
            $file[(count($file)-2)] = $file[(count($file)-2)].'.plugin';
            return assetpath($path[0].'/'.$this->dir.implode('.', $file));
        }
        
        /**
         * Return plugin class for $name
         *
         * @param string $name
         *
         * @return object;
         */
        public static function getInstance($name = null)
        {
            if(!$name)
                $name = get_called_class();
            
            return Clockwork::getInstance()->loadedPlugins[strtolower($name)];
        }
        
        /**
         * Override template engine by loading plugin templates
         *
         * @param string $path
         *
         * @return void
         */
        public function setupOverrideForPath($path, $lvl = null, $basedir = true)
        {
            $template = new Template(['basedir' => ($basedir && $basedir === true ? $this->dir() : $basedir), 'useLayoutBasedir' => ($basedir === false ? false : true)]);
            
            $url = $template->getUrl();
            $url = ($lvl !== null ? implode('/', array_splice($url, 0, ($lvl + 1))) : implode('/', $url));

            if(!is_array($path))
                $path = [$path];

            foreach($path as $p)
            {
                if($url == $p)
                {
                    include_once APP_DIR.'index.php';
                    include_once $this->dir().'index.php';
                    $template->loadFromURL(false, ($basedir && $basedir === true ? true : false));
                    define('CW_OVERRIDE_TEMPLATE', true);
                    break;
                }
            }
        }
    }
?>