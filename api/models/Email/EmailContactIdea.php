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
		public $template 	= 'ContactIdea';

		public function get_subject( $data ) {
			$subject =  "@".$data['from']['username']." sent an Inquiry about an Idea!";
			return $subject;
		}

	}