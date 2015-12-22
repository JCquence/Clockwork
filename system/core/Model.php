<?php if(!defined('ACCESS')) exit('Access denied');
    /**
     * Model
     * @author Jelle van der Coelen
     * @package Clockwork/Core
     */
    class Model extends ModelObject
    {
        /**
         * Active Record.
         * @var object
         */
        private $ActiveRecord;

        /**
         * Name of the Model.
         * @var string
         */
        protected $modelName;

        /**
         * Name of database table.
         * @var string
         */
        protected $tableName;

        /**
         * Holds objects collected from query.
         * @var array
         */
        private $objects = array();

        /**
         * Add an object to the database, returns object on failure.
         *
         * @param object $object Object to add.
         * @param int    $step   Step to use in check().
         *
         * @return boolean|object
         */
        protected function addObject(&$object, $step = 0)
        {
            if(!Clockwork::isModuleLoaded('Data/Database'))
                return;

            $database = Database::getInstance();

            if($object->check($step))
            {
                if($this->getFields('added'))
                    $object->set('added', date('Y-m-d H:i:s'));
                
                $fields = $this->getFields(true);

                $sql  = "INSERT INTO `".$this->tableName."` (";
                $sql .= "`".implode('`, `', $fields)."`";
                $sql .= ")VALUES(";

                $params = array();

                foreach($fields as $field)
                {
                    $sql .= ':'.$field.', ';
                    $params[$field] = $object->get($field, -1);
                }

                foreach($params as $field => $value)
                {
                    if($value === null)
                    {
                        $f = $object->getFields($field);

                        if($f && $f['Default'] !== null)
                            $params[$field] = $f['Default'];
                        else if($f && $f['Default'] === null && $f['Null'] == 'NO')
                            $params[$field] = '';
                    }
                }
                
                $sql = substr($sql, 0, -2).")";

                if($database->query($sql, $params))
                {
                    $object->set('id', $database->insertId());

                    return true;
                }
                else
                    $object->setError('handle', 'Add query failed');
            }
            
            return $object;
        }
        
        /**
         * Return all objects.
         *
         * @return array
         */
        public static function all()
        {
            $class = get_called_class();
            return (new $class(new Query))->getObjects();
        }

        /**
         * Constructor.
         *
         * Provide a Query object to run this query and return found objects.
         * Provide an integer to look for this ID (or $column).
         * Provide an array to create an new object filled with these values.
         *
         * @param mixed $mixed  What to get?
         * @param mixed $column Column to search in.
         *
         * @return void
         */
        public function __construct($mixed = null, $column = 'id')
        {
            //set modelname
            $this->modelName = get_called_class();

            //set table name
            if(empty($this->tableName))
                $this->tableName = Config::getSetting('db_table_prefix').strtolower($this->modelName);

            //cache fields
            parent::__construct();

            //determine action
            if($mixed !== null)
            {
                if(is_object($mixed) && get_class($mixed) == 'Query' && Clockwork::isModuleLoaded('Data/Query'))
                {
                    if($column == 1)
                    {
                        $this->values = $mixed->from(substr($this->tableName, strlen(Config::getSetting('db_table_prefix'))), null, true, true)->limit(1)->run(1);

                        if(empty($this->values))
                            $this->setError(404, 'Object not found');
                    }
                    else
                        $this->objects = $this->createObjects($mixed->from(substr($this->tableName, strlen(Config::getSetting('db_table_prefix'))), null, true, true)->run());
                }
                else if(is_array($mixed))
                {
                    foreach($mixed as $key => $value)
                        $this->set($key, $value);
                }
                else
                {
                    if(Clockwork::isModuleLoaded('Data/Query'))
                    {
                        $query = new Query();
                        $this->values = $query->from(strtolower($this->modelName))->where($column." = '".$mixed."'")->run(1);

                        if(empty($this->values))
                            $this->setError(404, 'Object not found');
                    }
                }
            }
        }

        /**
         * Create an object and return false on failure.
         *
         * @param mixed $mixed
         * @param mixed $column
         *
         * @return object|boolean
         */
        public static function create($mixed = null, $column = 'id')
        {
            $class = get_called_class();
            $object = new $class($mixed, $column);

            if($mixed === null)
                return $object;
            
            else if(!$object->getError(404))
            {
                if(!empty($object->objects))
                    return $object->objects;
                else if(is_object($mixed) && get_class($mixed) == 'Query' && Clockwork::isModuleLoaded('Data/Query') && $column != 1)
                    return false;

                return $object;
            }

            return false;
        }

        /**
         * Create objects from array or SQL.
         *
         * @param string|array $input Input to create objects from.
         *
         * @return array
         */
        public static function createObjects($input)
        {
            $objects = array();

            if(!is_array($input) && Clockwork::isModuleLoaded('Data/Database'))
            {
                $db    = Database::getInstance();
                $input = $db->fetchAll($db->query($input));
            }

            $objects = array();
            $model = get_called_class();

            if(!empty($input))
                foreach($input as $vars)
                    $objects[] = new $model($vars);

            return $objects;
        }

        /**
         * Delete an object.
         *
         * @param mixed   $object Object to delete. Omit this to delete the current object ($this).
         * @param boolean $force  Force deletion from database otherwise set status to -1.
         *
         * @return void
         */
        public function deleteObject($object = null, $force = true)
        {
            if(!$object)
                $object = $this;
            
            if($force && Clockwork::isModuleLoaded('Data/Database'))
                Database::getInstance()->query("DELETE FROM ".$this->tableName." WHERE id = :id", array('id' => $object->get('id')));
            else
                $object->set('status', -1)->save();
        }

        /**
         * Return collected objects from query.
         *
         * @return array
         */
        public function getObjects(){ return $this->objects; }

        /**
         * Check what to do with object.
         *
         * @param array  $vars Vars to pass into object.
         * @param int    $step Step to use in check().
         *
         * @return boolean|object
         */
        public function handleObject($vars, $step = 0)
        {
            $object = $this->parseObject($vars);
            
            return $object->save($step);
        }

        /**
         * Extend an object with provided vars.
         *
         * @param array  $vars   Vars to extend object with
         * @param object $object Object to extend.
         *
         * @return object
         */
        public function parseObject($vars = array(), $object = null)
        {
            if($object == null)
                $object = $this;
            
            $fields = $this->getFields(true);
            foreach($fields as $field)
                if(isset($vars[$field]))
                    $object->set($field, $vars[$field]);
               
            return $object;
        }
        
        /**
         * Return array with values of objects
         * @since 3.2.0
         *
         * @param string|array $key Provide an array of keys to return multidimensional array.
         * @param string       $index Set an index to use for the output array
         *
         * @return array
         */
        public function toArray($key = null, $index = null){ return objectToArray($this->getObjects(), $key, $index); }

        /**
         * Update an object in the database, returns object on failure.
         *
         * @param object $object Object to update.
         * @param int    $step   Step to use in check().
         * @param string $column Column to use in WHERE statement. Defaults to primary key.
         *
         * @return boolean|object
         */
        protected function updateObject(&$object, $step = 0, $column = null)
        {
            if(!Clockwork::isModuleLoaded('Data/Database'))
                return;

            $database = Database::getInstance();

            if($object->check($step))
            {
                if($this->getFields('edited'))
                    $object->set('edited', date('Y-m-d H:i:s'));

                $params = array();

                $sql = "UPDATE ".$this->tableName." SET ";

                //check fields if value is given
                $fields = $this->getFields(true);
                foreach($fields as $field)
                {
                    $found = false;
                    foreach($object->values as $f => $value)
                        if($field == $f)
                            $found = true;

                    if(!$found)
                        unset($fields[array_search($field, $fields)]);
                }

                //query
                foreach($fields as $field)
                {
                    $info = $object->getFields($field);

                    $sql .= "`".$field."` = :".$field.", ";
                    $params[$field] = $object->get($field, -1);
                }
                
                $sql = substr($sql, 0, -2)." WHERE ";
                
                if($column === null)
                {
                    $primary = $object->getFields('primary');
                    
                    foreach($primary as $field)
                    {
                        $sql .= "`".$field."` = :".$field." AND ";
                        $params[$field] = $object->get($field, -1);
                    }
                    
                    $sql = substr($sql, 0, -5);
                }
                else
                    $sql .= $column." = '".$object->get($column, -1)."'";
                
                //
                if($database->query($sql, $params))
                    return true;
                else
                    $object->setError('handle', 'Update query failed');
            }

            return $object;
        }

        /* ActiveRecord */

        /**
         * Magic __call for calling ActiveRecord functions.
         *
         * @param string $method Called method.
         * @param array  $args   Used arguments.
         *
         * @return void
         */
        public function __call($method, $args)
        {
            if(!empty($this->ActiveRecord) && (in_array($method, get_class_methods('ActiveRecord')) || preg_match('/^(by)/', $method)))
                return $this->ActiveRecord->{$method}($args);
            else
            {
                $trace = debug_backtrace();

                trigger_error('Call to undefined method '.$this->modelName.'::'.$method.'() in <b>'.$trace[0]['file'].'</b> on line <b>'.$trace[0]['line'].'</b><br />', E_USER_ERROR);
            }
        }

        /**
         * Extend $this with ActiveRecord.
         *
         * @param string $object Object to extend.
         *
         * @return self
         */
        public function find($object)
        {
            if(Clockwork::getInstance()->isModuleLoaded('Data/ActiveRecord'))
            {
                if(!$this->ActiveRecord || $this->ActiveRecord != $object)
                    $this->ActiveRecord = new ActiveRecord($object, $this);
            }

            return $this;
        }
    }
?>