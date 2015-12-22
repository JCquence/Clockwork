<?php if(!defined('ACCESS')) exit('Access denied');
    /**
     * Query
     * @author Jelle van der Coelen
     * @package Clockwork/Module/Data
     */
    class Query
    {
        /**
         * Holds al previousely used aliasses.
         * @var array
         */
        private $aliasses = array();

        /**
         * Holds which tables to select from.
         * @var array
         */
        private $from = array();

        /**
         * Holds where to group by.
         * @var array
         */
        private $group = array();

        /**
         * Holds which tables to join on.
         * @var array
         */
        private $join = array();

        /**
         * Holds the limit.
         * @var string
         */
        private $limit;

        /**
         * Params to bind for PDO.
         * @var array
         */
        private $params = array();

        /**
         * Holds what to order by.
         * @var string
         */
        private $order = array();
        
        /**
         * Holds what to select.
         * @var array
         */
        private $select = array('*');

        /**
         * Holds what to filter for.
         * @var array
         */
        private $where = array();

        /**
         * Holds generated SQL.
         * @var string
         */
        private $sql;

        /**
         * Create alias from table.
         *
         * @param string $table Table name to create alias from.
         *
         * @return string
         */
        public function alias($table)
        {
            if(strpos(str_replace(Config::getSetting('db_table_prefix'), '', $table), '_') !== false)
            {
                $table = explode('_', $table);
                $table = $table[1];
                $alias = $table{0};
            }
            else
                $alias = $table{0};

            $i = 2;
            while(in_array($alias, $this->aliasses))
                $alias = substr($table, 0, ($i++));

            $this->aliasses[] = $alias;

            return $alias;
        }

        /**
         * Constructor.
         *
         * @param string $quick Column to quickly search for.
         * @param string $where Value to quickly search for.
         *
         * @return void
         */
        public function __construct($quick = false, $where = 'id')
        {
            if($quick !== false && $where)
                $this->where($where.($quick === null ? " IS NULL" : " = ".$quick));
        }
        
        /**
         * Return a Query object to start chaining.
         *
         * @return object
         */
        public static function chain(){ return new Query(); }

        /**
         * Add table to select from.
         *  
         * @param string  $table  Table name to select from
         * @param string  $alias  Table alias.
         * @param boolean $prefix Prefix table with db_table_prefix from config.
         * @param boolean $reset  Clear all tables from $from.
         *
         * @return self
         */
        public function from($table, $alias = null, $prefix = true, $reset = false)
        {
            if($reset)
                $this->from = array();

            $this->from[$alias] = ($prefix ? Config::getSetting('db_table_prefix') : '').$table." ".$this->alias($table);
            
            return $this;
        }

        /**
         * Generate and return sql.
         *
         * @return string
         */
        public function generate()
        {
            $sql = '';

            //select
            $sql  = "SELECT ";
            $sql .= (count($this->select) > 0 ? implode(", ", $this->select) : '*');

            //from
            $sql .= " FROM ";
            $sql .= implode(", ", $this->from);

            //join
            $sql .= " ".implode(" ", $this->join)." ";

            //where
            if(!empty($this->where))
            {
                $sql .= " WHERE (";
                foreach($this->where as $key => $where)
                {
                    if(!isset($this->where[($key-1)]) || $this->where[($key-1)]['operator'] != 'OR')
                        $sql .= "( ";
                    
                    $sql .= $where['filter'];
                    
                    if($where['operator'] != 'OR')
                        $sql .= " )";

                    if($key != (count($this->where)-1))
                        $sql .= " ".$where['operator']." ";
                }

                $sql .= (!preg_match('/(\))$/i', $sql) ? " )" : "").")";
            }

            //group
            if(!empty($this->group))
            {
                $sql .= " GROUP BY ";
                $sql .= implode(", ", $this->group);
            }

            //order
            if(!empty($this->order))
            {
                $sql .= " ORDER BY ";
                $sql .= implode(", ", $this->order);
            }

            //limit
            if(!empty($this->limit))
                $sql .= " LIMIT ".$this->limit;

            $this->sql = $sql;

            return $sql;
        }

        /**
         * Add group by.
         *
         * @param string  $groupby Where to group by.
         * @param boolean $reset   Clear all group by's.
         *
         * @param self
         */
        public function group($groupby, $reset = false)
        {
            if($reset)
                $this->group = array();

            $this->group[] = $groupby;

            return $this;
        }

        /**
         * Add a join.
         *  
         * @param string  $table  Table name to join on
         * @param string  $on     Where to join on.
         * @param string  $type   Type of join
         * @param boolean $prefix Prefix table with db_table_prefix from config.
         * @param boolean $reset  Clear all joins.
         *
         * @return self
         */
        public function join($table, $on, $type = 'INNER', $prefix = true, $reset = false)
        {
            if($reset)
                $this->join = array();

            $this->join[] = $type." JOIN ".($prefix ? Config::getSetting('db_table_prefix') : '').$table." ".$this->alias($table)." on ".$on;

            return $this;
        }


        /**
         * Set limit.
         *
         * @param int $from Limit from.
         * @param int $to   Results.
         *
         * @return self
         */
        public function limit($from, $to = null)
        {
            $this->limit = $from;

            if($to)
                $this->limit .= ", ".$to;

            return $this;
        }

        /**
         * Add order.
         * 
         * @param string  $order Where to order by.
         * @param boolean $reset Clear all order by's.
         *
         * @return self
         */
        public function order($order, $reset = false)
        {
            if($reset)
                $this->order = array();

            $this->order[] = $order;

            return $this;
        }

        /**
         * Run query and return result(s).
         *
         * @param boolean $single Return a single record.
         *  
         * @return array
         */
        public function run($single = false)
        {
            $this->generate();

            $db = Database::getInstance();
            return $db->fetch($db->query($this->sql, $this->params), ($single == 1 ? false : true));
        }

        /**
         * Add to select.
         * 
         * @param string|array $select What to select.
         * @param boolean      $reset  Clear all select.
         *
         * @return self
         */
        public function select($select, $reset = false)
        {
            if($reset)
                $this->select = array();

            if(!is_array($select))
                $this->select[] = $select;
            else
                foreach($select as $s)
                    $this->select[] = $s;

            return $this;
        }

        /**
         * Add to where filter.
         * 
         * @param string  $where    Where statement.
         * @param string  $operator AND|OR.
         * @param boolean $reset    Clear all where clauses.
         *
         * @return self
         */
        public function where($where, $operator = 'AND', $reset = false)
        {
            if($reset)
                $this->where = array();

            if(!preg_match('/^(\*)/', $where))
            {
                $where = explode((strpos($where, 'AND') !== false ? 'AND' : 'OR'), $where);

                foreach($where as $w)
                {
                    $data    = explode(' ', trim($w));
                    $column  = $data[0]; array_shift($data);
                    $operand = $data[0]; array_shift($data);
                    $value   = preg_replace('/(^(\'|\")|(\'|\")$)/', '', trim(implode(' ', $data)));

                    $scolumn = str_replace(array('.', '(', ')', '`'), '', $column);
                    
                    $i = 1;
                    while(isset($this->params[$scolumn]))
                        $scolumn = str_replace('.', '', $column).($i++);
                    
                    $this->params[$scolumn] = ($value == 'NULL' ? null : $value);
                    $this->where[] = array('operator' => $operator, 'filter' => $column.' '.$operand.' :'.$scolumn);
                }
            }
            else
                $this->where[] = array('operator' => $operator, 'filter' => substr($where, 1));

            return $this;
        }
    }
?>