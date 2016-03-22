<?php
    $log->addFile( __FILE__ );

    global $process;

    $json['favorites'] = null;
    $json['favorited'] = true;

    if ( isset($_SESSION['user']) && $_SESSION['user']->hasSession() && !empty($id) ) {
				 
		requireModel('Ideas/DynamicIdea');

	    $idea       = new DynamicIdea();
	    $idea->id   = $id;
	    $result     = $idea->pull();

	    if ( $idea->exists( false ) ) {

	    	// trying to fvorite your own idea?
	    	if ( $idea->user_id != $_SESSION['user']->id ) {

	    		$idea       = new DynamicIdea();
	    		$idea->id   = $id;
	 			$result = $idea->pull('is_favorite', array("user_id"=>$_SESSION['user']->id));

			    //
			    // If idea exists, that means that it was already favorited
			    //
			    if ( !$idea->exists( false ) ) {
			    	
			    	$favorited = $idea->favorite();

			    	$idea->updateFavoriteCount( false );
			    	$idea->update('like_count');

			    	$view_count = $idea->view_count+1;

			    	$json['favorites'] = $view_count;
			    	$json['favorited'] = $favorited;

			    	$status = 200;

			    	//
			    	// After Process
			    	//
			    	class Process extends ServerTask {

			    		public function run() {

			    			global $id;

			    			$idea       = new DynamicIdea();
						    $idea->id   = $id;
						    $result     = $idea->pull();

			    			$user 		= new DynamicUser();
			    			$user->id 	= $idea->user_id;
			    			$user->pull();

			    			requireModel('Notifications/DynamicNotification');

			    			$notification = new DynamicNotification();
			    			$notification->fromTo( $_SESSION['user']->id, $user->id );
			    			$notification->setRelated( $idea->id );
			    			$notification->type('favorite-idea');
			    			$notification->send();

			    			$success = $user->sendEmail('FavoriteIdea', array("idea"=>$idea->getProfile()));
			    			
			    			return $success;

			    		}
			    	}

			    	$process = new Process();


			    } else {
			    	$status = 200;
			    	$json['favorited'] = false;
			    }

	    	} else {
				$json['favorited'] = false;
				$status = 400;
		        $log->error('error', array('id'=>1030, 'description'=>'Forbidden.', 'details'=>'Trying to favorite your own idea.') );
	    	}

	    } else {
	    	$json['favorited'] = false;
			$status = 404;
	        $log->error('error', array('id'=>1025, 'description'=>'Idea not found.', 'details'=>'This idea doesnt exist.') );
	    }

    } else {
    	$json['favorited'] = false;
		$status = 400;
        $log->error('error', array('id'=>1015, 'description'=>'Forbidden.', 'details'=>'Session needed for this action.') );
    }
