<?php
    
    requireCollection('StaticCollection');
    requireModel('Likes/LikeIdeaModel');

    //**************************************************************
    //
    // Static User
    //
    //**************************************************************
    class LikesIdeaCollection extends StaticCollection {
        
    	public $ModelClass = 'LikeIdeaModel';

        //
        // database related
        //
        protected $_pull_table = "likes";
        protected $order = "ORDER BY like_id";

        public function setIdea( $id ) {
            $this->setPreFilter('idea_id', $id );
          
        }
        protected function pre_filter_idea_id( $id ) {
            $this->where_filters[] = "idea_id = ".$id;
        }

        public function setFavedBy( $id ) {
            $this->setPreFilter('user_id', $id );
          
        }
        protected function pre_filter_user_id( $id ) {
            $this->where_filters[] = "user_id = ".$id;
        }

    }