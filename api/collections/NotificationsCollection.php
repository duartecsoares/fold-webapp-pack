<?php
    
    requireCollection('StaticCollection');
    requireModel('Notifications/StaticNotification');

    //**************************************************************
    //
    // Static User
    //
    //**************************************************************
    class NotificationsCollection extends StaticCollection {
        
    	public $ModelClass = 'StaticNotification';

        //
        // database related
        //
        protected $_pull_table  = "notifications";
        protected $order        = "ORDER BY id DESC";

        protected function pull_default( $obj ) {
            global $log;

            return $obj->generateRelated();

        }

        public function onBeforeGet( $models ) {
            global $log;

            $log->append('onBeforeGet', count($models));

            foreach($models as $key => $model) {
                if ( $model['message'] && $model['message']['spam'] ) {
                    $log->append('onBeforeGet', 'message spam : '. $model['message']['id']);
                    unset($models[$key]);
                }
            }

            $modelsIndexed = array_values($models);
            
            $log->append('onBeforeGet', count($modelsIndexed));

            return $modelsIndexed;
        }


        public function gatherRelatedData() {
            
            // $this->setFields("
            //     ideas.id, ideas.popularity_featured, ideas.popularity_alltime, ideas.created_at, ideas.user_id, ideas.id_cover, ideas.gallery, ideas.icon, ideas.cover, ideas.name, ideas.like_count, ideas.idea_type, ideas.description, ideas.looking_for, ideas.is_looking_for, ideas.traits, ideas.privacy,
            //     users.username as user__username, users.fullname as user__fullname, users.avatar as user__avatar, users.id as user__id, users.preferences as user__preferences,
            //     SUM(IF(likes.user_id = ".$user_id.", true, false)) as favorited");
            $this->setPullTable("notifications n");
            
            $this->setFields("  n.id, n.seen, n.created_at, n.notification_type, n.related_idea_id, n.related_message_id, n.from_id, n.to_id,
                                u.id as u__id, u.username as u__username, u.fullname as u__fullname, u.avatar as u__avatar, u.preferences as u__preferences, u.connections_data as u__connections_data, u.followed_count as u__followed_count, u.idea_count as u__idea_count,
                                i.id as i__id, i.name as i__name, i.cover as i__cover, i.id_cover as i__id_cover, i.gallery as i__gallery,
                                m.id as m__id,
                                m.message as m__message, m.hash as m__hash, m.spam as m__spam, m.flags as m__flags");

            $this->join  = "LEFT JOIN users u ON n.from_id = u.id 
                            LEFT JOIN ideas i ON n.related_idea_id = i.id 
                            LEFT JOIN messages m ON n.related_message_id = m.id";

            $this->order = "ORDER BY n.id DESC";
        }

        public function setTo( $id ) {
            $this->setPreFilter('to', $id );
        }

        public function setFrom( $id ) {
            $this->setPreFilter('from', $id );
        }

        public function getExists() {
            $this->setLimit(1);
            $this->setFields('id');
            return $this->pull();
        }

        public function pullOnEmpty() {
            
        }

        public function markAsSeen( $count = null ) {
            global $log, $pdo;

            $to_id = $_SESSION['user']->id;
            $data = array();
            $data[] = $to_id;

            if ( $count ) {
                $data[] = $count;
                $sql = "UPDATE notifications SET seen = 1
                        WHERE WHERE to_id = ? AND id IN( 
                            SELECT id 
                            FROM (SELECT id FROM notifications ORDER BY id DESC LIMIT 0, ?)
                        )";
            } else {
                $sql = "UPDATE notifications SET seen = 1 WHERE to_id = ?";
            }

            $sql_prepare = $pdo->prepare($sql);
            $sql_execute = $sql_prepare->execute($data);

            return $sql_execute;

        }

        public function setUnseen() {
            $this->setPreFilter('unseen', true );
        }

        protected function pre_filter_to( $id ) {
            $this->where_filters[] = "to_id = ".$id;
        }

        protected function pre_filter_from( $id ) {
            $this->where_filters[] = "from_id = ".$id;
        }

        protected function pre_filter_unseen( $unseen ) {

            if ( $unseen == true ) {
                $this->where_filters[] = "seen = 0";
            }
            
        }

    }