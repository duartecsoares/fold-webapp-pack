<?php
	$log->addFile( __FILE__ );

	$avatar = null;

	if ( isset($_SESSION['user']) ) {
		
		$_SESSION['user']->removeAvatar( $_POST['network'] );
		$_SESSION['user']->setAvatar(false);

	} else {
		$status = 400;
		$log->error('error', array('id'=>1003,'description'=>'Forbiden','details'=>'No permissions.'));
	}

