<?php
    
    requireModel('DynamicModel');
    requireModel('Flags/Traits');

    //**************************************************************
    //
    // Static User
    //
    //**************************************************************
    class DynamicFlag extends DynamicModel {
        use FlagTraits; 

        public function flag( $hash ) {
            global $log;
            $data = $this->getDataFromEncodedHash($hash );
            $method_name = null;

            if ( is_array($data) && !empty($data['type']) ) {
                $method_name = 'flag_'.$data['type'];
            }


            if ( method_exists($this, $method_name) ) {
                return $this->$method_name( $data );
            } else {
                return false;
            }

        }

        public function flag_message( $data ) {
            global $log;

            $log->append('flag_message', '---- init ----');


            if ( !empty($data['id']) && !empty($data['hash']) ) {
                requireModel('Messages/DynamicMessage');

                $message = new DynamicMessage();
                $message->id = $data['id'];

                // $log->append('flag_message', $message);

                if ( $message->exists() && $message->hash == $data['hash']) {
                    // $log->append('flag_message', true);
                    $message->flags++;

                    if ( $message->flags > 0 ) {
                        $message->spam = 1;
                        $message->update('flags,spam');
                    } else {
                        $message->update('flags');
                    }



                    // $log->append('flag_message', $message);

                } else {
                    $log->append('flag_message', false);
                }

                $success = true;
            } else {
                $success = false;
            }

            return $success;
        }
    }   