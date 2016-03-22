<?php
    
    requireCollection('StaticCollection');
    requireModel('Connections/StaticConnection');


    //**************************************************************
    //
    // Static User
    //
    //**************************************************************
    class ConnectionCollection extends StaticCollection {
        
    	public $ModelClass = 'StaticConnection';

        //
        // database related
        //
        protected $_pull_table = "connections";

 		protected function pre_filter_visible( $visible = 1 ) {
             $this->where_filters[] = "visible = ".$visible;
        }

    }