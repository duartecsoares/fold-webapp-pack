<?php
	
	set_time_limit( 7200 );

	if ( $_GET['set'] == 'ideas_looking_for' ) {
		$query = "SELECT * FROM ideas WHERE looking_for IS NOT NULL AND traits IS NULL";
		$result = $pdo->query($query);

	    $designWords    = array("design", "designer", "interface", "interaction", "ui/ux", "ux");
	    $developerWords = array("programmer", "programming", "developer", "development");

	    $backendWords   = array("backend", "php", "ruby", "sql", "database", "databases");
	    $frontEndWords  = array("javascript", "js", "css", "html");
	    $iosWords       = array("ios", "iphone", "ipad");
	    $androidWords   = array("android");

	    while ($idea = $result->fetch()) {

	        $skills         = strtolower($idea['looking_for']);

	        // $log->append('db_update_17', '----------------------------------------');
	        // $log->append('db_update_17', 'User skills: '.$skills);

	        $skillsSalt     = str_replace(" ",",", $skills);
	        $skillsSalt     = str_replace(".",",", $skillsSalt);
	        $skillsSalt     = str_replace("!",",", $skillsSalt);

	        // $log->append('db_update', '------------------');
	        // $log->append('db_update', $idea['name']);
	        // $log->append('db_update', $skillsSalt);

	        // $log->append('db_update_17', 'Skills Salt: '.$skillsSalt);

	        $skillsArray    = explode(",", $skillsSalt);

	        $isDesigner     = false;
	        $isDeveloper    = false;
	        $isFrontEnd     = false;
	        $isBackEnd      = false;
	        $isIOS          = false;
	        $isAndroid      = false;

	        $traits         = '';
	        $traitsArray    = array();

	        // $log->append('db_update_17', $skillsArray);

	        //
	        // Check every word
	        //
	        for( $i=0; $i<count($skillsArray); $i++) {

	            // design?
	            if ( in_array($skillsArray[$i], $designWords) ) {
	                $isDesigner = true;
	            }

	            // developer?
	            if ( in_array($skillsArray[$i], $developerWords) ) {
	                $isDeveloper = true;
	            }

	            // backend?
	            if ( in_array($skillsArray[$i], $backendWords) ) {
	                $isBackEnd = true;
	            }

	            // frontend?
	            if ( in_array($skillsArray[$i], $frontEndWords) ) {
	                $isFrontEnd = true;
	            }

	            // iOS?
	            if ( in_array($skillsArray[$i], $iosWords) ) {
	                $isIOS = true;
	            }

	            // Android?
	            if ( in_array($skillsArray[$i], $androidWords) ) {
	                $isAndroid = true;
	            }

	        }

	        if ( $isDesigner == true ) {
	            $traitsArray[]  = '1';
	        }

	        if ( $isIOS == true ) {
	            $traitsArray[]  = '5';
	        }

	        if ( $isAndroid == true ) {
	            $traitsArray[]  = '6';
	        }

	        if ( $isFrontEnd == true ) {
	            $traitsArray[]  = '3';
	            $isDeveloper = false;
	        }

	        if ( $isBackEnd == true ) {
	            $traitsArray[]  = '4';
	            $isDeveloper = false;
	        }

	        if ( $isDeveloper == true ) {
	            $traitsArray[]  = '2';
	        }

	        $traits = implode(",",$traitsArray);

	    	$array = array($traits, $idea['id']);

	    	$log->append('db_update', $traits);

	        $sql_update 	= "UPDATE ideas SET traits = ? WHERE id = ?";
	        $query_update 	= $pdo->prepare( $sql_update );
	        $result_update 	= $query_update->execute($array);

	        if ( !$result_update ) {
	        	$log->append('db_update', '! Could not update ideas: '.$ideas['name']);
	        	// $log->append('db_update_17', $array);
	        } else {
	            $log->append('db_update', 'Updated ideas: '.$ideas['name'].' | traits : '.$traits);
	        }
	    }
	}

	// if ( isset($_SESSION['user']) && $_SESSION['user']->hasSession() ) {

	// 	$_SESSION['user']->getFollowingIDs();

	// }

	// requireFunction('ClusterTask');

	// $a = new ClusterTask();


		// $mailer->Body    	= $_POST['Body'];
		// $mailer->AltBody 	= $_POST['AltBody'];
		// $mailer->From 		= $_POST['From'];
		// $mailer->FromName 	= $_POST['FromName'];

		// $mailer->Subject 	= $_POST['Subject'];
		// $mailer->addAddress($_POST['ToEmail'], $_POST['ToName']);


	// $data = array(
	// 		"Body" 		=> "Hello!!!",
	// 		"AltBody" 	=> "Html!!",
	// 		"From"		=> "hello@builditwith.me",
	// 		"FromName"	=> "Build It",
	// 		"Subject"	=> "Pumba Cluster",
	// 		"ToEmail" 	=> "hello@carlosgavina.com",
	// 		"ToName" 	=> "Carlos"
	// 	);

	// $a->set('emailer', $data, 'POST');
	// $a->run();


	// requireCollection('LikesUserCollection');

	// $col = new LikesUserCollection();
	// $col->setFollower( $_SESSION['user']->id );
	// $col->pull();
	// $ids = $col->arrayWith('following_id');

	// $json['following'] = $ids;


	// requireCollection('LikesIdeaCollection');

	// $col = new LikesIdeaCollection();
	// $col->setFavedBy( $_SESSION['user']->id );
	// $col->pull();
	// $ids = $col->arrayWith('idea_id');

	// $json['ideas'] = $ids;

    // requireCollection('ImageCollection');

    // $gallery = new ImageCollection();

    // $gallery->setPreFilter('idea', $_GET['idea']);
    // $gallery->pull();
    // $json['gallery'] = $gallery->models;
    // $json['images'] = $gallery->toJSON();

	// requireModel('Email/EmailModel');

	// $email = new Email();
	// $email->addTo('hello@carlosgavina.com');
	// $json['email'] = $email->send('Welcome', array("username"=>"super_user"));
  	
 //  	requireCollection('ImageCollection');
 //  	requireCollection('IdeaCollection');
 //    requireModel('Ideas/DynamicIdea');

 //    $ideaList = new IdeaList();
 //    $ideaList->ModelClass = 'DynamicIdea';
 //    $listArray = null;

 //    //
 //    //
 //    // Pagination
 //    //
 //    //
 //    if ( isset($_GET['page']) ) {
 //        $ideaList->setPage( $_GET['page'] ? $_GET['page'] : 0 );
 //    }

 //    if ( isset($_GET['perpage']) ) {
 //        $ideaList->setPerPage( $_GET['perpage'] ? $_GET['perpage'] : 5 );
 //    }

 //    $ideaList->setPreFilter('has_old_images');

 //    $models = $ideaList->get();
 //    $result = array();
 //    $galleries = array();

	// foreach ($models as $key => $row) {

	// 	if ( $row->image_1 ) {
	// 		$image = new StaticImage();
	// 		$local = $image->setLocal( $row->image_1 );

	// 		$log->append('upload_files', $local);

	// 		if ( $local && file_exists($local) ) {
	// 			$success = $row->sendImageToCDN('gallery', $image);
	// 			$result[] = array("id"=>$row->id, "image"=>$local, "success"=>$success);

	// 		} else {
	// 			$result[] = array("id"=>$row->id, "image"=>$local, "success"=>false);
	// 		}

	// 		//
	// 		// upload others
	// 		//
	// 		if ( $row->image_2 ) {
	// 			$image = new StaticImage();
	// 			$local = $image->setLocal( $row->image_2 );

	// 			$log->append('upload_files', $local);

	// 			if ( $local && file_exists($local) ) {
	// 				$success = $row->sendImageToCDN('gallery', $image);
	// 				$result[] = array("id"=>$row->id, "image"=>$local, "success"=>$success);

	// 			} else {
	// 				$result[] = array("id"=>$row->id, "image"=>$local, "success"=>false);
	// 			}

	// 		}

	// 		//
	// 		// upload others
	// 		//
	// 		if ( $row->image_3 ) {
	// 			$image = new StaticImage();
	// 			$local = $image->setLocal( $row->image_3 );

	// 			$log->append('upload_files', $local);

	// 			if ( $local && file_exists($local) ) {
	// 				$success = $row->sendImageToCDN('gallery', $image);
	// 				$result[] = array("id"=>$row->id, "image"=>$local, "success"=>$success);

	// 			} else {
	// 				$result[] = array("id"=>$row->id, "image"=>$local, "success"=>false);
	// 			}

	// 		}

	// 		//
	// 		// upload others
	// 		//
	// 		if ( $row->image_4 ) {
	// 			$image = new StaticImage();
	// 			$local = $image->setLocal( $row->image_4 );

	// 			$log->append('upload_files', $local);

	// 			if ( $local && file_exists($local) ) {
	// 				$success = $row->sendImageToCDN('gallery', $image);
	// 				$result[] = array("id"=>$row->id, "image"=>$local, "success"=>$success);

	// 			} else {
	// 				$result[] = array("id"=>$row->id, "image"=>$local, "success"=>false);
	// 			}

	// 		}

	// 	}
	// }

 //    $json['result'] = $result;


	// $log->addFile( __FILE__ );

	// requireCollection('TypesCollection');

	// $list = new TypesCollection();
	// $list->setPreFilter('visible', 1);
	// $list->pull();

	// $json['ids'] = $list->join('id');
	// $json['types'] = $list->getWhere('id', $json['ids']);
	// $json['idea_type'] = $list->getWhere('name', 'Mac');
	// $json['join'] = $list->join('id');
	// $json['idea_type'] = $list->join('id');

	// GET users
	// include_once dirname(__FILE__) . "/../../models/user.php";

	// $user = new SessionUser();
	// $user->username = 'xxxx';
	// $user->extra_info = 'asd asd asd a xxxx';
	// $user->update();
	// $json['createCookie'] = $user->createCookie( true );

	// $json['login'] = $_SESSION["user"]->login( $_GET['username'], $_GET['password'] );
	// $json['user'] = $_SESSION["user"]->getProfile();

	// $json['login'] = $_SESSION["user"]->logout();

	// $json['session_user'] = $_SESSION["user"]->getProfile();
	// $json['session_user_hassession'] = $_SESSION["user"]->hasSession();

	// $user = new DynamicUser();
	// $user->username = !empty($_GET['username']) ? $_GET['username'] : 'carlosgavina';
	// // $json['popularity_total'] = $session_user->calcPopularity();
	// $user->pull();

	// $user->addView( false, false );
	// $user->calcPopularity( false, false );

	// $user->update('view_count,popularity_alltime');

	// $user = new DynamicUser();
	// $user->username = 'test_user_xxxx';
	// $user->pull();
	// $user->fullname = "Mega Name";
	// $user->bio = 'hello world';
	// $user->push();
	// $json['created_user'] = $user->getProfile();

	// $userList = new UserList();
	// $userList->setPreFilter('traits', '1', 'and');
	// $userList->setPreFilter('status', 'unavailable');
	// $userList->setPreFilter('status', true);
	// $json['users'] = $userList->get('summary');
	// $json['count'] = $userList->count;

    // requireModel('Connections');

    // $c = new Connections();

    // $c->dribbble->setId('carlosgavina');
    // $c->dribbble->pull('info');
    // $c->dribbble->pull('shots');

    // $c->github->setId('jdsntg');
    // $c->github->pull('info');
    // $c->github->pull('repos');

    // requireFunction('base62');

    // $blackList = array("@", "=", "/", "(", ")");
    
    // $hash = base62encode(generateRandomChars(3, false).'1267');

    // $json['url'] = $hash;
 