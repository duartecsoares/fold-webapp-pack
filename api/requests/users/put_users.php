<?php
	$log->addFile( __FILE__ );
	
	requireModel('Users/DynamicUser');

	global $process;

	//
	// Start Execution
	//
	$proceed = true;
	$json["user"] = null;

	$log->append('new_user_result', $proceed);

	if ( isset($_PUT) && count($_PUT) > 0 ) {

		$username 	= $_PUT['username'];
		$password 	= base64_decode($_PUT['password']);
		$email 		= $_PUT['email'];

		//
		// Check if any of the basic required fields is missing
		//
		if ( empty($username) ) {
			$proceed = false;
	        $log->error('error', array('id'=>1002,'description'=>'Bad Request - Bad Parameter.','details'=>'[username] should be a string.'));
		}

		// validate password
		if ( empty($password) ) {
			$proceed = false;
	        $log->error('error', array('id'=>1002,'description'=>'Bad Request - Bad Parameter.','details'=>'[password] should be a string.'));
		} else if ( strlen($password) < 6 ) {
			$proceed = false;
			$log->error('error', array('id'=>1002,'description'=>'Bad Request - Bad Parameter.','details'=>'[password] should be a string, longer than 6 characters.'));
		}

		if ( !filter_var($email, FILTER_VALIDATE_EMAIL) ) {
			$proceed = false;
	        $log->error('error', array('id'=>1002,'description'=>'Bad Request - Bad Parameter.','details'=>'[email] isnt valid'));
		}

	} else {
		$proceed = false;
		$log->error('error', array('id'=>1002,'description'=>'Bad Request - No Parameter.','details'=>'No parameters received.'));
	}

	$log->append('new_user_result', $proceed);


	if ( $proceed == false ) {
		$status = 400;
	} else {

		$username = strtolower($username);
		$email = strtolower($email);

		$user = new DynamicUser();
		$user->username = $username;
		$userNameExists = $user->exists();

		$user->username = null;
		$user->account_email = $email;
		$emailExists = $user->exists();

		if ( $userNameExists == true ) {
			$status = 400;
			$log->error('warning', array('id'=>1002,'description'=>'Cant create new user, already exists.','details'=>'The user ['.$username.'] already exists.'));
		
		} else if ( $emailExists == true ) {
			$status = 400;
			$log->error('warning', array('id'=>1003,'description'=>'Cant create new user, already exists.','details'=>'The email ['.$email.'] already exists.'));

		} else {

			$user->username 		= $username;
			$user->setPassword($password);
			$user->account_email 	= $email;
			$result 				= $user->push();

			if ( $result == true ) {
				
				$json["user"] = $user->getProfile();
				$status = 200;

		    	//
		    	// After Process
		    	//
		    	class Process extends ServerTask {
		    		public function run() {
		    			global $user;
		    			return $user->sendEmail('Welcome');
		    		}
		    	}

		    	$process = new Process();

			}
			
		}

	}

