<?php if(!defined('ACCESS')) exit('Access denied');
    /**
     * Template
     * @author Jelle van der Coelen
     * @package Clockwork/Core
     */
    class Template
    {
        /**
         * Load controller with view
         * @const int
         */
        const LOAD_BOTH = 1;

        /**
         * Load controller only
         * @const int
         */
        const LOAD_CONTROLLER_ONLY = 0;

        /**
         * Load view only
         * @const int
         */
        const NO_CONTROLLER = -1;

        /**
         * Basedir for loading templates
         * @var string
         */
        private $basedir = '';

        /**
         * Layout to load
         * @var string
         */
        private $layout = 'default';

        /**
         * Whether to load layout or not
         * @var boolean
         */
        private $loadLayout = true;
        
        /**
         * Twig object
         * @var object
         */
        public $twig = null;

        /**
         * URI vars
         * @var array
         */
        private $urivars = array();

        /**
         * Parsed URL
         * @var array
         */
        private $url = array();
        
        /**
         * Whether to use basedir for layout and 404
         * @var boolean
         */
        private $useBasedir = true;

        /**
         * Assigned vars
         * @var array
         */
        private $vars = array();

        /**
         * Current view
         * @var string
         */
        private $view = '';

        /**
         * Set a variable to be used in view
         *
         * @param string $key
         * @param mixed  $var
         *
         * @return void
         */
        public function assign($key, $var = null){ $this->vars[$key] = $var; }

        /**
         * Constructor
         * 
         * @param mixed $settings (optional)
         *
         * @return void
         */
        public function __construct($settings = null)
        {
            // >= v3.x
            if($settings === null || is_array($settings))
            {
                $defaults = ['basedir'          => APP_DIR,
                             'loadFromUrl'      => false,
                             'useLayoutBasedir' => true,
                             'view'             => false];

                $settings = ($settings ? array_merge($defaults, $settings) : $defaults);
                $this->basedir    = $settings['basedir'];
                $this->useBasedir = $settings['useLayoutBasedir'];
            }
            
            if(Clockwork::isModuleLoaded('Twig/Autoloader'))
                $this->setupTwig();
                        
            $this->url = $this->parseUrl();

            // >= v3.x
            if(is_array($settings))
            {
                if($settings['loadFromUrl'] === true)
                    $this->loadFromURL();
                if($settings['view'] !== false)
                    $this->load($settings['view']);
            }
            
            // <= v2.x - Legacy
            else
            {
                if($view === true)
                    $this->loadFromURL();
                else if($view)
                    $this->load($settings['view']);
            }
        }

        /**
         * Disable layout
         *
         * @return void
         */
        public function disableLayout(){ $this->loadLayout = false; }

        /**
         * Return a previously assigned var
         *
         * @param string $key
         *
         * @return mixed
         */
        public function getAssignedVar($key){ return (isset($this->vars[$key]) ? $this->vars[$key] : null); }

        /**
         * Return all previously assigned vars
         *
         * @param string $key
         *
         * @return mixed
         */
        public function getAssignedVars(){ return $this->vars; }

        /**
         * Return uri var if set
         *
         * @param int $key
         *
         * @return string
         */
        public function getUriVar($key){ return (isset($this->urivars[$key]) ? $this->urivars[$key] : null); }

        /**
         * Return all uri vars
         *
         * @return array
         */
        public function getUriVars(){ return $this->urivars; }

        /**
         * Return (part of) url if set
         *
         * @param int $key (optional) Omit to return full url.
         *
         * @return string|array
         */
        public function getUrl($key = null)
        {
            if(is_int($key))
                return (isset($this->url[$key]) ? $this->url[$key] : null);
            else if($key == 'last')
                return $this->url[(count($this->url) - 1)];
            
            return $this->url;
        }
        
        /**
         * Get current view
         *
         * @return string
         */
        public function getView(){ return $this->view; }
        
        /**
         * Load template
         *
         * @param string $view
         * @param int    $controller (optional, default = self::LOAD_BOTH)
         *
         * @return void
         */
        public function load($view, $controller = self::LOAD_BOTH)
        {
            if($controller != self::LOAD_CONTROLLER_ONLY)
                $this->setView($view);

            if($controller != self::NO_CONTROLLER && file_exists($this->basedir.'controller/'.$view.'.php'))
                include $this->basedir.'controller/'.$view.'.php';

            if($controller == self::LOAD_CONTROLLER_ONLY)
                return;

            if(!file_exists($this->basedir.'template/view/'.$this->view.'.'.($this->twig ? 'twig' : 'php')))
                $this->setView('404');

            // - vars
            if(!$this->twig)
            {
                $vars = get_defined_vars();
                foreach($vars as $var => $value) unset($$var);
                foreach($this->vars as $var => $value) $$var = $value;
            }
            else
                $this->assign('this', $this);

            // - header
            if(Config::getSetting('autoload_layout') && $this->loadLayout)
                $this->loadHeader();

            // - view
            if($this->view != '404')
            {
                if($this->twig)
                {
                    $tpl = $this->twig->loadTemplate('view/'.$this->view.'.twig');
                    echo $tpl->render($this->vars);
                }
                else
                    include $this->basedir.'template/view/'.$this->view.'.php';
            }
            else if(file_exists($this->basedir.'template/view/404.'.($this->twig ? 'twig' : 'php')))
            {
                header('HTTP/1.1 404 Not found');
                
                $file = $this->basedir.'controller/404.php';
                if(file_exists($file)) include $file;

                if($this->twig)
                {
                    $tpl = $this->twig->loadTemplate('view/404.twig');
                    echo $tpl->render($this->vars);
                }
                else
                    include $this->basedir.'template/view/404.php';
            }
            else
                Clockwork::throwError('Template not found ('.$this->view.'), also no 404 view exists', Clockwork::ERROR_WARNING);

            // - footer
            if(Config::getSetting('autoload_layout') && $this->loadLayout)
                $this->loadFooter();

            // - parse variables
            if(!Config::getSetting('parse_template_vars'))
                return;
        }

        /**
         * Load controller only
         * 
         * @param string $view
         *
         * @return void
         */
        public function loadController($view){ $this->load($view, self::LOAD_CONTROLLER_ONLY); }

        /**
         * Load footer template
         *
         * @param string $layout (optional)
         *
         * @return void
         */
        public function loadFooter($layout = null){ $this->loadLayoutPart($layout, 'footer'); }

        /**
         * Load controller and view according to parsed URL
         *
         * @param boolean $reparse (optional, default = false)
         * @param boolean $shift (optional, default = false)
         *
         * @return void
         */
        public function loadFromUrl($reparse = false, $shift = false)
        {
            if($reparse)
                $this->url = $this->parseUrl();

            $this->route();
            $this->rewrite();

            if($shift)
                array_shift($this->url);

            $view = implode('/', $this->url);
            $dir  = $this->basedir.'template/view/';

            if(!file_exists($dir.$view.'.php') || !file_exists($this->basedir.'controller/'.$view.'.php'))
                if(is_dir($dir.$view) || is_dir($this->basedir.'controller/'.$view))
                    $view .= ($shift && preg_match('/(\/)$/', $view) ? '' : '/').'index';

            $this->load($view);
        }

        /**
         * Load header template
         *
         * @param string $layout (optional)
         *
         * @return void
         */
        public function loadHeader($layout = null){ $this->loadLayoutPart($layout, 'header'); }

        /**
         * Load header or footer
         *
         * @param string $layout (optional)
         * @param string $part   (optional, default = 'header')
         *
         * @return void
         */
        private function loadLayoutPart($layout = null, $part = 'header')
        {
            if(!$layout)
                $layout = $this->layout;

            //$file = ($this->useBasedir ? $this->basedir : APP_DIR).'template/layout/'.$layout.'.'.($this->twig ? 'twig' : 'php');

            //if(file_exists($file))
            {
                if($this->twig)
                {
                    $this->assign('part', $part);
                    $tpl = $this->twig->loadTemplate('layout/'.$layout.'.twig');
                    echo $tpl->render($this->vars);
                }
                else
                {
                    foreach($this->vars as $var => $value)
                        $$var = $value;
    
                    include $file;
                }
            }
            //else
            //    Clockwork::throwError('Layout not found ('.$layout.')', Clockwork::ERROR_WARNING);
        }

        /**
         * Load view only
         * 
         * @param string  $view
         * @param boolean $layout (optional, default = true) Load layout
         *
         * @return void
         */
        public function loadView($view, $layout = true)
        {
            if(!$layout) $this->loadLayout = false;
            $this->load($view, self::NO_CONTROLLER);
            if(!$layout) $this->loadLayout = true;
        }

        /**
         * Parse URL
         *
         * @return array
         */
        public function parseUrl()
        {
            $url = explode('/', substr(current(explode('?', urldecode($_SERVER['REQUEST_URI']))), strlen(ROOT_PATH)));
            $last = end($url);

            if(empty($last) && count($url) == 1)
                $url[0] = 'index';
            else if(empty($last))
                array_pop($url);

            return $url;
        }

        /**
         * Check whether url should be rewritten or not
         *
         * @return string
         */
        private function rewrite()
        {
            $rewrites = current(Config::getData('rewrite'));

            if(!empty($rewrites))
            {
                foreach($rewrites as $orig => $rewrite)
                {
                    if(preg_match('/^([0-9]\:)/', $orig))
                    {
                        $orig = explode(':', $orig);

                        if($this->url[$orig[0]] == $orig[1])
                            $this->url[$orig[0]] = $rewrite;
                    }
                }

                $uri = implode('/', $this->url);

                if(isset($rewrites[$uri]))
                    $this->url = explode('/', $rewrites[$uri]);
            }

            return implode('/', $this->url);
        }

        /**
         * Route url for use of URI vars
         *
         * @return void
         */
        public function route()
        {
            $routes = current(Config::getData('routes'));

            $origUrl = $this->url;
            $routeThis = false;

            foreach($routes as $route => $count)
            {
                $buildupUrl = '';
                foreach($this->url as $key => $url)
                {
                    $buildupUrl .= ($buildupUrl != '' ? '/' : '').$url;

                    $urlTest = array();
                    foreach($origUrl as $uk => $u)
                        if($uk <= substr_count($route, '/'))
                            $urlTest[] = $u;

                    if($routeThis)
                    {
                        for($i = 0; $i < $count; $i++)
                        {
                            if(isset($this->url[($key+$i)]) && !in_array($url, $urlTest))
                            {
                                $this->urivars[] = $this->url[($key+$i)];
                                unset($this->url[($key+$i)]);
                            }
                        }
                        
                        $routeThis = false;
                    }
                    
                    if(count($this->urivars) < $count && ($route == $buildupUrl))
                        $routeThis = true;
                }
            }

            if(Clockwork::getInstance()->isLibraryLoaded('sanitize'))
                $this->urivars = sanitize($this->urivars);
        }
        
        /**
         * Set basedir
         *
         * @param string $dir
         *
         *  @return void
         */
        public function setBasedir($dir){ $this->basedir = $dir; }

        /**
         * Set layout name
         *
         * @param string $layout
         *
         * @return void
         */
        public function setLayout($layout){ $this->layout = $layout; }

        /**
         * Setup Twig template engine
         *
         * @return void
         */
        public function setupTwig()
        {
            Twig_Autoloader::register(true);

            if(!is_dir($this->basedir.'template/'))
                Clockwork::throwError('Template directory ('.$this->basedir.'template/) does not exists');
            
            $paths = [$this->basedir.'template/', APP_DIR.'template/'];
            $plugins = Clockwork::getInstance()->loadedPlugins;
            
            foreach($plugins as $plugin)
                if(is_dir($plugin->dir().'template/'))
                    $paths[] = $plugin->dir().'template/';
            
            $loader     = new Twig_Loader_Filesystem($paths);
            $this->twig = new Twig_Environment($loader, array('cache' => (Config::getSetting('twig_cache') ? appdir('template/cache/') : false)));

            $functions = get_defined_functions();

            foreach($functions['user'] as $function)
                if(strpos(strtolower($function), 'twig') === false)
                    $this->twig->addFunction(new Twig_SimpleFunction($function, $function));
            
            $this->twig->addFunction(new Twig_SimpleFunction('PluginLoader', [Clockwork::getInstance(), 'pluginLoader']));
        }

        /**
         * Set template name
         *
         * @param string $view
         *
         * @return void
         */
        public function setView($view){ $this->view = $view; }
    }
?>