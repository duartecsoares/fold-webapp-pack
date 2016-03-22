<?php
	$log->addFile( __FILE__ );
	
	// GET users
	$dirFile = dirname(__FILE__);

	include_once $dirFile . "/../../functions/connectGithub.php";
	include_once $dirFile . "/../../functions/connectDribbble.php";
	include_once $dirFile . "/../../functions/connectTwitter.php";
	include_once $dirFile . "/../../functions/connectS3.php";
	include_once $dirFile . "/../../models/image.php";

	$folder = 'images/users/'.$_SESSION['user']->id.'/';

	$username 	= $__request__[3];
	$twitter 	= new ConnectTwitter( $username );
	$dribbble 	= new ConnectDribbble( $username );
	$github 	= new ConnectGithub( $username );

	$twitter_avatar 	= $twitter->getAvatar();
	$github_avatar 		= $github->getAvatar();
	$dribbble_avatar 	= $dribbble->getAvatar();

	$twitter_image = new Image();
	$twitter_url = $twitter_image->retrieve( $twitter_avatar, 'avatar_twitter.jpg', $folder);

	$dribbble_image = new Image();
	$dribbble_url = $dribbble_image->retrieve( $dribbble_avatar, 'avatar_dribbble.jpg', $folder);

	$github_image = new Image();
	$github_url = $github_image->retrieve( $github_avatar, 'avatar_github.jpg', $folder);


	$json['twitter_avatar'] = $twitter_url;
	$json['dribbble_avatar'] = $dribbble_url;
	$json['github_avatar'] = $github_url;


	$_SESSION['user']->addAvatar( $twitter_url, 'twitter' );
	$_SESSION['user']->addAvatar( $dribbble_url, 'dribbble' );
	$_SESSION['user']->addAvatar( $github_url, 'github' );

	$_SESSION['user']->setAvatar( null, 'twitter');
	$_SESSION['user']->updatePreferences();
