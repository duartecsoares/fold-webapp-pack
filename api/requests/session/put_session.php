<?php
	$log->addFile( __FILE__ );
	
	$result = false;
	$json["user"] = null;

	if ( !isset($_SESSION["user"]) || $_SESSION["user"]->hasSession() == false ) {
		$result = $_SESSION["user"]->login( $_PUT['username'], base64_decode($_PUT['password']) );
		$log->append('result', $result);
	}

	if ( $result == true ) {
		$json["user"] = $_SESSION["user"]->getProfile( true );
	}


