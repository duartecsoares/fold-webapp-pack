<?php

	$log->addFile( __FILE__ );

	requireModel('Email/EmailModel');
    requireModel('Users/DynamicUser');
	requireModel('idea');

	$data = array();

	if ( isset($_SESSION['user']) ) {

		if ( isset( $_GET['email']) && !empty($_GET['email']) ) {
			$which = $_GET['email'];
			$data = $_GET['data'];
		} else {
			$which = 'Welcome';
		}

		if ( !empty($_GET['username']) ) {

			$user = new DynamicUser();
			$user->username = $_GET['username'];
			$userSuccess = $user->pull();

			if ( !$userSuccess ) {
				$log->append('SendingEmail', '[✕] username not found');
			} else {

				$data['to'] = $user->getProfile();

				if ( $which == 'FavoriteIdea' || $which == 'ContactIdea' ) {
				    
				    $idea = new EditableIdea();
					$idea->id = $_GET['idea'];
					$result_idea = $idea->pull();

					if ( $result_idea && $idea->user_id == $user->id ) {
						$data['idea'] = $idea->ideaProfile();
					} else {
						$log->append('SendingEmail', '[✕] this idea is not from '.$user->username);
					}

				}

				if ( $_GET['message'] ) {
					$data['message'] = $_GET['message'];
				} else  {
					$log->append('SendingEmail', '[✕] message not found');
				}

				$to 			= $_GET['to'] ? $_GET['to'] : $user->getContact();
				$toName 		= $_GET['toname'] ? $_GET['toname'] : $user->getName();
				$replyTo 		= $_GET['replyto'] ? $_GET['replyto'] : $_SESSION['user']->getContact();
				$replyToName 	= $_GET['replytoname'] ? $_GET['replytoname'] : $_SESSION['user']->getName();

				$email = new Email();
				$email->addTo( $to, $toname );
				$email->replyTo( $replyTo, $replyToName );
				$json['email'] = $email->send($which, $data);

			}


		} else {
			$log->append('SendingEmail', '[✕] username not found');
		}
	} else {
		$log->append('SendingEmail', '[✕] user not logged in');
	}