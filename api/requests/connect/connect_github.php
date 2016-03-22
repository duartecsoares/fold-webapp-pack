<?php
	$log->addFile( __FILE__ );
	

	$username = $_SESSION["user"]->username;
	$username = strtolower($username);
	$avatar = null;
	$fieldname = "github";

	if ( isset($_POST) && !empty($_POST) && !empty($_POST["username"]) ) {
		$github_username = $_POST["username"];
	
		$connectionObject = $_USER->connectGithub($github_username, true);
		$json['user'] = $_USER->userProfile();
		$userConnections = $json['user']['connections'];

		if ( $userConnections && $userConnections['github'] ) {
			$connection = $userConnections['github'];
		}

		$folder = 'images/users/'.$_SESSION['user']->id.'/';
		$avatar = $_USER->retrieveGithubAvatar();
		$avatar_image = new Image();
		$avatar_image->retrieve( $avatar, 'avatar_'.$fieldname.'.jpg', $folder);

		$_SESSION['user']->addAvatar( $avatar_image->relative_url, 'github' );
		$_SESSION['user']->updatePreferences();

		$avatar = $avatar_image->cdn_url;

		$connection = (array) $connection;
		$connection['avatar'] = $avatar;

		$log->append('connect_github', $connection);
		// $connection->avatar = $avatar;

	}
	