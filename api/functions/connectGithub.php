<?php
	$log->addFile( __FILE__ );
	
	//
    // Idea class
    //
    class ConnectGithub {

        public $username;
        public $apiURL = 'https://api.github.com/';

        private $accessToken = '69ed3e739a9efdab871e9cb235dd5d8e9de68cd51ca528a9db2f918052539385';
		
        function __construct( $username = null ) {
            global $log;

            if ( $username ) {
                $this->username = $username;
            }

        }

        public function getUserData() {

			$url = $this->apiURL.'users/'.$this->username;

			$options  = array('http' => array('user_agent'=> $_SERVER['HTTP_USER_AGENT']));
			$context  = stream_context_create($options);
			$json = file_get_contents( $url, false, $context );
			$obj = json_decode($json);

			return $obj;

        }

        public function getAvatar() {
        	$data = $this->getUserData();
			$url = $data->avatar_url;

			return $url;

        }

		public function getUserRepos() {

			$url = $this->apiURL.'users/'.$this->username.'/repos';

			$options  = array('http' => array('user_agent'=> $_SERVER['HTTP_USER_AGENT']));
			$context  = stream_context_create($options);
			$json = file_get_contents( $url, false, $context );
			$obj = json_decode($json);

			return $obj;

		}

    }