<?php
	requireModel('Email/EmailTemplate');

	//
	// Available global objects:
	//
	// from 	- information of the "From" user (user that favorites an idea)
	// to 		- information of the "To" user (owner of the idea for example)
	// 
	// TODO: routing information from the webapp
	//
	Class EmailClass extends EmailTemplate {
		public $template 	= 'FavoriteIdea';
		public $subject 	= 'Your idea was favorited!';

		public function get_subject( $data ) {
			global $log;
			
			$log->append('info_to_email', $data);

			$subject = "@".$data['from']['username']." fave'd your idea: ".$data['idea']['name'];
			return $subject;
		}

	}