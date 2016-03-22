<?php
    
    requireModel('StaticModel');

    //**************************************************************
    //
    // Static User
    //
    //**************************************************************
    class StaticType extends StaticModel {
       
        public $id;
        public $name;

        //
        // database related
        //
        protected $_pull_table 	= "idea_types";
        protected $_push_table  = "idea_types";

        public function get( $onEach = '' ) {
            return array("id"=>$this->id, "name"=>$this->name);
        }

    }