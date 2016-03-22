<?php
	
  	//**************************************************************
    //
    // Notification Traits
    //
    //**************************************************************
    trait NotificationTraits {

        public $notification_type;
        public $from_id;
        public $to_id;
        public $related_idea_id;
        public $related_message_id;
        public $seen;
        public $created_at;

        //
        // database related
        //
        protected $_pull_table 	= "notifications";
        protected $_push_table  = "notifications";

        protected $_user;
        protected $_message;
        protected $_idea;

        protected $_insert_blacklist    = array("id", "_exists", "_user", "_idea", "_message");
        protected $_update_blacklist    = array("id", "_exists", "_user", "_idea", "_message");


        public function fromTo( $from, $to ) {
        	$this->from_id = $from;
        	$this->to_id = $to;

        	return true;
        }

        public function getSummary() {
            $array = array();

            $array['user'] = $this->user;

            return $array;
        }

        public function setRelated( $id ) {
            return $this->setRelatedIdea($id);
        }

        public function setRelatedMessage( $id ) {
            $this->related_message_id = $id;

            return $this->related_message_id;
        }

        public function setRelatedIdea( $id ) {
            $this->related_idea_id = $id;

            return $this->related_idea_id;
        }

        public function generateRelated() {
            global $log;

            $data       = $this;
            $array      = array();

            $user       = null;
            $idea       = null;
            $message    = null;

            foreach($data as $key => $value) {
                if ( !empty($value)  ) {
                    if ( strpos($key,'u__') !== false ) {
                        if ( !$user ) {
                            requireModel('Users/StaticUser');
                            $user = new StaticUser();
                        }

                        $finalKey = str_replace("u__", "", $key);
                        
                        if ( $finalKey == 'preferences') {
                            $user->setPreferences(  $value );
                        } else {
                           $user->$finalKey = $value; 
                        }
                        
                    } else if ( strpos($key,'i__') !== false ) {
                        if ( !$idea ) {
                            requireModel('Ideas/StaticIdea');
                            $idea = new StaticIdea();
                        }

                        if ( $key == 'i__id' ) {
                            $log->append('idea?', '[id] - '.$this->related_idea_id);
                        }

                        $log->append('idea?', $key.' - '.$value);

                        $finalKey = str_replace("i__", "", $key);
                        $idea->$finalKey = $value;

                    } else if ( strpos($key,'m__') !== false ) {
                        if ( !$message ) {
                            requireModel('Messages/StaticMessage');
                            $message = new StaticMessage();
                        }

                        $finalKey = str_replace("m__", "", $key);
                        $message->$finalKey = $value;

                        $log->append('message_is', $finalKey.':'.$value);
                    
                    }
                }
            }

            $array['id']                    = $this->id;
            $array['notification_type']     = $this->notification_type;
            $array['from_id']               = $this->from_id;
            $array['to_id']                 = $this->to_id;
            $array['related_idea_id']       = $this->related_idea_id;
            $array['related_message_id']    = $this->related_message_id;
            $array['created_at']            = $this->created_at;
            $array['seen']                  = $this->seen;

            $array['user']      = $user ? $user->getListSummary() : $user;
            $array['idea']      = $idea ? $idea->getListSummary() : $idea;
            $array['message']   = $message ? $message->getFull() : $message;

            // $this->_user    = $user->getSummary();
            // $this->_idea    = $idea->getSummary();
            // $this->_message = $message->get();

            return $array;
        }

        public function addToUser() {
            global $log;
            if ( strpos($key,'user__') !== false ) {
                $log->append('get_user_data', $key." : ".$val);
                $this->user_data[ str_replace("user__", "", $key) ] = $val;
            }
        }


        public function test() {
            global $log;
            $log->append('test','TEST!');
        }

        public function type( $type_str ) {

            //
            // 1 - Follow User
            // 2 - Contact User
            // 3 - Contact about Idea
            // 4 - Favorite Idea
            // 5 - App Update
            //

        	if( $type_str == 'follow' ) {
        		$this->notification_type = 1;
        	} else if( $type_str == 'contact' ) {
        		$this->notification_type = 2;
        	} else if( $type_str == 'contact-idea' ) {
                $this->notification_type = 3;
            } else if( $type_str == 'favorite-idea' ) {
                $this->notification_type = 4;
            } else {
                $this->notification_type = 0;
            }

        	return $this->notification_type;

        }

    }