<?php

    global $process, $json, $username;


    if ( isset($_SESSION['user']) && $_SESSION['user']->hasSession() && !empty($username) && $_SESSION['user']->username == $username ) {
				 
		requireModel('Users/DynamicUser');
        requireModel('Likes/LikeUserModel');

	    // $user       		= new DynamicUser();
	    // $user->username   	= $username;
	    $result     		= $_SESSION['user']->pull();
	

	    if ( $_SESSION['user']->exists( false ) ) {

	    	$result = $_SESSION['user']->updatePreferences( $_POST );
	    	
	    	if ( $result ) {
	    		$json['preferences'] = $_SESSION['user']->getPreferences();
	    	} else {
				$json['preferences'] = false;
				$status = 400;
	    	}


	    } else {
	    	$json['preferences'] = false;
			$status = 404;
	        $log->error('error', array('id'=>1016, 'description'=>'User not found.') );
	    }

	} else {
		$json['preferences'] = false;
		$status = 400;
        $log->error('error', array('id'=>1015, 'description'=>'Forbidden.', 'details'=>'Session needed for this action.') );
	}
