<?php
    requireFunction('curl');
    requireModel('DynamicModel');

    //**************************************************************
    //
    // Connection Traits
    //
    //**************************************************************
    trait ConnectionTraits {
    	public $last_pull; 

    	//
    	// api data for connecting and pulling data
    	//
        protected $idfieldname = 'username';
    	protected $api_url = '';
    	protected $storable = array('data', 'last_pull', 'username');
    	protected $response_type = 'json';
    	protected $success_codes = array(200, 201, 202, 203, 204);
    }


    //**************************************************************
    //
    // Connection Class
    //
    //**************************************************************
    class Connection {
    	use ConnectionTraits;

    	protected $status = null;

        public function disconnect() {
            $storable = $this->storable;
            foreach ( $storable as $field ) {
                $this->$field = null;
            }
        }

        public function reset() {
            $this->username = null;
        }

      	//
		//
		// set data
		//
		//
    	public function setId( $id ) {
    		$fieldname = $this->idfieldname;
    		$this->$fieldname = $id;
    	}

    	public function setIdFieldName( $name ) {
    		$this->idfieldname = $name;
    	}

        public function getId() {
            $idValue = $this->idfieldname;
            return $this->$idValue;
        }

        public function isEmpty() {
            $id = $this->getId();
            if ( empty($id) || $id == null ) {
                return true;
            } else {
                return false;
            }
        }

    	public function set( $data ) {
            global $log;

    		if ( is_string($data) ) {
    			$data = json_decode($data);
    		}

            if ( is_array($data) || is_object($data)  ) {
                foreach($data as $key => $value) {
                    $this->$key = $value;
                }
            }

    	}


      	//
		//
		// Dealing with the status code of an connection api request and the response
		//
		//
    	public function request( $url ) {
    		global $log;

    		$result = curl( $url, true );

    		$log->append('request_connection', $result);
    		$log->append('request_connection', is_array($result));

    		$this->status = $result['status'];

    		if ( in_array($result['status'], $this->success_codes) ) {
    			$result = $result['response'];
    		} else {
    			$result = null;
    		}

    		if ( $this->response_type == 'json' && $result ) {
    			$result = json_decode($result);
    		}

    		// $log->append('request', is_array($result));

    		return $result;
    	}

    	public function pull_default_error( $result ) {
    		global $log;
    		$log->error('warning', array('id'=>0, 'description'=>'oh noes.'));
    		return $result;
    	}

    	public function pull_default_success( $result ) {
    		$this->set( $result );
    		return $result;
    	}

    	public function pull_default_url() {
    		return $this->api_url;
    	}

    	public function pull_default( $url, $options ) {
    		global $log;
    		return $this->request($url );
    	}

    	//
		//
		// Pull data from an API
		//
		//
    	public function pull( $what = 'default', $options = null) {
    		global $log;

    		$pullMethod = 'pull_'.$what;

    		$errorMethod 	= $pullMethod.'_error';
    		$successMethod 	= $pullMethod.'_success';
    		$urlMethod 		= $pullMethod.'_url';

    		$result = null;
    		$url = null;

    		//
    		// generate url
    		//
			if ( method_exists($this, $urlMethod ) ) {
				$url = $this->$urlMethod( $options );
    		} else {
    			$url = $this->pull_default_url( $options );
    		}

    		//
    		// run method
    		//
    		if ( method_exists($this, $pullMethod ) ) {
    			$result = $this->$pullMethod($url, $options );
    		} else {
    			$result = $this->pull_default( $url, $options );
    		}

    		if ( $result ) {

	    		//
	    		// success method
	    		//
	    		if ( method_exists($this, $successMethod ) ) {
	    			$result = $this->$successMethod( $result );
	    		} else {
	    			$result = $this->pull_default_success($result);
	    		}

    		} else {

	    		//
	    		// error method
	    		//
	    		if ( method_exists($this, $errorMethod ) ) {
	    			$result = $this->$errorMethod( $result );
	    		} else {
	    			$result = $this->pull_default_error($result);
	    		}


    		}

            $log->append('connection_pull', '--------');
            $log->append('connection_pull', $what);
            $log->append('connection_pull', $result);

    		if ( $result ) {
    			$this->last_pull = time();
    		}

    		return $result;

    	}


      	//
		//
		// get current data
		//
		//
    	public function get( $compact = true ) {
    		global $log;

			$data = new stdClass();

    		foreach ($this->storable as $key ) {

                // $log->append('connection_get', $key);

    			if ( !empty($this->$key) || $this->$key == null ) {
                    $getterMethod = 'get_'.$key;



                    if ( method_exists($this, $getterMethod ) ) {
                        // $log->append('connection_get', $getterMethod.'()');

                        $data->$key = $this->$getterMethod( $this->$key );
                    } else {
                        $data->$key = $this->$key;
                    }    				
    			}
			}

    		return get_object_vars($data);
    	}

      	//
		//
		// get storable data (stringify) for the database
		//
		//
    	public function getStorableData() {
    		return json_encode($this->get());
    	}

    }


    //**************************************************************
    //
    // Dribbble Connection
    //
    //**************************************************************
    class DribbbleConnection extends Connection {

    	public $name 		= 'dribbble';

    	public $username 	= null;
    	public $images 		= null;
    	public $api_url 	= 'https://api.dribbble.com/v1/';

    	public $last_pull;
        public $calculated_popularity       = 0;
        public $recent_likes_received_count = 0;
        public $recent_views_received_count = 0;

        public $avatar_url                  = '';
        public $comments_received_count     = 0;
        public $followers_count             = 0;
        public $followings_count            = 0;
        public $likes_received_count        = 0;


        protected $idfieldname = 'username';
    	protected $access_token = '69ed3e739a9efdab871e9cb235dd5d8e9de68cd51ca528a9db2f918052539385';
    	protected $storable = array(
                'images',
                'last_pull',
                'username',
                'avatar_url',

                'comments_received_count',
                'followers_count',
                'followings_count',
                'likes_received_count',

                'recent_likes_received_count',
                'recent_views_received_count',

                'calculated_popularity'
            );

        public $COUNT_SHOTS = 6;

        public function pullAll() {
            $resultInfo = $this->pull('info');
            $resultShots = $this->pull('shots');

            return array($resultInfo,$resultShots);
        }

        public function get_images( $images ) {
            global $log;
            $result = array();
            if ( is_array($images) && count($images) > 0 ) {
                $result  = $images;
            }
            // $log->append('get_images', $images);
            // $log->append('get_images', $result);
            return $result;
        }

    	public function pull_info_url() {
    		return $this->api_url.'users/'.$this->username.'?access_token='.$this->access_token;
    	}

    	public function pull_shots_url() {
    		return $this->api_url.'users/'.$this->username.'/shots?access_token='.$this->access_token;
    	}

		public function pull_info_success( $result ) {
			global $log;

            $data = array(
                    'avatar_url'                => $result->avatar_url,
                    'comments_received_count'   => $result->comments_received_count,
                    'followers_count'           => $result->followers_count,
                    'followings_count'          => $result->followings_count,
                    'likes_received_count'      => $result->likes_received_count
                );

			$this->set( $data );
            $this->calculatePopularity();

			return $data;
		}

        protected function countRecentLikesViews( $shots ) {
            
            global $log;

            $array = array('views'=>0, 'likes'=>0);

            if ( is_array($shots) ) {
                foreach ( $shots as $shot ) {

                    if ( !empty($shot->views_count) ) {
                        $array['views'] += $shot->views_count;
                    }
                    if ( !empty($shot->likes_count) ) {
                        $array['likes'] += $shot->likes_count;
                    }

                }
            }

            return $array;
        }

        public function calculatePopularity() {
            global $log;

            $total = $this->followers_count * 10 + $this->recent_likes_received_count*5 + $this->recent_views_received_count;
            $log->append('popularity_connections', '-- '.$total);

            if ( $total > 0 ) {
                $this->calculated_popularity = round( pow(2,log( $total )) / log( 1.3 ), 0  );
            } else {
                $this->calculated_popularity = 0;
            }

            return $this->calculated_popularity;
        }

        private function filterShots( $result ) {
            global $log;

            $shots = array_slice($result, 0, $this->COUNT_SHOTS);
            $allowed = array(
                'id',
                'title',
                'width',
                'height',
                'image',
                'updated',
                'html_url',
                'views_count',
                'likes_count',
                'comments_count'
                );
            $final = array();

            foreach ( $shots as $shot ) {
                $shot = (array) $shot;
                if ( $shot['images'] ) {
                    $image = '';
                    $images = $shot['images'];

                    if ( $images->hdpi ) {
                        $image = $images->hdpi;
                    } else if ( $images->normal ) {
                        $image = $images->normal;
                    } else if ( $images->teaser ) {
                        $image = $images->teaser;
                    }
                    $shot['image'] = $image;
                }
                $filteredShot   = array_intersect_key($shot, array_flip($allowed));
                $final[]        = $filteredShot;
            }

            return $final;
        }

		public function pull_shots_success( $result ) {
			global $log;

            $shots = $this->filterShots( $result );

            $counts = $this->countRecentLikesViews( $shots  );
            $recent_likes_received_count = $counts['likes'];
            $recent_views_received_count = $counts['views'];

            $data = array(
                'images' => $shots,
                'recent_likes_received_count' => $recent_likes_received_count,
                'recent_views_received_count' => $recent_views_received_count
                );

			$this->set( $data );
            $this->calculatePopularity();

			return $result;
		}

    }


    //**************************************************************
    //
    // Giithub Connection
    //
    //**************************************************************
    class GithubConnection extends Connection {
    		
    	public $name 		= 'github';

    	public $username 	= null;
    	public $repos 		= null;
    	public $api_url 	= 'https://api.github.com/';
    	public $last_pull   = 0;

        public $avatar_url  = '';

        public $public_repos = 0;
        public $public_gists = 0;
        public $followers = 0;
        public $following = 0;

        public $total_stargazers_count  = 0;
        public $total_watchers_count    = 0;
        public $total_forks_count       = 0;

        public $calculated_popularity   = 0;

        public $COUNT_REPOS = 6;

        protected $idfieldname = 'username';
    	protected $storable = array(
            'repos',
            'last_pull',
            'username',
            'avatar_url',

            'followers',
            'following',
            'public_gists',
            'public_repos',

            'total_stargazers_count',
            'total_watchers_count',
            'total_forks_count',

            'calculated_popularity'
        );

        public function pullAll() {
            $resultInfo = $this->pull('info');
            $resultRepos = $this->pull('repos');
            return array($resultInfo,$resultRepos);
        }

        public function get_repos( $repos ) {
            global $log;
            $result = array();
            if ( is_array($repos) && count($repos) > 0 ) {
                $result  = $repos;
            }
            return $result;
        }

    	public function pull_info_url() {
    		return $this->api_url.'users/'.$this->username;
    	}

    	public function pull_repos_url() {
    		return $this->api_url.'users/'.$this->username.'/repos';
    	}

        public function calculatePopularity() {
            global $log;

            $total = $this->total_stargazers_count*10 + $this->total_watchers_count*7 + $this->total_forks_count + $this->public_repos + $this->public_gists + $this->followers*5;
            $log->append('popularity_connections', '-- '.$total);
            if ( $total > 0 ) {
                $this->calculated_popularity = round( pow(2, log( $total )) / log( 1.3 ), 0 );
            } else {
                $this->calculated_popularity = 0;
            }

            return $this->calculated_popularity;
        }

		public function pull_info_success( $result ) {
			global $log;

            $data = array(
                    'avatar_url'                => $result->avatar_url,
                    'public_repos'              => $result->public_repos,
                    'public_gists'              => $result->public_gists,
                    'followers'                 => $result->followers,
                    'following'                 => $result->following
                );

            $this->set( $data );
            $this->calculatePopularity();
			return $data;
		}
       
        private static function compareByStars($a, $b) {
            return $b->stargazers_count - $a->stargazers_count;
        }

        private function filterRepos( $repos ) {
            global $log;

            usort($repos, array($this,'compareByStars'));

            $allowed = array(
                'id',
                'name',
                'full_name',
                'html_url',
                'fork',
                'stargazers_count',
                'watchers_count',
                );
            $final = array();

            foreach ( $repos as $repo ) {
                $repo = (array) $repo;
                $final[] = array_intersect_key($repo, array_flip($allowed));
            }

            return $final;
        }


		public function pull_repos_success( $result ) {
			global $log;

            $repos = $this->filterRepos($result);
            $repos = array_slice($repos, 0, $this->COUNT_REPOS);

            $counts = $this->countTotalStats( $repos  );
            $total_stargazers_count     = $counts['total_stargazers_count'];
            $total_watchers_count       = $counts['total_watchers_count'];
            $total_forks_count          = $counts['total_forks_count'];

            $data = array(
                'repos'                     => $repos,
                'total_stargazers_count'    => $total_stargazers_count,
                'total_watchers_count'      => $total_watchers_count,
                'total_forks_count'         => $total_forks_count,
                );

            $this->set( $data );
            $this->calculatePopularity();

            return $result;

		}

        protected function countTotalStats( $repos ) {
            
            global $log;

            $array = array('total_stargazers_count'=>0, 'total_watchers_count'=>0, 'total_forks_count'=>0);

            if ( is_array($repos) ) {
                foreach ( $repos as $repo ) {

                    if ( !empty($repo->stargazers_count) ) {
                        $array['total_stargazers_count'] += $repo->stargazers_count;
                    }
                    if ( !empty($repo->watchers_count) ) {
                        $array['total_watchers_count'] += $repo->watchers_count;
                    }
                    if ( !empty($repo->forks_count) ) {
                        $array['total_forks_count'] += $repo->forks_count;
                    }
                }
            }

            return $array;
        }


    }

    //**************************************************************
    //
    // Dribbble Connection
    //
    //**************************************************************
    class TwitterConnection extends Connection {
    	
    	public $name 		= 'twitter';

    	public $username 	= null;
    	public $avatar 		= null;
    	public $last_pull;
    	public $api_url = 'https://api.dribbble.com/v1/';
        public $idfieldname = 'username';

    	protected $storable = array('images', 'last_pull', 'username', 'avatar_url');

    	public function pull_avatar_url() {
    		return $this->api_url.'users/'.$this->username.'?access_token='.$this->access_token;
    	}

    	public function pull_shots_url() {
    		return $this->api_url.'users/'.$this->username.'/shots?access_token='.$this->access_token;
    	}

		public function pull_avatar_success( $result ) {
			global $log;
			$this->set( array('avatar_url'=>$result->avatar_url) );
			return $result;
		}

		public function pull_shots_success( $result ) {
			global $log;
			$this->set( array('images'=>$result) );
			return $result;
		}

    }

    class Connections extends DynamicModel {

        public $dribbble = null;
        public $github = null;
        
        protected $storable = array('dribbble', 'github');

        function __construct() {
           $this->dribbble = new DribbbleConnection();
           $this->github = new GithubConnection();
        }


        public function set_dribbble( $data ) {
            global $log;
             $this->dribbble->set($data);

            // $log->append('set_dribbble', $data);
        }

        public function set_github( $data ) {
            global $log;

            $this->github->set($data);
            // $log->append('set_github', $data);
        }

        //
        //
        // get current data
        //
        //
        public function get( $compact = true ) {
            global $log;

            $data = new stdClass();
            $id = null;

            foreach ($this->storable as $key ) {
                if ( !empty($this->$key) || $this->$key == null ) {
                    if ( $compact == true ) {
                        $isEmpty = $this->$key->isEmpty();
                        if ( $isEmpty == true ) {
                            $data->$key = null;
                        } else {
                            $data->$key = $this->$key->get( $compact );
                        }
                    } else {
                        $data->$key = $this->$key->get( $compact );
                    }
                }
            }

            return get_object_vars($data);
        }


        public function getStorableData() {
            return json_encode($this->get());
        }
    }

