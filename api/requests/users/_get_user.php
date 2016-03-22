<?php
	$log->addFile( __FILE__ );

	requireModel('Users/DynamicUser');

	$user = new DynamicUser();
	$user->setUniqueIdentifier( $username );
	if ( isset($GET_exists) && isset($_SESSION['user']) && $_SESSION['user']->hasSession() ) {
		$user->id = $_SESSION['user']->id;
		$result = $user->pull('not_me');
	} else {
		$result = $user->pull();
	}
	$user->pullIdeas();

    $log->append('full_data', $user);

	if ( $result != false ) {

		if ( isset($GET_exists) ) {
			$json['user'] = true;
		} else if( isset($GET_summary) ) {
			$json['user'] = $user->getSummary();
		} else {
			
			$json['user'] = $user->getProfile();
			$status = 200;

            //
            // Inquiry to the user
            // 
            //
            // After Process
            //
            class Process extends ServerTask {

                public $user;

                function __construct($user) {
                    $this->user = $user;
                }

                public function run() {
                    
                    $user = $this->user;

					//
					// update view and popularity
					//
					$user->addView( false, false );
					$user->calcPopularity( false, false );

					$user->update('view_count,popularity_alltime');
                }
            }

            $process = new Process( $user );


		}
		
	} else {
		$status = 404;
		if ( isset($GET_exists) ) {
			$json['user'] = false;
		} else {
			$json['user'] = null;
		}
	}

