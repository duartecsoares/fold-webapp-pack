<?php
	$log->addFile( __FILE__ );
	
	// GET users
	$dirFile = dirname(__FILE__);

	include_once $dirFile . "/../../models/user.php";

	global $process;

	//
	// Start Execution
	//

	if ( !isset($_SESSION["user"]) ) {

		$status = 404;
		$json["user"] = null;

	} else {


		//
		// is this a reset password or just update password?
		//
		if ( isset($_PUT['reset']) && !empty($_PUT['reset']) && !empty($_PUT['password']) ) {

			requireModel('Users/DynamicUser');
			
			$user 					= new DynamicUser();
			$user->password_auth 	= $_PUT['reset'];

			$user->pull('reset');

			if ( $user->exists() && $user->isPasswordResetValid( 30 ) ) {
					
				$success = $user->setPassword( base64_decode($_PUT['password']) );

				if ( $success ) {

					$user->password_auth_created = '0000-00-00 00:00:00';
					$success = $user->update('password,password_auth_created');

					if ( $success ) {
						$status = 200;

						//
						// send email that password was changed
						//

				    	//
				    	// After Process, code processed after the request is returned
				    	//
				    	class Process extends ServerTask {

				    		public $user;

				    		function __construct($user) {
					            $this->user = $user;
					        }

				    		public function run() {
				    			return $this->user->sendEmail('ResetPasswordSuccess');
				    		}
				    	}

				    	$process = new Process( $user );
				    	//
				    	//
				    	//

					}


				} else {
					$status = 400;
				}

			} else {
				$status = 404;
			}

		} else {

			$newPassword 	= base64_decode($_PUT['new']);
			$oldPassword 	= base64_decode($_PUT['old']);

			$_SESSION["user"]->pull();

			$success = $_SESSION["user"]->updatePassword( $newPassword, $oldPassword );

			if ( $success && $_SESSION["user"]->update('password') ) {

				$status = 200;

				//
				// After Process, code processed after the request is returned
				//
				class Process extends ServerTask {

					public $user;

					function __construct($user) {
				        $this->user = $user;
				    }

					public function run() {
						return $this->user->sendEmail('PasswordChanged');
					}
				}

				$process = new Process( $_SESSION["user"] );
				//
				//
				//

			} else {
				$status = 400;
			}

		}

	}
	
