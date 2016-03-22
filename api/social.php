<?php

	$def = array();
	$data = array();

	$images = array(
			array("id"=>0, "title"=>"Connecting Designers & Developers.", "data"=>"", "url"=>"https://s3.amazonaws.com/buildit-storage/social/fb-banner.png"),
			array("id"=>1, "title"=>"Build your ideas. Skip the funding.", "data"=>"", "url"=>"https://s3.amazonaws.com/buildit-storage/social/fb-banner-2.png")
		);

	$def['meta'] = array(
			"title" => "Build it With Me - Connecting Designers & Developers",
			"desc" 	=> "Build it with Me is a tool that helps connecting you with like-minded designers & developers with the same goal: create cool & useful apps.",
			"image" => $images[0]['url']
		);

	
	if ( isset($_GET['req']) && isset($_GET['id']) ) {

		$req 	= $_GET['req'];
		$id 	= $_GET['id'];
		$domain = $_SERVER['HTTP_HOST'];
		$prefix = isset($_SERVER['HTTPS']) ? 'https://' : 'http://';

		if ( $req == 'people' ) {
			if ( isset($id) ) {
				$relative = '/api/users/'.$id;
			} else {
				$relative = '/api/users/';
			}
		} else if( $req == 'idea' ) {
			if ( isset($id) ) {
				$relative = '/api/ideas/'.$id;
			} else {
				$relative = '/api/ideas/';
			}
		}

		?>
		<?php
		if ( isset($relative) ) {	
			$image = null;	
			//  Initiate curl
			$ch = curl_init();
			// Disable SSL verification
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			// Will return the response, if false it print the response
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			// Set the url
			curl_setopt($ch, CURLOPT_URL, $prefix.$domain.$relative);
			// Execute
			$result=curl_exec($ch);
			// Closing
			curl_close($ch);

			$data = json_decode($result, true);
			$data['type'] = $req;

			if ( $data['type'] == 'people' && !$id ) {

			} else if ( $data['type'] == 'people' && $id ) {

				$username 		= $data['user']['username'];
				$name 			= $data['user']['fullname'];
				$bio 			= $data['user']['bio'];
				$image 			= $data['user']['avatar'];
				$user_string 	= $name ? $name : $username;

				$def['meta']['title'] = $user_string.' @'.$username.' - builditwith.me';
				$def['meta']['desc'] = $bio.' - Connect with '.$user_string.' on builditwith.me and other designers and developers. Build the next big thing.';

			} else if ( $data['type'] == 'idea' && !$id ) {

			} else if ( $data['type'] == 'idea' && $id ) {

				$image 			= $data['idea']['avatar'];
				$description 	= $data['idea']['description'];
				$about 			= $data['idea']['about'];
				$name 			= $data['idea']['name'];
				$user 			= $data['idea']['user'];

				if ( $user ) {
					$user_name 		= $user['fullname'];
					$user_username 	= $user['username'];
					$user_string 	= $user_name ? $user_name : $user_username;
				}

				$def['meta']['title'] 	= $name.' - builditwith.me';

				if ( $user ) {
					$def['meta']['desc'] 	= $description.' by '.$user_string.' - '.$about;
				} else {
					$def['meta']['desc'] 	= $description.' - '.$about;
				}

			}

			if ( $image ) {
				$def['meta']['image'] = $image;
			}

		}

	}

	$data['meta'] = $def['meta'];
