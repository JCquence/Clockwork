<?php if(!defined('ACCESS')) exit('Access denied');
    /**
     * Message
     * @author Jelle van der Coelen
     * @package Clockwork/Module
     */
    class Message
    {
        /**
         * Key for saving messages to session.
         * @const string
         */
        const KEY = 'messagesAndErrors';

        /**
         * Constructor
         *
         * @param string $message  Message to show.
         * @param string $key      Key to save to.
         * @param string $redirect Optional redirect.
         *
         * @return void
         */
        public function __construct($message = null, $key = null, $redirect = null)
        {
            if($message)
                $this->addMessage($message, $key);

            if($redirect)
                redirect($redirect);
        }

        /**
         * Add a message to the stack.
         *
         * @param string $message Message to show.
         * @param string $key     Key to save to.
         *
         * @return void
         */
        public function addMessage($message, $key = null)
        {
            if(!isset($_SESSION[self::KEY]))
                $_SESSION[self::KEY] = array();

            if($key)
                $_SESSION[self::KEY][$key] = $message;
            else
                $_SESSION[self::KEY][] = $message;
        }

        /**
         * Reset message(s).
         *
         * @param string $key Omit to reset all messages.
         *  
         * @return void
         */
        public static function clear($key = null)
        {
            if(!$key)
                $_SESSION[self::KEY] = array();
            else
            {
                if(isset($_SESSION[self::KEY][$key]))
                    unset($_SESSION[self::KEY][$key]);
            }
        }

        /**
         * Return a specific message.
         *
         * @param string $key Key of the message to return.
         *  
         * @return string
         */
        public static function get($key)
        {
            if(isset($_SESSION[self::KEY][$key]))
            {
                $message = $_SESSION[self::KEY][$key];
                Message::clear($key);

                return $message;
            }

            return null;
        }

        /**
         * Return all messages and clear them.
         *  
         * @return array
         */
        public static function getAll()
        {
            $messages = (!empty($_SESSION[self::KEY]) ? $_SESSION[self::KEY] : []);
            Message::clear();

            return $messages;
        }
    }
?>