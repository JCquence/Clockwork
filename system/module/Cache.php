<?php if(!defined('ACCESS')) exit('Access denied');
    /**
     * Cache
     * @author Jelle van der Coelen
     * @package Clockwork/Module
     */
    class Cache extends Singleton
    {
        /**
         * Holds data.
         * @var array
         */
        private $data = array();
        
        /**
         * Constructor.
         *
         * @return void
         */
        public function __construct()
        {
        }

        /**
         * Load data from cache.
         *
         * @param string $key Data to load.
         *
         * @return void
         */
        public static function loadData($key)
        {
            $cache = Cache::getInstance();

            $data = (isset($cache->data[$key]) ? $cache->data[$key] : null);
            
            if($key != 'ModelCachingFields' && $data === null && Config::getSetting('cache_to_database', false, false) && Clockwork::isModuleLoaded('Data/Database'))
            {
                if(($obj = Caching::create($key, '*`key`')) !== false)
                {
                    if($obj->get('object'))
                    {
                        $class = $obj->get('object');
                        new $class();
                    }

                    if($obj->get('lifespan') > 0)
                    {
                        $time = strtotime($obj->get(($obj->get('edited', -1) != '0000-00-00 00:00:00' ? 'edited' : 'added')));
                        
                        if($time < (time() - $obj->get('lifespan')))
                            $obj->deleteObject();
                    }

                    $data = unserialize($obj->get('value'));
                    $cache->data[$key] = $data;
                }
            }
            
            return $data;
        }

        /**
         * Save data to cache.
         *
         * @param string  $key   Data to save as.
         * @param mixed   $value Data to save.
         * @param boolean $db    Cache to database.
         *
         * @return void
         */
        public static function saveData($key, $value, $db = false)
        {
            $cache = Cache::getInstance();
            $cache->data[$key] = $value;
            
            if($key != 'ModelCachingFields' && $db && Config::getSetting('cache_to_database', false, false) && Clockwork::isModuleLoaded('Data/Database'))
            {
                $lifespan = ($db === true ? 0 : $db);

                if(($obj = Caching::create($key, '*`key`')) === false)
                    $obj = new Caching();

                $obj->set('key',      $key)
                    ->set('value',    serialize($value))
                    ->set('object',   (is_object($value) ? get_class($value) : ''))
                    ->set('lifespan', $lifespan)->save();
            }
        }
    }
?>