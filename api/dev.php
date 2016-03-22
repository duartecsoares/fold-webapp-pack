<?php
    $log->add('Method', $__requestMethod__ );

    if ( isset($_PUT) ) {
	    $log->append('params', $_PUT	, "PUT");
	}
    if ( isset($_GET) ) {
	    $log->append('params', $_GET    , "GET");
	}
    if ( isset($_POST) ) {
	    $log->append('params', $_POST	, "POST");
	}
    if ( isset($_DELETE) ) {
	    $log->append('params', $_DELETE	, "DELETE");
	}

	if ( isset($_SESSION["user"]) ) {
		$log->append('SessionUser', $_SESSION["user"]);
	}
	if ( isset($_COOKIE["user"]) ) {
    	$log->append('Cookie', $_COOKIE["user"] , "user");
	}
	if ( isset($_COOKIE["hash"]) ) {
	    $log->append('Cookie', $_COOKIE["hash"] , "hash");
	}