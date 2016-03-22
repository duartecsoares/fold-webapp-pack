<?php
    
    requireModel('DynamicModel');

    //**************************************************************
    //
    // Like Model
    //
    //**************************************************************
    class LikeModel extends DynamicModel {
        
        protected $_db_fields   = 'id';

        public function setUser( $id ) {
        	$this->user_id = $id;
        }

    }