<?php
	$log->addFile( __FILE__ );
	
	// GET users
	$dirFile = dirname(__FILE__);

	$username 	= ( isset($__request__[3]) ? strtolower($__request__[3]) : null );
	$action 	= ( isset($__request__[4]) ? strtolower($__request__[4]) : null );

	$fieldname = "users";
	$count = 0;
	$user = null;

	//
	// there's an action
	//
	if ( !empty($action) ) {

		//
		// load action
		//
		requirePHP('_delete_user_'.$action, dirname(__FILE__));

	} else if ( !empty($username) && $_SESSION['user'] && $_SESSION['user']->username == $username ) {

		$status = 400;
		$json['deleted'] = false;

		if ( !empty($_GET) && !empty($_GET['delete_phrase']) && !empty($_GET['password']) ) {

			$delete_phrase 	= $_GET['delete_phrase'];
			$user_phrase 	= $_SESSION['user']->getDeletePhrase();
			$passwordMatch 	= $_SESSION['user']->verifyPassword( base64_decode($_GET['password']) );

			if ( $delete_phrase != $user_phrase || $passwordMatch == false ) {
				$status = 400;
				$log->error('error', array('id'=>1010,'description'=>'Wrong password or empty delete phrase.','details'=>'Wrong password or user generated phrase ['.$user_phrase.'] differs from the one received ['.$delete_phrase.'].'));
				//$log->error('error', array('id'=>1010,'description'=>'Delete phrases do not match.','details'=>'User generated phrase ['.$user_phrase.'] differs from the one received ['.$delete_phrase.'].'));
			} else if( $_SESSION['user']->delete() ) {
				$status = 200;
				// PROCEED AND DELETE
				$json['deleted'] = true;
			}

			$json['user'] = null;

		} else {
			$status = 400;
        	$log->error('error', array('id'=>1010,'description'=>'Wrong password or delete phrase.','details'=>'Empty delete phrase.'));
		}


		if ($status == 200) {
			if ( isset( $_SESSION["user"] ) ) {
				if ( is_object($_SESSION["user"]) ) {
					$_SESSION["user"]->logout();
					unset($_SESSION["user"]);
				}
			} else if ( isset( $_COOKIE["user"] ) ) {
				requireFunction('deleteCookie');
				deleteCookie();
			}

			unset($_SESSION["user"]);
		}


	} else {
		$status = 403;
        $log->error('error', array('id'=>1009,'description'=>'No permission.','details'=>'Cannot delete user, not an admin or the user.'));
		// forbidden
	}


