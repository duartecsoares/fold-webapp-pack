<?php
	
	global $process;

	if ( !isset($_SESSION['user']) || ( isset($_SESSION['user']) && !$_SESSION['user']->hasSession() ) ) {


		if ( !empty($_POST['email']) || !empty($_POST['username']) ){
			requireModel('Users/DynamicUser');
			$user = new DynamicUser();

			if ( !empty($_POST['email']) ) {

				if ( filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) ) {
					$user->account_email = $_POST['email'];
				} else {
					$user->username = $_POST['email'];
				}
				
			} else if ( !empty($_POST['username']) ) {

				if ( !filter_var($_POST['username'], FILTER_VALIDATE_EMAIL) ) {
					$user->username = $_POST['username'];
				} else {
					$user->account_email = $_POST['username'];
				}

			}

			//
			// user exists?
			//
			$user->pull('exists');

			if ( $user->exists() ) {

				$hash 	= $user->generateResetPasswordHash();
				$result = $user->update('password_auth,password_auth_created');

				if ( $result ) {
					
			    	//
			    	// After Process, code processed after the request is returned
			    	//
			    	class Process extends ServerTask {

			    		public $user;
			    		public $hash;

			    		function __construct($user, $hash) {
				            $this->user = $user;
				            $this->hash = $hash;
				        }

			    		public function run() {
			    			return $this->user->sendEmail('ResetPassword', array("hash"=>$this->hash, "valid_minutes"=>30) );
			    		}
			    	}

			    	$process = new Process( $user, $hash );
			    	//
			    	//
			    	//

				} else {
					$status = 400;
		        	$log->error('error', array('id'=>1002,'description'=>'Bad Request','details'=>'Could not update the database.'));
				}

			} else {
				$status = 404;
			}

			$log->append('user_exists', $user->exists());
			

		} else {
			$status = 400;
        	$log->error('error', array('id'=>1002,'description'=>'Bad Request - Bad Parameter.','details'=>'[email] and [username] invalid.'));
		}

	} else {
		$status = 400;
		$log->error('error', array('id'=>1003,'description'=>'Forbiden','details'=>'Session available.'));
	}

