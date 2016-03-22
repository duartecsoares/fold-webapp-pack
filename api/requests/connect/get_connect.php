<?php
	$log->addFile( __FILE__ );
	
	// GET users
	$dirFile = dirname(__FILE__);

	include_once $dirFile . "/../../models/user.php";

	$fieldname = "connection";
	$connection = null;
	$github_username = null;

	if ( !isset($_SESSION["user"]) ) {

		$status = 404;

		$json["count"] = 0;

	} else {	

		$_USER = $_SESSION["user"];

		$service = $__request__[3];
		$count = 0;

		if ( !empty($service) ) {

			$service = strtolower($service);

			if ( $service == 'github') {

				$username = $_SESSION["user"]->username;
				$username = strtolower($username);

				if ( isset($_GET) && !empty($_GET) && !empty($_GET["username"]) ) {
					$github_username = $_GET["username"];
				
					$connection = $_USER->connectGithub($github_username, true);
					$json['user'] = $_USER->userProfile();
				}
				
				$fieldname = "github";

			} else if( $service == 'dribbble') {
				
				$username = $_SESSION["user"]->username;
				$username = strtolower($username);

				if ( isset($_GET) && !empty($_GET) && !empty($_GET["username"]) ) {
					$dribbble_username = $_GET["username"];
					
					$connection = $_USER->connectDribbble($dribbble_username, true);
					$json['user'] = $_USER->userProfile();

				}

				$fieldname = "dribbble";

			} else {
				$status = 404;
			}

		} else {
			$status = 400;
		}

	}
	
	$json[$fieldname] = $connection;
