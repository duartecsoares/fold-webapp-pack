<?php
	$log->addFile( __FILE__ );

	//
	// Start Execution
	//
	$username 	= ( isset($__request__[3]) ? strtolower($__request__[3]) : null );
	$action 	= ( isset($__request__[4]) ? strtolower($__request__[4]) : null );
	$user 		= null;
	$proceed 	= true;

	$log->append('params', array('username'=>$username, 'action'=>$action) , "REST");

	if ( !empty($username) && isset($_POST) ) {

	} else {
		$proceed = false;
		$log->error('error', array('id'=>1002,'description'=>'Bad Request - No Parameter.','details'=>'No parameters received.'));
	}

	if ( $proceed == false ) {
		$status = 400;
	} else if ( isset($_SESSION["user"]) && $_SESSION["user"]->hasSession() == true) {

		if( $_SESSION["user"]->isNotBlocked() ) {
			//
			// there's an action
			//
			if ( !empty($action) ) {

				//
				// load action
				//
				requirePHP('_post_user_'.$action, dirname(__FILE__));

			} else if ( $username == $_SESSION["user"]->username  ) {

				//
				// udpate user
				//
				requirePHP('_post_user_update'.$action, dirname(__FILE__));

			} else {

				//
				// trying to update a user with no prmission
				//
				$status = 403;

			}
		} else {
			$status = 450;
		}

	}

