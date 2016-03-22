<?php
	$log->addFile( __FILE__ );
	
	if ( !isset($_SESSION["user"]) || $_SESSION["user"]->hasSession() == false ) {

		$status = 404;
		$json["user"] = null;
		$json["count"] = 0;

	} else {
		
		$status = 200;
		$json["user"] = $_SESSION["user"]->getProfile( true );
		$json["count"] = 1;

	}
	
