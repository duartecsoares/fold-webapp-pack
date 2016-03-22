<?php
	$log->addFile( __FILE__ );
	
	requireFunction('traits');
	requireCollection('ConnectionCollection');

	if ( isset($_SESSION["user"]) && $_SESSION["user"]->hasSession() == true ) {

		$connections = new ConnectionCollection();
		$connections->setPreFilter('visible', 1);
		
		$json["connections"] 	= $connections->get();
		$json["traits"] 		= $__traits__;

		$_SESSION["user"]->pull();
		$json["user"] 			= $_SESSION["user"]->getProfile( true );
		$json["avatars"] 		= $_SESSION["user"]->getAvatars( true );
		$json["delete_phrase"] 	= $_SESSION["user"]->randomizeDeletePhrase();

	} else {
		$status = 403;
	}

