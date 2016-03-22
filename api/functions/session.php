<?php
    class Session {
        public $data = array();

        public function set() {

        }

        public function get() {   

        }

        public function start() {   

        	// start a session
		    session_start();

		    if ( $_COOKIE['user'] && $_COOKIE['hash'] ) {
				$log->add('cookie', $_COOKIE);	    	
		    }

        }

    }