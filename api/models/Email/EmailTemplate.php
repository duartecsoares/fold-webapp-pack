<?php

	requireVendor('Twig/Autoloader');
	Twig_Autoloader::register();

	class EmailTemplate {

		public $from 		= 'hello@madebyfold.com';
		public $fromName 	= 'Build It With Me';
		public $template 	= 'HelloWorld';
		public $subject 	= 'Hello Email!';

		protected $templatesFolder;
        
        function __construct() {
        	$path = dirname(__FILE__).'/../../Templates/Emails/';
			$this->templatesFolder = $path;

			$loader = new Twig_Loader_Filesystem( $path );
			$this->twig = new Twig_Environment($loader);

        }

        public function afterProcess( $content, $type ) {
        	return $content;
        }

		public function getNonHTML( $variables = array()) {
			$path = $this->templatesFolder.$this->template.'.txt';
			
			$variables = (array) $variables;

			if ( file_exists($path ) ) {
				$content = $this->twig->render($this->template.'.txt', $variables);
			} else {
				$content = null;
			}

			$content = $this->afterProcess( $content, 'text' );
			
			return $content;
		}

		public function getHTML( $variables = array() ) {
			$path = $this->templatesFolder.$this->template.'.html';
			
			$variables = (array) $variables;

			if ( file_exists($path ) ) {
				$content = $this->twig->render($this->template.'.html', $variables);
			} else {
				$content = null;
			}

			$content = $this->afterProcess( $content, 'html' );
			
			return $content;
		}

	}