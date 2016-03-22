<?php

    global $process, $json, $username;


    if ( isset($_SESSION['user']) && $_SESSION['user']->hasSession() && !empty($username) ) {
				 
		requireModel('Users/DynamicUser');
        requireModel('Likes/LikeUserModel');

	    $user       		= new DynamicUser();
	    $user->username   	= $username;
	    $result     		= $user->pull('basic');
	
		$log->append('is_followed', $user);

	    if ( $user->exists( false ) && !empty($user->id) ) {

	    	$follow = new LikeUserModel();
            $follow->setFollowing( $user->id );
            $follow->setFollower( $_SESSION['user']->id );
			$result = $follow->pull('exists');

			$log->append('is_followed', $result);

		    //
		    // If follow does not exist, that means that it was already favorited
		    //
		    if ( !$follow->exists( false ) ) {
		    	
		    	$followed = $user->follow();

		    	$user->updateFollowerCount( true );
		    	// $user->update('followed_count');

		    	$json['followed'] = $followed;
		    	$status = 200;

		    	//
		    	// After Process, code processed after the request is returned
		    	//
		    	class Process extends ServerTask {

		    		public $user;
		    		public $session;

		    		function __construct($user, $session) {
			            $this->user 	= $user;
			            $this->session 	= $session;
			        }

		    		public function run() {

		    			requireModel('Notifications/DynamicNotification');

		    			$notification = new DynamicNotification();
		    			$notification->fromTo( $this->session->id, $this->user->id );
		    			$notification->type('follow');
		    			$notification->send();

		    			$this->session->updateFollowingCount( true );
						$this->user->calcPopularity();
		    			return $this->user->sendEmail('FavoriteUser', array("message"=>$_SESSION['user']->username.' Followed You.'));
		    		}
		    	}

		    	$process = new Process( $user, $_SESSION['user'] );

		    } else {
		    	$status = 200;
		    	$json['followed'] = true;
		    }

	    } else {
	    	$json['followed'] = false;
			$status = 404;
	        $log->error('error', array('id'=>1016, 'description'=>'User not found.', 'details'=>'User you are trying to follow does not exist.') );
	    }

	} else {
		$json['followed'] = false;
		$status = 400;
        $log->error('error', array('id'=>1015, 'description'=>'Forbidden.', 'details'=>'Session needed for this action.') );
	}
