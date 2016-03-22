<?php
    
    requireModel('StaticModel');

    //**************************************************************
    //
    // Static User
    //
    //**************************************************************
    class StaticConversation extends StaticModel {
       
        public $from_id;
        public $to_id;
        public $created_at;
        public $updated_at;
        public $idea_id;


        //
        // database related
        //
        protected $_pull_table 	= "conversations";
        protected $_push_table  = "conversations";

    }