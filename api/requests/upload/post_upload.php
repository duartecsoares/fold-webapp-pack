<?php

	global $status, $json, $log;

	$log->append('upload_files', $_FILES);
	$log->append('upload_files', $_POST);


	if ( isset($_SESSION['user']) && $_SESSION['user']->hasSession() ) {

		$type = !empty($_POST['type']) ? strtolower($_POST['type']) : null;
		$requestFile = $type ? "_upload_".$type : null;

		$log->append('upload_files', 'has_session : type : '.$type);

		//
		// If there's a type send to the right file
		//
		if ( $type && $requestFile ) {
			$log->append('upload_files', "_upload_".$type.".php");
			requirePHP($requestFile, dirname(__FILE__));
		} else {
			$status = 404;
			$json['uploaded'] = false;
		}

	} else {

		$log->append('upload_files', 'no_Session');
		$status = 403;
	}
