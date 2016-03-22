<?php
    
    requireCollection('StaticCollection');
    requireModel('Messages/StaticMessage');

    //**************************************************************
    //
    // Static User
    //
    //**************************************************************
    class MessagesCollection extends StaticCollection {
        
    	public $ModelClass = 'StaticMessage';

        //
        // database related
        //
        protected $_pull_table  = "messages";
        protected $order        = "ORDER BY created_at";

        public function setConversation( $id ) {
            $this->setPreFilter('conversation', $id );
          
        }
        public function setFrom( $id ) {
            $this->setPreFilter('from', $id );
        }

        public function setDate( $date ) {
            $this->setPreFilter('date', $date );
        }

        public function pullOnEmpty() {
            
        }

        protected function pre_filter_conversation( $id ) {
            $this->where_filters[] = "conversation_id = ".$id;
        }

        protected function pre_filter_from( $id ) {
            $this->where_filters[] = "from_id = ".$id;
        }

        protected function pre_filter_date( $date ) {
            $this->where_filters[] = "created_at >= '".$date."'";
        }

    }