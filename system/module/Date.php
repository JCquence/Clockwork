<?php if(!defined('ACCESS')) exit('Access denied');
    /**
     * Date
     * @author Jelle van der Coelen
     * @package Clockwork/Module
     */
    class Date extends DateTime
    {
        /**
         * Database format.
         * @const string
         */
        const DATABASE = '%Y-%m-%d %H:%M:%S';

        /**
         * Date only format.
         * @const string
         */
        const DATE = '%Y-%m-%d';

        /**
         * Default format.
         * @const string
         */
        const DEFAULT_DATE = '%d %B %Y';

        /**
         * Format with time.
         * @const string
         */
        const DEFAULT_TIME = '%d %B %Y %H:%M';

        /**
         * Time only format.
         * @const string
         */
        const TIME = '%H:%M:%S';

        /**
         * Constructor.
         *
         * @param string $date Date string to format.
         *
         * @return void
         */
        public function __construct($date = null)
        {
            parent::__construct($date);
        }

        /**
         * Format date.
         *
         * @param string $format
         *
         * @return string
         */
        public function format($format = self::DEFAULT_DATE){ return strftime($format, $this->getTimestamp()); }
    }
?>