<?php
	$log->addFile( __FILE__ );
	

	if ( isset($__request__[3]) && !empty($__request__[3]) ) {
		$username = $__request__[3];
	} else {
		$username = null;
	}

	if ( isset($_GET) && count($_GET) > 0 ) {
		$GET_summary = isset($_GET['summary']) ? $_GET['summary'] : null;
		$GET_exists = isset($_GET['exists']) ? $_GET['exists'] : null;
	}

	if ( isset($username) && !empty($username) ) {
		//
		// Return user profile
		//
		include_once "_get_user.php";

	} else {

		//
		// return list of users
		//
		include_once "_list_users.php";
	}
