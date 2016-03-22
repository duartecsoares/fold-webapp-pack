<?php

	global $status, $json, $log;

	requireModel('Images/StaticImage');

	$image = new StaticImage();
	$success = $image->moveUploadedImage( $_FILES['avatar'] );

	if ( $success ) {

		$sent = $_SESSION['user']->sendAvatarToCDN( 'buildit', '', $image );

		if ( $sent ) {
			$_SESSION['user']->update('preferences');
			$status = 200;
		} else {
			$status = 400;
		}


	} else {
		$status = 400;
	}
	
	$json['uploaded'] = $image->cdn_url ? $image->cdn_url : $image->local_url;
