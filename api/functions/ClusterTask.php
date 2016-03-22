<?php
	
	requireFunction('curl');

	class ClusterTask {
		
		protected $auth;
		protected $task;
		protected $data = array();
		protected $method;
		protected $baseURL = "";

        function __construct( $username = null ) {
            global $__serverEvironment__, $log, $__local__;

            if ( $__serverEvironment__ == 'prod' ) {
                $this->baseURL = "http://cluster.foldservers.com/api/";
            } else if ($__serverEvironment__ == 'dev' || $__serverEvironment__ == 'beta') {
            	$this->baseURL = "http://cluster-dev.foldservers.com/api/";
            } else if($__serverEvironment__ == 'local') {
            	$this->baseURL = "http://cluster.dev/api/";
            }

        }

        public function getTaskURL() {
        	return $this->baseURL.$this->task;
        }


        public function getMethod() {
        	return $this->method;
        }

        public function getData() {
        	return $this->data;
        }


        public function set( $task, $data = [], $method = 'GET' ) {
        	$this->task 	= $task;
        	$this->data 	= $data;
        	$this->method 	= $method;
        }

		public function run() {
			global $log, $__cdnFolder__;

			$task = $this->getTaskURL();
			$data = $this->getData();

			$data['host'] 		= $_SERVER['HTTP_HOST'];
			$data['protocol'] 	= stripos($_SERVER['SERVER_PROTOCOL'],'https') === true ? 'https://' : 'http://';
			$data['ip'] 		= $_SERVER['SERVER_ADDR'];
			$data['env_folder'] = $__cdnFolder__;

			$log->append('ClusterTask', '-------- '.$task.' -------');
			$log->append('ClusterTask', $data);
			$log->append('ClusterTask', $this->getMethod());

			if ( $this->getMethod() == 'POST' ) {
				$result = curl_post( $task, true, $data );
			} else {
				$result = curl( $task, true );
			}
			
			if ( $result['status'] == 200 ) {
				$log->append('ClusterTask', '-- SUCCESS -- ');
				$log->append('ClusterTask', json_decode($result['response']));
			} else {
				$log->append('ClusterTask', '-- failed -- ');
				$log->append('ClusterTask', json_decode($result['response']));
			}


			return $result;
		}

	}