<?php
	$log->addFile( __FILE__ );

	   //
    // Idea class
    //
    class ConnectDribbble {

        public $username;
        public $apiURL = 'https://api.dribbble.com/v1/';

        private $accessToken = '69ed3e739a9efdab871e9cb235dd5d8e9de68cd51ca528a9db2f918052539385';
		
        function __construct( $username = null ) {
            global $log;

            if ( $username ) {
                $this->username = $username;
            }

        }

        public function getAvatar() {
        	$data = $this->getData();
			$url = $data->avatar_url;
			return $url;
        }

        public function getData() {

			$url = $this->apiURL.'users/'.$this->username.'?access_token='.$this->accessToken;

			$json = file_get_contents( $url );
			$obj = json_decode($json);
			
			return $obj;

        }

		public function getUserShots() {

			$url = $this->apiURL.'users/'.$this->username.'/shots?access_token='.$this->accessToken;

			$json = file_get_contents( $url );
			$obj = json_decode($json);
			
			return $obj;

		}

    }