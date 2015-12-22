<?php
    /**
     * User
     *
     */  
    class User extends Model
    {
        /**
         * Return full name
         *
         */
        public function name()
        {
            return $this->get('firstName').' '.$this->get('lastName');
        }
    }
?>