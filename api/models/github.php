<?php
    $log->addFile( __FILE__ );

    include_once dirname(__FILE__) . "/../functions/connectGithub.php";

    class GithubRepo {

    	public $id;
    	public $name;

    	public $full_name;
    	public $private;

    	public $fork;
    	public $html_url;

    	public $updated_at;
    	public $watchers_count;
    	public $stargazers_count;

        public $language;


        function __construct( $data = null ) {
        	global $log;

            if ( $data ) {
                $this->id 		    = $data->id;
                $this->name         = $data->name;
                $this->full_name 	= $data->full_name;
                $this->private 	    = $data->private;

                $this->fork                 = $data->fork;
                $this->html_url                  = $data->html_url;
                $this->updated_at           = $data->updated_at;
                $this->watchers_count       = $data->watchers_count;
                $this->stargazers_count     = $data->stargazers_count;
                $this->language 			= $data->language;
            }

        }

        public function getData() {
        	$array = array();

            $array['id']            = $this->id;
            $array['name']          = $this->name;

            $array['full_name']     = $this->full_name;
            $array['fork']          = $this->fork;

            $array['stargazers_count']  = $this->stargazers_count;

            $array['watchers_count']    = $this->watchers_count;
            $array['link']              = $this->html_url;

            return $array;
        }
    }

	class Github {
        public $username;
        public $connection = null;
        public $repos;
        public $last_pull;

        private $data;

        function __construct( $username = null ) {

            if ( $username ) {
            	$this->username = $username;
                $this->connection = new ConnectGithub($username);
            }
        }

        public function pullAvatar() {

            $avatar = $this->connection->getAvatar();
            $this->avatar = $avatar;

            return $avatar;

        }

        public function pull() {
            global $log;

            $repos = $this->connection->getUserRepos();

            if ( is_array($repos) && count($repos) > 0 ) {

            	$array = array();
            	$this->data = $repos;

        		for( $i = 0; $i < count($this->data); $i++ ) {

        			$shot = new GithubRepo( $this->data[$i] );
        			$array[] = $shot->getData();
        			$this->last_pull = time();

        		}

	        	if ( count($array) > 0 ) {
	        		$this->repos = $array;
	        	}

            }

            return $repos;
        }

        public function getStorableData() {

        	$array = array();

        	$array['username']     = $this->username;
        	$array['repos']        = $this->repos;
        	$array['last_pull']    = $this->last_pull;

        	return $array;

        }

    }