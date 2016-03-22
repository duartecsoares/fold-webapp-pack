<?php
	$log->addFile( __FILE__ );
	

	if ( isset($_SESSION["user"]) ) {
		$_SESSION["user"]->logout();
		unset($_SESSION["user"]);
	}
