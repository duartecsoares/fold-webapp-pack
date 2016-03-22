<?php
	$log->addFile( __FILE__ );
	global $status;

	// if ( isset($_SESSION["user"]) && $_SESSION["user"]->hasSession() == true && !empty($_GET['hash']) ) {

    	requireModel('Flags/DynamicFlag');

		// hash
		// type:hash:id
		//
		//

		$hash = $_GET['hash'];
		$flag = new DynamicFlag();
		$flagged = $flag->flag( $hash );
		$json['flagged'] = $flagged;

		if ( $flagged == true ) {
			$status = 200;
		} else {
			$status = 404;
		}

		// if ( !empty( $hash ) ) {
		// 	$json['hash'] = base64_decode($hash);
		// }



		// $encodedHash = $flag->getEncodedHash('user','1422');
		// $json['hash'] = $encodedHash;
		// $json['decode'] = $flag->getDataFromEncodedHash($encodedHash);

    // } else {
    //     $status = 403;
    // }


	