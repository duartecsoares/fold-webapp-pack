<?php
	$log->addFile( __FILE__ );
	
	global $json;

	//
	//
	//
	$service 	= ( isset($__request__[3]) ? strtolower($__request__[3]) : null );
	$username 	= ( isset($_POST['username']) ? strtolower($_POST['username']) : null );
	$proceed 	= true;

    if ( isset($_SESSION['user']) && $_SESSION['user']->hasSession() && !empty($service) ) {

		$log->append('connecting', '---- '.$service.' ----');

		$connectObject = $_SESSION["user"]->getConnectObject();

		if ( $connectObject->$service ) {

			if ( $username ) {
				$connectObject->$service->username = $username;
			}

			$connectResult = $connectObject->$service->pullAll();
			$log->append('connecting', $connectResult);
			$log->append('connecting', $connectObject->$service);

			if ( $connectResult[0] == null ) {
					
				$connectObject->$service->reset();
				$status = 404;

			} else {
				
				$_SESSION["user"]->connections_data = $connectObject->getStorableData();
				$_SESSION["user"]->update('connections_data');
				$json[$service] = $connectObject->$service->get();

				$_SESSION["user"]->sendAvatarToCDN( $service, $connectObject->$service->avatar_url );
				$json['avatars'] = $_SESSION['user']->getAvatars( true );

				$status = 200;

				//
				// After Process, code processed after the request is returned
				//
				class Process extends ServerTask {

					public $user;
					public $service;
					public $url;

					function __construct($user, $service, $url) {
			            $this->user 	= $user;
			            $this->service 	= $service;
			            $this->url 		= $url;
			        }

					public function run() {
						if ( $this->user ) {
							$this->user->calcPopularity( false, false );
							$this->user->update('preferences,popularity_alltime');
						}
					}
				}

				$process = new Process( $_SESSION["user"], $service, $connectObject->$service->avatar_url );

				//
				//
				//
			}

		} else {
			// error, unknown service
			$status = 400;
		}
    }


