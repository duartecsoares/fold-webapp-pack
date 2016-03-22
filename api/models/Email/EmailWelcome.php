<?php
	requireModel('Email/EmailTemplate');


	Class EmailClass extends EmailTemplate {
		public $template 	= 'Welcome';
		public $subject 	= 'Welcome to Build it With Me!';
	}