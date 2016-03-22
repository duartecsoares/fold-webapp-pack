<?php
    
    requireModel('StaticModel');

    //**************************************************************
    //
    // Static User
    //
    //**************************************************************
    class StaticTrait extends StaticModel {
       
        public $id;
        public $name;
        public $parent;
        public $string_id;

        //
        // database related
        //
        protected $_pull_table 	= "traits";
        protected $_push_table  = "traits";

    }