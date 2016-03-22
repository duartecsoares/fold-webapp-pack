<?php
	$log->addFile( __FILE__ );

	$avatar = null;

	if ( isset($_SESSION['user']) ) {

		if ( isset($_POST['network']) ) {

			$network = $_POST['network'];
			$avatar = $_SESSION['user']->setAvatar($network);

		} else {
			$status = 400;
			$log->error('error', array('id'=>1002,'description'=>'Bad Request - No Parameter.','details'=>'Network parameter was not received.'));
		}


	} else {
		$status = 400;
		$log->error('error', array('id'=>1003,'description'=>'Forbiden','details'=>'No permissions.'));
	}

	$json['avatar'] = $avatar;