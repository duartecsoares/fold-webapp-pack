<?php
	$log->addFile( __FILE__ );
	

	//
    // ConnectTwitter class
    //
    class ConnectTwitter {

        public $username;
        public $apiURL = '';
        private $accessToken = '';
		
        function __construct( $username = null ) {
            global $log;

            if ( $username ) {
                $this->username = $username;
            }

        }

        public function getAvatar() {

			$url = 'https://twitter.com/'.$this->username.'/profile_image?size=original';

			return $url;

        }
	
    }
