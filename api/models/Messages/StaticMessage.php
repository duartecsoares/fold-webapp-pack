<?php
    
    requireModel('StaticModel');

    //**************************************************************
    //
    // Static User
    //
    //**************************************************************
    class StaticMessage extends StaticModel {
       	
       	public $conversation_id;
        public $from_id;
        public $message;
        public $created_at;
        public $idea_id;
        public $spam;
        public $hash;
        public $flags;

        //
        // database related
        //
        protected $_pull_table 	= "messages";
        protected $_push_table  = "messages";

        protected $_insert_blacklist    = array("id", "_exists");
        protected $_update_blacklist    = array("id", "_exists");

        public function getFull() {
            $array = array();

            requireModel('Flags/DynamicFlag');
            $flag = new DynamicFlag();
            
            if ( $this->hash ) {
                $hash = $flag->getEncodedHash('message', $this->id, $this->hash);
            } else {
                $hash = null;
            }

            $array['id']                = $this->id;
            $array['message']           = $this->message;
            $array['from_id']           = $this->from_id;
            $array['conversation_id']   = $this->id;
            $array['hash']              = $hash;
            $array['flags']             = $this->flags;
            $array['spam']              = $this->spam;
            $array['created_at']        = $this->created_at;
            $array['idea_id']           = $this->idea_id;

            return $array;
        }

    }   