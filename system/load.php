<?php
    // --- version
    define('CW_VERSION', '3.2.0');

    // --- this file was included, used to check direct file access
    define('ACCESS', true);

    /* SETTINGS */
    // --- PHP
    ob_start();
    header('Content-type: text/html;charset=utf-8');

    // --- error handling
    error_reporting(E_ALL);
    ini_set('display_errors', true);
    set_time_limit(10);

    // --- document root
    if(strpos(ASSET_FOLDER, '/') !== false)
    {
        $asset        = explode('/', ASSET_FOLDER);
        $docroot      = current($asset);
        $asset_folder = end($asset);

        if(strrpos($_SERVER['DOCUMENT_ROOT'], $docroot) !== false)
            $_SERVER['DOCUMENT_ROOT'] = substr_replace($_SERVER['DOCUMENT_ROOT'], '', strrpos($_SERVER['DOCUMENT_ROOT'], $docroot), strlen($docroot));
    }
    else
        $asset_folder = ASSET_FOLDER;

    // --- path|dir
    define('ROOT_DIR',    str_replace(SYS_FOLDER, '', dirname(__FILE__)));
    define('ROOT_PATH',   '/');
    define('APP_DIR',     ROOT_DIR.APP_FOLDER.'/');
    define('APP_PATH',    ROOT_PATH.APP_FOLDER.'/');
    define('PLUGIN_DIR',  ROOT_DIR.APP_FOLDER.'/plugin/');
    define('PLUGIN_PATH', ROOT_PATH.APP_FOLDER.'/plugin/');
    define('ASSET_DIR',   ROOT_DIR.ASSET_FOLDER.'/');
    define('ASSET_PATH',  ROOT_PATH.$asset_folder.'/');
    define('SYS_DIR',     ROOT_DIR.SYS_FOLDER.'/');
    define('SYS_PATH',    ROOT_PATH.SYS_FOLDER.'/');

    // --- system folders
    $folders = scandir(SYS_DIR);
    foreach($folders as $folder)
    {
        if(!preg_match('/^(\.)/', $folder) && is_dir(SYS_DIR.$folder))
        {
            define(strtoupper($folder).'_DIR',  SYS_DIR.$folder.'/');
            define(strtoupper($folder).'_PATH', SYS_PATH.$folder.'/');
        }
    }


    /* SESSION */
    @session_start();


    /* INCLUDE */
    /**
     * Autoloader, load or create classes and objects.
     * @package Clockwork/Core
     *
     * @param string $class Class to load.
     *
     * @return void
     */
    function autoload($class)
    {
        $file = $class.'.php';

        //core
        if(file_exists(CORE_DIR.$file))
            include_once CORE_DIR.$file;
        
        //model
        else if(file_exists(APP_DIR.'model/'.$file))
            include_once APP_DIR.'model/'.$file;
                
        //
        else
        {
            //plugin
            $pclass = explode('_', $class);

            if(Clockwork::isPluginLoaded($pclass[0]))
            {
                $file = Plugin::getInstance($pclass[0])->dir().'model/'.$pclass[1].'.php';
                
                if(file_exists($file))
                {
                    include_once $file;
                    $loaded = true;
                }
            }
            
            //
            if(!isset($loaded))
            {
                $code = 'class '.$class.' extends Model
                         {
                             public function __construct($mixed = null, $column = \'id\')
                             {
                                 parent::__construct($mixed, $column);
                             }
                         }';
    
                eval($code);
            }
        }
    }
    spl_autoload_register('autoload');


    /* INIT */
    // --- clockwork
    Singleton::getInstance('Clockwork')->init();

    // --- error handling
    if(!Config::getSetting('debug'))
    {
        error_reporting(0);
        ini_set('display_errors', false);
    }

    // --- sanitize
    if(Clockwork::isLibraryLoaded('sanitize') && Config::getSetting('sanitize_request'))
    {
        $_REQUEST = array();
        $_GET     = sanitize($_GET);
        $_POST    = sanitize($_POST);
    }

    // --- CSRF
    if(Clockwork::isModuleLoaded('CSRF') && $_POST)
    {
        try{
            CSRF::check($_POST['CSRF-key']);
        }catch(Exception $e){ unset($_POST); $_POST = []; }
    }

    // --- locale
    if(Config::getSetting('locale', false))
        setlocale(LC_ALL, Config::getSetting('locale'));

    // --- Login
    if(Clockwork::isModuleLoaded('Login') && !defined('CW_CRON') && !defined('CW_SKIP_LOGIN'))
    {
        new Login();

        $_loginpage = (Config::getSetting('login_loginpage', false, false) ? Config::getSetting('login_loginpage') : 'login/');
        $_allowed   = (Config::getSetting('login_no_login', false, false) ? Config::getSetting('login_no_login') : []);
        $_allowed[] = $_loginpage;

        if(!Login::getUser() && !in_array(substr((strpos($_SERVER['REQUEST_URI'], '?') !== false ? stristr($_SERVER['REQUEST_URI'], '?', true) : $_SERVER['REQUEST_URI']), 1), $_allowed))
            redirect($_loginpage);
    }

    // --- app
    if(!defined('CW_CRON') && !defined('CW_OVERRIDE_TEMPLATE'))
    {
        if(Config::getSetting('load_template_engine', false, true) && !isset($template))
        {
            $template = new Template();
            include_once APP_DIR.'index.php';
            $template->loadFromURL();
        }
        else
            include_once APP_DIR.'index.php';
    }
?>