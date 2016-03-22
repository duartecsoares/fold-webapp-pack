<?php
    
    requireModel('StaticModel');

    //**************************************************************
    //
    // Static User
    //
    //**************************************************************
    class StaticConnection extends StaticModel {

    	public $id;
    	public $name;
    	public $description;
    	public $url;

        //
        // database related
        //
        protected $_pull_table 	= "connections";
        protected $_push_table  = "connections";

    }