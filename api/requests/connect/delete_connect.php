<?php
	$log->addFile( __FILE__ );
	
	// GET users
	$dirFile = dirname(__FILE__);

	include_once $dirFile . "/../../models/user.php";

	$fieldname = "deleted";
	$connection = false;


	if ( !isset($_SESSION["user"]) ) {

		$status = 403;
		$json["count"] = 0;
		$log->error('warning', array('id'=>1004,'description'=>'Permissions needed.','details'=>'A user must be logged in to perform this task.'));

	} else {

		$_USER = $_SESSION["user"];
		$service = $__request__[3];

		$log->append('delete_connect', $service);

		if ( !empty($service) ) {

			$service = strtolower($service);
			$connection = $_USER->deleteConnection( $service );
			$status = 200;

		} else {
			$status = 404;
		}

	}
	
	$json[$fieldname] = $connection;
