<?php if(!defined('ACCESS')) exit('Access denied');
    /**
     * Active Record
     * @author Jelle van der Coelen
     * @package Clockwork/Module/Data
     */
    class ActiveRecord
    {
        /**
         * Caller
         * @var string
         */
        private $caller;

        /**
         * Calling for
         * @var string
         */
        private $object;

        /**
         * Parent object
         * @var object
         */
        private $parent;

        /**
         * Constructor, setup vars and check fields
         *
         * @param string $object
         * @param object $parent
         *
         * @throws Clockwork Warning when requirements are not met
         *
         * @return void
         */
        public function __construct($object, $parent)
        {
            $this->caller = strtolower(get_class($parent));
            $this->object = $object;
            $this->table  = $object;
            $this->parent = $parent;

            $class = ucfirst($this->object);
            $check = new $class();
            
            // --- plugin fix
            if(strpos($this->caller, '_') !== false)
            {
                $plugin = explode('_', get_class($parent));
                $this->table = $plugin[0].'_'.ucfirst($this->object);
            }

            if(!$check->getFields($this->caller.'Id') && !$parent->getFields($this->object.'Id'))
            {
                $trace = debug_backtrace();
                Clockwork::throwError(ucfirst($object).' has no link with '.get_class($parent).' in <b>'.$trace[1]['file'].'</b> on line <b>'.$trace[1]['line'].'</b><br />', Clockwork::ERROR_WARNING, 1);
            }
        }

        /**
         * Return all objects
         *
         * @return array
         */
        public function all()
        {
            $class = ucfirst($this->table);
            return $class::create($this->query());
        }

        /**
         * Find objects by column
         *
         * @param string $args Column to search for
         *
         * @return array
         */
        public function by($args)
        {
            $class = ucfirst($this->table);
            return $class::create($this->query()->where($args[0]));
        }

        /**
         * Find first object
         *  
         * @param string  $args Column to order by
         * @param boolean $last (optional, default = false) Find last record instead of first
         *
         * @return object
         */
        public function first($args, $last = false)
        {
            $order  = preg_replace('/(asc|desc)$/i', '', trim((!empty($args[0]) ? $args[0] : 'id')));
            $order .= ' '.($last ? 'DESC' : 'ASC');

            $class = ucfirst($this->table);
            return $class::create($this->query()->order($order)->limit(1), 1);
        }

        /**
         * Find last object
         *  
         * @param string  $args Column to order by
         *
         * @return object
         */
        public function last($args){ return $this->first($args, true); }

        /**
         * Return Query with correct where clause
         *
         * @return object
         */
        private function query()
        {
            $class = ucfirst($this->table);
            $check = new $class();

            $query = new Query();
            if($check->getFields($this->caller.'Id'))
                $query->where($this->caller."Id = '".$this->parent->get('id')."'");
            else if($this->parent->getFields($this->object.'Id'))
                $query->where("id = '".$this->parent->get($this->object.'Id')."'");

            return $query;
        }

        /**
         * Find objects by column
         *
         * @param string $method
         * @param array  $args
         *
         * @throws Clockwork Warning when requirements are not met
         *
         * @return array
         */
        public function __call($method, $args)
        {
            if(preg_match('/^(by)/', $method))
                $this->by(array(lcfirst(end(preg_split('/^(by)/', $method))).' = '.$args[0][0]));
            else
            {
                $trace = debug_backtrace();
                Clockwork::throwError('Call to undefined method '.ucfirst($this->caller).'::'.$method.'() in <b>'.$trace[0]['file'].'</b> on line <b>'.$trace[0]['line'].'</b><br />', Clockwork::ERROR_WARNING, 1);
            }
        }
    }
?>