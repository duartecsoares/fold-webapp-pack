<?php
    
    requireModel('DynamicModel');

    //**************************************************************
    //
    // Static User
    //
    //**************************************************************
    class DynamicMessage extends DynamicModel {
       	
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

        public function onAfterInsert() {
            requireModel('Flags/DynamicFlag');
            $flag = new DynamicFlag();
            $this->hash = $flag->generateHash();
            $this->update('hash');
            return true;
        }

    }   