<?php
    
    requireCollection('StaticCollection');
    requireModel('Likes/LikeUserModel');

    //**************************************************************
    //
    // Static User
    //
    //**************************************************************
    class LikesUserCollection extends StaticCollection {
        
    	public $ModelClass = 'LikeUserModel';

        //
        // database related
        //
        protected $_pull_table  = "follows_users";
        protected $order        = "ORDER BY follow_id";

        public function setFollowing( $id ) {
            $this->setPreFilter('following', $id );
          
        }
        public function setFollower( $id ) {
            $this->setPreFilter('followedby', $id );
          
        }

        public function pullOnEmpty() {
            
        }

        protected function pre_filter_followedby( $id ) {
            $this->where_filters[] = "follower_id = ".$id;
        }
        protected function pre_filter_following( $id ) {
            $this->where_filters[] = "following_id = ".$id;
        }
    }