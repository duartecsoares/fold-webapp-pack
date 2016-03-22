<?php

    requireModel('Notifications/Traits');
    requireModel('DynamicModel');

    //**************************************************************
    //
    // Static User
    //
    //**************************************************************
    class DynamicNotification extends DynamicModel {
       	
        use NotificationTraits;

        public function send() {
            return $this->push();
        }

        protected function onBeforeDelete() {
            if ( $this->from_id && $this->to_id && $this->notification_type ) {
                return true;
            } else {
                return false;
            }
        }

        protected function deleteWhereValues() {
            global $log;
            $array = array(
                $this->from_id,
                $this->to_id,
                $this->notification_type
                );
            $log->append('deleteWhereValues', $array);
            return $array;
        }

        protected function deleteWhere() {

            $str = " from_id = ? and to_id = ? and notification_type = ?";

            return $str;
        }

    }   