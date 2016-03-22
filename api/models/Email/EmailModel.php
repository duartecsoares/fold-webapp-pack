<?php
	requireVendor('MailerMaster/email');

	class Email {

		public $to;
		
		public $toEmail;
		public $toName;

		public $replyEmail;
		public $replyName;

        //
        //
        // V1
        //
        //
		// function __construct() {
  //       	global $log;

		// 	$this->mailer = new PHPMailer();
		// 	$this->setAuth();
        	
  //       	$log->append('SendingEmail', 'Processing -------------------');

  //       }

  //       public function addTo( $email = null, $name = null) {
  //       	global $__dev__, $log;

	 //        $log->append('SendingEmail', 'To: '.$email.' | '.$name);    

  // 			if ( $__dev__ == true ) {
	 //            if ( in_array( $email, $this->devEmails ) ) {
	 //                $this->mailer->addAddress($email, $name);  
	 //                $log->append('SendingEmail', 'Final To: '.$email.' | '.$name);    
	 //            } else {
	 //                $log->append('SendingEmail', 'Final To: hello@carlosgavina.com | '.$name);    
	 //            	$this->mailer->addAddress('hello@carlosgavina.com', $name);
	 //            }
	 //        }
  //       }

  //       protected function setAuth() {
  //       	$mailer = $this->mailer;

  //       	$mailer->IsSMTP();
		// 	$mailer->CharSet 	= 'UTF-8';

		// 	$mailer->Host       = "smtp.gmail.com"; 				// SMTP server example
		// 	$mailer->SMTPDebug  = 0;                     			// enables SMTP debug information (for testing)
		// 	$mailer->SMTPAuth   = true;                  			// enable SMTP authentication
		// 	$mailer->Port       = 25;                    			// set the SMTP port for the GMAIL server
		// 	$mailer->Username   = "notification@builditwith.me"; 	// SMTP account username example
		// 	$mailer->Password   = "admnotify15";        			// SMTP account password example

  //       }

  //       public function replyTo($email = null, $name = null) {
  //       	global $__dev__, $log;

	 //        $log->append('SendingEmail', 'Reply To: '.$email.' | '.$name);    

  // 			if ( $__dev__ == true ) {
	 //            if ( in_array( $email, $this->devEmails ) ) {
  //       			$this->mailer->addReplyTo($email, $name);
  //       			$log->append('SendingEmail', 'Final Reply To: '.$email.' | '.$name); 
	 //            } else {
	 //                $log->append('SendingEmail', 'Final Reply To: hello@carlosgavina.com | '.$name);    
	 //            }
	 //        }

  //       }

  //       public function setSubject( $subject ) {
  //       	$this->subject = $subject;
  //       }

  //       public function send( $template = 'HelloWorld', $variables = array()) {
  //       	global $log;

  //       	$log->append('SendingEmail', '------------------------------');

  //       	$success 	= false;
  //       	$mailer 	= $this->mailer;

  //       	$fileLoaded = requireModel('Email/Email'.$template);

	 //        if ( $fileLoaded && class_exists('EmailClass') ) {

		// 		$log->append('SendingEmail', 'Class : Email'.$template.'.php');
		// 		$log->append('SendingEmail', 'Template : '.$template.'.html');

	 //        	$email = new EmailClass();

	 //        	//
	 //        	// append global information that should be accessible
	 //        	// on every email, like user in session
	 //        	//
	 //        	if ( isset($_SESSION['user']) && $_SESSION['user']->hasSession() == true ) {
	 //        		$variables['from'] = $_SESSION['user']->getProfile();
	 //        	} else {
	 //        		$variables['from'] = null;
	 //        	}

		// 		$mailer->Body    	= $email->getHTML($variables);
		// 		$mailer->AltBody 	= $email->getNonHTML($variables);
		// 		$mailer->From 		= $email->from;
		// 		$mailer->FromName 	= $email->fromName;

		// 		//
		// 		// if a "get_subject" method exists
		// 		// generate a custom subject
		// 		//
	 //            if ( method_exists('EmailClass', "get_subject") ) {
	            	
	 //            	$log->append('SendingEmail', 'get_subject() function detected, generate subject.');
	 //        		$mailer->Subject 	= $email->get_subject( $variables );

	 //            } else if ( !empty($this->subject) ) {
		// 			$mailer->Subject 	= $this->subject;
		// 		} else {
		// 			$mailer->Subject 	= $email->subject;
		// 		}


		// 		//
		// 		//	Send Email data
		// 		//
		// 		$log->append('SendingEmail', '------------------------------');

		// 		$log->append('SendingEmail', 'Subject: '.$mailer->Subject);
		// 		$log->append('SendingEmail', 'To: ');
		// 		$log->append('SendingEmail', $mailer->getToAddresses());


		// 		$log->append('SendingEmail', 'Template Variables -----------');
		// 		$log->append('SendingEmail', $variables);
		// 		$log->append('SendingEmail', '------------------------------');

		// 		//
		// 		//	Send Email
		// 		//
		// 		$success = $mailer->send();

		// 		//
		// 		//	Success Log
		// 		//
		// 		if ( $success ) {
		// 			$log->append('SendingEmail', '[✓] Sent!');
		// 		} else {
		// 			$log->append('SendingEmail', '[✕] Failed');
		// 		}
				

		// 	} else {
		// 		$log->append('SendingEmail', 'Failed to load Email Class: Email'.$template.'.php');
		// 	}

		// 	return $success;

  //       }

        //
        //
        // V2
        //
        //
		
		protected $devEmails = array('dianaribeiro14@hotmail.com', 'duartecsoares@me.com', 'hello@carlosgavina.com', 'santiago@madebyfold.com', 'gavinsan@gmail.com');
		protected $devAlias = array('@carlosgavina.com', '@madebyfold.com');

        function __construct() {
        	global $log;

			// $this->mailer = new PHPMailer();
			// $this->setAuth();
        	
        	$log->append('SendingEmail', 'Processing -------------------');

        }

        public function addTo( $email = null, $name = null) {
        	global $__dev__, $log;

  			if ( $__dev__ == true ) {

        		$log->append('SendingEmail', 'addTo -------------------');
  				$log->append('SendingEmail', $this->devAlias[0]);
  				$log->append('SendingEmail', $this->devAlias[1]);
  				$log->append('SendingEmail', $email);
  				$log->append('SendingEmail', strpos($email, $this->devAlias[0]));
  				$log->append('SendingEmail', strpos($email, $this->devAlias[1]));

	            if ( in_array( $email, $this->devEmails ) || strpos($email, $this->devAlias[0]) !== false || strpos($email, $this->devAlias[1]) !== false) {
	            	$this->toEmail 	= $email;
	            	$this->toName 	= $name;
	            } else {
	            	$this->toEmail 	= 'hello@madebyfold.com';
	            	$this->toName 	= 'Build Notification';
	            }
	        } else {
            	$this->toEmail 	= $email;
            	$this->toName 	= $name;
	        }
        }

        public function replyTo($email = null, $name = null) {
        	global $__dev__, $log;

			$log->append('SendingEmail', 'replyTo -------------------');
			$log->append('SendingEmail', $__dev__);

        	// if ( $__dev__ == true ) {
        		$this->replyEmail 	= $email;
            	$this->replyName 	= $name;
        	// }

        	$log->append('SendingEmail', $this->replyEmail);
        	$log->append('SendingEmail', $this->replyName);

            // if ( in_array( $email, $this->devEmails ) || strpos($email, $this->devAlias[0]) !== false || strpos($email, $this->devAlias[1]) !== false) {

            // } else {
            //	$this->toEmail 	= 'hello@madebyfold.com';
            //	$this->toName 	= 'Build Notification';
            // }

        }

        public function setSubject( $subject ) {
        	$this->subject = $subject;
        }

        public function send( $template = 'HelloWorld', $variables = array()) {
        	global $log, $__serverEvironment__;

        	$log->append('SendingEmail', '------------------------------');

        	$success 	= false;

        	$fileLoaded = requireModel('Email/Email'.$template);

	        if ( $fileLoaded && class_exists('EmailClass') ) {

				$log->append('SendingEmail', 'Class : Email'.$template.'.php');
				$log->append('SendingEmail', 'Template : '.$template.'.html');

	        	$email = new EmailClass();

	        	//
	        	// append global information that should be accessible
	        	// on every email, like user in session
	        	//
	        	if ( isset($_SESSION['user']) && $_SESSION['user']->hasSession() == true ) {
	        		$variables['from'] = $_SESSION['user']->getProfile();
	        	} else {
	        		$variables['from'] = null;
	        	}

	        	if ( $__serverEvironment__ == 'prod' ) {
	        		$routeBase = 'http://builditwith.me/';
	        	} else if ( $__serverEvironment__ != 'local' ) {
	        		$routeBase = 'http://'.$__serverEvironment__.'.builditwith.me/';
	        	} else {
	        		$routeBase = 'http://builditwithme.dev/';
	        	}
	        	
	        	//
	        	// app routes (router)
	        	//
	        	$variables['routes'] = array(
	        		
        			"app" 			=> $routeBase,
        			"user_profile" 	=> $routeBase.'profile/',
        			"idea_profile" 	=> $routeBase.'ideas/',
        			"people" 		=> $routeBase.'people',
        			"ideas" 		=> $routeBase.'ideas',
        			"flag" 			=> $routeBase.'flag?hash=',
        			
        			"user_settings"					=> $routeBase.'account/profile',
        			"user_settings_notifications"	=> $routeBase.'account/notifications',

        			"edit_idea"			=> $routeBase.'edit/',
        			"idea_tour"			=> $routeBase.'idea-tour',
        			"password_reset" 	=> $routeBase.'password-reset/',

        			"facebook"		=> 'http://facebook.com/builditwithme',
        			"twitter"		=> 'http://twitter.com/builditwithme',
        			"email_support"	=> 'hello@builditwith.me',

        			"image_biwm_logo" 		=> 'https://s3.amazonaws.com/buildit-storage/emails/assets/biwm-logo.png',
        			"image_twitter_logo" 	=> 'https://s3.amazonaws.com/buildit-storage/emails/assets/twitter.png',
        			"image_facebook_logo" 	=> 'https://s3.amazonaws.com/buildit-storage/emails/assets/facebook.png',
        			"image_default_avatar" 	=> 'https://s3.amazonaws.com/buildit-storage/emails/assets/default-avatar.png',
        			"image_welcome"			=> 'https://s3.amazonaws.com/buildit-storage/emails/assets/email-welcome.jpg',
        			"image_dribbble_logo"	=> 'https://s3.amazonaws.com/buildit-storage/emails/assets/dribbble.png',
        			"image_github_logo"		=> 'https://s3.amazonaws.com/buildit-storage/emails/assets/github.png',
        			"image_reply"			=> 'https://s3.amazonaws.com/buildit-storage/emails/assets/reply-dark.png',

        			"time_stamp" => time()

        		);

				$log->append('SendingEmail', 'routes : ');
				$log->append('SendingEmail', $variables['routes']);

				//
				// if a "get_subject" method exists
				// generate a custom subject
				//
	            if ( method_exists('EmailClass', "get_subject") ) {
	        		$subject = $email->get_subject( $variables );
	            } else if ( !empty($this->subject) ) {
					$subject = $this->subject;
				} else {
					$subject = $email->subject;
				}


				$log->append('SendingEmail', '------ VARIABLES ------');
				$log->append('SendingEmail', $variables);


				//
				// emails are sent through our Cluster Servers on Amazon
				//
				requireFunction('ClusterTask');

        		$log->append('ClusterMail', '- Init -------------------');

				$ClusterMail = new ClusterTask();

				$data = array(
					"Body" 		=> $email->getHTML($variables),
					"AltBody" 	=> $email->getNonHTML($variables),
					
					"From"		=> $email->from,
					"FromName"	=> $email->fromName,
					
					"Subject"	=> $subject,
					
					"ToEmail" 	=> $this->toEmail,
					"ToName" 	=> $this->toName,

					"ReplyEmail" 	=> $this->replyEmail,
					"ReplyName" 	=> $this->replyName
				);

				$ClusterMail->set('emailer', $data, 'POST');

        		$log->append('ClusterMail', $ClusterMail);

				$success = $ClusterMail->run();

        		$log->append('ClusterMail', $success);


				//
				//	Success Log
				//
				if ( $success ) {
					$log->append('SendingEmail', '[✓] Sent!');
				} else {
					$log->append('SendingEmail', '[✕] Failed');
				}
				

			} else {
				$log->append('SendingEmail', 'Failed to load Email Class: Email'.$template.'.php');
			}

			return $success;

        }

	} 