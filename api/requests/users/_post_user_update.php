<?php

	global $json;

    requireModel('Users/StaticUser');

	$proceed = true;

	if ( $_POST['account_email'] ) {
		$user = new StaticUser();
		$user->id 				= $_SESSION['user']->id;
		$user->account_email 	= $_POST['account_email'];
		$emailExists = $user->exists( true, 'email_not_me');
		if ( $emailExists ) {
			$proceed = false;
			$log->error('warning', array('id'=>1003,'description'=>'Cant change email, already exists.','details'=>'The email ['.$_POST['account_email'].'] already exists.'));
		}
	}

	if ( $proceed ) {
		$_SESSION["user"]->set( $_POST );
		$_SESSION["user"]->update();
		$user = $_SESSION["user"]->getProfile( true );
		$json["user"] = $user;
	} else {
		$status = 403;
	}