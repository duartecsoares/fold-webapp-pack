<?php
    requireModel('Likes/LikeModel');

    //**************************************************************
    //
    // Like Idea
    //
    //**************************************************************
    class LikeUserModel extends LikeModel {
        public $follower_id;
        public $following_id;

        protected $_db_fields   = 'id';

        //
        // database related
        //
        protected $_pull_table 	= "follows_users";
        protected $_push_table  = "follows_users";

        public function setFollowing( $id ) {
        	$this->following_id = $id;
        }

        public function setFollower( $id ) {
        	$this->follower_id = $id;
        }

        protected function deleteWhereValues() {
            return array($this->follower_id,$this->following_id);
        }

        protected function deleteWhere() {
            return " follower_id = ? AND following_id = ?";
        }

        protected function generate_query_exists() {
            return "SELECT * FROM ".$this->_pull_table." WHERE follower_id = ".$this->follower_id." AND following_id = ".$this->following_id;
        }

    }