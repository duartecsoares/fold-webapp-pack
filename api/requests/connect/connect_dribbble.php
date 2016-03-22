<?php
	$log->addFile( __FILE__ );


	$username = $_SESSION["user"]->username;
	$username = strtolower($username);
	$avatar = null;
	$fieldname = "dribbble";

	if ( isset($_POST) && !empty($_POST) && !empty($_POST["username"]) ) {
		$dribbble_username = $_POST["username"];
		
		$connectionObject = $_USER->connectDribbble($dribbble_username, true);
		$json['user'] = $_USER->userProfile();
		$userConnections = $json['user']['connections'];

		if ( $userConnections && $userConnections['dribbble'] ) {
			$connection = $userConnections['dribbble'];
		}

		$folder = 'images/users/'.$_SESSION['user']->id.'/';
		$dribbble_avatar = $_USER->retrieveDribbbleAvatar();
		$dribbble_image = new Image();
		$dribbble_image->retrieve( $dribbble_avatar, 'avatar_'.$fieldname.'.jpg', $folder);

		$_SESSION['user']->addAvatar( $dribbble_image->relative_url, 'dribbble' );
		$_SESSION['user']->updatePreferences();

		$avatar = $dribbble_image->cdn_url;
		// $connection['avatar'] = $avatar;

		$connection = (array) $connection;
		$connection['avatar'] = $avatar;
		
		$log->append('connect_dribbble', $connection);

	}
	
