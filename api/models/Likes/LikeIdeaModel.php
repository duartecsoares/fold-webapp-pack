<?php
    requireModel('Likes/LikeModel');

    //**************************************************************
    //
    // Like Idea
    //
    //**************************************************************
    class LikeIdeaModel extends LikeModel {
        public $idea_id;
        public $user_id;

        protected $_db_fields   = 'id';

        //
        // database relateds
        //
        protected $_pull_table 	= "likes";
        protected $_push_table  = "likes";

        public function setIdea( $id ) {
        	$this->idea_id = $id;
        }

        public function pullOnEmpty() {
            
        }

        protected function deleteWhereValues() {
            return array($this->idea_id,$this->user_id);
        }

        protected function deleteWhere() {
            return " idea_id = ? AND user_id = ?";
        }

    }