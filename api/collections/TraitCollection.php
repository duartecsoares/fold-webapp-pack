<?php
    
    requireCollection('StaticCollection');
    requireModel('Traits/StaticTrait');

    //**************************************************************
    //
    // Static User
    //
    //**************************************************************
    class TraitCollection extends StaticCollection {
        
    	public $ModelClass = 'StaticTrait';

        //
        // database related
        //
        protected $_pull_table = "traits";

 		protected function pre_filter_visible( $visible = 1 ) {
             $this->where_filters[] = "visible = ".$visible;
        }

    }