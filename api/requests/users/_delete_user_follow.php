<?php

    global $process, $json, $username;

    if ( isset($_SESSION['user']) && $_SESSION['user']->hasSession() && !empty($username) ) {
				 
		requireModel('Users/DynamicUser');
        requireModel('Likes/LikeUserModel');

	    $user       		= new DynamicUser();
	    $user->username   	= $username;
	    $result     		= $user->pull('basic');
	
	    if ( $user->exists( false ) && !empty($user->id) ) {

	    	$follow = new LikeUserModel();
            $follow->setFollowing( $user->id );
            $follow->setFollower( $_SESSION['user']->id );
			$result = $follow->pull('exists');

		    //
		    // If follow does not exist, that means that it was already favorited
		    //
		    if ( $follow->exists( false ) ) {
		    	
		    	$followed = $user->unFollow();

		    	$user->updateFollowerCount( true );

		    	$json['followed'] = false;
		    	$status = 200;

				requireModel('Notifications/DynamicNotification');

    			$notification = new DynamicNotification();
    			$notification->fromTo( $_SESSION['user']->id , $user->id );
    			$notification->type('follow');
    			$notification->delete();

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
		    			$this->session->updateFollowingCount( true );
						return $this->user->calcPopularity();
		    		}
		    	}

		    	$process = new Process( $user, $_SESSION['user'] );
		    	//
		    	//
		    	//

		    } else {
		    	$status = 200;
		    	$json['followed'] = false;
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
