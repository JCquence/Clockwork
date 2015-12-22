<?php if(!defined('ACCESS')) exit('Access denied');
    /**
     * ModelObject
     * @author Jelle van der Coelen
     * @package Clockwork/Core
     */
    class ModelObject
    {
        /**
         * Holds errors.
         * @var array
         */
        protected $errors = array();
        
        /**
         * Holds database fields.
         * @var array
         */
        protected $fields = array();

        /**
         * Holds values.
         * @var array
         */
        protected $values = array();

        /**
         * Always return true, override this.
         *
         * @param int $step Step to check.
         *
         * @return boolean
         */
        public function check($step = 0){ return ($this->hasErrors() ? false : true); }

        /**
         * Check whether the given value is empty or not.
         *
         * @param string $field Field to check.
         * @param string $error Error message.
         *
         * @return void
         */
        public function checkDefault($field, $error = '')
        {
            if(!$this->get($field))
                $this->setError($field, $error);
        }

        /**
         * Constructor.
         *
         * @return void
         */
        public function __construct()
        {
            $this->getFields();
        }

        /**
         * Return a value.
         *
         * @param string  $key Value to return.
         * @param boolean $ret Set to true to return Date object instead of formatted date.
         *
         * @return mixed
         */
        public function get($key, $ret = false)
        {
            if($ret !== -1 && ($field = $this->getFields($key)) && preg_match('/(date)/i', $field['Type']))
            {
                if(isset($this->values[$key]))
                {
                    if(Config::getSetting('check_empty_date'))
                    {
                        if(
                            ($field['Type'] == 'datetime' && $this->values[$key] == '0000-00-00 00:00:00') ||
                            ($field['Type'] == 'date' && $this->values[$key] == '0000-00-00')
                        )
                            return null;
                    }
                    
                    if(Clockwork::isModuleLoaded('Date'))
                    {
                        $date = new Date($this->values[$key]);

                        if($ret)
                            return $date;
                        else
                            return $date->format();
                    }
                }
                else
                    return null;
            }

            return (isset($this->values[$key]) ? $this->values[$key] : null);
        }

        /**
         * Return a specific error.
         *
         * @param string $field Error to return.
         *
         * @return string
         */
        public function getError($field){ return (isset($this->errors[$field]) ? $this->errors[$field] : null); }
        
        /**
         * Return all errors.
         *
         * @return array
         */
        public function getErrors(){ return $this->errors; }
        
        /**
         * Get columns from database.
         *  
         * @param string|boolean $key Field to return.
         *
         * @return array
         */
        public function getFields($key = null)
        {
            if(Clockwork::isModuleLoaded('Cache') && count($this->fields) == 0)
                $this->fields = Cache::loadData('Model'.ucfirst($this->modelName).'Fields');

            if(count($this->fields) == 0 && Clockwork::isModuleLoaded('Data/Database'))
            {
                $this->fields = Database::getInstance()->fetchAll("SHOW COLUMNS FROM ".$this->tableName);
                
                if(Clockwork::isModuleLoaded('Cache'))
                    Cache::saveData('Model'.ucfirst($this->modelName).'Fields', $this->fields, true);
            }

            if(!$key)
                return $this->fields;
            else if($key === true)
            {
                $fields = array();
                foreach($this->fields as $field)
                    $fields[] = $field['Field'];

                return $fields;
            }
            else if($key == 'primary' && !empty($this->fields))
            {
                $pri = array();

                foreach($this->fields as $field)
                    if($field['Key'] == 'PRI')
                        $pri[] = $field['Field'];
                
                return $pri;
            }
            else
            {
                if(!empty($this->fields))
                    foreach($this->fields as $field)
                        if($field['Field'] == $key)
                            return $field;

                return null;
            }

        }

        /**
         * Return an array with all values.
         *
         * @return array
         */
        public function getValues(){ return $this->values; }

        /**
         * Check whether there are errors set or not.
         *
         * @return boolean
         */
        public function hasErrors(){ return (count($this->getErrors()) > 0 ? true : false); }
        
        /**
         * Return a hashed string of $value.
         *
         * @param boolean $salt  Include salt.
         * @param string  $field Field to hash.
         *
         * @return string
         */
        public function hash($salt = false, $field = 'id'){ return hashStr($this->get($field), $salt); }

        /**
         * Check whether to insert or update object.
         *
         * @param int    $step   Step to use for check().
         * @param string $column #faal
         *
         * @return boolean|object
         */
        public function save($step = 0, $column = 'id')
        {
            if(!$this->get('id'))
                return $this->addObject($this, $step);
            else
                return $this->updateObject($this, $step, $column);
        }

        /**
         * Set a value.
         *
         * @param string $key   Value to set
         * @param mixed  $value Actual value
         *
         * @return self
         */
        public function set($key, $value){ $this->values[$key] = $value; return $this; }

        /**
         * Set an error.
         *
         * @param string $field Error to set.
         * @param string $value Error message.
         *
         * @return self
         */
        public function setError($field, $value){ $this->errors[$field] = $value; return $this; }

        /**
         * Find and set an object as value.
         * @since 2.1.8
         *
         * @param string|array $object Name of object to search for.
         *
         * @return self
         */
        public function setObject($object)
        {
            if(!is_array($object))
                $this->set($object, $this->find($object)->first());
            else
                foreach($object as $obj)
                    $this->setObject($object);

            return $this;
        }
    }
?>