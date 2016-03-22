<?php
	$log->addFile( __FILE__ );
	
	// GET users
	$dirFile = dirname(__FILE__);

	include_once $dirFile . "/../../models/user.php";

	$username = $__request__[3];
	$fieldname = "users";
	$count = 0;
	$users = null;

	if ( isset($_GET) && count($_GET) > 0 ) {
		$GET_summary = $_GET['summary'];
		$GET_exists = $_GET['exists'];
	}

	if ( empty($username) ) {
		if ( isset( $_GET ) && !empty( $_GET['orderby'] ) ) {
			$ascdesc = strtoupper($_GET['orderby']);
		} else {
			$ascdesc = 'DESC';
		}
		$query = "SELECT * FROM users ORDER BY id ".$ascdesc." LIMIT 51";
		$users = array();
	} else {

		if ( filter_var($username, FILTER_VALIDATE_EMAIL) ) {
			$email = $username;
			$query = "SELECT * FROM users WHERE account_email='".$email."'";
			$log->append('params', array('email'=>$email) , "REST");
		} else {
			$query = "SELECT * FROM users WHERE username='".$username."'";
		    $log->append('params', array('username'=>$username) , "REST");
		}
		
		$fieldname = "user";
	}

	$result = $pdo->query($query);

    $log->addQuery( $query );

    if ( $result == false ) {
        $log->error('error', array('id'=>1002,'description'=>'Bad Request - Bad Parameter.','details'=>'[id] should be a string.'));
        $status = 400;
    } else {

    	//
    	// get a list of traits
    	//
    	$traits = array();

    	$query_traits = "SELECT id, name, parent FROM traits";
		$result_traits = $pdo->query($query_traits);
		$log->addQuery( $query );

		while ($trait = $result_traits->fetch(PDO::FETCH_OBJ)) {
		    $traits[] = $trait;
		}

		$__traits__ = $traits;


		$result->setFetchMode(PDO::FETCH_CLASS, 'User');
		$count = $result->rowCount();

		if ( empty($username) ) {
			while ($user = $result->fetch()) {
			    $users[] = $user->userSummary();
			}
		} else {

			if ( $count != 0 ) {

				if ( isset($GET_exists) ) {
					$users = true;
					$fieldname = "exists";
				} else {

					while ($user = $result->fetch()) {
					    $users = $user->userProfile();

					    if ( $users['idea_count'] > 0 ) {

	    					include_once $dirFile . "/../../models/idea.php";

	    					if ( $_SESSION['user'] && $_SESSION['user']->id == $users['id'] ) {
	    						$query = "SELECT * FROM ideas WHERE user_id='".$users['id']."'";
	    					} else {
	    						$query = "SELECT * FROM ideas WHERE user_id='".$users['id']."' AND private IS NULL";
	    					}

					    	$result = $pdo->query($query);
					    	$log->addQuery( $query );
					    	$result->setFetchMode(PDO::FETCH_CLASS, 'Idea');

					    	if ( $result != false ) {
					    		$ideas = array();

								while ($idea = $result->fetch()) {
									$ideas[] = $idea->ideaSummary();
								}

								if (count($ideas) == 0) {
									$ideas = null;
									$users['idea_count'] = 0;
								}
					    		$users['ideas'] = $ideas;
						    }

					    }

					}
				}

			} else {
				if ( isset($GET_exists) ) {
					$users = false;
					$fieldname = "exists";
				}
				$status = 404;
			}

		}
	}
  
	$json[$fieldname] = $users;
	$json["count"] = $count;
