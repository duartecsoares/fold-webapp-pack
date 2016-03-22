<?php
    
    requireModel('DynamicModel');
    requireModel('Messages/DynamicMessage');

    //**************************************************************
    //
    // Static User
    //
    //**************************************************************
    class DynamicConversation extends DynamicModel {
       
        public $from_id;
        public $to_id;
        public $created_at;
        public $updated_at;
        public $idea_id;
        public $spam;

        //
        // database related
        //
        protected $_pull_table 	= "conversations";
        protected $_push_table  = "conversations";

        protected $_insert_blacklist    = array("id", "_exists");
        protected $_update_blacklist    = array("id", "_exists");

        protected function generate_query_fromtoexists() {
            $id  = $this->from_id;
            $pull_query = null;
            if ( !empty($id) ) {
                $pull_query = "SELECT * FROM ".$this->_push_table." WHERE from_id = ".$this->to_id." AND to_id = ".$this->from_id;
            } 
            // if ($this->idea_id) {
            //     $pull_query .= " AND idea_id = ".$this->idea_id;
            // }
            return $pull_query;
        }

        protected function generate_query_fromexists() {
            $id  = $this->from_id;
            $pull_query = null;
            if ( !empty($id) ) {
                $pull_query = "SELECT * FROM ".$this->_push_table." WHERE from_id = ".$this->from_id." AND to_id = ".$this->to_id;
            } 
            // if ($this->idea_id) {
            //     $pull_query .= " AND idea_id = ".$this->idea_id;
            // }
            return $pull_query;
        }

        public function conversationExists() {
            global $log;

            $exist = $this->pull('fromexists');

            $log->append('conversation', $exist);

            if ( !$exist ) {
                $exist = $this->pull('fromtoexists');
            }

            if ( $exist ) {
                return true;
            } else {
                return false;
            }

        }

        public function addMessage( $from_id, $text, $idea_id = null, $spam = null ) {
            
            $message = new DynamicMessage();
            $message->conversation_id = $this->id;
            $message->from_id   = $from_id;
            $message->message   = $text;
            $message->idea_id   = $idea_id;
            $message->spam      = $spam;

            $success = $message->push();

            return $message;
        } 

        public function getConversation() {
            $exists = $this->conversationExists();

            if ( $exists ) {
                return $this;
            } else {
                return $this->push();
            }
        }

    }   