<?php
    
    requireCollection('StaticCollection');
    requireModel('Types/StaticType');

    //**************************************************************
    //
    // Static User
    //
    //**************************************************************
    class TypesCollection extends StaticCollection {
        
    	public $ModelClass = 'StaticType';

        //
        // database related
        //
        protected $_pull_table = "idea_types";

 		protected function pre_filter_visible( $visible = 1 ) {
             $this->where_filters[] = "visible = ".$visible;
        }

    }