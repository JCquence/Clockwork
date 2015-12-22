<?php if(!defined('ACCESS')) exit('Access denied');
    /**
     * Database
     * @author Jelle van der Coelen
     * @package Clockwork/Module/Data
     */
    class Database extends Singleton
    {
        /**
         * Database PDO object.
         * @var object
         */
        private $dbh;

        /**
         * Determine wheter to show debug info or not.
         * @var boolean
         */
        var $debug = false;

        /**
         * Holds resultset.
         * @var resource
         */
        var $resultset;
        
        /**
         * Constructor.
         *
         * @return void
         */
        public function __construct()
        {
        }
        
        /**
         * Close connection.
         *
         * @return void
         */
        public static function close(){ $db = Database::getInstance(); $db->dbh = null; }
        
        /**
         * Connect to database.
         *
         * @return void
         */
        public function connect()
        {
            try
            {
                $this->dbh = new PDO(Config::getSetting('db_type').':unix_socket=/var/run/mysqld/mysqld.sock;dbname='.Config::getSetting('db_name').';host='.Config::getSetting('db_host'), Config::getSetting('db_user'), Config::getSetting('db_pass'));

                $this->query("SET CHAR SET UTF8");
            }
            catch(PDOException $e){ echo 'Connection failed: '. $e->getMessage(); }
        }

        /**
         * Set debug mode.
         *
         * @return void
         */
        public static function debug()
        {
            $db = Database::getInstance();

            $db->debug = ($db->debug ? false : true);
        }
        
        /**
         * Fetch row(s), return false on failure.
         *
         * @param resource $resultset Queried resultset.
         * @param boolean  $fetchAll  Determines whether to fetch all or not.
         *
         * @return array|boolean
         */
        public function fetch($resultset = null, $fetchAll = false)
        {
            if(!is_object($resultset))
                $resultset = $this->query($resultset);

            if($resultset == null)
                $resultset = $this->resultset;
                
            if($resultset != null)
            {
                if($fetchAll)
                    return $resultset->fetchAll(PDO::FETCH_ASSOC);
                else
                    return $resultset->fetch(PDO::FETCH_ASSOC);
            }
            else
                return false;
        }
        
        /**
         * Fetch all rows, return false on failure.
         *
         * @param resource $resultset Queried resultset.
         *
         * @return array|boolean
         */
        public function fetchAll($resultset = null){ return $this->fetch($resultset, true); }
        
        /**
         * Prepare and execute query.
         *
         * @param string $sql     SQL string to query.
         * @param array  $params  Omit to do a normal query instead of a PDO prepared query.
         *
         * @return resource
         */
        public function query($sql, $params = null)
        {
            if(!$params)
                $this->resultset = $this->dbh->query($sql);
            else
            {
                $stmt = $this->dbh->prepare($sql);

                if(!empty($params))
                {
                    foreach($params as $column => $param)
                    {
                        if(is_int($params[$column]))
                            $type = PDO::PARAM_INT;
                        else if(is_null($params[$column]) || $params[$column] === null)
                            $type = PDO::PARAM_NULL;
                        else
                            $type = PDO::PARAM_STR;
                        
                        $stmt->bindValue(':'.$column, $params[$column], $type);
                    }
                }
                
                $stmt->execute();
                $this->resultset = $stmt;

                if($this->debug)
                   var_dump($params);
            }

            if($this->debug)
                echo '<strong style="color: #dd0000;">'.$sql.'</strong> :: '.implode(', ', $this->dbh->errorInfo()).'<br /><br />';

            return $this->resultset;
        }

        /**
         * Return last inserted ID.
         *
         * @return int
         */
        public static function insertId()
        {
            $db = Database::getInstance();

            return $db->dbh->lastInsertId();
        }
    }
?>