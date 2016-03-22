<?php
    $log->addFile( __FILE__ );

    include_once dirname(__FILE__) . "/../functions/connectDribbble.php";

    class DribbbleShot {

    	public $id;
    	public $title;

    	public $width;
    	public $height;

    	public $image = null;
    	public $image_original = null;
    	public $views_count;
    	public $likes_count;
    	public $comments_count;

    	public $updated;
    	public $link;
    	public $tags;

        function __construct( $data = null ) {
        	global $log;

            if ( $data ) {
                $this->id 		= $data->id;
                $this->title 	= $data->title;
                $this->width 	= $data->width;
                $this->height 	= $data->height;

                $images = $data->images;

                if ( $images ) {
                    if ( $images->hidpi != null ) {
                		$this->image 			= $images->hidpi;
	                	$this->image_original 	= $images->hidpi;
	                } else if ( $images->normal != null ) {
	                	$this->image 			= $images->normal;
	                	$this->image_original 	= $images->normal;
	                } else if ( $images->teaser != null ) {
	                	$this->image 			= $images->teaser;
	                	$this->image_original 	= $images->teaser;
	                }  	
                }

                $this->views_count 		= $data->views_count;
                $this->likes_count 		= $data->likes_count;
                $this->comments_count 	= $data->comments_count;
                $this->updated 			= $data->updated_at;
                $this->link 			= $data->html_url;
                $this->tags 			= $data->tags;

            }

        }

        public function getData() {
        	$array = array();

            $array['id']         = $this->id;
            $array['title']      = $this->title;

            $array['width']      = $this->width;
            $array['height']     = $this->height;

            $array['image']      = $this->image;

            $array['updated']    = $this->updated;
            $array['link']       = $this->link;

            return $array;
        }
    }

	class Dribbble {
        public $username;
        public $connection = null;
        public $images;
        public $last_pull;
        public $user;

        private $data;

        function __construct( $username = null ) {

            if ( $username ) {
            	$this->username = $username;
                $this->connection = new ConnectDribbble($username);
            }
        }

        public function pullAvatar() {

            $avatar = $this->connection->getAvatar();
            $this->avatar = $avatar;
            
            return $avatar;

        }

        public function pull() {
            global $log;

            $shots = $this->connection->getUserShots();

            if ( is_array($shots) && count($shots) > 0 ) {

            	$array = array();
            	$this->data = $shots;

        		for( $i = 0; $i < count($this->data); $i++ ) {

        			$shot = new DribbbleShot( $this->data[$i] );
        			$array[] = $shot->getData();
        			$this->last_pull = time();

        		}

	        	if ( count($array) > 0 ) {
	        		$this->images = $array;
	        	}

            }

            // $log->append('connectDribbble',  $shots);

            return $shots;
        }

        public function getStorableData() {

        	$array = array();

        	$array['username'] = $this->username;
        	$array['images'] = $this->images;
        	$array['last_pull'] = $this->last_pull;

        	return $array;

        }

    }