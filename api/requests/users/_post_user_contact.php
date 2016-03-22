<?php
	$log->addFile( __FILE__ );

	global $action;
	global $username;
	global $json;
	global $process;
	global $log;

	requireModel('Users/DynamicUser');
	requireModel('idea');
	requireModel('Conversations/DynamicConversation');

	$log->append('_post_user_contact', $action);
	$log->append('_post_user_contact', $username);

	$user = new DynamicUser();
	$user->username = $username;
	$user->pull();
	$success = false;

	if ( !empty($_POST['message']) ) {

		$message = $_POST['message'];

		$canMessage = $_SESSION['user']->canSendMessage( $message, $user->id );
		$messageObject = null;

		if ( $canMessage ) {
			//
			// Inquiry about an idea
			// 
			if ( !empty($_POST['idea']) && is_numeric($_POST['idea']) ) {

				$ideaID = $_POST['idea'];

			    $idea = new EditableIdea();
				$idea->id = $ideaID;
				$result_idea = $idea->pull();

				$log->append('_post_user_contact', $result_idea);
				$log->append('_post_user_contact', $idea->user_id);
				$log->append('_post_user_contact', $user->id);

			    if ( $result_idea == false ) {
			    	$status = 400;
			    	$log->error('error', array('id'=>1016,'description'=>'Bad Request - Unknown','details'=>'Bad mysql requests, see logs.'));
			    } else if ( $idea->user_id == $user->id ) {

					$conversation = new DynamicConversation();
					$conversation->from_id = $_SESSION['user']->id;
					$conversation->to_id = $user->id;
					$conversation->idea_id = $ideaID;

					$conversationExists = $conversation->getConversation();

					if ( $conversationExists ) {
						$messageObject = $conversation->addMessage( $_SESSION['user']->id, $message, $ideaID );
					}

					$log->append('conversation', $conversation);
					$log->append('conversation', $conversationExists);

					$log->append('messageObject', $messageObject);

					//
					// Inquiry to the user
					// 
			    	//
			    	// After Process
			    	//
			    	class Process extends ServerTask {

			    		public $idea;
			    		public $message;

			    		function __construct($idea, $message, $user, $session, $messageObject) {
				            $this->idea 	= $idea;
				            $this->message 	= $message;
				       		$this->user 	= $user;
				            $this->session 	= $session;
				            $this->messageObject = $messageObject;
				        }

			    		public function run() {

			    			requireModel('Notifications/DynamicNotification');

			    			$flag = null;

			    			$notification = new DynamicNotification();
			    			$notification->fromTo( $this->session->id, $this->user->id );
			    			$notification->setRelated( $this->idea->id );

			    			if ( $this->messageObject ) {
			    				$notification->setRelatedMessage( $this->messageObject->id );

			    				requireModel('Flags/StaticFlag');
					            $flagObject = new StaticFlag();
					            $flagObject->hash = $this->messageObject->hash;
					            $flag = $flagObject->getEncodedHash('message', $this->messageObject->id);

			    			}

			    			$notification->type('contact-idea');
			    			$notification->send();

			    			return $this->idea->contact($this->message, array("flag"=>$flag) );
			    		}
			    	}

			    	$process = new Process( $idea, $message, $user, $_SESSION['user'], $messageObject);


			    }

			} else {

				$conversation = new DynamicConversation();
				$conversation->from_id = $_SESSION['user']->id;
				$conversation->to_id = $user->id;

				$log->append('conversation', $conversation);

				$conversationExists = $conversation->getConversation();

				if ( $conversationExists ) {
					$messageObject = $conversation->addMessage( $_SESSION['user']->id, $message, $ideaID );
				}

				$log->append('messageObject', $messageObject);

				//
				// Inquiry to the user
				// 
		    	//
		    	// After Process
		    	//
		    	class Process extends ServerTask {

		    		public $user;
		    		public $message;

		    		function __construct($user, $message, $session, $messageObject) {
			            $this->user 	= $user;
			            $this->message 	= $message;
			            $this->session 	= $session;
			        	$this->messageObject = $messageObject;
			        }

		    		public function run() {
		    			global $log;
		    			requireModel('Notifications/DynamicNotification');

		    			$notification = new DynamicNotification();
		    			$notification->fromTo( $this->session->id, $this->user->id );

		    			$flag = null;

		    			$log->append('running_process', $this->messageObject);

		    			if ( $this->messageObject ) {
		    				$notification->setRelatedMessage( $this->messageObject->id );

		    				requireModel('Flags/StaticFlag');
				            $flagObject = new StaticFlag();
				            $flagObject->hash = $this->messageObject->hash;
				            $flag = $flagObject->getEncodedHash('message', $this->messageObject->id);

				            $log->append('running_process', $flag);

		    			}

		    			$notification->type('contact');
		    			$notification->send();

		    			return $this->user->sendEmail('ContactUser', array("message"=>$this->message, "flag"=>$flag));
		    		}
		    	}

		    	$process = new Process( $user, $message, $_SESSION['user'], $messageObject );


			}
		} else {
			// $status = 403;
			$log->error('error', array('id'=>1030,'description'=>'Cannot Send Message','details'=>'Spam alert.'));
		}
	} else {
		$status = 400;
		$log->error('error', array('id'=>1016,'description'=>'Missing Message','details'=>'Missing message field in POST'));
	}

